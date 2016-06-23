<?php

namespace App\Http\Controllers;

use Storage;
use App\Contracts\SMSServiceContract;
use App\Exceptions\BadRequestException;
use App\Models\Device;
use App\Models\Follower;
use App\Models\Group;
use App\Models\User;
use App\Models\UserGroup;
use Illuminate\Http\Request;

use App\Http\Requests;

class UserController extends BaseController
{
    /**
     * 获取登录验证码
     *
     * @param Request $request
     * @param SMSServiceContract $SMS
     * @return \Illuminate\Http\JsonResponse
     * @throws BadRequestException
     */
    public function loginVerifyCode(Request $request, SMSServiceContract $SMS)
    {
        $this->validateParams($request->all(), [
            'username' => 'required',
        ]);

        $username = $request->input('username');

        $user = User::where('tel', $username)->first();
        if(empty($user)) {
            $api_token_length = config('message.api_token_length');
            User::create(['tel' => $username, 'api_token' => str_random($api_token_length)]);
        }
        
        $verify_code_refresh_time = strtotime($user['verify_code_refresh_at']);
        if(!empty($user['verify_code_refresh_at']) && $verify_code_refresh_time > time()) {
            $seconds = $verify_code_refresh_time - time();
            throw new BadRequestException("请求失败, 请在 $seconds 秒后重新请求", 400);
        }

        $verify_code = mt_rand(100000, 999999);
        User::where('tel', $username)
            ->update([
                'verify_code' => $verify_code,
                'verify_code_refresh_at' => date('Y-m-d H:i:s', strtotime("+1 minute")),
                'verify_code_expire_at' => date('Y-m-d H:i:s', strtotime("+5 minutes")),
            ]);

        // 发送验证码短信
        $message = "【云片网】您的验证码是$verify_code";
        $SMS->SendSMS($username, $message);

        return response()->json(['msg' => 'success']);
    }

    /**
     * 登录
     *
     * @param Request $request
     * @return mixed
     * @throws BadRequestException
     */
    public function login(Request $request)
    {
        $this->validateParams($request->all(), [
            'username' => 'required|exists:users,tel',
            'verify_code' => 'required',
            'ip' => 'required',
            'client' => 'required',
        ]);

        $username = $request->input('username');
        $verify_code = $request->input('verify_code');
        $user = User::where('tel', $username)
            ->where('verify_code', $verify_code)
            ->first();

        if(empty($user)) {
            throw new BadRequestException('登录失败', 400);
        }

        if(strtotime($user['verify_code_expire_at']) <= time()) {
            throw new BadRequestException('验证码失效, 请重新获取', 400);
        }

        $ip = $request->input('ip');
        $client = $request->input('client');

        $device = $user->devices()
            ->where('ip', $ip)
            ->where('client', $client)
            ->first();

        $api_token = str_random(32);
        if(empty($device)) {
            $device = new Device([
                'ip' => $ip,
                'client' => $client,
                'api_token' => $api_token,
            ]);
        } else {
            $device['api_token'] = $api_token;
        }
        $device->save();

        // 登录成功后, 验证码立即失效
        $user['verify_code_expire_at'] = null;
        $user['last_login_at'] = date('Y-m-d H:i:s', time());
        $user->save();

        $user['api_token'] = $api_token;

        return $user;
    }

    /**
     * 注册
     *
     * @deprecated
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $this->validateParams($request->all(), [
            'username' => 'required|unique:users,tel',
        ]);

        $username = $request->input('username');
        User::create(['tel' => $username, 'api_token' => str_random(24)]);

        return response()->json(['msg' => 'success']);
    }

    /**
     * 关注用户
     *
     * @param Request $request
     * @param $f_user_id
     * @return Follower
     * @throws BadRequestException
     */
    public function follow(Request $request, $f_user_id)
    {
        $user_id = $this->user_id();

        $this->validateParams(compact('f_user_id'), [
            'f_user_id' => 'required|numeric|exists:users,id',
        ]);

        // 关注者与被关注则相同时
        if($user_id == $f_user_id) {
            throw new BadRequestException('无法关注自己', 400);
        }

        $follower = Follower::where('follower_id', $user_id)
            ->where('followee_id', $f_user_id)
            ->get();
        if(!$follower->isEmpty()) {
            // 已关注被关注者时
            throw new BadRequestException('已关注', 400);
        }

        $follower = Follower::where('follower_id', $user_id)
            ->where('followee_id', $f_user_id)
            ->withTrashed()
            ->get();

        if(!$follower->isEmpty()) {
            Follower::where('follower_id', $user_id)
                ->where('followee_id', $f_user_id)
                ->withTrashed()
                ->restore();

            return Follower::where('follower_id', $user_id)
                ->where('followee_id', $f_user_id)
                ->get();
        }

        $follower = Follower::where('follower_id', $f_user_id)
            ->where('followee_id', $user_id)
            ->first();

        if(!empty($follower)) {
            // 被关注者已关注专注者时, 获取 group_id
            $group_id = $follower['group_id'];
//            $group = $follower->group;
//            $group_id = $group['id'];
        } else {
            // 双方互不关注时, 创建对话
            $group_name = "私聊: $user_id, $f_user_id";
            $conversation = app('IM')->createConversation($group_name, [$user_id, $f_user_id]);
            $conv_id = $conversation['objectId'];

            $group = Group::create([
                'name' => $group_name,
                'type' => 'private',
                'conv_id' => $conv_id,
            ]);
            $group_id = $group['id'];

            UserGroup::create(['user_id' => $user_id, 'group_id' => $group_id]);
            UserGroup::create(['user_id' => $f_user_id, 'group_id' => $group_id]);
        }

        $follower = new Follower();
        $follower->follower_id = intval($user_id);
        $follower->followee_id = intval($f_user_id);
        $follower->group_id = $group_id;
        $follower->save();

        unset($follower['id']);

        return $follower;
    }

    /**
     * 取消关注
     *
     * @param Request $request
     * @param $f_user_id
     * @return mixed
     * @throws BadRequestException
     */
    public function unfollow(Request $request, $f_user_id)
    {
        $user_id = $this->user_id();

        $this->validateParams(compact('f_user_id'), [
            'f_user_id' => 'required|numeric|exists:users,id',
        ]);

        if($user_id == $f_user_id) {
            throw new BadRequestException('无法对自己取消关注', 400);
        }

        Follower::where('follower_id', $user_id)
            ->where('followee_id', $f_user_id)
            ->delete();

        return Follower::where('follower_id', $user_id)->get();
    }

    /**
     * 获取被关注的人
     *
     * @param Request $request
     * @return mixed
     */
    public function getFollow(Request $request)
    {
        $user_id = $this->user_id();

        $followers = Follower::where('follower_id', $user_id)->get();

        $users = [];
        foreach ($followers as $follower) {
            $user = $follower->followee_user;
            $group = $follower->group;

            $users[] = [
                'id' => $user['id'],
                'nick_name' => $user['nick_name'],
                'conv_id' => $group['conv_id'],
            ];
        }

        return $users;
    }

    /**
     * 获取本用户个人信息
     * 
     * @param Request $request
     * @return mixed
     */
    public function getUserProfile(Request $request)
    {
        $user_id = $this->user_id();

        $user = User::find($user_id);

        return $user;
    }

    /**
     * 编辑个人信息
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function editUserProfile(Request $request)
    {
        $user = $this->user();

        $username = $request->input('username');
        $display_name = $request->input('display_name');
        $email = $request->input('email');
        $tel = $request->input('tel');

        if(!empty($username)) {
            $user['user_name'] = $username;
        }

        if(!empty($display_name)) {
            $user['display_name'] = $display_name;
        }

        if(!empty($email)) {
            $user['email'] = $email;
        }

        if(!empty($tel)) {
            $user['tel'] = $tel;
        }

        $user->save();

        return response('', 204);
    }

    /**
     * 编辑本用户头像
     * 
     * @param Request $request
     * @return mixed
     * @throws BadRequestException
     */
    public function editUserAvatar(Request $request)
    {
        $user = $this->user();

        $old_avatar_url = $user['avatar_url'];

        if(!$request->hasFile('avatar')) {
            throw new BadRequestException('上传文件为空', 400);
        }

        $file = $request->file('avatar');
        if(!$file->isValid()) {
            throw new BadRequestException('文件上传出错', 400);
        }

        $newFileName = sha1(time().rand(0,10000)).'.'.$file->getClientOriginalExtension();
        $savePath = 'avatar/'.$newFileName;

        $bytes = Storage::put(
            $savePath,
            file_get_contents($file->getRealPath())
        );

        if(!Storage::exists($savePath)) {
            throw new BadRequestException('保存文件失败', 400);
        }

        $user['avatar_url'] = $savePath;
        $user->save();

        // 删除老文件
        Storage::delete($old_avatar_url);

        return response(Storage::get($savePath))
            ->header('Content-Type', Storage::mimeType($savePath));
    }

    /**
     * 获取用户头像
     *
     * @param Request $request
     * @return mixed
     * @throws BadRequestException
     */
    public function getUserAvatar(Request $request)
    {
        $user = $this->user();

        $avatar_url = $user['avatar_url'];

        if(empty($avatar_url) || !Storage::exists($avatar_url)) {
            throw new BadRequestException('用户头像不存在', 400);
        }

        return response(Storage::get($avatar_url))
            ->header('Content-Type', Storage::mimeType($avatar_url));
    }

    /**
     * 通过文件名获取头像
     *
     * @param Request $request
     * @param $avatar_name
     * @return mixed
     * @throws BadRequestException
     */
    public function getAvatarByName(Request $request, $avatar_name)
    {
        $avatar_url = 'avatar/' . $avatar_name;
        if(empty($avatar_url) || !Storage::exists($avatar_url)) {
            throw new BadRequestException('用户头像不存在', 400);
        }

        return response(Storage::get($avatar_url))
            ->header('Content-Type', Storage::mimeType($avatar_url));
    }

    /**
     * 查找用户
     *
     * @param Request $request
     * @return mixed
     */
    public function findUsers(Request $request)
    {
        $name = $request->input('name');

        $users = User::where('tel', 'like', "%$name%")
            ->orWhere('nick_name', 'like', "%$name%")
            ->where('searchable', true)
            ->get();

        $res = [];
        foreach ($users as $user) {
            $res[] = [
                'id' => $user['id'],
                'username' => $user['user_name'],
                'display_name' => $user['display_name'] ?: $user['tel'],
                'avatar_url' => $user['avatar_url'],
            ];
        }

        return $res;
    }

    /**
     * 激活设备列表
     * 
     * @param Request $request
     * @return mixed
     */
    public function activeDeviceList(Request $request)
    {
        $user = $this->user();
        $devices = $user->devices;

        $currentApiToken = $request->header('Authorization');
        $currentApiToken = explode(' ', $currentApiToken)[1];

        $currentDevice = null;
        $otherDevices = [];
        foreach ($devices as &$device)
        {
            $apiToken = $device['api_token'];

            unset($device['api_token']);

            if($apiToken == $currentApiToken) {
                $currentDevice = $device;
            } else {
                $otherDevices[] = $device;
            }
        }

        return [
            'current_device' => $currentDevice,
            'other_devices' => $otherDevices,
        ];
    }

    /**
     * 注销设备
     *
     * @param Request $request
     * @param $device_id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws BadRequestException
     */
    public function logoutDevice(Request $request, $device_id)
    {
        $device = Device::find($device_id);

        if(empty($device)) {
            throw new BadRequestException('该设备不存在', 400);
        }

        $api_token = $request->header('Authorization');
        $api_token = explode(' ', $api_token)[1];

        if($device['api_token'] == $api_token) {
            throw new BadRequestException('无法注销当前设备', 400);
        }

        $device->delete();

        return response('', 204);
    }

    /**
     * 偏好设置
     * 
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws BadRequestException
     */
    public function editPreference(Request $request)
    {
        $user = $this->user();

        $searchable = $request->input('searchable');

        $this->validateParams(compact('searchable'), [
            'searchable' => 'boolean',
        ]);

        $user['searchable'] = $searchable;
        $user->save();

        return response('', 204);
    }
}

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
            User::create(['tel' => $username, 'api_token' => str_random(24), 'active' => true]);
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
//            'ip' => 'required',
//            'client' => 'required',
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

        $device = $user->devices()
            ->where('ip', '192.168.3.111')
            ->first();

        $api_token = str_random(32);
        if(empty($device)) {
            $device = new Device([
                'ip' => '192.168.3.111',
                'client' => 'chrome',
                'active' => true,
                'api_token' => $api_token,
            ]);
        } else {
            $device['api_token'] = $api_token;
        }
        $device = $user->devices()->save($device);
        
        // 登录成功后, 验证码立即失效
//        User::where('tel', $username)
//            ->update(['verify_code_expire_at' => null]);

        $user['api_token'] = $api_token;

        return $user;
    }

    /**
     * 注册
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $this->validateParams($request->all(), [
            'username' => 'required|unique:users,tel',
        ]);

        $username = $request->input('username');
        User::create(['tel' => $username, 'api_token' => str_random(24), 'active' => true]);

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
     * 编辑本用户个人信息
     * @param Request $request
     */
    public function editUserProfile(Request $request)
    {

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

        if(!$request->hasFile('avatar')) {
            throw new BadRequestException('上传文件为空', 400);
        }

        $file = $request->file('avatar');
        if(!$file->isValid()) {
            throw new BadRequestException('文件上传出错', 400);
        }

        $newFileName = md5(time().rand(0,10000)).'.'.$file->getClientOriginalExtension();
        $savePath = 'avatar/'.$newFileName;

        $bytes = Storage::put(
            $savePath,
            file_get_contents($file->getRealPath())
        );

        if(!Storage::exists($savePath)) {
            throw new BadRequestException('保存文件失败', 400);
        }

        $user->update([
            'avatar_url' => $savePath
        ]);

        header("Content-Type: ".Storage::mimeType($savePath));
        return Storage::get($savePath);
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
            ->get();

        $out = [];
        foreach ($users as $user) {
            $out[] = [
                'id' => $user['id'],
                'nick_name' => $user['nick_name'] ?: $user['tel'],
            ];
        }

        return $out;
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
        $devices = $user->devices->where('active', 1);

        $currentApiToken = $request->header('Authorization');

        $currentDevice = null;
        $otherDevices = [];
        foreach ($devices as &$device)
        {
            $apiToken = $device['api_token'];

            unset($device['api_token']);
            unset($device['active']);

            if($apiToken == $currentApiToken) {
                $currentDevice = $device;
            } else {
                $otherDevices = $device;
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

        if($device['api_token'] == $api_token) {
            throw new BadRequestException('无法注销当前设备', 400);
        }

        Device::where('id', $device_id)
            ->update([
                'active' => 0
            ]);

        return response('', 204);
    }
}

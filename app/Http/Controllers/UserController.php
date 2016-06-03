<?php

namespace App\Http\Controllers;

use App\Contracts\SMSServiceContract;
use App\Exceptions\BadRequestException;
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
            'username' => 'required|exists:users,tel',
        ]);

        $username = $request->input('username');

        $user = User::where('tel', $username)->first();
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

        // 登录成功后, 验证码立即失效
        User::where('tel', $username)
            ->update(['verify_code_expire_at' => null]);

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

        $follower = Follower::where('follower_id', $f_user_id)
            ->where('followee_id', $user_id)
            ->get();
        if(!$follower->isEmpty()) {
            // 被关注者已关注专注者时, 获取 group_id
            $group = $follower->group;
            $group_id = $group['id'];
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
}

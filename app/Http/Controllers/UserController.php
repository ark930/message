<?php

namespace App\Http\Controllers;

use App\Exceptions\BadRequestException;
use App\Models\Follower;
use App\Models\User;
use Illuminate\Http\Request;

use App\Http\Requests;

class UserController extends BaseController
{
    /**
     * 登录
     *
     * @param Request $request
     * @return mixed
     */
    public function login(Request $request)
    {
        $this->validateParams($request->all(), [
            'username' => 'required|exists:users,tel',
        ]);

        $username = $request->input('username');
        return User::where('tel', $username)->first();
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
        User::create(['tel' => $username, 'api_token' => str_random(60)]);

        return response()->json(['msg' => 'success']);
    }

    /**
     * 关注用户
     *
     * @param Request $request
     * @param $f_user_id
     * @return \App\Follower
     * @throws BadRequestException
     */
    public function follow(Request $request, $f_user_id)
    {
        $user_id = $this->user_id();

        $this->validateParams(compact('f_user_id'), [
            'f_user_id' => 'required|numeric|exists:users,id',
        ]);

        if($user_id == $f_user_id) {
            throw new BadRequestException('无法关注自己', 400);
        }

        $group_name = "私聊: $user_id, $f_user_id";
        $conversation = app('IM')->createConversation($group_name, [$user_id, $f_user_id]);

        $follower = new Follower();
        $follower->follower_id = intval($user_id);
        $follower->followee_id = intval($f_user_id);
        $f_user_id->conv_id = $conversation['objectId'];
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
            throw new BadRequestException('无法关注自己', 400);
        }

        Follower::where('follower_id', $user_id)
            ->where('followee_id', $f_user_id)->delete();

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

        return Follower::where('follower_id', $user_id)->get();
    }
}

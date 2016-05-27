<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use Auth;

class UserController extends BaseController
{
    /**
     * @param Request $request
     * @return mixed
     */
    public function login(Request $request)
    {
//        try {
//            $this->validate($request, [
//                'username' => 'required|exists:users,tel',
//            ], [
//                'exists' => 'The attribute field is required.',
//            ]);
//        } catch (ValidationException $e) {
//            return response()->json($e->getMessage(), 400);
//        }

        $validator = $this->validateParams($request->all(), [
            'username' => 'required|exists:users,tel',
        ]);

        if ($validator->fails()) {
            return response()->json(['msg' => $validator->errors()->first()], 400);
        }

        $username = $request->input('username');
        return \App\User::where('tel', $username)->first();
    }

    public function register(Request $request)
    {
        $validator = $this->validateParams($request->all(), [
            'username' => 'required|unique:users,tel',
        ]);

        if ($validator->fails()) {
            return response()->json(['msg' => $validator->errors()->first()], 400);
        }

        $username = $request->input('username');
        \App\User::create(['tel' => $username, 'api_token' => str_random(60)]);

        return response()->json(['msg' => 'success'], 400);
    }
    
    public function follow(Request $request, $f_user_id)
    {
        $user = Auth::guard('api')->user();
        $user_id = $user['id'];

        $validator = $this->validateParams(['f_user_id' => $f_user_id], [
            'f_user_id' => 'required|numeric|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['msg' => $validator->errors()->first()], 400);
        }

        if($user_id == $f_user_id) {
            return response()->json(['msg' => '无法关注自己'], 400);
        }

        $follower = new \App\Follower();
        $follower->follower_id = intval($user_id);
        $follower->followee_id = intval($f_user_id);
        $follower->save();

        return $follower;
    }
    
    public function unfollow(Request $request, $f_user_id)
    {
        $user = Auth::guard('api')->user();
        $user_id = $user['id'];

        $validator = $this->validateParams(['f_user_id' => $f_user_id], [
            'f_user_id' => 'required|numeric|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['msg' => $validator->errors()->first()], 400);
        }

        if($user_id == $f_user_id) {
            return response()->json(['msg' => '无法关注自己'], 400);
        }

        \App\Follower::where('follower_id', $user_id)
            ->where('followee_id', $f_user_id)->delete();

        return \App\Follower::where('follower_id', $user['id'])->get();
    }
    
    public function getFollow()
    {
        $user = Auth::guard('api')->user();

        return \App\Follower::where('follower_id', $user['id'])->get();
    }
}

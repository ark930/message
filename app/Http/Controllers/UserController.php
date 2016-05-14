<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Facades\Response;

class UserController extends Controller
{
    public function login(Request $request)
    {
        $username = $request->input('username');

        return \App\User::where('tel', $username)->first();
    }

    public function register(Request $request)
    {
        $username = $request->input('username');

        return \App\User::create(['tel' => $username]);
    }
    
    public function follow($user_id, $f_user_id)
    {
        $follower = new \App\Follower();
        $follower->follower_id = $user_id;
        $follower->followee_id = $f_user_id;
        $follower->save();
        return $follower;
//        return \App\Follower::create([
//            'follower_id'   => $user_id,
//            'followee_id'   => $f_user_id,
//        ]);
    }
    
    public function unfollow($user_id, $f_user_id)
    {
        return \App\Follower::where('follower_id', $user_id)
            ->where('followee_id', $f_user_id)->delete();
    }
    
    public function getFollow($user_id)
    {
        return \App\Follower::where('follower_id', $user_id)->get();
    }
}

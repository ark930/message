<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

class GroupController extends Controller
{
    public function createGroup(Request $request, $user_id)
    {
        $group_name = $request->input('name');

//        $client = new \GuzzleHttp\Client();
//        $res = $client->get('https://api.github.com/user', ['auth' =>  ['wangxiao_8800@qq.com', '722131wxw']]);
//        echo $res->getStatusCode(); // 200
//        echo $res->getBody();

        $group = new \App\Group();
        $group->name = $group_name;
        $group->save();

        $user = \App\User::find($user_id);
        $user->groups()->attach($group->id, ['privilege'=>'none']);

        return $user;
    }

    public function show($user_id)
    {
        $user = \App\User::find($user_id);
        $groups = $user->groups;

        return $groups;
    }

    public function create(Request $request, $user_id)
    {
        $group_name = $request->input('name');

        $group = new \App\Group();
        $group->name = $group_name;
        $group->save();
        $group->user_groups()->save(new \App\UserGroup());

        return \App\Group::find($group->id);
//        $group = new \App\Group();
//        $group->createGroup($group_name);
    }

    public function join($user_id, $group_id)
    {
        $user = \App\User::find($user_id);
        $groups = $user->groups()->attach($group_id);

        return $groups;
    }

    public function quit($user_id, $group_id)
    {
        $user = \App\User::find($user_id);
        $user->groups()->detach($group_id);

        return 'success';
    }

    public function invite()
    {

    }

    public function dismiss($user_id, $group_id)
    {
        \App\Group::find($group_id)->delete();
        
        return [
            $user_id, $group_id
        ];
    }
}

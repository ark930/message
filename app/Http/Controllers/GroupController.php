<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use Auth;

class GroupController extends BaseController
{
    /**
     * 显示用户组
     *
     * @return mixed
     */
    public function show()
    {
        $user = Auth::guard('api')->user();
        $user_id = $user['id'];

        $user = \App\User::find($user_id);
        $groups = $user->groups;

        return $groups;
    }

    /**
     * 创建用户组
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createGroup(Request $request)
    {
        $user = Auth::guard('api')->user();
        $user_id = $user['id'];
        
        $validator = $this->validateParams($request->all(), [
            'group_name' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['msg' => $validator->errors()->first()], 400);
        }
        
        $group_name = $request->input('group_name');

//        $client = new \GuzzleHttp\Client();
//        $res = $client->get('https://api.github.com/user', ['auth' =>  ['wangxiao_8800@qq.com', '722131wxw']]);
//        echo $res->getStatusCode(); // 200
//        echo $res->getBody();

        $group = new \App\Group();
        $group->name = $group_name;
        $group->save();

        $user = \App\User::find($user_id);
        $user->groups()->attach($group->id, ['privilege' => 'none']);

        return $user;
    }

//    public function create(Request $request, $user_id)
//    {
//        $group_name = $request->input('name');
//
//        $group = new \App\Group();
//        $group->name = $group_name;
//        $group->save();
//        $group->user_groups()->save(new \App\UserGroup());
//
//        return \App\Group::find($group->id);
////        $group = new \App\Group();
////        $group->createGroup($group_name);
//    }

    public function join($group_id)
    {
        $user = Auth::guard('api')->user();
        $user_id = $user['id'];

        $user = \App\User::find($user_id);
        $groups = $user->groups()->attach($group_id);

        return $groups;
    }

    public function quit($group_id)
    {
        $user = Auth::guard('api')->user();
        $user_id = $user['id'];

        $user = \App\User::find($user_id);
        $user->groups()->detach($group_id);

        return 'success';
    }

    public function invite()
    {
        $user = Auth::guard('api')->user();
        $user_id = $user['id'];
    }

    public function dismiss($group_id)
    {
        $user = Auth::guard('api')->user();
        $user_id = $user['id'];

        \App\Group::find($group_id)->delete();
        
        return [
            $user_id, $group_id
        ];
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

class GroupController extends BaseController
{
    /**
     * 显示用户组
     *
     * @return mixed
     */
    public function show()
    {
        $user_id = $this->user_id();

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
        $user_id = $this->user_id();

        $this->validateParams($request->all(), [
            'group_name' => 'required',
        ]);

        $group_name = $request->input('group_name');

        $conversation = app('IM')->createConversation($group_name, [$user_id]);

        $group = new \App\Group();
        $group->name = $group_name;
        $group->conv_id = $conversation['objectId'];
        $group->save();

        $user = \App\User::find($user_id);
        $user->groups()->attach($group->id, ['privilege' => 'none']);

        return $user;
    }

    /**
     * 加入用户组
     *
     * @param $group_id
     * @return mixed
     */
    public function join($group_id)
    {
        $user_id = $this->user_id();

        $user = \App\User::find($user_id);
        $groups = $user->groups()->attach($group_id);

        $group = \App\Group::find($group_id);
        $conversationId = $group['conv_id'];
        app('IM')->addMemberToConversation($conversationId, [$user_id]);

        return $groups;
    }

    /**
     * 退出用户组
     *
     * @param $group_id
     * @return string
     */
    public function quit($group_id)
    {
        $user_id = $this->user_id();

        $user = \App\User::find($user_id);
        $user->groups()->detach($group_id);

        $group = \App\Group::find($group_id);
        $conversationId = $group['conv_id'];
        app('IM')->removeMemberToConversation($conversationId, [$user_id]);

        return 'success';
    }

    public function invite()
    {
        $user_id = $this->user_id();
    }

    /**
     * 解散用户组
     * 
     * @param $group_id
     * @return array
     */
    public function dismiss($group_id)
    {
        $user_id = $this->user_id();

        \App\Group::find($group_id)->delete();
        
        return [
            $user_id, $group_id
        ];
    }
}

<?php

namespace App\Http\Controllers;

use App\Group;
use Illuminate\Http\Request;

use App\Http\Requests;

class MessageController extends BaseController
{
    /**
     * 发送消息
     *
     * @param Request $request
     * @return mixed
     */
    public function sendMessage(Request $request)
    {
        $user = $this->user();
        $user_id = $user['id'];

        $group_id = $request->input('group_id');
        $message = $request->input('message');

        $group = Group::find($group_id);
        $conversationId = $group['conv_id'];
        
        $conversation = app('IM')->sendMessage($user_id, $conversationId, $message);

        return $conversation;
    }

    /**
     * 获取聊天记录
     *
     * @param Request $request
     * @return mixed
     */
    public function getMessage(Request $request)
    {
        $group_id = $request->input('group_id');

        $group = Group::find($group_id);
        $conversationId = $group['conv_id'];

        $conversation = app('IM')->messageRecordByConversation($conversationId);

        return $conversation;
    }
}

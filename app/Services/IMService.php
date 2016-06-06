<?php

namespace App\Services;

class IMService
{
    use HttpClientTrait;
    
    protected $leancloudAppId = null;
    protected $leancloudAppKey = null;
    protected $leancloudAppMasterKey = null;

    protected $client = null;
    const BASE_URL = 'https://api.leancloud.cn/1.1/';

    public function __construct()
    {
        $this->leancloudAppId = config('leancloud.app_id');
        $this->leancloudAppKey = config('leancloud.app_key');
        $this->leancloudAppMasterKey = config('leancloud.app_master_key');

        $this->initHttpClient(self::BASE_URL, [
            'X-LC-Id' => $this->leancloudAppId,
            'X-LC-Key' => $this->leancloudAppMasterKey . ',master',
        ]);
    }

    /**
     * 创建对话
     *
     * @param $conversationName
     * @param $members
     * @return mixed
     */
    public function createConversation($conversationName, $members)
    {
        $body = $this->requestJson('POST', 'classes/_Conversation', [
            'name' => $conversationName,
            'm' => $members,
        ]);

        return $body;
    }

    /**
     * 添加对话成员
     *
     * @param $conversationId
     * @param $member
     * @return mixed
     */
    public function addMemberToConversation($conversationId, $member)
    {
        $body = $this->requestJson('PUT', 'classes/_Conversation/' . $conversationId, [
            'm' => [
                '__op' => 'AddUnique',
                'objects' => $member,
            ],
        ]);

        return $body;
    }

    /**
     * 删除对话成员
     *
     * @param $conversationId
     * @param $member
     * @return mixed
     */
    public function removeMemberToConversation($conversationId, $member)
    {
        $body = $this->requestJson('PUT', 'classes/_Conversation/' . $conversationId, [
            'm' => [
                '__op' => 'Remove',
                'objects' => $member,
            ],
        ]);

        return $body;
    }

    /**
     * 获取对话的聊天记录
     *
     * @param $conversationId
     * @return mixed
     */
    public function messageRecordByConversation($conversationId)
    {
        $body = $this->requestJson('GET', 'rtm/messages/logs?convid=' . $conversationId);

        return $body;
    }

    /**
     * 发送消息
     *
     * @param $from
     * @param $conversationId
     * @param $message
     * @return mixed
     */
    public function sendMessage($from, $conversationId, $message)
    {
        $body = $this->requestJson('POST', 'rtm/messages', [
            'from_peer' => strval($from),
            'message' => strval($message),
            'conv_id' => strval($conversationId),
            'transient' => false,
        ]);

        return $body;
    }

}
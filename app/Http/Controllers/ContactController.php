<?php

namespace App\Http\Controllers;

use App\Exceptions\BadRequestException;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Http\Request;

use App\Http\Requests;

class ContactController extends BaseController
{
    /**
     * 关注用户
     *
     * @deprecated 
     * @param Request $request
     * @param $f_user_id
     * @return Contact
     * @throws BadRequestException
     */
    public function followDeprecated(Request $request, $f_user_id)
    {
        $user_id = $this->user_id();

        $this->validateParams(compact('f_user_id'), [
            'f_user_id' => 'required|numeric|exists:users,id',
        ]);

        // 关注者与被关注则相同时
        if($user_id == $f_user_id) {
            throw new BadRequestException('无法对自己进行该操作', 400);
        }

        $contact = Contact::where('user_id', $user_id)
            ->where('contact_user_id', $f_user_id)
            ->get();
        if(!$contact->isEmpty()) {
            if($contact['relation'] == 'follow' || $contact['relation'] == 'star') {
                // 已关注被关注者时
                throw new BadRequestException('已关注', 400);
            }
            $contact['relation'] = 'follow';
            $contact->save();
        } else {
            //        $contact = Contact::where('user_id', $user_id)
//            ->where('contact_user_id', $f_user_id)
//            ->withTrashed()
//            ->get();

//        if(!$contact->isEmpty()) {
//            Contact::where('user_id', $user_id)
//                ->where('contact_user_id', $f_user_id)
//                ->withTrashed()
//                ->restore();
//
//            return Contact::where('user_id', $user_id)
//                ->where('contact_user_id', $f_user_id)
//                ->get();
//        }

            $contact = Contact::where('user_id', $f_user_id)
                ->where('contact_user_id', $user_id)
                ->first();

            if(!empty($contact)) {
                // 被关注者与用户有关联时, 获取 group_id
                $conv_id = $contact['conv_id'];
//            $group = $contact->group;
//            $group_id = $group['id'];
            } else {
                // 双方互不关注时, 创建对话
                $group_name = "私聊: $user_id, $f_user_id";
                $conversation = app('IM')->createConversation($group_name, [$user_id, $f_user_id]);
                $conv_id = $conversation['objectId'];

//            $group = Group::create([
//                'name' => $group_name,
//                'type' => 'private',
//                'conv_id' => $conv_id,
//            ]);
//            $group_id = $group['id'];
//
//            UserGroup::create(['user_id' => $user_id, 'group_id' => $group_id]);
//            UserGroup::create(['user_id' => $f_user_id, 'group_id' => $group_id]);
            }

            $contact = $this->createContact($user_id, $f_user_id, 'follow', $conv_id);

            unset($contact['id']);
        }

        return $contact;
    }

    /**
     * 取消关注
     *
     * @deprecated 
     * @param Request $request
     * @param $f_user_id
     * @return mixed
     * @throws BadRequestException
     */
    public function unfollowDeprecated(Request $request, $f_user_id)
    {
        $user_id = $this->user_id();

        $this->validateParams(compact('f_user_id'), [
            'f_user_id' => 'required|numeric|exists:users,id',
        ]);

        if($user_id == $f_user_id) {
            throw new BadRequestException('无法对自己进行该操作', 400);
        }

        Contact::where('user_id', $user_id)
            ->where('contact_user_id', $f_user_id)
            ->update([
                'relation' => 'stronger',
            ]);

        return Contact::where('user_id', $user_id)
            ->where('relation', 'follow')
            ->orWhere('relation', 'star')
            ->get();
    }

    /**
     * 关注用户
     *
     * @param Request $request
     * @param $f_user_id
     * @return Contact
     * @throws BadRequestException
     */
    public function follow(Request $request, $f_user_id)
    {
        $user_id = $this->user_id();

        $this->basicValidate($user_id, $f_user_id);

        $contact = Contact::where('user_id', $user_id)
            ->where('contact_user_id', $f_user_id)
            ->first();
        if(!empty($contact)) {
            if($contact['relation'] != 'follow' || $contact['relation'] != 'star') {
                $contact['relation'] = 'follow';
                $contact->save();
            }
        } else {
            $contact = Contact::where('user_id', $f_user_id)
                ->where('contact_user_id', $user_id)
                ->first();

            if(!empty($contact)) {
                // 被关注者与用户有关联时, 获取 conv_id
                $conv_id = $contact['conv_id'];
            } else {
                // 双方互不关注时, 创建对话
                $group_name = "私聊: $user_id, $f_user_id";
                $conversation = app('IM')->createConversation($group_name, [$user_id, $f_user_id]);
                $conv_id = $conversation['objectId'];
            }

            $contact = $this->createContact($user_id, $f_user_id, 'follow', $conv_id);

            unset($contact['id']);
        }

        return $contact;
    }

    /**
     * 取消关注
     * 
     * @param Request $request
     * @param $f_user_id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws BadRequestException
     */
    public function unfollow(Request $request, $f_user_id)
    {
        $user_id = $this->user_id();

        $this->basicValidate($user_id, $f_user_id);

        $contact = Contact::where('user_id', $user_id)
            ->where('contact_user_id', $f_user_id)
            ->first();
        if(!empty($contact)) {
            if ($contact['relation'] == 'follow' || $contact['relation'] == 'star') {
                $contact['relation'] = 'stranger';
                $contact->save();
            }
            return response('', 204);
        }
        
        throw new BadRequestException('未关注该用户, 无法进行该操作', 400);
    }
    
    /**
     * 加星
     *
     * @param Request $request
     * @param $f_user_id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws BadRequestException
     */
    public function star(Request $request, $f_user_id)
    {
        $user_id = $this->user_id();

        $this->basicValidate($user_id, $f_user_id);

        $contact = Contact::where('user_id', $user_id)
            ->where('contact_user_id', $f_user_id)
            ->first();

        if(empty($contact)) {
            throw new BadRequestException('未关注该用户, 无法标星', 400);
        } else {
            if ($contact['relation'] == 'follow') {
                $contact['relation'] = 'star';
                $contact->save();
            } else if ($contact['relation'] == 'star') {
                throw new BadRequestException('该用户已标星', 400);
            } else {
                throw new BadRequestException('未关注该用户, 无法标星', 400);
            }
        }

        return response('', 204);
    }

    /**
     * 取消加星
     *
     * @param Request $request
     * @param $f_user_id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws BadRequestException
     */
    public function unstar(Request $request, $f_user_id)
    {
        $user_id = $this->user_id();

        $this->basicValidate($user_id, $f_user_id);

        $contact = Contact::where('user_id', $user_id)
            ->where('contact_user_id', $f_user_id)
            ->where('relation', 'star')
            ->first();
        if(empty($contact)) {
            throw new BadRequestException('未对注该用户标星, 无法操作', 400);
        } else {
            $contact['relation'] = 'follow';
            $contact->save();
        }

        return response('', 204);
    }

    /**
     * 屏蔽
     *
     * @param Request $request
     * @param $f_user_id
     * @return Contact|\Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws BadRequestException
     */
    public function block(Request $request, $f_user_id)
    {
        $user_id = $this->user_id();

        $this->basicValidate($user_id, $f_user_id);

        $contact = Contact::where('user_id', $user_id)
            ->where('contact_user_id', $f_user_id)
            ->first();
        if(!empty($contact)) {
            if($contact['relation'] == 'block') {
                // 该用户已被屏蔽时, 不做任何操作
            } else {
                $contact['relation'] = 'block';
                $contact->save();
            }

            return response('', 204);
        } else {
            $contact = Contact::where('user_id', $f_user_id)
                ->where('contact_user_id', $user_id)
                ->first();

            if(!empty($contact)) {
                // 联系人与用户有关联时, 获取 conv_id
                $conv_id = $contact['conv_id'];
            } else {
                // 双方互不关联时, 创建对话
                $group_name = "私聊: $user_id, $f_user_id";
                $conversation = app('IM')->createConversation($group_name, [$user_id, $f_user_id]);
                $conv_id = $conversation['objectId'];
            }

            $contact = $this->createContact($user_id, $f_user_id, 'block', $conv_id);

            unset($contact['id']);
        }

        return $contact;
    }

    /**
     * 取消屏蔽
     *
     * @param Request $request
     * @param $f_user_id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws BadRequestException
     */
    public function unblock(Request $request, $f_user_id)
    {
        $user_id = $this->user_id();

        $this->basicValidate($user_id, $f_user_id);

        $contact = Contact::where('user_id', $user_id)
            ->where('contact_user_id', $f_user_id)
            ->where('relation', 'block')
            ->first();
        if(empty($contact)) {
            throw new BadRequestException('未屏蔽该用户, 无法继续操作', 400);
        } else {
            $contact['relation'] = 'stranger';
            $contact->save();
        }

        return response('', 204);
    }

    /**
     * 编辑联系人显示名称
     * 
     * @param Request $request
     * @param $f_user_id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws BadRequestException
     */
    public function editContactDisplayName(Request $request, $f_user_id)
    {
        $user_id = $this->user_id();

        $this->basicValidate($user_id, $f_user_id);

        $display_name = $request->input('display_name');
        $this->validateParams(compact('f_user_id'), [
            'display_name' => 'required|string',
        ]);

        $contact = Contact::where('user_id', $user_id)
            ->where('contact_user_id', $f_user_id)
            ->first();
        if(empty($contact)) {
            throw new BadRequestException('无法进行该操作', 400);
        } else {
            $contact['display_name'] = $display_name;
            $contact->save();
        }

        return response('', 204);
    }
    
    /**
     * 获取所有联系人
     *
     * @param Request $request
     * @return mixed
     */
    public function getContacts(Request $request)
    {
        $user_id = $this->user_id();

        $contacts = Contact::where('user_id', $user_id)->get();

        $res = [
            'star' => [],
            'follow' => [],
            'stranger' => [],
            'block' => []
        ];
        foreach ($contacts as $contact) {
            $relation = $contact['relation'];
            $user = $contact->contact;
            $display_name = $contact->getDisplayName();
            $tel = null;

            if($contact['contact_tel_visible']) {
                $tel = $user['tel'];
            }

            $res[$relation][] = [
                'use_id' => $contact['contact_user_id'],
                'display_name' => $display_name,
                'tel' => $tel,
                'avatar_path' => $user['avatar_url'],
                'type' => $user['type'],
                'conv_id' => $contact['conv_id'],
                'last_login_at' => $user['last_login_at'],
            ];
        }

        return $res;
    }

    /**
     * 获取联系人
     *
     * @param Request $request
     * @param $f_user_id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws BadRequestException
     */
    public function getContact(Request $request, $f_user_id)
    {
        $user_id = $this->user_id();

        $this->basicValidate($user_id, $f_user_id);

        $contact = Contact::where('user_id', $user_id)
            ->where('contact_user_id', $f_user_id)
            ->first();

        $user = $contact->contact;
        $display_name = $contact->getDisplayName();
        $tel = null;

        if($contact['contact_tel_visible']) {
            $tel = $user['tel'];
        }

        return [
            'use_id' => $contact['contact_user_id'],
            'display_name' => $display_name,
            'tel' => $tel,
            'avatar_path' => $user['avatar_url'],
            'type' => $user['type'],
            'conv_id' => $contact['conv_id'],
            'last_login_at' => $user['last_login_at'],
        ];
    }

    /**
     * 编辑联系人信息
     *
     * @param Request $request
     * @param $f_user_id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws BadRequestException
     */
    public function editContact(Request $request, $f_user_id)
    {
        $user_id = $this->user_id();

        $this->basicValidate($user_id, $f_user_id);

        $contact = Contact::where('user_id', $user_id)
            ->where('contact_user_id', $f_user_id)
            ->first();

        $display_name = $request->input('display_name');

        if(!empty($display_name)) {
            $contact['contact_display_name'] = $display_name;
        }
        $contact->save();

        return response('', 204);
    }

    /**
     * 上传本地联系人
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws BadRequestException
     */
    public function batchUploadContacts(Request $request)
    {
        $user_id = $this->user_id();

        $contacts = $request->all();

        foreach ($contacts as $c) {
            if(empty($c['tel'])) {
                throw new BadRequestException('参数错误', 400);
            }
        }

        foreach ($contacts as $c) {
            $tel = $c['tel'];
            $display_name = !empty($c['display_name']) ? $c['display_name'] : null;

            $user = User::where('tel', $tel)->first();
            if(empty($user)) {
                $user = User::create(['tel' => $tel]);
            }
            $contact_user_id = $user['id'];

            $contact = Contact::where('user_id', $user_id)
                ->where('contact_user_id', $contact_user_id)
                ->first();

            if(empty($contact)) {
                $group_name = "私聊: $user_id, $contact_user_id";
                $conversation = app('IM')->createConversation($group_name, [$user_id, $contact_user_id]);
                $conv_id = $conversation['objectId'];

                $contact = new Contact();
                $contact['user_id'] = $user_id;
                $contact['contact_user_id'] = $contact_user_id;
                $contact['contact_display_name'] = !empty($display_name) ? $display_name : null;
                $contact['contact_tel_visible'] = true;
                $contact['relation'] = 'follow';
                $contact['conv_id'] = $conv_id;
                $contact->save();
            }
        }

        return response('', 204);
    }

    /**
     * 获取对话列表
     *
     * @param Request $request
     * @return array
     */
    public function conversationList(Request $request)
    {
        $user_id = $this->user_id();

        $contacts = Contact::where('user_id', $user_id)
            ->where('conv_type', '!=', 'none')
            ->get();

        $res = [
            'normal' => [],
            'sticky' => [],
            'archive' => [],
        ];
        foreach ($contacts as $contact) {
            $convType = $contact['conv_type'];
            $user = $contact->contact;
            $display_name = $contact->getDisplayName();
            $tel = null;

            if($contact['contact_tel_visible']) {
                $tel = $user['tel'];
            }

            $res[$convType][] = [
                'use_id' => $contact['contact_user_id'],
                'display_name' => $display_name,
                'tel' => $tel,
                'avatar_path' => $user['avatar_url'],
                'type' => $user['type'],
                'conv_id' => $contact['conv_id'],
                'last_login_at' => $user['last_login_at'],
            ];
        }

        return $res;
    }

    /**
     * 设置对话属性
     *
     * @param Request $request
     * @param $f_user_id
     * @param $conv_type
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws BadRequestException
     */
    public function setConversation(Request $request, $f_user_id, $conv_type)
    {
        $user_id = $this->user_id();

        $this->basicValidate($user_id, $f_user_id);

        if(!in_array($conv_type, ['normal', 'sticky', 'archive'])) {
            throw new BadRequestException('操作异常', 400);
        }

        $contact = Contact::where('user_id', $user_id)
            ->where('contact_user_id', $f_user_id)
            ->first();

        if(empty($contact)) {
            throw new BadRequestException('操作异常', 400);
        } else {
            $contact['conv_type'] = $conv_type;
            $contact->save();
        }

        return response('', 204);
    }

    private function basicValidate($user_id, $f_user_id)
    {
        $this->validateParams(compact('f_user_id'), [
            'f_user_id' => 'required|numeric|exists:users,id',
        ]);

        if($user_id == $f_user_id) {
            throw new BadRequestException('无法对自己进行该操作', 400);
        }
    }

    private function createContact($user_id, $f_user_id, $relation, $conv_id)
    {
        $contact = new Contact();
        $contact->user_id = intval($user_id);
        $contact->contact_user_id = intval($f_user_id);
        $contact->relation = $relation;
        $contact->conv_id = $conv_id;
        $contact->save();

        return $contact;
    }
}

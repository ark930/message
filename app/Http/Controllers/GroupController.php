<?php

namespace App\Http\Controllers;

use Storage;
use App\Exceptions\BadRequestException;
use App\Models\Group;
use App\Models\User;
use App\Models\UserGroup;
use Illuminate\Http\Request;

use App\Http\Requests;

class GroupController extends BaseController
{
    /**
     * 获取用户所有的群
     *
     * @return mixed
     */
    public function getGroups()
    {
        $user = $this->user();

        $groups = $user->groups;

        foreach ($groups as &$group) {
            unset($group['pivot']);
            unset($group['deleted_at']);
        }

        return $groups;
    }

    /**
     * 获取用户指定的群
     *
     * @param $group_id
     * @return mixed
     */
    public function getGroup($group_id)
    {
        $user = $this->user();

        $group = $user->groups->where('id', intval($group_id))->first();

        unset($group['pivot']);
        unset($group['deleted_at']);

        return $group;
    }

    /**
     * 创建群
     *
     * 创建人默认是群主
     * 必须选择除了自己外的至少一人
     * 默认群名字是前三个成员名字列表，创建时用户也可以自定义群名字
     * 默认头像是前三个成员的头像集
     * @param Request $request
     * @return Group
     * @throws BadRequestException
     */
    public function create(Request $request)
    {
        $owner_user_id = $this->user_id();

        $display_name = $request->input('display_name');
        $members = $request->input('members');

        $this->validateParams($request->all(), [
            'members' => 'required',
        ]);

        $members = array_unique($members);

        $users = [];
        foreach ($members as $user_id) {
            if($owner_user_id == $user_id) {
                throw new BadRequestException("存在非法的 User ID", 400);
            }
            
            $user = User::find($user_id);
            if(empty($user)) {
                throw new BadRequestException("存在非法的 User ID", 400);
            }

            $users[] = $user;
        }
        $conversation = app('IM')->createConversation($display_name, array_merge([$owner_user_id], $users));

        // 默认群名字是前三个成员名字列表，创建时用户也可以自定义群名字。
        $owner_user = $this->user();
        if(empty($display_name)) {
            $display_name = $owner_user->getDisplayName();
            if(count($users) <= 2) {
                foreach ($users as $user) {
                    $display_name .= ', ' . $user->getDisplayName();
                }
            } else {
                for($i = 0; $i < 2; $i++) {
                    $display_name .= ', ' . $users[$i]->getDisplayName();
                }
            }
        }

        require(dirname(__FILE__) . "/md/MaterialDesign.Avatars.class.php");

        $avatar_word = mb_substr($display_name, 0, 1);
        $avatar = new \Md\MDAvatars($avatar_word);
        $newFileName = sha1(time().rand(0,10000)).'.png';
        $savePath = 'avatar/group/'.$newFileName;
        $tmpPath = '/tmp/'.$newFileName;
        $avatar->Save($tmpPath);
        $avatar->Free();

        $bytes = Storage::put(
            $savePath,
            file_get_contents($tmpPath)
        );

        if(!Storage::exists($savePath)) {
            throw new BadRequestException('保存文件失败', 400);
        }

        unlink($tmpPath);

        $group = new Group();
        $group['display_name'] = $display_name;
        $group['avatar_url'] = $savePath;
        $group['conv_id'] = $conversation['objectId'];
        $group->save();

        $owner_user->groups()->attach($group->id, [
            'role' => 'owner',
        ]);

        foreach ($users as $user) {
            $user->groups()->attach($group->id, [
                'role' => 'member',
            ]);
        }

        return $group;
    }

    /**
     * 加入群
     *
     * @param $group_id
     * @return mixed
     */
    public function join($group_id)
    {
        $user_id = $this->user_id();
        $user = $this->user();

        $user->groups()->attach($group_id);

        $group = Group::find($group_id);
        $conversationId = $group['conv_id'];
        app('IM')->addMemberToConversation($conversationId, [$user_id]);

        return res;
    }

    /**
     * 退出群
     *
     * @param $group_id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws BadRequestException
     */
    public function quit($group_id)
    {
        $user_id = $this->user_id();
        $user = $this->user();

        $userGroup = UserGroup::where('group_id', $group_id)
            ->where('user_id', $user_id)
            ->first();
        if(empty($userGroup)) {
            throw new BadRequestException("操作异常", 400);
        }

        if($this->isGroupOwner($group_id) == true) {
            throw new BadRequestException("群主无法退出", 400);
        }
        
        $group = Group::find($group_id);
        $conversationId = $group['conv_id'];
        app('IM')->removeMemberToConversation($conversationId, [$user_id]);

        $user->groups()->detach($group_id);

        return response('', 204);
    }

    /**
     * @param Request $request
     * @param $group_id
     * @param $user_id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws BadRequestException
     */
    public function invite(Request $request, $group_id, $user_id)
    {
        if($this->isGroupOwner($group_id) == false) {
            throw new BadRequestException("该用户没有权限进行这样的操作", 400);
        }

        $userGroup = UserGroup::where('group_id', $group_id)
            ->where('user_id', $user_id)
            ->first();

        if(!empty($userGroup)) {
            throw new BadRequestException("操作异常", 400);
        }

        $owner_user_id = $this->user_id();
        $user = User::find($user_id);
        $user->groups()->attach($group_id, [
            'role' => 'member',
            'inviter_user_id' => $owner_user_id,
        ]);

        return response('', 204);
    }

    /**
     * 移除成员
     *
     * @param Request $request
     * @param $group_id
     * @param $user_id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws BadRequestException
     */
    public function remove(Request $request, $group_id, $user_id)
    {
        if($this->isGroupOwner($group_id) == false) {
            throw new BadRequestException("该用户没有权限进行这样的操作", 400);
        }

        $owner_user_id = $this->user_id();
        if($owner_user_id == $user_id) {
            throw new BadRequestException("无法对自己进行操作", 400);
        }

        $userGroup = UserGroup::where('group_id', $group_id)
            ->where('user_id', $user_id)
            ->first();

        if(empty($userGroup)) {
            throw new BadRequestException("操作异常", 400);
        }

        UserGroup::where('group_id', $group_id)
            ->where('user_id', $user_id)
            ->delete();

        return response('', 204);
    }

    /**
     * 申请加入群
     *
     * @param Request $request
     * @param $group_id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws BadRequestException
     */
    public function apply(Request $request, $group_id)
    {
        $user_id = $this->user_id();

        $userGroup = UserGroup::where('group_id', $group_id)
            ->where('user_id', $user_id)
            ->first();

        if(!empty($userGroup)) {
            throw new BadRequestException("操作异常", 400);
        }

        $group = Group::find($group_id);
        if(empty($group)) {
            throw new BadRequestException("群 ID 不存在", 400);
        }

        $user = User::find($user_id);
        $user->groups()->attach($group_id, [
            'role' => 'applicant',
        ]);

        return response('', 204);
    }

    /**
     * 处理用户入群申请
     *
     * @param $group_id
     * @param $user_id
     * @param $result
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws BadRequestException
     */
    public function handleApplication($group_id, $user_id, $result)
    {
        if($this->isGroupOwner($group_id) == false) {
            throw new BadRequestException("该用户没有权限进行这样的操作", 400);
        }

        $userGroup = UserGroup::where('group_id', $group_id)
            ->where('user_id', $user_id)
            ->where('role', 'applicant')
            ->first();

        if(empty($userGroup)) {
            throw new BadRequestException("异常操作", 400);
        }

        if($result != 'accept' || $result != 'reject') {
            throw new BadRequestException("异常操作", 400);
        }

        if($result == 'accept') {
            $userGroup['role'] = 'member';
            $userGroup->save();
        }

        return response('', 204);
    }

    /**
     * 用户被邀请入群处理
     *
     * @param $group_id
     * @param $result
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws BadRequestException
     */
    public function handleInvitation($group_id, $result)
    {
        $user_id = $this->user_id();
        $userGroup = UserGroup::where('group_id', $group_id)
            ->where('user_id', $user_id)
            ->where('role', 'invitee')
            ->first();

        if(empty($userGroup)) {
            throw new BadRequestException("异常操作", 400);
        }

        if($result != 'accept' || $result != 'reject') {
            throw new BadRequestException("异常操作", 400);
        }

        if($result == 'accept') {
            $userGroup['role'] = 'member';
            $userGroup->save();
        }

        return response('', 204);
    }

    /**
     * 编辑群信息
     * 
     * @param Request $request
     * @param $group_id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws BadRequestException
     */
    public function editGroup(Request $request, $group_id)
    {
        if($this->isGroupOwner($group_id) == false) {
            throw new BadRequestException("该用户没有权限进行这样的操作", 400);
        }

        $display_name = $request->input('display_name');

        $group = Group::find($group_id);
        if(empty($group)) {
            throw new BadRequestException("该用户没有权限进行这样的操作", 400);
        }

        if(!empty($display_name)) {
            $group['display_name'] = $display_name;
        }

        $group->save();

        return response('', 204);
    }

    /**
     * 编辑群头像
     *
     * @param Request $request
     * @param $group_id
     * @return mixed
     * @throws BadRequestException
     */
    public function editGroupAvatar(Request $request, $group_id)
    {
        if($this->isGroupOwner($group_id) == false) {
            throw new BadRequestException("该用户没有权限进行这样的操作", 400);
        }

        $group = Group::find($group_id);
        if(empty($group)) {
            throw new BadRequestException("该用户没有权限进行这样的操作", 400);
        }

        $old_avatar_url = $group['avatar_url'];

        if(!$request->hasFile('avatar')) {
            throw new BadRequestException('上传文件为空', 400);
        }

        $file = $request->file('avatar');
        if(!$file->isValid()) {
            throw new BadRequestException('文件上传出错', 400);
        }

        $newFileName = sha1(time().rand(0,10000)).'.'.$file->getClientOriginalExtension();
        $savePath = 'avatar/group/'.$newFileName;

        $bytes = Storage::put(
            $savePath,
            file_get_contents($file->getRealPath())
        );

        if(!Storage::exists($savePath)) {
            throw new BadRequestException('保存文件失败', 400);
        }

        $group['avatar_url'] = $savePath;
        $group->save();

        // 删除老文件
        Storage::delete($old_avatar_url);

        return response(Storage::get($savePath))
            ->header('Content-Type', Storage::mimeType($savePath));
    }

    /**
     * 解散群
     *
     * @param $group_id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws BadRequestException
     */
    public function dismiss($group_id)
    {
        if($this->isGroupOwner($group_id) == false) {
            throw new BadRequestException("该用户没有权限进行这样的操作", 400);
        }

        $group = Group::find($group_id);

        if(empty($group)) {
            throw new BadRequestException("该用户没有权限进行这样的操作", 400);
        }
        $group->delete();
        
        return response('', 204);
    }

    /**
     * 判断是否是群主
     *
     * @param $group_id
     * @return bool
     */
    private function isGroupOwner($group_id)
    {
        $user_id = $this->user_id();

        $userGroup = UserGroup::where('user_id', $user_id)
            ->where('group_id', $group_id)
            ->where('role', 'owner')
            ->first();

        if(empty($userGroup)) {
            return false;
        }

        return true;
    }
}

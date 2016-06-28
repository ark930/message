### 创建群
接口调用者默认为群主, 此外必须至少选择除了自己外的一人作为群成员
#### 请求
> POST /api/v1/group

#### 请求参数说明
| 参数 | 必要 | 类型 | 说明 |
| ----- | ----- | ----- | ----- |
| members | 是 | array | 成员 User ID, 至少有一个  |
| group_name | 否 | string | 群名称。 如果这个参数为空, 默认群名字是前三个成员名字列表 |
----

### 退出群
当前用户退出指定的群
#### 请求
> POST /api/v1/group/{group_id}/quit

#### 请求参数说明
| 参数 | 必要 | 类型 | 说明 |
| ----- | ----- | ----- | ----- |
| group_id | 是 | id | Group ID |
----

### 解散群
只有群主有权限进行这个操作
#### 请求
> POST /api/v1/group/{group_id}/dismiss

#### 请求参数说明
| 参数 | 必要 | 类型 | 说明 |
| ----- | ----- | ----- | ----- |
| group_id | 是 | id | Group ID |
----

### 编辑群信息
只有群主有权限进行这个操作
#### 请求
> POST /api/v1/group/{group_id}

#### 请求参数说明
| 参数 | 必要 | 类型 | 说明 |
| ----- | ----- | ----- | ----- |
| group_id | 是 | id | Group ID  |
| display_name | 否 | string | 群名称 |
----

### 编辑群头像
只有群主有权限进行这个操作
#### 请求
> POST /api/v1/group/{group_id}/avatar
Content-Type: multipart/form-data

#### 请求参数说明
| 参数 | 必要 | 类型 | 说明 |
| ----- | ----- | ----- | ----- |
| group_id | 是 | id | Group ID |
| avatar | 是 | file | 群头像图片 |
----

### 移除群成员
只有群主有权限进行这个操作
#### 请求
> POST /api/v1/group/{group_id}/user/{user_id}/remove

#### 请求参数说明
| 参数 | 必要 | 类型 | 说明 |
| ----- | ----- | ----- | ----- |
| group_id | 是 | id | Group ID |
| user_id | 是 | id | 被移出群的 User ID |
----

### 申请加入群
当前用户申请加入指定的群
#### 请求
> POST /api/v1/group/{group_id}/apply

#### 请求参数说明
| 参数 | 必要 | 类型 | 说明 |
| ----- | ----- | ----- | ----- |
| group_id | 是 | id | Group ID |
----

### 处理用户入群申请
只有群主有权限进行这个操作
#### 请求
> POST /api/v1/group/{group_id}/user/{user_id}/apply/{result}

#### 请求参数说明
| 参数 | 必要 | 类型 | 说明 |
| ----- | ----- | ----- | ----- |
| group_id | 是 | id | Group ID |
| user_id | 是 | id | 申请者的 User ID |
| result | 是 | string | 申请结果, 必须是 accept 或者 reject |
----

### 获取用户所有的群
#### 请求
> GET /api/v1/group

#### 请求参数说明
无
----
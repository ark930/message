<?php

namespace App\Http\Controllers;

use Auth;
use Validator;

class BaseController extends Controller
{
    protected function validateParams($params, $rules)
    {
        $messages = [
            'exists' => ':attribute 不存在.',
            'unique' => ':attribute 已存在.',
        ];

        return Validator::make($params, $rules, $messages);
    }

    protected function user()
    {
        return Auth::guard('api')->user();
    }

    protected function user_id()
    {
        $user = $this->user();

        return $user['user_id'];
    }
}
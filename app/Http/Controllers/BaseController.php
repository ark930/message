<?php

namespace App\Http\Controllers;

use App\Exceptions\BadRequestException;
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

        $validator = Validator::make($params, $rules, $messages);

        if ($validator->fails()) {
            throw new BadRequestException($validator->errors()->first(), 400);
        }
    }

    protected function user()
    {
        return Auth::guard('api')->user();
    }

    protected function user_id()
    {
        return Auth::guard('api')->id();
    }
}
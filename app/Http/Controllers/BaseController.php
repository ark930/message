<?php

namespace App\Http\Controllers;

use Validator;
use Auth;

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
}
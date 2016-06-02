<?php

namespace App\Contracts;


interface SMSServiceContract
{
    public function SendSMS($tel, $message);
}
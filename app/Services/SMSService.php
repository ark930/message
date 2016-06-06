<?php

namespace App\Services;


use App\Contracts\SMSServiceContract;
use App\Exceptions\BadRequestException;
use Exception;
use GuzzleHttp\Exception\RequestException;

class SMSService implements SMSServiceContract
{
    use HttpClientTrait;

    protected $apiKey = null;
    protected $client = null;

    const BASE_URL = 'https://sms.yunpian.com/v2/';

    public function __construct()
    {
        $this->apiKey = config('yunpian.api_key');

        $this->initHttpClient(self::BASE_URL);
    }

    public function SendSMS($tel, $message)
    {
        $body = $this->requestForm('POST', 'sms/single_send.json', [
            'apikey' => $this->apiKey,
            'mobile' => $tel,
            'text' => $message,
        ]);

        return $body;
    }

    protected function exceptionHandler(Exception $e)
    {
        if($e instanceof RequestException) {
            $code = $e->getResponse()->getStatusCode();
            $body = $e->getResponse()->getBody();
            $message = \GuzzleHttp\json_decode($body, true)['msg'];

            throw new BadRequestException($message, $code);
        }

        throw new BadRequestException($e->getMessage(), $e->getCode());
    }

}

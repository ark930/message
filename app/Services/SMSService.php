<?php

namespace App\Services;


use App\Contracts\SMSServiceContract;
use App\Exceptions\BadRequestException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class SMSService implements SMSServiceContract
{
    protected $apiKey = null;
    protected $client = null;

    const BASE_URL = 'https://sms.yunpian.com/v2/';

    public function __construct()
    {
        $this->apiKey = config('yunpian.api_key');

        $this->client = new Client(['base_uri' => self::BASE_URL]);
    }

    public function SendSMS($tel, $message)
    {
        $body = $this->request('POST', 'sms/single_send.json', [
            'apikey' => $this->apiKey,
            'mobile' => $tel,
            'text' => $message,
        ]);

        return $body;
    }

    private function request($method, $url, $data = null)
    {
        if(empty($data)) {
            $options = [];
        } else {
            $options = [
                'form_params' => $data,
            ];
        }

        try {
            $res = $this->client->request($method, $url, $options);
        } catch (RequestException $e) {
            $code = $e->getResponse()->getStatusCode();
            $body = $e->getResponse()->getBody();
            $message = \GuzzleHttp\json_decode($body, true)['msg'];

            throw new BadRequestException($message, $code);
        } catch (\Exception $e) {
            throw new BadRequestException($e->getMessage(), $e->getCode());
        }

        $body =  $res->getBody();

        return \GuzzleHttp\json_decode($body, true);
    }

}

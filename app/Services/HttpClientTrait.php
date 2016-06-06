<?php

namespace App\Services;

use App\Exceptions\BadRequestException;
use GuzzleHttp\Client;
use Exception;

trait HttpClientTrait
{
    protected $httpClient = null;

    protected function initHttpClient($baseUrl, array $headers = [])
    {
        $this->httpClient = new Client([
            'base_uri' => $baseUrl,
            'timeout' => 5.0,
            'headers' => $headers,
        ]);
    }

    protected function requestJson($method, $url, $data = null)
    {
        return $this->request($method, $url, $data, true);
    }

    protected function requestForm($method, $url, $data = null)
    {
        return $this->request($method, $url, $data, false);
    }

    protected function request($method, $url, $data = null, $is_json = true)
    {
        if(empty($data)) {
            $options = [];
        } else {
            $options = $is_json ? ['json' => $data] : ['form_params' => $data];
        }

        try {
            $res = $this->httpClient->request($method, $url, $options);
        } catch (Exception $e) {
            $this->exceptionHandler($e);
        }

        $body =  $res->getBody();

        return \GuzzleHttp\json_decode($body, true);
    }

    protected function exceptionHandler(Exception $e)
    {
        throw new BadRequestException($e->getMessage(), $e->getCode());
    }
}

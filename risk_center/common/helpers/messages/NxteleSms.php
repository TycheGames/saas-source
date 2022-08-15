<?php


namespace common\helpers\messages;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

class NxteleSms extends BaseSms
{
    public $_batchMax = 500;

    public function getRequestReturnCollect()
    {
        // TODO: Implement getRequestReturnCollect() method.
    }

    public function sendSMS(array $mobileArr, $message)
    {
        $mobileArrTmp = [];
        foreach ($mobileArr as $mobile) {
            $mobileTmp = strlen($mobile) == 10 ? '91' . $mobile : $mobile;
            array_push($mobileArrTmp, $mobileTmp);
        }
        $mobilesStr = implode(',' ,$mobileArrTmp);

        $res = $this->postData($this->_baseUrl, [
            'appkey'         => $this->_userName,
            'secretkey'      => $this->_password,
            'phone'          => $mobilesStr,
            'content'        => urlencode($message),
            'source_address' => $this->_extArr['from'],
        ]);
        $result = $res['response']->getBody()->getContents();

        return $result;
    }

    public function balance()
    {
        $res = $this->postData($this->_baseUrl, [
            'appkey'         => $this->_userName,
            'secretkey'      => $this->_password,
        ]);
        $result = $res['response']->getBody()->getContents();

        return $result;
    }

    public function acceptReport()
    {
        // TODO: Implement acceptReport() method.
    }

    public function collectReport()
    {
        // TODO: Implement collectReport() method.
    }

    private function postData(string $uri, array $params = [])
    {
        $client = new Client([
                'base_uri'              => $uri,
                RequestOptions::TIMEOUT => 60,
            ]
        );
        $body[RequestOptions::HTTP_ERRORS] = false; //禁止http_errors 4xx 和 5xx
        $body[RequestOptions::FORM_PARAMS] = $params;
        $response = $client->request('POST', '', $body);

        $result = [
            'request'  => [
                'body' => $body,
            ],
            'response' => $response,
        ];

        return $result;
    }
}
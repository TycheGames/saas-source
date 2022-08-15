<?php


namespace common\helpers\messages;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

class PaasooSms extends BaseSms
{
    public $_batchMax = 1;

    public function getRequestReturnCollect()
    {
        // TODO: Implement getRequestReturnCollect() method.
    }

    public function sendSMS(array $mobileArr, $message)
    {
        $mobilesStr = strlen($mobileArr[0]) == 10 ? '91' . $mobileArr[0] : $mobileArr[0];
        $res = $this->getData($this->_baseUrl, [
            'key'    => $this->_userName,
            'secret' => $this->_password,
            'to'     => $mobilesStr,
            'text'   => $message, //不要urlencode,get方式底层自动转码
            'from'   => $this->_extArr['from'],
        ]);
        $result = $res['response']->getBody()->getContents();

        return $result;
    }

    public function balance()
    {
        $res = $this->getData($this->_baseUrl, [
            'key'    => $this->_userName,
            'secret' => $this->_password,
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

    private function getData(string $uri, array $params = [])
    {
        $client = new Client([
                'base_uri'              => $uri,
                RequestOptions::TIMEOUT => 60,
            ]
        );
        $body[RequestOptions::HTTP_ERRORS] = false; //禁止http_errors 4xx 和 5xx
        $body[RequestOptions::QUERY] = $params;
        $response = $client->request('GET', '', $body);

        $result = [
            'request'  => [
                'body' => $body,
            ],
            'response' => $response,
        ];

        return $result;
    }
}
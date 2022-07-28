<?php


namespace common\helpers\messages;


use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

class ChuangLanSms extends BaseSms
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

        $res = $this->postData('', [
            'account'  => $this->_userName,
            'password' => $this->_password,
            'msg'      => $message,
            'mobile'   => $mobilesStr,
            'senderId' => $this->_extArr['from'],
        ]);
        $result = $res['response']->getBody()->getContents();

        return $result;
    }

    public function balance()
    {
        $res = $this->postData('', [
            'account'  => $this->_userName,
            'password' => $this->_password,
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

    private function postData(string $uri, array $params)
    {
        $client = new Client([
                'base_uri'              => $this->_baseUrl,
                RequestOptions::TIMEOUT => 60,
            ]
        );
        $body[RequestOptions::HTTP_ERRORS] = false; //禁止http_errors 4xx 和 5xx
        $body[RequestOptions::JSON] = $params;
        $response = $client->request('POST', $uri, $body);

        $result = [
            'request'  => [
                'body' => $body,
            ],
            'response' => $response,
        ];

        return $result;
    }
}
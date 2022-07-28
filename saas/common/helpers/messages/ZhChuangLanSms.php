<?php


namespace common\helpers\messages;


use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

class ZhChuangLanSms extends BaseSms
{
    public $_batchMax = 500;
    public $_baseUrl = 'http://smssh1.253.com/msg/send/json';
    public $_balance_url = 'http://smssh1.253.com/msg/send/json';

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

        $result = $this->postData($this->_baseUrl, [
            'account'  => $this->_userName,
            'password' => $this->_password,
            'msg'      => $this->_extArr['from'].$message,
            'phone'   => $mobilesStr,
        ]);

        $data = array();
        if($result)
        {
            $data = array(
                'code'      => $result['code'],
                'messageId' => $result['msgId'],
                'data'      => []
            );
        }

        return $data;
    }

    public function balance()
    {
        $res = $this->postData($this->_balance_url, [
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
                'base_uri'              => $uri,
                RequestOptions::TIMEOUT => 60,
            ]
        );
        $body[RequestOptions::HTTP_ERRORS] = false; //禁止http_errors 4xx 和 5xx
        $body[RequestOptions::JSON] = $params;
        $response = $client->request('POST', '', $body);

        $result = 200 == $response->getStatusCode() ? json_decode($response->getBody()->getContents(), true) : [];
        return $result;
    }
}
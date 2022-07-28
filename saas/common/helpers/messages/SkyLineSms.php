<?php


namespace common\helpers\messages;

use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

class SkyLineSms extends BaseSms
{
    private $sendUri = 'sendsmsV2';
    private $balanceUri = 'getbalanceV2';

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

        $res = $this->postData($this->sendUri, [
            'numbers'  => $mobilesStr,
            'content'  => urlencode($message),
            'senderid' => $this->_extArr['from'],
        ]);
        $result = $res['response']->getBody()->getContents();

        return $result;
    }

    public function balance()
    {
        $res = $this->getData($this->balanceUri);
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

    private function getData(string $uri)
    {
        $dataTimeStr = Carbon::now(new \DateTimeZone('Asia/Shanghai'))->format('YmdHis');
        $signStr = strtolower(md5($this->_userName . $this->_password . $dataTimeStr));
        $client = new Client([
                'base_uri'              => $this->_baseUrl,
                RequestOptions::TIMEOUT => 60,
            ]
        );
        $body[RequestOptions::HTTP_ERRORS] = false; //禁止http_errors 4xx 和 5xx
        $body[RequestOptions::QUERY] = [
            'account'  => $this->_userName,
            'sign'     => $signStr,
            'datetime' => $dataTimeStr,
        ];
        $response = $client->request('GET', $uri, $body);

        $result = [
            'request'  => [
                'body' => $body,
            ],
            'response' => $response,
        ];

        return $result;
    }

    private function postData(string $uri, array $params)
    {
        $dataTimeStr = Carbon::now(new \DateTimeZone('Asia/Shanghai'))->format('YmdHis');
        $signStr = strtolower(md5($this->_userName . $this->_password . $dataTimeStr));
        $client = new Client([
                'base_uri'              => $this->_baseUrl,
                RequestOptions::TIMEOUT => 60,
            ]
        );
        $body[RequestOptions::HTTP_ERRORS] = false; //禁止http_errors 4xx 和 5xx
        $body[RequestOptions::JSON] = $params;
        $body[RequestOptions::QUERY] = [
            'account'  => $this->_userName,
            'sign'     => $signStr,
            'datetime' => $dataTimeStr,
        ];
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
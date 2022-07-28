<?php


namespace common\helpers\messages;


use Carbon\Carbon;
use common\helpers\RedisDelayQueue;
use common\helpers\RedisQueue;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

class KarixSms extends BaseSms
{
    public $_batchMax = 100;

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

        $client = new Client([
            RequestOptions::TIMEOUT => 30
        ]);

        $response = $client->request('POST', $this->_baseUrl, [
            RequestOptions::HTTP_ERRORS => false,
            RequestOptions::FORM_PARAMS => [
                'ver'    => '1.0',
                'key'    => $this->_password,
                'encrpt' => '0',
                'dest'   => $mobilesStr,
                'text'   => $message,
                'send'   => $this->_extArr['from'],
            ],
        ]);
        return 200 == $response->getStatusCode() ? $response->getBody()->getContents() : [];
    }

    public function balance()
    {
        return [];
    }

    public function acceptReport()
    {
        // TODO: Implement acceptReport() method.
    }

    public function collectReport()
    {
        // TODO: Implement collectReport() method.
    }
}
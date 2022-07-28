<?php


namespace common\helpers\messages;


use Carbon\Carbon;
use common\helpers\RedisDelayQueue;
use common\helpers\RedisQueue;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

class IndiaSms extends BaseSms
{
    public $_batchMax = 100;
    protected $_baseUrl = 'http://cloud.smsindiahub.in/vendorsms/pushsms.aspx';
    protected $_balanceUrl = 'http://cloud.smsindiahub.in/vendorsms/CheckBalance.aspx';

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

        $gwid = isset($this->_extArr['gwid']) ? $this->_extArr['gwid'] : $this->_extArr['type'];

        $response = $client->request('POST', $this->_baseUrl, [
            RequestOptions::HTTP_ERRORS => false,
            RequestOptions::FORM_PARAMS => [
                'user' => $this->_userName,
                'password' => $this->_password,
                'msg' => $message,
                'msisdn' => $mobilesStr,
                'sid' => $this->_extArr['from'],
                'fl' => 0,
                'gwid' => $gwid
            ],
        ]);
        $result = 200 == $response->getStatusCode() ? $response->getBody()->getContents() : new \stdClass();
        $result = json_decode($result,true);
        if (000 == $result['ErrorCode']) {   //发起成功
            $data = $result;
        }else{
            $data = false;
        }
        
        return $data;
    }

    public function balance()
    {
        $client = new Client([
            RequestOptions::TIMEOUT => 30
        ]);

        $response = $client->request('POST', $this->_balanceUrl, [
            RequestOptions::HTTP_ERRORS => false,
            RequestOptions::FORM_PARAMS => [
                'user' => $this->_userName,
                'password' => $this->_password,
            ],
        ]);
        $result = 200 == $response->getStatusCode() ? $response->getBody()->getContents() : new \stdClass();
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
}
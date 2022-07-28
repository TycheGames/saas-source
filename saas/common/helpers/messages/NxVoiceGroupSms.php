<?php


namespace common\helpers\messages;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

class NxVoiceGroupSms extends BaseSms
{
    public $_batchMax = 100;
    public $_baseUrl = 'http://api.nxcloud.com/api/voiceSms/gpsend';
    public $_queryUrl = 'http://api.nxcloud.com/api/voiceSms/getVoiceCdr';

    public function getRequestReturnCollect()
    {
        // TODO: Implement getRequestReturnCollect() method.
    }

    public function sendSMS(array $mobileArr, $voice_url)
    {
        $mobile = $mobileArr[0];
        $mobile = strlen($mobile) == 10 ? '91' . $mobile : $mobile;
        $params = [
            'appkey'       => $this->_userName,
            'secretkey'    => $this->_password,
            'phone'        => $mobile,
            'country_code' => $this->_extArr['country_code'],
            'show_phone'   => $this->_extArr['show_phone'],
            'url'          => $voice_url,
        ];
        $client = new Client([
                'base_uri'              => $this->_baseUrl,
                RequestOptions::TIMEOUT => 60,
            ]
        );
        $body[RequestOptions::HTTP_ERRORS] = false; //禁止http_errors 4xx 和 5xx
        $body[RequestOptions::FORM_PARAMS] = $params;
        $response = $client->request('POST', '', $body);
        $result = 200 == $response->getStatusCode() ? $response->getBody()->getContents() : '';
        $result = json_decode($result, true);
        return $result;
    }

    public function balance()
    {

    }

    public function acceptReport()
    {
        // TODO: Implement acceptReport() method.
    }

    public function collectReport()
    {
        // TODO: Implement collectReport() method.
    }

    public function queryResult($sendId){
        $params = [
            'appkey'       => $this->_userName,
            'secretkey'    => $this->_password,
            'voiceType'    => 0,
            'messageid'    => $sendId,
            'start_time'   => $this->_extArr['start_time'] ?? date('Y-m-d 00:00:00'),
            'end_time'     => $this->_extArr['end_time'] ?? date('Y-m-d H:i:s'),
            'page_size'    => $this->_extArr['page_size'] ?? 10,
            'page'         => $this->_extArr['page'] ?? 1,
        ];
        $client = new Client([
                'base_uri'              => $this->_queryUrl,
                RequestOptions::TIMEOUT => 60,
            ]
        );
        $body[RequestOptions::HTTP_ERRORS] = false; //禁止http_errors 4xx 和 5xx
        $body[RequestOptions::FORM_PARAMS] = $params;
        $response = $client->request('POST', '', $body);
        $result = 200 == $response->getStatusCode() ? $response->getBody()->getContents() : '';
        $result = json_decode($result, true);
        return $result;
    }
}
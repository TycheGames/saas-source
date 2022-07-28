<?php

namespace common\services;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use yii;

class ReCaptchaService extends BaseService
{

    private $url;
    private $secret;
    private $webSecret;
    private $webUrl;
    private $webUrlCN;

    public function __construct($config = [])
    {
        $this->secret = yii::$app->params['reCaptcha']['secret'];
        $this->webSecret = yii::$app->params['reCaptcha']['webSecret'];
        $this->url = trim(yii::$app->params['reCaptcha']['uri'], '/') . '/' . 'recaptcha/api/siteverify';
        $this->webUrl = trim(yii::$app->params['reCaptcha']['uri'], '/') . '/' . 'recaptcha/api.js?render=' . $this->webSecret;
        $this->webUrlCN = trim(yii::$app->params['reCaptcha']['uriCn'], '/') . '/' . 'recaptcha/api.js?render=' . $this->webSecret;
        parent::__construct($config);
    }


    public function getWebUrlCN()
    {
        return $this->webUrlCN;
    }

    public function getWebUrl()
    {
        return $this->webUrl;
    }

    public function getWebSecret()
    {
        return $this->webSecret;
    }

    public function verify($token, $ip)
    {
        $postData = [
            'secret' => $this->secret,
            'response' => $token,
            'remoteip' => $ip
        ];
        $client = new Client([
            RequestOptions::TIMEOUT => 3,
            'verify' => false
        ]);

        $responseRaw = $client->request('POST', $this->url, [
            RequestOptions::FORM_PARAMS => $postData,
        ]);

        $r = $responseRaw->getBody()->getContents();
        $result = json_decode($r, true);
        if(isset($result['success']) && true == $result['success'])
        {
            return true;
        }else{
            yii::error($r, 'reCaptcha');
            return false;
        }


    }


}

<?php

namespace common\services\user;

use common\services\BaseService;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Yii;

class YuanDingBankVerificationService extends BaseService
{
    private $url = 'https://in.creditech.biz/rt/verify_bank/';
    private $username = '';
    private $password = '';
    private $timeout = 60; //接口请求超时时间
    public $retryTime = 360; //重试间隔
    public $retryLimit = 3;  //重试次数
    public $response; //数据返回


    private $responseCodeSuccess = ['TXN'];
    private $responseCodeFailed = ['IAN', 'ERR', 'NNR', 'ITM', 'FAB'];
    private $responseCodeRetry = ['DTX', 'SPE', 'SPD', 'IPE', 'ISE', 'SNA', 'UNE', 'IE', 'SUA', 'TUP'];

    public $userSourceId;
    public $apiId;

    public function __construct($config = [])
    {
        parent::__construct($config);

        $this->url = Yii::$app->params['YuanDing'][$this->userSourceId]['url'] ?? Yii::$app->params['YuanDing']['common']['url'];
        $this->username = Yii::$app->params['YuanDing'][$this->userSourceId]['user'] ?? Yii::$app->params['YuanDing']['common']['user'];
        $this->password =  Yii::$app->params['YuanDing'][$this->userSourceId]['password'] ?? Yii::$app->params['YuanDing']['common']['password'];
        $this->apiId = Yii::$app->params['YuanDing'][$this->userSourceId]['user'] ?? Yii::$app->params['YuanDing']['common']['user'];
    }

    /**
     * 获取银行卡报告
     * @param $name
     * @param $account
     * @param $ifsc
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getBankInfo($name, $account, $ifsc)
    {
        return $this->request($name, $account, $ifsc)->handle();
    }


    /**
     * 判断银行卡信息是否匹配
     * @return bool
     */
    public function checkBankInfo()
    {
        if(isset($this->response['response_code'])
            && in_array($this->response['response_code'],$this->responseCodeSuccess)
            && isset($this->response['data']['status_b'])
            && (200 == $this->response['data']['status_b'])
        ){
            return true;
        }else{
            return false;
        }
    }
    /**
     * @param $name
     * @param $account
     * @param $ifsc
     * @return $this
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function request($name, $account, $ifsc)
    {
        $client = new Client([
            RequestOptions::AUTH => [$this->username, $this->password],
            RequestOptions::TIMEOUT => $this->timeout
        ]);
        $response = $client->request('POST', $this->url, [
            RequestOptions::JSON => [
                'bank_account' => $account,
                'ifsc_code' => $ifsc,
                'full_name' => $name,
            ],
        ]);

        $result = 200 == $response->getStatusCode() ? $response->getBody()->getContents() : new \stdClass();
        $this->response = json_decode($result, true);
        \Yii::info($this->response,'bank info verify');
        return $this;
    }


    public function requestNew($userId, $name, $account, $ifsc){
        $time = date('YmdHi');
        $client = new Client([
            RequestOptions::TIMEOUT => $this->timeout
        ]);
        $headers = [
            'c_name' => $this->username,
            'timestamp' => $time,
            'auth_info' => md5($this->username.$this->password.$time)
        ];
        $response = $client->request('POST', 'https://in.creditech.biz/v2/verify_bank/', [
            RequestOptions::JSON => [
                'uid' => $userId,
                'bank_account' => $account,
                'ifsc_code' => $ifsc,
                'full_name' => $name,
                'callbackURL' => 'http://api.i-credit.in/notify/bank-notify'
            ],
            RequestOptions::HEADERS => $headers
        ]);

        $result = 200 == $response->getStatusCode() ? $response->getBody()->getContents() : new \stdClass();
        $this->response = json_decode($result, true);
        \Yii::info([
            'headers' => $headers,
            'response' => $result
        ],'bank_verify_yuanding_new');
        return $this;
    }

    /**
     * 获取成功或重试
     * @return bool
     */
    public function handle()
    {
        if($this->isSuccess()){
            return true;
        }elseif ($this->isRetry()){
            $this->setError('Oops failed! Please try again in 5 minutes.');
            return false;
        }elseif($this->isFailed()){
            $this->setError('Please enter the correct bank account and IFSC code.');
            return false;
        }
    }




    public function isSuccess(){
        if(isset($this->response['response_code'])
            && in_array($this->response['response_code'],$this->responseCodeSuccess)
            && isset($this->response['status'])
            && 200 == $this->response['status']
        ){
            return true;
        }else{
            return false;
        }
    }


    public function isRetry()
    {
        if(isset($this->response['response_code'])
            && in_array($this->response['response_code'],$this->responseCodeRetry)
        ){
            return true;
        }else{
            return false;
        }
    }

    public function isFailed()
    {
        if(isset($this->response['response_code'])
            && in_array($this->response['response_code'],$this->responseCodeFailed)
        ){
            return true;
        }
        return false;
    }

}

<?php
namespace common\helpers\messages;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Yii;

class ScoreoneSms extends BaseSms {

    // 所有短信接口超时时间
    public $timeout = 30;

    public $_batchMax = 500;
    /**
    * @desc
    * @param string $baseUrl  请求地址   对应文档 APIURL
    * @param string $userName 用户名     对应文档nonce_str
    * @param string $password 密码       对应文档app_secret
    * @param array $extArr   扩展参数
    * @return
    */
    public function __construct($baseUrl, $userName, $password, $extArr = [], $smsServiceUse = '') {
        parent::__construct($baseUrl, $userName, $password, $extArr);
    }

    public function getSmsId() {
        return $this->_smsId;
    }


    /**
     * @param array $phone
     * @param string $message
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function sendSMS(array $phone, $message) {
        $phoneStr = implode(',',$phone);
        $url = $this->_baseUrl;
        $extArr = $this->_extArr;
        $content = $message;

        $client = new Client([
            RequestOptions::TIMEOUT => $this->timeout
        ]);

        $response = $client->request('POST', $url, [
            RequestOptions::HTTP_ERRORS => false,
            RequestOptions::FORM_PARAMS => [
                'appType' => $this->_password,
                'appCode' =>  $this->_userName,
                'areaCode' => 91,
                'phone' => $phoneStr,
                'message' => $content,
                'remark' => 'sms',
            ],
        ]);

        $result = $response->getBody()->getContents();
        $result = json_decode($result,true);
        if(isset($result['code']) && 0 == $result['code'])
        {
            return true;
        }else{
            return false;
        }
    }


    /**
     * 格式化天畅推送记录
     * @param $str
     *
     * @return array
     */
    public static function formatUploadData($str) {
        $ret = [];
        foreach (explode("&", $str) as $item) {
            $item = trim($item);
            if ($item && FALSE !== strpos($item, '=')) {
                list($k, $v) = explode("=", $item);
                $ret[$k] = $v;
            }
        }

        return self::checkUpload($ret);
    }

    /*
     * 检查上传记录
     */
    protected static function checkUpload(array $arr) {
        foreach (['id', 'sa', 'su'] as $val) {
            if (!isset($arr[$val])) {
                return [];
            }
        }

        return $arr;
    }

    public function collect() {

    }

    #请求数据和接收数据大集合
    public function getRequestReturnCollect() {
        //TODO
    }


    #取得余额 (剩余短信条数)
    public function balance() {
        $client = new Client([
            RequestOptions::TIMEOUT => $this->timeout
        ]);

        $response = $client->request('POST', $this->_baseUrl, [
            RequestOptions::FORM_PARAMS => [
                'accessId' => $this->_userName
            ],
        ]);
        $result = 200 == $response->getStatusCode() ? $response->getBody()->getContents() : new \stdClass();
        return $result;
    }


    public function acceptReport() {
        //TODO
    }

    public function collectReport()
    {

    }
}

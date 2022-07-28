<?php
namespace common\helpers\messages;


class TianChangSms extends BaseSms {

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
    * Array (
    *   [success] => 1
    *   [id] => 154544248378917736
    * )
    * @param array $phoneArr
    * @param string $message
    * @return
    */
    public function sendSMS(array $phoneArr, $message, $name = 'Sashaktrupee') {
        $ret = [];

        $phoneStr = implode(";",$phoneArr);
        $msg = urlencode($message);
        $url = $this->_baseUrl;
        $pwd = $this->_password;
        $uid = $this->_userName;

        $ctx = stream_context_create(self::$ctx_params);
        $result = \file_get_contents("{$url}?un={$uid}&pw={$pwd}&da={$phoneStr}&sm={$msg}&dc=15&tf=3&rf=2&rd=1", false, $ctx);

//        file_put_contents("/tmp/sms.log", "{$url}?un={$uid}&pw={$pwd}&da={$phone}&sm={$msg}&dc=15&tf=3&rf=2&rd=1\r\n",FILE_APPEND);
        $resp = json_decode($result, true);
        if ($resp['success']) {
            if(!empty($resp['id'])) {
                $phones = explode(";", $phoneStr);
                foreach($phones as $key => $value) {
                    $ret[$key]['to'] = $value;
                    $ret[$key]['send_id'] = $resp['id'];
                }
            }
        }

        return $ret;
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


    #取得余额
    public function balance() {
        //TODO
    }


    public function acceptReport() {
        //TODO
    }

    public function collectReport()
    {

    }
}

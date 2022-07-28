<?php

namespace common\helpers;

use common\models\LoanPerson;
use common\models\sms\SmsRetryRecord;
use common\models\sms\SmsSendRecord;
use common\models\UserChannelMap;
use common\services\ExternalChannelService;
use common\services\TemplateService;
use Yii;
use common\api\RedisQueue;
use common\base\LogChannel;
use common\helpers\CurlHelper;
use yii\base\Exception;

class MessageHelper {
    /**
     * 检查上报手机号格式是否正常，并剔除非法号码
     *
     * @param string $str
     * @return array
     */
    static function checkPhoneValidate($str = '') {

        if (!$str) {
            return [];
        }

        //替换字符串中的非法格式
        $search = array(" "," ","　","\n","\r","\t");
        $replace = array("","","","","","");
        $new_str = str_replace($search, $replace, $str);

        $array = explode(':', $new_str);
        $ret = [];

        if (!$array) {
            return $ret;
        }

        //验证手机格式
        foreach ($array as $v) {

            preg_match(Util::getPhoneMatch(), $v, $match_all);

            if ((isset($match_all) && $match_all[2])) {
                $ret[] = $match_all[2];
            }
        }

        return $ret;
    }



    /**
     * [sendAll 发送短信总方法]
     * @param $phone
     * @param $message
     * @param string $config
     * @param array $ext_arr
     * @return array
     * @throws \Exception
     */
    public static function sendAll($phone, $message, $config = 'smsService_LianDong_OTP', $ext_arr = []) {
        if (!isset(Yii::$app->params[$config])) {
            throw new Exception("未在params下找到:{$config}对应配置。", 3000);
        }
        if (!isset(explode('_', $config)[1])) {
            throw new Exception("格式配置错误，请按照对应格式配置:{$config}", 3001);
        }

        $className = explode('_', $config)[1];
        $path = "\common\helpers\messages";
        $class = "{$path}\\{$className}Sms";
        if (!class_exists($class)) {
            throw new Exception("请在:{$class}下实现对应短信实现类。", 3002);
        }

        $params = Yii::$app->params[$config];
        if (count($ext_arr) > 0) {
            foreach ($ext_arr as $k => $val) {
                $params[$k] = $val;
            }
        }
        /* @var $model \common\helpers\messages\BaseSms */
        $model = new $class($params['url'], $params['account'], $params['password'], $params);
        $res = [];
        $phoneArr = is_array($phone) ? $phone : [$phone];
        $bCount = ceil(count($phoneArr) / $model->_batchMax);
        for ($i = 0; $i < $bCount; $i++){
            try {
                $phoneSendList = array_slice($phoneArr,$i*$model->_batchMax ,$model->_batchMax);
                $res[$i] = $model->sendSMS($phoneSendList, $message);
            }
            catch (\Exception $ex) {
                $res[$i] = false;
            }
        }
        return $res;
    }

    /**
     * [queryBalance 短信余额]
     * @param int $phone 手机号
     * @param string $message 信息
     * @param string $config 渠道名
     * @param array $ext_arr
     * @return int
     * @throws \Exception
     */
    public static function queryBalance($config = 'smsService_JinCheng_HY', $ext_arr = []){
        if (!isset(Yii::$app->params[$config])) {
            throw new \Exception("未在params下找到:{$config}对应配置。", 3000);
        }
        if (!isset(explode('_', $config)[1])) {
            throw new \Exception("格式配置错误，请按照对应格式配置:{$config}", 3001);
        }

        $className = explode('_', $config)[1];
        $path = "\common\helpers\messages";
        $class = "{$path}\\{$className}Sms";
        if (!class_exists($class)) {
            throw new \Exception("请在:{$class}下实现对应短信实现类。", 3002);
        }

        $balance = 0;
        try {
            $params = Yii::$app->params[$config];
            if (count($ext_arr) > 0) {
                foreach ($ext_arr as $k => $val) {
                    $params[$k] = $val;
                }
            }
            /* @var $model \common\helpers\messages\BaseSms */
            $model = new $class($params['balance_url'], $params['account'], $params['password'], $params);
            $balance = $model->balance();
        }
        catch (\Exception $ex) {
        }
        return $balance;
    }

    /**
     * [querySendResult 查询结果]
     * @param string $config 渠道名
     * @param string $send_id
     * @param array $ext_arr
     * @return int
     * @throws \Exception
     */
    public static function querySendResult($config = 'smsService_NxVoiceGroup_All', $send_id, $ext_arr = []){
        if (!isset(Yii::$app->params[$config])) {
            throw new \Exception("未在params下找到:{$config}对应配置。", 3000);
        }
        if (!isset(explode('_', $config)[1])) {
            throw new \Exception("格式配置错误，请按照对应格式配置:{$config}", 3001);
        }

        $className = explode('_', $config)[1];
        $path = "\common\helpers\messages";
        $class = "{$path}\\{$className}Sms";
        if (!class_exists($class)) {
            throw new \Exception("请在:{$class}下实现对应短信实现类。", 3002);
        }

        if(!method_exists($class,'queryResult')){
            throw new \Exception("请在:{$class}下实现对应短信实现类的function。", 3003);
        }

        $queryResult = [];
        try {
            $params = Yii::$app->params[$config];
            if (count($ext_arr) > 0) {
                foreach ($ext_arr as $k => $val) {
                    $params[$k] = $val;
                }
            }
            /* @var $model \common\helpers\messages\BaseSms */
            $model = new $class($params['balance_url'], $params['account'], $params['password'], $params);
            $queryResult = $model->queryResult($send_id);
        }
        catch (\Exception $ex) {
        }
        return $queryResult;
    }

    /**
     * 限制手机号发送短信,1分钟一次
     */
    public static function limitSendSmsByPhone($phone){
        if(!empty($phone)) {
            $key = "limited-times-{$phone}";
            $ret = Yii::$app->redis->executeCommand('GET', [$key]);
            if(empty($ret)) {
                Yii::$app->redis->executeCommand('SET', [$key, 1]);
                Yii::$app->redis->executeCommand('EXPIRE', [$key, 60]);
            }else{
                return false;
            }
        }
        return true;
    }

    /**
     * 记录手机号发送次数
     */
    public static function addTimesSendSmsByPhone($phone){
        if(!empty($phone)) {
            $date = date('Ymd');
            $key = "limited-day-times-{$date}:{$phone}";
            $ret = Yii::$app->redis->executeCommand('HINCRBY', [$key, 'count', 1]);
            if(intval($ret) == 1) {
                Yii::$app->redis->executeCommand('EXPIRE', [$key, 24*60*60]);
            }
        }
        return $ret;
    }

    /**
     * 限制手机号发送短信,1天10次
     */
    public static function limitDaySendSmsByPhone($phone, $limit_times = 10) {
        if(!empty($phone)) {
            $date = date('Ymd');
            $key = "limited-day-times-{$date}:{$phone}";
            $ret = Yii::$app->redis->executeCommand('HGET', [$key, 'count']);
            if($ret > $limit_times){
                return false;
            }
        }
        return true;
    }
}

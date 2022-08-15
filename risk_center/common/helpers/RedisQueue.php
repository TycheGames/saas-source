<?php

namespace common\helpers;


class RedisQueue {

    const USER_ADMIN_CAPTCHA_CACHE = 'user_admin:captcha_cache'; //用户后台验证码缓存

    /**
     * 添加锁
     */
    const USER_OPERATE_LOCK = "user:operate:lock:";
    const ADMIN_OPERATE_LOCK = "admin:operate:lock:";
    const CREDIT_AUTO_CHECK = 'credit_auto_check'; //机审队列
    const CREDIT_AUTO_CHECK_OLD = 'credit_auto_check_old'; //机审队列-老用户
    const CREDIT_USER_CREDIT_CALC = 'credit_user_credit_calc'; //用户额度计算队列
    const CREDIT_NOT_PUSH_LIST = 'credit_not_push_list'; //风控回调失败队列

    const LIST_USER_MOBILE_APPS_UPLOAD = 'list_user_mobile_apps_upload';//上报app-list
    const LIST_USER_MOBILE_CONTACTS_UPLOAD = 'list_user_mobile_contacts_upload';//上报通讯记录
    const LIST_USER_MOBILE_SMS_UPLOAD = 'list_user_mobile_sms_upload';//上报短信记录
    const LIST_USER_MOBILE_CALL_RECORDS_UPLOAD = 'list_user_mobile_call_records_upload';//上报通话记录

    const GET_MODEL_SCORE_LIST = 'get_model_score_list'; //获取需要跑模型分的用户

    const MESSAGE_WEWORK_TOKEN_CACHE = 'message:wework:token:cache';

    const DING_DING_ALERT_LIST_RISK_CENTER = 'ding_ding_alert_list:risk_center'; //钉钉报警队列-risk_center

    static function getRedis($params) {
        if (empty($params)) {
            return \yii::$app->redis;
        }

        $key = is_array($params) ? $params[0] : $params;
        if (strpos($key, self::USER_OPERATE_LOCK) !== false) { # lock 类
            return \yii::$app->redis;
        } else if ($key === 'redis_alert') {
            return \yii::$app->redis_alert;
        }

        return \yii::$app->redis;
    }

    public static function push($params = [], $redisDb = '') {
        if(!empty($redisDb))
        {
            $redis = self::getRedis($redisDb);
        }
        else
        {
            $redis = self::getRedis($params);
        }

        return $redis->executeCommand('RPUSH', $params);
    }

    /**
     * 固定长度队列
     * @param $key
     * @param $value
     * @param int $length
     * @return mixed
     */
    public static function pushFixedLength($key, $value, $length = 100) {
        $redis = self::getRedis(null);
        $ret = $redis->rpush($key, $value);

        $len = $redis->llen($key);
        if ( $ret && $len > $length ) {
            return $redis->ltrim($key, -1 * $length, -1);
        }

        return $ret;
    }

    public static function getFixedList($key, $length = 100) {
        $redis = self::getRedis(null);
        return $redis->lrange($key, -1 * $length, -1);
    }

    public static function pop($params = [], $redisDb = '') {
        if(!empty($redisDb))
        {
            $redis = self::getRedis($redisDb);
        }
        else
        {
            $redis = self::getRedis($params);
        }
        return $redis->executeCommand('LPOP', $params);
    }

    /**
     * @param $key
     * @param $value
     * @param int $expireSecond
     * @return mixed
     */
    public static function newSet($key, $value, $expireSecond = 0)
    {
        $redis = self::getRedis($key);
        $set   = $redis->set($key, $value);

        if ($expireSecond > 0) {
            $redis->expire($key, $expireSecond);
        }

        return $set;
    }

    /**
     * @param $key
     * @return mixed
     */
    public static function newGet($key)
    {
        $redis = self::getRedis($key);

        return $redis->get($key);
    }
    /**
     * @param $key
     * @return mixed
     */
    public static function newDel($key)
    {
        $redis = self::getRedis($key);

        return intval($redis->del($key));
    }

    public static function set($params=['expire'=>3600,'key'=>'','value'=>'']) {
        $redis = self::getRedis($params['key']);

        $redis->set($params['key'], $params['value']);
        if(isset($params['expire']))
        {
            $redis->expire($params['key'], $params['expire']);
        }
        return true;
    }

    public static function get($params=['key'=>'']) {
        $redis = self::getRedis($params['key']);
        return $redis->get($params['key']);
    }

    public static function del($params=['key'=>'']) {
        $redis = self::getRedis($params['key']);
        return $redis->del($params['key']);
    }

    public static function getXyyp($params=['key'=>''])
    {
        $redis = self::getRedis('xyyp');
        return $redis->get($params['key']);
    }

    /**
     * 控制队列长度
     * COMMAND : LTRIM KEY_NAME START STOP
     */
    public static function getFixedLength($params = []) {
        $redis = self::getRedis($params);
        return $redis->executeCommand('LTRIM', $params);
    }

    /**
     * 获取队列所有元素
     * COMMAND : lrange KEY_NAME 0 -1
     */
    public static function getQueueList($params, $redisDb = '') {
        if(!empty($redisDb))
        {
            $redis = self::getRedis($redisDb);
        }
        else
        {
            $redis = self::getRedis($params);
        }
        return $redis->executeCommand('LRANGE', $params);
    }

    /**
     * 获取当前队列长度
     */
    public static function getLength($params = [], $redisDb = '') {
        if(!empty($redisDb))
        {
            $redis = self::getRedis($redisDb);
        }
        else
        {
            $redis = self::getRedis($params);
        }

        return $redis->executeCommand('LLEN', $params);
    }

    /**
     * 递增
     */
    public static function inc($params = []) {
        $redis = self::getRedis($params);
        return $redis->executeCommand('INCRBY', $params);
    }

    /**
     * 重置过期时间
     */
    public static function expire($params = []) {
        $redis = self::getRedis($params);
        return $redis->executeCommand('EXPIRE', $params);
    }

    /**
     * lock
     */
    public static function setnx($params) {
        $redis = self::getRedis($params);
        return $redis->executeCommand('SETNX', $params);
    }

    /**
     * 递减
     */
    public static function dec($params = []) {
        $redis = self::getRedis($params);
        return $redis->executeCommand('DECRBY', $params);
    }

    /**
     * 递减
     */
    public static function desc($params = []) {
        $redis = self::getRedis($params);
        return $redis->executeCommand('DECRBY', $params);
    }

    /**
     * 递增
     */
    public static function incr($params = ['key'=>'']) {
        $redis = self::getRedis($params);
        return $redis->executeCommand('INCRBY', $params);
    }
    //批量弹出
    public static function batchPop($params = [],$lenth=100) {
        $returnArr = [];
        $redis = self::getRedis($params);
        while (true) {
            if (count($returnArr) >= $lenth)  {
                break;
            } else {
                $result = $redis->executeCommand('LPOP', $params);
                if (!is_null(json_decode($result))) {
                    $returnArr[] = json_decode($result);
                } elseif ($result) {
                    $returnArr[] = $result;
                } else {
                    break;
                }
            }
        }
        return $returnArr;
    }

}


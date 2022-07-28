<?php

namespace common\helpers;


use yii\redis\Connection;

class RedisQueue {

    /**
     * 添加锁
     */
    const USER_OPERATE_LOCK = "user:operate:lock:";
    const ADMIN_OPERATE_LOCK = "admin:operate:lock:";
    const CREDIT_GET_DATA_SOURCE_PREFIX = 'credit_get_data_source'; //数据采集队列名
    const CREDIT_AUTO_CHECK = 'credit_auto_check'; //机审队列
    const CREDIT_AUTO_CHECK_OLD = 'credit_auto_check_old'; //机审队列-老用户
    const USER_LOGIN_PC_LOCK = 'user:login_pc:lock';
    const USER_LOGIN_MOBILE_LOCK = 'user:login_mobile:lock';

    const LIST_USER_PC_SESSION = 'list_session:user_pc';
    const LIST_USER_MOBILE_SESSION = 'list_session:user_mobile';
    const LIST_USER_MOBILE_APPS_UPLOAD = 'list_user_mobile_apps_upload';//上报app-list
    const LIST_USER_MOBILE_CONTACTS_UPLOAD = 'list_user_mobile_contacts_upload';//上报通讯记录
    const LIST_USER_MOBILE_SMS_UPLOAD = 'list_user_mobile_sms_upload';//上报短信记录
    const LIST_USER_MOBILE_CALL_RECORDS_UPLOAD = 'list_user_mobile_call_records_upload';//上报通话记录
    const LIST_USER_MOBILE_PHOTO_UPLOAD = 'list_user_mobile_photo_upload';//上报相册记录
    const Z_LIST_MERCHANT_AGEING_REPAY_ORDER = 'z_list_merchant_ageing_repay_order';//商户账龄还款订单列表
    const Z_LIST_MERCHANT_AGEING_REPAY_ORDER_SCORE = 'z_list_merchant_ageing_repay_order_score';//商户账龄还款订单列表(辅助排名)


    const CUISHOU_USER_TXL_KEY  = 'cui_user_txl';  //催收保存用户通讯录key
    //const COLLECTION_RESET_REMINDER_LIST = 'collection_reset_reminder_list';  //计息脚本中，到达逾期之前推入，用于更新提醒等级,订单提醒待提醒
    const COLLECTION_RESET_OVERDUE_LIST = 'collection_reset_overdue_list';  //计息脚本中逾期天数，到达某天时推入，用于更新逾期等级,订单催收待回收
    const REMIND_ORDER_LIST = 'remind_order_list';//到期日推入未还款需提醒的订单
    const REMIND_ORDER_CHANGE_STATUS = 'remind_order_change_status';//提醒订单状态变更


    const MESSAGE_WEWORK_TOKEN_CACHE = 'message:wework:token:cache';
    const USER_ADMIN_CAPTCHA_CACHE = 'user_admin:captcha_cache'; //用户后台验证码缓存

    const LIST_KUDOS_USER_REPAYMENT = 'list_kudos_user_repayment'; //kudos 用户每次还款
    const LIST_KUDOS_USER_ORDER_CLOSURE = 'list_kudos_user_order_closure'; //kudos 结束订单，按期还款
    const LIST_KUDOS_USER_ORDER_PRECLOSURE = 'list_kudos_user_order_preclosure'; //kudos 结束订单，提前还款
    const LIST_KUDOS_USER_ORDER_ISSUED = 'list_kudos_user_order_issued'; //kudos 逾期两天
    const LIST_KUDOS_USER_ORDER_RAISED = 'list_kudos_user_order_raised'; //kudos 逾期七天

    const LIST_VALIDATION_PAN_ACCUAUTH_NORMAL = 'list_validation_pan_accuauth_normal'; //pan验证-accuauth-normal
    const LIST_VALIDATION_PAN_ACCUAUTH_LITE = 'list_validation_pan_accuauth_lite'; //pan验证-accuauth-lite
    const LIST_VALIDATION_PAN_SERVICE = 'list_validation_pan_service'; //pan验证当前使用的服务
    const LIST_VERIFY_USER_BANK = 'list_verify_user_bank'; // 用户银行卡认证队列

    const KEY_PREFIX_VALIDATION_BANK_AADHAAR_API = 'key_validation_bank_aadhaar_api'; //银行卡验证-aadhaar_api-前缀
    const KEY_PREFIX_VALIDATION_BANK_YUAN_DING = 'key_validation_bank_yuan_ding'; //银行卡验证-元丁-前缀
    const KEY_VALIDATION_BANK_SERVICE_1 = 'key_validation_bank_service_1'; //银行卡验证服务1
    const KEY_VALIDATION_BANK_SERVICE_0 = 'key_validation_bank_service_0'; //银行卡验证服务0

    const QUEUE_REMIND_ORDER_DRAW_MONEY = 'queue_remind_order_draw_money'; //用户提现队列(弃用)
    const QUEUE_REMIND_ORDER_DRAW_MONEY_AUTO = 'queue_remind_order_draw_money_auto'; //用户提现队列

    const LIST_RAZORPAY_CREATE_VIRTUAL_ACCOUNT = 'list_razorpay_create_virtual_account'; //razorpay创建虚拟账号

    const LIST_AGLOW_LOAN_APPLY_REJECT = 'list_aglow_order_reject'; //aglow借款申请拒绝 包括 创建订单、借款申请、状态更新
    const LIST_AGLOW_LOAN_DISBURSED = 'list_aglow_loan_success'; //aglow放款订单 包括 创建订单、借款申请、状态更新、借款确认、放款请求、用户确认、放款状态更新
    const LIST_AGLOW_FEES_UPDATE = 'list_aglow_fees_update'; //aglow费用更新

    const LIST_KUDOS_CREATE_ORDER = 'list_kudos_create_order'; //kudos生成订单号

    const LIST_EXTERNAL_ORDER_PUSH = 'list_external_order_push'; //外部api推送队列
    const LIST_INSIDE_ORDER_PUSH = 'list_inside_order_push'; //内部api推送队列
    const LIST_EXTERNAL_ORDER_MESSAGE = 'list_external_order_message'; //事件短信队列

    const LIST_EXTERNAL_ORDER_CAN_LOAN_TIME = 'list_external_order_can_loan_time'; //外部api可再借时间推送队列

    const QUEUE_REMIND_NO_LOAN_AFTER_REPAY_AUTO = 'queue_remind_no_loan_after_repay_auto'; //用户还款后提醒申请队列
    const QUEUE_BIND_CARD_REJECT = 'queue_bind_card_reject'; //人审绑卡拒绝提示绑卡队列

    const KEY_PREFIX_LOAN_SUCCESS = 'key_prefix_loan_success';//放款成功数量前缀

    const PUSH_USER_LOGIN_DATA              = 'push_user_login_data'; //推送用户登陆日志队列
    const PUSH_ORDER_REJECT_DATA            = 'push_order_reject_data'; //推送订单驳回信息队列
    const PUSH_ORDER_LOAN_SUCCESS_DATA      = 'push_order_loan_success_data'; //推送订单放款信息队列
    const PUSH_PAN_CODE_LAST_LOAN_TIME_DATA = 'push_pan_code_last_loan_time_data'; //推送pan_code最后放款时间队列
    const PUSH_ORDER_REPAYMENT_SUCCESS_DATA = 'push_order_repayment_success_data'; //推送订单还款信息队列
    const PUSH_ORDER_OVERDUE_DATA           = 'push_order_overdue_data'; //推送订单逾期信息队列
    const PUSH_COLLECTION_SUGGESTION_DATA   = 'push_collection_suggestion_data'; //推送催收建议拒绝队列
    const PUSH_LOAN_COLLECTION_RECORD_DATA  = 'push_loan_collection_record_data'; //推送催收记录队列

    const PUSH_PAN_ORDER_ASSIST_APPLY     = 'push_pan_order_assist_apply'; //推送入催订单

    const PUSH_ORDER_ASSIST_APPLY     = 'push_order_assist_apply'; //推送入催订单
    const PUSH_ORDER_ASSIST_OVERDUE   = 'push_order_assist_overdue'; //推送逾期信息
    const PUSH_ORDER_ASSIST_REPAYMENT = 'push_order_assist_repayment'; //推送还款信息
    const PUSH_USER_CONTACTS          = 'push_user_contacts'; //推送用户通讯录
    const PUSH_ORDER_ASSIST_LINK      = 'push_order_assist_link'; //推送订单url

    const PUSH_ORDER_REMIND_APPLY     = 'push_order_remind_apply'; //推送提醒订单
    const PUSH_ORDER_REMIND_REPAYMENT = 'push_order_remind_repayment'; //推送还款信息

    const RISK_BLACK_LIST = 'risk_black_list'; //黑名单队列

    const PUSH_COLLECTION_LAST_ACCESS_USER  = 'push_collection_last_access_user'; //推送用户最后访问APP埋点队列
    const PUSH_ASSIST_CENTER_LAST_PAY_USER  = 'push_assist_center_last_pay_user'; //推送APP最后申请付款埋点用户 （指定催收中心库）

    const QUEUE_SELECT_USER_MESSAGE_KEYWORD = 'queue_select_user_message_keyword'; //检索用户短信收款金额队列

    const LIST_REMIND_APP_CALL_RECORDS  = 'list_remind_app_call_records'; //提醒app上报通话记录

    const SCHEDULE_LOAN_COLLECTION_ORDER_BACK_LIST  = 'schedule_loan_collection_order_back_list'; //班表计划操作回收订单队列

    const PUSH_MANUAL_CREDIT_ORDER_DATA  = 'push_manual_credit_order_data'; //推送待人审的订单队列
    const BEFORE_MANUAL_CREDIT_ORDER_ALERT  = 'before_manual_credit_order_alert'; //待人审订单显示锁

    const PUSH_MANUAL_CREDIT_BANK_ORDER_DATA  = 'push_manual_credit_bank_order_data'; //推送待人审绑卡的订单队列
    const BEFORE_MANUAL_CREDIT_BANK_ORDER_ALERT  = 'before_manual_credit_bank_order_alert'; //待人审绑卡订单显示锁

    const COLLECTION_NEW_MESSAGE_TEAM_TL_UID = 'collection_new_message_team_tl_uid'; //催收组长管理消息有新消息集合

    const MESSAGE_TIME_TASK_PRODUCT_NAME_CACHE = 'message_time_task:product_name_cache'; //短信定时任务产品名对应关系缓存

    const DING_DING_ALERT_LIST_SAAS = 'ding_ding_alert_list:saas'; //钉钉报警队列-saas
    const DING_DING_ALERT_LIST_BUSINESS = 'ding_ding_alert_list:business'; //钉钉报警队列-business

    const TEAM_LEADER_SLAVER_CACHE = 'team_leader_slaver:cache'; //组长的副手权限标识缓存

    static function getRedis($params) {
        if (empty($params)) {
            return \yii::$app->redis;
        }

        $key = is_array($params) ? $params[0] : $params;
        if (strpos($key, self::USER_OPERATE_LOCK) !== false) { # lock 类
            return \yii::$app->redis;
        }
        if ($key === 'redis_assist_center') {
            return \yii::$app->redis_assist_center;
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
     * 加锁
     * @param $key
     * @param int $expire 过期时间,单位秒
     * @return bool
     */
    public static function lock($key, $expire = 10)
    {
        if (1 == self::inc([$key, 1])) {
            self::expire([$key, $expire]);
            return true;
        } else {
            return false;
        }
    }

    /**
     * 解锁
     * @param $key
     * @return mixed
     */
    public static function unlock($key)
    {
        return self::del(['key' => $key]);
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

    public static function addSet($key, $value)
    {
        $redis = self::getRedis(null);
        return $redis->executeCommand('SADD', [$key, $value]);
    }

    public static function delSet($key, $value)
    {
        $redis = self::getRedis(null);
        return $redis->executeCommand('SREM', [$key, $value]);
    }

    public static function existSet($key, $value)
    {
        $redis = self::getRedis(null);
        return $redis->executeCommand('SISMEMBER', [$key, $value]);
    }

    /**
     * 向有序集合添加一个或多个成员，或者更新已存在成员的分数
     * @param string $key
     * @param ...$scores_member [score, member]
     * @return mixed
     */
    public static function addZSet(string $key, ...$scores_member)
    {
        /**
         * @var Connection $redis
         */
        $redis = self::getRedis(null);
        return $redis->zadd($key, ...$scores_member);
    }

    /**
     * 返回有序集合中指定成员的排名，有序集成员按分数值递减(从大到小)排序
     * @param string $key
     * @param $member
     * @return mixed
     */
    public static function getZRevRank(string $key, $member)
    {
        /**
         * @var Connection $redis
         */
        $redis = self::getRedis(null);
        return $redis->zrevrank($key, $member);
    }

    /**
     * 返回有序集中，成员的分数值
     * @param string $key
     * @param $member
     * @return mixed
     */
    public static function getZScore(string $key, $member)
    {
        /**
         * @var Connection $redis
         */
        $redis = self::getRedis(null);
        return $redis->zscore($key, $member);
    }

    /**
     * 通过索引区间返回有序集合指定区间内的成员
     * @param string $key
     * @param int $start
     * @param int $stop
     * @param string|null $withScores
     * @return mixed
     */
    public static function getZRange(string $key,int $start,int $stop, string $withScores = null)
    {
        /**
         * @var Connection $redis
         */
        $redis = self::getRedis(null);
        return $redis->zrange($key, $start, $stop, $withScores);
    }

    /**
     * 返回有序集中指定区间内的成员，通过索引，分数从高到低
     * @param string $key
     * @param int $start
     * @param int $stop
     * @param string|null $withScores
     * @return mixed
     */
    public static function getZRevRange(string $key,int $start,int $stop, string $withScores = null)
    {
        /**
         * @var Connection $redis
         */
        $redis = self::getRedis(null);
        return $redis->zrevrange($key, $start, $stop, $withScores);
    }
}


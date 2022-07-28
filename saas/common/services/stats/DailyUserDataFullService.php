<?php
/**
 * Created by loan
 * User: wangpeng
 * Date: 2019/7/5 0005
 * Time：17:05
 */


namespace common\services\stats;

use common\models\ClientInfoLog;
use common\models\enum\VerificationItem;
use common\models\order\UserLoanOrder;
use common\models\order\UserLoanOrderRepayment;
use common\models\order\UserOrderLoanCheckLog;
use common\models\user\LoanPerson;
use common\models\user\UserBankAccount;
use common\models\user\UserRegisterInfo;
use common\models\user\UserVerification;
use common\models\user\UserVerificationLog;
use yii\db\Query;

class DailyUserDataFullService
{
    public static function processClientInfoLogEvent() {
        return [
            'basic_num'      => UserVerification::TYPE_BASIC,
            'identity_num'   => UserVerification::TYPE_PAN,
            'contact_num'    => UserVerification::TYPE_CONTACT,
        ];
    }

    public static $processList = [
        'order'           => [],
        'audit_pass_order' => [UserLoanOrder::STATUS_CHECK,[UserLoanOrder::STATUS_LOANING,UserLoanOrder::STATUS_WAIT_DEPOSIT,UserLoanOrder::STATUS_WAIT_DRAW_MONEY]],
        //'bind_card_pass_order'  => [UserLoanOrder::STATUS_WAIT_DEPOSIT,UserLoanOrder::STATUS_LOANING],
        'withdraw_success_order' => [UserLoanOrder::STATUS_WAIT_DRAW_MONEY,UserLoanOrder::STATUS_LOANING],
//        'loan_success_order'    => [UserLoanOrder::STATUS_LOANING,UserLoanOrder::STATUS_LOAN_COMPLETE]
    ];

    //总新注册用户数 （`reg_num`）
    static function getNewUserNum($startTime,$endTIme){
        $res = LoanPerson::find()
            ->select([
                'user.merchant_id','reg.appMarket','media_source' => 'LOWER(reg.media_source)','count(user.id) as count',
                'packageName' => "clg.`package_name`",
            ])
            ->from(LoanPerson::tableName() . '  user')
            ->leftJoin(UserRegisterInfo::tableName() . '  reg','reg.user_id = user.id')
            ->leftJoin(ClientInfoLog::tableName() . ' clg','clg.event_id = user.id AND clg.event = '.ClientInfoLog::EVENT_REGISTER)
            ->where(['>=','user.created_at',$startTime])
            ->andWhere(['<','user.created_at',$endTIme])
            ->groupBy(['user.merchant_id','reg.appMarket','media_source','packageName'])
            ->asArray()
            ->all(\Yii::$app->get('db_read_1'));
        return $res;
    }


    //各个认证数（`basic_num`,`contact_num`）
    static function getVerificationNum($startTime,$endTIme,$type){
        $res = UserVerificationLog::find()
            ->select([
                'user.merchant_id','reg.appMarket','media_source' => 'LOWER(reg.media_source)','count(log.id) as count',
                'packageName' => "clg.`package_name`",
                ])
            ->from(UserVerificationLog::tableName() . '  log')
            ->leftJoin(LoanPerson::tableName() . '  user','user.id = log.user_id')
            ->leftJoin(UserRegisterInfo::tableName() . '  reg','reg.user_id = log.user_id')
            ->leftJoin(ClientInfoLog::tableName() . ' clg','clg.event_id = user.id AND clg.event = '.ClientInfoLog::EVENT_REGISTER)
            ->where(['>=','log.created_at',$startTime])
            ->andWhere(['<','log.created_at',$endTIme])
            ->andWhere(['log.type' => $type,'log.status' => UserVerificationLog::STATUS_VERIFY_SUCCESS])
            ->groupBy(['user.merchant_id','reg.appMarket','media_source','packageName'])
            ->asArray()
            ->all(\Yii::$app->get('db_read_1'));
        return $res;
    }

    //下单单数申请总金额（`order_num`,`order_amount`）
    static function getAllOrderNumAndAmount($startTime,$endTIme){
        $res = UserLoanOrder::find()
            ->select([
                'order.merchant_id','reg.appMarket','media_source' => 'LOWER(reg.media_source)','count(order.id) as count,sum(order.amount) as total_amount',
                'packageName' => "IF(order.is_export = 1,substring_index(substring_index(clg.app_market,'_',2),'_',-1),clg.package_name)"
            ])
            ->from(UserLoanOrder::tableName() . '  order')
            ->leftJoin(UserRegisterInfo::tableName() . '  reg','reg.user_id = order.user_id')
            ->leftJoin(ClientInfoLog::tableName() . ' clg','clg.event_id = order.id AND clg.event = '.ClientInfoLog::EVENT_APPLY_ORDER)
            ->where(['>=','order.created_at',$startTime])
            ->andWhere(['<','order.created_at',$endTIme])
            ->groupBy(['order.merchant_id','reg.appMarket','media_source','packageName'])
            ->asArray()
            ->all(\Yii::$app->get('db_read_1'));
        return $res;
    }

    //新户下单单数申请总金额（`order_num`,`order_amount`）
    static function getNewAllOrderNumAndAmount($startTime,$endTIme){
        $res = UserLoanOrder::find()
            ->select([
                'order.merchant_id','reg.appMarket','media_source' => 'LOWER(reg.media_source)','count(order.id) as count,sum(order.amount) as total_amount,count(DISTINCT(order.user_id)) as user_count',
                'packageName' => "IF(order.is_export = 1,substring_index(substring_index(clg.app_market,'_',2),'_',-1),clg.package_name)"
            ])
            ->from(UserLoanOrder::tableName() . '  order')
            ->leftJoin(UserRegisterInfo::tableName() . '  reg','reg.user_id = order.user_id')
            ->leftJoin(ClientInfoLog::tableName() . ' clg','clg.event_id = order.id AND clg.event = '.ClientInfoLog::EVENT_APPLY_ORDER)
            ->where(['order.is_first' => UserLoanOrder::FIRST_LOAN_IS])
            ->andWhere(['>=','order.created_at',$startTime])
            ->andWhere(['<','order.created_at',$endTIme])
            ->groupBy(['order.merchant_id','reg.appMarket','media_source','packageName'])
            ->asArray()
            ->all(\Yii::$app->get('db_read_1'));
        return $res;
    }

    //全平台新户下单单数申请总金额（`order_num`,`order_amount`）
    static function getPlatformNewAllOrderNumAndAmount($startTime,$endTIme){
        $res = UserLoanOrder::find()
            ->select([
                'order.merchant_id','reg.appMarket','media_source' => 'LOWER(reg.media_source)','count(order.id) as count,sum(order.amount) as total_amount,count(DISTINCT(order.user_id)) as user_count',
                'packageName' => "IF(order.is_export = 1,substring_index(substring_index(clg.app_market,'_',2),'_',-1),clg.package_name)"
            ])
            ->from(UserLoanOrder::tableName() . '  order')
            ->leftJoin(UserRegisterInfo::tableName() . '  reg','reg.user_id = order.user_id')
            ->leftJoin(ClientInfoLog::tableName() . ' clg','clg.event_id = order.id AND clg.event = '.ClientInfoLog::EVENT_APPLY_ORDER)
            ->where(['order.is_all_first' => UserLoanOrder::FIRST_LOAN_IS])
            ->andWhere(['>=','order.created_at',$startTime])
            ->andWhere(['<','order.created_at',$endTIme])
            ->groupBy(['order.merchant_id','reg.appMarket','media_source','packageName'])
            ->asArray()
            ->all(\Yii::$app->get('db_read_1'));
        return $res;
    }


    //老户下单单数申请总金额（`order_num`,`order_amount`）
    static function getOldAllOrderNumAndAmount($startTime,$endTIme){
        $res = UserLoanOrder::find()
            ->select([
                'order.merchant_id','reg.appMarket','media_source' => 'LOWER(reg.media_source)','count(order.id) as count,sum(order.amount) as total_amount',
                'packageName' => "IF(order.is_export = 1,substring_index(substring_index(clg.app_market,'_',2),'_',-1),clg.package_name)"
            ])
            ->from(UserLoanOrder::tableName() . '  order')
            ->leftJoin(UserRegisterInfo::tableName() . '  reg','reg.user_id = order.user_id')
            ->leftJoin(ClientInfoLog::tableName() . ' clg','clg.event_id = order.id AND clg.event = '.ClientInfoLog::EVENT_APPLY_ORDER)
            ->where(['order.is_first' => UserLoanOrder::FIRST_LOAN_NO])
            ->andWhere(['>=','order.created_at',$startTime])
            ->andWhere(['<','order.created_at',$endTIme])
            ->groupBy(['order.merchant_id','reg.appMarket','media_source','packageName'])
            ->asArray()
            ->all(\Yii::$app->get('db_read_1'));
        return $res;
    }

    //全平台老户下单单数申请总金额（`order_num`,`order_amount`）
    static function getPlatformOldAllOrderNumAndAmount($startTime,$endTIme){
        $res = UserLoanOrder::find()
            ->select([
                'order.merchant_id','reg.appMarket','media_source' => 'LOWER(reg.media_source)','count(order.id) as count,sum(order.amount) as total_amount',
                'packageName' => "IF(order.is_export = 1,substring_index(substring_index(clg.app_market,'_',2),'_',-1),clg.package_name)"
            ])
            ->from(UserLoanOrder::tableName() . '  order')
            ->leftJoin(UserRegisterInfo::tableName() . '  reg','reg.user_id = order.user_id')
            ->leftJoin(ClientInfoLog::tableName() . ' clg','clg.event_id = order.id AND clg.event = '.ClientInfoLog::EVENT_APPLY_ORDER)
            ->where(['order.is_all_first' => UserLoanOrder::FIRST_LOAN_NO])
            ->andWhere(['>=','order.created_at',$startTime])
            ->andWhere(['<','order.created_at',$endTIme])
            ->groupBy(['order.merchant_id','reg.appMarket','media_source','packageName'])
            ->asArray()
            ->all(\Yii::$app->get('db_read_1'));
        return $res;
    }

    /**
     * @param $before_status
     * @param $after_status
     * @param $startTime
     * @param $endTIme
     * @return array
     */
    //获取下单后续流程的订单数和金额（`audit_pass_order_num`,`audit_pass_order_amount`,
    //`bind_card_pass_order_num`,`bind_card_pass_order_amount`,`loan_success_order_num`,`loan_success_order_amount`）
    static function getOrderNumAndAmountByProcess($before_status, $after_status, $startTime, $endTIme){
        $res = UserLoanOrder::find()
            ->select([
                'order.merchant_id,reg.appMarket,LOWER(reg.media_source) AS media_source,count(order.id) as count,sum(order.amount) as total_amount,count(DISTINCT(order.user_id)) as user_count',
                'packageName' => "IF(order.is_export = 1,substring_index(substring_index(clg.app_market,'_',2),'_',-1),clg.package_name)"
            ])
            ->from(UserLoanOrder::tableName() . '  order')
            ->leftJoin(UserRegisterInfo::tableName() . '  reg','reg.user_id = order.user_id')
            ->leftJoin(UserOrderLoanCheckLog::tableName() . '  log','order.id = log.order_id')
            ->leftJoin(ClientInfoLog::tableName() . ' clg','clg.event_id = order.id AND clg.event = '.ClientInfoLog::EVENT_APPLY_ORDER)
            ->where(['log.before_status' => $before_status, 'log.after_status' => $after_status])
            ->andWhere(['>=','log.created_at',$startTime])
            ->andWhere(['<','log.created_at',$endTIme])
            ->groupBy(['order.merchant_id','reg.appMarket','media_source','packageName'])
            ->asArray()
            ->all(\Yii::$app->get('db_read_1'));
        return $res;
    }

    /**
     * 新客
     * @param $before_status
     * @param $after_status
     * @param $startTime
     * @param $endTIme
     * @return array
     */
    static function getNewOrderNumAndAmountByProcess($before_status, $after_status, $startTime, $endTIme){
        $res = UserLoanOrder::find()
            ->select([
                'order.merchant_id,reg.appMarket,LOWER(reg.media_source) AS media_source,count(order.id) as count,sum(order.amount) as total_amount,count(DISTINCT(order.user_id)) as user_count',
                'packageName' => "IF(order.is_export = 1,substring_index(substring_index(clg.app_market,'_',2),'_',-1),clg.package_name)"
            ])
            ->from(UserLoanOrder::tableName() . '  order')
            ->leftJoin(UserRegisterInfo::tableName() . '  reg','reg.user_id = order.user_id')
            ->leftJoin(UserOrderLoanCheckLog::tableName() . '  log','order.id = log.order_id')
            ->leftJoin(ClientInfoLog::tableName() . ' clg','clg.event_id = order.id AND clg.event = '.ClientInfoLog::EVENT_APPLY_ORDER)
            ->where(['order.is_first' => UserLoanOrder::FIRST_LOAN_IS, 'log.before_status' => $before_status, 'log.after_status' =>$after_status])
            ->andWhere(['>=','log.created_at',$startTime])
            ->andWhere(['<','log.created_at',$endTIme])
            ->groupBy(['order.merchant_id','reg.appMarket','media_source','packageName'])
            ->asArray()
            ->all(\Yii::$app->get('db_read_1'));
        return $res;
    }

    /**
     * 全平台新客
     * @param $before_status
     * @param $after_status
     * @param $startTime
     * @param $endTIme
     * @return array
     */
    static function getPlatformNewOrderNumAndAmountByProcess($before_status, $after_status, $startTime, $endTIme){
        $res = UserLoanOrder::find()
            ->select([
                'order.merchant_id,reg.appMarket,LOWER(reg.media_source) AS media_source,count(order.id) as count,sum(order.amount) as total_amount,count(DISTINCT(order.user_id)) as user_count',
                'packageName' => "IF(order.is_export = 1,substring_index(substring_index(clg.app_market,'_',2),'_',-1),clg.package_name)"
            ])
            ->from(UserLoanOrder::tableName() . '  order')
            ->leftJoin(UserRegisterInfo::tableName() . '  reg','reg.user_id = order.user_id')
            ->leftJoin(UserOrderLoanCheckLog::tableName() . '  log','order.id = log.order_id')
            ->leftJoin(ClientInfoLog::tableName() . ' clg','clg.event_id = order.id AND clg.event = '.ClientInfoLog::EVENT_APPLY_ORDER)
            ->where(['order.is_all_first' => UserLoanOrder::FIRST_LOAN_IS, 'log.before_status' => $before_status, 'log.after_status' =>$after_status])
            ->andWhere(['>=','log.created_at',$startTime])
            ->andWhere(['<','log.created_at',$endTIme])
            ->groupBy(['order.merchant_id','reg.appMarket','media_source','packageName'])
            ->asArray()
            ->all(\Yii::$app->get('db_read_1'));
        return $res;
    }

    /**
     * 老客
     * @param $before_status
     * @param $after_status
     * @param $startTime
     * @param $endTIme
     * @return array
     */
    static function getOldOrderNumAndAmountByProcess($before_status, $after_status, $startTime, $endTIme){
        $res = UserLoanOrder::find()
            ->select([
                'order.merchant_id,reg.appMarket,LOWER(reg.media_source) AS media_source,count(order.id) as count,sum(order.amount) as total_amount',
                'packageName' => "IF(order.is_export = 1,substring_index(substring_index(clg.app_market,'_',2),'_',-1),clg.package_name)"
            ])
            ->from(UserLoanOrder::tableName() . '  order')
            ->leftJoin(UserRegisterInfo::tableName() . '  reg','reg.user_id = order.user_id')
            ->leftJoin(UserOrderLoanCheckLog::tableName() . '  log','order.id = log.order_id')
            ->leftJoin(ClientInfoLog::tableName() . ' clg','clg.event_id = order.id AND clg.event = '.ClientInfoLog::EVENT_APPLY_ORDER)
            ->where(['order.is_first' => UserLoanOrder::FIRST_LOAN_NO, 'log.before_status' => $before_status, 'log.after_status' => $after_status])
            ->andWhere(['>=','log.created_at',$startTime])
            ->andWhere(['<','log.created_at',$endTIme])
            ->groupBy(['order.merchant_id','reg.appMarket','media_source','packageName'])
            ->asArray()
            ->all(\Yii::$app->get('db_read_1'));
        return $res;
    }

    /**
     * 全平台老客
     * @param $before_status
     * @param $after_status
     * @param $startTime
     * @param $endTIme
     * @return array
     */
    static function getPlatformOldOrderNumAndAmountByProcess($before_status, $after_status, $startTime, $endTIme){
        $res = UserLoanOrder::find()
            ->select([
                'order.merchant_id,reg.appMarket,LOWER(reg.media_source) AS media_source,count(order.id) as count,sum(order.amount) as total_amount',
                'packageName' => "IF(order.is_export = 1,substring_index(substring_index(clg.app_market,'_',2),'_',-1),clg.package_name)"
            ])
            ->from(UserLoanOrder::tableName() . '  order')
            ->leftJoin(UserRegisterInfo::tableName() . '  reg','reg.user_id = order.user_id')
            ->leftJoin(UserOrderLoanCheckLog::tableName() . '  log','order.id = log.order_id')
            ->leftJoin(ClientInfoLog::tableName() . ' clg','clg.event_id = order.id AND clg.event = '.ClientInfoLog::EVENT_APPLY_ORDER)
            ->where(['order.is_all_first' => UserLoanOrder::FIRST_LOAN_NO, 'log.before_status' => $before_status, 'log.after_status' => $after_status])
            ->andWhere(['>=','log.created_at',$startTime])
            ->andWhere(['<','log.created_at',$endTIme])
            ->groupBy(['order.merchant_id','reg.appMarket','media_source','packageName'])
            ->asArray()
            ->all(\Yii::$app->get('db_read_1'));
        return $res;
    }

    /**
     * 审核且绑卡通过
     * @param $startTime
     * @param $endTIme
     * @return array
     */
    static function getOrderBindBankPass($startTime, $endTIme){
        $res = UserLoanOrder::find()
            ->select([
                'order.merchant_id,reg.appMarket,LOWER(reg.media_source) AS media_source,count(order.id) as count,sum(order.amount) as total_amount',
                'packageName' => "IF(order.is_export = 1,substring_index(substring_index(clg.app_market,'_',2),'_',-1),clg.package_name)"
            ])
            ->from(UserLoanOrder::tableName() . '  order')
            ->leftJoin(UserRegisterInfo::tableName() . '  reg','reg.user_id = order.user_id')
            ->leftJoin(UserBankAccount::tableName() . '  bank','bank.id = order.card_id')
            ->leftJoin(UserOrderLoanCheckLog::tableName() . '  log','order.id = log.order_id')
            ->leftJoin(ClientInfoLog::tableName() . ' clg','clg.event_id = order.id AND clg.event = '.ClientInfoLog::EVENT_APPLY_ORDER)
            ->where(['bank.status' => UserBankAccount::STATUS_SUCCESS,
                     'log.before_status' => self::$processList['audit_pass_order'][0],
                     'log.after_status' => self::$processList['audit_pass_order'][1]])
            ->andWhere(['>=','log.created_at',$startTime])
            ->andWhere(['<','log.created_at',$endTIme])
            ->groupBy(['order.merchant_id','reg.appMarket','media_source','packageName'])
            ->asArray()
            ->all(\Yii::$app->get('db_read_1'));
        return $res;
    }

    /**
     * 新户审核且绑卡通过
     * @param $startTime
     * @param $endTIme
     * @return array
     */
    static function getNewOrderBindBankPass($startTime, $endTIme){
        $res = UserLoanOrder::find()
            ->select([
                'order.merchant_id,reg.appMarket,LOWER(reg.media_source) AS media_source,count(order.id) as count,sum(order.amount) as total_amount,count(DISTINCT(order.user_id)) as user_count',
                'packageName' => "IF(order.is_export = 1,substring_index(substring_index(clg.app_market,'_',2),'_',-1),clg.package_name)"
            ])
            ->from(UserLoanOrder::tableName() . '  order')
            ->leftJoin(UserRegisterInfo::tableName() . '  reg','reg.user_id = order.user_id')
            ->leftJoin(UserBankAccount::tableName() . '  bank','bank.id = order.card_id')
            ->leftJoin(UserOrderLoanCheckLog::tableName() . '  log','order.id = log.order_id')
            ->leftJoin(ClientInfoLog::tableName() . ' clg','clg.event_id = order.id AND clg.event = '.ClientInfoLog::EVENT_APPLY_ORDER)
            ->where(['bank.status' => UserBankAccount::STATUS_SUCCESS,
                     'order.is_first' => UserLoanOrder::FIRST_LOAN_IS,
                     'log.before_status' => self::$processList['audit_pass_order'][0],
                     'log.after_status' => self::$processList['audit_pass_order'][1]])
            ->andWhere(['>=','log.created_at',$startTime])
            ->andWhere(['<','log.created_at',$endTIme])
            ->groupBy(['order.merchant_id','reg.appMarket','media_source','packageName'])
            ->asArray()
            ->all(\Yii::$app->get('db_read_1'));
        return $res;
    }

    /**
     * 全平台新户审核且绑卡通过
     * @param $startTime
     * @param $endTIme
     * @return array
     */
    static function getPlatformNewOrderBindBankPass($startTime, $endTIme){
        $res = UserLoanOrder::find()
            ->select([
                'order.merchant_id,reg.appMarket,LOWER(reg.media_source) AS media_source,count(order.id) as count,sum(order.amount) as total_amount,count(DISTINCT(order.user_id)) as user_count',
                'packageName' => "IF(order.is_export = 1,substring_index(substring_index(clg.app_market,'_',2),'_',-1),clg.package_name)"
            ])
            ->from(UserLoanOrder::tableName() . '  order')
            ->leftJoin(UserRegisterInfo::tableName() . '  reg','reg.user_id = order.user_id')
            ->leftJoin(UserBankAccount::tableName() . '  bank','bank.id = order.card_id')
            ->leftJoin(UserOrderLoanCheckLog::tableName() . '  log','order.id = log.order_id')
            ->leftJoin(ClientInfoLog::tableName() . ' clg','clg.event_id = order.id AND clg.event = '.ClientInfoLog::EVENT_APPLY_ORDER)
            ->where(['bank.status' => UserBankAccount::STATUS_SUCCESS,
                     'order.is_all_first' => UserLoanOrder::FIRST_LOAN_IS,
                     'log.before_status' => self::$processList['audit_pass_order'][0],
                     'log.after_status' => self::$processList['audit_pass_order'][1]])
            ->andWhere(['>=','log.created_at',$startTime])
            ->andWhere(['<','log.created_at',$endTIme])
            ->groupBy(['order.merchant_id','reg.appMarket','media_source','packageName'])
            ->asArray()
            ->all(\Yii::$app->get('db_read_1'));
        return $res;
    }

    /**
     * 老户审核且绑卡通过
     * @param $startTime
     * @param $endTIme
     * @return array
     */
    static function getOldOrderBindBankPass($startTime, $endTIme){
        $res = UserLoanOrder::find()
            ->select([
                'order.merchant_id,reg.appMarket,LOWER(reg.media_source) AS media_source,count(order.id) as count,sum(order.amount) as total_amount',
                'packageName' => "IF(order.is_export = 1,substring_index(substring_index(clg.app_market,'_',2),'_',-1),clg.package_name)"
            ])
            ->from(UserLoanOrder::tableName() . '  order')
            ->leftJoin(UserRegisterInfo::tableName() . '  reg','reg.user_id = order.user_id')
            ->leftJoin(UserBankAccount::tableName() . '  bank','bank.id = order.card_id')
            ->leftJoin(UserOrderLoanCheckLog::tableName() . '  log','order.id = log.order_id')
            ->leftJoin(ClientInfoLog::tableName() . ' clg','clg.event_id = order.id AND clg.event = '.ClientInfoLog::EVENT_APPLY_ORDER)
            ->where(['bank.status' => UserBankAccount::STATUS_SUCCESS,'order.is_first' => UserLoanOrder::FIRST_LOAN_NO,
                     'log.before_status' => self::$processList['audit_pass_order'][0],
                     'log.after_status' => self::$processList['audit_pass_order'][1]])
            ->andWhere(['>=','log.created_at',$startTime])
            ->andWhere(['<','log.created_at',$endTIme])
            ->groupBy(['order.merchant_id','reg.appMarket','media_source','packageName'])
            ->asArray()
            ->all(\Yii::$app->get('db_read_1'));
        return $res;
    }

    /**
     * 全平台老户审核且绑卡通过
     * @param $startTime
     * @param $endTIme
     * @return array
     */
    static function getPlatformOldOrderBindBankPass($startTime, $endTIme){
        $res = UserLoanOrder::find()
            ->select([
                'order.merchant_id,reg.appMarket,LOWER(reg.media_source) AS media_source,count(order.id) as count,sum(order.amount) as total_amount',
                'packageName' => "IF(order.is_export = 1,substring_index(substring_index(clg.app_market,'_',2),'_',-1),clg.package_name)"
            ])
            ->from(UserLoanOrder::tableName() . '  order')
            ->leftJoin(UserRegisterInfo::tableName() . '  reg','reg.user_id = order.user_id')
            ->leftJoin(UserBankAccount::tableName() . '  bank','bank.id = order.card_id')
            ->leftJoin(UserOrderLoanCheckLog::tableName() . '  log','order.id = log.order_id')
            ->leftJoin(ClientInfoLog::tableName() . ' clg','clg.event_id = order.id AND clg.event = '.ClientInfoLog::EVENT_APPLY_ORDER)
            ->where(['bank.status' => UserBankAccount::STATUS_SUCCESS,'order.is_all_first' => UserLoanOrder::FIRST_LOAN_NO,
                     'log.before_status' => self::$processList['audit_pass_order'][0],
                     'log.after_status' => self::$processList['audit_pass_order'][1]])
            ->andWhere(['>=','log.created_at',$startTime])
            ->andWhere(['<','log.created_at',$endTIme])
            ->groupBy(['order.merchant_id','reg.appMarket','media_source','packageName'])
            ->asArray()
            ->all(\Yii::$app->get('db_read_1'));
        return $res;
    }

    /**
     * @name 放款成功
     * @param $startTime
     * @param $endTIme
     * @return array
     */
    static function getLoanSuccessNumAndAmount($startTime, $endTIme){
        $res = UserLoanOrderRepayment::find()
            ->select([
                'repayment.merchant_id','reg.appMarket','media_source' => 'LOWER(reg.media_source)','count(repayment.id) as count,sum(repayment.principal-repayment.cost_fee) as total_amount',
                'packageName' => "IF(order.is_export = 1,substring_index(substring_index(clg.app_market,'_',2),'_',-1),clg.package_name)"
            ])
            ->from(UserLoanOrderRepayment::tableName() . '  repayment')
            ->leftJoin(UserLoanOrder::tableName(). ' order','repayment.order_id = order.id')
            ->leftJoin(UserRegisterInfo::tableName() . '  reg','reg.user_id = repayment.user_id')
            ->leftJoin(ClientInfoLog::tableName() . ' clg','clg.event_id = repayment.order_id AND clg.event = '.ClientInfoLog::EVENT_APPLY_ORDER)
            ->where(['>=','repayment.loan_time',$startTime])
            ->andWhere(['<','repayment.loan_time',$endTIme])
            ->groupBy(['repayment.merchant_id','reg.appMarket','media_source','packageName'])
            ->asArray()
            ->all(\Yii::$app->get('db_read_1'));
        return $res;
    }

    /**
     * @name 新用户放款成功
     * @param $startTime
     * @param $endTIme
     * @return array
     */
    static function getNewLoanSuccessNumAndAmount($startTime, $endTIme){
        $res = UserLoanOrderRepayment::find()
            ->select([
                'repayment.merchant_id','reg.appMarket','media_source' => 'LOWER(reg.media_source)','count(repayment.id) as count,sum(repayment.principal-repayment.cost_fee) as total_amount',
                'packageName' => "IF(order.is_export = 1,substring_index(substring_index(clg.app_market,'_',2),'_',-1),clg.package_name)"
            ])
            ->from(UserLoanOrderRepayment::tableName() . '  repayment')
            ->leftJoin(UserLoanOrder::tableName() . '  order','order.id = repayment.order_id')
            ->leftJoin(UserRegisterInfo::tableName() . '  reg','reg.user_id = repayment.user_id')
            ->leftJoin(ClientInfoLog::tableName() . ' clg','clg.event_id = repayment.order_id AND clg.event = '.ClientInfoLog::EVENT_APPLY_ORDER)
            ->where(['order.is_first' => UserLoanOrder::FIRST_LOAN_IS])
            ->andWhere(['>=','repayment.loan_time',$startTime])
            ->andWhere(['<','repayment.loan_time',$endTIme])
            ->groupBy(['repayment.merchant_id','reg.appMarket','media_source','packageName'])
            ->asArray()
            ->all(\Yii::$app->get('db_read_1'));
        return $res;
    }

    /**
     * @name 全平台新用户放款成功
     * @param $startTime
     * @param $endTIme
     * @return array
     */
    static function getPlatformNewLoanSuccessNumAndAmount($startTime, $endTIme){
        $res = UserLoanOrderRepayment::find()
            ->select([
                'repayment.merchant_id','reg.appMarket','media_source' => 'LOWER(reg.media_source)','count(repayment.id) as count,sum(repayment.principal-repayment.cost_fee) as total_amount',
                'packageName' => "IF(order.is_export = 1,substring_index(substring_index(clg.app_market,'_',2),'_',-1),clg.package_name)"
            ])
            ->from(UserLoanOrderRepayment::tableName() . '  repayment')
            ->leftJoin(UserLoanOrder::tableName() . '  order','order.id = repayment.order_id')
            ->leftJoin(UserRegisterInfo::tableName() . '  reg','reg.user_id = repayment.user_id')
            ->leftJoin(ClientInfoLog::tableName() . ' clg','clg.event_id = repayment.order_id AND clg.event = '.ClientInfoLog::EVENT_APPLY_ORDER)
            ->where(['order.is_all_first' => UserLoanOrder::FIRST_LOAN_IS])
            ->andWhere(['>=','repayment.loan_time',$startTime])
            ->andWhere(['<','repayment.loan_time',$endTIme])
            ->groupBy(['repayment.merchant_id','reg.appMarket','media_source','packageName'])
            ->asArray()
            ->all(\Yii::$app->get('db_read_1'));
        return $res;
    }

    /**
     * @name 老客放款成功
     * @param $startTime
     * @param $endTIme
     * @return array
     */
    static function getOldLoanSuccessNumAndAmount($startTime, $endTIme){
        $res = UserLoanOrderRepayment::find()
            ->select([
                'repayment.merchant_id','reg.appMarket','media_source' => 'LOWER(reg.media_source)','count(repayment.id) as count,sum(repayment.principal-repayment.cost_fee) as total_amount',
                'packageName' => "IF(order.is_export = 1,substring_index(substring_index(clg.app_market,'_',2),'_',-1),clg.package_name)"
            ])
            ->from(UserLoanOrderRepayment::tableName() . '  repayment')
            ->leftJoin(UserLoanOrder::tableName() . '  order','order.id = repayment.order_id')
            ->leftJoin(UserRegisterInfo::tableName() . '  reg','reg.user_id = repayment.user_id')
            ->leftJoin(ClientInfoLog::tableName() . ' clg','clg.event_id = repayment.order_id AND clg.event = '.ClientInfoLog::EVENT_APPLY_ORDER)
            ->where(['order.is_first' => UserLoanOrder::FIRST_LOAN_NO])
            ->andWhere(['>=','repayment.loan_time',$startTime])
            ->andWhere(['<','repayment.loan_time',$endTIme])
            ->groupBy(['repayment.merchant_id','reg.appMarket','media_source','packageName'])
            ->asArray()
            ->all(\Yii::$app->get('db_read_1'));
        return $res;
    }

    /**
     * @name 全平台老客放款成功
     * @param $startTime
     * @param $endTIme
     * @return array
     */
    static function getPlatformOldLoanSuccessNumAndAmount($startTime, $endTIme){
        $res = UserLoanOrderRepayment::find()
            ->select([
                'repayment.merchant_id','reg.appMarket','media_source' => 'LOWER(reg.media_source)','count(repayment.id) as count,sum(repayment.principal-repayment.cost_fee) as total_amount',
                'packageName' => "IF(order.is_export = 1,substring_index(substring_index(clg.app_market,'_',2),'_',-1),clg.package_name)"
            ])
            ->from(UserLoanOrderRepayment::tableName() . '  repayment')
            ->leftJoin(UserLoanOrder::tableName() . '  order','order.id = repayment.order_id')
            ->leftJoin(UserRegisterInfo::tableName() . '  reg','reg.user_id = repayment.user_id')
            ->leftJoin(ClientInfoLog::tableName() . ' clg','clg.event_id = repayment.order_id AND clg.event = '.ClientInfoLog::EVENT_APPLY_ORDER)
            ->where(['order.is_all_first' => UserLoanOrder::FIRST_LOAN_NO])
            ->andWhere(['>=','repayment.loan_time',$startTime])
            ->andWhere(['<','repayment.loan_time',$endTIme])
            ->groupBy(['repayment.merchant_id','reg.appMarket','media_source','packageName'])
            ->asArray()
            ->all(\Yii::$app->get('db_read_1'));
        return $res;
    }

    //组合数据返回
    static function getDailyUserDataFull($leftTime, $rightTime){
        $arr = [];
        //注册
        $res = DailyUserDataFullService::getNewUserNum($leftTime,$rightTime);
        foreach ($res as $v){
            if(empty($v['packageName'])){
                continue;
            }
            $arr[$v['merchant_id']][$v['appMarket']][$v['media_source']][$v['packageName']]['reg_num'] = $v['count'];
        }
        //认证

        foreach (DailyUserDataFullService::processClientInfoLogEvent() as $key => $item){
            $res = DailyUserDataFullService::getVerificationNum($leftTime,$rightTime,$item);
            foreach ($res as $v){
                if(empty($v['packageName'])){
                    continue;
                }
                $arr[$v['merchant_id']][$v['appMarket']][$v['media_source']][$v['packageName']][$key] = $v['count'];
            }
        }
        //订单
        foreach (DailyUserDataFullService::$processList as $key => $item){
            if($item){
                $res = DailyUserDataFullService::getOrderNumAndAmountByProcess($item[0],$item[1],$leftTime,$rightTime);
                foreach ($res as $v){
                    $arr[$v['merchant_id']][$v['appMarket']][$v['media_source']][$v['packageName']][$key.'_num'] = $v['count'];
                    $arr[$v['merchant_id']][$v['appMarket']][$v['media_source']][$v['packageName']][$key.'_amount'] = $v['total_amount'];
                    //audit_pass_order   withdraw_success_order
                    $arr[$v['merchant_id']][$v['appMarket']][$v['media_source']][$v['packageName']][$key.'_user_num'] = $v['user_count'];
                }
                $res = DailyUserDataFullService::getNewOrderNumAndAmountByProcess($item[0],$item[1],$leftTime,$rightTime);
                foreach ($res as $v){
                    $arr[$v['merchant_id']][$v['appMarket']][$v['media_source']][$v['packageName']]['new_'.$key.'_num'] = $v['count'];
                    $arr[$v['merchant_id']][$v['appMarket']][$v['media_source']][$v['packageName']]['new_'.$key.'_amount'] = $v['total_amount'];
                    $arr[$v['merchant_id']][$v['appMarket']][$v['media_source']][$v['packageName']]['new_'.$key.'_user_num'] = $v['user_count'];
                }
                $res = DailyUserDataFullService::getPlatformNewOrderNumAndAmountByProcess($item[0],$item[1],$leftTime,$rightTime);
                foreach ($res as $v){
                    $arr[$v['merchant_id']][$v['appMarket']][$v['media_source']][$v['packageName']]['platform_new_'.$key.'_num'] = $v['count'];
                    $arr[$v['merchant_id']][$v['appMarket']][$v['media_source']][$v['packageName']]['platform_new_'.$key.'_amount'] = $v['total_amount'];
                    $arr[$v['merchant_id']][$v['appMarket']][$v['media_source']][$v['packageName']]['platform_new_'.$key.'_user_num'] = $v['user_count'];
                }
                $res = DailyUserDataFullService::getOldOrderNumAndAmountByProcess($item[0],$item[1],$leftTime,$rightTime);
                foreach ($res as $v){
                    $arr[$v['merchant_id']][$v['appMarket']][$v['media_source']][$v['packageName']]['old_'.$key.'_num'] = $v['count'];
                    $arr[$v['merchant_id']][$v['appMarket']][$v['media_source']][$v['packageName']]['old_'.$key.'_amount'] = $v['total_amount'];
                }
                $res = DailyUserDataFullService::getPlatformOldOrderNumAndAmountByProcess($item[0],$item[1],$leftTime,$rightTime);
                foreach ($res as $v){
                    $arr[$v['merchant_id']][$v['appMarket']][$v['media_source']][$v['packageName']]['platform_old_'.$key.'_num'] = $v['count'];
                    $arr[$v['merchant_id']][$v['appMarket']][$v['media_source']][$v['packageName']]['platform_old_'.$key.'_amount'] = $v['total_amount'];
                }
            }else{
                $res = DailyUserDataFullService::getAllOrderNumAndAmount($leftTime,$rightTime);
                foreach ($res as $v){
                    $arr[$v['merchant_id']][$v['appMarket']][$v['media_source']][$v['packageName']][$key.'_num'] = $v['count'];
                    $arr[$v['merchant_id']][$v['appMarket']][$v['media_source']][$v['packageName']][$key.'_amount'] = $v['total_amount'];
                }
                $res = DailyUserDataFullService::getNewAllOrderNumAndAmount($leftTime,$rightTime);
                foreach ($res as $v){
                    $arr[$v['merchant_id']][$v['appMarket']][$v['media_source']][$v['packageName']]['new_' . $key . '_num'] = $v['count'];
                    $arr[$v['merchant_id']][$v['appMarket']][$v['media_source']][$v['packageName']]['new_' . $key . '_amount'] = $v['total_amount'];
                    $arr[$v['merchant_id']][$v['appMarket']][$v['media_source']][$v['packageName']]['new_' . $key . '_user_num'] = $v['user_count'];
                }
                $res = DailyUserDataFullService::getPlatformNewAllOrderNumAndAmount($leftTime,$rightTime);
                foreach ($res as $v){
                    $arr[$v['merchant_id']][$v['appMarket']][$v['media_source']][$v['packageName']]['platform_new_' . $key . '_num'] = $v['count'];
                    $arr[$v['merchant_id']][$v['appMarket']][$v['media_source']][$v['packageName']]['platform_new_' . $key . '_amount'] = $v['total_amount'];
                    $arr[$v['merchant_id']][$v['appMarket']][$v['media_source']][$v['packageName']]['platform_new_' . $key . '_user_num'] = $v['user_count'];
                }
                $res = DailyUserDataFullService::getOldAllOrderNumAndAmount($leftTime,$rightTime);
                foreach ($res as $v){
                    $arr[$v['merchant_id']][$v['appMarket']][$v['media_source']][$v['packageName']]['old_' . $key . '_num'] = $v['count'];
                    $arr[$v['merchant_id']][$v['appMarket']][$v['media_source']][$v['packageName']]['old_' . $key . '_amount'] = $v['total_amount'];
                }
                $res = DailyUserDataFullService::getPlatformOldAllOrderNumAndAmount($leftTime,$rightTime);
                foreach ($res as $v){
                    $arr[$v['merchant_id']][$v['appMarket']][$v['media_source']][$v['packageName']]['platform_old_' . $key . '_num'] = $v['count'];
                    $arr[$v['merchant_id']][$v['appMarket']][$v['media_source']][$v['packageName']]['platform_old_' . $key . '_amount'] = $v['total_amount'];
                }
            }
        }
        //绑卡订单数
        $res = DailyUserDataFullService::getOrderBindBankPass($leftTime,$rightTime);
        foreach ($res as $v){
            $arr[$v['merchant_id']][$v['appMarket']][$v['media_source']][$v['packageName']]['bind_card_pass_order_num'] = $v['count'];
            $arr[$v['merchant_id']][$v['appMarket']][$v['media_source']][$v['packageName']]['bind_card_pass_order_amount'] = $v['total_amount'];
        }
        $res = DailyUserDataFullService::getNewOrderBindBankPass($leftTime,$rightTime);
        foreach ($res as $v){
            $arr[$v['merchant_id']][$v['appMarket']][$v['media_source']][$v['packageName']]['new_bind_card_pass_order_num'] = $v['count'];
            $arr[$v['merchant_id']][$v['appMarket']][$v['media_source']][$v['packageName']]['new_bind_card_pass_order_amount'] = $v['total_amount'];
            $arr[$v['merchant_id']][$v['appMarket']][$v['media_source']][$v['packageName']]['new_bind_card_pass_order_user_num'] = $v['user_count'];
        }
        $res = DailyUserDataFullService::getPlatformNewOrderBindBankPass($leftTime,$rightTime);
        foreach ($res as $v){
            $arr[$v['merchant_id']][$v['appMarket']][$v['media_source']][$v['packageName']]['platform_new_bind_card_pass_order_num'] = $v['count'];
            $arr[$v['merchant_id']][$v['appMarket']][$v['media_source']][$v['packageName']]['platform_new_bind_card_pass_order_amount'] = $v['total_amount'];
            $arr[$v['merchant_id']][$v['appMarket']][$v['media_source']][$v['packageName']]['platform_new_bind_card_pass_order_user_num'] = $v['user_count'];
        }
        $res = DailyUserDataFullService::getOldOrderBindBankPass($leftTime,$rightTime);
        foreach ($res as $v){
            $arr[$v['merchant_id']][$v['appMarket']][$v['media_source']][$v['packageName']]['old_bind_card_pass_order_num'] = $v['count'];
            $arr[$v['merchant_id']][$v['appMarket']][$v['media_source']][$v['packageName']]['old_bind_card_pass_order_amount'] = $v['total_amount'];
        }
        $res = DailyUserDataFullService::getPlatformOldOrderBindBankPass($leftTime,$rightTime);
        foreach ($res as $v){
            $arr[$v['merchant_id']][$v['appMarket']][$v['media_source']][$v['packageName']]['platform_old_bind_card_pass_order_num'] = $v['count'];
            $arr[$v['merchant_id']][$v['appMarket']][$v['media_source']][$v['packageName']]['platform_old_bind_card_pass_order_amount'] = $v['total_amount'];
        }

        //放款
        $res = DailyUserDataFullService::getLoanSuccessNumAndAmount($leftTime,$rightTime);
        foreach ($res as $v){
            $arr[$v['merchant_id']][$v['appMarket']][$v['media_source']][$v['packageName']]['loan_success_order_num'] = $v['count'];
            $arr[$v['merchant_id']][$v['appMarket']][$v['media_source']][$v['packageName']]['loan_success_order_amount'] = $v['total_amount'];
        }
        $res = DailyUserDataFullService::getNewLoanSuccessNumAndAmount($leftTime,$rightTime);
        foreach ($res as $v){
            $arr[$v['merchant_id']][$v['appMarket']][$v['media_source']][$v['packageName']]['new_loan_success_order_num'] = $v['count'];
            $arr[$v['merchant_id']][$v['appMarket']][$v['media_source']][$v['packageName']]['new_loan_success_order_amount'] = $v['total_amount'];
        }
        $res = DailyUserDataFullService::getPlatformNewLoanSuccessNumAndAmount($leftTime,$rightTime);
        foreach ($res as $v){
            $arr[$v['merchant_id']][$v['appMarket']][$v['media_source']][$v['packageName']]['platform_new_loan_success_order_num'] = $v['count'];
            $arr[$v['merchant_id']][$v['appMarket']][$v['media_source']][$v['packageName']]['platform_new_loan_success_order_amount'] = $v['total_amount'];
        }
        $res = DailyUserDataFullService::getOldLoanSuccessNumAndAmount($leftTime,$rightTime);
        foreach ($res as $v){
            $arr[$v['merchant_id']][$v['appMarket']][$v['media_source']][$v['packageName']]['old_loan_success_order_num'] = $v['count'];
            $arr[$v['merchant_id']][$v['appMarket']][$v['media_source']][$v['packageName']]['old_loan_success_order_amount'] = $v['total_amount'];
        }
        $res = DailyUserDataFullService::getPlatformOldLoanSuccessNumAndAmount($leftTime,$rightTime);
        foreach ($res as $v){
            $arr[$v['merchant_id']][$v['appMarket']][$v['media_source']][$v['packageName']]['platform_old_loan_success_order_num'] = $v['count'];
            $arr[$v['merchant_id']][$v['appMarket']][$v['media_source']][$v['packageName']]['platform_old_loan_success_order_amount'] = $v['total_amount'];
        }
        return $arr;
    }
}
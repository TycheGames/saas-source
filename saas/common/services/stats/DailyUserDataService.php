<?php
/**
 * Created by loan
 * User: wangpeng
 * Date: 2019/7/5 0005
 * Time：17:05
 */


namespace common\services\stats;

use common\models\order\UserLoanOrder;
use common\models\order\UserLoanOrderRepayment;
use common\models\order\UserOrderLoanCheckLog;
use common\models\user\LoanPerson;
use common\models\user\UserBankAccount;
use common\models\user\UserRegisterInfo;
use common\models\user\UserVerification;
use common\models\user\UserVerificationLog;
use yii\db\Query;

class DailyUserDataService
{
    public static $processVerificationList = [
        'basic_num'      => UserVerification::TYPE_BASIC,
        'identity_num'   => UserVerification::TYPE_VERIFY,
        'contact_num'    => UserVerification::TYPE_CONTACT,
    ];

    public static $processList = [
        'order'            => [],
        'audit_pass_order' => [UserLoanOrder::STATUS_CHECK,[UserLoanOrder::STATUS_LOANING,UserLoanOrder::STATUS_WAIT_DEPOSIT,UserLoanOrder::STATUS_WAIT_DRAW_MONEY]],
    ];

    //总新注册用户数 （`reg_num`）
    static function getNewUserNum($startTime,$endTIme){
        $res = LoanPerson::find()
            ->select(['user.merchant_id','reg.appMarket','count(user.id) as count'])
            ->from(LoanPerson::tableName() . '  user')
            ->leftJoin(UserRegisterInfo::tableName() . '  reg','reg.user_id = user.id')
            ->where(['>=','user.created_at',$startTime])
            ->andWhere(['<','user.created_at',$endTIme])
            ->groupBy(['user.merchant_id','reg.appMarket'])
            ->asArray()
            ->all(\Yii::$app->get('db_read_1'));
        return $res;
    }


    //总新注册各个认证数（`identity_num`,`basic_num`,`work_num`,`contact_num`,`tax_bill_num`,`credit_report_num`）
    static function getNewUserVerificationNum($startTime,$endTIme,$type){
        $res = UserVerificationLog::find()
            ->select(['user.merchant_id','reg.appMarket','count(log.id) as count'])
            ->from(UserVerificationLog::tableName() . '  log')
            ->leftJoin(LoanPerson::tableName() . '  user','user.id = log.user_id')
            ->leftJoin(UserRegisterInfo::tableName() . '  reg','reg.user_id = log.user_id')
            ->where(['>=','user.created_at',$startTime])
            ->andWhere(['<','user.created_at',$endTIme])
            ->andWhere(['>=','log.created_at',$startTime])
            ->andWhere(['<','log.created_at',$endTIme])
            ->andWhere(['log.type' => $type,'log.status' => UserVerificationLog::STATUS_VERIFY_SUCCESS])
            ->groupBy(['user.merchant_id','reg.appMarket'])
            ->asArray()
            ->all(\Yii::$app->get('db_read_1'));
        return $res;
    }

    //总新注册用户数下单单数申请总金额（`order_num`,`order_amount`）
    static function getNewUserAllOrderNumAndAmount($startTime,$endTIme){
        $res = UserLoanOrder::find()
            ->select(['user.merchant_id','reg.appMarket','count(order.id) as count,sum(order.amount) as total_amount'])
            ->from(UserLoanOrder::tableName() . '  order')
            ->leftJoin(LoanPerson::tableName() . '  user','user.id = order.user_id')
            ->leftJoin(UserRegisterInfo::tableName() . '  reg','reg.user_id = user.id')
            ->where(['>=','user.created_at',$startTime])
            ->andWhere(['<','user.created_at',$endTIme])
            ->andWhere(['>=','order.created_at',$startTime])
            ->andWhere(['<','order.created_at',$endTIme])
            ->groupBy(['user.merchant_id','reg.appMarket'])
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
     * @throws \yii\db\Exception
     */
    //获取下单后续流程的订单数和金额（`audit_pass_order_num`,`audit_pass_order_amount`,
    //`bind_card_pass_order_num`,`bind_card_pass_order_amount`,`loan_success_order_num`,`loan_success_order_amount`）
    static function getNewUserOrderNumAndAmountByProcess($before_status, $after_status, $startTime, $endTIme){
        $res = UserLoanOrder::find()
            ->select('user.merchant_id,appMarket,count(order.id) as count,sum(order.amount) as total_amount')
            ->from(UserLoanOrder::tableName() . '  order')
            ->leftJoin(LoanPerson::tableName() . '  user','user.id = order.user_id')
            ->leftJoin(UserRegisterInfo::tableName() . '  reg','reg.user_id = user.id')
            ->leftJoin(UserOrderLoanCheckLog::tableName() . '  log','order.id = log.order_id')
            ->where(['log.before_status' => $before_status, 'log.after_status' => $after_status])
            ->andWhere(['>=','order.created_at',$startTime])
            ->andWhere(['<','order.created_at',$endTIme])
            ->andWhere(['>=','user.created_at',$startTime])
            ->andWhere(['<','user.created_at',$endTIme])
            ->andWhere(['>=','log.created_at',$startTime])
            ->andWhere(['<','log.created_at',$endTIme])
            ->groupBy(['user.merchant_id','reg.appMarket'])
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
    static function getNewUserOrderBindBankPass($startTime, $endTIme){
        $res = UserLoanOrder::find()
            ->select('user.merchant_id,reg.appMarket,count(order.id) as count,sum(order.amount) as total_amount')
            ->from(UserLoanOrder::tableName() . '  order')
            ->leftJoin(LoanPerson::tableName() . '  user','user.id = order.user_id')
            ->leftJoin(UserRegisterInfo::tableName() . '  reg','reg.user_id = user.id')
            ->leftJoin(UserBankAccount::tableName() . '  bank','bank.id = order.card_id')
            ->leftJoin(UserOrderLoanCheckLog::tableName() . '  log','order.id = log.order_id')
            ->where([
                'bank.status' => UserBankAccount::STATUS_SUCCESS,
                'log.before_status' => UserLoanOrder::STATUS_CHECK,
                'log.after_status' => [UserLoanOrder::STATUS_LOANING,UserLoanOrder::STATUS_WAIT_DEPOSIT,UserLoanOrder::STATUS_WAIT_DRAW_MONEY]
            ])
            ->andWhere(['>=','order.created_at',$startTime])
            ->andWhere(['<','order.created_at',$endTIme])
            ->andWhere(['>=','user.created_at',$startTime])
            ->andWhere(['<','user.created_at',$endTIme])
            ->andWhere(['>=','bank.updated_at',$startTime])
            ->andWhere(['<','bank.updated_at',$endTIme])
            ->andWhere(['>=','log.created_at',$startTime])
            ->andWhere(['<','log.created_at',$endTIme])
            ->groupBy(['user.merchant_id','reg.appMarket'])
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
    static function getNewUserAllLoanSuccessNumAndAmount($startTime,$endTIme){
        $res = UserLoanOrderRepayment::find()
            ->select(['user.merchant_id','reg.appMarket','count(repayment.id) as count,sum(repayment.principal-repayment.cost_fee) as total_amount'])
            ->from(UserLoanOrderRepayment::tableName() . '  repayment')
            ->leftJoin(LoanPerson::tableName() . '  user','user.id = repayment.user_id')
            ->leftJoin(UserRegisterInfo::tableName() . '  reg','reg.user_id = user.id')
            ->where(['>=','user.created_at',$startTime])
            ->andWhere(['<','user.created_at',$endTIme])
            ->andWhere(['>=','repayment.loan_time',$startTime])
            ->andWhere(['<','repayment.loan_time',$endTIme])
            ->groupBy(['user.merchant_id','reg.appMarket'])
            ->asArray()
            ->all(\Yii::$app->get('db_read_1'));
        return $res;
    }


    //组合数据返回
    static function getDailyUserData($leftTime, $rightTime){
        $arr = [];
        //新注册
        $res = self::getNewUserNum($leftTime,$rightTime);
        foreach ($res as $v){
            $arr[$v['merchant_id']][$v['appMarket']]['reg_num'] = $v['count'];
        }

        //新用户认证
        foreach (self::$processVerificationList as $key => $item){
            $res = self::getNewUserVerificationNum($leftTime,$rightTime,$item);
            foreach ($res as $v){
                $arr[$v['merchant_id']][$v['appMarket']][$key] = $v['count'];
            }
        }
        //新用户下订单
        foreach (self::$processList as $key => $item){
            if($item){
                $res = self::getNewUserOrderNumAndAmountByProcess($item[0],$item[1],$leftTime,$rightTime);
            }else{
                $res = self::getNewUserAllOrderNumAndAmount($leftTime,$rightTime);
            }

            foreach ($res as $v){
                $arr[$v['merchant_id']][$v['appMarket']][$key.'_num'] = $v['count'];
                $arr[$v['merchant_id']][$v['appMarket']][$key.'_amount'] = $v['total_amount'];
            }
        }

        //新用户绑卡
        $res = self::getNewUserOrderBindBankPass($leftTime,$rightTime);
        foreach ($res as $v){
            $arr[$v['merchant_id']][$v['appMarket']]['bind_card_pass_order_num'] = $v['count'];
            $arr[$v['merchant_id']][$v['appMarket']]['bind_card_pass_order_amount'] = $v['total_amount'];
        }

        //新用户放款
        $res = self::getNewUserAllLoanSuccessNumAndAmount($leftTime,$rightTime);
        foreach ($res as $v){
            $arr[$v['merchant_id']][$v['appMarket']]['loan_success_order_num'] = $v['count'];
            $arr[$v['merchant_id']][$v['appMarket']]['loan_success_order_amount'] = $v['total_amount'];
        }
        return $arr;
    }
}
<?php
namespace common\services\customer_remind;

use backend\models\remind\RemindOrder;
use common\helpers\RedisQueue;
use common\models\order\UserLoanOrder;
use common\models\order\UserLoanOrderRepayment;
use common\services\BaseService;

/**
 * Class CustomerRemindService
 * @package common\services\message
 *
 */
class CustomerRemindService extends BaseService
{
    const USER_TYPE_ALL = 0;
    const ALL_NEW_SELF_NEW = 1;
    const ALL_OLD_SELF_NEW = 2;
    const ALL_OLD_SELF_OLD = 3;

    public static $user_type_map = [
        self::ALL_NEW_SELF_NEW => '全新本新',
        self::ALL_OLD_SELF_NEW => '全老本新',
        self::ALL_OLD_SELF_OLD => '全老本老',
    ];

    const BEFORE_DAY_1 = 1;
    const BEFORE_DAY_0 = 0;

    public static $before_day_map = [
        self::BEFORE_DAY_0 => '当天',
        self::BEFORE_DAY_1 => '提前一天',
    ];


    public function getDispatchCount($merchantIds){
        $result = [];
        //初始结果
        foreach (self::$before_day_map as $before_day => $value){
            $result[$before_day][self::USER_TYPE_ALL] = 0;
            foreach (self::$user_type_map as $user_type => $val){
                $result[$before_day][$user_type] = 0;
            }
        }
        $res = RemindOrder::find()
            ->alias('A')
            ->select([
                'A.plan_date_before_day',
                'count' => 'COUNT(1)',
                'userType' => '(
                CASE 
                WHEN C.is_first = 1 AND C.is_all_first = 1 THEN 1 
                WHEN C.is_first = 1 AND C.is_all_first = 0 THEN 2 
                ELSE 3
                END)'
            ])
            ->leftJoin(UserLoanOrderRepayment::tableName().' B','A.repayment_id = B.id')
            ->leftJoin(UserLoanOrder::tableName().' C','B.order_id = C.id')
            ->where(['A.dispatch_status' => RemindOrder::STATUS_WAIT_DISPATCH,'A.is_test' => RemindOrder::NOT_TEST_CAN_DISPATCH])
            ->andWhere(['A.merchant_id' => $merchantIds])
            ->groupBy(['A.plan_date_before_day','userType'])
            ->asArray()
            ->all(\Yii::$app->get('db_read_1'));

        foreach ($res as $val){
            $result[$val['plan_date_before_day']][$val['userType']] += $val['count'];
            $result[$val['plan_date_before_day']][self::USER_TYPE_ALL] += $val['count'];
        }
        return $result;
    }


    public function getDispatchIds($planDateBeforeDay,$userType,$merchantIds){
        $query = RemindOrder::find()
            ->alias('A')
            ->select(['A.repayment_id'])
            ->leftJoin(UserLoanOrderRepayment::tableName().' B','A.repayment_id = B.id')
            ->leftJoin(UserLoanOrder::tableName().' C','B.order_id = C.id')
            ->where([
                'A.dispatch_status' => RemindOrder::STATUS_WAIT_DISPATCH,
                'A.plan_date_before_day' => $planDateBeforeDay,
                'A.is_test' => RemindOrder::NOT_TEST_CAN_DISPATCH
            ])
            ->andWhere(['A.merchant_id' => $merchantIds]);

        switch ($userType){
            case self::ALL_NEW_SELF_NEW :
                $query->andWhere(['C.is_first' => UserLoanOrder::FIRST_LOAN_IS,'C.is_all_first' => UserLoanOrder::FIRST_LOAN_IS]);
                break;
            case self::ALL_OLD_SELF_NEW :
                $query->andWhere(['C.is_first' => UserLoanOrder::FIRST_LOAN_IS,'C.is_all_first' => UserLoanOrder::FIRST_LOAN_NO]);
                break;
            case self::ALL_OLD_SELF_OLD :
                $query->andWhere(['C.is_first' => UserLoanOrder::FIRST_LOAN_NO,'C.is_all_first' => UserLoanOrder::FIRST_LOAN_NO]);
                break;
        }
        $res = $query->asArray()->all();
        return array_column($res,'repayment_id');
    }

    public function dispatch($dispatchCount,$planDateBeforeDay,$userType,$merchantIds){
        $repaymentIds = $this->getDispatchIds($planDateBeforeDay,$userType,$merchantIds);

        if(empty($repaymentIds)){
            return ['code' => -1, 'message' => 'There are no assigned orders'];
        }
        if(count($repaymentIds) <  array_sum($dispatchCount)){
            return ['code' => -1, 'message' => 'dispatch count greater than collection Order count'];
        }

        foreach ($dispatchCount as $adminId => $count){
            //$successCount = 0;
            if(intval($count) == 0){
                continue;
            }
            $orderArr = array_rand($repaymentIds,$count);
            if(is_array($orderArr)){
                foreach ($orderArr as $orderIdKey){
                    RedisQueue::push([RedisQueue::REMIND_ORDER_LIST,json_encode(['repayment_id'=>$repaymentIds[$orderIdKey],
                                                                                 'admin_user_id'=> $adminId])]);
                    unset($repaymentIds[$orderIdKey]);
                    //$successCount++;
                }
            }else{
                //订单指派
                RedisQueue::push([RedisQueue::REMIND_ORDER_LIST,json_encode(['repayment_id'=>$repaymentIds[$orderArr],
                                                                             'admin_user_id'=> $adminId])]);
                unset($repaymentIds[$orderArr]);
                //$successCount++;
            }
//            $result[$adminId] = $successCount;
        }
        return [ 'code' => 0, 'message' => 'success'];
    }
}
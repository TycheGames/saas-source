<?php
namespace common\services\loan_collection;

use callcenter\models\loan_collection\LoanCollectionOrder;
use common\helpers\RedisQueue;
use common\models\order\UserLoanOrder;
use common\models\order\UserLoanOrderRepayment;
use common\models\product\ProductPeriodSetting;
use common\services\user\MgUserContentService;
use yii\helpers\Json;
use yii\base\Exception;

//一些workdeskController 和 CollectionController 共有的方法
class LoanCollectionOrderService
{
    //催收详情页 ajax获取通讯录
    public static function getTXL($loan_person_id, $db = null){
        try{
            $loan_mobile_contacts= MgUserContentService::getContactData($loan_person_id, $db);
        }catch(\Exception $e){
            $loan_mobile_contacts= [];
        }
        if (!empty($loan_mobile_contacts)) {
            $loan_mobile_contacts_list = [0=>$loan_mobile_contacts];
        }else{
            $loan_mobile_contacts_list = [0=>[]];
        }
        return ['loan_mobile_contacts_list'=>$loan_mobile_contacts_list,'all_loan_mobile_contacts'=>$loan_mobile_contacts];
    }
    //催收详情页 ajax获取还款信息
    public static function getULOR($order){
        /** @var UserLoanOrder $loan */
        $loan = $order->loanOrder;
        /** @var UserLoanOrderRepayment $repayment */
        $repayment = $order->repaymentOrder;
        $repayInfo = [
            'id' =>  $repayment->id,
            'order_id' => $repayment->order_id,
            'amount' => $repayment->principal,   //借款金额
            'overdue_fee' => $repayment->overdue_fee,      //逾期费用
            'total_money' => $repayment->total_money,           //预计还款总额
            'interests' => $repayment->interests,
            'true_total_money' => $repayment->true_total_money,           //实际还款总额
            'delay_reduce_amount' => $repayment->delay_reduce_amount,     //延期减免金额
            'coupon_money' => $repayment->coupon_money,             //红包金额
            //剩余金额
            'surplus_money' => $repayment->getScheduledPaymentAmount(),
            'overdue_day' => $repayment->overdue_day,
            'ulor_status' => UserLoanOrderRepayment::$repayment_status_map[$repayment->status],
            'plan_repayment_time' => date('Y-m-d', $repayment->plan_repayment_time),
            'apr' => $loan->day_rate ?? '--',
            'loan_time' => isset($loan->loan_time) ? date('Y-m-d H:i:s', $loan->loan_time) : '--',     //借款时间
            'cost_fee' => $repayment->cost_fee,
        ];
        if(isset($loan)){
            $repayInfo['loan_term'] = $loan->loan_term . ProductPeriodSetting::$loan_method_map[$loan->loan_method] ?? '--';//借款天数
        }else{
            $repayInfo['loan_term'] = '--';
        }
        return ['repayInfo'=>$repayInfo];
    }

    /**
     * @name xybt-工作台-续借建议修改
     * @date 2017-05-26
     * @author 胡浩
     * @use 用于修改下次贷款建议
     */
    public static function nextLoanAdvice($request){
        $id = intval($request['id']);
        $suggest = intval($request['suggest']);
        $remark = $request['remark'];
        if(!in_array($suggest,array_keys(LoanCollectionOrder::$next_loan_advice))){
            throw new Exception('status error');
        }
        $res = LoanCollectionOrder::updateNextLoanAdvice($id, $suggest, $remark);
        if($res === true){
            return Json::encode([
                'code' => 0,
                'message' => 'submit success'
            ]);
        }else{
            return Json::encode([
                'code' => -1,
                'message' => $res
            ]);
        }
    }


    /**
     * 逾期天数达到更新等级时 入催收待回收订单队列
     * @param $repaymentId
     * @param $overdueDay
     * @param bool $isStopOver 是否停催结束到入催
     * @return bool
     */
    public static function pushOverdueOrder($repaymentId,$overdueDay,$isStopOver){
        if($isStopOver){
            //延期到期
            if($res = LoanCollectionOrder::getResetLevelNameByOverdueDaysRange($overdueDay)){
                RedisQueue::push([RedisQueue::COLLECTION_RESET_OVERDUE_LIST,json_encode(['repayment_id'=>$repaymentId, 'level' => $res['level']])]);
                return true;
            }
        }else{
            if($res = LoanCollectionOrder::getResetLevelNameByOverdueDays($overdueDay)){
                RedisQueue::push([RedisQueue::COLLECTION_RESET_OVERDUE_LIST,json_encode(['repayment_id'=>$repaymentId, 'level' => $res['level']])]);
                return true;
            }
        }
        return false;
    }

}



?>
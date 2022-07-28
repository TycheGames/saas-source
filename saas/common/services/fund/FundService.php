<?php

namespace common\services\fund;

use common\models\fund\LoanFundDayQuota;
use common\models\order\UserLoanOrder;
use common\services\BaseService;
use common\services\order\OrderService;
use common\models\fund\LoanFund;

/**
 * 资金方服务 
 */
class FundService extends BaseService {
    

    /**
     * 订单自动分发给资金方
     * @param $order
     * @param $operator
     * @return array
     * @throws \Exception
     */
    public function orderAutoDispatch(UserLoanOrder $order, $operator) {
        $merchantId = $order->merchant_id;
        $funds =  LoanFund::canUserLoan($merchantId, $order->is_export);
        if(!$funds) {
            return [
                'code'=> -1,
                'message'=>'无可用的资金渠道'
            ];
        }

        /* @var $fund LoanFund */
        $useFund = null;
        $sort_funds = $funds;
        $resultCode = ['code'=>-1,'message'=> []];

        foreach ($sort_funds as $fund)
        {
            $result = $this->orderSetFund($order, $fund, $operator);
            if ($result['code'] == 0) {//操作成功
                $useFund = $fund;
                break;
            } else {
                $resultCode['message'][$fund->id] = $result['msg'] ?? '';
            }
        }

        if (is_null($useFund)) {
            if (isset($resultCode)) {
                return $resultCode;
            } else {
                return [ 'code'=>-1,'message'=>'无适合的资金渠道'];
            }
        }
        return [ 'code'=> 0, 'data' => ['fund'=>$useFund->id]];
    }


    /**
     * @param UserLoanOrder $order
     * @param LoanFund $fund
     * @param $operator
     * @param int $nCustomerType 类型 [1=>'新客', 2=>'老客']
     * @return array
     * @throws \Exception
     */
    public function orderSetFund(UserLoanOrder $order,LoanFund $fund, $operator) {
        $date = date('Y-m-d');

        $disbursalAmount = $order->disbursalAmount();
        $fundService = $fund->getService();
        // 获取当天余下配额

        $nCustomerType = LoanFundDayQuota::getOrderCustomerType($order);
        $dayRemainingQuota = $fund->getTodayRemainingQuota($nCustomerType);
        if($dayRemainingQuota >= $disbursalAmount) {
            $fund_support_order_ret = $fundService->supportOrder($order);
            if (0 != $fund_support_order_ret['code']) {
                return $fund_support_order_ret;
            }
        } else {//超出余额
            return [
                'code'=> -1,
                'msg'=>'配额不足'
            ];
        }


        $db = LoanFund::getDb();
        $transaction = $db->beginTransaction();
        try {
            $order->fund_id = (int)$fund->id;
            $fund->decreaseQuota($disbursalAmount, $date, $nCustomerType);

            $status = UserLoanOrder::LOAN_STATUS_FUND_MATCHED;
            $order->fund_id = intval($fund->id);
            $orderService = new OrderService($order);
            $orderService->changeOrderAllStatus(['after_loan_status' => $status], '', 0);

            //插入资金记录
            $transaction->commit();
            $ret = [ 'code'=>0 ];

            //将loanFund的实例与订单关联 $order->loanFund
            $order->populateRelation('loanFund', $fund);
            $this->afterSetOrderFund($order, $operator);
        } catch (\Exception $ex) {
            $transaction->rollBack();
            $ret = [
                'code'=>$ex->getCode() ? $ex->getCode() : -1,
                'message'=>$ex->getMessage() ."\n".$ex->getLine() . "\n" . $ex->getFile()
            ];
        }

        return $ret;
    }


    /**
     * 在设置订单资方后触发 
     * @param UserLoanOrder $order 订单模型
     * @param string $operator 操作人
     * @throws \Exception
     */
    public function afterSetOrderFund($order, $operator) {
//        RedisQueue::push([RedisQueue::LIST_RAZORPAY_CREATE_VIRTUAL_ACCOUNT, $order->id]);
    }


    /**
     * 订单放款成功反馈
     * @param UserLoanOrder $order
     * @throws \yii\base\InvalidConfigException
     */
    public function orderPaySuccess(UserLoanOrder $order)
    {
        $service = $this->getFundService($order->fund_id, $order->is_first);
        $service->orderPaySuccess($order);
    }


    /**
     * 逾期通知
     * @param UserLoanOrder $order
     * @throws \yii\base\InvalidConfigException
     */
    public function overdueNotify(UserLoanOrder $order)
    {
        $service = $this->getFundService($order->fund_id, $order->is_first);
        $service->overdueNotify($order);
    }


    /**
     * 订单还款成功
     * @param UserLoanOrder $order
     * @param null $is_prepay
     * @param null $repay_amount
     * @throws \yii\base\InvalidConfigException
     */
    public function orderRepaySuccess(UserLoanOrder $order, $is_prepay = null, $repay_amount = null)
    {
        $service = $this->getFundService($order->fund_id, $order->is_first);
        $service->orderRepaySuccess($order, $is_prepay, $repay_amount);
    }


    /**
     * 获取资方服务
     * @param $fundId
     * @param int $is_first
     * @return DefaultService|object
     * @throws \yii\base\InvalidConfigException
     */
    public function getFundService($fundId, $is_first){
        $loanFund = LoanFund::findOne($fundId);
        $service = $loanFund->getService();
        return $service;
    }

}
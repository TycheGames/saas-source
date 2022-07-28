<?php
namespace common\services\fund;

use common\helpers\RedisQueue;
use common\models\fund\LoanFund;
use common\models\order\UserLoanOrder;
use common\models\pay\PayAccountSetting;
use common\services\BaseService;


/**
 * 资方基类
 * Class BaseFundService
 * @package common\services\fund
 *
 * @property LoanFund $loanFund
 */
class BaseFundService extends BaseService
{

    public $loanFund;

    /**
     * 订单放款成功反馈
     * @param UserLoanOrder $order 订单
     * @return []
     */
    public function orderPaySuccess(UserLoanOrder $order)
    {
        //通知kudos
        if($order->loanFund->loan_account_id != 0)
        {
            switch ($order->loanFund->loanAccountSetting->service_type)
            {
                case PayAccountSetting::SERVICE_TYPE_KUDOS:
                    RedisQueue::push([RedisQueue::LIST_KUDOS_CREATE_ORDER, $order->id]);
                    break;

                case PayAccountSetting::SERVICE_TYPE_AGLOW:
                    RedisQueue::push([RedisQueue::LIST_AGLOW_LOAN_DISBURSED, $order->id]);
                    break;

                default:
                    break;
            }

        }
    }

    /**
     * 订单还款成功
     * @param UserLoanOrder $order 订单
     * @return []
     */
    public function orderRepaySuccess($order, $is_prepay = null, $repay_amount = null)
    {
        if($order->loanFund->loan_account_id != 0)
        {
            switch ($order->loanFund->loanAccountSetting->service_type)
            {
                case PayAccountSetting::SERVICE_TYPE_AGLOW:
                    RedisQueue::push([RedisQueue::LIST_AGLOW_FEES_UPDATE, $order->id]);
                    break;

                default:
                    break;
            }

        }

    }

    /**
     * 逾期通知
     * @param UserLoanOrder $order
     */
    public function overdueNotify(UserLoanOrder $order)
    {
        if($order->loanFund->loan_account_id != 0)
        {
            switch ($order->loanFund->loanAccountSetting->service_type)
            {
                case PayAccountSetting::SERVICE_TYPE_AGLOW:
                    RedisQueue::push([RedisQueue::LIST_AGLOW_FEES_UPDATE, $order->id]);
                    break;

                default:
                    break;
            }

        }
    }

    /**
     * 判断资方是否支持订单
     * @param UserLoanOrder $order 订单
     * @return []
     */
    public function supportOrder(UserLoanOrder $order)
    {
        return [ 'code' => -1,'msg' => '未定义规则'];
    }

}
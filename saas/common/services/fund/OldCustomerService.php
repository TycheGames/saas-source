<?php
namespace common\services\fund;

use common\models\fund\LoanFund;
use common\models\order\UserLoanOrder;

/**
 * 导流-全老本新资方
 * Class OldCustomerService
 * @package common\services\fund
 */
class OldCustomerService extends BaseFundService
{

    /**
     * 判断资方是否支持订单
     * @param UserLoanOrder $order 订单
     * @return []
     */
    public function supportOrder(UserLoanOrder $order)
    {
        if(
            UserLoanOrder::FIRST_LOAN_NO == $order->is_all_first
            && UserLoanOrder::FIRST_LOAN_IS == $order->is_first
            && UserLoanOrder::IS_EXPORT_YES == $order->is_export
        )
        {
            return [ 'code' => 0,'msg' => '资方分配成功'];
        }else{
            return [ 'code' => -1,'msg' => '资方不匹配:非老客户'];
        }

    }

}
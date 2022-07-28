<?php
namespace common\services\fund;

use common\models\order\UserLoanOrder;


/**
 * 导流-全新本新资方
 * Class NewCustomerService
 * @package common\services\fund
 */
class NewCustomerService extends BaseFundService
{

    /**
     * 判断资方是否支持订单
     * @param UserLoanOrder $order 订单
     * @return []
     */
    public function supportOrder(UserLoanOrder $order)
    {
        if(
            UserLoanOrder::FIRST_LOAN_IS == $order->is_all_first
            && UserLoanOrder::IS_EXPORT_YES == $order->is_export
        )
        {
            return [ 'code' => 0,'msg' => '资方分配成功'];
        }else{
            return [ 'code' => -1,'msg' => '资方不匹配:非新客户'];
        }

    }

}
<?php
namespace common\services\fund;

use common\models\order\UserLoanOrder;

/**
 * apk-老客资方
 * Class ApkOldCustomerService
 * @package common\services\fund
 */
class ApkOldCustomerService extends BaseFundService
{

    /**
     * 判断资方是否支持订单
     * @param UserLoanOrder $order 订单
     * @return []
     */
    public function supportOrder(UserLoanOrder $order)
    {
        if(
            UserLoanOrder::FIRST_LOAN_NO == $order->is_first
            && UserLoanOrder::IS_EXPORT_NO == $order->is_export
        )
        {
            if(!empty($this->loanFund->app_markets) && !in_array($order->clientInfoLog->app_market, explode(',', $this->loanFund->app_markets)))
            {
                return [ 'code' => -1, 'msg' => 'appmarket不匹配'];
            }else{
                return [ 'code' => 0,'msg' => '资方分配成功'];
            }
        }else{
            return [ 'code' => -1,'msg' => '非老客户'];
        }

    }

}
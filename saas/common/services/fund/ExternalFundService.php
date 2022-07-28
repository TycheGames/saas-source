<?php
namespace common\services\fund;

use common\models\order\UserLoanOrder;

/**
 * 导流资方
 * Class OldCustomerService
 * @package common\services\fund
 */
class ExternalFundService extends BaseFundService
{

    /**
     * 判断资方是否支持订单
     * @param UserLoanOrder $order 订单
     * @return []
     */
    public function supportOrder(UserLoanOrder $order)
    {
        return [ 'code' => 0,'msg' => '资方分配成功'];
    }

}
<?php

namespace common\services\fund;

use common\models\order\UserLoanOrder;

class DefaultService extends BaseFundService
{

    /**
     * 判断资方是否支持订单
     * @param UserLoanOrder $order 订单
     * @return []
     */
    public function supportOrder(UserLoanOrder $order)
    {
        return [ 'code' => -1,'msg' => '分配失败'];
    }

}
<?php
namespace common\services\fund;

use common\models\order\UserLoanOrder;
use common\models\order\UserLoanOrderRepayment;
use common\models\risk\RiskResultSnapshot;
use common\models\user\LoanPerson;
use yii\helpers\ArrayHelper;

/**
 * 导流-全老本老资方
 * Class SelfOldCustomerService
 * @package common\services\fund
 */
class SelfOldCustomerService extends BaseFundService
{
    /**
     * 判断资方是否支持订单
     * @param UserLoanOrder $order
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function supportOrder(UserLoanOrder $order)
    {
        if(
            UserLoanOrder::FIRST_LOAN_NO == $order->is_first
            && UserLoanOrder::IS_EXPORT_YES == $order->is_export
        )
        {
            return [ 'code' => 0,'msg' => '资方分配成功'];
        }else{
            return [ 'code' => -1,'msg' => '资方不匹配:非老客户'];
        }

    }

}
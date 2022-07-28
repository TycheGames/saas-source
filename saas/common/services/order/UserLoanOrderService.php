<?php

namespace common\services\order;

use common\models\order\UserLoanOrder;
use common\models\order\UserLoanOrderExtraRelation;
use common\models\user\LoanPerson;
use common\models\user\UserBasicInfo;
use common\services\BaseService;

class UserLoanOrderService extends BaseService
{

    /**
     * @param LoanPerson $loanPerson
     * @param null $nOrder
     * @return array
     */
    public function useOrderIdForUserInfo(LoanPerson $loanPerson, $nOrderId = null )
    {
        // 如果没有传订单ID就默认查当前用户最近的一条信息
        if (empty($nOrderId)) {
            $arrBasic = UserBasicInfo::find()->select('email_address')->where(['user_id'=>$loanPerson->id])->orderBy('id desc')->asArray()->limit(1)->scalar();
            return ['email'=>$arrBasic, 'contact' => $loanPerson->phone];
        }
        $oOrder = UserLoanOrder::find()->where(['id'=>$nOrderId, 'user_id'=>$loanPerson->id])->one();

        // 判断订单是否存在
        if (empty($oOrder)) {
            return false;
        }

        $nUserBasicInfoId = UserLoanOrderExtraRelation::find()->where(['order_id'=>$nOrderId])->select('user_basic_info_id')->scalar();
        $sEmailAddress = UserBasicInfo::find()->select('email_address')->where(['id' => $nUserBasicInfoId])->scalar();

        return ['email'=>$sEmailAddress, 'contact' => $loanPerson->phone];

    }// END useOrderIdForUserInfo


}// END CLASS
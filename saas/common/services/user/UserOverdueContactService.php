<?php


namespace common\services\user;

use common\models\order\UserLoanOrder;
use common\models\user\UserOverdueContact;
use common\services\BaseService;
use common\services\order\OrderExtraService;
use yii\base\Exception;

class UserOverdueContactService extends BaseService
{
    /**
     * 添加逾期紧急联系人名单
     * @param $orderId
     * @throws Exception
     */
    public function addContact($orderId)
    {
        $order = UserLoanOrder::findOne($orderId);
        $orderExtraService = new OrderExtraService($order);
        $userContact = $orderExtraService->getUserContact();
        $userId = $order->user_id;
        $check1 = UserOverdueContact::find()->select(['id'])
            ->where(['user_id' => $userId, 'phone' => $userContact->phone])->one();
        if(is_null($check1)){
            $phone1 = new UserOverdueContact();
            $phone1->user_id = $userId;
            $phone1->phone = $userContact->phone;
            if(!$phone1->save()){
                throw new Exception('第一紧急联系人添加失败');
            }

        }

        $check2 = UserOverdueContact::find()->select(['id'])
            ->where(['user_id' => $userId, 'phone' => $userContact->other_phone])->one();
        if(is_null($check2)){
            $phone2 = new UserOverdueContact();
            $phone2->user_id = $userId;
            $phone2->phone = $userContact->other_phone;
            if(!$phone2->save()){
                throw new Exception('第二紧急联系人添加失败');
            }
        }
    }

}
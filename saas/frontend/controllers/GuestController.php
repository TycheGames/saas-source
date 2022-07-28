<?php

namespace frontend\controllers;

use common\models\enum\ErrorCode;
use common\services\GuestService;
use frontend\models\guest\ApplyForm;
use frontend\models\guest\OrderDetailForm;
use Yii;


class GuestController extends BaseController
{


    /**
     * @name GuestController 申请还款
     * @method post
     * @param string amount
     * @param string key
     * @return array
     */
    public function actionApplyPayment()
    {
        $form = new ApplyForm();
        if ($form->load(Yii::$app->request->post(), '') && $form->validate()) {
            $service = new GuestService();
            if ($service->payment($form)) {
                return $this->return->setData($service->getResult())->returnOK();
            } else {
                return $this->return->returnFailed(ErrorCode::ERROR_COMMON(),
                    $service->getError());
            }
        } else {
            return $this->return->returnFailed(ErrorCode::ERROR_COMMON(),
                $this->getError($form->getErrorSummary(false)));
        }
    }


    public function actionOrderDetail()
    {
        $form = new OrderDetailForm();
        if ($form->load(Yii::$app->request->post(), '') && $form->validate()) {
            $service = new GuestService();
            if ($service->orderDetail($form)) {
                return $this->return->setData($service->getResult())->returnOK();
            } else {
                return $this->return->returnFailed(ErrorCode::ERROR_COMMON(),
                    $service->getError());
            }
        } else {
            return $this->return->returnFailed(ErrorCode::ERROR_COMMON(),
                $this->getError($form->getErrorSummary(false)));
        }
    }

}

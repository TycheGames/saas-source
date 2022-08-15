<?php

namespace frontend\controllers;


use common\models\enum\ErrorCode;
use common\models\enum\mg_user_content\UserContentType;
use common\services\risk\RiskOrderService;
use common\services\user\MgUserContentService;
use frontend\models\risk\ApplyForm;
use frontend\models\risk\CollectionSuggestionForm;
use frontend\models\risk\LoanCollectionRecordForm;
use frontend\models\risk\LoginLogForm;
use frontend\models\risk\ModelScoreForm;
use frontend\models\risk\OrderLoanSuccessForm;
use frontend\models\risk\OrderOverdueForm;
use frontend\models\risk\OrderRejectForm;
use frontend\models\risk\OrderRepaymentSuccessForm;
use frontend\models\risk\RemindLogForm;
use frontend\models\risk\RemindOrderForm;
use frontend\models\risk\RiskBlackForm;
use frontend\models\risk\UserContentForm;
use yii;

class RiskController extends BaseController
{

    /**
     * @return array
     * @throws yii\base\InvalidConfigException
     */
    public function actionApply()
    {
        $form = new ApplyForm();
        if ($form->load(Yii::$app->request->post(), '') && $form->validate()) {
            $service = new RiskOrderService();
            if ($service->apply($form)) {
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

    /**
     * 上传用户数据信息
     * @return array
     */
    public function actionUploadContentsNew(){
        $form = new UserContentForm();
        if ($form->load(Yii::$app->request->post(), '') && $form->validate()) {
            $service = new MgUserContentService();
            if ($service->saveMgUserContentByFormToRNew(new UserContentType(intval($form->type)), $form)) {
                return $this->return->setData([])->returnOK();
            }
        } else {
            return $this->return->returnFailed(ErrorCode::ERROR_COMMON(),
                $this->getError($form->getErrorSummary(false)));
        }
    }


    public function actionLoginLog()
    {
        $form = new LoginLogForm();
        if ($form->load(Yii::$app->request->post(), '') && $form->validate()) {
            $service = new RiskOrderService();
            if ($service->loginEventUpload($form)) {
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



    public function actionOrderReject(){
        $form = new OrderRejectForm();
        if ($form->load(Yii::$app->request->post(), '') && $form->validate()) {
            $service = new RiskOrderService();
            if ($service->orderReject($form)) {
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


    public function actionOrderLoanSuccess()
    {
        $form = new OrderLoanSuccessForm();
        if ($form->load(Yii::$app->request->post(), '') && $form->validate()) {
            $service = new RiskOrderService();
            if ($service->orderLoanSuccess($form)) {
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

    public function actionOrderRepaymentSuccess()
    {
        $form = new OrderRepaymentSuccessForm();
        if ($form->load(Yii::$app->request->post(), '') && $form->validate()) {
            $service = new RiskOrderService();
            if ($service->orderRepaymentSuccess($form)) {
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

    public function actionOrderOverdue()
    {
        $form = new OrderOverdueForm();
        if ($form->load(Yii::$app->request->post(), '') && $form->validate()) {
            $service = new RiskOrderService();
            if ($service->orderOverdue($form)) {
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

    public function actionRiskBlack()
    {
        $form = new RiskBlackForm();
        if ($form->load(Yii::$app->request->post(), '') && $form->validate()) {
            $service = new RiskOrderService();
            if ($service->addRiskBlack($form)) {
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

    public function actionCollectionSuggestion()
    {
        $form = new CollectionSuggestionForm();
        if ($form->load(Yii::$app->request->post(), '') && $form->validate()) {
            $service = new RiskOrderService();
            if ($service->collectionSuggestion($form)) {
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

    public function actionLoanCollectionRecord()
    {
        $form = new LoanCollectionRecordForm();
        if ($form->load(Yii::$app->request->post(), '') && $form->validate()) {
            $service = new RiskOrderService();
            if ($service->loanCollectionRecord($form)) {
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

    public function actionRemindOrder()
    {
        $form = new RemindOrderForm();
        if ($form->load(Yii::$app->request->post(), '') && $form->validate()) {
            $service = new RiskOrderService();
            if ($service->remindOrder($form)) {
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

    public function actionRemindLog()
    {
        $form = new RemindLogForm();
        if ($form->load(Yii::$app->request->post(), '') && $form->validate()) {
            $service = new RiskOrderService();
            if ($service->remindLog($form)) {
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

    /**
     * 获取用户模型分
     * @return array
     */
    public function actionGetModelScore()
    {
        $form = new ModelScoreForm();
        if ($form->load(Yii::$app->request->post(), '') && $form->validate()) {
            $service = new RiskOrderService();
            if ($service->getModelScore($form)) {
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
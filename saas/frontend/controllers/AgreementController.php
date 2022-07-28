<?php


namespace frontend\controllers;


use common\models\enum\ErrorCode;
use common\services\agreement\AgreementService;
use frontend\models\agreement\LoanServiceForm;
use frontend\models\agreement\SanctionLetterApiForm;
use frontend\models\agreement\SanctionLetterForm;
use yii\filters\AccessControl;
use Yii;

class AgreementController extends BaseController
{
    public function behaviors()
    {
        return [
            'access' => [
                'class'  => AccessControl::class,
                // 除了下面的action其他都需要登录
                'except' => [
                    'sanction-letter-api'
                ],
                'rules'  => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @name AgreementController 贷款服务合同信息 [agreement/loan-service]
     * @method post
     * @param int amount 借款金额
     * @param int days 借款天数
     * @param int productId 产品ID
     * @return array
     */
    public function actionLoanService(){
        $form = new LoanServiceForm();
        if ($form->load(Yii::$app->request->post(), '') && $form->validate()) {
            $userId = Yii::$app->user->identity->getId();
            $service = new AgreementService();
            $productId = $form->productId;
            $amount = $form->amount;
            $days = $form->days;
            if ($service->getLoanServiceData($userId,$productId,$amount,$days)) {
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
     * @name AgreementController 用户委托协议 [agreement/user-commissioned]
     * @method post
     * @return array
     */
    public function actionUserCommissioned(){
        $userId = Yii::$app->user->identity->getId();
        $service = new AgreementService();
        if ($service->getUserCommissionedData($userId)) {
            return $this->return->setData($service->getResult())->returnOK();
        } else {
            return $this->return->returnFailed(ErrorCode::ERROR_COMMON(),
                $service->getError());
        }
    }

    public function actionDemandPromissoryNote() {
        $userId = Yii::$app->user->identity->getId();
        $form = new LoanServiceForm();
        if (!$form->load(Yii::$app->request->post(), '') || !$form->validate()) {
            return $this->return
                ->setData([])
                ->returnFailed(ErrorCode::PARAMS_INVALID());
        }

        $service = new AgreementService();
        $clientInfo = $this->getClientInfo();
        if ($service->getDemandPromissoryNote($userId, $form, $clientInfo)) {
            return $this->return->setData($service->getResult())->returnOK();
        } else {
            return $this->return->returnFailed(ErrorCode::ERROR_COMMON(),
                $service->getError());
        }
    }


    /**
     * @name AgreementController sanction letter
     * @return array
     */
    public function actionSanctionLetter() {
        $form = new SanctionLetterForm();
        if (!$form->load(Yii::$app->request->post(), '') ) {
            return $this->return
                ->setData([])
                ->returnFailed(ErrorCode::PARAMS_INVALID());
        }

        $form->userID = Yii::$app->user->identity->getId();
        $form->clientInfo = $this->getClientInfo();
        if (!$form->validate()) {
            return $this->return
                ->setData([])
                ->returnFailed(ErrorCode::PARAMS_INVALID());
        }

        $service = new AgreementService();
        if ($service->getLoanSanctionLetter($form)) {
            return $this->return->setData($service->getResult())->returnOK();
        } else {
            return $this->return->returnFailed(ErrorCode::ERROR_COMMON(),
                $service->getError());
        }
    }


    /**
     * sanction letter api接口
     * @return array
     */
    public function actionSanctionLetterApi()
    {
        $form = new SanctionLetterApiForm();
        if (! ($form->load(Yii::$app->request->post(), '') && $form->validate())) {
            return $this->return
                ->setData([])
                ->returnFailed(ErrorCode::PARAMS_INVALID());
        }
        $service = new AgreementService();
        if ($service->getLoanSanctionLetterApi($form)) {
            return $this->return->setData($service->getResult())->returnOK();
        } else {
            return $this->return->returnFailed(ErrorCode::ERROR_COMMON(),
                $service->getError());
        }

    }
}
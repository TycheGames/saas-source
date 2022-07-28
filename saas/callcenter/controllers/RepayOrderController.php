<?php


namespace callcenter\controllers;

use backend\models\Merchant;
use callcenter\models\AdminUser;
use callcenter\models\AppReductionPlatformApply;
use callcenter\models\CollectionReduceApply;
use callcenter\models\loan_collection\LoanCollectionOrder;
use common\models\order\UserRepaymentReducedLog;
use common\models\user\LoanPerson;
use common\models\order\UserLoanOrderRepayment;
use common\services\message\WeWorkService;
use common\services\repayment\CustomerReductionService;
use common\services\repayment\ReductionService;
use yii\data\Pagination;
use yii\helpers\Url;
use yii\helpers\VarDumper;
use yii\web\IdentityInterface;

class RepayOrderController extends  BaseController
{
    /**
     * @name 减免-申请
     */
    public function actionApplyReduce($id){
        $admin_id = \Yii::$app->user->identity->getId();
        /** @var LoanCollectionOrder $loanCollectionOrder */
        $loanCollectionOrder = LoanCollectionOrder::find()->where(['id' => $id, 'merchant_id'=>$this->merchantIds])->one();
        $orderRepayment = UserLoanOrderRepayment::findOne($loanCollectionOrder->user_loan_order_repayment_id);
        $reductionService = new ReductionService();
        if($this->request->getIsPost()){
            $reduceRemark = $this->request->post('reduce_remark','');
            $res = $reductionService->collectionApplyReduce($loanCollectionOrder,$admin_id,$reduceRemark);
            if($res){
                $service = new WeWorkService();
                $message = '有减免催收订单的申请需要处理，催收订单ID:'.$loanCollectionOrder->id.',申请人：'.\Yii::$app->user->identity->username;
                $service->sendText(['yanzhenlin','zhufangqi','xionghuakun'],$message);
                return $this->redirectMessage('apply success', self::MSG_SUCCESS, Url::toRoute(['work-desk/admin-collection-order-list']));
            }else{
                return $this->redirectMessage('apply fail :'.$reductionService->getError(), self::MSG_ERROR);
            }
        }
        $repaymentInfo = [
            'totalMoney' => $orderRepayment->total_money,
            'principal' => $orderRepayment->principal,
            'scheduledPaymentAmount' => $orderRepayment->getScheduledPaymentAmount(),
            'trueTotalMoney' => $orderRepayment->true_total_money,
        ];
        /** @var UserLoanOrderRepayment $orderRepayment */
        return $this->render('apply-reduce', [
            'repaymentInfo' => $repaymentInfo
        ]);
    }
    /**
     * @name 催收减免-操作
     * @params int $id
     * @return string
     */
    public function actionReduced($id)
    {
        /** @var UserLoanOrderRepayment $orderRepayment */
        $orderRepayment = UserLoanOrderRepayment::find()->where(['id' => $id])->one();
        if($this->request->getIsPost()){
            /** @var IdentityInterface $operator */
            $operator = \Yii::$app->user->identity->getId();
            $remark = $this->request->post('reduce_remark');
            if(empty($remark)) {
                return $this->redirectMessage('备注不能为空', self::MSG_ERROR);
            }

            $reductionService = new ReductionService();
            $res = $reductionService->reductionHandle($orderRepayment->order_id, $operator, $remark, UserRepaymentReducedLog::FROM_CS_SYSTEM);
            if($res){
                return $this->redirectMessage('repayment success', self::MSG_SUCCESS, Url::toRoute(['collection/collection-order-list']));
            }else{
                return $this->redirectMessage('repayment error:'.$reductionService->getError(), self::MSG_ERROR);
            }
        }
        $repaymentInfo = [
            'totalMoney' => $orderRepayment->total_money,
            'principal' => $orderRepayment->principal,
            'scheduledPaymentAmount' => $orderRepayment->getScheduledPaymentAmount(),
            'trueTotalMoney' => $orderRepayment->true_total_money,
        ];
        return $this->render('reduced', [
            'repaymentInfo' => $repaymentInfo
        ]);
    }

    /**
     * @name 申请催收减免-列表
     * @return string
     */
    public function actionApplyReducedList()
    {
        $condition[] = 'and';
        if ($this->getRequest()->getIsGet()) {
            $search = $this->request->get();
            if (isset($search['apply_status']) && $search['apply_status'] != '') {
                $condition[] = ['A.apply_status' => intval($search['apply_status'])];
            }
            if (isset($search['admin_user_id']) && $search['admin_user_id'] != '') {
                $condition[] = ['A.admin_user_id' => intval($search['admin_user_id'])];
            }
            if (isset($search['username']) && $search['username'] != '') {
                $condition[] = ['B.username' => $search['username']];
            }
            if (isset($search['apply_start_time']) && !empty($search['apply_start_time'])) {
                $condition[] = ['A.created_at' => strtotime($search['apply_start_time'])];
            }
            if (isset($search['apply_end_time']) && !empty($search['apply_end_time'])) {
                $condition[] = ['A.created_at' => strtotime($search['apply_end_time'])];
            }
            if (isset($search['merchant_id']) && !empty($search['merchant_id'])) {
                $condition[] = ['A.merchant_id' => intval($search['merchant_id'])];
            }
            if (isset($search['loan_collection_order_id']) && !empty($search['loan_collection_order_id'])) {
                $condition[] = ['A.loan_collection_order_id' => intval($search['loan_collection_order_id'])];
            }
        }
        $query = CollectionReduceApply::find()
            ->select([
                'A.id',
                'A.admin_user_id',
                'A.merchant_id',
                'B.username',
                'A.loan_collection_order_id',
                'A.apply_status',
                'A.apply_remark',
                'A.apply_status',
                'A.created_at',
                'A.updated_at'
            ])
            ->from(CollectionReduceApply::tableName() . ' A')
            ->leftJoin(AdminUser::tableName() . ' B', 'A.admin_user_id = B.id')
            ->where($condition)->andWhere(['A.merchant_id'=>$this->merchantIds]);

        if(!empty($request['is_summary']) && $request['is_summary'] == 1){
            $pages = new Pagination(['totalCount' => $query->count('A.id')]);
        }else{
            $pages = new Pagination(['totalCount' => 999999]);
        }
        $page_size = \Yii::$app->request->get('page_size',15);
        $pages->pageSize = $page_size;
        $collectionReduceApply = $query->orderBy(['A.id' => SORT_DESC])
            ->offset($pages->offset)
            ->limit($pages->limit)
            ->asArray()
            ->all();
        return $this->render('apply-reduced-list',[
            'pages'=>$pages,
            'collectionReduceApply'=>$collectionReduceApply,
            'merchantList'=>Merchant::getMerchantByIds($this->merchantIds,false),
            'isNotMerchantAdmin' => $this->isNotMerchantAdmin
        ]);
    }


    /**
     * @name 申请催收减免-审批
     * @return string
     */
    public function actionReducedAudit($id)
    {
        $admin_id = \Yii::$app->user->identity->getId();
        $collectionReduceApply = CollectionReduceApply::find()
            ->select([
                'A.id',
                'B.user_loan_order_repayment_id',
                'A.apply_remark'
            ])
            ->from(CollectionReduceApply::tableName() . ' A')
            ->leftJoin(LoanCollectionOrder::tableName() . ' B', 'A.loan_collection_order_id = B.id')
            ->where(['A.id' => $id,'A.merchant_id' => $this->merchantIds])->asArray()->one();
        /** @var UserLoanOrderRepayment $orderRepayment */
        $orderRepayment = UserLoanOrderRepayment::find()->where(['id' => $collectionReduceApply['user_loan_order_repayment_id']])->one();
        if($this->request->getIsPost()){
            $auditRes = $this->request->post('audit_operation');
            $reductionService = new ReductionService();
            if($reductionService->auditReduceApply($id,$admin_id,$auditRes)){
                return $this->redirectMessage('reduce success', self::MSG_SUCCESS, Url::toRoute(['repay-order/apply-reduced-list']));
            }else{
                return $this->redirectMessage('reduce fail:'.$reductionService->getError(), self::MSG_ERROR, Url::toRoute(['repay-order/apply-reduced-list']));
            }
        }
        $repaymentInfo = [
            'totalMoney' => $orderRepayment->total_money,
            'principal' => $orderRepayment->principal,
            'scheduledPaymentAmount' => $orderRepayment->getScheduledPaymentAmount(),
            'trueTotalMoney' => $orderRepayment->true_total_money,
            'costFee' => $orderRepayment->cost_fee,
            'interests' => $orderRepayment->interests,
            'overdueFee' => $orderRepayment->overdue_fee,
            'delayReduceAmount' => $orderRepayment->delay_reduce_amount
        ];
        return $this->render('reduced-audit',[
            'repaymentInfo' => $repaymentInfo,
            'collectionReduceApply'=>$collectionReduceApply,
        ]);
    }
}
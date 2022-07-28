<?php
namespace common\services\repayment;

use callcenter\models\AppReductionPlatformApply;
use callcenter\models\CollectionReduceApply;
use callcenter\models\loan_collection\LoanCollectionOrder;
use common\helpers\CommonHelper;
use common\models\order\UserLoanOrderExternalRepayment;
use common\models\order\UserLoanOrderRepayment;
use common\models\order\UserRepaymentReducedLog;
use common\models\workOrder\UserApplyReduction;
use common\models\workOrder\UserApplyReductionExternal;
use common\services\BaseService;
use common\services\order\OrderService;
use frontend\models\loan\ApplyReductionForm;

class CustomerReductionService extends BaseService
{
    /** @var UserLoanOrderRepayment */
    public $repayment;

    public function getRepayment($repaymentId){
        $this->repayment = UserLoanOrderRepayment::findOne($repaymentId);
    }

    /** @var OrderService */
    public $orderService;


    /**
     * @param LoanCollectionOrder $loanCollectionOrder
     * @param $admin_id
     * @return bool
     */
    public function operateApplyCheck(LoanCollectionOrder $loanCollectionOrder,$admin_id){
        if($loanCollectionOrder->status == LoanCollectionOrder::STATUS_COLLECTION_FINISH){
            $this->setError('order is finish');
            return false;
        }
        if($loanCollectionOrder->open_app_apply_reduction == LoanCollectionOrder::OPEN_APP_APPLY_REDUCTION){
            $this->setError('app apply reduction has open');
            return false;
        }
        $appReductionPlatformApply = AppReductionPlatformApply::find()->where([
            'apply_admin_user_id' => $admin_id,
            'collection_order_id' => $loanCollectionOrder->id,
            'audit_status' => AppReductionPlatformApply::STATUS_WAIT_APPLY
        ])->one();
        if($appReductionPlatformApply){
            $this->setError('apply has repeat submit');
            return false;
        }
        return true;
    }


    /**
     * 客户端用户能否开启减免信息提交-申请
     * @param LoanCollectionOrder $loanCollectionOrder
     * @param $admin_id
     * @param string $apply_remark
     * @return bool
     */
    public function collectionApplyOpenAppReduction(LoanCollectionOrder $loanCollectionOrder,$admin_id,$apply_remark = ''){
        if(!$this->operateApplyCheck($loanCollectionOrder,$admin_id)){
            return false;
        }
        $appReductionPlatformApply = new AppReductionPlatformApply();
        $appReductionPlatformApply->apply_admin_user_id = $admin_id;
        $appReductionPlatformApply->collection_order_id = $loanCollectionOrder->id;
        $appReductionPlatformApply->loan_order_id = $loanCollectionOrder->user_loan_order_id;
        $appReductionPlatformApply->repayment_id = $loanCollectionOrder->user_loan_order_repayment_id;
        $appReductionPlatformApply->audit_status = AppReductionPlatformApply::STATUS_WAIT_APPLY;
        $appReductionPlatformApply->audit_remark = $apply_remark;
        if(!$appReductionPlatformApply->save()){
            $this->setError('apply table save fail');
            return false;
        }
        return true;
    }


    /**
     * 客户端用户能否开启减免信息提交申请-审核
     * @param int $reduceId
     * @param int $adminId
     * @param int $auditRes 1通过  2不通过
     * @return bool
     */
    public function auditOpenAppReductionApply($reduceId,$adminId,$auditRes){
        $appReductionPlatformApply = AppReductionPlatformApply::findOne($reduceId);
        if($appReductionPlatformApply->audit_status != AppReductionPlatformApply::STATUS_WAIT_APPLY){
            $this->setError('audit is completed');
            return false;
        }
        $loanCollectionOrder = LoanCollectionOrder::findOne($appReductionPlatformApply->collection_order_id);
        $this->getRepayment($loanCollectionOrder->user_loan_order_repayment_id);

        if(!in_array($auditRes, [AppReductionPlatformApply::STATUS_APPLY_PASS,AppReductionPlatformApply::STATUS_APPLY_REJECT])){
            $this->setError('audit param not allow');
            return false;
        }
        $appReductionPlatformApply->audit_status = $auditRes;
        $appReductionPlatformApply->audit_operator_id = $adminId;
        if(!$appReductionPlatformApply->save()){
            $this->setError('apply table save fail');
            return false;
        }
        if($auditRes == CollectionReduceApply::STATUS_APPLY_PASS && $this->repayment->status != UserLoanOrderRepayment::STATUS_REPAY_COMPLETE){
            $loanCollectionOrder->open_app_apply_reduction = LoanCollectionOrder::OPEN_APP_APPLY_REDUCTION;
            $loanCollectionOrder->save();
        }

        return true;
    }

    public function applyReduction(ApplyReductionForm $form){
        /** @var UserLoanOrderRepayment $repayment */
        $repayment = UserLoanOrderRepayment::find()->where(['order_id' => $form->orderId,'user_id' => $form->userId])->one();
        if($repayment->status == UserLoanOrderRepayment::STATUS_REPAY_COMPLETE){
            $this->setError('order is finish');
            return false;
        }
        //检查是否有进行中的减免工单
        if(UserApplyReduction::isAcceptProgressByOrderId($form->orderId,$repayment->merchant_id)){
            $this->setError('Reduction work order in progress');
            return false;
        }
        $res = UserApplyReduction::createReductionWorkOrder($repayment->merchant_id,$form->userId,$form->orderId,$form->reductionFee,$form->repaymentDate,$form->reasons,$form->contact);
        if(!$res){
            $this->setError('Reduction work order add fail');
            return false;
        }
        $result = [];

        $this->setResult($result);
        return true;
    }
}
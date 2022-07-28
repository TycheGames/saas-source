<?php
namespace common\services\repayment;

use callcenter\models\CollectionReduceApply;
use callcenter\models\loan_collection\LoanCollectionOrder;
use common\models\order\UserLoanOrderRepayment;
use common\models\order\UserOrderLoanCheckLog;
use common\models\order\UserRepaymentReducedLog;
use common\services\BaseService;
use common\models\order\UserLoanOrder;
use common\services\order\OrderService;
use yii\base\Exception;
use yii\web\IdentityInterface;

class ReductionService extends BaseService
{
    /** @var UserLoanOrderRepayment */
    public $repayment;

    public function getRepayment($repaymentId){
        $this->repayment = UserLoanOrderRepayment::findOne($repaymentId);
    }

    /** @var OrderService */
    public $orderService;

    /**
     * 获取月减免次数
     * @param $admin_id
     * @return int|string
     */
    public function getMonthReduceCount($admin_id){
        return CollectionReduceApply::find()
            ->where(['admin_user_id' => $admin_id])
            ->andWhere(['>=','created_at',strtotime(date('Y-m'))])
            ->count();
    }

    /**
     * 可减免性检查
     * @return bool
     */
    public function reduceCheck(){
        if($this->repayment->true_total_money < $this->repayment->getAmountInExpiryDate()){
            return false;
        }
        if($this->repayment->status != UserLoanOrderRepayment::STATUS_NORAML){
            return false;
        }
        if($this->repayment->is_overdue != UserLoanOrderRepayment::IS_OVERDUE_YES){
            return false;
        }
        return true;
    }

    /**
     * @param LoanCollectionOrder $loanCollectionOrder
     * @return bool
     */
    public function operateCheck(LoanCollectionOrder $loanCollectionOrder){
        $this->getRepayment($loanCollectionOrder->user_loan_order_repayment_id);
        if(!$this->reduceCheck()){
            return false;
        }
        /** @var CollectionReduceApply $collectionReduceApply */
        $collectionReduceApply = CollectionReduceApply::find()->where([
            'loan_collection_order_id' => $loanCollectionOrder->id,'apply_status' => CollectionReduceApply::STATUS_WAIT_APPLY
        ])->one();
        if($collectionReduceApply){
            return false;
        }
        return true;
    }

    /**
     * 申请减免
     * @param LoanCollectionOrder $loanCollectionOrder
     * @param $admin_id
     * @param string $apply_remark
     * @return bool
     */
    public function collectionApplyReduce(LoanCollectionOrder $loanCollectionOrder,$admin_id,$apply_remark = ''){
        $this->getRepayment($loanCollectionOrder->user_loan_order_repayment_id);
        if(!$this->reduceCheck()){
            $this->setError('not allow');
            return false;
        };
//        if($loanCollectionOrder->current_collection_admin_user_id != $admin_id){
//            return false;
//        }
//        if($this->getMonthReduceCount($admin_id) > 3){
//            $this->setError('reduce times not allow');
//            return false;
//        }
        $collectionReduceApply = CollectionReduceApply::find()->where([
            'admin_user_id' => $admin_id,
            'loan_collection_order_id' => $loanCollectionOrder->id
        ])->one();
        if($collectionReduceApply){
            $this->setError('apply has repeat submit');
            return false;
        }
        $collectionReduceApply = new CollectionReduceApply();
        $collectionReduceApply->admin_user_id = $admin_id;
        $collectionReduceApply->merchant_id = $loanCollectionOrder->merchant_id;
        $collectionReduceApply->loan_collection_order_id = $loanCollectionOrder->id;
        $collectionReduceApply->apply_status = CollectionReduceApply::STATUS_WAIT_APPLY;
        $collectionReduceApply->apply_remark = $apply_remark;
        if(!$collectionReduceApply->save()){
            $this->setError('apply table save fail');
            return false;
        }
        return true;
    }

    /**
     * 减免申请审批
     * @param int $reduceId
     * @param int $adminId
     * @param int $auditRes 1通过  2不通过
     * @return bool
     */
    public function auditReduceApply($reduceId,$adminId,$auditRes){
        $collectionReduceApply = CollectionReduceApply::findOne($reduceId);
        if($collectionReduceApply->apply_status != CollectionReduceApply::STATUS_WAIT_APPLY){
            $this->setError('audit is completed');
            return false;
        }
        $loanCollectionOrder = LoanCollectionOrder::findOne($collectionReduceApply->loan_collection_order_id);
        $this->getRepayment($loanCollectionOrder->user_loan_order_repayment_id);
//        if(!$this->reduceCheck()){
//            $this->setError('not allow');
//            return false;
//        };
        if(!in_array($auditRes, [CollectionReduceApply::STATUS_APPLY_PASS,CollectionReduceApply::STATUS_APPLY_REJECT])){
            $this->setError('audit param not allow');
            return false;
        }
        $collectionReduceApply->apply_status = $auditRes;
        $collectionReduceApply->operator_admin_user_id = $adminId;
        if(!$collectionReduceApply->save()){
            $this->setError('apply table save fail');
            return false;
        }
        if($auditRes == CollectionReduceApply::STATUS_APPLY_PASS && $this->repayment->status == UserLoanOrderRepayment::STATUS_NORAML){
            //开始减免
            $res = $this->reductionHandle($loanCollectionOrder->user_loan_order_id,$adminId,$collectionReduceApply->apply_remark,UserRepaymentReducedLog::FROM_CS_SYSTEM);
            if(!$res){
                return false;
            }
        }
        return true;
    }


    /**
     * 减免
     * @param $orderId
     * @param $operator
     * @param string $remark
     * @param int $from
     * @return bool
     */
    public function reductionHandle($orderId, $operator, $remark = '', $from = UserRepaymentReducedLog::FROM_ADMIN_SYSTEM){
        $order = UserLoanOrder::findOne($orderId);
        $service = new OrderService($order);
        $res = $service->reduction($operator, $remark, $from);
        if(!$res){
            $this->setError($service->getError());
        }
        return $res;
    }
}
<?php

namespace console\controllers;

use common\helpers\CommonHelper;
use common\models\order\UserLoanOrder;
use common\services\loan\LoanService;
use common\services\order\OrderService;
use common\services\repayment\RepaymentService;
use yii\db\Exception;
use yii;
use yii\console\ExitCode;

class MockController extends BaseController {

    /**
     * 前置审核
     * @param $id
     * @param $type
     */
    public function actionPreCheck($id,$type){
        if(!in_array($type, [1,2])){
            echo '参数错误';
            exit;
        }

        $order = UserLoanOrder::findOne($id);
        if(empty($order)){
            echo '订单不存在';
            exit;
        }

        if($order->status != UserLoanOrder::STATUS_CHECK || $order->audit_status != UserLoanOrder::AUDIT_STATUS_GET_DATA){
            echo '订单状态错误';
            exit;
        }

        $orderService = new OrderService($order);

        switch ($type){
            case 1://前置通过
                $orderService->changeOrderAllStatus([ 'after_audit_status' => UserLoanOrder::AUDIT_STATUS_AUTO_CHECK],'');
                break;
            case 2://前置拒绝
                $orderService->getDataReject('手动拒绝', 1, 2, 0);
                break;
            default:
                break;
        }
    }

    /**
     * 主决策审核
     * @param $id
     * @param $type
     */
    public function actionAutoCheck($id,$type){
        if(!in_array($type, [1,2,3])){
            echo '参数错误';
            exit;
        }

        $order = UserLoanOrder::findOne($id);
        if(empty($order)){
            echo '订单不存在';
            exit;
        }

        if($order->status != UserLoanOrder::STATUS_CHECK || $order->audit_status != UserLoanOrder::AUDIT_STATUS_AUTO_CHECK){
            echo '订单状态错误';
            exit;
        }

        $orderService = new OrderService($order);

        switch ($type)
        {
            case 1://通过
                $orderService->autoCheckApprove();
                break;
            case 2://拒绝
                $orderService->autoCheckReject('手动拒绝', 1, 2, 0);
                break;
            case 3://转人工
                $orderService->autoCheckManual('手动转人工', 1, 2);
                break;
            default:
        }
    }



    /**
     * 测试工具 - 打款回调模拟
     * @param int $order_id
     */
    public function actionLoanSuccessCallback($order_id){
        $order = UserLoanOrder::findOne($order_id);
        $loanService = new LoanService();
        $r = $loanService->loanSuccessCallback($order, time());
        var_dump($r);
    }


    /**
     * 测试工具 - 还款回调模拟
     * @param int $order_id
     * @param int $repaymentAmount
     */
    public function actionRepaymentCallback($order_id, $repaymentAmount)
    {
        $service = new RepaymentService();
        $repaymentAmount = CommonHelper::UnitToCents($repaymentAmount);
        $r = $service->repaymentHandle($order_id, $repaymentAmount);
        var_dump($r);
    }


}


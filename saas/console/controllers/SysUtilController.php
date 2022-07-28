<?php
namespace console\controllers;

use backend\models\remind\RemindLog;
use backend\models\remind\RemindOrder;
use common\helpers\CommonHelper;
use common\models\financial\FinancialPaymentOrder;
use common\models\order\RemindLogOther;
use common\models\order\RemindOrderOther;
use common\models\order\UserLoanOrder;
use common\models\order\UserLoanOrderRepayment;
use common\models\order\UserRepaymentLog;
use common\models\pay\LoanPayForm;
use common\models\pay\PayoutAccountInfo;
use common\models\pay\PayoutAccountSetting;
use common\services\GuestService;
use common\services\pay\RazorpayPayoutService;
use common\services\pay\RazorpayService;
use common\services\repayment\RepaymentService;
use Yii;

class SysUtilController extends BaseController {

    /**
     * 清空全部的db缓存
     */
    public function actionClearSchemaCache() {
        $ret = CommonHelper::clearSchemaCache();
        print "done {$ret}\n";
    }

    /**
     * 生成还款链接
     * @param int $orderID 订单号
     */
    public function actionPaymentLink($orderID)
    {
        $order = UserLoanOrder::findOne($orderID);
        $guestService = new GuestService();
        $paymentLink = $guestService->generatePaymentLink($order);
        $this->printMessage($paymentLink);
    }


    /**
     * 打款测试
     * @param $payoutAccountID
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function actionPayoutTest($payoutAccountID)
    {
        $payoutAccountSetting = PayoutAccountInfo::findOne(intval($payoutAccountID));
        $service = new RazorpayPayoutService($payoutAccountSetting);
        $txnId = 'test_payout_' . date('YmdHis');
        $this->printMessage($txnId);
        $form = new LoanPayForm();
        $form->beneName = 'REPEGON TECHNOLOGY PRIVATE LIMITED';
        $form->beneAccNo = '114505001681';
        $form->bankName = 'ICIC BANK';
        $form->beneIFSC = 'ICIC0001145';
        $form->txnId = $txnId;
        $form->amount = '100';
        $form->remark = 'test payout';
        $form->userID = 1;
        $service->loanPayHandle($form);
        var_dump($service->financialLoanCallback);
    }


    /**
     * 打款回调
     * @param $payOrderID
     * @param $payPaymentID
     */
    public function actionPaymentNotify($payOrderID, $payPaymentID)
    {
        /** @var FinancialPaymentOrder $financialPayment */
        $financialPayment = FinancialPaymentOrder::find()->where(['pay_order_id' => $payOrderID])->one();
        if(FinancialPaymentOrder::STATUS_DEFAULT != $financialPayment->status)
        {
            $this->printMessage("非默认状态");
            return;
        }
        $service = RazorpayService::getInstanceByPayAccountId($financialPayment->pay_account_id);
        $r = $service->orderRepaymentNotify($payOrderID, $payPaymentID);
        var_dump($r);

    }

    /**
     * 手动处理还款回调
     * @param $financialPaymentID
     * @param $payOrderID
     */
    public function actionPaymentNotifySuccess($financialPaymentID, $payOrderID)
    {
        $financialPaymentOrder = FinancialPaymentOrder::findOne($financialPaymentID);
        $service = RazorpayService::getInstanceByPayAccountId($financialPaymentOrder->pay_account_id);
        if($service->orderRepaymentNotify($financialPaymentOrder->pay_order_id, $payOrderID)){
            $this->printMessage('success');
        }
    }

    public function actionPaymentSuccessFile($filePath)
    {
        $data = file_get_contents($filePath);
        $list = explode("\n", $data);
        foreach ($list as $line)
        {
            $v = explode("\t", $line);
            $financialPaymentOrder = FinancialPaymentOrder::findOne(['pay_order_id' => $v[1]]);
            if($financialPaymentOrder)
            {
                $service = RazorpayService::getInstanceByPayAccountId($financialPaymentOrder->pay_account_id);
                if($service->orderRepaymentNotify($financialPaymentOrder->pay_order_id, $v[0])){
                    $this->printMessage('success');
                }
            }

        }
    }


    public function actionFixRepayment()
    {
        $list = FinancialPaymentOrder::find()
            ->where(['status' => FinancialPaymentOrder::STATUS_SUCCESS,
                'is_booked' => FinancialPaymentOrder::IS_BOOKED_NO])->all();

        foreach ($list as $value)
        {
            $item = FinancialPaymentOrder::findOne($value->id);
            $service = new RepaymentService();
            $r = $service->repaymentHandle($item->order_id, $item->amount, UserRepaymentLog::TYPE_ACTIVE,0, $item->payment_type);
            if($r)
            {
                $item->is_booked = FinancialPaymentOrder::IS_BOOKED_YES;
                $item->save();
            }else{
                $this->printMessage("{$item->order_id} 失败");
            }
        }
    }
}

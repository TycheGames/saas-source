<?php

namespace console\controllers;


use common\helpers\RedisDelayQueue;
use common\helpers\RedisQueue;
use common\models\aglow\LoanLicenceAglowOrder;
use common\models\enum\aglow\ApplyStatus;
use common\models\enum\aglow\ConfirmLoan;
use common\models\enum\aglow\LoanStatus;
use common\models\order\UserLoanOrder;
use common\models\order\UserLoanOrderRepayment;
use common\models\pay\PayAccountSetting;
use common\services\AglowService;
use yii\base\Exception;

class AglowController extends BaseController {


    /**
     * 订单拒绝
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function actionRejectOrder()
    {
        $startTime = time();
        while ($orderId = RedisQueue::pop([RedisQueue::LIST_AGLOW_LOAN_APPLY_REJECT])) {
            $this->printMessage("订单号：{$orderId}, 开始运行");
            try{
                $order = UserLoanOrder::findOne($orderId);
                $service = new AglowService($order->loanFund->loanAccountSetting);
                $r = $service->createAglowOrder($order);
                if(!$r)
                {
                    throw new Exception("订单号：{$orderId},aglow订单创建失败");
                }
                $r = $service->loanApply($orderId);
                if(!$r)
                {
                    throw new Exception("订单号：{$orderId},借款申请失败");
                }
                $r = $service->applyStatusUpdate($orderId, ApplyStatus::REJECT()->getValue());
                if(!$r)
                {
                    throw new Exception("订单号：{$orderId},借款状态更新失败");
                }
                $this->printMessage("订单号：{$orderId}, 运行成功");

            }catch (\Exception $exception)
            {
                $this->printMessage("订单号：{$orderId},异常退出，重入队列,message:{$exception->getMessage()}, tracer: {$exception->getTraceAsString()}");
                \Yii::error("订单号：{$orderId},异常退出，重入队列,message:{$exception->getMessage()},file:{$exception->getFile()},line:{$exception->getLine()}", 'aglow_reject_order');
                RedisDelayQueue::pushDelayQueue(RedisQueue::LIST_AGLOW_LOAN_APPLY_REJECT, $orderId, 120);
            }
            if((time() - $startTime) >= 300)
            {
                $this->printMessage("脚本运行超过5分钟，退出脚本");

                return;
            }


        }
    }


    /**
     * 放款成功
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function actionLoanSuccess()
    {
        if(!$this->lock())
        {
            $this->printMessage("已有进行中进程，退出");
            return 0;
        }
        $startTime = time();
        while ($orderId = RedisQueue::pop([RedisQueue::LIST_AGLOW_LOAN_DISBURSED])) {
            $this->printMessage("订单号：{$orderId}, 开始运行");
            try{
                $order = UserLoanOrder::findOne($orderId);
                $service = new AglowService($order->loanFund->loanAccountSetting);
                $r = $service->createAglowOrder($order);
                if(!$r)
                {
                    throw new Exception("订单号：{$orderId},createAglowOrder失败");
                }
                $r = $service->loanApply($orderId);
                if(!$r)
                {
                    throw new Exception("订单号：{$orderId},loanApply失败");
                }
                $r = $service->applyStatusUpdate($orderId, ApplyStatus::PASS()->getValue());
                if(!$r)
                {
                    throw new Exception("订单号：{$orderId},applyStatusUpdate失败");
                }
                $r = $service->confirmLoan($orderId, ConfirmLoan::YES()->getValue());
                if(!$r)
                {
                    throw new Exception("订单号：{$orderId},confirmLoan失败");
                }
                $r = $service->loanDisbursed($orderId);
                if(!$r)
                {
                    throw new Exception("订单号：{$orderId},loanDisbursed失败");
                }
                $r = $service->customerConfirm($orderId);
                if(!$r)
                {
                    throw new Exception("订单号：{$orderId},customerConfirm失败");
                }
                $r = $service->loanStatus($orderId, LoanStatus::SUCCESS()->getValue());
                if(!$r)
                {
                    throw new Exception("订单号：{$orderId},loanStatus失败");
                }
                $r = $service->loanRepayment($orderId);
                if(!$r)
                {
                    throw new Exception("订单号：{$orderId},loanRepayment失败");
                }
                $this->printMessage("订单号：{$orderId}, 运行成功");

            }catch (\Exception $exception)
            {
                $this->printMessage("订单号：{$orderId},异常退出，重入队列,message:{$exception->getMessage()}, tracer: {$exception->getTraceAsString()}");
                \Yii::error("订单号：{$orderId},异常退出，重入队列,message:{$exception->getMessage()}, tracer: {$exception->getTraceAsString()}", 'aglow_Loan_success');
                RedisDelayQueue::pushDelayQueue(RedisQueue::LIST_AGLOW_LOAN_DISBURSED, $orderId, 120);
            }
            if((time() - $startTime) >= 300)
            {
                $this->printMessage("脚本运行超过5分钟，退出脚本");

                return;
            }


        }
    }


    public function actionGenerateSanctionLetter($orderID)
    {
        $order = UserLoanOrder::findOne(intval($orderID));

        $service = new AglowService($order->loanFund->loanAccountSetting);
        $service->generateSanctionLetter($order);
    }

    /**
     * 费用变更
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function actionFeesUpdate()
    {
        $startTime = time();
        while ($orderId = RedisQueue::pop([RedisQueue::LIST_AGLOW_FEES_UPDATE])) {
            $this->printMessage("订单号：{$orderId}, 开始运行");
            try{
                $order = UserLoanOrder::findOne($orderId);
                if(0 == $order->loanFund->loan_account_id)
                {
                    $this->printMessage("订单号：{$orderId}, 没有对应nbfc账号，跳过处理");
                    continue;
                }
                $aglowOrder = LoanLicenceAglowOrder::findOne(['order_id' => $orderId]);
                $service = new AglowService($aglowOrder->payAccountSetting);
                $r = $service->feesUpdate($orderId);
                if(!$r)
                {
                    throw new Exception("订单号：{$orderId},feesUpdate失败");
                }
                $this->printMessage("订单号：{$orderId}, 运行成功");

            }catch (\Exception $exception)
            {
                $this->printMessage("订单号：{$orderId},异常退出，重入队列,message:{$exception->getMessage()}, tracer: {$exception->getTraceAsString()}");
                \Yii::error("订单号：{$orderId},异常退出，重入队列,{$exception->getMessage()}, tracer: {$exception->getTraceAsString()}", 'aglow_fees_update');
                RedisDelayQueue::pushDelayQueue(RedisQueue::LIST_AGLOW_FEES_UPDATE, $orderId, 120);
            }
            if((time() - $startTime) >= 300)
            {
                $this->printMessage("脚本运行超过5分钟，退出脚本");

                return;
            }


        }
    }



    public function actionFixFeesUpdate($startDate, $endDate)
    {
        $startTime = strtotime($startDate);
        $endTime = strtotime($endDate);

        $fundIDs = [];
        $query = UserLoanOrder::find()->select(['o.id as id'])->alias('o')
            ->leftJoin(UserLoanOrderRepayment::tableName().' as r','o.id = r.order_id')

            ->where([
                'o.status' => UserLoanOrder::STATUS_PAYMENT_COMPLETE,
                'o.fund_id' => $fundIDs])
            ->andWhere(['>=', 'closing_time', $startTime])
            ->andWhere(['<', 'closing_time', $endTime])
            ->orderBy(['o.id' => SORT_ASC])
            ->limit(1000)
            ->asArray();

        $maxID = 0;
        $cloneQuery = clone $query;
        $orders = $cloneQuery->andWhere(['>', 'o.id', $maxID])->all();

        while ($orders)
        {
            foreach ($orders as $order)
            {
                $maxID = $orderId = $order['id'];
                $aglowOrder = LoanLicenceAglowOrder::findOne(['order_id' => $orderId]);
                $service = new AglowService($aglowOrder->payAccountSetting);
                $r = $service->feesUpdate($orderId, true);
                if(!$r)
                {
                    $this->printMessage("订单号:{$orderId} 执行失败");
                    var_dump($service->getError());
                }
            }

            $cloneQuery = clone $query;
            $orders = $cloneQuery->andWhere(['>', 'o.id', $maxID])->all();
        }

    }

    /**
     * 修复还款计划
     * @param $orderId
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function actionFixLoanRepayment($orderId)
    {
        /** @var LoanLicenceAglowOrder $aglowOrder */
        $aglowOrder = LoanLicenceAglowOrder::find()->where(['order_id' => $orderId])->one();
        $service = new AglowService($aglowOrder->payAccountSetting);
        $r = $service->loanRepayment($orderId);
        var_dump($r);

    }


    /**
     * @param $orderId
     * @return int|void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function actionForceLoanSuccess($orderId)
    {

        $this->printMessage("订单号：{$orderId}, 开始运行");
        $order = UserLoanOrder::findOne($orderId);
        $service = new AglowService($order->loanFund->loanAccountSetting);
        $r = $service->createAglowOrder($order);
        if(!$r)
        {
            throw new Exception("订单号：{$orderId},createAglowOrder失败");
        }
        $r = $service->loanApply($orderId);
        if(!$r)
        {
            throw new Exception("订单号：{$orderId},loanApply失败");
        }
        $r = $service->applyStatusUpdate($orderId, ApplyStatus::PASS()->getValue());
        if(!$r)
        {
            throw new Exception("订单号：{$orderId},applyStatusUpdate失败");
        }
        $r = $service->confirmLoan($orderId, ConfirmLoan::YES()->getValue());
        if(!$r)
        {
            throw new Exception("订单号：{$orderId},confirmLoan失败");
        }
        $r = $service->loanDisbursed($orderId);
        if(!$r)
        {
            throw new Exception("订单号：{$orderId},loanDisbursed失败");
        }
        $r = $service->customerConfirm($orderId);
        if(!$r)
        {
            throw new Exception("订单号：{$orderId},customerConfirm失败");
        }
        $r = $service->loanStatus($orderId, LoanStatus::SUCCESS()->getValue());
        if(!$r)
        {
            throw new Exception("订单号：{$orderId},loanStatus失败");
        }
        $r = $service->loanRepayment($orderId);
        if(!$r)
        {
            throw new Exception("订单号：{$orderId},loanRepayment失败");
        }
        $this->printMessage("订单号：{$orderId}, 运行成功");


    }


    public function actionPushSettlements()
    {
        //aglow账号pay_account_setting和razorpay账号 payout_account_info的对应关系
        $maps = [
            25 => 13
        ];

        foreach ($maps as $payAccountID => $payoutAccountID)
        {
            $account = PayAccountSetting::findOne($payAccountID);
            $s = new AglowService($account);
            $s->pushSettlements($payoutAccountID);
        }

    }



    public function actionFixCorrect()
    {

        $maxID = 0;
        $query = LoanLicenceAglowOrder::find()->select(['id'])
            ->where(['status' => LoanLicenceAglowOrder::STATUS_LOAN_CLOSE])
            ->limit(1000)->orderBy(['id' => SORT_ASC]);
        $cloneQuery = clone $query;
        $aglowOrders = $cloneQuery->andWhere(['>', 'id', $maxID])->all();

        while ($aglowOrders)
        {
            foreach ($aglowOrders as $item)
            {
                $aglowOrder = LoanLicenceAglowOrder::findOne($item->id);
                $service = new AglowService($aglowOrder->payAccountSetting);
                $r = $service->correctOverdue($aglowOrder->order_id, true);
                if(!$r)
                {
                    $this->printMessage("订单号:{$aglowOrder->order_id} 执行失败");
                    var_dump($service->getError());
                }
            }
        }

    }


    /**
     * @param $orderId
     * @return int|void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function actionBatchForceLoanSuccess()
    {
        $list = [
            1902094,
            1902109,
            1902088,
            1902085,
            1902086,
            1902245,
            1902660,
            1902871,
            1902997,
            1901720,
            1903233,
            1903235,
            1903269,
            1903295,
            1901912,
            1902596,
            1903345,
            1903362,
            1903370,
            1903379,
            1903391,
            1903388,
            1903403,
            1902426,
            1903426,
            1903536,
            1903545,
            1903559,
            1903634,
            1902625,
            1903730,
            1903821,
            1903912,
            1903975,
            1904006,
            1904330,
            1904955,
            1905019,
            1905069,
            1905096,
            1905113,
            1905250,
            1905312,
            1905322,
            1905328,
            1905331,
            1905433,
            1905466,
            1905493,
            1905544,
            1905560,
            1905566,
            1905619,
            1905711,
            1905740,
            1905660,
            1905745,
            1905717,
            1905803,
            1905810,
            1905741,
            1905840,
            1905737,
            1905940,
            1906150,
            1906820,
            2152706,
        ];

        foreach ($list as $orderId) {
            $this->actionForceLoanSuccess($orderId);
        }



    }


    public function actionBatchUpdateFees()
    {
        $list = [
            1902094,
            1902109,
            1902088,
            1902085,
            1902086,
            1902245,
            1902660,
            1902871,
            1902997,
            1901720,
            1903233,
            1903235,
            1903269,
            1903295,
            1901912,
            1902596,
            1903345,
            1903362,
            1903370,
            1903379,
            1903391,
            1903388,
            1903403,
            1902426,
            1903426,
            1903536,
            1903545,
            1903559,
            1903634,
            1902625,
            1903730,
            1903821,
            1903912,
            1903975,
            1904006,
            1904330,
            1904955,
            1905019,
            1905069,
            1905096,
            1905113,
            1905250,
            1905312,
            1905322,
            1905328,
            1905331,
            1905433,
            1905466,
            1905493,
            1905544,
            1905560,
            1905566,
            1905619,
            1905711,
            1905740,
            1905660,
            1905745,
            1905717,
            1905803,
            1905810,
            1905741,
            1905840,
            1905737,
            1905940,
            1906150,
            1906820,
            2152706,
        ];


        foreach ($list as $orderId) {
            $aglowOrder = LoanLicenceAglowOrder::findOne(['order_id' => $orderId]);
            $service = new AglowService($aglowOrder->payAccountSetting);
            $r = $service->feesUpdate($orderId, true);
            if(!$r)
            {
                $this->printMessage("订单号:{$orderId} 执行失败");
                var_dump($service->getError());
            }
        }

    }
}


<?php
namespace console\controllers;

use backend\models\Merchant;
use common\helpers\Util;
use common\models\financial\FinancialPaymentOrder;
use common\models\fund\LoanFund;
use common\models\order\FinancialLoanRecord;
use common\models\order\UserLoanOrder;
use common\models\pay\PayAccountSetting;
use common\models\pay\PayoutAccountInfo;
use common\models\pay\RazorpaySettlements;
use common\models\user\UserBankAccount;
use common\services\message\WeWorkService;
use common\services\order\FinancialService;
use common\services\order\OrderService;
use common\services\pay\MpurseService;
use common\services\pay\PayoutService;
use common\services\pay\RazorpayPayoutService;
use common\services\pay\RazorpayService;
use Yii;
use yii\console\ExitCode;

class FinancialLoanPayController extends BaseController {


    /**
     * 还款订单未回调通知
     * @return int
     */
    public function actionOrderPaymentAuth(){
        if(!$this->lock()){
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $this->printMessage("脚本开始");

        $begin_time = time() - 3600;
        $data = FinancialPaymentOrder::find()->select(['pay_account_id', 'cnt' => 'count(1)'])
            ->where(['status' => FinancialPaymentOrder::STATUS_DEFAULT, 'auth_status' => FinancialPaymentOrder::STATUS_SUCCESS])
            ->andWhere(['>=', 'updated_at', $begin_time])
            ->andWhere(['<', 'updated_at', time()-180])
            ->groupBy(['pay_account_id'])
            ->asArray()
            ->all();

        if(!empty($data)){
            $service = new WeWorkService();
            foreach ($data as $v){
                $paySetting = PayAccountSetting::findOne($v['pay_account_id']);
                if(!empty($paySetting)){
                    $message = '[重要]商户'.Merchant::getMerchantId()[$paySetting->merchant_id].'的'.$paySetting->name.'有'.$v['cnt'].'笔还款订单需要前往Razorpay手动捕获';
                    $service->send($message);
                }
            }
        }

        $this->printMessage("脚本结束");
    }



    /**
     * 打款查询
     * @return int
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function actionLoanQuery()
    {
        if(!$this->lock()){
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $max_id = 0;

        if(!empty($id)){
            $where['id'] = $id;
        }
        $query = FinancialLoanRecord::find()->where([
            'status' => FinancialLoanRecord::UMP_CMB_PAYING,
            'service_type' => FinancialLoanRecord::SERVICE_TYPE_MPURSE
        ])->andWhere(['<=', 'updated_at', time() - 30 * 60])->orderBy(['id'=>SORT_ASC])->limit(500);
        $clone_query = clone $query;
        $records = $clone_query->andWhere(['>','id',$max_id])->all();
        $this->printMessage('符合条件查询['.count($records).']笔，处理中...');
        while ($records) {
            /** @var FinancialLoanRecord $record */
            foreach ($records as $record) {
                $max_id = $record->id;
                /** @var FinancialLoanRecord $loan */
                $loan = FinancialLoanRecord::findOne($record->id);
                if (!$loan) {
                    continue;
                }

                if ($loan->updated_at != $record->updated_at
                    || $loan->status != $record->status
                ) {
                    continue;
                }

                $service = new FinancialService();
                if($service->loanQuery($loan))
                {
                    $this->printMessage("订单号:{$loan->business_id},{$service->getResult()}");
                }
                else {
                    $this->printMessage("订单号:{$loan->business_id},查询失败");
                }

            }


            $clone_query = clone $query;
            $records     = $clone_query->andWhere(['>', 'id', $max_id])->all();
            $this->printMessage("符合条件打款[".count($records)."]笔，处理中...");

        }


    }

    /**
     * 打款失败2天后自动驳回
     * @return int
     * @throws \yii\db\Exception
     */
    public function actionLoanOrderReject(){
        if(!$this->lock()){
            return ExitCode::UNSPECIFIED_ERROR;
        }
        $this->printMessage('脚本开始');

        $time = time() - 86400 * 2;
        $financialLoanOrder = FinancialLoanRecord::find()
            ->where(['status' => FinancialLoanRecord::UMP_PAY_HANDLE_FAILED])
            ->andWhere(['<', 'updated_at', $time])
            ->all();
        foreach ($financialLoanOrder as $model){
            /** @var  UserLoanOrder $order */
            $order = UserLoanOrder::find()->where(['id' => $model->business_id, 'status' => UserLoanOrder::STATUS_LOANING])->one();
            if(is_null($order))
            {
                $this->printMessage('order_id:'.$model->business_id.'借款订单不存在');
                continue;
            }
            $model->status = FinancialLoanRecord::UMP_PAY_FAILED;
            $service = new OrderService($order);
            if($service->orderLoanReject(0, '打款失败') && $model->save()){
                $this->printMessage('order_id:'.$model->business_id.'操作成功');
            }else{
                $this->printMessage('order_id:'.$model->business_id.'操作失败');
            }
        }

        $this->printMessage('脚本结束');
    }


    /**
     * 新生成打款列表 2020-04-07
     * @param null $id
     * @return int
     */
    public function actionGenerateFinancialRecords($id = null) {

        if(!$this->lock()){
            return ExitCode::UNSPECIFIED_ERROR;
        }

        //资产一键审核
        $min_id = 0;
        $query = UserLoanOrder::find()
            ->where([
                'status' => UserLoanOrder::STATUS_LOANING,
                'loan_status' => UserLoanOrder::LOAN_STATUS_FUND_MATCHED
            ])->andWhere(['>', 'id', $min_id]);
        if(!is_null($id)){
            $query->andWhere([ 'id' => $id]);
        }
        $all =$query->orderBy(['id' => SORT_DESC])->all();

        /** @var UserLoanOrder $model */
        foreach ($all as $model) {
            $this->printMessage("开始处理借款订单，id:{$model->id}");
            try {
                if (!FinancialLoanRecord::addLock($model->id)) { //避免重复申请打款
                    $this->printMessage(sprintf('[%s][%s] FinancialLoanRecord::addLock failed [id:%s]', __CLASS__, __FUNCTION__,$model->id));
                    unset($model);
                    continue;
                }
                $item = UserLoanOrder::findOne($model->id);
                $order_id = $item->id;
                //校验数据
                //1.订单状态为待放款
                if (!(UserLoanOrder::STATUS_LOANING == $item->status && $item->loan_status == UserLoanOrder::LOAN_STATUS_FUND_MATCHED)) {
                    $this->printMessage(sprintf('[%s][%s] %s status error.', __CLASS__, __FUNCTION__, $order_id));
                    unset($item);
                    continue;
                }

                $card = $item->userBankAccount;
                $money = $item->disbursalAmount();
                $data = [
                    'user_id'      => $item->user_id,
                    'bind_card_id' => $item->card_id,
                    'business_id'  => $item->id,
                    'money'        => $money,
                    'ifsc'         => $card->ifsc,
                    'bank_name'    => !empty($card->bank_name) ? $card->bank_name : substr($card->ifsc, 0, 4),
                    'account'      => $card->account,
                ];


                $payoutService = PayoutService::getInstanceByGroup($item->loanFund->payout_group, $item->merchant_id);
                $financialService = $payoutService->getService();
                $financial = $financialService->createFinancialLoanRecord($data);
                echo '创建打款记录操作返回结果:'.print_r($financial,1).PHP_EOL;
                $item->loan_status = UserLoanOrder::LOAN_STATUS_PAY;
                if ($financial['code'] == 0) {
                    if ($item->save()) {
                        $this->printMessage("model {$item->id} finish.");
                        unset($item);
                    } else {
                        $this->printMessage("model {$item->id} update failed.");
                    }
                } else {
                    $this->printMessage("model {$item->id} financial_service error." . $financial['message'] ?? '-');
                }
            } catch (\Exception $e) {
                var_dump($e->getTraceAsString());
            }
        }
    }



    /**
     * 新打款脚本 2020-04-07
     * @param int $id
     * @return int
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function actionPayMoney($id = 0){
        if(!$this->lock()){
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $max_id = 0;

        if(!empty($id)){
            $where['id'] = $id;
        }
        $query = FinancialLoanRecord::find()->where([
            'status' => FinancialLoanRecord::UMP_PAYING,
        ])->orderBy(['id'=>SORT_ASC])->limit(500);
        $clone_query = clone $query;
        $records = $clone_query->andWhere(['>','id',$max_id])->all();
        $this->printMessage('符合条件打款['.count($records).']笔，处理中...');
        while ($records) {
            /** @var FinancialLoanRecord $record */
            foreach ($records as $record) {
                $max_id = $record->id;
                //避免重复申请打款
                if (!FinancialLoanRecord::addLock('LI' . $record->id)) {
                    continue;
                }
                /** @var FinancialLoanRecord $loan */
                $loan = FinancialLoanRecord::findOne($record->id);
                if (!$loan) {
                    $this->printMessage("支付订单不存在");
                    continue;
                }

                if ($loan->updated_at != $record->updated_at
                    || $loan->status != $record->status
                ) {
                    $this->printMessage("支付订单状态已变更");
                    continue;
                }

                if($loan->retry_time > time()){
                    $this->printMessage("未到重试时间");
                    continue;
                }

                $service = new FinancialService();
                if($service->doPayMoney($loan))
                {
                    $this->printMessage("订单号:{$loan->business_id},{$service->getResult()}");
                }else{
                    $this->printMessage("订单号:{$loan->business_id},请求失败");
                }
            }

            $clone_query = clone $query;
            $records     = $clone_query->andWhere(['>', 'id', $max_id])->all();
            $this->printMessage("符合条件打款[".count($records)."]笔，处理中...");
        }
    }



    /**
     * 打款查询 2020-04-07
     * @return int
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function actionLoanQueryNew()
    {
        if(!$this->lock()){
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $max_id = 0;

        if(!empty($id)){
            $where['id'] = $id;
        }
        $query = FinancialLoanRecord::find()->where([
            'status' => FinancialLoanRecord::UMP_CMB_PAYING,
            'service_type' =>[FinancialLoanRecord::SERVICE_TYPE_MPURSE, FinancialLoanRecord::SERVICE_TYPE_CASHFREE, FinancialLoanRecord::SERVICE_TYPE_RAZORPAY]
        ])->andWhere(['<=', 'updated_at', time() - 5 * 60])->orderBy(['id'=>SORT_ASC])->limit(500);
        $clone_query = clone $query;
        $records = $clone_query->andWhere(['>','id',$max_id])->all();
        $this->printMessage('符合条件查询['.count($records).']笔，处理中...');
        while ($records) {
            /** @var FinancialLoanRecord $record */
            foreach ($records as $record) {
                $max_id = $record->id;
                /** @var FinancialLoanRecord $loan */
                $loan = FinancialLoanRecord::findOne($record->id);
                if (!$loan) {
                    continue;
                }

                if ($loan->updated_at != $record->updated_at
                    || $loan->status != $record->status
                ) {
                    continue;
                }

                $service = new FinancialService();
                if($service->loanQuery($loan))
                {
                    $this->printMessage("订单号:{$loan->business_id},{$service->getResult()}");
                }
                else {
                    $this->printMessage("订单号:{$loan->business_id},查询失败");
                }

            }


            $clone_query = clone $query;
            $records     = $clone_query->andWhere(['>', 'id', $max_id])->all();
            $this->printMessage("符合条件打款[".count($records)."]笔，处理中...");

        }


    }

    /**
     * 获取Razorpay结算信息
     * @return int
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function actionGetSettlements(){
        if(!$this->lock()){
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $this->printMessage('start');
        $begin = strtotime('today') - 7 * 86400;
        $payoutAccountInfo = PayoutAccountInfo::findOne(13);
        $params = [
            'count' => 100,
            'from'  => $begin,
        ];

        $service = new RazorpayPayoutService($payoutAccountInfo);
        $response = $service->getSettlements($params);
        $data = json_decode($response, true);

        foreach ($data['items'] as $v){
            if($v['status'] != 'processed'){
                continue;
            }

            $model = RazorpaySettlements::find()->where(['settlements_id' => $v['id']])->one();
            if(!empty($model)){
                continue;
            }
            $model = new RazorpaySettlements();
            $model->pay_account_id   = $payoutAccountInfo->id;
            $model->status           = RazorpaySettlements::STATUS_DEFAULT;
            $model->settlements_id   = $v['id'];
            $model->data_status      = $v['status'];
            $model->amount           = $v['amount'];
            $model->fees             = $v['fees'];
            $model->tax              = $v['tax'];
            $model->utr              = $v['utr'];
            $model->settlements_time = $v['created_at'];
            $model->save();
        }
        $this->printMessage('end');
    }



    public function actionGetBalance()
    {
        if(!$this->lock()){
            return ExitCode::UNSPECIFIED_ERROR;
        }
        $accounts = PayoutAccountInfo::find()->all();

        /** @var PayoutAccountInfo $account */
        foreach ($accounts as $account)
        {
            $merchantName = $account->name;
            $this->printMessage("账户【{$merchantName}】,开始查询");
            try{
                $service = new RazorpayPayoutService($account);
                $balance = $service->getBalance();
                $this->printMessage("账户【{$merchantName}】,当前余额为:{$balance}");
            }catch (\Exception $exception)
            {
                $this->printMessage("账户【{$merchantName}】,查询失败");
            }
        }

    }
}

<?php

namespace common\services\order;

use backend\models\remind\RemindOrder;
use callcenter\models\loan_collection\LoanCollectionOrder;
use callcenter\service\LoanCollectionService;
use Carbon\Carbon;
use common\helpers\CommonHelper;
use common\helpers\MessageHelper;
use common\helpers\RedisDelayQueue;
use common\helpers\RedisQueue;
use common\models\BankCardAutoCheckLog;
use common\models\ClientInfoLog;
use common\models\coupon\UserCouponInfo;
use common\models\enum\DrawStep;
use common\models\enum\verify\PassCode;
use common\models\financial\FinancialPaymentOrder;
use common\models\kudos\LoanKudosOrder;
use common\models\manual_credit\ManualCreditLog;
use common\models\manual_credit\ManualCreditRules;
use common\models\message\ExternalOrderMessageForm;
use common\models\order\EsUserLoanOrder;
use common\models\order\SaasUserLoanOrderRepayment;
use common\models\order\UserLoanOrder;
use common\models\order\UserLoanOrderDelayPaymentLog;
use common\models\order\UserLoanOrderExtendLog;
use common\models\order\UserLoanOrderExternal;
use common\models\order\UserLoanOrderRepayment;
use common\models\order\UserOrderLoanCheckLog;
use common\models\order\UserRepaymentLog;
use common\models\order\UserRepaymentReducedLog;
use common\models\product\LoanProductSetting;
use common\models\product\ProductSetting;
use common\models\risk\RiskResultSnapshot;
use common\models\user\LoanPerson;
use common\models\user\SaasLoanPerson;
use common\models\user\UserBankAccount;
use common\models\workOrder\UserApplyReduction;
use common\services\BaseService;
use common\services\fund\FundService;
use common\services\KudosService;
use common\services\message\ExternalOrderMessageService;
use common\services\message\FirebasePushService;
use common\services\message\SendMessageService;
use common\services\message\WeWorkService;
use common\services\user\UserCanLoanCheckService;
use common\services\user\UserCreditLimitService;
use common\services\user\UserExtraService;
use yii\data\Pagination;
use yii\db\Exception;
use yii;
use yii\helpers\ArrayHelper;

/**
 * Class OrderService
 * @package common\services\order
 * @property UserLoanOrder $order
 * @property UserLoanOrderRepayment $repaymentOrder
 */
class OrderService extends BaseService
{

    /**
     * @var UserLoanOrder $order
     * @var UserLoanOrderRepayment $repaymentOrder
     */
    public $order;
    public $repaymentOrder;

    public $operator;

    private $merchantId;

    public function __construct(UserLoanOrder $order, $config = [])
    {
        parent::__construct($config);
        $this->order = $order;
        $this->merchantId = $this->order->merchant_id;

    }


    /**
     * 检测用户是否可以借款
     * @param integer $user_id
     * @return bool
     */
    public function checkCanApply($userId)
    {
        if(!$userId){
            return false;
        }
        //查询是否有在审核状态下的订单，存在不给申请
        $order = UserLoanOrder::find()
            ->select('id')
            ->where(['user_id' => $userId])
            ->andWhere(['status'=> UserLoanOrder::$opening_order_status,])
            ->one();
        if ($order) {
            return false;
        }
        return true;
    }

    public function setLoanCompleteOrderNum(string $productPackageName, string $orderAppMarket, int $isSelfNew = null, int $isAllNew = null)
    {
        $expireTime = strtotime('tomorrow') - time();
        $orderAppMarketArray = explode('_', $orderAppMarket);
        $orderPackageName = $orderAppMarketArray[1] ?? 'unknown';

        $strDate = date('Ymd');
        $totalKey = sprintf('%s:%s:%s', RedisQueue::KEY_PREFIX_LOAN_SUCCESS, $strDate, 'total');
        $totalRes = RedisQueue::inc([$totalKey, 1]);
        if ($totalRes < 2) {
            RedisQueue::expire([$totalKey, $expireTime]);
        }

        //key规则 前缀:日期:产品包:下单包:本平台:全平台
        $orderPackageNameKey = sprintf('%s:%s:%s:%s:%s:%s', RedisQueue::KEY_PREFIX_LOAN_SUCCESS, $strDate, $productPackageName, $orderPackageName, $isSelfNew, $isAllNew);
        $orderPackageNameRes = RedisQueue::inc([$orderPackageNameKey, 1]);
        if ($orderPackageNameRes < 2) {
            RedisQueue::expire([$orderPackageNameKey, $expireTime]);
        }
        //key规则 前缀:日期:产品包:下单包
        $orderPackageNameNoTagKey = sprintf('%s:%s:%s:%s:no_tag', RedisQueue::KEY_PREFIX_LOAN_SUCCESS, $strDate, $productPackageName, $orderPackageName);
        $orderPackageNameNoTagRes = RedisQueue::inc([$orderPackageNameNoTagKey, 1]);
        if ($orderPackageNameNoTagRes < 2) {
            RedisQueue::expire([$orderPackageNameNoTagKey, $expireTime]);
        }

        //key规则 前缀:日期:产品包:本平台:全平台
        $noOrderPackageNameKey = sprintf('%s:%s:%s:%s:%s', RedisQueue::KEY_PREFIX_LOAN_SUCCESS, $strDate,$productPackageName, $isSelfNew, $isAllNew);
        $noOrderPackageNameRes = RedisQueue::inc([$noOrderPackageNameKey, 1]);
        if ($noOrderPackageNameRes < 2) {
            RedisQueue::expire([$noOrderPackageNameKey, $expireTime]);
        }
        //key规则 前缀:日期:产品包
        $noOrderPackageNameNoTagKey = sprintf('%s:%s:%s:no_tag', RedisQueue::KEY_PREFIX_LOAN_SUCCESS, $strDate, $productPackageName);
        $noOrderPackageNameNoTagRes = RedisQueue::inc([$noOrderPackageNameNoTagKey, 1]);
        if ($noOrderPackageNameNoTagRes < 2) {
            RedisQueue::expire([$noOrderPackageNameNoTagKey, $expireTime]);
        }
    }

    /**
     * 生成还款计划并流转订单状态
     * @param UserLoanOrder $order
     * @param int $loanTime
     * @return bool
     */
    public function loanSuccess($loanTime = 0) : bool
    {
        //判断还款计划是否已生成，防止重复调用
        $repayment = UserLoanOrderRepayment::find()->select(['id'])
            ->where(['order_id' => $this->order->id])->limit(1)->one();
        if(!is_null($repayment)){
            return true;
        }
        if(empty($loanTime)){
            $loanTime = time();
        }

        $transaction = Yii::$app->db->beginTransaction();
        try{
            //更新订单状态为已打款
            $this->order->loan_time = $loanTime;
            $this->changeOrderAllStatus([
                'after_status' => UserLoanOrder::STATUS_LOAN_COMPLETE,
                'after_loan_status' => UserLoanOrder::LOAN_STATUS_LOAN_SUCCESS
            ], 'loan_success');


            //判断是否是首单 重置为非新手
            if(!$this->order->save()){
                throw new Exception('UserLoanOrder fail to save');
            }

            //生成分期总表
            $repayment = new UserLoanOrderRepayment();
            $repayment->merchant_id = $this->merchantId;
            $repayment->user_id = $this->order->user_id;
            $repayment->order_id = $this->order->id;
            $repayment->principal = $this->order->amount;
            $repayment->interests = $this->order->interests;
            $repayment->cost_fee = $this->order->cost_fee;
            $repayment->interest_time = strtotime($this->order->getRepayDate());//利息计算到当天
            $repayment->overdue_day = 0;
            $repayment->is_overdue = UserLoanOrderRepayment::IS_OVERDUE_NO;
            $repayment->plan_repayment_time = strtotime($this->order->getRepayDate());
            $repayment->plan_fee_time = strtotime($this->order->getFeeDate());
            $repayment->status = UserLoanOrderRepayment::STATUS_NORAML;
            $repayment->total_money = $this->order->calcTotalMoney();
            $repayment->true_total_money = 0;
            $repayment->card_id = $this->order->card_id;
            $repayment->loan_time= $this->order->loan_time;
            if(!$repayment->save()){
                throw new Exception('UserLoanOrderRepayment fail to save');
            }

            $transaction->commit();
            $params = [
                'app_name'            => $this->order->clientInfoLog->package_name,
                'order_id'            => $this->order->id,
                'user_id'             => $this->order->user_id,
                'loan_time'           => $this->order->loan_time,
                'principal'           => $this->order->amount,
                'plan_repayment_time' => $repayment->plan_repayment_time,
                'interests'           => $this->order->interests,
                'cost_fee'            => $this->order->cost_fee,
                'total_money'         => $repayment->total_money,
                'data_version'        => time(),
            ];
            RedisQueue::push([RedisQueue::PUSH_ORDER_LOAN_SUCCESS_DATA, json_encode($params)]);
            //推送pan_code最后放款时间队列
            RedisQueue::push([RedisQueue::PUSH_PAN_CODE_LAST_LOAN_TIME_DATA, json_encode(['pan_code' => $this->order->loanPerson->pan_code,'loan_time' => $this->order->loan_time])]);
            if (UserLoanOrder::IS_EXPORT_YES == $this->order->is_export) {
                $this->setLoanCompleteOrderNum(
                    $this->order->clientInfoLog->package_name,
                    $this->order->clientInfoLog->app_market,
                    $this->order->is_first,
                    $this->order->is_all_first
                );
            }

            //短信和push队列
            try{
                $sendMessageService = new SendMessageService();
                $messageService     = new ExternalOrderMessageService();
                $messageForm        = new ExternalOrderMessageForm();
                $productName        = $messageService->getProductName($this->order->is_export,$this->order->clientInfoLog->app_market);
                $sendMessageService->phone       = $this->order->loanPerson->phone;
                $sendMessageService->packageName = $this->order->is_export ? $this->order->productSetting->product_name : $this->order->clientInfoLog->package_name;
                $sendMessageService->productName = $productName;

                $messageForm->merchantId  = $this->order->merchant_id;
                $messageForm->userId      = $this->order->user_id;
                $messageForm->phone       = $this->order->loanPerson->phone;
                $messageForm->orderUuid   = $this->order->order_uuid;
                $messageForm->packageName = $this->order->clientInfoLog->package_name;
                $messageForm->productName = $productName;
                $messageForm->title       = ExternalOrderMessageForm::TITLE_LOAN_SUCCESS;
                $messageForm->message     = $sendMessageService->getMsgContent($this->order->is_export,'loanSuccess');
                //发短信
                $messageService->pushToMessageQueue($messageForm);
                //推送
                $messageForm->message  = $sendMessageService->getMsgContent($this->order->is_export,'pushLoanSuccess');
                if(UserLoanOrder::IS_EXPORT_YES == $this->order->is_export)
                {
                    $messageService->pushToExternalPushQueue($messageForm);
                }else{
                    $messageService->pushToInsidePushQueue($messageForm);
                }

                //资方回调
                $fundService = new FundService();
                $fundService->orderPaySuccess($this->order);

            }catch (\Exception $e){
                $service = new WeWorkService();
                $service->send(YII_ENV.':'.$e->getMessage().' in '.$e->getTraceAsString());
            }

            return true;
        } catch(\Exception $e){
            $transaction->rollBack();
            $this->setError($e->getMessage());
            $service = new WeWorkService();
            $message = sprintf("loanSuccess Error: \n %s",$e->getMessage());
            $service->send($message);
            return false;
        }
    }



    /**
     * 订单放款驳回
     * @param string $username
     * @param string $remark
     * @return bool
     * @throws Exception
     */
    public function orderLoanReject($username='',$remark=''):bool
    {
        $status = $this->order->status;
        if($status != UserLoanOrder::STATUS_LOANING){
            throw new Exception("The order status is not in the loan and cannot be dismissed!");
        }
        $afterStatus = UserLoanOrder::STATUS_LOAN_REJECT;

        try{
            $sendMessageService = new SendMessageService();
            $messageService     = new ExternalOrderMessageService();
            $messageForm        = new ExternalOrderMessageForm();
            $productName        = $messageService->getProductName($this->order->is_export,$this->order->clientInfoLog->app_market);

            $sendMessageService->phone       = $this->order->loanPerson->phone;
            $sendMessageService->packageName = $this->order->is_export ? $this->order->productSetting->product_name : $this->order->clientInfoLog->package_name;
            $sendMessageService->productName = $productName;

            $messageForm->merchantId  = $this->order->merchant_id;
            $messageForm->userId      = $this->order->user_id;
            $messageForm->phone       = $this->order->loanPerson->phone;
            $messageForm->orderUuid   = $this->order->order_uuid;
            $messageForm->packageName = $this->order->clientInfoLog->package_name;
            $messageForm->productName = $productName;
            $messageForm->title       = ExternalOrderMessageForm::TITLE_LOAN_REJECT;
            $messageForm->message     = $sendMessageService->getMsgContent($this->order->is_export,'loanReject');
            //短信
            $messageService->pushToMessageQueue($messageForm);
            //推送
            $messageForm->message  = $sendMessageService->getMsgContent($this->order->is_export,'pushLoanReject');
            if(UserLoanOrder::IS_EXPORT_YES == $this->order->is_export)
            {
                $messageService->pushToExternalPushQueue($messageForm);
            }else{
                $messageService->pushToInsidePushQueue($messageForm);
            }

        }catch (\Exception $e){
            $service = new WeWorkService();
            $service->send($e->getMessage().' in '.$e->getTraceAsString());
        }
        $this->changeOrderAllStatus([
            'after_status' => $afterStatus
        ],$remark, $username);

//            $sendMessage = "Dear ".$this->order->loanPerson->name.", if you applied for the ".($this->order->amount / 100)." rupee payment failed, please go to the APP to confirm the bank card information or re-tie the card.";
//            MessageHelper::sendAll($this->order->loanPerson->phone, $sendMessage, 'smsService_LianDong_OTP');
        return true;

    }

    /**
     * 获取订单详情
     * @param $hostInfo
     * @return array
     */
    public function getOrderDetail($hostInfo)
    {
        $disbursalAmount = CommonHelper::CentsToUnit($this->disbursalAmount());

        $showMpurse = false;
        $showSifang = false;
        $showQiming = false;
        $showQuanqiupay = false;
        $showRpay = false;
        $showMojo = false;
        $showJpay = false;
        $showTransfer = false;
        $showPayUplus = false;
        if(in_array($this->order->merchant_id, [1, 9])){
            $showTransfer = true;
        }

        if (in_array($this->order->merchant_id, [11,19,22,24,29,30,26])){
            $showPayUplus = false;
        }

        $payAccountSetting = $this->order->loanFund->payAccountSetting ?? null;
        if(!is_null($payAccountSetting))
        {
            $showMpurse = $payAccountSetting->getPaymentServiceEnableStatus('mpurse');
            $showSifang = $payAccountSetting->getPaymentServiceEnableStatus('sifang');
            $showQiming = $payAccountSetting->getPaymentServiceEnableStatus('qiming');
            $showQuanqiupay = $payAccountSetting->getPaymentServiceEnableStatus('quanqiupay');
            $showRpay = $payAccountSetting->getPaymentServiceEnableStatus('rpay');
            $showMojo = $payAccountSetting->getPaymentServiceEnableStatus('mojo');
            $showJpay = $payAccountSetting->getPaymentServiceEnableStatus('jpay');
        }

        $totalRepaymentAmount = CommonHelper::CentsToUnit($this->remainingPaymentAmount());
        if(in_array($this->order->clientInfoLog->package_name, ['bigshark', 'dhancash', 'hindmoney']))
        {
            //指定的saas强制小数点为0.1
            $totalRepaymentAmount = floor($totalRepaymentAmount) + 0.1;
        }
        $baseInfo = [
            'amount'                 => CommonHelper::CentsToUnit($this->loanAmount() + $this->totalInterests()),
            'delayMoney'             => 0,
            'delaySwitch'            => false,
            'delayDeductionMoney'    => 0,
            'delayDeductionSwitch'   => false,
            'extendMoney'            => 0,
            'extendSwitch'           => false,
            'extendExpectDate'       => null,
            'extendDate'             => null,
            'repaymentAmount'        => CommonHelper::CentsToUnit($this->totalRepaymentAmount()),
            'interests'              => CommonHelper::CentsToUnit($this->order->interests),
            'disbursalAmount'        => $disbursalAmount,
            'fees'                   => CommonHelper::CentsToUnit($this->processFee()),
            'reduce'                 => null, //延期减免滞纳金金额
            'days'                   => $this->totalLoanTerm(),
            'orderId'                => $this->order->id,
            'status'                 => $this->order->status,
            'gst'                    => CommonHelper::CentsToUnit($this->gst()),
            'totalRate'              => $this->totalRate(),
            'repaymentDate'          => date('d/m/Y', strtotime($this->repaymentTime())),
            'totalRepaymentAmount'   => $totalRepaymentAmount,
            'couponAmount'           => CommonHelper::CentsToUnit($this->couponAmount()),
            'repaidAmount'           => CommonHelper::CentsToUnit($this->repaidAmount()),
            'overdueFeePercent'      => round($this->order->overdue_rate / 1.18, 2) . '%',
            'overdueFeeAmount'       => CommonHelper::CentsToUnit($this->loanAmount() * $this->order->overdue_rate / 100),
            'showPraise'             => false,
            'showRepaymentModalType' => false,
            'id'                     => $this->order->order_uuid,
            'applyRelief'            => false, //判断是否可提交申请减免信息
            'reliefAudit'            => false, //判断提交申请减免信息是否审核中
            'showCashFree'           => false, //是否展示cashfree还款方式
            'showMpurse'             => $showMpurse, //是否展示mpurse还款方式
            'showSifang'             => $showSifang, //是否展示sifang还款方式
            'showQiming'             => $showQiming, //是否展示qiming还款方式
            'showQuanqiupay'         => $showQuanqiupay, //是否展示quanqiupay还款方式
            'showRpay'               => $showRpay, //是否展示rpay还款方式
            'showMojo'               => $showMojo, //是否展示mojo还款方式
            'showJpay'               => $showJpay, //是否展示japy还款方式
            'showTransfer'           => $showTransfer,
            'showPayUplus'           => $showPayUplus,
            'showDays'               => $this->order->productSetting->show_days == ProductSetting::SHOW_DAYS_YES,
            'agreementList'          => [],
        ];


        $paymentInfo = [];

        if($this->order->userLoanOrderRepayment)
        {
//            $baseInfo['agreementList'][] = [
//                'title' => 'View Sanction Letter',
//                'url'   => $hostInfo . "/h5/#/sanctionLetter?orderID={$this->order->id}",
//            ];
        }else{
//            $baseInfo['agreementList'][] = [
//                'title' => 'View Sanction Letter',
//                'url'   => $hostInfo . "/h5/#/sanctionLetter?productId={$this->order->product_id}&amount={$disbursalAmount}&days={$this->order->loan_term}",
//            ];
        }
        //订单放款第4天起，且没有还款，通知前端弹框
        if($this->order->userLoanOrderRepayment && UserLoanOrderRepayment::STATUS_NORAML == $this->order->userLoanOrderRepayment->status) {
            if((strtotime('today') - strtotime(date('Y-m-d', $this->order->loan_time))) / 86400 >= 3){
                $baseInfo['showRepaymentModalType'] = true;
            }
//            $baseInfo['applyRelief'] = LoanCollectionOrder::isOpenAppApplyReductionByOrderId($this->repaymentOrder->order_id);
//            $baseInfo['reliefAudit'] = UserApplyReduction::isAcceptProgressByOrderId($this->repaymentOrder->order_id,$this->repaymentOrder->merchant_id);
        }

        if ($this->order->userLoanOrderRepayment && UserLoanOrderRepayment::IS_EXTEND_YES == $this->order->userLoanOrderRepayment->is_extend) {
            $baseInfo['extendDate'] = Carbon::createFromIsoFormat('YYYY-MM-DD', $this->order->userLoanOrderRepayment->extend_end_date)->isoFormat('DD/MM/YYYY');
        }

        //待还款
        if (in_array($this->order->status, [UserLoanOrder::STATUS_LOAN_COMPLETE, UserLoanOrder::STATUS_OVERDUE])) {
            //逾期
            if ($this->order->userLoanOrderRepayment && $this->order->userLoanOrderRepayment->is_overdue == UserLoanOrderRepayment::IS_OVERDUE_YES) {
                $baseInfo['status'] = UserLoanOrder::STATUS_OVERDUE;
            }
        }

        //订单逾期之后
        if($this->order->userLoanOrderRepayment && UserLoanOrderRepayment::IS_OVERDUE_YES == $this->order->userLoanOrderRepayment->is_overdue)
        {
            $overdueFee = intval($this->order->userLoanOrderRepayment->overdue_fee / 1.18);
            $overdueFeeGST = $this->order->userLoanOrderRepayment->overdue_fee - $overdueFee;
            $paymentInfo['overdueFee'] = CommonHelper::CentsToUnit($overdueFee);
            $paymentInfo['overdueDay'] = $this->order->userLoanOrderRepayment->overdue_day;
            $paymentInfo['overdueGST'] = CommonHelper::CentsToUnit($overdueFeeGST);
            $paymentInfo['reduce'] = CommonHelper::CentsToUnit($this->order->userLoanOrderRepayment->delay_reduce_amount);
        }else{
            $paymentInfo['overdueFee'] = null;
            $paymentInfo['overdueDay'] = null;
            $paymentInfo['overdueGST'] = null;
        }

        $delayResult = $this->checkDelayStatus();
//        $baseInfo['delayMoney'] = $delayResult['delayMoney'];
//        $baseInfo['delaySwitch'] = $delayResult['delaySwitch'];
        $baseInfo['delayDeductionMoney'] = $delayResult['delayMoney'];
        $baseInfo['delayDeductionSwitch'] = $delayResult['delaySwitch'];
        //判断是否开启展期还款
        $extendResult = $this->checkExtendStatus();
        $baseInfo['extendMoney'] = $extendResult['extendMoney'];
        $baseInfo['extendSwitch'] = $extendResult['extendSwitch'];
        $baseInfo['extendExpectDate'] = $extendResult['extendExpectDate'];

        $data = array_merge($baseInfo,$paymentInfo);
        return $data;
    }


    /**
     * 判断该笔订单是否已放款
     * @return bool true 已放款  false 未放款
     */
    public function checkOrderHasBeenLoan()
    {
        if($this->getUserLoanOrderRepayment())
        {
            return true;
        }else{
            return false;
        }

    }

    public function checkExtendStatus()
    {
        $extendMoney = 0;
        $extendSwitch = false;
        $extendExpectDateStr = null;
        if (UserLoanOrder::IS_EXPORT_YES == $this->order->is_export) {
            /**
             * @var UserLoanOrder $loanOrder
             */
            $loanOrder = UserLoanOrderExternal::find()
                ->where(['order_uuid' => $this->order->order_uuid])
                ->one(Yii::$app->get('db_loan'));
            $productInfo = LoanProductSetting::find()
                ->where(['id' => $loanOrder->product_id])
                ->one();
        } else {
            $productInfo = $this->order->productSetting;
        }
        if (ProductSetting::STATUS_DELAY_OPEN == $productInfo->extend_status && UserLoanOrder::STATUS_LOAN_COMPLETE == $this->order->status) {
            $shouldDelayMoney = $this->order->amount * $productInfo->extend_ratio / 100;
            $shouldDelayMoney = round($shouldDelayMoney, -2);
            $extendMoney = min($this->remainingPaymentAmount(), $shouldDelayMoney);
            $repaymentDate = Carbon::createFromTimestamp(strtotime($this->repaymentTime()));
            $extendDayRange = explode(',',  $productInfo->extend_day);
            $overdueDay = $repaymentDate->diffInDays(Carbon::now(), false);
            if ($overdueDay >= intval($extendDayRange[0] ?? 0) && $overdueDay <= intval($extendDayRange[1] ?? 999999)) {
                $extendSwitch = true;
            }
            //因为上方调用了repaymentTime()， $this->repaymentOrder如果存在还款订单则有值
            if(!empty($this->repaymentOrder) && UserLoanOrderRepayment::IS_DELAY_YES == $this->repaymentOrder->is_extend)
            {
                $extendExpectDate = Carbon::createFromIsoFormat('YYYY-MM-DD', $this->repaymentOrder->extend_end_date);
            }else{
                $extendExpectDate = Carbon::createFromTimestamp(strtotime('today'));
            }
            $extendExpectDateStr = $extendExpectDate->addDays($this->order->loan_term)->isoFormat('DD/MM/YYYY');
        }

        return [
            'extendMoney'      => CommonHelper::CentsToUnit($extendMoney),
            'extendSwitch'     => $extendSwitch,
            'extendExpectDate' => $extendExpectDateStr,
        ];
    }

    public function checkDelayStatus()
    {
        return $this->checkDelayDeductionStatus();
        $delayMoney = 0;
        $delaySwitch = false;
        if (UserLoanOrder::IS_EXPORT_YES == $this->order->is_export) {
            /**
             * @var UserLoanOrder $loanOrder
             */
            $loanOrder = UserLoanOrderExternal::find()
                ->where(['order_uuid' => $this->order->order_uuid])
                ->one(Yii::$app->get('db_loan'));
            $productInfo = LoanProductSetting::find()
                ->where(['id' => $loanOrder->product_id])
                ->one();
        } else {
            $productInfo = $this->order->productSetting;
        }
        if (ProductSetting::STATUS_DELAY_OPEN == $productInfo->delay_status && UserLoanOrder::STATUS_LOAN_COMPLETE == $this->order->status) {
            $shouldDelayMoney = $this->order->amount * $productInfo->delay_ratio / 100;
            $shouldDelayMoney = round($shouldDelayMoney, -2);
            $delayMoney = min($this->remainingPaymentAmount(), $shouldDelayMoney);
            $repaymentDate = Carbon::createFromTimestamp(strtotime($this->repaymentTime()));
            if ($repaymentDate->diffInDays(Carbon::now(), false) >= $productInfo->delay_day) {
                $delaySwitch = true;
            }
        }

        return [
            'delayMoney'  => CommonHelper::CentsToUnit($delayMoney),
            'delaySwitch' => $delaySwitch,
        ];
    }


    /**
     * 判断是否支付延期+减免滞纳金功能
     * @return array
     */
    public function checkDelayDeductionStatus()
    {
        $delayMoney = 0;
        $delaySwitch = false;
        if (UserLoanOrder::IS_EXPORT_YES == $this->order->is_export) {
            /**
             * @var UserLoanOrder $loanOrder
             */
            $loanOrder = UserLoanOrderExternal::find()
                ->where(['order_uuid' => $this->order->order_uuid])
                ->one(Yii::$app->get('db_loan'));
            $productInfo = LoanProductSetting::find()
                ->where(['id' => $loanOrder->product_id])
                ->one();
        } else {
            $productInfo = $this->order->productSetting;
        }
        if (ProductSetting::STATUS_DELAY_OPEN == $productInfo->delay_deduction_status && UserLoanOrder::STATUS_LOAN_COMPLETE == $this->order->status) {
            $shouldDelayMoney = $this->order->amount * $productInfo->delay_deduction_ratio / 100;
            $shouldDelayMoney = round($shouldDelayMoney, -2);
            $delayMoney = min($this->remainingPaymentAmount(), $shouldDelayMoney);
            $repaymentDate = Carbon::createFromTimestamp(strtotime($this->repaymentTime()));
            if ($repaymentDate->diffInDays(Carbon::now(), false) >= $productInfo->delay_deduction_day) {
                $delaySwitch = true;
            }
        }

        return [
            'delayMoney'  => CommonHelper::CentsToUnit($delayMoney),
            'delaySwitch' => $delaySwitch,
        ];
    }


    /**
     * 获取用户借款订单列表
     * @param int $userId
     * @param int $page
     * @param int $pageSize
     * @return array
     */
    public static function getUserLoanOrderList($userId, $page, $pageSize)
    {
        $query = UserLoanOrder::find()->select(['id','amount', 'interests', 'created_at','status', 'loan_time', 'status'])
            ->where(['user_id' => $userId, 'is_export' => UserLoanOrder::IS_EXPORT_NO])
            ->andWhere(['or', ['status' => UserLoanOrder::$opening_order_status], ['>=', 'updated_at', time() - 86400]])
            ->andWhere(['!=', 'amount', 0])
            ->orderBy(['id' => SORT_DESC]);
        $clone = clone $query;
        $pages = new Pagination(['totalCount' => $clone->count()]);
        $pages->pageSize = $pageSize;
        $pages->setPage($page);
        /** @var UserLoanOrder[] $orders */
        $orders = $query->offset($pages->offset)->limit($pageSize)->all();
        $list = [];
        foreach($orders as $order)
        {
            $orderService = new OrderService($order);
            /**
             * 如果订单已还款，则显示已支付金额
             * 如果订单未还款，则显示剩余应还金额
             */
            if(UserLoanOrder::STATUS_PAYMENT_COMPLETE == $order->status)
            {
                $amount = $orderService->repaidAmount();
                $totalRepaymentAmount = CommonHelper::CentsToUnit($amount);
            }else{
                $amount = $orderService->remainingPaymentAmount();
                $totalRepaymentAmount = CommonHelper::CentsToUnit($amount);
                if(in_array($order->clientInfoLog->package_name, ['bigshark', 'dhancash', 'hindmoney']))
                {
                    //指定的saas强制小数点为0.1
                    $totalRepaymentAmount = floor($totalRepaymentAmount) + 0.1;
                }
            }
            if($orderService->checkOrderHasBeenLoan())
            {
                $date = $orderService->repaymentTime();
            }else{
                $date = date('Y-m-d H:i:s', $order->created_at);
            }

            $list[] = [
                'id' => $order->id,
                'amount' => $totalRepaymentAmount,
                'date' => $date,
                'status' => $order->status

            ];
        }
        return [
            'item' => $list,
            'totalPage' => $pages->getPageCount()
        ];
    }

    /**
     * 领取审核订单
     * @param $operator
     * @return bool
     */
    public function getAuditOrder($operator){
        if($this->userAuditOrderCount($operator) > 9)
        {
            $this->setError('Your order has reached its limit');
            return false;
        }
        if(!$this->order->canGetReviewOrder()){
            $this->setError('You cannot receive an audit of this order');
            return false;
        }
        $this->order->audit_status = UserLoanOrder::AUDIT_STATUS_MANUAL_CHECK;  //待人审
        $this->order->audit_operator = $operator;
        $this->order->audit_begin_time = time();
        if(!$this->order->save()){
            $this->setError('Order update failed');
            return false;
        }
        return true;
    }


    /**
     * 获取审核员拥有的审核订单数
     * @param $operatorId
     * @return int|string
     */
    public  function userAuditOrderCount($operatorId)
    {
        $count = UserLoanOrder::find()->where([
            'status' => UserLoanOrder::STATUS_CHECK,
            'audit_status' => UserLoanOrder::AUDIT_STATUS_MANUAL_CHECK,
            'audit_operator' => $operatorId
        ])->andWhere(['>=' , 'audit_begin_time', time() - 3600])
            ->count();
        return $count;

    }


    /**
     * 领取审核绑卡订单
     * @param $operator
     * @return bool
     */
    public function getAuditBankOrder($operator){
        if($this->userAuditBankOrderCount($operator) > 9)
        {
            $this->setError('Your order has reached its limit');
            return false;
        }
        if(!$this->order->canGetReviewBankOrder()){
            $this->setError('You cannot receive an audit of this order');
            return false;
        }
        $this->order->audit_bank_status = UserLoanOrder::AUDIT_BANK_STATUS_MANUAL_CHECK;  //待人审
        $this->order->audit_bank_operator = $operator;
        $this->order->audit_bank_begin_time = time();
        if(!$this->order->save()){
            $this->setError('Order update failed');
            return false;
        }
        return true;
    }


    /**
     * 获取审核员拥有的审核订单数
     * @param $operatorId
     * @return int|string
     */
    public  function userAuditBankOrderCount($operatorId)
    {
        $count = UserLoanOrder::find()->where([
            'status' => UserLoanOrder::STATUS_WAIT_DEPOSIT,
            'loan_status' => UserLoanOrder::LOAN_STATUS_BIND_CARD_CHECK,
            'audit_bank_status' => UserLoanOrder::AUDIT_BANK_STATUS_MANUAL_CHECK,
            'audit_bank_operator' => $operatorId
        ])->andWhere(['>=' , 'audit_bank_begin_time', time() - 3600])
            ->count();
        return $count;

    }

    /**
     * 人工信审检查历史完成
     * @param $module
     * @return int|bool
     */
    public function manualCheckLogFinish($module){
        $panCode = $this->order->loanPerson->pan_code;
        $packageName = $this->order->is_export == UserLoanOrder::IS_EXPORT_YES ?
            explode('_',$this->order->clientInfoLog->app_market)[1] :
            $this->order->clientInfoLog->package_name;
        $list = ManualCreditRules::getAllManualRules($module);
        if(count($list) != 1){
            return false;
        }
        foreach ($list as $ruleId => $value){
            $query = ManualCreditLog::find()
                ->select(['r' => '(que_info->\'$."'.$ruleId.'"\')','created_at'])
                ->where(['pan_code' => $panCode,'package_name' => $packageName,'action' => ManualCreditLog::ACTION_AUDIT_CREDIT])
                ->andWhere('`que_info`->\'$."'.$ruleId.'"\' = "1"')
                ->orderBy(['id' => SORT_DESC])
                ->limit(1);
            if($value['head_code'] == 'Module4'){
                $query->andWhere(['>','created_at',time() - 3600]);
            }

            $resLoan = $query->asArray()->one();
            if($resLoan){
                return $ruleId;
            }
            $resSaas = $query->asArray()->one(\Yii::$app->get('db_loan'));
            if($resSaas){
                return $ruleId;
            }
        }
        return false;
    }


    /**
     * 人工信审审核拒绝
     * @param $audit_remark //审核备注
     * @param $interval   //可再借选择
     * @param $rejectRule   //拒绝规则信息
     * @param $question     //问题
     * @param $isAuto
     * @return bool
     */
    public function manualCheckReject($audit_remark = '', $interval, $rejectRule, $question =[],$isAuto = 0){
        if(!$this->order->canReviewOrder($this->operator,$isAuto)){
            $this->setError('You cannot review the order');
            return false;
        }
        $headCode = $rejectRule['head_code'] ?? '';
        $backCode = $rejectRule['back_code'] ?? '';

        $packageName = $this->order->is_export == UserLoanOrder::IS_EXPORT_YES ?
            explode('_',$this->order->clientInfoLog->app_market)[1] :
            $this->order->clientInfoLog->package_name;
        //添加人工审核记录
        ManualCreditLog::addLog(
            $this->order->id,
            $this->merchantId,
            $this->order->loanPerson->pan_code,
            $packageName,
            $this->operator,
            ManualCreditLog::ACTION_AUDIT_CREDIT,
            ManualCreditLog::TYPE_REJECT,
            $rejectRule['id'],
            $question,
            $audit_remark,
            $isAuto ? 1 : 0
        );
        $after_status = UserLoanOrder::STATUS_CHECK_REJECT;
        $loanPerson = LoanPerson::findOne($this->order->user_id);
        $service = new UserCanLoanCheckService($loanPerson);
        $service->setCanLoanTime($interval);
        try{
            $sendMessageService = new SendMessageService();
            $messageService     = new ExternalOrderMessageService();
            $messageForm        = new ExternalOrderMessageForm();
            $productName        = $messageService->getProductName($this->order->is_export,$this->order->clientInfoLog->app_market);

            $sendMessageService->phone       = $this->order->loanPerson->phone;
            $sendMessageService->packageName = $this->order->is_export ? $this->order->productSetting->product_name : $this->order->clientInfoLog->package_name;
            $sendMessageService->productName = $productName;

            $messageForm->merchantId  = $this->order->merchant_id;
            $messageForm->userId      = $this->order->user_id;
            $messageForm->phone       = $this->order->loanPerson->phone;
            $messageForm->orderUuid   = $this->order->order_uuid;
            $messageForm->packageName = $this->order->clientInfoLog->package_name;
            $messageForm->productName = $productName;
            $messageForm->title       = ExternalOrderMessageForm::TITLE_PRE_RISK_REJECT;
            $messageForm->message     = $sendMessageService->getMsgContent($this->order->is_export,'checkReject');
            //发短信
            $messageService->pushToMessageQueue($messageForm);

            //推送区分内外部
            $messageForm->message  = $sendMessageService->getMsgContent($this->order->is_export,'pushCheckReject');
            if(UserLoanOrder::IS_EXPORT_YES == $this->order->is_export)
            {
                $messageService->pushToExternalPushQueue($messageForm);
                $this->pushExternalOrderCanLoanTime($this->order->order_uuid, $interval);
            }else{
                $messageService->pushToInsidePushQueue($messageForm);
            }

        }catch (\Exception $e){
            $service = new WeWorkService();
            $service->send($e->getMessage().' in '.$e->getTraceAsString());
        }
        return $this->changeOrderAllStatus(
            [
                'after_status' => $after_status,
                'after_audit_status' => UserLoanOrder::AUDIT_STATUS_MANUAL_CHECK_FINISH
            ],
            $audit_remark, $this->operator,['verifyCode' => [$headCode, $backCode]]);

    }

    /**
     * 人工信审审核通过
     * @param $audit_remark //审核备注
     * @param $question     //电核问题
     * @param $isAuto
     * @return bool
     */
    public function manualCheckApprove($audit_remark = '', $question =[], $isAuto = 0){
        if(!$this->order->canReviewOrder($this->operator,$isAuto)){
            $this->setError('You cannot review the order');
            return false;
        }

        /** @var UserBankAccount $bankAccount */
        $bankAccount = UserBankAccount::find()
            ->where([
                'id' => $this->order->card_id,
                'user_id' => $this->order->user_id,
            ])->one();

        //如果银行卡未完成姓名比对
        if(UserBankAccount::STATUS_SUCCESS != $bankAccount->status)
        {
            if(!empty($bankAccount->report_account_name)){
                $count = CommonHelper::nameDiff($this->order->loanPerson->name, $bankAccount->report_account_name);
                $diffCheck =  CommonHelper::nameCompare($this->order->loanPerson->name, $bankAccount->report_account_name);
                if($diffCheck || $count >= 1)
                {
                    $bankAccount->status = UserBankAccount::STATUS_SUCCESS;
                    $checkResult = BankCardAutoCheckLog::RESULT_PASS;
                    $bankAccount->save();

                }else{
                    //如果没有通过机审，则判断是否跳过
                    if(BankCardAutoCheckLog::checkCanSkip())
                    {
                        $bankAccount->status = UserBankAccount::STATUS_SUCCESS;
                        $checkResult = BankCardAutoCheckLog::RESULT_SKIP;
                    }else{
                        $checkResult = BankCardAutoCheckLog::RESULT_MANUAL;
                    }
                }


            }else{
                $checkResult = BankCardAutoCheckLog::RESULT_MANUAL;
            }

            $log = new BankCardAutoCheckLog();
            $log->user_id = $this->order->user_id;
            $log->order_id = $this->order->id;
            $log->result = $checkResult;
            $log->save();
        }

        $needBindCard = false;
        //如果绑定卡是已认证通过的，则直接进入分配资方环节，否则进入人审
        if(UserBankAccount::STATUS_SUCCESS != $bankAccount->status)
        {
            $afterAllStatus = [
                'after_status' => UserLoanOrder::STATUS_WAIT_DEPOSIT,
                'after_loan_status' => UserLoanOrder::LOAN_STATUS_BIND_CARD_CHECK,
            ];
            $needBindCard = true;
        }else{
            $step = $this->getDrawNexSept();
            switch ($step){
                case DrawStep::FUND_MATCH()->getValue():
                    $afterAllStatus = [
                        'after_status'      => UserLoanOrder::STATUS_LOANING,
                        'after_loan_status' => UserLoanOrder::LOAN_STATUS_FUND_MATCH,
                    ];
                    break;
                case DrawStep::MANUAL_DRAW()->getValue():
                    $afterAllStatus = [
                        'after_status'      => UserLoanOrder::STATUS_WAIT_DRAW_MONEY,
                        'after_loan_status' => UserLoanOrder::LOAN_STATUS_DRAW_MONEY,
                    ];
                    $sendDrawMoneyMsg = true;
                    break;
                case DrawStep::AUTO_DRAW()->getValue():
                    $afterAllStatus = [
                        'after_status'      => UserLoanOrder::STATUS_WAIT_DRAW_MONEY,
                        'after_loan_status' => UserLoanOrder::LOAN_STATUS_DRAW_MONEY,
                    ];
                    $this->order->auto_draw = UserLoanOrder::AUTO_DRAW_YES;
                    $this->order->auto_draw_time = time() + 3600;
                    break;
                default:
                    break;
            }
        }

        $packageName = $this->order->is_export == UserLoanOrder::IS_EXPORT_YES ?
            explode('_',$this->order->clientInfoLog->app_market)[1] :
            $this->order->clientInfoLog->package_name;
        //添加人工审核记录
        ManualCreditLog::addLog(
            $this->order->id,
            $this->merchantId,
            $this->order->loanPerson->pan_code,
            $packageName,
            $this->operator,
            ManualCreditLog::ACTION_AUDIT_CREDIT,
            ManualCreditLog::TYPE_PASS,
            0,
            $question,
            $audit_remark,
            $isAuto ? 1 : 0
        );

        $afterAllStatus['after_audit_status'] =  UserLoanOrder::AUDIT_STATUS_MANUAL_CHECK_FINISH;
        $result = $this->changeOrderAllStatus($afterAllStatus, $audit_remark,$this->operator);

        if($needBindCard){
            RedisQueue::push([RedisQueue::PUSH_MANUAL_CREDIT_BANK_ORDER_DATA, json_encode([
                'order_id' => $this->order->id,
                'package_name' => $packageName,
                'pan_code' => $this->order->loanPerson->pan_code,
                'bank_account' => $bankAccount->account
            ])]);
            $operateKey = sprintf('%s:%s', RedisQueue::BEFORE_MANUAL_CREDIT_BANK_ORDER_ALERT,$this->order->id);
            RedisQueue::set(['key' => $operateKey, 'value' => 'true', 'expire' => 30]);
        }
        return $result;
    }

    /**
     * 绑卡审核拒绝
     * @param $audit_remark //审核备注
     * @param $rejectRule
     * @param $question
     * @param $isAuto
     * @return bool
     */
    public function bankCheckReject($audit_remark, $rejectRule, $question = [],$isAuto = 0){
        if(!$this->order->canReviewBankOrder($this->operator,$isAuto)){
            $this->setError('You cannot review the order');
            return false;
        }

        $headCode = $rejectRule['head_code'] ?? '';
        $backCode = $rejectRule['back_code'] ?? '';

        /** @var UserBankAccount $bankAccount */
        $bankAccount = UserBankAccount::find()->where(['id' => $this->order->card_id, 'user_id' => $this->order->user_id])->one();
        if(is_null($bankAccount))
        {
            $this->setError('bank account not find');
            return false;
        }
        $bankAccount->status = UserBankAccount::STATUS_FAILED;
        $bankAccount->save();

        $packageName = $this->order->is_export == UserLoanOrder::IS_EXPORT_YES ?
            explode('_',$this->order->clientInfoLog->app_market)[1] :
            $this->order->clientInfoLog->package_name;
        //添加人工审核记录
        ManualCreditLog::addLog(
            $this->order->id,
            $this->merchantId,
            $this->order->loanPerson->pan_code,
            $packageName,
            $this->operator,
            ManualCreditLog::ACTION_AUDIT_BANK,
            ManualCreditLog::TYPE_REJECT,
            $rejectRule['id'],
            $question,
            $audit_remark,
            $isAuto ? 1 : 0,
            $bankAccount->account
        );

        if($this->order->bank_num > 2){
            $arr = ['after_status' => UserLoanOrder::STATUS_DEPOSIT_REJECT];
        }else{
            try{
                $sendMessageService = new SendMessageService();
                $messageService     = new ExternalOrderMessageService();
                $messageForm        = new ExternalOrderMessageForm();
                $productName        = $messageService->getProductName($this->order->is_export,$this->order->clientInfoLog->app_market);

                $sendMessageService->phone       = $this->order->loanPerson->phone;
                $sendMessageService->packageName = $this->order->is_export ? $this->order->productSetting->product_name : $this->order->clientInfoLog->package_name;
                $sendMessageService->productName = $productName;

                $messageForm->merchantId  = $this->order->merchant_id;
                $messageForm->userId      = $this->order->user_id;
                $messageForm->phone       = $this->order->loanPerson->phone;
                $messageForm->orderUuid     = $this->order->order_uuid;
                $messageForm->packageName = $this->order->clientInfoLog->package_name;
                $messageForm->productName = $productName;
                $messageForm->title       = ExternalOrderMessageForm::TITLE_BIND_CARD_REJECT;
                $messageForm->message     = $sendMessageService->getMsgContent($this->order->is_export,'bindCardReject');
                //发短信
                $messageService->pushToMessageQueue($messageForm);

                //推送区分内外部
                $messageForm->message  = $sendMessageService->getMsgContent($this->order->is_export,'pushBindCardReject');
                if(UserLoanOrder::IS_EXPORT_YES == $this->order->is_export)
                {
                    $messageService->pushToExternalPushQueue($messageForm);
                }else{
                    $messageService->pushToInsidePushQueue($messageForm);
                }

            }catch (\Exception $e){
                $service = new WeWorkService();
                $service->send($e->getMessage().' in '.$e->getTraceAsString());
            }
            $arr = [
                'after_audit_bank_status' => UserLoanOrder::AUDIT_BANK_STATUS_GET_ORDER,
                'after_loan_status' => UserLoanOrder::LOAN_STATUS_WAIT_BIND_CARD,
                'bank_num' => 1
            ];

            $dataStr = json_encode([
                'order_id'     => $this->order->id,
                'user_id'      => $this->order->user_id,
                'delay_minute' => 30,
            ]);
            //入提示延迟队列
            RedisDelayQueue::pushDelayQueue(RedisQueue::QUEUE_BIND_CARD_REJECT, $dataStr, 1800);

        }

        return $this->changeOrderAllStatus($arr, $audit_remark, $this->operator,['verifyCode' => [$headCode, $backCode]]);
    }

    /**
     * 绑卡审核通过
     * @param $audit_remark //审核备注
     * @param $question
     * @param $isAuto
     * @return bool
     */
    public function bankCheckApprove($audit_remark = '', $question = [], $isAuto = 0){
        if(!$this->order->canReviewBankOrder($this->operator,$isAuto)){
            $this->setError('You cannot review the order');
            return false;
        }
        /** @var UserBankAccount $bankAccount */
        $bankAccount = UserBankAccount::find()->where(['user_id' => $this->order->user_id , 'id' => $this->order->card_id])->one();
        if(is_null($bankAccount)){
            throw new yii\base\Exception('userBankAccount not find');
        }
        $bankAccount->status = UserBankAccount::STATUS_SUCCESS;
        $bankAccount->save();

        $packageName = $this->order->is_export == UserLoanOrder::IS_EXPORT_YES ?
            explode('_',$this->order->clientInfoLog->app_market)[1] :
            $this->order->clientInfoLog->package_name;
        //添加人工审核记录
        ManualCreditLog::addLog(
            $this->order->id,
            $this->merchantId,
            $this->order->loanPerson->pan_code,
            $packageName,
            $this->operator,
            ManualCreditLog::ACTION_AUDIT_BANK,
            ManualCreditLog::TYPE_PASS,
            0,
            $question,
            $audit_remark,
            $isAuto ? 1 : 0,
            $bankAccount->account
        );

        $step = $this->getDrawNexSept();
        switch ($step){
            case DrawStep::FUND_MATCH()->getValue():
                $afterAllStatus = [
                    'after_status'      => UserLoanOrder::STATUS_LOANING,
                    'after_loan_status' => UserLoanOrder::LOAN_STATUS_FUND_MATCH,
                ];
                break;
            case DrawStep::MANUAL_DRAW()->getValue():
                $afterAllStatus = [
                    'after_status'      => UserLoanOrder::STATUS_WAIT_DRAW_MONEY,
                    'after_loan_status' => UserLoanOrder::LOAN_STATUS_DRAW_MONEY,
                ];
                $packageName = $this->order->clientInfoLog->package_name;
                $this->sendMsgAndPushByOrderApprove($packageName, 0);
                break;
            case DrawStep::AUTO_DRAW()->getValue():
                $afterAllStatus = [
                    'after_status'      => UserLoanOrder::STATUS_WAIT_DRAW_MONEY,
                    'after_loan_status' => UserLoanOrder::LOAN_STATUS_DRAW_MONEY,
                ];
                $this->order->auto_draw = UserLoanOrder::AUTO_DRAW_YES;
                $this->order->auto_draw_time = time() + 3600;
                break;
            default:
                break;
        }

        return $this->changeOrderAllStatus($afterAllStatus, $audit_remark,$this->operator);
    }

    /**
     * 绑卡超时拒绝
     * @param $auditRemark
     * @return bool
     */
    public function bankTimeoutReject($auditRemark = ''){
        if(UserLoanOrder::STATUS_WAIT_DEPOSIT != $this->order->status)
        {
            return false;
        }
        if(!in_array($this->order->loan_status, [UserLoanOrder::LOAN_STATUS_WAIT_BIND_CARD,UserLoanOrder::LOAN_STATUS_BIND_CARD_CHECK]))
        {
            return false;
        }
        return $this->changeOrderAllStatus([ 'after_status' =>UserLoanOrder::STATUS_DEPOSIT_REJECT], $auditRemark);
    }

    /**
     * 人审超时拒绝
     * @param $auditRemark
     * @return bool
     */
    public function manualTimeoutReject($auditRemark = ''){
        if(UserLoanOrder::STATUS_CHECK != $this->order->status)
        {
            return false;
        }
        if(!in_array($this->order->audit_status, [UserLoanOrder::AUDIT_STATUS_GET_ORDER,UserLoanOrder::AUDIT_STATUS_MANUAL_CHECK]))
        {
            return false;
        }
        return $this->changeOrderAllStatus([ 'after_status' =>UserLoanOrder::STATUS_CHECK_REJECT], $auditRemark);
    }

    /**
     * 分配资方超时拒绝
     * @param $auditRemark
     * @return bool
     */
    public function updateLoanTimeoutReject($auditRemark = '', $interval = 0){
        if(UserLoanOrder::STATUS_LOANING != $this->order->status)
        {
            return false;
        }
        if(UserLoanOrder::LOAN_STATUS_FUND_MATCH != $this->order->loan_status)
        {
            return false;
        }
        try{
            $sendMessageService = new SendMessageService();
            $messageService     = new ExternalOrderMessageService();
            $messageForm        = new ExternalOrderMessageForm();
            $productName        = $messageService->getProductName($this->order->is_export,$this->order->clientInfoLog->app_market);

            $sendMessageService->phone       = $this->order->loanPerson->phone;
            $sendMessageService->packageName = $this->order->is_export ? $this->order->productSetting->product_name : $this->order->clientInfoLog->package_name;
            $sendMessageService->productName = $productName;

            $messageForm->merchantId  = $this->order->merchant_id;
            $messageForm->userId      = $this->order->user_id;
            $messageForm->phone       = $this->order->loanPerson->phone;
            $messageForm->orderUuid   = $this->order->order_uuid;
            $messageForm->packageName = $this->order->clientInfoLog->package_name;
            $messageForm->productName = $productName;
            $messageForm->title       = ExternalOrderMessageForm::TITLE_FUND_REJECT;
            $messageForm->message     = $sendMessageService->getMsgContent($this->order->is_export,'fundReject');
            //发短信
            $messageService->pushToMessageQueue($messageForm);

            //推送区分内外部
            $messageForm->message  = $sendMessageService->getMsgContent($this->order->is_export,'pushFundReject');
            if(UserLoanOrder::IS_EXPORT_YES == $this->order->is_export)
            {
                $messageService->pushToExternalPushQueue($messageForm);
                if($interval > 0)
                {
                    $this->pushExternalOrderCanLoanTime($this->order->order_uuid, $interval);
                }
            }else{
                $messageService->pushToInsidePushQueue($messageForm);
            }
        }catch (\Exception $e){
            $service = new WeWorkService();
            $service->send($e->getMessage().' in '.$e->getTraceAsString());
        }

        if($interval > 0)
        {
            $loanPerson = LoanPerson::findOne($this->order->user_id);
            $service = new UserCanLoanCheckService($loanPerson);
            $service->setCanLoanTime($interval);
        }

        return $this->changeOrderAllStatus([ 'after_status' =>UserLoanOrder::STATUS_LOAN_REJECT], $auditRemark);
    }


    /**
     * 提现超时驳回订单
     * @return bool
     */
    public function withdrawalTimeoutReject()
    {
        if(UserLoanOrder::STATUS_WAIT_DRAW_MONEY != $this->order->status)
        {
            return false;
        }
        try{
            $sendMessageService = new SendMessageService();
            $messageService     = new ExternalOrderMessageService();
            $messageForm        = new ExternalOrderMessageForm();
            $productName        = $messageService->getProductName($this->order->is_export,$this->order->clientInfoLog->app_market);

            $sendMessageService->phone       = $this->order->loanPerson->phone;
            $sendMessageService->packageName = $this->order->is_export ? $this->order->productSetting->product_name : $this->order->clientInfoLog->package_name;
            $sendMessageService->productName = $productName;

            $messageForm->merchantId  = $this->order->merchant_id;
            $messageForm->userId      = $this->order->user_id;
            $messageForm->phone       = $this->order->loanPerson->phone;
            $messageForm->orderUuid   = $this->order->order_uuid;
            $messageForm->packageName = $this->order->clientInfoLog->package_name;
            $messageForm->productName = $productName;
            $messageForm->title       = ExternalOrderMessageForm::TITLE_WITHDRAWAL_REJECT;
            $messageForm->message     = $sendMessageService->getMsgContent($this->order->is_export,'withdrawalReject');
            //发短信
            $messageService->pushToMessageQueue($messageForm);
            //推送区分内外部
            $messageForm->message  = $sendMessageService->getMsgContent($this->order->is_export,'pushWithdrawalReject');
            if(UserLoanOrder::IS_EXPORT_YES == $this->order->is_export)
            {
                $messageService->pushToExternalPushQueue($messageForm);
            }else{
                $messageService->pushToInsidePushQueue($messageForm);
            }

        }catch (\Exception $e){
            $service = new WeWorkService();
            $service->send($e->getMessage().' in '.$e->getTraceAsString());
        }

        $auditRemark = '提现超时';
        return $this->changeOrderAllStatus([ 'after_status' =>UserLoanOrder::STATUS_WAIT_DRAW_MONEY_TIMEOUT], $auditRemark);

    }

    /**
     * 获取数据阶段 驳回订单
     * @param $auditRemark
     * @return bool
     */
    public function getDataStageReject($auditRemark)
    {
        if(UserLoanOrder::STATUS_CHECK != $this->order->status)
        {
            return false;
        }
        if(UserLoanOrder::AUDIT_STATUS_GET_DATA != $this->order->audit_status)
        {
            return false;
        }
        return $this->changeOrderAllStatus([ 'after_status' =>UserLoanOrder::STATUS_CHECK_REJECT],$auditRemark);

    }

    /**
     * 前置风控阶段 驳回订单
     * @param $auditRemark
     * @return bool
     */
    public function getDataReject($auditRemark, $headCode, $backCode, $interval)
    {
        if(UserLoanOrder::STATUS_CHECK != $this->order->status)
        {
            return false;
        }
        if(UserLoanOrder::AUDIT_STATUS_GET_DATA != $this->order->audit_status)
        {
            return false;
        }
        $loanPerson = LoanPerson::findOne($this->order->user_id);
        $service = new UserCanLoanCheckService($loanPerson);
        $service->setCanLoanTime($interval);
        try{
            $sendMessageService = new SendMessageService();
            $messageService     = new ExternalOrderMessageService();
            $messageForm        = new ExternalOrderMessageForm();
            $productName        = $messageService->getProductName($this->order->is_export,$this->order->clientInfoLog->app_market);

            $sendMessageService->phone       = $this->order->loanPerson->phone;
            $sendMessageService->packageName = $this->order->is_export ? $this->order->productSetting->product_name : $this->order->clientInfoLog->package_name;
            $sendMessageService->productName = $productName;

            $messageForm->merchantId  = $this->order->merchant_id;
            $messageForm->userId      = $this->order->user_id;
            $messageForm->phone       = $this->order->loanPerson->phone;
            $messageForm->orderUuid   = $this->order->order_uuid;
            $messageForm->packageName = $this->order->clientInfoLog->package_name;
            $messageForm->productName = $productName;
            $messageForm->title       = ExternalOrderMessageForm::TITLE_PRE_RISK_REJECT;
            $messageForm->message     = $sendMessageService->getMsgContent($this->order->is_export,'preRiskReject');
            //发短信
            $messageService->pushToMessageQueue($messageForm);
            //推送区分内外部
            $messageForm->message  = $sendMessageService->getMsgContent($this->order->is_export,'pushPreRiskReject');
            if(UserLoanOrder::IS_EXPORT_YES == $this->order->is_export)
            {
                $messageService->pushToExternalPushQueue($messageForm);
                $this->pushExternalOrderCanLoanTime($this->order->order_uuid, $interval);
            }else{
                $messageService->pushToInsidePushQueue($messageForm);
            }

        }catch (\Exception $e){
            $service = new WeWorkService();
            $service->send($e->getMessage().' in '.$e->getTraceAsString());
        }

        return $this->changeOrderAllStatus([ 'after_status' => UserLoanOrder::STATUS_CHECK_REJECT],
            $auditRemark,0, ['verifyCode' => [$headCode, $backCode]]);

    }

    /**
     * 机审阶段 驳回订单
     * @param $auditRemark
     * @return bool
     */
    public function autoCheckReject($auditRemark, $headCode, $backCode, $interval)
    {
        if(UserLoanOrder::STATUS_CHECK != $this->order->status)
        {
            return false;
        }
        if(UserLoanOrder::AUDIT_STATUS_AUTO_CHECK != $this->order->audit_status)
        {
            return false;
        }
        $loanPerson = LoanPerson::findOne($this->order->user_id);
        $service = new UserCanLoanCheckService($loanPerson);
        $service->setCanLoanTime($interval);

        try{
            $sendMessageService = new SendMessageService();
            $messageService     = new ExternalOrderMessageService();
            $messageForm        = new ExternalOrderMessageForm();
            $productName        = $messageService->getProductName($this->order->is_export,$this->order->clientInfoLog->app_market);
            $sendMessageService->phone       = $this->order->loanPerson->phone;
            $sendMessageService->packageName = $this->order->is_export ? $this->order->productSetting->product_name : $this->order->clientInfoLog->package_name;
            $sendMessageService->productName = $productName;

            $messageForm->merchantId  = $this->order->merchant_id;
            $messageForm->userId      = $this->order->user_id;
            $messageForm->phone       = $this->order->loanPerson->phone;
            $messageForm->orderUuid   = $this->order->order_uuid;
            $messageForm->packageName = $this->order->clientInfoLog->package_name;
            $messageForm->productName = $productName;
            $messageForm->title       = ExternalOrderMessageForm::TITLE_PRE_RISK_REJECT;
            $messageForm->message     = $sendMessageService->getMsgContent($this->order->is_export,'mainRiskReject');
            //发短信
            $messageService->pushToMessageQueue($messageForm);
            //推送区分内外部
            $messageForm->message  = $sendMessageService->getMsgContent($this->order->is_export,'pushMainRiskReject');
            if(UserLoanOrder::IS_EXPORT_YES == $this->order->is_export)
            {
                $messageService->pushToExternalPushQueue($messageForm);
                $this->pushExternalOrderCanLoanTime($this->order->order_uuid, $interval);
            }else{
                $messageService->pushToInsidePushQueue($messageForm);
            }


            //将拒绝订单推送给aglow
            if(mt_rand(0,1) == 1)
            {
//                RedisQueue::push([RedisQueue::LIST_AGLOW_LOAN_APPLY_REJECT, $this->order->id]);
            }
        }catch (\Exception $e){
            $service = new WeWorkService();
            $service->send($e->getMessage().' in '.$e->getTraceAsString());
        }
        return $this->changeOrderAllStatus([ 'after_status' => UserLoanOrder::STATUS_CHECK_REJECT],
            $auditRemark,0, ['verifyCode' => [$headCode, $backCode]]);

    }



    /**
     * 机审借款 通过订单
     * @return bool
     */
    public function autoCheckApprove()
    {
        if(UserLoanOrder::STATUS_CHECK != $this->order->status)
        {
            return false;
        }
        if(UserLoanOrder::AUDIT_STATUS_AUTO_CHECK != $this->order->audit_status)
        {
            return false;
        }

        $needBindCard = false;
        $bankAccountStr = '';
        //如果订单没有绑卡，则提示用户绑卡
        if(empty($this->order->card_id))
        {
            //$this->sendMsgAndPushByBankCardError($this->order->clientInfoLog->package_name);
            $afterAllStatus = [
                'after_status' => UserLoanOrder::STATUS_WAIT_DEPOSIT
            ];
        }else{
            /** @var UserBankAccount $bankAccount */
            $bankAccount = UserBankAccount::find()
                ->where([
                    'id' => $this->order->card_id,
                    'user_id' => $this->order->user_id,
                ])->one();

            //如果银行卡未完成姓名比对
            if(UserBankAccount::STATUS_SUCCESS != $bankAccount->status)
            {
                if(!empty($bankAccount->report_account_name)){
                    $count = CommonHelper::nameDiff($this->order->loanPerson->name, $bankAccount->report_account_name);
                    $diffCheck = CommonHelper::nameCompare($this->order->loanPerson->name, $bankAccount->report_account_name);
                    if($diffCheck || $count >= 1)
                    {
                        $bankAccount->status = UserBankAccount::STATUS_SUCCESS;
                        $checkResult = BankCardAutoCheckLog::RESULT_PASS;
                        $bankAccount->save();

                    }else{
                        //如果没有通过机审，则判断是否跳过
                        if(BankCardAutoCheckLog::checkCanSkip())
                        {
                            $bankAccount->status = UserBankAccount::STATUS_SUCCESS;
                            $checkResult = BankCardAutoCheckLog::RESULT_SKIP;
                        }else{
                            $checkResult = BankCardAutoCheckLog::RESULT_MANUAL;
                        }
                    }


                }else{
                    $checkResult = BankCardAutoCheckLog::RESULT_MANUAL;
                }

                $log = new BankCardAutoCheckLog();
                $log->user_id = $this->order->user_id;
                $log->order_id = $this->order->id;
                $log->result = $checkResult;
                $log->save();
            }


            //如果绑定卡是已认证通过的，则直接进入分配资方环节，否则进入人审
            if(UserBankAccount::STATUS_SUCCESS != $bankAccount->status)
            {
                $afterAllStatus = [
                    'after_status' => UserLoanOrder::STATUS_WAIT_DEPOSIT,
                    'after_loan_status' => UserLoanOrder::LOAN_STATUS_BIND_CARD_CHECK,
                ];
                $bankAccountStr = $bankAccount->account;
                $needBindCard = true;
            }else{
                $step = $this->getDrawNexSept();
                switch ($step){
                    case DrawStep::FUND_MATCH()->getValue():
                        $afterAllStatus = [
                            'after_status'      => UserLoanOrder::STATUS_LOANING,
                            'after_loan_status' => UserLoanOrder::LOAN_STATUS_FUND_MATCH,
                        ];
                        break;
                    case DrawStep::MANUAL_DRAW()->getValue():
                        $afterAllStatus = [
                            'after_status'      => UserLoanOrder::STATUS_WAIT_DRAW_MONEY,
                            'after_loan_status' => UserLoanOrder::LOAN_STATUS_DRAW_MONEY,
                        ];
                        $packageName = $this->order->clientInfoLog->package_name;
                        $this->sendMsgAndPushByOrderApprove($packageName, 0);
                        break;
                    case DrawStep::AUTO_DRAW()->getValue():
                        $afterAllStatus = [
                            'after_status'      => UserLoanOrder::STATUS_WAIT_DRAW_MONEY,
                            'after_loan_status' => UserLoanOrder::LOAN_STATUS_DRAW_MONEY,
                        ];
                        $this->order->auto_draw = UserLoanOrder::AUTO_DRAW_YES;
                        $this->order->auto_draw_time = time() + 3600;
                        break;
                    default:
                        break;
                }

            }

        }
        $auditRemark = 'auto approve';
        $result = $this->changeOrderAllStatus($afterAllStatus ,$auditRemark);

        if($needBindCard){
            $packageName = $this->order->is_export == UserLoanOrder::IS_EXPORT_YES ?
                explode('_',$this->order->clientInfoLog->app_market)[1] :
                $this->order->clientInfoLog->package_name;
            RedisQueue::push([RedisQueue::PUSH_MANUAL_CREDIT_BANK_ORDER_DATA, json_encode([
                'order_id' => $this->order->id,
                'package_name' => $packageName,
                'pan_code' => $this->order->loanPerson->pan_code,
                'bank_account' => $bankAccountStr
            ])]);
            $operateKey = sprintf('%s:%s', RedisQueue::BEFORE_MANUAL_CREDIT_BANK_ORDER_ALERT,$this->order->id);
            RedisQueue::set(['key' => $operateKey, 'value' => 'true', 'expire' => 30]);
        }
        return $result;
    }

    public function sendMsgAndPushByOrderApprove(string $packageName, int $timeMinutes = -1)
    {
        $sendMessageService = new SendMessageService();
        $messageService     = new ExternalOrderMessageService();
        $messageForm        = new ExternalOrderMessageForm();
        $productName        = $messageService->getProductName($this->order->is_export,$this->order->clientInfoLog->app_market);

        $sendMessageService->phone       = $this->order->loanPerson->phone;
        $sendMessageService->packageName = $this->order->is_export ? $this->order->productSetting->product_name : $this->order->clientInfoLog->package_name;
        $sendMessageService->productName = $productName;

        $messageForm->merchantId  = $this->order->merchant_id;
        $messageForm->userId      = $this->order->user_id;
        $messageForm->phone       = $this->order->loanPerson->phone;
        $messageForm->orderUuid   = $this->order->order_uuid;
        $messageForm->packageName = $this->order->clientInfoLog->package_name;
        $messageForm->productName = $productName;
        $messageForm->title       = '';

        //短信
        switch ($timeMinutes) {
            case 0:
                $messageForm->message  = $sendMessageService->getMsgContent($this->order->is_export,'withdrawal0');
                break;
            case 20:
                $messageForm->message  = $sendMessageService->getMsgContent($this->order->is_export,'withdrawal20');
                $voiceText = $sendMessageService->getMsgContent($this->order->is_export,'riskVoice');
                break;
            case 40:
                $messageForm->message  = $sendMessageService->getMsgContent($this->order->is_export,'withdrawal40');
                break;
            case 60:
                $messageForm->message  = $sendMessageService->getMsgContent($this->order->is_export,'withdrawal60');
                break;
            case 120:
                $messageForm->message  = $sendMessageService->getMsgContent($this->order->is_export,'withdrawal120');
                break;
            case 240:
                $messageForm->message  = $sendMessageService->getMsgContent($this->order->is_export,'withdrawal240');
                break;
            case -1:
                //默认值，兼容旧数据
            default:
                $messageForm->message  = $sendMessageService->getMsgContent($this->order->is_export,'withdrawal0');
                break;
        }
        //发短信
        $messageService->pushToMessageQueue($messageForm);

        if (isset($voiceText)) {
            MessageHelper::sendAll($this->order->loanPerson->phone, $voiceText, SendMessageService::$voiceConfigList[$messageForm->packageName]);
        }

        //推送区分内外部
        $messageForm->message  = $sendMessageService->getMsgContent($this->order->is_export,'pushMainRiskReject');
        if(UserLoanOrder::IS_EXPORT_YES == $this->order->is_export)
        {
            $messageService->pushToExternalPushQueue($messageForm);
        }else{
            $messageService->pushToInsidePushQueue($messageForm);
        }
    }

    public function sendMsgAndPushByBankCardError(string $packageName)
    {
        $pushService = new FirebasePushService($packageName);
        $pushText = "[{$packageName}] For the final step, please re-add an available bank card to get the loan.";
        $pushService->pushToUser($this->order->loanPerson->id, $packageName, $pushText);
        //短信
        MessageHelper::sendAll($this->order->loanPerson->phone, $pushText, SendMessageService::$smsConfigList[$packageName]);
        //使用天一泓，再次发送，（卢山要求，两个通道同时发送，时间：2019年11月18日）
//        MessageHelper::sendAll($this->order->loanPerson->phone, $pushText, SendMessageService::$smsSkyLineConfigList[$packageName]);
    }

    //还款一小时未复借提醒
    public function sendMsgAndPushByNoLoanAfterRepay()
    {

        $sendMessageService = new SendMessageService();
        $messageService     = new ExternalOrderMessageService();
        $messageForm        = new ExternalOrderMessageForm();
        $productName        = $messageService->getProductName($this->order->is_export,$this->order->clientInfoLog->app_market);

        $sendMessageService->phone       = $this->order->loanPerson->phone;
        $sendMessageService->packageName = $this->order->is_export ? $this->order->productSetting->product_name : $this->order->clientInfoLog->package_name;
        $sendMessageService->productName = $productName;

        $messageForm->merchantId  = $this->order->merchant_id;
        $messageForm->userId      = $this->order->user_id;
        $messageForm->phone       = $this->order->loanPerson->phone;
        $messageForm->orderUuid   = $this->order->order_uuid;
        $messageForm->packageName = $this->order->clientInfoLog->package_name;
        $messageForm->productName = $productName;
        $messageForm->title       = '';
        $messageForm->message     = $sendMessageService->getMsgContent($this->order->is_export,'repeatedBorrowing');
        //发短信
        $messageService->pushToMessageQueue($messageForm);
        //推送区分内外部
        $messageForm->message  = $sendMessageService->getMsgContent($this->order->is_export,'pushRepeatedBorrowing');
        if(UserLoanOrder::IS_EXPORT_YES == $this->order->is_export)
        {
            $messageService->pushToExternalPushQueue($messageForm);
        }else{
            $messageService->pushToInsidePushQueue($messageForm);
        }

    }

    //绑卡被拒 提示绑卡
    public function sendMsgAndPushByBindCardReject($key)
    {
        $sendMessageService = new SendMessageService();
        $messageService     = new ExternalOrderMessageService();
        $messageForm        = new ExternalOrderMessageForm();
        $productName        = $messageService->getProductName($this->order->is_export,$this->order->clientInfoLog->app_market);

        $sendMessageService->phone       = $this->order->loanPerson->phone;
        $sendMessageService->packageName = $this->order->is_export ? $this->order->productSetting->product_name : $this->order->clientInfoLog->package_name;
        $sendMessageService->productName = $productName;

        $messageForm->merchantId  = $this->order->merchant_id;
        $messageForm->userId      = $this->order->user_id;
        $messageForm->phone       = $this->order->loanPerson->phone;
        $messageForm->orderUuid   = $this->order->order_uuid;
        $messageForm->packageName = $this->order->clientInfoLog->package_name;
        $messageForm->productName = $productName;
        $messageForm->title       = '';
        $messageForm->message     = $sendMessageService->getMsgContent($this->order->is_export,$key);
        //发短信
        $messageService->pushToMessageQueue($messageForm);
    }

    /**
     * 机审借款 进入人工
     * @return bool
     */
    public function autoCheckManual($auditRemark, $headCode, $backCode)
    {
        if(UserLoanOrder::STATUS_CHECK != $this->order->status)
        {
            return false;
        }
        if(UserLoanOrder::AUDIT_STATUS_AUTO_CHECK != $this->order->audit_status)
        {
            return false;
        }

        $result = $this->changeOrderAllStatus([ 'after_audit_status' => UserLoanOrder::AUDIT_STATUS_GET_ORDER],$auditRemark,
            0,['verifyCode' => [$headCode, $backCode]]);

        if($result){
            //下单包
            $packageName = $this->order->is_export == UserLoanOrder::IS_EXPORT_YES ?
                explode('_',$this->order->clientInfoLog->app_market)[1] :
                $this->order->clientInfoLog->package_name;
            RedisQueue::push([RedisQueue::PUSH_MANUAL_CREDIT_ORDER_DATA, json_encode([
                'order_id' => $this->order->id,
                'package_name' => $packageName,
                'pan_code' => $this->order->loanPerson->pan_code
            ])]);
            $operateKey = sprintf('%s:%s', RedisQueue::BEFORE_MANUAL_CREDIT_ORDER_ALERT,$this->order->id);
            RedisQueue::set(['key' => $operateKey, 'value' => 'true', 'expire' => 30]);
        }
        return $result;
    }


    /**
     * 变更订单审核状态
     * @param array $afterStatus ['after_status' => 1, 'after_audit_status' => 2, 'after_loan_status' => 5]
     * @param $auditRemark
     * @param int $operator
     * @param array $params
     * @return bool
     */
    public function changeOrderAllStatus(array $afterStatus, $auditRemark, $operator = 0 , $params = [])
    {

        $log = new UserOrderLoanCheckLog();
        $log->user_id = $this->order->user_id;
        $log->order_id = $this->order->id;
        $log->before_status = $this->order->status;
        $log->after_status = $afterStatus['after_status'] ?? $this->order->status;
        $log->before_audit_status = $this->order->audit_status;
        $log->after_audit_status = $afterStatus['after_audit_status'] ?? $this->order->audit_status;
        $log->before_audit_bank_status = $this->order->audit_bank_status;
        $log->after_audit_bank_status = $afterStatus['after_audit_bank_status'] ?? $this->order->audit_bank_status;
        $log->before_loan_status = $this->order->loan_status;
        $log->after_loan_status = $afterStatus['after_loan_status'] ?? $this->order->loan_status;
        $log->operator = $operator;
        if(isset($params['verifyCode'])){  //审核码
            $log->head_code = $params['verifyCode'][0];
            $log->back_code = $params['verifyCode'][1];
            $log->reason_remark = '';
        }
        $log->audit_remark = $auditRemark;
        $log->type = UserOrderLoanCheckLog::TYPE_LOAN;
        $log->operation_type = UserOrderLoanCheckLog::LOAN_CHECK;
        if(!$log->save()){
            $this->setError('添加订单日志失败');
            return false;
        }

        //额外参数处理
        if(isset($params['question'])){  //电核问题更新
            $this->order->audit_question = json_encode($params['question']);
        }

        if(isset($afterStatus['after_status'])){
            $this->order->status = $afterStatus['after_status'];
            if($afterStatus['after_status'] == UserLoanOrder::STATUS_CHECK_REJECT){
                if(in_array($this->order->audit_status, [UserLoanOrder::AUDIT_STATUS_GET_ORDER, UserLoanOrder::AUDIT_STATUS_MANUAL_CHECK])){
                    $status = 'reject_risk_manual';
                }else{
                    $status = 'reject_risk_auto';
                }
            }

            if(in_array($afterStatus['after_status'], [UserLoanOrder::STATUS_DEPOSIT_REJECT, UserLoanOrder::STATUS_WAIT_DRAW_MONEY_TIMEOUT, UserLoanOrder::STATUS_LOAN_REJECT])){
                $status = 'reject_loan';
            }
        }

        if(isset($afterStatus['after_audit_status'])){
            $this->order->audit_status = $afterStatus['after_audit_status'];
            $this->order->audit_time = time();
            $this->order->audit_operator = $operator;
        }

        if(isset($afterStatus['after_audit_bank_status'])){
            $this->order->audit_bank_status = $afterStatus['after_audit_bank_status'];
            $this->order->audit_bank_operator = $operator;
        }

        if(isset($afterStatus['bank_num'])){
            $this->order->bank_num += 1;
        }

        if(isset($afterStatus['after_loan_status'])){
            $this->order->loan_status = $afterStatus['after_loan_status'];
        }
        if(!$this->order->save()){
            $this->setError('更新审核通过订单失败');
            return false;
        }
        if(isset($status)){
            $info = [
                'order_id'      => $this->order->id,
                'user_id'       => $this->order->user_id,
                'app_name'      => $this->order->clientInfoLog->package_name,
                'status'        => $status,
                'reject_reason' => $auditRemark,
                'data_version'  => time(),
            ];

            RedisQueue::push([RedisQueue::PUSH_ORDER_REJECT_DATA, json_encode($info)]);
        }

//        $this->order->trigger(UserLoanOrder::EVENT_AFTER_CHANGE_STATUS);
        return true;
    }


    /**
     * 获取订单操作人
     * @param int $operator
     */
    public function setOperator($operator)
    {
        $this->operator = $operator;
        return $this;
    }

    /**
     * 订单完成还款(结束)
     * @param $time
     * @param $operationType
     * @param $remark
     * @return bool
     */
    public function orderRepayCompeted($time, $operationType, $remark = ''){
        $beforeStatus = $this->repaymentOrder->status;
        $this->repaymentOrder->status = UserLoanOrderRepayment::STATUS_REPAY_COMPLETE;
        $this->repaymentOrder->closing_time = $time;
        $this->repaymentOrder->is_delay_repayment = UserLoanOrderRepayment::IS_DELAY_NO;
        $this->repaymentOrder->is_extend = UserLoanOrderRepayment::IS_EXTEND_NO;
        $this->order->status = UserLoanOrder::STATUS_PAYMENT_COMPLETE;
        if(!$this->order->save()){
            $this->setError('Update order failed');
            return false;
        }
        if(!$this->repaymentOrder->save()){
            $this->setError('Update order failed');
            return false;
        }
        //置为老用户
        $loanPerson = LoanPerson::findOne(['id'=>$this->order->user_id]);
        if($loanPerson){
            if(LoanPerson::CUSTOMER_TYPE_OLD != $loanPerson->customer_type){
                $loanPerson->customer_type = LoanPerson::CUSTOMER_TYPE_OLD;
                $loanPerson->save();
                //置为首单, 在放款成后已有此逻辑
//                $userLoanOrder = UserLoanOrder::findOne(['id'=>$this->order->id]);
//                if($userLoanOrder){
//                    $userLoanOrder->is_first = UserLoanOrder::FIRST_LOAN_IS;
//                    $userLoanOrder->save();
//                }
            }
        }

        //添加订单记录
        $log = new UserOrderLoanCheckLog();
        $log->order_id = $this->order->id;
        $log->repayment_id = $this->repaymentOrder->id;
        $log->before_status = $beforeStatus;
        $log->after_status = $this->repaymentOrder->status;
        $log->operator = $this->operator ?? 0;
        $log->type = UserOrderLoanCheckLog::TYPE_REPAY;
        $log->remark = $remark;
        $log->operation_type = $operationType;
        if (!$log->save()) {
            $this->setError('生成日志表失败');
            return false;
        }

        //修改优惠券状态
        if(!empty($this->repaymentOrder->coupon_id) && !is_null($this->repaymentOrder->userCouponInfo)){
            $this->repaymentOrder->userCouponInfo->changeStatus(UserCouponInfo::STATUS_SUCCESS, $time);
        }

        //当已还金额小于应还金额时，触发kudos发券逻辑
        if($this->repaymentOrder->true_total_money < $this->repaymentOrder->total_money)
        {
            //通知kudos 优惠券金额
            KudosService::orderUseCoupon($this->repaymentOrder->order_id, $this->repaymentOrder->total_money - $this->repaymentOrder->true_total_money);
        }

        //催收订单更新
        $loanCollectionService = new LoanCollectionService();
        $loanCollectionService->repaymentCompleteUpdate($this->repaymentOrder->id);

        //还款完成短信
        try{
            $sendMessageService = new SendMessageService();
            $messageService     = new ExternalOrderMessageService();
            $messageForm        = new ExternalOrderMessageForm();
            $productName        = $messageService->getProductName($this->order->is_export,$this->order->clientInfoLog->app_market);
            $sendMessageService->phone       = $this->order->loanPerson->phone;
            $sendMessageService->packageName = $this->order->is_export ? $this->order->productSetting->product_name : $this->order->clientInfoLog->package_name;
            $sendMessageService->productName = $productName;

            $messageForm->merchantId  = $this->order->merchant_id;
            $messageForm->userId      = $this->order->user_id;
            $messageForm->phone       = $this->order->loanPerson->phone;
            $messageForm->orderUuid   = $this->order->order_uuid;
            $messageForm->packageName = $this->order->clientInfoLog->package_name;
            $messageForm->productName = $productName;
            $messageForm->title       = ExternalOrderMessageForm::TITLE_REPAY_COMPLETE;
            $messageForm->message     = $sendMessageService->getMsgContent($this->order->is_export,'repayComplete');
            $messageService->pushToMessageQueue($messageForm);

            //推送区分内外部
            if(UserLoanOrder::IS_EXPORT_YES == $this->order->is_export)
            {
                $messageForm->message  = $sendMessageService->getMsgContent($this->order->is_export,'pushRepayComplete');
                $messageService->pushToExternalPushQueue($messageForm);
            }else{
                $messageForm->message  = $sendMessageService->getMsgContent($this->order->is_export,'pushRepayComplete');
                $messageService->pushToInsidePushQueue($messageForm);
            }

        }catch (\Exception $e){
            $service = new WeWorkService();
            $service->send($e->getMessage().' in '.$e->getTraceAsString());
        }

        //推送kudos还款消息
        KudosService::pushOrderClose($this->order->id, $this->repaymentTime(), date('Y-m-d', $time));

        //额度计算
        //老用户开启风险定价的提额逻辑
//        $creditLimitService = new UserCreditLimitService();
//        $creditLimitService->repaymentCreditLimitCalculation($this->repaymentOrder);
        $dataStr = json_encode([
            'order_id'     => $this->order->id,
            'user_id'      => $this->order->user_id,
            'delay_minute' => 60,
        ]);
        RedisDelayQueue::pushDelayQueue(RedisQueue::QUEUE_REMIND_NO_LOAN_AFTER_REPAY_AUTO, $dataStr, 3600);

        $params = [
            'user_id'          => $this->order->user_id,
            'order_id'         => $this->order->id,
            'app_name'         => $this->order->clientInfoLog->package_name,
            'closing_time'     => $this->repaymentOrder->closing_time,
            'overdue_fee'      => $this->repaymentOrder->overdue_fee,
            'is_overdue'       => $this->repaymentOrder->is_overdue == UserLoanOrderRepayment::IS_OVERDUE_YES ? 'y' : 'n',
            'overdue_day'      => $this->repaymentOrder->overdue_day,
            'total_money'      => $this->repaymentOrder->total_money,
            'true_total_money' => $this->repaymentOrder->true_total_money,
            'data_version'     => time(),
        ];
        RedisQueue::push([RedisQueue::PUSH_ORDER_REPAYMENT_SUCCESS_DATA, json_encode($params)]);
        RedisQueue::push([RedisQueue::REMIND_ORDER_CHANGE_STATUS, json_encode(['id' => $this->repaymentOrder->id, 'status' => RemindOrder::STATUS_REPAY_COMPLETE])]);

        return true;
    }

    /**
     * 订单部分还款
     * @param $remark
     * @return bool
     */
    public function orderPartRepay($remark = ''){
        $beforeStatus = $this->repaymentOrder->status;
        //添加订单记录
        $log = new UserOrderLoanCheckLog();
        $log->order_id = $this->order->id;
        $log->repayment_id = $this->repaymentOrder->id;
        $log->before_status = $beforeStatus;
        $log->after_status = $this->repaymentOrder->status;
        $log->operator = $this->operator ?? 0;
        $log->type = UserOrderLoanCheckLog::TYPE_REPAY;
        $log->remark = $remark;
        $log->operation_type = UserOrderLoanCheckLog::REPAY_PARTIAL;
        if (!$log->save()) {
            $this->setError('生成日志表失败');
            return false;
        }
        if(!$this->repaymentOrder->save()){
            $this->setError('Update order failed');
            return false;
        }
        return true;
    }

    /**
     * 还款方法
     * @param $amount int 单位分
     * @param int $type
     * @param bool $forceCompletion
     * @param int $paymentType
     * @return bool
     */
    public function repayment($amount, $type = UserRepaymentLog::TYPE_ACTIVE, $forceCompletion = false, $paymentType = 0)
    {
        $time = time();
        $transaction = Yii::$app->db->beginTransaction();
        try{
            //获取userLoanOrderRepayment对象
            $this->getUserLoanOrderRepayment();
            if($this->repaymentOrder->status == UserLoanOrderRepayment::STATUS_REPAY_COMPLETE){
                throw new Exception('The order has been repaid and cannot be deducted');
            }
            if(!in_array($type, array_keys(UserRepaymentLog::$typeMap))){
                throw new Exception('UserRepayment type error');
            }


            if(in_array($paymentType, [FinancialPaymentOrder::PAYMENT_TYPE_DELAY, FinancialPaymentOrder::PAYMENT_TYPE_DELAY_REDUCE]))
            {
                $isDelay = FinancialPaymentOrder::IS_DELAY_YES;
            }else{
                $isDelay = FinancialPaymentOrder::IS_DELAY_NO;
            }

            $splitAmount = $this->getRepaymentSplitAmount($amount, $this->repaymentOrder);
            $this->repaymentOrder->true_total_money += $amount;
            if (UserLoanOrderRepayment::IS_DELAY_NO == $this->repaymentOrder->is_delay_repayment) {
                $this->repaymentOrder->is_delay_repayment = $isDelay;
            }
            //saas
            if(is_null($this->repaymentOrder->is_delay_repayment)){
                $this->repaymentOrder->is_delay_repayment = UserLoanOrderRepayment::IS_DELAY_NO;
            }

            //免滞纳金的金额
            $reduceAmount = 0;
            if (UserLoanOrderRepayment::IS_DELAY_YES == $isDelay) {
                $startDelayTime = strtotime('today');
                $endDelayTime = $startDelayTime + 86400 * $this->order->loan_term;
                $this->repaymentOrder->delay_repayment_number += 1;
                $this->repaymentOrder->delay_repayment_time = $endDelayTime;

                //计算减免滞纳金的金额
                if(FinancialPaymentOrder::PAYMENT_TYPE_DELAY_REDUCE == $paymentType)
                {
                    //取当次减免金额和剩余应还金额的最小值
                    $reduceAmount = min($this->repaymentOrder->getScheduledPaymentAmount(), intval(($this->repaymentOrder->overdue_fee - $this->repaymentOrder->delay_reduce_amount) / 2));
                }

                $delayLog = new UserLoanOrderDelayPaymentLog();
                $delayLog->order_id = $this->repaymentOrder->order_id;
                $delayLog->user_id = $this->repaymentOrder->user_id;
                $delayLog->amount = $amount;
                $delayLog->delay_reduce_amount = $reduceAmount;
                $delayLog->delay_start_time = $startDelayTime;
                $delayLog->delay_end_time = $endDelayTime;
                $delayLog->save();
            }

            //如果订单逾期，查询这笔订单当前的催收员id
            if(UserLoanOrderRepayment::IS_DELAY_YES == $this->repaymentOrder->is_overdue)
            {
                $collectorID = LoanCollectionOrder::getCollectorIdByOrderId($this->order->id);
            }else{
                $collectorID = 0;
            }
            $repaymentLog = new UserRepaymentLog();
            $repaymentLog->user_id = $this->order->user_id;
            $repaymentLog->merchant_id = $this->merchantId;
            $repaymentLog->order_id = $this->order->id;
            $repaymentLog->type = $type;
            $repaymentLog->amount = $amount;
            $repaymentLog->success_time = $time;
            $repaymentLog->is_delay_repayment = $isDelay;
            $repaymentLog->principal_loan_money = $splitAmount['principal_loan_money'] ?? 0;
            $repaymentLog->principal_cost_fee = $splitAmount['principal_cost_fee'] ?? 0;
            $repaymentLog->principal = $repaymentLog->principal_loan_money + $repaymentLog->principal_cost_fee;
            $repaymentLog->interests = $splitAmount['interests'] ?? 0;
            $repaymentLog->overdue_fee = $splitAmount['overdueFee'] ?? 0;
            $repaymentLog->collector_id = $collectorID;
            $repaymentLog->save();

            $this->repaymentOrder->true_total_principal += $repaymentLog->principal;
            $this->repaymentOrder->true_total_principal_loan_money = min($this->repaymentOrder->principal - $this->repaymentOrder->cost_fee, $this->repaymentOrder->true_total_principal);
            $this->repaymentOrder->true_total_principal_cost_fee = $this->repaymentOrder->true_total_principal - $this->repaymentOrder->true_total_principal_loan_money;
            $this->repaymentOrder->true_total_interests += $repaymentLog->interests;
            $this->repaymentOrder->true_total_overdue_fee += $repaymentLog->overdue_fee;
            $this->repaymentOrder->delay_reduce_amount += $reduceAmount;
            //应还金额等于0，说明完全还款
            $scheduledPaymentAmount = $this->repaymentOrder->getScheduledPaymentAmount();
            if(in_array($this->order->clientInfoLog->package_name, ['bigshark', 'dhancash', 'hindmoney']))
            {
                $scheduledPaymentAmount = max(0, $scheduledPaymentAmount- 100);
            }
            if(0 == $scheduledPaymentAmount || true === $forceCompletion)
            {
                if(!$this->orderRepayCompeted($time, UserOrderLoanCheckLog::REPAY_DEBIT)){
                    throw new Exception($this->getError());
                }
            }else{ //部分还款不更新状态
                if(!$this->orderPartRepay($time)){
                    throw new Exception($this->getError());
                }
            }

            if($this->repaymentOrder->is_push_assist == UserLoanOrderRepayment::IS_PUSH_ASSIST_YES){
                $params = [
                    'user_id'          => $this->repaymentOrder->user_id,
                    'order_id'         => $this->repaymentOrder->order_id,
                    'app_name'         => $this->order->clientInfoLog->package_name,
                    'request_id'       => $repaymentLog->id,
                    'total_money'      => $this->repaymentOrder->total_money,
                    'true_total_money' => $this->repaymentOrder->true_total_money,
                    'money'            => $amount,
                    'overdue_day'      => $this->repaymentOrder->overdue_day,
                    'overdue_fee'      => $this->repaymentOrder->overdue_fee,
                    'status'           => $this->repaymentOrder->status,
                ];

                RedisQueue::push([RedisQueue::PUSH_ORDER_ASSIST_REPAYMENT, json_encode($params)]);
            }

            if($this->repaymentOrder->is_push_remind == UserLoanOrderRepayment::IS_PUSH_REMIND_YES
                && $this->repaymentOrder->is_overdue == UserLoanOrderRepayment::IS_OVERDUE_NO){
                $params = [
                    'user_id'          => $this->repaymentOrder->user_id,
                    'order_id'         => $this->repaymentOrder->order_id,
                    'app_name'         => $this->order->clientInfoLog->package_name,
                    'request_id'       => $repaymentLog->id,
                    'total_money'      => $this->repaymentOrder->total_money,
                    'true_total_money' => $this->repaymentOrder->true_total_money,
                    'money'            => $amount,
                    'status'           => $this->repaymentOrder->status,
                ];

                RedisQueue::push([RedisQueue::PUSH_ORDER_REMIND_REPAYMENT, json_encode($params)]);
            }
            $transaction->commit();
            //用户主动线上还款才推送给kudos
            if(LoanKudosOrder::findOne(['order_id' => $this->order->id]))
            {
                $data = ['order_id' => $this->order->id, 'paid_amount' => $amount];
                RedisQueue::push([RedisQueue::LIST_KUDOS_USER_REPAYMENT, json_encode($data)]);
            }


            //通知资方
            $fundService = new FundService();
            $fundService->orderRepaySuccess($this->order);
            return true;
        }catch (\Exception $e){
            $this->setError($e->getMessage());
            $transaction->rollBack();
            $service = new WeWorkService();
            $message = '【'.YII_ENV.'】还款订单入账异常:'.$e->getMessage().$e->getTraceAsString();
            $service->send($message);
            return false;
        }
    }


    /**
     * @param int $amount 展期金额
     * @param int $days 展期天数
     */
    public function extend($amount, $days)
    {
        $this->getUserLoanOrderRepayment();
        $repaymentOrder = $this->repaymentOrder;
        //如果已经在展期中，则展期天数需要叠加
        $extendDate = $this->calcExtendDate($days);
        $beginDate = $extendDate['beginDate'];
        $endDate = $extendDate['endDate'];

        $repaymentOrder->is_extend = UserLoanOrderRepayment::IS_EXTEND_YES;
        $repaymentOrder->extend_begin_date = $beginDate;
        $repaymentOrder->extend_end_date = $endDate;
        $repaymentOrder->extend_total = $repaymentOrder->extend_total + 1;

        $log = new UserLoanOrderExtendLog();
        $log->user_id = $this->order->user_id;
        $log->order_id = $this->order->id;
        $log->amount = $amount;
        $log->days = $days;
        $log->begin_date = $beginDate;
        $log->end_date = $endDate;
        //如果订单逾期，查询这笔订单当前的催收员id
        $collectorID = LoanCollectionOrder::getCollectorIdByOrderId($this->order->id);
        $log->collector_id = $collectorID;
        if(!$repaymentOrder->save())
        {
            return false;
        }

        if(!$log->save())
        {
            return false;
        }

        return true;
    }


    /**
     * @param integer|null $days 展期天数
     * @return array ['beginDate' => 'Y-m-d', 'endDate' => 'Y-m-d']
     */
    public function calcExtendDate($days = null)
    {
        $this->getUserLoanOrderRepayment();
        $repaymentOrder = $this->repaymentOrder;

        if(is_null($days))
        {
            $days = $this->order->loan_term;
        }
        //如果已经在展期中，则展期天数需要叠加
        if(UserLoanOrderRepayment::IS_DELAY_YES == $repaymentOrder->is_extend)
        {
            $beginDate = $repaymentOrder->extend_end_date;
        }else{
            $beginDate = date('Y-m-d');
        }
        $endDate = date('Y-m-d', strtotime($beginDate) + 86400 * $days);
        return ['beginDate' => $beginDate, 'endDate' => $endDate];
    }


    /**
     * 计算还款金额的各项分配
     * 逻辑
     *      (废弃)1. 未逾期，先本金，后利息
     *      (废弃)2. 已逾期，未曾还款，先本金，再滞纳金，后利息
     *      (废弃)3. 已逾期，已有还款，先滞纳金，再本金，后利息
     *      循序：放款金额，手续费，利息，逾期费
     * @param int $repaymentAmount
     * @param UserLoanOrderRepayment $oldRepaymentOrder
     * @return array
     */
    private function getRepaymentSplitAmount($repaymentAmount, $oldRepaymentOrder)
    {
        $amount = $repaymentAmount;
        $result = [];
        //应还本金-放款金额
        $loanMoney = $oldRepaymentOrder->principal - $oldRepaymentOrder->cost_fee;
        //剩余本金
        $principal = $oldRepaymentOrder->principal - $oldRepaymentOrder->true_total_principal;
        if ($principal > 0) {
            // 本金未还完
            //剩余本金-放款金额
            $principalLoanMoney = $loanMoney > $oldRepaymentOrder->true_total_principal ? ($loanMoney - $oldRepaymentOrder->true_total_principal) : 0;
            //剩余本金-手续费
            $principalCostFee = ($oldRepaymentOrder->principal - $oldRepaymentOrder->true_total_principal) > $oldRepaymentOrder->cost_fee ?
                $oldRepaymentOrder->cost_fee : ($oldRepaymentOrder->principal - $oldRepaymentOrder->true_total_principal);
        } else {
            // 本金已还完
            $principalLoanMoney = $principalCostFee = 0;
        }
        $interests = $oldRepaymentOrder->interests - $oldRepaymentOrder->true_total_interests;
        $overdueFee = $oldRepaymentOrder->overdue_fee - $oldRepaymentOrder->true_total_overdue_fee;

        $rule = [
            'principal_loan_money' => $principalLoanMoney,
            'principal_cost_fee'   => $principalCostFee,
            'interests'            => $interests,
            'overdueFee'           => $overdueFee,
        ];

        //rule中的每一项即水桶，计算每个水桶还可以装水的量
        foreach ($rule as $key => $value) {
            if ($rule[$key] >= $amount) {
                $result[$key] = $amount;
                break;
            } else {
                $result[$key] = $rule[$key];
                $amount = $amount - $rule[$key];
            }
        }

        return $result;
    }

    /**
     * 还款减免方法
     * @param int $operator_id
     * @param string $remark
     * @param int $from
     * @return bool
     */
    public function reduction($operator_id,$remark = '', $from = UserRepaymentReducedLog::FROM_ADMIN_SYSTEM)
    {
        $time = time();
        $transaction = Yii::$app->db->beginTransaction();
        try{
            //获取userLoanOrderRepayment对象
            $this->getUserLoanOrderRepayment();
            if($this->repaymentOrder->status == UserLoanOrderRepayment::STATUS_REPAY_COMPLETE){
                throw new Exception('The order has been repaid and cannot be deducted');
            }
            if(!in_array($from, array_keys(UserRepaymentReducedLog::$type))){
                throw new Exception('from error');
            }
            $reductionMoney = $this->repaymentOrder->getScheduledPaymentAmount();
            if($this->repaymentOrder->overdue_fee < $reductionMoney){
                throw new Exception('repay money not less than scheduled');
            }
            $this->repaymentOrder->reduction_money = $reductionMoney;
            if(!$this->orderRepayCompeted($time,UserOrderLoanCheckLog::REPAY_REDUCTION, $remark)){
                throw new Exception($this->getError());
            }
            //添加减免记录
            $userRepaymentReducedLog = new UserRepaymentReducedLog();
            $userRepaymentReducedLog->order_id = $this->repaymentOrder->order_id;
            $userRepaymentReducedLog->repayment_id = $this->repaymentOrder->id;
            $userRepaymentReducedLog->user_id = $this->repaymentOrder->user_id;
            $userRepaymentReducedLog->merchant_id = $this->merchantId;
            $userRepaymentReducedLog->from = $from;
            $userRepaymentReducedLog->reduction_money = $reductionMoney;
            $userRepaymentReducedLog->operator_id = $operator_id ?? 0;
            $userRepaymentReducedLog->remark = $remark;
            if(!$userRepaymentReducedLog->save()){
                throw new Exception('add userRepaymentReducedLog failed');
            }

            if($this->repaymentOrder->is_push_assist == UserLoanOrderRepayment::IS_PUSH_ASSIST_YES){
                $params = [
                    'user_id'          => $this->repaymentOrder->user_id,
                    'order_id'         => $this->repaymentOrder->order_id,
                    'app_name'         => $this->order->clientInfoLog->package_name,
                    'request_id'       => 'reduced_'.$userRepaymentReducedLog->id,
                    'total_money'      => $this->repaymentOrder->total_money,
                    'true_total_money' => $this->repaymentOrder->true_total_money,
                    'money'            => 0,
                    'overdue_day'      => $this->repaymentOrder->overdue_day,
                    'overdue_fee'      => $this->repaymentOrder->overdue_fee,
                    'status'           => $this->repaymentOrder->status,
                ];

                RedisQueue::push([RedisQueue::PUSH_ORDER_ASSIST_REPAYMENT, json_encode($params)]);
            }

            if($this->repaymentOrder->is_push_remind == UserLoanOrderRepayment::IS_PUSH_REMIND_YES
                && $this->repaymentOrder->is_overdue == UserLoanOrderRepayment::IS_OVERDUE_NO){
                $params = [
                    'user_id'          => $this->repaymentOrder->user_id,
                    'order_id'         => $this->repaymentOrder->order_id,
                    'app_name'         => $this->order->clientInfoLog->package_name,
                    'request_id'       => 'reduced_'.$userRepaymentReducedLog->id,
                    'total_money'      => $this->repaymentOrder->total_money,
                    'true_total_money' => $this->repaymentOrder->true_total_money,
                    'money'            => 0,
                    'status'           => $this->repaymentOrder->status,
                ];

                RedisQueue::push([RedisQueue::PUSH_ORDER_REMIND_REPAYMENT, json_encode($params)]);
            }

            $transaction->commit();
            return true;
        }catch (\Exception $e){
            $this->setError($e->getMessage());
            $transaction->rollBack();
            return false;
        }

    }


    /**
     * 获取UserLoanOrderRepayment实例
     * @return UserLoanOrderRepayment|yii\db\ActiveQuery
     */
    public function getUserLoanOrderRepayment()
    {
        if(is_null($this->repaymentOrder)){
            return $this->repaymentOrder = $this->order->userLoanOrderRepayment;
        }else{
            return $this->repaymentOrder;
        }
    }


    /**
     * 后台获取借款订单的详情
     * @return array
     */
    public function getOrderDetailAllInfo(){
        $userExtraService = new UserExtraService($this->order->loanPerson);
        $info = $userExtraService->getUserExtraInfo(true);

        $allLoanCheckLog = UserOrderLoanCheckLog::find()
            ->where(['user_id' => $this->order->user_id])
            ->orderBy(['id' => SORT_ASC])
            ->all();
        $userOrderLoanCheckLogRecord = [];
        $userOrderLoanCheckLogNow = [];
        //历史审核日志
        if(!empty($allLoanCheckLog)){
            foreach($allLoanCheckLog as $val){
                if($val['order_id'] == $this->order->id){
                    $userOrderLoanCheckLogRecord [] = $val;
                }else{
                    $userOrderLoanCheckLogNow [] = $val;
                }
            }
        }
        $info['userOrderLoanCheckLogRecord'] = $userOrderLoanCheckLogRecord;
        $info['userOrderLoanCheckLogNow'] = $userOrderLoanCheckLogNow;
        return $info;
    }

    /**
     * 后台获取还款订单的详情
     * @return array
     */
    public function getOrderDetailInfo(){
        $orderExtraService = new OrderExtraService($this->order);
        $info = $orderExtraService->getUserLoanOrderExtraInfo();
        $info['userLoanOrder'] = $this->order;
        $info['loanPerson'] = $this->order->loanPerson;
        $info['userBankAccount'] = $this->order->userBankAccount;
        return $info;
    }


    /**
     * 指定时间段，指定范围内的应还还款订单信息(未完成还款的订单)
     * @param int $idLast
     * @param null $limit
     * @return array|yii\db\ActiveRecord[]
     */
    public static function getReminderRepaymentsByPlanTime($idLast = 0, $limit = null){
        $userLoanOrderRepayment = UserLoanOrderRepayment::find()
            ->where(['!=' , 'status' => UserLoanOrderRepayment::STATUS_REPAY_COMPLETE])
            ->andWhere([
                'is_overdue' => UserLoanOrderRepayment::IS_OVERDUE_YES,
                'overdue_day' => 1
            ])
            ->andWhere(['>', 'id', $idLast])
            ->orderBy(['id'=>SORT_ASC])->limit($limit)->all();;
        return $userLoanOrderRepayment;
    }

    public function saveOrderGps(): bool
    {
        $clientInfo = json_decode($this->order->client_info, true);

        if (empty($clientInfo['latitude']) || empty($clientInfo['longitude'])) {
            return false;
        }

        $esOrder = new EsUserLoanOrder;
        $esOrder->user_id = $this->order->user_id;
        $esOrder->order_id = $this->order->id;
        $esOrder->merchant_id = $this->order->merchant_id;
        $esOrder->order_time = Carbon::createFromTimestamp($this->order->order_time)->toIso8601ZuluString();
        $esOrder->location = [
            'lat' => $clientInfo['latitude'],    //纬度
            'lon' => $clientInfo['longitude'],    //经度
        ];
        $primaryKey = $esOrder->user_id . '_' . $esOrder->order_id;
        $esOrder->setPrimaryKey($primaryKey);

        return $esOrder->save();
    }

    /**
     * 分配资方
     * @param int $operator
     * @return array
     * @throws \Exception
     */
    public function reviewPass($operator = 0) {
        if(!(UserLoanOrder::STATUS_LOANING == $this->order->status && UserLoanOrder::LOAN_STATUS_FUND_MATCH == $this->order->loan_status))
        {
            return [
                'code' => -1,
                'message' => '订单状态不正确'
            ];
        }
        if (!$this->order->card_id) { //有银行卡ID
            return [
                'code' => -1,
                'message' => '未绑定银行卡',
            ];
        }

        $fund_service = new FundService();
        $ret = $fund_service->orderAutoDispatch($this->order, $operator);

        return $ret;
    }

    public function applyDraw($auditRemark = 'User apply draw money')
    {
        if ($this->order->status != UserLoanOrder::STATUS_WAIT_DRAW_MONEY ||
            $this->order->loan_status != UserLoanOrder::LOAN_STATUS_DRAW_MONEY
        ) {
            $this->setError('The order status is incorrect.');
            return false;
        } else {
            $afterAllStatus = [
                'after_status'      => UserLoanOrder::STATUS_LOANING,
                'after_loan_status' => UserLoanOrder::LOAN_STATUS_FUND_MATCH,
            ];
            return $this->changeOrderAllStatus($afterAllStatus ,$auditRemark);
        }
    }

    /**
     * 绑卡
     * @param UserBankAccount $bankAccount
     * @return bool
     */
    public function bindCard(UserBankAccount $bankAccount)
    {
        //订单状态判断
        if(
        !(UserLoanOrder::STATUS_WAIT_DEPOSIT == $this->order->status
            && UserLoanOrder::LOAN_STATUS_WAIT_BIND_CARD == $this->order->loan_status)
        )
        {
            $this->setError('Order status is wrong');
            return false;
        }

        $needBindCard = false;
        //判断银行卡状态，如果是未验证状态则进入人审，可用则跳过人审
        if(UserBankAccount::STATUS_SUCCESS == $bankAccount->status)
        {
            if (0 == $this->order->amount) {
                $status = UserLoanOrder::STATUS_WAIT_DRAW_MONEY;
                $loanStatus = UserLoanOrder::LOAN_STATUS_DRAW_MONEY;

                $packageName = $this->order->clientInfoLog->package_name;
                $this->sendMsgAndPushByOrderApprove($packageName, 0);
            } else {
                $status = UserLoanOrder::STATUS_LOANING;
                $loanStatus = UserLoanOrder::LOAN_STATUS_FUND_MATCH;
            }
        }else{
            $needBindCard = true;
            $status = UserLoanOrder::STATUS_WAIT_DEPOSIT;
            $loanStatus = UserLoanOrder::LOAN_STATUS_BIND_CARD_CHECK;
        }
        $this->order->card_id = $bankAccount->id;
        $result = $this->changeOrderAllStatus([
            'after_status' => $status,
            'after_loan_status' => $loanStatus], 'bind card', 0);

        if($needBindCard){
            $packageName = $this->order->is_export == UserLoanOrder::IS_EXPORT_YES ?
                explode('_',$this->order->clientInfoLog->app_market)[1] :
                $this->order->clientInfoLog->package_name;
            RedisQueue::push([RedisQueue::PUSH_MANUAL_CREDIT_BANK_ORDER_DATA, json_encode([
                'order_id' => $this->order->id,
                'package_name' => $packageName,
                'pan_code' => $this->order->loanPerson->pan_code,
                'bank_account' => $bankAccount->account
            ])]);
            $operateKey = sprintf('%s:%s', RedisQueue::BEFORE_MANUAL_CREDIT_BANK_ORDER_ALERT,$this->order->id);
            RedisQueue::set(['key' => $operateKey, 'value' => 'true', 'expire' => 30]);
        }
        return $result;
    }


    /**
     * 订单GST费用
     * @return int
     */
    public function gst()
    {
        return $this->order->gst_fee;
    }

    /**
     * 订单放款金额
     * @return int
     */
    public function disbursalAmount()
    {
        return $this->order->disbursalAmount();
    }

    /**
     * 借款金额 放款金额 + 手续费 + GST
     * @return int
     */
    public function loanAmount(){
        return $this->order->amount;
    }

    /**
     * 总应还金额
     * @return int
     */
    public function totalRepaymentAmount()
    {
        $this->getUserLoanOrderRepayment();
        if(is_null($this->repaymentOrder))
        {
            return $this->loanAmount() + $this->totalInterests();
        }else{
            return $this->repaymentOrder->total_money;
        }
    }
    /**
     * 总借款天数
     * @return int
     */
    public function totalLoanTerm()
    {
        return $this->order->getTotalLoanTerm();
    }

    /**
     * 总利息
     * @return int
     */
    public function totalInterests()
    {
        return $this->order->interests;
    }

    /**
     * 总利率
     * @return float|int
     */
    public function totalRate()
    {
        return sprintf("%0.2f", $this->order->day_rate * $this->totalLoanTerm());
    }

    /**
     * 服务费
     * @return int
     */
    public function processFee()
    {
        return $this->order->cost_fee - $this->order->gst_fee;
    }

    /**
     * 服务费 + GST
     * @return int
     */
    public function processFeeAndGst()
    {
        return $this->processFee() + $this->gst();
    }

    /**
     * @return string
     */
    public function repaymentTime()
    {
        $this->getUserLoanOrderRepayment();

        if(is_null($this->repaymentOrder))
        {
            return $this->order->getRepayDate();
        }else{
            return $this->repaymentOrder->getPlanRepaymentDate();

        }
    }

    /**
     * 逾期费用
     * @return int
     */
    public function overdueFee()
    {
        $this->getUserLoanOrderRepayment();

        if(is_null($this->repaymentOrder))
        {
            return 0;
        }else{
            return $this->repaymentOrder->overdue_fee;
        }

    }

    /**
     * 逾期天数
     * @return int
     */
    public function overduePeriod()
    {
        $this->getUserLoanOrderRepayment();
        if(is_null($this->repaymentOrder))
        {
            return 0;
        }else{
            return $this->repaymentOrder->overdue_day;
        }
    }

    /**
     * 剩余应还金额
     * @return int
     */
    public function remainingPaymentAmount()
    {
        $this->getUserLoanOrderRepayment();
        if(is_null($this->repaymentOrder))
        {
            return $this->loanAmount() + $this->totalInterests();
        }else{
            return $this->repaymentOrder->getScheduledPaymentAmount();
        }
    }

    /**
     * 总应还金额
     * @return int
     */
    public function totalPaymentAmount()
    {
        $this->getUserLoanOrderRepayment();
        if(is_null($this->repaymentOrder))
        {
            return $this->loanAmount() + $this->totalInterests();
        }else{
            return $this->repaymentOrder->total_money;
        }
    }

    /**
     * 已还总金额
     * @return int
     */
    public function repaidAmount()
    {
        $this->getUserLoanOrderRepayment();
        if(is_null($this->repaymentOrder))
        {
            return 0;
        }else{
            return $this->repaymentOrder->true_total_money;
        }
    }

    /**
     * 优惠券金额
     * @return int
     */
    public function couponAmount()
    {
        $this->getUserLoanOrderRepayment();
        if(is_null($this->repaymentOrder))
        {
            return 0;
        }else{
            return $this->repaymentOrder->coupon_money;
        }
    }


    /**
     * 订单状态变更
     * @param string $target
     * @return array
     */
    public function orderChangeStatus($target = null)
    {
        //审核中
        if (in_array($this->order->status, [UserLoanOrder::STATUS_CHECK, UserLoanOrder::STATUS_WAIT_DEPOSIT])) {
            $status = UserLoanOrder::STATUS_CHECK;
            $jump = "/h5/#/audit/0?id={$this->order->id}&target={$target}";
        }

        //审核被拒
        if (in_array($this->order->status, [UserLoanOrder::STATUS_CHECK_REJECT])) {
            $status = $this->order->status;
            $jump = "/h5/#/loanRejected?orderId={$this->order->id}&orderType=internal";
        }

        //待提现
        if (in_array($this->order->status, [UserLoanOrder::STATUS_WAIT_DRAW_MONEY])) {
            $status = $this->order->status;
            $jump = "/h5/#/withdrawals?orderId={$this->order->id}&productId={$this->order->product_id}";
        }

        //提现超时
        if (in_array($this->order->status, [UserLoanOrder::STATUS_DEPOSIT_REJECT, UserLoanOrder::STATUS_WAIT_DRAW_MONEY_TIMEOUT])) {
            $status = $this->order->status;
            $jump = "/h5/#/applyLoan?productId={$this->order->product_id}&target=list";
        }

        //打款中
        if (in_array($this->order->status, [UserLoanOrder::STATUS_LOANING])) {
            $status = $this->order->status;
            $jump = "/h5/#/audit/2?id={$this->order->id}";
        }

        //待还款
        if (in_array($this->order->status, [UserLoanOrder::STATUS_LOAN_COMPLETE, UserLoanOrder::STATUS_OVERDUE])) {
            //逾期
            if ($this->order->userLoanOrderRepayment && $this->order->userLoanOrderRepayment->is_overdue == UserLoanOrderRepayment::IS_OVERDUE_YES) {
                $status = UserLoanOrder::STATUS_OVERDUE;
                $jump = "/h5/#/orderDetail/{$this->order->id}";
            } else {
                $status = $this->order->status;
                $jump = "/h5/#/orderDetail/{$this->order->id}";
            }
        }

        //打款失败
        if (in_array($this->order->status, [UserLoanOrder::STATUS_LOAN_REJECT])) {
            $status = $this->order->status;
            $jump = "/h5/#/applyLoan?productId={$this->order->product_id}&target=list";
        }

        //需用户绑卡状态特殊处理
        if (UserLoanOrder::STATUS_WAIT_DEPOSIT == $this->order->status
            && UserLoanOrder::LOAN_STATUS_WAIT_BIND_CARD == $this->order->loan_status) {
            $status = $this->order->status;
            $jump = "/h5/#/audit/1?id={$this->order->id}&target={$target}&orderType=internal";
        }

        //已还款
        if (in_array($this->order->status, [UserLoanOrder::STATUS_PAYMENT_COMPLETE])) {
            $status = $this->order->status;
            $jump = '';
        }

        return [
            'status' => $status,
            'jump'   => [
                'path' => "/main/refresh_tablist",
                'isFinishPage' => false
            ],
        ];
    }

    public function pushExternalOrderCanLoanTime($orderUuid, $delayDay)
    {
        $data = [
            'orderUuid' => $orderUuid,
            'delayDay' => $delayDay,
        ];

        RedisQueue::push([RedisQueue::LIST_EXTERNAL_ORDER_CAN_LOAN_TIME,json_encode($data)]);
    }

    public static function orderEventHandler($event) {

    }


    /**
     * 判断全平台共债
     * @return bool
     * @throws yii\base\InvalidConfigException
     */
    public function haveLoanOrderOnAll()
    {
        //获取pan卡号
        $panCode = LoanPerson::find()->select(['pan_code'])->where(['id' => $this->order->user_id])->scalar();

        //先查询本平台
        $selfIds = ArrayHelper::getColumn(LoanPerson::find()->select(['id'])
            ->where(['pan_code' => $panCode])
            ->all(), 'id');
        if(empty($selfIds))
        {

        }else{
            $order = UserLoanOrder::find()->where([
                'user_id' => $selfIds,
                'status' => [UserLoanOrder::STATUS_LOAN_COMPLETE, UserLoanOrder::STATUS_LOANING]])
                ->andWhere(['>' , 'loan_status', UserLoanOrder::LOAN_STATUS_FUND_MATCH])
                ->limit(1)->one();
            if(!empty($order))
            {
                return true;
            }
        }


        //获取loan平台的用户id
        /** @var LoanPerson $saasUser */
        $ids = ArrayHelper::getColumn(LoanPerson::find()->select(['id'])
            ->where(['pan_code' => $panCode])
            ->all(Yii::$app->get('db_loan')), 'id');
        if(!empty($ids)){
            $order = UserLoanOrder::find()->where([
                'user_id' => $ids,
                'status' => [UserLoanOrder::STATUS_LOAN_COMPLETE, UserLoanOrder::STATUS_LOANING]])
                ->andWhere(['>' , 'loan_status', UserLoanOrder::LOAN_STATUS_FUND_MATCH])
                ->limit(1)->one(Yii::$app->get('db_loan'));
            if(!empty($order))
            {
                return true;
            }
        }

        return false;
    }


    /**
     * 全平台过资方分配的订单
     * @param int|array $sourceIds
     * @return int
     * @throws yii\base\InvalidConfigException
     */
    public function allPlatformLoaningOrderCount()
    {
        //获取pan卡号
        $panCode = LoanPerson::find()->select(['pan_code'])->where(['id' => $this->order->user_id])->scalar();
        //获取本平台用户id
        $selfIDs = ArrayHelper::getColumn(LoanPerson::find()->select(['id'])
            ->where(['pan_code' => $panCode])
            ->all(), 'id');
        if(empty($selfIDs))
        {
            $selfOrderCount = 1;
        }else{
            $selfOrderCount = UserLoanOrder::find()->where([
                'user_id' => $selfIDs,
                'status' => [UserLoanOrder::STATUS_LOAN_COMPLETE, UserLoanOrder::STATUS_LOANING]])
                ->andWhere(['>' , 'loan_status', UserLoanOrder::LOAN_STATUS_FUND_MATCH])
                ->count();

        }

        //获取saas平台的用户id
        /** @var LoanPerson $saasUser */
        $ids = ArrayHelper::getColumn(LoanPerson::find()->select(['id'])
            ->where(['pan_code' => $panCode])
            ->all(Yii::$app->get('db_loan')), 'id');
        if(empty($ids)){
            $saasOrderCount = 0;
        }else{
            $saasOrderCount = UserLoanOrder::find()->where([
                'user_id' => $ids,
                'status' => [UserLoanOrder::STATUS_LOAN_COMPLETE, UserLoanOrder::STATUS_LOANING]])
                ->andWhere(['>' , 'loan_status', UserLoanOrder::LOAN_STATUS_FUND_MATCH])
                ->count('id', Yii::$app->get('db_loan'));

        }

        return $selfOrderCount + $saasOrderCount;

    }

    /**
     * 全平台过资方分配的订单
     * @param string $panCode
     * @return int
     * @throws yii\base\InvalidConfigException
     */
    public static function allPlatformLoaningOrderCountByPan($panCode)
    {
        //获取saas平台
        $selfIDs = ArrayHelper::getColumn(LoanPerson::find()->select(['id'])
            ->where(['pan_code' => $panCode])
            ->all(), 'id');
        if (empty($selfIDs)) {
            $selfOrderCount = 0;
        } else {
            $selfOrderCount = UserLoanOrder::find()
                ->where(['user_id' => $selfIDs])
                ->andWhere(['status' => UserLoanOrder::$opening_order_status])
                ->count();
        }

        //获取loan平台
        $loanIDs = ArrayHelper::getColumn(LoanPerson::find()->select(['id'])
            ->where(['pan_code' => $panCode])
            ->all(Yii::$app->get('db_loan')), 'id');
        if (empty($loanIDs)) {
            $pushingOrderCount = $loanOrderCount = 0;
        } else {
            $loanOrderCount = UserLoanOrder::find()
                ->where(['user_id' => $loanIDs])
                ->andWhere(['status' => UserLoanOrder::$opening_order_status])
                ->count('id', Yii::$app->get('db_loan'));
            $pushingOrderCount = UserLoanOrderExternal::find()
                ->where(['user_id' => $loanIDs])
                ->andWhere(['loan_status' => UserLoanOrder::LOAN_STATUS_WAIT_BIND_CARD])
                ->count();
        }

        return $selfOrderCount + $loanOrderCount + $pushingOrderCount;
    }

    /**
     * @param $data
     * @return bool
     */
    public function orderRiskNotify($data){
        try{
            $result = json_decode($data, true);
            ksort($result);
            foreach ($result as $key => $value){
                $model = RiskResultSnapshot::findOne(['order_id' => $value['order_id'], 'tree_code' => $value['tree_code']]);
                if(empty($model)){
                    $model = new RiskResultSnapshot();
                    $model->order_id     = $value['order_id'];
                    $model->user_id      = $value['user_id'];
                    $model->tree_code    = $value['tree_code'];
                    $model->tree_version = $value['tree_version'];
                    $model->result_data  = $value['result_data'];
                    $model->txt          = $value['txt'];
                    $model->base_node    = $value['base_node'];
                    $model->guard_node   = $value['guard_node'];
                    $model->manual_node  = $value['manual_node'];
                    $model->result       = $value['result'];
                    $model->save();
                }
                $resultData = json_decode($value['result_data'], true);
                if($key == 'risk'){
                    switch ($resultData['result']){
                        case 'reject':
                            $this->autoCheckReject($resultData['txt'], $resultData['head_code'], $resultData['back_code'], $resultData['interval']);
                            break;
                        case 'manual':
                            $this->autoCheckManual($resultData['txt'], $resultData['head_code'], $resultData['back_code']);
                            break;
                        case 'approve':
                            $this->autoCheckApprove();
                            break;
                        default:
                            break;
                    }
                }elseif($key == 'amount'){
                    $limit_service = new UserCreditLimitService();
                    $limit_service->changeLimit($this->order->loanPerson->id, $resultData['result'] * 100);
                    $this->order->credit_limit = $resultData['result'] * 100;
                    $this->order->save();
                }elseif($key == 'credit'){
                    $limit_service = new UserCreditLimitService();
                    $limit_service->changeLimit($this->order->loanPerson->id, $resultData['result'] * 100);
                }
            }
            return true;
        } catch (\Exception $e){
            Yii::error($e->getMessage().' in '.$e->getTraceAsString(), 'order_risk');
            $service = new WeWorkService();
            $message = sprintf('[%s][%s]异常[order_id:%s] : %s in %s:%s',
                \yii::$app->id, Yii::$app->requestedRoute, $this->order->id, $e->getMessage(), $e->getFile(), $e->getLine());
            $message .= $e->getTraceAsString();
            $service->send($message);
            return false;
        }
    }


    /**
     * 获取提现下一步
     * @return mixed
     */
    public function getDrawNexSept()
    {
        //放款金额
        $disbursalAmount = $this->disbursalAmount();

        if($this->order->amount == 0)
        {
            return DrawStep::MANUAL_DRAW()->getValue();
        }

        /**
         * 1、风控计算出的额度等于用户放款金额，则直接进入资方分配
         * 2、风控计算出的额度小于用户放款金额，则需要用户手动提现
         * 3、风控计算出的额度大于用户放款金额，则有以下两种情况
         *  a.用户放款额度小于最大额度,则直接进入资方分配
         *  b.用户放款额度等于最大额度,则进入自动提现流程
         */
        if($disbursalAmount == $this->order->credit_limit)
        {
            return DrawStep::FUND_MATCH()->getValue();
        }elseif($disbursalAmount > $this->order->credit_limit)
        {
            return DrawStep::MANUAL_DRAW()->getValue();
        }else{
            //如果用户下单额度小于最大额度，或者是老客,则直接进入资方分配
            if($disbursalAmount < $this->order->old_credit_limit || UserLoanOrder::FIRST_LOAN_NO == $this->order->is_first)
            {
                return DrawStep::FUND_MATCH()->getValue();
            }else{
                return DrawStep::AUTO_DRAW()->getValue();
            }
        }

    }


    /**
     * 全平台最大逾期天数
     * @param int|array $sourceIds
     * @param string $panCode
     * @return int
     */
    public static function allPlatformOverdueMaxDayByPan($panCode, $sourceIds = [])
    {
        //获取本平台用户id
        $selfIDs = ArrayHelper::getColumn(LoanPerson::find()->select(['id'])
            ->where(['pan_code' => $panCode])
            ->all(), 'id');
        if (empty($selfIDs)) {
            $selfOverdueMaxDay = 0;
        } else {
            $selfOverdueMaxDay = UserLoanOrderRepayment::find()
                ->select('max(overdue_day)')
                ->where([
                    'user_id'    => $selfIDs,
                    'is_overdue' => UserLoanOrderRepayment::IS_OVERDUE_YES,
                ])
                ->scalar();
        }

        //获取loan平台的用户id
        $loanQuery = LoanPerson::find()
            ->select(['id'])
            ->where(['pan_code' => $panCode]);
        if (!empty($sourceIds)) {
            $loanQuery->andWhere(['source_id' => $sourceIds]);
        }
        $ids = ArrayHelper::getColumn($loanQuery->all(yii::$app->get('db_loan')), 'id');
        if (empty($ids)) {
            $loanOverdueMaxDay = 0;
        } else {
            $loanOverdueMaxDay = UserLoanOrderRepayment::find()
                ->select('max(overdue_day)')
                ->where([
                    'user_id'    => $ids,
                    'is_overdue' => UserLoanOrderRepayment::IS_OVERDUE_YES,
                ])
                ->scalar(yii::$app->get('db_loan'));
        }

        return max($loanOverdueMaxDay, $selfOverdueMaxDay);
    }
}

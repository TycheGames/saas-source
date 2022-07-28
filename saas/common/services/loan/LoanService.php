<?php

namespace common\services\loan;

use common\helpers\CommonHelper;
use common\helpers\Lock;
use common\helpers\RedisDelayQueue;
use common\helpers\RedisQueue;
use common\helpers\Util;
use common\models\ClientInfoLog;
use common\models\order\UserLoanOrder;
use common\models\product\ProductSetting;
use common\models\user\LoanPerson;
use common\models\user\LoanPersonExternal;
use common\models\user\UserBankAccount;
use common\services\BaseService;
use common\services\message\WeWorkService;
use common\services\order\OrderExtraService;
use common\services\order\OrderService;
use common\services\package\PackageService;
use common\services\user\CaptchaService;
use common\services\user\UserCreditLimitService;
use common\services\user\UserVerificationService;
use frontend\models\loan\ApplyDrawExportForm;
use frontend\models\loan\ApplyDrawForm;
use frontend\models\loan\ApplyLoanForm;
use frontend\models\loan\LoanDetailForm;
use frontend\models\loan\OrderBindCardExportForm;
use frontend\models\loan\OrderBindCardForm;
use frontend\models\loan\OrderStatusForm;
use frontend\models\loan\PushOrderUserCheckForm;
use yii\db\Exception;
use yii;


class LoanService extends BaseService
{

    /**
     * 获取用户订单申请锁
     * @param int $userId
     * @param int $productID
     * @return bool
     */
    public static function lockApplyLoanRecord($userId, $productID)
    {
        $lockKey = sprintf("%s%s:%s:%s", RedisQueue::USER_OPERATE_LOCK, 'order:apply', $userId, $productID);
        $ret = RedisQueue::inc([$lockKey, 1]);
        RedisQueue::expire([$lockKey, 5]);
        return (1 == $ret);
    }

    /**
     * 获取用户订单提现锁
     * @param int $userId
     * @param int $productID
     * @return bool
     */
    public static function lockApplyDrawRecord($userId, $productID)
    {
        $lockKey = sprintf("%s%s:%s:%s", RedisQueue::USER_OPERATE_LOCK, 'order:draw', $userId, $productID);
        $ret = RedisQueue::inc([$lockKey, 1]);
        RedisQueue::expire([$lockKey, 5]);
        return (1 == $ret);
    }

    /**
     * 释放用户订单申请锁
     * @param integer $user_id
     */
    public static function releaseApplyLoanLock($userId)
    {
        $lockKey = sprintf("%s%s:%s", RedisQueue::USER_OPERATE_LOCK, "order:apply", $userId);
        RedisQueue::del(["key" => $lockKey]);
    }

    

    /**
     * 申请借款
     * @param ApplyLoanForm $from
     * @return bool
     */
    public function applyLoan(ApplyLoanForm $from)
    {
        //获取订单锁
        if (!self::lockApplyLoanRecord($from->userId, $from->productId))
        {
            Yii::warning("user_id: {$from->userId} click too fast, please try again later", 'apply_order');
            $this->setError('click too fast, please try again later');
            return false;
        }
        //判断银行卡是否在维护中
        /** @var UserBankAccount $card */
        $card = UserBankAccount::find()
            ->select(['ifsc'])
            ->where([
                'id' => $from->bankCardId,
                'user_id' => $from->userId])
            ->one();
        if(empty($card))
        {
            $this->setError('card error');
            return false;
        }
        $ifscTag = substr($card->ifsc, 0, 3);
        if(in_array(strtolower($ifscTag), yii::$app->params['bankMaintenanceList']))
        {
            $this->setError('Due to the banking system, State Bank Of India is temporarily not supported, please choose to use a new bank card');
            return false;
        }

        //判断是否可以借款
        if(!$this->checkCanApply($from->userId,$from->clientInfo)){
            $this->setError('No ordering qualification');
            return false;
        }
        //判断共债
        $loanPerson = LoanPerson::findById($from->userId);
        $openOrderCount = OrderService::allPlatformLoaningOrderCountByPan($loanPerson->pan_code);
        $isAllNew = LoanPersonExternal::isAllPlatformNewCustomer($loanPerson->pan_code);
        $openOrderCheck = $isAllNew ? 2 : 3;
        if ($openOrderCount >= $openOrderCheck) {
            $this->setError('This loan product is sold out.');
            return false;
        }

        //判断额度是否足够
        $userCreditLimitService = new UserCreditLimitService();
        $maxLimit = $userCreditLimitService->getUserMaxLimit($from->userId, $from->productId);
        if($from->amount > $maxLimit)
        {
            $this->setError('Exceeding maximum limit');
            return false;
        }

        /**
         * @var ProductSetting $product
         */
        //合规配置，页面显示和下单两边都要修改
//        $appMarketFlag = $from->clientInfo['appMarket'] == 'bigshark_google';
        if (in_array($from->userId, [1187501, 1244268, 840593])) {
            //todo::写入指定的用户id
            $product = ProductSetting::findOne(['merchant_id' => -1, 'is_internal' => 1]);
        } else {
            $product = ProductSetting::findOne($from->productId);
        }
        if(is_null($product))
        {
            $this->setError('params err');
            return false;
        }

        //获取借款产品信息
        $amount = CommonHelper::UnitToCents($product->amount($from->amount)); //放款金额
        $dayRate = $product->day_rate;
        $costRate = $product->cost_rate;
        $interests = CommonHelper::UnitToCents($product->interestsCalc($from->amount)); //利息
        $costFee = CommonHelper::UnitToCents($product->processingFeesGst($from->amount)); //手续费+GST
        $overdueRate = $product->overdue_rate;  //逾期费率
        $loanMethod = $product->productPeriodSetting->loan_method;
        $periods = $product->productPeriodSetting->periods;
        $loanTerm = $product->productPeriodSetting->loan_term;
        $gstFee = CommonHelper::UnitToCents($product->gst($from->amount));
        $did = $from->clientInfo['szlmQueryId']  ?? '';
        $blackbox = $from->clientInfo['tdBlackbox']  ?? '';

        $packageService = new PackageService($from->packageName);
        $merchantId = $packageService->getMerchantId();
        //绑卡
        if(!empty($from->bankCardId))
        {
            $cardId = $from->bankCardId;
            $bankCheck = UserBankAccount::find()->select(['id'])->where([
                'user_id' => $from->userId,
                'id' => $cardId
            ])->limit(1)->one();
            if(is_null($bankCheck))
            {
                $this->setError('bank card err');
                return false;
            }
        }else{
            $cardId =  0;
        }


        $transaction = Yii::$app->db->beginTransaction();
        try{
            $order = UserLoanOrder::generateOrder(
                $from->userId, $from->productId, $amount, $dayRate,
                $interests, $costRate, $costFee, $overdueRate,
                $cardId, $loanMethod, $loanTerm, $periods ,$gstFee,
                $did,CommonHelper::UnitToCents($maxLimit),
                json_encode($from->clientInfo, JSON_UNESCAPED_UNICODE),
                $from->clientInfo['deviceId'], $from->clientInfo['ip'], $blackbox,
                $merchantId, $from->isExport);
            $service = new OrderExtraService($order);
            if(!$service->relateUserLoanOrder())
            {
                throw new Exception('server error please try again later 2','',9999);
            }
            ClientInfoLog::addLog($from->userId,  ClientInfoLog::EVENT_APPLY_ORDER, $order->id, $from->clientInfo );
//            if(YII_ENV_PROD){
//                $orderService = new OrderService($order);
//                $orderService->saveOrderGps();
//            }
            $transaction->commit();
        }catch (\Exception $exception){
            yii::error($exception->getTraceAsString(),'apply_loan');
            if (YII_ENV_PROD) {
                $service = new WeWorkService();
                $service->send($exception->getMessage() . ' in ' . $exception->getTraceAsString());
            }
            $this->setError($exception->getMessage());
            $transaction->rollBack();
            $alertService = new WeWorkService();
            $alertService->send("err:{$exception->getMessage()} \n  trace: {$exception->getTraceAsString()}");
            return false;
        }

//        RedisQueue::push([RedisQueue::CREDIT_GET_DATA_SOURCE_PREFIX, $order->id]);
        RedisDelayQueue::pushDelayQueue(RedisQueue::CREDIT_GET_DATA_SOURCE_PREFIX, $order->id, 600);
        $this->setResult([
            'orderId' => $order->id,
            'status' => $order->status
        ]);
        return true;
    }

    /**
     * 申请借款
     * @param ApplyLoanForm $from
     * @return bool
     */
    public function applyLoanForExternal(ApplyLoanForm $from)
    {
        //判断是否可以借款
        if (!$this->checkCanApply($from->userId, $from->clientInfo, true)) {
            $this->setError('No ordering qualification');
            return false;
        }

        //判断额度是否足够
        $userCreditLimitService = new UserCreditLimitService();
        $maxLimit = $userCreditLimitService->getUserMaxLimit($from->userId, $from->productId);
        if($from->amount > $maxLimit)
        {
            $this->setError('Exceeding maximum limit');
            return false;
        }

        /**
         * @var ProductSetting $product
         */
        $product = ProductSetting::find()
            ->where(['package_name' => $from->packageName])
            ->andWhere(['is_internal' => ProductSetting::IS_EXTERNAL_YES])
            ->limit(1)
            ->one();
        if(is_null($product))
        {
            $this->setError('params err');
            return false;
        }

        //获取借款产品信息
        $amount = CommonHelper::UnitToCents($product->amount($from->amount)); //本金
        $dayRate = $product->day_rate;
        $costRate = $product->cost_rate;
        $interests = CommonHelper::UnitToCents($product->interestsCalc($from->amount)); //利息
        $costFee = CommonHelper::UnitToCents($product->processingFeesGst($from->amount)); //手续费+GST
        $overdueRate = $product->overdue_rate;  //逾期费率
        $loanMethod = $product->productPeriodSetting->loan_method;
        $periods = $product->productPeriodSetting->periods;
        $loanTerm = $product->productPeriodSetting->loan_term;
        $gstFee = CommonHelper::UnitToCents($product->gst($from->amount));
        $did = $from->clientInfo['szlmQueryId']  ?? '';
        $blackbox = $from->clientInfo['tdBlackbox']  ?? '';

        $packageService = new PackageService($from->packageName);
        $merchantId = $packageService->getMerchantId();
        //绑卡
        if(!empty($from->bankCardId))
        {
            $cardId = $from->bankCardId;
            $bankCheck = UserBankAccount::find()->select(['id'])->where([
                'user_id' => $from->userId,
                'id' => $cardId
            ])->limit(1)->one();
            if(is_null($bankCheck))
            {
                $this->setError('bank card err');
                return false;
            }
        }else{
            $cardId =  0;
        }


        $transaction = Yii::$app->db->beginTransaction();
        try{
            $order = UserLoanOrder::generateOrder(
                $from->userId, $from->productId, $amount, $dayRate,
                $interests, $costRate, $costFee, $overdueRate,
                $cardId, $loanMethod, $loanTerm, $periods ,$gstFee,
                $did,CommonHelper::UnitToCents($maxLimit),
                json_encode($from->clientInfo, JSON_UNESCAPED_UNICODE),
                $from->clientInfo['deviceId'], $from->clientInfo['ip'], $blackbox,
                $merchantId, $from->isExport, $from->orderUUID, $from->isAllFirst);
            $service = new OrderExtraService($order);
            if(!$service->relateUserLoanOrder())
            {
                throw new Exception('server error please try again later 2','',9999);
            }
            ClientInfoLog::addLog($from->userId,  ClientInfoLog::EVENT_APPLY_ORDER, $order->id, $from->clientInfo );
//            if(YII_ENV_PROD){
//                $orderService = new OrderService($order);
//                $orderService->saveOrderGps();
//            }
            $transaction->commit();
        }catch (\Exception $exception){
            yii::error($exception->getTraceAsString(),'apply_loan');
            if (YII_ENV_PROD) {
                $service = new WeWorkService();
                $service->send($exception->getMessage() . ' in ' . $exception->getTraceAsString());
            }
            $this->setError($exception->getMessage());
            $transaction->rollBack();
            $alertService = new WeWorkService();
            $alertService->send("err:{$exception->getMessage()} \n  trace: {$exception->getTraceAsString()}");
            return false;
        }

//        RedisQueue::push([RedisQueue::CREDIT_GET_DATA_SOURCE_PREFIX, $order->id]);
        RedisDelayQueue::pushDelayQueue(RedisQueue::CREDIT_GET_DATA_SOURCE_PREFIX, $order->id, 600);
        $this->setResult([
            'orderId' => $order->id,
            'status' => $order->status
        ]);
        return true;
    }

    /**
     * 申请提现
     * @param ApplyDrawForm $applyDrawForm
     * @return bool
     */
    public function applyDraw(ApplyDrawForm $applyDrawForm): bool
    {
        //获取订单锁
        if (!self::lockApplyDrawRecord($applyDrawForm->userId, 1))
        {
            Yii::warning("user_id: {$applyDrawForm->userId} click too fast, please try again later", 'draw_order');
            $this->setError('click too fast, please try again later');
            return false;
        }

        /**
         * @var UserLoanOrder $order
         */
        $order = UserLoanOrder::find()
            ->where(['id' => $applyDrawForm->orderId])
            ->andWhere(['user_id' => $applyDrawForm->userId])
            ->one();

        if (!$order) {
            $this->setError('The order does not exist.');
            return false;
        }
        if ($order->status != UserLoanOrder::STATUS_WAIT_DRAW_MONEY ||
            $order->loan_status != UserLoanOrder::LOAN_STATUS_DRAW_MONEY
        ) {
            $this->setError('The order status is incorrect.');
            return false;
        }
        $product = ProductSetting::findOne($order->product_id);
        if(is_null($product))
        {
            $this->setError('Parameter error！');
            return false;
        }
        //判断额度是否足够
        $maxLimit = $order->credit_limit;
        if($applyDrawForm->amount > $maxLimit)
        {
            $this->setError('Exceeding maximum limit');
            return false;
        }
        $amount = CommonHelper::UnitToCents($product->amount($applyDrawForm->amount)); //放款金额
        $interests = CommonHelper::UnitToCents($product->interestsCalc($applyDrawForm->amount)); //利息
        $costFee = CommonHelper::UnitToCents($product->processingFeesGst($applyDrawForm->amount)); //手续费+GST
        $gstFee = CommonHelper::UnitToCents($product->gst($applyDrawForm->amount));
        $dayRate = $product->day_rate;
        $costRate = $product->cost_rate;
        $overdueRate = $product->overdue_rate;  //逾期费率
        $loanMethod = $product->productPeriodSetting->loan_method;
        $periods = $product->productPeriodSetting->periods;
        $loanTerm = $product->productPeriodSetting->loan_term;

        $order->amount = $amount;
        $order->interests = $interests;
        $order->cost_fee = $costFee;
        $order->gst_fee = $gstFee;
        $order->day_rate = $dayRate;
        $order->cost_rate = $costRate;
        $order->overdue_rate = $overdueRate;
        $order->loan_method = $loanMethod;
        $order->periods = $periods;
        $order->loan_term = $loanTerm;
//        $order->credit_limit = CommonHelper::UnitToCents($maxLimit);
        if (!$order->save()) {
            $this->setError('Server error, please try again later!');
            return false;
        } else {
            $service = new OrderService($order);
            if ($service->applyDraw()) {
                $this->setResult([]);
                return true;
            } else {
                $this->setError($service->getError());
                return false;
            }
        }
    }

    public function applyDrawExport(ApplyDrawExportForm $applyDrawForm): bool
    {
        /**
         * @var UserLoanOrder $order
         */
        $order = UserLoanOrder::find()
            ->where(['order_uuid' => $applyDrawForm->orderUuid])
            ->one();

        if (!$order) {
            $this->setError('The order does not exist.');
            return false;
        }
        if (
            $order->status != UserLoanOrder::STATUS_WAIT_DRAW_MONEY ||
            $order->loan_status != UserLoanOrder::LOAN_STATUS_DRAW_MONEY
        ) {
            $this->setError('The order status is incorrect.');
            return false;
        }
        $product = ProductSetting::findOne($order->product_id);

        //判断额度是否足够
        $maxLimit = $order->credit_limit;
        if(CommonHelper::UnitToCents($applyDrawForm->amount) > $maxLimit)
        {
            $this->setError('Exceeding maximum limit');
            return false;
        }
        $amount = CommonHelper::UnitToCents($product->amount($applyDrawForm->amount)); //放款金额
        $interests = CommonHelper::UnitToCents($product->interestsCalc($applyDrawForm->amount)); //利息
        $costFee = CommonHelper::UnitToCents($product->processingFeesGst($applyDrawForm->amount)); //手续费+GST
        $gstFee = CommonHelper::UnitToCents($product->gst($applyDrawForm->amount));
        $dayRate = $product->day_rate;
        $costRate = $product->cost_rate;
        $overdueRate = $product->overdue_rate;  //逾期费率
        $loanMethod = $product->productPeriodSetting->loan_method;
        $periods = $product->productPeriodSetting->periods;
        $loanTerm = $product->productPeriodSetting->loan_term;

        $order->amount = $amount;
        $order->interests = $interests;
        $order->cost_fee = $costFee;
        $order->gst_fee = $gstFee;
        $order->day_rate = $dayRate;
        $order->cost_rate = $costRate;
        $order->overdue_rate = $overdueRate;
        $order->loan_method = $loanMethod;
        $order->periods = $periods;
        $order->loan_term = $loanTerm;
//        $order->credit_limit = CommonHelper::UnitToCents($maxLimit);
        if (!$order->save()) {
            $this->setError('Server error, please try again later!');
            return false;
        } else {
            $service = new OrderService($order);
            if ($service->applyDraw()) {
                $this->setResult([]);
                return true;
            } else {
                $this->setError($service->getError());
                return false;
            }
        }
    }

    /**
     * 检测用户是否可以借款
     * @param int $userId
     * @param array $clientInfo
     * @param bool $isExternal
     * @return bool
     */
    public function checkCanApply($userId, $clientInfo, $isExternal = false)
    {
        if(!$userId){
            return false;
        }

        /**
         * @var LoanPerson $loanPerson
         */
        $loanPerson = LoanPerson::find()->select(['can_loan_time'])->where(['id' => $userId])->one();
        if(is_null($loanPerson))
        {
            return false;
        }
        //可在借时间未到，不允许下单
        if( time() < $loanPerson->can_loan_time)
        {
            return false;
        }
        //查询是否有在审核状态下的订单，存在不给申请
        if($this->haveOpeningOrder($userId))
        {
            return false;
        }

        //未完成认证项，不允许下单
        $service = new UserVerificationService($userId);
        if(!$service->getAllVerificationStatus($isExternal))
        {
            Yii::warning("用户ID{$userId}未完成认证项，下单请求不准入", 'apply_order');
            return false;
        }
        return true;
    }


    /**
     * 是否存在进行中订单
     * @param $userId
     * @return bool   true 有     false 没有
     */
    public function haveOpeningOrder($userId)
    {
        $order = UserLoanOrder::find()
            ->select('id')
            ->where(['user_id' => $userId])
            ->andWhere(['status'=> UserLoanOrder::$opening_order_status,])
            ->one();
        if ($order) {
            return true;
        }else{
            return false;
        }
    }

    /**
     * 是否存在进行中订单
     * @param $userId
     * @return bool   true 有     false 没有
     */
    public static function haveOpeningOrderNoExport($userId)
    {
        $order = UserLoanOrder::find()
            ->select('id')
            ->where(['user_id' => $userId])
            ->andWhere([
                'status'=> UserLoanOrder::$opening_order_status,
                'is_export' => UserLoanOrder::IS_EXPORT_NO,
            ])
            ->one();
        if ($order) {
            return true;
        }else{
            return false;
        }
    }




    /**
     * 放款成功后调用，生成还款计划和状态流转
     * @param UserLoanOrder $order
     * @param int $loanTime
     * @return bool
     */
    public function loanSuccessCallback(UserLoanOrder $order, $loanTime = 0) : bool
    {
        $service = new OrderService($order);
        return $service->loanSuccess($loanTime);
    }

    /**
     * 放款驳回
     * @param UserLoanOrder $order
     * @param $username
     * @param $remark
     * @return bool
     */
    public function loanReject(UserLoanOrder $order,$username='',$remark=''):bool
    {
        $service = new OrderService($order);
        return $service->orderLoanReject($username,$remark);
    }


    /**
     * 获取借款订单详情
     * @param LoanDetailForm $form
     * @return bool
     */
    public function getLoanDetail(LoanDetailForm $form)
    {
        /**
         * @var UserLoanOrder $order
         */
        $order = UserLoanOrder::find()
            ->where(['id' => $form->id, 'user_id' => $form->userId])
            ->limit(1)->one();
        if(is_null($order)){
            $this->setError('params err');
            return false;
        }
        $service = new OrderService($order);
        $data = $service->getOrderDetail($form->hostInfo);
        $this->setResult($data);
        return true;
    }


    /**
     * 订单绑卡
     * @param OrderBindCardForm $form
     * @param $userId
     * @return bool
     */
    public function orderBindCard(OrderBindCardForm $form, $userId)
    {
        /**
         * @var UserLoanOrder $order
         */
        $order = UserLoanOrder::find()->where([
            'id' => intval($form->orderId),
            'user_id' => $userId
        ])->one();
        if(is_null($order))
        {
            $this->setError('Order does not exist');
            return false;
        }

        /**
         * @var UserBankAccount $bankAccount
         */
        $bankAccount = UserBankAccount::find()->where([
            'user_id' => $userId,
            'id' => intval($form->bankCardId)
        ])->one();
        if(is_null($bankAccount))
        {
            $this->setError('Card not exist');
            return false;
        }
        $service = new OrderService($order);
        if($service->bindCard($bankAccount))
        {
            $this->setResult([]);
            return true;
        }else{
            $this->setError($service->getError());
            return false;
        }
    }

    /**
     * 订单绑卡
     * @param OrderBindCardExportForm $form
     * @return bool
     */
    public function orderBindCardFromExport(OrderBindCardExportForm $form)
    {
        /**
         * @var UserLoanOrder $order
         */
        $order = UserLoanOrder::find()
            ->where(['order_uuid' => $form->orderUuid])
            ->one();
        if (is_null($order)) {
            $this->setError('Order does not exist');
            return false;
        }

        $bankCardData = json_decode($form->bankCard, true);
        /**
         * @var UserBankAccount $bankAccount
         */
        $bankAccount = UserBankAccount::find()
            ->where([
                'user_id' => $order->user_id,
                'account' => $bankCardData['account'],
            ])
            ->one();
        if (is_null($bankAccount)) {
            $bankAccount = new UserBankAccount();
        }
        $user = LoanPerson::findOne($order->user_id);
        $bankAccount->user_id = $order->user_id;
        $bankAccount->source_id = $user->source_id;
        $bankAccount->source_type = UserBankAccount::SOURCE_EXPORT;
        $bankAccount->name = $user->name;
        $bankAccount->report_account_name = $bankCardData['report_account_name'];
        $bankAccount->account = $bankCardData['account'];
        $bankAccount->ifsc = $bankCardData['ifsc'];
        $bankAccount->status = $bankCardData['status']; //pan卡号相同，即姓名相同
        $bankAccount->bank_name = $bankCardData['bank_name'];
        $bankAccount->data = $bankCardData['data'];
        $bankAccount->merchant_id = $user->merchant_id;
        $bankAccount->save();

        $service = new OrderService($order);
        if ($service->bindCard($bankAccount)) {
            $this->setResult([]);
            return true;
        } else {
            $this->setError($service->getError());
            return false;
        }
    }


    /**
     * 订单变更状态
     * @param OrderStatusForm $form
     * @return bool
     */
    public function orderChangeStatus(OrderStatusForm $form)
    {
        /** @var UserLoanOrder $order */
        $order = UserLoanOrder::find()->where(['user_id' => $form->userId, 'id' => $form->id])->one();
        if(is_null($order))
        {
            $this->setError('order dose not exist');
            return false;
        }
        $orderService = new OrderService($order);
        $this->setResult($orderService->orderChangeStatus());
        return true;

    }
}

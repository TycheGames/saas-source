<?php
namespace common\services\repayment;

use callcenter\service\LoanCollectionService;
use common\helpers\CommonHelper;
use common\helpers\RedisQueue;
use common\models\financial\FinancialPaymentOrder;
use common\models\order\GetTransferData;
use common\models\order\UserLoanOrderRepayment;
use common\models\order\UserRepaymentLog;
use common\models\pay\PayAccountSetting;
use common\models\razorpay\RazorpayVirtualAccount;
use common\models\user\UserActiveTime;
use common\services\BaseService;
use common\models\order\UserLoanOrder;
use common\services\order\OrderService;
use common\services\package\PackageService;
use common\services\pay\CashFreePaymentService;
use common\services\pay\JpayService;
use common\services\pay\MojoService;
use common\services\pay\MpurseService;
use common\services\pay\QimingService;
use common\services\pay\QuanqiupayService;
use common\services\pay\RazorpayService;
use common\services\pay\RpayService;
use common\services\pay\SifangService;
use frontend\models\guest\ApplyForm;
use frontend\models\loan\GetTransferDataForm;
use frontend\models\loan\RepayByBankTransfer;
use frontend\models\loan\RepaymentApplyExportForm;
use frontend\models\loan\RepaymentApplyForm;
use frontend\models\loan\RepaymentResultForm;
use light\hashids\Hashids;
use yii\db\Exception;
use yii;

class RepaymentService extends BaseService
{

    /**
     * 创建虚拟账号
     * @param $userId
     * @return RazorpayVirtualAccount|null
     * @throws Exception
     */
    public function createVirtualAccount($orderId, $userId)
    {
        /** @var UserLoanOrder $userLoanOrder */
        $userLoanOrder = UserLoanOrder::find()->where(['id' =>$orderId, 'user_id' => $userId])->one();
        $service = new RazorpayService($userLoanOrder->loanFund->payAccountSetting);
        $model = $service->createVirtualAccount($orderId, $userId);
        return $model;

    }


    /**
     * 创建razorpay upi address
     * @param $orderId
     * @param $userId
     * @return \common\models\razorpay\RazorpayUPIAddress|null
     * @throws Exception
     */
    public function createRazorpayUpiAddress($orderId, $userId)
    {
        /** @var UserLoanOrder $userLoanOrder */
        $userLoanOrder = UserLoanOrder::find()->where(['id' => $orderId, 'user_id' => $userId])->one();
        $service = new RazorpayService($userLoanOrder->loanFund->payAccountSetting);
        $model = $service->createUPIAddress($orderId, $userId);
        return $model;

    }



    /**
     * 获取用户虚拟账户
     * @param RepayByBankTransfer $form
     * @return bool
     * @throws Exception
     */
    public function actionGetUserUPIAddress(RepayByBankTransfer $form)
    {
        /**
         * @var UserLoanOrder $userLoanOrder
         */
        $userLoanOrder = UserLoanOrder::find()
            ->where(['id' => $form->id, 'user_id' => $form->userId])
            ->one();
        $service = new RazorpayService($userLoanOrder->loanFund->payAccountSetting);
        $model = $service->createUPIAddress($form->id, $form->userId);
        $this->setResult([
            'address' => $model->address
        ]);
        return true;
    }

        /**
     * 获取用户虚拟账户
     * @param $userId
     * @return bool
     * @throws Exception
     */
    public function actionGetUserVa(RepayByBankTransfer $form){
        $userLoanOrder = UserLoanOrder::find()
            ->where(['id' => $form->id, 'user_id' => $form->userId])
            ->one();
        $service = new RazorpayService($userLoanOrder->loanFund->payAccountSetting);
        $model = $service->createUPIAddress($form->id, $form->userId);
        $this->setResult([
            'beneficiaryName' => $model->va_name,
            'bankName' => $model->va_bank_name,
            'accountNumber' => $model->va_account,
            'ifsc' => $model->va_ifsc
        ]);
        return true;

    }




    /**
     * 申请还款
     * @param RepaymentApplyForm $form
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \common\exceptions\UserExceptionExt
     */
    public function repaymentApplyNew(RepaymentApplyForm $form)
    {
        $userId = $form->userID;
        $orderId = $form->orderId;
        $form->amount = max(1, $form->amount);
        $amount = CommonHelper::UnitToCents($form->amount);
        $paymentType = $form->paymentType;
        /** @var UserLoanOrderRepayment $orderRepayment */
        $orderRepayment = UserLoanOrderRepayment::find()->where(['user_id' => $userId, 'order_id' => $orderId])->one();
        if(is_null($orderRepayment)){
            $this->setError('INVALID PARAMETER');
            return false;
        }


        //记录支付时间
        $userActiveModel = UserActiveTime::find()->where(['user_id' => $form->userID])->one();
        if(is_null($userActiveModel))
        {
            $userActiveModel = new UserActiveTime();
        }
        $userActiveModel->user_id = $form->userID;
        $userActiveModel->last_pay_time = time();
        $userActiveModel->save();
        //记录支付时间(推送至催收中心)
        RedisQueue::push([RedisQueue::PUSH_ASSIST_CENTER_LAST_PAY_USER, $orderRepayment->loanPerson->pan_code],'redis_assist_center');


        /** @var UserLoanOrder $userLoanOrder */
        $userLoanOrder = UserLoanOrder::find()->where(['user_id' => $userId, 'id' => $orderId])->one();
        $orderService = new OrderService($userLoanOrder);

        //判断延期
        if(in_array($paymentType, [FinancialPaymentOrder::PAYMENT_TYPE_DELAY, FinancialPaymentOrder::PAYMENT_TYPE_DELAY_REDUCE]))
        {
            $delayResult = $orderService->checkDelayStatus();
            if($delayResult['delaySwitch'] == false || $amount < $delayResult['delayMoney'])
            {
                $this->setError('Delay repayment is not supported, Please try again later!');
                return false;
            }
        }


        //判断展期
        if(FinancialPaymentOrder::PAYMENT_TYPE_EXTEND == $paymentType)
        {
            $extendResult = $orderService->checkExtendStatus();
            if(false == $extendResult['extendSwitch'] || $form->amount < $extendResult['extendMoney'])
            {
                $this->setError('Extend repayment is not supported, Please try again later!');
                return false;
            }
        }

        $loanFund = $userLoanOrder->loanFund;
        $payAccountSetting = $loanFund->payAccountSetting;


        if(in_array($form->serviceType, [FinancialPaymentOrder::SERVICE_TYPE_RAZORPAY,FinancialPaymentOrder::SERVICE_TYPE_RAZORPAY_PAYMENT_LINK]))
        {
            $service = new RazorpayService($payAccountSetting);
        }elseif(FinancialPaymentOrder::SERVICE_TYPE_CASHFREE == $form->serviceType){
            $service = new CashFreePaymentService($payAccountSetting);
        }elseif (FinancialPaymentOrder::SERVICE_TYPE_MPURSE == $form->serviceType || FinancialPaymentOrder::SERVICE_TYPE_MPURSE_UPI == $form->serviceType){
            $service = new MpurseService($payAccountSetting);
        }elseif (FinancialPaymentOrder::SERVICE_TYPE_SIFANG == $form->serviceType){
            $service = new SifangService($payAccountSetting);
        }elseif (FinancialPaymentOrder::SERVICE_TYPE_JPAY == $form->serviceType){
            $service = new JpayService($payAccountSetting);
        }elseif (FinancialPaymentOrder::SERVICE_TYPE_MOJO == $form->serviceType){
            $service = new MojoService($payAccountSetting);
        }elseif (FinancialPaymentOrder::SERVICE_TYPE_QIMING == $form->serviceType){
            $service = new QimingService($payAccountSetting);
        }elseif (FinancialPaymentOrder::SERVICE_TYPE_RPAY == $form->serviceType){
            $service = new RpayService($payAccountSetting);
        }elseif (FinancialPaymentOrder::SERVICE_TYPE_QUANQIUPAY == $form->serviceType){
            $service = new QuanqiupayService($payAccountSetting);
        }

        if(FinancialPaymentOrder::SERVICE_TYPE_RAZORPAY_PAYMENT_LINK == $form->serviceType)
        {
            $r = $service->createPaymentLink(CommonHelper::UnitToCents($form->amount), $form->userID, $form->orderId);
        }else{
            $r = $service->repaymentApply($form);
        }

        if($r) {
            $this->setResult($service->getResult());
            return true;
        }else{
            $this->setError($service->getError());
            return false;
        }

    }


    /**
     * 外部订单还款
     * @param RepaymentApplyExportForm $form
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \common\exceptions\UserExceptionExt
     */
    public function repaymentApplyFromExport(RepaymentApplyExportForm $form)
    {
        /** @var UserLoanOrder $userLoanOrder */
        $userLoanOrder = UserLoanOrder::find()->where(['order_uuid' => $form->orderUuid])->one();
        if(is_null($userLoanOrder))
        {
            $this->setError('Order does not exist');
            return false;
        }

        if(UserLoanOrder::STATUS_LOAN_COMPLETE != $userLoanOrder->status)
        {
            $this->setError('Order status incorrect');
            return false;
        }

        $repaymentApplyForm = new RepaymentApplyForm();
        $repaymentApplyForm->userID = $userLoanOrder->user_id;
        $repaymentApplyForm->orderId = $userLoanOrder->id;
        $repaymentApplyForm->serviceType = $form->serviceType;
        $repaymentApplyForm->amount = $form->amount;
        $repaymentApplyForm->customerEmail = $form->customerEmail;
        $repaymentApplyForm->customerPhone = $form->customerPhone;
        $repaymentApplyForm->customerName = $form->customerName;
        $repaymentApplyForm->customerUpiAccount = $form->customerUpiAccount;
        $repaymentApplyForm->host = $form->host;
        $repaymentApplyForm->paymentType = $form->paymentType;
        $repaymentApplyForm->paymentChannel = $form->paymentChannel;
        return $this->repaymentApplyNew($repaymentApplyForm);
    }


    /**
     * 还款结果返回
     * @param RepaymentResultForm $form
     * @return bool
     */
    public function repaymentResult(RepaymentResultForm $form)
    {
        $this->setResult(['isSuccess' => true]);
        return true;
    }


    /**
     * @param $orderId
     * @param $amount
     * @param int $type
     * @param $operator
     * @param int $paymentType
     * @return bool
     */
    public function repaymentHandle($orderId, $amount, $type = UserRepaymentLog::TYPE_ACTIVE, $operator = 0, $paymentType = 0)
    {
        $order = UserLoanOrder::findOne($orderId);
        $service = new OrderService($order);
        $service->setOperator($operator);

        //展期付款
        if(FinancialPaymentOrder::PAYMENT_TYPE_EXTEND == $paymentType)
        {
            $res = $service->extend($amount, $order->loan_term);
        }else{
            $res = $service->repayment($amount, $type, false, $paymentType);
        }
        if ($res && in_array($paymentType, [
                FinancialPaymentOrder::PAYMENT_TYPE_DELAY,
                FinancialPaymentOrder::PAYMENT_TYPE_DELAY_REDUCE,
                FinancialPaymentOrder::PAYMENT_TYPE_EXTEND,
            ])) {
            $collectionService = new LoanCollectionService();
            $collectionService->delayCollectionStop($orderId);
        }
        if(!$res){
            $this->setError($service->getError());
        }
        return $res;
    }


    /**
     * 游客还款
     * @param int $orderID
     * @param int $amount
     * @return bool
     */
    public function guestPayment(int $orderID, int $amount)
    {
        /** @var UserLoanOrder|null $order */
        $order = UserLoanOrder::find()->where(['id' => $orderID, 'status' => UserLoanOrder::STATUS_LOAN_COMPLETE])->one();
        if(is_null($order))
        {
            return false;
        }
        //获取支付账号配置
        $loanFund = $order->loanFund;
        $payAccountSetting = $loanFund->payAccountSetting;
        $service = new RazorpayService($payAccountSetting);
        if($service->createPaymentLink($amount, $order->user_id, $orderID))
        {
            $this->setResult($service->getResult());
            return true;
        }else{
            $this->setError($service->getError());
            return false;
        }

    }

    /**
     * @param GetTransferDataForm $form
     * @return bool
     */
    public function getTransferData(GetTransferDataForm $form)
    {
        /** @var UserLoanOrder|null $order */
        $order = UserLoanOrder::find()->where(['id' => $form->orderId])->one();
        if(is_null($order))
        {
            return false;
        }

        if(in_array($order->merchant_id, [1, 9])){
            $transfer = [
//                1 => [
//                    'name' => 'Sanjha Devi',
//                    'account_number' => '20140574170',
//                    'ifsc_code' => 'FINO0001200',
//                ],
//                2 => [
//                    'name' => 'Chanchal Kumar',
//                    'account_number' => '20140574410',
//                    'ifsc_code' => 'FINO0001200',
//                ],
//                3 => [
//                    'name' => 'Prameshwar Sade',
//                    'account_number' => '20140576154',
//                    'ifsc_code' => 'FINO0001200',
//                ],
//                4 => [
//                    'name' => 'Bino Sade',
//                    'account_number' => '20140574330',
//                    'ifsc_code' => 'FINO0001200',
//                ],
//                5 => [
//                    'name' => 'Punam Devi',
//                    'account_number' => '20140574148',
//                    'ifsc_code' => 'FINO0001200',
//                ],
                6 => [
                    'name' => 'AARATI DEVI',
                    'account_number' => '20107980544',
                    'ifsc_code' => 'FINO0001200',
                ],
            ];
        }elseif(in_array($order->merchant_id, [12])){
            $this->setError('error');
            return false;
            $transfer = [
//                1 => [
//                    'name' => 'RAJESH RAM',
//                    'account_number' => '20107980602',
//                    'ifsc_code' => 'FINO0001200',
//                ],
//                2 => [
//                    'name' => 'DILJU DEVI',
//                    'account_number' => '20107980555',
//                    'ifsc_code' => 'FINO0001200',
//                ],
//                3 => [
//                    'name' => 'KISHUNAWATI DEVI',
//                    'account_number' => '20107980566',
//                    'ifsc_code' => 'FINO0001200',
//                ]
            ];
        }else{
            $this->setError('error');
            return false;
        }

        /** @var GetTransferData  $transferData */
        $transferData = GetTransferData::find()->where(['order_uuid' => $order->order_uuid])->one();
        if(empty($transferData)){
            $key = array_rand($transfer);
            $transferData = new GetTransferData();
            $transferData->order_uuid = $order->order_uuid;
            $transferData->key = $key;
            $transferData->save();
        }else{
            if(isset($transfer[$transferData->key])){
                $key = $transferData->key;
            }else{
                $key = array_rand($transfer);
                $transferData->key = $key;
                $transferData->save();
            }
        }
        $data = $transfer[$key];
        $data['orderUUId'] = $order->order_uuid;

        $this->setResult($data);
        return true;
    }
}
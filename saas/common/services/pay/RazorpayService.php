<?php
namespace common\services\pay;

use common\helpers\CommonHelper;
use common\helpers\RedisQueue;
use common\models\financial\FinancialPaymentOrder;
use common\models\fund\LoanFund;
use common\models\order\FinancialLoanRecord;
use common\models\order\UserRepaymentLog;
use common\models\pay\LoanPayForm;
use common\models\pay\PayAccountSetting;
use common\models\pay\RazorpayAccountForm;
use common\models\razorpay\RazorpayAccount;
use common\models\razorpay\RazorpayContact;
use common\models\razorpay\RazorpayUPIAddress;
use common\models\razorpay\RazorpayVirtualAccount;
use common\models\user\LoanPerson;
use common\models\user\UserBankAccount;
use common\models\order\UserLoanOrder;
use common\services\loan\LoanService;
use common\services\message\DingDingService;
use common\services\message\WeWorkService;
use common\services\order\FinancialService;
use common\services\repayment\RepaymentService;
use frontend\models\loan\RepaymentApplyForm;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Razorpay\Api\Api;
use Yii;
use yii\db\Exception;
use yii\helpers\ArrayHelper;

/**
 * Class RazorpayService
 * @package common\services\pay
 *
 * @property RazorpayAccountForm $accountForm
 * @property PayAccountSetting $payAccountSetting
 */
class RazorpayService extends BasePayService
{
    private $payAccountSetting;
    private $paymentKey,$paymentSecret, $paymentDomain;
    private $payoutKey,$payoutSecret;
    private $webhooksSecret;
    private $accountId;
    private $baseUri;
    private $accountNumber;
    private $merchantId;


    public $loanPayStatus; //打款状态
    public $loanPaySuccessTime; //打款成功时间
    public $thirdOrderID; //三方支付ID

    public $loanPayQueryResult; //打款状态查询结果
    public $loanPayRequestResult; //打款请求结果


    public function __construct(PayAccountSetting $payAccountSetting, $config = [])
    {
        $this->payAccountSetting = $payAccountSetting;
        /** @var RazorpayAccountForm $form */
        $form = self::formModel();
        $form->load($payAccountSetting->getAccountInfo(), '');
        $this->accountId = $payAccountSetting->id;
        $this->paymentKey = $form->paymentKeyId;
        $this->paymentSecret = $form->paymentSecret;
        $this->paymentDomain = $form->paymentDomain;
        $this->payoutKey = $form->payoutKeyId;
        $this->payoutSecret = $form->payoutKeySecret;
        $this->webhooksSecret = $form->webhooksSecret;
        $this->baseUri = $form->baseUri;
        $this->accountNumber = $form->accountNumber;
        $this->merchantId = $this->payAccountSetting->merchant_id;
        parent::__construct($config);
    }


    /**
     * @param $payAccountId
     * @return RazorpayService
     */
    public static function getInstanceByPayAccountId($payAccountId)
    {
        $payAccountSetting = PayAccountSetting::findOne(['id' => $payAccountId, 'service_type' => PayAccountSetting::SERVICE_TYPE_RAZORPAY]);
        return new self($payAccountSetting);
    }



    /**
     * @return RazorpayAccountForm|\yii\base\Model
     */
    public static function formModel()
    {
        return new RazorpayAccountForm();
    }


    /**
     * 创建客户
     * @param $phone
     * @param $name
     * @param $email
     * @return mixed
     */
    public function createCustomer($userId, $phone, $name, $email)
    {
        $api = new Api($this->paymentKey, $this->paymentSecret);
        $result = $api->customer->create([
            'name' => $name,
            'email' => $email,
            'contact' => '+91' . strval($phone),
            'fail_existing' => "0"
        ]);
        return $result;
    }



    /**
     * 创建虚拟账号
     * @param integer$orderId
     * @param integer $userId
     * @return RazorpayVirtualAccount|null
     * @throws Exception
     */
    public function createVirtualAccount($orderId, $userId)
    {
        $model = RazorpayVirtualAccount::findOne([
            'order_id' => $orderId,
            'user_id' => $userId,
            'pay_account_id' => $this->accountId,
            'status' => RazorpayVirtualAccount::STATUS_ENABLE
        ]);
        if(!is_null($model))
        {
            return $model;
        }

        if(YII_ENV_PROD)
        {
            $api = new Api($this->paymentKey, $this->paymentSecret);
            $result = $api->virtualAccount->create([
                'receivers' => [
                    'types' => ['bank_account']
                ],
            ]);
            $receivers = $result->receivers[0];
            $model = new RazorpayVirtualAccount();
            $model->order_id = $orderId;
            $model->user_id = $userId;
            $model->vid = $result->id;
            $model->bid = $receivers['id'];
            $model->customer_id = $result->customer_id;
            $model->name = $receivers['name'];
            $model->bank_name = $receivers['bank_name'];
            $model->ifsc = $receivers['ifsc'];
            $model->account_number = $receivers['account_number'];
            $model->status = RazorpayVirtualAccount::STATUS_ENABLE;
            $model->merchant_id = $this->merchantId;
            $model->pay_account_id = $this->accountId;
            if($model->save())
            {
                return $model;
            }else{
                throw new Exception('RazorpayVirtualAccount save fail');
            }
        }else{
            $model = new RazorpayVirtualAccount();
            $model->order_id = $orderId;
            $model->user_id = $userId;
            $model->vid = uniqid();
            $model->bid = uniqid();
            $model->customer_id = null;
            $model->name = 'iCredit';
            $model->bank_name = "YES BANK";
            $model->ifsc = 'YESB0000007';
            $model->account_number = date('YmdHis');
            $model->status = RazorpayVirtualAccount::STATUS_ENABLE;
            $model->merchant_id = $this->merchantId;
            $model->pay_account_id = $this->accountId;
            if($model->save())
            {
                return $model;
            }else{
                throw new Exception('RazorpayVirtualAccount save fail');
            }
        }



    }


    /**
     * 创建虚拟账号
     * @param integer$orderId
     * @param integer $userId
     * @return RazorpayUPIAddress|null
     * @throws Exception
     */
    public function createUPIAddress($orderId, $userId)
    {
        $model = RazorpayUPIAddress::findOne([
            'order_id' => $orderId,
            'user_id' => $userId,
            'pay_account_id' => $this->accountId,
            'status' => RazorpayUPIAddress::STATUS_ENABLE,
            'version' => 1
        ]);
        if(!is_null($model))
        {
            return $model;
        }

        $lockKey = "lock:razorpay:create_virtual_account:order_id:{$orderId}";
        if(!RedisQueue::lock($lockKey, 120))
        {
            throw new Exception('The operation is too frequent, please try again in 2 minutes');
        }

        $api = new Api($this->paymentKey, $this->paymentSecret);
        $result = $api->virtualAccount->create([
            'receivers' => [
                'types' => ['vpa', 'bank_account']
            ],
        ]);
        $model = new RazorpayUPIAddress();
        $model->user_id = $userId;
        $model->order_id = $orderId;
        $model->vid = $result->id;
        $model->name = $result->name;
        $model->merchant_id = $this->merchantId;

        foreach ($result->receivers as $receiver)
        {
            if('bank_account' == $receiver['entity'])
            {
                $model->va_id = $receiver['id'];
                $model->va_name = $receiver['name'];
                $model->va_bank_name = $receiver['bank_name'];
                $model->va_ifsc = $receiver['ifsc'];
                $model->va_account = $receiver['account_number'];
            }

            if('vpa' == $receiver['entity'])
            {
                $model->vpa_id = $receiver['id'];
                $model->username = $receiver['username'];
                $model->handle = $receiver['handle'];
                $model->address = $receiver['address'];
            }
        }
        $model->status = RazorpayUPIAddress::STATUS_ENABLE;
        $model->pay_account_id = $this->accountId;
        $model->version = 1;
        if($model->save())
        {
            RedisQueue::unlock($lockKey);
            return $model;
        }else{
            RedisQueue::unlock($lockKey);
            throw new Exception('RazorpayUPIAddress save fail');
        }
    }



    /**
     * 还款申请
     * @param RepaymentApplyForm $form
     * @return bool
     */
    public function repaymentApply(RepaymentApplyForm $form)
    {
        $userId = $form->userID;
        $orderId = $form->orderId;
        $amount = CommonHelper::UnitToCents($form->amount);
        $paymentType = $form->paymentType;
        $api = new Api($this->paymentKey, $this->paymentSecret);
        $orderUuid = uniqid("order_{$orderId}_");
        try{
            $order  = $api->order->create([
                'receipt'         => $orderUuid,
                'amount'          => $amount,
                'currency'        => 'INR',
                'payment_capture' =>  '1',
                'notes'           => ['source' => 'saas', 'id' => $this->accountId]
            ]);
        }catch (\Exception $exception)
        {
            yii::error([
                'request' => $form->toArray(),
                'error' => $exception->getMessage()
            ], 'razorpay_error');
            $this->setError('Service is busy. Please try again later');
            return false;
        }

        if('created' != $order->status){
            $this->setError('Service is busy. Please try again later');
            return false;
        }

        if(in_array($paymentType, [FinancialPaymentOrder::PAYMENT_TYPE_DELAY, FinancialPaymentOrder::PAYMENT_TYPE_DELAY_REDUCE]))
        {
            $isDelay = true;
        }else{
            $isDelay = false;

        }
        $payOrderId = $order->id;
        $paymentOrder = new FinancialPaymentOrder();
        $paymentOrder->user_id = $userId;
        $paymentOrder->order_id = $orderId;
        $paymentOrder->amount = $amount;
        $paymentOrder->status = FinancialPaymentOrder::STATUS_DEFAULT;
        $paymentOrder->order_uuid = $orderUuid;
        $paymentOrder->pay_order_id = $payOrderId;
        $paymentOrder->merchant_id = $this->merchantId;
        $paymentOrder->pay_account_id = $this->accountId;
        $paymentOrder->is_delay_repayment = intval($isDelay);
        $paymentOrder->payment_type = intval($paymentType);
        $paymentOrder->service_type = $form->serviceType;
        if($paymentOrder->save()){
            $this->setResult([
                'amount' => intval(CommonHelper::CentsToUnit($amount)),
                'orderId' => $payOrderId,
                'key' => $this->paymentKey,
                'payUrl' => rtrim($this->paymentDomain, '/') . '/h5/#/confirmRepayment'
            ]);
            return true;
        }else{
            Yii::error("orderId:{$orderId},orderUuid:{$orderUuid},payOrderId:{$orderUuid}保存失败");
            return false;
        }

    }



    /**
     * 授权结果返回
     * @param $razorpayOrderId
     * @return bool
     */
    public function orderPaymentAuthorized($razorpayOrderId)
    {
        if(empty($razorpayOrderId))
        {
            return true;
        }
        /**
         * @var FinancialPaymentOrder $paymentOrder
         */
        $paymentOrder = FinancialPaymentOrder::find()->where([
            'pay_order_id' => $razorpayOrderId])->one();
        if(is_null($paymentOrder)){
            return true;
        }
        $paymentOrder->auth_status = FinancialPaymentOrder::STATUS_SUCCESS;
        $paymentOrder->save();
        return true;
    }

    /**
     * 虚拟账号还款回调处理
     * @param $params
     * @return bool
     */
    public function orderVirtualAccountRepaymentNotify($params)
    {
        $vid = $params['payload']['virtual_account']['entity']['id'];
        $payPaymentId = $params['payload']['payment']['entity']['id'];
        $amount = $params['payload']['payment']['entity']['amount'];
        $type = $params['payload']['payment']['entity']['method'];
        if('upi' == $type)
        {
            $bankTransfer = $params['payload']['upi_transfer']['entity']['id'];

        }else{
            $bankTransfer = $params['payload']['bank_transfer']['entity']['id'];
        }

        return $this->orderUpiAddressRepayment($vid, $payPaymentId, $bankTransfer, $amount, $type);

    }


    /**
     * 虚拟账号还款
     * @param $vid
     * @param $payPaymentId
     * @param $amount
     * @return bool
     */
    public function orderVirtualAccountRepayment($vid, $payPaymentId, $bankTransfer, $amount)
    {
        $financialPaymentOrder = FinancialPaymentOrder::find()->where(['pay_payment_id' => $payPaymentId])->limit(1)->one();
        if(!is_null($financialPaymentOrder))
        {
            return true;
        }

        /** @var RazorpayVirtualAccount $razorpayVirtualAccount */
        $razorpayVirtualAccount = RazorpayVirtualAccount::find()->where(['vid' => $vid])->limit(1)->one();
        if(is_null($razorpayVirtualAccount))
        {
            Yii::error("vid:{$vid}, paymentId:{$payPaymentId}, amount:{$amount}, 查不到对应虚拟账号", 'razorpay_virtual_account');
            return false;
        }

        $orderID = $razorpayVirtualAccount->order_id;

        /** @var FinancialPaymentOrder $financialPaymentOrder */
        $financialPaymentOrder = new FinancialPaymentOrder();
        $financialPaymentOrder->order_id = $orderID;
        $financialPaymentOrder->user_id = $razorpayVirtualAccount->user_id;
        $financialPaymentOrder->amount = $amount;
        $financialPaymentOrder->status = FinancialPaymentOrder::STATUS_SUCCESS;
        $financialPaymentOrder->pay_payment_id = $payPaymentId;
        $financialPaymentOrder->pay_order_id = $bankTransfer;
        $financialPaymentOrder->type = FinancialPaymentOrder::TYPE_VIRTUAL_ACCOUNT;
        $financialPaymentOrder->pay_account_id = $this->accountId;
        $financialPaymentOrder->merchant_id = $this->merchantId;
        $financialPaymentOrder->is_booked = FinancialPaymentOrder::IS_BOOKED_NO;
        $financialPaymentOrder->is_refund = FinancialPaymentOrder::IS_REFUND_NO;
        $financialPaymentOrder->success_time = time();
        $financialPaymentOrder->save();
        $service = new RepaymentService();
        $r =  $service->repaymentHandle($orderID, $amount, UserRepaymentLog::TYPE_VIRTUAL, 0, $financialPaymentOrder->is_delay_repayment);
        if($r)
        {
            $financialPaymentOrder->is_booked = FinancialPaymentOrder::IS_BOOKED_YES;
            return $financialPaymentOrder->save();
        }else{
            return false;
        }

    }


    /**
     * 虚拟账号还款
     * @param $vid
     * @param $payPaymentId
     * @param $bankTransfer
     * @param $amount
     * @param $type
     * @return bool
     */
    public function orderUpiAddressRepayment($vid, $payPaymentId, $bankTransfer, $amount, $type)
    {
        $lockKey = "razorpay:payment_virtual_account:callback_{$payPaymentId}";
        if(!RedisQueue::lock($lockKey, 60))
        {
            return false;
        }
        $financialPaymentOrder = FinancialPaymentOrder::find()->where(['pay_payment_id' => $payPaymentId])->limit(1)->one();
        if(!is_null($financialPaymentOrder))
        {
            return true;
        }

        /** @var RazorpayUPIAddress $razorpayVirtualAccount */
        $razorpayVirtualAccount = RazorpayUPIAddress::find()->where(['vid' => $vid, 'pay_account_id' => $this->accountId])->limit(1)->one();
        if(is_null($razorpayVirtualAccount))
        {
            $razorpayVirtualAccount = RazorpayVirtualAccount::findOne(['vid' => $vid]);
            if(is_null($razorpayVirtualAccount))
            {
                Yii::error("vid:{$vid}, paymentId:{$payPaymentId}, amount:{$amount}, 查不到对应虚拟账号", 'razorpay_virtual_account');
                return false;
            }
        }

        $order = UserLoanOrder::findOne($razorpayVirtualAccount->order_id);

        $financialPaymentOrder = new FinancialPaymentOrder();
        $financialPaymentOrder->order_id = $order->id ?? 0;
        $financialPaymentOrder->user_id = $razorpayVirtualAccount->user_id;
        $financialPaymentOrder->amount = $amount;
        $financialPaymentOrder->status = FinancialPaymentOrder::STATUS_SUCCESS;
        $financialPaymentOrder->pay_payment_id = $payPaymentId;
        $financialPaymentOrder->pay_order_id = $bankTransfer;
        $financialPaymentOrder->type = 'upi' == $type ? FinancialPaymentOrder::TYPE_UPI_ADDRESS : FinancialPaymentOrder::TYPE_VIRTUAL_ACCOUNT;
        $financialPaymentOrder->pay_account_id = $this->accountId;
        $financialPaymentOrder->merchant_id = $this->merchantId;
        $financialPaymentOrder->is_booked = FinancialPaymentOrder::IS_BOOKED_NO;
        $financialPaymentOrder->is_refund = FinancialPaymentOrder::IS_REFUND_NO;
        $financialPaymentOrder->success_time = time();
        $financialPaymentOrder->save();
        $service = new RepaymentService();
        $r =  $service->repaymentHandle($order->id, $amount, UserRepaymentLog::TYPE_VIRTUAL, 0, $financialPaymentOrder->is_delay_repayment);
        if($r)
        {
            $financialPaymentOrder->is_booked = FinancialPaymentOrder::IS_BOOKED_YES;
            return $financialPaymentOrder->save();
        }else{
            return false;
        }

    }

    /**
     * 还款结果返回
     * @param $razorpayOrderId
     * @return bool
     */
    public function orderRepaymentNotify($razorpayOrderId, $razorpayPaymentId)
    {

        /**
         * @var FinancialPaymentOrder $paymentOrder
         */
        $paymentOrder = FinancialPaymentOrder::find()->where([
            'pay_order_id' => $razorpayOrderId, 'pay_account_id' => $this->accountId])->one();
        if(is_null($paymentOrder)){
            return true;
        }
        if($paymentOrder->status != FinancialPaymentOrder::STATUS_DEFAULT){
            return true;
        }

        $lockKey = "razorpay:payment_gateway:callback_{$razorpayOrderId}";
        if(!RedisQueue::lock($lockKey, 60))
        {
            return false;
        }

        $paymentOrder->status = FinancialPaymentOrder::STATUS_SUCCESS;
        $paymentOrder->pay_payment_id = $razorpayPaymentId;
        $paymentOrder->is_booked = FinancialPaymentOrder::IS_BOOKED_NO;
        $paymentOrder->is_refund = FinancialPaymentOrder::IS_REFUND_NO;
        $paymentOrder->success_time = time();
        $paymentOrder->save();

        //检查订单是否已关闭，如已关闭，则直接告诉razorpay通知成功
        $orderCloseCheck = UserLoanOrder::find()
            ->where([
                'id' => $paymentOrder->order_id,
                'status' => UserLoanOrder::STATUS_PAYMENT_COMPLETE])->exists();
        if($orderCloseCheck)
        {
            return true;
        }
        $service = new RepaymentService();
        $r = $service->repaymentHandle($paymentOrder->order_id, $paymentOrder->amount, UserRepaymentLog::TYPE_ACTIVE, 0, $paymentOrder->payment_type);
        if($r)
        {
            $paymentOrder->is_booked = FinancialPaymentOrder::IS_BOOKED_YES;
            return $paymentOrder->save();
        }else{
            yii::error(['razorpayOrderId' => $razorpayOrderId, 'razorpayPaymentId' => $razorpayPaymentId, 'error' => $service->getError()], 'orderRepaymentNotify');
            return false;
        }

    }


    /**
     * 回调签名认证
     * @param $sign
     * @param $params
     * @return bool
     */
    public function verifyWebhookSignature($sign, $params)
    {
        $api = new Api($this->paymentKey, $this->paymentSecret);
        $api->utility->verifyWebhookSignature(json_encode($params), $sign, $this->webhooksSecret);
        return true;
    }


    /**
     * 打款回调方法
     * @param array $params
     * @return bool
     */
    public function payoutNotify($params)
    {
        $order_uuid = $params['payload']['payout']['entity']['reference_id'] ?? '';
        if(empty($order_uuid))
        {
            Yii::warning("order_uuid is null", 'razorpay_payout');
            return false;
        }

        $lockKey = "razorpay:paoout:callback_{$order_uuid}";
        if(!RedisQueue::lock($lockKey, 60))
        {
            return false;
        }

        $withdrawInfo = FinancialLoanRecord::findOne(['order_id' => $order_uuid]);
        if(empty($withdrawInfo)){
            Yii::warning("no withdraw info for order_uuid:".$order_uuid, 'razorpay_payout');
            return true;
        }

        if($withdrawInfo->status != FinancialLoanRecord::UMP_PAY_SUCCESS){
            $order = UserLoanOrder::findOne($withdrawInfo->business_id);
            $loanService = new LoanService();
            if ($params['event'] == 'payout.processed')
            {
                $success_time = $params['created_at'];
                $withdrawInfo->notify_result = json_encode($params);
                $withdrawInfo->status = FinancialLoanRecord::UMP_PAY_SUCCESS;
                $withdrawInfo->utr = $params['payload']['payout']['entity']['utr'] ?? '';
                $withdrawInfo->trade_no = $params['payload']['payout']['entity']['id'];
                if (!$withdrawInfo->success_time)
                {
                    $withdrawInfo->success_time = $success_time;
                }
                $withdrawInfo->save();
                $loanService->loanSuccessCallback($order, $success_time);
            } elseif($params['event'] == 'payout.reversed') {
                if(date('H') < '23' && $withdrawInfo->retry_num < 5 && date('Y-m-d') == date('Y-m-d', $withdrawInfo->created_at)){
                    $time = min(strtotime(date('Y-m-d 22:50:00')) - time(), 3600);
                    if($time > 300){
                        $withdrawInfo->retry_num = $withdrawInfo->retry_num + 1;
                        $withdrawInfo->retry_time = time() + mt_rand(300, $time);
                        $withdrawInfo->status = FinancialLoanRecord::UMP_PAYING;
                        $withdrawInfo->notify_result = json_encode($params);
                        $withdrawInfo->save();
                        return true;
                    }
                }

                $withdrawInfo->status = FinancialLoanRecord::UMP_PAY_HANDLE_FAILED;
                $withdrawInfo->notify_result = json_encode($params);
                $withdrawInfo->save();
                $service = new WeWorkService();
                $message = sprintf('[saas][order_id:%s] : 打款失败，需人工处理',
                    $withdrawInfo->business_id);
                $service->send($message);
            }
        }

        return true;

    }


    /**
     * 打款
     * @param FinancialLoanRecord $financialLoanRecord
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function doPayMoney(FinancialLoanRecord $financialLoanRecord){
        $contact = $this->createContact($financialLoanRecord->user_id);
        if(empty($contact)){
            return [];
        }
        $account = $this->createAccounts($contact->contact_id, $financialLoanRecord->ifsc, $financialLoanRecord->loanPerson->name, $financialLoanRecord->account);
        if(empty($account)){
            return [];
        }
        $params = [
            'account_number'  => $this->accountNumber,
            'fund_account_id' => $account->fund_account_id,
            'amount'          => YII_ENV_PROD ? $financialLoanRecord->money : 100,
            'reference_id'    => $financialLoanRecord->order_id,
            'currency'        => 'INR',
            'mode'            => 'IMPS',
            'purpose'         => 'payout',
        ];
        $response = $this->postData($this->baseUri.'payouts', $params);
        $data = json_decode($response->getBody()->getContents(), true);
        Yii::info(['order_id' => $financialLoanRecord->business_id, 'http_code' => $response->getStatusCode(), 'params' => $params, 'response' => $data], 'razorpay_payout');
        if(200 != $response->getStatusCode()){
            return [];
        }
        return $data;
    }

    /**
     * 创建联系人
     * @param $user_id
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function createContact($user_id){
        $contact = RazorpayContact::find()
            ->where([
                'user_id' => $user_id,
                'merchant_id' => $this->merchantId,
                'pay_account_id' => $this->payAccountSetting->id
            ])->one();
        if(!empty($contact)){
            return $contact;
        }
        $loan_person = LoanPerson::findOne(['id' => $user_id]);
        $params['name'] = $loan_person->name;
        $params['type'] = 'customer';
        $response = $this->postData($this->baseUri.'contacts', $params);
        $data  = json_decode($response->getBody()->getContents(), true);
        Yii::info(['user_id' => $user_id,'http_code' => $response->getStatusCode(),'params' => $params, 'response' => $data], 'razorpay_payout');
        if(200 != $response->getStatusCode() && 201 != $response->getStatusCode()){
            return [];
        }
        $query = new RazorpayContact();
        $query->pay_account_id = $this->payAccountSetting->id;
        $query->merchant_id = $this->merchantId;
        $query->user_id    = $user_id;
        $query->contact_id = $data['id'];
        $query->type       = $data['type'];
        $query->active     = $data['active'] ? 1 : 0;
        if($query->save()){
            return $query;
        }
        Yii::error(['user_id' => $user_id,'message' => 'RazorpayContact保存失败'], 'razorpay_payout');
        return [];
    }


    /**
     * 创建联系人账户
     * @param $contact_id
     * @param $ifsc
     * @param $name
     * @param $account
     * @return array|RazorpayAccount|null
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function createAccounts($contact_id,$ifsc,$name,$account){
        $fund_account = RazorpayAccount::find()->where([
            'contact_id' => $contact_id,
            'ifsc' => $ifsc,
            'name' => $name,
            'account' => $account,
            'merchant_id' => $this->merchantId,
            'pay_account_id' => $this->payAccountSetting->id
        ])->one();
        if(!empty($fund_account)){
            return $fund_account;
        }
        $params = [
            'contact_id'   => $contact_id,
            'account_type' => 'bank_account',
            'bank_account' => [
                'name'           => $name,
                'ifsc'           => $ifsc,
                'account_number' => $account,
            ]
        ];
        $response = $this->postData($this->baseUri.'fund_accounts', $params);
        $data  = json_decode($response->getBody()->getContents(), true);
        Yii::info(['http_code' => $response->getStatusCode(), 'params' => $params, 'response' => $data], 'financial');
        if(200 != $response->getStatusCode() && 201 != $response->getStatusCode()){
            return [];
        }
        $query = new RazorpayAccount();
        $query->merchant_id     = $this->merchantId;
        $query->pay_account_id  = $this->payAccountSetting->id;
        $query->contact_id      = $data['contact_id'];
        $query->fund_account_id = $data['id'];
        $query->ifsc            = $data['bank_account']['ifsc'];
        $query->name            = $data['bank_account']['name'];
        $query->account         = $data['bank_account']['account_number'];
        $query->account_type    = $data['account_type'];
        $query->active          = $data['active'] ? 1 : 0;
        if($query->save()){
            return $query;
        }
        Yii::info(['contact_id' => $contact_id,'message' => 'RazorpayAccount保存失败'], 'financial');
        return [];
    }


    /**
     * @param $url
     * @param $params
     * @return mixed|\Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function postData($url,$params){
        $client = new Client([
            RequestOptions::TIMEOUT => 120,
        ]);

        $response = $client->request('POST', $url, [
            RequestOptions::JSON => $params,
            RequestOptions::AUTH => [$this->payoutKey, $this->payoutSecret]
        ]);
        return $response;
    }


    /**
     * 生成打款订单
     * @param $data
     */
    public function createFinancialLoanRecord($data){
        try {
            $user_id      = $data['user_id'];//借款人ID
            $bind_card_id = intval($data['bind_card_id']);//绑卡自增表ID
            $business_id  = intval($data['business_id']);//业务订单主键ID
            $money        = $data['money'];//打款金额
            $ifsc         = $data['ifsc'];
            $bank_name    = $data['bank_name'];//银行名称
            $account      = $data['account'];//银行卡号
            if (empty($bind_card_id) || empty($business_id) || empty($money) || ($money <= 0 ) || empty($account) || empty($user_id)) {
                throw new Exception("抱歉，缺少必要的参数！");
            }

            $loan_data = FinancialLoanRecord::find()->where([
                'merchant_id' => $this->merchantId,
                'business_id' => $business_id,
                'status' => [FinancialLoanRecord::UMP_PAYING, FinancialLoanRecord::UMP_PAY_SUCCESS, FinancialLoanRecord::UMP_CMB_PAYING],
            ])->one();
            if (!empty($loan_data)) {
                throw new Exception("抱歉，正在处理的打款订单号，不能重复添加！");
            }

            $loan_person = LoanPerson::findOne($user_id);
            if (empty($loan_person)) {
                throw new Exception("抱歉，非平台用户");
            }

            $card_info = UserBankAccount::findOne($bind_card_id);
            if (empty($card_info)) {
                throw new Exception("抱歉，银行卡不存在");
            }

            $query = new FinancialLoanRecord();
            $query->user_id      = $user_id;
            $query->order_id     = self::generateOrderId();
            $query->bind_card_id = $bind_card_id;
            $query->business_id  = $business_id;
            $query->status       = FinancialLoanRecord::UMP_PAYING;
            $query->money        = $money;
            $query->ifsc         = $ifsc;
            $query->bank_name    = $bank_name;
            $query->account      = $account;
            $query->pay_account_id = $this->accountId;
            $query->merchant_id = $this->merchantId;
            $query->service_type = FinancialLoanRecord::SERVICE_TYPE_RAZORPAY;
            if ($query->save()) {
                return [
                    'code' => 0,
                    'message' => '插入成功',
                ];
            }
        }
        catch (Exception $e) {
            return [
                'code' => 1,
                'message' => $e->getMessage(),
            ];
        }

    }


    /**
     * 生成订单号
     */
    public static function generateOrderId()
    {
        $uniqid = "_" . uniqid(rand(0, 9));

        $order_id = date('Ymd') . "{$uniqid}";
        return $order_id;
    }



    /**
     * 打款状态查询
     * @param $txnId
     * @return bool
     */
    public function loanQueryHandle($txnId)
    {
        $this->loanPayStatus = FinancialService::LOAN_STATUS_PENDING;
        return true;
    }


    /**
     * 统一打款方法
     * @param LoanPayForm $form
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function loanPayHandle(LoanPayForm $form)
    {

        $contact = $this->createContactNew($form);
        if(empty($contact)){
            return false;
        }
        $account = $this->createAccountsNew($contact->contact_id, $form->beneIFSC, $form->beneName, $form->beneAccNo);
        if(empty($account)){
            return false;
        }
        $params = [
            'account_number'  => $this->accountNumber,
            'fund_account_id' => $account->fund_account_id,
            'amount'          => YII_ENV_PROD ? $form->amount : 100,
            'reference_id'    => $form->txnId,
            'currency'        => 'INR',
            'mode'            => 'IMPS',
            'purpose'         => 'payout',
        ];

        $response = $this->postData($this->baseUri.'payouts', $params);
        $this->loanPayRequestResult = $response->getBody()->getContents();
        $payout = json_decode($this->loanPayRequestResult, true);
        if(200 != $response->getStatusCode()){
            $this->loanPayStatus =  FinancialService::LOAN_STATUS_FAILURE;
        }else{
            $this->thirdOrderID = $payout['id'];
            $this->loanPayStatus = FinancialService::LOAN_STATUS_PENDING;
        }

        return true;
    }



    /**
     * 创建联系人新
     * @param LoanPayForm $form
     * @return array|RazorpayContact|null
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function createContactNew(LoanPayForm $form){
        $contact = RazorpayContact::findOne([
            'user_id' => $form->userID,
            'merchant_id' => $this->merchantId,
            'pay_account_id' => $this->payAccountSetting->id
        ]);
        if(!empty($contact)){
            return $contact;
        }
        $params['name'] = $form->beneName;
        $params['type'] = 'customer';
        $response = $this->postData($this->baseUri.'contacts', $params);
        $data  = json_decode($response->getBody()->getContents(), true);
        Yii::info(['user_id' => $form->userID,'http_code' => $response->getStatusCode(),'params' => $params, 'response' => $data], 'razorpay_payout');
        if(200 != $response->getStatusCode() && 201 != $response->getStatusCode()){
            return [];
        }
        $query = new RazorpayContact();
        $query->pay_account_id = $this->payAccountSetting->id;
        $query->merchant_id = $this->merchantId;
        $query->user_id    = $form->userID;
        $query->contact_id = $data['id'];
        $query->type       = $data['type'];
        $query->active     = $data['active'] ? 1 : 0;
        if($query->save()){
            return $query;
        }
        Yii::error(['user_id' => $form->userID,'message' => 'RazorpayContact保存失败'], 'razorpay_payout');
        return [];
    }


    public function createAccountsNew($contact_id, $ifsc, $name, $account){
        $fund_account = RazorpayAccount::findOne([
            'contact_id' => $contact_id,
            'ifsc' => $ifsc,
            'name' => $name,
            'account' => $account,
            'merchant_id' => $this->merchantId,
            'pay_account_id' => $this->payAccountSetting->id
        ]);
        if(!empty($fund_account)){
            return $fund_account;
        }
        $params = [
            'contact_id'   => $contact_id,
            'account_type' => 'bank_account',
            'bank_account' => [
                'name'           => $name,
                'ifsc'           => $ifsc,
                'account_number' => $account,
            ]
        ];
        $response = $this->postData($this->baseUri.'fund_accounts', $params);
        $data  = json_decode($response->getBody()->getContents(), true);
        Yii::info(['http_code' => $response->getStatusCode(), 'params' => $params, 'response' => $data], 'financial');
        if(200 != $response->getStatusCode() && 201 != $response->getStatusCode()){
            return [];
        }
        $query = new RazorpayAccount();
        $query->merchant_id     = $this->merchantId;
        $query->contact_id      = $data['contact_id'];
        $query->pay_account_id  = $this->payAccountSetting->id;
        $query->fund_account_id = $data['id'];
        $query->ifsc            = $data['bank_account']['ifsc'];
        $query->name            = $data['bank_account']['name'];
        $query->account         = $data['bank_account']['account_number'];
        $query->account_type    = $data['account_type'];
        $query->active          = $data['active'] ? 1 : 0;
        if($query->save()){
            return $query;
        }
        Yii::info(['contact_id' => $contact_id,'message' => 'RazorpayAccount保存失败'], 'financial');
        return [];
    }




    /**
     * 创建还款链接
     * @param $amount
     * @param $userID
     * @param $orderID
     * @return bool
     */
    public function createPaymentLink($amount, $userID, $orderID)
    {
        $api = new Api($this->paymentKey, $this->paymentSecret);
        $params = [
            'type' => 'link',
            'amount' => $amount,
            'description' => 'payment',
//            'partial_payment' => true
        ];
        $link  = $api->invoice->create($params);
        $payOrderId = $link->order_id;
        $invID = $link->id;
        $url = $link->short_url;

        $paymentOrder = new FinancialPaymentOrder();
        $paymentOrder->user_id = $userID;
        $paymentOrder->order_id = $orderID;
        $paymentOrder->amount = $amount;
        $paymentOrder->status = FinancialPaymentOrder::STATUS_DEFAULT;
        $paymentOrder->order_uuid = $invID;
        $paymentOrder->pay_order_id = $payOrderId;
        $paymentOrder->pay_account_id = $this->accountId;
        $paymentOrder->service_type = FinancialPaymentOrder::SERVICE_TYPE_RAZORPAY_PAYMENT_LINK;
        $paymentOrder->payment_type = FinancialPaymentOrder::PAYMENT_TYPE_DEFAULT;
        $paymentOrder->merchant_id = $this->merchantId;
        if($paymentOrder->save()){
            $this->setResult([
                'url' => $url
            ]);
            return true;
        }else{
            Yii::error("orderId:{$orderID},orderUuid:{$invID},payOrderId:{$payOrderId}保存失败");
            return false;
        }
    }

}
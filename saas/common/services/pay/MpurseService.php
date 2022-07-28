<?php
namespace common\services\pay;

use common\helpers\CommonHelper;
use common\helpers\RedisQueue;
use common\models\financial\FinancialPaymentOrder;
use common\models\order\UserRepaymentLog;
use common\models\pay\MpursePaymentForm;
use common\models\pay\PayAccountSetting;
use common\models\pay\RazorpayAccountForm;
use common\models\user\LoanPerson;
use common\services\repayment\RepaymentService;
use frontend\models\loan\RepaymentApplyForm;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Yii;
use yii\db\Exception;

/**
 * Class MpurseService
 * @package common\services\pay
 *
 * @property RazorpayAccountForm $accountForm
 * @property PayAccountSetting $payAccountSetting
 */
class MpurseService extends BasePayService
{
    private $payAccountSetting;
    private $partnerID,$key;
    private $accountId;
    private $url;
    private $merchantId;


    public $loanPayStatus; //打款状态
    public $loanPaySuccessTime; //打款成功时间
    public $thirdOrderID; //三方支付ID

    public $loanPayQueryResult; //打款状态查询结果
    public $loanPayRequestResult; //打款请求结果

    const FAIL_STATUS_CODE = [
        '6616',
        '6620',
        '6657',
        '6656',
        '6655',
        '8887',
        '8888',
        '9993',
        '9994',
    ];

    const SUCCESS_STATUS_CODE = '0000';
    const ORDER_REPEAT = '6657';

    const PAYMENT_STATUS_SUCCESS = 'TRANSACTION_SUCCESS'; //打款成功
    const PAYMENT_STATUS_FAILURE = 'TRANSACTION_FAILURE'; //打款失败
    const PAYMENT_STATUS_PENDING = 'PENDING'; //打款处理中


    public function __construct(PayAccountSetting $payAccountSetting, $config = [])
    {
        $this->payAccountSetting = $payAccountSetting;
        /** @var RazorpayAccountForm $form */
        $form = self::formModel();
        $form->load($payAccountSetting->getAccountInfo(), '');
        $this->accountId = $payAccountSetting->id;
        $this->partnerID = $form->mpursePartnerId;
        $this->key = $this->str2bin($form->mpurseKey);
        $this->merchantId = $this->payAccountSetting->merchant_id;

        if(YII_ENV_PROD){
            $this->url = 'https://api.mpursewallet.com/api/v2/gateway';
        }else{
            $this->url = 'https://stg.mpursewallet.com/api/v2/gateway';
        }

        parent::__construct($config);
    }


    /**
     * @param $payAccountId
     * @return MpurseService
     */
    public static function getInstanceByPayAccountId($payAccountId)
    {
        $payAccountSetting = PayAccountSetting::findOne($payAccountId);
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
     * @param MpursePaymentForm $mpForm
     * @return false|string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function unifiedEasyOrder(MpursePaymentForm $mpForm)
    {
        $url = "{$this->url}/unifiedEasyOrder/{$this->partnerID}";

        $data = json_encode($mpForm->toArray(), JSON_UNESCAPED_UNICODE);
        $params = $this->encryptParams($data);
        $response = $this->postData($url, $params);
        $result = $this->decryptData($response->getBody()->getContents());
        return $result;
    }

    /**
     * @param MpursePaymentForm $mpForm
     * @return false|string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function loanReimburse(MpursePaymentForm $mpForm)
    {
        $url = "{$this->url}/loanReimburse/{$this->partnerID}";

        $data = json_encode($mpForm->toArray(), JSON_UNESCAPED_UNICODE);
        $params = $this->encryptParams($data);
        $response = $this->postData($url, $params);
        $result = $this->decryptData($response->getBody()->getContents());
        return $result;
    }

    /**
     * 还款申请
     * @param RepaymentApplyForm $form
     * @return bool
     */
    public function repaymentApply(RepaymentApplyForm $form)
    {
        if($form->serviceType == FinancialPaymentOrder::SERVICE_TYPE_MPURSE_UPI){
            return $this->repaymentApplyUpi($form);
        }

        $userId = $form->userID;
        $orderId = $form->orderId;
        $amount = CommonHelper::UnitToCents($form->amount);
        $paymentType = $form->paymentType;
        $orderUuid = uniqid("{$orderId}order");

        $loanPerson = LoanPerson::findOne($userId);
        $mpForm = new MpursePaymentForm();
        $mpForm->amount = $form->amount;
        $mpForm->txnId = $orderUuid;
        $mpForm->product = $orderId;
        $mpForm->cName = $loanPerson->name;
        $mpForm->cMobile = $loanPerson->phone;

        try{
            $content = $this->unifiedEasyOrder($mpForm);
            yii::info(['user_id' => $userId, 'params' => $mpForm->toArray(), 'response' => $content], 'mpurse');
            $result = json_decode($content, true);

            if(!isset($result['status']) || self::SUCCESS_STATUS_CODE != $result['status']){
                throw new Exception('Service is busy. Please try again later');
            }

        }catch (\Exception $exception)
        {
            yii::error([
                'request' => $mpForm->toArray(),
                'error' => $exception->getMessage()
            ], 'mpurse_error');
            $this->setError('Service is busy. Please try again later');
            return false;
        }

        if(in_array($paymentType, [FinancialPaymentOrder::PAYMENT_TYPE_DELAY, FinancialPaymentOrder::PAYMENT_TYPE_DELAY_REDUCE]))
        {
            $isDelay = true;
        }else{
            $isDelay = false;
        }
        $paymentOrder = new FinancialPaymentOrder();
        $paymentOrder->user_id = $userId;
        $paymentOrder->order_id = $orderId;
        $paymentOrder->amount = $amount;
        $paymentOrder->status = FinancialPaymentOrder::STATUS_DEFAULT;
        $paymentOrder->order_uuid = $orderUuid;
        $paymentOrder->pay_order_id = $result['retBizParams']['mpQueryId'];
        $paymentOrder->merchant_id = $this->merchantId;
        $paymentOrder->pay_account_id = $this->accountId;
        $paymentOrder->is_delay_repayment = intval($isDelay);
        $paymentOrder->payment_type = intval($paymentType);
        $paymentOrder->service_type = $form->serviceType;
        if($paymentOrder->save()){
            $this->setResult([
                'hash' => $result['retBizParams']['hash'],
                'partnerId' => $result['retBizParams']['partnerId'],
                'mpQueryId' => $result['retBizParams']['mpQueryId'],
                'txnId' => $result['retBizParams']['txnId']
            ]);
            return true;
        }else{
            Yii::error("orderId:{$orderId},orderUuid:{$orderUuid},payOrderId:{$result['retBizParams']['mpQueryId']}保存失败");
            return false;
        }
    }

    /**
     * 还款申请
     * @param RepaymentApplyForm $form
     * @return bool
     */
    public function repaymentApplyUpi(RepaymentApplyForm $form)
    {
        $userId = $form->userID;
        $orderId = $form->orderId;
        $amount = CommonHelper::UnitToCents($form->amount);
        $paymentType = $form->paymentType;
        $orderUuid = uniqid("{$orderId}order");

        $mpForm = new MpursePaymentForm();
        $mpForm->amount = $form->amount;
        $mpForm->txnId = $orderUuid;
        $mpForm->product = $orderId;
        $mpForm->cName = $form->customerName;
        $mpForm->cMobile = substr($form->customerPhone, '-10');
        $mpForm->payerVA = $form->customerUpiAccount;

        try{
            $content = $this->loanReimburse($mpForm);
            yii::info(['user_id' => $userId, 'params' => $mpForm->toArray(), 'response' => $content], 'mpurse_upi');
            $result = json_decode($content, true);

            if(!isset($result['status']) || self::SUCCESS_STATUS_CODE != $result['status']){
                throw new Exception('Service is busy. Please try again later');
            }

        }catch (\Exception $exception)
        {
            yii::error([
                'request' => $mpForm->toArray(),
                'error' => $exception->getMessage()
            ], 'mpurse_error');
            $this->setError('Service is busy. Please try again later');
            return false;
        }

        if(in_array($paymentType, [FinancialPaymentOrder::PAYMENT_TYPE_DELAY, FinancialPaymentOrder::PAYMENT_TYPE_DELAY_REDUCE]))
        {
            $isDelay = true;
        }else{
            $isDelay = false;
        }
        $paymentOrder = new FinancialPaymentOrder();
        $paymentOrder->user_id = $userId;
        $paymentOrder->order_id = $orderId;
        $paymentOrder->amount = $amount;
        $paymentOrder->status = FinancialPaymentOrder::STATUS_DEFAULT;
        $paymentOrder->order_uuid = $orderUuid;
        $paymentOrder->pay_order_id = $result['retBizParams']['mpRefId'];
        $paymentOrder->merchant_id = $this->merchantId;
        $paymentOrder->pay_account_id = $this->accountId;
        $paymentOrder->is_delay_repayment = intval($isDelay);
        $paymentOrder->payment_type = intval($paymentType);
        $paymentOrder->service_type = $form->serviceType;
        if($paymentOrder->save()){
            $this->setResult([]);
            return true;
        }else{
            Yii::error("orderId:{$orderId},orderUuid:{$orderUuid},payOrderId:{$result['retBizParams']['mpQueryId']}保存失败");
            return false;
        }
    }

    /**
     * 还款结果返回
     * @param $razorpayOrderId
     * @return bool
     */
    public function orderRepaymentNotify($params)
    {
        $params = $this->decryptData($params);
        $params = json_decode($params, true);

        Yii::info($params, 'MpurseRepayment');

        $lockKey = "mpurse:payment:callback_{$params['mpQueryId']}";
        if(!RedisQueue::lock($lockKey, 60))
        {
            return false;
        }

        /**
         * @var FinancialPaymentOrder $paymentOrder
         */
        $paymentOrder = FinancialPaymentOrder::find()->where([
            'pay_order_id' => $params['mpQueryId'], 'order_uuid' => $params['txnId']])->one();
        if(is_null($paymentOrder)){
            return true;
        }
        if($paymentOrder->status != FinancialPaymentOrder::STATUS_DEFAULT){
            return true;
        }

        if(self::PAYMENT_STATUS_SUCCESS == $params['txnStatus']){
            $paymentOrder->status = FinancialPaymentOrder::STATUS_SUCCESS;
            $paymentOrder->is_booked = FinancialPaymentOrder::IS_BOOKED_NO;
            $paymentOrder->is_refund = FinancialPaymentOrder::IS_REFUND_NO;
            $paymentOrder->success_time = time();
            $paymentOrder->save();
            $service = new RepaymentService();
            $r = $service->repaymentHandle($paymentOrder->order_id, $paymentOrder->amount, UserRepaymentLog::TYPE_ACTIVE, 0, $paymentOrder->payment_type);
            if($r)
            {
                $paymentOrder->is_booked = FinancialPaymentOrder::IS_BOOKED_YES;
                return $paymentOrder->save();
            }else{
                return false;
            }
        }

        return true;
    }

    private function encryptParams($data)
    {
        return base64_encode(openssl_encrypt($data, 'aes-256-ecb', $this->key, OPENSSL_RAW_DATA));
    }

    private function decryptData($data)
    {
        return openssl_decrypt( base64_decode($data), 'aes-256-ecb', $this->key, OPENSSL_RAW_DATA);
    }


    public  function str2bin($hexdata)
    {
        $bindata="";
        for ($i=0;$i < strlen($hexdata);$i+=2) {
            $bindata.=chr(hexdec(substr($hexdata,$i,2)));
        }
        return $bindata;
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
            RequestOptions::HEADERS => [
                'Content-Type' => 'text/plain'
            ],
            RequestOptions::BODY => $params,
        ]);
        return $response;
    }

}
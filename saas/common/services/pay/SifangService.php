<?php
namespace common\services\pay;

use common\helpers\CommonHelper;
use common\helpers\RedisQueue;
use common\models\financial\FinancialPaymentOrder;
use common\models\order\UserRepaymentLog;
use common\models\pay\PayAccountSetting;
use common\models\pay\RazorpayAccountForm;
use common\models\pay\SifangPaymentForm;
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
class SifangService extends BasePayService
{
    private $payAccountSetting;
    private $userId,$apiKey;
    private $accountId;
    private $url;
    private $merchantId;

    public function __construct(PayAccountSetting $payAccountSetting, $config = [])
    {
        $this->payAccountSetting = $payAccountSetting;
        /** @var RazorpayAccountForm $form */
        $form = self::formModel();
        $form->load($payAccountSetting->getAccountInfo(), '');
        $this->accountId = $payAccountSetting->id;
        $this->userId = $form->sifangUserId;
        $this->apiKey = $form->sifangApiKey;
        $this->merchantId = $this->payAccountSetting->merchant_id;

        $this->url = 'https://manager.dp51688.com/api/';

        parent::__construct($config);
    }

    /**
     * @param $payAccountId
     * @return SifangService
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
     * @param $data
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function create($data)
    {
        $isTest = $data['IsTest'] ? 'true' : 'false';
        $str = '';
        $str .= 'Amount='.$data['Amount'].'&';
        $str .= 'CurrencyId='.$data['CurrencyId'].'&';
        $str .= 'IsTest='. $isTest .'&';
        $str .= 'PayerKey='.$data['PayerKey'].'&';
        $str .= 'PayerName='.$data['PayerName'].'&';
        $str .= 'PaymentChannelId='.$data['PaymentChannelId'].'&';
        $str .= 'ShopInformUrl='.$data['ShopInformUrl'].'&';
        $str .= 'ShopOrderId='.$data['ShopOrderId'].'&';
        $str .= 'ShopReturnUrl='.$data['ShopReturnUrl'].'&';
        $str .= 'ShopUserLongId='.$data['ShopUserLongId'].'&';
        $str .= 'HashKey='.$this->apiKey;

        $hash = strtoupper(hash('sha256', strtolower($str)));
        $data['EncryptValue'] = $hash;

        $response = $this->postData('createOrder', $data);
        return $response->getBody()->getContents();
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
        $orderUuid = uniqid("order_{$orderId}_");

        $loanPerson = LoanPerson::findOne($userId);

        $data = [
            'ShopUserLongId' => $this->userId,
            'ShopOrderId' => $orderUuid,
            'PaymentChannelId' => floatval($form->paymentChannel),
            'CurrencyId' => 2,
            'Amount' => floatval($form->amount),
            'PayerName' => $loanPerson->name,
            'ShopReturnUrl' => (!empty($form->host) ? $form->host : Yii::$app->request->getHostInfo()) . '/h5/#/siFangRepaymentSuccess',
            'ShopInformUrl' => Yii::$app->request->getHostInfo()."/notify/sifang-repayment?account_id=".$this->accountId,
            'IsTest' => !YII_ENV_PROD,
            'PayerKey' => strval($orderId),
        ];

        try{
            $content = $this->create($data);

            yii::info(['user_id' => $userId, 'params' => $data, 'response' => $content], 'sifang');
            $result = json_decode($content, true);

            if(!isset($result['Success']) || !$result['Success']){
                throw new Exception('Service is busy. Please try again later');
            }

        }catch (\Exception $exception)
        {
            yii::error([
                'request' => $data,
                'error' => $exception->getMessage()
            ], 'sifang_error');
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
        $paymentOrder->pay_order_id = $result['TrackingNumber'];
        $paymentOrder->merchant_id = $this->merchantId;
        $paymentOrder->pay_account_id = $this->accountId;
        $paymentOrder->is_delay_repayment = intval($isDelay);
        $paymentOrder->payment_type = intval($paymentType);
        $paymentOrder->service_type = $form->serviceType;
        if($paymentOrder->save()){
            $this->setResult([
                'orderUrl' => $result['PayUrl'],
            ]);
            return true;
        }else{
            Yii::error("orderId:{$orderId},orderUuid:{$orderUuid},payOrderId:{$result['TrackingNumber']}保存失败");
            return false;
        }
    }

    public function checkSign($params){
        $encryptValue = $params['EncryptValue'];

        unset($params['EncryptValue']);
        ksort($params);

        $str = '';
        foreach ($params as $k => $v){
            if(is_null($v)){
                continue;
            }

            if($k == 'IsTest'){
                $str .= $k.'='.($v ? 'true' : 'false').'&';
            }else{
                $str .= $k.'='.$v.'&';
            }
        }

        $str .= 'HashKey='.$this->apiKey;

        $sign = strtoupper(hash('sha256', strtolower($str)));

        if($encryptValue == $sign){
            return true;
        }

        return false;
    }

    /**
     * 还款结果返回
     * @param $razorpayOrderId
     * @return bool
     */
    public function orderRepaymentNotify($params)
    {
        if(!$this->checkSign($params)){
            return false;
        }

        $lockKey = "sifang:payment:callback_{$params['TrackingNumber']}";
        if(!RedisQueue::lock($lockKey, 60))
        {
            return false;
        }

        /**
         * @var FinancialPaymentOrder $paymentOrder
         */
        $paymentOrder = FinancialPaymentOrder::find()->where([
            'pay_order_id' => $params['TrackingNumber'], 'order_uuid' => $params['ShopOrderId']])->one();
        if(is_null($paymentOrder)){
            return true;
        }
        if($paymentOrder->status != FinancialPaymentOrder::STATUS_DEFAULT){
            return true;
        }

        if(2 == $params['OrderStatusId']){
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

    /**
     * @param $url
     * @param $params
     * @return mixed|\Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function postData($url,$params){
        $client = new Client([
            'base_uri'              => $this->url,
            RequestOptions::TIMEOUT => 60,
        ]);
        $response = $client->request('POST', $url, [
            RequestOptions::JSON => $params,
            RequestOptions::HTTP_ERRORS => false, //禁止http_errors 4xx 和 5xx
        ]);
        return $response;
    }

}
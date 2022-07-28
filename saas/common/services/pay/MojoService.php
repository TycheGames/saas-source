<?php
namespace common\services\pay;

use common\helpers\CommonHelper;
use common\helpers\RedisQueue;
use common\models\financial\FinancialPaymentOrder;
use common\models\order\UserLoanOrder;
use common\models\order\UserRepaymentLog;
use common\models\pay\MojoPaymentForm;
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
class MojoService extends BasePayService
{
    private $payAccountSetting;
    private $apiKey,$token,$salt;
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
        $this->apiKey = $form->mojoApiKey;
        $this->token = $form->mojoAuthToken;
        $this->salt = $form->mojoSalt;
        $this->merchantId = $this->payAccountSetting->merchant_id;

        if(YII_ENV_PROD){
            $this->url = 'https://www.instamojo.com/api/1.1/';
        }else{
            $this->url = 'https://www.instamojo.com/api/1.1/';
        }

        parent::__construct($config);
    }

    /**
     * @param $payAccountId
     * @return MojoService
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
        $order = UserLoanOrder::findOne($orderId);

        $mojoForm = new MojoPaymentForm();
        $mojoForm->amount = $form->amount;
        $mojoForm->purpose = $orderUuid;
        $mojoForm->buyer_name = $loanPerson->name;
        $mojoForm->phone = $loanPerson->phone;
        $mojoForm->email = $order->userBasicInfo->email_address;
        $mojoForm->webhook = Yii::$app->request->getHostInfo()."/notify/mojo-repayment?account_id=".$this->accountId;
        $mojoForm->allow_repeated_payments = false;

        try{
            $response = $this->postData('payment-requests/', $mojoForm->toArray());
            $content = $response->getBody()->getContents();
            yii::info(['user_id' => $userId, 'params' => $mojoForm->toArray(), 'response' => $content], 'mojo');
            $result = json_decode($content, true);

            if(!isset($result['success']) || !$result['success']){
                throw new Exception('Service is busy. Please try again later');
            }

        }catch (\Exception $exception)
        {
            yii::error([
                'request' => $mojoForm->toArray(),
                'error' => $exception->getMessage()
            ], 'mojo_error');
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
        $paymentOrder->pay_order_id = $result['payment_request']['id'];
        $paymentOrder->merchant_id = $this->merchantId;
        $paymentOrder->pay_account_id = $this->accountId;
        $paymentOrder->is_delay_repayment = intval($isDelay);
        $paymentOrder->payment_type = intval($paymentType);
        $paymentOrder->service_type = $form->serviceType;
        if($paymentOrder->save()){
            $this->setResult([
                'longurl' => $result['payment_request']['longurl'],
            ]);
            return true;
        }else{
            Yii::error("orderId:{$orderId},orderUuid:{$orderUuid},payOrderId:{$result['payment_request']['id']}保存失败");
            return false;
        }
    }

    public function checkSign($data){
        $mac_provided = $data['mac'];  // Get the MAC from the POST data
        unset($data['mac']);  // Remove the MAC key from the data.
        $ver = explode('.', phpversion());
        $major = (int) $ver[0];
        $minor = (int) $ver[1];
        if($major >= 5 && $minor >= 4){
            ksort($data, SORT_STRING | SORT_FLAG_CASE);
        } else {
            uksort($data, 'strcasecmp');
        }
        $mac_calculated = hash_hmac("sha1", implode("|", $data), $this->salt);
        if($mac_provided == $mac_calculated){
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

        $lockKey = "mojo:payment:callback_{$params['payment_request_id']}";
        if(!RedisQueue::lock($lockKey, 60))
        {
            return false;
        }

        /**
         * @var FinancialPaymentOrder $paymentOrder
         */
        $paymentOrder = FinancialPaymentOrder::find()->where([
            'pay_order_id' => $params['payment_request_id'], 'order_uuid' => $params['purpose']])->one();
        if(is_null($paymentOrder)){
            return true;
        }
        if($paymentOrder->status != FinancialPaymentOrder::STATUS_DEFAULT){
            return true;
        }

        if('Credit' == $params['status']){
            $paymentOrder->status = FinancialPaymentOrder::STATUS_SUCCESS;
            $paymentOrder->pay_payment_id = $params['payment_id'];
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
            RequestOptions::HEADERS => [
                'X-Api-Key' => $this->apiKey,
                'X-Auth-Token' => $this->token,
            ],
        ]);
        $response = $client->request('POST', $url, [
            RequestOptions::JSON => $params,
            RequestOptions::HTTP_ERRORS => false, //禁止http_errors 4xx 和 5xx
        ]);
        return $response;
    }

}
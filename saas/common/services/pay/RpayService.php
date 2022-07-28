<?php
namespace common\services\pay;

use common\helpers\CommonHelper;
use common\helpers\RedisQueue;
use common\models\financial\FinancialPaymentOrder;
use common\models\order\UserRepaymentLog;
use common\models\pay\PayAccountSetting;
use common\models\pay\RazorpayAccountForm;
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
class RpayService extends BasePayService
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
        $this->userId = $form->rpayKeyId;
        $this->apiKey = $form->rpayKeySecret;
        $this->merchantId = $this->payAccountSetting->merchant_id;

        $this->url = 'http://rapy.top/rpay-api/';

        parent::__construct($config);
    }

    /**
     * @param $payAccountId
     * @return RpayService
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

        $data = [
            'merchantId'      => $this->userId,
            'merchantOrderId' => $orderUuid,
            'amount'          => CommonHelper::CentsToUnit($amount),
            'timestamp'       => time() * 1000,
            'payType'         => 1,
            'remark'          => $orderUuid,
            'notifyUrl'       => Yii::$app->request->getHostInfo()."/notify/rpay-repayment?account_id=".$this->accountId,
        ];

        $data['sign'] = $this->getSign($data);

        try{
            $result = $this->postData('order/submit', $data);
            yii::info(['user_id' => $userId, 'params' => $data, 'response' => $result], 'rpay');

            if(!isset($result['code']) || $result['code'] != 0){
                throw new Exception('Service is busy. Please try again later');
            }
        }catch (\Exception $exception)
        {
            yii::error([
                'request' => $data,
                'error' => $exception->getMessage()
            ], 'rpay_error');
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
        $paymentOrder->merchant_id = $this->merchantId;
        $paymentOrder->pay_account_id = $this->accountId;
        $paymentOrder->is_delay_repayment = intval($isDelay);
        $paymentOrder->payment_type = intval($paymentType);
        $paymentOrder->service_type = $form->serviceType;
        if($paymentOrder->save()){
            $this->setResult([
                'orderUrl' => $result['data']['h5Url'],
            ]);
            return true;
        }else{
            Yii::error("orderId:{$orderId},orderUuid:{$orderUuid}保存失败");
            return false;
        }
    }

    public function getSign($params){
        $str = 'merchantId='.$params['merchantId'];
        $str .= '&merchantOrderId='.$params['merchantOrderId'];
        $str .= '&amount='.$params['amount'];
        $str .= '&'.$this->apiKey;
        return md5($str);
    }

    /**
     * 还款结果返回
     * @param $razorpayOrderId
     * @return bool
     */
    public function orderRepaymentNotify($params)
    {
        $lockKey = "rpay:payment:callback_{$params['merchantOrderId']}";
        if(!RedisQueue::lock($lockKey, 60))
        {
            return false;
        }

        $sign = $this->getSign($params);

        if($sign != $params['sign']){
            return false;
        }

        /**
         * @var FinancialPaymentOrder $paymentOrder
         */
        $paymentOrder = FinancialPaymentOrder::find()->where([
            'order_uuid' => $params['merchantOrderId']])->one();
        if(is_null($paymentOrder)){
            return true;
        }
        if($paymentOrder->status != FinancialPaymentOrder::STATUS_DEFAULT){
            return true;
        }

        if(1 == $params['status']){
            $paymentOrder->pay_order_id = $params['orderId'];
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
    public function postData($url, $params){
        $client = new Client([
            'base_uri'              => $this->url,
            RequestOptions::TIMEOUT => 60,
        ]);

        $response = $client->request('POST', $url, [
            RequestOptions::JSON => $params,
            RequestOptions::HTTP_ERRORS => false, //禁止http_errors 4xx 和 5xx
        ]);

        return $response->getStatusCode() == 200 ? json_decode($response->getBody()->getContents(), true) : [];
    }

}
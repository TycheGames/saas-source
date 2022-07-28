<?php
namespace common\services\pay;

use common\helpers\CommonHelper;
use common\helpers\RedisQueue;
use common\models\financial\FinancialPaymentOrder;
use common\models\order\UserRepaymentLog;
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
class JpayService extends BasePayService
{
    private $payAccountSetting;
    private $app_key,$app_secret;
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
        $this->app_key = $form->jpayAppKey;
        $this->app_secret = $form->jpayAppSecret;
        $this->merchantId = $this->payAccountSetting->merchant_id;

        if(YII_ENV_PROD){
            $this->url = 'https://api.jpayhome.com/app/open/collection/businessCollectionMoney';
        }else{
            $this->url = 'https://api.jpayhome.com/app/open/collection/businessCollectionMoney';
        }

        parent::__construct($config);
    }

    /**
     * @param $payAccountId
     * @return JpayService
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
     */
    public function getSign($data)
    {
        $str = '';
        $str .= $data['business_order_num'].'|';
        $str .= $data['amount'].'|';
        $str .= $data['productinfo'].'|';
        $str .= $data['firstname'].'|';
        $str .= $data['phone'].'|';
        $str .= $data['surl'].'|';
        $str .= $data['furl'].'|';
        $str .= $this->app_secret;

        return md5($str);
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
            'app_key' => $this->app_key,
            'business_order_num' => $orderUuid,
            'amount' => CommonHelper::CentsToUnit($amount),
            'firstname' => $loanPerson->name,
            'phone' => $loanPerson->phone,
            'productinfo' => $orderUuid,
            'surl' => (!empty($form->host) ? $form->host : Yii::$app->request->getHostInfo()) . '/h5/#/siFangRepaymentSuccess',
            'furl' => (!empty($form->host) ? $form->host : Yii::$app->request->getHostInfo()) . '/h5/#/siFangRepaymentSuccess',
        ];

        $data['sign'] = $this->getSign($data);

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
            $this->setResult($data);
            return true;
        }else{
            Yii::error("orderId:{$orderId},orderUuid:{$orderUuid}保存失败");
            return false;
        }
    }

    public function checkSign($params){
        $sign = $this->getSign($params['data']);

        if($params['data']['sign'] == strtoupper($sign)){
            return true;
        }

        return false;
    }

    /**
     * 还款结果返回
     * @param $params
     * @return bool
     */
    public function orderRepaymentNotify($params)
    {
        if(!$this->checkSign($params)){
            return false;
        }

        if(empty($params['data']['business_order_num'])){
            return false;
        }

        $lockKey = "jpay:payment:callback_{$params['data']['business_order_num']}";
        if(!RedisQueue::lock($lockKey, 60))
        {
            return false;
        }

        /**
         * @var FinancialPaymentOrder $paymentOrder
         */
        $paymentOrder = FinancialPaymentOrder::find()->where([
            'order_uuid' => $params['data']['business_order_num']])->one();
        if(is_null($paymentOrder)){
            return true;
        }
        if($paymentOrder->status != FinancialPaymentOrder::STATUS_DEFAULT){
            return true;
        }

        if('success' == $params['data']['status']){
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

}
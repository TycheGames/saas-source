<?php
namespace common\services\pay;

use common\exceptions\UserExceptionExt;
use common\helpers\CommonHelper;
use common\helpers\RedisQueue;
use common\models\cashfree\CashFreeAccount;
use common\models\cashfree\CashFreeBeneficiaryForm;
use common\models\cashfree\CashFreePaymentUrlForm;
use common\models\cashfree\CashFreeTransferForm;
use common\models\financial\FinancialPaymentOrder;
use common\models\order\FinancialLoanRecord;
use common\models\order\UserRepaymentLog;
use common\models\pay\CashfreePaymentAccountForm;
use common\models\pay\CashfreePayoutAccountForm;
use common\models\pay\LoanPayForm;
use common\models\pay\PayAccountSetting;
use common\models\pay\RazorpayAccountForm;
use common\models\user\LoanPerson;
use common\models\user\UserBankAccount;
use common\services\order\FinancialService;
use common\services\repayment\RepaymentService;
use frontend\models\loan\RepaymentApplyForm;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Yii;
use yii\db\Exception;

/**
 * Class CashFreeService
 * @package common\services\pay
 *
 * @property RazorpayAccountForm $accountForm
 * @property PayAccountSetting $payAccountSetting
 */
class CashFreePaymentService extends BasePayService
{
    private $payAccountSetting;
    private $accountId;
    private $baseUri;
    private $clientId;
    private $clientSecret;
    private $tokenCacheKey;

    //payment gateway密钥
    private $paymentBaseUri;
    private $paymentKey;
    private $paymentSecret;
    private $paymentNotifyUrl;


    public $loanPayStatus; //打款状态
    public $loanPaySuccessTime; //打款成功时间
    public $thirdOrderID; //三方支付ID

    public $financialLoanCallback; //回写到financial_loan_record的数据

    public $loanPayQueryResult; //打款状态查询结果
    public $loanPayRequestResult; //打款请求结果




    public function __construct(PayAccountSetting $payAccountSetting, $config = [])
    {
        $this->payAccountSetting = $payAccountSetting;
        if(YII_ENV_PROD)
        {
            $this->baseUri = 'https://payout-api.cashfree.com/';
            $this->paymentBaseUri = 'https://api.cashfree.com/';

        }else{
            $this->baseUri = 'https://payout-gamma.cashfree.com/';
            $this->paymentBaseUri = 'https://test.cashfree.com/';
        }

        $form = self::formModel();
        $form->load($payAccountSetting->getAccountInfo(), '');
        $this->accountId = $payAccountSetting->id;
        $this->tokenCacheKey = "cache:cash_free:payout:token:{$this->accountId}";

        $this->paymentKey = $form->key;
        $this->paymentSecret = $form->secret;
        $this->paymentNotifyUrl = $form->notifyUrl;
        parent::__construct($config);
    }




    /**
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getToken()
    {
        $token = RedisQueue::get(['key' => $this->tokenCacheKey]);
        if(!empty($token))
        {
            return $token;
        }
        $client = new Client([
            RequestOptions::TIMEOUT => 120,
        ]);

        $response = $client->request('POST',  $this->baseUri . 'payout/v1/authorize', [
            RequestOptions::HEADERS => ['X-Client-Id' => $this->clientId, 'X-Client-Secret' => $this->clientSecret]
        ]);
        $resultMeta = $response->getBody()->getContents();
        $result = json_decode($resultMeta,true);
        if(isset($result['status']) && 'SUCCESS' == $result['status']
            && isset($result['subCode']) && '200' == $result['subCode'])
        {
            $token = $result['data']['token'];
            $expiry = max(0, time() - $result['data']['expiry'] - 120 );
            RedisQueue::set(['expire'=> $expiry, 'key'=> $this->tokenCacheKey, 'value'=> $token]);
            yii::info($resultMeta, 'cash_free_payout_token');
            return $token;
        }else{
            yii::error($resultMeta, 'cash_free_payout_token');
            throw new UserExceptionExt("pay_account_id:{$this->accountId},token获取失败,具体原因见日志服务,category:cash_free_payout_token");
        }

    }


    /**
     * @param $payAccountId
     * @return CashFreePaymentService
     */
    public static function getInstanceByPayAccountId($payAccountId)
    {
        $payAccountSetting = PayAccountSetting::findOne($payAccountId);
        return new self($payAccountSetting);
    }


    /**
     * @return CashfreePaymentAccountForm|\yii\base\Model
     */
    public static function formModel()
    {
        return new CashfreePaymentAccountForm();
    }


    /**
     * 获取受益人id
     * @param $userID
     * @param $beneAccNo
     * @param $beneIFSC
     * @return string
     * @throws UserExceptionExt
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getBeneficiaryId($userID, $beneAccNo, $beneIFSC)
    {
        $model = CashFreeAccount::findOne([
            'user_id' => $userID,
            'bank_account' => $beneAccNo,
            'ifsc' => $beneIFSC,
            'pay_account_id' => $this->accountId,
            'status' => CashFreeAccount::STATUS_ENABLE
        ]);
        if(!is_null($model))
        {
            return $model->bene_id;
        }

        $loanPerson = LoanPerson::findOne($userID);
        $form = new CashFreeBeneficiaryForm();
        $form->beneId = uniqid("acc_{$userID}_");
        $form->bankAccount = $beneAccNo;
        $form->ifsc = $beneIFSC;
        $form->phone = $loanPerson->phone;
        $form->name = $loanPerson->name;
        $form->email = $loanPerson->userBasicInfo->email_address;
        $form->address1 = $loanPerson->userBasicInfo->aadhaar_address1;

        $response = $this->postData("{$this->baseUri}payout/v1/addBeneficiary", $form->toArray());
        $content = $response->getBody()->getContents();
        $result = json_decode($content, true);
        if(! (isset($result['status']) && "SUCCESS" == $result['status']
        && isset($result['subCode']) && '200' == $result['subCode']))
        {
            yii::error($content, 'cash_free_payout_add_beneficiary');
            throw new UserExceptionExt("user_id:$userID, 受益人创建失败");
        }

        $model = new CashFreeAccount();
        $model->user_id = $userID;
        $model->bene_id = $form->beneId;
        $model->bank_account = $form->bankAccount;
        $model->ifsc = $form->ifsc;
        $model->pay_account_id = $this->accountId;
        $model->status = CashFreeAccount::STATUS_ENABLE;
        if(!$model->save())
        {
            throw new UserExceptionExt("user_id:$userID, 受益人保存失败");
        }
        return $model->bene_id;

    }




    /**
     * @param $url
     * @param $params
     * @return mixed|\Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function postData($url,$params){
        $token = $this->getToken();
        $client = new Client([
            RequestOptions::TIMEOUT => 120,
        ]);

        $response = $client->request('POST', $url, [
            RequestOptions::JSON => $params,
            RequestOptions::HEADERS => ['Authorization' => 'Bearer ' .$token]
        ]);
        return $response;
    }


    /**
     * @param $url
     * @return mixed|\Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getData($url){
        $token = $this->getToken();
        $client = new Client([
            RequestOptions::TIMEOUT => 120,
        ]);

        $response = $client->request('GET', $url, [
            RequestOptions::HEADERS => ['Authorization' => 'Bearer ' .$token]
        ]);
        return $response;
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
     * @param RepaymentApplyForm $form
     * @return bool
     * @throws UserExceptionExt
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function repaymentApply(RepaymentApplyForm $form)
    {
        $userId = $form->userID;
        $orderId = $form->orderId;
        $amount = $form->amount;

        $loanPerson = LoanPerson::findOne($userId);
        $orderUuid =  uniqid("order_cf_{$orderId}_");

        //如果host为空，则取回调地址的域名
        if(empty($form->host))
        {
            $notifyUrl = parse_url($this->paymentNotifyUrl);
            $form->host =  $notifyUrl['scheme'] . '://' . $notifyUrl['host'];
            if(isset($notifyUrl['port']) && 80 != $notifyUrl['port'])
            {
                $form->host .= ':' . $notifyUrl['port'];
            }
        }

        $cashFreePaymentUrlForm = new CashFreePaymentUrlForm();
        $cashFreePaymentUrlForm->orderId = $orderUuid;
        $cashFreePaymentUrlForm->orderAmount = $amount;
        $cashFreePaymentUrlForm->customerEmail = $form->customerEmail;
        $cashFreePaymentUrlForm->customerName = $loanPerson->name;
        $cashFreePaymentUrlForm->customerPhone = $form->customerPhone;
        $cashFreePaymentUrlForm->returnUrl = "{$form->host}/notify/cash-free-return?id={$this->accountId}";
        $cashFreePaymentUrlForm->notifyUrl = $this->paymentNotifyUrl;

        $contents = $this->generatePaymentUrl($cashFreePaymentUrlForm);
        $result = json_decode($contents, true);
        if(! (isset($result['status']) && 'OK' == $result['status'] && isset($result['paymentLink'])))
        {
            yii::error([
                'user_id' => $userId,
                'contents' => $contents,
                'request' => $cashFreePaymentUrlForm->toArray()
            ], 'cash_free_apply_repayment');
            throw new UserExceptionExt("The request failed. Please try again");
        }

        yii::info([
            'user_id' => $userId,
            'contents' => $contents,
            'request' => $cashFreePaymentUrlForm->toArray()
        ], 'cash_free_apply_repayment');

        $paymentOrder = new FinancialPaymentOrder();
        $paymentOrder->user_id = $userId;
        $paymentOrder->order_id = $orderId;
        $paymentOrder->amount = CommonHelper::UnitToCents($amount);
        $paymentOrder->status = FinancialPaymentOrder::STATUS_DEFAULT;
        $paymentOrder->order_uuid = $orderUuid;
        $paymentOrder->pay_account_id = $this->accountId;
        $paymentOrder->service_type = FinancialPaymentOrder::SERVICE_TYPE_CASHFREE;
        $paymentOrder->payment_type = intval($form->paymentType);

        if($paymentOrder->save()){
            $this->setResult([
                'amount' => $amount,
                'url' => $result['paymentLink'],
            ]);
            return true;
        }else{
            Yii::error("orderId:{$orderId},orderUuid:{$orderUuid},payOrderId:{$orderUuid}保存失败");
            return false;
        }
    }


    /**
     * @param CashFreePaymentUrlForm $form
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function generatePaymentUrl(CashFreePaymentUrlForm $form)
    {
        $response = $this->paymentPostData($form->toArray(), 'api/v1/order/create');
        return $response->getBody()->getContents();
    }


    /**
     * @param $params
     * @param $url
     * @return mixed|\Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function paymentPostData($params, $url)
    {
        $client = new Client();
        $params['appId'] = $this->paymentKey;
        $params['secretKey'] = $this->paymentSecret;
        $response = $client->request('POST',  $this->paymentBaseUri . $url, [
            RequestOptions::FORM_PARAMS => $params,
            RequestOptions::TIMEOUT => 120,
        ]);
        return $response;
    }

    /**
     * 支付还款回调验签
     * @param array $postData
     * @return bool
     */
    public function paymentSignValidation(array $postData) : bool
    {
        $secretKey = $this->paymentSecret;
        $orderId = $postData["orderId"];
        $orderAmount = $postData["orderAmount"];
        $referenceId = $postData["referenceId"];
        $txStatus = $postData["txStatus"];
        $paymentMode = $postData["paymentMode"];
        $txMsg = $postData["txMsg"];
        $txTime = $postData["txTime"];
        $data = $orderId.$orderAmount.$referenceId.$txStatus.$paymentMode.$txMsg.$txTime;
        $hash_hmac = hash_hmac('sha256', $data, $secretKey, true) ;
        $computedSignature = base64_encode($hash_hmac);
        if ($postData['signature'] == $computedSignature) {
            return true;
        } else {
            return false;
        }
    }


    public function paymentNotify(array $postData) : bool
    {
        if(!$this->paymentSignValidation($postData))
        {
            $this->setError("Signature error");
            return false;
        }
        $orderUuid = $postData['orderId'];
        $paymentID = $postData['referenceId'];
        $txStatus = $postData['txStatus'];

        $paymentOrder = FinancialPaymentOrder::findOne([
            'order_uuid' => $orderUuid,
            'service_type' => FinancialPaymentOrder::SERVICE_TYPE_CASHFREE,
        ]);
        if(is_null($paymentOrder))
        {
            $this->setError("order not find");
            return false;
        }

        if('SUCCESS' == $txStatus)
        {
            if($paymentOrder->status == FinancialPaymentOrder::STATUS_SUCCESS){
                return true;
            }
            $paymentOrder->status = FinancialPaymentOrder::STATUS_SUCCESS;
            $paymentOrder->pay_payment_id = $paymentID;
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

        return false;

    }
}
<?php
namespace common\services\pay;

use common\helpers\CommonHelper;
use common\helpers\RedisQueue;
use common\models\order\FinancialLoanRecord;
use common\models\order\UserRepaymentLog;
use common\models\pay\LoanPayForm;
use common\models\pay\PayoutAccountInfo;
use common\models\pay\RazorpayAccountForm;
use common\models\pay\RazorpayPayoutAccountForm;
use common\models\razorpay\RazorpayAccount;
use common\models\razorpay\RazorpayContact;
use common\models\order\UserLoanOrder;
use common\services\loan\LoanService;
use common\services\message\DingDingService;
use common\services\message\WeWorkService;
use common\services\order\FinancialService;
use common\services\order\OrderService;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Razorpay\Api\Api;
use Yii;

/**
 * Class RazorpayPayoutService
 * @package common\services\pay
 *
 * @property RazorpayAccountForm $accountForm
 * @property PayoutAccountInfo $payAccountSetting
 */
class RazorpayPayoutService extends BasePayoutService
{
    private $payoutKey,$payoutSecret;
    private $webhooksSecret;
    private $baseUri;
    private $accountNumber;


    public $loanPayStatus; //打款状态
    public $loanPaySuccessTime; //打款成功时间
    public $thirdOrderID; //三方支付ID

    public $financialLoanCallback; //回写到financial_loan_record的数据


    public $loanPayQueryResult; //打款状态查询结果
    public $loanPayRequestResult; //打款请求结果


    public function __construct(PayoutAccountInfo $payAccountSetting, $config = [])
    {
        /** @var RazorpayPayoutAccountForm $form */
        $form = self::formPayoutModel();
        $form->load($payAccountSetting->getAccountInfo(), '');
        $this->accountId = $payAccountSetting->id;
        $this->payoutKey = $form->payoutKeyId;
        $this->payoutSecret = $form->payoutKeySecret;
        $this->webhooksSecret = $form->webhooksSecret;
        $this->baseUri = 'https://api.razorpay.com/v1/';
        $this->accountNumber = $form->accountNumber;
        parent::__construct($payAccountSetting, $config);
    }

    /**
     * @param $payAccountId
     * @return RazorpayPayoutService
     */
    public static function getInstanceByPayAccountId($payAccountId)
    {
        $payAccountSetting = PayoutAccountInfo::findOne($payAccountId);
        return new self($payAccountSetting);
    }


    /**
     * @return RazorpayPayoutAccountForm
     */
    public static function formPayoutModel()
    {
        return new RazorpayPayoutAccountForm();
    }




    /**
     * 回调签名认证
     * @param $sign
     * @param $params
     * @return bool
     */
    public function verifyWebhookSignature($sign, $params)
    {
        $api = new Api($this->payoutKey, $this->payoutSecret);
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

        /** @var FinancialLoanRecord $withdrawInfo */
        $withdrawInfo = FinancialLoanRecord::find()
            ->where([
                'order_id' => $order_uuid,
                'payout_account_id' => $this->accountId])->one();
        if(empty($withdrawInfo)){
            Yii::warning("no withdraw info for order_uuid:".$order_uuid, 'razorpay_payout');
            return true;
        }

        $lockKey = "razorpay:paoout:callback_{$order_uuid}";
        if(!RedisQueue::lock($lockKey, 60))
        {
            return false;
        }
        if(!in_array($withdrawInfo->status, [FinancialLoanRecord::UMP_PAY_SUCCESS, FinancialLoanRecord::UMP_PAYOUT_REVERSED])){
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
        }else{
            //如果打款订单表的状态已为成功，回调信息为失败，则说明razorpay先告诉我们成功后告诉我们失败，直接将订单表只为已还款
            if('payout.reversed' == $params['event'] && $withdrawInfo->trade_no == $params['payload']['payout']['entity']['id'])
            {
                $withdrawInfo->status = FinancialLoanRecord::UMP_PAYOUT_REVERSED;
                $withdrawInfo->notify_result = json_encode($params);
                $withdrawInfo->save();
                $order = UserLoanOrder::findOne($withdrawInfo->business_id);
                $orderService = new OrderService($order);
                $orderService->repayment(0, UserRepaymentLog::TYPE_ACTIVE, true);
                $service = new WeWorkService();
                $message = sprintf('[order_id:%s] : 打款异常',
                    $withdrawInfo->business_id);
                $service->send($message);

            }

        }

        return true;

    }


    /**
     * 打款状态查询
     * @param $txnId
     * @return bool
     */
    public function loanQueryHandle($txnId, $payID)
    {
        $url = "{$this->baseUri}payouts/{$payID}";
        $content = $this->getData($url);
        Yii::info([
            'txnId' => $txnId,
            'payID' => $payID,
            'response' => $content
        ], 'razorpay_payout_query');
        $response = json_decode($content, true);
        if(isset($response['status']) && 'processed' == $response['status'])
        {
            $this->financialLoanCallback = [
                'utr' => $response['utr'] ?? '',
                'notify_result' => $content,
                'success_time' => time()
            ];
            $this->loanPayStatus = FinancialService::LOAN_STATUS_SUCCESS;
        }else{
            $this->financialLoanCallback = [
                'notify_result' => $content,
            ];
            $this->loanPayStatus = FinancialService::LOAN_STATUS_PENDING;
        }
        return true;
    }

    /**
     * 获取结算信息
     * @param $params
     * @return mixed|\Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getSettlements($params){
        $url = "{$this->baseUri}settlements/?".http_build_query($params);
        return $this->getData($url);
    }


    /**
     * 统一打款方法
     * @param LoanPayForm $form
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function loanPayHandle(LoanPayForm $form)
    {

        $contact = $this->createContact($form);
        if(empty($contact)){
            return false;
        }
        $account = $this->createAccounts($contact->contact_id, $form->beneIFSC, $form->beneName, $form->beneAccNo);
        if(empty($account)){
            return false;
        }
        $params = [
            'account_number'  => $this->accountNumber,
            'fund_account_id' => $account->fund_account_id,
            'amount'          => $form->amount,
            'reference_id'    => $form->txnId,
            'currency'        => 'INR',
            'mode'            => 'IMPS',
            'purpose'         => 'payout',
            'notes'           => ['source' => 'saas', 'id' => $this->accountId]
        ];

        $response = $this->postData($this->baseUri.'payouts', $params, false);
        $content = $response->getBody()->getContents();
        $result = json_decode($content, true);
        if(200 != $response->getStatusCode()){
            $this->financialLoanCallback = [
                'result' => $content,
            ];
            //余额不足特殊处理
            if(isset($result['error']['description'])
                && 'Your account does not have enough balance to carry out the payout operation.' == $result['error']['description'])
            {
                $sendMsg = YII_ENV .",支付账号:{$this->payAccountSetting->name},账户余额不足，请尽快充值";
                $ddService = new DingDingService();
                $ddService->sendToGroup($sendMsg, 'business');
            }

            if(isset($result['error']['description'])
                && 'This operation is not allowed. Please contact Razorpay support for details.' == $result['error']['description'])
            {
                $sendMsg = YII_ENV .",支付账号:{$this->payAccountSetting->name},账户不允许打款";
                $ddService = new DingDingService();
                $ddService->sendToGroup($sendMsg, 'business');
            }
            $this->loanPayStatus =  FinancialService::LOAN_STATUS_FAILURE;
        }else{
            $this->financialLoanCallback = [
                'trade_no' => $result['id'],
                'result' => $content,
            ];
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
    public function createContact(LoanPayForm $form){
        $contact = RazorpayContact::findOne([
            'user_id' => $form->userID,
            'pay_account_id' => $this->accountId
        ]);
        if(!empty($contact)){
            return $contact;
        }
        $params['name'] = $form->beneName;
        $params['type'] = 'customer';
        $response = $this->postData($this->baseUri.'contacts', $params);
        $data  = json_decode($response->getBody()->getContents(), true);
        Yii::info(['user_id' => $form->userID,'http_code' => $response->getStatusCode(),'params' => $params, 'response' => $data], 'razorpay_payout');
        if(!in_array($response->getStatusCode(), [200, 201]))
        {
            return [];
        }
        $query = new RazorpayContact();
        $query->pay_account_id = $this->accountId;
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



    public function createAccounts($contact_id, $ifsc, $name, $account){
        $fund_account = RazorpayAccount::findOne([
            'contact_id' => $contact_id,
            'ifsc' => $ifsc,
            'name' => $name,
            'account' => $account,
            'pay_account_id' => $this->accountId
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
        $query->contact_id      = $data['contact_id'];
        $query->pay_account_id  = $this->accountId;
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
     * @param string $url
     * @param array $params
     * @param bool $httpErrors 设置成 false 来禁用HTTP协议抛出的异常(如 4xx 和 5xx 响应)，默认情况下HTPP协议出错时会抛出异常。
     * @return mixed|\Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function postData($url, $params, $httpErrors = true){
        $client = new Client([
            RequestOptions::TIMEOUT => 120,
            'http_errors' => $httpErrors
        ]);

        $response = $client->request('POST', $url, [
            RequestOptions::JSON => $params,
            RequestOptions::AUTH => [$this->payoutKey, $this->payoutSecret]
        ]);
        return $response;
    }


    /**
     * @param string $url
     * @param bool $httpErrors 设置成 false 来禁用HTTP协议抛出的异常(如 4xx 和 5xx 响应)，默认情况下HTPP协议出错时会抛出异常。
     * @return mixed|\Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getData($url, $httpErrors = true){
        $client = new Client([
            RequestOptions::TIMEOUT => 120,
            'http_errors' => $httpErrors
        ]);

        $response = $client->request('GET', $url, [
            RequestOptions::AUTH => [$this->payoutKey, $this->payoutSecret]
        ]);
        return $response->getBody()->getContents();
    }


    /**
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getBalance()
    {
        $url = "{$this->baseUri}transactions?account_number={$this->accountNumber}&count=1";
        $content = $this->getData($url);
        $response = json_decode($content, true);
        if(isset($response['items'][0]['balance']))
        {
            return CommonHelper::CentsToUnit($response['items'][0]['balance']);
        }
        return 'null';
    }

}
<?php
namespace common\services\pay;

use common\exceptions\UserExceptionExt;
use common\helpers\CommonHelper;
use common\helpers\RedisQueue;
use common\models\cashfree\CashFreeAccount;
use common\models\cashfree\CashFreeBeneficiaryForm;
use common\models\cashfree\CashFreeTransferForm;
use common\models\pay\CashfreePayoutAccountForm;
use common\models\pay\LoanPayForm;
use common\models\pay\PayAccountSetting;
use common\models\pay\PayoutAccountInfo;
use common\models\pay\RazorpayAccountForm;
use common\models\user\LoanPerson;
use common\services\order\FinancialService;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Yii;

/**
 * Class CashFreeService
 * @package common\services\pay
 *
 * @property RazorpayAccountForm $accountForm
 * @property PayAccountSetting $payAccountSetting
 */
class CashFreePayoutService extends BasePayoutService
{
    private $baseUri;
    private $clientId;
    private $clientSecret;
    private $tokenCacheKey;


    public $loanPayStatus; //打款状态
    public $loanPaySuccessTime; //打款成功时间
    public $thirdOrderID; //三方支付ID

    public $financialLoanCallback; //回写到financial_loan_record的数据

    public $loanPayQueryResult; //打款状态查询结果
    public $loanPayRequestResult; //打款请求结果




    public function __construct(PayoutAccountInfo $payAccountSetting, $config = [])
    {
        if(YII_ENV_PROD)
        {
            $this->baseUri = 'https://payout-api.cashfree.com/';
        }else{
            $this->baseUri = 'https://payout-gamma.cashfree.com/';
        }
        /** @var CashfreePayoutAccountForm $form */
        $form = self::formPayoutModel();
        $form->load($payAccountSetting->getAccountInfo(), '');
        $this->accountId = $payAccountSetting->id;
        $this->clientId = $form->cashFreeKey;
        $this->clientSecret = $form->cashFreeSecret;
        $this->tokenCacheKey = "cache:cash_free:payout:token:{$this->accountId}";
        parent::__construct($payAccountSetting, $config);
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
     * @return CashFreePayoutService
     */
    public static function getInstanceByPayAccountId($payAccountId)
    {
        $payAccountSetting = PayoutAccountInfo::findOne($payAccountId);
        return new self($payAccountSetting);
    }



    /**
     * @return CashfreePayoutAccountForm
     */
    public static function formPayoutModel()
    {
        return new CashfreePayoutAccountForm();
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

        $response = $this->getData("{$this->baseUri}payout/v1/getBeneId?bankAccount={$beneAccNo}&ifsc={$beneIFSC}");
        $content = $response->getBody()->getContents();
        yii::info($content, 'cashfree_payout_get_bene_id');
        $result = json_decode($content, true);
        if(isset($result['status']) && 'SUCCESS' == $result['status'] && isset($result['subCode']) && '200' == $result['subCode'])
        {
            $beneId = $result['data']['beneId'];
        }else{
            $loanPerson = LoanPerson::findOne($userID);
            $beneId = uniqid("acc_{$userID}_");
            $form = new CashFreeBeneficiaryForm();
            $form->beneId = $beneId;
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
        }

        $model = new CashFreeAccount();
        $model->user_id = $userID;
        $model->bene_id = $beneId;
        $model->bank_account = $beneAccNo;
        $model->ifsc = $beneIFSC;
        $model->pay_account_id = $this->accountId;
        $model->status = CashFreeAccount::STATUS_ENABLE;
        if(!$model->save())
        {
            throw new UserExceptionExt("user_id:$userID, 受益人保存失败");
        }
        return $model->bene_id;

    }




    /**
     * 统一打款方法
     * @param LoanPayForm $loanPayForm
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function loanPayHandle(LoanPayForm $loanPayForm)
    {

        $beneID = $this->getBeneficiaryId($loanPayForm->userID, $loanPayForm->beneAccNo, $loanPayForm->beneIFSC);
        $from = new CashFreeTransferForm();
        $from->beneId = $beneID;
        $from->amount = CommonHelper::CentsToUnit($loanPayForm->amount);
        $from->transferId = $loanPayForm->txnId;
        $response = $this->postData($this->baseUri.'payout/v1/requestTransfer', $from->toArray());
        $content = $response->getBody()->getContents();
        $result = json_decode($content, true);
        if(isset($result['status']) && 'SUCCESS' == $result['status']
            && isset($result['subCode']) && '200' == $result['subCode'])
        {
            $this->financialLoanCallback = [
                'trade_no' => $result['data']['referenceId'],
                'utr' => $result['data']['utr'],
                'result' => $content,
                'success_time' => time()
            ];
            $this->loanPayStatus = FinancialService::LOAN_STATUS_SUCCESS;
        }elseif(isset($result['status']) && 'PENDING' == $result['status']
            && isset($result['subCode']) && '201' == $result['subCode'])
        {
            $this->financialLoanCallback = [
                'trade_no' => $result['data']['referenceId'],
                'utr' => $result['data']['utr'],
                'result' => $content,
            ];
            $this->loanPayStatus = FinancialService::LOAN_STATUS_PENDING;
        }else{
            $this->financialLoanCallback = [
                'result' => $content,
            ];
            $this->loanPayStatus = FinancialService::LOAN_STATUS_FAILURE;
        }

        return true;
    }




    /**
     * 打款状态查询
     * @param $txnId
     * @return bool
     */
    public function loanQueryHandle($txnId)
    {
        $url = "{$this->baseUri}payout/v1/getTransferStatus?transferId={$txnId}";
        $response = $this->getData($url);
        $content = $response->getBody()->getContents();
        $result = json_decode($content, true);
        yii::info($content, 'cashfree_loan_query');
        if(isset($result['data']['transfer']['status']) && 'SUCCESS' == $result['data']['transfer']['status'])
        {
            $this->financialLoanCallback = [
                'utr' => $result['data']['transfer']['utr'],
                'notify_result' => $content,
                'success_time' => !empty($result['data']['transfer']['processedOn']) ? strtotime($result['data']['transfer']['processedOn']) : time()
            ];
            $this->loanPayStatus = FinancialService::LOAN_STATUS_SUCCESS;
        }elseif (isset($result['data']['transfer']['status']) && in_array($result['data']['transfer']['status'], ['FAILED', 'REVERSED'])){
            $this->financialLoanCallback = [
                'notify_result' => $content,
            ];
            $this->loanPayStatus = FinancialService::LOAN_STATUS_FAILURE;
        }
        else{
            $this->financialLoanCallback = [
                'notify_result' => $content,
            ];
            $this->loanPayStatus = FinancialService::LOAN_STATUS_PENDING;
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



}
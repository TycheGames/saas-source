<?php
namespace common\services\pay;

use common\helpers\CommonHelper;
use common\helpers\RedisQueue;
use common\models\order\FinancialLoanRecord;
use common\models\pay\LoanPayForm;
use common\models\pay\MpurseLoanPayForm;
use common\models\pay\MpursePayoutAccountForm;
use common\models\pay\PayAccountSetting;
use common\models\pay\PayoutAccountInfo;
use common\models\pay\RazorpayAccountForm;
use common\models\user\LoanPerson;
use common\models\user\UserBankAccount;
use common\services\order\FinancialService;
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
class MpursePayoutService extends BasePayoutService
{
    private $partnerID,$key;
    private $url;

    public $loanPayStatus; //打款状态
    public $loanPaySuccessTime; //打款成功时间
    public $thirdOrderID; //三方支付ID

    public $financialLoanCallback; //回写到financial_loan_record的数据


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

    const LOAN_STATUS_SUCCESS = 'TRANSACTION_SUCCESS'; //打款成功
    const LOAN_STATUS_FAILURE = 'TRANSACTION_FAILURE'; //打款失败
    const LOAN_STATUS_PENDING = 'PENDING'; //打款处理中


    public function __construct(PayoutAccountInfo $payAccountSetting, $config = [])
    {
        /** @var MpursePayoutAccountForm $form */
        $form = self::formPayoutModel();
        $form->load($payAccountSetting->getAccountInfo(), '');
        $this->accountId = $payAccountSetting->id;
        $this->partnerID = $form->mpursePartnerId;
        $this->key = $this->str2bin($form->mpurseKey);
        if(YII_ENV_PROD){
            $this->url = 'https://api.mpursewallet.com/api/v2/gateway';
        }else{
            $this->url = 'https://stg.mpursewallet.com/api/v2/gateway';
        }

        parent::__construct($payAccountSetting, $config);
    }


    /**
     * @param $payAccountId
     * @return MpursePayoutService
     */
    public static function getInstanceByPayAccountId($payAccountId)
    {
        $payAccountSetting = PayoutAccountInfo::findOne($payAccountId);
        return new self($payAccountSetting);
    }



    /**
     * @return MpursePayoutAccountForm
     */
    public static function formPayoutModel()
    {
        return new MpursePayoutAccountForm();
    }

    /**
     * @param MpurseLoanPayForm $mpForm
     * @return false|string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function loanPay(MpurseLoanPayForm $mpForm)
    {
        $url = "{$this->url}/loanPay/{$this->partnerID}";

        $data = json_encode($mpForm->toArray(), JSON_UNESCAPED_UNICODE);
        $params =  $this->encryptParams($data);
        $response = $this->postData($url, $params);
        $result = $this->decryptData($response->getBody()->getContents());
        yii::info($result, 'mpurse');
        return $result;
    }


    /**
     * @param LoanPayForm $form
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function loanPayHandle(LoanPayForm $form)
    {
        $mpForm = new MpurseLoanPayForm();
        $mpForm->amount = CommonHelper::CentsToUnit($form->amount);
        $mpForm->beneName = $form->beneName;
        $mpForm->beneAccNo = $form->beneAccNo;
        $mpForm->bankName = $form->bankName;
        $mpForm->beneIFSC = $form->beneIFSC;
        $mpForm->txnId = $form->txnId;
        $mpForm->remark = $form->remark;
        $mpForm->beneMobile = $form->beneMobile;
        $content = $this->loanPay($mpForm);
        $result = json_decode($content, true);
        if(!isset($result['status'])){
            return false;
        }
        if(self::ORDER_REPEAT == $result['status'])
        {
            $this->loanPayStatus = FinancialService::LOAN_STATUS_PENDING;
            return true;

        }
        if(self::SUCCESS_STATUS_CODE != $result['status']) {
            return false;
        }

        switch ($result['retBizParams']['status']){
            case self::LOAN_STATUS_PENDING:
                $this->financialLoanCallback = [
                    'trade_no' => $result['retBizParams']['mpRefId'],
                    'result' => $content,
                ];
                $this->loanPayStatus = FinancialService::LOAN_STATUS_PENDING;
                break;
            case self::LOAN_STATUS_FAILURE:
                $this->financialLoanCallback = [
                    'result' => $content,
                ];
                $this->loanPayStatus =  FinancialService::LOAN_STATUS_FAILURE;
                break;
            case self::LOAN_STATUS_SUCCESS:
                $this->financialLoanCallback = [
                    'trade_no' => $result['retBizParams']['mpRefId'],
                    'success_time' => time(),
                    'result' => $content,
                ];
                $this->loanPayStatus = FinancialService::LOAN_STATUS_SUCCESS;
                break;
            default:
                return false;

        }

        return true;
    }



    /**
     * @param string $txnId
     * @return false|string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function loanQuery($txnId)
    {
        $url = "{$this->url}/loanQueryTxnById/{$this->partnerID}";
        $data = json_encode(['txnId' => $txnId], JSON_UNESCAPED_UNICODE);
        $params =  $this->encryptParams($data);
        $response = $this->postData($url, $params);
        $result =  $this->decryptData($response->getBody()->getContents());
        Yii::info($result, 'mpurse');
        return $result;
    }


    /**
     * @param $txnId
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function loanQueryHandle($txnId)
    {
        $content = $this->loanQuery($txnId);
        $result = json_decode($content, true);
        //所以预期之外的情况都认为是进行中
        if(!isset($result['status'])){
            return false;
        }
        if(self::SUCCESS_STATUS_CODE != $result['status']) {
            return false;
        }
        $retBizParams = $result['retBizParams'];
        switch ($retBizParams['status'])
        {
            case self::LOAN_STATUS_PENDING:
                $this->financialLoanCallback = [
                    'notify_result' => $content,
                ];
                $this->loanPayStatus = FinancialService::LOAN_STATUS_PENDING;
                break;
            case self::LOAN_STATUS_FAILURE:
                $this->financialLoanCallback = [
                    'notify_result' => $content,
                ];
                $this->loanPayStatus =  FinancialService::LOAN_STATUS_FAILURE;
                break;
            case self::LOAN_STATUS_SUCCESS:
                $this->financialLoanCallback = [
                    'trade_no' => $retBizParams['mpRefId'],
                    'notify_result' => $content,
                    'success_time' => time()
                ];
                $this->loanPayStatus = FinancialService::LOAN_STATUS_SUCCESS;
                break;
            default:
                return false;

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
     * 打款回调方法
     * @param $params
     * @return bool
     */
    public function payoutNotify($params)
    {
        $params = $metaData = $this->decryptData($params);
        $params = json_decode($params, true);

        Yii::info($params, 'MpursePayout');

        $order_uuid = $params['txnId'] ?? '';
        if(empty($order_uuid))
        {
            Yii::warning("order_uuid is null", 'mpurse_payout');
            return false;
        }

        $lockKey = "mpurse:payout:callback_{$order_uuid}";
        if(!RedisQueue::lock($lockKey, 60))
        {
            return false;
        }

        $withdrawInfo = FinancialLoanRecord::findOne(['order_id' => $order_uuid]);
        if(empty($withdrawInfo)){
            Yii::warning("no withdraw info for order_uuid:".$order_uuid, 'mpurse_payout');
            return false;
        }

        if($withdrawInfo->status != FinancialLoanRecord::UMP_PAY_SUCCESS){
            $service = new FinancialService();
            if (self::LOAN_STATUS_SUCCESS == $params['status'])
            {
                $callback = [
                    'trade_no' => $params['mpQueryId'],
                    'success_time' => time(),
                    'notify_result' => $metaData
                ];
                $service->loanSuccessHandle($withdrawInfo, $callback);
            } elseif(self::LOAN_STATUS_FAILURE == $params['status']) {
                $callback = [
                    'notify_result' => $metaData
                ];
                $service->loanFailureHandle($withdrawInfo, $callback);

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
            RequestOptions::TIMEOUT => 120,
        ]);        $response = $client->request('POST', $url, [
            RequestOptions::HEADERS => [
                'Content-Type' => 'text/plain'
            ],
            RequestOptions::BODY => $params,
        ]);
        return $response;
    }


}
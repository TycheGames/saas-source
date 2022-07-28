<?php
namespace common\services\pay;

use common\exceptions\UserExceptionExt;
use common\helpers\CommonHelper;
use common\models\order\FinancialLoanRecord;
use common\models\pay\LoanPayForm;
use common\models\pay\PayAccountSetting;
use common\models\pay\PayoutAccountInfo;
use common\models\pay\PaytmPayoutAccountForm;
use common\models\pay\RazorpayAccountForm;
use common\models\paytm\PayTMTransferForm;
use common\models\user\LoanPerson;
use common\models\user\UserBankAccount;
use common\services\order\FinancialService;
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
class PaytmPayoutService extends BasePayoutService
{
    private $baseUri;
    private $paymentUri;
    private $merchantKey;
    private $merchantGuid;
    private $salesWalletGuid;
    private $payMerchantID;



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
            $this->baseUri = 'https://dashboard.paytm.com/';
            $this->paymentUri = 'https://securegw.paytm.in/';

        }else{
            $this->baseUri = 'https://staging-dashboard.paytm.com/';
            $this->paymentUri = 'https://securegw-stage.paytm.in/';
        }
        /** @var PaytmPayoutAccountForm $form */
        $form = self::formPayoutModel();
        $form->load($payAccountSetting->getAccountInfo(), '');
        $this->accountId = $payAccountSetting->id;
        $this->payMerchantID = $form->payTmMerchantID;
        $this->merchantKey = $form->payTmMerchantKey;
        $this->merchantGuid = $form->payTmMerchantGuid;

        parent::__construct($payAccountSetting, $config);
    }





    /**
     * @param $payAccountId
     * @return PaytmPayoutService
     */
    public static function getInstanceByPayAccountId($payAccountId)
    {
        $payAccountSetting = PayoutAccountInfo::findOne($payAccountId);
        return new self($payAccountSetting);
    }



    /**
     * @return PaytmPayoutAccountForm
     */
    public static function formPayoutModel()
    {
        return new PaytmPayoutAccountForm();
    }



    /**
     * 统一打款方法
     * @param LoanPayForm $loanPayForm
     * @return bool
     * @throws UserExceptionExt
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function loanPayHandle(LoanPayForm $loanPayForm)
    {

        $from = new PayTMTransferForm();
        $from->orderId = $loanPayForm->txnId;
        $from->subwalletGuid = $this->merchantGuid;
        $from->purpose = 'OTHERS';
        $from->date = date('Y-m-d');
        $from->beneficiaryAccount = $loanPayForm->beneAccNo;
        $from->beneficiaryIFSC = $loanPayForm->beneIFSC;
        $from->amount = CommonHelper::CentsToUnit($loanPayForm->amount);

        $response = $this->postData($this->baseUri.'bpay/api/v1/disburse/order/bank', $from->toArray());
        $content = $response->getBody()->getContents();
        $result = json_decode($content, true);
        if(isset($result['status']) && 'SUCCESS' == $result['status']
            && isset($result['statusCode']) && 'DE_001' == $result['subCode'])
        {
            $this->financialLoanCallback = [
//                'trade_no' => $result['data']['referenceId'],
//                'utr' => $result['data']['utr'],
                'result' => $content,
                'success_time' => time()
            ];
            $this->loanPayStatus = FinancialService::LOAN_STATUS_SUCCESS;
        }elseif(isset($result['status']) && 'ACCEPTED' == $result['status'])
        {
            $this->financialLoanCallback = [
//                'trade_no' => $result['data']['referenceId'],
//                'utr' => $result['data']['utr'],
                'result' => $content,
            ];
            $this->loanPayStatus = FinancialService::LOAN_STATUS_PENDING;
        }elseif(isset($result['status']) && 'FAILURE' == $result['status']){
            $this->financialLoanCallback = [
                'result' => $content,
            ];
            $this->loanPayStatus = FinancialService::LOAN_STATUS_FAILURE;
        }else{
            throw new UserExceptionExt("paytm打款，order_uuid:{$loanPayForm->txnId}，未知的返回");
        }

        return true;
    }




    /**
     * 打款状态查询
     * @param $txnId
     * @return bool
     * @throws UserExceptionExt
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function loanQueryHandle($txnId)
    {
        $response = $this->postData('bpay/api/v1/disburse/order/query', ['orderId' => $txnId]);
        $content = $response->getBody()->getContents();
        $result = json_decode($content, true);
        if(isset($result['status']) && 'SUCCESS' == $result['status']
        && isset($result['statusCode']) && 'DE_001' == $result['statusCode'])
        {
            $this->financialLoanCallback = [
                'utr' => $result['result']['rrn'] ?? '',
                'trade_no' => $result['result']['paytmOrderId'] ?? '',
                'notify_result' => $content,
                'success_time' => time()
            ];
            $this->loanPayStatus = FinancialService::LOAN_STATUS_SUCCESS;
        }elseif (isset($result['status']) && 'FAILURE' == $result['status']){
            $this->financialLoanCallback = [
                'notify_result' => $content,
            ];
            $this->loanPayStatus = FinancialService::LOAN_STATUS_FAILURE;
        }
        elseif(isset($result['status']) && 'PENDING' == $result['status']){
            $this->financialLoanCallback = [
                'notify_result' => $content,
            ];
            $this->loanPayStatus = FinancialService::LOAN_STATUS_PENDING;
        }else{
            throw new UserExceptionExt("paytm打款查询，order_uuid:{$txnId}，未知的返回");
        }
        return true;
    }




    /**
     * @param $url
     * @param $params
     * @return mixed|\Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function postData($url,array $params){
        $postData = json_encode($params, JSON_UNESCAPED_SLASHES);
        $checksum = $this->checksum($postData);
        $headers = [
            'Content-Type' => 'application/json',
            'x-mid' => $this->payMerchantID,
            'x-checksum' => $checksum
        ];

        $client = new Client([
            RequestOptions::TIMEOUT => 120,
            RequestOptions::HEADERS => $headers,
        ]);

        yii::info(['header' => $headers, 'post' => $postData], 'paytm_payout');
        $response = $client->request('POST', $url, [
            RequestOptions::BODY => $postData
        ]);
        return $response;
    }


    public function checksum(string $post_data)
    {
        require_once(yii::getAlias('@common') . '/exceptions/paytm/lib/encdec_paytm.php');
        $checksum = getChecksumFromString($post_data, $this->merchantKey);
        return $checksum;
    }

}
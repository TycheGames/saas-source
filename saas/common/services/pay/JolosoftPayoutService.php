<?php
namespace common\services\pay;

use common\helpers\RedisQueue;
use common\models\order\FinancialLoanRecord;
use common\models\pay\JolosoftPayoutAccountForm;
use common\models\pay\LoanPayForm;
use common\models\pay\PayoutAccountInfo;
use common\services\message\WeWorkService;
use common\services\order\FinancialService;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Yii;

/**
 * Class RazorpayPayoutService
 * @package common\services\pay
 *
 * @property PayoutAccountInfo $payAccountSetting
 */
class JolosoftPayoutService extends BasePayoutService
{
    private $apikey;
    private $baseUri;

    public $loanPayStatus; //打款状态

    public $financialLoanCallback; //回写到financial_loan_record的数据

    const LOAN_STATUS_SUCCESS = 'SUCCESS'; //打款成功
    const LOAN_STATUS_FAILURE = 'FAILED'; //打款失败
    const LOAN_STATUS_PENDING = 'ACCEPTED'; //打款处理中

    public function __construct(PayoutAccountInfo $payAccountSetting, $config = [])
    {
        /** @var JolosoftPayoutAccountForm $form */
        $form = self::formPayoutModel();
        $form->load($payAccountSetting->getAccountInfo(), '');
        $this->accountId = $payAccountSetting->id;
        $this->apikey = $form->jolosoftApikey;
        $this->baseUri = 'http://13.127.227.22/freeunlimited/v3/';
        parent::__construct($payAccountSetting, $config);
    }

    /**
     * @param $payAccountId
     * @return JolosoftPayoutService
     */
    public static function getInstanceByPayAccountId($payAccountId)
    {
        $payAccountSetting = PayoutAccountInfo::findOne($payAccountId);
        return new self($payAccountSetting);
    }

    /**
     * @return JolosoftPayoutAccountForm
     */
    public static function formPayoutModel()
    {
        return new JolosoftPayoutAccountForm();
    }

    /**
     * 统一打款方法
     * @param LoanPayForm $form
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function loanPayHandle(LoanPayForm $form)
    {
        $params = [
            'apikey'                 => $this->apikey,
            'beneficiary_account_no' => $form->beneAccNo,
            'beneficiary_ifsc'       => $form->beneIFSC,
            'amount'                 => intval($form->amount / 100),
            'orderid'                => $form->txnId,
            'purpose'                => 'OTHERS',
            'callbackurl'            => 'https://external-notify.smallflyelephantsaas.com/notify/jolosoft-payout?id='.$this->accountId,
        ];

        $result = $this->postData('transfer.php', $params);
        Yii::info(['params' => $params, 'result' => $result], 'jolosoft_payout');
        if(empty($result)){
            return false;
        }

        switch ($result['status']){
            case self::LOAN_STATUS_PENDING:
                $this->financialLoanCallback = [
                    'trade_no' => $result['txid'],
                    'result' => json_encode($result),
                ];
                $this->loanPayStatus = FinancialService::LOAN_STATUS_PENDING;
                break;
            case self::LOAN_STATUS_FAILURE:
                //余额不足特殊处理
                if(isset($result['error']) && 'Insufficient API balance' == $result['error'])
                {
                    $weWorkService = new WeWorkService();
                    $sendMsg = YII_ENV .",支付账号:{$this->payAccountSetting->name},账户余额不足，请尽快充值";
                    $weWorkService->send($sendMsg);
                    $weWorkService->sendText(['lushan'], $sendMsg);
                }
                $this->financialLoanCallback = [
                    'result' => json_encode($result),
                ];
                $this->loanPayStatus =  FinancialService::LOAN_STATUS_FAILURE;
                break;
            case self::LOAN_STATUS_SUCCESS:
                $this->financialLoanCallback = [
                    'trade_no' => $result['txid'],
                    'success_time' => time(),
                    'result' => json_encode($result),
                ];
                $this->loanPayStatus = FinancialService::LOAN_STATUS_SUCCESS;
                break;
            default:
                return false;
        }

        return true;
    }

    /**
     * 打款回调方法
     * @param array $params
     * @return bool
     */
    public function payoutNotify($params)
    {
        $order_uuid = $params['userorderid'] ?? '';
        if(empty($order_uuid))
        {
            Yii::warning("order_uuid is null", 'jolosoft_payout');
            return false;
        }

        $lockKey = "jolosoft:payout:callback_{$order_uuid}";
        if(!RedisQueue::lock($lockKey, 60))
        {
            return false;
        }

        $withdrawInfo = FinancialLoanRecord::findOne(['order_id' => $order_uuid]);
        if(empty($withdrawInfo)){
            Yii::warning("no withdraw info for order_uuid:".$order_uuid, 'jolosoft_payout');
            return true;
        }

        if($withdrawInfo->status != FinancialLoanRecord::UMP_PAY_SUCCESS){
            $service = new FinancialService();
            if (self::LOAN_STATUS_SUCCESS == $params['status'])
            {
                $callback = [
                    'trade_no' => $params['joloorderid'],
                    'success_time' => time(),
                    'notify_result' => json_encode($params)
                ];
                $service->loanSuccessHandle($withdrawInfo, $callback);
            } elseif (self::LOAN_STATUS_FAILURE == $params['status']) {
                $callback = [
                    'notify_result' => json_encode($params)
                ];
                $service->loanFailureHandle($withdrawInfo, $callback);
            }
        }

        return true;
    }

    /**
     * @param $url
     * @param $params
     * @return array|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function postData($url, $params){
        $client = new Client([
            RequestOptions::TIMEOUT => 60,
            'base_uri' => $this->baseUri
        ]);

        $response = $client->request('POST', $url, [
            RequestOptions::JSON => $params,
        ]);
        return 200 == $response->getStatusCode() ? json_decode($response->getBody()->getContents(), true) : [];
    }
}
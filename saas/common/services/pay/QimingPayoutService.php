<?php
namespace common\services\pay;

use common\helpers\RedisQueue;
use common\models\order\FinancialLoanRecord;
use common\models\pay\LoanPayForm;
use common\models\pay\PayoutAccountInfo;
use common\models\pay\QimingPayoutAccountForm;
use common\services\order\FinancialService;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Yii;
use yii\db\Exception;
use function GuzzleHttp\Psr7\build_query;

/**
 * Class RazorpayPayoutService
 * @package common\services\pay
 *
 * @property PayoutAccountInfo $payAccountSetting
 */
class QimingPayoutService extends BasePayoutService
{
    private $keyId;
    private $apiKey;
    private $baseUri;

    public $loanPayStatus; //打款状态

    public $financialLoanCallback; //回写到financial_loan_record的数据

    const LOAN_STATUS_SUCCESS = 'Success'; //打款成功
    const LOAN_STATUS_FAILURE = 'Failed'; //打款失败
    const LOAN_STATUS_PENDING = 'Doing'; //打款处理中
    const LOAN_STATUS_INIT = 'Init'; //打款处理中

    public function __construct(PayoutAccountInfo $payAccountSetting, $config = [])
    {
        /** @var QimingPayoutAccountForm $form */
        $form = self::formPayoutModel();
        $form->load($payAccountSetting->getAccountInfo(), '');
        $this->accountId = $payAccountSetting->id;
        $this->keyId = $form->qimingKeyId;
        $this->apiKey = $form->qimingApiKey;
        $this->baseUri = YII_ENV_PROD ? 'https://pay.hfju5.com/' : 'http://13.126.98.81:8072/';
        parent::__construct($payAccountSetting, $config);
    }

    /**
     * @param $payAccountId
     * @return QimingPayoutService
     */
    public static function getInstanceByPayAccountId($payAccountId)
    {
        $payAccountSetting = PayoutAccountInfo::findOne($payAccountId);
        return new self($payAccountSetting);
    }

    /**
     * @return QimingPayoutAccountForm
     */
    public static function formPayoutModel()
    {
        return new QimingPayoutAccountForm();
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
            'title' => $form->txnId,
            'outTradeNo' => $form->txnId,
            'amount' => $form->amount,
            'callbackUrl' => 'https://external-notify.smallflyelephantsaas.com/notify/qiming-payout?id='.$this->accountId,
            'transferMode' => 'Banktransfer',
            'name' => $form->beneName,
            'account' => $form->beneAccNo,
            'ifsc' => $form->beneIFSC,
        ];

        $result = $this->postData('payout/createOrder', $params);
        Yii::info(['params' => $params, 'result' => $result], 'qiming_payout');
        if(!isset($result['code']) || $result['code'] != 0){
            return false;
        }

        switch ($result['data']['status']){
            case self::LOAN_STATUS_INIT:
            case self::LOAN_STATUS_PENDING:
                $this->financialLoanCallback = [
                    'trade_no' => $result['data']['id'],
                    'result' => json_encode($result),
                ];
                $this->loanPayStatus = FinancialService::LOAN_STATUS_PENDING;
                break;
            case self::LOAN_STATUS_FAILURE:
                $this->financialLoanCallback = [
                    'result' => json_encode($result),
                ];
                $this->loanPayStatus =  FinancialService::LOAN_STATUS_FAILURE;
                break;
            case self::LOAN_STATUS_SUCCESS:
                $this->financialLoanCallback = [
                    'trade_no' => $result['data']['id'],
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

    public function checkSign($params, $sign){
        ksort($params);
        $str = build_query($params, false);
        $str .= '&appKey='.$this->apiKey;
        $check_sign = md5($str);

        if($check_sign == $sign){
            return true;
        }

        return false;
    }

    /**
     * 打款回调方法
     * @param array $params
     * @return bool
     */
    public function payoutNotify($params)
    {
        $order_uuid = $params['outTradeNo'] ?? '';
        if(empty($order_uuid))
        {
            Yii::warning("order_uuid is null", 'qiming_payout');
            return false;
        }

        $lockKey = "qiming:payout:callback_{$order_uuid}";
        if(!RedisQueue::lock($lockKey, 60))
        {
            return false;
        }

        $withdrawInfo = FinancialLoanRecord::findOne(['order_id' => $order_uuid]);
        if(empty($withdrawInfo)){
            Yii::warning("no withdraw info for order_uuid:".$order_uuid, 'qiming_payout');
            return true;
        }

        if($withdrawInfo->status != FinancialLoanRecord::UMP_PAY_SUCCESS){
            $service = new FinancialService();
            if (self::LOAN_STATUS_SUCCESS == $params['status'])
            {
                $callback = [
                    'trade_no' => $params['id'],
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
     * @return mixed|\Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function postData($url, $params){
        ksort($params);
        $str = build_query($params, false);
        $str .= '&appKey='.$this->apiKey;
        $sign = md5($str);

        $client = new Client([
            'base_uri'              => $this->baseUri,
            RequestOptions::TIMEOUT => 60,
            RequestOptions::HEADERS => [
                'keyId' => $this->keyId,
                'sign' => $sign,
            ],
        ]);

        $response = $client->request('POST', $url, [
            RequestOptions::JSON => $params,
            RequestOptions::HTTP_ERRORS => false, //禁止http_errors 4xx 和 5xx
        ]);

        return $response->getStatusCode() == 200 ? json_decode($response->getBody()->getContents(), true) : [];
    }
}
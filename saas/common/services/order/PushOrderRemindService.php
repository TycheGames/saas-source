<?php


namespace common\services\order;


use common\helpers\EncryptData;
use common\models\order\UserLoanOrder;
use common\models\order\UserLoanOrderRepayment;
use common\models\product\ProductSetting;
use common\services\GuestService;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use yii\base\BaseObject;
use Yii;

class PushOrderRemindService extends BaseObject
{
    private $config            = [];
    private $appUrl            = [];
    private $applyUri          = 'remind/apply';
    private $orderRepaymentUri = 'remind/order-repayment';

    public function __construct($config = [])
    {
        parent::__construct($config);

        $this->config = Yii::$app->params['RemindCenter'];
        $this->appUrl = Yii::$app->params['appUrl'];
    }

    /**
     * @param UserLoanOrderRepayment $repayment
     * @return array|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \yii\base\Exception
     */
    public function pushOrder(UserLoanOrderRepayment $repayment){
        $order = $repayment->userLoanOrder;
        $orderExtraService = new OrderExtraService($order);
        $contact           = $orderExtraService->getUserContact();
        $appName           = $order->clientInfoLog->package_name;

        $repay_count = UserLoanOrderRepayment::find()->where(['user_id' => $repayment->user_id, 'status' => UserLoanOrderRepayment::STATUS_REPAY_COMPLETE])->count();

        if($order->is_export == UserLoanOrder::IS_EXPORT_YES){
            $sourceFrom = explode('_', $order->clientInfoLog->app_market)[1] ?? '';
            $productName = ProductSetting::getLoanExportProductName($appName, $sourceFrom);
        }else{
            $sourceFrom = $appName;
            $productName = $order->productSetting->product_name;
        }

        //生成还款链接
        $guestService = new GuestService();
        $paymentLink = $guestService->generatePaymentLink($order);

        $params = [
            'user_id'          => $order->user_id,
            'order_id'         => $order->id,
            'app_name'         => $appName,
            'order_info'       => [
                'status'              => $repayment->status,
                'loan_time'           => $repayment->loan_time,
                'loan_term'           => $order->loan_term,
                'plan_repayment_time' => $repayment->plan_repayment_time,
                'total_money'         => $repayment->total_money,
                'true_total_money'    => $repayment->true_total_money,
                'principal'           => $repayment->principal,
                'interests'           => $repayment->interests,
                'cost_fee'            => $repayment->cost_fee,
                'coupon_money'        => $repayment->coupon_money,
                'is_first'            => $order->is_first,
                'repay_count'         => $repay_count,
                'app_url'             => $this->appUrl[$sourceFrom] ?? '',
                'payment_link'        => $paymentLink,
                'bank_name'           => !empty($order->userBankAccount->bank_name) ? $order->userBankAccount->bank_name : substr($order->userBankAccount->ifsc, 0, 4),
                'bank_account'        => $order->userBankAccount->account,
                'pay_amount'          => $repayment->principal - $repayment->cost_fee,
            ],
            'user_basic_info'  => [
                'name'                       => $order->loanPerson->name,
                'phone'                      => $order->loanPerson->phone,
                'sex'                        => $order->loanPerson->gender,
                'birthday'                   => $order->loanPerson->birthday,
                'pan_code'                   => $order->loanPerson->pan_code,
                'aadhaar'                    => !empty($order->loanPerson->check_code) ? EncryptData::decrypt($order->loanPerson->check_code) : $order->loanPerson->aadhaar_number,
                'product_name'               => $productName,
                'source_from'                => $sourceFrom,
                'contact1_name'              => !empty($contact->name) ? $contact->name : 'other',
                'contact1_mobile_number'     => $contact->phone,
                'contact1_relative'          => $contact->relative_contact_person,
                'contact2_name'              => !empty($contact->other_name) ? $contact->other_name : 'other',
                'contact2_mobile_number'     => $contact->other_phone,
                'contact2_relative'          => $contact->other_relative_contact_person,
            ]
        ];

        $result = $this->postData($this->applyUri, $params);
        return $result;
    }

    /**
     * @param $params
     * @return array|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function pushOrderRemindRepayment($params){
        $result = $this->postData($this->orderRepaymentUri, $params);
        return $result;
    }

    /**
     * @param string $uri
     * @param array $params
     * @return array|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function postData(string $uri, array $params)
    {
        $client = new Client([
                'base_uri'              => $this->config[$params['app_name']]['base_url'],
                RequestOptions::TIMEOUT => 60,
            ]
        );
        $params['token'] = $this->config[$params['app_name']]['token'];
        $response = $client->request('POST', $uri, [
            RequestOptions::FORM_PARAMS => $params
        ]);

        $result = 200 == $response->getStatusCode() ? json_decode($response->getBody()->getContents(), true) : [];
        return $result;
    }

}
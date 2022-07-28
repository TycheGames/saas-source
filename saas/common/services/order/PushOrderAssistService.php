<?php


namespace common\services\order;


use common\helpers\EncryptData;
use common\models\order\UserLoanOrder;
use common\models\order\UserLoanOrderExternal;
use common\models\order\UserLoanOrderRepayment;
use common\models\product\ProductSetting;
use common\models\user\MgUserMobileContacts;
use common\services\GuestService;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use yii\base\BaseObject;
use Yii;

class PushOrderAssistService extends BaseObject
{
    private $config            = [];
    private $appUrl            = [];
    private $applyUri          = 'assist/apply';
    private $uploadContactUri  = 'assist/upload-contact';
    private $orderOverdueUri   = 'assist/order-overdue';
    private $orderRepaymentUri = 'assist/order-repayment';
    private $linkUri           = 'assist/link';

    public function __construct($config = [])
    {
        parent::__construct($config);

        $this->config = Yii::$app->params['AssistCenter'];
        $this->appUrl = Yii::$app->params['appUrl'];
    }

    /**
     * @param UserLoanOrderRepayment $repayment
     * @return array|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \yii\base\Exception
     */
    public function pushOrder(UserLoanOrderRepayment $repayment, $app_name=''){
        $order = $repayment->userLoanOrder;
        $orderExtraService = new OrderExtraService($order);
        $workInfo          = $orderExtraService->getUserWorkInfo();
        $basicInfo         = $orderExtraService->getUserBasicInfo();
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
            'app_name'         => !empty($app_name) ? $app_name : $appName,
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
                'overdue_fee'         => $repayment->overdue_fee,
                'coupon_money'        => $repayment->coupon_money,
                'overdue_day'         => $repayment->overdue_day,
                'is_first'            => $order->is_first,
                'repay_count'         => $repay_count,
                'imei'                => $order->device_id,
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
                'educated'                   => $workInfo->educated,
                'marital'                    => $basicInfo->marital_status,
                'residential_address1'       => $workInfo->residential_address1,
                'residential_address2'       => $workInfo->residential_address2,
                'residential_detail_address' => $workInfo->residential_detail_address,
                'company_name'               => '',
                'company_address'            => '',
                'company_phone'              => '',
                'product_name'               => $productName,
                'source_from'                => $sourceFrom,
                'aadhaar_address1'           => $basicInfo->aadhaar_address1,
                'aadhaar_address2'           => $basicInfo->aadhaar_address2,
                'aadhaar_detail_address'     => $basicInfo->aadhaar_detail_address,
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
     * @param $data
     * @return array|int[]|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function pushUserContacts($data){
        $order = UserLoanOrder::findOne($data['order_id']);
        if($order->is_export == UserLoanOrder::IS_EXPORT_YES){
            $db = Yii::$app->mongodb_loan;
            $orderExternal = UserLoanOrderExternal::userExternalOrder($order->order_uuid);
            $user_id = intval($orderExternal->user_id);
        }else{
            $db = Yii::$app->mongodb;
            $user_id = intval($data['user_id']);
        }
        $contact = MgUserMobileContacts::find()
            ->select(['mobile', 'name'])
            ->where(['user_id' => $user_id])
            ->limit(1000)
            ->asArray()->all($db);
        if(empty($contact)){
            return ['code' => 0];
        }

        foreach ($contact as &$v){
            unset($v['_id']);
        }

        $params = [
            'app_name' => $data['app_name'],
            'user_id'  => $data['user_id'],
            'data'     => json_encode($contact)
        ];
        $result = $this->postData($this->uploadContactUri, $params);
        return $result;
    }

    /**
     * @param UserLoanOrderRepayment $repayment
     * @return array|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function pushOrderOverdue(UserLoanOrderRepayment $repayment){
        $params = [
            'user_id'     => $repayment->user_id,
            'order_id'    => $repayment->order_id,
            'app_name'    => $repayment->userLoanOrder->clientInfoLog->package_name,
            'total_money' => $repayment->total_money,
            'overdue_day' => $repayment->overdue_day,
            'overdue_fee' => $repayment->overdue_fee,
        ];
        $result = $this->postData($this->orderOverdueUri, $params);
        return $result;
    }

    /**
     * @param $params
     * @return array|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function pushOrderAssistRepayment($params){
        $result = $this->postData($this->orderRepaymentUri, $params);
        return $result;
    }

    public function pushOrderLink(UserLoanOrderRepayment $repayment){
        $order = $repayment->userLoanOrder;
        $appName = $order->clientInfoLog->package_name;

        if($order->is_export == UserLoanOrder::IS_EXPORT_YES){
            $sourceFrom = explode('_', $order->clientInfoLog->app_market)[1] ?? '';
        }else{
            $sourceFrom = $appName;
        }

        //生成还款链接
        $guestService = new GuestService();
        $paymentLink = $guestService->generatePaymentLink($order);

        $params = [
            'user_id'      => $order->user_id,
            'order_id'     => $order->id,
            'app_name'     => $appName,
            'app_url'      => $this->appUrl[$sourceFrom] ?? '',
            'payment_link' => $paymentLink,
        ];

        $result = $this->postData($this->linkUri, $params);
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
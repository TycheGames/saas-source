<?php

namespace common\services\risk;

use Carbon\Carbon;
use common\models\enum\City;
use common\models\enum\Gender;
use common\models\order\UserLoanOrder;
use common\models\order\UserLoanOrderExtraRelation;
use common\models\package\PackageSetting;
use common\models\pay\CibilKudosAccountForm;
use common\models\user\LoanPerson;
use common\models\user\UserCreditReportExperian;
use common\services\order\OrderExtraService;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Yii;
use yii\db\Exception;


class CreditReportExperianService extends BaseDataService
{

    private $url = YII_ENV_PROD ? 'http://api.kudosfinance.in/experian/partners/prod.php' : 'http://api.kudosfinance.in/experian/partners/uat.php';

    /**
     * @var UserCreditReportExperian
     */
    private $UserCreditReportExperian;
    private $partnerId;
    private $authKey;

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $packageSetting = PackageSetting::findOne(['source_id' => $this->loanPerson->source_id]);
        $accountInfo = $packageSetting->creditAccountSetting->getAccountInfo();
        $model = self::formModel();
        $model->load($accountInfo, '');
        $this->partnerId = $model->partnerId;
        $this->authKey = $model->authKey;
    }

    /**
     * @return CibilKudosAccountForm|\yii\base\Model
     */
    public static function formModel()
    {
        return new CibilKudosAccountForm();
    }

    /**
     * 1. 查询数据
     * @return bool
     * @throws \Exception
     */
    public function getData(): bool
    {
        $this->order = UserLoanOrder::findOne($this->order->id);

        $this->initData();
        if(isset($this->UserCreditReportExperian->status) && $this->UserCreditReportExperian->status == UserCreditReportExperian::STATUS_SUCCESS){
            return true;
        }

        if(!$this->checkDataExpired()){
            return true;
        }

        if(!$this->canRetry()){
            return true;
        }

        $result = $this->getReport();
        return $result;
    }

    /**
     * @return bool
     */
    private function getReport(): bool
    {
        $this->UserCreditReportExperian->user_id     = $this->order->loanPerson->id;
        $this->UserCreditReportExperian->merchant_id = $this->order->loanPerson->merchant_id;
        $this->UserCreditReportExperian->pan_code    = $this->order->loanPerson->pan_code;
        $this->UserCreditReportExperian->retry_num   = $this->UserCreditReportExperian->retry_num + 1;

        if(!$this->UserCreditReportExperian->save()){
            return false;
        }

        $extra = UserLoanOrderExtraRelation::findOne(['order_id' => $this->order->id]);
        $extra->user_credit_report_experian_id = $this->UserCreditReportExperian->id;
        if(!$extra->save()){
            return false;
        }

        $time = $this->order->order_time - 30 * 86400;
        $reportExperian = UserCreditReportExperian::find()
            ->where(['pan_code' => $this->loanPerson->pan_code,
                     'status' => UserCreditReportExperian::STATUS_SUCCESS])
            ->andWhere(['>=', 'query_time', $time])
            ->orderBy(['query_time' => SORT_DESC])
            ->one(Yii::$app->db_loan);

        if(!empty($reportExperian)){
            $this->UserCreditReportExperian->data        = $reportExperian->data;
            $this->UserCreditReportExperian->status      = $reportExperian->status;
            $this->UserCreditReportExperian->score       = $reportExperian->score;
            $this->UserCreditReportExperian->data_status = $reportExperian->data_status;
            $this->UserCreditReportExperian->query_time  = $reportExperian->query_time;
            if($this->UserCreditReportExperian->save()){
                return true;
            }else{
                return false;
            }
        }

        $name = LoanPerson::getNameConversion($this->order->loanPerson->name);
        $userExtraService = new OrderExtraService($this->order);
        $userWorkInfo = $userExtraService->getUserWorkInfo();
        $userBasicInfo = $userExtraService->getUserBasicInfo();
        $params = [
            'BorrowerReason'     => 1,
            'BorrowerLoanAmt'    => 1700,
            'BorrowerTenure'     => $this->order->loan_term,
            'BorrowerLastName'   => $name['last_name'],
            'BorrowerFirstName'  => $name['first_name'],
            'BorrowerMiddleName' => $name['middle_name'],
            'BorrowerGenderCode' => Gender::$mapForKudosExperian[$this->order->loanPerson->gender],
            'BorrowerPAN'        => $this->order->loanPerson->pan_code,
            'BorrowerPassportNo' => '',
            'BorrowerDOB'        => Carbon::rawCreateFromFormat('Y-m-d',$this->order->loanPerson->birthday)->format('Ymd'),
            'BorrowerPhone'      => strlen($this->order->loanPerson->phone) < 12 ? '91' . $this->order->loanPerson->phone : $this->order->loanPerson->phone,
            'BorrowerEmail'      => '',
            'Borrower_Addr1'     => $userWorkInfo->residential_detail_address,
            'Borrower_Addr2'     => '',
            'Borrower_Addr3'     => '',
            'Borrower_City'      => $userWorkInfo->residential_address2,
            'Borrower_StateCode' => City::$map[$userWorkInfo->residential_address1],
            'Borrower_Pincode'   => $userBasicInfo->zip_code,
        ];

        try {
            $response = $this->postData($params, $this->url);
        } catch (\Exception $exception) {
            return false;
        }
        $xml_result = $response->getBody()->getContents();
        Yii::info(['order_id' => $this->order->id,'params' => json_encode($params), 'response' => $xml_result],'Experian');

        try {
            $result = simplexml_load_string($xml_result);
            $result = $result->children('http://schemas.xmlsoap.org/soap/envelope/')->Body;
            $result = $result->children('urn:cbv2');
            $result = json_decode(json_encode($result), true);
            $result = json_decode(json_encode(simplexml_load_string($result['processResponse']['out'])), true);

            if(isset($result['Header']['SystemCode']) && $result['Header']['SystemCode'] == 0){
                $this->UserCreditReportExperian->data       = $xml_result;
                $this->UserCreditReportExperian->status     = UserCreditReportExperian::STATUS_SUCCESS;
                $this->UserCreditReportExperian->score      = $result['SCORE']['BureauScore'] ?? 0;
                $this->UserCreditReportExperian->is_request = 1;
                $this->UserCreditReportExperian->query_time = time();
                if(!empty($result['UserMessage']['UserMessageText']) && $result['UserMessage']['UserMessageText'] == 'Normal Response'){
                    $this->UserCreditReportExperian->data_status = UserCreditReportExperian::STATUS_SUCCESS;
                }
                if($this->UserCreditReportExperian->save()){
                    return true;
                }
            }

        } catch (\Exception $e){

        } catch (\Throwable $ex){

        }

        return false;
    }


    private function initData()
    {
        if(is_null($this->UserCreditReportExperian))
        {
            $this->UserCreditReportExperian = $this->order->userCreditReportExperian;
            if(is_null($this->UserCreditReportExperian)){
                $this->UserCreditReportExperian = new UserCreditReportExperian();
            }
        }
    }

    public function canRetry() : bool
    {
        $this->initData();
        if(!isset($this->UserCreditReportExperian->retry_num)){
            return true;
        }
        return $this->UserCreditReportExperian->retry_num < $this->retryLimit;
    }


    public function validateData() : bool
    {
        return true;
    }

    public function postData($params, $url)
    {
        $client = new Client([
            RequestOptions::TIMEOUT => 60,
            RequestOptions::HEADERS => [
                'PARTNERID' => $this->partnerId,
                'PARTNERXAPIKEY' => $this->authKey,
                'QUERY' => 'EXPCALL'
            ],
        ]);
        $response = $client->request('POST', $url, [
            RequestOptions::FORM_PARAMS => $params
        ]);
        return $response;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    private function checkDataExpired(){
        $time = $this->order->order_time - 30 * 86400;

        $experianReport = UserCreditReportExperian::find()
            ->select(['id','query_time'])
            ->where([
                'pan_code' => $this->loanPerson->pan_code,
                'status' => UserCreditReportExperian::STATUS_SUCCESS])
            ->andWhere(['>=', 'query_time', $time])
            ->orderBy(['query_time' => SORT_DESC])
            ->one();

        if(empty($experianReport)){
            return true;
        }

        $extra = UserLoanOrderExtraRelation::findOne(['order_id' => $this->order->id]);
        $extra->user_credit_report_experian_id = $experianReport['id'];
        if(!$extra->save()){
            throw new \Exception("订单:{$this->order->id}关联experian征信报告id:{$experianReport['id']}失败");
        }

        return false;
    }

}

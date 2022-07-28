<?php

namespace common\services\risk;

use Carbon\Carbon;
use common\helpers\CommonHelper;
use common\models\enum\City;
use common\models\enum\Gender;
use common\models\order\UserLoanOrder;
use common\models\order\UserLoanOrderExtraRelation;
use common\models\package\PackageSetting;
use common\models\pay\CibilKudosAccountForm;
use common\models\pay\KudosAccountForm;
use common\models\user\LoanPerson;
use common\models\user\UserCreditReportCibil;
use common\models\user\UserCreditReportExperian;
use common\services\order\OrderExtraService;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Yii;

class CreditReportCibilService extends BaseDataService
{

    private $url = YII_ENV_PROD ? 'http://api.kudosfinance.in/cbapiv3/prod.php' : 'http://api.kudosfinance.in/cbapiv3/uat.php';
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
//        $this->url = $model->url;
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
        if(isset($this->order->userCreditReportCibil->status) && $this->order->userCreditReportCibil->status == UserCreditReportCibil::STATUS_SUCCESS){
            return true;
        }

        if(!$this->checkDataExpired()){
            return true;
        }

        if(!$this->canRetry()){
            return true;
        }

        $result = $this->getReport($this->order->user_id);

        return $result;
    }

    public function canRetry() : bool
    {
        if(!isset($this->order->userCreditReportCibil->retry_num)){
            return true;
        }
        return $this->retryLimit > $this->order->userCreditReportCibil->retry_num;
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
                'QUERY' => 'TU3'
            ],
        ]);
        $response = $client->request('POST', $url, [
            RequestOptions::FORM_PARAMS => $params
        ]);
        return $response;
    }

    /**
     *
     * @param int $userId
     * @return bool
     * @throws \Exception
     */
    private function getReport(int $userId): bool
    {
        if(empty($this->order->userCreditReportCibil)){
            $report = new UserCreditReportCibil();
            $report->user_id     = $userId;
            $report->pan_code    = $this->order->loanPerson->pan_code;
            $report->merchant_id = $this->order->loanPerson->merchant_id;
            $report->retry_num   = 1;
            if(!$report->save()){
                return false;
            }

            $extra = UserLoanOrderExtraRelation::findOne(['order_id' => $this->order->id]);
            $extra->user_credit_report_cibil_id = $report->id;
            if(!$extra->save()){
                return false;
            }
        }else{
            $report = $this->order->userCreditReportCibil;
            $report->retry_num = $report->retry_num + 1;
            if(!$report->save()){
                return false;
            }
        }

        $time = $this->order->order_time - 30 * 86400;
        $reportCibil = UserCreditReportCibil::find()
            ->where(['pan_code' => $this->loanPerson->pan_code,
                     'status' => UserCreditReportCibil::STATUS_SUCCESS])
            ->andWhere(['>=', 'query_time', $time])
            ->orderBy(['query_time' => SORT_DESC])
            ->one(Yii::$app->db_loan);

        if(!empty($reportCibil)){
            $report->data       = $reportCibil->data;
            $report->status     = $reportCibil->status;
            $report->score      = $reportCibil->score;
            $report->name       = $reportCibil->name;
            $report->query_time = $reportCibil->query_time;
            if($report->save()){
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
            'BorrowerFName' => $name['first_name'],
            'BorrowerMName' => $name['middle_name'],
            'BorrowerLName' => $name['last_name'],
            'BorrowerDOB' => Carbon::rawCreateFromFormat('Y-m-d',$this->order->loanPerson->birthday)->format('dmY'),
            'BorrowerGender' => Gender::$mapForKudos[$this->order->loanPerson->gender],
            'BorrowerEmail' => '',
            'BorrowerCompanyName' => $userWorkInfo->company_name ?? '',
            'Idnumber' => $this->order->loanPerson->pan_code,
            'Idtype' => '01',
            'BorrowerPhone' => strlen($this->order->loanPerson->phone) <12 ? '91' . $this->order->loanPerson->phone : $this->order->loanPerson->phone,
            'BorrowerPhoneType' => '01',
            'Borrower_Addr1' => $userWorkInfo->residential_detail_address,
            'Borrower_Addr2' => '',
            'Borrower_Addr3' => '',
            'Borrower_Addr4' => '',
            'Borrower_Addr5' => '',
            'Borrower_AddrType' => '01',
            'Borrower_City' => $userWorkInfo->residential_address2,
            'Borrower_Pincode' => $userBasicInfo->zip_code,
            'Borrower_ResiType' => '',
            'Borrower_StateCode' => City::$map[$userWorkInfo->residential_address1],
            'Borrower_RequestAmount' => 1700,
            'Borrower_LoanPurpose' => '05',
            'Borrower_RepaymentPer_Mnths' => '1',
            'ConsumerConsentForUIDAIAuthentication' => 'true',
            'GSTStateCode' => City::$map[$userWorkInfo->residential_address1],
            'Request_ReferenceNum' => rand(1000000, 9999999),
            'Borrower_Income' => intval(CommonHelper::CentsToUnit($userWorkInfo->monthly_salary))
        ];

        try {
            $response = $this->postData($params, $this->url);
            $xml_result = $response->getBody()->getContents();
            Yii::info(['order_id' => $this->order->id,'params' => json_encode($params), 'response' => $xml_result],'CIBIL');
            if(200 != $response->getStatusCode()){
                return false;
            }
        } catch (\Exception $exception) {
            return false;
        }

        try {
            $xpath = simplexml_load_string($xml_result);
            $xpath->registerXPathNamespace('soap', 'http://schemas.xmlsoap.org/soap/envelope/');
            $result = $xpath->xpath("soap:Body");
            $result = json_decode(json_encode($result),true);
            $xml = htmlspecialchars_decode($result[0]['ExecuteXMLStringResponse']['ExecuteXMLStringResult']);
            $data = json_decode(json_encode(simplexml_load_string($xml)),true);
            if(isset($data['ContextData']['Field'][0]['Applicants'])){
                $data = $data['ContextData']['Field'][0]['Applicants']['Applicant']['DsCibilBureau']['Response']['CibilBureauResponse'];
            }
            if(isset($data['ContextData']['Field'][0]['Applicant'])){
                $data = $data['ContextData']['Field'][0]['Applicant']['DsCibilBureau']['Response']['CibilBureauResponse'];
            }
            $IsSucess = $data['IsSucess'] ?? '';
            $CreditReport = json_decode(json_encode(simplexml_load_string($data['BureauResponseXml'])),true);
            if($IsSucess == 'True'){
                $report->data = $xml_result;
                $report->status = UserCreditReportCibil::STATUS_SUCCESS;
                $score = $CreditReport['ScoreSegment']['Score'] ?? 0;
                if($score == '000-1'){
                    $score = -1;
                }
                $report->score = $score;
                $name = '';
                for ($i = 1; $i < 7; $i++){
                    if(isset($CreditReport['NameSegment']['ConsumerName'.$i])){
                        $name .= $CreditReport['NameSegment']['ConsumerName'.$i].' ';
                    }
                }
                $report->name = trim($name);
                $report->is_request = 1;
                $report->query_time = time();
                if($report->save()){
                    return true;
                }
            }
        } catch (\Exception $e){

        } catch (\Throwable $ex){

        }

        return false;

    }

    /**
     * @return bool
     * @throws \Exception
     */
    private function checkDataExpired(){
        $time = $this->order->order_time - 30 * 86400;
        $cibilReport = UserCreditReportCibil::find()
            ->select(['id','query_time'])
            ->where([
                'pan_code' => $this->loanPerson->pan_code,
                'status' => UserCreditReportCibil::STATUS_SUCCESS])
            ->andWhere(['>=', 'query_time', $time])
            ->orderBy(['query_time' => SORT_DESC])
            ->one();

        if(empty($cibilReport)){
            return true;
        }

        $extra = UserLoanOrderExtraRelation::findOne(['order_id' => $this->order->id]);
        $extra->user_credit_report_cibil_id = $cibilReport['id'];
        if(!$extra->save()){
            throw new \Exception("订单:{$this->order->id}关联cibil征信报告id:{$cibilReport['id']}失败");
        }

        return false;
    }

}

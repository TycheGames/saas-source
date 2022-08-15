<?php

namespace common\services\risk;

use Carbon\Carbon;
use common\helpers\CommonHelper;
use common\models\enum\City;
use common\models\enum\Gender;
use common\models\pay\CibilKudosAccountForm;
use common\models\RiskOrder;
use common\models\user\UserCreditReportBangaloreExperian;
use common\models\user\UserCreditReportExperian;
use common\models\user\UserCreditReportMobiExperian;
use common\models\user\UserCreditReportShanyunExperian;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Yii;
use yii\db\Exception;


class CreditReportExperianService extends BaseDataService
{

    private $url = YII_ENV_PROD ? 'http://elaap.kudosfinance.in:5000/api/bureau-experian' : 'http://elaap.kudosfinance.in:5000/api/bureau-experian';

    /**
     * @var UserCreditReportExperian
     */
    private $UserCreditReportExperian;
    private $companyCode;
    private $token;

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->companyCode = Yii::$app->params['KudosExperian']['company_code'];
        $this->token = Yii::$app->params['KudosExperian']['token'];
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
        $this->order = RiskOrder::findOne($this->order->id);

        $this->initData();
        if(isset($this->UserCreditReportExperian->status) && $this->UserCreditReportExperian->status == UserCreditReportExperian::STATUS_SUCCESS){
            return true;
        }

        if(isset($this->order->userCreditReportShanyunExperian->status)
            && $this->order->userCreditReportShanyunExperian->status == UserCreditReportShanyunExperian::STATUS_SUCCESS){
            return true;
        }

        if(isset($this->order->userCreditReportMobiExperian->status)
            && $this->order->userCreditReportMobiExperian->status == UserCreditReportMobiExperian::STATUS_SUCCESS){
            return true;
        }

        if(isset($this->order->userCreditReportBangaloreExperian->status)
            && $this->order->userCreditReportBangaloreExperian->status == UserCreditReportBangaloreExperian::STATUS_SUCCESS){
            return true;
        }

        if(!$this->checkDataExpired()){
            return true;
        }

        if(!$this->canRetry()){
            return false;
        }

        $result = $this->getReport();
        return $result;
    }

    /**
     * @return bool
     */
    private function getReport(): bool
    {
        $this->UserCreditReportExperian->pan_code    = $this->infoUser->pan_code;
        $this->UserCreditReportExperian->retry_num   = $this->UserCreditReportExperian->retry_num + 1;

        if(!$this->UserCreditReportExperian->save()){
            return false;
        }

        $this->order->user_experian_id = $this->UserCreditReportExperian->id;
        if(!$this->order->save()){
            return false;
        }

        if(empty($this->infoUser->email_address)){
            return false;
        }

        $name = CommonHelper::getNameConversion($this->infoUser->pan_verify_name);
        $params = [
            'BorrowerReason'     => 1,
            'BorrowerLoanAmt'    => 1700,
            'BorrowerTenure'     => 7,
            'BorrowerLastName'   => !empty($name['last_name']) ? $name['last_name'] : 'kumar',
            'BorrowerFirstName'  => $name['first_name'],
            'BorrowerMiddleName' => $name['middle_name'],
            'BorrowerGenderCode' => Gender::$mapForKudosExperian[$this->infoUser->gender],
            'BorrowerPAN'        => $this->infoUser->pan_code,
            'BorrowerPassportNo' => '',
            'BorrowerDOB'        => Carbon::rawCreateFromFormat('Y-m-d',$this->infoUser->pan_birthday)->format('Ymd'),
            'BorrowerPhone'      => $this->infoUser->phone,
            'BorrowerEmail'      => $this->infoUser->email_address,
            'Borrower_Addr1'     => $this->infoUser->residential_detail_address,
            'Borrower_Addr2'     => $this->infoUser->residential_detail_address,
            'Borrower_Addr3'     => '',
            'Borrower_City'      => $this->infoUser->residential_city,
            'Borrower_StateCode' => City::$map[$this->infoUser->residential_address],
            'Borrower_Pincode'   => $this->infoUser->aadhaar_pin_code,
        ];

        try {
            $response = $this->postData($params, $this->url);
        } catch (\Exception $exception) {
            return false;
        }
        $res = json_decode($response->getBody()->getContents(), true);
        $xml_result = $res['data'];
        Yii::info(['risk_order_id' => $this->order->id,'params' => json_encode($params), 'response' => $xml_result],'Experian');

        try {
            if(isset($res['STATUS']) && $res['STATUS'] == 'SUCCESS'){
                $result = simplexml_load_string($xml_result);
                $result = $result->children('http://schemas.xmlsoap.org/soap/envelope/')->Body;
                $result = $result->children('urn:cbv2');
                $result = json_decode(json_encode($result), true);
                $result = json_decode(json_encode(simplexml_load_string($result['processResponse']['out'])), true);

                if(isset($result['Header']['SystemCode']) && $result['Header']['SystemCode'] == 0){
                    $this->UserCreditReportExperian->data   = $xml_result;
                    $this->UserCreditReportExperian->status = UserCreditReportExperian::STATUS_SUCCESS;
                    $this->UserCreditReportExperian->score  = $result['SCORE']['BureauScore'] ?? 0;
                    $this->UserCreditReportExperian->is_request = 1;
                    $this->UserCreditReportExperian->query_time = time();
                    if(!empty($result['UserMessage']['UserMessageText']) && $result['UserMessage']['UserMessageText'] == 'Normal Response'){
                        $this->UserCreditReportExperian->data_status = UserCreditReportExperian::STATUS_SUCCESS;
                    }

                    if($this->UserCreditReportExperian->save()){
                        return true;
                    }
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
                'Authorization' => $this->token,
                'company_code' => $this->companyCode
            ],
        ]);
        $response = $client->request('POST', $url, [
            RequestOptions::JSON => $params
        ]);
        return $response;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    private function checkDataExpired(){
        $time = $this->order->infoOrder->order_time - 30 * 86400;

        $bangaloreReport = UserCreditReportBangaloreExperian::find()
            ->select(['id','query_time'])
            ->where([
                'pan_code' => $this->infoUser->pan_code,
                'status' => UserCreditReportBangaloreExperian::STATUS_SUCCESS])
            ->andWhere(['>=', 'query_time', $time])
            ->orderBy(['query_time' => SORT_DESC])
            ->one();

        $report = UserCreditReportExperian::find()
            ->select(['id','query_time'])
            ->where([
                'pan_code' => $this->infoUser->pan_code,
                'status' => UserCreditReportExperian::STATUS_SUCCESS])
            ->andWhere(['>=', 'query_time', $time])
            ->orderBy(['query_time' => SORT_DESC])
            ->one();

        $mobiReport = UserCreditReportMobiExperian::find()
            ->select(['id','query_time'])
            ->where([
                'pan_code' => $this->infoUser->pan_code,
                'status' => UserCreditReportMobiExperian::STATUS_SUCCESS])
            ->andWhere(['>=', 'query_time', $time])
            ->orderBy(['query_time' => SORT_DESC])
            ->one();

        $shanyunReport = UserCreditReportShanyunExperian::find()
            ->select(['id','query_time'])
            ->where([
                'pan_code' => $this->infoUser->pan_code,
                'status' => UserCreditReportShanyunExperian::STATUS_SUCCESS])
            ->andWhere(['>=', 'query_time', $time])
            ->orderBy(['query_time' => SORT_DESC])
            ->one();

        if(empty($bangaloreReport) && empty($report) && empty($mobiReport) && empty($shanyunReport)){
            return true;
        }

        $updated_at = $report['query_time'] ?? 0;
        $bangalore_updated_at = $bangaloreReport['query_time'] ?? 0;
        $mobi_updated_at = $mobiReport['query_time'] ?? 0;
        $shanyun_updated_at = $shanyunReport['query_time'] ?? 0;

        $arr = [
            0 => $updated_at,
            1 => $bangalore_updated_at,
            2 => $mobi_updated_at,
            3 => $shanyun_updated_at,
        ];

        $key = array_search(max($arr), $arr);

        if($key == 0){
            $this->order->user_experian_id = $report['id'];
        }elseif($key == 1){
            $this->order->user_bangalore_experian_id = $bangaloreReport['id'];
        }elseif($key == 2){
            $this->order->user_mobi_experian_id = $mobiReport['id'];
        }elseif($key == 3){
            $this->order->user_shanyun_experian_id = $shanyunReport['id'];
        }else{
            return true;
        }

        if(!$this->order->save()){
            throw new \Exception("订单:{$this->order->id}关联Experian征信报告失败");
        }

        return false;
    }

}

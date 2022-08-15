<?php

namespace common\services\risk;

use Carbon\Carbon;
use common\helpers\ArrayToXml;
use common\helpers\CommonHelper;
use common\models\enum\City;
use common\models\enum\Gender;
use common\models\RiskOrder;
use common\models\user\UserCreditReportBangaloreExperian;
use common\models\user\UserCreditReportExperian;
use common\models\user\UserCreditReportMobiExperian;
use common\models\user\UserCreditReportShanyunExperian;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Yii;
use yii\db\Exception;


class CreditReportBangaloreExperianService extends BaseDataService
{

    private $url = YII_ENV_PROD ? 'https://connect.experian.in/nextgen-ind-pds-webservices-cbv2/endpoint' : 'https://connectuat.experian.in/nextgen-ind-pds-webservices-cbv2/endpoint';

    /**
     * @var UserCreditReportBangaloreExperian
     */
    private $experian;
    private $username;
    private $password;

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->username = Yii::$app->params['BangaloreExperian']['username'];
        $this->password = Yii::$app->params['BangaloreExperian']['password'];
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
        if(isset($this->experian->status) && $this->experian->status == UserCreditReportBangaloreExperian::STATUS_SUCCESS){
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

        if(isset($this->order->userCreditReportExperian->status)
            && $this->order->userCreditReportExperian->status == UserCreditReportExperian::STATUS_SUCCESS){
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
        $this->experian->pan_code  = $this->infoUser->pan_code;
        $this->experian->retry_num = $this->experian->retry_num + 1;

        if(!$this->experian->save()){
            return false;
        }

        $this->order->user_bangalore_experian_id = $this->experian->id;
        if(!$this->order->save()){
            return false;
        }

        $name = CommonHelper::getNameConversion($this->infoUser->pan_verify_name);
        $params = [
            'Identification' => [
                'XMLUser'     => $this->username,
                'XMLPassword' => $this->password,
            ],
            'Application' => [
                'EnquiryReason'       => '02',
                'FinancePurpose'      => '99',
                'AmountFinanced'      => '1700',
                'DurationOfAgreement' => '7',
                'ScoreFlag'           => '1'
            ],
            'Applicant' => [
                'Surname'      => $name['last_name'],
                'FirstName'    => $name['first_name'],
                'GenderCode'   => Gender::$mapForKudosExperian[$this->infoUser->gender],
                'IncomeTaxPAN' => $this->infoUser->pan_code,
                'DateOfBirth'  => Carbon::rawCreateFromFormat('Y-m-d', $this->infoUser->pan_birthday)->format('Ymd'),
                'MobilePhone'  => $this->infoUser->phone,
            ],
            'Address' => [
                'FlatNoPlotNoHouseNo' => $this->infoUser->residential_detail_address,
                'City'                => $this->infoUser->residential_city,
                'State'               => City::$map[$this->infoUser->residential_address],
                'PinCode'             => $this->infoUser->aadhaar_pin_code,
            ],
            'AdditionalAddressFlag' => [
                'Flag' => 'N'
            ]
        ];

        $body = ArrayToXml::convert($params, 'INProfileRequest', true, 'utf-8');

        try {
            $response = $this->postData($body, $this->url);
        } catch (\Exception $exception) {
            return false;
        }
        $xml_result = $response->getBody()->getContents();
        Yii::info(['risk_order_id' => $this->order->id,'params' => $body, 'response' => $xml_result],'BangaloreExperian');

        try {
            $result = simplexml_load_string($xml_result);
            $result = $result->children('http://schemas.xmlsoap.org/soap/envelope/')->Body;
            $result = $result->children('urn:cbv2');
            $result = json_decode(json_encode($result), true);
            $result = json_decode(json_encode(simplexml_load_string($result['processResponse']['out'])), true);

            if(isset($result['Header']['SystemCode']) && $result['Header']['SystemCode'] == 0){
                $this->experian->data       = $xml_result;
                $this->experian->status     = UserCreditReportBangaloreExperian::STATUS_SUCCESS;
                $this->experian->score      = $result['SCORE']['BureauScore'] ?? 0;
                $this->experian->is_request = 1;
                $this->experian->query_time = time();
                if(!empty($result['UserMessage']['UserMessageText']) && $result['UserMessage']['UserMessageText'] == 'Normal Response'){
                    $this->experian->data_status = UserCreditReportBangaloreExperian::STATUS_SUCCESS;
                }

                if($this->experian->save()){
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
        if(is_null($this->experian))
        {
            $this->experian = $this->order->userCreditReportBangaloreExperian;
            if(is_null($this->experian)){
                $this->experian = new UserCreditReportBangaloreExperian();
            }
        }
    }

    public function canRetry() : bool
    {
        $this->initData();
        if(!isset($this->experian->retry_num)){
            return true;
        }
        return $this->experian->retry_num < $this->retryLimit;
    }


    public function validateData() : bool
    {
        return true;
    }

    public function postData($body, $url)
    {
        $client = new Client([
            RequestOptions::TIMEOUT => 60,
            RequestOptions::HEADERS => [
                'Content-Type' => 'text/xml;'
            ],
        ]);
        $response = $client->request('POST', $url, [
            RequestOptions::BODY => $body
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

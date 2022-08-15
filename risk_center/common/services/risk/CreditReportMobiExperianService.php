<?php

namespace common\services\risk;

use common\helpers\CommonHelper;
use common\models\enum\Gender;
use common\models\RiskOrder;
use common\models\user\UserCreditReportBangaloreExperian;
use common\models\user\UserCreditReportExperian;
use common\models\user\UserCreditReportMobiExperian;
use common\models\user\UserCreditReportShanyunExperian;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Yii;


class CreditReportMobiExperianService extends BaseDataService
{

    // 接口请求需要白名单
    private $url = YII_ENV_PROD ? 'https://api.peaksecurity.in/api/v1/credit/get' : 'https://sandbox.peaksecurity.in/api/v1/credit/get';

    /**
     * @var UserCreditReportMobiExperian
     */
    private $experian;
    private $appId;
    private $appSecretKey;

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->appId = Yii::$app->params['MobiExperian']['appId'];
        $this->appSecretKey = Yii::$app->params['MobiExperian']['appSecretKey'];
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
        if(isset($this->experian->status) && $this->experian->status == UserCreditReportMobiExperian::STATUS_SUCCESS){
            return true;
        }

        if(isset($this->order->userCreditReportShanyunExperian->status) && $this->order->userCreditReportShanyunExperian->status == UserCreditReportShanyunExperian::STATUS_SUCCESS){
            return true;
        }

        if(isset($this->order->userCreditReportExperian->status) && $this->order->userCreditReportExperian->status == UserCreditReportExperian::STATUS_SUCCESS){
            return true;
        }

        if(isset($this->order->userCreditReportBangaloreExperian->status) && $this->order->userCreditReportBangaloreExperian->status == UserCreditReportBangaloreExperian::STATUS_SUCCESS){
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

        $this->order->user_mobi_experian_id = $this->experian->id;
        if(!$this->order->save()){
            return false;
        }

        $name = CommonHelper::getNameConversion($this->infoUser->pan_verify_name);

        $params = [
            'func_type' => 3,
            'need_origin_data' => 1,
            'pan' => $this->infoUser->pan_code,
            'first_name' => $name['first_name'],
            'last_name' => $name['last_name'],
            'mobile' => $this->infoUser->phone,
            'gender' => Gender::$mapForKudosExperian[$this->infoUser->gender],
            'request_time' => time(),
            'date_of_birth' => $this->infoUser->pan_birthday,
            'city' => $this->infoUser->residential_city,
            'state' => $this->infoUser->residential_address,
            'pin_code' => $this->infoUser->aadhaar_pin_code,
            'Address' => $this->infoUser->residential_detail_address,
            "real_time_secret" => "ta*9K082we45-4"
        ];

        try {
            $response = $this->postData($params, $this->url);
        } catch (\Exception $exception) {
            return false;
        }
        $res = $response->getBody()->getContents();
        Yii::info(['risk_order_id' => $this->order->id,'params' => json_encode($params), 'response' => $res],'MobiExperian');

        $data = json_decode($res, true);
        $xml_result = $data['data']['result'];

        try {
            if(isset($data['data']['status_code']) && in_array($data['data']['status_code'], [1,2])) {
                $result = simplexml_load_string($xml_result);
                $result = $result->children('http://schemas.xmlsoap.org/soap/envelope/')->Body;
                $result = $result->children('urn:cbv2');
                $result = json_decode(json_encode($result), true);
                $result = json_decode(json_encode(simplexml_load_string($result['processResponse']['out'])), true);

                if (isset($result['Header']['SystemCode']) && $result['Header']['SystemCode'] == 0) {
                    $this->experian->data       = $xml_result;
                    $this->experian->status     = UserCreditReportMobiExperian::STATUS_SUCCESS;
                    $this->experian->score      = $result['SCORE']['BureauScore'] ?? 0;
                    $this->experian->is_request = 1;
                    $this->experian->query_time = time();
                    if (!empty($result['UserMessage']['UserMessageText']) && $result['UserMessage']['UserMessageText'] == 'Normal Response') {
                        $this->experian->data_status = UserCreditReportMobiExperian::STATUS_SUCCESS;
                    }

                    if ($this->experian->save()) {
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
        if(is_null($this->experian))
        {
            $this->experian = $this->order->userCreditReportMobiExperian;
            if(is_null($this->experian)){
                $this->experian = new UserCreditReportMobiExperian();
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

    public function postData($params, $url)
    {
        $client = new Client([
            RequestOptions::TIMEOUT => 60,
            RequestOptions::HEADERS => [
                'x-m-app-id' => $this->appId,
                'x-m-signature' => md5(json_encode($params).$this->appSecretKey.$this->appId),
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

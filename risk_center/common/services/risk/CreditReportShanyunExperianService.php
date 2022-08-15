<?php

namespace common\services\risk;

use Carbon\Carbon;
use common\helpers\CommonHelper;
use common\models\enum\Gender;
use common\models\RiskOrder;
use common\models\user\UserCreditReportBangaloreExperian;
use common\models\user\UserCreditReportExperian;
use common\models\user\UserCreditReportMobiExperian;
use common\models\user\UserCreditReportShanyunExperian;
use common\services\message\WeWorkService;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Yii;


class CreditReportShanyunExperianService extends BaseDataService
{

    private $url = YII_ENV_PROD ? 'https://api.riskcloud.in/v3/searchExperainCredit' : 'https://api.riskcloud.in/v3/searchExperainCredit';

    /**
     * @var UserCreditReportShanyunExperian
     */
    private $experian;
    private $appId;
    private $appSecretKey;

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->appId = Yii::$app->params['ShanyunExperian']['appId'];
        $this->appSecretKey = Yii::$app->params['ShanyunExperian']['appSecretKey'];
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
        if(isset($this->experian->status) && $this->experian->status == UserCreditReportShanyunExperian::STATUS_SUCCESS){
            return true;
        }

        if(isset($this->order->userCreditReportExperian->status) && $this->order->userCreditReportExperian->status == UserCreditReportExperian::STATUS_SUCCESS){
            return true;
        }

        if(isset($this->order->userCreditReportMobiExperian->status)
            && $this->order->userCreditReportMobiExperian->status == UserCreditReportMobiExperian::STATUS_SUCCESS){
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

        $this->order->user_shanyun_experian_id = $this->experian->id;
        if(!$this->order->save()){
            return false;
        }

        $name = CommonHelper::getNameConversion($this->infoUser->pan_verify_name);

        $params = [
            'uid'         => uniqid($this->infoUser->user_id.'_'),
            'dataFormat'  => 'json',
            'pan'         => $this->infoUser->pan_code,
            'firstName'   => $name['first_name'],
            'lastName'    => empty($name['last_name']) ? 'kumar' : $name['last_name'],
            'mobile'      => $this->infoUser->phone,
            'gender'      => Gender::$mapForKudosExperian[$this->infoUser->gender],
            'dateOfBirth' => Carbon::rawCreateFromFormat('Y-m-d', $this->infoUser->pan_birthday)->format('d/m/Y'),
        ];

        $params['appId']     = $this->appId;
        $params['timestamp'] = time() * 1000;
        $params['nonce']     = uniqid();
        $params['sign']      = $this->getSign($params, $this->appSecretKey);

        try {
            $response = $this->postData($params, $this->url);
        } catch (\Exception $exception) {
            return false;
        }
        $res = $response->getBody()->getContents();
        Yii::info(['risk_order_id' => $this->order->id,'params' => json_encode($params), 'response' => $res],'ShanyunExperian');

        $data = json_decode($res, true);
        if($data['statusCode'] != 0){
            $service = new WeWorkService();
            $msg = 'ShanyunExperian 调用失败，params:'.json_encode($params).',response:'.$res;
            $service->send($msg);
            return false;
        }

        if($data['data']['errorCode'] == 11000 || $data['data']['errorCode'] == 11006){
            $this->experian->status     = UserCreditReportShanyunExperian::STATUS_SUCCESS;
            $this->experian->is_request = 1;
            $this->experian->query_time = time();
            $this->experian->data_status = UserCreditReportShanyunExperian::STATUS_SUCCESS;

            if($data['data']['errorCode'] == 11000){
                $result = $data['data']['reportData']['INProfileResponse'];
                $this->experian->data  = json_encode($result);
                $this->experian->score = $result['SCORE']['BureauScore'] ?? 0;
            }

            if($this->experian->save()){
                return true;
            }
        }

        return false;
    }


    private function initData()
    {
        if(is_null($this->experian))
        {
            $this->experian = $this->order->userCreditReportShanyunExperian;
            if(is_null($this->experian)){
                $this->experian = new UserCreditReportShanyunExperian();
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
        ]);
        $response = $client->request('POST', $url, [
            RequestOptions::JSON => $params
        ]);
        return $response;
    }

    public function getSign($postData, $appSecret){
        $postData['appSecret'] = $appSecret;
        ksort($postData);
        $temp = [];
        foreach ($postData as $key => $value) {
            if (!is_array($value)) {
                $temp[] = $key;
                $temp[] = $value;
            }
        }
        return sha1(implode("", $temp));
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

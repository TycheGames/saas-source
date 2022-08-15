<?php
/**
 * User : Yinghao
 * Email: yhzs15155@gmail.com
 * Date : 2020-01-02
 * Time : 18:00
 */

namespace console\controllers;

use Carbon\Carbon;
use common\helpers\ArrayToXml;
use common\helpers\RedisQueue;
use common\helpers\Util;
use common\models\enum\Gender;
use common\models\InfoDevice;
use common\models\InfoOrder;
use common\models\order\EsUserLoanOrder;
use common\models\risk\RiskResultSnapshot;
use common\models\RiskOrder;
use common\models\user\UserCreditReportBangaloreExperian;
use common\models\user\UserCreditReportExperian;
use common\services\LogStashService;
use common\services\order\PushOrderRiskService;
use frontend\models\risk\ApplyForm;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Yii;
use yii\console\ExitCode;

class TestController extends BaseController
{
    public function actionTest()
    {




        die;
        $a = [
            'user_basic_info' => [
                'phone' => '1352232183',
                'pan_code' => 'abc123213',
                'aadhaar_md5' => '1',
                'filled_name' => 'allen'
            ]
        ];

        $b = new ApplyForm();
        $b->load($a, '');

        var_dump($b);
        var_dump($b->user_basic_info);

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

    public function actionShanyunExperian(){
        if(!$this->lock()){
            return;
        }

        $url = 'https://api.riskcloud.in/v3/searchExperainCredit';
        $appId = 'kRd118En7S633Qi7';
        $key = 'PTj69nRFV2643715';
        $pan_code = 'EJXPR7556A';
        $firstName = 'PRIYANKA';
        $lastName = 'RAJANNA';
        $phone = '9900753059';

        $now = time();
        $params = [
            'uid'         => uniqid('test_'),
            'dataFormat'  => 'json',
            'pan'         => $pan_code,
            'firstName'   => $firstName,
            'lastName'    => empty($lastName) ? 'kumar' : $lastName,
            'mobile'      => $phone,
            'gender'      => Gender::$mapForKudosExperian[0],
            'dateOfBirth' => '19/04/1974',
        ];

        $params['appId']     = $appId;
        $params['timestamp'] = time() * 1000;
        $params['nonce']     = uniqid();
        $params['sign']      = $this->getSign($params, $key);

        $client = new Client([
            RequestOptions::TIMEOUT => 60,
        ]);
        $response = $client->request('POST', $url, [
            RequestOptions::JSON => $params
        ]);

        $data = json_decode($response->getBody()->getContents(), true);
        file_put_contents('response_'.$now.'.txt', json_encode($data));

        if($data['statusCode'] == 0 && $data['data']['errorCode'] == 11000){
            $result = $data['data']['reportData']['INProfileResponse'];
            file_put_contents('experian_'.$now.'.txt', json_encode($result));
        }

        $this->printMessage('脚本结束');
    }

    public function actionMobiExperian(){
        if(!$this->lock()){
            return;
        }

        $url = 'https://api.peaksecurity.in/api/v1/credit/get';
        $appId = 'ruzhongzhi@repegon.onaliyun.com';
        $key = 'X<yvg>_-apQ%3PCuy)';
        $pan_code = 'EJXPR7556A';
        $firstName = 'PRIYANKA';
        $lastName = 'RAJANNA';
        $phone = '9900753059';

        $now = time();
        $params = [
            'func_type' => 3,
            'need_origin_data' => 1,
            'pan' => $pan_code,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'mobile' => $phone,
            'gender' => Gender::$mapForKudosExperian[0],
            'request_time' => $now,
            'date_of_birth' => '1997-04-19',
            'city' => 'Mysore',
            'state' => 'Karnataka',
            'pin_code' => '571189',
            'Address' => 'CBT COLONY KASABA HOBLI HUNSUR TALUK MYSORE DISTRICT KARNATAKA',
            "real_time_secret" => "ta*9K082we45-4"
        ];

        $client = new Client([
            RequestOptions::TIMEOUT => 60,
            RequestOptions::HEADERS => [
                'x-m-app-id' => $appId,
                'x-m-signature' => md5(json_encode($params).$key.$appId),
            ],
        ]);
        $response = $client->request('POST', $url, [
            RequestOptions::JSON => $params
        ]);

        $res = json_decode($response->getBody()->getContents(), true);
        file_put_contents('response_'.$now.'.txt', json_encode($res));

        $xml_result = $res['data']['result'];

        if(isset($res['data']['status_code']) && $res['data']['status_code'] == 1){
            $result = simplexml_load_string($xml_result);
            $result = $result->children('http://schemas.xmlsoap.org/soap/envelope/')->Body;
            $result = $result->children('urn:cbv2');
            $result = json_decode(json_encode($result), true);
            $result = json_decode(json_encode(simplexml_load_string($result['processResponse']['out'])), true);

            file_put_contents('experian_'.$now.'.txt', json_encode($result));
        }

        $this->printMessage('脚本结束');
    }

    public function actionPushOrderStatus(){
        if(!$this->lock()){
            return;
        }

        while (true){
            $riskOrderId = RedisQueue::pop([RedisQueue::CREDIT_NOT_PUSH_LIST]);
            if(empty($riskOrderId)){
                return;
            }

            $riskOrder = RiskOrder::findOne($riskOrderId);
            if(empty($riskOrder)){
                $this->printMessage('risk_order_id:'.$riskOrderId.'订单不存在');
                continue;
            }

            if($riskOrder->is_push == RiskOrder::IS_PUSH_YES){
                $this->printMessage('risk_order_id:'.$riskOrderId.'订单已推送');
                continue;
            }

            $risk = RiskResultSnapshot::find()
                ->where(['order_id' => $riskOrder->order_id,
                         'app_name' => $riskOrder->app_name,
                         'user_id' => $riskOrder->user_id])
                ->asArray()->all();
            $params = [];
            foreach ($risk as $v){
                if($riskOrder->type == RiskOrder::TYPE_AUTO_CHECK){
                    if($v['tree_code'] == 'T102'){
                        $key = 'risk';
                    }elseif($v['tree_code'] == 'C101'){
                        $key = 'amount';
                    }else{
                        continue;
                    }
                }else{
                    if($v['tree_code'] == 'RepayC101'){
                        $key = 'credit';
                    }else{
                        continue;
                    }
                }

                $params[$key] = $v;
            }

            $pushService = new PushOrderRiskService($riskOrder->infoOrder->product_source);
            $res = $pushService->pushOrderRisk($riskOrder->order_id, $params);
            if(isset($res['code']) && $res['code'] == 0){
                $riskOrder->is_push = RiskOrder::IS_PUSH_YES;
                $riskOrder->save();
                $this->printMessage('risk_order_id:'.$riskOrderId.'风控订单回调成功');
            }else{
                $this->printMessage('risk_order_id:'.$riskOrderId.'风控订单回调失败，需手动处理');
            }
        }

    }

    public function actionExperian(){
        $url = 'https://connect.experian.in/nextgen-ind-pds-webservices-cbv2/endpoint';

        $username = Yii::$app->params['BangaloreExperian']['username'];
        $password = Yii::$app->params['BangaloreExperian']['password'];

        $params = [
            'Identification' => [
                'XMLUser' => $username,
                'XMLPassword' => $password,
            ],
            'Application' => [
                'EnquiryReason' => '02',
                'FinancePurpose' => '99',
                'AmountFinanced' => '170000',
                'DurationOfAgreement' => '7',
                'ScoreFlag' => '1'
            ],
            'Applicant' => [
                'Surname' => 'PARIDA',
                'FirstName' => 'SUJIT',
                'GenderCode' => '1',
                'IncomeTaxPAN' => 'BNDPP4306B',
                'DateOfBirth' => '19920822',
                'MobilePhone' => '8908622420',
            ],
            'Address' => [
                'FlatNoPlotNoHouseNo' => 'Mono steel colony New dudhai',
                'City' => 'Anjar',
                'State' => '24',
                'PinCode' => '370115',
            ],
            'AdditionalAddressFlag' => [
                'Flag' => 'N'
            ]
        ];

        $body = ArrayToXml::convert($params, 'INProfileRequest', true, 'utf-8');
        $client = new Client([
            RequestOptions::TIMEOUT => 60,
            RequestOptions::HEADERS => [
                'Content-Type' => 'text/xml;'
            ],
        ]);
        $response = $client->request('POST', $url, [
            'body' => $body
        ]);

        var_dump($response->getStatusCode());
        file_put_contents('experian.txt', $response->getBody()->getContents());
    }


    public function actionTestEs()
    {
        EsUserLoanOrder::createIndex();
    }

    public function actionPushEs($maxId = 0){
        if(!$this->lock()){
            return;
        }
        $query = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoDevice::tableName().' as d', 'o.app_name=d.app_name and o.user_id=d.user_id and o.order_id=d.order_id')
            ->select(['o.id', 'o.app_name', 'o.user_id', 'o.order_id', 'o.order_time', 'd.latitude', 'd.longitude'])
            ->orderBy(['o.id' => SORT_ASC])
            ->limit(1000);

        $cloneQuery = clone $query;
        $data = $cloneQuery->where(['>', 'o.id', $maxId])->asArray()->all();
        while ($data){
            foreach ($data as $v){
                $maxId = $v['id'];
                $this->printMessage('maxId:'.$maxId);
                if (empty($v['latitude']) || empty($v['longitude'])) {
                    continue;
                }

                $esOrder = new EsUserLoanOrder();
                $esOrder->app_name = $v['app_name'];
                $esOrder->user_id = $v['user_id'];
                $esOrder->order_id = $v['order_id'];
                $esOrder->order_time = Carbon::createFromTimestamp($v['order_time'])->toIso8601ZuluString();
                $esOrder->location = [
                    'lat' => $v['latitude'],    //纬度
                    'lon' => $v['longitude'],    //经度
                ];
                $primaryKey = $esOrder->app_name . '_' . $esOrder->user_id . '_' . $esOrder->order_id;
                $esOrder->setPrimaryKey($primaryKey);
                $esOrder->save();
            }

            $cloneQuery = clone $query;
            $data = $cloneQuery->where(['>', 'o.id', $maxId])->asArray()->all();
        }

        $this->printMessage('脚本结束');
    }

    public function actionGetEs(){
        $time = 1596718801;
        $latitude = '21.6858043';
        $longitude = '87.1679981';
        $orderNum = EsUserLoanOrder::find()
            ->query([
                'bool' => [
                    'must' => [
                        'range' => [
                            'order_time' => [
                                'gte' => Carbon::createFromTimestamp($time)->subDays(7)->toIso8601ZuluString(),
                                'lte' => Carbon::createFromTimestamp($time)->toIso8601ZuluString()
                            ]
                        ]
                    ],
                    'filter' => [
                        'geo_distance' => [
                            'distance' => '500m',
                            'location' => [
                                'lat' => $latitude,
                                'lon' => $longitude,
                            ]
                        ]
                    ]
                ]
            ])
            ->count();

        var_dump($orderNum);
    }



}// END CLASS
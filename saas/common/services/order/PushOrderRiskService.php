<?php


namespace common\services\order;


use callcenter\models\loan_collection\LoanCollectionRecord;
use common\helpers\CommonHelper;
use common\models\ClientInfoLog;
use common\models\enum\mg_user_content\UserContentType;
use common\models\order\UserLoanOrder;
use common\models\order\UserLoanOrderExternal;
use common\models\user\LoanPerson;
use common\models\user\MgUserCallReports;
use common\models\user\MgUserInstalledApps;
use common\models\user\MgUserMobileContacts;
use common\models\user\MgUserMobilePhotos;
use common\models\user\MgUserMobileSms;
use common\models\user\UserCreditReportFrLiveness;
use common\models\user\UserCreditReportFrVerify;
use common\models\user\UserPhotoUrl;
use common\models\user\UserPictureMetadataLog;
use common\models\user\UserRegisterInfo;
use common\models\user\UserSmsDataPushTime;
use common\services\FileStorageService;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use yii\base\BaseObject;
use Yii;

class PushOrderRiskService extends BaseObject
{
    private $baseUrl                  = '';
    private $applyUri                 = 'risk/apply';
    private $uploadContentsNewUri     = 'risk/upload-contents-new';
    private $loginLogUri              = 'risk/login-log';
    private $orderRejectUri           = 'risk/order-reject';
    private $orderLoanSuccessUri      = 'risk/order-loan-success';
    private $orderRepaymentSuccessUri = 'risk/order-repayment-success';
    private $orderOverdueUri          = 'risk/order-overdue';
    private $riskBlackUri             = 'risk/risk-black';
    private $collectionSuggestionUri  = 'risk/collection-suggestion';
    private $loanCollectionRecordUri  = 'risk/loan-collection-record';
    private $remindOrderUri           = 'risk/remind-order';
    private $remindLogUri             = 'risk/remind-log';

    public function __construct($config = [])
    {
        parent::__construct($config);

        $this->baseUrl = Yii::$app->params['RiskCenter']['base_url'];
    }

    /**
     * @param UserLoanOrder $order
     * @return array|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \yii\mongodb\Exception
     */
    public function pushOrder(UserLoanOrder $order){
        $orderExtraService = new OrderExtraService($order);
        $workInfo          = $orderExtraService->getUserWorkInfo();
        $basicInfo         = $orderExtraService->getUserBasicInfo();
        $contact           = $orderExtraService->getUserContact();
        $ocrPanReport      = $orderExtraService->getUserOcrPanReport();
        $PanVerReport      = $orderExtraService->getUserVerifyPanReport();
        $ocrAadhaarReport  = $orderExtraService->getUserOcrAadhaarReport();
        $questionReport    = $orderExtraService->getUserQuestionReport();
        $frLivenessReport  = $orderExtraService->getUserFrReport();
        $frCompareReport   = $orderExtraService->getUserFrCompareReport();

        $pictureMetadata = $this->getPictureMetadata($order);
        $register = UserRegisterInfo::find()->where(['user_id' => $order->user_id])->asArray()->one();

        $params = [
            'user_id'          => $order->user_id,
            'order_id'         => $order->id,
            'app_name'         => $order->clientInfoLog->package_name,
            'picture_metadata' => [
                'number30'                     => $pictureMetadata['number30'],
                'number90'                     => $pictureMetadata['number90'],
                'number_all'                   => $pictureMetadata['number_all'],
                'metadata_earliest'            => $pictureMetadata['metadata_earliest'],
                'metadata_latest'              => $pictureMetadata['metadata_latest'],
                'metadata_earliest_positioned' => $pictureMetadata['metadata_earliest_positioned'],
                'metadata_latest_positioned'   => $pictureMetadata['metadata_latest_positioned'],
                'gps_in_india_number'          => $pictureMetadata['gps_in_india_number'],
                'gps_notin_india_number'       => $pictureMetadata['gps_notin_india_number'],
                'gps_null_number'              => $pictureMetadata['gps_null_number'],
            ],
            'client_info'      => [
                'client_type'   => $order->clientInfoLog->client_type,
                'os_version'    => $order->clientInfoLog->os_version,
                'app_version'   => $order->clientInfoLog->app_version,
                'device_name'   => $order->clientInfoLog->device_name,
                'app_market'    => $order->clientInfoLog->app_market,
                'device_id'     => $order->clientInfoLog->device_id,
                'brand_name'    => $order->clientInfoLog->brand_name,
                'bundle_id'     => $order->clientInfoLog->bundle_id,
                'latitude'      => $order->clientInfoLog->latitude,
                'longitude'     => $order->clientInfoLog->longitude,
                'szlm_query_id' => $order->clientInfoLog->szlm_query_id,
                'screen_width'  => $order->clientInfoLog->screen_width,
                'screen_height' => $order->clientInfoLog->screen_height,
                'ip'            => $order->clientInfoLog->ip,
                'client_time'   => $order->clientInfoLog->client_time
            ],
            'order_info'       => [
                'is_first'          => $order->is_first == UserLoanOrder::FIRST_LOAN_IS ? 'y' : 'n',
                'is_all_first'      => $order->is_all_first == UserLoanOrder::FIRST_LOAN_IS ? 'y' : 'n',
                'periods'           => $order->periods,
                'is_external_first' => 'n',
                'is_external'       => $order->is_export == UserLoanOrder::IS_EXPORT_YES ? 'y' : 'n',
                'external_app_name' => $order->is_export == UserLoanOrder::IS_EXPORT_YES ? (explode('_', $order->clientInfoLog->app_market)[1] ?? '') : '',
                'day_rate'          => $order->day_rate,
                'overdue_rate'      => $order->overdue_rate,
                'cost_rate'         => $order->cost_rate,
                'order_time'        => $order->order_time,
                'principal'         => $order->amount,
                'loan_amount'       => $order->amount - $order->cost_fee,
                'product_name'      => $order->productSetting->product_name,
                'product_id'        => $order->productSetting->id,
                'product_source'    => 'saas',
            ],
            'user_basic_info'  => [
                'phone'                      => $order->loanPerson->phone,
                'pan_code'                   => $order->loanPerson->pan_code,
                'pan_ocr_code'               => $PanVerReport->pan_ocr,
                'aadhaar_md5'                => $order->loanPerson->aadhaar_md5,
                'gender'                     => $order->loanPerson->gender,
                'email_address'              => $basicInfo->email_address,
                'filled_name'                => $basicInfo->full_name,
                'pan_ocr_name'               => $ocrPanReport->full_name,
                'aadhaar_ocr_name'           => $ocrAadhaarReport->full_name,
                'pan_verify_name'            => $order->loanPerson->name,
                'filled_birthday'            => $basicInfo->birthday,
                'pan_birthday'               => $order->loanPerson->birthday,
                'aadhaar_birthday'           => $ocrAadhaarReport->date_info,
                'education_level'            => $workInfo->educated,
                'occupation'                 => $workInfo->industry,
                'residential_detail_address' => $workInfo->residential_detail_address,
                'residential_address'        => $workInfo->residential_address1,
                'residential_city'           => $workInfo->residential_address2,
                'aadhaar_address'            => $ocrAadhaarReport->address,
                'aadhaar_ocr_pin_code'       => $ocrAadhaarReport->pin,
                'aadhaar_filled_city'        => $basicInfo->aadhaar_address2,
                'aadhaar_pin_code'           => $basicInfo->aadhaar_pin_code,
                'monthly_salary'             => $workInfo->monthly_salary,
                'contact1_mobile_number'     => $contact->phone,
                'contact2_mobile_number'     => $contact->other_phone,
                'fr_liveness_source'         => $frLivenessReport->type == UserCreditReportFrLiveness::SOURCE_ACCUAUTH ? 'accu_auth' : 'advance_ai',
                'fr_liveness_score'          => $frLivenessReport->score,
                'fr_verify_source'           => $frCompareReport->type == UserCreditReportFrVerify::SOURCE_ACCUAUTH ? 'accu_auth' : 'advance_ai',
                'fr_verify_type'             => $frCompareReport->report_type == UserCreditReportFrVerify::TYPE_FR_COMPARE_PAN ? 'pan' : 'fr',
                'fr_verify_score'            => $frCompareReport->score,
                'language_need_check'        => !empty($questionReport) ? 'y' : 'n',
                'language_correct_number'    => $questionReport->correct_num ?? 0,
                'language_time'              => !empty($questionReport) ? ($questionReport->submit_time - $questionReport->enter_time) : 0,
                'register_time'              => $order->loanPerson->created_at,
                'app_market'                 => $register['appMarket'] ?? '',
                'media_source'               => $register['media_source'] ?? '',
            ]
        ];

        $result = $this->postData($this->applyUri, $params);
        return $result;
    }

    /**
     * @param $date
     * @param $user_id
     * @param $db
     * @return int[]
     */
    private function getPictureMetadataGps($date, $user_id, $db){
        $data = MgUserMobilePhotos::find()
            ->select(['GPSLongitude', 'GPSLatitude', 'GPSLongitudeRef', 'GPSLatitudeRef'])
            ->where([
                'user_id' => $user_id,
                'date' => $date
            ])
            ->asArray()
            ->all($db);
        $InIndiaCount = 0;
        $NotInIndiaCount = 0;
        $count = 0;
        foreach ($data as $v){
            if(!empty($v['GPSLongitude'])
                && !empty($v['GPSLatitude'])
                && !empty($v['GPSLongitudeRef'])
                && !empty($v['GPSLatitudeRef'])
                && in_array($v['GPSLongitudeRef'], ['W', 'E'])
                && in_array($v['GPSLatitudeRef'], ['S', 'N'])
            ){
                try {
                    $long = CommonHelper::GetDecimalFromDms($v['GPSLongitude'], $v['GPSLongitudeRef']);
                    $lat = CommonHelper::GetDecimalFromDms($v['GPSLatitude'], $v['GPSLatitudeRef']);
                    if($long < 68.7 || $long > 97.25 || $lat < 8.4 || $lat > 37.6){
                        //定位地址不在印度
                        $NotInIndiaCount++;
                    }else{
                        //定位地址在印度
                        $InIndiaCount++;
                    }
                } catch (\Exception $e){
                    //定位地址为空
                    $count++;
                }
            }else{
                //定位地址为空
                $count++;
            }
        }

        $pictureMetadata = [
            'gps_in_india_number' => $InIndiaCount,
            'gps_notin_india_number' => $NotInIndiaCount,
            'gps_null_number' => $count
        ];

        return $pictureMetadata;
    }

    /**
     * @param UserLoanOrder $order
     * @return mixed
     * @throws \yii\mongodb\Exception
     */
    private function getPictureMetadata(UserLoanOrder $order){
        if($order->is_export == UserLoanOrder::IS_EXPORT_YES){
            $pictureMetadata = UserPictureMetadataLog::find()
                ->where(['order_uuid' => $order->order_uuid])
                ->orderBy(['id' => SORT_DESC])
                ->asArray()
                ->one();
            $db = Yii::$app->db_loan;
            $orderExternal = UserLoanOrderExternal::userExternalOrder($order->order_uuid);
            $user_id = intval($orderExternal->user_id);
            $type = 'loan_s3';
        }else{
            $db = Yii::$app->db;
            $user_id = intval($order->user_id);
            $type = 's3';
        }

        /** @var $userPhoto UserPhotoUrl */
        $userPhoto = UserPhotoUrl::find()
            ->where(['user_id' => $user_id])
            ->orderBy(['id' => SORT_DESC])
            ->one($db);

        if(empty($userPhoto)){
            if(empty($pictureMetadata)){
                $pictureMetadata['number30'] = 0;
                $pictureMetadata['number90'] = 0;
                $pictureMetadata['number_all'] = 0;
                $pictureMetadata['metadata_earliest'] = '';
                $pictureMetadata['metadata_latest'] = '';
                $pictureMetadata['metadata_earliest_positioned'] = '';
                $pictureMetadata['metadata_latest_positioned'] = '';
            }

            $pictureMetadata['gps_in_india_number'] = 0;
            $pictureMetadata['gps_notin_india_number'] = 0;
            $pictureMetadata['gps_null_number'] = 0;
            return $pictureMetadata;
        }

        $service = new FileStorageService();
        $url = $service->getSignedUrl($userPhoto->url, 3600, $type);
        $data = file_get_contents($url);
        $data = json_decode($data, true)['content'];

        $count30 = 0;
        $count90 = 0;
        $countAll = 0;
        $metadata_earliest = [];
        $metadata_latest = [];
        $metadata_earliest_positioned = [];
        $metadata_latest_positioned = [];
        $InIndiaCount = 0;
        $NotInIndiaCount = 0;
        $count = 0;
        $begin_time30 = $order->order_time - 30 * 86400;
        $begin_time90 = $order->order_time - 90 * 86400;
        foreach ($data as $v){
            $countAll++;
            if($v['AlbumFileLastModifiedTime']/1000 >= $begin_time30){
                $count30++;
            }

            if($v['AlbumFileLastModifiedTime']/1000 >= $begin_time90){
                $count90++;
            }

            if(empty($metadata_earliest) || $v['AlbumFileLastModifiedTime'] < $metadata_earliest['AlbumFileLastModifiedTime']){
                $metadata_earliest = $v;
            }

            if(empty($metadata_latest) || $v['AlbumFileLastModifiedTime'] > $metadata_latest['AlbumFileLastModifiedTime']){
                $metadata_latest = $v;
            }

            if(!empty($v['GPSLongitude'])
                && !empty($v['GPSLatitude'])
                && !empty($v['GPSLongitudeRef'])
                && !empty($v['GPSLatitudeRef'])
                && in_array($v['GPSLongitudeRef'], ['W', 'E'])
                && in_array($v['GPSLatitudeRef'], ['S', 'N'])
            ){
                if(empty($metadata_earliest_positioned) || $v['AlbumFileLastModifiedTime'] < $metadata_earliest_positioned['AlbumFileLastModifiedTime']){
                    $metadata_earliest_positioned = $v;
                }

                if(empty($metadata_latest_positioned) || $v['AlbumFileLastModifiedTime'] > $metadata_latest_positioned['AlbumFileLastModifiedTime']){
                    $metadata_latest_positioned = $v;
                }

                try {
                    $long = CommonHelper::GetDecimalFromDms($v['GPSLongitude'], $v['GPSLongitudeRef']);
                    $lat = CommonHelper::GetDecimalFromDms($v['GPSLatitude'], $v['GPSLatitudeRef']);
                    if($long < 68.7 || $long > 97.25 || $lat < 8.4 || $lat > 37.6){
                        //定位地址不在印度
                        $NotInIndiaCount++;
                    }else{
                        //定位地址在印度
                        $InIndiaCount++;
                    }
                } catch (\Exception $e){
                    //定位地址为空
                    $count++;
                }
            }else{
                //定位地址为空
                $count++;
            }
        }

        if(empty($pictureMetadata)) {
            $pictureMetadata['number30']                     = $count30;
            $pictureMetadata['number90']                     = $count90;
            $pictureMetadata['number_all']                   = $countAll;
            $pictureMetadata['metadata_earliest']            = !empty($metadata_earliest) ? json_encode($metadata_earliest) : '';
            $pictureMetadata['metadata_latest']              = !empty($metadata_latest) ? json_encode($metadata_latest) : '';
            $pictureMetadata['metadata_earliest_positioned'] = !empty($metadata_earliest_positioned) ? json_encode($metadata_earliest_positioned) : '';
            $pictureMetadata['metadata_latest_positioned']   = !empty($metadata_latest_positioned) ? json_encode($metadata_latest_positioned) : '';
        }
        $pictureMetadata['gps_in_india_number']    = $InIndiaCount;
        $pictureMetadata['gps_notin_india_number'] = $NotInIndiaCount;
        $pictureMetadata['gps_null_number']        = $count;

        return $pictureMetadata;
    }

    /**
     * @param UserLoanOrder $order
     * @param UserContentType $contentType
     * @return array|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function uploadContentsNew($user_id, UserContentType $contentType){
        $loanPerson = LoanPerson::findOne($user_id);
        $clientInfoLog = ClientInfoLog::find()->where(['event' => ClientInfoLog::EVENT_LOGIN, 'user_id' => $user_id])->orderBy(['id' => SORT_DESC])->one();
        $user_id = intval($user_id);
        switch ($contentType->getValue()){
            case UserContentType::SMS()->getValue():
                $model = UserSmsDataPushTime::findOne(['user_id' => $user_id]);
                if(empty($model)){
                    $model = new UserSmsDataPushTime();
                    $model->user_id = $user_id;
                    $data = MgUserMobileSms::find()->where(['user_id' => $user_id])->asArray()->all();
                }else{
                    $data = MgUserMobileSms::find()->where(['user_id' => $user_id])->andWhere(['>=', 'created_at', $model->last_push_time])->asArray()->all();
                }
                break;
            case UserContentType::CALL_RECORDS()->getValue():
                $data = MgUserCallReports::find()->where(['user_id' => $user_id])->asArray()->all();
                break;
            case UserContentType::CONTACT()->getValue():
                $data = MgUserMobileContacts::find()->where(['user_id' => $user_id])->asArray()->all();
                break;
            case UserContentType::APP_LIST()->getValue():
                $data = MgUserInstalledApps::find()->select(['user_id', 'addeds'])->where(['user_id' => $user_id])->asArray()->all();
                break;
        }

        foreach ($data as &$v){
            unset($v['user_id']);
            $v['user_phone'] = intval($loanPerson->phone);
            $v['pan_code'] = $loanPerson->pan_code;
        }

        $params = [
            'type' => $contentType->getValue(),
            'app_name' => $clientInfoLog->package_name,
            'data' => json_encode($data),
        ];
        $result = $this->postData($this->uploadContentsNewUri, $params);

        if($contentType->getValue() == UserContentType::SMS()->getValue() && $result['code'] == 0){
            $model->last_push_time = strtotime('today');
            $model->save();
        }
        return $result;
    }

    /**
     * @param ClientInfoLog $clientInfoLog
     * @return array|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function pushLoginLog(ClientInfoLog $clientInfoLog){
        $loanPerson = LoanPerson::findOne($clientInfoLog['user_id']);
        $params = [
            'app_name'      => $clientInfoLog['package_name'],
            'request_id'    => $clientInfoLog['id'],
            'phone'         => $loanPerson['phone'],
            'pan_code'      => $loanPerson['pan_code'],
            'user_id'       => $clientInfoLog['user_id'],
            'client_type'   => $clientInfoLog['client_type'] ?? '',
            'os_version'    => $clientInfoLog['os_version'] ?? '',
            'app_version'   => $clientInfoLog['app_version'] ?? '',
            'device_name'   => $clientInfoLog['device_name'] ?? '',
            'app_market'    => $clientInfoLog['app_market'] ?? '',
            'device_id'     => $clientInfoLog['device_id'] ?? '',
            'brand_name'    => $clientInfoLog['brand_name'] ?? '',
            'bundle_id'     => $clientInfoLog['bundle_id'] ?? '',
            'latitude'      => $clientInfoLog['latitude'] ?? '',
            'longitude'     => $clientInfoLog['longitude'] ?? '',
            'szlm_query_id' => $clientInfoLog['szlm_query_id'] ?? '',
            'screen_width'  => $clientInfoLog['screen_width'] ?? 0,
            'screen_height' => $clientInfoLog['screen_height'] ?? 0,
            'ip'            => $clientInfoLog['ip'] ?? '',
            'client_time'   => $clientInfoLog['client_time'],
            'event_time'    => $clientInfoLog['created_at'],
        ];
        $result = $this->postData($this->loginLogUri, $params);
        return $result;
    }

    /**
     * @param $params
     * @return array|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function pushOrderReject($params){
        $result = $this->postData($this->orderRejectUri, $params);
        return $result;
    }

    /**
     * @param $params
     * @return array|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function pushOrderLoanSuccess($params){
        $result = $this->postData($this->orderLoanSuccessUri, $params);
        return $result;
    }

    /**
     * @param $params
     * @return array|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function pushOrderRepaymentSuccess($params){
        $result = $this->postData($this->orderRepaymentSuccessUri, $params);
        return $result;
    }

    /**
     * @param $params
     * @return array|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function pushOrderOverdue($params){
        $result = $this->postData($this->orderOverdueUri, $params);
        return $result;
    }

    /**
     * @param $params
     * @return array|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function pushRiskBlack($params){
        $result = $this->postData($this->riskBlackUri, $params);
        return $result;
    }

    /**
     * @param $orderId
     * @return array|bool|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function pushCollectionSuggestion(UserLoanOrder $order){
        $params = [
            'app_name'      => $order->clientInfoLog->package_name,
            'user_id'       => $order->user_id,
            'order_id'      => $order->id,
            'pan_code'      => $order->loanPerson->pan_code,
            'phone'         => $order->loanPerson->phone,
            'szlm_query_id' => $order->did,
        ];

        $result = $this->postData($this->collectionSuggestionUri, $params);
        return $result;
    }

    /**
     * @param LoanCollectionRecord $loanCollectionRecord
     * @param UserLoanOrder $order
     * @param LoanPerson $loanPerson
     * @return array|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function pushLoanCollectionRecord(LoanCollectionRecord $loanCollectionRecord, UserLoanOrder $order, LoanPerson $loanPerson){
        $params = [
            'order_id'               => $loanCollectionRecord['loan_order_id'],
            'app_name'               => $order->clientInfoLog->package_name,
            'user_id'                => $loanCollectionRecord['loan_user_id'],
            'request_id'             => $loanCollectionRecord['id'],
            'pan_code'               => $loanPerson['pan_code'],
            'contact_type'           => $loanCollectionRecord['contact_type'],
            'order_level'            => $loanCollectionRecord['order_level'],
            'operate_type'           => $loanCollectionRecord['operate_type'],
            'operate_at'             => $loanCollectionRecord['operate_at'],
            'promise_repayment_time' => $loanCollectionRecord['promise_repayment_time'],
            'risk_control'           => $loanCollectionRecord['risk_control'],
            'is_connect'             => $loanCollectionRecord['is_connect'],
        ];
        $result = $this->postData($this->loanCollectionRecordUri, $params);
        return $result;
    }

    /**
     * @param $params
     * @return array|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function pushRemindOrder($params){
        $result = $this->postData($this->remindOrderUri, $params);
        return $result;
    }

    /**
     * @param $params
     * @return array|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function pushRemindLog($params){
        $result = $this->postData($this->remindLogUri, $params);
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
                'base_uri'              => $this->baseUrl,
                RequestOptions::TIMEOUT => 60,
            ]
        );
        $response = $client->request('POST', $uri, [
            RequestOptions::FORM_PARAMS => $params
        ]);

        $result = 200 == $response->getStatusCode() ? json_decode($response->getBody()->getContents(), true) : [];
        return $result;
    }

}
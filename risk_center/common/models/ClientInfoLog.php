<?php

namespace common\models;
use common\models\user\LoanPerson;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;


/**
 * Class ClientInfoLog
 * @package common\models\fund
 *  @property int $id
 *  @property int $user_id
 *  @property int $event
 *  @property int $event_id
 *  @property string $client_type
 *  @property string $os_version
 *  @property string $app_version
 *  @property string $device_name
 *  @property string $app_market
 *  @property string $device_id
 *  @property string $brand_name
 *  @property string $bundle_id
 *  @property string $longitude
 *  @property string $latitude
 *  @property string $config_version
 *  @property string $szlm_query_id
 *  @property int $screen_width
 *  @property int $screen_height
 *  @property string $package_name
 *  @property string $google_push_token
 *  @property string $td_blackbox
 *  @property string $ip
 *  @property int $client_time
 *  @property int $created_at
 *  @property int $updated_at
 *
 */
class ClientInfoLog extends ActiveRecord
{

    const EVENT_REGISTER = 1;//LoanPerson
    const EVENT_LOGIN = 2;//UserLoginLog
    const EVENT_BASIC_INFO = 3;//UserBasicInfo
    const EVENT_WORK_INFO = 4;//UserWorkInfo
    const EVENT_PAN_OCR = 5;//UserCreditechReport
    const EVENT_ADH_OCR_FRONT = 6;//UserCreditechReport
    const EVENT_ADH_OCR_BACKED = 7;
    const EVENT_LIVE_IDENTIFY = 8;//UserCreditechReport
    const EVENT_PAN_IDENTIFY = 9;//UserPanCheckLog
    const EVENT_CONTACT = 10;//UserContact
    const EVENT_APPLY_ORDER = 11;//UserLoanOrder
    const EVENT_PAN_TO_FACE_COMPARISON = 12;//UserCreditechReport
    const EVENT_FACE_TO_FACE_COMPARISON = 13;
    const EVENT_EKYC = 14;//UserCreditReportEkyc
    const EVENT_PASSPORT_OCR = 15;
    const EVENT_PASSPORT_IDENTIFY = 16;
    const EVENT_VOTER_ID_OCR = 17;
    const EVENT_VOTER_ID_IDENTIFY = 18;
    const EVENT_DRIVER_LICENCE_OCR = 19;
    const EVENT_DRIVER_LICENCE_IDENTIFY = 20;
    const EVENT_BIND_CARD = 21;//UserBankAccount
    const EVENT_LANGUAGE = 22;//UserQuestionVerification

    public static function addLog($userId, $event, $eventId, $params)
    {
        $model = new self();
        $model->user_id = $userId;
        $model->event = $event;
        $model->event_id = $eventId;
        $model->client_type = $params['clientType'] ?? '';
        $model->os_version = $params['osVersion'] ?? '';
        $model->app_version = $params['appVersion'] ?? '';
        $model->device_name = $params['deviceName'] ?? '';
        $model->app_market = $params['appMarket'] ?? '';
        $model->device_id = $params['deviceId'] ?? '';
        $model->brand_name = $params['brandName'] ?? '';
        $model->bundle_id = $params['bundleId'] ?? '';
        $model->longitude = $params['longitude'] ?? '';
        $model->latitude = $params['latitude'] ?? '';
        $model->szlm_query_id = $params['szlmQueryId'] ?? '';
        $model->screen_width = $params['screenWidth'] ?? 0;
        $model->screen_height = $params['screenHeight'] ?? 0;
        $model->package_name = $params['packageName'] ?? '';
        $model->google_push_token = $params['googlePushToken'] ?? '';
        $model->td_blackbox = $params['tdBlackbox'] ?? '';
        $model->client_time = isset($params['timestamp']) ? intval($params['timestamp'] / 1000) : 0;
        $model->ip = $params['ip'] ?? '';
        $r = $model->save();
        return $r;

    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%client_info_log}}';
    }


    /**
     * @return object|\yii\db\Connection|null
     * @throws \yii\base\InvalidConfigException
     */
    public static function getDb()
    {
        return Yii::$app->get('db');
    }

    public function behaviors() {
        return [
            TimestampBehavior::class
        ];
    }

    public static function getPackNameByOrderId($orderId){
        /** @var ClientInfoLog $data */
        $data = static::find()->select(['package_name'])->where(['event' => ClientInfoLog::EVENT_APPLY_ORDER,'event_id' => $orderId])->one();
        if(is_null($data))
        {
            return null;
        }
        return $data->package_name;
    }
}

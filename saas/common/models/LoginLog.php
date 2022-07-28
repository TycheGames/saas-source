<?php

namespace common\models;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * Class LoginLog
 * @package common\models
 *
 * @property int $request_id
 * @property string $app_name
 * @property string $phone
 * @property string $pan_code
 * @property int $user_id
 * @property int $client_type
 * @property int $os_version
 * @property int $app_version
 * @property int $device_name
 * @property int $app_market
 * @property int $device_id
 * @property int $brand_name
 * @property int $bundle_id
 * @property int $latitude
 * @property int $longitude
 * @property int $szlm_query_id
 * @property int $screen_width
 * @property int $screen_height
 * @property int $package_name
 * @property int $ip
 * @property int $client_time
 * @property int $event_time
 * @property int $created_at
 * @property int $updated_at
 */
class LoginLog extends ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%login_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['app_name', 'request_id', 'phone', 'user_id', 'event_time',
            ], 'required'],
            [['client_type', 'os_version', 'app_version', 'device_name', 'app_market',
              'device_id', 'brand_name', 'bundle_id', 'latitude', 'longitude', 'szlm_query_id', 'screen_width',
              'screen_height', 'ip', 'client_time',
             ], 'safe'],
            [['app_name', 'request_id'], 'unique', 'targetAttribute' => ['app_name', 'request_id']]
        ];
    }


    /**
     * @return object|\yii\db\Connection|null
     * @throws \yii\base\InvalidConfigException
     */
    public static function getDb()
    {
        return Yii::$app->get('db_risk');
    }

    public function behaviors() {
        return [
            TimestampBehavior::class
        ];
    }

}

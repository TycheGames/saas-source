<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%info_device}}".
 *
 * @property int $id
 * @property string $app_name
 * @property string $phone
 * @property string $pan_code
 * @property int $user_id
 * @property int $order_id 事件关联ID ，如订单号
 * @property string $client_type
 * @property string $os_version
 * @property string $app_version
 * @property string $device_name
 * @property string $app_market
 * @property string $device_id
 * @property string $brand_name
 * @property string $bundle_id
 * @property string $latitude
 * @property string $longitude
 * @property string $szlm_query_id
 * @property int $screen_width
 * @property int $screen_height
 * @property string $ip
 * @property int $client_time
 * @property int $event_time
 * @property int $created_at
 * @property int $updated_at
 */
class InfoDevice extends ActiveRecord
{


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%info_device}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['app_name', 'phone', 'pan_code', 'user_id','client_type', 'os_version', 'app_version', 'device_name', 'app_market',
                'device_id', 'brand_name', 'bundle_id', 'latitude', 'longitude', 'szlm_query_id', 'screen_width',
                'screen_height', 'ip', 'client_time', 'event_time',  'order_id'
            ], 'safe'],
            [['app_name', 'phone', 'pan_code', 'user_id'], 'required'],
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

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }
}

<?php
namespace common\models\third_data;


use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii;

/**
 * Class ThirdDataShumeng
 * @package common\models\third_data
 * @property int $user_id
 * @property int $order_id
 * @property string $app_name
 * @property string $lat
 * @property string $lng
 * @property string $district
 * @property string $state
 * @property string $country
 * @property int $retry_limit
 * @property int $status
 * @property string $data
 * @property int created_at
 * @property int updated_at
 */
class ThirdDataGoogleMaps extends ActiveRecord {

    const STATUS_DEFAULT = 0;
    const STATUS_SUCCESS = 1;
    const STATUS_FAILED = -1;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%third_data_google_maps}}';
    }


    /**
     * @return object|yii\db\Connection|null
     * @throws yii\base\InvalidConfigException
     */
    public static function getDb()
    {
        return yii::$app->get('db');
    }

    public function behaviors() {
        return [
            TimestampBehavior::class
        ];
    }
}
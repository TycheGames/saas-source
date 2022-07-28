<?php

namespace common\models\user;

use Yii;
use MongoDB\BSON\ObjectId;
use yii\behaviors\TimestampBehavior;
use yii\mongodb\ActiveRecord;

/**
 * This is the model class for collection "user_installed_apps".
 *
 * @property ObjectId|string $_id
 * @property mixed $user_id
 * @property mixed $app_name
 * @property mixed $package_name
 * @property mixed $version_code
 * @property mixed $clientType
 * @property mixed $osVersion
 * @property mixed $appVersion
 * @property mixed $deviceName
 * @property mixed $appMarket
 * @property mixed $deviceId
 * @property mixed $created_at
 * @property mixed $updated_at
 */
class MgUserInstalledApps extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function collectionName()
    {
        return 'user_installed_apps';
    }

    public static function getDb()
    {
        return Yii::$app->get('mongodb');
    }

    /**
     * {@inheritdoc}
     */
    public function attributes()
    {
        return [
            '_id',
            'user_id',
            'merchant_id',
            'addeds',
            'deleteds',
            'updateds',
            'clientType',
            'osVersion',
            'appVersion',
            'deviceName',
            'appMarket',
            'deviceId',
            'created_at',
            'updated_at',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'merchant_id', 'app_name', 'package_name', 'version_code', 'clientType', 'osVersion', 'appVersion', 'deviceName', 'appMarket', 'deviceId', 'created_at', 'updated_at'], 'safe']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            '_id' => 'ID',
            'user_id' => 'User ID',
            'app_name' => 'App Name',
            'package_name' => 'Package Name',
            'version_code' => 'Version Code',
            'clientType' => 'Client Type',
            'osVersion' => 'Os Version',
            'appVersion' => 'App Version',
            'deviceName' => 'Device Name',
            'appMarket' => 'App Market',
            'deviceId' => 'Device ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }
}

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
 * @property mixed $user_phone
 * @property mixed $app_name
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
            'user_phone',
            'pan_code',
            'app_name',
            'addeds',
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
            [['user_phone', 'pan_code', 'app_name', 'created_at', 'updated_at'], 'safe']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            '_id' => 'ID',
            'user_phone' => 'User Phone',
            'app_name' => 'App Name',
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

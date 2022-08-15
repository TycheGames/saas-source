<?php

namespace common\models\user;

use Yii;
use MongoDB\BSON\ObjectId;
use yii\behaviors\TimestampBehavior;
use yii\mongodb\ActiveRecord;

/**
 * This is the model class for collection "user_mobile_contacts".
 *
 * @property ObjectId|string $_id
 * @property mixed $user_phone
 * @property mixed $mobile
 * @property mixed $name
 * @property mixed $created_at
 * @property mixed $updated_at
 */
class MgUserMobileContacts extends ActiveRecord
{

    /**
     * @return array|string
     */
    public static function collectionName()
    {
        return 'user_mobile_contacts';
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
            'mobile',
            'name',
            'contactedTimes',
            'contactedLastTime',
            'contactLastUpdatedTimestamp',
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
            [['user_phone', 'pan_code', 'app_name', 'mobile', 'name', 'created_at', 'updated_at'], 'safe']
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
            'mobile' => 'Mobile',
            'name' => 'Name',
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

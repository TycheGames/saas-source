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
 * @property mixed $user_id
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

    public static function getLoanDb()
    {
        return Yii::$app->get('mongodb_loan');
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
            [['user_id', 'merchant_id', 'mobile', 'name', 'created_at', 'updated_at'], 'safe']
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

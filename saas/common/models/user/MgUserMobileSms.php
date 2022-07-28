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
class MgUserMobileSms extends ActiveRecord
{

    /**
     * @return array|string
     */
    public static function collectionName()
    {
        return 'user_mobile_sms';
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
            'mid',
            'userName',
            'user_id',
            'merchant_id',
            'threadId',
            'phone',
            'messageDate',
            'protocol',
            'read',
            'status',
            'type',
            'messageContent',
            'serviceCenter',
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
            [
                [
                    'userName',
                    'user_id',
                    'merchant_id',
                    'mid',
                    'threadId',
                    'phone',
                    'messageDate',
                    'protocol',
                    'read',
                    'status',
                    'type',
                    'messageContent',
                    'serviceCenter',
                    'created_at',
                    'updated_at',
                ]
                , 'safe']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            '_id' => 'ID',
            'mid' => 'message id',
            'userName' => 'user name',
            'user_id' => 'user id',
            'threadId' => 'thread id',
            'phone' => 'phone',
            'messageDate' => 'message date',
            'protocol' => 'protocol',
            'read' => 'read',
            'status' => 'status',
            'type' => 'type',
            'messageContent' => 'message content',
            'serviceCenter' => 'service center',
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

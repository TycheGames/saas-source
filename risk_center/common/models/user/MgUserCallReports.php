<?php

namespace common\models\user;

use Yii;
use MongoDB\BSON\ObjectId;
use yii\behaviors\TimestampBehavior;
use yii\mongodb\ActiveRecord;

/**
 * This is the model class for collection "user_call_reports".
 *
 * @property ObjectId|string $_id
 * @property mixed $user_phone
 * @property mixed $mobile
 * @property mixed $name
 * @property mixed $created_at
 * @property mixed $updated_at
 */
class MgUserCallReports extends ActiveRecord
{

    /**
     * @return array|string
     */
    public static function collectionName()
    {
        return 'user_call_reports';
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
            'callName',
            'callNumber',
            'callType',
            'callDate',
            'callDateTime',
            'callDuration',
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
                    'user_phone',
                    'pan_code',
                    'app_name',
                    'callName',
                    'callType',
                    'callDate',
                    'callDuration',
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
            'user_phone' => 'user phone',
            'callName' => 'call name',
            'callNumber' => '号码',
            'callType' => '1=呼入 2=呼出 3-未接',
            'callDate' => '通话日期',
            'callDuration' => '通话时长',
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

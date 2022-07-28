<?php

namespace common\models\message;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;


/**
 * Class NxPhoneLog
 * @package common\model\message
 * @property int $id
 * @property int $order_id
 * @property int $collector_id
 * @property string $nx_name
 * @property string $nx_orderid
 * @property string $phone
 * @property int $type
 * @property int $phone_type
 * @property int $call_type
 * @property int $status
 * @property int $duration
 * @property int $direction
 * @property string $record_url
 * @property string $start_time
 * @property string $answer_time
 * @property string $end_time
 * @property string $hangup_cause
 * @property int $created_at
 * @property int $updated_at
 *
 */
class NxPhoneLog extends ActiveRecord
{
    const STATUS_NO = 0;
    const STATUS_YES = 1;

    public static $status_map = [
        self::STATUS_YES => 'OPEN',
        self::STATUS_NO => 'CLOSE'
    ];

    const CALL_COLLECTION = 0;
    const CALL_CREDITAUDIT = 1;
    const CALL_CUSTOMER = 2;

    public static $call_map = [
        self::CALL_COLLECTION => 'Collection',
        self::CALL_CREDITAUDIT => 'CreditAudit',
        self::CALL_CUSTOMER => 'Customer',
    ];
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%nx_phone_log}}';
    }

    /**
     * @return object|\yii\db\Connection|null
     * @throws \yii\base\InvalidConfigException
     */
    public static function getDb()
    {
        return Yii::$app->get('db_assist');
    }

    public function behaviors() {
        return [
            TimestampBehavior::class
        ];
    }


    public function rules()
    {
        return [
            [['collector_id', 'nx_orderid'], 'required'],
            [['id','order_id','nx_name','status', 'phone', 'type', 'call_type', 'phone_type', 'duration', 'direction', 'record_url', 'start_time', 'answer_time', 'end_time', 'hangup_cause', 'created_at','updated_at'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'order_id' => 'order_id',
            'nx_name'     => 'nx_name',
            'phone'       => 'phone',
            'type'        => 'type',
            'duration'    => 'duration',
            'direction'   => 'direction',
            'record_url'  => 'record_url',
            'start_time'  => 'start_time',
            'answer_time' => 'answer_time',
            'end_time'    => 'end_time',
            'hangup_cause'    => 'hangup_cause',
            'created_at'  => 'created time',
            'updated_at'  => 'updated time',
        ];
    }

}

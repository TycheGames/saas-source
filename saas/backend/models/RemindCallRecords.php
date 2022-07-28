<?php
namespace backend\models;

use Yii;
use yii\base\Event;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * RemindCallRecords model
 *
 * @property int $id
 * @property int $user_id
 * @property string $callName
 * @property string $callNumber
 * @property int $callType
 * @property string $callDate
 * @property int $callDateTime
 * @property int $callDuration
 * @property int $created_at
 * @property int $updated_at
 */
class RemindCallRecords extends ActiveRecord
{

    const CALL_TYPE_IN = 1;
    const CALL_TYPE_OUT = 2;
    const CALL_TYPE_MISS = 3;
    const CALL_TYPE_VOICE = 4;
    const CALL_TYPE_REJECT = 5;
    const CALL_TYPE_STOP = 6;

    static public $call_map = [
        self::CALL_TYPE_IN => '呼入',
        self::CALL_TYPE_OUT => '呼出',
        self::CALL_TYPE_MISS => '未接',
        self::CALL_TYPE_VOICE => '语音邮箱',
        self::CALL_TYPE_REJECT => '拒接',
        self::CALL_TYPE_STOP => '阻止',
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%remind_call_records}}';
    }

    public static function getDb()
    {
        return Yii::$app->get('db');
    }

    public static function getDb_rd()
    {
        return Yii::$app->get('db_read_1');
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }
}
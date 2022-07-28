<?php

namespace callcenter\models;

use Yii;
use yii\base\Event;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * CollectionCallRecords model
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
class CollectionCallRecords extends ActiveRecord
{

    const CALL_TYPE_IN = 1;
    const CALL_TYPE_OUT = 2;
    const CALL_TYPE_MISS = 3;
    const CALL_TYPE_VOICE = 4;
    const CALL_TYPE_REJECT = 5;
    const CALL_TYPE_STOP = 6;

    static public $call_map = [
        self::CALL_TYPE_IN     => '呼入',
        self::CALL_TYPE_OUT    => '呼出',
        self::CALL_TYPE_MISS   => '未接',
        self::CALL_TYPE_VOICE  => '语音邮箱',
        self::CALL_TYPE_REJECT => '拒接',
        self::CALL_TYPE_STOP   => '阻止',
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%collection_call_records}}';
    }

    public static function getDb()
    {
        return Yii::$app->get('db_assist');
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

    //事件
    const EVENT_AFTER_APP_COLLECTOR_CALL_RECORDS_UPLOAD = 'event_after_app_collector_call_records_upload'; //催收员手机通话记录上传添加记录触发

    /**
     * model初始化
     * @author
     */
    public function init(){
        parent::init();
        $this->on(static::EVENT_AFTER_APP_COLLECTOR_CALL_RECORDS_UPLOAD, ['callcenter\service\CallStatisticsService', 'CallEventHandler']);
    }

    /**
     * 记录保存后触发
     * @author zhangyuliang
     * @param bool $insert
     * @param array $changedAttributes
     */
    public function afterSave($insert, $changedAttributes){
        parent::afterSave($insert, $changedAttributes);
        if ($insert) {
            $this->trigger(static::EVENT_AFTER_APP_COLLECTOR_CALL_RECORDS_UPLOAD,new Event());
        }
    }
}
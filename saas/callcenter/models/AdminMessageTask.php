<?php

namespace callcenter\models;


use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * Class AdminMessageTask
 * @package callcenter\models
 * @property int $id
 * @property int $status
 * @property int $outside
 * @property int $group
 * @property int $task_type
 */
class AdminMessageTask extends ActiveRecord
{

    const STATUS_OPEN = 1;
    const STATUS_CLOSE = 0;

    public static $status_map = [
        self::STATUS_OPEN => 'open',
        self::STATUS_CLOSE => 'close'
    ];

    const TASK_TYPE_S_D1 = 1;  //D1还款率
    const TASK_TYPE_S_D2 = 2;  //D2还款率
    const TASK_TYPE_S_D3 = 3;  //D3还款率

    const TASK_TYPE_S_D4 = 4; //D4还款率
    const TASK_TYPE_S_D8 = 5; //D8还款率

    const TASK_TYPE_M1 = 6;  //M1：xxxrs/人
    const TASK_TYPE_M2 = 7;  //M2：xxxRs/人
    const TASK_TYPE_M3 = 8;  //M3：xxxrs/人
    const TASK_TYPE_M3P = 9;  //M3+：xxxrs/人


    public static $task_type_overdue_day = [
        self::TASK_TYPE_S_D1,
        self::TASK_TYPE_S_D2,
        self::TASK_TYPE_S_D3,
    ];

    public static $task_type_overdue_day_map = [
        self::TASK_TYPE_S_D1 => 'D1 repay apr',
        self::TASK_TYPE_S_D2 => 'D2 repay apr',
        self::TASK_TYPE_S_D3 => 'D3 repay apr',
    ];

    public static $task_type_new = [
        self::TASK_TYPE_S_D4,
        self::TASK_TYPE_S_D8,
    ];

    public static $task_type_new_map = [
        self::TASK_TYPE_S_D4 => 'D4 repay apr',
        self::TASK_TYPE_S_D8 => 'D8 repay apr',
    ];

    public static $task_type_m = [
        self::TASK_TYPE_M1,
        self::TASK_TYPE_M2,
        self::TASK_TYPE_M3,
        self::TASK_TYPE_M3P,
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%admin_message_task}}';
    }

    public static function getDb()
    {
        return \Yii::$app->get('db_assist');
    }

    public function rules()
    {
        return [
            [['outside', 'group', 'task_type', 'task_value','status'], 'required'],
            ['outside', 'check']
        ];
    }

    public function check($attribute,$params){
        if(empty($this->id)){
            $dish= AdminMessageTask::findOne(['outside'=>$this->outside,'group'=>$this->group,'task_type' => $this->task_type]);
            if($dish){
                $this->addError($attribute, 'exist!');
            }else{
                $this->clearErrors($attribute);
            }
        }
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }
}
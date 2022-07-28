<?php

namespace callcenter\models;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use Yii;

/**
 * Class CollectorClassSchedule
 * @package callcenter\models
 * @property int $id
 * @property string $date
 * @property int $admin_id
 * @property int $status
 * @property int $type
 * @property int $operator_id
 * @property string $remark
 * @property int $created_at
 * @property int $updated_at
 */
class CollectorClassSchedule extends ActiveRecord
{

    const STATUS_DEL = 0;
    const STATUS_OPEN = 1;

    const DEFAULT_TYPE = 0;
    const ABSENTEEISM_TYPE = 1;  //旷工
    const SWITCH_TYPE = 2;   //调休
    const LEAVE_TYPE = 3;    //事假
    const SICK_LEAVE_TYPE = 4; //病假
    const FUNERAL_LEAVE_TYPE = 5; //丧假
    const WEDDING_LEAVE_TYPE = 6; //婚假
    const FIRE_TYPE = 7;  //开除
    const RESIGNATION_TYPE = 8; //离职
    const LOSE_CONTACT_TYPE = 9; //失联
    const OTHER_TYPE = 10; //其他
    const WEEK_OFF_TYPE = 11; //休息

    //缺勤类型
    public static $absence_type_map = [
        self::ABSENTEEISM_TYPE => '旷工absenteeism',
        self::SWITCH_TYPE => '调休switch',
        self::LEAVE_TYPE => '事假leave',
        self::SICK_LEAVE_TYPE => '病假sick leave',
        self::FUNERAL_LEAVE_TYPE => '丧假funeral leave',
        self::WEDDING_LEAVE_TYPE => '婚假wedding leave',
        self::FIRE_TYPE => '开除fire',
        self::RESIGNATION_TYPE => '离职resignation',
        self::LOSE_CONTACT_TYPE => '失联lose contact',
        self::OTHER_TYPE => '其他other',
        self::WEEK_OFF_TYPE => '休息week off',
    ];

    public static $absence_type_today_after_map = [
        self::ABSENTEEISM_TYPE => '旷工absenteeism',
        self::SWITCH_TYPE => '调休switch',
        self::LEAVE_TYPE => '事假leave',
        self::SICK_LEAVE_TYPE => '病假sick leave',
        self::FUNERAL_LEAVE_TYPE => '丧假funeral leave',
        self::WEDDING_LEAVE_TYPE => '婚假wedding leave',
        self::LOSE_CONTACT_TYPE => '失联lose contact',
        self::OTHER_TYPE => '其他other',
        self::WEEK_OFF_TYPE => '休息week off',
    ];

    public static $absence_type_back_today_list = [
        self::ABSENTEEISM_TYPE,
        self::SWITCH_TYPE,
        self::LEAVE_TYPE,
        self::SICK_LEAVE_TYPE,
        self::FUNERAL_LEAVE_TYPE,
        self::WEDDING_LEAVE_TYPE,
        self::LOSE_CONTACT_TYPE,
        self::OTHER_TYPE,
        self::WEEK_OFF_TYPE
    ];

    public static $absence_type_back_all_list = [
        self::FIRE_TYPE,
        self::RESIGNATION_TYPE,
    ];

    public static $absence = [1,2,3,4,5,6,7,8,9,10];


    public static function tableName()
    {
        return '{{%collector_class_schedule}}';
    }

    /**
     * @return object|\yii\db\Connection|null
     * @throws \yii\base\InvalidConfigException
     */
    public static function getDb()
    {
        return Yii::$app->get('db_assist');
    }

    public static function getDb_rd()
    {
        return Yii::$app->get('db_assist_read');
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }
}
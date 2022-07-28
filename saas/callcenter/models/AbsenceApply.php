<?php
namespace callcenter\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * AbsenceApply model
 *
 * @property integer $id
 * @property string $date
 * @property string $collector_id
 * @property string $team_leader_id
 * @property string $to_person
 * @property int $status
 * @property int $finish_status
 * @property int $execute_status
 * @property int $type
 * @property integer $created_at
 * @property integer $updated_at
 */
class AbsenceApply extends \yii\db\ActiveRecord
{
    //状态
    const STATUS_WAIT = 0;
    const STATUS_YES  = 1;
    const STATUS_NO   = 2;

    //类型
    const TYPE_YES  = 1;
    const TYPE_NO   = 2;
    const TYPE_PERSON = 3;
    const TYPE_TEAM = 4;
    const TYPE_ALL  = 5;

    //执行状态
    const EXECUTE_NO = 0;
    const EXECUTE_YES = 1;

    public static $status_map = [
        self::STATUS_WAIT => 'wait audit',
        self::STATUS_YES => 'pass',
        self::STATUS_NO => 'no pass',
    ];

    public static $type_map = [
        self::TYPE_YES => '同意,不做处理',
        self::TYPE_NO => '拒绝',
        self::TYPE_PERSON => '分派给个人',
        self::TYPE_TEAM => '分派给小组',
        self::TYPE_ALL => '分派给所有人',
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%absence_apply}}';
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
            TimestampBehavior::className(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['date','collector_id', 'team_leader_id', 'type'], 'required'],
            [['to_person','execute_status'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'   => 'ID',
            'date' => 'date',
            'collector_id' => 'collector_id',
            'team_leader_id' => 'team_leader_id',
            'to_person' => 'to person',
            'status' => 'status',
            'execute_status' => 'execute status',
            'finish_status' => 'finish_status',
            'type' => 'type',
            'created_at' => 'created time',
            'updated_at' => 'updated time',
        ];
    }

}
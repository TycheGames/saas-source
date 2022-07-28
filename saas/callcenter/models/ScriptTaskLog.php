<?php

namespace callcenter\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\data\ActiveDataProvider;
use yii\data\Pagination;
use yii\db\ActiveRecord;


/**
 * This is the model class for table "{{%script_task_log}}".
 *
 * @property int $id
 * @property int $script_type 脚本类型
 * @property array $script_params 脚本参数
 * @property int $exec_status 执行状态
 * @property int $operator_id 执行计划创建人
 * @property int $exec_start_time 执行开始时间
 * @property int $exec_end_time 执行结束时间
 * @property int $created_at
 * @property int $updated_at
 */
class ScriptTaskLog extends ActiveRecord
{
    public const STATUS_INIT = 0;
    public const STATUS_EXECUTING = 1;
    public const STATUS_COMPLETE = 2;
    public const STATUS_ERROR = 3;

    public static $execStatusMap = [
        self::STATUS_INIT      => '初始化',
        self::STATUS_EXECUTING => '执行中',
        self::STATUS_COMPLETE  => '执行结束',
        self::STATUS_ERROR     => '执行异常',
    ];

    public const SCRIPT_TYPE_DISPATCH = 1;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%script_task_log}}';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db_assist');
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['script_type', 'exec_status', 'operator_id', 'exec_start_time', 'exec_end_time', 'created_at', 'updated_at'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'              => 'ID',
            'script_type'     => 'Script Type',
            'exec_status'     => 'Exec Status',
            'operator_id'     => 'Operator ID',
            'exec_start_time' => 'Exec Start Time',
            'exec_end_time'   => 'Exec End Time',
            'created_at'      => 'Created At',
            'updated_at'      => 'Updated At',
        ];
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

    public function search($params, $pageSize = 10)
    {
        $query = self::find()->orderBy(['id' => SORT_DESC]);
        if (!($this->load($params, 'ScriptTaskLog')) || !$this->validate()) {
            $countQuery = clone $query;
            $pages = new Pagination([
                'totalCount' => $countQuery->count(),
                'pageSize'   => $pageSize,
            ]);

            return new ActiveDataProvider([
                'query'      => $query,
                'pagination' => $pages,
            ]);
        }

        $query->andFilterWhere(['exec_status' => $this->exec_status]);

        $countQuery = clone $query;
        $pages = new Pagination([
            'totalCount' => $countQuery->count(),
            'pageSize'   => $pageSize,
        ]);

        return new ActiveDataProvider([
            'query'      => $query,
            'pagination' => $pages,
        ]);
    }
}

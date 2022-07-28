<?php

namespace callcenter\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%collector_attendance_day_data}}".
 *
 * @property int $id
 * @property string $date 日期
 * @property int $outside
 * @property int $group
 * @property int $group_game
 * @property int $total_num
 * @property int $today_add_num
 * @property int $attendance_num
 * @property int $created_at 创建时间
 * @property int $updated_at 更新时间
 */
class CollectorAttendanceDayData extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%collector_attendance_day_data}}';
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

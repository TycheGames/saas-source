<?php

namespace callcenter\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%collector_back_money}}".
 *
 * @property int $id
 * @property string $date 日期
 * @property int $admin_user_id
 * @property int $back_money
 * @property int $delay_money
 * @property int $delay_order_count
 * @property int $extend_money
 * @property int $extend_order_count
 * @property int $finish_order_count
 * @property int $created_at 创建时间
 * @property int $updated_at 更新时间
 */
class CollectorBackMoney extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%collector_back_money}}';
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

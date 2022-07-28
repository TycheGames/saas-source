<?php

namespace callcenter\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%input_overday_out}}".
 *
 * @property int $id
 * @property string $date 日期
 * @property string $package_name
 * @property int $input_amount 入催金额
 * @property int $user_type 用户类型
 * @property int $merchant_id 商户ID
 * @property int $overday_total_amount 逾期出崔总金额
 * @property int $overday1_amount 逾期一天出崔金额
 * @property int $overday2_amount 逾期两天出崔金额
 * ....
 * @property int $overday30_amount 逾期30天出崔金额
 * @property int $created_at 创建时间
 * @property int $updated_at 更新时间
 */
class InputOverdayOutAmount extends \yii\db\ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%input_overday_out_amount}}';
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

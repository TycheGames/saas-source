<?php

namespace callcenter\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%order_statistic}}".
 *
 * @property int $id
 * @property string $date 日期
 * @property int $merchant_id 商户id
 * @property int $loan_num 入催单数
 * @property int $repay_num 出催单数
 * @property int $created_at 创建时间
 * @property int $updated_at 更新时间
 */
class OrderStatistics extends \yii\db\ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%order_statistic}}';
    }

    /**
     * @return object|\yii\db\Connection|null
     * @throws \yii\base\InvalidConfigException
     */
    public static function getDb()
    {
        return Yii::$app->get('db_assist');
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }
}

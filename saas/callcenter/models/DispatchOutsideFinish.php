<?php

namespace callcenter\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%dispatch_outside_finish}}".
 *
 * @property int $id
 * @property string $date 日期
 * @property int $outside 机构
 * @property int $merchant_id 商户
 * @property int $total_dispatch_num 当日分派单数当日去重
 * @property int $total_dispatch_amount 当日分派金额
 * @property int $total_repay_num 还款订单数
 * @property int $total_repay_amount 还款订单的金额
 * ....
 * @property int $overday1_3_dispatch_num 逾期3天内分派并还款
 * @property int $overday1_3_dispatch_amount
 * @property int $overday1_3_repay_num
 * @property int $overday1_3_repay_amount
 *....
 * @property int $overlevel4_dispatch_num 逾期3天内分派并还款
 * @property int $overlevel4_dispatch_amount
 * @property int $overlevel4_repay_num
 * @property int $overlevel4_repay_amount
 * ....
 * @property int $created_at 创建时间
 * @property int $updated_at 更新时间
 */
class DispatchOutsideFinish extends ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%dispatch_outside_finish}}';
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

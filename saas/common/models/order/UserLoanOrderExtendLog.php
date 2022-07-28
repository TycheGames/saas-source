<?php

namespace common\models\order;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%user_loan_order_extend_log}}".
 *
 * @property int $id
 * @property int $user_id
 * @property int $order_id
 * @property int $amount 展期金额
 * @property int $days 展期天数
 * @property string $begin_date 开始日期
 * @property string $end_date 结束日期
 * @property int $created_at
 * @property int $updated_at
 * @property int $collector_id
 */
class UserLoanOrderExtendLog extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user_loan_order_extend_log}}';
    }



    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }
}

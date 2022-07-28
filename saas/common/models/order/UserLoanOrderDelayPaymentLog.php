<?php

namespace common\models\order;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%user_loan_order_delay_payment_log}}".
 *
 * @property int $id
 * @property int $user_id
 * @property int $order_id
 * @property int $amount 延期支付金额
 * @property int $delay_reduce_amount 减免金额
 * @property int $delay_start_time 延期开始时间
 * @property int $delay_end_time 延期截止时间
 * @property int $created_at
 * @property int $updated_at
 */
class UserLoanOrderDelayPaymentLog extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user_loan_order_delay_payment_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'order_id', 'amount', 'delay_start_time', 'delay_end_time', 'created_at', 'updated_at', 'delay_reduce_amount'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'order_id' => 'Order ID',
            'amount' => 'Amount',
            'delay_start_time' => 'Delay Start Time',
            'delay_end_time' => 'Delay End Time',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'delay_reduce_amount' => 'delay_reduce_amount',
        ];
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }
}

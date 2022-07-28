<?php

namespace common\models;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * Class InfoRepayment
 * @package common\models
 *
 * @property string $app_name
 * @property int $order_id
 * @property int $user_id
 * @property int $total_money
 * @property int $true_total_money
 * @property int $principal
 * @property int $interests
 * @property int $cost_fee
 * @property int $overdue_fee
 * @property string $is_overdue
 * @property int $overdue_day
 * @property string $status
 * @property int $loan_time
 * @property int $plan_repayment_time
 * @property int $closing_time
 * @property int $created_at
 * @property int $updated_at
 */
class InfoRepayment extends ActiveRecord
{
    const STATUS_PENDING = 'pending';
    const STATUS_CLOSED = 'closed';

    const OVERDUE_YES = 'y';
    const OVERDUE_NO = 'n';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%info_repayment}}';
    }


    /**
     * @return object|\yii\db\Connection|null
     * @throws \yii\base\InvalidConfigException
     */
    public static function getDb()
    {
        return Yii::$app->get('db_risk');
    }

    public function behaviors() {
        return [
            TimestampBehavior::class
        ];
    }

    public function rules()
    {
        return [
            [['app_name', 'order_id', 'user_id', 'total_money', 'true_total_money', 'principal',
                'interests', 'cost_fee',  'overdue_fee', 'is_overdue', 'overdue_day',
                'status', 'loan_time', 'plan_repayment_time', 'closing_time',
            ], 'required'],
            [['created_at', 'updated_at'], 'safe']
        ];
    }

}

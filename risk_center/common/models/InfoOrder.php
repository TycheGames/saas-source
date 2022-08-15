<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%info_order}}".
 *
 * @property int $id
 * @property string $product_name
 * @property int $product_id
 * @property string $product_source
 * @property int $app_name
 * @property int $user_id 用户id
 * @property int $order_id
 * @property int $principal
 * @property int $loan_amount
 * @property int $day_rate
 * @property int $overdue_rate
 * @property int $cost_rate
 * @property int $periods
 * @property int $status
 * @property int $order_time
 * @property int $loan_time
 * @property int $is_external
 * @property string $external_app_name
 * @property int $is_first
 * @property int $is_all_first
 * @property int $data_version
 * @property string $reject_reason 被拒原因
 * @property int $created_at
 * @property int $updated_at
 *
 * 关联表
 * @property InfoUser $infoUser
 * @property ActiveRecord $userCreditReportCibil
 * @property ActiveRecord $userCreditReportExperian
 */
class InfoOrder extends ActiveRecord
{
    //TODO 1. 征信报告cibil，experian关联；2. elasticsearch数据建立
    const STATUS_DEFAULT = 'default';
    const STATUS_RISK_AUTO_REJECT = 'reject_risk_auto';
    const STATUS_RISK_MANUAL_REJECT = 'reject_risk_manual';
    const STATUS_LOAN_REJECT = 'reject_loan';
    const STATUS_PENDING_REPAYMENT = 'pending_repayment';
    const STATUS_CLOSED_REPAYMENT = 'closed_repayment';

    const ENUM_IS_FIRST_Y = 'y'; //是首单
    const ENUM_IS_FIRST_N = 'n'; //不是首单
    const ENUM_IS_ALL_FIRST_Y = 'y'; //是首单
    const ENUM_IS_ALL_FIRST_N = 'n'; //不是首单

    public static $statusRejectSet = [
        self::STATUS_RISK_AUTO_REJECT,
        self::STATUS_RISK_MANUAL_REJECT,
        self::STATUS_LOAN_REJECT,
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%info_order}}';
    }

    /**
     * @return object|\yii\db\Connection|null
     * @throws \yii\base\InvalidConfigException
     */
    public static function getDb()
    {
        return Yii::$app->get('db');
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    public function rules()
    {
        return [
            [['is_first', 'is_all_first', 'periods', 'is_external_first', 'is_external',
                'day_rate', 'overdue_rate',  'cost_rate', 'order_time', 'app_name', 'order_id', 'user_id', 'status',
                'principal', 'loan_time', 'loan_amount'
            ], 'required'],
            [['created_at', 'updated_at', 'reject_reason', 'data_version', 'external_app_name', 'product_name', 'product_id', 'product_source'], 'safe']
        ];
    }

    public function getInfoUser()
    {
        return $this->hasOne(InfoUser::class, ['user_id' => 'user_id', 'order_id' => 'order_id', 'app_name' => 'app_name']);
    }
}

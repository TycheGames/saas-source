<?php

namespace common\models\kudos;

use common\models\order\UserLoanOrder;
use common\models\pay\PayAccountSetting;
use common\models\user\LoanPerson;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%loan_kudos_order}}".
 *
 * @property int $id
 * @property int $user_id
 * @property int $order_id
 * @property string $partner_loan_id
 * @property string $kudos_loan_id
 * @property int $kudos_tranche_id
 * @property int $disbursement_amt 放款金额 单位分
 * @property int $repayment_amt 还款金额 单位分
 * @property string $kudos_onboarded
 * @property int $kudos_status
 * @property int $validation_status
 * @property int $next_validation_time 下一次请求 validation接口的时间
 * @property int $next_check_status_time 下一次请求check status接口的时间
 * @property int $need_check_status 是否需要请求check status接口 0不需要 1需要
 * @property int $need_coupon_request 是否需要请求 0不需要 1需要 2已完成
 * @property int $coupon_amount 优惠券金额 单位分
 * @property int $merchant_id 商户id
 * @property int $pay_account_id 账号id
 * @property int $created_at
 * @property int $updated_at
 *
 * 关联表
 * @property UserLoanOrder $userLoanOrder
 * @property LoanPerson $loanPerson
 * @property LoanKudosPerson $kudosPerson
 * @property LoanKudosTranche $kudosTranche
 * @property PayAccountSetting $payAccountSetting
 */
class LoanKudosOrder extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%loan_kudos_order}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'order_id', 'kudos_status', 'disbursement_amt',
                'kudos_tranche_id', 'created_at', 'updated_at' , 'validation_status',
                'next_check_status_time', 'need_check_status', 'merchant_id', 'pay_account_id'
            ],
                'integer'],
            [['partner_loan_id', 'kudos_loan_id'], 'string', 'max' => 128],
            [['kudos_onboarded'], 'string', 'max' => 64],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'                          => 'ID',
            'user_id'                     => 'User ID',
            'order_id'                    => 'Order ID',
            'partner_loan_id'             => 'Partner Loan ID',
            'kudos_loan_id'               => 'Kudos Loan ID',
            'kudos_tranche_id'            => 'Kudos Tranche ID',
            'kudos_onboarded'             => 'Kudos Onboarded',
            'kudos_validation_status'     => 'Kudos Validation Status',
            'kudos_repay_schedule_status' => 'Kudos Repay Schedule Status',
            'merchant_id'                 => 'merchant_id',
            'pay_account_id'              => 'pay_account_id',
            'created_at'                  => 'Created At',
            'updated_at'                  => 'Updated At',
        ];
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    public function getUserLoanOrder()
    {
        return $this->hasOne(UserLoanOrder::class, ['id' => 'order_id']);
    }

    public function getLoanPerson()
    {
        return $this->hasOne(LoanPerson::class, ['id' => 'user_id']);
    }

    public function getKudosPerson()
    {
        return $this->hasOne(LoanKudosPerson::class, ['order_id' => 'order_id', 'user_id' => 'user_id', 'merchant_id' => 'merchant_id', 'pay_account_id'=> 'pay_account_id']);
    }

    public function getKudosTranche()
    {
        return $this->hasOne(LoanKudosTranche::class, ['id' => 'kudos_tranche_id', 'merchant_id' => 'merchant_id', 'pay_account_id' => 'pay_account_id']);
    }

    public function getPayAccountSetting()
    {
        return $this->hasOne(PayAccountSetting::class, ['id' => 'pay_account_id']);

    }
}

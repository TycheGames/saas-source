<?php

namespace common\models\aglow;

use common\models\order\UserLoanOrder;
use common\models\pay\PayAccountSetting;
use common\models\user\LoanPerson;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%loan_licence_aglow_order}}".
 *
 * @property int $id
 * @property int $user_id
 * @property int $order_id
 * @property string $application_no
 * @property string $customer_identification_no
 * @property string $loan_account_no
 * @property int $status
 * @property int $created_at
 * @property int $updated_at
 *
 * 关联表
 * @property UserLoanOrder $userLoanOrder
 * @property LoanPerson $loanPerson
 * @property PayAccountSetting $payAccountSetting
 */
class LoanLicenceAglowOrder extends \yii\db\ActiveRecord
{

    const STATUS_DEFAULT = 0;
    const STATUS_LOAN_APPLY= 10;
    const STATUS_APPLY_STATUS_UPDATE_PASS = 20;
    const STATUS_APPLY_STATUS_UPDATE_REJECT = 21;
    const STATUS_CONFIRM_LOAN_YES = 30;
    const STATUS_CONFIRM_LOAN_NO = 31;
    const STATUS_LOAN_DISBURSED = 40;
    const STATUS_CUSTOMER_CONFIRM = 50;
    const STATUS_LOAN_STATUS_SUCCESS = 60;
    const STATUS_LOAN_STATUS_FAIL = 61;
    const STATUS_LOAN_REPAYMENT = 70;
    const STATUS_LOAN_CLOSE = 80;
    const STATUS_CORRECT_OVERDUE = 90;

    public static $statusMap = [
        self::STATUS_DEFAULT => '默认',
        self::STATUS_LOAN_APPLY => '借款申请推送',
        self::STATUS_APPLY_STATUS_UPDATE_PASS => '借款申请通过',
        self::STATUS_APPLY_STATUS_UPDATE_REJECT => '借款申请拒绝',
        self::STATUS_CONFIRM_LOAN_YES => '用户确认提现',
        self::STATUS_CONFIRM_LOAN_NO => '用户拒绝提现',
        self::STATUS_LOAN_DISBURSED => '放款信息推送',
        self::STATUS_CUSTOMER_CONFIRM => '用户已确认',
        self::STATUS_LOAN_STATUS_SUCCESS => '放款成功',
        self::STATUS_LOAN_STATUS_FAIL => '放款失败',
        self::STATUS_LOAN_REPAYMENT => '还款计划推送',
        self::STATUS_LOAN_CLOSE => '订单关闭',
        self::STATUS_CORRECT_OVERDUE => '已推送逾期信息',
    ];


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%loan_licence_aglow_order}}';
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

    public function getPayAccountSetting()
    {
        return $this->hasOne(PayAccountSetting::class, ['id' => 'pay_account_id']);

    }

}

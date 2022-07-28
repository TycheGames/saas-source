<?php

namespace common\models\order;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
/**
 * 借款审核表
 * @property integer $id ID
 * @property integer $order_id 订单ID
 * @property integer $repayment_id 还款表ID
 * @property integer $before_status 变更前状态
 * @property integer $after_status 变更后状态
 * @property integer $before_audit_status 变更前审核状态
 * @property integer $after_audit_status 变更后审核状态
 * @property integer $before_audit_bank_status 变更前审核状态
 * @property integer $after_audit_bank_status 变更后审核状态
 * @property integer $before_loan_status 变更前放款状态
 * @property integer $after_loan_status 变更后审放款态
 * @property integer $operator 操作人
 * @property string $remark 备注
 * @property integer $type 1、借款；2、还款
 * @property integer $operation_type 操作类型，如放款初审、复审等，详见model类
 * @property integer $repayment_type 还款类型：
 * @property integer $created_at 创建时间
 * @property integer $updated_at 更新时间
 * @property string $head_code 备注--头码
 * @property string $back_code 备注--尾码
 * @property string $reason_remark 审核码
 * @property string $audit_remark 审核描述
 * @property integer $can_loan_type 是否可再借 1 可再借 -1 不可再借 2 1个月后再借
 * @property integer $user_id 用户ID
 * 
 */
class UserOrderLoanCheckLog extends ActiveRecord
{
    //类型
    const TYPE_LOAN = 1; //借款
    const TYPE_REPAY = 2;//还款

    const TYPE_REFUSE = -3;//被决绝的原因

    public static $type_list = [
        self::TYPE_LOAN => 'loan',
        self::TYPE_REPAY => 'repay',
    ];

    const CAN_NOT_LOAN = -1;
    const CAN_LOAN = 1;
    const MONTH_LOAN = 2;
    const WEEK_LOAN = 3;

    public static $can_loan_type_list = [
        self::WEEK_LOAN => 'Can borrow it again in 7 days.', //7天后可借
        self::MONTH_LOAN => 'Can borrow it again in 30 days.', //30天后可借
        self::CAN_NOT_LOAN => 'Never borrow',  //不可再借
        self::CAN_LOAN => 'Ready to borrow', //可再借
    ];

    const LOAN_CHECK = 1;
    const REPAY_DEBIT = 11;   //还款扣款
    const REPAY_PARTIAL = 12; //部分还款
    const REPAY_REDUCTION = 13; //还款减免

    public static $operation_type_list = [
        self::LOAN_CHECK => 'borrowing audit',
        self::REPAY_DEBIT => 'debit',
        self::REPAY_PARTIAL => 'partial payment',
        self::REPAY_REDUCTION => 'reduction',
    ];

    //还款类型
    const REPAYMENT_TYPE_1 = 1;

    public static function tableName()
    {
        return '{{%user_order_loan_check_log}}';
    }

    /**
     * @return object|\yii\db\Connection|null
     * @throws \yii\base\InvalidConfigException
     */
    public static function getDb()
    {
        return Yii::$app->get('db');
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

}
<?php

namespace common\models\order;
use common\models\coupon\UserCouponInfo;
use common\models\user\LoanPerson;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;


/**
 * Class UserLoanOrderRepayment
 * @package common\models\user
 *
 * 表属性
 * @property int $id
 * @property int $user_id
 * @property int $order_id 订单id
 * @property int $total_money 应还总额
 * @property int $true_total_money 已还总额
 * @property int $principal 本金
 * @property int $interests 利息
 * @property int $overdue_fee 滞纳金
 * @property int $cost_fee 手续费
 * @property int $reduction_money 减免金额
 * @property int $delay_reduce_amount 延期减免金额
 * @property int $coupon_money 优惠券金额
 * @property int $is_overdue 是否是逾期：0，不是；1，是
 * @property int $overdue_day 逾期天数
 * @property int $collection_overdue_day 在催逾期天数
 * @property int $coupon_id 优惠券id
 * @property string $operator_name 操作人
 * @property int $card_id 银行卡ID
 * @property int $merchant_id 商户号
 * @property int $status 状态
 * @property int $is_delay_repayment 延期还款 0:否 1:是
 * @property int $delay_repayment_time 延期付款截止时间
 * @property int $delay_repayment_number 延期付款次数
 * @property int $true_total_principal 已还本金
 * @property int $true_total_principal_loan_money 已还本金-放款金额
 * @property int $true_total_principal_cost_fee 已还本金-手续费
 * @property int $true_total_interests 已还利息
 * @property int $true_total_overdue_fee 已还滞纳金
 * @property int $loan_time 放款时间
 * @property int $plan_repayment_time 结息日期
 * @property int $plan_fee_time 开始计算滞纳金时间
 * @property int $closing_time 订单结清时间
 * @property int $interest_time 最后一次计息时间
 * @property int $created_at 创建时间
 * @property int $updated_at 更新时间
 * @property string $is_extend 是否展期中 yes no
 * @property string $extend_begin_date 展期开始时间
 * @property string $extend_end_date 展期结束时间
 * @property int $extend_total 展期次数
 * @property int $is_push_assist 是否入催到催收中心
 * @property int $is_push_remind 是否推送到提醒中心
 *
 * 关联表
 * @property UserLoanOrder userLoanOrder
 * @property UserCouponInfo userCouponInfo
 * @property LoanPerson loanPerson
 */
class UserLoanOrderRepayment extends ActiveRecord
{
    const IS_DELAY_YES = 1;
    const IS_DELAY_NO = 0;

    const IS_OVERDUE_YES = 1;
    const IS_OVERDUE_NO = 0;

    const IS_PUSH_ASSIST_YES = 1;
    const IS_PUSH_ASSIST_NO = 0;

    const IS_PUSH_REMIND_YES = 1;
    const IS_PUSH_REMIND_NO = 0;

    const STATUS_NORAML = 0; //待还款
    const STATUS_REPAY_COMPLETE = 1; //已还款

    public static $delay_repayment_map = [
        self::IS_DELAY_YES => 'yes',
        self::IS_DELAY_NO => 'no',
    ];

    public static $repayment_status_map = [
        self::STATUS_NORAML => 'waiting for repayment',
        self::STATUS_REPAY_COMPLETE => 'repay complete'
    ];

    public static $overdue_status_map = [
        self::IS_OVERDUE_NO => 'no overdue',
        self::IS_OVERDUE_YES => 'overdue'
    ];

    public static $extend_map = [
        self::IS_EXTEND_YES => 'extend',
        self::IS_EXTEND_NO => 'no extend'
    ];

    const IS_EXTEND_YES = 'yes'; //在展期中
    const IS_EXTEND_NO = 'no'; //不在展期中

    public static function tableName()
    {
        return '{{%user_loan_order_repayment}}';
    }

    public static function getDb()
    {
        return Yii::$app->get('db');
    }

    public static function getDbName(){
        if(preg_match('/dbname=(\w+)/', Yii::$app->db->dsn, $db) && !empty($db[1])){
            return $db[1];
        }
        return null;
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

    public function getUserCouponInfo()
    {
        return $this->hasOne(UserCouponInfo::class, ['id' => 'coupon_id']);
    }

    /**
     * 逾期费用计算
     * @return int
     */
    public function calcOverdueFee()
    {
        $order = $this->userLoanOrder;
        $overdueRate = $order->overdue_rate;
//        $cacl_money = min($this->principal, ($this->total_money - $this->true_total_money));
        $cacl_money = $this->principal;
        //计算本金和当前逾期费的差额
        $diffOverdueFee = max(0, $cacl_money - $this->overdue_fee);
        //逾期费不能大于本金
        return min($diffOverdueFee, intval(floor($cacl_money * $overdueRate * 100 / 10000)));
    }

    /**
     * 获取应还金额
     * @return int
     */
    public function getScheduledPaymentAmount() : int
    {
        return max($this->total_money - $this->true_total_money - $this->coupon_money - $this->delay_reduce_amount, 0);
    }

    /**
     * 到期日应还金额
     * @return int
     */
    public function getAmountInExpiryDate() : int
    {
        return $this->principal + $this->interests;
    }

    /**
     * 获取到期日
     * @return string
     */
    public function getPlanRepaymentDate() : string
    {
        return date('Y-m-d',$this->plan_repayment_time);
    }


    /**
     * 获取最大逾期天数
     * @param $condition
     * @return int
     */
    public static function getMaxOverdueDay($condition)
    {
        return self::find()->select(['overdue_day'])
            ->where($condition)->max('overdue_day') ?? 0;
    }


    /**
     * 获取一条离今天最近的到期日的记录
     * @param $condition
     * @return array|ActiveRecord|null|UserLoanOrderRepayment
     */
    public static function getOrderRecentDue($condition)
    {
        return  self::find()
            ->where($condition)
            ->andWhere(['<=', 'plan_repayment_time', time()])
            ->orderBy(['plan_repayment_time' => SORT_DESC])
            ->one();
    }


    /**
     * 获取用户待还款订单
     * @param $userId
     * @return array|ActiveRecord[]
     */
    public static function userRepaymentNormal($userId)
    {
        return self::find()
            ->where(['user_id' => intval($userId)])
            ->andWhere(['status' => self::STATUS_NORAML])
            ->orderBy(['id' => SORT_DESC])->all();
    }
}
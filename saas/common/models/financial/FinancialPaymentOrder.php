<?php

namespace common\models\financial;
use common\models\order\UserLoanOrder;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;


/**
 * Class FinancialPaymentOrder
 * @package common\models\user
 *
 * 表属性
 * @property int $id
 * @property int $order_id 订单id
 * @property int $user_id 用户id
 * @property int $amount 支付金额
 * @property string $order_uuid 我们生成的支付订单号
 * @property string $pay_order_id 支付返回的order id
 * @property string $pay_payment_id 支付返回payment id
 * @property int $status 状态
 * @property int $type 支付类型 1-支付网关 2-虚拟账号
 * @property int $service_type 1-razorpay 2-cashfree 3-paytm
 * @property int $auth_status 授权状态
 * @property int $source_id
 * @property int $merchant_id 商户号
 * @property int $payment_type 0正常还款 1逾期部分延期 2逾期减免
 * @property int $pay_account_id pay_account_setting id
 * @property string $remark 备注
 * @property int $is_booked 是否已入账
 * @property int $is_refund 是否已退款
 * @property int $is_delay_repayment 延期还款 0:否 1:是
 * @property int $success_time 成功时间
 * @property int $created_at 创建时间
 * @property int $updated_at 更新时间
 *
 * 关联表
 * @property UserLoanOrder userLoanOrder
 */
class FinancialPaymentOrder extends ActiveRecord
{
    const IS_DELAY_YES = 1;
    const IS_DELAY_NO = 0;

    const STATUS_DEFAULT = 0;
    const STATUS_SUCCESS = 1;
    const STATUS_FAILED = -1;

    public static $status_map = [
        self::STATUS_DEFAULT => '默认',
        self::STATUS_SUCCESS => '成功',
        self::STATUS_FAILED => '失败',
    ];

    const IS_BOOKED_YES = 1;
    const IS_BOOKED_NO = -1;

    public static $is_booked_map = [
        self::IS_BOOKED_YES => '是',
        self::IS_BOOKED_NO => '否',
    ];

    const IS_REFUND_YES = 1;
    const IS_REFUND_NO = -1;

    public static $is_refund_map = [
        self::IS_REFUND_YES => '是',
        self::IS_REFUND_NO => '否',
    ];

    const AUTH_YES = 1;
    const AUTH_NO = 0;

    public static $auth_map = [
        self::AUTH_YES => '是',
        self::AUTH_NO => '否',
    ];

    const TYPE_PAYMENT_GATEWAY = 1;
    const TYPE_VIRTUAL_ACCOUNT = 2;
    const TYPE_UPI_ADDRESS = 3;

    public static $type_map = [
        self::TYPE_PAYMENT_GATEWAY => '支付网关',
        self::TYPE_VIRTUAL_ACCOUNT => '虚拟账号',
        self::TYPE_UPI_ADDRESS => 'upi地址',
    ];

    //支付通道
    const SERVICE_TYPE_RAZORPAY = 1;
    const SERVICE_TYPE_CASHFREE = 2;
    const SERVICE_TYPE_PATTM = 3;
    const SERVICE_TYPE_MPURSE = 4;
    const SERVICE_TYPE_MPURSE_UPI = 5;
    const SERVICE_TYPE_RAZORPAY_PAYMENT_LINK = 6;
    const SERVICE_TYPE_SIFANG = 9;
    const SERVICE_TYPE_JPAY = 10;
    const SERVICE_TYPE_MOJO = 12;
    const SERVICE_TYPE_QIMING = 13;
    const SERVICE_TYPE_RPAY = 14;
    const SERVICE_TYPE_QUANQIUPAY = 15;
    const SERVICE_TYPE_PAYU = 20;


    public static $service_type_map = [
        self::SERVICE_TYPE_RAZORPAY => 'razorpay',
        self::SERVICE_TYPE_CASHFREE => 'cashfree',
        self::SERVICE_TYPE_PATTM => 'paytm',
        self::SERVICE_TYPE_MPURSE => 'mpurse',
        self::SERVICE_TYPE_MPURSE_UPI => 'mpurse_upi',
        self::SERVICE_TYPE_RAZORPAY_PAYMENT_LINK => 'razorpay payment link',
        self::SERVICE_TYPE_SIFANG => 'sifang',
        self::SERVICE_TYPE_JPAY => 'jpay',
        self::SERVICE_TYPE_MOJO => 'mojo',
        self::SERVICE_TYPE_QIMING => 'qiming',
        self::SERVICE_TYPE_RPAY => 'rpay',
        self::SERVICE_TYPE_QUANQIUPAY => 'quanqiupay',
    ];

    //支付类型
    const PAYMENT_TYPE_DEFAULT = 0;
    const PAYMENT_TYPE_DELAY = 1;
    const PAYMENT_TYPE_DELAY_REDUCE = 2;
    const PAYMENT_TYPE_EXTEND = 3; //真正的展期


    public static $payment_type_map = [
        self::PAYMENT_TYPE_DEFAULT => 'default',
        self::PAYMENT_TYPE_DELAY => 'delay',
        self::PAYMENT_TYPE_DELAY_REDUCE => 'delay and reduce overdue free',
        self::PAYMENT_TYPE_EXTEND       => 'extend',
    ];

    const IS_SUMMARY_YES = 1;
    const IS_SUMMARY_NO = 0;

    public static $summary_map = [
        self::IS_SUMMARY_NO => 'NO',
        self::IS_SUMMARY_YES => 'YES',
    ];


    public static function tableName()
    {
        return '{{%financial_payment_order}}';
    }

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

    public function getUserLoanOrder()
    {
        return $this->hasOne(UserLoanOrder::class, ['id' => 'order_id']);
    }

}
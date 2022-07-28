<?php

namespace common\models\order;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;


/**
 * Class UserRepaymentLog
 * @package common\models\order
 *
 * 表属性
 * @property int $id
 * @property int $user_id
 * @property int $merchant_id
 * @property int $order_id 订单id
 * @property int $amount 本次还款金额
 * @property int $principal 本次还款-本金
 * @property int $principal_loan_money 本次还款-本金-放款金额
 * @property int $principal_cost_fee 本次还款-本金-手续费
 * @property int $interests 本次还款-利息
 * @property int $overdue_fee 本次还款-滞纳金
 * @property int $is_delay_repayment 延期还款 0:否 1:是
 * @property int $type 还款方式 1 主动还款  2 系统代扣   3 手动入账
 * @property int $success_time 入账时间
 * @property int $collector_id 催收员id
 * @property int $created_at 创建时间
 * @property int $updated_at 更新时间
 */
class UserRepaymentLog extends ActiveRecord
{
    const IS_DELAY_YES = 1;
    const IS_DELAY_NO = 0;

    const TYPE_ACTIVE = 1; //线上还款
    const TYPE_SYSTEM = 2;
    const TYPE_MANUAL = 3;
    const TYPE_OFFLINE = 4;
    const TYPE_VIRTUAL = 5;

    public static $typeMap = [
        self::TYPE_ACTIVE => 'Active repayment',
        self::TYPE_OFFLINE => 'Repayment offline',
        self::TYPE_SYSTEM => 'System charge',
        self::TYPE_MANUAL => 'Manual entry',
        self::TYPE_VIRTUAL => 'Virtual account',
    ];

    public static function tableName()
    {
        return '{{%user_repayment_log}}';
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

}
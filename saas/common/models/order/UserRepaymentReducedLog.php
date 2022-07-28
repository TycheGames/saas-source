<?php

namespace common\models\order;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;


/**
 * Class UserRepaymentReducedLog
 * @package common\models\order
 *
 * 表属性
 * @property int $id
 * @property int $user_id
 * @property int $merchant_id
 * @property int $order_id 订单id
 * @property int $repayment_id 订单id
 * @property int $from 操作来源 后台还是催收
 * @property int $reduction_money 减免金额
 * @property int $operator_id 操作人id
 * @property int $operator_name 操作人
 * @property int $remark 备注
 * @property int $created_at 创建时间
 * @property int $updated_at 更新时间
 */
class UserRepaymentReducedLog extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%user_repayment_reduced_log}}';
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

    const FROM_ADMIN_SYSTEM = 1;
    const FROM_CS_SYSTEM = 2;


    public static $type = [
        self::FROM_ADMIN_SYSTEM=>'backend',
        self::FROM_CS_SYSTEM=>'callcenter',
    ];

}
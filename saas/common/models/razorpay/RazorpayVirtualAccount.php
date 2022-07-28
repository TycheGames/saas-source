<?php

namespace common\models\razorpay;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * Class RazorpayVirtualAccount
 * @package common\models\razorpay
 *
 * 表属性
 * @property int $id
 * @property int $user_id
 * @property int $order_id
 * @property string $vid
 * @property string $bid
 * @property string $customer_id
 * @property string $name
 * @property string $bank_name
 * @property string $ifsc
 * @property string $account_number
 * @property int $status
 * @property int $pay_account_id pay_account_setting id
 * @property int $merchant_id 商户号
 * @property int $created_at 创建时间
 * @property int $updated_at 更新时间
 *
 */
class RazorpayVirtualAccount extends ActiveRecord
{

    const STATUS_ENABLE = 1;
    const STATUS_DISABLE = -1;

    public static $status_map = [
        self::STATUS_ENABLE => '可用',
        self::STATUS_DISABLE => '禁用',
    ];

    public static function tableName()
    {
        return '{{%razorpay_virtual_account}}';
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
}
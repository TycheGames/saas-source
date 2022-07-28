<?php

namespace common\models\razorpay;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * Class RazorpayUPIAddress
 * @package common\models\razorpay
 *
 * 表属性
 * @property int $id
 * @property int $user_id
 * @property int $order_id
 * @property string $vid
 * @property string $vpa_id
 * @property string $username
 * @property string $name
 * @property string $handle 银行名
 * @property string $address upi地址
 * @property int $status
 * @property int $pay_account_id
 * @property int $merchant_id
 * @property string $va_id 虚拟账号id
 * @property string $va_name 虚拟账号收款人
 * @property string $va_bank_name 虚拟账号银行
 * @property string $va_ifsc 虚拟账号ifsc
 * @property string $va_account 虚拟账号收款账号
 * @property int $version 版本号
 * @property int $created_at 创建时间
 * @property int $updated_at 更新时间
 *
 */
class RazorpayUPIAddress extends ActiveRecord
{

    const STATUS_ENABLE = 1;
    const STATUS_DISABLE = -1;

    public static $status_map = [
        self::STATUS_ENABLE => '可用',
        self::STATUS_DISABLE => '禁用',
    ];

    public static function tableName()
    {
        return '{{%razorpay_upi_address}}';
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
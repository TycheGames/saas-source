<?php

namespace common\models\razorpay;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * Class RazorpayAccount
 * @package common\models\razorpay
 *
 * 表属性
 * @property int $id
 * @property string $contact_id
 * @property string $ifsc
 * @property string $name
 * @property string $fund_account_id
 * @property string $account
 * @property string $account_type
 * @property int $active
 * @property int $merchant_id 商户号
 * @property int $pay_account_id 支付账号
 * @property int $created_at 创建时间
 * @property int $updated_at 更新时间
 *
 */
class RazorpayAccount extends ActiveRecord
{

    public static function tableName()
    {
        return '{{%razorpay_account}}';
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
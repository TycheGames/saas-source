<?php

namespace common\models\cashfree;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * Class CashFreeAccount
 * @package common\models\cashfree
 *
 * 表属性
 * @property int $id
 * @property string $user_id
 * @property string $bene_id
 * @property string $bank_account
 * @property string $ifsc
 * @property int $status
 * @property int $pay_account_id 支付账号
 * @property int $created_at 创建时间
 * @property int $updated_at 更新时间
 *
 */
class CashFreeAccount extends ActiveRecord
{

    const STATUS_ENABLE = 1;
    const DTATUS_DISABLE = -1;

    public static function tableName()
    {
        return '{{%cash_free_account}}';
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
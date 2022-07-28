<?php

namespace common\models\pay;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;


/**
 * @property int $id
 * @property int $pay_account_id
 * @property int $status
 * @property string $settlements_id
 * @property string $data_status
 * @property int $amount
 * @property int $fees
 * @property int $tax
 * @property int $settlements_time
 * @property string $utr
 * @property int $created_at
 * @property int $updated_at
 *
 */
class RazorpaySettlements extends ActiveRecord
{
    const STATUS_DEFAULT = 0;
    const STATUS_SUCCESS = 1;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%razorpay_settlements}}';
    }

    /**
     * @return object|\yii\db\Connection|null
     * @throws \yii\base\InvalidConfigException
     */
    public static function getDb()
    {
        return Yii::$app->get('db');
    }

    public function behaviors() {
        return [
            TimestampBehavior::class
        ];
    }

}

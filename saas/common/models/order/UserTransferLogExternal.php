<?php

namespace common\models\order;


use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * @property integer $id ID
 * @property string $order_uuid
 * @property int $order_id
 * @property float $amount
 * @property string $utr
 * @property string $remark
 * @property string $pic
 * @property string $account_number
 * @property float $order_amount
 * @property integer $created_at 创建时间
 * @property integer $updated_at 更新时间
 *
 */
class UserTransferLogExternal extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%user_transfer_log_external}}';
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
}
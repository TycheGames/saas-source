<?php

namespace common\models\order;


use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * @property integer $id ID
 * @property integer $request_id
 * @property integer $order_id
 * @property integer $user_id
 * @property string  $app_name
 * @property integer $remind_return
 * @property integer $payment_after_days
 * @property string  $source
 * @property integer $created_at 创建时间
 * @property integer $updated_at 更新时间
 *
 */
class RemindLogOther extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%remind_log}}';
    }

    public static function getDb()
    {
        return Yii::$app->get('db_risk');
    }
}
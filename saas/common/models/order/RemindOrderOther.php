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
 * @property integer $status
 * @property integer $remind_return
 * @property integer $payment_after_days
 * @property integer $remind_count
 * @property integer $created_at 创建时间
 * @property integer $updated_at 更新时间
 *
 */
class RemindOrderOther extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%remind_order}}';
    }

    public static function getDb()
    {
        return Yii::$app->get('db_risk');
    }
}
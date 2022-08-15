<?php

namespace common\models;

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
class RemindOrder extends ActiveRecord
{
    const STATUS_WAIT_REMIND = 0;
    const STATUS_REMINDED = 1;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%remind_order}}';
    }

    /**
     * @return object|\yii\db\Connection|null
     * @throws \yii\base\InvalidConfigException
     */
    public static function getDb()
    {
        return Yii::$app->get('db');
    }
}

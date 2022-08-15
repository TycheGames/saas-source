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
 * @property integer $remind_return
 * @property integer $payment_after_days
 * @property integer $source
 * @property integer $created_at 创建时间
 * @property integer $updated_at 更新时间
 *
 */
class RemindLog extends ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%remind_log}}';
    }

    /**
     * @return object|\yii\db\Connection|null
     * @throws \yii\base\InvalidConfigException
     */
    public static function getDb()
    {
        return Yii::$app->get('db');
    }

    public function rules()
    {
        return [
            [['order_id', 'app_name', 'user_id', 'request_id', 'source',
              'created_at', 'updated_at'], 'required'],
            [['remind_return', 'payment_after_days'], 'safe'],
        ];
    }
}

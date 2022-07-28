<?php

namespace common\models\order;


use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * @property integer $id ID
 * @property string $order_uuid
 * @property string $key
 * @property integer $created_at 创建时间
 * @property integer $updated_at 更新时间
 *
 */
class GetTransferData extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%get_transfer_data}}';
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
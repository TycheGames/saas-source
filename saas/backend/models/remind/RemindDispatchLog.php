<?php
namespace backend\models\remind;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * Class RemindDispatchLog
 * @package backend\models\remind
 * @property int $id
 * @property int $remind_id
 * @property int $before_customer_user_id
 * @property int $after_customer_user_id
 * @property int $before_customer_group
 * @property int $after_customer_group
 * @property int $created_at
 * @property int $updated_at
 */
class RemindDispatchLog extends ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%remind_dispatch_log}}';
    }

    public static function getDb()
    {
        return Yii::$app->get('db');
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }
}
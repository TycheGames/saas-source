<?php
namespace backend\models\remind;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * Class RemindLog
 * @package backend\models\remind
 * @property int $id
 * @property int $remind_id
 * @property int $customer_user_id
 * @property int $operator_user_id
 * @property int $remind_return
 * @property int $payment_after_days
 * @property int $sms_template
 * @property string $remind_remark
 * @property int $created_at
 * @property int $updated_at
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
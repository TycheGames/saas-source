<?php
namespace common\models\user;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use Yii;

/**
 * UserLoginLog model
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $last_push_time
 * @property integer $created_at
 * @property integer $updated_at
 */
class UserSmsDataPushTime extends ActiveRecord {

    /**
     * @return object|\yii\db\Connection|null
     * @throws \yii\base\InvalidConfigException
     */
    public static function getDb()
    {
        return Yii::$app->get('db');
    }

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return '{{%user_sms_data_push_time}}';
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }
}

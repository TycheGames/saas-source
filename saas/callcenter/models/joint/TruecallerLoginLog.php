<?php

namespace callcenter\models\joint;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;;

/**
 * This is the model class for table "{{%truecaller_account}}".
 *
 * @property int $id
 * @property string $phone_number
 * @property string $full_name
 * @property string $country_code
 * @property int $user_id
 * @property int $true_name
 * @property int $sim_changed
 * @property string $data
 * @property int $verification_timestamp
 * @property int $status
 * @property int $created_at 创建时间
 * @property int $updated_at 更新时间
 */
class TruecallerLoginLog extends ActiveRecord
{
    const STATUS_FAIL = 0;
    const STATUS_SUCCESS = 1;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%truecaller_login_log}}';
    }

    /**
     * @return object|\yii\db\Connection|null
     * @throws \yii\base\InvalidConfigException
     */
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

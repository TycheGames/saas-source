<?php

namespace callcenter\models\joint;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;;

/**
 * This is the model class for table "{{%truecaller_account}}".
 *
 * @property int $id
 * @property int $user_id
 * @property string $phone_number
 * @property string $full_name
 * @property int $true_name
 * @property string $country_code
 * @property string $gender
 * @property string $street  街道
 * @property string $city  城市
 * @property string $user_locale
 * @property string $zipcode 邮编
 * @property string $email 邮箱
 * @property int $sim_changed
 * @property int $created_at 创建时间
 * @property int $updated_at 更新时间
 */
class TruecallerAccount extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%truecaller_account}}';
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

<?php

namespace common\models\user;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * Class UserOverdueContact
 * @package common\models\user
 *
 * @property int $id
 * @property int $user_id
 * @property int $merchant_id
 * @property string $phone 紧急联系人手机号
 * @property int $created_at
 * @property int $updated_at
 */
class UserOverdueContact extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%user_overdue_contact}}';
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
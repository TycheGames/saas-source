<?php

namespace common\models\user;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * Class UserContact
 * @package common\models\user
 *
 * @property int $id
 * @property int $user_id
 * @property int $merchant_id
 * @property int $relative_contact_person 与第一联系人的关系
 * @property string $name 第一联系人姓名
 * @property string $phone 第一联系人手机号
 * @property int $other_relative_contact_person 与第二联系的关系
 * @property string $other_name 第二联系人姓名
 * @property string $other_phone 第二联系人手机号
 * @property string $facebook_account
 * @property string $whatsApp_account
 * @property string $skype_account
 * @property string $client_info
 * @property int $created_at
 * @property int $updated_at
 */
class UserContact extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%user_contact}}';
    }

    public static function getDb()
    {
        return Yii::$app->get('db');
    }

    public static function getDbName(){
        if(preg_match('/dbname=(\w+)/', Yii::$app->db->dsn, $db) && !empty($db[1])){
            return $db[1];
        }
        return null;
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    public static function findByUserID($userID, $db = null)
    {
        $condition = [
            'user_id' => $userID,
        ];

        return self::find()->where($condition)->orderBy('id desc')->one($db);
    }

}
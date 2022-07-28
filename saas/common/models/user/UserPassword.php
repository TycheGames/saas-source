<?php
namespace common\models\user;

use yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * UserPassword model
 *
 * @property int $id
 * @property int $status
 * @property string $user_id
 * @property string $password
 */
class UserPassword extends ActiveRecord
{

	public static function tableName()
	{
		return '{{%user_password}}';
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
	        ['user_id',  'unique', 'message' => '用户ID唯一'],
//			['password', 'string', 'length' => [6, 20], 'message' => '密码为6-16位字符或数字', 'tooShort'=>'密码为6-16位字符或数字', 'tooLong'=>'密码为6-16位字符或数字'],
            [['status','password'], 'safe']
		];
	}

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }
}
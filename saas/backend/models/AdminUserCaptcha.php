<?php
namespace backend\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * UserCaptcha model
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $phone
 * @property string $captcha
 * @property string $type
 * @property integer $generate_time
 * @property integer $expire_time
 */
class AdminUserCaptcha extends ActiveRecord
{
	// 验证码30分钟有效期
	const EXPIRE_SPACE = 1800;
    // 验证码30分钟内相同
    const SAME_SPACE = 1800;

	// 验证码类型
    const TYPE_ADMIN_LOGIN = 'admin_login';
    const TYPE_ADMIN_CS_LOGIN = 'admin_cs_login';

    /**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return '{{%admin_user_captcha}}';
	}

    /**
     * @return object|\yii\db\Connection|null
     * @throws \yii\base\InvalidConfigException
     */
	public static function getDb()
	{
		return Yii::$app->get('db');
	}


    /**
     * 获得验证码的短信内容
     * @param string $packageName
     * @return string
     */
    public function getSMS($packageName)
    {
        return "[{$packageName}] verifyCode {$this->captcha} is your verification OTP -XPROSP";
//        return "{$this->captcha} is your {$packageName} OTP.For security,please DO NOT share it with anyone.";
//        return "[{$packageName} APP ] {$this->captcha} is your {$packageName} OTP. For security, please DO NOT share it with anyone.";
    }



}
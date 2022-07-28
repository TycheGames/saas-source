<?php
namespace common\models\user;

use yii\db\ActiveRecord;

/**
 * UserLoginLog model
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $device_id
 * @property string $source
 * @property integer $type
 * @property string $push_token 谷歌推送用户标识
 * @property string $created_ip
 * @property integer created_at
 */
class UserLoginLog extends ActiveRecord {
    //登录类型
    const TYPE_NORMAL  = 1;
    const TYPE_CAPTCHA = 2;

    public static $types = array(
        self::TYPE_NORMAL => '用户名密码登录',
        self::TYPE_CAPTCHA => '验证码登录',
    );

    public static function getUserLastPushLog($userId)
    {
        /**
         * @var UserLoginLog $data
         */
        $data = self::find()->select(['push_token'])
            ->where(['user_id' => intval($userId) ])
            ->andWhere(['!=', 'push_token', ''])
            ->orderBy(['id' => SORT_DESC])
            ->one();
        if(is_null($data))
        {
            return null;
        }
        return $data->push_token;
    }


    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb() {
        return \yii::$app->db;
    }

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'tb_user_login_log';
    }
}

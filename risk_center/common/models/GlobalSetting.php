<?php

namespace common\models;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;


/**
 * Class GlobalSetting
 * @package common\models
 *  @property int $id
 * @property string $key
 * @property string $value
 *  @property int $created_at
 *  @property int $updated_at
 *
 */
class GlobalSetting extends ActiveRecord
{

    const KEY_NO_PASSWORD_LOGIN_LIST = 'no_password_login_list';
    const KEY_SKIP_CHECK_LIST        = 'skip_check_list';

    public static $key_map = [
        self::KEY_NO_PASSWORD_LOGIN_LIST => '免密登录名单',
        self::KEY_SKIP_CHECK_LIST        => '跳过风控名单',
    ];

    /**
     * 判断用户是否在通用密码登录名单中
     * @param $userId
     * @return bool
     */
    public static function checkUserInGeneralPasswordList($userId)
    {
        /** @var GlobalSetting $model */
        $model = self::find()->where(['key' => self::KEY_NO_PASSWORD_LOGIN_LIST])->one();
        if(is_null($model))
        {
            return false;
        }
        $value = explode(",", $model->value);
        if(empty($value) || !in_array($userId, $value))
        {
            return false;
        }
        return true;
    }

    /**
     * 判断用户是否在跳过风控名单中
     * @param $userId
     * @return bool
     */
    public static function checkUserInSkipCheckList($userId)
    {
        /** @var GlobalSetting $model */
        $model = self::find()->where(['key' => self::KEY_SKIP_CHECK_LIST])->one();
        if(is_null($model))
        {
            return false;
        }
        $value = explode(",", $model->value);
        if(empty($value) || !in_array($userId, $value))
        {
            return false;
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%global_setting}}';
    }


    /**
     * @return object|\yii\db\Connection|null
     * @throws \yii\base\InvalidConfigException
     */
    public static function getDb()
    {
        return Yii::$app->get('db');
    }

    public function behaviors() {
        return [
            TimestampBehavior::class
        ];
    }


    public function rules()
    {
        return [
            [['id', 'created_at', 'updated_at'], 'integer'],
            [['id', 'created_at', 'updated_at', 'value', 'key'], 'safe'],
        ];
    }
}

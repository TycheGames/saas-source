<?php
namespace backend\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;


/**
 * Class AdminLoginLog
 * @package backend\models
 *
 * @property int $user_id
 * @property string $ip
 * @property string $username
 * @property string $phone
 * @property int $type
 * @property string $app_version
 * @property string $brand_name
 * @property string $device_id
 * @property string $device_name
 */
class AdminLoginLog extends ActiveRecord
{

    const TYPE_WEB = 1;
    const TYPE_APP = 2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%admin_login_log}}';
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

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {

    }

}
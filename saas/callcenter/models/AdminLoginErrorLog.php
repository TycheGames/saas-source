<?php
namespace callcenter\models;
use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;


/**
 * Class AdminLoginErrorLog
 * @package callcenter\models
 *
 * @property int $id
 * @property string $username
 * @property string $otp
 * @property string $password
 * @property int $type
 * @property string $ip
 * @property int $system
 * @property int $created_at
 * @property int $updated_at
 */
class AdminLoginErrorLog extends ActiveRecord {

    const TYPE_PASSWORD = 1;
    const TYPE_OTP = 2;
    const TYPE_REAL_PERSON = 3;

    const SYSTEM_PC = 1;
    const SYSTEM_APP = 2;


    public static function tableName() {
        return '{{%admin_login_error_log}}';
    }

    public static function getDb() {
        return Yii::$app->get('db_assist');
    }

    /**
     * @inheritdoc
     */
    public function behaviors() {
        return [
            TimestampBehavior::class,
        ];
    }

    public static function createLog($username, $otp, $password, $ip, $type, $system)
    {
        $model = new self();
        $model->username = $username;
        $model->otp = $otp;
        $model->password = $password;
        $model->type = $type;
        $model->ip = $ip;
        $model->system = $system;
        return $model->save();
    }
}

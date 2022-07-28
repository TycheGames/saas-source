<?php
namespace callcenter\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * AdminUserRole model
 */
class AdminLoginLog extends \yii\db\ActiveRecord
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
        return Yii::$app->get('db_assist');
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
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
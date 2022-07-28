<?php
namespace backend\models\remind;

use backend\models\AdminUser;
use backend\models\Merchant;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * Class RemindAdmin
 * @package backend\models\remind
 * @property int $id
 * @property int $merchant_id
 * @property int $admin_user_id
 * @property int $remind_group
 * @property int $status
 * @property int $created_at
 * @property int $updated_at
 */
class RemindAdmin extends ActiveRecord
{
    public $phone;
    public $username;

    const OPEN_STATUS = 1;
    const DEL_STATUS = 0;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%remind_admin}}';
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
    public function rules() {
        return [
            [['remind_group','username'], 'required'],
            [['admin_user_id'], 'unique'],
            ['remind_group','validateGroup'],
            ['username','validateUsername'],
            ['merchant_id','safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'admin_user_id' => 'admin user id',
            'remind_group' => 'remind group',
            'created_at' => 'created time',
            'updated_at' => 'updated time',
        ];
    }

    /**
     * Validates the Username.
     */
    public function validateUsername($attribute) {
        if (!$this->hasErrors()) {
            /** @var AdminUser $adminUser */
            $adminUser =  AdminUser::find()->where(['username' => $this->username])->one();
            if (!$adminUser) {
                $this->addError($attribute, 'username no exist');
            }else{
                $this->admin_user_id = $adminUser->id;
            }
        }
    }

    /**
     * Validates the Group.
     */
    public function validateGroup($attribute) {
        if (!$this->hasErrors()) {
            if($this->remind_group != 0){
                $remindGroup =  RemindGroup::find()->where(['id' => $this->remind_group])->one();
                if (!$remindGroup) {
                    $this->addError($attribute, 'group no exist');
                }
            }
        }
    }

    public static function getGroupSize($remindGroup){
        return self::find()->where(['status' => self::OPEN_STATUS,'remind_group' => $remindGroup])->count();
    }
}
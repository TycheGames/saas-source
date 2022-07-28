<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/10/3
 * Time: 18:14
 */

namespace common\models\user;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * Class UserVerificationLog
 * @package common\models\user
 * @property integer $id
 * @property integer $user_id 用户ID
 * @property integer $type 认证类型
 * @property integer $status 认证状态
 * @property int $created_at 创建时间
 * @property int $updated_at 更新时间
 */

class UserVerificationLog extends ActiveRecord
{
    const STATUS_VERIFY_SUCCESS = 1;
    const STATUS_VERIFY_FAIL = 0;

    public static function tableName()
    {
        return '{{%user_verification_log}}';
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

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'                         => 'id',
            'user_id'                    => 'User ID',
            'type'                       => 'Verification type',
            'status'                     => 'Verification result status',
            'updated_at'                 => 'Updated At',
            'created_at'                 => 'Created At',
        ];
    }
}
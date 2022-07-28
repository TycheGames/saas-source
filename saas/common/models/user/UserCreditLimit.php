<?php

namespace common\models\user;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;


/**
 * Class UserCreditLimit
 * @package common\models\user
 *
 * @property int $id
 * @property int $user_id
 * @property int $max_limit 最大额度
 * @property string $min_limit 最小额度
 * @property string $type 类型
 * @property int $created_at
 * @property int $updated_at
 */
class UserCreditLimit extends ActiveRecord
{

    const TYPE_7_DAY = 1; //7天额度

    public static function tableName()
    {
        return '{{%user_credit_limit}}';
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
     * 初始化额度记录
     * @param int $user_id
     */
    public static function initUserCredit($user_id, $type) {
        $userCreditLimit = new UserCreditLimit();
        $userCreditLimit->user_id = $user_id;
        $userCreditLimit->max_limit = 150000;
        $userCreditLimit->min_limit = 150000;
        $userCreditLimit->type = $type;
        return $userCreditLimit->save();
    }


}
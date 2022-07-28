<?php

namespace common\models\user;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;


/**
 * Class UserCreditLimitChangeLog
 * @package common\models\user
 *
 * @property int $id
 * @property int $user_id
 * @property int $before_max_limit 更改前最大额度
 * @property string $after_max_limit 更改后最大额度
 * @property string $before_min_limit 更换前最小额度
 * @property string $after_min_limit 更改后最小额度
 * @property int $type 类型
 * @property string $reason 原因
 * @property int $created_at
 * @property int $updated_at
 */
class UserCreditLimitChangeLog extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%user_credit_limit_change_log}}';
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


}
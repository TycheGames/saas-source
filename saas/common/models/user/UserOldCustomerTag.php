<?php

namespace common\models\user;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;


/**
 * Class UserOldCustomerTag
 * @package common\models\user
 *
 * @property int $id
 * @property int $user_id
 * @property int $product_id
 * @property int $pan_code
 * @property int $created_at
 * @property int $updated_at
 */
class UserOldCustomerTag extends ActiveRecord
{

    public static function tableName()
    {
        return '{{%user_old_customer_tag}}';
    }

    public static function getDb()
    {
        return Yii::$app->get('db_loan');
    }
    

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

}
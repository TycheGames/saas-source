<?php

namespace common\models\user;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%user_aadhaar_mask_back_log}}".
 *
 * @property int $id
 * @property int $aadhaar_id
 * @property int $request_id
 * @property int $status
 * @property string $result
 * @property int $created_at
 * @property int $updated_at
 */
class UserAadhaarMaskBackLog extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user_aadhaar_mask_back_log}}';
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

}

<?php

namespace common\models\user;

use Yii;
use MongoDB\BSON\ObjectId;

/**
 *
 * @property ObjectId|string $_id
 * @property mixed $user_phone
 * @property mixed $mobile
 * @property mixed $name
 * @property mixed $created_at
 * @property mixed $updated_at
 */
class MgUserMobileSmsW extends MgUserMobileSms
{

    /**
     * @return array|string
     */
    public static function collectionName()
    {
        return 'user_mobile_sms_w';
    }
}

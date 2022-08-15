<?php

namespace common\services;

use yii\base\BaseObject;


class MgUserMobileSmsService extends BaseObject
{
    const SUB_MODEL_BASE_NAME_SPACE = 'common\models\user';

    public static function getModelName($pan_code='')
    {
        if(empty($pan_code)){
            return self::SUB_MODEL_BASE_NAME_SPACE.'\\MgUserMobileSms';
        }
        $last_str = strtoupper(substr($pan_code, '-1'));
        $className = self::SUB_MODEL_BASE_NAME_SPACE.'\\MgUserMobileSms'.$last_str;
        return $className;
    }
}

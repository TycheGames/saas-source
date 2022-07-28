<?php

namespace common\models\order;

use Yii;

class UserLoanOrderRepaymentExternal extends UserLoanOrderRepayment
{
    public static function getDb()
    {
        return Yii::$app->get('db_loan');
    }
}
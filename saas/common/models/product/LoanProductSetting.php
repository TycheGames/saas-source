<?php


namespace common\models\product;

use Yii;

class LoanProductSetting extends ProductSetting
{
    public static function getDb()
    {
        return Yii::$app->get('db_loan');
    }
}
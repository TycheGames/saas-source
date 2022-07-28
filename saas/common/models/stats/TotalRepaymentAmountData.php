<?php

namespace common\models\stats;
use yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
class TotalRepaymentAmountData extends ActiveRecord
{

    public static function tableName(){
        return '{{%total_repayment_amount_data}}';
    }

    public static function getDb()
    {
        return Yii::$app->get('db_stats');
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }
}
<?php

namespace common\models\stats;
use Yii;

/**
 * This is the model class for table "{{%statistics_loan_copy2}}".
 */
class StatisticsLoanCopy2 extends StatisticsLoanCopy
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%statistics_loan_copy2}}';
    }
    public static function getDb()
    {
        return Yii::$app->get('db_stats');
    }

}
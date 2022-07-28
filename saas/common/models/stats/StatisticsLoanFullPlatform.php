<?php

namespace common\models\stats;
use Yii;

/**
 * This is the model class for table "{{%statistics_loan_full_platform}}".
 */
class StatisticsLoanFullPlatform extends StatisticsLoanCopy
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%statistics_loan_full_platform}}';
    }
    public static function getDb()
    {
        return Yii::$app->get('db_stats');
    }

}
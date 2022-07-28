<?php

namespace common\models\stats;
use Yii;

/**
 * This is the model class for table "{{%statistics_loan2_full_platform}}".
 */
class StatisticsLoan2FullPlatform extends StatisticsLoanCopy
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%statistics_loan2_full_platform}}';
    }
    public static function getDb()
    {
        return Yii::$app->get('db_stats');
    }

}
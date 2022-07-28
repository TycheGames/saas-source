<?php

namespace common\models\stats;
use Yii;
use yii\db\ActiveRecord;

/**
 * Class StatisticsLoanCopy
 * @package common\models\stats
 * @property int $id
 * @property int $merchant_id
 * @property int fund_id
 * @property string app_market
 * @property string media_source
 * @property string package_name
 * @property int $date_time
 * @property int $loan_term
 * @property int loan_num
 * @property int loan_num_old
 * @property int loan_num_new
 * @property int loan_money
 * @property int loan_money_old
 * @property int loan_money_new
 * @property int created_at
 * @property int updated_at
 */
class StatisticsLoanCopy extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%statistics_loan_copy}}';
    }
    public static function getDb()
    {
        return Yii::$app->get('db_stats');
    }

}
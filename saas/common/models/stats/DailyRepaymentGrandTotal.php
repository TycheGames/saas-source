<?php

namespace common\models\stats;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii;

/**
 * Class DailyRepaymentGrandTotal
 * @package common\models\stats
 * @property int $id
 * @property string $date
 * @property int $merchant_id
 * @property string $package_name
 * @property string $overdue_day
 * @property int $all_repay_amount
 * @property int $all_repay_order_num
 * @property int $delay_repay_amount
 * @property int $delay_repay_order_num
 * @property int $created_at
 * @property int $updated_at
 */
class DailyRepaymentGrandTotal extends ActiveRecord
{

    public static function tableName(){
        return '{{%daily_repayment_grand_total}}';
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
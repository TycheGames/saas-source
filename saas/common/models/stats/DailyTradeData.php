<?php
namespace common\models\stats;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * Class DailyTradeData
 * @package common\models\stats
 */
class DailyTradeData extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName(){
        return '{{%daily_trade_data}}';
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

<?php
namespace common\models\stats;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * Class DailyRiskRejectData
 * @package common\models\stats
 * @property string date 日期
 * @property string app_market 渠道
 * @property string tree_code 节点
 * @property string txt 被拒原因
 * @property int reject_count 被拒次数
 * @property int created_at
 * @property int updated_at
 */
class DailyRiskRejectData extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName(){
        return '{{%daily_risk_reject_data}}';
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

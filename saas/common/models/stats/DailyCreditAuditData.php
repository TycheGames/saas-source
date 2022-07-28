<?php
namespace common\models\stats;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * Class DailyCreditAuditData
 * @package common\models\stats
 * @property string date 日期
 * @property int merchant_id 商户id
 * @property int operator_id 操作人id
 * @property int action 审核类型
 * @property int audit_count 审批件数
 * @property int pass_count 通过件数
 * @property int first_overdue_count 首逾件数
 * @property int created_at
 * @property int updated_at
 */
class DailyCreditAuditData extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName(){
        return '{{%daily_credit_audit_data}}';
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

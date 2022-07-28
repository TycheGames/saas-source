<?php
namespace common\models\stats;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
/**
 * Class ChannelDailyData
 * @package common\models\stats
 * @property string date 日期
 * @property int merchant_id 商户id
 * @property int source_id 用户来源id
 * @property int app_market 渠道
 * @property int media_source
 * @property string $package_name
 * @property int reg_num
 * @property int basic_num
 * @property int contact_num
 * @property int order_num
 * @property int order_amount
 * @property int audit_pass_order_num
 * @property int audit_pass_order_amount
 * @property int bind_card_pass_order_num
 * @property int bind_card_pass_order_amount
 * @property int loan_success_order_num
 * @property int loan_success_order_amount
 * @property int created_at
 * @property int updated_at
 */
class DailyUserFullData extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName(){
        return '{{%daily_user_full_data}}';
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

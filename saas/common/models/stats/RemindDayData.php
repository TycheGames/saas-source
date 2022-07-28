<?php
namespace common\models\stats;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
/**
 * Class RemindDayData
 * @package common\models\stats
 * @property string $date 日期
 * @property int $merchant_id
 * @property int $admin_user_id
 * @property int $remind_group
 * @property int $today_dispatch_num
 * @property int $today_dispatch_remind_num
 * @property int $today_repay_num
 * @property int $created_at
 * @property int $updated_at
 */
class RemindDayData extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName(){
        return '{{%remind_day_data}}';
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

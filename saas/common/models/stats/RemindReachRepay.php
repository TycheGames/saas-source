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
 * @property int $user_type
 * @property int $remind_num
 * @property int $reach_num
 * @property int $repay_num
 * @property int $created_at
 * @property int $updated_at
 */
class RemindReachRepay extends ActiveRecord
{
    const USER_TYPE_ALL = 0;
    const USER_TYPE_NEW = 1;
    const USER_TYPE_OLD = 2;

    public static $user_type_map = [
        self::USER_TYPE_ALL => 'all',
        self::USER_TYPE_NEW => 'new',
        self::USER_TYPE_OLD => 'old',
    ];

    /**
     * @inheritdoc
     */
    public static function tableName(){
        return '{{%remind_reach_repay}}';
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

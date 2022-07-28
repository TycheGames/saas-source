<?php
namespace backend\models\remind;

use Yii;
use yii\base\UserException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * Class RemindSetting
 * @package backend\models\remind
 * @property int $id
 * @property int $merchant_id
 * @property int $run_time
 * @property int $run_status
 * @property int $plan_date_before_day
 * @property int $created_at
 * @property int $updated_at
 */
class RemindSetting extends ActiveRecord
{
    const RUN_STATUS_DEFAULT = 0;
    const RUN_STATUS_FINISH = 1;

    public static $run_status_map = [
        self::RUN_STATUS_DEFAULT => 'unexecuted',
        self::RUN_STATUS_FINISH => 'executed'
    ];


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%remind_setting}}';
    }

    public static function getDb()
    {
        return Yii::$app->get('db');
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }
}
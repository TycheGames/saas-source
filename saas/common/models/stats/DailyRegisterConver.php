<?php

namespace common\models\stats;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * Class DailyRegisterConver
 * @package common\models\stats
 * @property int $id
 * @property string $date
 * @property int $type
 * @property int $source_id
 * @property string $app_market
 * @property string $media_source
 * @property int $reg_num
 * @property int $basic_num
 * @property int $kyc_num
 * @property int $address_num
 * @property int $contact_num
 * @property int $apply_num
 * @property int $audit_pass_num
 * @property int $withdraw_num
 * @property int $loan_num
 * @property int created_at
 * @property int updated_at
 */
class DailyRegisterConver extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%daily_register_conver}}';
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
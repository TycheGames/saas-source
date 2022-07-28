<?php

namespace common\models\stats;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * Class UserStructureOrderTransform
 * @package common\models\stats
 * @property int $id
 * @property string $date
 * @property int $merchant_id
 * @property int $package_name
 * @property int $user_type
 * @property int $apply_order_num
 * @property int $apply_order_money
 * @property int $apply_person_num
 * @property int $audit_pass_order_num
 * @property int $audit_pass_order_money
 * @property int $audit_pass_person_num
 * @property int $withdraw_order_num
 * @property int $withdraw_order_money
 * @property int $withdraw_person_num
 * @property int $loan_success_order_num
 * @property int $loan_success_order_money
 * @property int $loan_success_person_num
 * @property int created_at
 * @property int updated_at
 */
class UserStructureOrderTransform extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user_structure_order_transform}}';
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
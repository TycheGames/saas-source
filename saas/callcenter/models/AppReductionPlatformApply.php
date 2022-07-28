<?php

namespace callcenter\models;


use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use Yii;

/**
 * Class CollectionReduceApply
 * @package callcenter\models
 * @name AppReductionPlatformApply
 * @property $collection_order_id 催收订单id
 * @property $apply_admin_user_id 申请人admin ID
 * @property $loan_order_id 订单id
 * @property $repayment_id 还款id
 * @property $audit_operator_id 审核人id
 * @property $audit_status 审核状态
 * @property $audit_time 审核时间
 * @property $audit_result 审核结果
 * @property $audit_remark
 * @property $created_at
 * @property $updated_at
 */
class AppReductionPlatformApply extends ActiveRecord
{
    const STATUS_WAIT_APPLY = 0;  //待审批
    const STATUS_APPLY_PASS = 1;  //审批通过
    const STATUS_APPLY_REJECT = 2; //审批不用过


    public static function tableName() {
        return '{{%app_reduction_platform_apply}}';
    }

    public static function getDb() {
        return Yii::$app->get('db_assist');
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }
}
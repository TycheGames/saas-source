<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/9/25
 * Time: 22:24
 */

namespace callcenter\models;


use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use Yii;

/**
 * Class CollectionReduceApply
 * @package callcenter\models
 * @name CollectionReduceApply 减免审批表
 * @property $collection_id 申请人ID
 * @property $admin_user_id 申请人admin ID
 * @property $loan_collection_order_id 催收订单id
 * @property $reduced_type 减免类型
 * @property $apply_status 申请状态
 * @property $apply_remark 申请备注
 * @property $operator_admin_user_id 审批人admin ID
 * @property int $merchant_id
 * @property $created_at
 * @property $updated_at
 */
class CollectionReduceApply extends ActiveRecord
{
    const STATUS_WAIT_APPLY = 0;  //待审批
    const STATUS_APPLY_PASS = 1;  //审批通过
    const STATUS_APPLY_REJECT = 2; //审批不用过

    const REDUCE_APPLY_TYPE_OVERDUE = 1;  //减免逾期罚息
    const REDUCE_APPLY_TYPE_OVERDUE_COST = 2; //减免逾期罚息+服务费
    const REDUCE_APPLY_TYPE_OVERDUE_COST_INTEREST = 3; //减免逾期罚息+服务费+利息


    public static $apply_status_list = [
        self::STATUS_WAIT_APPLY => 'wait apply',
        self::STATUS_APPLY_PASS => 'pass',
        self::STATUS_APPLY_REJECT => 'not pass'
    ];


    public static $reduce_type = [
        self::REDUCE_APPLY_TYPE_OVERDUE => 'reduce overdue',
        self::REDUCE_APPLY_TYPE_OVERDUE_COST => 'reduce overdue and cost fee',
        self::REDUCE_APPLY_TYPE_OVERDUE_COST_INTEREST => 'reduce overdue、cost fee、interest'
    ];

    public static function tableName() {
        return '{{%collection_reduce_apply}}';
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
<?php

namespace callcenter\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%collection_order_dispatch_log}}".
 *
 * @property int $id
 * @property int $collection_order_id 催收订单id
 * @property int $collection_order_level 催的订单逾期等级
 * @property int $order_repayment_id 还款订单ID
 * @property int $outside 机构ID
 * @property int $merchant_id 商户ID
 * @property int $admin_user_id 催收员
 * @property int $type 类型：1直接派给公司,2直接派给具体某个人的
 * @property int $operator_id 操作人id
 * @property int $overdue_day 逾期天数
 * @property int $overdue_fee 逾期费
 * @property int $created_at 创建时间
 * @property int $updated_at 更新时间
 */
class CollectionOrderDispatchLog extends ActiveRecord
{

    const TO_COMPANY_TYPE = 1;
    const TO_ADMIN_USER_TYPE = 2;
    const TO_JUMP_COMPANY_TO_USER_TYPE = 3;

    public static $type_map = [
        self::TO_COMPANY_TYPE => '公司',
        self::TO_ADMIN_USER_TYPE => '个人',
        self::TO_JUMP_COMPANY_TO_USER_TYPE => '直接到个人'
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%collection_order_dispatch_log}}';
    }

    /**
     * @return object|\yii\db\Connection|null
     * @throws \yii\base\InvalidConfigException
     */
    public static function getDb()
    {
        return Yii::$app->get('db_assist');
    }

    public static function getDb_rd()
    {
        return Yii::$app->get('db_assist_read');
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }
}

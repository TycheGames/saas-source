<?php

namespace common\models\workOrder;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * Class UserWorkOrderAcceptLog
 * @package common\models\workOrder
 *
 * 表属性
 * @property int $id
 * @property int $type 工单类型
 * @property int $apply_id 工单ID
 * @property int $accept_user_id 受理人id
 * @property string $remark 备注
 * @property int $result 受理结果
 * @property int $created_at 创建时间
 * @property int $updated_at 更新时间
 *
 */
class UserWorkOrderAcceptLog extends ActiveRecord
{

    const RESULT_ACCEPT_COMPLETED = 1; //订单完成
    const RESULT_STILL_TO_CONTACT = 2; //仍需联系

    public static $result_map = [
        self::RESULT_ACCEPT_COMPLETED => 'accept finish',
        self::RESULT_STILL_TO_CONTACT => 'still need to contact'
    ];

    const TYPE_REDUCTION = 1;
    const TYPE_COMPLAINT = 2;
    /**
     * @return string
     */
    public static function tableName()
    {
        return '{{%user_work_order_accept_log}}';
    }

    /**
     * @return object|\yii\db\Connection|null
     * @throws \yii\base\InvalidConfigException
     */
    public static function getDb()
    {
        return Yii::$app->get('db');
    }
    

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }
}
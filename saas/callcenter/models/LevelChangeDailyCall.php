<?php

namespace callcenter\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%level_change_daily_call}}".
 *
 * @property int $id
 * @property int $collection_order_id 催收订单id
 * @property int $loan_order_id 借款订单id
 * @property int $repayment_id 还款订单id
 * @property int $over_level 订单逾期等级
 * @property int $user_id 用户id
 * @property int $user_phone 手机号
 * @property int $send_status 发送状态
 * @property string $send_id 发送id
 * @property string $remark 备注
 * @property int $created_at 创建时间
 * @property int $updated_at 更新时间
 */
class LevelChangeDailyCall extends ActiveRecord
{

    const SEND_STATUS_DEFAULT = 0;
    const SEND_STATUS_SENDING = 1;
    const SEND_STATUS_SUCCESS = 2;

    const SEND_STATUS_FAIL = -1;


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%level_change_daily_call}}';
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

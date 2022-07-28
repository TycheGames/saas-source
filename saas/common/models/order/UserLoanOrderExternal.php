<?php

namespace common\models\order;
use Yii;
use yii\db\ActiveRecord;
use yii\db\Exception;

/**
 * This is the model class for table "{{%user_loan_order_external}}".
 *
 * 表属性
 * @property int $id
 * @property string $order_uuid 展示给用户看的订单号
 * @property int $user_id 用户id
 * @property int $product_id 产品ID
 * @property int $amount 金额，单位为分
 * @property string $day_rate 日利率
 * @property int $interests 总共利息，单位分
 * @property int $overdue_fee 单位：分，滞纳金，脚本跑出来，当还款的时候重新计算进行核对
 * @property string $overdue_rate 滞纳金日利率，单位为百分之几
 * @property int $cost_fee 手续费，单位为分
 * @property string $cost_rate 一整期手续费利率，单位为百分之几
 * @property int $loan_method 期数单位：0-按天 1-按月 2-按年
 * @property int $loan_term 每期的时间周期，根据loan_method确定单位
 * @property int $periods 多少期
 * @property int $card_id 银行卡ID
 * @property int $loan_status 支付状态
 * @property int $audit_operator
 * @property int $status 状态
 * @property int $audit_status 审核状态
 * @property int $audit_bank_status 审核绑卡状态
 * @property int $bank_num 绑卡次数
 * @property int audit_bank_operator 绑卡审核人ID
 * @property int audit_question 电核问题
 * @property int audit_remark  审核备注
 * @property int $audit_begin_time 领取审核订单时间
 * @property int $audit_bank_begin_time 领取绑卡审核订单时间
 * @property int $order_time 下单时间
 * @property int $loan_time 放款时间，用于计算利息的起止时间
 * @property int $audit_time 订单审核时间
 * @property int $is_first 是否是首单，0，不是；1，是
 * @property string $app_market 下单app名
 * @property string $client_info 客户端信息
 * @property string $device_id 设备号
 * @property string $ip ip地址
 * @property string $black_box 同盾用户标识
 * @property int $created_at 创建时间
 * @property int $updated_at 更新时间
 * @property int $fund_id 资方ID
 * @property int $gst_fee GST 增值税，是手续费的18%
 * @property string $did 数盟设备指纹
 * @property string $credit_limit 授信额度
 * @property int $merchant_id 商户id
 * @property int $is_export 是否外部订单 0-否 1-是
 */
class UserLoanOrderExternal extends ActiveRecord
{

    public static function tableName()
    {
        return '{{%user_loan_order_external}}';
    }

    /**
     * @return object|\yii\db\Connection|null
     * @throws \yii\base\InvalidConfigException
     */
    public static function getDb()
    {
        return Yii::$app->get('db_loan');
    }

    /**
     * 获取外部订单
     * @param $userId
     * @return ActiveRecord|null
     */
    public static function userExternalOrder($orderUuid)
    {
        return self::find()->where(['order_uuid' => $orderUuid])->one();
    }
}
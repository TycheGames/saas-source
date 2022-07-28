<?php

namespace common\models\workOrder;
use common\helpers\CommonHelper;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * Class UserApplyReduction
 * @package common\models\workOrder
 *
 * 表属性
 * @property int $id
 * @property int $user_id
 * @property int $order_id 借款订单ID
 * @property int $merchant_id 商户id
 * @property string $apply_reduction_fee 申请减免金额
 * @property string $assume_repayment_date 预计还款时间
 * @property string $reduction_reasons 申请减免原因
 * @property string $contact_information 联系方式手机或email
 * @property int $last_accept_user_id 最后受理人id
 * @property int $last_accept_time 最后受理时间
 * @property int $accept_status 受理状态
 * @property int $created_at 创建时间
 * @property int $updated_at 更新时间
 *
 */
class UserApplyReduction extends ActiveRecord
{

    const ACCEPT_DEFAULT_STATUS = 0;  //提交后默认进行中
    const ACCEPT_FINISH_STATUS = 1;  //处理完成（关闭）

    public static $accept_status_map = [
        self::ACCEPT_DEFAULT_STATUS => 'wait accept',
        self::ACCEPT_FINISH_STATUS  => 'accept finish',
    ];

    /**
     * @return string
     */
    public static function tableName()
    {
        return '{{%user_apply_reduction}}';
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

    /**
     * 判断订单是否有减免信息提交的工单在进行中
     * @param $orderId
     * @param $merchantId
     * @return bool
     */
    public static function isAcceptProgressByOrderId($orderId,$merchantId){
        return self::find()
            ->where(['order_id' => $orderId, 'accept_status' => self::ACCEPT_DEFAULT_STATUS,'merchant_id' => $merchantId])
            ->exists();
    }

    /**
     * 添加
     * @param $merchantId
     * @param $userId
     * @param $orderId
     * @param $reductionFee
     * @param $repaymentDate
     * @param $reasons
     * @param $contact
     * @return bool
     */
    public static function createReductionWorkOrder($merchantId,$userId, $orderId, $reductionFee,$repaymentDate,$reasons,$contact){
        $userApplyReduction = new self();
        $userApplyReduction->merchant_id = $merchantId;
        $userApplyReduction->user_id = $userId;
        $userApplyReduction->order_id = $orderId;
        $userApplyReduction->apply_reduction_fee = CommonHelper::UnitToCents($reductionFee);
        $userApplyReduction->assume_repayment_date = $repaymentDate;
        $userApplyReduction->reduction_reasons = $reasons;
        $userApplyReduction->contact_information = $contact;
        $userApplyReduction->accept_status = self::ACCEPT_DEFAULT_STATUS;
        return $userApplyReduction->save();
    }
}
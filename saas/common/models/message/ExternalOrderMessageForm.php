<?php

namespace common\models\message;


use yii\base\Model;

class ExternalOrderMessageForm extends Model
{

    public $userId;
    public $phone;
    public $orderUuid;
    public $title;
    public $message;
    public $packageName;
    public $merchantId;
    public $productName;
    public $sendSms = true;
    public $sendPush = true;

//    const EVENT_PRE_RISK_REJECT     = 'event_pre_risk_reject';  //前置风控阶段 驳回订单
//    const EVENT_MAIN_RISK_REJECT    = 'event_main_risk_reject';  //机审阶段 驳回订单
//    const EVENT_MANUAL_RISK_REJECT  = 'event_manual_risk_reject'; //人工信审审核拒绝
//    const EVENT_BIND_CARD_REJECT    = 'event_bind_card_reject'; //绑卡审核拒绝
//    const EVENT_WITHDRAWAL_REJECT   = 'event_withdrawal_reject'; //提现超时驳回订单
//    const EVENT_FUND_REJECT         = 'event_fund_reject';     //分配资方超时拒绝
//    const EVENT_LOAN_REJECT         = 'event_loan_reject';    //放款驳回
//    const EVENT_LOAN_SUCCESS        = 'event_loan_success';   //放款成功
//    const EVENT_REPAY_COMPLETE      = 'event_repay_complete'; //还款完成

    const TITLE_PRE_RISK_REJECT     = 'Check Reject';  //审核驳回
    const TITLE_BIND_CARD_REJECT    = 'Bind Card Reject';
    const TITLE_WITHDRAWAL_REJECT   = 'Withdrawal Reject';
    const TITLE_FUND_REJECT         = 'Order Reject';
    const TITLE_LOAN_REJECT         = 'Loan Fail';
    const TITLE_LOAN_SUCCESS        = 'Loan Success';
    const TITLE_REPAY_COMPLETE      = 'Repay Complete';
    const TITLE_DELAY_REPAYMENT     = 'Delay Repayment';


    public function attributeLabels()
    {
        return [
            'userId' => '用户id',
            'phone' => '手机号',
            'orderUuid' => '订单号',
            'title' => '标题',
            'message' => '内容',
            'packageName' => '包名',
            'productName' => '产品名',
            'merchantId' => '商户ID',
            'sendSms' => '是否发送短信',
            'sendPush' => '是否消息推送',

        ];
    }

    public function rules()
    {
        return [
            [['userId', 'phone', 'orderUuid', 'packageName', 'merchantId', 'title', 'message', 'sendSms', 'sendPush', 'productName'], 'required']
        ];
    }

}

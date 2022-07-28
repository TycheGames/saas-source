<?php
/**
 * @var \omnilight\scheduling\Schedule $schedule
 */

$dateStr = date('Ymd');
$dirStr = '/data/logs/app';

$schedule->exec(': > /tmp/schedule_risk.log ')->dailyAt('00:01'); //清除每日日志

$merchantList = \backend\models\Merchant::getAllMerchantId();

if (YII_ENV_PROD) {
    for ($i = 1; $i <= 3; $i++){
//        $schedule->command("order/auto-check 1 {$i} >> {$dirStr}/auto_check_{$dateStr}_1_{$i}.log 2>&1 &")->everyMinute();
//        $schedule->command("order/auto-check 2 {$i} >> {$dirStr}/auto_check_{$dateStr}_2_{$i}.log 2>&1 &")->everyMinute();
        $schedule->command("order/get-data {$i} >> {$dirStr}/get_data_{$dateStr}_{$i}.log 2>&1 &")->everyMinute();
    }

    $schedule->command("order/add-risk-black-list >> {$dirStr}/add_risk_black_list_{$dateStr}.log 2>&1 &")->cron('* * * * *'); #添加黑名单

    for ($i = 1; $i <= 15; $i++){
        $schedule->command("assist/push-order-assist {$i} >> {$dirStr}/push_order_assist_{$dateStr}_{$i}.log 2>&1 &")->cron('* 0-5 * * *'); #推送逾期订单到催收中心
        $schedule->command("assist/push-order-assist-overdue {$i} >> {$dirStr}/push_order_assist_overdue_{$dateStr}_{$i}.log 2>&1 &")->cron('* 0-5 * * *'); #推送订单逾期信息

        $schedule->command("order/push-order-overdue {$i} >> {$dirStr}/push_order_overdue_{$dateStr}_{$i}.log 2>&1 &")->cron('* * * * *'); #推送订单逾期信息
    }
    $schedule->command("assist/push-user-contacts >> {$dirStr}/push_user_contacts_{$dateStr}.log 2>&1 &")->cron('* * * * *'); #推送用户通讯录
    $schedule->command("assist/push-order-assist-repayment >> {$dirStr}/push_order_assist_repayment_{$dateStr}.log 2>&1 &")->cron('* * * * *'); #推送订单还款信息

    $schedule->command("order/push-login-log >> {$dirStr}/push_login_log_{$dateStr}.log 2>&1 &")->cron('* * * * *'); #推送用户登陆信息
    $schedule->command("order/push-order-reject >> {$dirStr}/push_order_reject_{$dateStr}.log 2>&1 &")->cron('* * * * *'); #推送订单驳回信息
    $schedule->command("order/push-order-loan-success >> {$dirStr}/push_order_loan_success_{$dateStr}.log 2>&1 &")->cron('* * * * *'); #推送订单放款信息
    $schedule->command("order/push-order-repayment-success >> {$dirStr}/push_order_repayment_success_{$dateStr}.log 2>&1 &")->cron('* * * * *'); #推送订单还款信息
    $schedule->command("order/push-collection-suggestion >> {$dirStr}/push_collection_suggestion_{$dateStr}.log 2>&1 &")->cron('* * * * *'); #推送催收建议拒绝
    $schedule->command("order/push-loan-collection-record >> {$dirStr}/push_loan_collection_record_{$dateStr}.log 2>&1 &")->cron('* * * * *'); #推送催收记录
//    $schedule->command("order/push-remind-order >> {$dirStr}/push_remind_order_{$dateStr}.log 2>&1 &")->cron('*/5 * * * *'); #推送提醒订单
//    $schedule->command("order/push-remind-log >> {$dirStr}/push_remind_log_{$dateStr}.log 2>&1 &")->cron('*/5 * * * *'); #推送提醒日志

    ##################资方分配
    foreach ($merchantList as $merchantId)
    {
        $schedule->command("order/update-loan {$merchantId} >> {$dirStr}/update-loan_{$dateStr}.log 2>&1 &")->cron('* * * * *'); #资方分配脚本
    }

    $schedule->command("order/loan-fund-quota-redis-set  >> {$dirStr}/loan-fund-quota-redis-set_{$dateStr}.log 2>&1 &")->cron('* * * * *'); #资方配额存入redis

    $schedule->command("order/init-fund-quota >> {$dirStr}/init-fund-quota_{$dateStr}.log 2>&1 &")->cron('5 0 * * *'); #资方配额初始化
    $schedule->command("order/loan-fund-quota-alert >> {$dirStr}/loan-fund-quota-alert_{$dateStr}.log 2>&1 &")->cron('10 * * * *'); #资方分配脚本
//    $schedule->command("order/auto-draw >> {$dirStr}/auto-draw_{$dateStr}.log 2>&1 &")->cron('* * * * *'); #自动提现

    ############################## 支付脚本
        $schedule->command("financial-loan-pay/pay-money >>{$dirStr}/_pay-money_{$dateStr}.log 2>&1 &")->cron('* 8-23 * * *');#放款脚本
//        $schedule->command("financial-loan-pay/loan-query-new >>{$dirStr}/_pay-money_{$dateStr}.log 2>&1 &")->cron('* * * * *');#放款脚本
        $schedule->command("financial-loan-pay/generate-financial-records  >>{$dirStr}/_generate-financial-records_{$dateStr}.log 2>&1 &")->cron('* * * * *');#生成打款记录
//    $schedule->command("financial-loan-pay/get-settlements >>{$dirStr}/_get-settlements_{$dateStr}.log 2>&1 &")->cron('10 17 * * *');#获取结算信息

//    foreach ($merchantList as $merchantId)
//    {
//        $schedule->command("financial-loan-pay/pay-money-new {$merchantId} >>{$dirStr}/_pay-money-new_{$dateStr}_{$merchantId}.log 2>&1 &")->cron('* * * * *');#放款脚本
//    }
//    foreach ($merchantList as $merchantId)
//    {
//        $schedule->command("financial-loan-pay/generate-financial-records-new {$merchantId} >>{$dirStr}/_generate-financial-records-new_{$dateStr}_{$merchantId}.log 2>&1 &")->cron('* * * * *');#放款脚本
//    }


//    $schedule->command("financial-loan-pay/order-payment-auth >>{$dirStr}/_order-payment-auth_{$dateStr}.log 2>&1 &")->cron('*/30 * * * *');#还款订单未回调通知
    $schedule->command("financial-loan-pay/loan-order-reject >>{$dirStr}/_loan-order-reject_{$dateStr}.log 2>&1 &")->cron('*/30 * * * *');#打款失败2天后自动驳回

    ############################## kudos 脚本
//    $schedule->command("order/kudos-order-generate >>{$dirStr}/_kudos-order-generate_{$dateStr}.log 2>&1 &")->cron('* * * * *');#kudos创建订单
//    $schedule->command("order/kudos-order-loan-request >>{$dirStr}/_kudos_order_loan_request_{$dateStr}.log 2>&1 &")->cron('* * * * *');#kudos loan_request
//    $schedule->command("order/kudos-order-borrower-info >>{$dirStr}/_kudos_order_borrower_info_{$dateStr}.log 2>&1 &")->cron('* * * * *');#kudos borrower_info
//    $schedule->command("order/kudos-order-upload-document >>{$dirStr}/_kudos_order_upload_document_{$dateStr}.log 2>&1 &")->cron('* * * * *');#kudos upload_document
//    $schedule->command("order/kudos-order-validation-get >>{$dirStr}/_kudos_order_validation_get_{$dateStr}.log 2>&1 &")->cron('* * * * *');#kudos validation_get
//    $schedule->command("order/kudos-order-loan-repayment-schedule >>{$dirStr}/_kudos_order_loan_repayment_schedule_{$dateStr}.log 2>&1 &")->cron('* * * * *');#kudos loan_repayment_schedule
//    $schedule->command("order/kudos-order-validation >>{$dirStr}/_kudos_order_validation_{$dateStr}.log 2>&1 &")->cron('* * * * *');#kudos次日验证脚本
//    $schedule->command("order/push-order-sum-to-kudos >>{$dirStr}/_push_order_sum_to_kudos_{$dateStr}.log 2>&1 &")->cron('15 14 * * *');#kudos次日资金计划脚本
//    $schedule->command("order/kudos-repayment >>{$dirStr}/_kudos_repayment_{$dateStr}.log 2>&1 &")->cron('* * * * *');#kudos线上还款金额推送
//    $schedule->command("order/kudos-order-check-status >>{$dirStr}/_kudos_order_check_status_{$dateStr}.log 2>&1 &")->cron('* * * * *');#kudos 订单状态检查
//    $schedule->command("order/kudos-order-coupon >> {$dirStr}/_kudos-order-coupon_{$dateStr}.log 2>&1 &")->cron('* * * * *');#kudos 优惠券逻辑

    ################################# 超时失效脚本
    $schedule->command("order/bind-card-timeout >>{$dirStr}/_bind_card_timeout_{$dateStr}.log 2>&1 &")->cron('* * * * *');#绑卡超时驳回
    $schedule->command("order/withdrawal-timeout-reject >>{$dirStr}/_withdrawal_timeout_reject_{$dateStr}.log 2>&1 &")->cron('* * * * *');#体现超时驳回
    $schedule->command("order/manual-check-timeout >>{$dirStr}/_manual_check_timeout_{$dateStr}.log 2>&1 &")->cron('* * * * *');#人审超时驳回
    $schedule->command("order/user-coupon-expired >>{$dirStr}/_user_coupon_expired_{$dateStr}.log 2>&1 &")->cron('* * * * *');#优惠券失效脚本

    ################################ 人审和绑卡审核前自动判断脚本
    $schedule->command("order/before-manual-credit >>{$dirStr}/_before_manual_credit_{$dateStr}.log 2>&1 &")->cron('* * * * *');#人审和前自动判断脚本
    $schedule->command("order/before-manual-bank-credit >>{$dirStr}/_before_manual_bank_credit_{$dateStr}.log 2>&1 &")->cron('* * * * *');#绑卡审核前自动判断脚本

    ################################## razorpay 脚本
//    $schedule->command("order/create-razorpay-virtual-account >>{$dirStr}/_create-razorpay-virtual-account_{$dateStr}.log 2>&1 &")->cron('* * * * *');#创建razorpay虚拟账号

    ################################## aglow 脚本
//    $schedule->command("aglow/reject-order >>{$dirStr}/_aglow_reject-order_{$dateStr}.log 2>&1 &")->cron('* * * * *');#aglow被拒订单推送
//    $schedule->command("aglow/loan-success >>{$dirStr}/_aglow_loan-success_{$dateStr}.log 2>&1 &")->cron('* * * * *');#aglow放款成功订单同步
//    $schedule->command("aglow/fees-update >>{$dirStr}/_aglow_fees-update_{$dateStr}.log 2>&1 &")->cron('* * * * *');#aglow费用更新
//    $schedule->command("aglow/push-settlements >>{$dirStr}/_aglow_push-settlements_{$dateStr}.log 2>&1 &")->cron('1 22 * * *');#aglow费用更新

    //延迟队列
    foreach (\common\helpers\RedisDelayQueue::getBucketList() as $bucketName)
    {
        $schedule->command("delay-queue/timer {$bucketName} >>{$dirStr}/_delay-queue-timer_{$dateStr}_{$bucketName}.log 2>&1 &")->cron('* * * * *');
    }


}

if(YII_ENV_TEST) {
    //延迟队列
    foreach (\common\helpers\RedisDelayQueue::getBucketList() as $bucketName)
    {
        $schedule->command("delay-queue/timer {$bucketName} >>{$dirStr}/_delay-queue-timer_{$dateStr}_{$bucketName}.log 2>&1 &")->cron('* * * * *');
    }
}
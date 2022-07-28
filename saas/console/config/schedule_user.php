<?php
/**
 * @var \omnilight\scheduling\Schedule $schedule
 */

$dateStr = date('Ymd');
$dirStr = '/data/logs/app';

$schedule->exec(': > /tmp/schedule_user.log ')->dailyAt('00:01'); //清除每日日志

if (YII_ENV_PROD) {
    $schedule->command("user/user-content-mobile >> {$dirStr}/user_content_mobile_{$dateStr}.log 2>&1 &")->everyMinute();
    $schedule->command("user/user-content-app >> {$dirStr}/user_content_app_{$dateStr}.log 2>&1 &")->everyMinute();
    $schedule->command("user/user-content-sms >> {$dirStr}/user_content_sms_{$dateStr}.log 2>&1 &")->everyMinute();
    $schedule->command("user/user-content-call-records >> {$dirStr}/user_content_call_records_{$dateStr}.log 2>&1 &")->everyMinute();
//    $schedule->command("user/user-photo-records >> {$dirStr}/user_photo_records_{$dateStr}.log 2>&1 &")->cron('* * * * *');
    $schedule->command("order/calc-interest >> $dirStr/order_calc_interest_{$dateStr}.log 2>&1 &")->cron('1 0 * * *'); #计息脚本
    $schedule->command("order/extend-expiry >> $dirStr/order_extend_expiry_{$dateStr}.log 2>&1 &")->cron('0 1 * * *'); #展期失效脚本
    $schedule->command("order/push-external-order-can-loan-time 1 >> {$dirStr}/push_external_order_can_loan_time_{$dateStr}_1.log 2>&1 &")->everyMinute();
    $schedule->command("order/push-external-order-can-loan-time 2 >> {$dirStr}/push_external_order_can_loan_time_{$dateStr}_2.log 2>&1 &")->everyMinute();

    //同步照片脚本
//    $schedule->command("third-data/migrate-file verify >> {$dirStr}/migrate-file_verify_{$dateStr}.log 2>&1 &")->everyFiveMinutes();
//    $schedule->command("third-data/migrate-file liveness >> {$dirStr}/migrate-file_liveness_{$dateStr}.log 2>&1 &")->everyFiveMinutes();
//    $schedule->command("third-data/migrate-file aad >> {$dirStr}/migrate-file_aad_{$dateStr}.log 2>&1 &")->everyFiveMinutes();
//    $schedule->command("third-data/migrate-file pan >> {$dirStr}/migrate-file_pan_{$dateStr}.log 2>&1 &")->everyFiveMinutes();

    $schedule->command("user/user-aadhaar-encrypt >> {$dirStr}/user_aadhaar_encrypt_{$dateStr}.log 2>&1 &")->everyMinute(); #aadhaar照片加密
    $schedule->command("user/user-aadhaar-front-delete >> {$dirStr}/user_aadhaar_front_delete_{$dateStr}.log 2>&1 &")->everyMinute(); # 删除aadhaar正面照
    $schedule->command("user/user-aadhaar-back-delete >> {$dirStr}/user_aadhaar_back_delete_{$dateStr}.log 2>&1 &")->everyMinute(); # 删除aadhaar背面照
    $schedule->command("user/async-verify-user-bank-card >> {$dirStr}/async_verify_user_bank_card_{$dateStr}.log 2>&1 &")->everyMinute(); # 异步银行卡认证

    $schedule->command("order/remind-draw-money-by-half >> {$dirStr}/remind_draw_money_by_half_{$dateStr}.log 2>&1 &")->everyMinute();
    $schedule->command("order/remind-draw-money-by-1 >> {$dirStr}/remind_draw_money_by_1_{$dateStr}.log 2>&1 &")->everyMinute();
    $schedule->command("order/remind-draw-money-by-2 >> {$dirStr}/remind_draw_money_by_2_{$dateStr}.log 2>&1 &")->everyMinute();
    $schedule->command("order/remind-draw-money-by-4 >> {$dirStr}/remind_draw_money_by_4_{$dateStr}.log 2>&1 &")->everyMinute();
    $schedule->command("order/remind-draw-money-by-8 >> {$dirStr}/remind_draw_money_by_8_{$dateStr}.log 2>&1 &")->everyMinute();
    $schedule->command("order/remind-draw-money-by-12 >> {$dirStr}/remind_draw_money_by_12_{$dateStr}.log 2>&1 &")->everyMinute();
    $schedule->command("order/remind-draw-money-by-16 >> {$dirStr}/remind_draw_money_by_16_{$dateStr}.log 2>&1 &")->everyMinute();
    $schedule->command("order/remind-draw-money-by-20 >> {$dirStr}/remind_draw_money_by_20_{$dateStr}.log 2>&1 &")->everyMinute();

    $schedule->command("user/collector-ranking >> {$dirStr}/user_collector_ranking_{$dateStr}.log 2>&1 &")->everyThirtyMinutes();

    $schedule->command("remind/order-to-remind >> $dirStr/order_to_remind_{$dateStr}.log 2>&1 &")->cron('1 0 * * *'); #需要入提醒订单脚本
    for ($i=1;$i<=5;$i++){
        $schedule->command("remind/push-order-remind {$i} >> $dirStr/push_order_remind_{$dateStr}_{$i}.log 2>&1 &")->cron('* 0-3 * * *'); #推送提醒订单到提醒中心
    }

    $schedule->command("remind/push-order-remind-repayment >> $dirStr/push_order_remind_repayment_{$dateStr}.log 2>&1 &")->cron('* * * * *'); #推送订单还款信息



}

if (YII_ENV_TEST) {
    $schedule->command("order/push-external-order-can-loan-time >> {$dirStr}/push_external_order_can_loan_time_{$dateStr}.log 2>&1 &")->everyMinute();

//    $schedule->command("order/remind-draw-money-by-half >> {$dirStr}/remind_draw_money_by_half_{$dateStr}.log 2>&1 &")->everyMinute();
//    $schedule->command("order/remind-draw-money-by-1 >> {$dirStr}/remind_draw_money_by_1_{$dateStr}.log 2>&1 &")->everyMinute();
//    $schedule->command("order/remind-draw-money-by-2 >> {$dirStr}/remind_draw_money_by_2_{$dateStr}.log 2>&1 &")->everyMinute();
//    $schedule->command("order/remind-draw-money-by-4 >> {$dirStr}/remind_draw_money_by_4_{$dateStr}.log 2>&1 &")->everyMinute();
//    $schedule->command("order/remind-draw-money-by-8 >> {$dirStr}/remind_draw_money_by_8_{$dateStr}.log 2>&1 &")->everyMinute();
//    $schedule->command("order/remind-draw-money-by-12 >> {$dirStr}/remind_draw_money_by_12_{$dateStr}.log 2>&1 &")->everyMinute();
//    $schedule->command("order/remind-draw-money-by-16 >> {$dirStr}/remind_draw_money_by_16_{$dateStr}.log 2>&1 &")->everyMinute();
//    $schedule->command("order/remind-draw-money-by-20 >> {$dirStr}/remind_draw_money_by_20_{$dateStr}.log 2>&1 &")->everyMinute();
}
<?php
/**
 * @var \omnilight\scheduling\Schedule $schedule
 */

$dateStr = date('Ymd');
$dirStr = '/data/logs/app';

$schedule->exec(': > /tmp/schedule_user.log ')->dailyAt('00:01'); //清除每日日志

if (YII_ENV_PROD) {

    for ($i = 1; $i <= 3; $i++){
        $schedule->command("user/user-content-app {$i} >> {$dirStr}/user_content_app_{$dateStr}_{$i}.log 2>&1 &")->everyMinute();
        $schedule->command("user/user-content-call-records {$i} >> {$dirStr}/user_content_call_records_{$dateStr}_{$i}.log 2>&1 &")->everyMinute();
    }

    for ($i = 1; $i <= 15; $i++){
        $schedule->command("user/user-content-mobile {$i} >> {$dirStr}/user_content_mobile_{$dateStr}_{$i}.log 2>&1 &")->everyMinute();
        $schedule->command("user/user-content-sms {$i} >> {$dirStr}/user_content_sms_{$dateStr}_{$i}.log 2>&1 &")->everyMinute();
        $schedule->command("order/get-model-score {$i} >> {$dirStr}/get_model_score_{$dateStr}_{$i}.log 2>&1 &")->cron('* * * * *');
    }

    $schedule->command("order/get-model-score-list >> {$dirStr}/get_model_score_list_{$dateStr}.log 2>&1 &")->cron('01 6 * * *');

    $schedule->command("monitor/order-with-product-and-city >> {$dirStr}/monitor_order_with_product_and_city_{$dateStr}.log 2>&1 &")->everyTenMinutes();
    $schedule->command("monitor/loan-with-product-and-city >> {$dirStr}/monitor_loan_with_product_and_city_{$dateStr}.log 2>&1 &")->everyTenMinutes();

    $schedule->command("monitor/order-with-product-and-state >> {$dirStr}/monitor_order_with_product_and_state_{$dateStr}.log 2>&1 &")->everyTenMinutes();
    $schedule->command("monitor/loan-with-product-and-state >> {$dirStr}/monitor_loan_with_product_and_state_{$dateStr}.log 2>&1 &")->everyTenMinutes();

    $schedule->command("monitor/order-with-product-and-media-source >> {$dirStr}/monitor_order_with_product_and_media_source_{$dateStr}.log 2>&1 &")->everyTenMinutes();
    $schedule->command("monitor/loan-with-product-and-media-source >> {$dirStr}/monitor_loan_with_product_and_media_source_{$dateStr}.log 2>&1 &")->everyTenMinutes();

    $schedule->command("monitor/order-with-szlm-query-id >> {$dirStr}/monitor_order_with_szlm_query_id_{$dateStr}.log 2>&1 &")->hourly();
    $schedule->command("monitor/loan-with-szlm-query-id >> {$dirStr}/monitor_loan_with_szlm_query_id_{$dateStr}.log 2>&1 &")->hourly();

    $schedule->command("monitor/loan-with-product-and-media-source >> {$dirStr}/monitor_loan_with_product_and_media_source_{$dateStr}.log 2>&1 &")->everyTenMinutes();

    #异常关联预警
    $schedule->command("monitor/abnormal-association-szlm-query-id >> {$dirStr}/monitor_abnormal_association_szlm_query_id_{$dateStr}.log 2>&1 &")->everyTenMinutes();
    $schedule->command("monitor/abnormal-association-pan-code >> {$dirStr}/monitor_abnormal_association_pan_code_{$dateStr}.log 2>&1 &")->everyTenMinutes();
    $schedule->command("monitor/abnormal-association-aadhaar >> {$dirStr}/monitor_abnormal_association_aadhaar_{$dateStr}.log 2>&1 &")->everyTenMinutes();
    $schedule->command("monitor/abnormal-association-phone >> {$dirStr}/monitor_abnormal_association_phone_{$dateStr}.log 2>&1 &")->everyTenMinutes();

    #Experian报告调用预警
    $schedule->command("monitor/experian-abnormal-rate-of-calling >> {$dirStr}/monitor_experian_abnormal_rate_of_calling_{$dateStr}.log 2>&1 &")->hourly();

    #通讯录数据落库异常预警
    $schedule->command("monitor/address-book-abnormal-data >> {$dirStr}/address_book_abnormal_data_{$dateStr}.log 2>&1 &")->everyThirtyMinutes();
}


//延迟队列
foreach (\common\helpers\RedisDelayQueue::getBucketList() as $bucketName)
{
    $schedule->command("delay-queue/timer {$bucketName} >>{$dirStr}/_delay-queue-timer_{$dateStr}_{$bucketName}.log 2>&1 &")->cron('* * * * *');
}
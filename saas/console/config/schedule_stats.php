<?php
/**
 * @var \omnilight\scheduling\Schedule $schedule
 */

$dateStr = date('Ymd');
$dirStr = '/data/logs/app';

$schedule->exec(': > /tmp/schedule_stats.log ')->dailyAt('00:01'); //清除每日日志

if (YII_ENV_PROD) {
    $schedule->command("stats-operate/build-daily-user-data >> {$dirStr}/build_daily_user_stats_{$dateStr}.log 2>&1 &")->cron('*/10 * * * *');  //用户统计
    $schedule->command("stats-operate/build-daily-user-full-data >> {$dirStr}/build_daily_user_full_stats_{$dateStr}.log 2>&1 &")->cron('*/20 * * * *');  //用户统计(全量)
    $schedule->command("stats-operate/loan-repay-list >> {$dirStr}/build_daily_loan_repay_stats_{$dateStr}.log 2>&1 &")->cron('*/15 * * * *');  //每日借还款数据对比
    $schedule->command("stats-operate/credit-audit-data >> $dirStr/build_credit_audit_data_{$dateStr}.log 2>&1 &")->cron('*/15 * * * *');         //信审员操作统计
    $schedule->command("stats-operate/build-daily-risk-reject-data >> {$dirStr}/build_daily_risk_reject_stats_{$dateStr}.log 2>&1 &")->cron('*/20 * * * *');  //风控被拒每日统计脚本
    #每日到期还款单数/金
    $schedule->command("stats-operate/day-data-statistics-run >>{$dirStr}/_day_data_statistics_run_{$dateStr}.log 2>&1 &")->cron('*/5 * * * *'); #数据分析-财务数据-每日到期还款续借率/每日到期还款单数/金额（当天的+14天）
    $schedule->command("stats-operate/day-data-statistics-run 1 >>{$dirStr}/_day_data_statistics_run_{$dateStr}.log 2>&1 &")->cron('*/20 * * * *'); #数据分析-财务数据-每日到期还款续借率/每日到期还款单数/金额（7天内）
    $schedule->command("stats-operate/day-data-statistics-run 2 >>{$dirStr}/_day_data_statistics_run_{$dateStr}.log 2>&1 &")->cron('0 3 * * *'); #数据分析-财务数据-每日到期还款续借率/每日到期还款单数/金额（7-240天的）

    //每日累计还款数据
    $schedule->command("stats-operate/daily-repay-grand-run >>{$dirStr}/_daily_repay_grand_run_{$dateStr}.log 2>&1 &")->cron('*/10 * * * *'); #每日累计还款数据

    //总还款金额数据
    $schedule->command("stats-operate/total-repayment-amount-run >>{$dirStr}/_total_repayment_amount_run_{$dateStr}.log 2>&1 &")->cron('*/5 * * * *'); #总还款金额数据（当天的+14天）
    $schedule->command("stats-operate/total-repayment-amount-run 1 >>{$dirStr}/_total_repayment_amount_run_{$dateStr}.log 2>&1 &")->cron('*/20 * * * *'); #总还款金额数据（7天内）
    $schedule->command("stats-operate/total-repayment-amount-run 2 >>{$dirStr}/_total_repayment_amount_run_{$dateStr}.log 2>&1 &")->cron('0 3 * * *'); #总还款金额数据（7-240天的）

    #数据分析-财务数据-每日借款数据
    $schedule->command("stats-operate/daily-loan >>{$dirStr}/_daily_loan_{$dateStr}.log 2>&1 &")->cron('*/8 * * * *'); #数据分析-财务数据-每日借款数据(按本金)
    $schedule->command("stats-operate/daily-loan-by-full-platform >>{$dirStr}/_daily_loan_by_full_platform_{$dateStr}.log 2>&1 &")->cron('*/8 * * * *'); #数据分析-财务数据-每日借款数据(按本金,新老户按全平台)
    $schedule->command("stats-operate/daily-loan2 >>{$dirStr}/_daily_loan2_{$dateStr}.log 2>&1 &")->cron('*/8 * * * *'); #数据分析-财务数据-每日借款数据（按放款金额）
    $schedule->command("stats-operate/daily-loan2-by-full-platform >>{$dirStr}/_daily_loan2_by_full_platform_{$dateStr}.log 2>&1 &")->cron('*/8 * * * *'); #数据分析-财务数据-每日借款数据(按打款金额,新老户按全平台)
    $schedule->command("stats-operate/daily-loan-by-user-structure >>{$dirStr}/_daily_loan_by_user_structure_{$dateStr}.log 2>&1 &")->cron('*/8 * * * *'); #数据分析-财务数据-每日借款数据（按本金-结构）
    $schedule->command("stats-operate/daily-loan2-by-user-structure >>{$dirStr}/_daily_loan2_by_user_structure_{$dateStr}.log 2>&1 &")->cron('*/8 * * * *'); #数据分析-财务数据-每日借款数据（按打款金额-结构）

    $schedule->command("stats-operate/daily-register-conver >>{$dirStr}/_daily_register_conver_{$dateStr}.log 2>&1 &")->cron('*/30 * * * *'); #数据分析-运营数据-每日注册转化

    $schedule->command("stats-operate/repay-reborrowing >>{$dirStr}/_repay_reborrowing_{$dateStr}.log 2>&1 &")->cron('*/30 * * * *'); #数据分析-运营数据-每日还款复借统计
//    $schedule->command("stats-operate/user-operation-data >>{$dirStr}/_user_operation_data_{$dateStr}.log 2>&1 &")->cron('*/15 * * * *'); #每日用户数据转化

    $schedule->command("stats-operate/user-structure-export-repayment-data >>{$dirStr}/_user_structure_export_repayment_data_{$dateStr}.log 2>&1 &")->cron('*/5 * * * *');
    $schedule->command("stats-operate/user-structure-source-export-repayment-data >>{$dirStr}/_user_structure_source_export_repayment_data_{$dateStr}.log 2>&1 &")->cron('*/5 * * * *');

    $schedule->command("stats-operate/user-structure-order-transform-build >>{$dirStr}/_user_structure_order_transform_{$dateStr}.log 2>&1 &")->cron('*/8 * * * *'); #用户结构订单转化

    //提醒脚本
    $schedule->command("remind/order-remind >>{$dirStr}/_order_remind_push_{$dateStr}.log 2>&1 &")->cron('0 3 * * *'); #每日提醒订单推入
    $schedule->command("remind/remind-dispatch >>{$dirStr}/_order_remind_dispatch_{$dateStr}.log 2>&1 &")->cron('* * * * *'); #提醒分派更新脚本
    $schedule->command("remind/remind-order-change-status >>{$dirStr}/_remind_order_change_status_{$dateStr}.log 2>&1 &")->cron('* * * * *'); #提醒订单状态变更
    $schedule->command("remind/remind-recycle >>{$dirStr}/_remind_recycle_{$dateStr}.log 2>&1 &")->cron('0 2 * * *'); #提醒订单自动回收
    $schedule->command("stats-operate/remind-day-data-build >>{$dirStr}/_remind_day_data_build_{$dateStr}.log 2>&1 &")->cron('*/15 * * * *'); #每日提醒数据
    $schedule->command("stats-operate/remind-reach-repay-data-build >>{$dirStr}/_remind_reach_repay_data_build_{$dateStr}.log 2>&1 &")->cron('*/15 * * * *'); #每日提醒触达还款的统计

    $schedule->command("remind/remind-app-call-record >>{$dirStr}/_remind_app_call_record_{$dateStr}.log 2>&1 &")->cron('* * * * *'); #提醒app上报通话记录

    //$schedule->command("wang-peng/access-app-no-repay >>{$dirStr}/_access_app_no_repay_{$dateStr}.log 2>&1 &")->cron('40 7,14 * * *'); #脚本拉数据最近3日访问app未结清
}
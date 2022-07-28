<?php
/**
 * @var \omnilight\scheduling\Schedule $schedule
 *
 * manaual:   ./yii schedule/run --scheduleFile=@console/config/schedule_collection.php
 *
 * crontab:
 * * * * * echo >>/tmp/schedule.log; date >>/tmp/schedule.log 2>&1
 * * * * * /usr/local/bin/php /data/www/wzdai.com/yii schedule/run --scheduleFile=@console/config/schedule_collection.php >>/tmp/schedule_.log 2>&1
 */

$dateStr = date('Ymd');
$dirStr = '/data/logs/app';

$schedule->exec(': > /tmp/schedule_collection.log ')->dailyAt('00:01'); //清除每日日志

if (YII_ENV_PROD) { #非线上环境，手动执行
    ############################  业务脚本
    //  停催重新入催收
    $schedule->command("collection/collection-stop-regain-input >>{$dirStr}/_collection_stop_regain_input_{$dateStr}.log 2>&1 &")->cron('* * * * *'); #停催重新入催收
//    #分配订单到机构
//    $schedule->command("collection/auto-dispatch-to-company >>{$dirStr}/_auto_dispatch_to_company_{$dateStr}.log 2>&1 &")->cron('* * * * *'); #订单自动分配到机构
    #订单自动分配（手动计划）
    $schedule->command("collection/manual-dispatch-script-task >>{$dirStr}/_manual-dispatch-script-task_{$dateStr}.log 2>&1 &")->everyMinute(); #订单自动分配（手动计划）

    #订单自动分配
//    $schedule->command("collection/auto-dispatch-to-operator >>{$dirStr}/_auto_dispatch_to_operator_{$dateStr}.log 2>&1 &")->cron('40 7 * * *'); #订单自动分配

    #更新逾期订单等级
    $schedule->command("collection/update-overdue-level >>{$dirStr}/_update_overdue_level_{$dateStr}.log 2>&1 &")->cron('*/5 * * * *'); #更新订单等级，更新后回收订单
    #更新催收订单的用户最后访问时间
    $schedule->command("collection/update-user-last-access-time >>{$dirStr}/_update_user_last_access_time_{$dateStr}.log 2>&1 &")->cron('* * * * *'); #更新催收订单的用户最后访问时间
    #更新大盘用户最后放款时间
    $schedule->command("collection/update-user-last-loan-time >>{$dirStr}/_update_user_last_loan_time_{$dateStr}.log 2>&1 &")->cron('* * * * *'); #更新大盘用户最后放款时间
    #账龄更新发送短信
    $schedule->command("collection/level-change-send >>{$dirStr}/_level_change_send_{$dateStr}.log 2>&1 &")->cron('0 9 * * *'); #账龄更新发送短信
    #账龄更新发送短信结果查询
    $schedule->command("collection/level-change-send-query >>{$dirStr}/_level_change_send_query_{$dateStr}.log 2>&1 &")->cron('*/20 10 * * *'); #账龄更新发送短信结果查询
    #班表计划操作回收脚本
    $schedule->command("collection/back-class-schedule-order >>{$dirStr}/_back_class_schedule_order_{$dateStr}.log 2>&1 &")->cron('* * * * *'); #班表计划操作回收脚本
    #缺勤自动审核脚本
    $schedule->command("collection/audit-absence >>{$dirStr}/_audit_absence_{$dateStr}.log 2>&1 &")->cron('* * * * *');#自动审核缺勤脚本
    #缺勤回收分派脚本
    $schedule->command("collection/absence-recycle-dispatch >>{$dirStr}/_absence_recycle_dispatch_{$dateStr}.log 2>&1 &")->cron('* * * * *');#缺勤自动回收分派脚本


    #############################  统计脚本

//    $schedule->command("collection/order-overview-statistics1 >>{$dirStr}/_order-overview-statistics1{$dateStr}.log 2>&1 &")->cron('30 */6 * * *');#订单概览统计 状态和组分别
    $schedule->command("collection/worker-day-statistics >>{$dirStr}/_worker-day-statistics_{$dateStr}.log 2>&1 &")->cron('20 * * * *');#催收员每日统计
    $schedule->command("collection/order-statistics >>{$dirStr}/_order-statistics_{$dateStr}.log 2>&1 &")->cron('40 * * * *');#订单总的状况每日统计
    $schedule->command("collection/all-admin-statistic >>{$dirStr}/_all-admin-statistic_new_{$dateStr}.log 2>&1 &")->cron('42 8-23 * * *');#催收机构、人员每日工作情况
    $schedule->command("collection/total-statistics >>{$dirStr}/_all-collection-total-statistics_new_{$dateStr}.log 2>&1 &")->cron('45 8-23 * * *');#催收机构人员统计累计
    $schedule->command("collection/input-overdue-out >>{$dirStr}/_input_overdue_out_{$dateStr}.log 2>&1 &")->cron('10 * * * *');#逾期出催率统计

    $schedule->command("collection/outside-snapshot-day >>{$dirStr}/_outside_snapshot_day_{$dateStr}.log 2>&1 &")->cron('5 * * * *');#机构每日快照统计
    $schedule->command("collection/outside-day-order-data >>{$dirStr}/_outside_day_order_data_{$dateStr}.log 2>&1 &")->cron('30 * * * *');#机构每日订单统计


    $schedule->command("collection/statistics-daily >>{$dirStr}/_statistics-daily_{$dateStr}.log 2>&1 &")->cron('10 6 * * *');#每日催收统计,当天到期还款数、到期应还金额、滞纳金
//    $schedule->command("collection/order-statistics >>{$dirStr}/_order-statistics_{$dateStr}.log 2>&1 &")->cron('20 * * * *');#订单分布统计概览

    $schedule->command("collection/dispatch-overdue-day-statistics >>{$dirStr}/_dispatch_overdue_day_statistics_{$dateStr}.log 2>&1 &")->cron('0 8-23 * * *');#每日逾期天数分派统计

    $schedule->command("collection/collector-app-attendance >>{$dirStr}/_collector_app_attendance_{$dateStr}.log 2>&1 &")->cron('*/15 * * * *');#app出勤率统计脚本
    $schedule->command("collection/collector-back-day-data >>{$dirStr}/_collector_back_day_data_{$dateStr}.log 2>&1 &")->cron('*/10 * * * *');#每日催回金额脚本

    $schedule->command("collection/total-track-statistics >>{$dirStr}/_all-collection-total-track-statistics_{$dateStr}.log 2>&1 &")->cron('*/15 * * * *');#催收机构人员统计累计new

    ##催收组长消息脚本###
    $schedule->command("collection-manage-message/send-today-task >>{$dirStr}/_message_send_today_task_{$dateStr}.log 2>&1 &")->cron('55 8 * * *');#目标下达
    $schedule->command("collection-manage-message/report-personnel >>{$dirStr}/_message_report_personnel_{$dateStr}.log 2>&1 &")->cron('30 9 * * *');#上报失联/旷工/请假人员
    $schedule->command("collection-manage-message/open-case >>{$dirStr}/_message_open_case_{$dateStr}.log 2>&1 &")->cron('30 10 * * *');#开案
    $schedule->command("collection-manage-message/result-tracking >>{$dirStr}/_message_result_tracking_{$dateStr}.log 2>&1 &")->cron('0 11-16 * * *');#开案
    $schedule->command("collection-manage-message/no-repay-tracking >>{$dirStr}/_message_no_repay_tracking_{$dateStr}.log 2>&1 &")->cron('0 16-18 * * *');#有无还款结果追踪
    $schedule->command("collection-manage-message/in-hour-no-repay-tracking >>{$dirStr}/_message_in_hour_no_repay_tracking_{$dateStr}.log 2>&1 &")->cron('0 11-18 * * *');#时间段内无还款结果追踪
    $schedule->command("collection-manage-message/has-part-repayment >>{$dirStr}/_message_has_part_repayment_{$dateStr}.log 2>&1 &")->cron('30 17 * * *');#有部分还款订单结果追踪
    $schedule->command("collection-manage-message/has-can-reduce >>{$dirStr}/_message_has_can_reduce_{$dateStr}.log 2>&1 &")->cron('0 18 * * *');#有可减免订单结果追踪
    $schedule->command("collection-manage-message/has-no-promise-of-repayment >>{$dirStr}/_message_has_no_promise_of_repayment_{$dateStr}.log 2>&1 &")->cron('*/30 14-17 * * *');#有可减免订单结果追踪

    ##每天根据班表开始更新副手权限###
    $schedule->command("collection/update-deputy-user-role >>{$dirStr}/update-deputy-team-user-role-_{$dateStr}.log 2>&1 &")->cron('1 0 * * *'); //每天根据班表开始更新副手权限，即添加副手权限标识
}

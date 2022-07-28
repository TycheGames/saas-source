<?php
/**
 * @var \omnilight\scheduling\Schedule $schedule
 */

$dateStr = date('Ymd');
$dirStr = '/data/logs/app';

$schedule->exec(': > /tmp/schedule_message.log ')->dailyAt('00:01'); //清除每日日志


if (YII_ENV_PROD) { #非线上环境，手动执行

    ############################ 定时任务模式 - admin配置

    foreach (\common\models\message\MessageTimeTask::$is_export_map as $is_export => $v)
    {
        // 每日00:00发送
        $schedule->command("message-time-task/send-repayment-task-q {$is_export} >>{$dirStr}/_message_task_q_{$dateStr}.log 2>&1 &")->cron('0 0 * * *');
        // 每日00:30发送
        $schedule->command("message-time-task/send-repayment-task-q-q {$is_export} >>{$dirStr}/_message_task_q_q_{$dateStr}.log 2>&1 &")->cron('30 0 * * *');
        // 每日01:00发送
        $schedule->command("message-time-task/send-repayment-task-r {$is_export} >>{$dirStr}/_message_task_r_{$dateStr}.log 2>&1 &")->cron('0 1 * * *');
        // 每日01:30发送
        $schedule->command("message-time-task/send-repayment-task-r-r {$is_export} >>{$dirStr}/_message_task_r_r_{$dateStr}.log 2>&1 &")->cron('30 1 * * *');
        // 每日02:00发送
        $schedule->command("message-time-task/send-repayment-task-s {$is_export} >>{$dirStr}/_message_task_s_{$dateStr}.log 2>&1 &")->cron('0 2 * * *');
        // 每日02:30发送
        $schedule->command("message-time-task/send-repayment-task-s-s {$is_export} >>{$dirStr}/_message_task_s_s_{$dateStr}.log 2>&1 &")->cron('30 2 * * *');
        // 每日03:00发送
        $schedule->command("message-time-task/send-repayment-task-t {$is_export} >>{$dirStr}/_message_task_t_{$dateStr}.log 2>&1 &")->cron('0 3 * * *');
        // 每日03:30发送
        $schedule->command("message-time-task/send-repayment-task-t-t {$is_export} >>{$dirStr}/_message_task_t_t_{$dateStr}.log 2>&1 &")->cron('30 3 * * *');
        // 每日04:00发送
        $schedule->command("message-time-task/send-repayment-task-u {$is_export} >>{$dirStr}/_message_task_u_{$dateStr}.log 2>&1 &")->cron('0 4 * * *');
        // 每日04:30发送
        $schedule->command("message-time-task/send-repayment-task-u-u {$is_export} >>{$dirStr}/_message_task_u_u_{$dateStr}.log 2>&1 &")->cron('30 4 * * *');
        // 每日05:00发送
        $schedule->command("message-time-task/send-repayment-task-v {$is_export} >>{$dirStr}/_message_task_v_{$dateStr}.log 2>&1 &")->cron('0 5 * * *');
        // 每日05:30发送
        $schedule->command("message-time-task/send-repayment-task-v-v {$is_export} >>{$dirStr}/_message_task_v_v_{$dateStr}.log 2>&1 &")->cron('30 5 * * *');
        // 每日06:00发送
        $schedule->command("message-time-task/send-repayment-task-w {$is_export} >>{$dirStr}/_message_task_w_{$dateStr}.log 2>&1 &")->cron('0 6 * * *');
        // 每日06:30发送
        $schedule->command("message-time-task/send-repayment-task-w-w {$is_export} >>{$dirStr}/_message_task_w_w_{$dateStr}.log 2>&1 &")->cron('30 6 * * *');
        // 每日07:00发送
        $schedule->command("message-time-task/send-repayment-task-x {$is_export} >>{$dirStr}/_message_task_x_{$dateStr}.log 2>&1 &")->cron('0 7 * * *');
        // 每日07:30发送
        $schedule->command("message-time-task/send-repayment-task-x-x {$is_export} >>{$dirStr}/_message_task_x_x_{$dateStr}.log 2>&1 &")->cron('30 7 * * *');


        // 每日8:00发送
        $schedule->command("message-time-task/send-repayment-task-a {$is_export} >>{$dirStr}/_message_task_a_{$dateStr}.log 2>&1 &")->cron('0 8 * * *');
        // 每日8:30发送
        $schedule->command("message-time-task/send-repayment-task-a-a {$is_export} >>{$dirStr}/_message_task_a_a_{$dateStr}.log 2>&1 &")->cron('30 8 * * *');
        // 每日9:00发送
        $schedule->command("message-time-task/send-repayment-task-b {$is_export} >>{$dirStr}/_message_task_b_{$dateStr}.log 2>&1 &")->cron('0 9 * * *');
        // 每日9:30发送
        $schedule->command("message-time-task/send-repayment-task-b-b {$is_export} >>{$dirStr}/_message_task_b_b_{$dateStr}.log 2>&1 &")->cron('30 9 * * *');
        // 每日10:00发送
        $schedule->command("message-time-task/send-repayment-task-c {$is_export} >>{$dirStr}/_message_task_c_{$dateStr}.log 2>&1 &")->cron('0 10 * * *');
        // 每日10:30发送
        $schedule->command("message-time-task/send-repayment-task-c-c {$is_export} >>{$dirStr}/_message_task_c_c_{$dateStr}.log 2>&1 &")->cron('30 10 * * *');
        // 每日11:00发送
        $schedule->command("message-time-task/send-repayment-task-d {$is_export} >>{$dirStr}/_message_task_d_{$dateStr}.log 2>&1 &")->cron('0 11 * * *');
        // 每日11:30发送
        $schedule->command("message-time-task/send-repayment-task-d-d {$is_export} >>{$dirStr}/_message_task_d_d_{$dateStr}.log 2>&1 &")->cron('30 11 * * *');
        // 每日12:00发送
        $schedule->command("message-time-task/send-repayment-task-e {$is_export} >>{$dirStr}/_message_task_e_{$dateStr}.log 2>&1 &")->cron('0 12 * * *');
        // 每日12:30发送
        $schedule->command("message-time-task/send-repayment-task-e-e {$is_export} >>{$dirStr}/_message_task_e_e_{$dateStr}.log 2>&1 &")->cron('30 12 * * *');
        // 每日13:00发送
        $schedule->command("message-time-task/send-repayment-task-f {$is_export} >>{$dirStr}/_message_task_f_{$dateStr}.log 2>&1 &")->cron('0 13 * * *');
        // 每日13:30发送
        $schedule->command("message-time-task/send-repayment-task-f-f {$is_export} >>{$dirStr}/_message_task_f_f_{$dateStr}.log 2>&1 &")->cron('30 13 * * *');
        // 每日14:00发送
        $schedule->command("message-time-task/send-repayment-task-g {$is_export} >>{$dirStr}/_message_task_g_{$dateStr}.log 2>&1 &")->cron('0 14 * * *');
        // 每日14:30发送
        $schedule->command("message-time-task/send-repayment-task-g-g {$is_export} >>{$dirStr}/_message_task_g_g_{$dateStr}.log 2>&1 &")->cron('30 14 * * *');
        // 每日15:00发送
        $schedule->command("message-time-task/send-repayment-task-h {$is_export} >>{$dirStr}/_message_task_h_{$dateStr}.log 2>&1 &")->cron('0 15 * * *');
        // 每日15:30发送
        $schedule->command("message-time-task/send-repayment-task-h-h {$is_export} >>{$dirStr}/_message_task_h_h_{$dateStr}.log 2>&1 &")->cron('30 15 * * *');
        // 每日16:00发送
        $schedule->command("message-time-task/send-repayment-task-i {$is_export} >>{$dirStr}/_message_task_i_{$dateStr}.log 2>&1 &")->cron('0 16 * * *');
        // 每日16:30发送
        $schedule->command("message-time-task/send-repayment-task-i-i {$is_export} >>{$dirStr}/_message_task_i_i_{$dateStr}.log 2>&1 &")->cron('30 16 * * *');
        // 每日17:00发送
        $schedule->command("message-time-task/send-repayment-task-j {$is_export} >>{$dirStr}/_message_task_j_{$dateStr}.log 2>&1 &")->cron('0 17 * * *');
        // 每日17:30发送
        $schedule->command("message-time-task/send-repayment-task-j-j {$is_export} >>{$dirStr}/_message_task_j_j_{$dateStr}.log 2>&1 &")->cron('30 17 * * *');
        // 每日18:00发送
        $schedule->command("message-time-task/send-repayment-task-k {$is_export} >>{$dirStr}/_message_task_k_{$dateStr}.log 2>&1 &")->cron('0 18 * * *');
        // 每日18:30发送
        $schedule->command("message-time-task/send-repayment-task-k-k {$is_export} >>{$dirStr}/_message_task_k_k_{$dateStr}.log 2>&1 &")->cron('30 18 * * *');
        // 每日19:00发送
        $schedule->command("message-time-task/send-repayment-task-l {$is_export} >>{$dirStr}/_message_task_l_{$dateStr}.log 2>&1 &")->cron('0 19 * * *');
        // 每日19:30发送
        $schedule->command("message-time-task/send-repayment-task-l-l {$is_export} >>{$dirStr}/_message_task_l_l_{$dateStr}.log 2>&1 &")->cron('30 19 * * *');
        // 每日20:00发送
        $schedule->command("message-time-task/send-repayment-task-m {$is_export} >>{$dirStr}/_message_task_m_{$dateStr}.log 2>&1 &")->cron('0 20 * * *');
        // 每日20:30发送
        $schedule->command("message-time-task/send-repayment-task-m-m {$is_export} >>{$dirStr}/_message_task_m_m_{$dateStr}.log 2>&1 &")->cron('30 20 * * *');
        // 每日21:00发送
        $schedule->command("message-time-task/send-repayment-task-n {$is_export} >>{$dirStr}/_message_task_n_{$dateStr}.log 2>&1 &")->cron('0 21 * * *');
        // 每日21:30发送
        $schedule->command("message-time-task/send-repayment-task-n-n {$is_export} >>{$dirStr}/_message_task_n_n_{$dateStr}.log 2>&1 &")->cron('30 21 * * *');
        // 每日22:00发送
        $schedule->command("message-time-task/send-repayment-task-o {$is_export} >>{$dirStr}/_message_task_o_{$dateStr}.log 2>&1 &")->cron('0 22 * * *');
        // 每日22:30发送
        $schedule->command("message-time-task/send-repayment-task-o-o {$is_export} >>{$dirStr}/_message_task_o_o_{$dateStr}.log 2>&1 &")->cron('30 22 * * *');
        // 每日23:00发送
        $schedule->command("message-time-task/send-repayment-task-p {$is_export} >>{$dirStr}/_message_task_p_{$dateStr}.log 2>&1 &")->cron('0 23 * * *');
        // 每日23:30发送
        $schedule->command("message-time-task/send-repayment-task-p-p {$is_export} >>{$dirStr}/_message_task_p_p_{$dateStr}.log 2>&1 &")->cron('30 23 * * *');
    }

    ################################# 内部事件短信推送脚本
    $schedule->command("order/inside-event-push >>{$dirStr}/_inside-event-push-_{$dateStr}.log 2>&1 &")->cron('* * * * *');#内部推送脚本
    $schedule->command("order/event-message >>{$dirStr}/_event-message-_{$dateStr}.log 2>&1 &")->cron('* * * * *');#内部推送脚本
//    $schedule->command("order/remind-draw-money-auto >>{$dirStr}/_order_remind_draw_money_auto_{$dateStr}.log 2>&1 &")->cron('* * * * *'); //提现提醒脚本
    $schedule->command("order/bind-card-reject >>{$dirStr}/_bind-card-reject_{$dateStr}.log 2>&1 &")->cron('* * * * *'); //绑卡决绝 提醒绑卡
    $schedule->command("order/remind-no-loan-after-repay-auto >>{$dirStr}/_order/remind-no-loan-after-repay-auto_{$dateStr}.log 2>&1 &")->cron('* * * * *'); //

    ################################# 检索用户短信收款金额队列
    $schedule->command("remind/select-user-message-keyword >>{$dirStr}/select-user-message-keyword-_{$dateStr}.log 2>&1 &")->cron('* * * * *'); //检索用户短信收款金额队列


}

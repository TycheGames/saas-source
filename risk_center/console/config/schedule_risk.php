<?php
/**
 * @var \omnilight\scheduling\Schedule $schedule
 */

$dateStr = date('Ymd');
$dirStr = '/data/logs/app';

$schedule->exec(': > /tmp/schedule_risk.log ')->dailyAt('00:01'); //清除每日日志

if (YII_ENV_PROD) {
    for ($i = 1; $i <= 15; $i++){
        $schedule->command("order/auto-check 1 {$i} >> {$dirStr}/auto_check_{$dateStr}_1_{$i}.log 2>&1 &")->cron('* * * * *');
    }
    for ($i = 1; $i <= 25; $i++){
        $schedule->command("order/auto-check 2 {$i} >> {$dirStr}/auto_check_{$dateStr}_2_{$i}.log 2>&1 &")->cron('* * * * *');
    }
    for ($i = 1; $i <= 3; $i++){
        $schedule->command("order/user-credit {$i} >> {$dirStr}/user_credit_{$dateStr}_{$i}.log 2>&1 &")->cron('* * * * *');
    }

}



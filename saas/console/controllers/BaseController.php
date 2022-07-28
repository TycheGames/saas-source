<?php

namespace console\controllers;

use yii\console\Controller;
use yii;

class BaseController extends Controller {


    /**
     * @param $message
     */
    protected function printMessage($message)
    {
        $pid = function_exists('posix_getpid') ? posix_getpid() : get_current_user();
        $date = date('Y-m-d H:i:s');
        $mem = floor(memory_get_usage(true) / 1024 / 1024) . 'MB';
        //时间 进程号 内存使用量 日志内容
        echo "{$date} {$pid} $mem {$message}".PHP_EOL;
    }


    protected  function lock($lock_name = NULL)
    {
        if (empty($lock_name)) {
            $backtrace = debug_backtrace(null, 2);
            $class = $backtrace[1]['class']; # self::class
            $func = $backtrace[1]['function'];
            $args = implode('_', $backtrace[1]['args']);
            $lock_name = base64_encode($class . $func . $args);
        }
        $lock = yii::$app->mutex->acquire($lock_name);
        if (!$lock) {
            return false;
        }

        register_shutdown_function(function () use ($lock_name) {
            return yii::$app->mutex->release($lock_name);
        });

        return TRUE;
    }
}


<?php

if(!YII_ENV_DEV){
    $logPath = '/data/logs/code/';
}else{
    $logPath = '@runtime/logs/';
}
if(PHP_SAPI === 'cli'){
    $log_prefix_name = 'console';
}else{
    $log_prefix_name = basename(realpath('../'));
}

$_info_except = ['yii\web\Session*', 'yii\db\*', 'yii\mongodb\*', 'yii\base\UserException','yii\web\HttpException:403', 'yii\web\User::login', 'auth_info'];


return [
    'aliases' => [
        '@bower' => dirname(dirname(__DIR__)) . '/node_modules',
        '@npm'   => dirname(dirname(__DIR__)) . '/node_modules',
    ],
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'timeZone' => 'Asia/Kolkata',
    'language' => 'zh-CN',
    'components' => [
        'i18n' => [
            'translations' => [
                '*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@common/messages',
                    'fileMap' => [
                        'common' => 'common.php',
                    ],
                ],
            ],
        ],
        'cache' => [
            'class' => \yii\redis\Cache::class,
            'redis' => 'redis',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'flushInterval' => 1,
            'targets' => [
                [
                    'class' => \common\components\JsonFileTarget::class,
                    'levels' => ['error', 'warning','info'],
                    'logFile' => $logPath . "{$log_prefix_name}.log",
                    'maxFileSize' => 204800,
                    'maxLogFiles' => 1,
                    'exportInterval'=> 1,//不缓存日志
                    'logVars' => [],//不打印额外信息
                    'except' => $_info_except,
                ],
                [
                    'class'          => \common\components\AuthJsonFileTarget::class,
                    'levels'         => ['error', 'warning', 'info'],
                    'logFile'        => $logPath . "auth_info.json",
                    'categories'     => ['auth_info'],
                    'maxFileSize'    => 204800,
                    'maxLogFiles'    => 1,
                    'exportInterval' => 1,//不缓存日志
                    'logVars'        => [],//不打印额外信息
                ],
            ],
        ],
    ],
];

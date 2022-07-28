<?php
$env = get_cfg_var('env');
$env = $env ? $env : 'dev';
defined('YII_ENV') or define('YII_ENV', $env);
defined('YII_DEBUG') or define('YII_DEBUG', YII_ENV != 'prod');

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../../vendor/yiisoft/yii2/Yii.php';
require __DIR__ . '/../../common/config/bootstrap.php';
require __DIR__ . '/../config/bootstrap.php';

$config = yii\helpers\ArrayHelper::merge(
    require __DIR__ . '/../../common/config/main.php',
    require(__DIR__ . '/../../environments/' . YII_ENV . '/common/config/main.php'),
    require __DIR__ . '/../config/main.php',
    require(__DIR__ . '/../../environments/' . YII_ENV . '/frontend/config/main.php')
);

(new yii\web\Application($config))->run();

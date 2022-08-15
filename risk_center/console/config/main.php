<?php
$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/params.php',
    require(__DIR__ . '/../../environments/' . YII_ENV . '/common/config/params.php')
);

return [
    'id' => 'app-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'console\controllers',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'controllerMap' => [
        'fixture' => [
            'class' => 'yii\console\controllers\FixtureController',
            'namespace' => 'common\fixtures',
          ],
    ],
    'components' => [
        'mutex'        => [
            'class' => \yii\mutex\FileMutex::class,
            'mutexPath' => YII_ENV_PROD ? '/tmp/mutex' : '/tmp/risk_center/mutex',
        ],
        'errorHandler' => [
            'class' => \console\components\ErrorHandler::class,
        ],
    ],
    'params' => $params,
];

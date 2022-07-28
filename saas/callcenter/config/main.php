<?php
$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/params.php',
    require(__DIR__ . '/../../environments/' . YII_ENV . '/common/config/params.php'),
    require(__DIR__ . '/../../environments/' . YII_ENV . '/callcenter/config/params.php')
);

return [
    'id' => 'callcenter',
    'name' => '催收中心',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'defaultRoute' => 'main/index',
    'controllerNamespace' => 'callcenter\controllers',
    'components' => [
        'errorHandler' => [
            'class' => \common\components\ErrorHandler::class,

        ],
        'request' => [
            'cookieValidationKey' => 'Ns0-5LAjvfa6E9_U37TrWX0d7F9Sc-CY',
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ],
            'secureHeaders' => [
                // Common:
                'X-Forwarded-Host',

                // Microsoft:
                'Front-End-Https',
                'X-Rewrite-Url',
            ]
        ],
        'view' => [
            'class' => \callcenter\components\View::class,
        ],
        'user' => [
            'identityClass' => \callcenter\models\AdminUser::class,
            'loginUrl' => ['main/login'],
        ],
        'session' => [
            'class' => \yii\redis\Session::class,
            'redis' => 'redis',
            'name' => 'callcenter_session',
            'keyPrefix' => 'callcenter:',
            'timeout' => 259200,
            'cookieParams' => [
                'lifetime' => 259200,
                'httponly' => true
            ],
        ],

    ],

    'params' => $params,
];

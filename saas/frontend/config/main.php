<?php
$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/params.php',
    require(__DIR__ . '/../../environments/' . YII_ENV . '/common/config/params.php'),
    require(__DIR__ . '/../../environments/' . YII_ENV . '/frontend/config/params.php')
);



return [
    'id' => 'app-frontend',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'frontend\controllers',
    'defaultRoute' => 'app/home',
    'components' => [
        'request' => [
            'csrfParam' => '_csrf-frontend',
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
        'user' => [
            'identityClass' => 'common\models\user\LoanPerson',
            'enableAutoLogin' => false,
            'identityCookie' => ['name' => '_identity-frontend', 'httpOnly' => true],
            'loginUrl' => null,
        ],
        'session' => [
            'class' => yii\redis\Session::class,
            'redis' => 'redis',
            'name' => 'SESSIONID',
            'keyPrefix' => 'loan_user:',
            'timeout' => 259200, //3 * 24 * 3600,
            'cookieParams' => [
                'lifetime' => 604800,
                'httponly' => true,
                'domain' => '',
            ],
        ],
        'errorHandler' => [
            'class' => \common\components\ErrorHandler::class,
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                '<controller:[\w\-]+>/<action:[\w\-]+>' => '<controller>/<action>',
                '<controller:[\w\-]+>/\w+!<action:[\w\-]+>' => '<controller>/<action>',
            ],
        ],
    ],
    'params' => $params,
];

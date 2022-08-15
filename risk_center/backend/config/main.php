<?php
$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/params.php',
    require(__DIR__ . '/../../environments/' . YII_ENV . '/common/config/params.php'),
    require(__DIR__ . '/../../environments/' . YII_ENV . '/backend/config/params.php')
);

return [
    'id' => 'app-backend',
    'basePath' => dirname(__DIR__),

    'defaultRoute' => 'main/index',  ###
    'controllerNamespace' => 'backend\controllers',
    'bootstrap' => ['log'],
    'modules' => [],
    'components' => [
        'request' => [
            'cookieValidationKey' => 'OtfZwulqNO2p0S-mR7jkq_kIdJENp6yH',
        ],
        'user' => [
            'identityClass' => \backend\models\AdminUser::class,
            'loginUrl' => ['main/login'],
        ],
        'view' => [
            'class' => \backend\components\View::class,
        ],
        'session' => [
            'class' => \yii\redis\Session::class,
            'redis' => 'redis', // 使用redis做session
            'name' => 'backend_session', // 与后台区分开会话key，保证前后台能同时单独登录
            'timeout' => 604800, //7 * 24 * 3600,
            'keyPrefix' => 'backend:',
            'cookieParams' => [
                'lifetime' => 604800, #12 * 3600,
                'httponly' => true,
            ],
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'errorHandler' => [
            'class' => \common\components\ErrorHandler::class,
        ],
        'assetManager'=>[
            'bundles'=>[
                'yii\web\JqueryAsset'=>[
                    'jsOptions'=>[
                        'position'=>\yii\web\View::POS_HEAD,
                    ]
                ]
            ]
        ],

        /*
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
            ],
        ],
        */
    ],
    'params' => $params,
];

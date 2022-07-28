<?php
return [
    'adminEmail' => '627335893@qq.com',
    'apiList' => [
        [
            'class' => \frontend\controllers\AppController::class,
            'label' => 'app配置接口',
        ],
        [
            'class' => \frontend\controllers\LoanController::class,
            'label' => '借款接口',
        ],
        [
            'class' => \frontend\controllers\UserController::class,
            'label' => '用户接口',
        ],
        [
            'class' => \frontend\controllers\AgreementController::class,
            'label' => '协议接口',
        ],
    ],
    // 权限配置Controller,只能是后台backend命名空间下的
    'permissionControllers' => [
        'BackEndAdminUserController' => 'Admin user',
        'ProductSettingController'=>'Product setting',
        'UserController'=>'User management',
        'LoanOrderController'=>'LoanOrder',
        'FinancialController'=>'Financial',
        'CreditAuditController'=>'Credit audit',
        'DecisionTreeController' => 'Risk Decision',
        'MessageTimeTaskController'=>'Message Sms',
        'DataStatsController'=>'Data Stats',
        'DataStatsFullPlatformController'=>'Data Stats Full Platform',
        'RepayOrderController' => 'Repay Order',
        'VersionController' => 'Version',
        'DevelopmentToolsController' => 'development tools',
        'CustomerController' => 'Customer',
        'UserCouponController' => 'UserCoupon',
        'AppController' => 'AppSetting',
        'QuestionController' => 'QuestionSetting',
    ],
];

<?php

use yii\helpers\Url;

//yii::$app->language = 'zh-CN';
$topmenu = $menu = [];

//一级菜单
$topmenu = [
    'index'            => [Yii::T('common', 'Home'), Url::to(['main/home'])],
    'risk'             => [Yii::T('common', 'Risk'), Url::to(['decision-tree/rule-list'])],
    'system'           => [Yii::T('common', 'System'), Url::to(['back-end-admin-user/list'])],
    'developmentTools' => [Yii::T('common', 'Tools'), Url::to(['development-tools/clear-schema-cache'])],
];

//二级菜单-首页
$menu['index'] = [
    'menu_home'          => ['Home', Url::to(['main/home'])],
];

$menu['risk'] = [
    'menu_risk_begin'   => [Yii::T('common', 'DecisionTreeManagement'), 'groupbegin'],
    'menu_rule_list'    => [Yii::T('common', 'ruleManagement'), Url::to(['decision-tree/rule-list'])],
    'menu_rule_test'    => [Yii::T('common', 'ruleTest'), Url::to(['decision-tree/rule-test'])],
    'menu_version_list' => array(Yii::T('common', 'versionConfig'), Url::to(['decision-tree/version-list'])),
    'menu_risk_end'     => [Yii::T('common', 'DecisionTreeManagement'), 'groupend'],
];


//二级菜单-系统管理
$menu['system'] = [
    //系统管理员
    'menu_adminuser_begin'            => [Yii::T('common', 'System administrator'), 'groupbegin'],
    'menu_adminuser_list'             => [Yii::T('common', 'Administrator management'), Url::to(['back-end-admin-user/list'])],
    'menu_adminuser_role_list'        => [Yii::T('common', 'Role management'), Url::to(['back-end-admin-user/role-list'])],
    'menu_adminuser_visit_list'       => [Yii::T('common', 'Operation record'), Url::to(['back-end-admin-user/visit-list'])],
    'menu_adminuser_end'              => [Yii::T('common', 'System administrator'), 'groupend'],
];

$menu['developmentTools'] = [
    'menu_development_begin'                 => [Yii::T('common', 'Tools'), 'groupbegin'],
    'menu_development_clear_schema_cache'    => [Yii::T('common', 'Table structure cache cleaning'), Url::to(['development-tools/clear-schema-cache'])],
    'menu_development_push_redis'            => [Yii::T('common', 'Re-enter the risk control queue'), Url::to(['development-tools/push-redis'])],
    'menu_development_redis_list'         => ['redis队列', Url::to(['development-tools/redis-list'])],
    'menu_development_end'                   => [Yii::T('common', 'Tools'), 'groupend'],
];




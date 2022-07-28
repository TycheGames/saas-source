<?php
use yii\helpers\Url;

// 一级菜单
$topmenu = array (
    'index'             => array('index', Url::to(['collection/my-work'])),
    'workbench' 		=> array('work', Url::to(['work-desk/admin-collection-order-list'])),
    'manage' 			=> array('manage', Url::to(['collection/collection-order-list'])),
);


$menu['index'] 	= array(
    'menu_home'			=> array('home', Url::to(['collection/my-work'])),
);

// 二级菜单
$menu['workbench'] 	= array(

    'menu_admin_order_begin'		=> array('order', 'groupbegin'),
    'menu_admin_order_list'			=> array('my order list', Url::to(['work-desk/admin-collection-order-list'])),
//    'menu_admin_order_list_1'     => array('有偿还意愿列表',Url::to(['work-desk/admin-purpose-collection-order-list'])),
    'menu_admin_record_list'     => array('my record list',Url::to(['work-desk/admin-record-list'])),
    'menu_admin_order_end'			=> array('order', 'groupend'),

);

$menu['manage'] 	= array(
    'menu_order_collection_begin'	=> array('order', 'groupbegin'),
    'menu_order_list'				=> array('all order list', Url::to(['collection/collection-order-list'])),
    'menu_dispatch_to_person_by_rule'   => array('dispatch to collector', Url::to(['collection/dispatch-to-person-by-rule'])),
    'menu_dispatch_order_list'      => array('wait collection list', Url::to(['collection/dispatch-order-list'])),
    'menu_collection_record_list'	=> array('collection record list', Url::to(['collection/collection-record-list'])),
    'menu_reduce_apply_list'    => array('apply reduce list',Url::to(['repay-order/apply-reduced-list'])),
    'menu_order_collection_end'	 			=> array('order', 'groupend'),

    'menu_user_collection_begin'	=> array('person', 'groupbegin'),
    'menu_user_list'				=> array('collector list', Url::to(['user-collection/user-list'])),
    'menu_sms_template_list'     => array('sms template', Url::to(['content-setting/sms-template-list'])),
    'menu_get_login_sms_code'     => ['login OTP', Url::to(['user-collection/get-login-sms-code'])],
    'menu_user_login_log'		=> array('Login Log', Url::to(['user-collection/login-log'])),
    'menu_user_collection_end'	 	=> array('person', 'groupend'),

    'menu_collection_statistics_begin'			=> array('statistics', 'groupbegin'),
    'menu_collection_day_data_list'	 			=> array('people‘s daily work', Url::to(['collection-statistics/loan-collection-day-data-list'])),
    'menu_collection_statistics_end'	 		=> array('statistics', 'groupend'),


);


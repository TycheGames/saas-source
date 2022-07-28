<?php
use yii\helpers\Url;

// 一级菜单
$topmenu = array (
    'index'             => array('index', Url::to(['work-desk/my-work'])),
    'workbench' 		=> array('work', Url::to(['work-desk/admin-collection-order-list'])),
);

$menu['index'] 	= array(
    'menu_home'			=> array('home', Url::to(['work-desk/my-work'])),
);


// 二级菜单
$menu['workbench'] 	= array(
    'menu_admin_order_begin'		=> array('order', 'groupbegin'),
    'menu_admin_order_list'			=> array('my orders list', Url::to(['work-desk/admin-collection-order-list'])),
    'menu_admin_record_list'     => array('my record list',Url::to(['work-desk/admin-record-list'])),
    'menu_admin_order_end'			=> array('order', 'groupend'),
);


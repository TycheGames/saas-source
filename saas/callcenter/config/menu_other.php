<?php
use yii\helpers\Url;
use common\models\Setting;
use common\models\LoanProject;

// 一级菜单
$topmenu = array (
    'manage' 			=> array('manage', Url::to(['collection/collection-order-list'])),
);

$menu['manage'] 	= array(
    'menu_order_collection_begin'	=> array('order', 'groupbegin'),
    'menu_order_list'				=> array('all order list', Url::to(['collection/collection-order-list'])),
    'menu_collection_record_list'	=> array('collection record list', Url::to(['collection/collection-record-list'])),
    'menu_order_collection_end'	 			=> array('order', 'groupend'),

    'menu_user_collection_begin'	=> array('person company', 'groupbegin'),
    'menu_user_list'				=> array('collector list', Url::to(['user-collection/user-list'])),
    'menu_user_login_log'		=> array('Login Log', Url::to(['user-collection/login-log'])),
    'menu_user_collection_end'	 	=> array('person and company', 'groupend'),

    'menu_collection_statistics_begin'			=> array('statistics', 'groupbegin'),
    'menu_collection_track_statistics'	 		=> array(Yii::T('common', 'Tracking Data Statistics'), Url::to(['collection-statistics/loan-collection-admin-track'])),
    'menu_collection_work_statistics'	 		=> array(Yii::T('common', 'Daily Statistics'), Url::to(['collection-statistics/loan-collection-admin-work-list'])),
    'menu_collector_attendance_day_data'	    => array(Yii::T('common', 'Collector Attendance'), Url::to(['collection-statistics/collector-attendance-day-data'])),
    'menu_collector_back_money_data'	        => array('people‘s daily finish', Url::to(['collection-statistics/collector-back-money-data2'])),
    'menu_collector_nx_phone_data_data'	    => array(Yii::T('common', 'Collector NX Log'), Url::to(['collection-statistics/collector-nx-phone-data'])),
    'menu_collection_statistics_end'	 		=> array('statistics', 'groupend'),
);

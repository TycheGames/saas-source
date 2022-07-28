<?php
use yii\helpers\Url;
use common\models\Setting;
use common\models\LoanProject;

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
//    'menu_admin_csz_order_list'     => array('催收中列表',Url::to(['work-desk/admin-in-collection-order-list'])),
//    'menu_admin_cnhk_order_list'     => array('承诺还款列表',Url::to(['work-desk/admin-promise-collection-order-list'])),
//    'menu_admin_order_list_1'     => array('有偿还意愿列表',Url::to(['work-desk/admin-purpose-collection-order-list'])),
    'menu_admin_record_list'     => array('my record list',Url::to(['work-desk/admin-record-list'])),
    'menu_admin_order_end'			=> array('order', 'groupend'),

);

$menu['manage'] 	= array(
    'menu_order_collection_begin'	=> array('order', 'groupbegin'),
    'menu_order_list'				=> array('all order list', Url::to(['collection/collection-order-list'])),
    'menu_dispatch_to_company_by_rule' => array('dispatch to company', Url::to(['collection/dispatch-to-company-by-rule'])),
    'menu_dispatch_to_person_by_rule'   => array('dispatch to collector', Url::to(['collection/dispatch-to-person-by-rule'])),
    'menu_company_dispatch_order_list'  => array('wait to company list', Url::to(['collection/company-dispatch-order-list'])),
    'menu_dispatch_order_list'      => array('wait collection list', Url::to(['collection/dispatch-order-list'])),
    'menu_collection_record_list'	=> array('collection record list', Url::to(['collection/collection-record-list'])),
    'menu_collection_order_dispatch_log'	=> array('dispatch log', Url::to(['collection/collection-order-dispatch-log'])),
    'menu_order_status_change_log_list'		=> array('status change list', Url::to(['collection/collection-status-change-list'])),
    'menu_admin_status_suggest_log_list'    => array('suggest list',Url::to(['collection/admin-collection-status-suggest-list'])),
    'menu_reduce_apply_list'    => array('apply reduce list',Url::to(['repay-order/apply-reduced-list'])),
    'menu_order_collection_end'	 			=> array('order', 'groupend'),

    'menu_user_collection_begin'	=> array('person company', 'groupbegin'),
    'menu_company_list'				=> array('company list', Url::to(['user-company/company-lists'])),
    'menu_admin_list'				=> array('admin list', Url::to(['user-collection/admin-list'])),
    'menu_user_list'				=> array('collector list', Url::to(['user-collection/user-list'])),
    'menu_monitor_list'		        => array('monitor list', Url::to(['user-collection/monitor-list'])),
    'menu_sms_template_list'     => array('sms template', Url::to(['content-setting/sms-template-list'])),
    'menu_get_login_sms_code'     => ['login OTP', Url::to(['user-collection/get-login-sms-code'])],
    'menu_user_login_log'		=> array('Login Log', Url::to(['user-collection/login-log'])),
    'menu_user_collection_end'	 	=> array('person and company', 'groupend'),


    'menu_collection_statistics_begin'			=> array('statistics', 'groupbegin'),
    'menu_collection_track_statistics'	 		=> array(Yii::T('common', 'Tracking Data Statistics'), Url::to(['collection-statistics/loan-collection-admin-track'])),
    'menu_collection_work_statistics'	 		=> array(Yii::T('common', 'Daily Statistics'), Url::to(['collection-statistics/loan-collection-admin-work-list'])),
    'menu_input_overdue_day_out'		    	=> array(Yii::T('common', 'Collector Rate(By Orders)'), Url::to(['collection-statistics/input-overdue-day-out'])),
    'menu_input_overdue_day_out_amount'		=> array(Yii::T('common', 'Collector Rate（By Amount'), Url::to(['collection-statistics/input-overdue-day-out-amount'])),
    'menu_dispatch_outside_finish'		        => array(Yii::T('common', 'Organization Daily Order Statistics'), Url::to(['collection-statistics/dispatch-outside-finish'])),
//    'menu_collection_every_day_chart'	 		=> array('每日催回率', Url::to(['collection-statistics/every-day-chart'])),
    'menu_dispatch_overdue_days_finish'	    => array(Yii::T('common', 'Distribute Order Rate'), Url::to(['collection-statistics/dispatch-overdue-days-finish'])),
    'menu_collector_back_money_data'	        => array(Yii::T('common', 'Collector’s Daily Complete Amount'), Url::to(['collection-statistics/collector-back-money-data2'])),
    'menu_collection_statistics_end'	 		=> array('statistics', 'groupend'),


);


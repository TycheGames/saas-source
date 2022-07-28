<?php
use yii\helpers\Url;
use common\models\Setting;
use common\models\LoanProject;

// 一级菜单
$topmenu = array (
    'index'             => array('index', Url::to(['work-desk/my-work'])),
    'workbench' 		=> array('work', Url::to(['work-desk/admin-collection-order-list'])),
    'manage' 			=> array('manage', Url::to(['collection/collection-order-list'])),
    'system'            => array('system', Url::to(['admin-user/list'])),
);



$menu['index'] 	= array(
    'menu_home'			=> array('home', Url::to(['work-desk/my-work'])),
);

// 二级菜单
$menu['workbench'] 	= array(

    'menu_admin_order_begin'		=> array('order', 'groupbegin'),
    'menu_admin_order_list'			=> array('my order list', Url::to(['work-desk/admin-collection-order-list'])),
    'menu_admin_record_list'     => array('my record list',Url::to(['work-desk/admin-record-list'])),
    'menu_dispatch_script_task_list' => ['dispatch script task', Url::to(['dispatch-script/list'])],
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
    'menu_collection_stop_list'    => array('collection stop list',Url::to(['collection/collection-stop-list'])),
    'menu_order_collection_end'	 			=> array('order', 'groupend'),

    'menu_user_collection_begin'	=> array('person company', 'groupbegin'),
    'menu_company_list'				=> array('company list', Url::to(['user-company/company-lists'])),
    'menu_user_list'				=> array('collector list', Url::to(['user-collection/user-list'])),
    'menu_team_leader_list'		    => array('team leader list', Url::to(['user-collection/team-leader-list'])),
    'menu_big_team_leader_list'		=> array('big team leader list', Url::to(['user-collection/big-team-leader-list'])),
    'menu_monitor_list'		        => array('monitor list', Url::to(['user-collection/monitor-list'])),
    'menu_super_team_leader_list'	=> ['super team leader list', Url::to(['user-collection/super-team-leader-list'])],
    'menu_admin_list'				=> array('admin list', Url::to(['user-collection/admin-list'])),
    'menu_sms_template_list'     => array('sms template', Url::to(['content-setting/sms-template-list'])),
    'menu_get_login_sms_code'     => ['login OTP', Url::to(['user-collection/get-login-sms-code'])],
    'menu_user_login_log'		=> array('Login Log', Url::to(['user-collection/login-log'])),
    'menu_user_class_schedule'		=> array('Daily Work Plan', Url::to(['user-collection/class-schedule'])),
    'menu_team_user_class_schedule'		=> array('Daily Work Plan(TL)', Url::to(['user-collection/team-leader-class-schedule'])),
    'menu_my_message_list'		=> array('my message', Url::to(['user-collection/my-message-list'])),
    'menu_user_collection_end'	 	=> array('person and company', 'groupend'),

    'menu_collection_statistics_begin'			=> array('statistics', 'groupbegin'),
    'menu_collection_order_statistics'			=> array('total input and out', Url::to(['collection-statistics/order-statistics'])),
    'menu_collection_track_statistics'	 		=> array(Yii::T('common', 'Tracking Data Statistics'), Url::to(['collection-statistics/loan-collection-admin-track'])),
    'menu_total_track_statistics'	 		=> array(Yii::T('common', 'New Tracking Data Statistics'), Url::to(['collection-statistics-new/total-admin-track'])),
    'menu_collection_work_statistics'	 		=> array(Yii::T('common', 'Daily Statistics'), Url::to(['collection-statistics/loan-collection-admin-work-list'])),
    'menu_input_overdue_day_out'		    	=> array(Yii::T('common', 'Collector Rate(By Orders)'), Url::to(['collection-statistics/input-overdue-day-out'])),
    'menu_input_overdue_day_out_all_label'		=> array(Yii::T('common', 'Collector Rate(By Orders All Label)'), Url::to(['collection-statistics/input-overdue-day-out-all-label'])),
    'menu_input_overdue_day_out_amount'		=> array(Yii::T('common', 'Collector Rate(By Amount)'), Url::to(['collection-statistics/input-overdue-day-out-amount'])),
    'menu_input_overdue_day_out_amount_all_label'		=> array(Yii::T('common', 'Collector Rate(By Amount All Label)'), Url::to(['collection-statistics/input-overdue-day-out-amount-all-label'])),
    'menu_dispatch_outside_finish'		        => array(Yii::T('common', 'Organization Daily Order Statistics'), Url::to(['collection-statistics/dispatch-outside-finish'])),
//    'menu_collection_every_day_chart'	 		=> array('每日催回率', Url::to(['collection-statistics/every-day-chart'])),
    'menu_dispatch_overdue_days_finish'	    => array(Yii::T('common', 'Distribute Order Rate'), Url::to(['collection-statistics/dispatch-overdue-days-finish'])),
    'menu_collector_attendance_day_data'	    => array(Yii::T('common', 'Collector Attendance'), Url::to(['collection-statistics/collector-attendance-day-data'])),
    'menu_collector_back_money_data'	        => array(Yii::T('common', 'Collector’s Daily Complete Amount'), Url::to(['collection-statistics/collector-back-money-data2'])),
    'menu_collector_day_one_data'	        => array(Yii::T('common', 'Collector Day One Data'), Url::to(['collection-statistics/collector-day-one-data'])),
    'menu_collection_statistics_end'	 		=> array('statistics', 'groupend'),


);

$menu['system'] = array(
    'menu_adminuser_begin'			=> array('系统管理员', 'groupbegin'),
    'menu_adminuser_list'			=> array('管理员管理', Url::to(['admin-user/list'])),
    'menu_adminuser_role_list'		=> array('角色管理', Url::to(['admin-user/role-list'])),
    'menu_adminuser_lock_list'		=> array('账号解锁', Url::to(['admin-user/lock-list'])),
    'menu_adminuser_operate_list'		=> array('访问记录', Url::to(['admin-user/collection-operate-list'])),
    'menu_adminuser_nx_list'		=> array('催收员牛信账号绑定', Url::to(['admin-user/nx-list'])),
    'menu_team_message_task'     => ['组长任务消息配置', Url::to(['content-setting/team-message-task'])],
    'menu_adminuser_end'	 		=> array('系统管理员', 'groupend'),
);


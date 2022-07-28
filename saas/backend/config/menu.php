<?php

use yii\helpers\Url;

//yii::$app->language = 'zh-CN';
$topmenu = $menu = [];

//一级菜单
$topmenu = [
    'index'      => [Yii::T('common', 'Home'), Url::to(['main/home'])],
    'dataStats'  => [Yii::T('common', 'Data'), Url::to(['data-stats/daily-trade-data'])],
    'user'       => [Yii::T('common', 'User'), Url::to(['user/list'])],
    'loanOrder'  => [Yii::T('common', 'Order'), Url::to(['loan-order/list'])],
    'content'    => [Yii::T('common', 'Content'), Url::to(['message-time-task/list', 'is_export'=>'0'])],
    'creditAudit' => [Yii::T('common', 'Audit'), Url::to(['credit-audit/bank-list'])],
//    'risk'    => [Yii::T('common', 'Risk'), Url::to(['decision-tree/rule-list'])],
    'fund'      => [Yii::T('common', 'Capital'), Url::to(['loan-fund/index'])],
    'customer'   => [Yii::T('common', 'Customer Service'), Url::to(['customer/loan-order'])],
    'system'     => [Yii::T('common', 'System'), Url::to(['admin-user/list'])],
    'developmentTools' => [Yii::T('common', 'Tools'), Url::to(['development-tools/clear-schema-cache'])],
    'merchantSetting' => [Yii::T('common', 'Merchants'), Url::to(['merchant-setting/merchant-list'])],
];

//二级菜单-首页
$menu['index'] = [
    'menu_home'          => ['Home', Url::to(['main/home'])],
];

$menu['merchantSetting'] = [
    'menu_merchantSetting_begin'           => ['商户管理', 'groupbegin'],
    'menu_merchant_list'              => [Yii::T('common', 'Merchants'), Url::to(['merchant-setting/merchant-list'])],
//    'menu_account_setting_list'              => [Yii::T('common', 'Account Management'), Url::to(['merchant-setting/account-list'])],
    'menu_adminuser_personal_center'  => [Yii::T('common', 'Personal center'), Url::to(['personal-center/index'])],
//    'menu_payout_account_list'       => [Yii::T('common', '放款账号'), Url::to(['merchant-setting/payout-account-list'])],
//    'menu_payout_account_setting'       => [Yii::T('common', '放款设置'), Url::to(['merchant-setting/payout-setting-list'])],
    'menu_merchantSetting_end'             => ['商户管理', 'groupend'],
];


$menu['creditAudit'] = [
    'menu_credit_audit_begin'       => [Yii::T('common', 'Bank card review'), 'groupbegin'],
    'menu_credit_audit_bank_list'   => [Yii::T('common', 'creditBankList'), Url::to(['credit-audit/bank-list'])],
    'menu_credit_audit_bank_review' => [Yii::T('common', 'myBankReview'), Url::to(['credit-audit/review-bank-list'])],
    'menu_credit_audit_end'         => [Yii::T('common', 'Bank card review'), 'groupend'],
    'menu_credit_audit_list_begin'       => [Yii::T('common', 'Credit review'), 'groupbegin'],
    'menu_credit_audit_list'        => [Yii::T('common', 'creditList'), Url::to(['credit-audit/list'])],
    'menu_credit_audit_review'      => [Yii::T('common', 'myReview'), Url::to(['credit-audit/review-list'])],
    'menu_credit_nx_phone_data'     => ['nxPhoneLog', Url::to(['credit-audit/nx-phone-data'])],
    'menu_credit_log_list'          => ['credit log list', Url::to(['credit-audit/credit-log-list'])],
    'menu_credit_audit_list_end'         => [Yii::T('common', 'Credit review'), 'groupend'],

    'menu_credit_config_begin'       => [Yii::T('common', 'CreditConf'), 'groupbegin'],
    'menu_manual_module_list'       => [Yii::T('common', 'manualModule'), Url::to(['credit-audit/manual-module-list'])],
    'menu_manual_type_list'         => [Yii::T('common', 'manualType'), Url::to(['credit-audit/manual-type-list'])],
    'menu_manual_rules_list'        => [Yii::T('common', 'manualRules'), Url::to(['credit-audit/manual-rules-list'])],
    'menu_credit_config_end'         => [Yii::T('common', 'CreditConf'), 'groupend'],
];

//$menu['risk'] = [
//    'menu_risk_begin'           => [Yii::T('common', 'DecisionTreeManagement'), 'groupbegin'],
//    'menu_rule_list' => [Yii::T('common', 'ruleManagement'),Url::to(['decision-tree/rule-list'])],
//    'menu_rule_test' => [Yii::T('common', 'ruleTest'),Url::to(['decision-tree/rule-test'])],
//    'menu_version_list'    => array(Yii::T('common', 'versionConfig'), Url::to(['decision-tree/version-list'])),
//    'menu_risk_end'             => [Yii::T('common', 'DecisionTreeManagement'), 'groupend'],
//];
//二级菜单-用户管理
$menu['user'] = [
    //用户管理
    'menu_user_begin'           => [Yii::T('common', 'User'), 'groupbegin'],
    'menu_user'                 => [Yii::T('common', 'userList'), Url::to(['user/list'])],
    'menu_user_end'             => [Yii::T('common', 'User'), 'groupend'],
];

//二级菜单-借款列表管理
$menu['loanOrder'] = [
    'menu_loan_order_begin'           => [Yii::T('common', 'LoanManagement'), 'groupbegin'],
    'menu_loan_order_list'            => [Yii::T('common', 'loanList'), Url::to(['loan-order/list'])],
    'menu_loan_order_end'             => [Yii::T('common', 'LoanManagement'), 'groupend'],

    'menu_loan_manage_begin'        => array(Yii::T('common', 'TransferManagement'), 'groupbegin'),
    'menu_loan_list'                => array(Yii::T('common', 'transferList'), Url::to(['financial/loan-list'])),
    'menu_loan_manage_end'          => array(Yii::T('common', 'TransferManagement'), 'groupend'),

    'menu_repay_order_begin'           => [Yii::T('common', 'RepaymentManagement'), 'groupbegin'],
    'menu_repay_order_list'            => [Yii::T('common', 'repaymentList'), Url::to(['repay-order/list'])],
    'menu_external_user_transfer_log'  => [Yii::T('common', 'External user transfer log'), Url::to(['repay-order/external-user-transfer-log'])],
    'menu_repayment_log_list'     => [Yii::T('common', 'repaymentLog'), Url::to(['repay-order/repayment-log-list'])],
    'menu_repay_order_reduced_log'     => [Yii::T('common', 'reduceLog'), Url::to(['repay-order/reduced-log'])],
    'menu_pay_order_list'     => [Yii::T('common', 'payOrder'), Url::to(['repay-order/pay-order-list'])],
    'menu_virtual_account_list'     => ['虚拟账号', Url::to(['repay-order/virtual-account-list'])],
    'menu_repay_order_end'             => [Yii::T('common', 'RepaymentManagement'), 'groupend'],

    'menu_kudos_order_begin'   => [Yii::T('common', 'KudosManagement'), 'groupbegin'],
    'menu_kudos_order_list'     => [Yii::T('common', 'orderList'), Url::to(['loan-order/order-list'])],
    'menu_kudos_tranche_list'  => [Yii::T('common', 'orderTranche'), Url::to(['loan-order/kudos-tranche'])],
    'menu_kudos_order_payment' => [Yii::T('common', 'orderDisburse'), Url::to(['loan-order/kudos-disburse'])],
    'menu_kudos_order_end'     => [Yii::T('common', 'KudosManagement'), 'groupend'],
];

//二级菜单-内容管理
$menu['content'] = [
    'menu_content_begin'          => [Yii::T('common', 'Content'), 'groupbegin'],
    'menu_content_msm_list'       => [Yii::T('common', 'Internal orders SMS task'), Url::to(['message-time-task/list', 'is_export' => '0'])],
    'menu_content_msm_list_export'  => [Yii::T('common', 'External orders SMS task'), Url::to(['message-time-task/list', 'is_export' => '1'])],
    'menu_content_list_slow'      => [Yii::T('common', 'couponTemplate'), Url::to(['user-coupon/list-slow'])],
    'menu_content_list'           => [Yii::T('common', 'couponList'), Url::to(['user-coupon/list'])],
    'menu_language_question_list' => [Yii::T('common', 'List of language question'), Url::to(['question/question-list'])],
    'menu_content_end'            => [Yii::T('common', 'Content'), 'groupend'],
];

//二级菜单-数据统计
$menu['dataStats'] = [
    'menu_user_data_stats_begin'     => [Yii::T('common', 'UserData'), 'groupbegin'],
    'menu_daily_trade_data_list'      => [Yii::T('common', 'Comparison of daily borrowing and repayment data'), Url::to(['data-stats/daily-trade-data'])],
    'menu_daily_trade_data_full_platform_list'      => [Yii::T('common', 'Comparison of daily borrowing and repayment data(full platform)'), Url::to(['data-stats-full-platform/daily-trade-data'])],
    'menu_data_daily_list'             => [Yii::T('common', 'Daily borrowing data (principal)'), Url::to(['data-stats/daily-data'])],
    'menu_data_daily_full_platform_list' => [Yii::T('common', 'Daily borrowing data (principal - full platform)'), Url::to(['data-stats/daily-data-full-platform'])],
    'menu_data_daily_user_structure_list' => [Yii::T('common', 'Daily borrowing data (principal - user structure)'), Url::to(['data-stats-full-platform/daily-data-user-structure'])],
    'menu_data_daily2_list'            => [Yii::T('common', 'Daily borrowing data (loans)'), Url::to(['data-stats/daily-data2'])],
    'menu_data_daily2_full_platform_list' => [Yii::T('common', 'Daily borrowing data (loans - full platform)'), Url::to(['data-stats/daily-data2-full-platform'])],
    'menu_data_daily2_user_structure_list' => [Yii::T('common', 'Daily borrowing data (loans - user structure)'), Url::to(['data-stats-full-platform/daily-data2-user-structure'])],
    'menu_data_again_loan_statistics_list'   => [Yii::T('common', 'Daily re-lending data statistics'), Url::to(['data-stats/day-again-repay-statistics'])],
    'menu_day_data_repayment_count_statistics_list'      => [Yii::T('common', 'Daily repayment order data'), Url::to(['data-stats/day-data-repayment-num-statistics'])],
    'menu_day_data_repayment_count_statistics_full_platform_list'    => [Yii::T('common', 'Daily repayment order data(full platform)'), Url::to(['data-stats/day-data-repayment-num-statistics-full-platform'])],
    'menu_day_data_repayment_count_statistics_user_structure_list'    => [Yii::T('common', 'Daily repayment order data(user structure)'), Url::to(['data-stats-full-platform/day-data-repayment-num-statistics-user-structure'])],
    'menu_day_data_repayment_count_statistics_user_structure_sub_merchant_list'    => [Yii::T('common', 'Daily repayment order data(user structure)').'sub', Url::to(['data-stats-full-platform/day-data-repayment-num-statistics-user-structure-sub-merchant'])],
    'menu_day_data_repayment_amount_statistics_list'      => [Yii::T('common', 'Daily repayment amount data'), Url::to(['data-stats/day-data-repayment-statistics'])],
    'menu_day_data_repayment_amount_statistics_full_platform_list'      => [Yii::T('common', 'Daily repayment amount data(full platform)'), Url::to(['data-stats/day-data-repayment-statistics-full-platform'])],
    'menu_day_data_repayment_amount_statistics_user_structure_list'      => [Yii::T('common', 'Daily repayment amount data(user structure)'), Url::to(['data-stats-full-platform/day-data-repayment-statistics-user-structure'])],
    'menu_total_repayment_amount_list'  => [Yii::T('common', 'Total repayment amount data'), Url::to(['data-stats/total-repayment-amount'])],
    'menu_daily_user_data_list'      => [Yii::T('common', 'User Daily Report (Registration)'), Url::to(['data-stats/daily-user-data'])],
    'menu_daily_user_full_data_list' => [Yii::T('common', 'User daily report (full volume)'), Url::to(['data-stats/daily-user-full-data'])],
    'menu_daily_user_platform_full_data_list' => [Yii::T('common', 'User daily report (full volume -all platform)'), Url::to(['data-stats/daily-user-platform-full-data'])],
//    'menu_user_data_transform_result' => [Yii::T('common', 'Daily user data conversion'), Url::to(['data-stats/user-data-transform'])],
//    'menu_user_data_transform_kyc_result' => [Yii::T('common', 'Daily user KYC conversions'), Url::to(['data-stats/user-data-transform-kyc'])],
    'menu_user_structure_order_transform' => [Yii::T('common', 'Order data transform(structure)'), Url::to(['data-stats-full-platform/user-structure-order-transform'])],
    'menu_daily_register_conver' => ['每日注册转化', Url::to(['data-stats-full-platform/daily-register-conver'])],
    'menu_amount_recovery_overview' => [Yii::T('common', 'Real-time overview'), Url::to(['data-stats/amount-recovery-overview'])],
    'menu_user_structure_export_num' => [Yii::T('common', 'User structure export num'), Url::to(['data-stats/user-structure-export-num'])],
    'menu_user_structure_export_money' => [Yii::T('common', 'User structure export money'), Url::to(['data-stats/user-structure-export-money'])],
    'menu_user_structure_source_export_num' => [Yii::T('common', 'User structure source export num'), Url::to(['data-stats/user-structure-source-export-num'])],
    'menu_user_structure_source_export_money' => [Yii::T('common', 'User structure source export money'), Url::to(['data-stats/user-structure-source-export-money'])],
    'menu_daily_data_repayment_grand_list' => [Yii::T('common', 'Daily cumulative repayment data'), Url::to(['data-stats/daily-repayment-grand'])],
    'menu_user_data_stats_end'       => [Yii::T('common', 'UserData'), 'groupend'],

    //财务数据
//    'menu_data_finance_begin'          => [Yii::T('common', 'FinancialData'), 'groupbegin'],
//    'menu_daily_data_repayment_list'   => [Yii::T('common', 'Repayment data due'), Url::to(['data-stats/daily-repayment-data'])],
//    'menu_data_finance_end'            => [Yii::T('common', 'FinancialData'), 'groupend'],
//
    'menu_risk_data_begin'              => [Yii::T('common', 'Risk control data'), 'groupbegin'],
    'menu_risk_data_pre_reject_reason'      => [Yii::T('common', 'Pre-rejected statistics'), Url::to(['data-stats/pre-reject-reason'])],
    'menu_risk_data_main_reject_reason'      => [Yii::T('common', 'Master Decision Rejected Statistics'), Url::to(['data-stats/main-reject-reason'])],
    'menu_daily_risk_reject_data_list'      => [Yii::T('common', 'Daily statistics of reasons for rejection of risk control'), Url::to(['data-stats/daily-risk-reject'])],
    'menu_risk_data_end'                => [Yii::T('common', 'Risk control data'), 'groupend'],
//
    'menu_credit_data_begin'            => [Yii::T('common', 'Letter review data'), 'groupbegin'],
    'menu_daily_credit_audit_data'      => [Yii::T('common', 'Daily statistics of letter reviewers'), Url::to(['data-stats/daily-credit-audit'])],
    'menu_credit_data_end'              => [Yii::T('common', 'Letter review data'), 'groupend'],

];

//二级菜单-系统管理
$menu['system'] = [
    //系统管理员
    'menu_adminuser_begin'            => [Yii::T('common', 'System administrator'), 'groupbegin'],
    'menu_adminuser_list'             => [Yii::T('common', 'Administrator management'), Url::to(['admin-user/list'])],
    'menu_adminuser_lock_list'        => [Yii::T('common', 'Account unlock'), Url::to(['back-end-admin-user/lock-list'])],
    'menu_adminuser_role_list'        => [Yii::T('common', 'Role management'), Url::to(['back-end-admin-user/role-list'])],
    'menu_adminuser_visit_list'       => [Yii::T('common', 'Operation record'), Url::to(['back-end-admin-user/visit-list'])],
    'menu_adminuser_nx_list'          => [Yii::T('common','Bind niuxin account'), Url::to(['back-end-admin-user/nx-list'])],
    'menu_adminuser_end'              => [Yii::T('common', 'System administrator'), 'groupend'],

    //产品设置
    'menu_product_begin'              => [Yii::T('common', 'ProductSettings'), 'groupbegin'],
    'menu_product_setting_index'      => [Yii::T('common', 'productManagement'), Url::to(['product-setting/setting-list'])],
    'menu_product_type_setting_index' => [Yii::T('common', 'productType'), Url::to(['product-setting/period-setting-list'])],
    'menu_package_setting'            => [Yii::T('common', 'Package setting'), Url::to(['package-setting/index'])],
    'menu_tab_bar_icon'               => [Yii::T('common', 'TabBarIcon'), Url::to(['tab-bar-icon/index'])],
    'menu_product_end'                => [Yii::T('common', 'ProductSettings'), 'groupend'],

    //版本控制
    'menu_version_begin'              => [Yii::T('common', 'VersionControl'), 'groupbegin'],
    'menu_check_version_config'       => [Yii::T('common', 'Version update rule configuration'), Url::to(['version/list'])],
    'menu_version_end'                => [Yii::T('common', 'VersionControl'), 'groupend'],

    //APP设置
    'menu_app_begin'                  => [Yii::T('common', 'APP settings'), 'groupbegin'],
    'menu_no_password_login'          => [Yii::T('common', 'Universal Password Login'), Url::to(['app/no-password-login'])],
    'menu_validation_switch_rule'     => [Yii::T('common', 'Authentication service routing rules'), Url::to(['app/validation-switch-rule'])],
    'menu_set_real_name_collection_admin'   => [Yii::T('common', 'Set RealName Collection Admin'), Url::to(['app/set-real-name-collection-admin'])],
    'menu_nx_phone_config'            => ['牛信电话开关', Url::to(['app/nx-phone-config'])],
    'menu_nx_phone_sdk_config'        => ['牛信电话SDK开关', Url::to(['app/nx-phone-sdk-config'])],
    'menu_app_end'                    => [Yii::T('common', 'APP settings'), 'groupend'],
];

$menu['developmentTools'] = [
    'menu_development_begin'                 => [Yii::T('common', 'Tools'), 'groupbegin'],
    'menu_development_clear_schema_cache'    => [Yii::T('common', 'Table structure cache cleaning'), Url::to(['development-tools/clear-schema-cache'])],
    'menu_development_push_redis'            => [Yii::T('common', 'Re-enter the risk control queue'), Url::to(['development-tools/push-redis'])],
    'menu_development_skip_check_list'    => ['跳过风控配置', Url::to(['development-tools/skip-check-list'])],
    'menu_development_skip_check'            => [Yii::T('common', 'Skip machine review'), Url::to(['development-tools/skip-check'])],
    'menu_development_set_id_display_status' => [Yii::T('common', 'Set id display status'), Url::to(['development-tools/set-id-display-status'])],
    'menu_development_id_decryption'         => [Yii::T('common', 'ID decryption'), Url::to(['development-tools/id-decryption'])],
    'menu_development_id_ebcryption'         => [Yii::T('common', 'ID encryption'), Url::to(['development-tools/id-encryption'])],
    'menu_development_redis_list'         => ['redis队列', Url::to(['development-tools/redis-list'])],
    'menu_development_collection_list'         => ['催收批量操作', Url::to(['development-tools/collection'])],
    'menu_development_collection_list_list'         => ['催收批量操作列表', Url::to(['development-tools/collection-list'])],
    'menu_development_get_user_otp'         => ['获取用户验证码', Url::to(['development-tools/get-user-otp'])],
    'menu_phone_finish_amount_pull'   => ['手机号完成金额信息拉取', Url::to(['development-tools/phone-finish-amount-pull'])],
    'menu_development_end'                   => [Yii::T('common', 'Tools'), 'groupend'],
];

$menu['fund'] = [
    'menu_fund_begin'          =>  [Yii::T('common', 'Capital'), 'groupbegin'],
    'menu_fund_list'           => [Yii::T('common', 'Capital'), Url::to(['loan-fund/index'])],
    'menu_fund_operate_log'    => [Yii::T('common', 'Fund operate log'), Url::to(['loan-fund/fund-log'])],
    'menu_fund_end'            =>  [Yii::T('common', 'Capital'), 'groupend'],
];

$menu['customer'] = [
    'menu_customer_begin'    =>  [Yii::T('common', 'Order Search'), 'groupbegin'],
    'menu_customer_loan_order_search'      => [Yii::T('common', 'loanOrder'), Url::to(['customer/loan-order'])],
    'menu_customer_disburse_order_search'  => ['Disburse Order', Url::to(['customer/disburse-order'])],
    'menu_customer_repay_order_search'     => [Yii::T('common', 'repayOrder'), Url::to(['customer/repay-order'])],
    'menu_customer_user_list_search'     => [Yii::T('common', 'userList'), Url::to(['customer/user-list'])],
    'menu_get_login_sms_code'     => ['Get Login OTP', Url::to(['customer/get-login-sms-code'])],
    'menu_nx_phone_data'     => ['Nx Phone Data', Url::to(['customer/nx-phone-data'])],
    'menu_customer_end'    =>  [Yii::T('common', 'Order Search'), 'groupend'],

    'menu_work_order_begin'    =>  ['Work order', 'groupbegin'],
    'menu_complaint_order_list'      => ['Complaint order', Url::to(['customer/complaint-order'])],
    'menu_work_order_end'    =>  ['Work order', 'groupend'],

    'menu_order_remind_begin'    =>  [Yii::T('common', 'Order Remind'), 'groupbegin'],
    'menu_my_remind_order_list'     => [Yii::T('common', 'My Remind Order'), Url::to(['customer/my-remind-order-list'])],
    'menu_remind_dispatch'     => [Yii::T('common', 'Remind Dispatch'), Url::to(['customer/remind-dispatch'])],
    'menu_all_remind_order_list'     => [Yii::T('common', 'All Remind Order'), Url::to(['customer/all-remind-order-list'])],
    'menu_remind_day_data'     => [Yii::T('common', 'Remind Data'), Url::to(['customer/remind-day-data'])],
    'menu_remind_reach_repay_data'     => [Yii::T('common', 'Remind Reach'), Url::to(['customer/remind-reach-repay-data'])],
    'menu_reminder_call_data'	    => ['Remind Call data', Url::to(['customer/reminder-call-data'])],
    'menu_user_class_schedule'		=> ['Daily Work Plan', Url::to(['customer/class-schedule'])],
    'menu_order_remind_end'    =>  [Yii::T('common', 'Order Remind'), 'groupend'],

    'menu_remind_begin'                  => [Yii::T('common', 'Remind Setting'), 'groupbegin'],
    'menu_remind_setting_list'     => [Yii::T('common', 'Remind Plan'), Url::to(['customer/remind-setting-list'])],
    'menu_remind_group'     => [Yii::T('common', 'Remind Group'), Url::to(['customer/remind-group'])],
    'menu_remind_admin_list'     => [Yii::T('common', 'Remind Admin'), Url::to(['customer/remind-admin-list'])],
    'menu_remind_sms_template'     => [Yii::T('common', 'Remind Sms Temp'), Url::to(['customer/remind-sms-template'])],
    'menu_remind_end'             => [Yii::T('common', 'Remind Setting'), 'groupend'],

];



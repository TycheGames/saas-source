<?php

use yii\helpers\Url;

//yii::$app->language = 'zh-CN';
$topmenu = $menu = [];

//一级菜单
$topmenu = [
    'index'      => [Yii::T('common', 'Index'), Url::to(['main/home'])],
    'dataStats'  => [Yii::T('common', 'BigData'), Url::to(['data-stats/daily-data'])],
//    'user'       => [Yii::T('common', 'Users'), Url::to(['user/list'])],
    'loanOrder'  => [Yii::T('common', 'Loan'), Url::to(['loan-order/list'])],
//    'content'    => [Yii::T('common', 'SMS'), Url::to(['message-time-task/list', 'is_export'=>'0'])],
    'creditAudit' => [Yii::T('common', 'Review'), Url::to(['credit-audit/bank-list'])],
//    'risk'    => [Yii::T('common', 'Risk'), Url::to(['decision-tree/rule-list'])],
//    'fund'      => [Yii::T('common', 'Fund'), Url::to(['loan-fund/index'])],
    'customer'   => [Yii::T('common', 'CS Remind'), Url::to(['customer/loan-order'])],
    'system'     => [Yii::T('common', 'System Center'), Url::to(['admin-user/list'])],
//    'developmentTools' => [Yii::T('common', 'Tools'), Url::to(['development-tools/clear-schema-cache'])],
//    'merchantSetting' => [Yii::T('common', 'Merchants'), Url::to(['merchant-setting/merchant-list'])],
];

//二级菜单-首页
$menu['index'] = [
    'menu_home'          => ['Home', Url::to(['main/home'])],
];

$menu['merchantSetting'] = [
    'menu_merchantSetting_begin'           => ['商户管理', 'groupbegin'],
    'menu_merchant_list'              => [Yii::T('common', 'Merchants'), Url::to(['merchant-setting/merchant-list'])],
    'menu_account_setting_list'              => [Yii::T('common', 'Account Management'), Url::to(['merchant-setting/account-list'])],
    'menu_adminuser_personal_center'  => [Yii::T('common', 'Personal center'), Url::to(['personal-center/index'])],
    'menu_merchantSetting_end'             => ['商户管理', 'groupend'],
];


$menu['creditAudit'] = [
    'menu_credit_audit_begin'       => [Yii::T('common', 'Audit'), 'groupbegin'],
    'menu_credit_audit_bank_list'   => [Yii::T('common', 'creditBankList'), Url::to(['credit-audit/bank-list'])],
    'menu_credit_audit_bank_review' => [Yii::T('common', 'myBankReview'), Url::to(['credit-audit/review-bank-list'])],
    'menu_credit_audit_list'        => [Yii::T('common', 'creditList'), Url::to(['credit-audit/list'])],
    'menu_credit_audit_review'      => [Yii::T('common', 'myReview'), Url::to(['credit-audit/review-list'])],
    'menu_credit_audit_end'         => [Yii::T('common', 'Audit'), 'groupend'],

//    'menu_credit_config_begin'       => [Yii::T('common', 'CreditConf'), 'groupbegin'],
//    'menu_manual_module_list'       => [Yii::T('common', 'manualModule'), Url::to(['credit-audit/manual-module-list'])],
//    'menu_manual_type_list'         => [Yii::T('common', 'manualType'), Url::to(['credit-audit/manual-type-list'])],
//    'menu_manual_rules_list'        => [Yii::T('common', 'manualRules'), Url::to(['credit-audit/manual-rules-list'])],
//    'menu_credit_config_end'         => [Yii::T('common', 'CreditConf'), 'groupend'],
];

$menu['risk'] = [
    'menu_risk_begin'           => [Yii::T('common', 'DecisionTreeManagement'), 'groupbegin'],
    'menu_rule_list' => [Yii::T('common', 'ruleManagement'),Url::to(['decision-tree/rule-list'])],
    'menu_rule_test' => [Yii::T('common', 'ruleTest'),Url::to(['decision-tree/rule-test'])],
    'menu_version_list'    => array(Yii::T('common', 'versionConfig'), Url::to(['decision-tree/version-list'])),
    'menu_risk_end'             => [Yii::T('common', 'DecisionTreeManagement'), 'groupend'],
];
//二级菜单-用户管理
$menu['user'] = [
    //用户管理
    'menu_user_begin'           => [Yii::T('common', 'Users'), 'groupbegin'],
    'menu_user'                 => [Yii::T('common', 'userList'), Url::to(['user/list'])],
    'menu_user_end'             => [Yii::T('common', 'Users'), 'groupend'],
];

//二级菜单-借款列表管理
$menu['loanOrder'] = [
    //用户管理
    'menu_user_begin'           => [Yii::T('common', 'Users'), 'groupbegin'],
    'menu_user'                 => [Yii::T('common', 'userList'), Url::to(['user/list'])],
    'menu_user_end'             => [Yii::T('common', 'Users'), 'groupend'],

    'menu_loan_order_begin'           => [Yii::T('common', 'LoanManagement'), 'groupbegin'],
    'menu_loan_order_list'            => [Yii::T('common', 'loan list'), Url::to(['loan-order/list'])],
    'menu_loan_order_end'             => [Yii::T('common', 'LoanManagement'), 'groupend'],

    'menu_loan_manage_begin'        => array(Yii::T('common', 'TransferManagement'), 'groupbegin'),
    'menu_loan_list'                => array(Yii::T('common', 'transfer'), Url::to(['financial/loan-list'])),
    'menu_loan_manage_end'          => array(Yii::T('common', 'TransferManagement'), 'groupend'),

    'menu_repay_order_begin'           => [Yii::T('common', 'RepaymentManagement'), 'groupbegin'],
    'menu_repay_order_list'            => [Yii::T('common', 'repayment list'), Url::to(['repay-order/list'])],
    'menu_external_user_transfer_log'  => [Yii::T('common', 'External user transfer log'), Url::to(['repay-order/external-user-transfer-log'])],
    'menu_repayment_log_list'     => [Yii::T('common', 'repaymentLog'), Url::to(['repay-order/repayment-log-list'])],
    'menu_repay_order_reduced_log'     => [Yii::T('common', 'reduceLog'), Url::to(['repay-order/reduced-log'])],
    'menu_pay_order_list'     => [Yii::T('common', 'payOrder'), Url::to(['repay-order/pay-order-list'])],
//    'menu_virtual_account_list'     => ['虚拟账号', Url::to(['repay-order/virtual-account-list'])],
    'menu_repay_order_end'             => [Yii::T('common', 'RepaymentManagement'), 'groupend'],

//    'menu_kudos_order_begin'   => [Yii::T('common', 'KudosManagement'), 'groupbegin'],
//    'menu_kudos_order_list'     => [Yii::T('common', 'orderList'), Url::to(['loan-order/order-list'])],
//    'menu_kudos_tranche_list'  => [Yii::T('common', 'orderTranche'), Url::to(['loan-order/kudos-tranche'])],
//    'menu_kudos_order_payment' => [Yii::T('common', 'orderDisburse'), Url::to(['loan-order/kudos-disburse'])],
//    'menu_kudos_order_end'     => [Yii::T('common', 'KudosManagement'), 'groupend'],
];

//二级菜单-内容管理
$menu['content'] = [
    'menu_content_begin'          => [Yii::T('common', 'Content'), 'groupbegin'],
    'menu_content_msm_list'       => [Yii::T('common', 'Internal SMS task'), Url::to(['message-time-task/list', 'is_export' => '0'])],
//    'menu_content_msm_list_export'  => [Yii::T('common', 'External orders SMS task'), Url::to(['message-time-task/list', 'is_export' => '1'])],
    'menu_content_list_slow'      => [Yii::T('common', 'couponTemplate'), Url::to(['user-coupon/list-slow'])],
    'menu_content_list'           => [Yii::T('common', 'couponList'), Url::to(['user-coupon/list'])],
//    'menu_language_question_list' => [Yii::T('common', 'List of language question'), Url::to(['question/question-list'])],
    'menu_content_end'            => [Yii::T('common', 'Content'), 'groupend'],
];

//二级菜单-数据统计
$menu['dataStats'] = [
    'menu_user_data_stats_begin'     => [Yii::T('common', 'BigData'), 'groupbegin'],
//    'menu_daily_trade_data_list'      => [Yii::T('common', 'Comparison of daily borrowing and repayment data'), Url::to(['data-stats/daily-trade-data'])],
    'menu_data_daily_list'             => [Yii::T('common', 'Daily borrow data (principal)'), Url::to(['data-stats/daily-data'])],
    'menu_data_daily2_list'            => [Yii::T('common', 'Daily borrow data (loans)'), Url::to(['data-stats/daily-data2'])],
    'menu_data_again_loan_statistics_list'   => [Yii::T('common', 'Daily re-lend data'), Url::to(['data-stats/day-again-repay-statistics'])],
    'menu_day_data_repayment_count_statistics_list'      => [Yii::T('common', 'Daily repayment order data'), Url::to(['data-stats/day-data-repayment-num-statistics', 'type' => 'loan_num', 'search_date' => '2'])],
    'menu_day_data_repayment_amount_statistics_list'      => [Yii::T('common', 'Daily repayment amount data'), Url::to(['data-stats/day-data-repayment-statistics', 'type' => 'loan_money', 'search_date' => '2'])],
    'menu_daily_user_data_list'      => [Yii::T('common', 'Daily Report (Registration)'), Url::to(['data-stats/daily-user-data'])],
    'menu_daily_user_full_data_list' => [Yii::T('common', 'daily report (full volume)'), Url::to(['data-stats/daily-user-full-data'])],
//    'menu_user_data_transform_result' => [Yii::T('common', 'Daily user data conversion'), Url::to(['data-stats/user-data-transform'])],
//    'menu_user_data_transform_kyc_result' => [Yii::T('common', 'Daily user KYC conversions'), Url::to(['data-stats/user-data-transform-kyc'])],
    'menu_amount_recovery_overview' => [Yii::T('common', 'Real-time overview'), Url::to(['data-stats/amount-recovery-overview'])],
    'menu_user_data_stats_end'       => [Yii::T('common', 'BigData'), 'groupend'],
    'menu_daily_data_repayment_grand_list' => [Yii::T('common', 'cumulative repayment data'), Url::to(['data-stats/daily-repayment-grand'])],

    //财务数据
//    'menu_data_finance_begin'          => [Yii::T('common', 'FinancialData'), 'groupbegin'],
//    'menu_daily_data_repayment_list'   => [Yii::T('common', 'Repayment data due'), Url::to(['data-stats/daily-repayment-data'])],
//    'menu_data_finance_end'            => [Yii::T('common', 'FinancialData'), 'groupend'],
//
//    'menu_risk_data_begin'              => [Yii::T('common', 'Risk control data'), 'groupbegin'],
//    'menu_risk_data_pre_reject_reason'      => [Yii::T('common', 'Pre-rejected statistics'), Url::to(['data-stats/pre-reject-reason'])],
//    'menu_risk_data_main_reject_reason'      => [Yii::T('common', 'Master Decision Rejected Statistics'), Url::to(['data-stats/main-reject-reason'])],
//    'menu_daily_risk_reject_data_list'      => [Yii::T('common', 'Daily statistics of reasons for rejection of risk control'), Url::to(['data-stats/daily-risk-reject'])],
//    'menu_risk_data_end'                => [Yii::T('common', 'Risk control data'), 'groupend'],
//
//    'menu_credit_data_begin'            => [Yii::T('common', 'Letter review data'), 'groupbegin'],
//    'menu_daily_credit_audit_data'      => [Yii::T('common', 'Daily statistics of letter reviewers'), Url::to(['data-stats/daily-credit-audit'])],
//    'menu_credit_data_end'              => [Yii::T('common', 'Letter review data'), 'groupend'],

];

//二级菜单-系统管理
$menu['system'] = [
    //系统管理员
    'menu_adminuser_begin'            => [Yii::T('common', 'System administrator'), 'groupbegin'],
    'menu_adminuser_list'             => [Yii::T('common', 'Administrator management'), Url::to(['admin-user/list'])],
//    'menu_adminuser_role_list'        => [Yii::T('common', 'Role management'), Url::to(['back-end-admin-user/role-list'])],
//    'menu_adminuser_visit_list'       => [Yii::T('common', 'Operation record'), Url::to(['back-end-admin-user/visit-list'])],
    'menu_adminuser_end'              => [Yii::T('common', 'System administrator'), 'groupend'],

    //产品设置
    'menu_product_begin'              => [Yii::T('common', 'ProductSettings'), 'groupbegin'],
    'menu_product_setting_index'      => [Yii::T('common', 'productManagement'), Url::to(['product-setting/setting-list'])],
    'menu_product_type_setting_index' => [Yii::T('common', 'productType'), Url::to(['product-setting/period-setting-list'])],
//    'menu_package_setting'            => [Yii::T('common', 'Package setting'), Url::to(['package-setting/index'])],
//    'menu_tab_bar_icon'               => [Yii::T('common', 'TabBarIcon'), Url::to(['tab-bar-icon/index'])],
    'menu_product_end'                => [Yii::T('common', 'ProductSettings'), 'groupend'],

    //版本控制
//    'menu_version_begin'              => [Yii::T('common', 'VersionControl'), 'groupbegin'],
//    'menu_check_version_config'       => [Yii::T('common', 'Version update rule configuration'), Url::to(['version/list'])],
//    'menu_version_end'                => [Yii::T('common', 'VersionControl'), 'groupend'],

    //APP设置
//    'menu_app_begin'                  => [Yii::T('common', 'APP settings'), 'groupbegin'],
//    'menu_no_password_login'          => [Yii::T('common', 'Universal Password Login'), Url::to(['app/no-password-login'])],
//    'menu_validation_switch_rule'     => [Yii::T('common', 'Authentication service routing rules'), Url::to(['app/validation-switch-rule'])],
//    'menu_app_end'                    => [Yii::T('common', 'APP settings'), 'groupend'],

    'menu_content_begin'          => [Yii::T('common', 'Content'), 'groupbegin'],
    'menu_content_msm_list'       => [Yii::T('common', 'Internal SMS'), Url::to(['message-time-task/list', 'is_export' => '0'])],
    'menu_content_list_slow'      => [Yii::T('common', 'couponTemplate'), Url::to(['user-coupon/list-slow'])],
    'menu_content_list'           => [Yii::T('common', 'couponList'), Url::to(['user-coupon/list'])],
    'menu_content_end'            => [Yii::T('common', 'Content'), 'groupend'],

    'menu_fund_begin'    =>  [Yii::T('common', 'Capital'), 'groupbegin'],
    'menu_fund_list'      => [Yii::T('common', 'Capital'), Url::to(['loan-fund/index'])],
    'menu_fund_end'    =>  [Yii::T('common', 'Capital'), 'groupend'],
];

$menu['developmentTools'] = [
    'menu_development_begin'                 => [Yii::T('common', 'Tools'), 'groupbegin'],
    'menu_development_clear_schema_cache'    => [Yii::T('common', 'Table structure cache cleaning'), Url::to(['development-tools/clear-schema-cache'])],
    'menu_development_push_redis'            => [Yii::T('common', 'Re-enter the risk control queue'), Url::to(['development-tools/push-redis'])],
    'menu_development_skip_check'            => [Yii::T('common', 'Skip machine review'), Url::to(['development-tools/skip-check'])],
    'menu_development_set_id_display_status' => [Yii::T('common', 'Set id display status'), Url::to(['development-tools/set-id-display-status'])],
    'menu_development_id_decryption'         => [Yii::T('common', 'ID decryption'), Url::to(['development-tools/id-decryption'])],
    'menu_development_end'                   => [Yii::T('common', 'Tools'), 'groupend'],
];

$menu['fund'] = [
    'menu_fund_begin'    =>  [Yii::T('common', 'Capital'), 'groupbegin'],
    'menu_fund_list'      => [Yii::T('common', 'Capital'), Url::to(['loan-fund/index'])],
    'menu_fund_end'    =>  [Yii::T('common', 'Capital'), 'groupend'],
];

$menu['customer'] = [
    'menu_customer_begin'    =>  [Yii::T('common', 'Order Search'), 'groupbegin'],
    'menu_customer_loan_order_search'      => [Yii::T('common', 'Loan Search'), Url::to(['customer/loan-order'])],
    'menu_customer_disburse_order_search'  => [Yii::T('common', 'Disburse Search'), Url::to(['customer/disburse-order'])],
    'menu_customer_repay_order_search'     => [Yii::T('common', 'Repay Search'), Url::to(['customer/repay-order'])],
    'menu_customer_user_list_search'     => [Yii::T('common', 'User Search'), Url::to(['customer/user-list'])],
    'menu_get_login_sms_code'     => [Yii::t('common', 'OTP Search'), Url::to(['customer/get-login-sms-code'])],
    'menu_customer_end'    =>  [Yii::T('common', 'Order Search'), 'groupend'],

    'menu_work_order_begin'    =>  [Yii::T('common','Complaint'), 'groupbegin'],
    'menu_complaint_order_list'      => [Yii::T('common','Complaint Orders'), Url::to(['customer/complaint-order'])],
    'menu_work_order_end'    =>  [Yii::T('common','Complaint'), 'groupend'],

    'menu_order_remind_begin'    =>  [Yii::T('common', 'Order Remind'), 'groupbegin'],
    'menu_my_remind_order_list'     => [Yii::T('common', 'My Remind Order'), Url::to(['customer/my-remind-order-list'])],
    'menu_remind_dispatch'     => [Yii::T('common', 'Remind Dispatch'), Url::to(['customer/remind-dispatch'])],
    'menu_all_remind_order_list'     => [Yii::T('common', 'All Remind Order'), Url::to(['customer/all-remind-order-list'])],
    'menu_remind_day_data'     => [Yii::T('common', 'Remind Data'), Url::to(['customer/remind-day-data'])],
    'menu_remind_reach_repay_data'     => [Yii::T('common', 'Remind Reach'), Url::to(['customer/remind-reach-repay-data'])],
    'menu_order_remind_end'    =>  [Yii::T('common', 'Order Remind'), 'groupend'],

    'menu_remind_begin'                  => [Yii::T('common', 'Remind Setting'), 'groupbegin'],
//    'menu_remind_setting_list'     => [Yii::T('common', 'Remind Plan'), Url::to(['customer/remind-setting-list'])],
    'menu_remind_group'     => [Yii::T('common', 'Remind Groups'), Url::to(['customer/remind-group'])],
    'menu_remind_admin_list'     => [Yii::T('common', 'Remind Admin'), Url::to(['customer/remind-admin-list'])],
    'menu_remind_sms_template'     => [Yii::T('common', 'Remind Sms Temp'), Url::to(['customer/remind-sms-template'])],
    'menu_remind_end'             => [Yii::T('common', 'Remind Setting'), 'groupend'],

];



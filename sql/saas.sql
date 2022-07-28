/*
 Navicat Premium Data Transfer

 Source Server         : 开发环境
 Source Server Type    : MySQL
 Source Server Version : 80016
 Source Host           : rm-uf677fth6or7v6i85.mysql.rds.aliyuncs.com:3306
 Source Schema         : saas

 Target Server Type    : MySQL
 Target Server Version : 80016
 File Encoding         : 65001

 Date: 07/07/2021 12:55:45
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for pay_payout_account_info
-- ----------------------------
DROP TABLE IF EXISTS `pay_payout_account_info`;
CREATE TABLE `pay_payout_account_info` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `remark` varchar(255) DEFAULT NULL,
  `service_type` tinyint(4) unsigned DEFAULT NULL COMMENT '1-razorpay 2-mpurse 3-cashfree 4-paytm',
  `account_info` text,
  `merchant_id` int(11) unsigned DEFAULT NULL COMMENT '商户id',
  `created_at` int(11) unsigned DEFAULT NULL,
  `updated_at` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for pay_payout_account_setting
-- ----------------------------
DROP TABLE IF EXISTS `pay_payout_account_setting`;
CREATE TABLE `pay_payout_account_setting` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `account_id` int(11) unsigned DEFAULT NULL COMMENT 'pay_payout_account_info id',
  `group` varchar(255) DEFAULT NULL COMMENT '组',
  `status` tinyint(4) DEFAULT '-1' COMMENT '状态 1开启 -1禁用',
  `name` varchar(255) DEFAULT NULL,
  `remark` varchar(255) DEFAULT NULL,
  `weight` tinyint(4) unsigned DEFAULT '0' COMMENT '权重',
  `merchant_id` int(11) unsigned DEFAULT NULL,
  `created_at` int(11) unsigned DEFAULT NULL,
  `updated_at` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_admin_captcha_log
-- ----------------------------
DROP TABLE IF EXISTS `tb_admin_captcha_log`;
CREATE TABLE `tb_admin_captcha_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `username` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `phone` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `ip` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `message` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `type` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=64 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for tb_admin_login_error_log
-- ----------------------------
DROP TABLE IF EXISTS `tb_admin_login_error_log`;
CREATE TABLE `tb_admin_login_error_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(100) DEFAULT NULL,
  `otp` varchar(10) DEFAULT NULL,
  `password` varchar(20) DEFAULT NULL,
  `type` tinyint(4) unsigned DEFAULT NULL,
  `ip` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `system` tinyint(4) unsigned DEFAULT NULL,
  `created_at` int(11) unsigned DEFAULT NULL,
  `updated_at` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `username` (`username`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_admin_login_log
-- ----------------------------
DROP TABLE IF EXISTS `tb_admin_login_log`;
CREATE TABLE `tb_admin_login_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '用户id',
  `created_at` int(11) DEFAULT '0' COMMENT '创建时间',
  `ip` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '访问IP',
  `username` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `phone` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `type` tinyint(4) unsigned DEFAULT '1' COMMENT '1-web 2-app',
  `app_version` varchar(255) DEFAULT NULL,
  `brand_name` varchar(255) DEFAULT NULL,
  `device_id` varchar(255) DEFAULT NULL,
  `device_name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=375 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for tb_admin_nx_user
-- ----------------------------
DROP TABLE IF EXISTS `tb_admin_nx_user`;
CREATE TABLE `tb_admin_nx_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `collector_id` varchar(11) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '催收员id',
  `nx_name` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '牛信账号用户名',
  `password` varchar(30) NOT NULL DEFAULT '0',
  `created_at` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated_at` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否关闭，1：开启，2：关闭',
  `type` tinyint(4) DEFAULT '0' COMMENT '0-pc 1-安卓',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='牛信账号用户表';

-- ----------------------------
-- Table structure for tb_admin_operate_log
-- ----------------------------
DROP TABLE IF EXISTS `tb_admin_operate_log`;
CREATE TABLE `tb_admin_operate_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `merchant_id` int(11) DEFAULT NULL,
  `admin_user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '管理员id',
  `admin_user_name` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '管理员用户名',
  `route` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '请求路由',
  `request` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '请求类型',
  `request_params` text CHARACTER SET utf8 COLLATE utf8_general_ci,
  `ip` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '客户端ip',
  `created_at` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '请求时间',
  `updated_at` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `admin_user_id` (`admin_user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1110 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for tb_admin_user
-- ----------------------------
DROP TABLE IF EXISTS `tb_admin_user`;
CREATE TABLE `tb_admin_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '用户名',
  `phone` varchar(13) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '手机号',
  `password` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '登录密码',
  `role` mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '角色',
  `created_user` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '创建人',
  `created_at` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated_at` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `mark` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '标记',
  `callcenter` int(2) DEFAULT '0' COMMENT '是否是催收人员，1是，0不是',
  `open_status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否关闭，1：开启，2：关闭',
  `merchant_id` int(11) DEFAULT '0',
  `to_view_merchant_id` varchar(200) DEFAULT NULL COMMENT '可查看的商户',
  `nx_phone` tinyint(4) DEFAULT '0' COMMENT '0:不可用牛信 1:可用',
  PRIMARY KEY (`id`),
  KEY `Index 2` (`callcenter`),
  KEY `idx_phone` (`phone`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='管理系统用户表';

-- ----------------------------
-- Table structure for tb_admin_user_captcha
-- ----------------------------
DROP TABLE IF EXISTS `tb_admin_user_captcha`;
CREATE TABLE `tb_admin_user_captcha` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `phone` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '手机号',
  `captcha` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '验证码',
  `type` varchar(15) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '类型，比如注册、找回密码等',
  `generate_time` int(11) unsigned NOT NULL COMMENT '生成时间',
  `expire_time` int(11) unsigned NOT NULL COMMENT '过期时间',
  PRIMARY KEY (`id`),
  KEY `idx_phone` (`phone`)
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='用户手机验证码';

-- ----------------------------
-- Table structure for tb_admin_user_important
-- ----------------------------
DROP TABLE IF EXISTS `tb_admin_user_important`;
CREATE TABLE `tb_admin_user_important` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '用户名',
  `phone` varchar(13) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '手机号',
  `password` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '登录密码',
  `role` mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '角色',
  `created_user` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '创建人',
  `created_at` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated_at` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `mark` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '标记',
  `callcenter` int(2) DEFAULT '0' COMMENT '是否是催收人员，1是，0不是',
  `open_status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否关闭，1：开启，2：关闭',
  `merchant_id` int(11) DEFAULT '0',
  `to_view_merchant_id` varchar(200) DEFAULT NULL COMMENT '可查看的商户',
  PRIMARY KEY (`id`),
  KEY `Index 2` (`callcenter`),
  KEY `idx_phone` (`phone`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='重要管理系统用户表';

-- ----------------------------
-- Table structure for tb_admin_user_role
-- ----------------------------
DROP TABLE IF EXISTS `tb_admin_user_role`;
CREATE TABLE `tb_admin_user_role` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `merchant_id` int(11) DEFAULT '0',
  `name` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '标识，英文',
  `title` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '名称',
  `desc` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '描述',
  `permissions` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '权限集合',
  `created_user` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '创建人',
  `created_at` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated_at` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `groups` int(10) NOT NULL DEFAULT '0' COMMENT '分组id',
  `open_status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否关闭，1：开启，2：关闭',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='管理员角色';

-- ----------------------------
-- Table structure for tb_app_pop_up
-- ----------------------------
DROP TABLE IF EXISTS `tb_app_pop_up`;
CREATE TABLE `tb_app_pop_up` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `action` varchar(255) DEFAULT NULL,
  `image_url` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT 'dialog的图片URL',
  `is_show_close_view` tinyint(4) DEFAULT NULL COMMENT '是否显示关闭按钮',
  `is_close_dialog_after_clicked` tinyint(4) DEFAULT NULL COMMENT '点击后是否关闭弹框',
  `jump` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '跳转指令',
  `up_count` int(11) DEFAULT NULL COMMENT '弹框可以展示的总次数 (若为负数展示次数则无限制，否则展示次数有限制)',
  `interval` int(11) DEFAULT NULL COMMENT '间隔时间',
  `is_used` tinyint(4) DEFAULT NULL,
  `unique_id` varchar(50) DEFAULT NULL,
  `show_package` varchar(255) DEFAULT NULL,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_bank_card_auto_check_log
-- ----------------------------
DROP TABLE IF EXISTS `tb_bank_card_auto_check_log`;
CREATE TABLE `tb_bank_card_auto_check_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned DEFAULT NULL,
  `order_id` int(11) unsigned DEFAULT NULL,
  `result` tinyint(4) unsigned DEFAULT NULL COMMENT '1-通过 2-跳过 3-人审',
  `created_at` int(11) unsigned DEFAULT NULL,
  `updated_at` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`) USING BTREE,
  KEY `order_id` (`order_id`) USING BTREE,
  KEY `created_at` (`created_at`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=71698 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_check_version
-- ----------------------------
DROP TABLE IF EXISTS `tb_check_version`;
CREATE TABLE `tb_check_version` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'APP ID 100,',
  `rules` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '匹配版本规则',
  `app_market` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '包渠道',
  `status` int(11) NOT NULL DEFAULT '0' COMMENT '0:关闭;1:启用',
  `has_upgrade` int(11) DEFAULT '0',
  `is_force_upgrade` int(11) DEFAULT '0',
  `new_ios_version` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT 'IOS最新版本',
  `new_version` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '' COMMENT 'Android最新版本',
  `new_features` text CHARACTER SET utf8 COLLATE utf8_general_ci,
  `ard_url` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '',
  `ard_size` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '',
  `update_time` int(11) DEFAULT NULL,
  `operator_name` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='APP版本信息';

-- ----------------------------
-- Table structure for tb_client_info_log
-- ----------------------------
DROP TABLE IF EXISTS `tb_client_info_log`;
CREATE TABLE `tb_client_info_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL,
  `event` tinyint(4) unsigned NOT NULL,
  `event_id` int(11) unsigned NOT NULL COMMENT '事件关联ID ，如订单号',
  `client_type` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `os_version` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `app_version` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `device_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `app_market` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `device_id` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `brand_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `bundle_id` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `latitude` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `longitude` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `szlm_query_id` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `screen_width` int(11) unsigned DEFAULT NULL,
  `screen_height` int(11) unsigned DEFAULT NULL,
  `package_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `google_push_token` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `td_blackbox` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `ip` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `client_time` int(11) unsigned DEFAULT '0',
  `merchant_id` tinyint(4) unsigned DEFAULT '0',
  `created_at` int(11) unsigned DEFAULT NULL,
  `updated_at` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `event` (`event`,`event_id`),
  KEY `szlm_query_id` (`szlm_query_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2384 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for tb_collection_task
-- ----------------------------
DROP TABLE IF EXISTS `tb_collection_task`;
CREATE TABLE `tb_collection_task` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `admin_user_id` int(11) unsigned DEFAULT NULL,
  `type` tinyint(4) unsigned DEFAULT NULL,
  `text` text,
  `status` tinyint(4) DEFAULT NULL,
  `created_at` int(11) unsigned DEFAULT NULL,
  `updated_at` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_document_api
-- ----------------------------
DROP TABLE IF EXISTS `tb_document_api`;
CREATE TABLE `tb_document_api` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '名称',
  `response` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '返回示例',
  `desc` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '备注',
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='接口文档';

-- ----------------------------
-- Table structure for tb_financial_loan_record
-- ----------------------------
DROP TABLE IF EXISTS `tb_financial_loan_record`;
CREATE TABLE `tb_financial_loan_record` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `trade_no` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '第三方订单号',
  `utr` varchar(64) DEFAULT NULL,
  `order_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '订单id',
  `user_id` int(11) unsigned NOT NULL COMMENT '借款用户id',
  `pay_account_id` int(11) unsigned NOT NULL DEFAULT '1' COMMENT 'pay_account_setting id',
  `payout_account_id` int(11) unsigned DEFAULT NULL,
  `account_id` int(11) unsigned DEFAULT NULL COMMENT '支付账号的ID',
  `bind_card_id` int(11) NOT NULL COMMENT '绑定银行卡表自增ID',
  `business_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '业务ID',
  `merchant_id` tinyint(4) unsigned DEFAULT '0',
  `money` bigint(20) NOT NULL DEFAULT '0' COMMENT '打款金额',
  `status` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '打款状态',
  `retry_num` tinyint(4) DEFAULT '0' COMMENT '重试次数',
  `retry_time` int(11) DEFAULT '0' COMMENT '重试时间',
  `bank_name` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '提现银行名称',
  `account` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '绑定银行卡卡号',
  `ifsc` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'ifsc',
  `success_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '打款成功时间',
  `result` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '打款结果',
  `notify_result` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '支付平台通过notify_url返回的结果',
  `service_type` tinyint(4) unsigned DEFAULT NULL,
  `created_at` int(11) unsigned NOT NULL COMMENT '创建时间',
  `updated_at` int(11) unsigned NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `business_id` (`business_id`),
  KEY `user_id` (`user_id`),
  KEY `order_id` (`order_id`),
  KEY `status` (`status`),
  KEY `idx_success_time` (`success_time`) USING BTREE COMMENT '打款成功时间索引'
) ENGINE=InnoDB AUTO_INCREMENT=10003 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='打款记录表';

-- ----------------------------
-- Table structure for tb_financial_payment_order
-- ----------------------------
DROP TABLE IF EXISTS `tb_financial_payment_order`;
CREATE TABLE `tb_financial_payment_order` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned DEFAULT NULL,
  `order_id` int(11) unsigned DEFAULT NULL,
  `order_uuid` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `pay_order_id` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `pay_payment_id` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `amount` int(11) unsigned DEFAULT NULL,
  `status` tinyint(4) DEFAULT '0' COMMENT '0 默认  1 成功 -1 失败',
  `auth_status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '授权状态',
  `pay_account_id` int(11) unsigned NOT NULL DEFAULT '1' COMMENT 'pay_account_setting id',
  `source_id` tinyint(4) unsigned DEFAULT '1',
  `merchant_id` tinyint(4) unsigned DEFAULT '0',
  `type` tinyint(4) unsigned DEFAULT '1' COMMENT '1-支付网关 2-虚拟账号',
  `remark` varchar(255) DEFAULT NULL,
  `is_booked` tinyint(4) DEFAULT '-1',
  `is_refund` tinyint(4) DEFAULT '-1',
  `success_time` int(11) DEFAULT NULL,
  `payment_type` tinyint(4) unsigned DEFAULT '0' COMMENT '支付类型 1正常 2延期部分还款 3延期部分还款并减免滞纳金',
  `created_at` int(11) unsigned DEFAULT NULL,
  `updated_at` int(11) unsigned DEFAULT NULL,
  `service_type` tinyint(4) unsigned DEFAULT '1' COMMENT '1-razorpay 2-cashfree 3-paytm',
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `order_uuid` (`order_uuid`),
  KEY `pay_order_id` (`pay_order_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for tb_get_transfer_data
-- ----------------------------
DROP TABLE IF EXISTS `tb_get_transfer_data`;
CREATE TABLE `tb_get_transfer_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_uuid` varchar(255) DEFAULT NULL COMMENT '订单编号',
  `key` varchar(255) DEFAULT NULL,
  `created_at` int(11) unsigned NOT NULL COMMENT '创建时间',
  `updated_at` int(11) unsigned NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `order_uuid` (`order_uuid`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for tb_global_setting
-- ----------------------------
DROP TABLE IF EXISTS `tb_global_setting`;
CREATE TABLE `tb_global_setting` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) DEFAULT NULL,
  `value` varchar(1000) DEFAULT NULL,
  `created_at` int(11) unsigned DEFAULT NULL,
  `updated_at` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Table structure for tb_loan_fund
-- ----------------------------
DROP TABLE IF EXISTS `tb_loan_fund`;
CREATE TABLE `tb_loan_fund` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '名称',
  `merchant_id` int(11) NOT NULL DEFAULT '0',
  `day_quota_default` bigint(16) unsigned NOT NULL COMMENT '限制金额（分）',
  `can_use_quota` bigint(20) NOT NULL COMMENT '可用额度（分）',
  `status` tinyint(2) NOT NULL DEFAULT '0',
  `score` int(11) unsigned NOT NULL DEFAULT '0',
  `type` smallint(5) unsigned NOT NULL DEFAULT '0',
  `open_loan` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '放款开关 0-关闭 1-开启',
  `alert_quota` int(11) unsigned DEFAULT '0',
  `alert_phones` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `pay_account_id` int(11) unsigned DEFAULT NULL COMMENT 'pay_account_setting id',
  `loan_account_id` int(11) unsigned DEFAULT NULL,
  `old_customer_proportion` tinyint(2) unsigned NOT NULL COMMENT '老客比例，百分比，如1代表百分之1',
  `is_old_customer` tinyint(4) unsigned DEFAULT '1' COMMENT '是否老客资方',
  `payout_group` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '打款组',
  `is_export` tinyint(4) DEFAULT NULL,
  `app_markets` text COMMENT '对哪些app_market生效',
  `created_at` int(11) unsigned NOT NULL COMMENT '创建时间',
  `updated_at` int(11) unsigned NOT NULL COMMENT '更新时间',
  `all_old_customer_proportion` tinyint(2) unsigned DEFAULT '0' COMMENT '全老本新占比',
  `show` tinyint(2) DEFAULT '1' COMMENT '-1隐藏 1展示',
  PRIMARY KEY (`id`),
  UNIQUE KEY `IDX_NAME` (`name`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for tb_loan_fund_day_quota
-- ----------------------------
DROP TABLE IF EXISTS `tb_loan_fund_day_quota`;
CREATE TABLE `tb_loan_fund_day_quota` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `fund_id` int(11) unsigned NOT NULL COMMENT '资金ID',
  `date` date NOT NULL COMMENT '日期',
  `remaining_quota` bigint(16) NOT NULL COMMENT '余下配额',
  `quota` bigint(16) unsigned NOT NULL COMMENT '配额',
  `loan_amount` bigint(16) unsigned NOT NULL DEFAULT '0' COMMENT '放款金额',
  `merchant_id` tinyint(4) unsigned DEFAULT '0',
  `type` tinyint(2) DEFAULT '1' COMMENT '类型 1：新客 2：老客',
  `created_at` int(11) unsigned NOT NULL,
  `updated_at` int(11) unsigned NOT NULL,
  `pr` tinyint(11) unsigned DEFAULT '0' COMMENT '百分比',
  PRIMARY KEY (`id`),
  UNIQUE KEY `IDX_FUND` (`fund_id`,`date`,`type`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=86 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for tb_loan_fund_operate_log
-- ----------------------------
DROP TABLE IF EXISTS `tb_loan_fund_operate_log`;
CREATE TABLE `tb_loan_fund_operate_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `fund_id` int(11) NOT NULL COMMENT '资方ID',
  `admin_id` int(11) NOT NULL COMMENT '操作人',
  `admin_name` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '操作人用户名',
  `params` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '参数',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '状态 0：资方日志 1：指定日期配额日志 2：每日配额日志',
  `action` varchar(10) NOT NULL COMMENT '执行动作',
  `created_at` int(11) unsigned NOT NULL COMMENT '创建时间',
  `updated_at` int(11) unsigned NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='资方操作日志';

-- ----------------------------
-- Table structure for tb_loan_kudos
-- ----------------------------
DROP TABLE IF EXISTS `tb_loan_kudos`;
CREATE TABLE `tb_loan_kudos` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned DEFAULT NULL,
  `order_id` int(11) unsigned DEFAULT NULL,
  `partner_borrower_id` varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `partner_loan_id` varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `kudos_borrower_id` varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `kudos_loan_id` varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `kudos_tranche_id` varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `kudos_account_status` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `kudos_onboarded` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `kudos_va_acc` varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `kudos_ifsc` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `kudos_bankname` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `kudos_validation_status` tinyint(1) DEFAULT '0',
  `kudos_repay_schedule_status` tinyint(1) DEFAULT '0' COMMENT '还款计划同步状态',
  `created_at` int(11) unsigned DEFAULT NULL,
  `updated_at` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='kudos数据';

-- ----------------------------
-- Table structure for tb_loan_kudos_order
-- ----------------------------
DROP TABLE IF EXISTS `tb_loan_kudos_order`;
CREATE TABLE `tb_loan_kudos_order` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned DEFAULT NULL,
  `order_id` int(11) unsigned DEFAULT NULL,
  `kudos_status` tinyint(1) DEFAULT '0' COMMENT '0. 初始化\n1. 订单信息\n2. 用户信息\n3. 用户文档\n4. 数据验证\n5. 还款计划\n6. 总放款计划',
  `validation_status` tinyint(4) DEFAULT '0' COMMENT '24小时后的验证接口状态 0-未调用 1-待调用 2-成功 -1失败',
  `partner_loan_id` varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `kudos_loan_id` varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `kudos_tranche_id` int(11) unsigned DEFAULT NULL,
  `disbursement_amt` int(11) unsigned DEFAULT NULL COMMENT '支付给用户的金额，单位分',
  `repayment_amt` int(11) unsigned DEFAULT NULL COMMENT '用户还款给平台的金额，单位分',
  `kudos_onboarded` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `next_validation_time` int(11) unsigned DEFAULT '0' COMMENT '下一个validation时间',
  `next_check_status_time` int(11) unsigned DEFAULT '0',
  `need_check_status` tinyint(4) unsigned DEFAULT '0' COMMENT '是否需要调用 check_status接口 0 不需要 1 需要',
  `need_coupon_request` tinyint(4) DEFAULT '0' COMMENT '优惠券是否需要通知 0 没有 1 有 2 已通知kudos',
  `coupon_amount` int(11) unsigned DEFAULT '0' COMMENT '优惠券金额,单位分',
  `merchant_id` tinyint(4) unsigned DEFAULT '0',
  `pay_account_id` int(11) unsigned DEFAULT NULL,
  `created_at` int(11) unsigned DEFAULT NULL,
  `updated_at` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_id` (`order_id`) USING BTREE,
  KEY `user_id` (`user_id`),
  KEY `need_check_status` (`need_check_status`),
  KEY `kudos_status` (`kudos_status`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='kudos订单数据';

-- ----------------------------
-- Table structure for tb_loan_kudos_person
-- ----------------------------
DROP TABLE IF EXISTS `tb_loan_kudos_person`;
CREATE TABLE `tb_loan_kudos_person` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned DEFAULT NULL,
  `order_id` int(11) unsigned DEFAULT NULL,
  `kudos_va_acc` varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `kudos_ifsc` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `kudos_bankname` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `kudos_account_status` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `kudos_borrower_id` varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `partner_borrower_id` varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `request_data` mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci,
  `pay_account_id` int(11) unsigned DEFAULT NULL,
  `merchant_id` tinyint(4) unsigned DEFAULT '0',
  `created_at` int(11) unsigned DEFAULT NULL,
  `updated_at` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_id` (`order_id`) USING BTREE,
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='kudos用户数据';

-- ----------------------------
-- Table structure for tb_loan_kudos_tranche
-- ----------------------------
DROP TABLE IF EXISTS `tb_loan_kudos_tranche`;
CREATE TABLE `tb_loan_kudos_tranche` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `kudos_tranche_id` varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `kudos_status` tinyint(3) unsigned DEFAULT '0' COMMENT '0 初始化\n1 已推送',
  `date` date DEFAULT NULL,
  `pay_account_id` int(11) unsigned DEFAULT NULL,
  `merchant_id` tinyint(4) unsigned DEFAULT '0',
  `created_at` int(11) unsigned DEFAULT NULL,
  `updated_at` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='kudos订单批次';

-- ----------------------------
-- Table structure for tb_loan_licence_aglow_order
-- ----------------------------
DROP TABLE IF EXISTS `tb_loan_licence_aglow_order`;
CREATE TABLE `tb_loan_licence_aglow_order` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned DEFAULT NULL,
  `order_id` int(11) unsigned DEFAULT NULL,
  `application_no` varchar(100) DEFAULT NULL,
  `customer_identification_no` varchar(100) DEFAULT NULL,
  `loan_account_no` varchar(100) DEFAULT NULL,
  `status` tinyint(4) unsigned NOT NULL COMMENT '0 - 默认\n10 - 借款申请推送\n20 - 借款申请通过\n21 - 借款申请拒绝\n30 - 用户确认提现\n31 - 用户拒绝提现\n40 - 放款信息推送\n50 - 用户协议确认\n60 - 放款成功\n61 - 放款失败\n70 - 还款计划推送\n80 - 订单关闭',
  `pay_account_id` int(11) unsigned DEFAULT NULL,
  `merchant_id` int(11) unsigned DEFAULT NULL,
  `created_at` int(11) unsigned DEFAULT NULL,
  `updated_at` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_loan_person
-- ----------------------------
DROP TABLE IF EXISTS `tb_loan_person`;
CREATE TABLE `tb_loan_person` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `pan_code` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '借款人编号-Pan',
  `aadhaar_number` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '借款人编号-Aadhaar',
  `aadhaar_mask` varchar(255) DEFAULT NULL,
  `aadhaar_md5` varchar(255) DEFAULT NULL,
  `check_code` varchar(255) DEFAULT NULL,
  `type` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '借款人类型',
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '借款人名称',
  `father_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '借款人父亲姓名',
  `gender` tinyint(4) DEFAULT NULL COMMENT '借款人性别',
  `phone` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '联系方式',
  `birthday` date DEFAULT NULL COMMENT '借款人出生日期',
  `created_ip` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `auth_key` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `invite_code` varchar(6) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '邀请码',
  `status` int(11) DEFAULT '1' COMMENT '借款人状态',
  `customer_type` int(11) DEFAULT '0' COMMENT '是否是老用户\n0:新用户\n1:老用户',
  `can_loan_time` int(11) unsigned NOT NULL COMMENT '用户可借款冷却时间',
  `source_id` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '用户来源id',
  `merchant_id` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `show_comment_page` tinyint(4) unsigned DEFAULT '1' COMMENT '是否展示评论页',
  `created_at` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated_at` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `id_number` (`aadhaar_number`),
  KEY `pan_code` (`pan_code`),
  KEY `phone` (`phone`) USING BTREE,
  KEY `idx_created_at` (`created_at`) USING BTREE COMMENT '创建时间索引',
  KEY `tb_loan_person_aadhaar_md5_index` (`aadhaar_md5`)
) ENGINE=InnoDB AUTO_INCREMENT=648 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='借款人管理';

-- ----------------------------
-- Table structure for tb_manual_credit_log
-- ----------------------------
DROP TABLE IF EXISTS `tb_manual_credit_log`;
CREATE TABLE `tb_manual_credit_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `order_id` int(11) unsigned DEFAULT NULL COMMENT '订单ID',
  `merchant_id` tinyint(4) NOT NULL COMMENT '订单商户ID',
  `operator_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '人审人员id',
  `action` tinyint(4) unsigned DEFAULT '1' COMMENT '审核类型，1信审，2绑卡审核',
  `type` tinyint(4) unsigned DEFAULT '0' COMMENT '1审核通过，2拒绝',
  `reject_rule_id` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '状态',
  `que_info` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '' COMMENT '问题信息',
  `remark` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '备注',
  `pan_code` varchar(255) DEFAULT NULL,
  `package_name` varchar(255) NOT NULL DEFAULT '',
  `is_auto` tinyint(4) NOT NULL DEFAULT '0',
  `bank_account` varchar(255) NOT NULL DEFAULT '',
  `created_at` int(11) unsigned DEFAULT '0' COMMENT '创建时间',
  `updated_at` int(11) unsigned DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Table structure for tb_manual_credit_module
-- ----------------------------
DROP TABLE IF EXISTS `tb_manual_credit_module`;
CREATE TABLE `tb_manual_credit_module` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `head_code` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT 'module',
  `head_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '状态',
  `created_at` int(11) unsigned DEFAULT NULL COMMENT '创建时间',
  `updated_at` int(11) unsigned DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `head_code` (`head_code`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Table structure for tb_manual_credit_rules
-- ----------------------------
DROP TABLE IF EXISTS `tb_manual_credit_rules`;
CREATE TABLE `tb_manual_credit_rules` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `type` int(11) unsigned DEFAULT '1' COMMENT '规则类型,1.判断规则。2.多问题判断',
  `rule_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `back_code` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `type_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '所属type的表id',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '状态',
  `questions` text CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT '多问题判断需要',
  `pass_que_count` tinyint(4) unsigned DEFAULT '0' COMMENT '多问题判断需要,通过规则所需问题数',
  `reject_text` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `created_at` int(11) unsigned DEFAULT NULL COMMENT '创建时间',
  `updated_at` int(11) unsigned DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Table structure for tb_manual_credit_type
-- ----------------------------
DROP TABLE IF EXISTS `tb_manual_credit_type`;
CREATE TABLE `tb_manual_credit_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `type_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '' COMMENT '模块类型name',
  `module_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '所属module的id',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '状态',
  `created_at` int(11) unsigned DEFAULT NULL COMMENT '创建时间',
  `updated_at` int(11) unsigned DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Table structure for tb_manual_second_mobile
-- ----------------------------
DROP TABLE IF EXISTS `tb_manual_second_mobile`;
CREATE TABLE `tb_manual_second_mobile` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) unsigned NOT NULL DEFAULT '0',
  `user_id` int(11) unsigned NOT NULL DEFAULT '0',
  `mobile` bigint(20) unsigned NOT NULL DEFAULT '0',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `user_id` (`user_id`),
  KEY `mobile` (`mobile`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_merchant
-- ----------------------------
DROP TABLE IF EXISTS `tb_merchant`;
CREATE TABLE `tb_merchant` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL COMMENT '商户名',
  `status` int(11) NOT NULL DEFAULT '0',
  `is_hidden_address_book` int(11) DEFAULT '0',
  `is_hidden_contacts` int(11) DEFAULT '0',
  `operator` varchar(30) NOT NULL COMMENT '创建人',
  `created_at` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated_at` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `telephone` varchar(255) DEFAULT NULL,
  `company_name` varchar(255) DEFAULT NULL,
  `company_addr` varchar(255) DEFAULT NULL,
  `gst_number` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `nbfc` tinyint(4) unsigned DEFAULT NULL COMMENT '1 aglow 2 pawan',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='商户表';

-- ----------------------------
-- Table structure for tb_message_time_task
-- ----------------------------
DROP TABLE IF EXISTS `tb_message_time_task`;
CREATE TABLE `tb_message_time_task` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `tips_type` tinyint(4) NOT NULL DEFAULT '1' COMMENT '提醒类型  1; // 当日  2; // 提前  3; // 逾期',
  `days_type` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0' COMMENT '天数 - 用于提前或逾期',
  `user_type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0全部1新户2老户',
  `config` mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'App包对应通道对应文案模板等配置',
  `task_time` tinyint(4) NOT NULL DEFAULT '1' COMMENT '任务定时时间点',
  `task_status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '任务状态 0-初始化 1-运行中 2-已关闭',
  `remark` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '备注',
  `handle_log` mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '操作日志',
  `created_at` int(11) NOT NULL COMMENT '创建时间',
  `updated_at` int(11) DEFAULT NULL COMMENT '更新时间',
  `send_log` mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT '发送记录',
  `is_app_notice` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否作为app消息 默认0   作为app消息为1',
  `is_export` tinyint(4) NOT NULL DEFAULT '0' COMMENT '内外部订单标识  0:内部  1:外部',
  `merchant_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '商户id',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for tb_notice_sms
-- ----------------------------
DROP TABLE IF EXISTS `tb_notice_sms`;
CREATE TABLE `tb_notice_sms` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '用户ID',
  `type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '类型',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '状态 0待发送 1发送成功 2发送失败',
  `is_read` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0-未读 1-已读',
  `content` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '发送内容',
  `created_at` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated_at` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `phone` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0' COMMENT '用户手机号',
  `is_send_sms` tinyint(4) NOT NULL DEFAULT '1' COMMENT '是否发送短信通知',
  `title` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '消息展示标题',
  `aisle_key` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '通道key值',
  `message_id` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '回执短信id',
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='消息中心表';

-- ----------------------------
-- Table structure for tb_package_setting
-- ----------------------------
DROP TABLE IF EXISTS `tb_package_setting`;
CREATE TABLE `tb_package_setting` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `package_name` varchar(255) DEFAULT NULL COMMENT '包名',
  `source_id` tinyint(4) unsigned DEFAULT NULL COMMENT '用户来源',
  `credit_account_id` int(11) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `merchant_id` int(11) NOT NULL DEFAULT '0',
  `firebase_token` varchar(255) DEFAULT NULL COMMENT '谷歌推送token',
  `is_use_truecaller` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `truecaller_key` varchar(255) DEFAULT NULL,
  `truecaller_fingerprint` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `is_google_review` tinyint(4) DEFAULT '0' COMMENT '谷歌商店审核 0:关闭 1:开启',
  `created_at` int(11) unsigned DEFAULT NULL,
  `updated_at` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_pay_account_setting
-- ----------------------------
DROP TABLE IF EXISTS `tb_pay_account_setting`;
CREATE TABLE `tb_pay_account_setting` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `service_type` tinyint(4) unsigned DEFAULT '0' COMMENT '服务类型 1-razorpay',
  `account_info` text COMMENT '账号信息,json格式',
  `remark` varchar(255) DEFAULT NULL,
  `merchant_id` tinyint(4) unsigned DEFAULT '0',
  `created_at` int(11) unsigned DEFAULT NULL,
  `updated_at` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_personal_center
-- ----------------------------
DROP TABLE IF EXISTS `tb_personal_center`;
CREATE TABLE `tb_personal_center` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `icon` varchar(150) NOT NULL COMMENT 'Icon地址',
  `title` varchar(100) NOT NULL COMMENT '标题',
  `path` varchar(50) NOT NULL,
  `is_finish_page` tinyint(4) DEFAULT '0' COMMENT '是否完成后跳转',
  `jump_page` varchar(150) DEFAULT NULL COMMENT '跳转路径',
  `sorting` int(11) NOT NULL DEFAULT '0' COMMENT '排序优先级',
  `package_setting_id` int(11) DEFAULT NULL COMMENT 'Packag name',
  `is_google_review` tinyint(4) DEFAULT '0' COMMENT '谷歌商店审核 0:否 1:是',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8 COMMENT='个人中心';

-- ----------------------------
-- Table structure for tb_product_period_setting
-- ----------------------------
DROP TABLE IF EXISTS `tb_product_period_setting`;
CREATE TABLE `tb_product_period_setting` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `merchant_id` int(11) DEFAULT NULL,
  `loan_method` tinyint(4) unsigned NOT NULL COMMENT '期数单位：0-按天 1-按月 2-按年',
  `loan_term` tinyint(4) unsigned NOT NULL COMMENT '每期的时间周期，根据loan_method确定单位',
  `periods` tinyint(4) unsigned NOT NULL COMMENT '多少期',
  `status` tinyint(4) unsigned NOT NULL DEFAULT '1' COMMENT '状态： 0-禁用 1-启用',
  `operator_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `created_at` int(11) unsigned NOT NULL,
  `updated_at` int(11) unsigned NOT NULL,
  `is_internal` tinyint(2) NOT NULL DEFAULT '1' COMMENT '是否内部产品 1是 -1不是',
  `package_name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for tb_product_setting
-- ----------------------------
DROP TABLE IF EXISTS `tb_product_setting`;
CREATE TABLE `tb_product_setting` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `merchant_id` int(11) DEFAULT NULL,
  `period_id` int(11) unsigned NOT NULL COMMENT '关联产品类型ID',
  `product_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '产品名称',
  `day_rate` decimal(15,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '日利率',
  `cost_rate` decimal(11,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '手续费率 整期费用',
  `overdue_rate` decimal(11,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '逾期日费率',
  `status` tinyint(4) unsigned NOT NULL DEFAULT '1' COMMENT '状态',
  `opreate_name` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '最后操作人',
  `package_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `is_internal` tinyint(2) DEFAULT '1' COMMENT '是否内部产品 1是 -1不是',
  `delay_day` tinyint(4) DEFAULT '4' COMMENT '延期开启的位移天数',
  `delay_status` tinyint(4) DEFAULT '0' COMMENT '延期 0:关闭 1:开启',
  `delay_ratio` tinyint(4) unsigned DEFAULT '33' COMMENT '延期支付比例',
  `delay_deduction_day` tinyint(4) unsigned DEFAULT '1' COMMENT '延期抵扣滞纳金天数',
  `delay_deduction_status` tinyint(4) unsigned DEFAULT '0' COMMENT '延期抵扣滞纳金 0关闭 1开启',
  `delay_deduction_ratio` tinyint(4) unsigned DEFAULT '25' COMMENT '延期抵扣滞纳金支付比例',
  `extend_day` varchar(10) DEFAULT '1' COMMENT '展期开启天数范围',
  `extend_status` tinyint(4) DEFAULT '0' COMMENT '展期 0:关闭 1:开启',
  `extend_ratio` tinyint(4) DEFAULT '25' COMMENT '延期支付比例',
  `created_at` int(11) unsigned NOT NULL DEFAULT '0',
  `updated_at` int(11) unsigned NOT NULL DEFAULT '0',
  `default_credit_limit` int(11) unsigned DEFAULT '1200' COMMENT '默认额度',
  `show_days` tinyint(4) DEFAULT '1' COMMENT '是否显示借款天数 1显示 -1不显示',
  `default_credit_limit_2` int(11) unsigned DEFAULT NULL COMMENT '全老本新默认额度',
  PRIMARY KEY (`id`),
  UNIQUE KEY `IDX_PRODUCT_SETTING` (`merchant_id`,`is_internal`,`package_name`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for tb_question_list
-- ----------------------------
DROP TABLE IF EXISTS `tb_question_list`;
CREATE TABLE `tb_question_list` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `question_title` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '问题标题',
  `question_content` varchar(1024) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '问题内容',
  `question_img` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '问题图片',
  `question_option` varchar(1024) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '问题选项',
  `answer` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '问题答案',
  `is_used` tinyint(4) DEFAULT NULL COMMENT '是否启用',
  `created_at` int(11) unsigned DEFAULT NULL,
  `updated_at` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=62 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_razorpay_account
-- ----------------------------
DROP TABLE IF EXISTS `tb_razorpay_account`;
CREATE TABLE `tb_razorpay_account` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `contact_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `fund_account_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '联系人帐户id',
  `ifsc` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `name` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `account` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '银行卡卡号',
  `account_type` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `active` int(11) NOT NULL,
  `merchant_id` tinyint(4) unsigned DEFAULT '0',
  `pay_account_id` int(11) unsigned DEFAULT NULL,
  `created_at` int(11) unsigned NOT NULL COMMENT '创建时间',
  `updated_at` int(11) unsigned NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `contact_id` (`contact_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='支付联系人帐户表';

-- ----------------------------
-- Table structure for tb_razorpay_contace
-- ----------------------------
DROP TABLE IF EXISTS `tb_razorpay_contace`;
CREATE TABLE `tb_razorpay_contace` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT '借款用户id',
  `contact_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `type` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `active` int(11) NOT NULL,
  `merchant_id` tinyint(4) unsigned DEFAULT '0',
  `pay_account_id` int(11) unsigned DEFAULT NULL,
  `created_at` int(11) unsigned NOT NULL COMMENT '创建时间',
  `updated_at` int(11) unsigned NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `contact_id` (`contact_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='支付联系人';

-- ----------------------------
-- Table structure for tb_razorpay_upi_address
-- ----------------------------
DROP TABLE IF EXISTS `tb_razorpay_upi_address`;
CREATE TABLE `tb_razorpay_upi_address` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned DEFAULT NULL,
  `order_id` int(11) unsigned DEFAULT NULL,
  `vid` varchar(100) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `vpa_id` varchar(100) DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `handle` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `status` tinyint(4) DEFAULT '1',
  `pay_account_id` int(11) unsigned DEFAULT NULL,
  `merchant_id` tinyint(4) unsigned DEFAULT NULL,
  `va_id` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `va_bank_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `va_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `va_ifsc` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `va_account` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `version` tinyint(4) unsigned DEFAULT '0',
  `created_at` int(11) unsigned DEFAULT NULL,
  `updated_at` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`) USING BTREE,
  KEY `order_id` (`order_id`) USING BTREE,
  KEY `vid` (`vid`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_razorpay_virtual_account
-- ----------------------------
DROP TABLE IF EXISTS `tb_razorpay_virtual_account`;
CREATE TABLE `tb_razorpay_virtual_account` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned DEFAULT NULL,
  `order_id` int(11) unsigned DEFAULT NULL,
  `vid` varchar(255) DEFAULT NULL,
  `bid` varchar(255) DEFAULT NULL,
  `customer_id` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `bank_name` varchar(255) DEFAULT NULL,
  `ifsc` varchar(255) DEFAULT NULL,
  `account_number` varchar(255) DEFAULT NULL,
  `status` tinyint(4) DEFAULT '1',
  `merchant_id` tinyint(4) unsigned DEFAULT '0',
  `created_at` int(11) unsigned DEFAULT NULL,
  `updated_at` int(11) unsigned DEFAULT NULL,
  `pay_account_id` tinyint(4) unsigned DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_id` (`order_id`) USING BTREE,
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_refund_order
-- ----------------------------
DROP TABLE IF EXISTS `tb_refund_order`;
CREATE TABLE `tb_refund_order` (
  `id` int(11) unsigned NOT NULL,
  `order_id` int(11) unsigned DEFAULT NULL,
  `user_id` int(11) unsigned DEFAULT NULL,
  `order_uuid` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `amount` int(11) unsigned DEFAULT NULL,
  `status` tinyint(4) DEFAULT '1' COMMENT '  1 已退款 -1 已撤销',
  `remark` varchar(255) DEFAULT NULL,
  `operator_name` varchar(64) DEFAULT NULL,
  `created_at` int(11) unsigned DEFAULT NULL,
  `updated_at` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `order_uuid` (`order_uuid`),
  KEY `user_id` (`user_id`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for tb_remind_admin
-- ----------------------------
DROP TABLE IF EXISTS `tb_remind_admin`;
CREATE TABLE `tb_remind_admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `merchant_id` tinyint(4) unsigned DEFAULT NULL,
  `admin_user_id` int(11) unsigned DEFAULT NULL,
  `remind_group` int(11) unsigned DEFAULT '0',
  `status` tinyint(4) DEFAULT '1',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_remind_app_screen_shot
-- ----------------------------
DROP TABLE IF EXISTS `tb_remind_app_screen_shot`;
CREATE TABLE `tb_remind_app_screen_shot` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_remind_call_records
-- ----------------------------
DROP TABLE IF EXISTS `tb_remind_call_records`;
CREATE TABLE `tb_remind_call_records` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned DEFAULT NULL,
  `callName` varchar(255) DEFAULT NULL,
  `callNumber` varchar(255) DEFAULT NULL,
  `callType` tinyint(4) DEFAULT NULL,
  `callDate` varchar(255) DEFAULT NULL,
  `callDateTime` int(11) unsigned DEFAULT NULL,
  `callDuration` int(11) unsigned DEFAULT NULL,
  `created_at` int(11) unsigned DEFAULT NULL,
  `updated_at` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `callNumber` (`callNumber`) USING BTREE,
  KEY `user_id` (`user_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_remind_checkin_log
-- ----------------------------
DROP TABLE IF EXISTS `tb_remind_checkin_log`;
CREATE TABLE `tb_remind_checkin_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned DEFAULT NULL,
  `type` tinyint(4) unsigned DEFAULT '1' COMMENT '1-上班打卡 2-下班打卡',
  `address_type` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '打卡地址1,公司；2家',
  `date` date DEFAULT NULL,
  `created_at` int(11) unsigned DEFAULT NULL,
  `updated_at` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`) USING BTREE,
  KEY `checkin_time` (`date`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_remind_dispatch_log
-- ----------------------------
DROP TABLE IF EXISTS `tb_remind_dispatch_log`;
CREATE TABLE `tb_remind_dispatch_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `remind_id` int(11) unsigned DEFAULT '0',
  `before_customer_user_id` int(11) unsigned NOT NULL DEFAULT '0',
  `after_customer_user_id` int(11) unsigned NOT NULL DEFAULT '0',
  `before_customer_group` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `after_customer_group` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `remind_id` (`remind_id`),
  KEY `after_customer_user_id` (`after_customer_user_id`),
  KEY `created_at` (`created_at`),
  KEY `before_customer_user_id` (`before_customer_user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_remind_group
-- ----------------------------
DROP TABLE IF EXISTS `tb_remind_group`;
CREATE TABLE `tb_remind_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `merchant_id` tinyint(4) unsigned DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `team_leader_id` varchar(100) DEFAULT '',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_remind_log
-- ----------------------------
DROP TABLE IF EXISTS `tb_remind_log`;
CREATE TABLE `tb_remind_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `remind_id` int(11) DEFAULT NULL,
  `customer_user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '订单所属人id',
  `operator_user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '操作人id',
  `remind_return` int(11) NOT NULL DEFAULT '0',
  `payment_after_days` int(11) unsigned NOT NULL DEFAULT '0',
  `sms_template` int(11) unsigned NOT NULL DEFAULT '0',
  `remind_remark` varchar(255) DEFAULT NULL,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `remind_id` (`remind_id`),
  KEY `customer_user_id` (`customer_user_id`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_remind_order
-- ----------------------------
DROP TABLE IF EXISTS `tb_remind_order`;
CREATE TABLE `tb_remind_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `repayment_id` int(11) DEFAULT NULL COMMENT '订单还款ID',
  `customer_user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '客服ID',
  `customer_group` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '组',
  `plan_date_before_day` int(11) NOT NULL DEFAULT '0',
  `status` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '状态',
  `remind_return` int(11) NOT NULL DEFAULT '0',
  `payment_after_days` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'after days',
  `remind_count` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '提醒次数',
  `remind_remark` varchar(255) DEFAULT NULL COMMENT '提醒备注',
  `dispatch_status` tinyint(4) unsigned DEFAULT '0' COMMENT '分派状态',
  `dispatch_time` int(11) DEFAULT NULL,
  `merchant_id` tinyint(4) unsigned DEFAULT NULL,
  `is_test` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `created_at` int(11) DEFAULT NULL COMMENT '创建时间',
  `updated_at` int(11) DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `repaymenr_id` (`repayment_id`),
  KEY `created_at` (`created_at`),
  KEY `customer_user_id` (`customer_user_id`),
  KEY `dispatch_time` (`dispatch_time`)
) ENGINE=InnoDB AUTO_INCREMENT=66 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_remind_setting
-- ----------------------------
DROP TABLE IF EXISTS `tb_remind_setting`;
CREATE TABLE `tb_remind_setting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `merchant_id` tinyint(4) unsigned DEFAULT NULL,
  `run_time` int(11) DEFAULT NULL,
  `run_status` int(4) unsigned DEFAULT '0',
  `plan_date_before_day` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `run_time` (`run_time`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_remind_sms_template
-- ----------------------------
DROP TABLE IF EXISTS `tb_remind_sms_template`;
CREATE TABLE `tb_remind_sms_template` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `merchant_id` tinyint(4) unsigned DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `package_name` varchar(50) DEFAULT '',
  `content` text,
  `status` tinyint(4) unsigned DEFAULT '0',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_reminder_call_data
-- ----------------------------
DROP TABLE IF EXISTS `tb_reminder_call_data`;
CREATE TABLE `tb_reminder_call_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` date DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `name` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '',
  `type` tinyint(4) DEFAULT NULL,
  `is_valid` tinyint(4) DEFAULT '0',
  `times` int(11) unsigned DEFAULT '0',
  `duration` int(11) unsigned DEFAULT '0',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `phone_type` tinyint(4) DEFAULT '1' COMMENT '1:本机拨打 2:牛信拨打',
  PRIMARY KEY (`id`),
  KEY `date` (`date`),
  KEY `phone` (`phone`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_reminder_class_schedule
-- ----------------------------
DROP TABLE IF EXISTS `tb_reminder_class_schedule`;
CREATE TABLE `tb_reminder_class_schedule` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` date DEFAULT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '0已删除,1正常',
  `type` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `remark` varchar(255) DEFAULT NULL,
  `operator_id` int(11) DEFAULT NULL,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `date` (`date`),
  KEY `admin_id` (`admin_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_risk_black_list
-- ----------------------------
DROP TABLE IF EXISTS `tb_risk_black_list`;
CREATE TABLE `tb_risk_black_list` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned DEFAULT NULL,
  `black_status` tinyint(4) DEFAULT NULL,
  `black_remark` varchar(255) DEFAULT NULL,
  `source` tinyint(4) unsigned DEFAULT NULL,
  `operator_id` int(11) unsigned DEFAULT NULL,
  `created_at` int(11) unsigned DEFAULT NULL,
  `updated_at` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Table structure for tb_risk_black_list_aadhaar
-- ----------------------------
DROP TABLE IF EXISTS `tb_risk_black_list_aadhaar`;
CREATE TABLE `tb_risk_black_list_aadhaar` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `merchant_id` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) unsigned DEFAULT NULL,
  `value` varchar(20) DEFAULT NULL,
  `source` tinyint(4) unsigned DEFAULT NULL,
  `operator_id` int(11) unsigned DEFAULT NULL,
  `created_at` int(11) unsigned DEFAULT NULL,
  `updated_at` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `value` (`value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Table structure for tb_risk_black_list_contact
-- ----------------------------
DROP TABLE IF EXISTS `tb_risk_black_list_contact`;
CREATE TABLE `tb_risk_black_list_contact` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned DEFAULT NULL,
  `value` varchar(20) DEFAULT NULL,
  `source` tinyint(4) unsigned DEFAULT NULL,
  `operator_id` int(11) unsigned DEFAULT NULL,
  `created_at` int(11) unsigned DEFAULT NULL,
  `updated_at` int(11) unsigned DEFAULT NULL,
  `merchant_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `value` (`value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Table structure for tb_risk_black_list_deviceid
-- ----------------------------
DROP TABLE IF EXISTS `tb_risk_black_list_deviceid`;
CREATE TABLE `tb_risk_black_list_deviceid` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `merchant_id` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) unsigned DEFAULT NULL,
  `value` varchar(20) DEFAULT NULL,
  `source` tinyint(4) unsigned DEFAULT NULL,
  `operator_id` int(11) unsigned DEFAULT NULL,
  `created_at` int(11) unsigned DEFAULT NULL,
  `updated_at` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `value` (`value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Table structure for tb_risk_black_list_pan
-- ----------------------------
DROP TABLE IF EXISTS `tb_risk_black_list_pan`;
CREATE TABLE `tb_risk_black_list_pan` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `merchant_id` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) unsigned DEFAULT NULL,
  `value` varchar(20) DEFAULT NULL,
  `source` tinyint(4) unsigned DEFAULT NULL,
  `operator_id` int(11) unsigned DEFAULT NULL,
  `created_at` int(11) unsigned DEFAULT NULL,
  `updated_at` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `value` (`value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Table structure for tb_risk_black_list_phone
-- ----------------------------
DROP TABLE IF EXISTS `tb_risk_black_list_phone`;
CREATE TABLE `tb_risk_black_list_phone` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `merchant_id` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) unsigned DEFAULT NULL,
  `value` varchar(20) DEFAULT NULL,
  `source` tinyint(4) unsigned DEFAULT NULL,
  `operator_id` int(11) unsigned DEFAULT NULL,
  `created_at` int(11) unsigned DEFAULT NULL,
  `updated_at` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `value` (`value`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Table structure for tb_risk_black_list_szlm
-- ----------------------------
DROP TABLE IF EXISTS `tb_risk_black_list_szlm`;
CREATE TABLE `tb_risk_black_list_szlm` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `merchant_id` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) unsigned DEFAULT NULL,
  `value` varchar(100) DEFAULT NULL,
  `source` tinyint(4) unsigned DEFAULT NULL,
  `operator_id` int(11) unsigned DEFAULT NULL,
  `created_at` int(11) unsigned DEFAULT NULL,
  `updated_at` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `value` (`value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Table structure for tb_risk_result_snapshot
-- ----------------------------
DROP TABLE IF EXISTS `tb_risk_result_snapshot`;
CREATE TABLE `tb_risk_result_snapshot` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned DEFAULT NULL,
  `order_id` int(11) unsigned DEFAULT NULL,
  `tree_code` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `tree_version` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `result_data` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `base_node` text CHARACTER SET utf8 COLLATE utf8_general_ci,
  `guard_node` text CHARACTER SET utf8 COLLATE utf8_general_ci,
  `manual_node` text CHARACTER SET utf8 COLLATE utf8_general_ci,
  `result` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `txt` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `created_at` int(11) unsigned DEFAULT '0',
  `updated_at` int(11) unsigned DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `created_at` (`created_at`),
  KEY `order_id` (`order_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=16389 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for tb_risk_result_snapshot_gray
-- ----------------------------
DROP TABLE IF EXISTS `tb_risk_result_snapshot_gray`;
CREATE TABLE `tb_risk_result_snapshot_gray` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned DEFAULT NULL,
  `order_id` int(11) unsigned DEFAULT NULL,
  `tree_code` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `tree_version` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `result_data` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `base_node` text CHARACTER SET utf8 COLLATE utf8_general_ci,
  `guard_node` text CHARACTER SET utf8 COLLATE utf8_general_ci,
  `manual_node` text CHARACTER SET utf8 COLLATE utf8_general_ci,
  `result` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `txt` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `created_at` int(11) unsigned DEFAULT '0',
  `updated_at` int(11) unsigned DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `created_at` (`created_at`),
  KEY `order_id` (`order_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for tb_risk_rules
-- ----------------------------
DROP TABLE IF EXISTS `tb_risk_rules`;
CREATE TABLE `tb_risk_rules` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `type` tinyint(2) unsigned NOT NULL COMMENT '类型: 0,无定义; 1,基础; 2,哨兵表达式',
  `status` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '状态: 0,停用; 1,调试; 2,启用;',
  `code` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '节点代号',
  `order` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '优先级-DESC',
  `version` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '1.0',
  `alias` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '规则别名',
  `guard` varchar(256) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '条件表达式',
  `result` varchar(256) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '结果',
  `params` text CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT '默认参数',
  `description` text CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT '描述',
  `created_at` int(11) unsigned NOT NULL COMMENT '创建时间',
  `updated_at` int(11) unsigned NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_code_order_version` (`code`,`order`,`version`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1813 DEFAULT CHARSET=utf8 COMMENT='规则表';

-- ----------------------------
-- Table structure for tb_rule_version
-- ----------------------------
DROP TABLE IF EXISTS `tb_rule_version`;
CREATE TABLE `tb_rule_version` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `version` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `is_default` tinyint(4) DEFAULT NULL,
  `is_gray` tinyint(4) DEFAULT '0',
  `version_base_by` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `remark` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `created_at` int(11) unsigned DEFAULT NULL,
  `updated_at` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `version` (`version`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_shareholder_order
-- ----------------------------
DROP TABLE IF EXISTS `tb_shareholder_order`;
CREATE TABLE `tb_shareholder_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `inside_order_id` int(11) DEFAULT NULL,
  `order_no` varchar(64) DEFAULT NULL COMMENT '订单号',
  `order_id` varchar(64) DEFAULT NULL COMMENT '贷款id',
  `order_status` tinyint(3) unsigned DEFAULT NULL COMMENT '订单状态 10:新建订单 30:欺诈拒绝 40:审批中 50:审批拒绝 60:审批通过 70:用户取消贷款 80:放款中 85:待重新放款 90:放款成功 100:放款失败 110:正常还款中 120:逾期 130:提前还款中 140:正常结清 150:提前结清 160:逾期结清',
  `customer_id` varchar(64) DEFAULT NULL COMMENT '客户id',
  `is_reloan` tinyint(1) DEFAULT NULL COMMENT '是否为复贷',
  `order_time` datetime DEFAULT NULL COMMENT '下单时间',
  `check_time` datetime DEFAULT NULL COMMENT '审核时间',
  `conclusion` tinyint(4) DEFAULT NULL COMMENT '审批结论 1:审批通过 2:审批拒绝 3:贷款取消 4:其他',
  `request_principal` int(11) DEFAULT NULL COMMENT '申请金额',
  `request_date` date DEFAULT NULL COMMENT '申请日期',
  `request_period` tinyint(3) unsigned DEFAULT NULL COMMENT '申请期限',
  `principal` int(11) DEFAULT NULL COMMENT '合同金额',
  `due_date` date DEFAULT NULL COMMENT '还款日期',
  `approved_period` int(11) DEFAULT NULL COMMENT '贷款账期',
  `approved_date` date DEFAULT NULL COMMENT '放款日期',
  `approved_amount` int(11) DEFAULT NULL COMMENT '放款金额',
  `late_date` date DEFAULT NULL COMMENT '逾期日期',
  `late_day` int(11) DEFAULT NULL COMMENT '逾期天数',
  `interest_rate` float DEFAULT NULL COMMENT '利率',
  `interest` int(11) DEFAULT NULL COMMENT '利息',
  `part_repay_flag` tinyint(1) DEFAULT NULL COMMENT '是否部分还款',
  `total_amount` int(11) DEFAULT NULL COMMENT '应还总金额',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_shareholder_repay_order
-- ----------------------------
DROP TABLE IF EXISTS `tb_shareholder_repay_order`;
CREATE TABLE `tb_shareholder_repay_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `inside_order_id` int(11) DEFAULT NULL,
  `order_no` varchar(64) DEFAULT NULL COMMENT '订单号',
  `paid_time` date DEFAULT NULL COMMENT '还款成功日期',
  `current_period` tinyint(4) DEFAULT NULL COMMENT '本期期数',
  `current_principal` int(11) DEFAULT NULL COMMENT '本期应还本金',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_shareholder_user
-- ----------------------------
DROP TABLE IF EXISTS `tb_shareholder_user`;
CREATE TABLE `tb_shareholder_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `inside_user_id` int(11) DEFAULT NULL,
  `customer_id` varchar(64) DEFAULT NULL COMMENT '用户id',
  `id_card_name` varchar(64) DEFAULT NULL COMMENT '掩码后的用户姓名',
  `id_card_no` varchar(64) DEFAULT NULL COMMENT '掩码后的AadhaarNo',
  `id_card_birthday` date DEFAULT NULL,
  `cell_phone` varchar(64) DEFAULT NULL COMMENT '掩码后的手机号',
  `nationality` varchar(64) DEFAULT NULL COMMENT '国籍',
  `gender` tinyint(4) DEFAULT NULL COMMENT '性别 1:男 2:女 3:跨性别 -1:未知',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_sms_service_config
-- ----------------------------
DROP TABLE IF EXISTS `tb_sms_service_config`;
CREATE TABLE `tb_sms_service_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `platform` tinyint(4) DEFAULT NULL,
  `merchant_id` tinyint(4) DEFAULT NULL,
  `request_uri` varchar(255) DEFAULT NULL,
  `account` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `ext_config` text CHARACTER SET utf8 COLLATE utf8_general_ci,
  `status` tinyint(4) DEFAULT NULL,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_tab_bar_icon
-- ----------------------------
DROP TABLE IF EXISTS `tb_tab_bar_icon`;
CREATE TABLE `tb_tab_bar_icon` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(50) NOT NULL COMMENT '菜单标题',
  `type` enum('index','my') CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT 'index' COMMENT '类型',
  `normal_img` varchar(200) NOT NULL COMMENT '未选中状态Icon',
  `select_img` varchar(200) NOT NULL COMMENT '已选中状态Icon',
  `normal_color` char(7) NOT NULL COMMENT '未选中状态Color',
  `select_color` char(7) NOT NULL COMMENT '已选中状态Color',
  `package_setting_id` int(11) NOT NULL COMMENT '包ID',
  `is_google_review` tinyint(4) DEFAULT '0' COMMENT '谷歌商店审核 0:否 1:是',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_temp_whitelist
-- ----------------------------
DROP TABLE IF EXISTS `tb_temp_whitelist`;
CREATE TABLE `tb_temp_whitelist` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `phone` varchar(255) DEFAULT NULL,
  `pan` varchar(255) DEFAULT NULL,
  `status` tinyint(4) DEFAULT '1',
  `created_at` int(11) unsigned DEFAULT NULL,
  `updated_at` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pan_phone` (`phone`,`pan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_third_data_shumeng
-- ----------------------------
DROP TABLE IF EXISTS `tb_third_data_shumeng`;
CREATE TABLE `tb_third_data_shumeng` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL,
  `merchant_id` int(11) NOT NULL DEFAULT '0',
  `order_id` int(11) unsigned NOT NULL,
  `device_id` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `report` text CHARACTER SET utf8 COLLATE utf8_general_ci,
  `status` tinyint(4) DEFAULT '0',
  `err` tinyint(4) DEFAULT '0' COMMENT '0 设备正常 1 虚拟机 2 设备异常 -3设备号不存在',
  `device_type` tinyint(4) DEFAULT NULL,
  `retry_limit` tinyint(4) unsigned DEFAULT '0',
  `created_at` int(11) unsigned DEFAULT NULL,
  `updated_at` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`) USING BTREE,
  KEY `user_id` (`user_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for tb_truecaller_account
-- ----------------------------
DROP TABLE IF EXISTS `tb_truecaller_account`;
CREATE TABLE `tb_truecaller_account` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `phone_number` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `gender` varchar(50) DEFAULT NULL,
  `street` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `zipcode` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_truecaller_login_log
-- ----------------------------
DROP TABLE IF EXISTS `tb_truecaller_login_log`;
CREATE TABLE `tb_truecaller_login_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `phone_number` varchar(50) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `data` text,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_user_aadhaar_mask_back_log
-- ----------------------------
DROP TABLE IF EXISTS `tb_user_aadhaar_mask_back_log`;
CREATE TABLE `tb_user_aadhaar_mask_back_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `aadhaar_id` int(11) unsigned DEFAULT NULL,
  `status` tinyint(4) DEFAULT NULL,
  `request_id` varchar(64) DEFAULT NULL,
  `result` text COMMENT '报告内容',
  `created_at` int(11) unsigned DEFAULT NULL,
  `updated_at` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `aadhaar_id` (`aadhaar_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_user_aadhaar_mask_log
-- ----------------------------
DROP TABLE IF EXISTS `tb_user_aadhaar_mask_log`;
CREATE TABLE `tb_user_aadhaar_mask_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `aadhaar_id` int(11) unsigned DEFAULT NULL,
  `status` tinyint(4) DEFAULT NULL,
  `request_id` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `result` mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT '报告内容',
  `created_at` int(11) unsigned DEFAULT NULL,
  `updated_at` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tb_user_aadhaar_mask_log_aadhaar_id_index` (`aadhaar_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Table structure for tb_user_active_time
-- ----------------------------
DROP TABLE IF EXISTS `tb_user_active_time`;
CREATE TABLE `tb_user_active_time` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned DEFAULT NULL,
  `last_active_time` int(11) unsigned NOT NULL DEFAULT '0',
  `last_pay_time` int(11) unsigned NOT NULL DEFAULT '0',
  `last_money_sms_time` int(11) unsigned NOT NULL DEFAULT '0',
  `max_money` int(11) DEFAULT '0',
  `level_change_call_success_time` int(11) unsigned NOT NULL DEFAULT '0',
  `created_at` int(11) unsigned DEFAULT NULL,
  `updated_at` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `last_active_time` (`last_active_time`) USING BTREE,
  KEY `last_pay_time` (`last_pay_time`) USING BTREE,
  KEY `last_money_sms_time` (`last_money_sms_time`) USING BTREE,
  KEY `user_id` (`user_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=371 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_user_apply_complaint
-- ----------------------------
DROP TABLE IF EXISTS `tb_user_apply_complaint`;
CREATE TABLE `tb_user_apply_complaint` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL COMMENT '用户id',
  `problem_id` int(11) unsigned DEFAULT '0' COMMENT '投诉项目id',
  `description` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '内容描述',
  `image_list` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '图片信息',
  `contact_information` varchar(50) DEFAULT NULL COMMENT '联系方式手机或email',
  `last_accept_user_id` int(11) DEFAULT NULL COMMENT '最后受理人id',
  `last_accept_time` int(11) DEFAULT NULL COMMENT '最后受理时间',
  `accept_status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '受理状态',
  `merchant_id` tinyint(4) DEFAULT NULL,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COMMENT='用户申请投诉工单';

-- ----------------------------
-- Table structure for tb_user_apply_reduction
-- ----------------------------
DROP TABLE IF EXISTS `tb_user_apply_reduction`;
CREATE TABLE `tb_user_apply_reduction` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `merchant_id` tinyint(4) DEFAULT NULL COMMENT '商户',
  `user_id` int(11) DEFAULT NULL COMMENT '用户id',
  `order_id` int(11) DEFAULT NULL COMMENT '借款订单ID',
  `apply_reduction_fee` int(11) unsigned DEFAULT '0' COMMENT '申请减免金额',
  `assume_repayment_date` varchar(50) DEFAULT NULL COMMENT '预计还款时间',
  `reduction_reasons` text COMMENT '申请减免原因',
  `contact_information` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '联系方式手机或email',
  `last_accept_user_id` int(11) DEFAULT NULL COMMENT '最后受理人id',
  `last_accept_time` int(11) DEFAULT NULL COMMENT '最后受理时间',
  `accept_status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '受理状态',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='用户申请减免工单';

-- ----------------------------
-- Table structure for tb_user_bank_account
-- ----------------------------
DROP TABLE IF EXISTS `tb_user_bank_account`;
CREATE TABLE `tb_user_bank_account` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `source_type` tinyint(4) DEFAULT '1' COMMENT '1:元丁 2:AadhaarApi',
  `user_id` int(11) unsigned DEFAULT NULL,
  `merchant_id` int(11) NOT NULL DEFAULT '0',
  `service_account_name` varchar(256) DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '开户姓名',
  `report_account_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '验证报告中的开户名',
  `account` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '账户',
  `ifsc` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT 'ifsc code',
  `bank_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '银行名',
  `status` tinyint(4) DEFAULT '0' COMMENT '认证状态 0-未认证 1-认证通过 -1-认证失败',
  `main_card` tinyint(4) unsigned DEFAULT '0' COMMENT '0 - 非主卡 1 - 是主卡',
  `retry_limit` tinyint(4) unsigned DEFAULT '0' COMMENT '重试次数 ',
  `data` text CHARACTER SET utf8 COLLATE utf8_general_ci,
  `retry_time` int(11) unsigned DEFAULT NULL,
  `client_info` text CHARACTER SET utf8 COLLATE utf8_general_ci,
  `source_id` int(11) unsigned DEFAULT '1',
  `created_at` int(11) unsigned DEFAULT NULL,
  `updated_at` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`) USING BTREE,
  KEY `account` (`account`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=70 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for tb_user_bank_account_log
-- ----------------------------
DROP TABLE IF EXISTS `tb_user_bank_account_log`;
CREATE TABLE `tb_user_bank_account_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `account_id` int(11) DEFAULT NULL,
  `source_type` tinyint(4) DEFAULT '1' COMMENT '1:元丁 2:AadhaarApi 3:历史 4:Accuauth',
  `user_id` int(11) unsigned DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL COMMENT '开户姓名',
  `report_account_name` varchar(255) DEFAULT NULL COMMENT '验证报告中的开户名',
  `account` varchar(255) DEFAULT NULL COMMENT '账户',
  `ifsc` varchar(255) DEFAULT NULL COMMENT 'ifsc code',
  `bank_name` varchar(255) DEFAULT NULL COMMENT '银行名',
  `status` tinyint(4) DEFAULT '0' COMMENT '认证状态 0:待认证 1:认证中 2:认证成功 -1:认证失败',
  `data` text,
  `remark` varchar(255) DEFAULT NULL COMMENT '备注',
  `created_at` int(11) unsigned DEFAULT NULL,
  `updated_at` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`) USING BTREE,
  KEY `account` (`account`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_user_basic_info
-- ----------------------------
DROP TABLE IF EXISTS `tb_user_basic_info`;
CREATE TABLE `tb_user_basic_info` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned DEFAULT NULL,
  `merchant_id` int(11) NOT NULL DEFAULT '0',
  `full_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '全名',
  `birthday` date DEFAULT NULL COMMENT '生日',
  `religion` tinyint(4) unsigned DEFAULT NULL COMMENT '宗教',
  `student` tinyint(4) unsigned DEFAULT NULL COMMENT '是否学生',
  `marital_status` tinyint(4) unsigned DEFAULT NULL COMMENT '婚姻状态',
  `email_address` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '电子邮箱',
  `zip_code` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `loan_purpose` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '借款用途',
  `bank_statement` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '银行流水照片',
  `aadhaar_pin_code` varchar(16) DEFAULT NULL COMMENT 'aad卡上的邮编',
  `aadhaar_address1` varchar(255) DEFAULT NULL COMMENT 'aad卡上的居住区域联邦',
  `aadhaar_address2` varchar(255) DEFAULT NULL COMMENT 'aad卡上的居住区域城市',
  `aadhaar_address_code1` tinyint(4) unsigned DEFAULT NULL COMMENT 'aad卡上的居住地区编号联邦',
  `aadhaar_address_code2` tinyint(4) unsigned DEFAULT NULL COMMENT 'aad卡上的居住地区编号城市',
  `aadhaar_detail_address` varchar(255) DEFAULT NULL COMMENT 'aad卡上的居住详细地址',
  `client_info` varchar(1024) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `created_at` int(11) unsigned DEFAULT NULL,
  `updated_at` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=70 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for tb_user_captcha
-- ----------------------------
DROP TABLE IF EXISTS `tb_user_captcha`;
CREATE TABLE `tb_user_captcha` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `phone` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '手机号',
  `captcha` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '验证码',
  `type` varchar(15) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '类型，比如注册、找回密码等',
  `source_id` int(11) unsigned DEFAULT '1' COMMENT '用户来源',
  `generate_time` int(11) unsigned NOT NULL COMMENT '生成时间',
  `expire_time` int(11) unsigned NOT NULL COMMENT '过期时间',
  PRIMARY KEY (`id`),
  KEY `idx_phone` (`phone`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='用户手机验证码';

-- ----------------------------
-- Table structure for tb_user_contact
-- ----------------------------
DROP TABLE IF EXISTS `tb_user_contact`;
CREATE TABLE `tb_user_contact` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL,
  `merchant_id` int(11) NOT NULL DEFAULT '0',
  `relative_contact_person` tinyint(4) unsigned DEFAULT NULL COMMENT '与第一联系人的关系',
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '第一联系人姓名',
  `phone` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '第一联系人手机号',
  `other_relative_contact_person` tinyint(4) unsigned DEFAULT NULL COMMENT '与第二联系的关系',
  `other_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '第二联系人姓名',
  `other_phone` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '第二联系人手机号',
  `facebook_account` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `whatsApp_account` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `skype_account` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `client_info` varchar(1024) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `created_at` int(11) unsigned DEFAULT NULL,
  `updated_at` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `phone` (`phone`),
  KEY `other_phone` (`other_phone`)
) ENGINE=InnoDB AUTO_INCREMENT=55 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for tb_user_conversion_data
-- ----------------------------
DROP TABLE IF EXISTS `tb_user_conversion_data`;
CREATE TABLE `tb_user_conversion_data` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL,
  `data` text COMMENT '用户信息',
  `created_at` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_user_coupon_info
-- ----------------------------
DROP TABLE IF EXISTS `tb_user_coupon_info`;
CREATE TABLE `tb_user_coupon_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT '用户id',
  `merchant_id` int(11) NOT NULL DEFAULT '0',
  `phone` bigint(20) DEFAULT '0' COMMENT '手机号',
  `coupon_id` int(11) NOT NULL COMMENT '优惠券模板ID',
  `use_case` tinyint(4) NOT NULL DEFAULT '1' COMMENT '使用类型：1、慢就赔',
  `use_type` tinyint(4) DEFAULT '1' COMMENT '使用方式',
  `coupon_code` char(21) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '优惠券批次号',
  `title` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '' COMMENT '优惠券标题',
  `amount` int(11) NOT NULL DEFAULT '0' COMMENT '优惠券金额',
  `is_use` tinyint(4) DEFAULT '0' COMMENT '0:未使用;1:已使用',
  `start_time` int(11) DEFAULT '0' COMMENT '起效时间',
  `end_time` int(11) DEFAULT '0' COMMENT '结束时间',
  `use_time` int(11) DEFAULT '0' COMMENT '使用时间',
  `user_admin` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '操作人',
  `created_at` int(11) DEFAULT NULL COMMENT '创建时间',
  `updated_at` int(11) DEFAULT NULL COMMENT '更新时间',
  `remark` text CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT '备注',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `phone` (`phone`),
  KEY `idx_start_time` (`start_time`),
  KEY `idx_end_time` (`end_time`),
  KEY `idx_coupon_id` (`coupon_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='用户领券记录';

-- ----------------------------
-- Table structure for tb_user_credit_limit
-- ----------------------------
DROP TABLE IF EXISTS `tb_user_credit_limit`;
CREATE TABLE `tb_user_credit_limit` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL,
  `max_limit` int(11) unsigned NOT NULL DEFAULT '150000',
  `min_limit` int(11) unsigned NOT NULL DEFAULT '150000',
  `type` tinyint(4) unsigned DEFAULT '1',
  `created_at` int(11) unsigned NOT NULL,
  `updated_at` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=646 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='用户额度表';

-- ----------------------------
-- Table structure for tb_user_credit_limit_change_log
-- ----------------------------
DROP TABLE IF EXISTS `tb_user_credit_limit_change_log`;
CREATE TABLE `tb_user_credit_limit_change_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL COMMENT '用户',
  `before_max_limit` int(11) unsigned NOT NULL COMMENT '更改前额度',
  `after_max_limit` int(11) unsigned NOT NULL COMMENT '更改后额度',
  `before_min_limit` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `after_min_limit` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `type` tinyint(4) unsigned DEFAULT NULL COMMENT '类型',
  `reason` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '原因',
  `created_at` int(11) unsigned NOT NULL,
  `updated_at` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='用户额度更改记录';

-- ----------------------------
-- Table structure for tb_user_credit_report
-- ----------------------------
DROP TABLE IF EXISTS `tb_user_credit_report`;
CREATE TABLE `tb_user_credit_report` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned DEFAULT NULL,
  `merchant_id` int(11) NOT NULL DEFAULT '0',
  `account_name` varchar(256) DEFAULT NULL,
  `source_type` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1:accuauth',
  `report_type` tinyint(4) DEFAULT NULL COMMENT '1. aad-ocr 2. pan-ocr 3. pan-verify',
  `report_status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0:未收到 1:已收到',
  `report_data` text CHARACTER SET utf8 COLLATE utf8_general_ci,
  `created_at` int(11) unsigned DEFAULT NULL,
  `updated_at` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=135 DEFAULT CHARSET=utf8 COMMENT='用户认证报告';

-- ----------------------------
-- Table structure for tb_user_credit_report_cibil
-- ----------------------------
DROP TABLE IF EXISTS `tb_user_credit_report_cibil`;
CREATE TABLE `tb_user_credit_report_cibil` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL,
  `merchant_id` int(11) NOT NULL DEFAULT '0',
  `pan_code` varchar(255) DEFAULT NULL,
  `retry_num` int(11) unsigned DEFAULT NULL,
  `score` int(11) DEFAULT NULL COMMENT '征信分',
  `name` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `data` mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci,
  `status` int(11) NOT NULL DEFAULT '0' COMMENT '0:失败，1:成功',
  `is_request` int(11) unsigned NOT NULL DEFAULT '0',
  `query_time` int(11) unsigned NOT NULL DEFAULT '0',
  `created_at` int(11) unsigned NOT NULL DEFAULT '0',
  `updated_at` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `pan_code` (`pan_code`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for tb_user_credit_report_experian
-- ----------------------------
DROP TABLE IF EXISTS `tb_user_credit_report_experian`;
CREATE TABLE `tb_user_credit_report_experian` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `merchant_id` int(11) NOT NULL DEFAULT '0',
  `pan_code` varchar(255) DEFAULT NULL,
  `retry_num` int(11) DEFAULT NULL,
  `score` int(11) DEFAULT NULL COMMENT '征信分',
  `name` varchar(64) DEFAULT NULL,
  `data` mediumtext,
  `status` int(11) NOT NULL DEFAULT '0' COMMENT '0:失败，1:成功',
  `is_request` int(11) unsigned NOT NULL DEFAULT '0',
  `query_time` int(11) unsigned NOT NULL DEFAULT '0',
  `data_status` int(11) DEFAULT NULL,
  `created_at` int(11) unsigned NOT NULL DEFAULT '0',
  `updated_at` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `pan_code` (`pan_code`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_user_credit_report_fr_liveness
-- ----------------------------
DROP TABLE IF EXISTS `tb_user_credit_report_fr_liveness`;
CREATE TABLE `tb_user_credit_report_fr_liveness` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned DEFAULT NULL,
  `merchant_id` int(11) NOT NULL DEFAULT '0',
  `report_id` int(11) unsigned DEFAULT NULL,
  `type` tinyint(4) DEFAULT '0' COMMENT 'sdk类型',
  `report_status` tinyint(4) DEFAULT '0' COMMENT '0:未收到 1:已收到 2:报告错误 3:未达到阈值 4:通过',
  `data_status` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `is_used` tinyint(4) DEFAULT '0' COMMENT '0:未使用 1:当前在使用',
  `score` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `img_fr_path` varchar(256) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '人脸照地址',
  `data_fr_path` varchar(256) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '数据包地址',
  `created_at` int(11) unsigned DEFAULT NULL,
  `updated_at` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tb_user_credit_report_fr_liveness_report_id_index` (`report_id`),
  KEY `tb_user_credit_report_fr_liveness_user_id_index` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_user_credit_report_fr_verify
-- ----------------------------
DROP TABLE IF EXISTS `tb_user_credit_report_fr_verify`;
CREATE TABLE `tb_user_credit_report_fr_verify` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned DEFAULT NULL,
  `merchant_id` int(11) NOT NULL DEFAULT '0',
  `report_id` int(11) unsigned DEFAULT NULL,
  `report_status` tinyint(4) DEFAULT NULL COMMENT '0:未收到 1:已收到 2:报告错误 3:未达到阈值',
  `data_status` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `is_used` tinyint(4) DEFAULT '0' COMMENT '0:未使用 1:当前在使用',
  `report_type` tinyint(4) DEFAULT NULL COMMENT '0:fr_compare_pan 1:fr_compare_fr',
  `identical` tinyint(1) DEFAULT NULL COMMENT '人脸匹配结果',
  `score` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '人脸匹配分数',
  `img1_path` varchar(256) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `img2_path` varchar(256) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `img1_report_id` int(11) unsigned DEFAULT NULL,
  `img2_report_id` int(11) unsigned DEFAULT NULL,
  `created_at` int(11) unsigned DEFAULT NULL,
  `updated_at` int(11) unsigned DEFAULT '0',
  `type` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `tb_user_credit_report_fr_verify_report_id_index` (`report_id`),
  KEY `tb_user_credit_report_fr_verify_user_id_index` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户人脸对比';

-- ----------------------------
-- Table structure for tb_user_credit_report_ocr_aad
-- ----------------------------
DROP TABLE IF EXISTS `tb_user_credit_report_ocr_aad`;
CREATE TABLE `tb_user_credit_report_ocr_aad` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL,
  `merchant_id` int(11) NOT NULL DEFAULT '0',
  `report_id` int(11) unsigned DEFAULT NULL,
  `report_status` tinyint(4) DEFAULT '0' COMMENT '0:未收到 1:已收到 2:报告错误 3:未达到阈值 4:通过',
  `data_front_status` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `data_back_status` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `is_used` tinyint(4) DEFAULT '0' COMMENT '0:未使用 1:当前在使用',
  `card_no` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `card_no_mask` varchar(64) DEFAULT NULL,
  `card_no_md5` varchar(64) DEFAULT NULL,
  `card_no_encode` varchar(64) DEFAULT NULL,
  `vid` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `date_type` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `date_info` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `gender` tinyint(4) DEFAULT NULL,
  `father_name` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `mother_name` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `full_name` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `phone_number` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `address` varchar(256) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `pin` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `state` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `city` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `img_front_path` varchar(256) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `img_back_path` varchar(256) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `img_front_mask_path` varchar(256) DEFAULT NULL,
  `img_back_mask_path` varchar(255) DEFAULT NULL,
  `check_data_z_path` varchar(256) DEFAULT NULL,
  `check_data_f_path` varchar(256) DEFAULT NULL,
  `is_mask` tinyint(4) DEFAULT '0',
  `is_mask_back` tinyint(4) DEFAULT '0',
  `is_encode` tinyint(4) DEFAULT '0',
  `created_at` int(11) unsigned DEFAULT NULL,
  `updated_at` int(11) unsigned DEFAULT NULL,
  `type` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `tb_user_credit_report_ocr_aad_report_id_index` (`report_id`),
  KEY `tb_user_credit_report_ocr_aad_user_id_index` (`user_id`),
  KEY `card_no` (`card_no`),
  KEY `tb_user_credit_report_ocr_aad_card_no_md5_index` (`card_no_md5`),
  KEY `is_mask_back` (`is_mask_back`),
  KEY `is_mask` (`is_mask`)
) ENGINE=InnoDB AUTO_INCREMENT=69 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_user_credit_report_ocr_pan
-- ----------------------------
DROP TABLE IF EXISTS `tb_user_credit_report_ocr_pan`;
CREATE TABLE `tb_user_credit_report_ocr_pan` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned DEFAULT NULL,
  `merchant_id` int(11) NOT NULL DEFAULT '0',
  `report_id` int(11) unsigned DEFAULT NULL,
  `report_status` tinyint(4) DEFAULT '0' COMMENT '0:未收到 1:已收到 2:报告错误 3:未达到阈值 4:通过',
  `data_status` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `is_used` tinyint(4) DEFAULT '0' COMMENT '0:未使用 1:当前在使用',
  `card_no` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `date_type` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `date_info` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `father_name` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `full_name` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `img_front_path` varchar(256) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `img_back_path` varchar(256) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `created_at` int(11) unsigned DEFAULT NULL,
  `updated_at` int(11) unsigned DEFAULT NULL,
  `type` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `tb_user_credit_report_ocr_pan_report_id_index` (`report_id`),
  KEY `tb_user_credit_report_ocr_pan_user_id_index` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=67 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_user_loan_order
-- ----------------------------
DROP TABLE IF EXISTS `tb_user_loan_order`;
CREATE TABLE `tb_user_loan_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_uuid` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '订单编号',
  `user_id` int(11) unsigned NOT NULL COMMENT '用户id',
  `product_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '产品ID',
  `amount` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '金额，单位为分',
  `day_rate` decimal(15,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '日利率',
  `interests` int(11) unsigned NOT NULL COMMENT '总共利息，单位分',
  `overdue_fee` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '单位：分，滞纳金，脚本跑出来，当还款的时候重新计算进行核对',
  `overdue_rate` decimal(11,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '滞纳金日利率，单位为百分之几',
  `cost_fee` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '手续费+gst，单位为分',
  `cost_rate` decimal(11,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '一整期手续费利率，单位为百分之几',
  `gst_fee` int(11) unsigned DEFAULT '0' COMMENT 'GST费用 是手续费的18%',
  `loan_method` tinyint(4) unsigned NOT NULL COMMENT '期数单位：0-按天 1-按月 2-按年',
  `loan_term` tinyint(4) unsigned NOT NULL COMMENT '每期的时间周期，根据loan_method确定单位',
  `periods` tinyint(4) unsigned NOT NULL COMMENT '多少期',
  `card_id` int(11) unsigned DEFAULT '0' COMMENT '银行卡ID',
  `status` tinyint(4) NOT NULL DEFAULT '0',
  `audit_status` tinyint(4) DEFAULT '1',
  `audit_bank_status` tinyint(4) DEFAULT '1',
  `bank_num` tinyint(4) DEFAULT '1',
  `loan_status` tinyint(4) DEFAULT '0' COMMENT '支付状态',
  `audit_operator` int(11) unsigned DEFAULT '0',
  `audit_bank_operator` int(11) unsigned DEFAULT '0',
  `audit_begin_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '领取审核订单时间',
  `audit_bank_begin_time` int(11) NOT NULL DEFAULT '0' COMMENT '领取审核订单绑卡时间',
  `order_time` int(11) unsigned NOT NULL COMMENT '下单时间',
  `loan_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '放款时间，用于计算利息的起止时间',
  `audit_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '订单审核时间',
  `audit_question` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '审核问题',
  `is_first` tinyint(2) unsigned DEFAULT '0' COMMENT '是否是首单，0，不是；1，是',
  `is_all_first` tinyint(3) unsigned DEFAULT '0' COMMENT '是否全局首单，0：不是；1：是',
  `app_market` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '下单app名',
  `client_info` text CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT '客户端信息',
  `device_id` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '设备号',
  `ip` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT 'ip地址',
  `black_box` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '同盾用户标识',
  `did` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '数盟设备指纹',
  `fund_id` int(11) unsigned DEFAULT '0' COMMENT '资方ID',
  `merchant_id` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `is_export` tinyint(4) DEFAULT NULL COMMENT '是否外部导流订单',
  `credit_limit` int(11) unsigned DEFAULT '0' COMMENT '当前授信额度',
  `created_at` int(11) unsigned NOT NULL COMMENT '创建时间',
  `updated_at` int(11) unsigned NOT NULL COMMENT '更新时间',
  `coupon_id` int(11) DEFAULT NULL,
  `coupon_money` int(11) DEFAULT NULL,
  `is_push` int(11) DEFAULT NULL,
  `old_credit_limit` int(11) unsigned DEFAULT NULL COMMENT '老授信额度',
  `auto_draw` enum('y','n') DEFAULT 'n' COMMENT '是否自动提现',
  `auto_draw_time` int(11) unsigned DEFAULT NULL COMMENT '自动提现期限',
  PRIMARY KEY (`id`),
  KEY `idx_loan_time` (`loan_time`),
  KEY `idx_status` (`status`),
  KEY `user_id` (`user_id`),
  KEY `device_id` (`device_id`),
  KEY `ip` (`ip`),
  KEY `order_time` (`order_time`),
  KEY `loan_status` (`loan_status`) USING BTREE,
  KEY `did` (`did`) USING BTREE,
  KEY `order_uuid` (`order_uuid`)
) ENGINE=InnoDB AUTO_INCREMENT=67 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='借款订单';

-- ----------------------------
-- Table structure for tb_user_loan_order_delay_payment_log
-- ----------------------------
DROP TABLE IF EXISTS `tb_user_loan_order_delay_payment_log`;
CREATE TABLE `tb_user_loan_order_delay_payment_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned DEFAULT NULL,
  `order_id` int(11) unsigned DEFAULT NULL,
  `amount` int(11) unsigned DEFAULT NULL COMMENT '延期支付金额',
  `delay_start_time` int(11) unsigned DEFAULT NULL COMMENT '延期开始时间',
  `delay_end_time` int(11) unsigned DEFAULT NULL COMMENT '延期截止时间',
  `created_at` int(11) unsigned DEFAULT NULL,
  `updated_at` int(11) unsigned DEFAULT NULL,
  `delay_reduce_amount` int(11) unsigned DEFAULT '0' COMMENT '延期减免金额',
  PRIMARY KEY (`id`),
  KEY `tb_user_loan_order_delay_payment_order_id_index` (`order_id`),
  KEY `tb_user_loan_order_delay_payment_user_id_index` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='用户订单延期日志表';

-- ----------------------------
-- Table structure for tb_user_loan_order_extend_log
-- ----------------------------
DROP TABLE IF EXISTS `tb_user_loan_order_extend_log`;
CREATE TABLE `tb_user_loan_order_extend_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned DEFAULT NULL,
  `order_id` int(11) unsigned DEFAULT NULL,
  `amount` int(11) unsigned DEFAULT NULL,
  `days` tinyint(4) unsigned DEFAULT NULL,
  `begin_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `created_at` int(11) unsigned DEFAULT NULL,
  `updated_at` int(11) unsigned DEFAULT NULL,
  `collector_id` int(11) unsigned DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_user_loan_order_extra_relation_new
-- ----------------------------
DROP TABLE IF EXISTS `tb_user_loan_order_extra_relation_new`;
CREATE TABLE `tb_user_loan_order_extra_relation_new` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(11) unsigned DEFAULT NULL,
  `user_ocr_pan_id` int(11) unsigned DEFAULT NULL,
  `user_ocr_aadhaar_id` int(11) unsigned DEFAULT NULL,
  `user_verify_pan_id` int(11) unsigned DEFAULT NULL,
  `user_fr_id` int(11) unsigned DEFAULT NULL,
  `user_fr_pan_id` int(11) unsigned DEFAULT NULL,
  `user_fr_fr_id` int(11) unsigned DEFAULT NULL,
  `user_basic_info_id` int(11) unsigned DEFAULT NULL,
  `user_work_info_id` int(11) unsigned DEFAULT NULL,
  `user_contact_id` int(11) unsigned DEFAULT NULL,
  `user_credit_report_cibil_id` int(11) unsigned DEFAULT NULL COMMENT 'cibil征信报告',
  `user_credit_report_experian_id` int(11) DEFAULT NULL,
  `user_language_report_id` int(11) unsigned DEFAULT NULL,
  `created_at` int(11) unsigned DEFAULT NULL,
  `updated_at` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tb_user_loan_order_extra_relation_order_id_index` (`order_id`)
) ENGINE=InnoDB AUTO_INCREMENT=66 DEFAULT CHARSET=utf8 COMMENT='订单用户信息关联表';

-- ----------------------------
-- Table structure for tb_user_loan_order_repayment
-- ----------------------------
DROP TABLE IF EXISTS `tb_user_loan_order_repayment`;
CREATE TABLE `tb_user_loan_order_repayment` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL COMMENT '用户id',
  `order_id` int(11) NOT NULL DEFAULT '0' COMMENT '跟借款订单关联，为借款订单',
  `total_money` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '应还总额，单位分',
  `true_total_money` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '已还总额 ，单位分',
  `principal` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '本金，单位为分',
  `interests` int(11) unsigned DEFAULT '0' COMMENT '利息：单位分',
  `cost_fee` int(11) unsigned DEFAULT '0' COMMENT '手续费：单位分',
  `overdue_fee` int(11) unsigned DEFAULT '0' COMMENT '滞纳金：单位分',
  `coupon_money` int(11) unsigned DEFAULT '0' COMMENT '抵扣券抵扣金额',
  `reduction_money` int(11) unsigned DEFAULT '0' COMMENT '减免金额',
  `is_overdue` tinyint(2) unsigned DEFAULT '0' COMMENT '是否是逾期：0，不是；1，是',
  `overdue_day` int(11) unsigned DEFAULT '0' COMMENT '逾期天数',
  `collection_overdue_day` int(11) unsigned DEFAULT '0',
  `card_id` int(11) unsigned DEFAULT '0' COMMENT '银行卡ID',
  `coupon_id` int(11) unsigned DEFAULT '0' COMMENT '抵扣券ID',
  `merchant_id` tinyint(4) unsigned DEFAULT '0',
  `operator_name` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '操作人',
  `status` tinyint(4) DEFAULT '0' COMMENT '状态：0：生息中；1还款完成',
  `is_delay_repayment` tinyint(4) DEFAULT '0' COMMENT '延期还款 0:否 1:是',
  `loan_time` int(11) unsigned DEFAULT '0' COMMENT '放款事件',
  `plan_repayment_time` int(11) unsigned DEFAULT '0' COMMENT '结息日期',
  `plan_fee_time` int(11) unsigned DEFAULT '0' COMMENT '开始计算滞纳金时间',
  `closing_time` int(11) unsigned DEFAULT '0' COMMENT '清单结清时间',
  `interest_time` int(11) unsigned DEFAULT '0' COMMENT '起息日',
  `created_at` int(11) DEFAULT '0' COMMENT '创建时间',
  `updated_at` int(11) unsigned DEFAULT '0' COMMENT '更新时间',
  `is_extend` enum('yes','no') CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT 'no' COMMENT '是否展期中',
  `extend_begin_date` date DEFAULT NULL COMMENT '展期开始日期',
  `extend_end_date` date DEFAULT NULL COMMENT '展期结束日期',
  `extend_total` tinyint(4) unsigned DEFAULT '0' COMMENT '展期次数',
  `delay_reduce_amount` int(11) unsigned DEFAULT '0' COMMENT '延期减免金额',
  `is_push_assist` int(11) DEFAULT '0',
  `is_push_remind` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `order_id` (`order_id`),
  KEY `plan_fee_time` (`plan_fee_time`),
  KEY `idx_loan_time` (`loan_time`),
  KEY `is_overdue` (`is_overdue`),
  KEY `overdue_day` (`overdue_day`)
) ENGINE=InnoDB AUTO_INCREMENT=66 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='还款订单';

-- ----------------------------
-- Table structure for tb_user_login_log
-- ----------------------------
DROP TABLE IF EXISTS `tb_user_login_log`;
CREATE TABLE `tb_user_login_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL COMMENT '用户id',
  `type` tinyint(4) NOT NULL COMMENT '登录类型，比如普通用户名密码登录、qq联合登录',
  `source` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '来源信息',
  `created_ip` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'ip',
  `device_id` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `push_token` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `created_at` int(11) unsigned NOT NULL COMMENT '时间',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `device_id` (`device_id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='用户登录日志';

-- ----------------------------
-- Table structure for tb_user_order_loan_check_log
-- ----------------------------
DROP TABLE IF EXISTS `tb_user_order_loan_check_log`;
CREATE TABLE `tb_user_order_loan_check_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL DEFAULT '0' COMMENT '订单ID',
  `user_id` int(11) DEFAULT NULL,
  `repayment_id` int(11) DEFAULT '0' COMMENT '借款时为0，还款时，如果有计划表，那边为分期计划表中的自增ID，用来获取分期数据',
  `before_status` int(11) DEFAULT '0' COMMENT '原始状态',
  `after_status` int(11) DEFAULT '0' COMMENT '修改后状态',
  `operator` int(11) unsigned DEFAULT NULL COMMENT '审核人',
  `remark` text CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT '备注',
  `type` int(11) DEFAULT '0' COMMENT '1、借款；2、还款',
  `operation_type` tinyint(4) unsigned NOT NULL COMMENT '操作类型，如放款初审、复审等，详见model类',
  `repayment_type` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '还款类型：1零钱贷  2房租贷',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `head_code` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '备注--头码',
  `back_code` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '备注--尾码',
  `reason_remark` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '审核码',
  `can_loan_type` tinyint(4) NOT NULL DEFAULT '1' COMMENT '是否可再借 1 可再借 -1 不可再借 2 1个月后再借',
  `tree` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '决策树',
  `rule_version` varchar(16) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '' COMMENT '决策树版本',
  `before_audit_status` tinyint(4) DEFAULT NULL,
  `after_audit_status` tinyint(4) DEFAULT NULL,
  `after_audit_bank_status` tinyint(4) DEFAULT NULL,
  `before_audit_bank_status` tinyint(4) DEFAULT NULL,
  `audit_remark` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `before_loan_status` int(11) DEFAULT NULL,
  `after_loan_status` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `index_created_at` (`created_at`),
  KEY `index_before_status` (`before_status`,`type`,`tree`),
  KEY `index_operator` (`operator`),
  KEY `IDX_order_id` (`order_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=179 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='借款审核表';

-- ----------------------------
-- Table structure for tb_user_overdue_contact
-- ----------------------------
DROP TABLE IF EXISTS `tb_user_overdue_contact`;
CREATE TABLE `tb_user_overdue_contact` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `phone` varchar(16) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `user_id` int(10) unsigned DEFAULT NULL,
  `merchant_id` int(11) NOT NULL DEFAULT '0',
  `created_at` int(11) unsigned DEFAULT NULL,
  `updated_at` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `phone` (`phone`)
) ENGINE=InnoDB AUTO_INCREMENT=273 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for tb_user_pan_check_log
-- ----------------------------
DROP TABLE IF EXISTS `tb_user_pan_check_log`;
CREATE TABLE `tb_user_pan_check_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned DEFAULT NULL,
  `merchant_id` int(11) NOT NULL DEFAULT '0',
  `account_name` varchar(256) DEFAULT NULL,
  `pan_input` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `pan_ocr` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `ocr_report_id` int(11) unsigned DEFAULT NULL,
  `check_third_source` tinyint(4) DEFAULT NULL COMMENT '0:历史记录 1:第三方',
  `report_status` tinyint(4) DEFAULT NULL COMMENT 'pan卡 0:无效 1:有效',
  `data_status` tinyint(4) DEFAULT NULL COMMENT '1:通过\n2:不通过（pan卡号不符规则）\n3:不通过（pan卡号无效）\n4:不通过（两次卡号不一致）\n5:不通过（卡号被他人绑定）',
  `full_name` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `first_name` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `middle_name` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `last_name` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `report_data` text CHARACTER SET utf8 COLLATE utf8_general_ci,
  `is_used` tinyint(4) DEFAULT NULL,
  `client_info` text CHARACTER SET utf8 COLLATE utf8_general_ci,
  `package_name` varchar(64) DEFAULT NULL,
  `created_at` int(11) unsigned DEFAULT NULL,
  `updated_at` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tb_user_pan_check_log_pan_input_index` (`pan_input`),
  KEY `tb_user_pan_check_log_user_id_index` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for tb_user_password
-- ----------------------------
DROP TABLE IF EXISTS `tb_user_password`;
CREATE TABLE `tb_user_password` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT '用户id',
  `password` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '登录密码',
  `status` tinyint(3) NOT NULL DEFAULT '0' COMMENT '是否已确认',
  `created_at` int(11) NOT NULL COMMENT '创建时间',
  `updated_at` int(11) NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=646 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='用户登录密码表';

-- ----------------------------
-- Table structure for tb_user_photo_url
-- ----------------------------
DROP TABLE IF EXISTS `tb_user_photo_url`;
CREATE TABLE `tb_user_photo_url` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `created_at` int(11) unsigned NOT NULL DEFAULT '0',
  `updated_at` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_user_question_verification
-- ----------------------------
DROP TABLE IF EXISTS `tb_user_question_verification`;
CREATE TABLE `tb_user_question_verification` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned DEFAULT NULL,
  `merchant_id` int(11) NOT NULL DEFAULT '0',
  `questions` varchar(255) DEFAULT NULL COMMENT '分配的题目',
  `answers` varchar(255) DEFAULT NULL COMMENT '配置的答案',
  `user_answers` varchar(255) DEFAULT NULL COMMENT '用户的答案',
  `question_num` tinyint(4) DEFAULT NULL COMMENT '题目数量',
  `correct_num` tinyint(4) DEFAULT NULL COMMENT '正确数量',
  `enter_time` int(11) unsigned DEFAULT NULL COMMENT '前端收集，进入时间',
  `submit_time` int(11) unsigned DEFAULT NULL COMMENT '前端收集，提交时间',
  `data_status` tinyint(4) DEFAULT '0' COMMENT '0:分配题目\n1:提交答案',
  `created_at` int(11) unsigned DEFAULT NULL,
  `updated_at` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tb_user_question_verification_user_id_index` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户回答问题认证';

-- ----------------------------
-- Table structure for tb_user_red_packets_slow
-- ----------------------------
DROP TABLE IF EXISTS `tb_user_red_packets_slow`;
CREATE TABLE `tb_user_red_packets_slow` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `merchant_id` int(11) NOT NULL DEFAULT '0',
  `code_pre` char(4) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '批次号前缀',
  `title` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '红包标题',
  `amount` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0' COMMENT '固定金额范围,单位为分',
  `use_case` tinyint(11) NOT NULL DEFAULT '1',
  `use_type` int(11) NOT NULL DEFAULT '0' COMMENT '使用方式',
  `status` int(11) NOT NULL DEFAULT '0' COMMENT '0:禁用;1:启用',
  `user_use_days` int(11) DEFAULT '0' COMMENT '用户使用天数',
  `user_admin` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '操作人',
  `created_at` int(11) DEFAULT '0' COMMENT '创建时间',
  `updated_at` int(11) DEFAULT '0' COMMENT '更新时间',
  `remark` text CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT '使用说明',
  `use_start_time` int(11) DEFAULT '0' COMMENT '使用开始时间',
  `use_end_time` int(11) DEFAULT '0' COMMENT '使用结束时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='优惠券模板';

-- ----------------------------
-- Table structure for tb_user_register_info
-- ----------------------------
DROP TABLE IF EXISTS `tb_user_register_info`;
CREATE TABLE `tb_user_register_info` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `clientType` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `osVersion` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `appVersion` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `deviceName` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `appMarket` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '应用市场',
  `deviceId` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '设备码',
  `did` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '数盟设备ID',
  `date` date DEFAULT NULL COMMENT '日期',
  `headers` text CHARACTER SET utf8 COLLATE utf8_general_ci,
  `apps_flyer_uid` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT 'appsflyer的uid',
  `media_source` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT 'appsflyer的渠道号',
  `af_status` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '是自然量还是非自然量 Organic, Non-organic',
  `created_at` int(11) unsigned DEFAULT '0',
  `updated_at` int(11) unsigned DEFAULT NULL,
  `campaign` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '子渠道',
  PRIMARY KEY (`id`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_appMarket` (`appMarket`),
  KEY `user_id` (`user_id`),
  KEY `did` (`did`)
) ENGINE=InnoDB AUTO_INCREMENT=646 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='用户注册信息表';

-- ----------------------------
-- Table structure for tb_user_repayment_log
-- ----------------------------
DROP TABLE IF EXISTS `tb_user_repayment_log`;
CREATE TABLE `tb_user_repayment_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL,
  `merchant_id` int(11) NOT NULL DEFAULT '0',
  `order_id` int(11) unsigned NOT NULL,
  `amount` bigint(20) DEFAULT NULL COMMENT '本次还款金额',
  `principal` int(11) DEFAULT NULL COMMENT '本次还款-本金',
  `interests` int(11) DEFAULT NULL COMMENT '本次还款-利息',
  `overdue_fee` int(11) DEFAULT NULL COMMENT '本次还款-滞纳金',
  `is_delay_repayment` tinyint(4) DEFAULT '0' COMMENT '延期还款 0:否 1:是',
  `type` tinyint(4) unsigned NOT NULL,
  `success_time` int(11) unsigned DEFAULT NULL,
  `created_at` int(11) unsigned DEFAULT NULL,
  `updated_at` int(11) unsigned DEFAULT NULL,
  `collector_id` int(10) unsigned DEFAULT '0' COMMENT '催收员id',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户还款日志表';

-- ----------------------------
-- Table structure for tb_user_repayment_reduced_log
-- ----------------------------
DROP TABLE IF EXISTS `tb_user_repayment_reduced_log`;
CREATE TABLE `tb_user_repayment_reduced_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `repayment_id` int(11) unsigned DEFAULT '0' COMMENT '还款订单ID',
  `order_id` int(11) unsigned DEFAULT '0' COMMENT '订单id',
  `user_id` int(11) unsigned DEFAULT '0' COMMENT '用户ID',
  `merchant_id` int(11) NOT NULL DEFAULT '0',
  `reduction_money` int(11) unsigned DEFAULT '0' COMMENT '减免滞纳金金额',
  `from` tinyint(4) unsigned DEFAULT '1' COMMENT '1来于后台，2来于催收',
  `operator_id` int(11) DEFAULT '0' COMMENT '操作人id',
  `operator_name` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '操作人',
  `remark` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '' COMMENT '备注',
  `created_at` int(11) DEFAULT '0' COMMENT '创建时间',
  `updated_at` int(11) DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `repayment_id` (`repayment_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for tb_user_signature
-- ----------------------------
DROP TABLE IF EXISTS `tb_user_signature`;
CREATE TABLE `tb_user_signature` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned DEFAULT NULL,
  `document_id` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `content` text CHARACTER SET utf8 COLLATE utf8_general_ci,
  `status` tinyint(4) DEFAULT NULL,
  `created_at` int(11) unsigned DEFAULT NULL,
  `updated_at` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for tb_user_transfer_log_external
-- ----------------------------
DROP TABLE IF EXISTS `tb_user_transfer_log_external`;
CREATE TABLE `tb_user_transfer_log_external` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_uuid` varchar(100) DEFAULT NULL,
  `order_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `amount` float(11,2) DEFAULT NULL,
  `utr` varchar(128) DEFAULT NULL,
  `remark` varchar(255) DEFAULT NULL,
  `pic` json DEFAULT NULL,
  `account_number` varchar(128) DEFAULT NULL,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_user_verification
-- ----------------------------
DROP TABLE IF EXISTS `tb_user_verification`;
CREATE TABLE `tb_user_verification` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL COMMENT '用户ID',
  `real_verify_status` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '是否进行了人脸活体认证',
  `real_fr_compare_pan_status` tinyint(4) unsigned DEFAULT '0' COMMENT '是否进行了人脸对比PAN认证',
  `real_fr_compare_fr_status` tinyint(4) unsigned DEFAULT '0' COMMENT '是否进行了人脸对比人脸认证',
  `real_basic_status` tinyint(4) unsigned DEFAULT '0' COMMENT '是否进行了基础信息填写',
  `real_work_status` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '是否进行了工作信息认证',
  `ocr_aadhaar_status` tinyint(4) unsigned DEFAULT '0' COMMENT '是否进行了OCR-AADHAAR',
  `ocr_pan_status` tinyint(4) unsigned DEFAULT '0' COMMENT '是否进行了OCR-PAN',
  `real_pan_status` tinyint(4) unsigned DEFAULT '0' COMMENT '是否进行了PAN验真',
  `real_contact_status` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '是否进行了联系人认证',
  `real_language_status` tinyint(4) unsigned DEFAULT '0' COMMENT '是否进行了语言认证',
  `is_first_loan` tinyint(4) unsigned NOT NULL DEFAULT '1' COMMENT '是否是首次借款，1：是，0：否',
  `status` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '状态，默认为0，备用',
  `updated_at` int(11) unsigned NOT NULL DEFAULT '0',
  `created_at` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=71 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='认证表';

-- ----------------------------
-- Table structure for tb_user_verification_log
-- ----------------------------
DROP TABLE IF EXISTS `tb_user_verification_log`;
CREATE TABLE `tb_user_verification_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `user_id` int(11) unsigned DEFAULT '0' COMMENT '用户id',
  `type` tinyint(4) unsigned DEFAULT '0' COMMENT '认证类型',
  `status` tinyint(4) DEFAULT '0' COMMENT '认证结果状态',
  `created_at` int(11) unsigned DEFAULT NULL COMMENT '创建时间',
  `updated_at` int(11) unsigned DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=191 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_user_work_info
-- ----------------------------
DROP TABLE IF EXISTS `tb_user_work_info`;
CREATE TABLE `tb_user_work_info` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL,
  `merchant_id` int(11) NOT NULL DEFAULT '0',
  `educated_school` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '校名',
  `educated` tinyint(4) unsigned NOT NULL COMMENT '教育程度',
  `residential_pincode` varchar(16) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `residential_address1` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '居住区域联邦',
  `residential_address2` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '居住区域城市',
  `residential_address_code1` tinyint(4) unsigned DEFAULT NULL COMMENT '居住区域编号联邦',
  `residential_address_code2` tinyint(4) unsigned DEFAULT NULL COMMENT '居住区域编号城市',
  `residential_detail_address` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '居住详细地址',
  `industry` tinyint(4) unsigned NOT NULL COMMENT '行业',
  `company_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '公司名',
  `company_phone` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '公司电话',
  `company_address1` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '公司区域联邦',
  `company_address2` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '公司区域城市',
  `company_address_code1` tinyint(4) unsigned DEFAULT NULL COMMENT '公司区域编号联邦',
  `company_address_code2` tinyint(4) unsigned DEFAULT NULL COMMENT '公司区域编号城市',
  `company_detail_address` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '公司详细地址',
  `work_position` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '职位',
  `company_seniority` tinyint(4) unsigned DEFAULT NULL COMMENT '当前公司工龄',
  `working_seniority` tinyint(4) unsigned DEFAULT NULL COMMENT '工龄',
  `monthly_salary` int(11) unsigned DEFAULT NULL COMMENT '月薪',
  `certificate_of_company_docs` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '公司证明照片',
  `client_info` varchar(1024) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `created_at` int(11) unsigned DEFAULT NULL,
  `updated_at` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `residential_address_sindex` (`residential_address_code1`,`residential_address_code2`)
) ENGINE=InnoDB AUTO_INCREMENT=69 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for tb_user_work_order_accept_log
-- ----------------------------
DROP TABLE IF EXISTS `tb_user_work_order_accept_log`;
CREATE TABLE `tb_user_work_order_accept_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` tinyint(4) DEFAULT NULL COMMENT '工单类型',
  `apply_id` int(11) DEFAULT NULL COMMENT '工单id',
  `accept_user_id` int(11) DEFAULT NULL COMMENT '受理人id',
  `remark` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '备注',
  `result` int(11) DEFAULT NULL COMMENT '受理结果',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `apply_id` (`apply_id`),
  KEY `accept_user_id` (`accept_user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='工单受理日志';

-- ----------------------------
-- Table structure for tb_validation_rule
-- ----------------------------
DROP TABLE IF EXISTS `tb_validation_rule`;
CREATE TABLE `tb_validation_rule` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `validation_type` tinyint(4) unsigned DEFAULT NULL COMMENT '认证类型',
  `service_error` int(11) unsigned DEFAULT NULL COMMENT '服务单位时间发生错误数量',
  `service_time` int(11) unsigned DEFAULT NULL COMMENT '服务单位时间时长，分钟',
  `service_current` tinyint(4) DEFAULT NULL COMMENT '当前服务',
  `service_switch` tinyint(4) DEFAULT NULL COMMENT '替换服务',
  `is_used` tinyint(4) DEFAULT NULL COMMENT '是否启用',
  `created_at` int(11) unsigned DEFAULT NULL,
  `updated_at` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tb_validation_rule_service_current_index` (`service_current`),
  KEY `tb_validation_rule_validation_type_index` (`validation_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='认证规则路由';

SET FOREIGN_KEY_CHECKS = 1;

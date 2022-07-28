/*
 Navicat Premium Data Transfer

 Source Server         : 开发环境
 Source Server Type    : MySQL
 Source Server Version : 80016
 Source Host           : rm-uf677fth6or7v6i85.mysql.rds.aliyuncs.com:3306
 Source Schema         : saas_stats

 Target Server Type    : MySQL
 Target Server Version : 80016
 File Encoding         : 65001

 Date: 07/07/2021 12:56:29
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for tb_daily_credit_audit_data
-- ----------------------------
DROP TABLE IF EXISTS `tb_daily_credit_audit_data`;
CREATE TABLE `tb_daily_credit_audit_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `date` date NOT NULL COMMENT '日期',
  `merchant_id` tinyint(4) NOT NULL COMMENT '商户ID',
  `operator_id` int(11) unsigned NOT NULL COMMENT '操作人id',
  `action` tinyint(4) unsigned DEFAULT '1' COMMENT '1:信审；2：审核绑卡',
  `audit_count` int(11) unsigned DEFAULT '0' COMMENT '审批件数',
  `pass_count` int(11) unsigned DEFAULT '0' COMMENT '通过件数',
  `loan_success_count` int(11) unsigned DEFAULT '0' COMMENT '放款成功数',
  `first_overdue_count` int(11) unsigned DEFAULT '0' COMMENT '首逾数',
  `created_at` int(11) DEFAULT NULL COMMENT '创建时间',
  `updated_at` int(11) DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=67 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_daily_global_user_full_data
-- ----------------------------
DROP TABLE IF EXISTS `tb_daily_global_user_full_data`;
CREATE TABLE `tb_daily_global_user_full_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL COMMENT '日期',
  `merchant_id` tinyint(4) NOT NULL COMMENT '商户',
  `package_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '包名',
  `user_type` tinyint(4) NOT NULL COMMENT '全平台新老户类型',
  `order_num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '下单数',
  `order_amount` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '总金额',
  `order_user_num` int(11) NOT NULL COMMENT '下单人数',
  `audit_pass_order_num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '审核通过订单数',
  `audit_pass_order_amount` bigint(20) unsigned NOT NULL COMMENT '审核通过订单金额',
  `audit_pass_order_user_num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '新客审核通过订单人数',
  `bind_card_pass_order_num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '绑卡通过订单数',
  `bind_card_pass_order_amount` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '绑卡通过订单金额',
  `bind_card_pass_order_user_num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '绑卡通过订单人数',
  `withdraw_success_order_num` int(11) NOT NULL COMMENT '提现订单数',
  `withdraw_success_order_amount` bigint(20) NOT NULL COMMENT '提现订单金额',
  `withdraw_success_order_user_num` int(11) NOT NULL COMMENT '提现人数',
  `loan_success_order_num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '放款单数',
  `loan_success_order_amount` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '放款金额',
  `loan_success_order_user_num` int(11) NOT NULL COMMENT '放款人数',
  `created_at` int(11) NOT NULL COMMENT '创建时间',
  `updated_at` int(11) NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `dsa` (`date`,`merchant_id`) USING BTREE,
  KEY `date` (`date`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=511 DEFAULT CHARSET=utf8 COMMENT='渠道数据日报';

-- ----------------------------
-- Table structure for tb_daily_register_conver
-- ----------------------------
DROP TABLE IF EXISTS `tb_daily_register_conver`;
CREATE TABLE `tb_daily_register_conver` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` date DEFAULT NULL,
  `type` tinyint(4) DEFAULT NULL,
  `source_id` tinyint(4) DEFAULT NULL,
  `app_market` varchar(50) DEFAULT '' COMMENT 'appMarket',
  `media_source` varchar(100) DEFAULT '' COMMENT 'media_source',
  `reg_num` int(11) unsigned DEFAULT '0',
  `basic_num` int(11) unsigned DEFAULT '0',
  `kyc_num` int(11) unsigned DEFAULT '0',
  `address_num` int(11) unsigned DEFAULT '0',
  `contact_num` int(11) unsigned DEFAULT '0',
  `apply_num` int(11) unsigned DEFAULT '0',
  `audit_pass_num` int(11) unsigned DEFAULT '0',
  `withdraw_num` int(11) unsigned DEFAULT '0',
  `loan_num` int(11) unsigned DEFAULT '0',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `date` (`date`,`type`,`source_id`,`app_market`,`media_source`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_daily_repayment_data
-- ----------------------------
DROP TABLE IF EXISTS `tb_daily_repayment_data`;
CREATE TABLE `tb_daily_repayment_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` date DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '还款状态',
  `num` int(11) DEFAULT '0' COMMENT '到期笔数',
  `total_money` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '需要总还款金额',
  `true_total_money` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '已还总金额',
  `total_principal` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '还款总本金',
  `total_interests` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '总利息',
  `total_cost_fee` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '总服务费',
  `total_overdue_fee` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '总滞纳金',
  `created_at` int(11) DEFAULT '0' COMMENT '创建时间',
  `updated_at` int(11) DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `ds` (`date`,`status`) USING BTREE,
  KEY `date` (`date`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='每日还款单数/金额统计表V1';

-- ----------------------------
-- Table structure for tb_daily_repayment_grand_total
-- ----------------------------
DROP TABLE IF EXISTS `tb_daily_repayment_grand_total`;
CREATE TABLE `tb_daily_repayment_grand_total` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` date DEFAULT NULL,
  `merchant_id` tinyint(4) DEFAULT NULL,
  `package_name` varchar(50) DEFAULT NULL,
  `overdue_day` int(10) DEFAULT NULL,
  `all_repay_amount` bigint(20) unsigned DEFAULT '0' COMMENT '总还款金额',
  `all_repay_order_num` int(11) DEFAULT NULL COMMENT '总有还款的订单数',
  `delay_repay_amount` bigint(20) unsigned DEFAULT '0' COMMENT '延期还款金额',
  `delay_repay_order_num` int(11) DEFAULT NULL COMMENT '延期还款的订单数',
  `extend_amount` bigint(20) DEFAULT '0' COMMENT '展期金额',
  `extend_order_num` int(11) DEFAULT '0' COMMENT '展期订单数',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `date` (`date`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_daily_risk_reject_data
-- ----------------------------
DROP TABLE IF EXISTS `tb_daily_risk_reject_data`;
CREATE TABLE `tb_daily_risk_reject_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `date` date NOT NULL COMMENT '日期',
  `app_market` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '渠道包',
  `tree_code` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '' COMMENT '节点',
  `txt` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '' COMMENT '被拒原因',
  `reject_count` int(11) unsigned DEFAULT '0' COMMENT '被拒次数',
  `created_at` int(11) DEFAULT NULL COMMENT '创建时间',
  `updated_at` int(11) DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_daily_trade_data
-- ----------------------------
DROP TABLE IF EXISTS `tb_daily_trade_data`;
CREATE TABLE `tb_daily_trade_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `date` date DEFAULT NULL COMMENT '统计的日期',
  `merchant_id` tinyint(4) NOT NULL COMMENT '商户ID',
  `package_name` varchar(50) NOT NULL COMMENT '包',
  `app_market` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '渠道',
  `hour` int(11) NOT NULL COMMENT '小时',
  `user_type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '用户类型；0：所有用户，1:新用户，2：老用户',
  `reg_num` int(11) NOT NULL DEFAULT '0' COMMENT '注册数',
  `apply_num` int(11) NOT NULL DEFAULT '0' COMMENT '申请单数',
  `apply_money` int(11) NOT NULL DEFAULT '0' COMMENT '申请金额',
  `apply_check_num` int(11) NOT NULL DEFAULT '0' COMMENT '风控通过人数',
  `manual_order_num` int(11) NOT NULL DEFAULT '0' COMMENT '进入人审的订单数',
  `manual_num` int(11) NOT NULL DEFAULT '0' COMMENT '人审的订单数',
  `loan_num` int(11) NOT NULL DEFAULT '0' COMMENT '放款单数',
  `loan_money` int(11) NOT NULL DEFAULT '0' COMMENT '放款金额',
  `loan_money_today` int(11) NOT NULL DEFAULT '0' COMMENT '放款金额(当天申请)',
  `repayment_num` int(11) NOT NULL DEFAULT '0' COMMENT '还款单数',
  `repayment_money` int(11) NOT NULL DEFAULT '0' COMMENT '还款金额',
  `repays_money` int(11) NOT NULL DEFAULT '0' COMMENT '到期金额',
  `fee` int(11) NOT NULL DEFAULT '0' COMMENT '服务费',
  `interests` int(11) NOT NULL DEFAULT '0' COMMENT '利息',
  `repays_money_tomorrow` int(11) NOT NULL DEFAULT '0' COMMENT '次日到期金额',
  `fee_tomorrow` int(11) NOT NULL DEFAULT '0' COMMENT '次日服务费',
  `interests_tomorrow` int(11) NOT NULL DEFAULT '0' COMMENT '次日利息',
  `created_at` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated_at` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `active_repayment_tomorrow` int(10) DEFAULT '0' COMMENT '下一天主动还款人数',
  `repayment_money_tomorrow` int(10) DEFAULT '0' COMMENT '下一天的还款金额',
  `repayment_num_tomorrow` int(10) DEFAULT '0' COMMENT '下一天的还款单数',
  `active_repayment` int(11) DEFAULT '0' COMMENT '主动还款单数',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `date` (`date`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8 COMMENT='每日借还款数据对比V2';

-- ----------------------------
-- Table structure for tb_daily_user_data
-- ----------------------------
DROP TABLE IF EXISTS `tb_daily_user_data`;
CREATE TABLE `tb_daily_user_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL COMMENT '日期',
  `merchant_id` tinyint(4) NOT NULL COMMENT '商户',
  `app_market` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '渠道',
  `reg_num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '注册人数',
  `identity_num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '身份认证数',
  `basic_num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '基本信息数',
  `work_num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '工作认证数',
  `contact_num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '联系人认证数',
  `tax_bill_num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '税单认证书',
  `credit_report_num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '征信报告认证数',
  `order_num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '下单数',
  `order_amount` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '总金额',
  `audit_pass_order_num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '审核通过订单的总数',
  `audit_pass_order_amount` bigint(20) unsigned NOT NULL COMMENT '审核通过订单的总金额',
  `bind_card_pass_order_num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '绑卡通过订单总数',
  `bind_card_pass_order_amount` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '绑卡通过订单总金额',
  `loan_success_order_num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '放款单数',
  `loan_success_order_amount` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '总放款金额',
  `created_at` int(11) NOT NULL COMMENT '创建时间',
  `updated_at` int(11) NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `dsa` (`date`,`app_market`) USING BTREE,
  KEY `date` (`date`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=95 DEFAULT CHARSET=utf8 COMMENT='渠道数据日报';

-- ----------------------------
-- Table structure for tb_daily_user_full_data
-- ----------------------------
DROP TABLE IF EXISTS `tb_daily_user_full_data`;
CREATE TABLE `tb_daily_user_full_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL COMMENT '日期',
  `merchant_id` tinyint(4) NOT NULL COMMENT '商户',
  `app_market` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '渠道',
  `media_source` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT 'mediaSource',
  `package_name` varchar(100) NOT NULL DEFAULT '' COMMENT 'packageName',
  `reg_num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '注册人数',
  `basic_num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '基本信息数',
  `identity_num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '身份认证数',
  `contact_num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '联系人认证数',
  `order_num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '下单数',
  `order_amount` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '总金额',
  `new_order_num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '新客下单数',
  `platform_new_order_num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '全平台新客下单数',
  `new_order_user_num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '新客下单人数',
  `platform_new_order_user_num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '全平台新客下单人数',
  `new_order_amount` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '新客总金额',
  `platform_new_order_amount` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '全平台新客总金额',
  `old_order_num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '老客下单数',
  `platform_old_order_num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '全平台老客下单数',
  `old_order_amount` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '老客总金额',
  `platform_old_order_amount` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '全平台老客总金额',
  `audit_pass_order_num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '审核通过订单的总数',
  `audit_pass_order_user_num` int(11) NOT NULL COMMENT '审核通过人数',
  `audit_pass_order_amount` bigint(20) unsigned NOT NULL COMMENT '审核通过订单的总金额',
  `new_audit_pass_order_num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '新客审核通过订单的总数',
  `platform_new_audit_pass_order_num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '全平台新客审核通过订单的总数',
  `new_audit_pass_order_user_num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '新客审核通过订单的人数',
  `platform_new_audit_pass_order_user_num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '全平台新客审核通过订单的人数',
  `new_audit_pass_order_amount` bigint(20) unsigned NOT NULL COMMENT '新客审核通过订单的总金额',
  `platform_new_audit_pass_order_amount` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '全平台新客审核通过订单的总金额',
  `old_audit_pass_order_num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '老客审核通过订单的总数',
  `platform_old_audit_pass_order_num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '全平台老客审核通过订单的总数',
  `old_audit_pass_order_amount` bigint(20) unsigned NOT NULL COMMENT '老客审核通过订单的总金额',
  `platform_old_audit_pass_order_amount` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '全平台老客审核通过订单的总金额',
  `bind_card_pass_order_num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '绑卡通过订单总数',
  `bind_card_pass_order_amount` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '绑卡通过订单总金额',
  `new_bind_card_pass_order_num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '新客绑卡通过订单总数',
  `platform_new_bind_card_pass_order_num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '全平台新客绑卡通过订单总数',
  `new_bind_card_pass_order_user_num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '新客绑卡通过订单人数',
  `platform_new_bind_card_pass_order_user_num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '全平台新客绑卡通过订单人数',
  `new_bind_card_pass_order_amount` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '新客绑卡通过订单总金额',
  `platform_new_bind_card_pass_order_amount` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '全平台新客绑卡通过订单总金额',
  `old_bind_card_pass_order_num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '老客绑卡通过订单总数',
  `platform_old_bind_card_pass_order_num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '全平台老客绑卡通过订单总数',
  `old_bind_card_pass_order_amount` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '老客绑卡通过订单总金额',
  `platform_old_bind_card_pass_order_amount` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '全平台老客绑卡通过订单总金额',
  `withdraw_success_order_num` int(11) NOT NULL COMMENT '提现订单数',
  `withdraw_success_order_user_num` int(11) NOT NULL COMMENT '提现人数',
  `withdraw_success_order_amount` bigint(20) NOT NULL COMMENT '提现订单总金额',
  `new_withdraw_success_order_num` int(11) NOT NULL COMMENT '新客提现订单数',
  `platform_new_withdraw_success_order_num` int(11) NOT NULL COMMENT '全平台新客提现订单数',
  `new_withdraw_success_order_user_num` int(11) NOT NULL COMMENT '新客提现人数',
  `platform_new_withdraw_success_order_user_num` int(11) NOT NULL COMMENT '全平台新客提现人数',
  `new_withdraw_success_order_amount` bigint(20) NOT NULL COMMENT '新客提现订单金额',
  `platform_new_withdraw_success_order_amount` bigint(20) NOT NULL COMMENT '全平台新客提现订单金额',
  `old_withdraw_success_order_num` int(11) NOT NULL COMMENT '老客提现订单数',
  `platform_old_withdraw_success_order_num` int(11) NOT NULL COMMENT '全平台老客提现订单数',
  `old_withdraw_success_order_user_num` int(11) NOT NULL COMMENT '老客提现人数',
  `platform_old_withdraw_success_order_user_num` int(11) NOT NULL COMMENT '全平台老客提现人数',
  `old_withdraw_success_order_amount` bigint(20) NOT NULL COMMENT '老客提现订单金额',
  `platform_old_withdraw_success_order_amount` bigint(20) NOT NULL COMMENT '全平台老客提现订单金额',
  `loan_success_order_num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '放款单数',
  `new_loan_success_order_num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '新客放款单数',
  `platform_new_loan_success_order_num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '全平台新客放款单数',
  `new_loan_success_order_amount` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '新客总放款金额',
  `platform_new_loan_success_order_amount` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '全平台新客总放款金额',
  `old_loan_success_order_num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '老客放款单数',
  `platform_old_loan_success_order_num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '全平台老客放款单数',
  `old_loan_success_order_amount` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '老客总放款金额',
  `platform_old_loan_success_order_amount` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '全平台老客总放款金额',
  `loan_success_order_amount` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '总放款金额',
  `created_at` int(11) NOT NULL COMMENT '创建时间',
  `updated_at` int(11) NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `dsa` (`date`,`merchant_id`,`app_market`,`media_source`,`package_name`) USING BTREE,
  KEY `date` (`date`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=526 DEFAULT CHARSET=utf8 COMMENT='渠道数据日报';

-- ----------------------------
-- Table structure for tb_re_apply_data
-- ----------------------------
DROP TABLE IF EXISTS `tb_re_apply_data`;
CREATE TABLE `tb_re_apply_data` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `merchant_id` tinyint(4) NOT NULL COMMENT '商户',
  `date` date DEFAULT NULL,
  `repay_num` int(11) NOT NULL COMMENT '还款人数',
  `borrow_apply_num` int(11) DEFAULT '0' COMMENT '复借人数',
  `borrow_apply1_num` int(11) DEFAULT '0' COMMENT '当天复借人数',
  `borrow_apply7_num` int(11) DEFAULT '0' COMMENT '七天内复借人数',
  `borrow_apply14_num` int(11) DEFAULT '0' COMMENT '14天内复借人数',
  `borrow_apply30_num` int(11) DEFAULT '0' COMMENT '30天内复借人数',
  `borrow_apply31_num` int(11) DEFAULT '0' COMMENT '30天以上复借人数',
  `borrow_succ_num` int(11) DEFAULT '0' COMMENT '复借成功人数',
  `borrow_succ1_num` int(11) DEFAULT '0' COMMENT '当天复借成功人数',
  `borrow_succ7_num` int(11) DEFAULT '0' COMMENT '七天内复借成功人数',
  `borrow_succ14_num` int(11) DEFAULT '0' COMMENT '14天内复借成功人数',
  `borrow_succ30_num` int(11) DEFAULT '0' COMMENT '30天内复借成功人数',
  `borrow_succ31_num` int(11) DEFAULT '0' COMMENT '30天以上复借成功人数',
  `created_at` int(11) DEFAULT NULL COMMENT '创建时间',
  `updated_at` int(11) DEFAULT NULL COMMENT '修改时间',
  `borrow_succ_money` int(11) DEFAULT '0' COMMENT '复借成功金额',
  `borrow_succ1_money` int(11) DEFAULT '0' COMMENT '当日复借成功金额',
  `borrow_succ7_money` int(11) DEFAULT '0' COMMENT '7日内复借成功金额',
  `borrow_succ14_money` int(11) DEFAULT '0' COMMENT '10日内复借成功金额',
  `borrow_succ30_money` int(11) DEFAULT '0' COMMENT '30日内复借成功金额',
  `borrow_succ31_money` int(11) DEFAULT '0' COMMENT '31日以上复借成功金额',
  `app_market` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '' COMMENT 'appMarket',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `date` (`date`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=417 DEFAULT CHARSET=utf8 COMMENT='还款复借数据';

-- ----------------------------
-- Table structure for tb_remind_day_data
-- ----------------------------
DROP TABLE IF EXISTS `tb_remind_day_data`;
CREATE TABLE `tb_remind_day_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` date DEFAULT NULL,
  `merchant_id` tinyint(4) unsigned DEFAULT NULL,
  `admin_user_id` int(11) DEFAULT NULL,
  `remind_group` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `today_dispatch_num` int(10) unsigned NOT NULL DEFAULT '0',
  `today_dispatch_remind_num` int(10) unsigned NOT NULL DEFAULT '0',
  `today_repay_num` int(10) unsigned NOT NULL DEFAULT '0',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `date` (`date`),
  KEY `admin_user_id` (`admin_user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_remind_reach_repay
-- ----------------------------
DROP TABLE IF EXISTS `tb_remind_reach_repay`;
CREATE TABLE `tb_remind_reach_repay` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` date DEFAULT NULL,
  `merchant_id` tinyint(4) unsigned DEFAULT NULL,
  `user_type` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `remind_num` int(10) unsigned NOT NULL DEFAULT '0',
  `reach_num` int(10) unsigned NOT NULL DEFAULT '0',
  `repay_num` int(10) unsigned NOT NULL DEFAULT '0',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_statistics_day_data
-- ----------------------------
DROP TABLE IF EXISTS `tb_statistics_day_data`;
CREATE TABLE `tb_statistics_day_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` date DEFAULT NULL,
  `merchant_id` tinyint(4) NOT NULL COMMENT '商户',
  `app_market` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '',
  `media_source` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '',
  `package_name` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '',
  `user_type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '用户类型；0：所有用户，1:新用户，2：老用户',
  `expire_num` int(11) DEFAULT '0' COMMENT '到期笔数',
  `expire_money` bigint(20) DEFAULT '0' COMMENT '到期金额',
  `repay_num` int(11) DEFAULT '0' COMMENT '还款笔数',
  `repay_money` bigint(20) DEFAULT '0' COMMENT '还款金额',
  `repay_zc_num` int(11) DEFAULT '0' COMMENT '正常还款笔数',
  `repay_zc_money` bigint(20) DEFAULT '0' COMMENT '正常还款金额',
  `extend_num` int(11) DEFAULT '0' COMMENT '展期单数',
  `extend_money` bigint(20) DEFAULT '0' COMMENT '展期金额',
  `created_at` int(11) DEFAULT '0' COMMENT '创建时间',
  `updated_at` int(11) DEFAULT '0' COMMENT '更新时间',
  `fund_id` int(11) unsigned DEFAULT '0',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `date` (`date`,`merchant_id`,`app_market`,`media_source`,`package_name`,`fund_id`,`user_type`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8 COMMENT='每日还款统计V2';

-- ----------------------------
-- Table structure for tb_statistics_loan2_full_platform
-- ----------------------------
DROP TABLE IF EXISTS `tb_statistics_loan2_full_platform`;
CREATE TABLE `tb_statistics_loan2_full_platform` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `merchant_id` tinyint(4) NOT NULL COMMENT '商户ID',
  `app_market` varchar(100) NOT NULL COMMENT 'app market',
  `media_source` varchar(100) NOT NULL COMMENT 'media source',
  `package_name` varchar(100) NOT NULL COMMENT 'package_name',
  `date_time` int(11) DEFAULT '0' COMMENT '日期',
  `loan_num` int(11) DEFAULT '0' COMMENT '放款单数',
  `loan_num_old` int(11) DEFAULT '0' COMMENT '老用户放款单数',
  `loan_num_new` int(11) DEFAULT '0' COMMENT '新用户放款单数',
  `loan_money` bigint(11) unsigned DEFAULT '0' COMMENT '放款金额',
  `loan_money_old` bigint(11) unsigned DEFAULT '0' COMMENT '老用户放款金额',
  `loan_money_new` bigint(11) DEFAULT '0' COMMENT '新用户放款金额',
  `created_at` int(11) DEFAULT '0',
  `fund_id` int(11) DEFAULT '0' COMMENT '放款资方',
  `loan_term` int(11) unsigned DEFAULT NULL COMMENT '期限',
  `updated_at` int(11) NOT NULL DEFAULT '0' COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='每日放款数据统计表';

-- ----------------------------
-- Table structure for tb_statistics_loan2_user_structure
-- ----------------------------
DROP TABLE IF EXISTS `tb_statistics_loan2_user_structure`;
CREATE TABLE `tb_statistics_loan2_user_structure` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `merchant_id` tinyint(4) NOT NULL COMMENT '商户ID',
  `app_market` varchar(100) NOT NULL COMMENT 'app market',
  `media_source` varchar(100) NOT NULL COMMENT 'media source',
  `package_name` varchar(100) NOT NULL COMMENT 'package_name',
  `date_time` int(11) DEFAULT '0' COMMENT '日期',
  `loan_num` int(11) DEFAULT '0' COMMENT '放款单数',
  `loan_num_old` int(11) DEFAULT '0' COMMENT '老用户放款单数',
  `loan_num_new` int(11) DEFAULT '0' COMMENT '新用户放款单数',
  `loan_num_all_old_loan_new` int(11) DEFAULT '0' COMMENT '全老本新用户放款单数',
  `loan_money` bigint(11) unsigned DEFAULT '0' COMMENT '放款金额',
  `loan_money_old` bigint(11) unsigned DEFAULT '0' COMMENT '老用户放款金额',
  `loan_money_new` bigint(11) DEFAULT '0' COMMENT '新用户放款金额',
  `loan_money_all_old_loan_new` bigint(11) DEFAULT '0' COMMENT '全老本新用户放款金额',
  `created_at` int(11) DEFAULT '0',
  `fund_id` int(11) DEFAULT '0' COMMENT '放款资方',
  `loan_term` int(11) unsigned DEFAULT NULL COMMENT '期限',
  `updated_at` int(11) NOT NULL DEFAULT '0' COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COMMENT='每日放款数据统计表';

-- ----------------------------
-- Table structure for tb_statistics_loan_copy
-- ----------------------------
DROP TABLE IF EXISTS `tb_statistics_loan_copy`;
CREATE TABLE `tb_statistics_loan_copy` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `merchant_id` tinyint(4) NOT NULL COMMENT '商户ID',
  `app_market` varchar(100) NOT NULL COMMENT 'app market',
  `media_source` varchar(100) NOT NULL COMMENT 'media source',
  `package_name` varchar(100) NOT NULL COMMENT 'package_name',
  `date_time` int(11) DEFAULT '0' COMMENT '日期',
  `loan_num` int(11) DEFAULT '0' COMMENT '放款单数',
  `loan_num_old` int(11) DEFAULT '0' COMMENT '老用户放款单数',
  `loan_num_new` int(11) DEFAULT '0' COMMENT '新用户放款单数',
  `loan_money` bigint(11) unsigned DEFAULT '0' COMMENT '放款金额',
  `loan_money_old` bigint(11) unsigned DEFAULT '0' COMMENT '老用户放款金额',
  `loan_money_new` bigint(11) DEFAULT '0' COMMENT '新用户放款金额',
  `created_at` int(11) DEFAULT '0',
  `fund_id` int(11) DEFAULT '0' COMMENT '放款资方',
  `loan_term` int(11) unsigned DEFAULT NULL COMMENT '期限',
  `updated_at` int(11) NOT NULL DEFAULT '0' COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COMMENT='每日放款数据统计表';

-- ----------------------------
-- Table structure for tb_statistics_loan_copy2
-- ----------------------------
DROP TABLE IF EXISTS `tb_statistics_loan_copy2`;
CREATE TABLE `tb_statistics_loan_copy2` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `merchant_id` tinyint(4) NOT NULL COMMENT '商户',
  `app_market` varchar(100) NOT NULL COMMENT 'app market',
  `media_source` varchar(100) NOT NULL COMMENT 'media source',
  `package_name` varchar(100) NOT NULL COMMENT 'package_name',
  `date_time` int(11) DEFAULT '0' COMMENT '日期',
  `loan_num` int(11) DEFAULT '0' COMMENT '放款单数',
  `loan_num_old` int(11) DEFAULT '0' COMMENT '老用户放款单数',
  `loan_num_new` int(11) DEFAULT '0' COMMENT '新用户放款单数',
  `loan_money` bigint(11) unsigned DEFAULT '0' COMMENT '放款金额',
  `loan_money_old` bigint(11) unsigned DEFAULT '0' COMMENT '老用户放款金额',
  `loan_money_new` bigint(11) DEFAULT '0' COMMENT '新用户放款金额',
  `created_at` int(11) DEFAULT '0',
  `fund_id` int(11) DEFAULT '0' COMMENT '放款资方',
  `loan_term` int(11) DEFAULT NULL COMMENT '期限',
  `updated_at` int(11) NOT NULL DEFAULT '0' COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `date_time` (`date_time`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='每日放款数据统计表';

-- ----------------------------
-- Table structure for tb_statistics_loan_full_platform
-- ----------------------------
DROP TABLE IF EXISTS `tb_statistics_loan_full_platform`;
CREATE TABLE `tb_statistics_loan_full_platform` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `merchant_id` tinyint(4) NOT NULL COMMENT '商户ID',
  `app_market` varchar(100) NOT NULL COMMENT 'app market',
  `media_source` varchar(100) NOT NULL COMMENT 'media source',
  `package_name` varchar(100) NOT NULL COMMENT 'package_name',
  `date_time` int(11) DEFAULT '0' COMMENT '日期',
  `loan_num` int(11) DEFAULT '0' COMMENT '放款单数',
  `loan_num_old` int(11) DEFAULT '0' COMMENT '老用户放款单数',
  `loan_num_new` int(11) DEFAULT '0' COMMENT '新用户放款单数',
  `loan_money` bigint(11) unsigned DEFAULT '0' COMMENT '放款金额',
  `loan_money_old` bigint(11) unsigned DEFAULT '0' COMMENT '老用户放款金额',
  `loan_money_new` bigint(11) DEFAULT '0' COMMENT '新用户放款金额',
  `created_at` int(11) DEFAULT '0',
  `fund_id` int(11) DEFAULT '0' COMMENT '放款资方',
  `loan_term` int(11) unsigned DEFAULT NULL COMMENT '期限',
  `updated_at` int(11) NOT NULL DEFAULT '0' COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='每日放款数据统计表';

-- ----------------------------
-- Table structure for tb_statistics_loan_user_structure
-- ----------------------------
DROP TABLE IF EXISTS `tb_statistics_loan_user_structure`;
CREATE TABLE `tb_statistics_loan_user_structure` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `merchant_id` tinyint(4) NOT NULL COMMENT '商户ID',
  `app_market` varchar(100) NOT NULL COMMENT 'app market',
  `media_source` varchar(100) NOT NULL COMMENT 'media source',
  `package_name` varchar(100) NOT NULL COMMENT 'package_name',
  `date_time` int(11) DEFAULT '0' COMMENT '日期',
  `loan_num` int(11) DEFAULT '0' COMMENT '放款单数',
  `loan_num_old` int(11) DEFAULT '0' COMMENT '全老本老用户借款单数',
  `loan_num_new` int(11) DEFAULT '0' COMMENT '全新本新用户借款单数',
  `loan_num_all_old_loan_new` int(11) DEFAULT '0' COMMENT '全老本新用户借款单数',
  `loan_money` bigint(11) unsigned DEFAULT '0' COMMENT '放款金额',
  `loan_money_old` bigint(11) unsigned DEFAULT '0' COMMENT '老用户借款本金',
  `loan_money_new` bigint(11) DEFAULT '0' COMMENT '新用户借款本金',
  `loan_money_all_old_loan_new` bigint(11) DEFAULT '0' COMMENT '全老本新用户借款本金',
  `created_at` int(11) DEFAULT '0',
  `fund_id` int(11) DEFAULT '0' COMMENT '放款资方',
  `loan_term` int(11) unsigned DEFAULT NULL COMMENT '期限',
  `updated_at` int(11) NOT NULL DEFAULT '0' COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='每日放款数据统计表';

-- ----------------------------
-- Table structure for tb_total_repayment_amount_data
-- ----------------------------
DROP TABLE IF EXISTS `tb_total_repayment_amount_data`;
CREATE TABLE `tb_total_repayment_amount_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` date DEFAULT NULL,
  `merchant_id` tinyint(4) DEFAULT NULL COMMENT '商户ID',
  `package_name` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT 'package_name',
  `media_source` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT 'media_source',
  `user_type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '用户类型；0：所有用户，1:新用户，2：老用户',
  `fund_id` int(11) unsigned DEFAULT '0',
  `expire_num` int(11) DEFAULT '0' COMMENT '到期笔数',
  `expire_money` bigint(20) DEFAULT '0' COMMENT '到期金额',
  `repay_num` int(11) DEFAULT '0' COMMENT '有还款记录笔数',
  `repay_money` bigint(20) DEFAULT '0' COMMENT '累计还款金额',
  `delay_num` int(11) DEFAULT '0' COMMENT '有延期的单数',
  `delay_money` bigint(20) DEFAULT '0' COMMENT '延期金额',
  `created_at` int(11) DEFAULT '0' COMMENT '创建时间',
  `updated_at` int(11) DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `dmfpmu` (`date`,`merchant_id`,`fund_id`,`package_name`,`media_source`,`user_type`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=811 DEFAULT CHARSET=utf8 COMMENT='总还款金额数据';

-- ----------------------------
-- Table structure for tb_user_operation_data
-- ----------------------------
DROP TABLE IF EXISTS `tb_user_operation_data`;
CREATE TABLE `tb_user_operation_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` date DEFAULT NULL,
  `app_market` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '',
  `type` int(11) unsigned NOT NULL DEFAULT '0',
  `num` int(11) unsigned NOT NULL DEFAULT '0',
  `created_at` int(11) unsigned DEFAULT NULL,
  `updated_at` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=7249 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_user_structure_export_repayment_data
-- ----------------------------
DROP TABLE IF EXISTS `tb_user_structure_export_repayment_data`;
CREATE TABLE `tb_user_structure_export_repayment_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` date DEFAULT NULL,
  `merchant_id` tinyint(4) NOT NULL COMMENT '商户',
  `package_name` varchar(50) DEFAULT NULL,
  `user_type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '用户类型；0：所有用户',
  `expire_num` int(11) unsigned DEFAULT '0' COMMENT '到期笔数',
  `expire_money` bigint(20) unsigned DEFAULT '0' COMMENT '到期金额',
  `first_over_num` int(11) unsigned DEFAULT '0' COMMENT '首逾笔数',
  `first_over_money` bigint(20) unsigned DEFAULT '0' COMMENT '首逾金额',
  `created_at` int(11) DEFAULT '0' COMMENT '创建时间',
  `updated_at` int(11) DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `date` (`date`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='每日还款统计V2';

-- ----------------------------
-- Table structure for tb_user_structure_order_transform
-- ----------------------------
DROP TABLE IF EXISTS `tb_user_structure_order_transform`;
CREATE TABLE `tb_user_structure_order_transform` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` date DEFAULT NULL,
  `merchant_id` tinyint(4) DEFAULT NULL,
  `package_name` varchar(50) DEFAULT NULL,
  `user_type` tinyint(4) DEFAULT NULL,
  `apply_order_num` int(11) unsigned DEFAULT '0',
  `apply_order_money` bigint(20) unsigned DEFAULT '0',
  `apply_person_num` int(11) unsigned DEFAULT '0',
  `audit_pass_order_num` int(11) unsigned DEFAULT '0',
  `audit_pass_order_money` bigint(20) unsigned DEFAULT '0',
  `audit_pass_person_num` int(11) unsigned DEFAULT '0',
  `withdraw_order_num` int(11) unsigned DEFAULT '0',
  `withdraw_order_money` bigint(20) unsigned DEFAULT '0',
  `withdraw_person_num` int(11) unsigned DEFAULT '0',
  `loan_success_order_num` int(11) unsigned DEFAULT '0',
  `loan_success_order_money` bigint(20) unsigned DEFAULT '0',
  `loan_success_person_num` int(11) unsigned DEFAULT '0',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `date` (`date`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_user_structure_source_export_repayment_data
-- ----------------------------
DROP TABLE IF EXISTS `tb_user_structure_source_export_repayment_data`;
CREATE TABLE `tb_user_structure_source_export_repayment_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` date DEFAULT NULL,
  `merchant_id` tinyint(4) NOT NULL COMMENT '商户',
  `package_name` varchar(50) DEFAULT NULL,
  `user_type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '用户类型；0：所有用户',
  `expire_num` int(11) unsigned DEFAULT '0' COMMENT '到期笔数',
  `expire_money` bigint(20) unsigned DEFAULT '0' COMMENT '到期金额',
  `first_over_num` int(11) unsigned DEFAULT '0' COMMENT '首逾笔数',
  `first_over_money` bigint(20) unsigned DEFAULT '0' COMMENT '首逾金额',
  `created_at` int(11) DEFAULT '0' COMMENT '创建时间',
  `updated_at` int(11) DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `date` (`date`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8 COMMENT='每日还款统计V2';

SET FOREIGN_KEY_CHECKS = 1;

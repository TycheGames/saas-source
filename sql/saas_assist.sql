/*
 Navicat Premium Data Transfer

 Source Server         : 开发环境
 Source Server Type    : MySQL
 Source Server Version : 80016
 Source Host           : rm-uf677fth6or7v6i85.mysql.rds.aliyuncs.com:3306
 Source Schema         : saas_assist

 Target Server Type    : MySQL
 Target Server Version : 80016
 File Encoding         : 65001

 Date: 07/07/2021 12:56:12
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for tb_absence_apply
-- ----------------------------
DROP TABLE IF EXISTS `tb_absence_apply`;
CREATE TABLE `tb_absence_apply` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `collector_id` int(11) NOT NULL COMMENT '催收员id',
  `team_leader_id` int(11) NOT NULL COMMENT '小组长id',
  `status` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '审核状态 0:待审核 1:通过 2:不通过',
  `finish_status` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '最终审核状态 0:待审核 1:通过 2:不通过',
  `type` tinyint(4) DEFAULT '0' COMMENT '缺勤类型',
  `to_person` varchar(255) NOT NULL DEFAULT '' COMMENT '指定催收员',
  `execute_status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '执行状态',
  `created_at` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated_at` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `date` date DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 COMMENT='缺勤申请表';

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
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8;

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
  `type` tinyint(4) unsigned DEFAULT '1' COMMENT '1-web 2-app',
  `app_version` varchar(255) DEFAULT NULL,
  `brand_name` varchar(255) DEFAULT NULL,
  `device_id` varchar(255) DEFAULT NULL,
  `device_name` varchar(255) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=380 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_admin_manager_relation
-- ----------------------------
DROP TABLE IF EXISTS `tb_admin_manager_relation`;
CREATE TABLE `tb_admin_manager_relation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) DEFAULT NULL,
  `outside` int(11) DEFAULT NULL,
  `group` int(11) DEFAULT NULL,
  `group_game` int(11) DEFAULT NULL,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `aogg` (`admin_id`,`outside`,`group`,`group_game`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=65 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_admin_message
-- ----------------------------
DROP TABLE IF EXISTS `tb_admin_message`;
CREATE TABLE `tb_admin_message` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) DEFAULT NULL,
  `content` text,
  `status` tinyint(4) NOT NULL DEFAULT '1',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `admin_id` (`admin_id`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=69 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_admin_message_task
-- ----------------------------
DROP TABLE IF EXISTS `tb_admin_message_task`;
CREATE TABLE `tb_admin_message_task` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `outside` int(10) DEFAULT NULL COMMENT '机构id',
  `group` int(10) DEFAULT NULL COMMENT '账龄组id',
  `task_type` tinyint(4) DEFAULT NULL COMMENT '任务类型',
  `task_value` int(10) DEFAULT NULL COMMENT '配置值',
  `status` tinyint(4) DEFAULT NULL COMMENT '开启状态',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8 COMMENT='消息管理任务配置表';

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
  `mark` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '标记',
  `callcenter` int(2) DEFAULT '0' COMMENT '是否是催收人员，1是，0不是',
  `open_status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否关闭，1：开启，2：关闭',
  `can_dispatch` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否可分派',
  `open_search_label` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否开启订单搜索标签',
  `login_app` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'app登录',
  `outside` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '机构',
  `group` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '团队组',
  `group_game` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '小组分组',
  `merchant_id` tinyint(4) unsigned DEFAULT '0',
  `to_view_merchant_id` varchar(100) DEFAULT NULL,
  `created_at` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated_at` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `real_name` varchar(100) DEFAULT NULL,
  `nx_phone` tinyint(4) DEFAULT '0' COMMENT '0:不可使用pc牛信 1:可使用',
  `job_number` varchar(100) NOT NULL DEFAULT '' COMMENT '工号',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `Index 2` (`callcenter`) USING BTREE,
  KEY `idx_phone` (`phone`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=272 DEFAULT CHARSET=utf8 COMMENT='管理系统用户表';

-- ----------------------------
-- Table structure for tb_admin_user_master_slave_relation
-- ----------------------------
DROP TABLE IF EXISTS `tb_admin_user_master_slave_relation`;
CREATE TABLE `tb_admin_user_master_slave_relation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) DEFAULT NULL,
  `slave_admin_id` int(11) DEFAULT NULL,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `admin_id` (`admin_id`),
  KEY `slave_admin_id` (`slave_admin_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_admin_user_role
-- ----------------------------
DROP TABLE IF EXISTS `tb_admin_user_role`;
CREATE TABLE `tb_admin_user_role` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '标识，英文',
  `title` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '名称',
  `desc` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '描述',
  `permissions` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '权限集合',
  `created_user` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '创建人',
  `created_at` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated_at` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `groups` int(10) NOT NULL DEFAULT '0' COMMENT '分组id',
  `open_status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否关闭，1：开启，2：关闭',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COMMENT='管理员角色';

-- ----------------------------
-- Table structure for tb_app_reduction_platform_apply
-- ----------------------------
DROP TABLE IF EXISTS `tb_app_reduction_platform_apply`;
CREATE TABLE `tb_app_reduction_platform_apply` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `collection_order_id` int(11) DEFAULT NULL,
  `loan_order_id` int(11) DEFAULT NULL,
  `repayment_id` int(11) DEFAULT NULL,
  `apply_admin_user_id` int(11) DEFAULT NULL,
  `audit_operator_id` int(11) DEFAULT NULL,
  `audit_status` tinyint(4) DEFAULT NULL,
  `audit_time` int(11) DEFAULT NULL,
  `audit_result` tinyint(4) DEFAULT NULL,
  `audit_remark` varchar(255) DEFAULT NULL,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `collection_order_id` (`collection_order_id`),
  KEY `loan_order_id` (`loan_order_id`),
  KEY `repayment_id` (`repayment_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='申请app可主动减免表';

-- ----------------------------
-- Table structure for tb_app_screen_shot
-- ----------------------------
DROP TABLE IF EXISTS `tb_app_screen_shot`;
CREATE TABLE `tb_app_screen_shot` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_collection_admin_operate_log
-- ----------------------------
DROP TABLE IF EXISTS `tb_collection_admin_operate_log`;
CREATE TABLE `tb_collection_admin_operate_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `admin_user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `admin_user_name` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0' COMMENT '用户名',
  `route` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0' COMMENT '请求路由',
  `request` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0' COMMENT '请求类型',
  `request_params` varchar(20000) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0' COMMENT '请求参数',
  `ip` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0' COMMENT '客户端ip',
  `created_at` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '请求时间',
  `updated_at` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `admin_user_id` (`admin_user_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_collection_call_records
-- ----------------------------
DROP TABLE IF EXISTS `tb_collection_call_records`;
CREATE TABLE `tb_collection_call_records` (
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
) ENGINE=InnoDB AUTO_INCREMENT=184 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_collection_checkin_log
-- ----------------------------
DROP TABLE IF EXISTS `tb_collection_checkin_log`;
CREATE TABLE `tb_collection_checkin_log` (
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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_collection_order_dispatch_log
-- ----------------------------
DROP TABLE IF EXISTS `tb_collection_order_dispatch_log`;
CREATE TABLE `tb_collection_order_dispatch_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `collection_order_id` int(11) DEFAULT NULL COMMENT '催收订单ID',
  `collection_order_level` tinyint(4) DEFAULT NULL COMMENT '派发时订单的逾期等级',
  `type` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '1直接派给公司,2直接派给具体某个人的',
  `outside` tinyint(4) DEFAULT NULL COMMENT '机构ID',
  `admin_user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '催收员ID',
  `order_repayment_id` int(11) DEFAULT NULL COMMENT '还款订单ID',
  `overdue_day` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '派发时订单的逾期天数',
  `overdue_fee` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '派发时订单的逾期费',
  `merchant_id` int(11) DEFAULT NULL COMMENT '商户号',
  `operator_id` int(11) DEFAULT NULL COMMENT '操作ID',
  `created_at` int(11) DEFAULT NULL COMMENT '创建时间即派发时间',
  `updated_at` int(11) DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `collection_order_id` (`collection_order_id`),
  KEY `order_repayment_id` (`order_repayment_id`),
  KEY `admin_user_id` (`admin_user_id`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=128 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_collection_reduce_apply
-- ----------------------------
DROP TABLE IF EXISTS `tb_collection_reduce_apply`;
CREATE TABLE `tb_collection_reduce_apply` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `collection_id` int(11) unsigned DEFAULT '0' COMMENT '申请人ID',
  `admin_user_id` int(11) unsigned DEFAULT '0' COMMENT '申请人admin ID',
  `loan_collection_order_id` int(11) unsigned DEFAULT '0' COMMENT '催收订单id',
  `apply_status` tinyint(4) DEFAULT NULL COMMENT '申请状态',
  `apply_remark` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '申请备注',
  `operator_admin_user_id` int(11) unsigned DEFAULT '0' COMMENT '操作人admin ID',
  `merchant_id` int(11) DEFAULT NULL COMMENT '商户ID',
  `created_at` int(11) unsigned DEFAULT NULL COMMENT '创建时间',
  `updated_at` int(11) unsigned DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_collector_attendance_day_data
-- ----------------------------
DROP TABLE IF EXISTS `tb_collector_attendance_day_data`;
CREATE TABLE `tb_collector_attendance_day_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` date DEFAULT NULL COMMENT '日期',
  `outside` tinyint(4) DEFAULT NULL COMMENT '机构id',
  `group` tinyint(4) DEFAULT NULL COMMENT '催收组',
  `group_game` tinyint(4) unsigned DEFAULT '0' COMMENT '小组',
  `total_num` int(11) DEFAULT NULL COMMENT '总人数',
  `today_add_num` int(11) DEFAULT '0' COMMENT '当天新增人数',
  `attendance_num` int(11) DEFAULT NULL COMMENT '出勤人数',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_collector_back_money
-- ----------------------------
DROP TABLE IF EXISTS `tb_collector_back_money`;
CREATE TABLE `tb_collector_back_money` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` date DEFAULT NULL,
  `admin_user_id` int(11) DEFAULT NULL,
  `back_money` int(11) DEFAULT NULL,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `delay_money` int(11) NOT NULL DEFAULT '0',
  `delay_order_count` int(11) NOT NULL DEFAULT '0',
  `extend_money` int(11) NOT NULL DEFAULT '0',
  `extend_order_count` int(11) NOT NULL DEFAULT '0',
  `finish_order_count` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_collector_call_data
-- ----------------------------
DROP TABLE IF EXISTS `tb_collector_call_data`;
CREATE TABLE `tb_collector_call_data` (
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
  `phone_type` tinyint(4) DEFAULT '1' COMMENT '1:本机 2:牛信',
  PRIMARY KEY (`id`),
  KEY `date` (`date`),
  KEY `phone` (`phone`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_collector_class_schedule
-- ----------------------------
DROP TABLE IF EXISTS `tb_collector_class_schedule`;
CREATE TABLE `tb_collector_class_schedule` (
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
  KEY `admin_id` (`admin_id`),
  KEY `date` (`date`)
) ENGINE=InnoDB AUTO_INCREMENT=114 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_collector_work_api_operate
-- ----------------------------
DROP TABLE IF EXISTS `tb_collector_work_api_operate`;
CREATE TABLE `tb_collector_work_api_operate` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` date DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `is_get_order_list` tinyint(4) unsigned DEFAULT '0',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_company_team
-- ----------------------------
DROP TABLE IF EXISTS `tb_company_team`;
CREATE TABLE `tb_company_team` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `outside` int(11) DEFAULT NULL,
  `team` int(11) DEFAULT NULL,
  `alias` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `outside` (`outside`) USING BTREE,
  KEY `team` (`team`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for tb_dispatch_outside_finish
-- ----------------------------
DROP TABLE IF EXISTS `tb_dispatch_outside_finish`;
CREATE TABLE `tb_dispatch_outside_finish` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `date` date DEFAULT NULL COMMENT '分派日期',
  `outside` tinyint(4) DEFAULT NULL COMMENT '机构',
  `total_dispatch_num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '当日分派单数当日去重',
  `total_dispatch_amount` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '当日分派金额',
  `total_repay_num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '还款订单数',
  `total_repay_amount` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '还款订单的金额',
  `overday1_dispatch_num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '逾期1天内分派单数',
  `overday1_dispatch_amount` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '逾期1天内分派金额',
  `overday1_repay_num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '逾期1天内分派完成单数',
  `overday1_repay_amount` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '逾期1天内分派完成金额',
  `overday1_3_dispatch_num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '逾期3天内分派单数',
  `overday1_3_dispatch_amount` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '逾期3天内分派金额',
  `overday1_3_repay_num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '逾期3天内分派完成单数',
  `overday1_3_repay_amount` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '逾期3天内分派完成金额',
  `overday1_5_dispatch_num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '逾期5天内分派单数',
  `overday1_5_dispatch_amount` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '逾期5天内分派金额',
  `overday1_5_repay_num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '逾期5天内分派完成单数',
  `overday1_5_repay_amount` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '逾期5天内分派完成金额',
  `overlevel2_dispatch_num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'D1-3分派单数',
  `overlevel2_dispatch_amount` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT 'D1-3分派金额',
  `overlevel2_repay_num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'D1-3分派完成单数',
  `overlevel2_repay_amount` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT 'D1-3分派完成金额',
  `overlevel3_dispatch_num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'D4-7分派单数',
  `overlevel3_dispatch_amount` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT 'D4-7分派金额',
  `overlevel3_repay_num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'D4-7分派完成单数',
  `overlevel3_repay_amount` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT 'D4-7分派完成金额',
  `overlevel4_dispatch_num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'S1分派单数',
  `overlevel4_dispatch_amount` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT 'S1分派金额',
  `overlevel4_repay_num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'S1分派完成单数',
  `overlevel4_repay_amount` bigint(19) unsigned NOT NULL DEFAULT '0' COMMENT 'S1分派完成金额',
  `overlevel5_dispatch_num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'S2分派单数',
  `overlevel5_dispatch_amount` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT 'S2分派金额',
  `overlevel5_repay_num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'S2分派完成单数',
  `overlevel5_repay_amount` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT 'S2分派完成金额',
  `overlevel6_dispatch_num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'M1分派单数',
  `overlevel6_dispatch_amount` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT 'M1分派金额',
  `overlevel6_repay_num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'M1分派完成单数',
  `overlevel6_repay_amount` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT 'M1分派完成金额',
  `overlevel7_dispatch_num` int(11) unsigned NOT NULL DEFAULT '0',
  `overlevel7_dispatch_amount` bigint(20) unsigned NOT NULL DEFAULT '0',
  `overlevel7_repay_num` int(11) unsigned NOT NULL DEFAULT '0',
  `overlevel7_repay_amount` bigint(20) unsigned NOT NULL DEFAULT '0',
  `overlevel8_dispatch_num` int(11) unsigned NOT NULL DEFAULT '0',
  `overlevel8_dispatch_amount` bigint(20) unsigned NOT NULL DEFAULT '0',
  `overlevel8_repay_num` int(11) unsigned NOT NULL DEFAULT '0',
  `overlevel8_repay_amount` bigint(20) unsigned NOT NULL DEFAULT '0',
  `overlevel9_dispatch_num` int(11) unsigned NOT NULL DEFAULT '0',
  `overlevel9_dispatch_amount` bigint(20) unsigned NOT NULL DEFAULT '0',
  `overlevel9_repay_num` int(11) unsigned NOT NULL DEFAULT '0',
  `overlevel9_repay_amount` bigint(20) unsigned NOT NULL DEFAULT '0',
  `merchant_id` int(11) DEFAULT NULL COMMENT '商户ID',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_dispatch_overdue_days_finish
-- ----------------------------
DROP TABLE IF EXISTS `tb_dispatch_overdue_days_finish`;
CREATE TABLE `tb_dispatch_overdue_days_finish` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `date` date DEFAULT NULL COMMENT '分派日期',
  `admin_user_id` int(11) DEFAULT NULL COMMENT '分派催收员的id',
  `overdue_day` tinyint(4) DEFAULT NULL COMMENT '分派订单逾期天数',
  `dispatch_count` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '分派单数',
  `new_dispatch_count` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '分派单数-新客',
  `old_dispatch_count` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '分派单数-老客',
  `dispatch_amount` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '分派金额',
  `new_dispatch_amount` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '分派金额-新客',
  `old_dispatch_amount` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '分派金额-老客',
  `today_repay_count` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '当天还款单数',
  `new_today_repay_count` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '当天还款单数-新客',
  `old_today_repay_count` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '当天还款单数-老客',
  `today_repay_amount` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '当天还款金额',
  `new_today_repay_amount` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '当天还款金额-新客',
  `old_today_repay_amount` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '当天还款金额-老客',
  `total_repay_count` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '总还款单数',
  `new_total_repay_count` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '总还款单数-新客',
  `old_total_repay_count` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '总还款单数-老客',
  `total_repay_amount` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '总还款金额',
  `new_total_repay_amount` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '总还款金额-新客',
  `old_total_repay_amount` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '总还款金额-老客',
  `created_at` int(11) DEFAULT NULL COMMENT '创建时间',
  `updated_at` int(11) DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `date` (`date`) USING BTREE,
  KEY `admin_user_id` (`admin_user_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for tb_input_overday_out
-- ----------------------------
DROP TABLE IF EXISTS `tb_input_overday_out`;
CREATE TABLE `tb_input_overday_out` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` date DEFAULT NULL COMMENT '日期',
  `package_name` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '' COMMENT '来源包',
  `user_type` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '用户新老类型;0全部,1新用户(首单),2老用户(非首单)',
  `input_count` int(11) NOT NULL DEFAULT '0' COMMENT '入催单数',
  `overday_total_count` int(11) NOT NULL DEFAULT '0' COMMENT '逾期出崔总单',
  `overlevel4_count` int(11) NOT NULL DEFAULT '0' COMMENT 'S1出崔',
  `overlevel5_count` int(11) NOT NULL DEFAULT '0' COMMENT 'S2',
  `overlevel6_count` int(11) NOT NULL DEFAULT '0' COMMENT 'M1',
  `overlevel7_count` int(11) NOT NULL DEFAULT '0' COMMENT 'M2',
  `overlevel8_count` int(11) NOT NULL DEFAULT '0' COMMENT 'M3',
  `overlevel9_count` int(11) NOT NULL DEFAULT '0' COMMENT 'M3+',
  `overday1_count` int(11) NOT NULL DEFAULT '0' COMMENT '逾期一天出崔单',
  `overday2_count` int(11) NOT NULL DEFAULT '0' COMMENT '逾期两天出崔单数',
  `overday3_count` int(11) NOT NULL DEFAULT '0',
  `overday4_count` int(11) NOT NULL DEFAULT '0',
  `overday5_count` int(11) NOT NULL DEFAULT '0',
  `overday6_count` int(11) NOT NULL DEFAULT '0',
  `overday7_count` int(11) NOT NULL DEFAULT '0',
  `overday8_count` int(11) NOT NULL DEFAULT '0',
  `overday9_count` int(11) NOT NULL DEFAULT '0',
  `overday10_count` int(11) NOT NULL DEFAULT '0',
  `overday11_count` int(11) NOT NULL DEFAULT '0',
  `overday12_count` int(11) NOT NULL DEFAULT '0',
  `overday13_count` int(11) NOT NULL DEFAULT '0',
  `overday14_count` int(11) NOT NULL DEFAULT '0',
  `overday15_count` int(11) NOT NULL DEFAULT '0',
  `overday16_count` int(11) NOT NULL DEFAULT '0',
  `overday17_count` int(11) NOT NULL DEFAULT '0',
  `overday18_count` int(11) NOT NULL DEFAULT '0',
  `overday19_count` int(11) NOT NULL DEFAULT '0',
  `overday20_count` int(11) NOT NULL DEFAULT '0',
  `overday21_count` int(11) NOT NULL DEFAULT '0',
  `overday22_count` int(11) NOT NULL DEFAULT '0',
  `overday23_count` int(11) NOT NULL DEFAULT '0',
  `overday24_count` int(11) NOT NULL DEFAULT '0',
  `overday25_count` int(11) NOT NULL DEFAULT '0',
  `overday26_count` int(11) NOT NULL DEFAULT '0',
  `overday27_count` int(11) NOT NULL DEFAULT '0',
  `overday28_count` int(11) NOT NULL DEFAULT '0',
  `overday29_count` int(11) NOT NULL DEFAULT '0',
  `overday30_count` int(11) NOT NULL DEFAULT '0',
  `merchant_id` int(11) DEFAULT NULL COMMENT '商户ID',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `date` (`date`)
) ENGINE=InnoDB AUTO_INCREMENT=2699 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_input_overday_out_amount
-- ----------------------------
DROP TABLE IF EXISTS `tb_input_overday_out_amount`;
CREATE TABLE `tb_input_overday_out_amount` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` date DEFAULT NULL COMMENT '日期',
  `package_name` varchar(100) DEFAULT NULL,
  `user_type` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '用户新老类型;0全部,1新用户(首单),2老用户(非首单)',
  `input_amount` int(11) NOT NULL DEFAULT '0' COMMENT '入催单数',
  `overday_total_amount` int(11) NOT NULL DEFAULT '0' COMMENT '逾期出崔总单',
  `overlevel4_amount` int(11) NOT NULL DEFAULT '0' COMMENT 'S1出崔',
  `overlevel5_amount` int(11) NOT NULL DEFAULT '0' COMMENT 'S2',
  `overlevel6_amount` int(11) NOT NULL DEFAULT '0' COMMENT 'M1',
  `overlevel7_amount` int(11) NOT NULL DEFAULT '0' COMMENT 'M2',
  `overlevel8_amount` int(11) NOT NULL DEFAULT '0' COMMENT 'M3',
  `overlevel9_amount` int(11) NOT NULL DEFAULT '0' COMMENT 'M3+',
  `overday1_amount` int(11) NOT NULL DEFAULT '0' COMMENT '逾期一天出崔单',
  `overday2_amount` int(11) NOT NULL DEFAULT '0' COMMENT '逾期两天出崔单数',
  `overday3_amount` int(11) NOT NULL DEFAULT '0',
  `overday4_amount` int(11) NOT NULL DEFAULT '0',
  `overday5_amount` int(11) NOT NULL DEFAULT '0',
  `overday6_amount` int(11) NOT NULL DEFAULT '0',
  `overday7_amount` int(11) NOT NULL DEFAULT '0',
  `overday8_amount` int(11) NOT NULL DEFAULT '0',
  `overday9_amount` int(11) NOT NULL DEFAULT '0',
  `overday10_amount` int(11) NOT NULL DEFAULT '0',
  `overday11_amount` int(11) NOT NULL DEFAULT '0',
  `overday12_amount` int(11) NOT NULL DEFAULT '0',
  `overday13_amount` int(11) NOT NULL DEFAULT '0',
  `overday14_amount` int(11) NOT NULL DEFAULT '0',
  `overday15_amount` int(11) NOT NULL DEFAULT '0',
  `overday16_amount` int(11) NOT NULL DEFAULT '0',
  `overday17_amount` int(11) NOT NULL DEFAULT '0',
  `overday18_amount` int(11) NOT NULL DEFAULT '0',
  `overday19_amount` int(11) NOT NULL DEFAULT '0',
  `overday20_amount` int(11) NOT NULL DEFAULT '0',
  `overday21_amount` int(11) NOT NULL DEFAULT '0',
  `overday22_amount` int(11) NOT NULL DEFAULT '0',
  `overday23_amount` int(11) NOT NULL DEFAULT '0',
  `overday24_amount` int(11) NOT NULL DEFAULT '0',
  `overday25_amount` int(11) NOT NULL DEFAULT '0',
  `overday26_amount` int(11) NOT NULL DEFAULT '0',
  `overday27_amount` int(11) NOT NULL DEFAULT '0',
  `overday28_amount` int(11) NOT NULL DEFAULT '0',
  `overday29_amount` int(11) NOT NULL DEFAULT '0',
  `overday30_amount` int(11) NOT NULL DEFAULT '0',
  `merchant_id` int(11) DEFAULT NULL COMMENT '商户ID',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `date` (`date`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_level_change_daily_call
-- ----------------------------
DROP TABLE IF EXISTS `tb_level_change_daily_call`;
CREATE TABLE `tb_level_change_daily_call` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `collection_order_id` int(11) DEFAULT NULL,
  `loan_order_id` int(11) DEFAULT NULL,
  `repayment_id` int(11) DEFAULT NULL,
  `over_level` tinyint(4) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `user_phone` bigint(20) DEFAULT NULL,
  `send_status` int(11) NOT NULL DEFAULT '0',
  `send_id` varchar(50) DEFAULT NULL,
  `remark` varchar(255) DEFAULT NULL,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_little_level_setting
-- ----------------------------
DROP TABLE IF EXISTS `tb_little_level_setting`;
CREATE TABLE `tb_little_level_setting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `level` int(10) DEFAULT NULL,
  `overdue_day` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for tb_loan_collection
-- ----------------------------
DROP TABLE IF EXISTS `tb_loan_collection`;
CREATE TABLE `tb_loan_collection` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `admin_user_id` int(11) NOT NULL DEFAULT '0' COMMENT 'admin用户uid',
  `username` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '姓名',
  `phone` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '手机号',
  `group` int(11) NOT NULL DEFAULT '0' COMMENT '1:S1;2:S2;3:S3;4:S4;5:S5;',
  `created_at` int(11) DEFAULT NULL COMMENT '创建时间',
  `updated_at` int(11) DEFAULT NULL,
  `operator_name` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '操作人',
  `status` int(11) DEFAULT '0' COMMENT '状态：-1，禁用，1：待审核；2、可用',
  `outside` int(11) unsigned DEFAULT NULL COMMENT '委外机构id',
  `real_name` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '委外机构人员真实姓名',
  `group_game` tinyint(4) unsigned NOT NULL DEFAULT '1' COMMENT '小组分组',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `admin_user_id` (`admin_user_id`) USING BTREE,
  KEY `group` (`group`) USING BTREE,
  KEY `outside` (`outside`) USING BTREE,
  KEY `status` (`status`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='催收用户';

-- ----------------------------
-- Table structure for tb_loan_collection_day_statistic
-- ----------------------------
DROP TABLE IF EXISTS `tb_loan_collection_day_statistic`;
CREATE TABLE `tb_loan_collection_day_statistic` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` date DEFAULT NULL COMMENT '日期',
  `admin_user_id` int(11) NOT NULL COMMENT '催收人ID',
  `username` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '催收人名字',
  `group` int(10) NOT NULL DEFAULT '0' COMMENT '分组 单位为分',
  `group_game` int(10) NOT NULL DEFAULT '0' COMMENT '小组',
  `outside` int(11) NOT NULL COMMENT '催收机构',
  `get_total_money` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '本金总额 单位为分',
  `get_total_count` int(11) DEFAULT '0' COMMENT '总单数',
  `finish_total_money` bigint(20) DEFAULT '0' COMMENT '完成总金额',
  `finish_total_count` int(11) DEFAULT '0' COMMENT '总完成单数',
  `operate_total` int(11) DEFAULT '0' COMMENT '操作数',
  `merchant_id` int(11) DEFAULT NULL COMMENT '商户ID',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `Index 2` (`admin_user_id`) USING BTREE,
  KEY `outside` (`outside`) USING BTREE,
  KEY `loan_group` (`group`) USING BTREE,
  KEY `created_at` (`created_at`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_loan_collection_deadline
-- ----------------------------
DROP TABLE IF EXISTS `tb_loan_collection_deadline`;
CREATE TABLE `tb_loan_collection_deadline` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `deadline_time` int(11) DEFAULT '0',
  `deadline_amount` int(11) DEFAULT '0',
  `deadline_principal` bigint(20) DEFAULT '0',
  `stage_type` tinyint(4) unsigned NOT NULL DEFAULT '1' COMMENT '区分单分期',
  `sub_from` tinyint(4) unsigned NOT NULL DEFAULT '1' COMMENT '项目来源',
  `created_at` int(11) DEFAULT '0',
  `updated_at` int(11) DEFAULT '0',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `sub_from` (`sub_from`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_loan_collection_ip
-- ----------------------------
DROP TABLE IF EXISTS `tb_loan_collection_ip`;
CREATE TABLE `tb_loan_collection_ip` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `outside` int(11) DEFAULT '-1',
  `ip` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `status` int(4) DEFAULT '1' COMMENT '状态，1开启；0禁用;-1删除',
  `operator` int(10) DEFAULT '0',
  `remark` text CHARACTER SET utf8 COLLATE utf8_general_ci,
  `created_at` int(11) DEFAULT '0',
  `updated_at` int(11) DEFAULT '0',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_loan_collection_order
-- ----------------------------
DROP TABLE IF EXISTS `tb_loan_collection_order`;
CREATE TABLE `tb_loan_collection_order` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT '借款人ID',
  `user_loan_order_id` int(11) NOT NULL DEFAULT '0' COMMENT '借款记录ID',
  `user_loan_order_repayment_id` int(11) NOT NULL DEFAULT '0' COMMENT '还款明细ID',
  `dispatch_name` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '0' COMMENT '派单人',
  `dispatch_time` int(11) DEFAULT '0' COMMENT '派单时间',
  `current_collection_admin_user_id` int(11) DEFAULT '0' COMMENT '当前催收员ID',
  `current_overdue_level` int(11) DEFAULT '0' COMMENT '当前逾期等级',
  `customer_type` tinyint(2) DEFAULT NULL COMMENT '用户是否老用户1是0不是',
  `status` int(10) DEFAULT '0' COMMENT '催收状态',
  `promise_repayment_time` int(11) DEFAULT '0' COMMENT '承诺还款时间',
  `last_collection_time` int(11) DEFAULT '0' COMMENT '最后催收时间',
  `next_loan_advice` tinyint(4) DEFAULT '0',
  `created_at` int(11) DEFAULT '0' COMMENT '创建时间',
  `updated_at` int(11) DEFAULT '0' COMMENT '更新时间',
  `operator_name` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '操作人',
  `remark` text CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT '备注',
  `before_status` int(11) DEFAULT '0',
  `open_app_apply_reduction` tinyint(4) DEFAULT '0' COMMENT '是否开通提交可减免',
  `outside` int(4) DEFAULT '0' COMMENT '委外机构ID',
  `dispatch_outside_time` int(11) DEFAULT '0' COMMENT '分派机构时间',
  `current_overdue_group` tinyint(4) unsigned DEFAULT '0' COMMENT '所属催收分组',
  `contact_status` tinyint(4) DEFAULT '0' COMMENT '联系状态',
  `is_purpose` tinyint(1) unsigned DEFAULT '0' COMMENT '是否有偿还意愿',
  `last_dispatch_time` int(11) unsigned DEFAULT '0' COMMENT '记录一下派单时间',
  `merchant_id` int(11) unsigned NOT NULL DEFAULT '0',
  `user_last_access_time` int(11) unsigned DEFAULT '0' COMMENT '用户最后一次访问时间',
  `user_last_pay_action_time` int(11) unsigned DEFAULT '0' COMMENT '用户最后一次支付行为',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `user_id` (`user_id`) USING BTREE,
  KEY `user_loan_order_id` (`user_loan_order_id`) USING BTREE,
  KEY `current_overdue_group` (`current_overdue_group`) USING BTREE,
  KEY `created_at` (`created_at`) USING BTREE,
  KEY `user_loan_order_repayment_id` (`user_loan_order_repayment_id`) USING BTREE,
  KEY `dispatch_time` (`dispatch_time`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=66 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_loan_collection_order_all
-- ----------------------------
DROP TABLE IF EXISTS `tb_loan_collection_order_all`;
CREATE TABLE `tb_loan_collection_order_all` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `loan_collection_order_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '催收订单表id',
  `user_loan_order_repayment_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '还款id',
  `dispatch_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '派单时间',
  `current_collection_admin_user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '催收人id',
  `current_overdue_level` int(11) NOT NULL DEFAULT '0' COMMENT '逾期等级',
  `current_overdue_group` int(11) NOT NULL DEFAULT '0' COMMENT '催收员分组',
  `outside_id` int(11) NOT NULL DEFAULT '0' COMMENT '催收机构id',
  `last_collection_time` int(11) NOT NULL DEFAULT '0' COMMENT '最后催收时间',
  `overdue_status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '逾期状态:0未逾期-提醒订单，1已逾期-催收订单',
  `customer_type` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否老用户 1是0否',
  `status` tinyint(1) unsigned NOT NULL COMMENT '是否已转派，0,正常;1已过期',
  `that_day_status` tinyint(1) NOT NULL COMMENT '当天是否被回收0,正常,1回收过',
  `created_at` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated_at` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `loan_collection_order_id` (`loan_collection_order_id`) USING BTREE,
  KEY `status` (`status`) USING BTREE,
  KEY `user_loan_order_repayment_id` (`user_loan_order_repayment_id`) USING BTREE,
  KEY `dispatch_time` (`dispatch_time`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=97 DEFAULT CHARSET=utf8 COMMENT='派单记录';

-- ----------------------------
-- Table structure for tb_loan_collection_order_all_copy1
-- ----------------------------
DROP TABLE IF EXISTS `tb_loan_collection_order_all_copy1`;
CREATE TABLE `tb_loan_collection_order_all_copy1` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `loan_collection_order_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '催收订单表id',
  `user_loan_order_repayment_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '还款id',
  `dispatch_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '派单时间',
  `current_collection_admin_user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '催收人id',
  `current_overdue_level` int(11) NOT NULL DEFAULT '0' COMMENT '逾期等级',
  `current_overdue_group` int(11) NOT NULL DEFAULT '0' COMMENT '催收员分组',
  `outside_id` int(11) NOT NULL DEFAULT '0' COMMENT '催收机构id',
  `last_collection_time` int(11) NOT NULL DEFAULT '0' COMMENT '最后催收时间',
  `last_reminder_time` int(11) NOT NULL DEFAULT '0' COMMENT '最后提醒时间',
  `overdue_status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '逾期状态:0未逾期-提醒订单，1已逾期-催收订单',
  `customer_type` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否老用户 1是0否',
  `status` tinyint(1) unsigned NOT NULL COMMENT '是否已转派，0,正常;1已过期',
  `created_at` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated_at` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `loan_collection_order_id` (`loan_collection_order_id`) USING BTREE,
  KEY `status` (`status`) USING BTREE,
  KEY `user_loan_order_repayment_id` (`user_loan_order_repayment_id`) USING BTREE,
  KEY `dispatch_time` (`dispatch_time`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=4041 DEFAULT CHARSET=utf8 COMMENT='派单记录';

-- ----------------------------
-- Table structure for tb_loan_collection_record
-- ----------------------------
DROP TABLE IF EXISTS `tb_loan_collection_record`;
CREATE TABLE `tb_loan_collection_record` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `order_id` int(11) unsigned NOT NULL COMMENT '催收订单ID',
  `operator` int(11) NOT NULL COMMENT '催收操作人ID',
  `contact_id` int(11) NOT NULL COMMENT '联系人ID',
  `contact_type` tinyint(4) DEFAULT '0' COMMENT '联系人类型 0: 紧急联系人 1:通讯录联系人',
  `contact_name` varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '联系人姓名',
  `relation` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '联系人关系',
  `contact_phone` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '联系人电话',
  `stress_level` tinyint(4) DEFAULT '0' COMMENT '施压等级',
  `order_level` tinyint(4) NOT NULL COMMENT '当前催收等级',
  `order_state` tinyint(4) NOT NULL COMMENT '当前催收状态',
  `operate_type` tinyint(4) NOT NULL COMMENT '催收类型',
  `operate_at` int(11) NOT NULL COMMENT '催收时间',
  `content` varchar(512) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '' COMMENT '催收内容',
  `created_at` int(11) NOT NULL COMMENT '创建时间',
  `updated_at` int(11) NOT NULL COMMENT '更新时间',
  `remark` varchar(512) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '备注（以备催收人员查阅）',
  `promise_repayment_time` int(11) DEFAULT '0' COMMENT '承诺还款时间',
  `send_note` tinyint(1) NOT NULL DEFAULT '1' COMMENT '短信发送状态 1 成功  0失败',
  `risk_control` tinyint(4) NOT NULL DEFAULT '0' COMMENT '风控标签',
  `is_connect` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否接通  ',
  `loan_user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '借款用户id',
  `loan_order_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '借款订单id',
  `merchant_id` tinyint(4) unsigned DEFAULT '0',
  `user_amount` int(11) DEFAULT NULL COMMENT '用户回复已还款金额',
  `user_utr` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '用户回复还款对应utr',
  `user_pic` json DEFAULT NULL COMMENT '上传用户截图或图片',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_order_id` (`order_id`) USING BTREE,
  KEY `idx_operator` (`operator`) USING BTREE,
  KEY `contact_phone` (`contact_phone`) USING BTREE,
  KEY `created_at` (`created_at`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8 COMMENT='催收记录表';

-- ----------------------------
-- Table structure for tb_loan_collection_record_statistic
-- ----------------------------
DROP TABLE IF EXISTS `tb_loan_collection_record_statistic`;
CREATE TABLE `tb_loan_collection_record_statistic` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `outside_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '机构id',
  `order_group` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '订单组',
  `order_total` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '总订单数',
  `operate_total` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '处理量',
  `record_count` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '总拨打次数',
  `record_yes` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '总接通次数',
  `self_count` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '本人拨打次数',
  `self_yes` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '本人接通次数',
  `address_count` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '拨打通讯录次数',
  `address_yes` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '通讯录接通次数',
  `created_at` int(11) unsigned NOT NULL DEFAULT '0',
  `updated_at` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_loan_collection_statistic
-- ----------------------------
DROP TABLE IF EXISTS `tb_loan_collection_statistic`;
CREATE TABLE `tb_loan_collection_statistic` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_user_id` int(11) NOT NULL COMMENT '催收人ID',
  `username` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '催收人名字',
  `real_name` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '' COMMENT '催收人真实名',
  `loan_group` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '分组 单位为分',
  `outside` int(11) NOT NULL COMMENT '催收机构',
  `total_money` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '本金总额 单位为分',
  `loan_total` int(11) DEFAULT '0' COMMENT '总单数',
  `today_finish_total_money` bigint(20) DEFAULT '0' COMMENT '当日完成金额',
  `finish_total_money` bigint(20) DEFAULT '0' COMMENT '完成总金额',
  `no_finish_total_money` bigint(20) DEFAULT '0' COMMENT '没有完成总金额',
  `operate_total` int(11) DEFAULT '0' COMMENT '操作数',
  `today_finish_total` int(11) DEFAULT '0' COMMENT '当日完成单数',
  `finish_total` int(11) DEFAULT '0' COMMENT '总完成单数',
  `finish_total_rate` decimal(7,4) DEFAULT '0.0000' COMMENT '完成率',
  `no_finish_rate` decimal(7,4) DEFAULT '0.0000' COMMENT '迁移率',
  `finish_late_fee` bigint(20) DEFAULT '0' COMMENT '催回滞纳金',
  `late_fee_total` bigint(20) DEFAULT '0' COMMENT '总滞纳金',
  `finish_late_fee_rate` decimal(7,4) DEFAULT '0.0000' COMMENT '滞纳金回收率',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `order_level` int(11) DEFAULT '0' COMMENT '订单催收级别',
  `huankuan_total_money` int(11) DEFAULT '0' COMMENT '催收成功单子的本金总额',
  `today_get_loan_total` int(11) DEFAULT '0' COMMENT '今日入催总单数',
  `today_get_total_money` bigint(20) unsigned DEFAULT '0' COMMENT '今日入催本金总额 单位为分',
  `leave_principal` int(10) unsigned DEFAULT '0' COMMENT '今日转出本金总额 单位为分',
  `get_principal` int(10) unsigned DEFAULT '0' COMMENT '今日转入本金总额 单位为分',
  `member_fee` bigint(20) unsigned DEFAULT '0' COMMENT '回收综合费',
  `dis_money` bigint(20) unsigned DEFAULT '0' COMMENT '展期金额',
  `total_money_m` bigint(20) unsigned DEFAULT '0',
  `loan_total_m` int(11) unsigned DEFAULT '0',
  `merchant_id` int(11) DEFAULT NULL COMMENT '商户ID',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `Index 2` (`admin_user_id`,`order_level`) USING BTREE,
  KEY `outside` (`outside`) USING BTREE,
  KEY `loan_group` (`loan_group`) USING BTREE,
  KEY `order_level` (`order_level`) USING BTREE,
  KEY `created_at` (`created_at`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_loan_collection_statistic_new
-- ----------------------------
DROP TABLE IF EXISTS `tb_loan_collection_statistic_new`;
CREATE TABLE `tb_loan_collection_statistic_new` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '催收员id',
  `admin_user_name` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '催收人姓名',
  `real_name` varchar(64) DEFAULT NULL COMMENT '催收人真实名',
  `loan_group` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '催收所属组',
  `outside_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '催收机构id',
  `order_level` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '订单级别',
  `today_all_money` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '今日入催本金',
  `loan_finish_total` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '当日完成的单数',
  `loan_total` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '今日入催单数',
  `today_finish_money` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '当日派单基础上完成的金额',
  `today_no_finish_money` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '当日没有完成的金额',
  `all_late_fee` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '总滞纳金',
  `finish_late_fee` int(11) NOT NULL DEFAULT '0' COMMENT '当日完成的滞纳金',
  `dispatch_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '派单日期',
  `operate_total` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '操作单数',
  `true_total_money` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '实际还款金额',
  `oneday_money` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '首日完成金额',
  `oneday_total` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '首日完成单数',
  `finish_total_rate` decimal(7,4) NOT NULL DEFAULT '0.0000' COMMENT '还款率',
  `today_finish_late_fee` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '今日完成订单滞纳金总额',
  `no_finish_rate` decimal(7,4) NOT NULL DEFAULT '0.0000' COMMENT '迁徙率',
  `stage_type` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '产品类型  0短期  1分期',
  `merchant_id` int(11) DEFAULT NULL COMMENT '商户ID',
  `created_at` int(11) NOT NULL DEFAULT '0',
  `updated_at` int(11) NOT NULL DEFAULT '0',
  `sub_from` tinyint(4) NOT NULL DEFAULT '1' COMMENT '项目来源',
  PRIMARY KEY (`Id`) USING BTREE,
  KEY `admin_user_id` (`admin_user_id`) USING BTREE,
  KEY `outside_id` (`outside_id`) USING BTREE,
  KEY `loan_group` (`loan_group`) USING BTREE,
  KEY `order_level` (`order_level`) USING BTREE,
  KEY `sub_from` (`sub_from`) USING BTREE,
  KEY `dispatch_time` (`dispatch_time`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_loan_collection_statistic_v2
-- ----------------------------
DROP TABLE IF EXISTS `tb_loan_collection_statistic_v2`;
CREATE TABLE `tb_loan_collection_statistic_v2` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_user_id` int(11) NOT NULL COMMENT '催收人ID',
  `username` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '催收人名字',
  `loan_group` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '分组 单位为分',
  `outside` int(11) NOT NULL COMMENT '催收机构',
  `total_money` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '本金总额 单位为分',
  `loan_total` int(11) DEFAULT '0' COMMENT '总单数',
  `today_finish_total_money` bigint(20) DEFAULT '0' COMMENT '当日完成金额',
  `finish_total_money` bigint(20) DEFAULT '0' COMMENT '完成总金额',
  `no_finish_total_money` bigint(20) DEFAULT '0' COMMENT '没有完成总金额',
  `operate_total` int(11) DEFAULT '0' COMMENT '操作数',
  `today_finish_total` int(11) DEFAULT '0' COMMENT '当日完成单数',
  `finish_total` int(11) DEFAULT '0' COMMENT '总完成单数',
  `finish_total_rate` decimal(7,4) DEFAULT '0.0000' COMMENT '完成率',
  `no_finish_rate` decimal(7,4) DEFAULT '0.0000' COMMENT '迁移率',
  `finish_late_fee` bigint(20) DEFAULT '0' COMMENT '催回滞纳金',
  `late_fee_total` bigint(20) DEFAULT '0' COMMENT '总滞纳金',
  `finish_late_fee_rate` decimal(7,4) DEFAULT '0.0000' COMMENT '滞纳金回收率',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `order_level` int(11) DEFAULT '0' COMMENT '订单催收级别',
  `huankuan_total_money` int(11) DEFAULT '0' COMMENT '催收成功单子的本金总额',
  `today_get_loan_total` int(11) DEFAULT '0' COMMENT '今日入催总单数',
  `today_get_total_money` bigint(20) unsigned DEFAULT '0' COMMENT '今日入催本金总额 单位为分',
  `leave_principal` int(10) unsigned DEFAULT '0' COMMENT '今日转出本金总额 单位为分',
  `get_principal` int(10) unsigned DEFAULT '0' COMMENT '今日转入本金总额 单位为分',
  `member_fee` bigint(20) unsigned DEFAULT '0' COMMENT '回收综合费',
  `dis_money` bigint(20) unsigned DEFAULT '0' COMMENT '展期金额',
  `total_money_m` bigint(20) unsigned DEFAULT '0',
  `loan_total_m` int(11) unsigned DEFAULT '0',
  `merchant_id` int(11) DEFAULT NULL COMMENT '商户ID',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `Index 2` (`admin_user_id`,`order_level`) USING BTREE,
  KEY `outside` (`outside`) USING BTREE,
  KEY `loan_group` (`loan_group`) USING BTREE,
  KEY `order_level` (`order_level`) USING BTREE,
  KEY `created_at` (`created_at`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=522 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_loan_collection_status_change_log
-- ----------------------------
DROP TABLE IF EXISTS `tb_loan_collection_status_change_log`;
CREATE TABLE `tb_loan_collection_status_change_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `loan_collection_order_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '催收订单ID',
  `before_status` int(11) DEFAULT '0' COMMENT '操作前状态',
  `after_status` int(11) DEFAULT '0' COMMENT '操作后状态',
  `type` int(11) DEFAULT '0' COMMENT '操作类型',
  `updated_at` int(11) unsigned DEFAULT NULL,
  `created_at` int(11) unsigned DEFAULT '0' COMMENT '创建时间',
  `operator_name` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '操作人',
  `remark` text CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT '操作备注',
  `merchant_id` int(11) DEFAULT NULL COMMENT '商户ID',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=218 DEFAULT CHARSET=utf8 COMMENT='状态流转表';

-- ----------------------------
-- Table structure for tb_loan_collection_suggestion_change_log
-- ----------------------------
DROP TABLE IF EXISTS `tb_loan_collection_suggestion_change_log`;
CREATE TABLE `tb_loan_collection_suggestion_change_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `collection_id` int(11) NOT NULL,
  `suggestion_before` int(5) DEFAULT NULL,
  `suggestion` int(5) DEFAULT NULL,
  `created_at` int(11) DEFAULT NULL,
  `operator_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `remark` text CHARACTER SET utf8 COLLATE utf8_general_ci,
  `outside` int(11) DEFAULT '0',
  `stage_type` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '是否分期  0不是  1是',
  `merchant_id` int(11) DEFAULT NULL COMMENT '商户ID',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `index` (`order_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_loan_collection_track_statistic
-- ----------------------------
DROP TABLE IF EXISTS `tb_loan_collection_track_statistic`;
CREATE TABLE `tb_loan_collection_track_statistic` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `dispatch_date` date NOT NULL COMMENT '派单日期',
  `admin_user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '催收员id',
  `admin_user_name` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '催收人姓名',
  `loan_group` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '催收所属组',
  `outside_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '催收机构id',
  `order_level` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '订单级别',
  `today_all_money` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '当日分派订单到期金额',
  `loan_total` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '当日分派订单数',
  `all_late_fee` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '当日分派订单的应还滞纳金',
  `operate_total` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '当日分派后有操作(有写催记)单数',
  `today_finish_money` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '当日派单后在手中完成的到期金额(和已还去最小)',
  `loan_finish_total` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '当日分派后在手中完结的单数',
  `true_total_money` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '当日分派后在手中完结订单的已还款金额',
  `today_finish_late_fee` int(11) DEFAULT '0' COMMENT '当日分派后在手中完结订单的应还滞纳金总额',
  `finish_late_fee` int(11) NOT NULL DEFAULT '0' COMMENT '当日分派后在手中完结订单的滞纳金min(已还金额-到期金额 ,0)，减免时小',
  `oneday_money` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '当日分派后且在当日完成到期金额(和已还去最小)',
  `oneday_total` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '当日分派后且在当日完成单数',
  `order_merchant_id` int(11) DEFAULT NULL COMMENT '商户ID',
  `user_merchant_id` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL DEFAULT '0',
  `updated_at` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`Id`) USING BTREE,
  KEY `admin_user_id` (`admin_user_id`) USING BTREE,
  KEY `outside_id` (`outside_id`) USING BTREE,
  KEY `loan_group` (`loan_group`) USING BTREE,
  KEY `order_level` (`order_level`) USING BTREE,
  KEY `dispatch_date` (`dispatch_date`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_loan_collection_user_company
-- ----------------------------
DROP TABLE IF EXISTS `tb_loan_collection_user_company`;
CREATE TABLE `tb_loan_collection_user_company` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '催单公司代号名称',
  `real_title` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '催单公司真实名称',
  `status` int(2) DEFAULT '1' COMMENT '状态，1启用，0删除',
  `system` int(2) DEFAULT '0' COMMENT '是否是自营团队，1是，0不是',
  `auto_dispatch` tinyint(4) NOT NULL DEFAULT '0',
  `merchant_id` tinyint(4) unsigned DEFAULT '0',
  `created_at` int(11) unsigned DEFAULT NULL,
  `updated_at` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_loan_collection_user_schedule
-- ----------------------------
DROP TABLE IF EXISTS `tb_loan_collection_user_schedule`;
CREATE TABLE `tb_loan_collection_user_schedule` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` int(10) DEFAULT NULL,
  `company_id` int(10) DEFAULT NULL,
  `max_amount` int(10) DEFAULT '0' COMMENT '每人每天最大接单量',
  `system` int(2) DEFAULT '0' COMMENT '是否是公司团队，1是，0不是',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_loan_reminder_record
-- ----------------------------
DROP TABLE IF EXISTS `tb_loan_reminder_record`;
CREATE TABLE `tb_loan_reminder_record` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `order_id` int(11) unsigned NOT NULL COMMENT '催收订单ID',
  `operator` int(11) NOT NULL COMMENT '催收操作人ID',
  `contact_id` int(11) NOT NULL COMMENT '联系人ID',
  `contact_type` tinyint(4) DEFAULT '0' COMMENT '联系人类型 0: 紧急联系人 1:通讯录联系人',
  `contact_name` varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '联系人姓名',
  `relation` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '联系人关系',
  `contact_phone` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '联系人电话',
  `stress_level` tinyint(4) DEFAULT '0' COMMENT '施压等级',
  `order_level` tinyint(4) NOT NULL COMMENT '当前催收等级',
  `order_state` tinyint(4) NOT NULL COMMENT '当前催收状态',
  `operate_type` tinyint(4) NOT NULL COMMENT '催收类型',
  `operate_at` int(11) NOT NULL COMMENT '催收时间',
  `content` varchar(512) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '' COMMENT '催收内容',
  `created_at` int(11) NOT NULL COMMENT '创建时间',
  `updated_at` int(11) NOT NULL COMMENT '更新时间',
  `remark` varchar(512) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '备注（以备催收人员查阅）',
  `promise_repayment_time` int(11) DEFAULT '0' COMMENT '承诺还款时间',
  `send_note` tinyint(1) NOT NULL DEFAULT '1' COMMENT '短信发送状态 1 成功  0失败',
  `risk_control` tinyint(4) NOT NULL DEFAULT '0' COMMENT '风控标签',
  `is_connect` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否接通  ',
  `loan_user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '借款用户id',
  `loan_order_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '借款订单id',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_order_id` (`order_id`) USING BTREE,
  KEY `idx_operator` (`operator`) USING BTREE,
  KEY `contact_phone` (`contact_phone`) USING BTREE,
  KEY `created_at` (`created_at`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='催收记录表';

-- ----------------------------
-- Table structure for tb_nx_phone_log
-- ----------------------------
DROP TABLE IF EXISTS `tb_nx_phone_log`;
CREATE TABLE `tb_nx_phone_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `collector_id` int(11) NOT NULL DEFAULT '0' COMMENT '催收员id',
  `nx_name` varchar(30) NOT NULL DEFAULT '0' COMMENT '催收员牛信账号',
  `nx_orderid` varchar(50) NOT NULL DEFAULT '0' COMMENT '牛信订单id',
  `type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '电话类型,1:本人 2:联系人 3通讯录',
  `phone` varchar(11) NOT NULL DEFAULT '0' COMMENT '拨打号码',
  `duration` int(5) NOT NULL DEFAULT '0' COMMENT '通话时长',
  `direction` int(5) NOT NULL DEFAULT '0' COMMENT '呼叫方向 1呼出  0呼入',
  `record_url` varchar(100) NOT NULL DEFAULT '0' COMMENT '录音访问地址',
  `start_time` varchar(20) DEFAULT '0' COMMENT '开始时间',
  `answer_time` varchar(20) DEFAULT '0' COMMENT '接通时间',
  `end_time` varchar(20) DEFAULT '0' COMMENT '结束时间',
  `hangup_cause` varchar(20) DEFAULT '0' COMMENT '挂机原因',
  `created_at` int(11) DEFAULT '0' COMMENT '创建时间',
  `updated_at` int(11) DEFAULT '0' COMMENT '更新时间',
  `status` tinyint(4) DEFAULT '0' COMMENT '0:未回调1:已回调',
  `call_type` tinyint(4) DEFAULT '0' COMMENT '0:催收 1:信审 2:客服',
  `phone_type` tinyint(4) DEFAULT '0' COMMENT '拨打类型',
  `order_id` int(11) DEFAULT NULL COMMENT '催收订单id',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `collector_id` (`collector_id`) USING BTREE,
  KEY `nx_name` (`nx_name`) USING BTREE,
  KEY `created_at` (`created_at`) USING BTREE,
  KEY `phone` (`phone`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_order_overview_statistics_byday
-- ----------------------------
DROP TABLE IF EXISTS `tb_order_overview_statistics_byday`;
CREATE TABLE `tb_order_overview_statistics_byday` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `create_at` int(11) DEFAULT '0',
  `stage_type` tinyint(4) DEFAULT '0' COMMENT '是否是分期  0不是 1是',
  `new_amount` int(11) DEFAULT '0' COMMENT '入催订单数',
  `new_principal` int(11) DEFAULT '0' COMMENT '入催订单本金',
  `repay_principal` int(11) DEFAULT '0' COMMENT '还款本金，单位‘分’',
  `repay_amount` int(11) DEFAULT '0' COMMENT '还款订单数',
  `repay_late_fee` int(11) DEFAULT '0' COMMENT '实际还款滞纳金，单位分',
  `merchant_id` int(11) DEFAULT NULL COMMENT '商户ID',
  `update_at` int(11) DEFAULT '0',
  `sub_from` tinyint(4) NOT NULL DEFAULT '1' COMMENT '项目来源',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `sub_from` (`sub_from`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_order_overview_statistics_bygroup
-- ----------------------------
DROP TABLE IF EXISTS `tb_order_overview_statistics_bygroup`;
CREATE TABLE `tb_order_overview_statistics_bygroup` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `create_at` int(11) DEFAULT '0',
  `stage_type` tinyint(4) DEFAULT '0' COMMENT '是否是分期  0不是 1是',
  `amount` int(11) DEFAULT '0' COMMENT '订单数',
  `principal` bigint(20) DEFAULT '0' COMMENT '本金，单位分',
  `overdue_fee` bigint(20) DEFAULT '0' COMMENT '实际滞纳金，单位分',
  `true_overdue_fee` bigint(20) DEFAULT '0' COMMENT '应还滞纳金，单位分',
  `group` int(5) DEFAULT '0' COMMENT '催收分组，S1，S2，M1M2，M2M3，M3+',
  `order_status` int(11) DEFAULT '0',
  `update_at` int(11) DEFAULT '0',
  `sub_from` tinyint(4) DEFAULT '1' COMMENT '项目来源',
  `merchant_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  KEY `sub_from` (`sub_from`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_order_overview_statistics_byrate
-- ----------------------------
DROP TABLE IF EXISTS `tb_order_overview_statistics_byrate`;
CREATE TABLE `tb_order_overview_statistics_byrate` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `create_at` int(11) NOT NULL DEFAULT '0' COMMENT '入催时间',
  `update_at` int(11) DEFAULT '0',
  `stage_type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否分期  0不是  1 是',
  `sub_from` tinyint(4) NOT NULL DEFAULT '1' COMMENT '项目来源',
  `deadline_amount` bigint(20) NOT NULL DEFAULT '0' COMMENT '今日到期应还本金',
  `collection_amount` bigint(20) NOT NULL DEFAULT '0' COMMENT '今日入催本金',
  `merchant_id` int(11) DEFAULT NULL COMMENT '商户ID',
  `repay_1_amount` int(11) NOT NULL DEFAULT '0' COMMENT '逾期1天催回本金',
  `repay_2_amount` int(11) NOT NULL DEFAULT '0' COMMENT '逾期2天催回本金',
  `repay_3_amount` int(11) NOT NULL DEFAULT '0' COMMENT '逾期3天催回本金',
  `repay_4_amount` int(11) NOT NULL DEFAULT '0' COMMENT '逾期4天催回本金',
  `repay_5_amount` int(11) NOT NULL DEFAULT '0' COMMENT '逾期5天催回本金',
  `repay_6_amount` int(11) NOT NULL DEFAULT '0' COMMENT '逾期6天催回本金',
  `repay_7_amount` int(11) NOT NULL DEFAULT '0' COMMENT '逾期7天催回本金',
  `repay_10_amount` int(11) NOT NULL DEFAULT '0' COMMENT '逾期8~10天催回本金',
  `repay_30_amount` int(11) NOT NULL DEFAULT '0' COMMENT '逾期11~15天催回本金',
  `repay_20_amount` int(11) NOT NULL DEFAULT '0' COMMENT '逾期16~20天催回本金',
  `repay_21_amount` int(11) NOT NULL DEFAULT '0' COMMENT '逾期21~30天催回本金',
  `repay_60_amount` int(11) NOT NULL DEFAULT '0' COMMENT '逾期31~60天催回本金',
  `repay_90_amount` int(11) NOT NULL DEFAULT '0' COMMENT '逾期61~90天催回本金',
  `repay_999_amount` int(11) NOT NULL DEFAULT '0' COMMENT '逾期91天及以上催回本金',
  `s1_collection_amount` int(11) NOT NULL DEFAULT '0' COMMENT 's1入催本金',
  `s2_collection_amount` int(11) NOT NULL DEFAULT '0' COMMENT 's2入催本金',
  `m1_collection_amount` int(11) NOT NULL DEFAULT '0' COMMENT 'm1入催本金',
  `m2_collection_amount` int(11) NOT NULL DEFAULT '0' COMMENT 'm2入催本金',
  `m3_collection_amount` int(11) NOT NULL DEFAULT '0' COMMENT 'm3入催本金',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `sub_from` (`sub_from`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_order_overview_statistics_bystage
-- ----------------------------
DROP TABLE IF EXISTS `tb_order_overview_statistics_bystage`;
CREATE TABLE `tb_order_overview_statistics_bystage` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `overdue_level` int(11) DEFAULT '0' COMMENT '逾期等级',
  `stage_type` tinyint(4) DEFAULT '0' COMMENT '是否是分期  0不是 1是',
  `principal_1000` bigint(20) DEFAULT '0' COMMENT '0~1000(包含)的应还本金',
  `true_principal_1000` bigint(20) DEFAULT '0' COMMENT '0~1000(包含)的实际本金',
  `principal_1500` bigint(20) DEFAULT '0',
  `true_principal_1500` bigint(20) DEFAULT '0',
  `principal_2000` bigint(20) DEFAULT '0',
  `true_principal_2000` bigint(20) DEFAULT '0',
  `principal_2500` bigint(20) DEFAULT '0',
  `true_principal_2500` bigint(20) DEFAULT '0',
  `principal_3000` bigint(20) DEFAULT '0',
  `true_principal_3000` bigint(20) DEFAULT '0',
  `principal_3000plus` bigint(20) DEFAULT '0',
  `true_principal_3000plus` bigint(20) DEFAULT '0',
  `create_at` int(11) DEFAULT '0',
  `update_at` int(11) DEFAULT '0',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_order_overview_statistics_bystatus
-- ----------------------------
DROP TABLE IF EXISTS `tb_order_overview_statistics_bystatus`;
CREATE TABLE `tb_order_overview_statistics_bystatus` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `create_at` int(11) DEFAULT '0',
  `order_status` int(5) DEFAULT '0' COMMENT '订单状态类型，催收中，承诺还款，催收成功',
  `stage_type` tinyint(4) DEFAULT '0' COMMENT '是否是分期  0不是 1 是',
  `amount` int(11) DEFAULT '0' COMMENT '订单数',
  `principal` bigint(20) DEFAULT '0' COMMENT '本金，单位‘分’',
  `true_overdue_fee` bigint(20) DEFAULT '0' COMMENT '实际滞纳金，单位分',
  `overdue_fee` bigint(20) DEFAULT '0' COMMENT '应还滞纳金，单位分',
  `update_at` int(11) DEFAULT '0',
  `sub_from` tinyint(4) NOT NULL DEFAULT '1' COMMENT '项目来源',
  `merchant_id` int(11) DEFAULT NULL COMMENT '商户ID',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `sub_from` (`sub_from`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_order_statistic
-- ----------------------------
DROP TABLE IF EXISTS `tb_order_statistic`;
CREATE TABLE `tb_order_statistic` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `date` date DEFAULT NULL COMMENT '日期',
  `loan_num` int(11) DEFAULT '0' COMMENT '入催单数',
  `repay_num` int(11) DEFAULT '0' COMMENT '出催单数',
  `merchant_id` int(11) DEFAULT NULL COMMENT '商户ID',
  `created_at` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updated_at` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `date` (`date`) USING BTREE,
  KEY `merchant_id` (`merchant_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COMMENT='订单分布统计';

-- ----------------------------
-- Table structure for tb_outside_day_data
-- ----------------------------
DROP TABLE IF EXISTS `tb_outside_day_data`;
CREATE TABLE `tb_outside_day_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `date` date DEFAULT NULL COMMENT '日期',
  `outside` int(11) NOT NULL DEFAULT '0' COMMENT '机构，0则未分配机构的',
  `total_finish_num` int(11) NOT NULL DEFAULT '0' COMMENT '总完成单数',
  `total_finish_amount` int(11) NOT NULL DEFAULT '0' COMMENT '总完成应还金额',
  `current_progress_num` int(11) NOT NULL DEFAULT '0' COMMENT '目前进行中单数',
  `current_progress_amount` int(11) NOT NULL DEFAULT '0' COMMENT '目前进行中金额',
  `today_dispatch_num` int(11) NOT NULL DEFAULT '0' COMMENT '当天分配的订单数',
  `today_dispatch_amount` int(11) NOT NULL DEFAULT '0' COMMENT '当天分配订单金额',
  `today_finish_num` int(11) NOT NULL DEFAULT '0' COMMENT '当天完成订单数',
  `today_finish_amount` int(11) NOT NULL DEFAULT '0' COMMENT '当天完成订金额',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `date` (`date`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_script_task_log
-- ----------------------------
DROP TABLE IF EXISTS `tb_script_task_log`;
CREATE TABLE `tb_script_task_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `script_type` tinyint(3) unsigned DEFAULT NULL COMMENT '脚本类型',
  `exec_status` tinyint(4) DEFAULT '0' COMMENT '执行状态',
  `operator_id` int(11) unsigned DEFAULT NULL COMMENT '执行计划创建人',
  `exec_start_time` int(11) unsigned DEFAULT NULL COMMENT '执行开始时间',
  `exec_end_time` int(11) unsigned DEFAULT NULL COMMENT '执行结束时间',
  `created_at` int(11) unsigned DEFAULT NULL,
  `updated_at` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='脚本执行日志';

-- ----------------------------
-- Table structure for tb_sms_template
-- ----------------------------
DROP TABLE IF EXISTS `tb_sms_template`;
CREATE TABLE `tb_sms_template` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `merchant_id` tinyint(4) DEFAULT NULL,
  `package_name` varchar(50) DEFAULT '',
  `content` text CHARACTER SET utf8 COLLATE utf8_general_ci,
  `can_send_group` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `can_send_outside` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `is_use` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for tb_stop_regain_input_order
-- ----------------------------
DROP TABLE IF EXISTS `tb_stop_regain_input_order`;
CREATE TABLE `tb_stop_regain_input_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `collection_order_id` int(11) DEFAULT NULL,
  `loan_order_id` int(11) DEFAULT NULL,
  `loan_repayment_id` int(11) DEFAULT NULL,
  `operator_id` int(11) DEFAULT NULL,
  `status` tinyint(4) DEFAULT NULL,
  `next_input_time` int(11) DEFAULT '0',
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `collection_order_id` (`collection_order_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='停催重新待入催表';

SET FOREIGN_KEY_CHECKS = 1;

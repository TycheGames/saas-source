<?php
/**
 * Created by loan
 * User: wangpeng
 * Date: 2019/7/5 0005
 * Time：17:05
 */


namespace common\services\stats;

use common\models\order\UserLoanOrder;
use common\models\order\UserRepaymentLog;
use common\models\stats\DailyTradeData;

class HourLoanRepayDataService extends StatsBaseService
{


    public function getTradeData($start_time, $end_time){
        $data = [];
        $date = date('Y-m-d',$start_time);
        echo $date."\n";
        $read_db = $this->db;

        //统计注册量

        $regSql = "select count(l.id) as countnum, 
            FROM_UNIXTIME(l.created_at,'%H') as hours,
            reg.appMarket,
            clg.package_name,
            l.merchant_id
            from tb_loan_person as l
            LEFT JOIN tb_user_register_info as reg on l.id = reg.user_id
            LEFT JOIN tb_client_info_log as clg on l.id = clg.event_id AND clg.event = 1
            where l.created_at>={$start_time}
            and l.created_at<{$end_time}
            GROUP BY l.merchant_id,hours,reg.appMarket,clg.package_name
            order by hours asc";
        $all_reg = $read_db->createCommand($regSql)->queryAll();
        foreach ($all_reg as $value) {
            $merchant_id = $value['merchant_id'];
            $app_market =$value['appMarket'];
            $packageName =$value['package_name'];
            $hour =sprintf ( "%02d",$value['hours']+1);
            $user_type = 0;
            $data[$merchant_id][$hour][$user_type][$packageName][$app_market]['reg_num'] = $value['countnum'] ?? 0;
        }


        //统计转人工订单数

        $manualSql = "select count(lg.order_id) as countnum, 
            FROM_UNIXTIME(lg.created_at,'%H') as hours,
            reg.appMarket,
            IF(o.is_export = 1,substring_index(substring_index(clg.app_market,'_',2),'_',-1),clg.package_name) as packageName,
            o.merchant_id
            from tb_user_order_loan_check_log lg 
            left join tb_user_loan_order o on lg.order_id = o.id
            LEFT JOIN tb_user_register_info as reg on lg.user_id = reg.user_id
            LEFT JOIN tb_client_info_log as clg on o.id = clg.event_id AND clg.event = 11
            where lg.created_at>={$start_time}
            and lg.created_at<{$end_time}
            and lg.before_audit_status = " . UserLoanOrder::AUDIT_STATUS_AUTO_CHECK ." 
            and lg.after_audit_status = " . UserLoanOrder::AUDIT_STATUS_GET_ORDER ." 
            and lg.after_status = " . UserLoanOrder::STATUS_CHECK ."
            GROUP BY o.merchant_id,hours,reg.appMarket,packageName
            order by hours asc";
        $all_manual = $read_db->createCommand($manualSql)->queryAll();
        foreach ($all_manual as $value) {
            $merchant_id = $value['merchant_id'];
            $app_market =$value['appMarket'];
            $packageName =$value['packageName'];
            $hour =sprintf ( "%02d",$value['hours']+1);
            $user_type = 0;
            $data[$merchant_id][$hour][$user_type][$packageName][$app_market]['manual_order_num'] = $value['countnum'] ?? 0;
        }

        //统计人工审核单数
        $manualSql = "select count(lg.order_id) as countnum, 
            FROM_UNIXTIME(lg.created_at,'%H') as hours,
            reg.appMarket,
            IF(o.is_export = 1,substring_index(substring_index(clg.app_market,'_',2),'_',-1),clg.package_name) as packageName,
            o.merchant_id
            from tb_user_order_loan_check_log lg 
            left join tb_user_loan_order o on lg.order_id = o.id
            LEFT JOIN tb_user_register_info as reg on lg.user_id = reg.user_id
            LEFT JOIN tb_client_info_log as clg on o.id = clg.event_id AND clg.event = 11
            where lg.created_at>={$start_time}
            and lg.created_at<{$end_time}
            and lg.before_audit_status = " . UserLoanOrder::AUDIT_STATUS_MANUAL_CHECK ." 
            and lg.after_audit_status = " . UserLoanOrder::AUDIT_STATUS_MANUAL_CHECK_FINISH ." 
            and lg.before_status = " . UserLoanOrder::STATUS_CHECK ."
            GROUP BY o.merchant_id,hours,reg.appMarket,packageName
            order by hours asc";


        $all_manual = $read_db->createCommand($manualSql)->queryAll();
        foreach ($all_manual as $value) {
            $merchant_id = $value['merchant_id'];
            $app_market =$value['appMarket'];
            $packageName =$value['packageName'];
            $hour =sprintf ( "%02d",$value['hours']+1);
            $user_type = 0;
            $data[$merchant_id][$hour][$user_type][$packageName][$app_market]['manual_num'] = $value['countnum'] ?? 0;
        }

        //所有用户申请人数，申请金额

        $all_apply_sql = "select
            count(DISTINCT o.user_id) as countnum,
            sum(o.amount-o.cost_fee) as money,
            o.is_first,
            o.is_all_first,
            FROM_UNIXTIME(o.created_at,'%H') as hours,
            reg.appMarket,
            IF(o.is_export = 1,substring_index(substring_index(clg.app_market,'_',2),'_',-1),clg.package_name) as packageName,
            o.merchant_id
            from tb_user_loan_order as o
            LEFT JOIN tb_user_register_info as reg on o.user_id = reg.user_id
            LEFT JOIN tb_client_info_log as clg on o.id = clg.event_id AND clg.event = 11
            where o.created_at>={$start_time}
            and o.created_at<{$end_time}
            GROUP BY o.merchant_id,o.is_first,o.is_all_first,hours,reg.appMarket,packageName
            order by hours asc";

        $all_apply = $read_db->createCommand($all_apply_sql)->queryAll();
        foreach ($all_apply as $value) {
            $merchant_id = $value['merchant_id'];
            $app_market =$value['appMarket'];
            $packageName =$value['packageName'];
            $hour =sprintf ( "%02d",$value['hours']+1);

            $list = self::getUserTypeList($value['is_first'],$value['is_all_first']);
            foreach ($list as $user_type){
                if(isset($data[$merchant_id][$hour][$user_type][$packageName][$app_market]['apply_money'])){
                    $data[$merchant_id][$hour][$user_type][$packageName][$app_market]['apply_money'] += $value['money']??0;
                    $data[$merchant_id][$hour][$user_type][$packageName][$app_market]['apply_num'] += $value['countnum']??0;
                }else{
                    $data[$merchant_id][$hour][$user_type][$packageName][$app_market]['apply_money'] = $value['money']??0;
                    $data[$merchant_id][$hour][$user_type][$packageName][$app_market]['apply_num'] = $value['countnum']??0;
                }
            }
        }

        //所有用户风控通过人数
        $all_apply_check_sql = "select
            count(DISTINCT o.user_id) as countnum,
            o.is_first,
            o.is_all_first,
            FROM_UNIXTIME(l.created_at,'%H') as hours,
            reg.appMarket,
            IF(o.is_export = 1,substring_index(substring_index(clg.app_market,'_',2),'_',-1),clg.package_name) as packageName,
            o.merchant_id
            from tb_user_loan_order as o
            LEFT JOIN tb_user_register_info as reg on o.user_id = reg.user_id
            LEFT JOIN tb_user_order_loan_check_log AS l ON l.order_id = o.id
            LEFT JOIN tb_client_info_log as clg on o.id = clg.event_id AND clg.event = 11
            where l.created_at>={$start_time}
            and l.created_at<{$end_time}
            AND l.before_status = ".UserLoanOrder::STATUS_CHECK."
            AND l.after_status in (".UserLoanOrder::STATUS_WAIT_DEPOSIT.",".UserLoanOrder::STATUS_LOANING.",".UserLoanOrder::STATUS_WAIT_DRAW_MONEY.") 
            GROUP BY o.merchant_id,o.is_first,o.is_all_first,hours,reg.appMarket,packageName
            order by hours asc";

        $all_apply_check = $read_db->createCommand($all_apply_check_sql)->queryAll();
        foreach ($all_apply_check as $value) {
            $merchant_id = $value['merchant_id'];
            $app_market =$value['appMarket'];
            $packageName =$value['packageName'];
            $hour =sprintf ( "%02d",$value['hours']+1);

            $list = self::getUserTypeList($value['is_first'],$value['is_all_first']);
            foreach ($list as $user_type){
                if(isset($data[$merchant_id][$hour][$user_type][$packageName][$app_market]['apply_check_num'])){
                    $data[$merchant_id][$hour][$user_type][$packageName][$app_market]['apply_check_num'] += $value['countnum']??0;
                }else{
                    $data[$merchant_id][$hour][$user_type][$packageName][$app_market]['apply_check_num'] = $value['countnum']??0;
                }
            }
        }

        //所有用户放款单数，放款金额
        $all_loan_sql = "select count(DISTINCT r.user_id) as countnum,
            sum(r.principal-r.cost_fee) as money,
            o.is_first,
            o.is_all_first,
            FROM_UNIXTIME(r.loan_time,'%H') as hours,
            reg.appMarket,
            IF(o.is_export = 1,substring_index(substring_index(clg.app_market,'_',2),'_',-1),clg.package_name) as packageName,
            r.merchant_id
            from tb_user_loan_order_repayment as r
            LEFT JOIN tb_user_register_info as reg on r.user_id = reg.user_id
            LEFT join tb_user_loan_order as o on r.order_id = o.id
            LEFT JOIN tb_client_info_log as clg on o.id = clg.event_id AND clg.event = 11
            where o.id>0
            and r.loan_time>={$start_time}
            and r.loan_time<{$end_time}
            GROUP BY r.merchant_id,o.is_first,o.is_all_first,hours,reg.appMarket,packageName
            order by hours asc";

        $all_loan = $read_db->createCommand($all_loan_sql)->queryAll();
        foreach ($all_loan as $k=>$value) {
            $merchant_id = $value['merchant_id'];
            $hour =sprintf ( "%02d",$value['hours']+1);
            $app_market =$value['appMarket'];
            $packageName =$value['packageName'];

            $list = self::getUserTypeList($value['is_first'],$value['is_all_first']);
            foreach ($list as $user_type){
                if(isset($data[$merchant_id][$hour][$user_type][$packageName][$app_market]['loan_money'])){
                    $data[$merchant_id][$hour][$user_type][$packageName][$app_market]['loan_money'] += $value['money']??0;
                    $data[$merchant_id][$hour][$user_type][$packageName][$app_market]['loan_num'] += $value['countnum']??0;
                }else{
                    $data[$merchant_id][$hour][$user_type][$packageName][$app_market]['loan_money'] = $value['money']??0;
                    $data[$merchant_id][$hour][$user_type][$packageName][$app_market]['loan_num'] = $value['countnum']??0;
                }
            }
        }

        //所有用户放款金额(当日申请)
        $all_loan_sql = "select 
            sum(r.principal-r.cost_fee) as money,
            FROM_UNIXTIME(r.loan_time,'%H') as hours,
            reg.appMarket,
            IF(o.is_export = 1,substring_index(substring_index(clg.app_market,'_',2),'_',-1),clg.package_name) as packageName,
            r.merchant_id
            from tb_user_loan_order_repayment as r
            LEFT JOIN tb_user_register_info as reg on r.user_id = reg.user_id
            LEFT join tb_user_loan_order as o on r.order_id = o.id
            LEFT JOIN tb_client_info_log as clg on o.id = clg.event_id AND clg.event = 11
            where r.loan_time>={$start_time}
            and r.loan_time<{$end_time}
            and o.created_at>={$start_time}
            and o.created_at<{$end_time}
            GROUP BY r.merchant_id,hours,reg.appMarket,packageName
            order by hours asc";
        $all_loan_today = $read_db->createCommand($all_loan_sql)->queryAll();
        foreach ($all_loan_today as $k=>$value) {
            $merchant_id = $value['merchant_id'];
            $hour =sprintf ( "%02d",$value['hours']+1);
            $app_market =$value['appMarket'];
            $packageName =$value['packageName'];
            $user_type = 0;
            $data[$merchant_id][$hour][$user_type][$packageName][$app_market]['loan_money_today'] = $value['money'] ?? 0;
        }

        //今日到期单数 到期金额
        $all_repay_sql = "select count(r.order_id) as countnum,
            sum(r.principal + r.interests) as money,
            sum(r.cost_fee) as fee, 
            sum(r.interests) as interests,
            o.is_first,
            o.is_all_first,
            reg.appMarket,
            IF(o.is_export = 1,substring_index(substring_index(clg.app_market,'_',2),'_',-1),clg.package_name) as packageName,
            r.merchant_id
            from tb_user_loan_order_repayment as r
            LEFT JOIN tb_user_register_info as reg on r.user_id = reg.user_id
            LEFT join tb_user_loan_order as o on r.order_id=o.id
            LEFT JOIN tb_client_info_log as clg on o.id = clg.event_id AND clg.event = 11
            where r.plan_repayment_time>={$start_time} 
            and r.plan_repayment_time<{$end_time}
            GROUP BY r.merchant_id,o.is_first,o.is_all_first,reg.appMarket,packageName";

        $all_repays = $read_db->createCommand($all_repay_sql)->queryAll();
        foreach ($all_repays as $value) {
            $merchant_id = $value['merchant_id'];
            $app_market =$value['appMarket'];
            $packageName =$value['packageName'];
            $hour = '00';

            $list = self::getUserTypeList($value['is_first'],$value['is_all_first']);
            foreach ($list as $user_type){
                if(isset($data[$merchant_id][$hour][$user_type][$packageName][$app_market]['repays_money'])){
                    $data[$merchant_id][$hour][$user_type][$packageName][$app_market]['repays_money']  += $value['money'];
                    $data[$merchant_id][$hour][$user_type][$packageName][$app_market]['fee']  += $value['fee'];
                    $data[$merchant_id][$hour][$user_type][$packageName][$app_market]['interests']  += $value['interests'];
                }else{
                    $data[$merchant_id][$hour][$user_type][$packageName][$app_market]['repays_money']  = $value['money'];
                    $data[$merchant_id][$hour][$user_type][$packageName][$app_market]['fee']  = $value['fee'];
                    $data[$merchant_id][$hour][$user_type][$packageName][$app_market]['interests']  = $value['interests'];
                }
            }
        }

        //所有提前还款单数 金额  流水表
        $all_repay_up_sql = "select count(distinct m.order_id) as countnum, 
            sum(m.amount) as money,
            o.is_first,
            o.is_all_first,
            reg.appMarket,
            IF(o.is_export = 1,substring_index(substring_index(clg.app_market,'_',2),'_',-1),clg.package_name) as packageName,
            r.merchant_id
            from tb_user_repayment_log as m
            LEFT join tb_user_loan_order as o on m.order_id=o.id
            LEFT JOIN tb_user_register_info as reg on m.user_id = reg.user_id
            left join tb_user_loan_order_repayment as r on r.order_id = m.order_id
            LEFT JOIN tb_client_info_log as clg on o.id = clg.event_id AND clg.event = 11
            where r.plan_repayment_time >= {$start_time}
            and r.plan_repayment_time < {$end_time}
            and m.success_time < {$start_time}
            GROUP BY r.merchant_id,o.is_first,o.is_all_first,reg.appMarket,packageName";

        $all_repay_up = $read_db->createCommand($all_repay_up_sql)->queryAll();
        foreach($all_repay_up as $value){
            $merchant_id = $value['merchant_id'];
            $app_market =$value['appMarket'];
            $packageName =$value['packageName'];
            $hour = '00';

            $list = self::getUserTypeList($value['is_first'],$value['is_all_first']);
            foreach ($list as $user_type){
                if(isset($data[$merchant_id][$hour][$user_type][$packageName][$app_market]['repayment_money'])){
                    $data[$merchant_id][$hour][$user_type][$packageName][$app_market]['repayment_money'] += $value['money'];
                    $data[$merchant_id][$hour][$user_type][$packageName][$app_market]['repayment_num'] += $value['countnum'];
                }else{
                    $data[$merchant_id][$hour][$user_type][$packageName][$app_market]['repayment_money'] = $value['money'];
                    $data[$merchant_id][$hour][$user_type][$packageName][$app_market]['repayment_num'] = $value['countnum'];
                }
            }
        }

        // 下一天的还款数
        $all_repay_up_sql = "select count(distinct m.order_id) as countnum, 
            sum(m.amount) as money,
            o.is_first,
            o.is_all_first,
            reg.appMarket,
            IF(o.is_export = 1,substring_index(substring_index(clg.app_market,'_',2),'_',-1),clg.package_name) as packageName,
            r.merchant_id
            from tb_user_repayment_log as m
            LEFT join tb_user_loan_order as o on m.order_id=o.id
            LEFT JOIN tb_user_register_info as reg on m.user_id = reg.user_id
            left join tb_user_loan_order_repayment as r on r.order_id = m.order_id
            LEFT JOIN tb_client_info_log as clg on o.id = clg.event_id AND clg.event = 11
            where r.plan_repayment_time >= {$start_time} + 86400
            and r.plan_repayment_time < {$end_time} + 86400
            and m.success_time < {$start_time}
            GROUP BY r.merchant_id,o.is_first,o.is_all_first,reg.appMarket,packageName";
        $all_repay_up = $read_db->createCommand($all_repay_up_sql)->queryAll();
        foreach($all_repay_up as $value){
            $merchant_id = $value['merchant_id'];
            $app_market =$value['appMarket'];
            $packageName =$value['packageName'];
            $hour = '00';

            $list = self::getUserTypeList($value['is_first'],$value['is_all_first']);
            foreach ($list as $user_type){
                if(isset($data[$merchant_id][$hour][$user_type][$packageName][$app_market]['repayment_money_tomorrow'])){
                    $data[$merchant_id][$hour][$user_type][$packageName][$app_market]['repayment_money_tomorrow'] += $value['money'];
                    $data[$merchant_id][$hour][$user_type][$packageName][$app_market]['repayment_num_tomorrow'] += $value['countnum'];
                }else{
                    $data[$merchant_id][$hour][$user_type][$packageName][$app_market]['repayment_money_tomorrow'] = $value['money'];
                    $data[$merchant_id][$hour][$user_type][$packageName][$app_market]['repayment_num_tomorrow'] = $value['countnum'];
                }
            }
        }


        // 提前还款中 - 主动还款 单独查询
        $before_active_sql = "select count(distinct m.order_id) as active_num,
            o.is_first,
            o.is_all_first,
            reg.appMarket,
            IF(o.is_export = 1,substring_index(substring_index(clg.app_market,'_',2),'_',-1),clg.package_name) as packageName,
            r.merchant_id
            from tb_user_repayment_log as m
            left join tb_user_loan_order_repayment as r on r.order_id = m.order_id
            left join tb_user_loan_order as o on o.id = m.order_id
            LEFT JOIN tb_user_register_info as reg on m.user_id = reg.user_id
            LEFT JOIN tb_client_info_log as clg on o.id = clg.event_id AND clg.event = 11
            where r.plan_repayment_time >= {$start_time}
            and r.plan_repayment_time<{$end_time}
            and m.success_time < {$start_time}
            and m.type = ".UserRepaymentLog::TYPE_ACTIVE."
            GROUP BY r.merchant_id,o.is_first,o.is_all_first,reg.appMarket,packageName";

        $all_repay_active = $read_db->createCommand($before_active_sql)->queryAll();

        foreach ($all_repay_active as $value) {
            $merchant_id = $value['merchant_id'];
            $app_market =$value['appMarket'];
            $packageName =$value['packageName'];
            $hour = '00';

            $list = self::getUserTypeList($value['is_first'],$value['is_all_first']);
            foreach ($list as $user_type){
                if(isset($data[$merchant_id][$hour][$user_type][$packageName][$app_market]['active_repayment'])){
                    $data[$merchant_id][$hour][$user_type][$packageName][$app_market]['active_repayment'] += $value['active_num'];
                }else{
                    $data[$merchant_id][$hour][$user_type][$packageName][$app_market]['active_repayment'] = $value['active_num'];
                }
            }
        }

        $before_active_sql = "select count(distinct m.order_id) as active_num,
            o.is_first,
            o.is_all_first,
            reg.appMarket,
            IF(o.is_export = 1,substring_index(substring_index(clg.app_market,'_',2),'_',-1),clg.package_name) as packageName,
            r.merchant_id
            from tb_user_repayment_log as m
            left join tb_user_loan_order_repayment as r on r.order_id = m.order_id
            left join tb_user_loan_order as o on o.id = m.order_id
            LEFT JOIN tb_user_register_info as reg on m.user_id = reg.user_id
            LEFT JOIN tb_client_info_log as clg on o.id = clg.event_id AND clg.event = 11
            where r.plan_fee_time >= {$start_time} + 86400
            and r.plan_fee_time<{$end_time} + 86400
            and m.success_time < {$start_time}
            and m.type = ".UserRepaymentLog::TYPE_ACTIVE."
            GROUP BY r.merchant_id,o.is_first,o.is_all_first,reg.appMarket,packageName";
        $all_repay_active = $read_db->createCommand($before_active_sql)->queryAll();

        foreach ($all_repay_active as $value) {
            $merchant_id = $value['merchant_id'];
            $app_market =$value['appMarket'];
            $packageName =$value['packageName'];
            $hour = '00';

            $list = self::getUserTypeList($value['is_first'],$value['is_all_first']);
            foreach ($list as $user_type){
                if(isset($data[$merchant_id][$hour][$user_type][$packageName][$app_market]['active_repayment_tomorrow'])){
                    $data[$merchant_id][$hour][$user_type][$packageName][$app_market]['active_repayment_tomorrow'] += $value['active_num'];
                }else{
                    $data[$merchant_id][$hour][$user_type][$packageName][$app_market]['active_repayment_tomorrow'] = $value['active_num'];
                }
            }
        }


        //所有到期 在当日还款单数 还款金额
        $all_repay_sql = "select count(distinct m.order_id) as countnum,
            sum(m.amount) as money,
            o.is_first,
            o.is_all_first,
            FROM_UNIXTIME(m.success_time,'%H') as hours,
            reg.appMarket,
            IF(o.is_export = 1,substring_index(substring_index(clg.app_market,'_',2),'_',-1),clg.package_name) as packageName,
            r.merchant_id
            from tb_user_repayment_log as m
            LEFT join tb_user_loan_order as o on m.order_id=o.id
            left join tb_user_loan_order_repayment as r on r.order_id = m.order_id
            LEFT JOIN tb_user_register_info as reg on m.user_id = reg.user_id
            LEFT JOIN tb_client_info_log as clg on o.id = clg.event_id AND clg.event = 11
            where r.plan_repayment_time >= {$start_time}
            and r.plan_repayment_time < {$end_time}
            and m.success_time >= {$start_time}
            and m.success_time < {$end_time}
            GROUP BY r.merchant_id,o.is_first,o.is_all_first, hours,reg.appMarket,packageName
            ORDER by hours asc";

        $all_repay = $read_db->createCommand($all_repay_sql)->queryAll();

        foreach ($all_repay as $value) {
            $merchant_id = $value['merchant_id'];
            $app_market =$value['appMarket'];
            $packageName =$value['packageName'];
            $hour =sprintf ( "%02d",$value['hours']+1);

            $list = self::getUserTypeList($value['is_first'],$value['is_all_first']);
            foreach ($list as $user_type){
                if(isset($data[$merchant_id][$hour][$user_type][$packageName][$app_market]['repayment_money'])){
                    $data[$merchant_id][$hour][$user_type][$packageName][$app_market]['repayment_money'] += $value['money'];
                    $data[$merchant_id][$hour][$user_type][$packageName][$app_market]['repayment_num'] += $value['countnum'];
                }else{
                    $data[$merchant_id][$hour][$user_type][$packageName][$app_market]['repayment_money'] = $value['money'];
                    $data[$merchant_id][$hour][$user_type][$packageName][$app_market]['repayment_num'] = $value['countnum'];
                }
            }
        }


        $all_repay_sql = "select count(distinct m.order_id) as countnum,
            sum(m.amount) as money,
            o.is_first,
            o.is_all_first,
            FROM_UNIXTIME(m.success_time,'%H') as hours,
            reg.appMarket,
            IF(o.is_export = 1,substring_index(substring_index(clg.app_market,'_',2),'_',-1),clg.package_name) as packageName,
            r.merchant_id
            from tb_user_repayment_log as m
            LEFT join tb_user_loan_order as o on m.order_id=o.id
            left join tb_user_loan_order_repayment as r on r.order_id = m.order_id
            LEFT JOIN tb_user_register_info as reg on m.user_id = reg.user_id
            LEFT JOIN tb_client_info_log as clg on o.id = clg.event_id AND clg.event = 11
            where r.plan_repayment_time >= {$start_time} + 86400
            and r.plan_repayment_time < {$end_time} + 86400
            and m.success_time >= {$start_time}
            and m.success_time < {$end_time}
            GROUP BY r.merchant_id,o.is_first,o.is_all_first, hours,reg.appMarket,packageName
            ORDER by hours asc";
        $all_repay = $read_db->createCommand($all_repay_sql)->queryAll();
        foreach ($all_repay as $value) {
            $merchant_id = $value['merchant_id'];
            $app_market =$value['appMarket'];
            $packageName =$value['packageName'];
            $hour =sprintf ( "%02d",$value['hours']+1);

            $list = self::getUserTypeList($value['is_first'],$value['is_all_first']);
            foreach ($list as $user_type){
                if(isset($data[$merchant_id][$hour][$user_type][$packageName][$app_market]['repayment_money_tomorrow'])){
                    $data[$merchant_id][$hour][$user_type][$packageName][$app_market]['repayment_money_tomorrow'] += $value['money'];
                    $data[$merchant_id][$hour][$user_type][$packageName][$app_market]['repayment_num_tomorrow'] += $value['countnum'];
                }else{
                    $data[$merchant_id][$hour][$user_type][$packageName][$app_market]['repayment_money_tomorrow'] = $value['money'];
                    $data[$merchant_id][$hour][$user_type][$packageName][$app_market]['repayment_num_tomorrow'] = $value['countnum'];
                }
            }
        }


        //所有到期还款单数 还款金额  主动还款数量单独查询
        $all_repay_sql = "select count(distinct m.order_id) as countnum,
            o.is_first,
            o.is_all_first,
            FROM_UNIXTIME(m.success_time,'%H') as hours,
            reg.appMarket,
            IF(o.is_export = 1,substring_index(substring_index(clg.app_market,'_',2),'_',-1),clg.package_name) as packageName,
            r.merchant_id
            from tb_user_repayment_log as m
            LEFT join tb_user_loan_order as o on m.order_id=o.id
            left join tb_user_loan_order_repayment as r on r.order_id = m.order_id
            LEFT join tb_loan_person as l on m.user_id = l.id
            LEFT JOIN tb_user_register_info as reg on m.user_id = reg.user_id
            LEFT JOIN tb_client_info_log as clg on o.id = clg.event_id AND clg.event = 11
            where r.plan_repayment_time >= {$start_time}
            and r.plan_repayment_time < {$end_time}
            and m.success_time >= {$start_time}
            and m.success_time < {$end_time}
            and m.type = ".UserRepaymentLog::TYPE_ACTIVE."
            GROUP BY r.merchant_id,o.is_first,o.is_all_first, hours,reg.appMarket,packageName
            ORDER by hours asc";

        $all_repay = $read_db->createCommand($all_repay_sql)->queryAll();
        foreach ($all_repay as $key => $value) {
            $merchant_id = $value['merchant_id'];
            $app_market =$value['appMarket'];
            $packageName =$value['packageName'];
            $hour =sprintf ( "%02d",$value['hours']+1);

            $list = self::getUserTypeList($value['is_first'],$value['is_all_first']);
            foreach ($list as $user_type){
                if(isset($data[$merchant_id][$hour][$user_type][$packageName][$app_market]['active_repayment'])){
                    $data[$merchant_id][$hour][$user_type][$packageName][$app_market]['active_repayment'] += $value['countnum'];
                }else{
                    $data[$merchant_id][$hour][$user_type][$packageName][$app_market]['active_repayment'] = $value['countnum'];
                }
            }
        }

        $all_repay_sql = "select count(distinct m.order_id) as countnum,
            o.is_first,
            o.is_all_first,
            FROM_UNIXTIME(m.success_time,'%H') as hours,
            reg.appMarket,
            IF(o.is_export = 1,substring_index(substring_index(clg.app_market,'_',2),'_',-1),clg.package_name) as packageName,
            r.merchant_id
            from tb_user_repayment_log as m
            LEFT join tb_user_loan_order as o on m.order_id=o.id
            left join tb_user_loan_order_repayment as r on r.order_id = m.order_id
            LEFT JOIN tb_user_register_info as reg on m.user_id = reg.user_id
            LEFT JOIN tb_client_info_log as clg on o.id = clg.event_id AND clg.event = 11
            where r.plan_repayment_time >= {$start_time} + 86400
            and r.plan_repayment_time < {$end_time} + 86400
            and m.success_time >= {$start_time}
            and m.success_time < {$end_time}
            and m.type = ".UserRepaymentLog::TYPE_ACTIVE."
            GROUP BY r.merchant_id,o.is_first,o.is_all_first, hours,reg.appMarket,packageName
            ORDER by hours asc";
        $all_repay = $read_db->createCommand($all_repay_sql)->queryAll();
        foreach ($all_repay as $key => $value) {
            $merchant_id = $value['merchant_id'];
            $app_market =$value['appMarket'];
            $packageName =$value['packageName'];
            $hour =sprintf ( "%02d",$value['hours']+1);

            $list = self::getUserTypeList($value['is_first'],$value['is_all_first']);
            foreach ($list as $user_type){
                if(isset($data[$merchant_id][$hour][$user_type][$packageName][$app_market]['active_repayment_tomorrow'])){
                    $data[$merchant_id][$hour][$user_type][$packageName][$app_market]['active_repayment_tomorrow'] += $value['countnum'];
                }else{
                    $data[$merchant_id][$hour][$user_type][$packageName][$app_market]['active_repayment_tomorrow'] = $value['countnum'];
                }
            }
        }

        //今日到期单数 到期金额
        $repays_tomorrow =[];
        $all_repay_sql = "select count(r.order_id) as countnum,
            sum(r.principal + r.interests) as money,
            sum(r.cost_fee) as fee, 
            sum(r.interests) as interests,
            o.is_first,
            o.is_all_first,
            reg.appMarket,
            IF(o.is_export = 1,substring_index(substring_index(clg.app_market,'_',2),'_',-1),clg.package_name) as packageName,
            r.merchant_id
            from tb_user_loan_order_repayment as r
            LEFT join tb_user_loan_order as o on r.order_id=o.id
            LEFT JOIN tb_user_register_info as reg on r.user_id = reg.user_id
            LEFT JOIN tb_client_info_log as clg on o.id = clg.event_id AND clg.event = 11
            where r.plan_repayment_time>={$start_time} + 86400
            and r.plan_repayment_time<{$end_time} + 86400
            GROUP BY r.merchant_id,o.is_first,o.is_all_first,reg.appMarket,packageName";

        $all_repays = $read_db->createCommand($all_repay_sql)->queryAll();
        foreach ($all_repays as $value) {
            $merchant_id = $value['merchant_id'];
            $app_market =$value['appMarket'];
            $packageName =$value['packageName'];
            $hour = '00';

            $list = self::getUserTypeList($value['is_first'],$value['is_all_first']);
            foreach ($list as $user_type){
                if(isset($data[$merchant_id][$hour][$user_type][$packageName][$app_market]['repays_money_tomorrow'])){
                    $data[$merchant_id][$hour][$user_type][$packageName][$app_market]['repays_money_tomorrow'] += $value['money'];
                    $data[$merchant_id][$hour][$user_type][$packageName][$app_market]['fee_tomorrow'] += $value['fee'];
                    $data[$merchant_id][$hour][$user_type][$packageName][$app_market]['interests_tomorrow'] += $value['interests'];
                }else{
                    $data[$merchant_id][$hour][$user_type][$packageName][$app_market]['repays_money_tomorrow'] = $value['money'];
                    $data[$merchant_id][$hour][$user_type][$packageName][$app_market]['fee_tomorrow'] = $value['fee'];
                    $data[$merchant_id][$hour][$user_type][$packageName][$app_market]['interests_tomorrow'] = $value['interests'];
                }
            }
        }
        $field_name=array("reg_num","apply_num","apply_money","apply_check_num","repayment_money","repayment_num","loan_num","loan_money","loan_money_today","active_repayment", "manual_order_num", "manual_num", "repayment_num_tomorrow", "repayment_money_tomorrow", "repay_rate_tomorrow", "active_repayment_tomorrow","repays_money","fee","interests","repays_money_tomorrow","fee_tomorrow","interests_tomorrow");
        foreach ($data as $merchant_id => $item){
            foreach ($item as $hour => $user_types) {
                foreach ($user_types as $user_type => $packageNames) {
                    foreach ($packageNames as $packageName => $app_markets) {
                        foreach ($app_markets as $app_market => $val) {
                            foreach ($field_name as $_k => $_v) {
                                if(!isset($val[$_v])){
                                    $val[$_v] = 0;
                                }
                            }
                            $DailyTrade = DailyTradeData::find()->where(
                                [
                                    'date'=>$date,
                                    'merchant_id' => $merchant_id,
                                    'hour'=>$hour,
                                    'user_type'=>$user_type,
                                    'package_name' => $packageName,
                                    'app_market' => $app_market
                                ])->one();
                            if(empty($DailyTrade)){
                                $DailyTrade = new DailyTradeData();
                                $DailyTrade->date =$date;
                                $DailyTrade->merchant_id = $merchant_id;
                                $DailyTrade->hour =$hour;
                                $DailyTrade->user_type =$user_type;
                                $DailyTrade->package_name =$packageName;
                                $DailyTrade->app_market =$app_market;
                                $DailyTrade->created_at =time();
                            }
                            $DailyTrade->reg_num =$val['reg_num'];//注册人数
                            $DailyTrade->apply_num =$val['apply_num'];//申请人数
                            $DailyTrade->apply_money =$val['apply_money'];//申请金额
                            $DailyTrade->apply_check_num =$val['apply_check_num'];//风控通过人数
                            $DailyTrade->manual_order_num =$val['manual_order_num'];//进入人审的订单数
                            $DailyTrade->manual_num =$val['manual_num'];//人审的订单数
                            $DailyTrade->loan_num =$val['loan_num'];//放款人数
                            $DailyTrade->loan_money =$val['loan_money'];//放款金额
                            $DailyTrade->loan_money_today =$val['loan_money_today'];//放款金额(当天申请)
                            $DailyTrade->repayment_num =$val['repayment_num'];//提前还款人数 还款人数
                            $DailyTrade->active_repayment =$val['active_repayment'];//提前还款主动还款人数
                            $DailyTrade->repayment_money =$val['repayment_money'];//提前还款金额  还款金额
                            $DailyTrade->repays_money =$val['repays_money'];//到期金额
                            $DailyTrade->fee =$val['fee'];//服务费
                            $DailyTrade->interests =$val['interests'];//利息
                            $DailyTrade->repays_money_tomorrow =$val['repays_money_tomorrow'];//次日到期金额
                            $DailyTrade->fee_tomorrow =$val['fee_tomorrow'];//次日服务费
                            $DailyTrade->interests_tomorrow =$val['interests_tomorrow'];//次日利息


                            // 下一天的数据开始
                            $DailyTrade->repayment_num_tomorrow =$val['repayment_num_tomorrow'];//提前还款人数
                            $DailyTrade->active_repayment_tomorrow =$val['active_repayment_tomorrow'];//提前还款主动还款人数
                            $DailyTrade->repayment_money_tomorrow =$val['repayment_money_tomorrow'];//提前还款金额
                            // 下一天的数据结束


                            $DailyTrade->updated_at =time();
                            if (!$DailyTrade->save()) {
                                echo  $date. "的借还款数据保存失败：" . json_encode($val)."\n";
                            }
                        }
                    }
                }
            }
        }
    }

    //获取用户标签
    private static function getUserTypeList($is_first,$is_all_first){
        $arr = [0];//0 all
        if($is_first == UserLoanOrder::FIRST_LOAN_IS ){
            $arr[] = 1;  //本新
        }else{
            $arr[] = 2;  //本老  == //全老本老
        }
        if($is_all_first == UserLoanOrder::FIRST_LOAN_IS ){
            $arr[] = 3;  //全新  == //全新本新
        }else{
            $arr[] = 4;   //全老
        }
        if($is_all_first == UserLoanOrder::FIRST_LOAN_NO && $is_first == UserLoanOrder::FIRST_LOAN_IS){
            $arr[] = 5;  //全老本新
        }
        return $arr;
    }
}
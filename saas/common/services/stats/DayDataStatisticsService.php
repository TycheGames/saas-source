<?php
/**
 * Created by loan
 * User: wangpeng
 * Date: 2019/7/5 0005
 * Time：17:05
 */


namespace common\services\stats;

use common\helpers\Util;
use common\models\ClientInfoLog;
use common\models\order\UserLoanOrder;
use common\models\order\UserLoanOrderRepayment;
use common\models\order\UserRepaymentLog;
use common\models\stats\StatisticsDayData;
use common\models\user\LoanPerson;
use common\models\user\UserRegisterInfo;

class DayDataStatisticsService extends StatsBaseService
{


    public function runDayDataStatisticsNew($pre_date){
        $pre_time = strtotime($pre_date);
        $end_time = $pre_time + 86400;
        $result = [];

        $expire_res = UserLoanOrderRepayment::find()
            ->select([
                'A.merchant_id','B.loan_method','B.loan_term','B.is_first','B.is_all_first','B.fund_id','A.order_id','A.status',
                'A.principal','A.interests','A.is_overdue','A.cost_fee','A.total_money','A.coupon_money',
                'C.appMarket','C.media_source','D.package_name','D.app_market','B.is_export'
            ])
            ->from(UserLoanOrderRepayment::tableName(). ' A')
            ->leftJoin(UserLoanOrder::tableName() . ' B', 'A.order_id = B.id')
            ->leftJoin(UserRegisterInfo::tableName() . ' C', 'A.user_id = C.user_id')
            ->leftJoin(ClientInfoLog::tableName() . ' D', 'A.order_id = D.event_id AND D.event = '.ClientInfoLog::EVENT_APPLY_ORDER)
            ->where(['>=','A.plan_repayment_time',$pre_time])
            ->andWhere(['<', 'A.plan_repayment_time',$end_time])
            ->asArray()
            ->all($this->db);
        if($expire_res) {
            $this->_deal_data_dq($expire_res, $result, $pre_date);
        }
        $this->save_statistics_daya_data($result, $pre_date);
    }

    private function _deal_data_dq($expire_res, &$res, $pre_date){
        $pre_time = strtotime($pre_date);
        $end_time = $pre_time + 86400;

        $order_id_arr = array_column($expire_res, 'order_id');
        $op_arr = [];
        $op_data_res = UserRepaymentLog::find()->where(['order_id' => $order_id_arr])->asArray()->all($this->db);
        foreach($op_data_res as $key => $value) {
            if(!isset($op_arr[$value['order_id']]['dq_op'])) {
                $op_arr[$value['order_id']]['dq_op'] = 0;
            }
            if(!isset($op_arr[$value['order_id']]['dq_op_day'])) {
                $op_arr[$value['order_id']]['dq_op_day'] = 0;
            }
            $op_arr[$value['order_id']]['dq_op'] += $value['amount'];
            if($value['success_time'] <= $end_time) {
                $op_arr[$value['order_id']]['dq_op_day'] += $value['amount'];
            }
        }

        foreach ($expire_res as $val) {
            $_oid = $val['order_id'];
            $merchant_id = $val['merchant_id'];
            $appMarket = $val['appMarket'];
            $mediaSource = strtolower($val['media_source'] ?? '');
            $packageName = $val['is_export'] == UserLoanOrder::IS_EXPORT_YES ? explode('_',$val['app_market'])[1] : $val['package_name'];
            $fund_id = empty($val['fund_id']) ? 0 : $val['fund_id'];
            $true_repayment = isset($op_arr[$_oid]) ? $op_arr[$_oid] : 0;
            if(!empty($true_repayment)) {
                $true_repayment = $this->get_money_by_loan_method($true_repayment, $val['coupon_money']);
            }

            $this->init_result($res,$merchant_id,$appMarket,$mediaSource,$packageName,$fund_id);
            $this->get_res_logic($res[$merchant_id][$appMarket][$mediaSource][$packageName][$fund_id][0], $val, $true_repayment);

            //正常新用户还款
            if ($val['is_first'] == 1) {
                $this->get_res_logic($res[$merchant_id][$appMarket][$mediaSource][$packageName][$fund_id][1], $val, $true_repayment);
            }else{//正常老用户还款
                $this->get_res_logic($res[$merchant_id][$appMarket][$mediaSource][$packageName][$fund_id][2], $val, $true_repayment);
            }
            //正常全平台新用户还款
            if ($val['is_all_first'] == 1) {
                $this->get_res_logic($res[$merchant_id][$appMarket][$mediaSource][$packageName][$fund_id][3], $val, $true_repayment);
            }else{//正常老用户还款
                $this->get_res_logic($res[$merchant_id][$appMarket][$mediaSource][$packageName][$fund_id][4], $val, $true_repayment);
            }

            //正常全平台和本平台组合用户还款
            if ($val['is_all_first'] == 1 && $val['is_first'] == 1) {
                $this->get_res_logic($res[$merchant_id][$appMarket][$mediaSource][$packageName][$fund_id][5], $val, $true_repayment);
            }elseif ($val['is_all_first'] == 0 && $val['is_first'] == 1){
                $this->get_res_logic($res[$merchant_id][$appMarket][$mediaSource][$packageName][$fund_id][6], $val, $true_repayment);
            }elseif ($val['is_all_first'] == 0 && $val['is_first'] == 0){
                $this->get_res_logic($res[$merchant_id][$appMarket][$mediaSource][$packageName][$fund_id][7], $val, $true_repayment);
            }
        }
    }

    /**
     * 保存
     * @param $result
     * @param $pre_time
     * @param $end_time
     */
    private function save_statistics_daya_data($result, $pre_date) {
        if(empty($result)) {
            return;
        }
        foreach ($result as $merchantId => $appMarketData) {
            foreach ($appMarketData as $appMarket => $mediaSourceData) {
                foreach ($mediaSourceData as $mediaSource => $packageNameData) {
                    foreach ($packageNameData as $packageName => $fund_data) {
                        foreach ($fund_data as $fund_id => $item) {
                            foreach ($item as $user_type => $value) {
                                $statisticsDayData = StatisticsDayData::find()
                                    ->where([
                                        'date' => $pre_date,
                                        'merchant_id' => $merchantId,
                                        'app_market' => $appMarket,
                                        'media_source' => $mediaSource,
                                        'package_name' => $packageName,
                                        'user_type' => $user_type,
                                        'fund_id' => $fund_id,
                                    ])->one();
                                if(!$statisticsDayData){
                                    $statisticsDayData = new StatisticsDayData();
                                    $statisticsDayData->date = $pre_date;
                                    $statisticsDayData->merchant_id = $merchantId;
                                    $statisticsDayData->app_market = $appMarket;
                                    $statisticsDayData->media_source = $mediaSource;
                                    $statisticsDayData->package_name = $packageName;
                                    $statisticsDayData->user_type = $user_type;
                                    $statisticsDayData->fund_id = $fund_id;
                                }
                                foreach ($value as $k => $v){
                                    $statisticsDayData->$k = $v;
                                }
                                $statisticsDayData->save();
                            }
                        }
                    }
                }
            }
        }
    }


    /**
     * 获取还款金额类
     * @param $result
     * @param $val
     * @param $true_repayment
     */
    private function get_res_logic(&$result, $val, $true_repayment) {
        //到期金额   应还
        $result['expire_num']++;
        $result['expire_money'] += ($val['principal'] + $val['interests']);

        //已还款
        if($val['status'] == UserLoanOrderRepayment::STATUS_REPAY_COMPLETE){
            $result['repay_num']++;
            if ($val['is_overdue'] == 0) {
                $result['repay_zc_num']++;
            }
        }else{
//            if($val['is_extend'] == 1){
//                $result['extend_num']++;
//            }
        }
        $repay_money = max(0,min($true_repayment['true_repayment_money'],($val['principal'] + $val['interests'])));
        //$repay_money = max(0,$true_repayment['true_repayment_money']-$member_fee);
        $result['repay_money'] += $repay_money;

//        if($val['is_extend'] == 1){
//            $extend_money = $val['principal'] - $repay_money;
//            $result['extend_money'] += $extend_money;
//        }

        //正常还款
        $repay_zc_money = max(0,min($true_repayment['true_repayment_money_day'],($val['principal'] + $val['interests'])));
        //$repay_zc_money = max(0,$true_repayment['true_repayment_money_day']-$member_fee);
        $result['repay_zc_money'] += $repay_zc_money;

    }


    /**
     * 初始化数组
     * @param $res
     * @param $merchant_id
     * @param $appMarket
     * @param $mediaSource
     * @param $packageName
     * @param $fund_id
     */
    private function init_result(&$res, $merchant_id, $appMarket, $mediaSource, $packageName, $fund_id) {
        $init_array = [
            'expire_num',
            'expire_money',
            'repay_num',
            'repay_money',
            'repay_zc_num',
            'repay_zc_money',
            'extend_num',
            'extend_money'
        ];

        $user_type = [0,1,2,3,4,5,6,7];
        foreach($user_type as $key => $value) {
            foreach($init_array as $k =>$v) {
                if(!isset($res[$merchant_id][$appMarket][$mediaSource][$packageName][$fund_id][$value][$v])) {
                    $res[$merchant_id][$appMarket][$mediaSource][$packageName][$fund_id][$value][$v] = 0;
                }
            }
        }

    }

    /**
     * 获取 还款额和每日还款额
     * @param $data
     * @param $coupon_money
     * @return mixed
     */
    private function get_money_by_loan_method($data, $coupon_money = 0) {
        $res['true_repayment_money'] = isset($data['dq_op']) ? $data['dq_op']+$coupon_money : 0;
        $res['true_repayment_money_day'] = isset($data['dq_op_day']) ? $data['dq_op_day']+$coupon_money : 0;
        return $res;
    }


    /**
     * 统一组装返回数据
     * @param $data
     * @param $total_data
     * @param $date
     * @param $type
     * @param $value
     */
    public function _getReturnData(&$data, &$total_data, $date, $type, $value, $today_time){

        //按天
        $expire_num = $value['expire_num'] ?? 0;
        $expire_money = $value['expire_money'] ?? 0;
        $repay_num = $value['repay_num'] ?? 0;
        $repay_money = $value['repay_money'] ?? 0;
        $repay_xj_num = $value['repay_xj_num'] ?? 0;
        $repay_xj_money = $value['repay_xj_money'] ?? 0;
        $repay_zc_num = $value['repay_zc_num'] ?? 0;
        $repay_zc_money = $value['repay_zc_money'] ?? 0;
        $extend_num = $value['extend_num'] ?? 0;
        $extend_money = $value['extend_money'] ?? 0;
        $repay_zcxj_num = $value['repay_zcxj_num'] ?? 0;
        $repay_zcxj_money = $value['repay_zcxj_money'] ?? 0;
        $zcxj_rate = $value['zcxj_rate'] ?? 0;
        $xj_rate = $value['xj_rate'] ?? 0;
        $fee = $value['fee'] ?? 0;
        $interests = $value['interests'] ?? 0;
        $late_fee = $value['late_fee'] ?? 0;
        $data[$date]['expire_num_'.$type] = $expire_num;
        $data[$date]['expire_money_'.$type] = $expire_money;
        $data[$date]['repay_num_'.$type] = $repay_num;
        $data[$date]['repay_money_'.$type] = $repay_money;
        $data[$date]['repay_xj_num_'.$type] = $repay_xj_num;
        $data[$date]['repay_xj_money_'.$type] = $repay_xj_money;
        $data[$date]['repay_zc_num_'.$type] = $repay_zc_num;
        $data[$date]['repay_zc_money_'.$type] = $repay_zc_money;
        $data[$date]['extend_num_'.$type] = $extend_num;
        $data[$date]['extend_money_'.$type] = $extend_money;
        $data[$date]['repay_zcxj_num_'.$type] = $repay_zcxj_num;
        $data[$date]['repay_zcxj_money_'.$type] = $repay_zcxj_money;
        $data[$date]['zcxj_rate_'.$type] = $zcxj_rate;
        $data[$date]['xj_rate_'.$type] = $xj_rate;
        $data[$date]['fee_'.$type] = $fee;
        $data[$date]['interests_'.$type] = $interests;
        $data[$date]['late_fee_'.$type] = $late_fee;

        //汇总
        $total_expire_num = $total_data['expire_num_'.$type] ?? 0;
        $total_expire_money = $total_data['expire_money_'.$type] ?? 0;
        $total_repay_num = $total_data['repay_num_'.$type] ?? 0;
        $total_repay_money = $total_data['repay_money_'.$type] ?? 0;
        $total_repay_zc_num = $total_data['repay_zc_num_'.$type] ?? 0;
        $total_repay_zc_money = $total_data['repay_zc_money_'.$type] ?? 0;
        $total_extend_num = $total_data['extend_num_'.$type] ?? 0;
        $total_extend_money = $total_data['extend_money_'.$type] ?? 0;
        $total_fee = $total_data['fee_'.$type] ?? 0;
        $total_interests = $total_data['interests_'.$type] ?? 0;
        $total_late_fee = $total_data['late_fee_'.$type] ?? 0;
        $total_data['expire_num_'.$type] = $total_expire_num + $expire_num;
        $total_data['expire_money_'.$type] = $total_expire_money + $expire_money;
        $total_data['repay_num_'.$type] = $total_repay_num + $repay_num;
        $total_data['repay_money_'.$type] = $total_repay_money + $repay_money;
        $total_data['repay_zc_num_'.$type] = $total_repay_zc_num + $repay_zc_num;
        $total_data['repay_zc_money_'.$type] = $total_repay_zc_money + $repay_zc_money;
        $total_data['extend_num_'.$type] = $total_extend_num + $extend_num;
        $total_data['extend_money_'.$type] = $total_extend_money + $extend_money;
        $total_data['fee_'.$type] = $total_fee + $fee;
        $total_data['interests_'.$type] = $total_interests + $interests;
        $total_data['late_fee_'.$type] = $total_late_fee + $late_fee;

        //汇总（时间大于今天的不累加）
        $t_total_expire_num = $total_data['t_expire_num_'.$type] ?? 0;
        $t_total_expire_money = $total_data['t_expire_money_'.$type] ?? 0;
        $t_total_repay_num = $total_data['t_repay_num_'.$type] ?? 0;
        $t_total_repay_money = $total_data['t_repay_money_'.$type] ?? 0;
        $t_total_repay_zc_num = $total_data['t_repay_zc_num_'.$type] ?? 0;
        $t_total_repay_zc_money = $total_data['t_repay_zc_money_'.$type] ?? 0;
        $t_total_extend_num = $total_data['t_extend_num_'.$type] ?? 0;
        $t_total_extend_money = $total_data['t_extend_money_'.$type] ?? 0;
        $t_total_fee = $total_data['t_fee_'.$type] ?? 0;
        $t_total_interests = $total_data['t_interests_'.$type] ?? 0;
        $t_total_late_fee = $total_data['t_late_fee_'.$type] ?? 0;
        if($today_time > strtotime($date)){
            $total_data['t_expire_num_'.$type] = $t_total_expire_num + $expire_num;
            $total_data['t_expire_money_'.$type] = $t_total_expire_money + $expire_money;
            $total_data['t_repay_num_'.$type] = $t_total_repay_num + $repay_num;
            $total_data['t_repay_money_'.$type] = $t_total_repay_money + $repay_money;
            $total_data['t_repay_zc_num_'.$type] = $t_total_repay_zc_num + $repay_zc_num;
            $total_data['t_repay_zc_money_'.$type] = $t_total_repay_zc_money + $repay_zc_money;
            $total_data['t_extend_num_'.$type] = $t_total_extend_num + $extend_num;
            $total_data['t_extend_money_'.$type] = $t_total_extend_money + $extend_money;
            $total_data['t_fee_'.$type] = $t_total_fee + $fee;
            $total_data['t_interests_'.$type] = $t_total_interests + $interests;
            $total_data['t_late_fee_'.$type] = $t_total_late_fee + $late_fee;
        }else{
            $total_data['t_expire_num_'.$type] = $t_total_expire_num;
            $total_data['t_expire_money_'.$type] = $t_total_expire_money;
            $total_data['t_repay_num_'.$type] = $t_total_repay_num;
            $total_data['t_repay_money_'.$type] = $t_total_repay_money;
            $total_data['t_repay_zc_num_'.$type] = $t_total_repay_zc_num;
            $total_data['t_repay_zc_money_'.$type] = $t_total_repay_zc_money;
            $total_data['t_extend_num_'.$type] = $t_total_extend_num;
            $total_data['t_extend_money_'.$type] = $t_total_extend_money;
            $total_data['t_fee_'.$type] = $t_total_fee;
            $total_data['t_interests_'.$type] = $t_total_interests;
            $total_data['t_late_fee_'.$type] = $t_total_late_fee;
        }

        unset($data);
        unset($total_data);
    }


    /**
     * 统一组装返回数据
     * @param $data
     * @param $total_data
     * @param $date
     * @param $merchantId
     * @param $type
     * @param $value
     */
    public function _getReturnDataSubMerchant(&$data, &$total_data, $date, $merchantId,$type, $value, $today_time){

        //按天
        $expire_num = $value['expire_num'] ?? 0;
        $expire_money = $value['expire_money'] ?? 0;
        $repay_num = $value['repay_num'] ?? 0;
        $repay_money = $value['repay_money'] ?? 0;
        $repay_xj_num = $value['repay_xj_num'] ?? 0;
        $repay_xj_money = $value['repay_xj_money'] ?? 0;
        $repay_zc_num = $value['repay_zc_num'] ?? 0;
        $repay_zc_money = $value['repay_zc_money'] ?? 0;
        $extend_num = $value['extend_num'] ?? 0;
        $extend_money = $value['extend_money'] ?? 0;
        $repay_zcxj_num = $value['repay_zcxj_num'] ?? 0;
        $repay_zcxj_money = $value['repay_zcxj_money'] ?? 0;
        $zcxj_rate = $value['zcxj_rate'] ?? 0;
        $xj_rate = $value['xj_rate'] ?? 0;
        $fee = $value['fee'] ?? 0;
        $interests = $value['interests'] ?? 0;
        $late_fee = $value['late_fee'] ?? 0;
        $data[$merchantId][$date]['expire_num_'.$type] = $expire_num;
        $data[$merchantId][$date]['expire_money_'.$type] = $expire_money;
        $data[$merchantId][$date]['repay_num_'.$type] = $repay_num;
        $data[$merchantId][$date]['repay_money_'.$type] = $repay_money;
        $data[$merchantId][$date]['repay_xj_num_'.$type] = $repay_xj_num;
        $data[$merchantId][$date]['repay_xj_money_'.$type] = $repay_xj_money;
        $data[$merchantId][$date]['repay_zc_num_'.$type] = $repay_zc_num;
        $data[$merchantId][$date]['repay_zc_money_'.$type] = $repay_zc_money;
        $data[$merchantId][$date]['extend_num_'.$type] = $extend_num;
        $data[$merchantId][$date]['extend_money_'.$type] = $extend_money;
        $data[$merchantId][$date]['repay_zcxj_num_'.$type] = $repay_zcxj_num;
        $data[$merchantId][$date]['repay_zcxj_money_'.$type] = $repay_zcxj_money;
        $data[$merchantId][$date]['zcxj_rate_'.$type] = $zcxj_rate;
        $data[$merchantId][$date]['xj_rate_'.$type] = $xj_rate;
        $data[$merchantId][$date]['fee_'.$type] = $fee;
        $data[$merchantId][$date]['interests_'.$type] = $interests;
        $data[$merchantId][$date]['late_fee_'.$type] = $late_fee;

        //汇总
        $total_expire_num = $total_data[$merchantId]['expire_num_'.$type] ?? 0;
        $total_expire_money = $total_data[$merchantId]['expire_money_'.$type] ?? 0;
        $total_repay_num = $total_data[$merchantId]['repay_num_'.$type] ?? 0;
        $total_repay_money = $total_data[$merchantId]['repay_money_'.$type] ?? 0;
        $total_repay_zc_num = $total_data[$merchantId]['repay_zc_num_'.$type] ?? 0;
        $total_repay_zc_money = $total_data[$merchantId]['repay_zc_money_'.$type] ?? 0;
        $total_extend_num = $total_data[$merchantId]['extend_num_'.$type] ?? 0;
        $total_extend_money = $total_data[$merchantId]['extend_money_'.$type] ?? 0;
        $total_fee = $total_data[$merchantId]['fee_'.$type] ?? 0;
        $total_interests = $total_data[$merchantId]['interests_'.$type] ?? 0;
        $total_late_fee = $total_data[$merchantId]['late_fee_'.$type] ?? 0;
        $total_data[$merchantId]['expire_num_'.$type] = $total_expire_num + $expire_num;
        $total_data[$merchantId]['expire_money_'.$type] = $total_expire_money + $expire_money;
        $total_data[$merchantId]['repay_num_'.$type] = $total_repay_num + $repay_num;
        $total_data[$merchantId]['repay_money_'.$type] = $total_repay_money + $repay_money;
        $total_data[$merchantId]['repay_zc_num_'.$type] = $total_repay_zc_num + $repay_zc_num;
        $total_data[$merchantId]['repay_zc_money_'.$type] = $total_repay_zc_money + $repay_zc_money;
        $total_data[$merchantId]['extend_num_'.$type] = $total_extend_num + $extend_num;
        $total_data[$merchantId]['extend_money_'.$type] = $total_extend_money + $extend_money;
        $total_data[$merchantId]['fee_'.$type] = $total_fee + $fee;
        $total_data[$merchantId]['interests_'.$type] = $total_interests + $interests;
        $total_data[$merchantId]['late_fee_'.$type] = $total_late_fee + $late_fee;

        //汇总（时间大于今天的不累加）
        $t_total_expire_num = $total_data[$merchantId]['t_expire_num_'.$type] ?? 0;
        $t_total_expire_money = $total_data[$merchantId]['t_expire_money_'.$type] ?? 0;
        $t_total_repay_num = $total_data[$merchantId]['t_repay_num_'.$type] ?? 0;
        $t_total_repay_money = $total_data[$merchantId]['t_repay_money_'.$type] ?? 0;
        $t_total_repay_zc_num = $total_data[$merchantId]['t_repay_zc_num_'.$type] ?? 0;
        $t_total_repay_zc_money = $total_data[$merchantId]['t_repay_zc_money_'.$type] ?? 0;
        $t_total_extend_num = $total_data[$merchantId]['t_extend_num_'.$type] ?? 0;
        $t_total_extend_money = $total_data[$merchantId]['t_extend_money_'.$type] ?? 0;
        $t_total_fee = $total_data[$merchantId]['t_fee_'.$type] ?? 0;
        $t_total_interests = $total_data[$merchantId]['t_interests_'.$type] ?? 0;
        $t_total_late_fee = $total_data[$merchantId]['t_late_fee_'.$type] ?? 0;
        if($today_time > strtotime($date)){
            $total_data[$merchantId]['t_expire_num_'.$type] = $t_total_expire_num + $expire_num;
            $total_data[$merchantId]['t_expire_money_'.$type] = $t_total_expire_money + $expire_money;
            $total_data[$merchantId]['t_repay_num_'.$type] = $t_total_repay_num + $repay_num;
            $total_data[$merchantId]['t_repay_money_'.$type] = $t_total_repay_money + $repay_money;
            $total_data[$merchantId]['t_repay_zc_num_'.$type] = $t_total_repay_zc_num + $repay_zc_num;
            $total_data[$merchantId]['t_repay_zc_money_'.$type] = $t_total_repay_zc_money + $repay_zc_money;
            $total_data[$merchantId]['t_extend_num_'.$type] = $t_total_extend_num + $extend_num;
            $total_data[$merchantId]['t_extend_money_'.$type] = $t_total_extend_money + $extend_money;
            $total_data[$merchantId]['t_fee_'.$type] = $t_total_fee + $fee;
            $total_data[$merchantId]['t_interests_'.$type] = $t_total_interests + $interests;
            $total_data[$merchantId]['t_late_fee_'.$type] = $t_total_late_fee + $late_fee;
        }else{
            $total_data[$merchantId]['t_expire_num_'.$type] = $t_total_expire_num;
            $total_data[$merchantId]['t_expire_money_'.$type] = $t_total_expire_money;
            $total_data[$merchantId]['t_repay_num_'.$type] = $t_total_repay_num;
            $total_data[$merchantId]['t_repay_money_'.$type] = $t_total_repay_money;
            $total_data[$merchantId]['t_repay_zc_num_'.$type] = $t_total_repay_zc_num;
            $total_data[$merchantId]['t_repay_zc_money_'.$type] = $t_total_repay_zc_money;
            $total_data[$merchantId]['t_extend_num_'.$type] = $t_total_extend_num;
            $total_data[$merchantId]['t_extend_money_'.$type] = $t_total_extend_money;
            $total_data[$merchantId]['t_fee_'.$type] = $t_total_fee;
            $total_data[$merchantId]['t_interests_'.$type] = $t_total_interests;
            $total_data[$merchantId]['t_late_fee_'.$type] = $t_total_late_fee;
        }

        unset($data);
        unset($total_data);
    }


    public function _exportDailyLoanData($datas){
        $this->_setcsvHeader('还款数据.csv');
        $now_date_time = strtotime(date('Y-m-d', time()));
        $items = [];
        foreach($datas as $key=> $value){
            $items[] = [
                '日期'=>$key,
                '到期单数' =>$value['expire_num_0'],
                '正常还款' => $value['repay_zc_num_0'],
                '已还款单数' =>$value['repay_num_0'],
                '首逾' => ($value['unix_time_key'] >= $now_date_time) ? '-' : (empty($value['expire_num_0']) ? '-' : sprintf("%0.2f", ($value['expire_num_0'] - $value['repay_zc_num_0']) / $value['expire_num_0'] * 100) . "%"),
                '还款率' => empty($value['expire_num_0']) ? '-' : sprintf("%0.2f", ($value['repay_num_0'] / $value['expire_num_0']) * 100) . "%",
                '逾期数' => ($value['unix_time_key'] >= $now_date_time) ? '-' : $value['expire_num_0'] - $value['repay_num_0'],
                '逾期率' => ($value['unix_time_key'] >= $now_date_time) ? '-' : empty($value['expire_num_0']) ? '-' : sprintf("%0.2f", (($value['expire_num_0'] - $value['repay_num_0']) / $value['expire_num_0']) * 100) . "%",
                '新用户到期数' => isset($value['expire_num_1']) ? $value['expire_num_1'] : 0,
                '新用户首逾' => ($value['unix_time_key'] >= $now_date_time) ? '-' : (empty($value['expire_num_1']) ? '0%' : sprintf("%0.2f", ($value['expire_num_1'] - $value['repay_zc_num_1']) / $value['expire_num_1'] * 100) . "%"),
                '新用户还款率' => empty($value['expire_num_1']) ? '-' : sprintf("%0.2f", ($value['repay_num_1'] / $value['expire_num_1']) * 100) . "%",
                '新用户逾期数' => ($value['unix_time_key'] >= $now_date_time) ? '-' : ((isset($value['expire_num_1']) && isset($value['repay_num_1'])) ? $value['expire_num_1'] - $value['repay_num_1'] : '-'),
                '新用户逾期率' => ($value['unix_time_key'] >= $now_date_time) ? '-' : empty($value['expire_num_1']) ? '-' : sprintf("%0.2f", (($value['expire_num_1'] - $value['repay_num_1']) / $value['expire_num_1']) * 100) . "%",
                '老用户到期数' => isset($value['expire_num_2']) ? $value['expire_num_2'] : 0,
                '老用户首逾' => ($value['unix_time_key'] >= $now_date_time) ? '-' : (empty($value['expire_num_2']) ? '0%' : sprintf("%0.2f", ($value['expire_num_2'] - $value['repay_zc_num_2']) / $value['expire_num_2'] * 100) . "%"),
                '老用户还款率' => empty($value['expire_num_2']) ? '-' : sprintf("%0.2f", ($value['repay_num_2'] / $value['expire_num_2']) * 100) . "%",
                '老用户逾期数' => ($value['unix_time_key'] >= $now_date_time) ? '-' : ((isset($value['expire_num_2']) && isset($value['repay_num_2'])) ? $value['expire_num_2'] - $value['repay_num_2'] : '-'),
                '老用户逾期率' => ($value['unix_time_key'] >= $now_date_time) ? '-' : empty($value['expire_num_2']) ? '-' : sprintf("%0.2f", (($value['expire_num_2'] - $value['repay_num_2']) / $value['expire_num_2']) * 100) . "%",
            ];
        }
        echo $this->_array2csv($items);
        exit;
    }

    public function _exportDailyRepaymentData($datas){
        $this->_setcsvHeader('还款数据.csv');
        $now_date_time = strtotime(date('Y-m-d', time()));
        $items = [];
        foreach($datas as $key=> $value){
            $items[] = [
                '日期'=>$key,
                '到期金额' =>$value['expire_money_0'] / 100,
                '正常还款' => number_format($value['repay_zc_money_0']/100),
                '已还款金额' =>$value['repay_money_0'] / 100,
                '首逾' => ($value['unix_time_key'] >= $now_date_time) ? '-' : (empty($value['expire_money_0'])?'-':sprintf("%0.2f",($value['expire_money_0']-$value['repay_zc_money_0'])/($value['expire_money_0'])*100)."%"),
                '还款率' => empty($value['expire_money_0'])?'-':sprintf("%0.2f",(($value['repay_money_0'])/($value['expire_money_0']))*100)."%",
                '逾期金额' => ($value['unix_time_key'] >= $now_date_time) ? '-' : number_format(($value['expire_money_0']-$value['repay_money_0'])/100),
                '逾期率' => ($value['unix_time_key'] >= $now_date_time) ? '-' : empty($value['expire_money_0'])?'-':sprintf("%0.2f",(($value['expire_money_0']-$value['repay_money_0'])/($value['expire_money_0']))*100)."%",
                '新用户到期金额' => isset($value['expire_money_1'])?number_format(floor($value['expire_money_1'])/100):0,
                '新用户首逾' => ($value['unix_time_key'] >= $now_date_time) ? '-' : (empty($value['expire_money_1'])?'0%':sprintf("%0.2f",($value['expire_money_1']-$value['repay_zc_money_1'])/($value['expire_money_1'])*100)."%"),
                '新用户还款率' => empty($value['expire_money_1'])?'-':sprintf("%0.2f",(($value['repay_money_1'])/($value['expire_money_1']))*100)."%",
                '新用户逾期金额' => ($value['unix_time_key'] >= $now_date_time) ? '-' : ((isset($value['expire_money_1']) && isset($value['repay_money_1'])) ? number_format(($value['expire_money_1']-$value['repay_money_1'])/100) : 0),
                '新用户逾期率' => ($value['unix_time_key'] >= $now_date_time) ? '-' : empty($value['expire_money_1'])?'-':sprintf("%0.2f",(($value['expire_money_1']-$value['repay_money_1'])/($value['expire_money_1']))*100)."%",
                '老用户到期金额' => isset($value['expire_money_2'])?number_format(floor($value['expire_money_2'])/100):0,
                '老用户首逾' => ($value['unix_time_key'] >= $now_date_time) ? '-' : (empty($value['expire_money_2'])?'0%':sprintf("%0.2f",($value['expire_money_2']-$value['repay_zc_money_2'])/($value['expire_money_2'])*100)."%"),
                '老用户还款率' => empty($value['expire_money_2'])?'-':sprintf("%0.2f",($value['repay_money_2']/$value['expire_money_2'])*100)."%",
                '老用户逾期金额' => ($value['unix_time_key'] >= $now_date_time) ? '-' : ((isset($value['expire_money_2']) && isset($value['repay_money_2'])) ? number_format(($value['expire_money_2']-$value['repay_money_2'])/100) : 0),
                '老用户逾期率' => ($value['unix_time_key'] >= $now_date_time) ? '-' :empty($value['expire_money_2'])?'-': sprintf("%0.2f",(($value['expire_money_2']-$value['repay_money_2'])/$value['expire_money_2'])*100)."%",
            ];
        }
        echo $this->_array2csv($items);
        exit;
    }
}
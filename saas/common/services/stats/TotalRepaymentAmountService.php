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
use common\models\order\UserLoanOrderDelayPaymentLog;
use common\models\order\UserLoanOrderRepayment;
use common\models\order\UserRepaymentLog;
use common\models\stats\TotalRepaymentAmountData;
use common\models\user\LoanPerson;
use common\models\user\UserRegisterInfo;

class TotalRepaymentAmountService extends StatsBaseService
{


    public function runTotalRepaymentAmount($pre_date){
        echo "date:{$pre_date}\n";
        $pre_time = strtotime($pre_date);
        $end_time = $pre_time + 86400;
        $result = [];

        $expire_res = UserLoanOrderRepayment::find()
            ->select([
                'A.merchant_id','B.is_first','B.is_all_first','B.fund_id','A.order_id','A.status',
                'A.principal','A.interests','A.is_overdue','C.customer_type',
                'A.true_total_money','E.media_source','D.package_name','D.app_market','A.plan_repayment_time','B.is_export',
            ])
            ->from(UserLoanOrderRepayment::tableName(). ' A')
            ->leftJoin(UserLoanOrder::tableName() . ' B', 'A.order_id = B.id')
            ->leftJoin(LoanPerson::tableName(). ' C', 'A.user_id = C.id')
            ->leftJoin(ClientInfoLog::tableName() . ' D', 'A.order_id = D.event_id AND D.event = '.ClientInfoLog::EVENT_APPLY_ORDER)
            ->leftJoin(UserRegisterInfo::tableName(). ' E', 'A.user_id = E.user_id')
            ->where(['>=','A.plan_repayment_time',$pre_time])
            ->andWhere(['<', 'A.plan_repayment_time',$end_time])
            ->asArray()
            ->all($this->db);
        if($expire_res) {
            $this->_deal_data_dq($expire_res, $result);
        }
        $this->save_statistics_daya_data($result, $pre_date);
    }

    private function _deal_data_dq($expire_res, &$res){

        $order_id_arr = array_column($expire_res, 'order_id');
        $yq_arr = [];

        $yq_data_res = UserLoanOrderDelayPaymentLog::find()->where(['order_id' => $order_id_arr])->asArray()->all($this->db);
        foreach($yq_data_res as $key => $value) {
            if(!isset($yq_arr[$value['order_id']])) {
                $yq_arr[$value['order_id']] = 0;
            }
            $yq_arr[$value['order_id']] += $value['amount'];
        }

        foreach ($expire_res as $val) {
            $_oid = $val['order_id'];
            $merchant_id = $val['merchant_id'];
            $mediaSource = strtolower($val['media_source'] ?? '');
            $packageName = $val['is_export'] == UserLoanOrder::IS_EXPORT_YES ? explode('_',$val['app_market'])[1] : $val['package_name'];
            $fund_id = empty($val['fund_id']) ? 0 : $val['fund_id'];


            $delay_money = isset($yq_arr[$_oid]) ? $yq_arr[$_oid] : 0;

            $this->init_result($res,$merchant_id,$fund_id,$packageName,$mediaSource);
            $this->get_res_logic($res[$merchant_id][$fund_id][$packageName][$mediaSource][0], $val, $delay_money);
            //正常新用户还款d
            if ($val['customer_type'] == 0 || $val['is_first'] == 1) {
                $this->get_res_logic($res[$merchant_id][$fund_id][$packageName][$mediaSource][1], $val, $delay_money);
            }elseif($val['customer_type'] == 1 && $val['is_first'] == 0){//正常老用户还款
                $this->get_res_logic($res[$merchant_id][$fund_id][$packageName][$mediaSource][2], $val, $delay_money);
            }

            //正常全平台和本平台组合用户还款
            if ($val['is_all_first'] == 1 && $val['is_first'] == 1) {
                $this->get_res_logic($res[$merchant_id][$fund_id][$packageName][$mediaSource][3], $val, $delay_money);
            }elseif ($val['is_all_first'] == 0 && $val['is_first'] == 1){
                $this->get_res_logic($res[$merchant_id][$fund_id][$packageName][$mediaSource][4], $val, $delay_money);
            }elseif ($val['is_all_first'] == 0 && $val['is_first'] == 0){
                $this->get_res_logic($res[$merchant_id][$fund_id][$packageName][$mediaSource][5], $val, $delay_money);
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
        foreach ($result as $merchantId => $fund_data) {
            foreach ($fund_data as $fund_id => $packageNameData) {
                foreach ($packageNameData as $packageName => $mediaSourceData) {
                    foreach ($mediaSourceData as $mediaSource => $item) {
                        foreach ($item as $user_type => $value) {
                            $totalRepaymentAmountData = TotalRepaymentAmountData::find()
                                ->where([
                                    'date' => $pre_date,
                                    'merchant_id' => $merchantId,
                                    'fund_id' => $fund_id,
                                    'package_name' => $packageName,
                                    'media_source' => $mediaSource,
                                    'user_type' => $user_type
                                ])->one();
                            if(!$totalRepaymentAmountData){
                                $totalRepaymentAmountData = new TotalRepaymentAmountData();
                                $totalRepaymentAmountData->date = $pre_date;
                                $totalRepaymentAmountData->merchant_id = $merchantId;
                                $totalRepaymentAmountData->fund_id = $fund_id;
                                $totalRepaymentAmountData->package_name = $packageName;
                                $totalRepaymentAmountData->media_source = $mediaSource;
                                $totalRepaymentAmountData->user_type = $user_type;
                            }
                            foreach ($value as $k => $v){
                                $totalRepaymentAmountData->$k = $v;
                            }
                            $totalRepaymentAmountData->save();

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
     * @param $delay_money
     */
    private function get_res_logic(&$result, $val, $delay_money) {
        //到期金额   应还
        $result['expire_num']++;
        $result['expire_money'] += ($val['principal'] + $val['interests']);
        //累计已还金额
        if($val['true_total_money'] > 0){
            $result['repay_num']++;
        }
        $result['repay_money'] += $val['true_total_money'];
        //延期已还金额
        if($delay_money > 0){
            $result['delay_num']++;
        }
        $result['delay_money'] += $delay_money;
    }


    /**
     * 初始化数组
     * @param $res
     * @param $merchant_id
     * @param $fund_id
     * @param $packageName
     * @param $mediaSource
     */
    private function init_result(&$res, $merchant_id, $fund_id, $packageName, $mediaSource) {
        $init_array = [
            'expire_num',
            'expire_money',
            'repay_num',
            'repay_money',
            'delay_num',
            'delay_money'
        ];

        $user_type = [0,1,2,3,4,5];

        foreach($user_type as $key => $value) {
            foreach($init_array as $k =>$v) {
                if(!isset($res[$merchant_id][$fund_id][$packageName][$mediaSource][$value][$v])) {
                    $res[$merchant_id][$fund_id][$packageName][$mediaSource][$value][$v] = 0;
                }
            }
        }

    }


    /**
     * 统一组装返回数据
     * @param $data
     * @param $total_data
     * @param $date
     * @param $type
     * @param $value
     */
    public function _getReturnData(&$data, &$total_data, $date, $type, $value){

        //按天
        $expire_num = $value['expire_num'] ?? 0;
        $expire_money = $value['expire_money'] ?? 0;
        $repay_num = $value['repay_num'] ?? 0;
        $repay_money = $value['repay_money'] ?? 0;
        $delay_num = $value['delay_num'] ?? 0;
        $delay_money = $value['delay_money'] ?? 0;

        $data[$date]['expire_num_'.$type] = $expire_num;
        $data[$date]['expire_money_'.$type] = $expire_money;
        $data[$date]['repay_num_'.$type] = $repay_num;
        $data[$date]['repay_money_'.$type] = $repay_money;
        $data[$date]['delay_num_'.$type] = $delay_num;
        $data[$date]['delay_money_'.$type] = $delay_money;

        //汇总
        $total_expire_num = $total_data['expire_num_'.$type] ?? 0;
        $total_expire_money = $total_data['expire_money_'.$type] ?? 0;
        $total_repay_num = $total_data['repay_num_'.$type] ?? 0;
        $total_repay_money = $total_data['repay_money_'.$type] ?? 0;

        $total_delay_num = $total_data['delay_num_'.$type] ?? 0;
        $total_delay_money = $total_data['delay_money_'.$type] ?? 0;

        $total_data['expire_num_'.$type] = $total_expire_num + $expire_num;
        $total_data['expire_money_'.$type] = $total_expire_money + $expire_money;
        $total_data['repay_num_'.$type] = $total_repay_num + $repay_num;
        $total_data['repay_money_'.$type] = $total_repay_money + $repay_money;

        $total_data['delay_num_'.$type] = $total_delay_num + $delay_num;
        $total_data['delay_money_'.$type] = $total_delay_money + $delay_money;
        unset($data);
        unset($total_data);
    }



    public function _exportTotalRepaymentAmount($datas){
        $this->_setcsvHeader('总还款金额数据.csv');
        $items = [];
        foreach($datas as $key=> $value){
            $items[] = [
                '日期'=>$key,
                '到期单数' =>$value['expire_num_0'],
                '到期金额' =>$value['expire_money_0'] / 100,
                '有过还款单数' =>$value['repay_num_0'],
                '累计还款的金额' =>$value['repay_money_0'] / 100,
                '有过延期的单数' =>$value['delay_num_0'],
                '延期的金额'  =>$value['delay_money_0'] / 100,
                '新用户到期单数' =>$value['expire_num_1'],
                '新用户到期金额' =>$value['expire_money_1'] / 100,
                '新用户有过还款单数' =>$value['repay_num_1'],
                '新用户累计还款的金额' =>$value['repay_money_1'] / 100,
                '新用户有过延期的单数' =>$value['delay_num_1'],
                '新用户延期的金额'  =>$value['delay_money_1'] / 100,
                '老用户到期单数' =>$value['expire_num_2'],
                '老用户到期金额' =>$value['expire_money_2'] / 100,
                '老用户有过还款单数' =>$value['repay_num_2'],
                '老用户累计还款的金额' =>$value['repay_money_2'] / 100,
                '老用户有过延期的单数' =>$value['delay_num_2'],
                '老用户延期的金额'  =>$value['delay_money_2'] / 100,

                '全新本新到期单数' =>$value['expire_num_3'],
                '全新本新到期金额' =>$value['expire_money_3'] / 100,
                '全新本新有过还款单数' =>$value['repay_num_3'],
                '全新本新累计还款的金额' =>$value['repay_money_3'] / 100,
                '全新本新有过延期的单数' =>$value['delay_num_3'],
                '全新本新延期的金额'  =>$value['delay_money_3'] / 100,

                '全老本新到期单数' =>$value['expire_num_4'],
                '全老本新到期金额' =>$value['expire_money_4'] / 100,
                '全老本新有过还款单数' =>$value['repay_num_4'],
                '全老本新累计还款的金额' =>$value['repay_money_4'] / 100,
                '全老本新有过延期的单数' =>$value['delay_num_4'],
                '全老本新延期的金额'  =>$value['delay_money_4'] / 100,

                '全老本老到期单数' =>$value['expire_num_5'],
                '全老本老到期金额' =>$value['expire_money_5'] / 100,
                '全老本老有过还款单数' =>$value['repay_num_5'],
                '全老本老累计还款的金额' =>$value['repay_money_5'] / 100,
                '全老本老有过延期的单数' =>$value['delay_num_5'],
                '全老本老延期的金额'  =>$value['delay_money_5'] / 100,
            ];
        }
        echo $this->_array2csv($items);
        exit;
    }
}
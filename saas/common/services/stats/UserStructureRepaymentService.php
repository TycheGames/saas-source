<?php


namespace common\services\stats;

use common\models\ClientInfoLog;
use common\models\order\UserLoanOrder;
use common\models\order\UserLoanOrderExternal;
use common\models\order\UserLoanOrderRepayment;
use common\models\stats\UserStructureExportRepaymentData;
use common\models\stats\UserStructureSourceExportRepaymentData;

class UserStructureRepaymentService extends StatsBaseService
{


    public function runUserStructureRepaymentByDate($pre_time){
        $pre_date = date('Y-m-d',$pre_time);
        echo "date:{$pre_date}\n";
        $end_time = $pre_time + 86400;
        $result = [];

        $expire_res = UserLoanOrderRepayment::find()
            ->select([
                'A.merchant_id','B.is_first','B.is_all_first','A.is_overdue',
                'A.principal','A.interests','C.app_market'
            ])
            ->from(UserLoanOrderRepayment::tableName(). ' A')
            ->leftJoin(UserLoanOrder::tableName() . ' B', 'A.order_id = B.id')
            ->leftJoin(ClientInfoLog::tableName() . ' C', 'A.order_id = C.event_id AND C.event = '. ClientInfoLog::EVENT_APPLY_ORDER)
            ->where(['B.is_export' => UserLoanOrder::IS_EXPORT_YES])
            ->andwhere(['>=','A.plan_repayment_time',$pre_time])
            ->andWhere(['<', 'A.plan_repayment_time',$end_time])
            ->asArray()
            ->all($this->db);
        if($expire_res) {
            foreach ($expire_res as $val) {
                $merchantId = $val['merchant_id'];
                $exportPackage = explode('_',$val['app_market'])[1];
                $init_array = [
                    'expire_num',
                    'expire_money',
                    'first_over_num',
                    'first_over_money'
                ];

                $user_type = [0,1,2,3];  //1全新、本新; 2全老、本新;3全老、本老
                foreach($user_type as $key => $value) {
                    foreach($init_array as $k =>$v) {
                        if(!isset($result[$merchantId][$exportPackage][$value][$v])) {
                            $result[$merchantId][$exportPackage][$value][$v] = 0;
                        }
                    }
                }


                $this->get_res_logic($result[$merchantId][$exportPackage][0], $val);

                if ($val['is_first'] == 1 && $val['is_all_first'] == 1) {
                    //1全新、本新
                    $this->get_res_logic($result[$merchantId][$exportPackage][1], $val);
                }else if ($val['is_first'] == 1 && $val['is_all_first'] == 0){
                    //2全老、本新
                    $this->get_res_logic($result[$merchantId][$exportPackage][2], $val);
                }else if ($val['is_first'] == 0 && $val['is_all_first'] == 0){
                    //3全老、本老
                    $this->get_res_logic($result[$merchantId][$exportPackage][3], $val);
                }
            }
        }
        if(empty($result)) {
            return;
        }

        foreach ($result as $merchantId => $exportPackageData) {
            foreach ($exportPackageData as $exportPackage => $item) {
                foreach ($item as $user_type => $v) {
                    $userStructureExportRepaymentData = UserStructureExportRepaymentData::find()->where(
                        [
                            'date' => $pre_date,
                            'merchant_id' => $merchantId,
                            'package_name' => $exportPackage,
                            'user_type' => $user_type
                        ])->one();
                    if(is_null($userStructureExportRepaymentData)){
                        $userStructureExportRepaymentData = new UserStructureExportRepaymentData();
                        $userStructureExportRepaymentData->date = $pre_date;
                        $userStructureExportRepaymentData->merchant_id = $merchantId;
                        $userStructureExportRepaymentData->package_name = $exportPackage;
                        $userStructureExportRepaymentData->user_type = $user_type;
                    }
                    $userStructureExportRepaymentData->expire_num = $v['expire_num'];
                    $userStructureExportRepaymentData->expire_money = $v['expire_money'];
                    $userStructureExportRepaymentData->first_over_num = $v['first_over_num'];
                    $userStructureExportRepaymentData->first_over_money = $v['first_over_money'];
                    $userStructureExportRepaymentData->save();
                }
            }
        }
    }

    public function runUserStructureSourceRepaymentByDate($pre_time){
        $pre_date = date('Y-m-d',$pre_time);
        echo "date:{$pre_date}\n";
        $end_time = $pre_time + 86400;
        $result = [];

        $maxId = 0;
        $query = UserLoanOrderRepayment::find()
            ->select([
                'A.id', 'A.merchant_id','B.is_first','B.is_first','A.is_overdue','B.order_uuid',
                'A.principal','A.interests','C.app_market'
            ])
            ->from(UserLoanOrderRepayment::tableName(). ' A')
            ->leftJoin(UserLoanOrder::tableName() . ' B', 'A.order_id = B.id')
            ->leftJoin(ClientInfoLog::tableName() . ' C', 'A.order_id = C.event_id AND C.event = '. ClientInfoLog::EVENT_APPLY_ORDER)
            ->where(['B.is_export' => UserLoanOrder::IS_EXPORT_YES])
            ->andwhere(['>=','A.plan_repayment_time',$pre_time])
            ->andWhere(['<', 'A.plan_repayment_time',$end_time]);


        $expire_res = $query->andWhere(['>','A.id',$maxId])->limit(1000)->orderBy(['A.id' => SORT_ASC])->asArray()->all($this->db);
        while($expire_res) {
            $orderUuidArr = array_column($expire_res, 'order_uuid');
            $orderExternal = UserLoanOrderExternal::find()->select(['order_uuid','is_first'])->where(['order_uuid' => $orderUuidArr])->asArray()->all();
            $orderExternalArr = array_column($orderExternal,'is_first','order_uuid');
            foreach ($expire_res as $val) {
                $maxId = $val['id'];
                if(!isset($orderExternalArr[$val['order_uuid']])){
                    continue;
                }
                $isFirstExternal = $orderExternalArr[$val['order_uuid']];
                $merchantId = $val['merchant_id'];
                $exportPackage = explode('_',$val['app_market'])[1];
                $init_array = [
                    'expire_num',
                    'expire_money',
                    'first_over_num',
                    'first_over_money'
                ];

                $user_type = [0,1,2,3,4];  //1来源新、放款新; 2来源新、放款老; 3来源老、放款新; 4来源老、放款老;
                foreach($user_type as $key => $value) {
                    foreach($init_array as $k =>$v) {
                        if(!isset($result[$merchantId][$exportPackage][$value][$v])) {
                            $result[$merchantId][$exportPackage][$value][$v] = 0;
                        }
                    }
                }


                $this->get_res_logic($result[$merchantId][$exportPackage][0], $val);

                if ($isFirstExternal == 1 && $val['is_first'] == 1) {
                    //1来源新、放款新
                    $this->get_res_logic($result[$merchantId][$exportPackage][1], $val);
                }else if ($isFirstExternal == 1 && $val['is_first'] == 0){
                    //2来源新、放款老
                    $this->get_res_logic($result[$merchantId][$exportPackage][2], $val);
                }else if ($isFirstExternal == 0 && $val['is_first'] == 1){
                    //3来源老、放款新
                    $this->get_res_logic($result[$merchantId][$exportPackage][3], $val);
                }else if ($isFirstExternal == 0 && $val['is_first'] == 0){
                    //4来源老、放款老
                    $this->get_res_logic($result[$merchantId][$exportPackage][4], $val);
                }
            }
            $expire_res = $query->andWhere(['>','A.id',$maxId])->limit(1000)->orderBy(['A.id' => SORT_ASC])->asArray()->all($this->db);
        }
        if(empty($result)) {
            return;
        }

        foreach ($result as $merchantId => $exportPackageData) {
            foreach ($exportPackageData as $exportPackage => $item) {
                foreach ($item as $user_type => $v) {
                    $userStructureExportRepaymentData = UserStructureSourceExportRepaymentData::find()->where(
                        [
                            'date' => $pre_date,
                            'merchant_id' => $merchantId,
                            'package_name' => $exportPackage,
                            'user_type' => $user_type
                        ])->one();
                    if(is_null($userStructureExportRepaymentData)){
                        $userStructureExportRepaymentData = new UserStructureSourceExportRepaymentData();
                        $userStructureExportRepaymentData->date = $pre_date;
                        $userStructureExportRepaymentData->merchant_id = $merchantId;
                        $userStructureExportRepaymentData->package_name = $exportPackage;
                        $userStructureExportRepaymentData->user_type = $user_type;
                    }
                    $userStructureExportRepaymentData->expire_num = $v['expire_num'];
                    $userStructureExportRepaymentData->expire_money = $v['expire_money'];
                    $userStructureExportRepaymentData->first_over_num = $v['first_over_num'];
                    $userStructureExportRepaymentData->first_over_money = $v['first_over_money'];
                    $userStructureExportRepaymentData->save();
                }
            }
        }
    }

    /**
     * 获取还款金额类
     * @param $result
     * @param $val
     */
    private function get_res_logic(&$result, $val) {
        //到期金额   应还
        $result['expire_num']++;
        $result['expire_money'] += ($val['principal'] + $val['interests']);

        //已还款
        if ($val['is_overdue'] == 1) {
            $result['first_over_num']++;
            $result['first_over_money'] += ($val['principal'] + $val['interests']);
        }
    }

    /**
     * 统一组装返回数据
     * @param $data
     * @param $total_data
     * @param $date
     * @param $type
     * @param $value
     * @param $today_time
     */
    public function _getReturnData(&$data, &$total_data, $date, $type, $value, $today_time){

        //按天
        $expire_num = $value['expire_num'] ?? 0;
        $expire_money = $value['expire_money'] ?? 0;
        $first_over_num = $value['first_over_num'] ?? 0;
        $first_over_money = $value['first_over_money'] ?? 0;


        $data[$date]['expire_num_'.$type] = $expire_num;
        $data[$date]['expire_money_'.$type] = $expire_money;
        $data[$date]['first_over_num_'.$type] = $first_over_num;
        $data[$date]['first_over_money_'.$type] = $first_over_money;


        //汇总
        $total_expire_num = $total_data['expire_num_'.$type] ?? 0;
        $total_expire_money = $total_data['expire_money_'.$type] ?? 0;
        $total_first_over_num = $total_data['first_over_num_'.$type] ?? 0;
        $total_first_over_money = $total_data['first_over_money_'.$type] ?? 0;


        $total_data['expire_num_'.$type] = $total_expire_num + $expire_num;
        $total_data['expire_money_'.$type] = $total_expire_money + $expire_money;
        $total_data['first_over_num_'.$type] = $total_first_over_num + $first_over_num;
        $total_data['first_over_money_'.$type] = $total_first_over_money + $first_over_money;

        //汇总（时间大于今天的不累加）
        $t_total_expire_num = $total_data['t_expire_num_'.$type] ?? 0;
        $t_total_expire_money = $total_data['t_expire_money_'.$type] ?? 0;
        $t_total_first_over_num = $total_data['t_first_over_num_'.$type] ?? 0;
        $t_total_first_over_money = $total_data['t_first_over_money_'.$type] ?? 0;


        if($today_time > strtotime($date)){
            $total_data['t_expire_num_'.$type] = $t_total_expire_num + $expire_num;
            $total_data['t_expire_money_'.$type] = $t_total_expire_money + $expire_money;
            $total_data['t_first_over_num_'.$type] = $t_total_first_over_num + $first_over_num;
            $total_data['t_first_over_money_'.$type] = $t_total_first_over_money + $first_over_money;
        }else{
            $total_data['t_expire_num_'.$type] = $t_total_expire_num;
            $total_data['t_expire_money_'.$type] = $t_total_expire_money;
            $total_data['t_first_over_num_'.$type] = $t_total_first_over_num;
            $total_data['t_first_over_money_'.$type] = $t_total_first_over_money;
        }
        unset($data);
        unset($total_data);
    }
}
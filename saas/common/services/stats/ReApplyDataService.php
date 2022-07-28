<?php
/**
 * Created by loan
 * User: wangpeng
 * Date: 2019/7/5 0005
 * Time：17:05
 */


namespace common\services\stats;

use common\helpers\CommonHelper;
use common\helpers\Util;
use common\models\stats\ReApplyData;

class ReApplyDataService extends StatsBaseService
{
    public function actionDayRepayRun($start_time,$fields=''){
        $date = date('Y-m-d',$start_time);
        $end_time=$start_time+86400;
        echo $date . "\n";

        $db = $this->db;

        $apply=[];
        $apply1=[];
        $apply7=[];
        $apply14=[];
        $apply30=[];
        $apply31=[];
        $succ=[];
        $succ1=[];
        $succ7=[];
        $succ14=[];
        $succ30=[];
        $succ31=[];
        $data=[];
        //当日还款人数
        $sql="select {$fields},r.closing_time from tb_user_loan_order as o
              LEFT join tb_user_loan_order_repayment as r on o.id=r.order_id
              LEFT join tb_loan_person as p on o.user_id=p.id
              LEFT join tb_user_register_info as i on i.user_id=o.user_id
              where r.closing_time>={$start_time} and r.closing_time<{$end_time}
              GROUP by {$fields}";
        $result = $db->createCommand($sql)->queryAll();
        $user_time=array_column($result,'closing_time','user_id');
        $user_arr=array_column($result,'user_id');
        $user_str=$user_arr?implode(',',$user_arr):"''";
        $this->_getSourceData($field='repay_num',$result,$data);

        //复借申请人数 ($create_time-$user_time[$user_id])>0 //复借成功人数$loan_time>0
        $sql="select {$fields},min(o.created_at) as created_at,o.loan_time,o.amount from tb_user_loan_order as o
              LEFT join tb_user_loan_order_repayment as r on o.id=r.order_id
              LEFT join tb_loan_person as p on o.user_id=p.id
              LEFT join tb_user_register_info as i on i.user_id=o.user_id
              where o.created_at>{$start_time}
              and o.user_id in ({$user_str})
              GROUP by {$fields}";
        $result = $db->createCommand($sql)->queryAll();

        foreach($result as $item){
            $create_time=$item['created_at'];
            $loan_time=$item['loan_time'];
            $user_id=$item['user_id'];
            if(isset($user_time[$user_id])){
                $day=($create_time-$user_time[$user_id])/86400;
                if($day>0){
                    $apply[]=$item;
                    if($loan_time>0){
                        $succ[]=$item;
                    }
                }
                if($day>0 &&$create_time<$end_time){
                    $apply1[]=$item;
                    if($loan_time>0){
                        $succ1[]=$item;
                    }
                }
                if($day>0 &&$create_time<($end_time+7*86400)){
                    $apply7[]=$item;
                    if($loan_time>0){
                        $succ7[]=$item;
                    }
                }
                if($day>0 &&$create_time<($end_time+10*86400)){
                    $apply14[]=$item;
                    if($loan_time>0){
                        $succ14[]=$item;
                    }
                }
                if($day>0 &&$create_time<($end_time+30*86400)){
                    $apply30[]=$item;
                    if($loan_time>0){
                        $succ30[]=$item;
                    }
                }
                if($day>0&&$create_time>=($end_time+30*86400)) {
                    $apply31[] = $item;
                    if ($loan_time > 0) {
                        $succ31[] = $item;
                    }
                }
            }

        }

        $this->_getSourceData($field='borrow_apply_num',$apply,$data);
        $this->_getSourceData($field='borrow_apply1_num',$apply1,$data);
        $this->_getSourceData($field='borrow_apply7_num',$apply7,$data);
        $this->_getSourceData($field='borrow_apply14_num',$apply14,$data);
        $this->_getSourceData($field='borrow_apply30_num',$apply30,$data);
        $this->_getSourceData($field='borrow_apply31_num',$apply31,$data);

        $this->_getSourceData($field='borrow_succ_num',$succ,$data);
        $this->_getSourceData($field='borrow_succ_money',$succ,$data);
        $this->_getSourceData($field='borrow_succ1_num',$succ1,$data);
        $this->_getSourceData($field='borrow_succ1_money',$succ1,$data);
        $this->_getSourceData($field='borrow_succ7_num',$succ7,$data);
        $this->_getSourceData($field='borrow_succ7_money',$succ7,$data);
        $this->_getSourceData($field='borrow_succ14_num',$succ14,$data);
        $this->_getSourceData($field='borrow_succ14_money',$succ14,$data);
        $this->_getSourceData($field='borrow_succ30_num',$succ30,$data);
        $this->_getSourceData($field='borrow_succ30_money',$succ30,$data);
        $this->_getSourceData($field='borrow_succ31_num',$succ31,$data);
        $this->_getSourceData($field='borrow_succ31_money',$succ31,$data);

        $this->_saveData($data,$date);
    }

    private function _getSourceData($field,$result,&$data){
        foreach($result as $item){
            $merchantId = $item['merchant_id'];
            $app_market = $item['appMarket'];
            if(strstr($field,"money")) {
                if (!isset($data[$merchantId][$app_market][$field])) {
                    $data[$merchantId][$app_market][$field] = 0;
                }
                $data[$merchantId][$app_market][$field]+=$item['amount'];
            }else{
                if(!isset($data[$merchantId][$app_market][$field])){
                    $data[$merchantId][$app_market][$field]=0;
                }
                $data[$merchantId][$app_market][$field]++;
            }
        }
    }

    private function _saveData($data,$date){
        foreach($data as $merchantId=> $app_market_arr){
            foreach($app_market_arr as $app_market=> $value){
                $reapply_data = ReApplyData::find()->where(['date'=>$date,'merchant_id'=>$merchantId,'app_market'=>$app_market])->one();
                if(empty($reapply_data)){
                    $reapply_data = new ReApplyData();
                    $reapply_data->created_at=time();
                    $reapply_data->merchant_id=$merchantId;
                    $reapply_data->app_market=$app_market;
                    $reapply_data->date=$date;
                }
                $reapply_data->repay_num=$value['repay_num']??0;
                $reapply_data->borrow_apply_num=$value['borrow_apply_num']??0;
                $reapply_data->borrow_apply1_num=$value['borrow_apply1_num']??0;
                $reapply_data->borrow_apply7_num=$value['borrow_apply7_num']??0;
                $reapply_data->borrow_apply14_num=$value['borrow_apply14_num']??0;
                $reapply_data->borrow_apply30_num=$value['borrow_apply30_num']??0;
                $reapply_data->borrow_apply31_num=$value['borrow_apply31_num']??0;
                $reapply_data->borrow_succ_num=$value['borrow_succ_num']??0;
                $reapply_data->borrow_succ1_num=$value['borrow_succ1_num']??0;
                $reapply_data->borrow_succ7_num=$value['borrow_succ7_num']??0;
                $reapply_data->borrow_succ14_num=$value['borrow_succ14_num']??0;
                $reapply_data->borrow_succ30_num=$value['borrow_succ30_num']??0;
                $reapply_data->borrow_succ31_num=$value['borrow_succ31_num']??0;
                $reapply_data->borrow_succ_money=$value['borrow_succ_money']??0;
                $reapply_data->borrow_succ1_money=$value['borrow_succ1_money']??0;
                $reapply_data->borrow_succ7_money=$value['borrow_succ7_money']??0;
                $reapply_data->borrow_succ14_money=$value['borrow_succ14_money']??0;
                $reapply_data->borrow_succ30_money=$value['borrow_succ30_money']??0;
                $reapply_data->borrow_succ31_money=$value['borrow_succ31_money']??0;
                $reapply_data->updated_at=time();
                if(!$reapply_data->save()){
                    CommonHelper::stdout("还款复借数据保存失败");
                }
            }
        }
    }


    public function _exportAgainReapy($data){
        $this->_setcsvHeader('每日还款复借数据.csv');
        Util::cliLimitChange(1024);
        $items = [];
        if(!empty($data)) {
            foreach($data as $date => $rows){
                $items[] = [
                    '日期'=> $rows['date'],
                    '渠道' =>  $rows['app_market'],
                    '当天还款人数' => $rows['repay_num'] ?? 0,
                    '复借申请人数	' => $rows['borrow_apply_num'] ?? 0,
                    '复借申请率	' => empty($rows['repay_num'])? 0 : sprintf("%0.2f", ($rows['borrow_apply_num']/$rows['repay_num'])*100) .'%',
                    '复借成功人数' => $rows['borrow_succ_num'] ?? 0,
                    '复借成功率	' => empty($rows['borrow_apply_num'])? 0 : sprintf("%0.2f", ($rows['borrow_succ_num']/$rows['borrow_apply_num'])*100) .'%',
                    '当日复借人数' => $rows['borrow_apply1_num'] ?? 0,
                    '当日复借申请率' => empty($rows['repay_num'])? 0 : sprintf("%0.2f", ($rows['borrow_apply1_num']/$rows['repay_num'])*100) .'%',
                    '当日复借成功人数' => $rows['borrow_succ1_num'] ?? 0,
                    '7日内复借申请人数' => $rows['borrow_apply7_num'] ?? 0,
                    '7日内复借申请率' => empty($rows['repay_num'])? 0 : sprintf("%0.2f", ($rows['borrow_apply7_num']/$rows['repay_num'])*100) .'%',
                    '7日内复借成功人数' => $rows['borrow_succ7_num'] ?? 0,
                    '7日内复借成功率' => empty($rows['borrow_apply7_num'])? 0 : sprintf("%0.2f", ($rows['borrow_succ7_num']/$rows['borrow_apply7_num'])*100) .'%',
                    '10日内复借申请人数' => $rows['borrow_apply14_num'] ?? 0,
                    '10日内复借申请率' => empty($rows['repay_num'])? 0 : sprintf("%0.2f", ($rows['borrow_apply14_num']/$rows['repay_num'])*100) .'%',
                    '10日内复借成功人数' => $rows['borrow_succ14_num'] ?? 0,
                    '10日内复借成功率' => empty($rows['borrow_apply14_num'])? 0 : sprintf("%0.2f", ($rows['borrow_succ14_num']/$rows['borrow_apply14_num'])*100) .'%',
                    '30日内复借申请人数' => $rows['borrow_apply30_num'] ?? 0,
                    '30日内复借申请率' => empty($rows['repay_num'])? 0 : sprintf("%0.2f", ($rows['borrow_apply30_num']/$rows['repay_num'])*100) .'%',
                    '30日内复借成功人数' => $rows['borrow_succ30_num'] ?? 0,
                    '30日内复借成功率' => empty($rows['borrow_apply30_num'])? 0 : sprintf("%0.2f", ($rows['borrow_succ30_num']/$rows['borrow_apply30_num'])*100) .'%',
                    '31日内复借申请人数' => $rows['borrow_apply31_num'] ?? 0,
                    '31日内复借申请率' => empty($rows['repay_num'])? 0 : sprintf("%0.2f", ($rows['borrow_apply31_num']/$rows['repay_num'])*100) .'%',
                    '31日内复借成功人数' => $rows['borrow_succ31_num'] ?? 0,
                    '31日内复借成功率' => empty($rows['borrow_apply31_num'])? 0 : sprintf("%0.2f", ($rows['borrow_succ31_num']/$rows['borrow_apply31_num'])*100) .'%',
                ];
            }
        }
        echo $this->_array2csv($items);
        exit;
    }

    /**
     * @name 组合数据 /_setBarChart
     */
    public function _setBarChart($title, $params) {
        return [
            'title' => $title,
            'data' => $params,
        ];
    }
}
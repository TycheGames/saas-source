<?php

namespace backend\controllers;


use backend\models\Merchant;
use backend\models\search\DailyTradeDataSearch;
use common\helpers\Util;
use common\models\fund\LoanFund;
use common\models\package\PackageSetting;
use common\models\stats\DailyRegisterConver;
use common\models\stats\DailyTradeData;
use common\models\stats\StatisticsDayData;
use common\models\stats\StatisticsLoan2UserStructure;
use common\models\stats\StatisticsLoanUserStructure;
use common\models\stats\UserStructureOrderTransform;
use common\models\user\UserRegisterInfo;
use common\services\stats\DayDataStatisticsService;
use common\services\stats\DayOrderDataStatisticsService;
use common\services\stats\UserStructureOrderTransformService;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;
use Yii;

class DataStatsFullPlatformController extends BaseController
{
    //统计只读库
    private function getStatsReadDb(){
        return \yii::$app->db_stats_read;
    }

    //统计主库
    private function getStatsDb(){
        return \yii::$app->db_stats;
    }

    /**
     * @name 用户数据-每日借还款数据对比
     * @return string
     */
    public function actionDailyTradeData(){
        $add_start = $this->request->get('add_start', date('Y-m-d', time() - 7 * 86400));
        $add_end = $this->request->get('add_end', date('Y-m-d', time()));
        $loan_type = $this->request->get('loan_type', 0);
        $repay_type = $this->request->get('repay_type', 0);
        $data_type = $this->request->get('data_type',0);
        $contrast_type = $this->request->get('contrast_type',0);

        if (!empty($add_start)) {
            $pre_date = $add_start;
        }else{
            $pre_date = date('Y-m-d', time() );
        }
        if (!empty($add_end)) {
            $today_date = $add_end;
        }else{
            $today_date = date('Y-m-d', time()+ 86400); //默认显示7天的数据
        }

        $searchForm = new DailyTradeDataSearch();
        $searchArray = $searchForm->search($this->request->get());

        // 列表查询部分
        $query = DailyTradeData::find()->where($searchArray)->orderBy("date desc");
//        echo $query->createCommand()->getRawSql();
        $countQuery = clone $query;
        $pages = new Pagination(['totalCount' => $countQuery->count('*')]);
        $pages->pageSize = 1000;
        if(!empty($contrast_type)){
            $trade_data = [];
        }else{
            $trade_data = $query->offset($pages->offset)->limit($pages->limit)
                ->select([
                    'sum(apply_num) as apply_num',
                    'sum(apply_money) as apply_money',
                    'sum(manual_order_num) as manual_order_num',
                    'sum(loan_num) as loan_num',
                    'sum(loan_money) as loan_money',
                    'sum(repayment_num) as repayment_num',
                    'sum(repayment_money) as repayment_money',
                    'sum(active_repayment) as active_repayment',
                    'sum(repays_money) as repays_money',
                    'sum(fee) as fee',
                    'sum(interests) as interests',
                    'hour',
                    'date',
                    'user_type',
                ])
                ->asArray()
                ->groupBy('hour, date, user_type')
                ->all($this->getStatsDb());
        }

        $data=[];
        foreach($trade_data as $value){
            $hour= $value['hour'];
            $date=$value['date'];
            if($value['user_type']==0){
                $data[$date][$hour]['apply_num_0']=$value['apply_num'];
                $data[$date][$hour]['apply_money_0']=$value['apply_money'];
                $data[$date][$hour]['manual_order_num_0']=$value['manual_order_num'];
                $data[$date][$hour]['loan_num_0']=$value['loan_num'];
                $data[$date][$hour]['loan_money_0']=$value['loan_money'];
                $data[$date][$hour]['repayment_num_0']=$value['repayment_num'];
                $data[$date][$hour]['active_repayment_0']=$value['active_repayment'];
                $data[$date][$hour]['repayment_money_0']=$value['repayment_money'];
                $data[$date][$hour]['pass_rate_0']= empty($value['apply_num']) ? 0 : round($value['loan_num'] / $value['apply_num'],4);
                $data[$date][$hour]['repay_rate_0']=empty($value['repays_money']) ? 0 : round(($value['repayment_money'])/$value['repays_money'],4);
            }
            if($value['user_type']==3){
                $data[$date][$hour]['apply_num_1']=$value['apply_num'];
                $data[$date][$hour]['apply_money_1']=$value['apply_money'];
                $data[$date][$hour]['manual_order_num_1']=$value['manual_order_num'];
                $data[$date][$hour]['loan_num_1']=$value['loan_num'];
                $data[$date][$hour]['loan_money_1']=$value['loan_money'];
                $data[$date][$hour]['repayment_num_1']=$value['repayment_num'];
                $data[$date][$hour]['active_repayment_1']=$value['active_repayment'];
                $data[$date][$hour]['repayment_money_1']=$value['repayment_money'];
                $data[$date][$hour]['pass_rate_1']=empty($value['apply_num']) ? 0 : round($value['loan_num'] / $value['apply_num'],4);
                $data[$date][$hour]['repay_rate_1']=empty($value['repays_money']) ? 0 : round(($value['repayment_money'])/$value['repays_money'],4);
            }
            if($value['user_type']==4){
                $data[$date][$hour]['apply_num_2']=$value['apply_num'];
                $data[$date][$hour]['apply_money_2']=$value['apply_money'];
                $data[$date][$hour]['manual_order_num_2']=$value['manual_order_num'];
                $data[$date][$hour]['loan_num_2']=$value['loan_num'];
                $data[$date][$hour]['loan_money_2']=$value['loan_money'];
                $data[$date][$hour]['repayment_num_2']=$value['repayment_num'];
                $data[$date][$hour]['active_repayment_2']=$value['active_repayment'];
                $data[$date][$hour]['repayment_money_2']=$value['repayment_money'];
                $data[$date][$hour]['pass_rate_2']=empty($value['apply_num']) ? 0 : round($value['loan_num'] / $value['apply_num'],4);
                $data[$date][$hour]['repay_rate_2']=empty($value['repays_money']) ? 0 : round(($value['repayment_money'])/$value['repays_money'],4);
            }
        }
        if(!empty($data)){
            foreach($data as $k=> $value){
                krsort($value);
                $data[$k]= $value;
                $flag[]=$k;
            }
            array_multisort($flag, SORT_DESC, $data);
        }

        //根据选择的不同，显示不同类型的数据到折线图上

        $view_loan_type = '';
        $user_type=0;
        switch ($loan_type) {
            case 0:
                $view_loan_type = "loan_money";
                $user_type=0;
                break;
            case 1:
                $view_loan_type = "apply_num";
                $user_type=0;
                break;
            case 2:
                $view_loan_type = "apply_money";
                $user_type=0;
                break;
            case 3:
                $view_loan_type = "loan_num";
                $user_type=0;
                break;
            case 4:
                $view_loan_type = "pass_rate";
                $user_type=0;
                break;
            case 5:
                $view_loan_type = "apply_num";
                $user_type=4;
                break;
            case 6:
                $view_loan_type = "apply_money";
                $user_type=4;
                break;
            case 7:
                $view_loan_type = "loan_num";
                $user_type=4;
                break;
            case 8:
                $view_loan_type = "loan_money";
                $user_type=4;
                break;
            case 9:
                $view_loan_type = "pass_rate";
                $user_type=4;
                break;
            case 10:
                $view_loan_type = "apply_num";
                $user_type=3;
                break;
            case 11:
                $view_loan_type = "apply_money";
                $user_type=3;
                break;
            case 12:
                $view_loan_type = "loan_num";
                $user_type=3;
                break;
            case 13:
                $view_loan_type = "loan_money";
                $user_type=3;
                break;
            case 14:
                $view_loan_type = "pass_rate";
                $user_type=3;
                break;
            case 15:
                $view_loan_type = "manual_order_num";
                $user_type = 0;
                break;
//            case 16:
//                $view_loan_type = "test_order_num";
//                $user_type = 0;
//                break;
//            case 17:
//                $view_loan_type = "test_order_money";
//                $user_type = 0;
//                break;
            case 18:
                $view_loan_type = "loan_money_today";
                $user_type = 0;
                break;
            case 19:
                $view_loan_type = "apply_check_num";
                $user_type = 3;
                break;
            case 20:
                $view_loan_type = "apply_check_num";
                $user_type = 4;
                break;
            case 21:
                $view_loan_type = "apply_check_num";
                $user_type = 0;
                break;
            case 22:
                $view_loan_type = "manual_num";
                $user_type = 0;
                break;
            case 23:
                $view_loan_type = "reg_num";
                $user_type = 0;
                break;
        }

        // 借款折线图部分
        $date_time = array();
        for ($i = 1; $i <= 24; $i++) {
            $date_time[] = $i;
        }

        if(!empty($contrast_type)){
            $maps = DailyTradeData::find()->select('app_market')->where($searchArray)->andWhere(['=','user_type',$user_type])->asArray()->groupBy("app_market")->column();
            $trade_loan = DailyTradeData::find()->select([
                'sum(reg_num) as reg_num',
                'sum(apply_num) as apply_num',
                'sum(apply_money) as apply_money',
                'sum(apply_check_num) as apply_check_num',
                'sum(loan_num) as loan_num',
                'sum(loan_money) as loan_money',
                'sum(manual_order_num) as manual_order_num',
                'sum(manual_num) as manual_num',
                'sum(loan_money_today) as loan_money_today',
                'hour',
                'app_market',
            ])->where($searchArray)->andWhere(['=','user_type',$user_type])->asArray()->groupBy("app_market,hour")->all($this->getStatsDb());
        }else {
            $n = ceil((strtotime($today_date) - strtotime($pre_date)) / 86400);
            for ($i = 0; $i <= $n; $i++) {
                $maps[] = date('Y-m-d', strtotime($today_date) - $i * 86400);
            }

            $trade_loan = DailyTradeData::find()->select([
                'sum(reg_num) as reg_num',
                'sum(apply_num) as apply_num',
                'sum(apply_money) as apply_money',
                'sum(apply_check_num) as apply_check_num',
                'sum(loan_num) as loan_num',
                'sum(loan_money) as loan_money',
                'sum(manual_order_num) as manual_order_num',
                'sum(manual_num) as manual_num',
                'sum(loan_money_today) as loan_money_today',
                'hour',
                'date',
            ])->where($searchArray)->andWhere(['=','user_type',$user_type])->orderBy("date desc")->asArray()->groupBy("date,hour")->all($this->getStatsDb());
        }

        for ($i = 0; $i < count($maps); $i++) {
            $y_val[$maps[$i]] = array();
        }
        if ($trade_loan) {
            if(!empty($contrast_type)){
                foreach (array_reverse($trade_loan) as $key => $vl) {
                    for ($i = 0; $i < count($maps); $i++) {
                        if ($vl['app_market'] == $maps[$i]) {
                            $j = 0;
                            while ($j <= 23) {
                                if ($date_time[$j] == $vl['hour']) {
                                    if ($view_loan_type == "apply_money" || $view_loan_type == "loan_money" || $view_loan_type == 'test_order_money' || $view_loan_type == "loan_money_today") {
                                        $vl[$view_loan_type] = $vl[$view_loan_type] / 100;
                                    }
                                    if ($view_loan_type == "pass_rate") {
                                        $vl[$view_loan_type] = empty($vl['apply_num']) ? 0 : sprintf("%0.2f", $vl['loan_num'] / $vl['apply_num'] * 100);
                                    }
                                    $y_val[$maps[$i]][$j] = $vl[$view_loan_type];
                                } elseif (empty($y_val[$maps[$i]][$j])) {
                                    $y_val[$maps[$i]][$j] = 0;
                                }
                                ++$j;
                            }
                        }
                    }
                }
            }else{
                foreach (array_reverse($trade_loan) as $key => $vl) {
                    for ($i = 0; $i < count($maps); $i++) {
                        if ($vl['date'] == $maps[$i]) {
                            $j = 0;
                            while ($j <= 23) {
                                if ($date_time[$j] == $vl['hour']) {
                                    if ($view_loan_type == "apply_money" || $view_loan_type == "loan_money" || $view_loan_type == 'test_order_money' || $view_loan_type == "loan_money_today") {
                                        $vl[$view_loan_type] = $vl[$view_loan_type] / 100;
                                    }
                                    if ($view_loan_type == "pass_rate") {
                                        $vl[$view_loan_type] = empty($vl['apply_num']) ? 0 : sprintf("%0.2f", $vl['loan_num'] / $vl['apply_num'] * 100);
                                    }
                                    $y_val[$maps[$i]][$j] = $vl[$view_loan_type];
                                } elseif (empty($y_val[$maps[$i]][$j])) {
                                    $y_val[$maps[$i]][$j] = 0;
                                }
                                ++$j;
                            }
                        }
                    }
                }
            }
        }
        $date = date('Y-m-d');
        $h = date("H",time())+1;
        $legend_loan = array_values($maps);
        $series_loan = array();
        for ($j = 0; $j <count($maps); $j++) {
            $series_loan[] = array(
                'name' => $legend_loan[$j],
                'type' => 'line',
                'data' => $y_val[$maps[$j]],
            );
        }
        if($data_type == 0){ //汇总
            foreach ($series_loan as $key=> $value) {
                foreach ($value['data'] as $hour => $item) {
                    if(strstr($item,'.')){
                        $item=sprintf('%0.2f',$item);
                    }
                    $num = empty($item)?0:$item;
                    $hours = $hour-1;
                    if($hours == -1){
                        $series_loan[$key]['data'][$hour] = $num;
                    }else{
                        $name = empty($contrast_type) ? $value['name'] : $add_start;
                        if(isset($series_loan[$key]['data'][$hours]) && $name!=$date){
                            if(strstr($series_loan[$key]['data'][$hours],'.')){
                                $series_loan[$key]['data'][$hours]=sprintf('%0.2f',$series_loan[$key]['data'][$hours]);
                            }
                            $series_loan[$key]['data'][$hour] +=$series_loan[$key]['data'][$hours];
                        }else{
                            if($hour>$h-1){
                                unset($series_loan[$key]['data'][$hour]);
                            }else{
                                if(strstr($series_loan[$key]['data'][$hours],'.')){
                                    $series_loan[$key]['data'][$hours]=sprintf('%0.2f',$series_loan[$key]['data'][$hours]);
                                }
                                $series_loan[$key]['data'][$hour] +=$series_loan[$key]['data'][$hours];
                            }
                        }
                    }
                }
            }

        }else{  //分时
            foreach ($series_loan as $key=> $value) {
                foreach ($value['data'] as $hour => $item) {
                    if(strstr($item,'.')){
                        $item=sprintf('%0.2f',$item);
                    }
                    $name = empty($contrast_type) ? $value['name'] : $add_start;
                    if(isset($series_loan[$key]['data'][$hour])&&$name!=$date){
                        $series_loan[$key]['data'][$hour] = $item;//之前所有还款单数
                    }else{
                        if($hour>$h-1){
                            unset($series_loan[$key]['data'][$hour]);
                        }else{
                            $series_loan[$key]['data'][$hour] =$item;
                        }
                    }
                }
            }
        }

        $view_repay_type = '';
        $user_type=0;
        switch ($repay_type) {
            case 0:
                $view_repay_type = "repay_rate";
                $user_type=0;
                break;
            case 1:
                $view_repay_type = "repayment_num";
                $user_type=0;
                break;
            case 2:
                $view_repay_type = "repayment_money";
                $user_type=0;
                break;
            case 3:
                $view_repay_type = "repayment_num";
                $user_type=4;
                break;
            case 4:
                $view_repay_type = "repayment_money";
                $user_type=4;
                break;
            case 5:
                $view_repay_type = "repay_rate";
                $user_type=4;
                break;
            case 6:
                $view_repay_type = "repayment_num";
                $user_type=3;
                break;
            case 7:
                $view_repay_type = "repayment_money";
                $user_type=3;
                break;
            case 8:
                $view_repay_type = "repay_rate";
                $user_type=3;
                break;

            case 9:
                $view_repay_type = "active_repayment";
                $user_type=0;
                break;

            case 10:
                $view_repay_type = "active_repayment";
                $user_type=3;
                break;

            case 11:
                $view_repay_type = "active_repayment";
                $user_type=4;
                break;
        }

        if(!empty($contrast_type)){
            $trade_repay = DailyTradeData::find()->select([
                'sum(repayment_num) as repayment_num',
                'sum(repayment_money) as repayment_money',
                'sum(active_repayment) as active_repayment',
                'sum(fee) as fee',
                'sum(interests) as interests',
                'sum(repayment_num_tomorrow) as repayment_num_tomorrow',
                'sum(repayment_money_tomorrow) as repayment_money_tomorrow',
                'sum(active_repayment_tomorrow) as active_repayment_tomorrow',
                'sum(fee_tomorrow) as fee_tomorrow',
                'sum(interests_tomorrow) as interests_tomorrow',
                'hour',
                'app_market'
            ])->where(['=','user_type',$user_type])->andWhere($searchArray)->orderBy("hour desc")
                ->groupBy("hour")->asArray()->all($this->getStatsDb());

            $repays_money = DailyTradeData::find()->select("sum(repays_money) as repays_money,sum(repays_money_tomorrow) as repays_money_tomorrow,app_market")
                ->where($searchArray)
                ->andWhere(['=','user_type',$user_type])
                ->groupBy("app_market")->asArray()->all($this->getStatsDb());
            $repays_money_arr = ArrayHelper::map($repays_money, 'app_market', 'repays_money');
        }else{
            $trade_repay = DailyTradeData::find()->select([
                'sum(repayment_num) as repayment_num',
                'sum(repayment_money) as repayment_money',
                'sum(active_repayment) as active_repayment',
                'sum(fee) as fee',
                'sum(interests) as interests',
                'sum(repayment_num_tomorrow) as repayment_num_tomorrow',
                'sum(repayment_money_tomorrow) as repayment_money_tomorrow',
                'sum(active_repayment_tomorrow) as active_repayment_tomorrow',
                'sum(fee_tomorrow) as fee_tomorrow',
                'sum(interests_tomorrow) as interests_tomorrow',
                'hour',
                'date'
            ])
                ->where(['=','user_type',$user_type])->andWhere($searchArray)->orderBy("hour desc")
                ->groupBy("date, hour")->asArray()->all($this->getStatsDb());

            $repays_money = DailyTradeData::find()->select("sum(repays_money) as repays_money,sum(repays_money_tomorrow) as repays_money_tomorrow,date")
                ->where($searchArray)
                ->andWhere(['=','user_type',$user_type])
                ->groupBy("date")->asArray()->all($this->getStatsDb());
            $repays_money_arr = ArrayHelper::map($repays_money, 'date', 'repays_money');
        }

        $date_times = array();

        for ($i = 0; $i <= 24; $i++) {
            $date_times[] = $i;
        }
        for ($i = 0; $i < count($maps); $i++) {
            $y_val[$maps[$i]] = array();
        }
        if ($trade_repay) {
            if(!empty($contrast_type)){
                foreach (array_reverse($trade_repay) as $key => $vl) {
                    for ($i = 0; $i < count($maps); $i++) {
                        if ($vl['app_market'] == $maps[$i]) {
                            $j = 0;
                            while ($j <= 24) {
                                if ($date_times[$j] == $vl['hour']) {
                                    if ($view_repay_type == "repayment_money") {
                                        $vl[$view_repay_type] = $vl[$view_repay_type] / 100;
                                    }
                                    if ($view_repay_type == "repay_rate") {
                                        $vl[$view_repay_type] = empty($repays_money_arr[$vl['app_market']]) ? 0 : sprintf("%0.2f", $vl['repayment_money'] / $repays_money_arr[$vl['appMarket']] * 100);
                                    }
                                    $y_val[$maps[$i]][$j] = $vl[$view_repay_type];
                                } elseif (empty($y_val[$maps[$i]][$j])) {
                                    $y_val[$maps[$i]][$j] = 0;
                                }
                                ++$j;
                            }
                        }
                    }
                }
            }else {
                foreach (array_reverse($trade_repay) as $key => $vl) {
                    for ($i = 0; $i < count($maps); $i++) {
                        if ($vl['date'] == $maps[$i]) {
                            $j = 0;
                            while ($j <= 24) {
                                if ($date_times[$j] == $vl['hour']) {
                                    if ($view_repay_type == "repayment_money") {
                                        $vl[$view_repay_type] = $vl[$view_repay_type] / 100;
                                    }
                                    if ($view_repay_type == "repay_rate") {
                                        $vl[$view_repay_type] = empty($repays_money_arr[$vl['date']]) ? 0 : sprintf("%0.2f", $vl['repayment_money'] / $repays_money_arr[$vl['date']] * 100);
                                    }
                                    $y_val[$maps[$i]][$j] = $vl[$view_repay_type];
                                } elseif (empty($y_val[$maps[$i]][$j])) {
                                    $y_val[$maps[$i]][$j] = 0;
                                }
                                ++$j;
                            }
                        }
                    }
                }
            }
        }
        $legend_repay = array_values($maps);
        $series_repay = array();
        for ($j = 0; $j <count($maps); $j++) {
            $series_repay[] = array(
                'name' => $legend_repay[$j],
                'type' => 'line',
                'data' => $y_val[$maps[$j]],
            );
        }

        if($data_type == 0){
            foreach ($series_repay as $key=> $value) {
                foreach ($value['data'] as $hour => $item) {
                    $num = empty($item)?0:$item;
                    $hours = $hour-1;
                    if(strstr($num,'.')){
                        $num=sprintf('%0.2f',$num);
                    }
                    if($hours == -1){
                        $series_repay[$key]['data'][$hour] = $num;//所有还款单数
                    }else{
                        $name = empty($contrast_type) ? $value['name'] : $add_start;
                        if(isset($series_repay[$key]['data'][$hours])&&$name!=$date){
                            if(strstr($series_repay[$key]['data'][$hours],'.')){
                                $series_repay[$key]['data'][$hours]=sprintf('%0.2f',$series_repay[$key]['data'][$hours]);
                            }
                            $series_repay[$key]['data'][$hour] +=$series_repay[$key]['data'][$hours];//之前所有还款单数
                        }else{
                            if($hour>$h){
                                unset($series_repay[$key]['data'][$hour]);
                            }else{
                                if(strstr($series_repay[$key]['data'][$hours],'.')){
                                    $series_repay[$key]['data'][$hours]=sprintf('%0.2f',$series_repay[$key]['data'][$hours]);
                                }
                                $series_repay[$key]['data'][$hour] +=$series_repay[$key]['data'][$hours];
                            }
                        }
                    }
                }
            }
        }else{
            foreach ($series_repay as $key=> $value) {
                foreach ($value['data'] as $hour => $item) {
                    if(strstr($item,'.')){
                        $item=sprintf('%0.2f',$item);
                    }
                    $name = empty($contrast_type) ? $value['name'] : $add_start;
                    if(isset($series_repay[$key]['data'][$hour])&&$name!=$date){
                        $series_repay[$key]['data'][$hour] = $item;//之前所有还款单数
                    }else{
                        if($hour>$h){
                            unset($series_repay[$key]['data'][$hour]);
                        }else{
                            $series_repay[$key]['data'][$hour] =$item;
                        }
                    }
                }
            }
        }


        // 每日的下一天数据======================================================
        // 还款折线图下一天

        if(!empty($contrast_type)){
            $repays_money_tomorrow_arr = ArrayHelper::map($repays_money, 'app_market', 'repays_money_tomorrow');
        }else{
            $repays_money_tomorrow_arr = ArrayHelper::map($repays_money, 'date', 'repays_money_tomorrow');
        }

        for ($i = 0; $i < count($maps); $i++) {
            $y_val[$maps[$i]] = array();
        }
        if ($trade_repay) {
            if(!empty($contrast_type)){
                foreach (array_reverse($trade_repay) as $key => $vl) {
                    for ($i = 0; $i <count($maps); $i++) {
                        if ($vl['app_market'] == $maps[$i]) {
                            $j = 0;
                            while ($j <= 24) {
                                if ($date_times[$j] == $vl['hour']) {
                                    if($view_repay_type == "repayment_money"){
                                        $vl[$view_repay_type] = $vl['repayment_money_tomorrow']/100;
                                    }
                                    if($view_repay_type == "repay_rate"){
                                        $vl[$view_repay_type] =empty($repays_money_tomorrow_arr[$vl['app_market']])?0:sprintf("%0.2f", $vl['repayment_money_tomorrow']/$repays_money_tomorrow_arr[$vl['app_market']]*100);
                                    }
                                    $y_val[$maps[$i]][$j] = $vl[$view_repay_type];
                                } elseif (empty($y_val[$maps[$i]][$j])) {
                                    $y_val[$maps[$i]][$j] = 0;
                                }
                                ++$j;
                            }
                        }
                    }
                }
            }else{
                foreach (array_reverse($trade_repay) as $key => $vl) {
                    for ($i = 0; $i <count($maps); $i++) {
                        if ($vl['date'] == $maps[$i]) {
                            $j = 0;
                            while ($j <= 24) {
                                if ($date_times[$j] == $vl['hour']) {
                                    if($view_repay_type == "repayment_money"){
                                        $vl[$view_repay_type] = $vl['repayment_money_tomorrow']/100;
                                    }
                                    if($view_repay_type == "repay_rate"){
                                        $vl[$view_repay_type] =empty($repays_money_tomorrow_arr[$vl['date']])?0:sprintf("%0.2f", $vl['repayment_money_tomorrow']/$repays_money_tomorrow_arr[$vl['date']]*100);
                                    }
                                    $y_val[$maps[$i]][$j] = $vl[$view_repay_type];
                                } elseif (empty($y_val[$maps[$i]][$j])) {
                                    $y_val[$maps[$i]][$j] = 0;
                                }
                                ++$j;
                            }
                        }
                    }
                }
            }
        }

        $series_repay_tomorrow = array();
        for ($j = 0; $j <count($maps); $j++) {
            $series_repay_tomorrow[] = array(
                'name' => $legend_repay[$j],
                'type' => 'line',
                'data' => $y_val[$maps[$j]],
            );
        }

        if($data_type == 0){
            foreach ($series_repay_tomorrow as $key=> $value) {
                foreach ($value['data'] as $hour => $item) {
                    $num = empty($item)?0:$item;
                    $hours = $hour-1;
                    if(strstr($num,'.')){
                        $num=sprintf('%0.2f',$num);
                    }
                    if($hours == -1){
                        $series_repay_tomorrow[$key]['data'][$hour] = $num;//所有还款单数
                    }else{
                        $name = empty($contrast_type) ? $value['name'] : $add_start;
                        if(isset($series_repay_tomorrow[$key]['data'][$hours])&&$name!=$date){
                            if(strstr($series_repay_tomorrow[$key]['data'][$hours],'.')){
                                $series_repay_tomorrow[$key]['data'][$hours]=sprintf('%0.2f',$series_repay_tomorrow[$key]['data'][$hours]);
                            }
                            $series_repay_tomorrow[$key]['data'][$hour] +=$series_repay_tomorrow[$key]['data'][$hours];//之前所有还款单数
                        }else{
                            if($hour>$h){
                                unset($series_repay_tomorrow[$key]['data'][$hour]);
                            }else{
                                if(strstr($series_repay_tomorrow[$key]['data'][$hours],'.')){
                                    $series_repay_tomorrow[$key]['data'][$hours]=sprintf('%0.2f',$series_repay_tomorrow[$key]['data'][$hours]);
                                }
                                $series_repay_tomorrow[$key]['data'][$hour] +=$series_repay_tomorrow[$key]['data'][$hours];
                            }
                        }
                    }
                }
            }
        }else{
            foreach ($series_repay_tomorrow as $key=> $value) {
                foreach ($value['data'] as $hour => $item) {
                    if(strstr($item,'.')){
                        $item=sprintf('%0.2f',$item);
                    }
                    $name = empty($contrast_type) ? $value['name'] : $add_start;
                    if(isset($series_repay_tomorrow[$key]['data'][$hour])&&$name!=$date){
                        $series_repay_tomorrow[$key]['data'][$hour] = $item;//之前所有还款单数
                    }else{
                        if($hour>$h){
                            unset($series_repay_tomorrow[$key]['data'][$hour]);
                        }else{
                            $series_repay_tomorrow[$key]['data'][$hour] =$item;
                        }
                    }
                }
            }
        }
        //  ======================================================
        //获取脚本最后更新时间
        $last_update_query = DailyTradeData::find()->select(['updated_at'])->orderBy(['updated_at' => SORT_DESC])->asArray()->one();
        $update_time = (!empty($last_update_query['updated_at'])) ? date("Y-m-d H:i:s",$last_update_query['updated_at']) : '';
        $searchList = UserRegisterInfo::getChannelSearchList();
        $packageNameList = ArrayHelper::getColumn(DailyTradeData::find()->select(['package_name'])->distinct(['package_name'])->where(['merchant_id' => $this->merchantIds])->indexBy(['package_name'])->asArray()->all(),'package_name','package_name');
        return $this->render("hour-trade-data-new-v2", [
            'trade_data' => $data,
            'update_time' => $update_time,
            'pages' => $pages,
            'legend_loan' => $legend_loan,
            'legend_repay' => $legend_repay,
            'x' => $date_time,
            'xs' => $date_times,
            'series_loan' => $series_loan,
            'series_repay' => $series_repay,
            'series_repay_tomorrow' => $series_repay_tomorrow,
            'add_start' => $add_start,
            'add_end' => $add_end,
            'searchList' => $searchList,
            'packageNameList' => $packageNameList,
            'isNotMerchantAdmin' => $this->isNotMerchantAdmin
        ]);
    }


    /**
     * @name 用户数据-每日借款数据（本金-结构）
     * @date 2017-03
     * @return string|void
     */
    public function actionDailyDataUserStructure() {
        if(!$this->isNotMerchantAdmin){
            die('not find');
        }
        ini_set('memory_limit', '1024M');

        $add_start = $this->request->get('add_start');
        $add_end = $this->request->get('add_end');
        $channel = $this->request->get('channel');
        $fund_id = $this->request->get('fund_id');
        $merchantIds = $this->request->get('merchant_id',[]);
        $appMarket = $this->request->get('app_market',[]);
        $mediaSource = $this->request->get('media_source',[]);
        $packageName = $this->request->get('package_name',[]);

        $condition[] = 'and';
        if(!empty($appMarket)){
            $condition[] = ['app_market' => $appMarket];
        }
        if(!empty($mediaSource)){
            $condition[] = ['media_source' => $mediaSource];
        }
        if(!empty($packageName)){
            $condition[] = ['package_name' => $packageName];
        }
        if($fund_id && !in_array(0, $fund_id)){
            $condition[] = ['fund_id' => $fund_id];
        }
        if($merchantIds){
            $condition[] = ['merchant_id' => $merchantIds];
        }

        //商户限制
        if ($add_start) {//开始日期
            $add_start = strtotime($add_start);
        } else {
            $add_start = strtotime(date('Y-m-d', time() - 30 * 24 * 3600));
        }
        if ($add_end) {//结束日期
            $add_end = strtotime($add_end) + 24 * 3600;
        } else {
            $add_end = strtotime(date('Y-m-d', time())) + 24 * 3600;
        }
        $condition[] = ['>=', 'date_time', $add_start];
        $condition[] = ['<', 'date_time', $add_end];
        $query = StatisticsLoanUserStructure::find()
            ->select([
                'date_time',
                'loan_term',
                'loan_num' => 'SUM(loan_num)',
                'loan_num_old' => 'SUM(loan_num_old)',
                'loan_num_new' => 'SUM(loan_num_new)',
                'loan_num_all_old_loan_new' => 'SUM(loan_num_all_old_loan_new)',
                'loan_money' => 'SUM(loan_money)',
                'loan_money_old' => 'SUM(loan_money_old)',
                'loan_money_new' => 'SUM(loan_money_new)',
                'loan_money_all_old_loan_new' => 'SUM(loan_money_all_old_loan_new)',
                'updated_at',
                'created_at'
            ])
            ->where($condition)
            ->andWhere(['merchant_id' => $this->merchantIds])
            ->groupBy(['date_time','loan_term'])
            ->orderBy(['date_time' => SORT_DESC]);
        $info = $query->asArray()->all($this->getStatsDb());
        $last_list = StatisticsLoanUserStructure::find()->orderBy(['updated_at'=>SORT_DESC])->one();
        $last_update_at = $last_list['updated_at'];

        $total_loan_num = 0;
        $total_loan_money = 0;
        $total_loan_num_new = 0;
        $total_loan_num_old = 0;
        $total_loan_num_all_old_loan_new = 0;
        $total_loan_money_new = 0;
        $total_loan_money_old = 0;
        $total_loan_money_all_old_loan_new = 0;

        $ret = $query->all($this->getStatsDb());
        foreach ($ret as $item) {
            $total_loan_num = $total_loan_num + $item['loan_num'];
            $total_loan_money += $item['loan_money'];
            $total_loan_num_new += $item['loan_num_new'];
            $total_loan_num_old += $item['loan_num_old'];
            $total_loan_num_all_old_loan_new += $item['loan_num_all_old_loan_new'];
            $total_loan_money_new += $item['loan_money_new'];
            $total_loan_money_old += $item['loan_money_old'];
            $total_loan_money_all_old_loan_new += $item['loan_money_all_old_loan_new'];
        }
        $fundList = LoanFund::getAllFundArray($this->merchantIds);
        $appMarketList = ArrayHelper::getColumn(StatisticsLoanUserStructure::find()->select(['app_market'])->distinct(['app_market'])->where(['merchant_id' => $this->merchantIds])->indexBy(['app_market'])->asArray()->all(),'app_market','app_market');
        $mediaSourceList = ArrayHelper::getColumn(StatisticsLoanUserStructure::find()->select(['media_source'])->distinct(['media_source'])->where(['merchant_id' => $this->merchantIds])->indexBy(['media_source'])->asArray()->all(),'media_source','media_source');
        $packageNameList = ArrayHelper::getColumn(StatisticsLoanUserStructure::find()->select(['package_name'])->distinct(['package_name'])->where(['merchant_id' => $this->merchantIds])->indexBy(['package_name'])->asArray()->all(),'package_name','package_name');
        if(isset($mediaSourceList[''])) {
            $mediaSourceList['-'] = '-';
            unset($mediaSourceList['']);
        }
        return $this->render('daily-data-user-structure', array(
            'data' => $info,
            'total_loan_num' => $total_loan_num,
            'total_loan_money' => $total_loan_money,
            'total_loan_num_new' => $total_loan_num_new,
            'total_loan_num_old' => $total_loan_num_old,
            'total_loan_num_all_old_loan_new' => $total_loan_num_all_old_loan_new,
            'total_loan_money_new' => $total_loan_money_new,
            'total_loan_money_old' => $total_loan_money_old,
            'total_loan_money_all_old_loan_new' => $total_loan_money_all_old_loan_new,
            'channel' => $channel,
            'last_update_at' => $last_update_at,
            'fundList' => $fundList,
            'appMarketList' => $appMarketList,
            'mediaSourceList' => $mediaSourceList,
            'packageNameList' => $packageNameList,
        ));
    }


    /**
     * @name 用户数据-每日借款数据（放款金额-结构）
     * @date 2017-03
     * @return string|void
     */
    public function actionDailyData2UserStructure() {
        if(!$this->isNotMerchantAdmin){
            die('not find');
        }
        ini_set('memory_limit', '1024M');

        $add_start = $this->request->get('add_start');
        $add_end = $this->request->get('add_end');
        $channel = $this->request->get('channel');
        $fund_id = $this->request->get('fund_id');
        $merchantIds = $this->request->get('merchant_id',[]);
        $appMarket = $this->request->get('app_market',[]);
        $mediaSource = $this->request->get('media_source',[]);
        $packageName = $this->request->get('package_name',[]);

        $condition[] = 'and';
        if(!empty($appMarket)){
            $condition[] = ['app_market' => $appMarket];
        }
        if(!empty($mediaSource)){
            $condition[] = ['media_source' => $mediaSource];
        }
        if(!empty($packageName)){
            $condition[] = ['package_name' => $packageName];
        }
        if($fund_id && !in_array(0, $fund_id)){
            $condition[] = ['fund_id' => $fund_id];
        }
        if($merchantIds){
            $condition[] = ['merchant_id' => $merchantIds];
        }

        //商户限制
        if ($add_start) {//开始日期
            $add_start = strtotime($add_start);
        } else {
            $add_start = strtotime(date('Y-m-d', time() - 30 * 24 * 3600));
        }
        if ($add_end) {//结束日期
            $add_end = strtotime($add_end) + 24 * 3600;
        } else {
            $add_end = strtotime(date('Y-m-d', time())) + 24 * 3600;
        }

        $condition[] = ['>=', 'date_time', $add_start];
        $condition[] = ['<', 'date_time', $add_end];
        $query = StatisticsLoan2UserStructure::find()
            ->select([
                'date_time',
                'loan_term',
                'loan_num' => 'SUM(loan_num)',
                'loan_num_old' => 'SUM(loan_num_old)',
                'loan_num_new' => 'SUM(loan_num_new)',
                'loan_num_all_old_loan_new' => 'SUM(loan_num_all_old_loan_new)',
                'loan_money' => 'SUM(loan_money)',
                'loan_money_old' => 'SUM(loan_money_old)',
                'loan_money_new' => 'SUM(loan_money_new)',
                'loan_money_all_old_loan_new' => 'SUM(loan_money_all_old_loan_new)',
                'updated_at',
                'created_at'
            ])
            ->where($condition)
            ->andWhere(['merchant_id' => $this->merchantIds])
            ->groupBy(['date_time','loan_term'])
            ->orderBy(['date_time' => SORT_DESC]);
        $info = $query->asArray()->all($this->getStatsDb());
        $last_list = StatisticsLoan2UserStructure::find()->orderBy(['updated_at'=>SORT_DESC])->one();
        $last_update_at = $last_list['updated_at'];

        $total_loan_num = 0;
        $total_loan_money = 0;
        $total_loan_num_new = 0;
        $total_loan_num_old = 0;
        $total_loan_num_all_old_loan_new = 0;
        $total_loan_money_new = 0;
        $total_loan_money_old = 0;
        $total_loan_money_all_old_loan_new = 0;

        $ret = $query->all($this->getStatsDb());
        foreach ($ret as $item) {
            $total_loan_num = $total_loan_num + $item['loan_num'];
            $total_loan_money += $item['loan_money'];
            $total_loan_num_new += $item['loan_num_new'];
            $total_loan_num_old += $item['loan_num_old'];
            $total_loan_num_all_old_loan_new += $item['loan_num_all_old_loan_new'];
            $total_loan_money_new += $item['loan_money_new'];
            $total_loan_money_old += $item['loan_money_old'];
            $total_loan_money_all_old_loan_new += $item['loan_money_all_old_loan_new'];
        }
        $fundList = LoanFund::getAllFundArray($this->merchantIds);
        $appMarketList = ArrayHelper::getColumn(StatisticsLoan2UserStructure::find()->select(['app_market'])->distinct(['app_market'])->where(['merchant_id' => $this->merchantIds])->indexBy(['app_market'])->asArray()->all(),'app_market','app_market');
        $mediaSourceList = ArrayHelper::getColumn(StatisticsLoan2UserStructure::find()->select(['media_source'])->distinct(['media_source'])->where(['merchant_id' => $this->merchantIds])->indexBy(['media_source'])->asArray()->all(),'media_source','media_source');
        $packageNameList = ArrayHelper::getColumn(StatisticsLoan2UserStructure::find()->select(['package_name'])->distinct(['package_name'])->where(['merchant_id' => $this->merchantIds])->indexBy(['package_name'])->asArray()->all(),'package_name','package_name');
        if(isset($mediaSourceList[''])) {
            $mediaSourceList['-'] = '-';
            unset($mediaSourceList['']);
        }
        return $this->render('daily-data2-user-structure', array(
            'data' => $info,
            'total_loan_num' => $total_loan_num,
            'total_loan_money' => $total_loan_money,
            'total_loan_num_new' => $total_loan_num_new,
            'total_loan_num_old' => $total_loan_num_old,
            'total_loan_num_all_old_loan_new' => $total_loan_num_all_old_loan_new,
            'total_loan_money_new' => $total_loan_money_new,
            'total_loan_money_old' => $total_loan_money_old,
            'total_loan_money_all_old_loan_new' => $total_loan_money_all_old_loan_new,
            'channel' => $channel,
            'last_update_at' => $last_update_at,
            'fundList' => $fundList,
            'appMarketList' => $appMarketList,
            'mediaSourceList' => $mediaSourceList,
            'packageNameList' => $packageNameList,
        ));
    }

    /**
     * @name 用户数据-每日还款金额数据（结构）
     */
    public function actionDayDataRepaymentStatisticsUserStructure() {
        return $this->repaymentStatistics('loan_money');
    }

    /**
     * @name 用户数据-每日还款单数数据（结构）
     */
    public function actionDayDataRepaymentNumStatisticsUserStructure() {
        return $this->repaymentStatistics('loan_num');
    }

    private function repaymentStatistics($type){
        if(!$this->isNotMerchantAdmin){
            die('not find');
        }
        $condition[] = 'and';
        $search = $this->request->get();
        $fund_id = $search['fund_id']??0;
        $appMarket = $search['app_market']??[];
        $mediaSource = $search['media_source']??[];
        $packageName = $search['package_name']??[];
        $newType = 5;
        $allOldLoanNewType = 6;
        $oldType = 7;
        $field="user_type,date";
        if (!empty($search['begin_created_at'])) {
            $begin_created_at = str_replace(' ', '', $search['begin_created_at']);
        }else{
            $begin_created_at =date('Y-m-d', time() - 7*86400 );
        }
        if (!empty($search['end_created_at'])) {
            $end_created_at = str_replace(' ', '', $search['end_created_at']);
        }else {
            $end_created_at = date('Y-m-d', time() + 7*86400);
        }

        $condition[] = ['>=', 'date', $begin_created_at];
        $condition[] = ['<=', 'date', $end_created_at];

        if(!empty($appMarket)){
            $condition[] = ['app_market' => $appMarket];
        }
        if(!empty($mediaSource)){
            $condition[] = ['media_source' => $mediaSource];
        }
        if(!empty($packageName)){
            $condition[] = ['package_name' => $packageName];
        }
        if($fund_id && !in_array(0, $fund_id)){
            $condition[] = ['fund_id' => $fund_id];
        }
        $merchantIds = $this->request->get('merchant_id',[]);
        if($merchantIds){
            $condition[] = ['merchant_id' => $merchantIds];
        }

        $info = StatisticsDayData::find()->select("sum(expire_num) as expire_num,
                      sum(expire_money) as expire_money,
                      sum(repay_num) as repay_num,
                      sum(repay_money) as repay_money,
                      sum(repay_zc_num) as repay_zc_num,
                      sum(repay_zc_money) as repay_zc_money,
                      sum(extend_num) as extend_num,
                      sum(extend_money) as extend_money,
                      user_type,
                      date")
            ->where($condition)
            ->andWhere(['merchant_id' => $this->merchantIds])
            ->groupBy($field)
            ->orderBy('date DESC')->asArray()->all($this->getStatsDb());

        $data = $total_data = [];
        $today_time = strtotime(date("Y-m-d", time()));
        $service =  new DayDataStatisticsService();

        foreach($info as $value){
            $date=$value['date'];
            if($value['user_type']==0){
                $service->_getReturnData($data, $total_data, $date, 0, $value, $today_time);
            }
            if(isset($value['user_type']) && $value['user_type'] == $newType){
                $service->_getReturnData($data, $total_data, $date, 1, $value, $today_time);
            }
            if(isset($value['user_type']) && $value['user_type'] == $allOldLoanNewType){
                $service->_getReturnData($data, $total_data, $date, 2, $value, $today_time);
            }
            if(isset($value['user_type']) && $value['user_type'] == $oldType){
                $service->_getReturnData($data, $total_data, $date, 3, $value, $today_time);
            }
            $data[$date]['unix_time_key'] = strtotime($value['date']);
            $data[$date]['time_key'] = $value['date'];
        }

        if($type == 'loan_num'){//每日还款单数
            $views = 'daily-loan-data-new';
        }
        if($type == 'loan_money'){//每日还款金额
            $views = 'daily-repayments-data-new';
        }
        $fundList = LoanFund::getAllFundArray($this->merchantIds);
        $appMarketList = ArrayHelper::getColumn(StatisticsDayData::find()->select(['app_market'])->distinct(['app_market'])->where(['merchant_id' => $this->merchantIds])->indexBy(['app_market'])->asArray()->all(),'app_market','app_market');
        $mediaSourceList = ArrayHelper::getColumn(StatisticsDayData::find()->select(['media_source'])->distinct(['media_source'])->where(['merchant_id' => $this->merchantIds])->indexBy(['media_source'])->asArray()->all(),'media_source','media_source');
        $packageNameList = ArrayHelper::getColumn(StatisticsDayData::find()->select(['package_name'])->distinct(['package_name'])->where(['merchant_id' => $this->merchantIds])->indexBy(['package_name'])->asArray()->all(),'package_name','package_name');
        if(isset($mediaSourceList[''])) {
            $mediaSourceList['-'] = '-';
            unset($mediaSourceList['']);
        }
        return $this->render($views, [
                'info' => $data,
                'total_info' => $total_data,
                'pages' => [],
                'update_time'=>0,
                'fundList' => $fundList,
                'appMarketList' => $appMarketList,
                'mediaSourceList' => $mediaSourceList,
                'packageNameList' => $packageNameList,
                'isNotMerchantAdmin' => $this->isNotMerchantAdmin,
            ]
        );
    }

    /**
     * @name 用户数据-每日还款金额数据（结构-分离商户）
     */
    public function actionDayDataRepaymentNumStatisticsUserStructureSubMerchant() {
        if(!$this->isNotMerchantAdmin){
            die('not find');
        }
        $condition[] = 'and';
        $search = $this->request->get();
        $fund_id = $search['fund_id']??0;
        $appMarket = $search['app_market']??[];
        $mediaSource = $search['media_source']??[];
        $packageName = $search['package_name']??[];
        $newType = 5;
        $allOldLoanNewType = 6;
        $oldType = 7;
        $field="user_type,date,merchant_id";
        if (!empty($search['begin_created_at'])) {
            $begin_created_at = str_replace(' ', '', $search['begin_created_at']);
        }else{
            $begin_created_at =date('Y-m-d', time() - 7*86400 );
        }
        if (!empty($search['end_created_at'])) {
            $end_created_at = str_replace(' ', '', $search['end_created_at']);
        }else {
            $end_created_at = date('Y-m-d', time() + 7*86400);
        }

        $condition[] = ['>=', 'date', $begin_created_at];
        $condition[] = ['<=', 'date', $end_created_at];

        if(!empty($appMarket)){
            $condition[] = ['app_market' => $appMarket];
        }
        if(!empty($mediaSource)){
            $condition[] = ['media_source' => $mediaSource];
        }
        if(!empty($packageName)){
            $condition[] = ['package_name' => $packageName];
        }
        if($fund_id && !in_array(0, $fund_id)){
            $condition[] = ['fund_id' => $fund_id];
        }
        $merchantIds = $this->request->get('merchant_id',[]);
        if($merchantIds){
            $condition[] = ['merchant_id' => $merchantIds];
        }

        $info = StatisticsDayData::find()->select("sum(expire_num) as expire_num,
                      sum(expire_money) as expire_money,
                      sum(repay_num) as repay_num,
                      sum(repay_money) as repay_money,
                      sum(repay_zc_num) as repay_zc_num,
                      sum(repay_zc_money) as repay_zc_money,
                      sum(extend_num) as extend_num,
                      sum(extend_money) as extend_money,
                      user_type,
                      merchant_id,
                      date")
            ->where($condition)
            ->andWhere(['merchant_id' => $this->merchantIds])
            ->groupBy($field)
            ->orderBy(['merchant_id' => SORT_ASC,'date' => SORT_DESC])->asArray()->all($this->getStatsDb());

        $data = $total_data = [];
        $today_time = strtotime(date("Y-m-d", time()));
        $service =  new DayDataStatisticsService();

        foreach($info as $value){
            $date=$value['date'];
            $merchantId=$value['merchant_id'];
            if($value['user_type']==0){
                $service->_getReturnDataSubMerchant($data, $total_data, $date,$merchantId, 0, $value, $today_time);
            }
            if(isset($value['user_type']) && $value['user_type'] == $newType){
                $service->_getReturnDataSubMerchant($data, $total_data, $date,$merchantId, 1, $value, $today_time);
            }
            if(isset($value['user_type']) && $value['user_type'] == $allOldLoanNewType){
                $service->_getReturnDataSubMerchant($data, $total_data, $date,$merchantId, 2, $value, $today_time);
            }
            if(isset($value['user_type']) && $value['user_type'] == $oldType){
                $service->_getReturnDataSubMerchant($data, $total_data, $date,$merchantId, 3, $value, $today_time);
            }
            $data[$merchantId][$date]['unix_time_key'] = strtotime($value['date']);
            $data[$merchantId][$date]['time_key'] = $value['date'];
        }


        $views = 'daily-loan-data-new-sub-merchant';

        $fundList = LoanFund::getAllFundArray($this->merchantIds);
        $appMarketList = ArrayHelper::getColumn(StatisticsDayData::find()->select(['app_market'])->distinct(['app_market'])->where(['merchant_id' => $this->merchantIds])->indexBy(['app_market'])->asArray()->all(),'app_market','app_market');
        $mediaSourceList = ArrayHelper::getColumn(StatisticsDayData::find()->select(['media_source'])->distinct(['media_source'])->where(['merchant_id' => $this->merchantIds])->indexBy(['media_source'])->asArray()->all(),'media_source','media_source');
        $packageNameList = ArrayHelper::getColumn(StatisticsDayData::find()->select(['package_name'])->distinct(['package_name'])->where(['merchant_id' => $this->merchantIds])->indexBy(['package_name'])->asArray()->all(),'package_name','package_name');
        if(isset($mediaSourceList[''])) {
            $mediaSourceList['-'] = '-';
            unset($mediaSourceList['']);
        }
        return $this->render($views, [
                'data' => $data,
                'total_info' => $total_data,
                'pages' => [],
                'update_time'=>0,
                'fundList' => $fundList,
                'appMarketList' => $appMarketList,
                'mediaSourceList' => $mediaSourceList,
                'packageNameList' => $packageNameList,
                'isNotMerchantAdmin' => $this->isNotMerchantAdmin,
                'merchantList' => Merchant::getMerchantId(false)
            ]
        );
    }

    /**
     * @name DataStatsFullPlatformController 订单数据转化(用户结构)
     * @return string
     */
    public function actionUserStructureOrderTransform() {
        if(!$this->isNotMerchantAdmin){
            die('not find');
        }
        ini_set('memory_limit', '1024M');
        $condition[] = 'and';
        $search = $this->request->get();
        $packageName = $search['package_name']??[];
        $merchantIds = $search['merchant_id']??[];
        if($merchantIds){
            $condition[] = ['merchant_id' => $merchantIds];
        }

        $field="date,package_name,user_type";
        if (!empty($search['begin_created_at'])) {
            $begin_created_at = str_replace(' ', '', $search['begin_created_at']);
        }else{
            $begin_created_at =date('Y-m-d', time() - 15*86400 );
        }
        if (!empty($search['end_created_at'])) {
            $end_created_at = str_replace(' ', '', $search['end_created_at']);
        }else {
            $end_created_at = date('Y-m-d', time() + 86400);
        }

        $condition[] = ['>=', 'date', $begin_created_at];
        $condition[] = ['<=', 'date', $end_created_at];

        if($packageName){
            $condition[] = ['package_name' => $packageName];
        }

        $info = UserStructureOrderTransform::find()->select([
            'date',
            'user_type',
            'package_name',
            'apply_order_num' => 'sum(apply_order_num)',
            'apply_order_money' => 'sum(apply_order_money)',
            'apply_person_num' => 'sum(apply_person_num)',
            'audit_pass_order_num' => 'sum(audit_pass_order_num)',
            'audit_pass_order_money' => 'sum(audit_pass_order_money)',
            'audit_pass_person_num' => 'sum(audit_pass_person_num)',
            'withdraw_order_num' => 'sum(withdraw_order_num)',
            'withdraw_order_money' => 'sum(withdraw_order_money)',
            'withdraw_person_num' => 'sum(withdraw_person_num)',
            'loan_success_order_num' => 'sum(loan_success_order_num)',
            'loan_success_order_money' => 'sum(loan_success_order_money)',
            'loan_success_person_num' => 'sum(loan_success_person_num)',
        ])
            ->where($condition)
            ->groupBy($field)
            ->orderBy('date DESC')->asArray()->all($this->getStatsDb());

        $data = $total_data = $date_data = [];
        $service =  new UserStructureOrderTransformService();

        foreach($info as $value){
            $date = $value['date'];
            $packageName = $value['package_name'];
            $service->_getReturnData($data, $total_data, $date_data, $date,$packageName, $value['user_type'], $value);
            $data[$date][$packageName]['unix_time_key'] = strtotime($value['date']);
            $data[$date][$packageName]['time_key'] = $value['date'];
        }
        $packageNames = array_column(UserStructureOrderTransform::find()->select(['package_name'])
            ->distinct(['package_name'])->where(['merchant_id' => $this->merchantIds])->andWhere(['!=','package_name',''])->asArray()->all(),'package_name','package_name');
        return $this->render('user-structure-order-transform', [
                'info' => $data,
                'total_info' => $total_data,
                'date_data' => $date_data,
                'pages' => [],
                'update_time'=>0,
                'packageNames' => $packageNames
            ]
        );
    }

    /**
     * @name 用户数据-每日注册转化
     * @return string
     */
    public function actionDailyRegisterConver(){
        $add_start = $this->request->get('add_start',date('Y-m-d',strtotime('-7 day')));
        $add_end = $this->request->get('add_end',date('Y-m-d'));
        $type = $this->request->get('type', '0');
        $condition = ['AND'];
        $condition[] = ['type' => $type];
        if ($add_start) {
            $condition[] = ['>=', 'date', $add_start];
        }
        if ($add_end) {
            $condition[] = ['<=', 'date', $add_end];
        }
        $appMarket = $this->request->get('app_market',[]);
        if(!empty($appMarket)){
            $condition[] = ['app_market' => $appMarket];
        }
        $mediaSource = $this->request->get('media_source',[]);
        if(!empty($mediaSource)){
            $condition[] = ['media_source' => $mediaSource];
        }
        $source_id = $this->request->get('source_id',0);
        if(!empty($source_id)){
            $condition[] = ['source_id' => $source_id];
        }
        $pages = new Pagination(['totalCount' => 9999999]);
        $pages->pageSize = Yii::$app->request->get('per-page', 15);

        $query = DailyRegisterConver::find()->where($condition);
        $totalQuery = clone $query;
        $totalData = $totalQuery->select(
            [
                'sum(reg_num) as reg_num',
                'sum(basic_num) as basic_num',
                'sum(kyc_num) as kyc_num',
                'sum(address_num) as address_num',
                'sum(contact_num) as contact_num',
                'sum(apply_num) as apply_num',
                'sum(audit_pass_num) as audit_pass_num',
                'sum(withdraw_num) as withdraw_num',
                'sum(loan_num) as loan_num',
            ])->asArray()->all();
        $totalData[0]['date'] = '汇总';
        $totalData[0]['Type'] = '1';
        $dataQuery = clone $query;
        $dateData = $dataQuery->select(
            [
                'date',
                'sum(reg_num) as reg_num',
                'sum(basic_num) as basic_num',
                'sum(kyc_num) as kyc_num',
                'sum(address_num) as address_num',
                'sum(contact_num) as contact_num',
                'sum(apply_num) as apply_num',
                'sum(audit_pass_num) as audit_pass_num',
                'sum(withdraw_num) as withdraw_num',
                'sum(loan_num) as loan_num',
            ])->groupBy(['date'])->orderBy(['date' => SORT_DESC])->asArray()->all();
        foreach ($dateData as &$v){
            $v['Type'] = 2;
        }
        $sourceMap = PackageSetting::getSourceIdMap($this->merchantIds);
        $totalData = array_merge($totalData,$dateData);
        if ($this->request->get('submitcsv') == 'export_direct') {
            $data = $query->orderBy(['date' => SORT_DESC,'id' => SORT_DESC])->asArray()->all();
            $totalData = array_merge($totalData,$data);
            return $this->_exportDailyRegisterConver($totalData, $sourceMap);
        }else{
            $data = $query->offset($pages->offset)->limit($pages->limit)->orderBy(['date' => SORT_DESC,'id' => SORT_DESC])->asArray()->all();
        }

        return $this->render('daily-register-conver', [
            'totalData' => $totalData,
            'data' => $data,
            'sourceMap' => $sourceMap,
            'pages' => $pages,
        ]);
    }

    private function _exportDailyRegisterConver($data, $sourceMap){
        $this->_setcsvHeader('.每日注册转化（大盘+导流）.csv');
        Util::cliLimitChange(1024);
        $items = [];
        foreach ($data as $value){
            if(!isset($value['source_id'])){
                $source = '-';
            }else{
                $source = array_flip($sourceMap)[$value['source_id']] ?? '-';
            }
            $items[] = [
                '日期' => $value['date'],
                'sourceApp' => $source,
                'app_market' => $value['app_market'] ?? '-',
                'media_source' => $value['media_source'] ?? '-',
                '注册数' => $value['reg_num'],
                '基础认证' => $value['basic_num'],
                'KYC认证' => $value['kyc_num'],
                '地址证明' => $value['address_num'],
                '紧急联系人' => $value['contact_num'],
                '注册到认证' => !empty($value['reg_num']) ? sprintf("%0.2f",$value['contact_num']/$value['reg_num']*100) .'%': '-',
                '申请' => $value['apply_num'],
                '认证到申请' => !empty($value['contact_num']) ? sprintf("%0.2f",$value['apply_num']/$value['contact_num']*100) .'%': '-' ,
                '注册到申请' => !empty($value['reg_num']) ? sprintf("%0.2f",$value['apply_num']/$value['reg_num']*100) .'%': '-',
                '过件' => $value['audit_pass_num'],
                '过件率' => !empty($value['apply_num']) ? sprintf("%0.2f",$value['audit_pass_num']/$value['apply_num']*100) .'%': '-',
                '提现' => $value['withdraw_num'],
                '放款' => $value['loan_num'],
                '注册到放款' => !empty($value['reg_num']) ? sprintf("%0.2f",$value['loan_num']/$value['reg_num']*100) .'%': '-',
            ];
        }
        echo $this->_array2csv($items);
        die;
    }
}
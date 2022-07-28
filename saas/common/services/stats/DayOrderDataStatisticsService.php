<?php
/**
 * Created by loan
 * User: wangpeng
 * Date: 2019/7/5 0005
 * Time：17:05
 */


namespace common\services\stats;

use common\models\ClientInfoLog;
use common\models\order\UserLoanOrder;
use common\models\stats\StatisticsLoan2FullPlatform;
use common\models\stats\StatisticsLoan2UserStructure;
use common\models\stats\StatisticsLoanCopy;
use common\models\stats\StatisticsLoanCopy2;
use common\models\stats\StatisticsLoanFullPlatform;
use common\models\stats\StatisticsLoanUserStructure;
use common\models\user\UserRegisterInfo;

class DayOrderDataStatisticsService extends StatsBaseService
{

    public function actionDailyLoans($time,$isLoanMoney = false,$isAllNewOld = false)
    {
        $time_start = empty($time)?strtotime("today"):$time; //今天零点
        $end_time =$time_start + 86400;
        $_hour = date('H',time());//当前的小时数

        //如果当前时间为24点，则计算前一天所有的放款数据,显示日期为前一天的24时
        if( $_hour == 0 && date('i',time())<=10 ){
            $end_time = $time_start;
            $time_start = $end_time-86400;
        }
        $financial_loan = UserLoanOrder::find()
            ->select([
                'A.merchant_id',
                'A.fund_id',
                'A.amount',
                'A.cost_fee',
                'B.app_market',
                'B.package_name',
                'C.media_source',
                'A.loan_term',
                'A.is_export',
                'A.is_first',
                'A.is_all_first'
            ])
            ->from(UserLoanOrder::tableName() . ' A')
            ->leftJoin(ClientInfoLog::tableName() . ' B','A.id = B.event_id AND B.event = '.ClientInfoLog::EVENT_APPLY_ORDER)
            ->leftJoin(UserRegisterInfo::tableName() . ' C',' A.user_id = C.user_id')
            ->where(['>=','A.loan_time',$time_start])
            ->andWhere(['<','A.loan_time',$end_time])
            ->asArray()->all($this->db);
        $data = [];
        if ($financial_loan) {
            // app类型部分开始

            $temp_data = [];
            foreach ($financial_loan as $item) {
                $mediaSource = strtolower($item['media_source'] ?? '');
                $loanTerm = $item['loan_term'] ?? '';
                $appMarketArr = explode('_',$item['app_market']);
                $packageName = $item['is_export'] == UserLoanOrder::IS_EXPORT_YES ? ($appMarketArr[1] ?? '') : $item['package_name'];
                $temp_data[$item['merchant_id']][$item['fund_id']][$item['app_market']][$mediaSource][$packageName][$loanTerm][] = $item;
            }
            foreach ($temp_data as $merchantId => $merchantIdData) {
                foreach ($merchantIdData as $fundId => $fundData) {
                    foreach ($fundData as $appMarket => $appMarketData) {
                        foreach ($appMarketData as $mediaSource => $mediaSourceData) {
                            foreach ($mediaSourceData as $packageName => $packageNameData) {
                                foreach ($packageNameData as $loanTerm => $loanTermData) {
                                    $data[$merchantId][$fundId][$appMarket][$mediaSource][$packageName][$loanTerm] = [
                                        'loan_num'       => 0,
                                        'loan_money'     => 0,
                                        'loan_num_old'   => 0,
                                        'loan_money_old' => 0,
                                        'loan_num_new'   => 0,
                                        'loan_money_new' => 0,
                                    ];
                                    foreach ($loanTermData as $value) {
                                        $amount = $isLoanMoney ? ($value['amount'] - $value['cost_fee']) : $value['amount'];
                                        $data[$merchantId][$fundId][$appMarket][$mediaSource][$packageName][$loanTerm]['loan_num']++;
                                        $data[$merchantId][$fundId][$appMarket][$mediaSource][$packageName][$loanTerm]['loan_money'] += $amount;

                                        if($isAllNewOld){ //全平台新老
                                            if ($value['is_all_first'] == 0) {
                                                $data[$merchantId][$fundId][$appMarket][$mediaSource][$packageName][$loanTerm]['loan_num_old']++;
                                                $data[$merchantId][$fundId][$appMarket][$mediaSource][$packageName][$loanTerm]['loan_money_old'] += $amount;
                                            } else {
                                                $data[$merchantId][$fundId][$appMarket][$mediaSource][$packageName][$loanTerm]['loan_num_new']++;
                                                $data[$merchantId][$fundId][$appMarket][$mediaSource][$packageName][$loanTerm]['loan_money_new'] += $amount;
                                            }
                                        }else{
                                            if ($value['is_first'] == 0) {
                                                $data[$merchantId][$fundId][$appMarket][$mediaSource][$packageName][$loanTerm]['loan_num_old']++;
                                                $data[$merchantId][$fundId][$appMarket][$mediaSource][$packageName][$loanTerm]['loan_money_old'] += $amount;
                                            } else {
                                                $data[$merchantId][$fundId][$appMarket][$mediaSource][$packageName][$loanTerm]['loan_num_new']++;
                                                $data[$merchantId][$fundId][$appMarket][$mediaSource][$packageName][$loanTerm]['loan_money_new'] += $amount;
                                            }
                                        }

                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        //数据写入
        foreach ($data as $merchantId => $merchantIdData) {
            foreach ($merchantIdData as $fundId => $fundData) {
                foreach ($fundData as $appMarket => $appMarketData) {
                    foreach ($appMarketData as $mediaSource => $mediaSourceData) {
                        foreach ($mediaSourceData as $packageName => $loanTermData) {
                            foreach ($loanTermData as $loanTerm => $val) {
                                if ($isLoanMoney && $isAllNewOld) {  //全平台新老放款额数据
                                    $class = new StatisticsLoan2FullPlatform();
                                } elseif ($isLoanMoney && !$isAllNewOld) { //非全平台新老放款额数据
                                    $class = new StatisticsLoanCopy2();
                                } elseif (!$isLoanMoney && $isAllNewOld) { //全平台新老本金额数据
                                    $class = new StatisticsLoanFullPlatform();
                                } elseif (!$isLoanMoney && !$isAllNewOld) { //非全平台新老本金额数据
                                    $class = new StatisticsLoanCopy();
                                } else {
                                    continue;
                                }
                                $statistics_loan = $class::find()
                                    ->where([
                                        'date_time' => $time_start,
                                        'merchant_id' => $merchantId,
                                        'app_market' => $appMarket,
                                        'media_source' => $mediaSource,
                                        'package_name' => $packageName,
                                        'fund_id' => $fundId,
                                        'loan_term' => $loanTerm
                                    ])
                                    ->one();
                                if (empty($statistics_loan)) {
                                    $statistics_loan              = $class;
                                    $statistics_loan->created_at  = time();//创建时间
                                    $statistics_loan->date_time   = $time_start; //日期
                                    $statistics_loan->merchant_id = $merchantId; //商户
                                    $statistics_loan->app_market = $appMarket;
                                    $statistics_loan->media_source = $mediaSource;
                                    $statistics_loan->package_name = $packageName;
                                    $statistics_loan->fund_id     = $fundId; //日期
                                    $statistics_loan->loan_term  = $loanTerm;

                                }
                                $statistics_loan->loan_num       = $val['loan_num'];             //放款单数
                                $statistics_loan->loan_num_old   = $val['loan_num_old'];     //老用户放款单数
                                $statistics_loan->loan_num_new   = $val['loan_num_new'];     //新用户放款单数
                                $statistics_loan->loan_money     = $val['loan_money'];         //放款金额
                                $statistics_loan->loan_money_old = $val['loan_money_old']; //老用户放款金额
                                $statistics_loan->loan_money_new = $val['loan_money_new']; //新用户放款金额
                                $statistics_loan->updated_at     = time();
                                $statistics_loan->save();
                            }
                        }
                    }
                }
            }
        }
        echo date('Y-m-d', $time_start)."\n";
    }


    public function actionDailyLoansUserStructure($time,$isLoanMoney = false)
    {
        $time_start = empty($time)?strtotime("today"):$time; //今天零点
        $end_time =$time_start + 86400;
        $_hour = date('H',time());//当前的小时数

        //如果当前时间为24点，则计算前一天所有的放款数据,显示日期为前一天的24时
        if( $_hour == 0 && date('i',time())<=10 ){
            $end_time = $time_start;
            $time_start = $end_time-86400;
        }
        $financial_loan = UserLoanOrder::find()
            ->select([
                'A.merchant_id',
                'A.fund_id',
                'A.amount',
                'A.cost_fee',
                'B.app_market',
                'B.package_name',
                'C.media_source',
                'A.loan_term',
                'A.is_export',
                'A.is_first',
                'A.is_all_first'
            ])
            ->from(UserLoanOrder::tableName() . ' A')
            ->leftJoin(ClientInfoLog::tableName() . ' B','A.id = B.event_id AND B.event = '.ClientInfoLog::EVENT_APPLY_ORDER)
            ->leftJoin(UserRegisterInfo::tableName() . ' C',' A.user_id = C.user_id')
            ->where(['>=','A.loan_time',$time_start])
            ->andWhere(['<','A.loan_time',$end_time])
            ->asArray()->all($this->db);
        $data = [];
        if ($financial_loan) {
            // app类型部分开始

            $temp_data = [];
            foreach ($financial_loan as $item) {
                $loanTerm = $item['loan_term'] ?? '';
                $mediaSource = strtolower($item['media_source'] ?? '');
                $appMarketArr = explode('_',$item['app_market']);
                $packageName = $item['is_export'] == UserLoanOrder::IS_EXPORT_YES ? ($appMarketArr[1] ?? '') : $item['package_name'];
                $temp_data[$item['merchant_id']][$item['fund_id']][$item['app_market']][$mediaSource][$packageName][$loanTerm][] = $item;
            }
            foreach ($temp_data as $merchantId => $merchantIdData) {
                foreach ($merchantIdData as $fundId => $fundData) {
                    foreach ($fundData as $appMarket => $appMarketData) {
                        foreach ($appMarketData as $mediaSource => $mediaSourceData) {
                            foreach ($mediaSourceData as $packageName => $packageNameData) {
                                foreach ($packageNameData as $loanTerm => $loanTermData) {
                                    $data[$merchantId][$fundId][$appMarket][$mediaSource][$packageName][$loanTerm] = [
                                        'loan_num'                    => 0,
                                        'loan_money'                  => 0,
                                        'loan_num_old'                => 0,
                                        'loan_money_old'              => 0,
                                        'loan_num_new'                => 0,
                                        'loan_money_new'              => 0,
                                        'loan_num_all_old_loan_new'   => 0,
                                        'loan_money_all_old_loan_new' => 0,
                                    ];
                                    foreach ($loanTermData as $value) {
                                        $amount = $isLoanMoney ? ($value['amount'] - $value['cost_fee']) : $value['amount'];
                                        $data[$merchantId][$fundId][$appMarket][$mediaSource][$packageName][$loanTerm]['loan_num']++;
                                        $data[$merchantId][$fundId][$appMarket][$mediaSource][$packageName][$loanTerm]['loan_money'] += $amount;


                                        if ($value['is_all_first'] == 0 && $value['is_first'] == 0) {
                                            $data[$merchantId][$fundId][$appMarket][$mediaSource][$packageName][$loanTerm]['loan_num_old']++;
                                            $data[$merchantId][$fundId][$appMarket][$mediaSource][$packageName][$loanTerm]['loan_money_old'] += $amount;
                                        } elseif ($value['is_all_first'] == 1 && $value['is_first'] == 1) {
                                            $data[$merchantId][$fundId][$appMarket][$mediaSource][$packageName][$loanTerm]['loan_num_new']++;
                                            $data[$merchantId][$fundId][$appMarket][$mediaSource][$packageName][$loanTerm]['loan_money_new'] += $amount;
                                        } elseif ($value['is_all_first'] == 0 && $value['is_first'] == 1) {
                                            $data[$merchantId][$fundId][$appMarket][$mediaSource][$packageName][$loanTerm]['loan_num_all_old_loan_new']++;
                                            $data[$merchantId][$fundId][$appMarket][$mediaSource][$packageName][$loanTerm]['loan_money_all_old_loan_new'] += $amount;
                                        }


                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        //数据写入
        foreach ($data as $merchantId => $merchantIdData) {
            foreach ($merchantIdData as $fundId => $fundData) {
                foreach ($fundData as $appMarket => $appMarketData) {
                    foreach ($appMarketData as $mediaSource => $mediaSourceData) {
                        foreach ($mediaSourceData as $packageName => $loanTermData) {
                            foreach ($loanTermData as $loanTerm => $val) {
                                if ($isLoanMoney) {  //全平台新老放款额数据
                                    $class = new StatisticsLoan2UserStructure();
                                } else {
                                    $class = new StatisticsLoanUserStructure();
                                }
                                $statistics_loan = $class::find()
                                    ->where([
                                        'date_time' => $time_start,
                                        'merchant_id' => $merchantId,
                                        'app_market' => $appMarket,
                                        'media_source' => $mediaSource,
                                        'package_name' => $packageName,
                                        'fund_id' => $fundId,
                                        'loan_term' => $loanTerm
                                    ])
                                    ->one();
                                if (empty($statistics_loan)) {
                                    $statistics_loan              = $class;
                                    $statistics_loan->created_at  = time();//创建时间
                                    $statistics_loan->date_time   = $time_start; //日期
                                    $statistics_loan->merchant_id = $merchantId; //商户
                                    $statistics_loan->app_market = $appMarket;
                                    $statistics_loan->media_source = $mediaSource;
                                    $statistics_loan->package_name = $packageName;
                                    $statistics_loan->fund_id     = $fundId; //日期
                                    $statistics_loan->loan_term = $loanTerm;

                                }
                                $statistics_loan->loan_num                    = $val['loan_num'];             //放款单数
                                $statistics_loan->loan_num_old                = $val['loan_num_old'];     //老用户放款单数
                                $statistics_loan->loan_num_new                = $val['loan_num_new'];     //新用户放款单数
                                $statistics_loan->loan_num_all_old_loan_new   = $val['loan_num_all_old_loan_new'];     //全老本新用户放款单数
                                $statistics_loan->loan_money                  = $val['loan_money'];         //放款金额
                                $statistics_loan->loan_money_old              = $val['loan_money_old']; //老用户放款金额
                                $statistics_loan->loan_money_new              = $val['loan_money_new']; //新用户放款金额
                                $statistics_loan->loan_money_all_old_loan_new = $val['loan_money_all_old_loan_new'];   //全老本新用户放款金额
                                $statistics_loan->updated_at                  = time();
                                $statistics_loan->save();
                            }
                        }
                    }
                }
            }
        }
        echo date('Y-m-d', $time_start)."\n";
    }

    /**
     * 数据导出
     * @param $datas
     */
    public function exportDailyData($datas){
        $fname = '放款数据.csv';
        $this->_setcsvHeader($fname);
        $items = [];
        foreach($datas as $value){
            $loan_num_new = $value['loan_num_new'];
            $loan_num_old = $value['loan_num_old'];
            $total = $loan_num_new + $loan_num_old;
            $new_pre = (!empty($total)) ? round(($loan_num_new/$total)*100) : 0;
            $old_pre = 100 - $new_pre;

            $loan_num = $value['loan_num'];
            $loan_money = $value['loan_money']/100;
            $loan_money_new = $value['loan_money_new']/100;
            $loan_money_old = $value['loan_money_old']/100;
            $items[] = [
                '日期'=>date("Y-m-d",$value['date_time']),
                '期限' =>$value['loan_term'],
                '放款单数' =>$value['loan_num'],
                '放款总额' =>sprintf("%0.2f",$value['loan_money']/100),
                '借款件均' => ($loan_num>0)?sprintf("%0.2f",$loan_money/$loan_num):0,
                '新老用户比' => $new_pre.'-'.$old_pre,
                '新用户借款单数' => $loan_num_new,
                '新用户借款总额' => $loan_money_new,
                '新用户借款件均' => ($loan_num_new>0)?sprintf("%0.2f",$loan_money_new/$loan_num_new):0,
                '老用户借款单数' => $loan_num_old,
                '老用户借款总额' => $loan_money_old,
                '老用户借款件均' => ($loan_num_old>0)?sprintf("%0.2f",$loan_money_old/$loan_num_old):0,
            ];
        }
        echo $this->_array2csv($items);
        exit;
    }
}
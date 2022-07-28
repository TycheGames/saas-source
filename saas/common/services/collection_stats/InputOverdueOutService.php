<?php

namespace common\services\collection_stats;

use callcenter\models\InputOverdayOut;
use callcenter\models\InputOverdayOutAmount;
use callcenter\models\loan_collection\LoanCollectionOrder;
use common\models\order\UserLoanOrder;
use common\models\order\UserLoanOrderRepayment;

class InputOverdueOutService
{
    public function runInputOverdueOut($startDate){
        $data = [];
        $maxOverdueDay = 30;
        $startTime = strtotime($startDate);

        $limit = 500;
        $maxId = 0;

        $LoanCollectionOrders = LoanCollectionOrder::find()
            ->where(['>=','created_at',$startTime])
            ->andWhere(['<','created_at',$startTime+86400])
            ->orderBy(['id' => SORT_ASC])
            ->limit($limit)
            ->all(LoanCollectionOrder::getDb_rd());

        while ($LoanCollectionOrders){
            /** @var LoanCollectionOrder $LoanCollectionOrder */
            foreach ($LoanCollectionOrders as $LoanCollectionOrder){
                $maxId = $LoanCollectionOrder->id;
                /** @var UserLoanOrderRepayment $repayment */
                $repayment = UserLoanOrderRepayment::find()
                    ->where(['id' => $LoanCollectionOrder->user_loan_order_repayment_id])
                    ->one(\Yii::$app->db_read_1);
                /** @var UserLoanOrder $order */
                $order = UserLoanOrder::find()
                    ->where(['id' => $LoanCollectionOrder->user_loan_order_id])
                    ->one(\Yii::$app->db_read_1);
                if(!isset($order->loanPerson)){
                    continue;
                }
                $packageName = $order->is_export == UserLoanOrder::IS_EXPORT_YES
                    ? explode('_',$order->clientInfoLog->app_market)[1]
                    : $order->clientInfoLog->package_name;
                if(is_null($repayment)){
                    echo '还款表记录不存在,repayment_id:'.$LoanCollectionOrder->user_loan_order_repayment_id.PHP_EOL;
                    continue;
                }
                if(is_null($order)){
                    echo '下单表记录不存在,order_id:'.$LoanCollectionOrder->user_loan_order_id.PHP_EOL;
                    continue;
                }
                $merchantId = $LoanCollectionOrder->merchant_id;
                $amount = $repayment->getAmountInExpiryDate();
                $userTypeArr = $this->getUserTypeArr($order->is_first,$order->is_all_first);
                $this->setData($data,$merchantId,$packageName,$userTypeArr,'input_count','input_amount',$amount);

                if($LoanCollectionOrder->status == LoanCollectionOrder::STATUS_COLLECTION_FINISH
                    && $repayment->status == UserLoanOrderRepayment::STATUS_REPAY_COMPLETE)
                {
                    if($repayment->overdue_day > 0){
                        $this->setData($data,$merchantId,$packageName,$userTypeArr,'overday_total_count','overday_total_amount',$amount);
                        if($repayment->overdue_day <= $maxOverdueDay){
                            $this->setData($data,$merchantId,$packageName,$userTypeArr,'overday'.$repayment->overdue_day.'_count','overday'.$repayment->overdue_day.'_amount',$amount);
                        }else{
                            //按逾期等级
                            $levelKey = LoanCollectionOrder::LEVEL_M2;
                            foreach (LoanCollectionOrder::$reset_overdue_days as $overdue_day => $level){
                                if($repayment->overdue_day >= $overdue_day){
                                    $levelKey = $level;
                                }else{
                                    break;
                                }
                            }
                            $this->setData($data,$merchantId,$packageName,$userTypeArr,'overlevel'.$levelKey.'_count','overlevel'.$levelKey.'_amount',$amount);
                        }
                    }
                }
            }
            $LoanCollectionOrders = LoanCollectionOrder::find()
                ->where(['>=','created_at',$startTime])
                ->andWhere(['<','created_at',$startTime+86400])
                ->andWhere(['>','id',$maxId])
                ->orderBy(['id' => SORT_ASC])
                ->limit($limit)
                ->all(LoanCollectionOrder::getDb_rd());
        }

        $this->saveData($data,$startDate);
    }

    private function setData(&$data,$merchantId,$packageName,$userTypeArr,$countField,$moneyField,$amount){
        foreach ($userTypeArr as $userType){
            if(isset($data[$merchantId][$packageName][$userType]['count'][$countField])){
                $data[$merchantId][$packageName][$userType]['count'][$countField] += 1;
            }else{
                $data[$merchantId][$packageName][$userType]['count'][$countField] = 1;
            }
            if(isset($data[$merchantId][$packageName][$userType]['money'][$moneyField])){
                $data[$merchantId][$packageName][$userType]['money'][$moneyField] += $amount;
            }else{
                $data[$merchantId][$packageName][$userType]['money'][$moneyField] = $amount;
            }
        }
    }

    private function getUserTypeArr($is_first,$is_all_first){
        $result = [InputOverdayOut::USER_TYPE_ALL];
        if($is_first == UserLoanOrder::FIRST_LOAN_NO){
            //本老= 全老本老
            $result[] = InputOverdayOut::USER_TYPE_OLD;
            if($is_all_first == UserLoanOrder::FIRST_LOAN_NO){
                $result[] = InputOverdayOut::USER_TYPE_ALL_OLD_SELF_OLD;
            }

        }else{
            $result[] = InputOverdayOut::USER_TYPE_NEW;
            if($is_all_first == UserLoanOrder::FIRST_LOAN_IS){
                $result[] = InputOverdayOut::USER_TYPE_ALL_NEW_SELF_NEW;
            }else{
                $result[] = InputOverdayOut::USER_TYPE_ALL_OLD_SELF_NEW;
            }
        }
        return $result;
    }

    private function saveData($data,$startDate){
        //更新数据
        foreach ($data as $merchantId => $packageNameVal){
            foreach ($packageNameVal as $packageName => $userValue) {
                foreach ($userValue as $userType => $value) {
                    //更新单数表
                    $inputOverdayOut = InputOverdayOut::find()->where([
                        'date' => $startDate,'merchant_id' => $merchantId, 'package_name' => $packageName, 'user_type' => $userType
                    ])->one();
                    if (is_null($inputOverdayOut)) {
                        $inputOverdayOut = new InputOverdayOut();
                        $inputOverdayOut->merchant_id = $merchantId;
                        $inputOverdayOut->package_name    = $packageName;
                        $inputOverdayOut->date      = $startDate;
                        $inputOverdayOut->user_type = $userType;
                    }
                    foreach ($value['count'] as $key => $val) {
                        $inputOverdayOut->$key = $val;
                    }
                    $inputOverdayOut->save();

                    //更新金额表
                    $inputOverdayOutAmount = InputOverdayOutAmount::find()->where([
                        'date' => $startDate,'merchant_id' => $merchantId, 'package_name' => $packageName, 'user_type' => $userType
                    ])->one();
                    if (is_null($inputOverdayOutAmount)) {
                        $inputOverdayOutAmount = new InputOverdayOutAmount();
                        $inputOverdayOutAmount->merchant_id = $merchantId;
                        $inputOverdayOutAmount->package_name    = $packageName;
                        $inputOverdayOutAmount->date      = $startDate;
                        $inputOverdayOutAmount->user_type = $userType;
                    }
                    foreach ($value['money'] as $key => $val) {
                        $inputOverdayOutAmount->$key = $val;
                    }
                    $inputOverdayOutAmount->save();
                }
            }
        }
    }


    public static function setTotalInputCountData(&$list,&$totalData,&$totalInputData){
        $totalData['overday1_3_count'] = 0; //S1 1-3
        $totalData['overday4_7_count'] = 0; //S1 4-7
        $totalData['overday8_15_count'] = 0; //S2
        $totalData['overday16_30_count'] = 0; //M1

        $totalInputData['overday1_3_count'] = 0;
        $totalInputData['overday4_7_count'] = 0;
        $totalInputData['overday8_15_count'] = 0;
        $totalInputData['overday16_30_count'] = 0;
        $totalInputData['overday1_7_count'] = 0;
        $totalInputData['overday1_15_count'] = 0;
        foreach (LoanCollectionOrder::$level as $lv => $v){
            if($lv >= LoanCollectionOrder::LEVEL_M2){
                $totalInputData['overlevel'.$lv.'_count'] = 0;
            }
        }
        for ($day = 1; $day <= 30; $day++){
            $key = "overday{$day}_count";
            $totalInputData[$key] = 0;
        }

        foreach ($list as &$val){
            $val['overday1_3_count'] = 0;
            $val['overday4_7_count'] = 0;
            $val['overday8_15_count'] = 0;
            $val['overday16_30_count'] = 0;
            for ($day = 1; $day <= 30; $day++){
                $key = "overday{$day}_count";
                if($day <= 3){
                    $val['overday1_3_count'] += $val[$key];
                }
                if($day >= 4 && $day <= 7){
                    $val['overday4_7_count'] += $val[$key];
                }
                if($day >= 8 && $day <= 15){
                    $val['overday8_15_count'] += $val[$key];
                }
                if($day >= 16 && $day <= 30){
                    $val['overday16_30_count'] += $val[$key];
                }
                $totalInputData[$key] += ($val[$key] > 0 ? $val['input_count'] : 0);
            }
            $val['overday1_7_count'] = $val['overday1_3_count'] + $val['overday4_7_count'];
            //S1+S2
            $val['overday1_15_count'] = $val['overday1_7_count'] + $val['overday8_15_count'];

            $totalInputData['overday1_3_count'] += ($val['overday1_3_count'] > 0 ? $val['input_count'] : 0);
            $totalInputData['overday4_7_count'] += ($val['overday4_7_count'] > 0 ? $val['input_count'] : 0);
            $totalInputData['overday8_15_count'] += ($val['overday8_15_count'] > 0 ? $val['input_count'] : 0);
            $totalInputData['overday16_30_count'] += ($val['overday16_30_count'] > 0 ? $val['input_count'] : 0);
            $totalInputData['overday1_7_count'] += ($val['overday1_7_count'] > 0 ? $val['input_count'] : 0);
            $totalInputData['overday1_15_count'] += ($val['overday1_15_count'] > 0 ? $val['input_count'] : 0);

            foreach (LoanCollectionOrder::$level as $lv => $v){
                if($lv >= LoanCollectionOrder::LEVEL_M2){
                    $totalInputData['overlevel'.$lv.'_count'] = ($val['overlevel'.$lv.'_count'] > 0 ? $val['input_count'] : 0);
                }
            }
        }
        for ($day = 1; $day <= 30; $day++){
            $key = "overday{$day}_count";
            if($day <= 3){
                $totalData['overday1_3_count'] += $totalData[$key];
            }
            if($day >= 4 && $day <= 7){
                $totalData['overday4_7_count'] += $totalData[$key];
            }
            if($day >= 8 && $day <= 15){
                $totalData['overday8_15_count'] += $totalData[$key];
            }
            if($day >= 16 && $day <= 30){
                $totalData['overday16_30_count'] += $totalData[$key];
            }
        }
        //S1
        $totalData['overday1_7_count'] = $totalData['overday1_3_count'] + $totalData['overday4_7_count'];
        //S1+S2
        $totalData['overday1_15_count'] = $totalData['overday1_7_count'] + $totalData['overday8_15_count'];
    }

    public static function setTotalInputAmountData(&$list,&$totalData,&$totalInputData){
        $totalData['overday1_3_amount'] = 0; //S1 1-3
        $totalData['overday4_7_amount'] = 0; //S1 4-7
        $totalData['overday8_15_amount'] = 0; //S2
        $totalData['overday16_30_amount'] = 0; //M1

        $totalInputData['overday1_3_amount'] = 0;
        $totalInputData['overday4_7_amount'] = 0;
        $totalInputData['overday8_15_amount'] = 0;
        $totalInputData['overday16_30_amount'] = 0;
        $totalInputData['overday1_7_amount'] = 0;
        $totalInputData['overday1_15_amount'] = 0;
        foreach (LoanCollectionOrder::$level as $lv => $v){
            if($lv >= LoanCollectionOrder::LEVEL_M2){
                $totalInputData['overlevel'.$lv.'_amount'] = 0;
            }
        }
        for ($day = 1; $day <= 30; $day++){
            $key = "overday{$day}_amount";
            $totalInputData[$key] = 0;
        }

        foreach ($list as &$val){
            $val['overday1_3_amount'] = 0;
            $val['overday4_7_amount'] = 0;
            $val['overday8_15_amount'] = 0;
            $val['overday16_30_amount'] = 0;
            for ($day = 1; $day <= 30; $day++){
                $key = "overday{$day}_amount";
                if($day <= 3){
                    $val['overday1_3_amount'] += $val[$key];
                }
                if($day >= 4 && $day <= 7){
                    $val['overday4_7_amount'] += $val[$key];
                }
                if($day >= 8 && $day <= 15){
                    $val['overday8_15_amount'] += $val[$key];
                }
                if($day >= 16 && $day <= 30){
                    $val['overday16_30_amount'] += $val[$key];
                }
                $totalInputData[$key] += ($val[$key] > 0 ? $val['input_amount'] : 0);
            }
            $val['overday1_7_amount'] = $val['overday1_3_amount'] + $val['overday4_7_amount'];
            //S1+S2
            $val['overday1_15_amount'] = $val['overday1_7_amount'] + $val['overday8_15_amount'];

            $totalInputData['overday1_3_amount'] += ($val['overday1_3_amount'] > 0 ? $val['input_amount'] : 0);
            $totalInputData['overday4_7_amount'] += ($val['overday4_7_amount'] > 0 ? $val['input_amount'] : 0);
            $totalInputData['overday8_15_amount'] += ($val['overday8_15_amount'] > 0 ? $val['input_amount'] : 0);
            $totalInputData['overday16_30_amount'] += ($val['overday16_30_amount'] > 0 ? $val['input_amount'] : 0);
            $totalInputData['overday1_7_amount'] += ($val['overday1_7_amount'] > 0 ? $val['input_amount'] : 0);
            $totalInputData['overday1_15_amount'] += ($val['overday1_15_amount'] > 0 ? $val['input_amount'] : 0);

            foreach (LoanCollectionOrder::$level as $lv => $v){
                if($lv >= LoanCollectionOrder::LEVEL_M2){
                    $totalInputData['overlevel'.$lv.'_amount'] = ($val['overlevel'.$lv.'_amount'] > 0 ? $val['input_amount'] : 0);
                }
            }
        }
        for ($day = 1; $day <= 30; $day++){
            $key = "overday{$day}_amount";
            if($day <= 3){
                $totalData['overday1_3_amount'] += $totalData[$key];
            }
            if($day >= 4 && $day <= 7){
                $totalData['overday4_7_amount'] += $totalData[$key];
            }
            if($day >= 8 && $day <= 15){
                $totalData['overday8_15_amount'] += $totalData[$key];
            }
            if($day >= 16 && $day <= 30){
                $totalData['overday16_30_amount'] += $totalData[$key];
            }
        }
        //S1
        $totalData['overday1_7_amount'] = $totalData['overday1_3_amount'] + $totalData['overday4_7_amount'];
        //S1+S2
        $totalData['overday1_15_amount'] = $totalData['overday1_7_amount'] + $totalData['overday8_15_amount'];
    }

    public static function setChatCountLevelByDayData(&$list){
        foreach ($list as &$val){
            $val['overday1_3_count'] = 0;
            $val['overday4_7_count'] = 0;
            $val['overday8_15_count'] = 0;
            $val['overday16_30_count'] = 0;
            for ($day = 1; $day <= 30; $day++){
                $key = "overday{$day}_count";
                if($day <= 3){
                    $val['overday1_3_count'] += $val[$key];
                }
                if($day >= 4 && $day <= 7){
                    $val['overday4_7_count'] += $val[$key];
                }
                if($day >= 8 && $day <= 15){
                    $val['overday8_15_count'] += $val[$key];
                }
                if($day >= 16 && $day <= 30){
                    $val['overday16_30_count'] += $val[$key];
                }
            }
            $val['overday1_7_count'] = $val['overday1_3_count'] + $val['overday4_7_count'];
            //S1+S2
            $val['overday1_15_count'] = $val['overday1_7_count'] + $val['overday8_15_count'];
        }
    }

    public static function setChatAmountLevelByDayData(&$list){
        foreach ($list as &$val){
            $val['overday1_3_amount'] = 0;
            $val['overday4_7_amount'] = 0;
            $val['overday8_15_amount'] = 0;
            $val['overday16_30_amount'] = 0;
            for ($day = 1; $day <= 30; $day++){
                $key = "overday{$day}_amount";
                if($day <= 3){
                    $val['overday1_3_amount'] += $val[$key];
                }
                if($day >= 4 && $day <= 7){
                    $val['overday4_7_amount'] += $val[$key];
                }
                if($day >= 8 && $day <= 15){
                    $val['overday8_15_amount'] += $val[$key];
                }
                if($day >= 16 && $day <= 30){
                    $val['overday16_30_amount'] += $val[$key];
                }
            }
            $val['overday1_7_amount'] = $val['overday1_3_amount'] + $val['overday4_7_amount'];
            //S1+S2
            $val['overday1_15_amount'] = $val['overday1_7_amount'] + $val['overday8_15_amount'];
        }
    }
}
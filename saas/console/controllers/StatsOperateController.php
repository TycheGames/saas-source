<?php

/**
 * Created by PhpStorm.
 * User: user
 * Date: 2016/9/29
 * Time: 14:58
 */

namespace console\controllers;

use backend\models\AdminUser;
use backend\models\remind\RemindLog;
use backend\models\remind\RemindOrder;
use common\helpers\Util;
use common\models\ClientInfoLog;
use common\models\manual_credit\ManualCreditLog;
use common\models\order\FinancialLoanRecord;
use common\models\order\UserLoanOrder;
use common\models\order\UserLoanOrderDelayPaymentLog;
use common\models\order\UserLoanOrderExtendLog;
use common\models\order\UserLoanOrderRepayment;
use common\models\order\UserOrderLoanCheckLog;
use common\models\order\UserRepaymentLog;
use common\models\risk\RiskResultSnapshot;
use common\models\stats\DailyCreditAuditData;
use common\models\stats\DailyRegisterConver;
use common\models\stats\DailyRepaymentGrandTotal;
use common\models\stats\DailyRiskRejectData;
use common\models\stats\DailyUserData;
use common\models\stats\DailyUserFullData;
use common\models\stats\RemindDayData;
use common\models\stats\RemindReachRepay;
use common\models\stats\UserOperationData;
use common\models\stats\UserStructureOrderTransform;
use common\models\user\LoanPerson;
use common\models\user\UserRegisterInfo;
use common\models\user\UserVerification;
use common\models\user\UserVerificationLog;
use common\services\LogStashService;
use common\services\stats\DailyCreditAuditDataService;
use common\services\stats\DailyUserDataFullService;
use common\services\stats\DailyUserDataService;
use common\services\stats\DayDataStatisticsService;
use common\services\stats\DayOrderDataStatisticsService;
use common\services\stats\HourLoanRepayDataService;
use common\services\stats\TotalRepaymentAmountService;
use common\services\stats\UserStructureOrderTransformService;
use common\services\stats\UserStructureRepaymentService;
use yii\db\Query;
use common\services\stats\ReApplyDataService;
use yii\console\ExitCode;

class StatsOperateController extends BaseController {

    /**
     * StatsOperateController 插入用户日报数据
     * @param string $startTime
     * @param string $endTime
     */
    public function actionBuildDailyUserData($startTime = '',$endTime = '')
    {
        if(empty($startTime)){
            $leftTime = strtotime("today");
            if(date('H',time()) == 0){ //更新前一天的
                $leftTime -= 86400;
            }
        }else{
            $leftTime = strtotime($startTime);
        }
        if(empty($endTime)){
            $rightTime = $leftTime + 86400;
        }else{
            $rightTime = strtotime($endTime) + 86400;
        }
        $this->printMessage('START');
        while($rightTime > $leftTime){
            $today = date('Y-m-d',$leftTime);
            $this->printMessage($today);
            $arr = DailyUserDataService::getDailyUserData($leftTime,$leftTime+86400);
            foreach ($arr as $merchantId => $value){
                foreach ($value as $appMarket => $val){
                    $dailyUserData = DailyUserData::find()->where(['date' => $today,'merchant_id' => $merchantId,'app_market' => $appMarket])->one();
                    if($dailyUserData == null){
                        $dailyUserData = new DailyUserData();
                    }
                    $dailyUserData->date = $today;
                    $dailyUserData->merchant_id = $merchantId;
                    $dailyUserData->app_market = $appMarket;
                    foreach ($val as $key=>$v){
                        $dailyUserData->{$key} = $v;
                    }
                    $dailyUserData->save();
                }
            }
            $leftTime += 86400;
        }
        $this->printMessage('END');
    }


    /**
     * @name 插入用户日报数据（全量）
     * @param integer $startTime 开始时间
     * @param integer $endTime   结束时间
     */
    public function actionBuildDailyUserFullData($startTime = '', $endTime = '')
    {
        if(!$this->lock()){
            return ExitCode::UNSPECIFIED_ERROR;
        }
        if(empty($startTime)){
            $leftTime = strtotime("today");
            if(date('H',time()) == 0){ //更新前一天的
                $leftTime -= 86400;
            }
        }else{
            $leftTime = strtotime($startTime);
        }
        if(empty($endTime)){
            $rightTime = $leftTime + 86400;
        }else{
            $rightTime = strtotime($endTime) + 86400;
        }
        echo 'start'.PHP_EOL;
        while($rightTime > $leftTime){
            $today = date('Y-m-d',$leftTime);
            echo $today.PHP_EOL;
            $arr = DailyUserDataFullService::getDailyUserDataFull($leftTime,$leftTime+86400);
            foreach ($arr as $merchantId => $value){
                foreach ($value as $appMarket => $val2){
                    foreach ($val2 as $mediaSource => $packageNameData){
                        foreach ($packageNameData as $packageName => $val) {
                            $mediaSource       = empty($mediaSource) ? '' : $mediaSource; //null和空字符 统一转空字符
                            $dailyUserFullData = DailyUserFullData::find()->where(
                                ['date' => $today, 'merchant_id' => $merchantId, 'app_market' => $appMarket, 'media_source' => $mediaSource, 'package_name' => $packageName]
                            )->one();
                            if ($dailyUserFullData == null) {
                                $dailyUserFullData = new DailyUserFullData();
                            }
                            $dailyUserFullData->date         = $today;
                            $dailyUserFullData->merchant_id  = $merchantId;
                            $dailyUserFullData->app_market   = $appMarket;
                            $dailyUserFullData->media_source = $mediaSource;
                            $dailyUserFullData->package_name = $packageName;
                            foreach ($val as $key => $v) {
                                $dailyUserFullData->{$key} = $v;
                            }
                            $dailyUserFullData->save();
                        }
                    }
                }
            }
            $leftTime += 86400;
        }
        echo 'END'.PHP_EOL;
    }


    /**
     * @name 每日借还款统计新/actionLoanRepayList
     * @param int $type
     * @return int
     * @throws \Exception
     */
    public function actionLoanRepayList($startDate="",$endDate=""){
        $startTime = empty($startDate)?strtotime("today"):strtotime($startDate); //今天零点
        $_hour = date('H',time());//当前的小时数
        if( intval($_hour) <= 1 ){
            $startTime = $startTime-86400;
        }
        $endTime = empty($endDate)?$startTime + 86400:strtotime($endDate);
        if (!empty($startTime)){
            $dt_start = $startTime;
            $dt_end = $endTime;
            $service = new HourLoanRepayDataService();
            while ($dt_start<$dt_end){
                $service->getTradeData($dt_start, $dt_start+86400);
                $dt_start = strtotime('+1 day',$dt_start);
            }
        }
    }


    /**
     * @name 每日还款金额和单数数据/actionDayDataStatisticsRun
     * @author meiyunfei
     * @date 2018-01
     *
     */
    public function actionDayDataStatisticsRun($type = 0, $startTime='', $endTime=''){
        Util::cliLimitChange(1024);
        //频率高加锁
        if(!$this->lock()){
            return ExitCode::UNSPECIFIED_ERROR;
        }
        if($type == 0){
            $endDate =date("Y-m-d",strtotime("+14 day"));
            $startDate = date("Y-m-d",strtotime("+1 day"));
        }elseif($type == 1){
            $endDate =date("Y-m-d",strtotime("+1 day"));
            $startDate = date("Y-m-d",strtotime("-8 day"));
        }elseif($type == 2){
            $endDate =date("Y-m-d",strtotime("-8 day"));
            $startDate = date("Y-m-d",strtotime("-240 day"));
        }else{
            $endDate = $endTime;
            $startDate = $startTime;
        }
        $countDate = (strtotime($endDate)-strtotime($startDate))/86400;
        $dayDataStatisticsService = new DayDataStatisticsService();
        for($datei = 0;$datei<$countDate;$datei++){
            $dateNum = strtotime($endDate)-$datei*86400;
            $date = date('Y-m-d',$dateNum);
            $this->printMessage('date:'.$date);
            $dayDataStatisticsService->runDayDataStatisticsNew($date);
        }
    }

    /**
     * @name 总还款金额数据
     */
    public function actionTotalRepaymentAmountRun($type = 0, $startTime='', $endTime=''){
        Util::cliLimitChange(1024);
        if($type == 0){
            //频率高加锁
            if(!$this->lock()){
                return ExitCode::UNSPECIFIED_ERROR;
            }
            $endDate =date("Y-m-d",strtotime("+14 day"));
            $startDate = date("Y-m-d",strtotime("+1 day"));
        }elseif($type == 1){
            $endDate =date("Y-m-d",strtotime("+1 day"));
            $startDate = date("Y-m-d",strtotime("-8 day"));
        }elseif($type == 2){
            $endDate =date("Y-m-d",strtotime("-8 day"));
            $startDate = date("Y-m-d",strtotime("-240 day"));
        }else{
            $endDate = $endTime;
            $startDate = $startTime;
        }
        $countDate = (strtotime($endDate)-strtotime($startDate))/86400;
        $dayDataStatisticsService = new TotalRepaymentAmountService();
        for($datei = 0;$datei<$countDate;$datei++){
            $dateNum = strtotime($endDate)-$datei*86400;
            $date = date('Y-m-d',$dateNum);
            $dayDataStatisticsService->runTotalRepaymentAmount($date);
        }
    }


    /**
 * 财务数据-每日借款数据
 * @param $startDate
 * @param $endDate
 * @return int
 * @throws \Exception
 */

    public  function actionDailyLoan($startDate = '', $endDate = '',$type=0){
        if(!$this->lock()){
            return ExitCode::UNSPECIFIED_ERROR;
        }
        Util::cliLimitChange(1024);
        $_hour = date('H',time());//当前的小时数

        //如果当前时间为24点，则计算前一天所有的放款数据,显示日期为前一天的24时
        if( ($_hour == 0 && date('i',time())<=20) || $type>0 ){
            $startDate = !empty($startDate)?$startDate:date('Y-m-d',strtotime('today')-7*86400);
            $endDate = !empty($endDate)?$endDate:date('Y-m-d',strtotime('+1 day'));
        }else{
            $startDate = !empty($startDate)?$startDate:date('Y-m-d',strtotime('today'));
            $endDate = !empty($endDate)?$endDate:date('Y-m-d',strtotime('+1 day'));
        }
        $countDate = (strtotime($endDate)-strtotime($startDate))/86400;
        $service = new DayOrderDataStatisticsService();
        for($datei = 1;$datei<=$countDate;$datei++){
            $dateNum = strtotime($endDate)-$datei*86400;
            $service->actionDailyLoans($dateNum);
        }
    }

    /**
 * 财务数据-每日借款数据-新老按全平台
 * @param $startDate
 * @param $endDate
 * @return int
 * @throws \Exception
 */

    public  function actionDailyLoanByFullPlatform($startDate = '', $endDate = '',$type=0){
        if(!$this->lock()){
            return ExitCode::UNSPECIFIED_ERROR;
        }
        Util::cliLimitChange(1024);
        $_hour = date('H',time());//当前的小时数

        //如果当前时间为24点，则计算前一天所有的放款数据,显示日期为前一天的24时
        if( ($_hour == 0 && date('i',time())<=20) || $type>0 ){
            $startDate = !empty($startDate)?$startDate:date('Y-m-d',strtotime('today')-7*86400);
            $endDate = !empty($endDate)?$endDate:date('Y-m-d',strtotime('+1 day'));
        }else{
            $startDate = !empty($startDate)?$startDate:date('Y-m-d',strtotime('today'));
            $endDate = !empty($endDate)?$endDate:date('Y-m-d',strtotime('+1 day'));
        }
        $countDate = (strtotime($endDate)-strtotime($startDate))/86400;
        $service = new DayOrderDataStatisticsService();
        for($datei = 1;$datei<=$countDate;$datei++){
            $dateNum = strtotime($endDate)-$datei*86400;
            $service->actionDailyLoans($dateNum,false,true);
        }
    }

    /**
     * 财务数据-每日借款数据(按打款金额)
     * @param $startDate
     * @param $endDate
     * @return int
     * @throws \Exception
     */

    public  function actionDailyLoan2($startDate = '', $endDate = '',$type=0){
        if(!$this->lock()){
            return ExitCode::UNSPECIFIED_ERROR;
        }
        Util::cliLimitChange(1024);
        $_hour = date('H',time());//当前的小时数

        //如果当前时间为24点，则计算前一天所有的放款数据,显示日期为前一天的24时
        if( ($_hour == 0 && date('i',time())<=20) || $type>0 ){
            $startDate = !empty($startDate)?$startDate:date('Y-m-d',strtotime('today')-7*86400);
            $endDate = !empty($endDate)?$endDate:date('Y-m-d',strtotime('+1 day'));
        }else{
            $startDate = !empty($startDate)?$startDate:date('Y-m-d',strtotime('today'));
            $endDate = !empty($endDate)?$endDate:date('Y-m-d',strtotime('+1 day'));
        }
        $countDate = (strtotime($endDate)-strtotime($startDate))/86400;
        $service = new DayOrderDataStatisticsService();
        for($datei = 1;$datei<=$countDate;$datei++){
            $dateNum = strtotime($endDate)-$datei*86400;
            $service->actionDailyLoans($dateNum,true,false);
        }
    }

    /**
     * 财务数据-每日借款数据(按打款金额-新老按全平台)
     * @param $startDate
     * @param $endDate
     * @return int
     * @throws \Exception
     */

    public  function actionDailyLoan2ByFullPlatform($startDate = '', $endDate = '',$type=0){
        if(!$this->lock()){
            return ExitCode::UNSPECIFIED_ERROR;
        }
        Util::cliLimitChange(1024);
        $_hour = date('H',time());//当前的小时数

        //如果当前时间为24点，则计算前一天所有的放款数据,显示日期为前一天的24时
        if( ($_hour == 0 && date('i',time())<=20) || $type>0 ){
            $startDate = !empty($startDate)?$startDate:date('Y-m-d',strtotime('today')-7*86400);
            $endDate = !empty($endDate)?$endDate:date('Y-m-d',strtotime('+1 day'));
        }else{
            $startDate = !empty($startDate)?$startDate:date('Y-m-d',strtotime('today'));
            $endDate = !empty($endDate)?$endDate:date('Y-m-d',strtotime('+1 day'));
        }
        $countDate = (strtotime($endDate)-strtotime($startDate))/86400;
        $service = new DayOrderDataStatisticsService();
        for($datei = 1;$datei<=$countDate;$datei++){
            $dateNum = strtotime($endDate)-$datei*86400;
            $service->actionDailyLoans($dateNum,true,true);
        }
    }

    /**
     * 财务数据-每日借款数据-(按本金-按结构)
     * @param $startDate
     * @param $endDate
     * @return int
     * @throws \Exception
     */
    public  function actionDailyLoanByUserStructure($startDate = '', $endDate = '',$type=0){
        if(!$this->lock()){
            return ExitCode::UNSPECIFIED_ERROR;
        }
        Util::cliLimitChange(1024);
        $_hour = date('H',time());//当前的小时数

        //如果当前时间为24点，则计算前一天所有的放款数据,显示日期为前一天的24时
        if( ($_hour == 0 && date('i',time())<=20) || $type>0 ){
            $startDate = !empty($startDate)?$startDate:date('Y-m-d',strtotime('today')-7*86400);
            $endDate = !empty($endDate)?$endDate:date('Y-m-d',strtotime('+1 day'));
        }else{
            $startDate = !empty($startDate)?$startDate:date('Y-m-d',strtotime('today'));
            $endDate = !empty($endDate)?$endDate:date('Y-m-d',strtotime('+1 day'));
        }
        $countDate = (strtotime($endDate)-strtotime($startDate))/86400;
        $service = new DayOrderDataStatisticsService();
        for($datei = 1;$datei<=$countDate;$datei++){
            $dateNum = strtotime($endDate)-$datei*86400;
            $service->actionDailyLoansUserStructure($dateNum);
        }
    }

    /**
     * 财务数据-每日借款数据-(按打款金额-按结构)
     * @param $startDate
     * @param $endDate
     * @return int
     * @throws \Exception
     */

    public  function actionDailyLoan2ByUserStructure($startDate = '', $endDate = '',$type=0){
        if(!$this->lock()){
            return ExitCode::UNSPECIFIED_ERROR;
        }
        Util::cliLimitChange(1024);
        $_hour = date('H',time());//当前的小时数

        //如果当前时间为24点，则计算前一天所有的放款数据,显示日期为前一天的24时
        if( ($_hour == 0 && date('i',time())<=20) || $type>0 ){
            $startDate = !empty($startDate)?$startDate:date('Y-m-d',strtotime('today')-7*86400);
            $endDate = !empty($endDate)?$endDate:date('Y-m-d',strtotime('+1 day'));
        }else{
            $startDate = !empty($startDate)?$startDate:date('Y-m-d',strtotime('today'));
            $endDate = !empty($endDate)?$endDate:date('Y-m-d',strtotime('+1 day'));
        }
        $countDate = (strtotime($endDate)-strtotime($startDate))/86400;
        $service = new DayOrderDataStatisticsService();
        for($datei = 1;$datei<=$countDate;$datei++){
            $dateNum = strtotime($endDate)-$datei*86400;
            $service->actionDailyLoansUserStructure($dateNum,true);
        }
    }

    /**
     * @name 还款复借统计 - 每日复借数据统计
     */
    public function actionRepayReborrowing($startDate="",$endDate=""){
        if(!$this->lock()){
            return ExitCode::UNSPECIFIED_ERROR;
        }
        $startTime=$startDate?strtotime($startDate):strtotime("today")-60*86400;
        $endTime=$endDate?strtotime($endDate):strtotime("today")+86400;
        $_hour = date('H',time());//当前的小时数
        if( $_hour <= 2 ){
            $endTime = $startTime;
            $startTime = $endTime-86400;
        }
        $countDate = ($endTime-$startTime)/86400;
        $service = new ReApplyDataService();
        for($datei = 1;$datei<=$countDate;$datei++){
            $dateNum = $endTime-$datei*86400;
            
            $service->actionDayRepayRun($dateNum,$fields='o.merchant_id,o.user_id,i.appMarket');//全部放款APP与全部产品
        }
    }


    /**
     * @name 信审每日统计脚本-按信审员维度
     * @param string $startTime
     */
    public function actionCreditAuditData($startTime = ''){
        if(!$this->lock()){
            return ExitCode::UNSPECIFIED_ERROR;
        }
        $leftTime = !empty($startTime) ? strtotime($startTime) : strtotime("today");
        if(date('H',time()) == 0 && empty($startTime)){ //更新前一天的
            $leftTime -= 86400;
        }
        $rightTime = $leftTime + 86400;
        $today = date('Y-m-d',$leftTime);
        $this->printMessage('START:'.$today);
        $arr = [];
        $merchantIds = [];
        //审批数量
        $res = DailyCreditAuditDataService::getAuditCountData($leftTime,$rightTime);
        foreach ($res as $val){
            $arr[$val['operator_id']][$val['action']]['audit_count'] = $val['audit_count'];
            $merchantIds[$val['operator_id']] = $val['merchant_id'];
        }
        //审批通过数量
        $res = DailyCreditAuditDataService::getPassCountData($leftTime,$rightTime);
        foreach ($res as $val){
            $arr[$val['operator_id']][$val['action']]['pass_count'] = $val['pass_count'];
        }

        foreach ($arr as $operator_id => $value){
            foreach ($value as $action => $val){
                $dailyCreditAuditData = DailyCreditAuditData::find()->where(['date' => $today,'action' => $action,'operator_id' => $operator_id])->one();
                if($dailyCreditAuditData == null){
                    $dailyCreditAuditData = new DailyCreditAuditData();
                    $dailyCreditAuditData->date = $today;
                    $dailyCreditAuditData->action = $action;
                    $dailyCreditAuditData->operator_id = $operator_id;
                    $dailyCreditAuditData->merchant_id = $merchantIds[$operator_id];
                }
                foreach ($val as $key=>$v){
                    $dailyCreditAuditData->{$key} = $v;
                }
                $dailyCreditAuditData->save();
            }
        }

        //更新成功放款
        $loanData = UserLoanOrderRepayment::find()
            ->select([
                'log.operator_id',
                'log.action',
                'loan_success_count' => 'count(1)'
            ])
            ->from(UserLoanOrderRepayment::tableName() . '  repayment')
            ->leftJoin(ManualCreditLog::tableName() . ' log',' log.order_id = repayment.order_id')
            ->where(['>=', 'repayment.loan_time' , $leftTime])
            ->andWhere(['<', 'repayment.loan_time' , $rightTime])
            ->andWhere(['log.type'=> ManualCreditLog::TYPE_PASS,'log.is_auto' => ManualCreditLog::NO_AUTO])
            ->groupBy(['log.operator_id','log.action'])
            ->asArray()
            ->all(\Yii::$app->get('db_read_1'));


        foreach ($loanData as $item){
            $dailyCreditAuditData = DailyCreditAuditData::find()->where(['date' => $today,'action' => $item['action'],
                'operator_id' => $item['operator_id']])->one();
            if($dailyCreditAuditData){
                foreach ($item as $key=>$v){
                    $dailyCreditAuditData->{$key} = $v;
                }
                $dailyCreditAuditData->save();
            }
        }

        //更新首逾
        $overdueData = UserLoanOrderRepayment::find()
            ->select([
                'log.operator_id',
                'log.action',
                'first_overdue_count' => 'count(1)',
                'date' => 'FROM_UNIXTIME(repayment.loan_time,\'%Y-%m-%d\')'
            ])
            ->from(UserLoanOrderRepayment::tableName() . '  repayment')
            ->leftJoin(ManualCreditLog::tableName() . ' log',' log.order_id = repayment.order_id')
            ->where(['repayment.is_overdue' => UserLoanOrderRepayment::IS_OVERDUE_YES])
            ->andWhere(['>=', 'repayment.plan_fee_time' , $leftTime])
            ->andWhere(['<', 'repayment.plan_fee_time' , $rightTime])
            ->andWhere(['log.type'=> ManualCreditLog::TYPE_PASS,'log.is_auto' => ManualCreditLog::NO_AUTO])
            ->groupBy(['date','log.operator_id','log.action'])
            ->asArray()
            ->all(\Yii::$app->get('db_read_1'));


        foreach ($overdueData as $item){
            $dailyCreditAuditData = DailyCreditAuditData::find()->where(['date' => $item['date'],'action' => $item['action'],
                'operator_id' => $item['operator_id']])->one();
            if($dailyCreditAuditData){
                foreach ($item as $key=>$v){
                    $dailyCreditAuditData->{$key} = $v;
                }
                $dailyCreditAuditData->save();
            }
        }

        $this->printMessage('END');
    }


    /**
     * @name 风控被拒每日统计 - 风控被拒原因每日统计
     * @param string $startTime
     */
    public function actionBuildDailyRiskRejectData($startTime = ''){
        $leftTime = !empty($startTime) ? strtotime($startTime) : strtotime("today");
        if(date('H',time()) == 0 && empty($startTime)){ //更新前一天的
            $leftTime -= 86400;
        }
        $rightTime = $leftTime + 86400;
        $today = date('Y-m-d',$leftTime);
        $arr = [];

        //风控结果数据
        $res = RiskResultSnapshot::find()
            ->select('B.appMarket,A.tree_code,A.txt,count(A.id) as reject_count')
            ->from(RiskResultSnapshot::tableName() . ' A')
            ->leftJoin(UserRegisterInfo::tableName() . ' B', 'A.user_id = B.user_id')
            ->where(['>=','A.created_at',$leftTime])
            ->andWhere(['<','A.created_at',$rightTime])
            ->andWhere(['A.result' => 'reject'])
            ->groupBy('B.appMarket,A.tree_code,A.txt')
            ->asArray()
            ->all(\Yii::$app->get('db_read_1'));


        foreach ($res as $val){
            $arr[$val['appMarket']][$val['tree_code']][$val['txt']] = $val['reject_count'];
        }

        foreach ($arr as $app_market => $value){
            foreach ($value as $tree_code =>$val){
                foreach ($val as $txt => $reject_count){
                    /** @var DailyRiskRejectData $dailyRiskRejectData */
                    $dailyRiskRejectData = DailyRiskRejectData::find()
                        ->where(['date' => $today,'app_market' => $app_market, 'tree_code' => $tree_code, 'txt' => $txt])->one();
                    if($dailyRiskRejectData == null){
                        $dailyRiskRejectData = new DailyRiskRejectData();
                        $dailyRiskRejectData->date = $today;
                        $dailyRiskRejectData->app_market = $app_market;
                        $dailyRiskRejectData->tree_code = $tree_code;
                        $dailyRiskRejectData->txt = $txt;
                    }
                    $dailyRiskRejectData->reject_count = $reject_count;
                    $dailyRiskRejectData->save();
                }
            }

        }
    }


    /**
     * @name 用户每日转化 - 每日借还款数据对比|每日用户KYC转化
     * @param string $startTime
     * @param string $endTime
     */
    public function actionUserOperationData($startTime = '', $endTime = ''){
        if(!$this->lock()){
            return ExitCode::UNSPECIFIED_ERROR;
        }
        if(empty($startTime)){
            $leftTime = strtotime("today");
            if(date('H',time()) == 0){ //更新前一天的
                $leftTime -= 86400;
            }
        }else{
            $leftTime = strtotime($startTime);
        }
        if(empty($endTime)){
            $rightTime = $leftTime + 86400;
        }else{
            $rightTime = strtotime($endTime) + 86400;
        }
        echo 'start'.PHP_EOL;
        $aliLogService = new LogStashService();
        while($rightTime > $leftTime) {
            $today = date('Y-m-d', $leftTime);
            echo $today.PHP_EOL;
            $arr = [];

            //认证basic/KYC/联系人/绑卡

            $sql = "select count(DISTINCT user_id) as uv,count(user_id) as pv,appMarket,type,status group by appMarket,type,status limit 1000";
            $res = $aliLogService->getYunTuLoanLog($sql,$leftTime, $leftTime+86400);
            foreach ($res as $v){
                $value =  $v->getContents();
                /*
                array(5) {
                  'appMarket' =>
                  string(22) "sashaktrupee_mobimagic"
                  'pv' =>
                  string(1) "1"
                  'status' =>
                  string(7) "success"
                  'type' =>
                  string(12) "contact_info"
                  'uv' =>
                  string(1) "1"
                }
                */
                if($key = array_search($value['type'].'_'.$value['status'].'_pv',UserOperationData::$ali_log_type_map,true)){
                    $arr[$value['appMarket']][$key] = $value['pv'];
                }
                if($key = array_search($value['type'].'_'.$value['status'].'_uv',UserOperationData::$ali_log_type_map,true)){
                    $arr[$value['appMarket']][$key] = $value['uv'];
                }
            }

            $sql = "select count(DISTINCT user_id) as uv,count(user_id) as pv,appMarket,type group by appMarket,type limit 1000";
            $res = $aliLogService->getYunTuLoanLog($sql,$leftTime, $leftTime+86400);
            foreach ($res as $v){
                $value =  $v->getContents();
                if($key = array_search($value['type'].'_pv',UserOperationData::$ali_log_type_map,true)){
                    $arr[$value['appMarket']][$key] = $value['pv'];
                }
                if($key = array_search($value['type'].'_uv',UserOperationData::$ali_log_type_map,true)){
                    $arr[$value['appMarket']][$key] = $value['uv'];
                }
            }

            //注册
            $res = LoanPerson::find()
                ->select(['reg.appMarket','count(user.id) as count'])
                ->from(LoanPerson::tableName() . '  user')
                ->leftJoin(UserRegisterInfo::tableName() . '  reg','reg.user_id = user.id')
                ->where(['>=','user.created_at',$leftTime])
                ->andWhere(['<','user.created_at',$leftTime+86400])
                ->groupBy(['reg.appMarket'])
                ->asArray()
                ->all(\Yii::$app->get('db_read_1'));
            foreach ($res as $val){
                $arr[$val['appMarket']][UserOperationData::TYPE_USER_REGISTER] = $val['count'];
            }

            //三要素认证成功数
            $field = ['reg.appMarket,count(log.user_id) as count'];
            $query = UserVerificationLog::find()
                ->select(['log1.user_id'])
                ->from(UserVerificationLog::tableName() . ' log1')
                ->leftJoin(UserVerificationLog::tableName() . '  log2','log1.user_id = log2.user_id')
                ->leftJoin(UserVerificationLog::tableName() . '  log3','log1.user_id = log3.user_id')
                ->where(['log1.type' => UserVerification::TYPE_OCR_PAN,'log1.status' => UserVerificationLog::STATUS_VERIFY_SUCCESS])
                ->andWhere(['>=','log1.created_at',$leftTime])
                ->andWhere(['<','log1.created_at',$leftTime+86400])
                ->andWhere(['log2.type' => UserVerification::TYPE_WORK,'log2.status' => UserVerificationLog::STATUS_VERIFY_SUCCESS])
                ->andWhere(['>=','log2.created_at',$leftTime])
                ->andWhere(['<','log2.created_at',$leftTime+86400])
                ->andWhere(['log3.type' => UserVerification::TYPE_CONTACT,'log3.status' => UserVerificationLog::STATUS_VERIFY_SUCCESS])
                ->andWhere(['>=','log3.created_at',$leftTime])
                ->andWhere(['<','log3.created_at',$leftTime+86400])
                ->groupBy('log1.user_id');
            $res = (new Query())->select($field)
                ->from(['log' => $query])
                ->leftJoin(UserRegisterInfo::tableName() . ' reg','log.user_id = reg.user_id')
                ->groupBy(['reg.appMarket'])->createCommand(\Yii::$app->get('db_read_1'))->queryAll();
            foreach ($res as $val){
                $arr[$val['appMarket']][UserOperationData::TYPE_THREE_ELEMENT_VERIFY] = $val['count'];
            }

            //新客申请单数
            $res = UserLoanOrder::find()
                ->select(['reg.appMarket','count(order.id) as count','count(DISTINCT(order.user_id)) as user_count'])
                ->from(UserLoanOrder::tableName() . '  order')
                ->leftJoin(UserRegisterInfo::tableName() . '  reg','reg.user_id = order.user_id')
                ->where(['order.is_first' => UserLoanOrder::FIRST_LOAN_IS])
                ->andWhere(['>=','order.created_at',$leftTime])
                ->andWhere(['<','order.created_at',$leftTime+86400])
                ->groupBy(['reg.appMarket'])
                ->asArray()
                ->all(\Yii::$app->get('db_read_1'));
            foreach ($res as $val){
                $arr[$val['appMarket']][UserOperationData::TYPE_NEW_USER_APPLY_ORDER] = $val['count'];
                $arr[$val['appMarket']][UserOperationData::TYPE_NEW_USER_APPLY_BY_PERSON] = $val['user_count'];
            }

            //新客风控通过的的订单数  （审核通过 至放款或 至绑卡审核）
            $field = ['appMarket,count(id) as count'];
            $query = UserLoanOrder::find()
                ->select('reg.appMarket,order.id')
                ->from(UserLoanOrder::tableName() . '  order')
                ->leftJoin(UserRegisterInfo::tableName() . '  reg','reg.user_id = order.user_id')
                ->leftJoin(UserOrderLoanCheckLog::tableName() . '  log','order.id = log.order_id')
                ->where(
                    [
                        'order.is_first' => UserLoanOrder::FIRST_LOAN_IS,
                        'log.before_status' => UserLoanOrder::STATUS_CHECK,
                        'log.after_status' => [UserLoanOrder::STATUS_LOANING,UserLoanOrder::STATUS_WAIT_DEPOSIT,UserLoanOrder::STATUS_WAIT_DRAW_MONEY]
                    ])
                ->andWhere(['>=','log.created_at',$leftTime])
                ->andWhere(['<','log.created_at',$leftTime+86400])
                ->groupBy(['order.id','reg.appMarket']);
            $res = (new Query())->select($field)->from(['order' => $query])
                ->groupBy(['appMarket'])->createCommand(\Yii::$app->get('db_read_1'))->queryAll();
            foreach ($res as $val){
                $arr[$val['appMarket']][UserOperationData::TYPE_NEW_USER_RISK_PASS] = $val['count'];
            }

            //风控通过（机审通过）的订单数
            $field = ['appMarket,count(id) as count'];
            $query = UserLoanOrder::find()
                ->select('reg.appMarket,order.id')
                ->from(UserLoanOrder::tableName() . '  order')
                ->leftJoin(UserRegisterInfo::tableName() . '  reg','reg.user_id = order.user_id')
                ->leftJoin(UserOrderLoanCheckLog::tableName() . '  log','order.id = log.order_id')
                ->where(
                    [
                        'log.before_status' => UserLoanOrder::STATUS_CHECK,
                        'log.after_status' => [UserLoanOrder::STATUS_LOANING,UserLoanOrder::STATUS_WAIT_DEPOSIT,UserLoanOrder::STATUS_WAIT_DRAW_MONEY],
                        'log.before_audit_status' => UserLoanOrder::AUDIT_STATUS_AUTO_CHECK,
                        'log.after_audit_status' => UserLoanOrder::AUDIT_STATUS_AUTO_CHECK,
                    ])
                ->andWhere(['>=','log.created_at',$leftTime])
                ->andWhere(['<','log.created_at',$leftTime+86400])
                ->groupBy(['order.id','reg.appMarket']);
            $res = (new Query())->select($field)->from(['order' => $query])
                ->groupBy(['appMarket'])->createCommand(\Yii::$app->get('db_read_1'))->queryAll();
            foreach ($res as $val){
                $arr[$val['appMarket']][UserOperationData::TYPE_USER_RISK_CREDIT_PASS] = $val['count'];
            }

            //风控通过（人工信审通过）的订单数
            $field = ['appMarket,count(id) as count'];
            $query = UserLoanOrder::find()
                ->select('reg.appMarket,order.id')
                ->from(UserLoanOrder::tableName() . '  order')
                ->leftJoin(UserRegisterInfo::tableName() . '  reg','reg.user_id = order.user_id')
                ->leftJoin(UserOrderLoanCheckLog::tableName() . '  log','order.id = log.order_id')
                ->where(
                    [
                        'log.before_status' => UserLoanOrder::STATUS_CHECK,
                        'log.after_status' => [UserLoanOrder::STATUS_LOANING,UserLoanOrder::STATUS_WAIT_DEPOSIT,UserLoanOrder::STATUS_WAIT_DRAW_MONEY],
                        'log.before_audit_status' => UserLoanOrder::AUDIT_STATUS_MANUAL_CHECK,
                        'log.after_audit_status' => UserLoanOrder::AUDIT_STATUS_MANUAL_CHECK_FINISH,
                    ])
                ->andWhere(['>=','log.created_at',$leftTime])
                ->andWhere(['<','log.created_at',$leftTime+86400])
                ->groupBy(['order.id','reg.appMarket']);
            $res = (new Query())->select($field)->from(['order' => $query])
                ->groupBy(['appMarket'])->createCommand(\Yii::$app->get('db_read_1'))->queryAll();
            foreach ($res as $val){
                $arr[$val['appMarket']][UserOperationData::TYPE_USER_MANUAL_CREDIT_PASS] = $val['count'];
            }

            //进人工卡认证审核的订单数
            $field = ['appMarket,count(id) as count'];
            $query = UserLoanOrder::find()
                ->select('reg.appMarket,order.id')
                ->from(UserLoanOrder::tableName() . '  order')
                ->leftJoin(UserRegisterInfo::tableName() . '  reg','reg.user_id = order.user_id')
                ->leftJoin(UserOrderLoanCheckLog::tableName() . '  log','order.id = log.order_id')
                ->where([
                    'log.before_status' => UserLoanOrder::STATUS_CHECK,
                    'log.after_status' => UserLoanOrder::STATUS_WAIT_DEPOSIT
                ])
                ->andWhere(['>=','log.created_at',$leftTime])
                ->andWhere(['<','log.created_at',$leftTime+86400])
                ->groupBy(['order.id','reg.appMarket']);
            $res = (new Query())->select($field)->from(['order' => $query])
                ->groupBy(['appMarket'])->createCommand(\Yii::$app->get('db_read_1'))->queryAll();
            foreach ($res as $val){
                $arr[$val['appMarket']][UserOperationData::TYPE_USER_RISK_PASS_TO_BANK_AUDIT] = $val['count'];
            }

            //卡认证审核通过的订单数
            $res = ManualCreditLog::find()
                ->select('reg.appMarket,count(order.id) as count')
                ->from(ManualCreditLog::tableName() . '  credit')
                ->leftJoin(UserLoanOrder::tableName() . '  order','credit.order_id = order.id')
                ->leftJoin(UserRegisterInfo::tableName() . '  reg','reg.user_id = order.user_id')
                ->where([
                    'credit.action' => ManualCreditLog::ACTION_AUDIT_BANK,
                    'credit.type' => ManualCreditLog::TYPE_PASS,
                ])
                ->andWhere(['>=','credit.created_at',$leftTime])
                ->andWhere(['<','credit.created_at',$leftTime+86400])
                ->groupBy(['reg.appMarket'])
                ->asArray()
                ->all(\Yii::$app->get('db_read_1'));
            foreach ($res as $val){
                $arr[$val['appMarket']][UserOperationData::TYPE_USER_RISK_PASS_TO_BANK_AUDIT_PASS] = $val['count'];
            }

            //卡认证审核被拒的订单数
            $res = ManualCreditLog::find()
                ->select('reg.appMarket,count(order.id) as count')
                ->from(ManualCreditLog::tableName() . '  credit')
                ->leftJoin(UserLoanOrder::tableName() . '  order','credit.order_id = order.id')
                ->leftJoin(UserRegisterInfo::tableName() . '  reg','reg.user_id = order.user_id')
                ->where([
                    'credit.action' => ManualCreditLog::ACTION_AUDIT_BANK,
                    'credit.type' => ManualCreditLog::TYPE_REJECT,
                ])
                ->andWhere(['>=','credit.created_at',$leftTime])
                ->andWhere(['<','credit.created_at',$leftTime+86400])
                ->groupBy(['reg.appMarket'])
                ->asArray()
                ->all(\Yii::$app->get('db_read_1'));
            foreach ($res as $val){
                $arr[$val['appMarket']][UserOperationData::TYPE_USER_RISK_PASS_TO_BANK_AUDIT_NO_PASS] = $val['count'];
            }

            //放款数
            $res = FinancialLoanRecord::find()
                ->select('reg.appMarket,count(loan.id) as count')
                ->from(FinancialLoanRecord::tableName() . '  loan')
                ->leftJoin(UserLoanOrder::tableName() . '  order','loan.business_id = order.id')
                ->leftJoin(UserRegisterInfo::tableName() . '  reg','reg.user_id = loan.user_id')
                ->where(['>=','loan.created_at',$leftTime])
                ->andWhere(['<','loan.created_at',$leftTime+86400])
                ->groupBy(['reg.appMarket'])
                ->asArray()
                ->all(\Yii::$app->get('db_read_1'));
            foreach ($res as $val){
                $arr[$val['appMarket']][UserOperationData::TYPE_LOAN_ORDER] = $val['count'];
            }

            //放款成功数
            $res = UserLoanOrderRepayment::find()
                ->select('reg.appMarket,count(repayment.id) as count')
                ->from(UserLoanOrderRepayment::tableName() . '  repayment')
                ->leftJoin(UserRegisterInfo::tableName() . '  reg','reg.user_id = repayment.user_id')
                ->where(['>=','repayment.loan_time',$leftTime])
                ->andWhere(['<','repayment.loan_time',$leftTime+86400])
                ->groupBy(['reg.appMarket'])
                ->asArray()
                ->all(\Yii::$app->get('db_read_1'));
            foreach ($res as $val){
                $arr[$val['appMarket']][UserOperationData::TYPE_LOAN_ORDER_SUCCESS] = $val['count'];
            }

            //新客放款成功数
            $res = UserLoanOrderRepayment::find()
                ->select('reg.appMarket,count(repayment.id) as count')
                ->from(UserLoanOrderRepayment::tableName() . '  repayment')
                ->leftJoin(UserRegisterInfo::tableName() . '  reg','reg.user_id = repayment.user_id')
                ->leftJoin(UserLoanOrder::tableName() . '  order','order.id = repayment.order_id')
                ->where(['order.is_first' => UserLoanOrder::FIRST_LOAN_IS])
                ->andWhere(['>=','repayment.loan_time',$leftTime])
                ->andWhere(['<','repayment.loan_time',$leftTime+86400])
                ->groupBy(['reg.appMarket'])
                ->asArray()
                ->all(\Yii::$app->get('db_read_1'));
            foreach ($res as $val){
                $arr[$val['appMarket']][UserOperationData::TYPE_NEW_LOAN_ORDER_SUCCESS] = $val['count'];
            }

            //入表
            foreach ($arr as $appMarket => $value){
                foreach ($value as $type => $val){
                    $userOperationData = UserOperationData::find()->where(['date' => $today,'app_market' => $appMarket,'type' => $type])->one();
                    if($userOperationData == null){
                        $userOperationData = new UserOperationData();
                        $userOperationData->date = $today;
                        $userOperationData->app_market = $appMarket;
                        $userOperationData->type = $type;;
                    }
                    $userOperationData->num = $val;;
                    $userOperationData->save();
                }
            }

            $leftTime += 86400;
        }
        echo 'end'.PHP_EOL;
    }


    /**
     * @name 提醒员和提醒组的统计 - Remind Data
     * @throws \yii\base\InvalidConfigException
     */
    public function actionRemindDayDataBuild(){
        if(!$this->lock()){
            return ExitCode::UNSPECIFIED_ERROR;
        }
        $db_read = \Yii::$app->get('db_read_1');
        $todayTime = strtotime("today");
        if(date('H',time()) == 0){ //更新前一天的
            $todayTime -= 86400;
        }

        $today = date('Y-m-d',$todayTime);
        echo 'start:'.$today.PHP_EOL;
        $start_id = 0;
        $query = RemindOrder::find()
            ->select(['id','customer_user_id','customer_group'])
            ->where(['dispatch_status' => [RemindOrder::STATUS_FINISH_DISPATCH,RemindOrder::STATUS_IS_OVERDUE,RemindOrder::STATUS_REPAY_COMPLETE]])
            ->andWhere(['>=','dispatch_time',$todayTime])
            ->andWhere(['<','dispatch_time',$todayTime+86400])
            ->andWhere(['>','customer_user_id',0]);
        $allIds = $query->andWhere(['>', 'id', $start_id])
            ->orderBy(['id' => SORT_ASC])->asArray()
            ->limit(1000)->all($db_read);

        $data = [];
        $customerUserIds = [];
        $merchantIds = [];
        while ($allIds){
            foreach ($allIds as $val){
                $id = $val['id'];
                $uid = $val['customer_user_id'];
                $group = $val['customer_group'];
                $customerUserIds[$uid] = $uid;
                //进入派单数
                if(isset($data[$uid][$group]['today_dispatch_num'])){
                    $data[$uid][$group]['today_dispatch_num'] += 1;
                }else{
                    $data[$uid][$group]['today_dispatch_num'] = 1;
                }
                $remindLog = RemindLog::find()
                    ->where(['remind_id' => $id,'operator_user_id' => $uid])
                    ->andWhere(['>','created_at',$todayTime])
                    ->andWhere(['<','created_at',$todayTime+86400])
                    ->one();
                //派单且提醒过的
                if($remindLog){
                    if(isset($data[$uid][$group]['today_dispatch_remind_num'])){
                        $data[$uid][$group]['today_dispatch_remind_num'] += 1;
                    }else{
                        $data[$uid][$group]['today_dispatch_remind_num'] = 1;
                    }
                }
            }
            $start_id = $id;
            $allIds = $query->andWhere(['>', 'id', $start_id])
                ->orderBy(['id' => SORT_ASC])->asArray()
                ->limit(1000)->all($db_read);
        }

        $todayRepayData = RemindOrder::find()
            ->select(['A.customer_user_id','A.customer_group','COUNT(A.id) as total_num'])
            ->from(RemindOrder::tableName() . 'A')
            ->leftJoin(UserLoanOrderRepayment::tableName() . 'B','A.repayment_id = B.id')
            ->where([
                'A.dispatch_status' => RemindOrder::STATUS_REPAY_COMPLETE
            ])
            ->andWhere(['>=','B.closing_time',$todayTime])
            ->andWhere(['<','B.closing_time',$todayTime+86400])
            ->groupBy(['A.customer_user_id','A.customer_group'])
            ->asArray()
            ->all($db_read);
        foreach ($todayRepayData as $item){
            $customerUserIds[$item['customer_user_id']] = $item['customer_user_id'];
            $data[$item['customer_user_id']][$item['customer_group']]['today_repay_num'] = $item['total_num'];
        }

        if($customerUserIds){
            $merchantIds = array_column(AdminUser::find()->select(['id','merchant_id'])->where(['id' => $customerUserIds])->asArray()->all($db_read),'merchant_id','id');
        }
        //入表
        foreach ($data as $customerUserId => $value){
            foreach ($value as $customerGroup => $val){
                $remindDayData = RemindDayData::find()->where(['date' => $today,'admin_user_id' => $customerUserId,'remind_group' => $customerGroup])->one();
                if($remindDayData == null){
                    $remindDayData = new RemindDayData();
                    $remindDayData->date = $today;
                    $remindDayData->admin_user_id = $customerUserId;
                    $remindDayData->remind_group = $customerGroup;
                    $remindDayData->merchant_id = $merchantIds[$customerUserId] ?? 0;
                }
                foreach ($val as $key => $v){
                    $remindDayData->$key = $v;;
                }
                $remindDayData->save();
            }
        }
        echo 'END';
    }

    /**
     * @name 提醒触达还款的统计（区分新老户）- Remind Reach
     * @throws \yii\base\InvalidConfigException
     */
    public function actionRemindReachRepayDataBuild(){
        if(!$this->lock()){
            return ExitCode::UNSPECIFIED_ERROR;
        }
        //新增提醒数据表：日期、新客提醒单数、新客触达单数、新户触达率、新户还款单数、新客提醒还款率、老客提醒单数、老客触达单数、老客触达率、老客提醒还款率
        $db_read = \Yii::$app->get('db_read_1');
        $todayTime = strtotime("today");
        if(date('H',time()) == 0){ //更新前一天的
            $todayTime -= 86400;
        }

        $today = date('Y-m-d',$todayTime);
        echo 'start:'.$today.PHP_EOL;
        $limit = 1000;
        $offset = 0;
        $query = RemindLog::find()
            ->select(['max_remind_return' => 'MAX(A.remind_return)','B.dispatch_status','D.is_first','E.merchant_id'])
            ->from(RemindLog::tableName() . 'A')
            ->leftJoin(RemindOrder::tableName(). 'B','A.remind_id = B.id')
            ->leftJoin(UserLoanOrderRepayment::tableName(). 'C','B.repayment_id = C.id')
            ->leftJoin(UserLoanOrder::tableName(). 'D','C.order_id = D.id')
            ->leftJoin(AdminUser::tableName(). 'E','A.customer_user_id = E.id')
            ->where(['>=','A.created_at',$todayTime])
            ->andWhere(['<','A.created_at',$todayTime+86400]);
        $allRemind = $query
            ->groupBy(['A.remind_id'])
            ->orderBy(['A.id' => SORT_ASC])
            ->offset($offset)
            ->limit($limit)
            ->asArray()
            ->all($db_read);

        $data = [];

        while ($allRemind){
            foreach ($allRemind as $value){
                if(!isset($data[$value['merchant_id']])){ //初始化
                    $data[$value['merchant_id']][RemindReachRepay::USER_TYPE_ALL] = ['remind_num' => 0,'reach_num' => 0, 'repay_num' => 0];
                    $data[$value['merchant_id']][RemindReachRepay::USER_TYPE_NEW] = ['remind_num' => 0,'reach_num' => 0, 'repay_num' => 0];
                    $data[$value['merchant_id']][RemindReachRepay::USER_TYPE_OLD] = ['remind_num' => 0,'reach_num' => 0, 'repay_num' => 0];
                }
                //提醒数
                $data[$value['merchant_id']][RemindReachRepay::USER_TYPE_ALL]['remind_num'] += 1;
                if($value['is_first'] == UserLoanOrder::FIRST_LOAN_IS){
                    $data[$value['merchant_id']][RemindReachRepay::USER_TYPE_NEW]['remind_num'] += 1;
                }else{
                    $data[$value['merchant_id']][RemindReachRepay::USER_TYPE_OLD]['remind_num'] += 1;
                }
                //触达数
                if($value['max_remind_return'] > 0){
                    $data[$value['merchant_id']][RemindReachRepay::USER_TYPE_ALL]['reach_num'] += 1;
                    if($value['is_first'] == UserLoanOrder::FIRST_LOAN_IS){
                        $data[$value['merchant_id']][RemindReachRepay::USER_TYPE_NEW]['reach_num'] += 1;
                    }else{
                        $data[$value['merchant_id']][RemindReachRepay::USER_TYPE_OLD]['reach_num'] += 1;
                    }
                }

                //还款数
                if($value['dispatch_status'] == RemindOrder::STATUS_REPAY_COMPLETE){
                    $data[$value['merchant_id']][RemindReachRepay::USER_TYPE_ALL]['repay_num'] += 1;
                    if($value['is_first'] == UserLoanOrder::FIRST_LOAN_IS){
                        $data[$value['merchant_id']][RemindReachRepay::USER_TYPE_NEW]['repay_num'] += 1;
                    }else{
                        $data[$value['merchant_id']][RemindReachRepay::USER_TYPE_OLD]['repay_num'] += 1;
                    }
                }
            }
            $offset = $offset + $limit;
            $allRemind = $query
                ->groupBy(['A.remind_id'])
                ->orderBy(['A.id' => SORT_ASC])
                ->offset($offset)
                ->limit($limit)
                ->asArray()
                ->all($db_read);
        }
        //入表
        foreach ($data as $merchantId => $userTypeValue){
            foreach ($userTypeValue as $userType => $value){
                $remindDayData = RemindReachRepay::find()->where(['date' => $today,'merchant_id' => $merchantId,'user_type' => $userType])->one();
                if($remindDayData == null){
                    $remindDayData = new RemindReachRepay();
                    $remindDayData->date = $today;
                    $remindDayData->merchant_id = $merchantId;
                    $remindDayData->user_type = $userType;
                }
                foreach ($value as $key => $v){
                    $remindDayData->$key = $v;;
                }
                $remindDayData->save();
            }
        }
        echo 'END';
    }


    /**
     * @name StatsOperateController 用户结构还款单数和金额数据
     */
    public function actionUserStructureExportRepaymentData($startTime='', $endTime=''){
        if(empty($startTime)){
            if(!$this->lock()){
                return ExitCode::UNSPECIFIED_ERROR;
            }
            $leftTime = strtotime("today");
            $leftTime -= 86400;
        }else{
            $leftTime = strtotime($startTime);
        }
        if(empty($endTime)){
            $rightTime = $leftTime + 86400 * 14;
        }else{
            $rightTime = strtotime($endTime) + 86400;
        }
        $this->printMessage('START');
        $service = new UserStructureRepaymentService();
        while($rightTime > $leftTime){
            $today = date('Y-m-d',$leftTime);
            $this->printMessage($today);
            $service->runUserStructureRepaymentByDate($leftTime);
            $leftTime += 86400;
        }
        $this->printMessage('END');
    }


    /**
     * @name StatsOperateController 用户推单来源还款单数和金额数据
     */
    public function actionUserStructureSourceExportRepaymentData($startTime='', $endTime=''){
        if(empty($startTime)){
            if(!$this->lock()){
                return ExitCode::UNSPECIFIED_ERROR;
            }
            $leftTime = strtotime("today");
            $leftTime -= 86400;
        }else{
            $leftTime = strtotime($startTime);
        }
        if(empty($endTime)){
            $rightTime = $leftTime + 86400 * 14;
        }else{
            $rightTime = strtotime($endTime) + 86400;
        }
        $this->printMessage('START');
        $service = new UserStructureRepaymentService();
        while($rightTime > $leftTime){
            $today = date('Y-m-d',$leftTime);
            $this->printMessage($today);
            $service->runUserStructureSourceRepaymentByDate($leftTime);
            $leftTime += 86400;
        }
        $this->printMessage('END');
    }

    /**
     * 用户结构订单转化
     * @param $startDate
     * @param $endDate
     * @throws \Exception
     */

    public  function actionUserStructureOrderTransformBuild($startDate = '', $endDate = ''){
        if(empty($startDate)){
            $leftTime = strtotime("today");
            if(date('H',time()) == 0){ //更新前一天的
                $leftTime -= 86400;
            }
        }else{
            $leftTime = strtotime($startDate);
        }
        if(empty($endDate)){
            $rightTime = $leftTime + 86400;
        }else{
            $rightTime = strtotime($endDate) + 86400;
        }
        echo 'start'.PHP_EOL;
        while($rightTime > $leftTime){
            $today = date('Y-m-d',$leftTime);
            echo $today.PHP_EOL;
            $arr = UserStructureOrderTransformService::getUserStructureOrderTransform($leftTime,$leftTime+86400);

            foreach ($arr as $userType => $merchantData){
                foreach ($merchantData as $merchantId => $packageData){
                    foreach ($packageData as $packageName => $val){
                        $userStructureOrderTransform = UserStructureOrderTransform::find()->where(
                            ['date' => $today,'merchant_id' => $merchantId,'package_name' => $packageName,'user_type' => $userType]
                        )->one();
                        if($userStructureOrderTransform == null){
                            $userStructureOrderTransform = new UserStructureOrderTransform();
                            $userStructureOrderTransform->date = $today;
                            $userStructureOrderTransform->merchant_id = $merchantId;
                            $userStructureOrderTransform->package_name = $packageName;
                            $userStructureOrderTransform->user_type = $userType;
                        }
                        foreach ($val as $k => $v){
                            $userStructureOrderTransform->{$k} = $v;
                        }
                        $userStructureOrderTransform->save();
                    }
                }
            }
            $leftTime += 86400;
        }
        echo 'END'.PHP_EOL;
    }

    public function actionDailyRepayGrandRun(){
        $leftTime = strtotime("today");
        if(date('H',time()) == 0 && date('i',time()) == 0){ //更新前一天的
            $leftTime -= 86400;
        }
        $rightTime = $leftTime + 86400;
        echo 'start'.PHP_EOL;
        while($rightTime > $leftTime){
            $today = date('Y-m-d',$leftTime);
            echo $today.PHP_EOL;
            $data = [];

            $allRepay = UserRepaymentLog::find()
                ->select([
                    'money' => 'SUM(A.amount)',
                    'num' => 'COUNT(DISTINCT(A.order_id))',
                    'A.merchant_id',
                    'packageName' => "IF(B.is_export = 1,substring_index(substring_index(C.app_market,'_',2),'_',-1),C.package_name)",
                    'D.overdue_day'
                ])
                ->from(UserRepaymentLog::tableName().' A')
                ->leftJoin(UserLoanOrder::tableName().' B','A.order_id = B.id')
                ->leftJoin(ClientInfoLog::tableName(). ' C','A.order_id = C.event_id AND C.event = '.ClientInfoLog::EVENT_APPLY_ORDER)
                ->leftJoin(UserLoanOrderRepayment::tableName().' D','A.order_id = D.order_id')
                ->where(['>=','A.success_time',$leftTime])
                ->andWhere(['<','A.success_time',$leftTime + 86400])
                ->groupBy(['packageName','A.merchant_id','D.overdue_day'])
                ->asArray()
                ->all();
            foreach ($allRepay as $item){
                $data[$item['merchant_id']][$item['packageName']][$item['overdue_day']]['all_repay_amount'] = $item['money'];
                $data[$item['merchant_id']][$item['packageName']][$item['overdue_day']]['all_repay_order_num'] = $item['num'];
            }

            $delayRepay = UserLoanOrderDelayPaymentLog::find()
                ->select([
                    'money' => 'SUM(A.amount)',
                    'num' => 'COUNT(DISTINCT(A.order_id))',
                    'B.merchant_id',
                    'packageName' => "IF(B.is_export = 1,substring_index(substring_index(C.app_market,'_',2),'_',-1),C.package_name)",
                    'D.overdue_day'
                ])
                ->from(UserLoanOrderDelayPaymentLog::tableName().' A')
                ->leftJoin(UserLoanOrder::tableName().' B','A.order_id = B.id')
                ->leftJoin(ClientInfoLog::tableName(). ' C','A.order_id = C.event_id AND C.event = '.ClientInfoLog::EVENT_APPLY_ORDER)
                ->leftJoin(UserLoanOrderRepayment::tableName().' D','A.order_id = D.order_id')
                ->where(['>=','A.created_at',$leftTime])
                ->andWhere(['<','A.created_at',$leftTime + 86400])
                ->groupBy(['packageName','B.merchant_id','D.overdue_day'])
                ->asArray()
                ->all();
            foreach ($delayRepay as $item){
                $data[$item['merchant_id']][$item['packageName']][$item['overdue_day']]['delay_repay_amount'] = $item['money'];
                $data[$item['merchant_id']][$item['packageName']][$item['overdue_day']]['delay_repay_order_num'] = $item['num'];
            }

            $extendRepay = UserLoanOrderExtendLog::find()
                ->select([
                    'money' => 'SUM(A.amount)',
                    'num' => 'COUNT(DISTINCT(A.order_id))',
                    'B.merchant_id',
                    'packageName' => "IF(B.is_export = 1,substring_index(substring_index(C.app_market,'_',2),'_',-1),C.package_name)",
                    'D.overdue_day'
                ])
                ->from(UserLoanOrderExtendLog::tableName().' A')
                ->leftJoin(UserLoanOrder::tableName().' B','A.order_id = B.id')
                ->leftJoin(ClientInfoLog::tableName(). ' C','A.order_id = C.event_id AND C.event = '.ClientInfoLog::EVENT_APPLY_ORDER)
                ->leftJoin(UserLoanOrderRepayment::tableName().' D','A.order_id = D.order_id')
                ->where(['>=','A.created_at',$leftTime])
                ->andWhere(['<','A.created_at',$leftTime + 86400])
                ->groupBy(['packageName','B.merchant_id','D.overdue_day'])
                ->asArray()
                ->all();
            foreach ($extendRepay as $item){
                $data[$item['merchant_id']][$item['packageName']][$item['overdue_day']]['extend_amount'] = $item['money'];
                $data[$item['merchant_id']][$item['packageName']][$item['overdue_day']]['extend_order_num'] = $item['num'];
            }

            foreach ($data as $merchantId => $mData){
                foreach ($mData as $packageName => $sData){
                    foreach ($sData as $overdueDay => $oData){
                        $dailyRepaymentGrandTotal = DailyRepaymentGrandTotal::find()->where(['date' => $today,'merchant_id' => $merchantId,'package_name' => $packageName,'overdue_day' => $overdueDay])->one();
                        if(is_null($dailyRepaymentGrandTotal)){
                            $dailyRepaymentGrandTotal = new DailyRepaymentGrandTotal();
                            $dailyRepaymentGrandTotal->date = $today;
                            $dailyRepaymentGrandTotal->merchant_id = $merchantId;
                            $dailyRepaymentGrandTotal->package_name = $packageName;
                            $dailyRepaymentGrandTotal->overdue_day = $overdueDay;
                        }
                        foreach ($oData as $k => $v){
                            $dailyRepaymentGrandTotal->$k = $v;
                        }
                        $dailyRepaymentGrandTotal->save();
                    }
                }
            }


            $leftTime = $leftTime + 86400;
        }
        echo 'end'.PHP_EOL;
    }

    public function actionDailyRegisterConver($start='', $end=''){
        if(!$this->lock()){
            return;
        }
        $this->printMessage('脚本开始');
        if(empty($start)){
            $beginTime = strtotime('today') - 86400 * 60;
        }else{
            $beginTime = strtotime($start);
        }

        $endTime = empty($end) ? strtotime('today') : strtotime($end);

        while ($beginTime <= $endTime){
            $date = date('Y-m-d', $beginTime);
            echo $date.PHP_EOL;
            $data[0] = $this->_getDailyRegisterConver($beginTime);//累计
            $data[1] = $this->_getDailyRegisterConver($beginTime, 1);//当天
            $data[2] = $this->_getDailyRegisterConver($beginTime, 2);//3天内
            $data[3] = $this->_getDailyRegisterConver($beginTime, 3);//7天内
            $data[4] = $this->_getDailyRegisterConver($beginTime, 4);//10天内
            $data[5] = $this->_getDailyRegisterConver($beginTime, 5);//30天内

            foreach ($data as $type => $sourceIdData){
                foreach ($sourceIdData as $source_id => $appMarketData){
                    foreach ($appMarketData as $appMarket => $mediaSourceData){
                        foreach ($mediaSourceData as $media_source => $value){
                            $model = DailyRegisterConver::find()
                                ->where(['date' => $date,
                                         'type' => $type,
                                         'source_id' => $source_id,
                                         'app_market' => $appMarket,
                                         'media_source' => $media_source])
                                ->one();
                            if(empty($model)){
                                $model = new DailyRegisterConver();
                                $model->date = $date;
                                $model->type = $type;
                                $model->source_id = $source_id;
                                $model->app_market = $appMarket;
                                $model->media_source = $media_source;
                            }

                            foreach ($value as $k => $v){
                                $model->{$k} = $v;
                            }
                            $model->save();
                        }
                    }
                }
            }
            $beginTime += 86400;
        }
        $this->printMessage('脚本结束');
    }

    private function _getDailyRegisterConver($beginTime, $type=0){
        if(!empty($type)){
            $endTime = $beginTime + 86400 * $type;
        }else{
            $endTime = strtotime('today') + 86400;
        }
        $arr = [];
        //注册
        $res = LoanPerson::find()
            ->select(['p.source_id','i.appMarket','i.media_source', 'p.id'])
            ->from(LoanPerson::tableName() . '  p')
            ->leftJoin(UserRegisterInfo::tableName() . '  i','i.user_id = p.id')
            ->where(['>=','p.created_at',$beginTime])
            ->andWhere(['<','p.created_at',$beginTime + 86400])
            ->groupBy(['p.id'])
            ->indexBy('id')
            ->asArray()
            ->all(\Yii::$app->get('db_read_1'));
        $userIds = [];
        foreach ($res as $v){
            if(stripos($v['appMarket'], 'external') !== false){
                continue;
            }

            $userIds[] = $v['id'];

            if(!isset($arr[$v['source_id']][$v['appMarket']][$v['media_source']]['reg_num'])){
                $arr[$v['source_id']][$v['appMarket']][$v['media_source']]['reg_num'] = 0;
            }
            $arr[$v['source_id']][$v['appMarket']][$v['media_source']]['reg_num']++;
        }

        //认证
        $result = UserVerificationLog::find()
            ->where(['user_id' => $userIds,
                     'status' => UserVerificationLog::STATUS_VERIFY_SUCCESS,
                     'type' => [UserVerification::TYPE_BASIC,
                                UserVerification::TYPE_FR_COMPARE_PAN,
                                UserVerification::TYPE_CONTACT,
                                UserVerification::TYPE_OCR_AADHAAR]])
            ->andWhere(['>=', 'created_at', $beginTime])
            ->andWhere(['<', 'created_at', $endTime])
            ->groupBy(['user_id', 'type'])
            ->asArray()
            ->all(\Yii::$app->get('db_read_1'));

        foreach ($result as $v){
            $source_id = $res[$v['user_id']]['source_id'];
            $appMarket = $res[$v['user_id']]['appMarket'];
            $media_source = $res[$v['user_id']]['media_source'];
            switch ($v['type']){
                case UserVerification::TYPE_BASIC:
                    $key = 'basic_num';
                    break;
                case UserVerification::TYPE_CONTACT:
                    $key = 'contact_num';
                    break;
                case UserVerification::TYPE_OCR_AADHAAR:
                    $key = 'address_num';
                    break;
                case UserVerification::TYPE_FR_COMPARE_PAN:
                    $key = 'kyc_num';
                    break;
            }

            if(!isset($arr[$source_id][$appMarket][$media_source][$key])){
                $arr[$source_id][$appMarket][$media_source][$key] = 0;
            }
            $arr[$source_id][$appMarket][$media_source][$key]++;
        }

        //申请
        $result = UserLoanOrder::find()
            ->select(['user_id'])
            ->where(['user_id' => $userIds, 'is_export' => UserLoanOrder::IS_EXPORT_NO])
            ->andWhere(['>=', 'order_time', $beginTime])
            ->andWhere(['<', 'order_time', $endTime])
            ->groupBy(['user_id'])
            ->asArray()
            ->column(\Yii::$app->get('db_read_1'));
        $this->_getDailyRegisterConverData($arr, $res, $result, 'apply_num');

        //过件
        $result = UserOrderLoanCheckLog::find()
            ->alias('l')
            ->leftJoin(UserLoanOrder::tableName(). ' as o', 'o.id=l.order_id')
            ->select(['l.user_id'])
            ->where(['l.user_id' => $userIds,
                     'l.before_status' => UserLoanOrder::STATUS_CHECK,
                     'o.is_export' => UserLoanOrder::IS_EXPORT_NO,
                     'l.after_status' => [
                         UserLoanOrder::STATUS_LOANING,
                         UserLoanOrder::STATUS_WAIT_DEPOSIT,
                         UserLoanOrder::STATUS_WAIT_DRAW_MONEY
                     ]])
            ->andWhere(['>=', 'l.created_at', $beginTime])
            ->andWhere(['<', 'l.created_at', $endTime])
            ->groupBy(['l.user_id'])
            ->asArray()
            ->column(\Yii::$app->get('db_read_1'));
        $this->_getDailyRegisterConverData($arr, $res, $result, 'audit_pass_num');

        //提现
        $result = UserOrderLoanCheckLog::find()
            ->alias('l')
            ->leftJoin(UserLoanOrder::tableName(). ' as o', 'o.id=l.order_id')
            ->select(['l.user_id'])
            ->where(['l.user_id' => $userIds,
                     'o.is_export' => UserLoanOrder::IS_EXPORT_NO,
                     'l.before_status' => [
                         UserLoanOrder::STATUS_CHECK,
                         UserLoanOrder::STATUS_WAIT_DEPOSIT,
                         UserLoanOrder::STATUS_WAIT_DRAW_MONEY],
                     'l.after_status' => UserLoanOrder::STATUS_LOANING])
            ->andWhere(['>=', 'l.created_at', $beginTime])
            ->andWhere(['<', 'l.created_at', $endTime])
            ->groupBy(['l.user_id'])
            ->asArray()
            ->column(\Yii::$app->get('db_read_1'));
        $this->_getDailyRegisterConverData($arr, $res, $result, 'withdraw_num');

        //放款
        $result = UserLoanOrder::find()
            ->select(['user_id'])
            ->where(['user_id' => $userIds, 'is_export' => UserLoanOrder::IS_EXPORT_NO])
            ->andWhere(['>=', 'loan_time', $beginTime])
            ->andWhere(['<', 'loan_time', $endTime])
            ->groupBy(['user_id'])
            ->asArray()
            ->column(\Yii::$app->get('db_read_1'));

        $this->_getDailyRegisterConverData($arr, $res, $result, 'loan_num');
        return $arr;
    }

    private function _getDailyRegisterConverData(&$arr, $res, $result, $key){
        foreach ($result as $v){
            $source_id = $res[$v]['source_id'];
            $appMarket = $res[$v]['appMarket'];
            $media_source = $res[$v]['media_source'];

            if(!isset($arr[$source_id][$appMarket][$media_source][$key])){
                $arr[$source_id][$appMarket][$media_source][$key] = 0;
            }
            $arr[$source_id][$appMarket][$media_source][$key]++;
        }
    }
}



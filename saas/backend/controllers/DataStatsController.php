<?php

namespace backend\controllers;

use backend\models\AdminUser;
use common\models\fund\LoanFund;
use common\models\order\UserLoanOrder;
use common\models\order\UserLoanOrderRepayment;
use common\models\package\PackageSetting;
use common\models\risk\MgRiskTreeResult;
use common\models\risk\RiskResultSnapshot;
use common\models\stats\DailyCreditAuditData;
use common\models\stats\DailyRepaymentGrandTotal;
use common\models\stats\DailyRiskRejectData;
use common\models\stats\DailyTradeData;
use common\models\stats\DailyUserData;
use common\models\stats\DailyUserFullData;
use common\models\stats\ReApplyData;
use common\models\stats\StatisticsDayData;
use common\models\stats\StatisticsLoan2FullPlatform;
use common\models\stats\StatisticsLoanCopy;
use common\models\stats\StatisticsLoanCopy2;
use common\models\stats\StatisticsLoanFullPlatform;
use common\models\stats\TotalRepaymentAmountData;
use common\models\stats\UserOperationData;
use common\models\stats\UserStructureExportRepaymentData;
use common\models\stats\UserStructureSourceExportRepaymentData;
use common\models\user\UserRegisterInfo;
use common\services\stats\DayDataStatisticsService;
use common\services\stats\DayOrderDataStatisticsService;
use common\services\stats\ReApplyDataService;
use common\services\stats\TotalRepaymentAmountService;
use common\services\stats\UserStructureRepaymentService;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;
use yii;

class DataStatsController extends BaseController
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
     * @name 用户数据-用户日报表(注册)
     * @return string
     */
    public function actionDailyUserData(){
        $add_start = $this->request->get('add_start',date('Y-m-d',strtotime('-7 day')));
        $add_end = $this->request->get('add_end',date('Y-m-d'));
        $condition[] = 'and';
        $count = 9999999;
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
        $merchantIds = $this->request->get('merchant_id',[]);
        $pages = new Pagination(['totalCount' => $count]);
        $pages->pageSize = yii::$app->request->get('per-page', 15);

        $query = DailyUserData::find()->where($condition)->andWhere(['merchant_id' => $this->merchantIds]);
        if($this->isNotMerchantAdmin && $merchantIds){
            $query->andWhere(['merchant_id' => $merchantIds]);
        }
        $totalQuery = clone $query;
        $totalData =  $totalQuery->select(
            [
                'sum(reg_num) as reg_num',
                'sum(basic_num) as basic_num',
                'sum(identity_num) as identity_num',
                'sum(contact_num) as contact_num',
                'sum(order_num) as order_num',
                'sum(order_amount) as order_amount',
                'sum(audit_pass_order_num) as audit_pass_order_num',
                'sum(audit_pass_order_amount) as audit_pass_order_amount',
                'sum(bind_card_pass_order_num) as bind_card_pass_order_num',
                'sum(bind_card_pass_order_amount) as bind_card_pass_order_amount',
                'sum(loan_success_order_num) as loan_success_order_num',
                'sum(loan_success_order_amount) as loan_success_order_amount',
            ])->asArray()->all($this->getStatsDb());
        $totalData[0]['date'] = '总汇总';
        $totalData[0]['Type'] = 1; //汇总
        $dateData =  $totalQuery->select(
            [
                'date',
                'sum(reg_num) as reg_num',
                'sum(basic_num) as basic_num',
                'sum(identity_num) as identity_num',
                'sum(contact_num) as contact_num',
                'sum(order_num) as order_num',
                'sum(order_amount) as order_amount',
                'sum(audit_pass_order_num) as audit_pass_order_num',
                'sum(audit_pass_order_amount) as audit_pass_order_amount',
                'sum(bind_card_pass_order_num) as bind_card_pass_order_num',
                'sum(bind_card_pass_order_amount) as bind_card_pass_order_amount',
                'sum(loan_success_order_num) as loan_success_order_num',
                'sum(loan_success_order_amount) as loan_success_order_amount',
            ])->groupBy(['date'])->orderBy(['date' => SORT_DESC])->asArray()->all($this->getStatsDb());
        foreach ($dateData as &$val){
            $val['Type'] = 2;
        }
        $totalData = array_merge($totalData,$dateData);
        if ($this->request->get('submitcsv') == 'export_direct') {
            $data = $query->orderBy(['id' => SORT_DESC])->asArray()->all($this->getStatsDb());
        }else{
            $data = $query->orderBy(['id' => SORT_DESC])->offset($pages->offset)->limit($pages->limit)->asArray()->all($this->getStatsDb());
        }

        if ($this->request->get('submitcsv') == 'export_direct') {
//            var_dump($data);exit;
            $totalData = array_merge($totalData,$data);
            return $this->_exportDailyUserData($totalData);
        }

        $views = 'daily-user-data';

        $searchList = UserRegisterInfo::getChannelSearchList();

        return $this->render($views, [
            'totalData' => $totalData,
            'data' => $data,
            'count' => $count,
            'searchList' => $searchList,
            'pages' => $pages,
            'add_start' => $add_start,
            'add_end' => $add_end,
            'isNotMerchantAdmin' => $this->isNotMerchantAdmin
        ]);
    }

    /**
     * 每日用户统计导出
     */
    private function _exportDailyUserData($arr){
        \ini_set('memory_limit', "512m");
        $this->_setcsvHeader('daily_user_data_export'.date('YmdHis').'.csv');
        echo "日期,app_market,注册数,基本认证数,身份认证数,联系人认证数,申请订单单数,申请订单金额,审核通过订单单数,审核通过订单金额,绑卡通过订单单数,绑卡通过订单金额,放款订单单数,放款订单金额,更新时间\n";
        foreach($arr as $key => $value){
            $str = '';
            $str .= $value['date'].',';
            $str .= (isset($value['app_market'])?$value['app_market']:'-').',';
            $str .= $value['reg_num'].',';
            $str .= $value['basic_num'].',';
            $str .= $value['identity_num'].',';
            $str .= $value['contact_num'].',';
            $str .= $value['order_num'].',';
            $str .= ($value['order_amount'] / 100).',';
            $str .= $value['audit_pass_order_num'].',';
            $str .= ($value['audit_pass_order_amount'] / 100).',';
            $str .= $value['bind_card_pass_order_num'].',';
            $str .= ($value['bind_card_pass_order_amount'] / 100).',';
            $str .= $value['loan_success_order_num'].',';
            $str .= ($value['loan_success_order_amount'] / 100).',';
            $str .= (isset($value['updated_at'])?date("Y-m-d H:i:s" , $value['updated_at']):'-').',';
            $str .= "\n";
            echo $str;
        }die;
    }


    /**
     * @name 用户数据-用户日报表(全量)
     * @return string
     */
    public function actionDailyUserFullData(){
        $isShow = false;
        if(in_array(Yii::$app->user->identity->getId(),[3,7])){
            $isShow = true;
        }
        $add_start = $this->request->get('add_start',date('Y-m-d',strtotime('-7 day')));
        $add_end = $this->request->get('add_end',date('Y-m-d'));
        $condition[] = 'and';
        $count = 9999999;
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
        $packageName = $this->request->get('package_name',[]);
        if(!empty($packageName)){
            $condition[] = ['package_name' => $packageName];
        }
        $merchantIds = $this->request->get('merchant_id',[]);
        $pages = new Pagination(['totalCount' => $count]);
        $pages->pageSize = yii::$app->request->get('per-page', 15);

        $query = DailyUserFullData::find()->where($condition)->andWhere(['merchant_id' => $this->merchantIds]);
        if($this->isNotMerchantAdmin && $merchantIds){
            $query->andWhere(['merchant_id' => $merchantIds]);
        }
        $totalQuery = clone $query;
        $totalData = $totalQuery->select(
            [
                'sum(reg_num) as reg_num',
                'sum(basic_num) as basic_num',
                'sum(identity_num) as identity_num',
                'sum(contact_num) as contact_num',
                'sum(order_num) as order_num',
                'sum(new_order_num) as new_order_num',
                'sum(new_order_user_num) as new_order_user_num',
                'sum(old_order_num) as old_order_num',
                'sum(order_amount) as order_amount',
                'sum(new_order_amount) as new_order_amount',
                'sum(old_order_amount) as old_order_amount',
                'sum(audit_pass_order_num) as audit_pass_order_num',
                'sum(audit_pass_order_user_num) as audit_pass_order_user_num',
                'sum(new_audit_pass_order_num) as new_audit_pass_order_num',
                'sum(new_audit_pass_order_user_num) as new_audit_pass_order_user_num',
                'sum(old_audit_pass_order_num) as old_audit_pass_order_num',
                'sum(audit_pass_order_amount) as audit_pass_order_amount',
                'sum(new_audit_pass_order_amount) as new_audit_pass_order_amount',
                'sum(old_audit_pass_order_amount) as old_audit_pass_order_amount',
                'sum(bind_card_pass_order_num) as bind_card_pass_order_num',
                'sum(new_bind_card_pass_order_num) as new_bind_card_pass_order_num',
                'sum(new_bind_card_pass_order_user_num) as new_bind_card_pass_order_user_num',
                'sum(old_bind_card_pass_order_num) as old_bind_card_pass_order_num',
                'sum(bind_card_pass_order_amount) as bind_card_pass_order_amount',
                'sum(new_bind_card_pass_order_amount) as new_bind_card_pass_order_amount',
                'sum(old_bind_card_pass_order_amount) as old_bind_card_pass_order_amount',
                'sum(new_withdraw_success_order_user_num) as new_withdraw_success_order_user_num',
                'sum(withdraw_success_order_num) as withdraw_success_order_num',
                'sum(old_withdraw_success_order_num) as old_withdraw_success_order_num',
                'sum(new_withdraw_success_order_num) as new_withdraw_success_order_num',
                'sum(loan_success_order_num) as loan_success_order_num',
                'sum(new_loan_success_order_num) as new_loan_success_order_num',
                'sum(old_loan_success_order_num) as old_loan_success_order_num',
                'sum(loan_success_order_amount) as loan_success_order_amount',
                'sum(new_loan_success_order_amount) as new_loan_success_order_amount',
                'sum(old_loan_success_order_amount) as old_loan_success_order_amount'
            ])->asArray()->all($this->getStatsDb());
        $totalData[0]['date'] = '汇总';
        $totalData[0]['Type'] = 1; //汇总
        $dateData =  $totalQuery->select(
            [
                'date',
                'sum(reg_num) as reg_num',
                'sum(basic_num) as basic_num',
                'sum(identity_num) as identity_num',
                'sum(contact_num) as contact_num',
                'sum(order_num) as order_num',
                'sum(new_order_num) as new_order_num',
                'sum(new_order_user_num) as new_order_user_num',
                'sum(old_order_num) as old_order_num',
                'sum(order_amount) as order_amount',
                'sum(new_order_amount) as new_order_amount',
                'sum(old_order_amount) as old_order_amount',
                'sum(audit_pass_order_num) as audit_pass_order_num',
                'sum(audit_pass_order_user_num) as audit_pass_order_user_num',
                'sum(new_audit_pass_order_num) as new_audit_pass_order_num',
                'sum(new_audit_pass_order_user_num) as new_audit_pass_order_user_num',
                'sum(old_audit_pass_order_num) as old_audit_pass_order_num',
                'sum(audit_pass_order_amount) as audit_pass_order_amount',
                'sum(new_audit_pass_order_amount) as new_audit_pass_order_amount',
                'sum(old_audit_pass_order_amount) as old_audit_pass_order_amount',
                'sum(bind_card_pass_order_num) as bind_card_pass_order_num',
                'sum(new_bind_card_pass_order_num) as new_bind_card_pass_order_num',
                'sum(new_bind_card_pass_order_user_num) as new_bind_card_pass_order_user_num',
                'sum(old_bind_card_pass_order_num) as old_bind_card_pass_order_num',
                'sum(bind_card_pass_order_amount) as bind_card_pass_order_amount',
                'sum(new_bind_card_pass_order_amount) as new_bind_card_pass_order_amount',
                'sum(old_bind_card_pass_order_amount) as old_bind_card_pass_order_amount',
                'sum(new_withdraw_success_order_user_num) as new_withdraw_success_order_user_num',
                'sum(withdraw_success_order_num) as withdraw_success_order_num',
                'sum(old_withdraw_success_order_num) as old_withdraw_success_order_num',
                'sum(new_withdraw_success_order_num) as new_withdraw_success_order_num',
                'sum(loan_success_order_num) as loan_success_order_num',
                'sum(new_loan_success_order_num) as new_loan_success_order_num',
                'sum(old_loan_success_order_num) as old_loan_success_order_num',
                'sum(loan_success_order_amount) as loan_success_order_amount',
                'sum(new_loan_success_order_amount) as new_loan_success_order_amount',
                'sum(old_loan_success_order_amount) as old_loan_success_order_amount'
            ])->groupBy(['date'])->orderBy(['date' => SORT_DESC])->asArray()->all($this->getStatsDb());
        foreach ($dateData as &$val){
            $val['Type'] = 2;
        }
        $totalData = array_merge($totalData,$dateData);
        if ($this->request->get('submitcsv') == 'export_direct') {
            $data = $query->orderBy(['date' => SORT_DESC,'id' => SORT_DESC])->asArray()->all($this->getStatsDb());
        }else{
            $data = $query->offset($pages->offset)->limit($pages->limit)->orderBy(['date' => SORT_DESC,'id' => SORT_DESC])->asArray()->all($this->getStatsDb());
        }
        if ($this->request->get('submitcsv') == 'export_direct') {
//            var_dump($data);exit;
            $totalData = array_merge($totalData,$data);
            return $this->_exportDailyUserFullData($totalData);
        }
        $views = 'daily-user-full-data';

        $searchList = UserRegisterInfo::getChannelSearchList();
        $mediaSourceList = UserRegisterInfo::getMediaSourceSearchList();
        $packageNameList = ArrayHelper::getColumn(DailyUserFullData::find()->select(['package_name'])->distinct(['package_name'])->where(['merchant_id' => $this->merchantIds])->indexBy(['package_name'])->asArray()->all(),'package_name','package_name');
        return $this->render($views, [
            'totalData' => $totalData,
            'data' => $data,
            'count' => $count,
            'searchList' => $searchList,
            'mediaSourceList' => $mediaSourceList,
            'pages' => $pages,
            'add_start' => $add_start,
            'add_end' => $add_end,
            'isNotMerchantAdmin' => $this->isNotMerchantAdmin,
            'packageNameList' => $packageNameList,
            'isShow' => $isShow
        ]);
    }

    /**
     * @name 用户数据-用户日报表(全量-全平台)
     * @return string
     */
    public function actionDailyUserPlatformFullData(){
        $isShow = false;
        if(in_array(Yii::$app->user->identity->getId(),[3,7])){
            $isShow = true;
        }
        $add_start = $this->request->get('add_start',date('Y-m-d',strtotime('-7 day')));
        $add_end = $this->request->get('add_end',date('Y-m-d'));
        $condition[] = 'and';
        $count = 9999999;
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
        $packageName = $this->request->get('package_name',[]);
        if(!empty($packageName)){
            $condition[] = ['package_name' => $packageName];
        }
        $merchantIds = $this->request->get('merchant_id',[]);
        $pages = new Pagination(['totalCount' => $count]);
        $pages->pageSize = yii::$app->request->get('per-page', 15);

        $query = DailyUserFullData::find()->where($condition)->andWhere(['merchant_id' => $this->merchantIds]);
        if($this->isNotMerchantAdmin && $merchantIds){
            $query->andWhere(['merchant_id' => $merchantIds]);
        }
        $totalQuery = clone $query;
        $totalData = $totalQuery->select(
            [
                'sum(reg_num) as reg_num',
                'sum(basic_num) as basic_num',
                'sum(identity_num) as identity_num',
                'sum(contact_num) as contact_num',
                'sum(order_num) as order_num',
                'sum(platform_new_order_num) as platform_new_order_num',
                'sum(platform_new_order_user_num) as platform_new_order_user_num',
                'sum(platform_old_order_num) as platform_old_order_num',
                'sum(order_amount) as order_amount',
                'sum(platform_new_order_amount) as platform_new_order_amount',
                'sum(platform_old_order_amount) as platform_old_order_amount',
                'sum(audit_pass_order_num) as audit_pass_order_num',
                'sum(platform_new_audit_pass_order_num) as platform_new_audit_pass_order_num',
                'sum(platform_new_audit_pass_order_user_num) as platform_new_audit_pass_order_user_num',
                'sum(platform_old_audit_pass_order_num) as platform_old_audit_pass_order_num',
                'sum(audit_pass_order_amount) as audit_pass_order_amount',
                'sum(platform_new_audit_pass_order_amount) as platform_new_audit_pass_order_amount',
                'sum(platform_old_audit_pass_order_amount) as platform_old_audit_pass_order_amount',
                'sum(bind_card_pass_order_num) as bind_card_pass_order_num',
                'sum(platform_new_bind_card_pass_order_num) as platform_new_bind_card_pass_order_num',
                'sum(platform_new_bind_card_pass_order_user_num) as platform_new_bind_card_pass_order_user_num',
                'sum(platform_old_bind_card_pass_order_num) as platform_old_bind_card_pass_order_num',
                'sum(bind_card_pass_order_amount) as bind_card_pass_order_amount',
                'sum(platform_new_bind_card_pass_order_amount) as platform_new_bind_card_pass_order_amount',
                'sum(platform_old_bind_card_pass_order_amount) as platform_old_bind_card_pass_order_amount',
                'sum(platform_new_withdraw_success_order_user_num) as platform_new_withdraw_success_order_user_num',
                'sum(platform_old_withdraw_success_order_num) as platform_old_withdraw_success_order_num',
                'sum(loan_success_order_num) as loan_success_order_num',
                'sum(platform_new_loan_success_order_num) as platform_new_loan_success_order_num',
                'sum(platform_old_loan_success_order_num) as platform_old_loan_success_order_num',
                'sum(loan_success_order_amount) as loan_success_order_amount',
                'sum(platform_new_loan_success_order_amount) as platform_new_loan_success_order_amount',
                'sum(platform_old_loan_success_order_amount) as platform_old_loan_success_order_amount'
            ])->asArray()->all($this->getStatsDb());
        $totalData[0]['date'] = '汇总';
        $totalData[0]['Type'] = 1; //汇总
        $dateData =  $totalQuery->select(
            [
                'date',
                'sum(reg_num) as reg_num',
                'sum(basic_num) as basic_num',
                'sum(identity_num) as identity_num',
                'sum(contact_num) as contact_num',
                'sum(order_num) as order_num',
                'sum(platform_new_order_num) as platform_new_order_num',
                'sum(platform_new_order_user_num) as platform_new_order_user_num',
                'sum(platform_old_order_num) as platform_old_order_num',
                'sum(order_amount) as order_amount',
                'sum(platform_new_order_amount) as platform_new_order_amount',
                'sum(platform_old_order_amount) as platform_old_order_amount',
                'sum(audit_pass_order_num) as audit_pass_order_num',
                'sum(platform_new_audit_pass_order_num) as platform_new_audit_pass_order_num',
                'sum(platform_new_audit_pass_order_user_num) as platform_new_audit_pass_order_user_num',
                'sum(platform_old_audit_pass_order_num) as platform_old_audit_pass_order_num',
                'sum(audit_pass_order_amount) as audit_pass_order_amount',
                'sum(platform_new_audit_pass_order_amount) as platform_new_audit_pass_order_amount',
                'sum(platform_old_audit_pass_order_amount) as platform_old_audit_pass_order_amount',
                'sum(bind_card_pass_order_num) as bind_card_pass_order_num',
                'sum(platform_new_bind_card_pass_order_num) as platform_new_bind_card_pass_order_num',
                'sum(platform_new_bind_card_pass_order_user_num) as platform_new_bind_card_pass_order_user_num',
                'sum(platform_old_bind_card_pass_order_num) as platform_old_bind_card_pass_order_num',
                'sum(bind_card_pass_order_amount) as bind_card_pass_order_amount',
                'sum(platform_new_bind_card_pass_order_amount) as platform_new_bind_card_pass_order_amount',
                'sum(platform_old_bind_card_pass_order_amount) as platform_old_bind_card_pass_order_amount',
                'sum(platform_new_withdraw_success_order_user_num) as platform_new_withdraw_success_order_user_num',
                'sum(platform_old_withdraw_success_order_num) as platform_old_withdraw_success_order_num',
                'sum(loan_success_order_num) as loan_success_order_num',
                'sum(platform_new_loan_success_order_num) as platform_new_loan_success_order_num',
                'sum(platform_old_loan_success_order_num) as platform_old_loan_success_order_num',
                'sum(loan_success_order_amount) as loan_success_order_amount',
                'sum(platform_new_loan_success_order_amount) as platform_new_loan_success_order_amount',
                'sum(platform_old_loan_success_order_amount) as platform_old_loan_success_order_amount'
            ])->groupBy(['date'])->orderBy(['date' => SORT_DESC])->asArray()->all($this->getStatsDb());
        foreach ($dateData as &$val){
            $val['Type'] = 2;
        }
        $totalData = array_merge($totalData,$dateData);
        if ($this->request->get('submitcsv') == 'export_direct') {
            $data = $query->orderBy(['date' => SORT_DESC,'id' => SORT_DESC])->asArray()->all($this->getStatsDb());
        }else{
            $data = $query->offset($pages->offset)->limit($pages->limit)->orderBy(['date' => SORT_DESC,'id' => SORT_DESC])->asArray()->all($this->getStatsDb());
        }
        if ($this->request->get('submitcsv') == 'export_direct') {
//            var_dump($data);exit;
            $totalData = array_merge($totalData,$data);
            return $this->_exportDailyUserFullData($totalData);
        }
        $views = 'daily-user-platform-full-data';

        $searchList = UserRegisterInfo::getChannelSearchList();
        $mediaSourceList = UserRegisterInfo::getMediaSourceSearchList();
        $packageNameList = ArrayHelper::getColumn(DailyUserFullData::find()->select(['package_name'])->distinct(['package_name'])->where(['merchant_id' => $this->merchantIds])->indexBy(['package_name'])->asArray()->all(),'package_name','package_name');
        return $this->render($views, [
            'totalData' => $totalData,
            'data' => $data,
            'count' => $count,
            'searchList' => $searchList,
            'mediaSourceList' => $mediaSourceList,
            'pages' => $pages,
            'add_start' => $add_start,
            'add_end' => $add_end,
            'isNotMerchantAdmin' => $this->isNotMerchantAdmin,
            'isShow' => $isShow,
            'packageNameList' => $packageNameList
        ]);
    }

    /**
     * 每日用户统计导出
     */
    private function _exportDailyUserFullData($arr){
        \ini_set('memory_limit', "512m");
        $this->_setcsvHeader('daily_user_full_data_export'.date('YmdHis').'.csv');
        echo "日期,app_market,media_source,注册数,基本认证数,身份认证数,联系人认证数,申请订单单数,新客申请订单单数,新客申请订单人数,老客申请订单单数,申请订单金额,新客申请订单金额,老客申请订单金额,";
        echo "审核通过订单单数,审核通过订单人数,新客审核通过订单单数,新客审核通过订单人数,老客审核通过订单单数,审核通过订单金额,新客审核通过订单金额,老客审核通过订单金额,";
        echo "绑卡通过订单单数,新客绑卡通过订单单数,新客绑卡通过订单人数,老客绑卡通过订单单数,绑卡通过订单金额,新客绑卡通过订单金额,老客绑卡通过订单金额,";
        echo "放款订单单数,新客放款订单单数,老客放款订单单数,放款订单金额,新客放款订单金额,老客放款订单金额,更新时间\n";
        foreach($arr as $key => $value){
            $str = '';
            $str .= (isset($value['date']) ? $value['date'] : '-').',';
            $str .= (isset($value['app_market'])?$value['app_market']:'-').',';
            $str .= (isset($value['media_source'])?$value['media_source']:'-').',';
            $str .= $value['reg_num'].',';
            $str .= $value['basic_num'].',';
            $str .= (isset($value['identity_num']) ? $value['identity_num'] : 0).',';
            $str .= $value['contact_num'].',';
            $str .= $value['order_num'].',';
            $str .= $value['new_order_num'].',';
            $str .= $value['new_order_user_num'].',';
            $str .= $value['old_order_num'].',';
            $str .= ($value['order_amount'] / 100).',';
            $str .= ($value['new_order_amount'] / 100).',';
            $str .= ($value['old_order_amount'] / 100).',';
            $str .= $value['audit_pass_order_num'].',';
            $str .= $value['audit_pass_order_user_num'].',';
            $str .= $value['new_audit_pass_order_num'].',';
            $str .= $value['new_audit_pass_order_user_num'].',';
            $str .= $value['old_audit_pass_order_num'].',';
            $str .= ($value['audit_pass_order_amount'] / 100).',';
            $str .= ($value['new_audit_pass_order_amount'] / 100).',';
            $str .= ($value['old_audit_pass_order_amount'] / 100).',';
            $str .= $value['bind_card_pass_order_num'].',';
            $str .= $value['new_bind_card_pass_order_num'].',';
            $str .= $value['new_bind_card_pass_order_user_num'].',';
            $str .= $value['old_bind_card_pass_order_num'].',';
            $str .= ($value['bind_card_pass_order_amount'] / 100).',';
            $str .= ($value['new_bind_card_pass_order_amount'] / 100).',';
            $str .= ($value['old_bind_card_pass_order_amount'] / 100).',';
            $str .= $value['loan_success_order_num'].',';
            $str .= $value['new_loan_success_order_num'].',';
            $str .= $value['old_loan_success_order_num'].',';
            $str .= ($value['loan_success_order_amount'] / 100).',';
            $str .= ($value['new_loan_success_order_amount'] / 100).',';
            $str .= ($value['old_loan_success_order_amount'] / 100).',';
            $str .= (isset($value['updated_at'])?date("Y-m-d H:i:s" , $value['updated_at']):'-').',';
            $str .= "\n";
            echo $str;
        }die;
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
        $app_market = $this->request->get('app_market',0);
        $contrast_type = $this->request->get('contrast_type',0);
        $merchantIds = $this->request->get('merchant_id',[]);
        $packageName = $this->request->get('package_name',[]);
        $condition[] = 'and';

        if(!empty($app_market)){
            $condition[] = ['app_market' => $app_market];
        }
        if(!empty($packageName)){
            $condition[] = ['package_name' => $packageName];
        }
        if(!empty($merchantIds)){
            $condition[] = ['merchant_id' => $merchantIds];
        }
        if(!empty($contrast_type) && $add_start != $add_end){
            $add_start = date('Y-m-d');
            $add_end = date('Y-m-d');
        }

        if (!empty($add_start)) {
            $condition[] = ['>=', 'date', $add_start];
            $pre_date = $add_start;
        }else{
            $pre_date = date('Y-m-d', time() );
            $condition[] = ['>=', 'date', $pre_date];
        }
        if (!empty($add_end)) {
            $condition[] = ['<=', 'date', $add_end];
            $today_date = $add_end;
        }else{
            $today_date = date('Y-m-d', time()+ 86400); //默认显示7天的数据
            $condition[] = ['<=', 'date', $today_date];
        }

        // 列表查询部分
        $query = DailyTradeData::find()->where($condition)->orderBy("date desc");
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
            if($value['user_type']==1){
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
            if($value['user_type']==2){
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
                $user_type=2;
                break;
            case 6:
                $view_loan_type = "apply_money";
                $user_type=2;
                break;
            case 7:
                $view_loan_type = "loan_num";
                $user_type=2;
                break;
            case 8:
                $view_loan_type = "loan_money";
                $user_type=2;
                break;
            case 9:
                $view_loan_type = "pass_rate";
                $user_type=2;
                break;
            case 10:
                $view_loan_type = "apply_num";
                $user_type=1;
                break;
            case 24:
                $view_loan_type = "apply_num";
                $user_type=3;
                break;
            case 30:
                $view_loan_type = "apply_num";
                $user_type=5;
                break;
            case 11:
                $view_loan_type = "apply_money";
                $user_type=1;
                break;
            case 25:
                $view_loan_type = "apply_money";
                $user_type=3;
                break;
            case 31:
                $view_loan_type = "apply_money";
                $user_type=5;
                break;
            case 12:
                $view_loan_type = "loan_num";
                $user_type=1;
                break;
            case 26:
                $view_loan_type = "loan_num";
                $user_type=3;
                break;
            case 32:
                $view_loan_type = "loan_num";
                $user_type=5;
                break;
            case 13:
                $view_loan_type = "loan_money";
                $user_type=1;
                break;
            case 27:
                $view_loan_type = "loan_money";
                $user_type=3;
                break;
            case 33:
                $view_loan_type = "loan_money";
                $user_type=5;
                break;
            case 14:
                $view_loan_type = "pass_rate";
                $user_type=1;
                break;
            case 28:
                $view_loan_type = "pass_rate";
                $user_type=3;
                break;
            case 34:
                $view_loan_type = "pass_rate";
                $user_type=5;
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
                $user_type = 1;
                break;
            case 29:
                $view_loan_type = "apply_check_num";
                $user_type = 3;
                break;
            case 35:
                $view_loan_type = "apply_check_num";
                $user_type = 5;
                break;
            case 20:
                $view_loan_type = "apply_check_num";
                $user_type = 2;
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
            $maps = DailyTradeData::find()->select('app_market')->where($condition)->andWhere(['=','user_type',$user_type])->asArray()->groupBy("app_market")->column();
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
            ])->where($condition)->andWhere(['=','user_type',$user_type])->asArray()->groupBy("app_market,hour")->all($this->getStatsDb());
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
            ])->where($condition)->andWhere(['=','user_type',$user_type])->orderBy("date desc")->asArray()->groupBy("date,hour")->all($this->getStatsDb());
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
                $user_type=2;
                break;
            case 4:
                $view_repay_type = "repayment_money";
                $user_type=2;
                break;
            case 5:
                $view_repay_type = "repay_rate";
                $user_type=2;
                break;
            case 6:
                $view_repay_type = "repayment_num";
                $user_type=1;
                break;
            case 12:
                $view_repay_type = "repayment_num";
                $user_type=3;
                break;
            case 16:
                $view_repay_type = "repayment_num";
                $user_type=5;
                break;
            case 7:
                $view_repay_type = "repayment_money";
                $user_type=1;
                break;
            case 13:
                $view_repay_type = "repayment_money";
                $user_type=3;
                break;
            case 17:
                $view_repay_type = "repayment_money";
                $user_type=5;
                break;
            case 8:
                $view_repay_type = "repay_rate";
                $user_type=1;
                break;
            case 14:
                $view_repay_type = "repay_rate";
                $user_type=3;
                break;
            case 18:
                $view_repay_type = "repay_rate";
                $user_type=5;
                break;
            case 9:
                $view_repay_type = "active_repayment";
                $user_type=0;
                break;

            case 10:
                $view_repay_type = "active_repayment";
                $user_type=1;
                break;
            case 15:
                $view_repay_type = "active_repayment";
                $user_type=3;
                break;
            case 19:
                $view_repay_type = "active_repayment";
                $user_type=5;
                break;
            case 11:
                $view_repay_type = "active_repayment";
                $user_type=2;
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
            ])->where(['=','user_type',$user_type])->andWhere($condition)->orderBy("hour desc")
                ->groupBy("hour")->asArray()->all($this->getStatsDb());

            $repays_money = DailyTradeData::find()->select("sum(repays_money) as repays_money,sum(repays_money_tomorrow) as repays_money_tomorrow,app_market")
                ->where($condition)
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
                ->where(['=','user_type',$user_type])->andWhere($condition)->orderBy("hour desc")
                ->groupBy("date, hour")->asArray()->all($this->getStatsDb());

            $repays_money = DailyTradeData::find()->select("sum(repays_money) as repays_money,sum(repays_money_tomorrow) as repays_money_tomorrow,date")
                ->where($condition)
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
        $last_update_query = DailyTradeData::find()
            ->select(['updated_at'])
            ->orderBy(['updated_at' => SORT_ASC])
            ->limit(1)
            ->one();

        $update_time = (!empty($last_update_query['updated_at'])) ? date("Y-m-d H:i:s",$last_update_query['updated_at']) : '';
        $searchList = UserRegisterInfo::getChannelSearchList();
        $packageNameList = ArrayHelper::getColumn(DailyTradeData::find()->select(['package_name'])->distinct(['package_name'])->where(['merchant_id' => $this->merchantIds])->indexBy(['package_name'])->asArray()->all(),'package_name','package_name');
        unset($packageNameList['']);
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
     * @name 用户数据-每日借款数据（本金）
     * @date 2017-03
     * @return string|void
     */
    public function actionDailyData() {
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
        $query = StatisticsLoanCopy::find()
            ->select([
                'date_time',
                'loan_term',
                'loan_num' => 'SUM(loan_num)',
                'loan_num_old' => 'SUM(loan_num_old)',
                'loan_num_new' => 'SUM(loan_num_new)',
                'loan_money' => 'SUM(loan_money)',
                'loan_money_old' => 'SUM(loan_money_old)',
                'loan_money_new' => 'SUM(loan_money_new)',
                'updated_at',
                'created_at'
            ])
            ->where($condition)
            ->andWhere(['merchant_id' => $this->merchantIds])
            ->groupBy(['date_time','loan_term'])
            ->orderBy(['date_time' => SORT_DESC]);
        $info = $query->asArray()->all($this->getStatsDb());
        $last_list = StatisticsLoanCopy::find()->orderBy(['updated_at'=>SORT_DESC])->one();
        $last_update_at = $last_list['updated_at'];

        $total_loan_num = 0;
        $total_loan_money = 0;
        $total_loan_num_new = 0;
        $total_loan_num_old = 0;
        $total_loan_money_new = 0;
        $total_loan_money_old = 0;

        $ret = $query->all($this->getStatsDb());
        foreach ($ret as $item) {
            $total_loan_num = $total_loan_num + $item['loan_num'];
            $total_loan_money += $item['loan_money'];
            $total_loan_num_new += $item['loan_num_new'];
            $total_loan_num_old += $item['loan_num_old'];
            $total_loan_money_new += $item['loan_money_new'];
            $total_loan_money_old += $item['loan_money_old'];
        }
        //导出数据
        if($this->request->get('submitcsv') == 'exportcsv'){
            $service = new DayOrderDataStatisticsService();
            return $service->exportDailyData($info);
        }
        $fundList = LoanFund::getAllFundArray($this->merchantIds);
        $appMarketList = ArrayHelper::getColumn(StatisticsLoanCopy::find()->select(['app_market'])->distinct(['app_market'])->where(['merchant_id' => $this->merchantIds])->indexBy(['app_market'])->asArray()->all(),'app_market','app_market');
        $mediaSourceList = ArrayHelper::getColumn(StatisticsLoanCopy::find()->select(['media_source'])->distinct(['media_source'])->where(['merchant_id' => $this->merchantIds])->indexBy(['media_source'])->asArray()->all(),'media_source','media_source');
        $packageNameList = ArrayHelper::getColumn(StatisticsLoanCopy::find()->select(['package_name'])->distinct(['package_name'])->where(['merchant_id' => $this->merchantIds])->indexBy(['package_name'])->asArray()->all(),'package_name','package_name');
        if(isset($mediaSourceList[''])) {
            $mediaSourceList['-'] = '-';
            unset($mediaSourceList['']);
        }
        return $this->render('daily-data', array(
            'data' => $info,
            'total_loan_num' => $total_loan_num,
            'total_loan_money' => $total_loan_money,
            'total_loan_num_new' => $total_loan_num_new,
            'total_loan_num_old' => $total_loan_num_old,
            'total_loan_money_new' => $total_loan_money_new,
            'total_loan_money_old' => $total_loan_money_old,
            'channel' => $channel,
            'last_update_at' => $last_update_at,
            'fundList' => $fundList,
            'appMarketList' => $appMarketList,
            'mediaSourceList' => $mediaSourceList,
            'packageNameList' => $packageNameList,
            'isNotMerchantAdmin' => $this->isNotMerchantAdmin
        ));
    }

    /**
     * @name 用户数据-每日借款数据（本金-全平台）
     * @date 2017-03
     * @return string|void
     */
    public function actionDailyDataFullPlatform() {
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
            $condition[] = ['merchant_id' => $condition];
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
        $query = StatisticsLoanFullPlatform::find()
            ->select([
                'date_time',
                'loan_term',
                'loan_num' => 'SUM(loan_num)',
                'loan_num_old' => 'SUM(loan_num_old)',
                'loan_num_new' => 'SUM(loan_num_new)',
                'loan_money' => 'SUM(loan_money)',
                'loan_money_old' => 'SUM(loan_money_old)',
                'loan_money_new' => 'SUM(loan_money_new)',
                'updated_at',
                'created_at'
            ])
            ->where($condition)
            ->andWhere(['merchant_id' => $this->merchantIds])
            ->groupBy(['date_time','loan_term'])
            ->orderBy(['date_time' => SORT_DESC]);
        $info = $query->asArray()->all($this->getStatsDb());
        $last_list = StatisticsLoanFullPlatform::find()->orderBy(['updated_at'=>SORT_DESC])->one();
        $last_update_at = $last_list['updated_at'];

        $total_loan_num = 0;
        $total_loan_money = 0;
        $total_loan_num_new = 0;
        $total_loan_num_old = 0;
        $total_loan_money_new = 0;
        $total_loan_money_old = 0;

        $ret = $query->all($this->getStatsDb());
        foreach ($ret as $item) {
            $total_loan_num = $total_loan_num + $item['loan_num'];
            $total_loan_money += $item['loan_money'];
            $total_loan_num_new += $item['loan_num_new'];
            $total_loan_num_old += $item['loan_num_old'];
            $total_loan_money_new += $item['loan_money_new'];
            $total_loan_money_old += $item['loan_money_old'];
        }
        //导出数据
        if($this->request->get('submitcsv') == 'exportcsv'){
            $service = new DayOrderDataStatisticsService();
            return $service->exportDailyData($info);
        }
        $fundList = LoanFund::getAllFundArray($this->merchantIds);
        $appMarketList = ArrayHelper::getColumn(StatisticsLoanFullPlatform::find()->select(['app_market'])->distinct(['app_market'])->where(['merchant_id' => $this->merchantIds])->indexBy(['app_market'])->asArray()->all(),'app_market','app_market');
        $mediaSourceList = ArrayHelper::getColumn(StatisticsLoanFullPlatform::find()->select(['media_source'])->distinct(['media_source'])->where(['merchant_id' => $this->merchantIds])->indexBy(['media_source'])->asArray()->all(),'media_source','media_source');
        $packageNameList = ArrayHelper::getColumn(StatisticsLoanFullPlatform::find()->select(['package_name'])->distinct(['package_name'])->where(['merchant_id' => $this->merchantIds])->indexBy(['package_name'])->asArray()->all(),'package_name','package_name');
        if(isset($mediaSourceList[''])) {
            $mediaSourceList['-'] = '-';
            unset($mediaSourceList['']);
        }
        return $this->render('daily-data-full-platform', array(
            'data' => $info,
            'total_loan_num' => $total_loan_num,
            'total_loan_money' => $total_loan_money,
            'total_loan_num_new' => $total_loan_num_new,
            'total_loan_num_old' => $total_loan_num_old,
            'total_loan_money_new' => $total_loan_money_new,
            'total_loan_money_old' => $total_loan_money_old,
            'channel' => $channel,
            'last_update_at' => $last_update_at,
            'fundList' => $fundList,
            'appMarketList' => $appMarketList,
            'mediaSourceList' => $mediaSourceList,
            'packageNameList' => $packageNameList,
        ));
    }

    /**
     * @name 用户数据-每日借款数据（放款金额）
     * @date 2017-03
     * @return string|void
     */
    public function actionDailyData2() {
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
        $query = StatisticsLoanCopy2::find()
            ->select([
                'date_time',
                'loan_term',
                'loan_num' => 'SUM(loan_num)',
                'loan_num_old' => 'SUM(loan_num_old)',
                'loan_num_new' => 'SUM(loan_num_new)',
                'loan_money' => 'SUM(loan_money)',
                'loan_money_old' => 'SUM(loan_money_old)',
                'loan_money_new' => 'SUM(loan_money_new)',
                'updated_at',
                'created_at'
            ])
            ->where($condition)
            ->andWhere(['merchant_id' => $this->merchantIds])
            ->groupBy(['date_time','loan_term'])
            ->orderBy(['date_time' => SORT_DESC]);
        $info = $query->asArray()->all($this->getStatsDb());
        $last_list = StatisticsLoanCopy2::find()->orderBy(['updated_at'=>SORT_DESC])->one();
        $last_update_at = $last_list['updated_at'];

        $total_loan_num = 0;
        $total_loan_money = 0;
        $total_loan_num_new = 0;
        $total_loan_num_old = 0;
        $total_loan_money_new = 0;
        $total_loan_money_old = 0;

        $ret = $query->all($this->getStatsDb());
        foreach ($ret as $item) {
            $total_loan_num = $total_loan_num + $item['loan_num'];
            $total_loan_money += $item['loan_money'];
            $total_loan_num_new += $item['loan_num_new'];
            $total_loan_num_old += $item['loan_num_old'];
            $total_loan_money_new += $item['loan_money_new'];
            $total_loan_money_old += $item['loan_money_old'];
        }
        //导出数据
        if($this->request->get('submitcsv') == 'exportcsv'){
            $service = new DayOrderDataStatisticsService();
            return $service->exportDailyData($info);
        }
        $fundList = LoanFund::getAllFundArray($this->merchantIds);
        $appMarketList = ArrayHelper::getColumn(StatisticsLoanCopy2::find()->select(['app_market'])->distinct(['app_market'])->where(['merchant_id' => $this->merchantIds])->indexBy(['app_market'])->asArray()->all(),'app_market','app_market');
        $mediaSourceList = ArrayHelper::getColumn(StatisticsLoanCopy2::find()->select(['media_source'])->distinct(['media_source'])->where(['merchant_id' => $this->merchantIds])->indexBy(['media_source'])->asArray()->all(),'media_source','media_source');
        $packageNameList = ArrayHelper::getColumn(StatisticsLoanCopy2::find()->select(['package_name'])->distinct(['package_name'])->where(['merchant_id' => $this->merchantIds])->indexBy(['package_name'])->asArray()->all(),'package_name','package_name');
        if(isset($mediaSourceList[''])) {
            $mediaSourceList['-'] = '-';
            unset($mediaSourceList['']);
        }
        return $this->render('daily-data2', array(
            'data' => $info,
            'total_loan_num' => $total_loan_num,
            'total_loan_money' => $total_loan_money,
            'total_loan_num_new' => $total_loan_num_new,
            'total_loan_num_old' => $total_loan_num_old,
            'total_loan_money_new' => $total_loan_money_new,
            'total_loan_money_old' => $total_loan_money_old,
            'channel' => $channel,
            'last_update_at' => $last_update_at,
            'fundList' => $fundList,
            'appMarketList' => $appMarketList,
            'mediaSourceList' => $mediaSourceList,
            'packageNameList' => $packageNameList,
            'isNotMerchantAdmin' => $this->isNotMerchantAdmin
        ));
    }

    /**
     * @name 用户数据-每日借款数据（放款金额-全平台）
     * @date 2017-03
     * @return string|void
     */
    public function actionDailyData2FullPlatform() {
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
        $query = StatisticsLoan2FullPlatform::find()
            ->select([
                'date_time',
                'loan_term',
                'loan_num' => 'SUM(loan_num)',
                'loan_num_old' => 'SUM(loan_num_old)',
                'loan_num_new' => 'SUM(loan_num_new)',
                'loan_money' => 'SUM(loan_money)',
                'loan_money_old' => 'SUM(loan_money_old)',
                'loan_money_new' => 'SUM(loan_money_new)',
                'updated_at',
                'created_at'
            ])
            ->where($condition)
            ->andWhere(['merchant_id' => $this->merchantIds])
            ->groupBy(['date_time','loan_term'])
            ->orderBy(['date_time' => SORT_DESC]);
        $info = $query->asArray()->all($this->getStatsDb());
        $last_list = StatisticsLoan2FullPlatform::find()->orderBy(['updated_at'=>SORT_DESC])->one();
        $last_update_at = $last_list['updated_at'];

        $total_loan_num = 0;
        $total_loan_money = 0;
        $total_loan_num_new = 0;
        $total_loan_num_old = 0;
        $total_loan_money_new = 0;
        $total_loan_money_old = 0;

        $ret = $query->all($this->getStatsDb());
        foreach ($ret as $item) {
            $total_loan_num = $total_loan_num + $item['loan_num'];
            $total_loan_money += $item['loan_money'];
            $total_loan_num_new += $item['loan_num_new'];
            $total_loan_num_old += $item['loan_num_old'];
            $total_loan_money_new += $item['loan_money_new'];
            $total_loan_money_old += $item['loan_money_old'];
        }
        //导出数据
        if($this->request->get('submitcsv') == 'exportcsv'){
            $service = new DayOrderDataStatisticsService();
            return $service->exportDailyData($info);
        }
        $fundList = LoanFund::getAllFundArray($this->merchantIds);
        $appMarketList = ArrayHelper::getColumn(StatisticsLoan2FullPlatform::find()->select(['app_market'])->distinct(['app_market'])->where(['merchant_id' => $this->merchantIds])->indexBy(['app_market'])->asArray()->all(),'app_market','app_market');
        $mediaSourceList = ArrayHelper::getColumn(StatisticsLoan2FullPlatform::find()->select(['media_source'])->distinct(['media_source'])->where(['merchant_id' => $this->merchantIds])->indexBy(['media_source'])->asArray()->all(),'media_source','media_source');
        $packageNameList = ArrayHelper::getColumn(StatisticsLoan2FullPlatform::find()->select(['package_name'])->distinct(['package_name'])->where(['merchant_id' => $this->merchantIds])->indexBy(['package_name'])->asArray()->all(),'package_name','package_name');
        if(isset($mediaSourceList[''])) {
            $mediaSourceList['-'] = '-';
            unset($mediaSourceList['']);
        }
        return $this->render('daily-data2-full-platform', array(
            'data' => $info,
            'total_loan_num' => $total_loan_num,
            'total_loan_money' => $total_loan_money,
            'total_loan_num_new' => $total_loan_num_new,
            'total_loan_num_old' => $total_loan_num_old,
            'total_loan_money_new' => $total_loan_money_new,
            'total_loan_money_old' => $total_loan_money_old,
            'channel' => $channel,
            'last_update_at' => $last_update_at,
            'fundList' => $fundList,
            'appMarketList' => $appMarketList,
            'mediaSourceList' => $mediaSourceList,
            'packageNameList' => $packageNameList,
            'isNotMerchantAdmin' => $this->isNotMerchantAdmin
        ));
    }


    /**
     * @name 用户数据-每日复借数据统计
     * @return string|void
     */
    public function actionDayAgainRepayStatistics(){
        $date_start = $this->request->get('date_start', date("Y-m-d", strtotime('-7 day')));
        $date_end = $this->request->get('date_end', date("Y-m-d", strtotime('today')));
        $app_market = $this->request->get('app_market');
        $condition[] = 'and';
        if($app_market){
            $condition[] = ['app_market' => $app_market];
        }
        $merchantIds = $this->request->get('merchant_id',[]);
        if($merchantIds){
            $condition[] = ['merchant_id' => $merchantIds];
        }

        $query = ReApplyData::find()
            ->where(['between', 'date', $date_start, $date_end])
            ->andWhere($condition)
            ->andWhere(['merchant_id' => $this->merchantIds])
            ->orderBy(['date' => SORT_DESC]);
        $totalQuery = clone $query;
        $dateQuery = clone $query;
        $totalData = $totalQuery->select([
            'repay_num' => 'sum(repay_num)',
            'borrow_apply_num' => 'sum(borrow_apply_num)',
            'borrow_succ_num' => 'sum(borrow_succ_num)',
            'borrow_succ_money' => 'sum(borrow_succ_money)',
            'borrow_apply1_num' => 'sum(borrow_apply1_num)',
            'borrow_succ1_num' => 'sum(borrow_succ1_num)',
            'borrow_succ1_money' => 'sum(borrow_succ1_money)',
            'borrow_apply7_num' => 'sum(borrow_apply7_num)',
            'borrow_succ7_num' => 'sum(borrow_succ7_num)',
            'borrow_succ7_money' => 'sum(borrow_succ7_money)',
            'borrow_apply14_num' => 'sum(borrow_apply14_num)',
            'borrow_succ14_num' => 'sum(borrow_succ14_num)',
            'borrow_succ14_money' => 'sum(borrow_succ14_money)',
            'borrow_apply30_num' => 'sum(borrow_apply30_num)',
            'borrow_succ30_num' => 'sum(borrow_succ30_num)',
            'borrow_succ30_money' => 'sum(borrow_succ30_money)',
            'borrow_apply31_num' => 'sum(borrow_apply31_num)',
            'borrow_succ31_num' => 'sum(borrow_succ31_num)',
            'borrow_succ31_money' => 'sum(borrow_succ31_money)'

        ])->asArray()->all($this->getStatsDb());
        $totalData[0]['date'] = '总汇总';
        $totalData[0]['Type'] = 1; //汇总
        $dateData = $dateQuery->select([
            'date',
            'repay_num' => 'sum(repay_num)',
            'borrow_apply_num' => 'sum(borrow_apply_num)',
            'borrow_succ_num' => 'sum(borrow_succ_num)',
            'borrow_succ_money' => 'sum(borrow_succ_money)',
            'borrow_apply1_num' => 'sum(borrow_apply1_num)',
            'borrow_succ1_num' => 'sum(borrow_succ1_num)',
            'borrow_succ1_money' => 'sum(borrow_succ1_money)',
            'borrow_apply7_num' => 'sum(borrow_apply7_num)',
            'borrow_succ7_num' => 'sum(borrow_succ7_num)',
            'borrow_succ7_money' => 'sum(borrow_succ7_money)',
            'borrow_apply14_num' => 'sum(borrow_apply14_num)',
            'borrow_succ14_num' => 'sum(borrow_succ14_num)',
            'borrow_succ14_money' => 'sum(borrow_succ14_money)',
            'borrow_apply30_num' => 'sum(borrow_apply30_num)',
            'borrow_succ30_num' => 'sum(borrow_succ30_num)',
            'borrow_succ30_money' => 'sum(borrow_succ30_money)',
            'borrow_apply31_num' => 'sum(borrow_apply31_num)',
            'borrow_succ31_num' => 'sum(borrow_succ31_num)',
            'borrow_succ31_money' => 'sum(borrow_succ31_money)'
        ])->groupBy(['date'])->asArray()->all($this->getStatsDb());
        foreach ($dateData as &$val){
            $val['Type'] = 2;
        }
        $totalData = array_merge($totalData,$dateData);
        //var_dump($totalData);exit;
        $pages = new Pagination(['totalCount' => 9999999]);
        $pages->pageSize = \yii::$app->request->get('per-page', 15);
        $data = $query
            ->asArray()
            ->offset($pages->offset)
            ->limit($pages->limit)
            ->all($this->getStatsDb());
        $update_time = !empty($data)?date('Y-m-d H:i:s',$data[0]['updated_at']):date('Y-m-d H:i:s');
        //导出数据
        if($this->request->get('submitcsv') == 'exportdata'){
            $service = new ReApplyDataService();
            return $service->_exportAgainReapy($data);
        }
        $searchList = UserRegisterInfo::getChannelSearchList();
        return $this->render("consumer-repay",array(
            'totalData' => $totalData,
            'data'=>$data,
            'update_time'=>$update_time,
            'searchList' => $searchList,
            'pages' => $pages,
            'isNotMerchantAdmin' => $this->isNotMerchantAdmin
        ));
    }

    /**
     * @name 用户数据-每日还款金额数据
     */
    public function actionDayDataRepaymentStatistics() {
        return $this->repaymentStatistics('loan_money');
    }

    /**
     * @name 用户数据-每日还款单数数据
     */
    public function actionDayDataRepaymentNumStatistics() {
        return $this->repaymentStatistics('loan_num');
    }

    /**
     * @name 用户数据-每日还款金额数据（全平台）
     */
    public function actionDayDataRepaymentStatisticsFullPlatform() {
        return $this->repaymentStatistics('loan_money',true);
    }

    /**
     * @name 用户数据-每日还款单数数据（全平台）
     */
    public function actionDayDataRepaymentNumStatisticsFullPlatform() {
        return $this->repaymentStatistics('loan_num',true);
    }

    private function repaymentStatistics($type,$isAllPlatform = false){
        if($isAllPlatform && !$this->isNotMerchantAdmin){
            die('not find');
        }
        $condition[] = 'and';
        $search = $this->request->get();
        $fund_id = $search['fund_id']??0;
        $appMarket = $search['app_market']??[];
        $mediaSource = $search['media_source']??[];
        //var_dump($mediaSource);exit;
        $packageName = $search['package_name']??[];

        $newType = $isAllPlatform ? 3 : 1;
        $oldType = $isAllPlatform ? 4 : 2;
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

        $info = StatisticsDayData::find()
            ->select("sum(expire_num) as expire_num,
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
            ->orderBy('date DESC')
            ->asArray()
            ->all($this->getStatsDb());

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
            if(isset($value['user_type']) && $value['user_type'] == $oldType){
                $service->_getReturnData($data, $total_data, $date, 2, $value, $today_time);
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
        if ($this->request->get('submitcsv') == 'exportnum') {
            return $service->_exportDailyLoanData($data);
        }
        if ($this->request->get('submitcsv') == 'exportmoney') {
            return $service->_exportDailyRepaymentData($data);
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
                'isAllPlatform' => $isAllPlatform
            ]
        );
    }

    /**
     * @name 前置风控被拒统计
     * @return string
     * @throws \yii\mongodb\Exception
     */
    public function actionPreRejectReason()
    {
        $time = $this->request->get('date', date('Y-m-d'));
        $beginTime = strtotime($time);
        $endTime = $beginTime + 86400;

        $data = MgRiskTreeResult::rejectReasonStatistics('T101', false, $beginTime, $endTime);
        $list = [];
        foreach($data as $v)
        {
            $list[$v['_id']] = $v['num_tutorial'];
        }
        arsort($list);

        return $this->render('pre-reject-reason', [
            'title' => '前置风控被拒统计',
            'data' => $list,
        ]);
    }

    /**
     * @name 主决策风控被拒统计
     * @return string
     * @throws \yii\mongodb\Exception
     */
    public function actionMainRejectReason()
    {
        $time = $this->request->get('date', date('Y-m-d'));
        $beginTime = strtotime($time);
        $endTime = $beginTime + 86400;

        $list = [];
        $data = MgRiskTreeResult::rejectReasonStatistics('T102', false, $beginTime, $endTime);
        foreach($data as $v)
        {
            $list[$v['_id']] = $v['num_tutorial'];
        }
        arsort($list);

        return $this->render('pre-reject-reason', [
            'title' => '主决策风控被拒统计',
            'data' => $list,
        ]);
    }

    /**
     * @name 风控被拒原因每日统计
     * @return string
     */
    public function actionDailyRiskReject()
    {
        $addStart = $this->request->get('add_start');
        $addEnd = $this->request->get('add_end');
        $appMarket = $this->request->get('app_market',[]);
        $treeCode = $this->request->get('tree_code',[]);
        $txt = $this->request->get('txt');
        $condition[] = 'and';
        $count = 9999999;
        if ($addStart) {
            $condition[] = ['>=', 'date', $addStart];
        }
        if ($addEnd) {
            $condition[] = ['<=', 'date', $addEnd];
        }
        if($appMarket){
            $condition[] = ['app_market' => $appMarket];
        }
        if ($treeCode) {
            $condition[] = ['tree_code' => $treeCode];
        }
        if ($txt) {
            $condition[] = ['txt' => $txt];
        }
        $pages = new Pagination(['totalCount' => $count]);
        $pages->pageSize = \yii::$app->request->get('per-page', 15);
        $data = DailyRiskRejectData::find()
            ->where($condition)
            ->offset($pages->offset)
            ->limit($pages->limit)
            ->orderBy(['id' => SORT_DESC])
            ->asArray()
            ->all($this->getStatsDb());
        $searchList = UserRegisterInfo::getChannelSearchList();
        $treeCodeList = RiskResultSnapshot::getTreeCodeSearchList();
        $views = 'daily-risk-reject-data';
        return $this->render($views, [
            'data' => $data,
            'count' => $count,
            'pages' => $pages,
            'searchList' => $searchList,
            'treeCodeList' => $treeCodeList
        ]);
    }

    /**
     * @name 信审员每日统计
     * @return string
     */
    public function actionDailyCreditAudit()
    {
        $addStart = $this->request->get('add_start',date("Y-m-d", strtotime('-7 day')));
        $addEnd = $this->request->get('add_end', date("Y-m-d", strtotime('today')));
        $action = $this->request->get('action');
        $operators = $this->request->get('operators',[]);
        $username = $this->request->get('username');
        $condition[] = 'and';
        $count = 9999999;
        if ($addStart) {
            $condition[] = ['>=', 'date', $addStart];
        }
        if ($addEnd) {
            $condition[] = ['<=', 'date', $addEnd];
        }
        if($action){
            $condition[] = ['action' => intval($action)];
        }
        if(!empty($operators)){
            $condition[] = ['operator_id' => $operators];
        }
        if($username){
            $user = AdminUser::find()->where(['username' => $username])->one();
            if($user){
                $condition[] = ['operator_id' => $user->id];
            }else{
                $condition[] = ['operator_id' => 0];
            }
        }
        $query = DailyCreditAuditData::find()
            ->from(DailyCreditAuditData::tableName())
            ->where($condition)
            ->andWhere(['merchant_id' => Yii::$app->user->identity->merchant_id]);
        $totalQuery = clone $query;
        $dateQuery = clone $query;


        $totalData = $totalQuery->select([
                'date' => 'CONCAT("total")',
                'type' => 'ABS(1)',
                'audit_count' => 'SUM(audit_count)',
                'pass_count' => 'SUM(pass_count)',
                'loan_success_count' => 'SUM(loan_success_count)',
                'first_overdue_count' => 'SUM(first_overdue_count)',
            ])->asArray()
            ->all($this->getStatsDb());

        $dateData = $dateQuery->select([
            'type' => 'ABS(2)',
            'date',
            'audit_count' => 'SUM(audit_count)',
            'pass_count' => 'SUM(pass_count)',
            'loan_success_count' => 'SUM(loan_success_count)',
            'first_overdue_count' => 'SUM(first_overdue_count)',
        ])->groupBy(['date'])->orderBy(['date' => SORT_DESC])->asArray()
            ->all($this->getStatsDb());

        $totalData = array_merge($totalData,$dateData);


        $pages = new Pagination(['totalCount' => $count]);
        $pages->pageSize = \yii::$app->request->get('per-page', 15);
        $data = $query
            ->offset($pages->offset)
            ->limit($pages->limit)
            ->orderBy(['id' => SORT_DESC])
            ->asArray()
            ->all($this->getStatsDb());

        $operatorIds = DailyCreditAuditData::find()->select(['operator_id'])
            ->distinct(['operator_id'])->where(['merchant_id' => $this->merchantIds])->asArray()->all($this->getStatsDb());
        $operatorList = [];
        if(!empty($operatorIds))
        {
            $operatorIds = ArrayHelper::getColumn($operatorIds,'operator_id');
            $adminUser = AdminUser::find()->where(['id' => $operatorIds])->all();
            foreach ($adminUser as $value){
                $operatorList[$value['id']] = $value['username'];
            }
        }

        $views = 'daily-credit-audit-data';
        return $this->render($views, [
            'data' => $data,
            'count' => $count,
            'pages' => $pages,
            'totalData' => $totalData,
            'addStart' => $addStart,
            'addEnd' => $addEnd,
            'operatorList' => $operatorList
        ]);
    }


    /**
     * @name 用户数据转化统计
     * @return string
     */
    public function actionUserDataTransform()
    {
        $addStart = $this->request->get('add_start', date("Y-m-d", strtotime('-7 day')));
        $addEnd = $this->request->get('add_end',date("Y-m-d", strtotime('today')));
        $appMarket = $this->request->get('app_market',[]);
        $condition[] = 'and';
        $count = 9999999;
        if ($addStart) {
            $condition[] = ['>=', 'date', $addStart];
        }
        if ($addEnd) {
            $condition[] = ['<=', 'date', $addEnd];
        }
        if($appMarket){
            $condition[] = ['app_market' => $appMarket];
        }
        $pages = new Pagination(['totalCount' => $count]);
        $pages->pageSize = \yii::$app->request->get('per-page', 15);
        $query = UserOperationData::find()
            ->select('*')
            ->where($condition)
            ->andWhere(['type' => array_keys(UserOperationData::$type_name_map)])
            ->orderBy(['date' => SORT_DESC,'id' => SORT_DESC]);
        $totalQuery = clone $query;
        $pageData = $query->groupBy(['date','app_market'])
            ->offset($pages->offset)
            ->limit($pages->limit)
            ->asArray()
            ->all($this->getStatsDb());
        $dateArr = array_column($pageData,'date');
        $appMarketArr = array_column($pageData,'app_market');

        $data = UserOperationData::find()
            ->select('*')
            ->where(['date' => $dateArr,'app_market' => $appMarketArr])
            ->andWhere(['type' => array_keys(UserOperationData::$type_name_map)])
            ->orderBy(['date' => SORT_DESC,'id' => SORT_DESC])
            ->groupBy(['date','app_market','type'])
            ->asArray()
            ->all($this->getStatsDb());

        $dateData = [];
        foreach ($data as $val){
            if(in_array($val['type'],array_keys(UserOperationData::$type_name_map))){
                if(isset($date_data[$val['date']][$val['app_market']][$val['type']])){
                    $dateData[$val['date']][$val['app_market']][$val['type']] += $val['num'];
                }else{
                    $dateData[$val['date']][$val['app_market']][$val['type']] = $val['num'];
                }
            }
        }

        $allData = $totalQuery->groupBy(['date','app_market','type'])
            ->asArray()
            ->all($this->getStatsDb());
        $totalData = [];
        foreach ($allData as $val){
            if(in_array($val['type'],array_keys(UserOperationData::$type_name_map))){
                if(isset($totalData[''][$val['type']])){
                    $totalData[''][$val['type']] += $val['num'];
                }else{
                    $totalData[''][$val['type']] = $val['num'];
                }
                if(isset($totalData[$val['date']][$val['type']])){
                    $totalData[$val['date']][$val['type']] += $val['num'];
                }else{
                    $totalData[$val['date']][$val['type']] = $val['num'];
                }
            }
        }

        $searchList = UserRegisterInfo::getChannelSearchList();
        $views = 'user-data-transform';
        return $this->render($views, [
            'searchList' => $searchList,
            'totalDataList' => $totalData,
            'dateData' => $dateData,
            'addStart' => $addStart,
            'addEnd' => $addEnd,
            'count' => $count,
            'pages' => $pages,
            'update_time' => 0
        ]);
    }

    /**
     * @name 用户数据KYC转化统计
     * @return string
     */
    public function actionUserDataTransformKyc()
    {
        $addStart = $this->request->get('add_start', date("Y-m-d", strtotime('-7 day')));
        $addEnd = $this->request->get('add_end',date("Y-m-d", strtotime('today')));
        $appMarket = $this->request->get('app_market',[]);
        $condition[] = 'and';
        $count = 9999999;
        if ($addStart) {
            $condition[] = ['>=', 'date', $addStart];
        }
        if ($addEnd) {
            $condition[] = ['<=', 'date', $addEnd];
        }
        if($appMarket){
            $condition[] = ['app_market' => $appMarket];
        }
        $pages = new Pagination(['totalCount' => $count]);
        $pages->pageSize = \yii::$app->request->get('per-page', 15);
        $query = UserOperationData::find()->select('*')->where($condition)
            ->andWhere(['type' => array_keys(UserOperationData::$kyc_type_name_map)])
            ->orderBy(['date' => SORT_DESC,'id' => SORT_DESC]);
        $totalQuery = clone $query;
        $pageData = $query->groupBy(['date','app_market'])
            ->offset($pages->offset)
            ->limit($pages->limit)
            ->asArray()
            ->all($this->getStatsDb());
        $dateArr = array_column($pageData,'date');
        $appMarketArr = array_column($pageData,'app_market');

        $data = UserOperationData::find()
            ->select('*')
            ->where(['date' => $dateArr,'app_market' => $appMarketArr])
            ->andWhere(['type' => array_keys(UserOperationData::$kyc_type_name_map)])
            ->orderBy(['date' => SORT_DESC,'id' => SORT_DESC])
            ->groupBy(['date','app_market','type'])
            ->asArray()
            ->all($this->getStatsDb());

        $dateData = [];
        foreach ($data as $val){
            if(in_array($val['type'],array_keys(UserOperationData::$kyc_type_name_map))){
                if(isset($date_data[$val['date']][$val['app_market']][$val['type']])){
                    $dateData[$val['date']][$val['app_market']][$val['type']] += $val['num'];
                }else{
                    $dateData[$val['date']][$val['app_market']][$val['type']] = $val['num'];
                }
            }
        }

        $allData = $totalQuery->groupBy(['date','app_market','type'])
            ->asArray()
            ->all($this->getStatsDb());
        $totalData = [];
        foreach ($allData as $val){
            if(in_array($val['type'],array_keys(UserOperationData::$kyc_type_name_map))){
                if(isset($totalData[''][$val['type']])){
                    $totalData[''][$val['type']] += $val['num'];
                }else{
                    $totalData[''][$val['type']] = $val['num'];
                }
                if(isset($totalData[$val['date']][$val['type']])){
                    $totalData[$val['date']][$val['type']] += $val['num'];
                }else{
                    $totalData[$val['date']][$val['type']] = $val['num'];
                }
            }
        }

        $searchList = UserRegisterInfo::getChannelSearchList();
        $views = 'user-data-transform-kyc';
        return $this->render($views, [
            'searchList' => $searchList,
            'totalDataList' => $totalData,
            'dateData' => $dateData,
            'addStart' => $addStart,
            'addEnd' => $addEnd,
            'count' => $count,
            'pages' => $pages,
            'update_time' => 0
        ]);
    }

    /**
     * @name 金额回收总览
     * @return string
     */
    public function actionAmountRecoveryOverview()
    {
        $data = [];
        $loanFund = LoanFund::find()->where(['merchant_id' => $this->merchantIds])->all();
        $conditions[] = 'and';
        $endTime = $this->request->get('endTime');
        if(!empty($this->request->get('endTime')))
        {
            $endTime = strtotime($endTime) + 86400;
            $conditions[] = ['<', 'A.loan_time', $endTime];
        }
        $merchantIds = $this->request->get('merchant_id',[]);
        if($merchantIds){
            $conditions[] = ['A.merchant_id' => $merchantIds];
        }

        //总待收本息
        $waitFinishTotalPrincipalAndInterest = UserLoanOrderRepayment::find()
            ->select([
                'B.fund_id',
                'wait_finish_total_principal_and_interest' => 'SUM(A.principal + A.interests)'
            ])
            ->from(UserLoanOrderRepayment::tableName(). 'A')
            ->leftJoin(UserLoanOrder::tableName() .'B','A.order_id = B.id')
            ->where(['A.status' => UserLoanOrderRepayment::STATUS_NORAML])
            ->andWhere(['A.merchant_id' => $this->merchantIds])
            ->andWhere($conditions)
            ->groupBy(['B.fund_id'])->asArray()->all();
        foreach ($waitFinishTotalPrincipalAndInterest as $val){
            $data[$val['fund_id']]['wait_finish_total_principal_and_interest'] = $val['wait_finish_total_principal_and_interest'];
        }

        //待收本息和待收滞纳金（已逾期）
        $waitFinishPrincipalAndInterestExpire = UserLoanOrderRepayment::find()
            ->select([
                'B.fund_id',
                'wait_finish_principal_and_interest_expire' => 'SUM(A.principal + A.interests)',
                'wait_finish_overdue_fee' => 'SUM(A.overdue_fee)',
            ])
            ->from(UserLoanOrderRepayment::tableName(). 'A')
            ->leftJoin(UserLoanOrder::tableName() .'B','A.order_id = B.id')
            ->where(['A.status' => UserLoanOrderRepayment::STATUS_NORAML,'A.is_overdue' => UserLoanOrderRepayment::IS_OVERDUE_YES])
            ->andWhere(['A.merchant_id' => $this->merchantIds])
            //->andWhere(['<=','A.plan_repayment_time',strtotime('today')])
            ->andWhere($conditions)
            ->groupBy(['B.fund_id'])->asArray()->all();
        foreach ($waitFinishPrincipalAndInterestExpire as $val){
            $data[$val['fund_id']]['wait_finish_principal_and_interest_expire'] = $val['wait_finish_principal_and_interest_expire'];
            $data[$val['fund_id']]['wait_finish_overdue_fee'] = $val['wait_finish_overdue_fee'];
        }


        //待收本息（未到期，未逾期）
        $waitFinishPrincipalAndInterestBeforeExpire = UserLoanOrderRepayment::find()
            ->select([
                'B.fund_id',
                'wait_finish_principal_and_interest_before_expire' => 'SUM(A.principal + A.interests)',
            ])
            ->from(UserLoanOrderRepayment::tableName(). 'A')
            ->leftJoin(UserLoanOrder::tableName() .'B','A.order_id = B.id')
            ->where(['A.status' => UserLoanOrderRepayment::STATUS_NORAML,'A.is_overdue' => UserLoanOrderRepayment::IS_OVERDUE_NO])
            ->andWhere(['A.merchant_id' => $this->merchantIds])
            //->andWhere(['>','A.plan_repayment_time',strtotime('today')])
            ->andWhere($conditions)
            ->groupBy(['B.fund_id'])->asArray()->all();
        foreach ($waitFinishPrincipalAndInterestBeforeExpire as $val){
            $data[$val['fund_id']]['wait_finish_principal_and_interest_before_expire'] = $val['wait_finish_principal_and_interest_before_expire'];
        }

        //已收本息 和 已收滞纳金
        $finishPrincipalAndInterest = UserLoanOrderRepayment::find()
             ->select([
                 'B.fund_id',
                 'finish_principal_and_interest' => 'SUM(A.principal + A.interests)',
                 'finish_overdue_fee' => 'SUM(A.overdue_fee)',
             ])
             ->from(UserLoanOrderRepayment::tableName(). 'A')
             ->leftJoin(UserLoanOrder::tableName() .'B','A.order_id = B.id')
             ->where(['A.status' => UserLoanOrderRepayment::STATUS_REPAY_COMPLETE])
             ->andWhere(['A.merchant_id' => $this->merchantIds])
             ->andWhere($conditions)
             ->groupBy(['B.fund_id'])->asArray()->all();
        foreach ($finishPrincipalAndInterest as $val){
            $data[$val['fund_id']]['finish_principal_and_interest'] = $val['finish_principal_and_interest'];
            $data[$val['fund_id']]['finish_overdue_fee'] = $val['finish_overdue_fee'];
        }
        //moneyclick资方不显示
        unset($data[3]);
        $loanFundList = [];
        foreach ($loanFund as $v){
            $loanFundList[$v['id']] = $v['name'];
        }
        $totalData = [];
        foreach ($data as $v){
            $totalData['wait_finish_total_principal_and_interest']         = ($totalData['wait_finish_total_principal_and_interest'] ?? 0) + ($v['wait_finish_total_principal_and_interest'] ?? 0);
            $totalData['wait_finish_principal_and_interest_expire']        = ($totalData['wait_finish_principal_and_interest_expire'] ?? 0) + ($v['wait_finish_principal_and_interest_expire'] ?? 0);
            $totalData['wait_finish_principal_and_interest_before_expire'] = ($totalData['wait_finish_principal_and_interest_before_expire'] ?? 0) + ($v['wait_finish_principal_and_interest_before_expire'] ?? 0);
            $totalData['wait_finish_overdue_fee']                          = ($totalData['wait_finish_overdue_fee'] ?? 0) + ($v['wait_finish_overdue_fee'] ?? 0);
            $totalData['finish_overdue_fee']                               = ($totalData['finish_overdue_fee'] ?? 0) + ($v['finish_overdue_fee'] ?? 0);
            $totalData['finish_principal_and_interest']                    = ($totalData['finish_principal_and_interest'] ?? 0) + ($v['finish_principal_and_interest'] ?? 0);
        }
        return $this->render('amount-recovery-overview', [
            'totalData' => $totalData,
            'loanFundList' => $loanFundList,
            'data' => $data,
            'isNotMerchantAdmin' => $this->isNotMerchantAdmin
        ]);
    }

    /**
     * @name 用户结构表（订单）
     */
    public function actionUserStructureExportNum() {
        return $this->userStructureExport('num');
    }

    /**
     * @name 用户结构表（金额）
     */
    public function actionUserStructureExportMoney() {
        return $this->userStructureExport('money');
    }

    private function userStructureExport($type){
        if(!$this->isNotMerchantAdmin){
            die('not find');
        }
        $condition[] = 'and';
        $search = $this->request->get();
        $package_name = $search['package_name']??'';

        $field="user_type,date";
        if (!empty($search['begin_created_at'])) {
            $begin_created_at = str_replace(' ', '', $search['begin_created_at']);
        }else{
            $begin_created_at =date('Y-m-d', time() - 7*86400 );
        }
        if (!empty($search['end_created_at'])) {
            $end_created_at = str_replace(' ', '', $search['end_created_at']);
        }else {
            $end_created_at = date('Y-m-d', time() + 86400);
        }

        $loan_term = 7;
        $condition[] = ['>=', 'date', $begin_created_at];
        $condition[] =['<=', 'date', $end_created_at];

        if(!empty($package_name)){
            $condition[] = ['package_name' => $package_name];
        }
        $merchantIds = $this->request->get('merchant_id',[]);
        if($merchantIds){
            $condition[] = ['merchant_id' => $merchantIds];
        }

        $info = UserStructureExportRepaymentData::find()->select("sum(expire_num) as expire_num,
                      sum(expire_money) as expire_money,
                      sum(first_over_num) as first_over_num,
                      sum(first_over_money) as first_over_money,
                      user_type,
                      date")
            ->where($condition)
            ->andWhere(['merchant_id' => $this->merchantIds])
            ->groupBy($field)
            ->orderBy('date DESC')->asArray()->all($this->getStatsDb());

        $data = $total_data = [];
        $today_time = strtotime(date("Y-m-d", time()));
        $service =  new UserStructureRepaymentService();

        foreach($info as $value){
            $date=$value['date'];
            if($value['user_type']==0){
                $service->_getReturnData($data, $total_data, $date, 0, $value, $today_time);
            }
            if(isset($value['user_type']) && $value['user_type'] == 1){
                $service->_getReturnData($data, $total_data, $date, 1, $value, $today_time);
            }
            if(isset($value['user_type']) && $value['user_type'] == 2){
                $service->_getReturnData($data, $total_data, $date, 2, $value, $today_time);
            }
            if(isset($value['user_type']) && $value['user_type'] == 3){
                $service->_getReturnData($data, $total_data, $date, 3, $value, $today_time);
            }
            $data[$date]['unix_time_key'] = strtotime($value['date']);
            $data[$date]['time_key'] = $value['date'];
        }

        if($type == 'num'){//单数
            $views = 'user-structure-data-num';
        }
        if($type == 'money'){//金额
            $views = 'user-structure-data-money';
        }

        $packageNameList = PackageSetting::getLoanPackageNameMap();
        return $this->render($views, [
                'info' => $data,
                'total_info' => $total_data,
                'pages' => [],
                'update_time'=>0,
                'loan_term' => $loan_term,
                'packageNameList' => $packageNameList,
                'isNotMerchantAdmin' => $this->isNotMerchantAdmin,
            ]
        );
    }

    /**
     * @name 用户来源放款结构表（订单）
     */
    public function actionUserStructureSourceExportNum() {
        return $this->userStructureSourceExport('num');
    }

    /**
     * @name 用户来源放款结构表（金额）
     */
    public function actionUserStructureSourceExportMoney() {
        return $this->userStructureSourceExport('money');
    }

    private function userStructureSourceExport($type){
        if(!$this->isNotMerchantAdmin){
            die('not find');
        }
        $condition[] = 'and';
        $search = $this->request->get();
        $package_name = $search['package_name']??'';

        $field="user_type,date";
        if (!empty($search['begin_created_at'])) {
            $begin_created_at = str_replace(' ', '', $search['begin_created_at']);
        }else{
            $begin_created_at =date('Y-m-d', time() - 7*86400 );
        }
        if (!empty($search['end_created_at'])) {
            $end_created_at = str_replace(' ', '', $search['end_created_at']);
        }else {
            $end_created_at = date('Y-m-d', time() + 86400);
        }

        $loan_term = 7;
        $condition[] = ['>=', 'date', $begin_created_at];
        $condition[] = ['<=', 'date', $end_created_at];

        if(!empty($package_name)){
            $condition[] = ['package_name' => $package_name];
        }
        $merchantIds = $this->request->get('merchant_id',[]);
        if($merchantIds){
            $condition[] = ['merchant_id' => $merchantIds];
        }

        $info = UserStructureSourceExportRepaymentData::find()->select("sum(expire_num) as expire_num,
                      sum(expire_money) as expire_money,
                      sum(first_over_num) as first_over_num,
                      sum(first_over_money) as first_over_money,
                      user_type,
                      date")
            ->where($condition)
            ->andWhere(['merchant_id' => $this->merchantIds])
            ->groupBy($field)
            ->orderBy('date DESC')->asArray()->all($this->getStatsDb());

        $data = $total_data = [];
        $today_time = strtotime(date("Y-m-d", time()));
        $service =  new UserStructureRepaymentService();

        foreach($info as $value){
            $date=$value['date'];
            if($value['user_type']==0){
                $service->_getReturnData($data, $total_data, $date, 0, $value, $today_time);
            }
            if(isset($value['user_type']) && $value['user_type'] == 1){
                $service->_getReturnData($data, $total_data, $date, 1, $value, $today_time);
            }
            if(isset($value['user_type']) && $value['user_type'] == 2){
                $service->_getReturnData($data, $total_data, $date, 2, $value, $today_time);
            }
            if(isset($value['user_type']) && $value['user_type'] == 3){
                $service->_getReturnData($data, $total_data, $date, 3, $value, $today_time);
            }
            if(isset($value['user_type']) && $value['user_type'] == 4){
                $service->_getReturnData($data, $total_data, $date, 4, $value, $today_time);
            }
            $data[$date]['unix_time_key'] = strtotime($value['date']);
            $data[$date]['time_key'] = $value['date'];
        }

        if($type == 'num'){//单数
            $views = 'user-structure-source-data-num';
        }
        if($type == 'money'){//金额
            $views = 'user-structure-source-data-money';
        }

        $packageNameList = PackageSetting::getLoanPackageNameMap();
        return $this->render($views, [
                'info' => $data,
                'total_info' => $total_data,
                'pages' => [],
                'update_time'=>0,
                'loan_term' => $loan_term,
                'packageNameList' => $packageNameList,
                'isNotMerchantAdmin' => $this->isNotMerchantAdmin,
            ]
        );
    }

    /**
     * @name 用户数据-总还款金额数据
     */
    public function actionTotalRepaymentAmount() {
        $condition[] = 'and';
        $search = $this->request->get();
        $fund_id = $search['fund_id']??0;
        $merchantIds = $this->request->get('merchant_id',[]);
        $mediaSource = $this->request->get('media_source',[]);
        $packageName = $this->request->get('package_name',[]);

        $field="user_type,date";
        if (!empty($search['begin_created_at'])) {
            $begin_created_at = str_replace(' ', '', $search['begin_created_at']);
        }else{
            $begin_created_at =date('Y-m-d', time() - 7*86400 );
        }
        if (!empty($search['end_created_at'])) {
            $end_created_at = str_replace(' ', '', $search['end_created_at']);
        }else {
            $end_created_at = date('Y-m-d', time() + 86400);
        }

        $condition[] = ['>=', 'date', $begin_created_at];
        $condition[] = ['<=', 'date', $end_created_at];

        if(!empty($mediaSource)){
            $condition[] = ['media_source' => $mediaSource];
        }
        if($fund_id && !in_array(0, $fund_id)){
            $condition[] = ['fund_id' => $fund_id];
        }

        if(!empty($packageName)){
            $condition[] = ['package_name' => $packageName];
        }

        if($merchantIds){
            $condition[] = ['merchant_id' => $merchantIds];
        }
        $info = TotalRepaymentAmountData::find()
            ->select("sum(expire_num) as expire_num,
                      sum(expire_money) as expire_money,
                      sum(repay_num) as repay_num,
                      sum(repay_money) as repay_money,
                      sum(delay_num) as delay_num,
                      sum(delay_money) as delay_money,
                      user_type,
                      date")
            ->where($condition)
            ->andWhere(['merchant_id' => $this->merchantIds])
            ->orderBy('date DESC')
            ->asArray()
            ->groupBy($field)
            ->all($this->getStatsDb());


        $data = $total_data = [];
        $service =  new TotalRepaymentAmountService();

        foreach($info as $value){
            $date=$value['date'];

            if(isset($value['user_type'])){
                $service->_getReturnData($data, $total_data, $date,  $value['user_type'], $value);
            }
            $data[$date]['unix_time_key'] = strtotime($value['date']);
            $data[$date]['time_key'] = $value['date'];
        }

        if ($this->request->get('submitcsv') == 'export') {
            return $service->_exportTotalRepaymentAmount($data);
        }
        $fundList = LoanFund::getAllFundArray($this->merchantIds);
        $mediaSourceList = ArrayHelper::getColumn(TotalRepaymentAmountData::find()->select(['media_source'])->distinct(['media_source'])->where(['merchant_id' => $this->merchantIds])->indexBy(['media_source'])->asArray()->all(),'media_source','media_source');
        $packageNameList = ArrayHelper::getColumn(TotalRepaymentAmountData::find()->select(['package_name'])->distinct(['package_name'])->where(['merchant_id' => $this->merchantIds])->indexBy(['package_name'])->asArray()->all(),'package_name','package_name');
        if(isset($mediaSourceList[''])) {
            $mediaSourceList['-'] = '-';
            unset($mediaSourceList['']);
        }
        return $this->render('total-repayment-amount', [
                'info' => $data,
                'fundList' => $fundList,
                'mediaSourceList' => $mediaSourceList,
                'packageNameList' => $packageNameList,
                'total_info' => $total_data,
                'pages' => [],
                'update_time'=>0,
                'isNotMerchantAdmin' => $this->isNotMerchantAdmin,
            ]
        );
    }

    /**
     * @name 用户数据-每日累计还款金额
     */
    public function actionDailyRepaymentGrand() {
        $where = [];
        $search = $this->request->get();
        $packageName = $search['package_name'] ?? '';
        if(isset($packageName) && $packageName != ''){
            $where['package_name'] = $packageName;
        }
        $merchant_id = $search['merchant_id'] ?? [];
        if(!empty($merchant_id)){
            $where['merchant_id'] = $merchant_id;
        }
        $query = DailyRepaymentGrandTotal::find()->select([
            'date',
            'all_repay_amount' => 'SUM(all_repay_amount)',
            'all_repay_order_num' => 'SUM(all_repay_order_num)',
            'delay_repay_amount' => 'SUM(delay_repay_amount)',
            'delay_repay_order_num' => 'SUM(delay_repay_order_num)',
            'extend_amount' => 'SUM(extend_amount)',
            'extend_order_num' => 'SUM(extend_order_num)',
            '16_30repay_amount' => 'SUM(IF(overdue_day >= 16 AND overdue_day < 31,all_repay_amount,0))',
            '16_30repay_order_num' => 'SUM(IF(overdue_day >= 16 AND overdue_day < 31,all_repay_order_num,0))',
            '31_60repay_amount' => 'SUM(IF(overdue_day >= 31 AND overdue_day < 61,all_repay_amount,0))',
            '31_60repay_order_num' => 'SUM(IF(overdue_day >= 31 AND overdue_day < 61,all_repay_order_num,0))',
            '61_90repay_amount' => 'SUM(IF(overdue_day >= 61 AND overdue_day < 91,all_repay_amount,0))',
            '61_90repay_order_num' => 'SUM(IF(overdue_day >= 61 AND overdue_day < 91,all_repay_order_num,0))',
            '91_repay_amount' => 'SUM(IF(overdue_day >= 91,all_repay_amount,0))',
            '91_repay_order_num' => 'SUM(IF(overdue_day >= 91,all_repay_order_num,0))',

        ])
            ->where($where)
            ->andWhere(['merchant_id' => $this->merchantIds]);
        $sDate = $search['s_date'] ?? date('Y-m-d', time() - 7*86400 );
        if(isset($sDate) && $sDate != ''){
            $query->andWhere(['>=','date',$sDate]);
        }
        $eDate = $search['e_date'] ?? date('Y-m-d');
        if(isset($eDate) && $eDate != ''){
            $query->andWhere(['<=','date',$eDate]);
        }
        if (isset($search['overdue_day']) && $search['overdue_day'] !== '') {
            $overdue_day = explode('-',$search['overdue_day']);

            if(count($overdue_day) > 1){
                $query->andWhere(['>=','overdue_day',$overdue_day[0]])->andWhere(['<=','overdue_day',$overdue_day[1]]);
            }else{
                $query->andWhere(['overdue_day' => $overdue_day]);
            }
        }
        if ($this->request->get('submitcsv') == 'export') {
            $info = $query->groupBy(['date'])->orderBy(['date' => SORT_DESC])->asArray()->all();
            $this->_setcsvHeader('dailyRepaymentGrand.csv');
            $items = [];
            foreach($info as $value){
                $items[] = [
                    Yii::T('common', 'date') => $value['date'],
                    Yii::T('common', 'Total repayment orders') => $value['all_repay_order_num'],
                    Yii::T('common', 'Total repayment amount') => number_format($value['all_repay_amount']/100),
                    Yii::T('common', 'Delay orders') => $value['delay_repay_order_num'],
                    Yii::T('common', 'Delay amount') => number_format($value['delay_repay_amount']/100),
                    Yii::T('common', 'Extension number') => $value['extend_order_num'],
                    Yii::T('common', 'Extension money') => number_format($value['extend_amount']/100),
                ];
            }
            echo $this->_array2csv($items);
            exit;
        }

        $totalQuery = clone $query;
        $pages = new Pagination(['totalCount' => $query->count()]);
        $pages->pageSize = \yii::$app->request->get('per-page', 15);
        $info = $query
            ->groupBy(['date'])
            ->offset($pages->offset)
            ->limit($pages->limit)
            ->orderBy(['date' => SORT_DESC])
            ->asArray()
            ->all();
        $totalInfo = $totalQuery->asArray()->one();
        $packageNameList = ArrayHelper::getColumn(DailyRepaymentGrandTotal::find()
            ->select(['package_name'])->distinct(['package_name'])
            ->where(['merchant_id' => $this->merchantIds])
            ->indexBy(['package_name'])->asArray()->all()
            ,'package_name','package_name');
        return $this->render('daily-repayment-grand', [
                'info' => $info,
                'totalInfo' => $totalInfo,
                'pages' => $pages,
                'packageNameList' => $packageNameList,
                'isNotMerchantAdmin' => $this->isNotMerchantAdmin
            ]
        );
    }
}
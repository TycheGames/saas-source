<?php

namespace callcenter\controllers;
use backend\models\Merchant;
use callcenter\models\AdminUser;
use callcenter\models\CollectionCheckinLog;
use callcenter\models\CollectorAttendanceDayData;
use callcenter\models\CollectorBackMoney;
use callcenter\models\CollectorCallData;
use callcenter\models\CompanyTeam;
use callcenter\models\DispatchOutsideFinish;
use callcenter\models\DispatchOverdueDaysFinish;
use callcenter\models\InputOverdayOut;
use callcenter\models\InputOverdayOutAmount;
use callcenter\models\loan_collection\LoanCollectionOrder;
use callcenter\models\LoanCollectionDayStatistics;
use callcenter\models\loan_collection\UserCompany;
use callcenter\models\LoanCollectionStatisticNew;
use callcenter\models\LoanCollectionStatistics;
use callcenter\models\order_statistics\OrderStatisticsByDay;
use callcenter\models\order_statistics\OrderStatisticsByGroup;
use callcenter\models\order_statistics\OrderStatisticsByRate;
use callcenter\models\order_statistics\OrderStatisticsByStatus;
use callcenter\models\OrderStatistics;
use callcenter\models\OutsideDayData;
use common\models\message\NxPhoneLog;
use common\models\GlobalSetting;
use common\models\order\UserLoanOrderRepayment;
use common\models\package\PackageSetting;
use common\models\stats\StatisticsDayData;
use common\services\collection_stats\InputOverdueOutService;
use Yii;
use yii\data\Pagination;
use yii\db\Query;
use yii\helpers\Url;

class CollectionStatisticsController extends  BaseController{


    /**
     * @name 催收员每日统计（漏斗）
     */
    public function actionLoanCollectionDayDataList(){
        $condition = [];
        $condition[] = 'and';
        $operator_id = Yii::$app->user->id;
        $user = AdminUser::findIdentity($operator_id);
        $is_outside = false;  //是自己人

        //取出所有启用的催收机构 默认是自营
        if($user && $user->outside > 0){
            $is_outside = true;
            $outside = $user->outside;
            $condition[] = ['S.outside' => $outside];
        }else{
            $outside = $this->request->get('outside','');
            if(!empty($outside)){
                $condition[] = ['S.outside' => $outside];
            }
        }
        $startDate = $this->request->get('start_date',date('Y-m-d',strtotime('-7 day')));
        $endDate = $this->request->get('end_date',date('Y-m-d'));
        if ($startDate) {
            $condition[] = ['>=', 'S.date', $startDate];
        }
        if ($endDate) {
            $condition[] = ['<=', 'S.date', $endDate];
        }
        //催收人名字
        $username = $this->request->get('username','');
        if (!empty($username)) {
            $condition[] = ['L.username' => trim($username)];
        }
        $group  = $this->request->get('group','');
        if(!empty($group)){
            $condition[] = ['S.group' => trim($group)];
        }
        $group_game  = $this->request->get('group_game','');
        if(!empty($group_game)){
            $condition[] = ['S.group_game' => trim($group_game)];
        }

        // 加上商户号判断
        if (is_array($this->merchantIds)) {
            $sMerchantIds = $this->merchantIds;
        } else {
            $sMerchantIds = explode(',', $this->merchantIds);
        }

        $condition[] = ['C.merchant_id' => $sMerchantIds];
        $condition[] = ['L.merchant_id' => $sMerchantIds];

        $query = LoanCollectionDayStatistics::find()
            ->from(LoanCollectionDayStatistics::tableName() . ' S')
            ->leftJoin(UserCompany::tableName() . ' C','C.id = S.outside')
            ->leftJoin(AdminUser::tableName(). ' L','L.id = S.admin_user_id')
            ->where($condition);
        $totalQuery = clone $query;
        $sumData = $totalQuery->select([
            'S.date',
            'L.group',
            'C.real_title',
            'L.username',
            'sum(S.get_total_count) as get_total_count',
            'sum(S.get_total_money) as get_total_money',
            'sum(S.operate_total) as operate_total',
            'sum(S.finish_total_count) as finish_total_count',
            'sum(S.finish_total_money) as finish_total_money',
            'sum(S.get_total_count) as get_total_count',
            'sum(S.get_total_money) as get_total_money'
        ])->asArray()->all();
        $sumData[0]['date'] = 'Collect';
        $sumData[0]['Type'] = 1; //汇总
        if(empty($username)){
            unset($sumData[0]['username']); //汇总
        }
        if(empty($outside)){
            unset($sumData[0]['real_title']); //汇总
        }
        if(empty($group)){
            unset($sumData[0]['group']); //汇总
        }
        if(empty($group_game)){
            unset($sumData[0]['group_game']); //汇总
        }
        $sumDateData = $totalQuery->select([
            'S.date',
            'L.group',
            'C.real_title',
            'L.username',
            'sum(S.get_total_count) as get_total_count',
            'sum(S.get_total_money) as get_total_money',
            'sum(S.operate_total) as operate_total',
            'sum(S.finish_total_count) as finish_total_count',
            'sum(S.finish_total_money) as finish_total_money',
            'sum(S.get_total_count) as get_total_count',
            'sum(S.get_total_money) as get_total_money'
        ])->groupBy(['date'])->orderBy(['date' => SORT_DESC])->asArray()->all();
        foreach ($sumDateData as &$val){
            $val['Type'] = 2;
            if(empty($username)){
                unset($val['username']); //汇总
            }
            if(empty($outside)){
                unset($val['real_title']); //汇总
            }
            if(empty($group)){
                unset($val['group']); //汇总
            }
            if(empty($group_game)){
                unset($val['group_game']); //汇总
            }
        }
        $dateData = array_merge($sumData,$sumDateData);
        //var_dump($dateData);exit;
        if(!empty($request['is_summary']) && $request['is_summary'] == 1){
            $pages = new Pagination(['totalCount' => $query->count('id')]);
        }else{
            $pages = new Pagination(['totalCount' => 999999]);
        }
        $page_size = Yii::$app->request->get('page_size',15);
        $pages->pageSize = $page_size;
        $data = $query
            ->select(['S.*','C.real_title','L.username','L.group'])
            ->orderBy(['S.id' => SORT_DESC])
            ->offset($pages->offset)
            ->limit($pages->limit)
            ->asArray()
            ->all();
        $teamList = CompanyTeam::getTeamsByOutside($outside);
        return $this->render('loan-collection-day-data-list',[
            'pages'=>$pages,
            'data'=>$data,
            'dateData'=>$dateData,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'is_outside'=> $is_outside,
            'teamList' => $teamList,
            'merchant_id' => $this->merchantIds
        ]);

    }

    /**
     * @name 订单状况每日统计
     * @return string
     */
    public function actionOrderStatistics(){
        $condition = [];
        $condition[] = 'and';
        $startDate = $this->request->get('start_date',date('Y-m-d',strtotime('-7 day')));
        $endDate = $this->request->get('end_date',date('Y-m-d'));
        if (!empty($startDate)) {
            $condition[] = ['>=', 'date', $startDate];
        }
        if (!empty($endDate)) {
            $condition[] = ['<=', 'date', $endDate];
        }

        // 加上商户号判断
        if (is_array($this->merchantIds)) {
            $sMerchantIds = $this->merchantIds;
        } else {
            $sMerchantIds = explode(',', $this->merchantIds);
        }

        $condition[] = ['merchant_id' => $sMerchantIds];
        $select = ['sum(loan_num) as loan_num','sum(repay_num) as repay_num'];
        $query = OrderStatistics::find()->where($condition);
        $totalQuery = clone $query;
        $totalData = $totalQuery->select($select)->one();
        $select[] = 'date';
        $select[] = 'updated_at';
        $pages = new Pagination(['totalCount' => $query->count('id')]);
        $page_size = Yii::$app->request->get('page_size',15);
        $pages->pageSize = $page_size;

        $list = $query
            ->select($select)
            ->groupBy(['date'])
            ->orderBy(['date' => SORT_DESC])
            ->offset($pages->offset)
            ->limit($pages->limit)
            ->asArray()
            ->all();
        return $this->render('order-statistics',[
            'totalData'=>$totalData,
            'pages' => $pages,
            'list' => $list,
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);
    }

    //统计汇总
    private function _TotalData($regroup,$loan_group,$order_level,$group_game){
        $result = [];
        $result['total_all_money'] = 0;  //本金总额
        $result['total_all_total'] = 0;  //订单数
        $result['total_yes_money'] = 0;  //还款本金
        $result['total_not_money'] = 0;  //未还本金
        $result['total_yes_total'] = 0;  //还款订单数
        $result['total_all_late'] = 0;   //滞纳金总金额
        $result['finish_fee'] = 0;     //还款率
        $result['no_finish_fee'] = 0;   //迁徙率
        $result['late_fee'] = 0;    //滞纳金回收率
        $result['total_yes_late'] = 0;   //收取滞纳金金额
        $result['total_operate_total'] =  0 ;    //处理量
        $result['oneday_money'] =  0 ;    //首日完成金额
        $result['oneday_money_fee'] =  0 ;    //首日完成率
        if(!empty($loan_group) || !empty($order_level) || !empty($group_game)){
            if($regroup){
                foreach ($regroup as $val){
                    $result['total_all_money'] += $val['today_all_money'];
                    $result['total_all_total'] += $val['loan_total'];
                    $result['total_yes_money'] += $val['today_finish_money'];
                    $result['total_not_money'] =  $result['total_all_money']-$result['total_yes_money'];
                    $result['total_yes_total'] += $val['loan_finish_total'];
                    $result['total_all_late'] += $val['today_finish_late_fee'];
                    $result['oneday_money'] += $val['oneday_money'];
                    $result['finish_fee'] =$result['total_all_money']? $result['total_yes_money']/$result['total_all_money']:0;
                    $result['no_finish_fee']  = 1-$result['finish_fee'];
                    $result['oneday_money_fee']  = $result['total_all_money']? $result['oneday_money']/$result['total_all_money']:0;
                    $result['total_yes_late'] +=$val['finish_late_fee'];
                    $result['late_fee'] =$result['total_all_late']? $result['total_yes_late']/ $result['total_all_late']:0;
                    $result['total_operate_total'] +=$val['operate_total'];
                }
            }
        }
        return $result;
    }

    //统计催收人员跟踪每日工作情况 过滤条件
    private function getAdminWorkListTrackFilter(){
        $username = $this->request->get('username');
        $start_time = $this->request->get('start_time');
        $end_time = $this->request->get('end_time');
        $loan_group = $this->request->get('loan_group');
        $order_level = $this->request->get('order_level');
        $outside = $this->request->get('outside');
        $group_game = $this->request->get('group_game');
        $realName  = $this->request->get('real_name');
        $condition = [];
        $condition[] = 'and';
        if($start_time && $end_time)
        {
            $start = strtotime($start_time);
            $end = strtotime($end_time)+(86400);
            $condition[] = ['between', 'dispatch_time', $start, $end];
        }
        else
        {
            $start_time = strtotime(date('Y-m-01'));
            $end_time = time();
            $condition[] = ['between', 'dispatch_time', $start_time, $end_time];
        }
        if ($outside) {
            $condition[] = ['outside_id' => $outside];
        }


        if(!empty($username)){
            $loan_collections = AdminUser::find()
                ->where(['like','username',$username])
                ->all();
            $uids = array_column($loan_collections,'id');
            if(empty($uids)){
                $uids = [0];
            }
            $condition[] = ['admin_user_id' => $uids];
        }
        if(!empty($realName)){
            $loan_collections = AdminUser::find()
                ->where(['like','real_name',$realName])
                ->all();
            $uids = array_column($loan_collections,'id');
            if(empty($uids)){
                $uids = [0];
            }
            $condition[] = ['admin_user_id' => $uids];
        }
//        if($username)
//        {
//            $username = trim($username);
//            $condition .= " and admin_user_name= '{$username}'";
//        }

        if(!empty($group_game)){
            $loan_collections = AdminUser::find()
                ->where(['group_game'=>$group_game])
                ->all();
            $uids = array_column($loan_collections,'id');
            if(empty($uids)){
                $uids = [0];
            }
            $condition[] = ['admin_user_id' => $uids];
        }

        //某个分组级别
        if ($loan_group) {
            $condition[] = ['loan_group' => intval($loan_group)];
        }
        if ($order_level) {
            $condition[] = ['order_level' => intval($order_level)];
        }
        return $condition;
    }

    //统计催收人员每日工作情况 过滤条件
    private function getAdminWorkListFilter(){
        $username = $this->request->get('username');
        $oneday = $this->request->get('oneday');

        $loan_group = $this->request->get('loan_group');
        $order_level = $this->request->get('order_level');
        $group_game = $this->request->get('group_game');
        $realName = $this->request->get('real_name');
        $condition = [];
        $condition[] = 'and';
        //具体某一天
        if($oneday){
            if(strtotime($oneday) == strtotime('today')){
                $condition[] = ['>=', 'created_at', strtotime('today')];
            }else{
                $next_time = strtotime($oneday)+24*3600;
                $condition[] = ['between', 'created_at', strtotime($oneday), $next_time];
            }
        }else{
            $condition[] = ['>=', 'created_at', strtotime('today')]; //默认是当天
        }
        if(!empty($group_game)){
            $loan_collections = AdminUser::find()
                ->where(['group_game'=>$group_game])
                ->all();
            $uids = array_column($loan_collections,'id');
            if(empty($uids)){
                $uids = [0];
            }
            $condition[] = ['admin_user_id' => $uids];
        }
        //具体某个人的
        if(!empty($username)){
            $loan_collections = AdminUser::find()
                ->where(['like','username',$username])
                ->all();
            $uids = array_column($loan_collections,'id');
            if(empty($uids)){
                $uids = [0];
            }
            $condition[] = ['admin_user_id' => $uids];
        }
        //具体某个人的
        if(!empty($realName)){
            $loan_collections = AdminUser::find()
                ->where(['like','real_name',$realName])
                ->all();
            $uids = array_column($loan_collections,'id');
            if(empty($uids)){
                $uids = [0];
            }
            $condition[] = ['admin_user_id' => $uids];
        }
        //某个分组级别
        if ($loan_group) {
            $condition[] = ['loan_group' => intval($loan_group)];
        }
        if ($order_level) {
            $condition[] = ['order_level' => intval($order_level)];
        }
        //某个资方

        return $condition;
    }

    //排序条件
    public function SortFilter($data)
    {
        $flag = 1;
        $dataArrNew = [];
        if($sort_by =$this->request->get('btn_sort'))
        {
            $flag = (int)$this->request->get('flag');
            if($sort_by == 'finish_total_rate' )
            {
                $i=1;
                foreach ($data as $key=>$val)
                {
                    $k= number_format(($val['today_finish_money']/$val['today_all_money']),4)*10000;
                    $arr = array_keys($dataArrNew);
                    if(in_array($k,$arr))
                    {
                        $k+=1;
                        $i++;
                    }
                    $dataArrNew[$k] = $val;
                }
            }elseif($sort_by == 'loan_total')
            {
                $i=1;
                foreach ($data as $key=>$val)
                {
                    $k = $val['loan_total'];
                    $arr = array_keys($dataArrNew);
                    if(in_array($val['loan_total'],$arr))
                    {
                        $k+=$i;
                        $i++;
                    }
                    $dataArrNew[$k] = $val;
                }
            }elseif ($sort_by == 'no_finish_total_rate')
            {
                $i = 1;
                foreach ($data as $key=>$val)
                {
                    $k= number_format(($val['today_no_finish_money']/$val['today_all_money']),4)*10000;
                    $arr = array_keys($dataArrNew);
                    if(in_array($k,$arr))
                    {
                        $k+=1;
                        $i++;
                    }
                    $dataArrNew[$k] = $val;
                }
            }elseif ($sort_by =='late_fee')
            {
                $i = 1;
                foreach ($data as $key=>$val)
                {
                    @$k= number_format(($val['finish_late_fee']/$val['today_finish_late_fee']),4)*100000;
                    $arr = array_keys($dataArrNew);
                    if(in_array($k,$arr))
                    {
                        $k+=$i;
                        $i++;
                    }
                    $dataArrNew[$k] = $val;
                }
            }elseif ($sort_by == 'today_all_money')
            {
                $i=1;
                foreach ($data as $key=>$val)
                {
                    $arr = array_keys($dataArrNew);
                    $k = $val['today_all_money'];
                    if(in_array($val['today_all_money'],$arr))
                    {
                        $k+=$i;
                        $i++;
                    }
                    $dataArrNew[$k] = $val;
                }
            }elseif ($sort_by == 'today_finish_money')
            {
                $i=1;
                foreach ($data as $key=>$val)
                {
                    $arr = array_keys($dataArrNew);
                    $k =$val['today_finish_money'];
                    if(in_array($val['today_finish_money'],$arr))
                    {
                        $k+=$i;
                        $i++;
                    }
                    $dataArrNew[$k] = $val;
                }
            }elseif ($sort_by == 'loan_finish_total')
            {
                $i=1;
                foreach ($data as $key=>$val)
                {
                    $k =$val['loan_finish_total'];
                    $arr = array_keys($dataArrNew);
                    if(in_array($val['loan_finish_total'],$arr))
                    {
                        $k+=$i;
                        $i++;
                    }
                    $dataArrNew[$k] = $val;
                }
            }elseif($sort_by == 'operate_rate_sort'){
                $i=1;
                foreach ($data as $key=>$val)
                {
                    $k= $val['order_total'] ? number_format(($val['operate_total']/$val['order_total']),4)*10000:0;
                    $arr = array_keys($dataArrNew);
                    if(in_array($k,$arr))
                    {
                        $k+=$i;
                        $i++;
                    }
                    $dataArrNew[$k] = $val;
                }
            }elseif($sort_by == 'yes_rate_sort'){
                $i=1;
                foreach ($data as $key=>$val)
                {
                    $k= !empty($val['record_count'])?number_format(($val['record_yes']/$val['record_count']),4)*10000:0;
                    $arr = array_keys($dataArrNew);
                    if(in_array($k,$arr))
                    {
                        $k+=$i;
                        $i++;
                    }
                    $dataArrNew[$k] = $val;
                }
            }elseif($sort_by == 'oneday_money_rate'){
                $i=1;
                foreach ($data as $key=>$val)
                {
                    $k= number_format(($val['oneday_money']/$val['today_all_money']),4)*10000;
                    $arr = array_keys($dataArrNew);
                    if(in_array($k,$arr))
                    {
                        $k+=1;
                        $i++;
                    }
                    $dataArrNew[$k] = $val;
                }
            }
            if($flag%2 ==0)
            {
                ksort($dataArrNew);
            }
            else
            {
                krsort($dataArrNew);
            }
            ++$flag;
        }
        return [$flag,$dataArrNew];
    }


    /**
    * 催收人员每日工作列表导出方法
    */
    private function _exportAdminWorkInfos($loan_collection_statistics,$oustide,$setRealNameCollectionAdmin){
        $this->_setcsvHeader($oustide.'催收人员每日工作列表导出.csv');
        $outsid = UserCompany::getAll();
        $outsideinfo = [];
        foreach ($outsid as $lue) {
            $outsideinfo[$lue['id']] = $lue['title'];
        }
        $items = [];
        foreach($loan_collection_statistics as $value){
            /* if ($value['order_level'] == 0) {
                 $order_level = LoanCollection::$groups[$value['loan_group']];
             }else{
                 $order_level = LoanCollection::$group[$value['order_level']];
             }*/
            $order_level = LoanCollectionOrder::$level[$value['order_level']];
            $order_group = LoanCollectionOrder::$level[$value['loan_group']];
            $arr = [
                '机构'=>$outsideinfo[$value['outside']] ?? '-',
                '催收员分组'=>$order_group,
                '催收员' => $value['username'],
                '订单分组' => $order_level,
                '本金总额' => number_format($value['total_money'] / 100,2),
                '还款本金总额'=>number_format($value['finish_total_money'] / 100,2),
                '当日分派金额'=>number_format( $value['today_get_total_money'] / 100,2),
                '当日还款本金'=>number_format($value['today_finish_total_money'] / 100,2),
                '剩余本金总额'=>number_format($value['no_finish_total_money'] / 100,2),
                '订单总数'=>$value['loan_total'],
                '还款总数' => number_format($value['finish_total']),
                '当日分派数' => $value['today_get_loan_total'],
                '当日还款数' => $value['today_finish_total'],
                '还款率' => sprintf("%.2f", $value['finish_total_rate']*100).'%',
                '迁徙率' => sprintf("%.2f", $value['no_finish_rate']*100).'%',
                '滞纳金总金额' => number_format( $value['late_fee_total'] / 100,2),
                '滞纳金收取金额' => number_format( $value['finish_late_fee'] / 100,2),
                '滞纳金回收率'=>sprintf("%.2f", $value['finish_late_fee_rate']*100).'%',
                '总催回金额' => number_format( $value['member_fee'] / 100,2),
                '每日处理量'=>number_format($value['operate_total']),
            ];
            if($setRealNameCollectionAdmin){
                $arr['real_name'] = $value['real_name'];
            }
            $items[] = $arr;

        }
        echo $this->_array2csv($items);
        exit;
    }
    /**
     * @name 催收员每日统计
     */
    public function actionLoanCollectionAdminWorkList(){
        $condition = $this->getAdminWorkListFilter();
        //判断是否为委外机构员工 如果是就只显示他们机构的情况
        $operator_id = Yii::$app->user->id;
        $user = AdminUser::findIdentity($operator_id);
        $is_outside = false;  //是自己人
        $outside = $this->request->get('outside');

        //取出所有启用的催收机构 默认是自营
        if($user && $user->outside > 0){
            $is_outside = true;
            $condition[] = ['outside' => $user->outside];
        }
        if($outside){
            $condition[] = ['outside' => $outside];
        }

        // 商户判断
        $nMerchantId = Yii::$app->request->get('merchant_id');

        if ($this->isNotMerchantAdmin && $nMerchantId >= 0 && $nMerchantId != '') {
            $condition[] = ['merchant_id' => $nMerchantId];
        } else {
            // 加上商户号判断
            if (is_array($this->merchantIds)) {
                $sMerchantIds = $this->merchantIds;
            } else {
                $sMerchantIds = explode(',', $this->merchantIds);
            }
            $condition[] = ['merchant_id' => $sMerchantIds];
        }

        $is_csv = false;
        if(Yii::$app->request->get('submitcsv') == 'exportcsv') $is_csv = true;
        $flag = 1;
        $sort = [];
        if($sort_by =$this->request->get('btn_sort'))
        {
            $flag = (int)$this->request->get('flag');
            if($flag%2 ==0)
            {
                $sort[$sort_by] = SORT_DESC;
            }
            else
            {
                $sort[$sort_by] = SORT_ASC;
            }
            ++$flag;
        }
        $sort['loan_group'] = SORT_ASC;
        $sort['admin_user_id'] = SORT_DESC;
        $query = LoanCollectionStatistics::queryCondition($condition,true,$sort);
        $countQuery = clone $query;
        $pages = new Pagination(['totalCount' => $countQuery->count('*')]);
        $page_size = Yii::$app->request->get('page_size',15);
        $pages->pageSize = $page_size;
        $outsideinfo = UserCompany::allOutsideRealName($this->merchantIds);
        //导出
        if($is_csv){
            $loan_collection_statistics = $query->asArray()->all(Yii::$app->get('db_assist_read'));
            $outsideinfo_outside = '所有';
            if (!empty($outside)) {
                $outsideinfo_outside = $outsideinfo[$outside];
            }
            $list = [$loan_collection_statistics,$outsideinfo_outside];
        }else{
            $loan_collection_statistics = $query->offset($pages->offset)->limit($pages->limit)->Asarray()->all();
            $list = [
                'loan_collection_statistics'=>$loan_collection_statistics,
                'outsides' => $outsideinfo,
                // 'loan_group'=>$loan_group,
                'pages' => $pages,

            ];
        }

        $setRealNameCollectionAdmin = GlobalSetting::checkCollectionUsernameCanSetRealName(Yii::$app->user->identity);
        if(Yii::$app->request->get('submitcsv') == 'exportcsv'){
            return $this->_exportAdminWorkInfos($list[0],$list[1],$setRealNameCollectionAdmin);
        }
        $loan_group = Yii::$app->request->get('loan_group',0);
        $order_level = Yii::$app->request->get('order_level',0);
        $group_game = Yii::$app->request->get('group_game',0);
        $total_all_money = 0;
        $total_all_total = 0;
        $total_yes_money = 0;
        $total_today_money = 0;
        $total_today_total = 0;
        $today_get_loan_total = 0;
        $today_get_total_money = 0;
        $total_yes_total = 0;
        $total_all_late = 0;
        $finish_fee = 0;
        $no_finish_fee = 0;
        $late_fee = 0;
        $total_yes_late = 0;
        $total_operate_total =  0 ;
        $member_fee = 0;
        $updated_time = 0;
        if(!empty($loan_group) || !empty($order_level) || !empty($group_game) || !empty($fund_id)){
            $all_data =  LoanCollectionStatistics::find()->where($condition)->all();
            if($all_data){
                foreach ($all_data as $val){
                    $total_all_money += $val['total_money'];
                    $total_all_total += $val['loan_total'];
                    $total_yes_money += $val['finish_total_money'];
                    $total_today_money += $val['today_finish_total_money'];
                    $total_today_total += $val['today_finish_total'];
                    $today_get_loan_total += $val['today_get_loan_total'];
                    $today_get_total_money += $val['today_get_total_money'];
                    $total_all_late += $val['late_fee_total'];
                    $total_yes_late += $val['finish_late_fee'];
                    $total_operate_total += $val['operate_total']  ;
                    $total_yes_total += $val['finish_total'];
                    $member_fee += isset($val['member_fee'])?$val['member_fee']:0;
                    $updated_time = $val['updated_at'];
                    $loan_group = $val['loan_group'];
                    $order_level = $val['order_level'];
                    $finish_fee = $total_all_money? $total_yes_money/$total_all_money:'0';
                    $no_finish_fee = 1-$finish_fee;
                    $late_fee = $total_all_late? $total_yes_late/$total_all_late : '0';

                }
            }
        }
        $teamList = CompanyTeam::getTeamsByOutside($outside);

        // 获取所有商户，只能是商户管理员的时候才在页面上显示
        if ($this->isNotMerchantAdmin) {
            $arrMerchant = Merchant::getMerchantId();
        } else {
            $arrMerchant = [];
        }

        return $this->render('loan-collection-statistics',[
            'loan_collection_statistics'=>$list['loan_collection_statistics'],
            'outsides' => $list['outsides'],
            // 'loan_group'=>$loan_group,
            'pages' => $list['pages'],
            'is_outside'=>$is_outside,
            'is_workdesc'=>false,
            'total_all_money'=>$total_all_money,
            'total_all_total'=>$total_all_total,
            'total_yes_money'=>$total_yes_money,
            'total_today_money'=>$total_today_money,
            'total_today_total'=>$total_today_total,
            'today_get_loan_total'=>$today_get_loan_total,
            'today_get_total_money'=>$today_get_total_money,
            'total_all_late'=>$total_all_late,
            'total_yes_late'=>$total_yes_late,
            'total_operate_total'=>$total_operate_total,
            'total_yes_total' =>$total_yes_total,
            'loan_group'=>$loan_group,
            'order_level'=>$order_level,
            'group_game'=>$group_game,
            'updated_time'=>$updated_time,
            'finish_fee'=>$finish_fee,
            'no_finish_fee'=>$no_finish_fee,
            'late_fee'=>$late_fee,
            'member_fee'=>$member_fee,
            'teamList' => $teamList,
            'merchant_id' => $this->merchantIds,
            'arrMerchant' => $arrMerchant,
            'flag'=> $flag,
            'setRealNameCollectionAdmin' => $setRealNameCollectionAdmin
        ]);
    }


    /**
     * 催收机构每日工作导出方法
     */
    private function _exportOutsideWorkInfos($outside_data,$oustide){
        $this->_setcsvHeader('催收机构每日工作列表导出.csv');
        $items = [];
        foreach($outside_data as $group){
            foreach ($group as $key=>$value) {
                // if ($key == LoanCollection::GROUP_S_TWO || $key == LoanCollection::GROUP_S_ONE) {
                foreach ($value as $val) {
                    if ($val['order_level'] == 0) {
                        $order_level = LoanCollectionOrder::$level[$val['loan_group']];
                    }else{
                        $order_level = LoanCollectionOrder::$level[$val['order_level']];
                    }
                    $items[] = [
                        '催收机构'=>$oustide[$val['outside_id']] ?? '--',
                        '催收员分组'=>LoanCollectionOrder::$level[$val['loan_group']],  //分组
                        '订单分组'=>$order_level,
                        '本金总额' => number_format($val['total_money'] / 100,2),
                        '当日分派金额'=>number_format( $val['today_get_total_money'] / 100,2),
                        '当日还款本金'=>number_format( $val['today_finish_total_money'] / 100,2),
                        '还款本金总额'=>number_format( $val['finish_total_money'] / 100,2),
                        '剩余本金总额'=>number_format( ($val['total_money']-$val['finish_total_money']) / 100,2),
                        '迁徙率' => sprintf("%.2f", $val['no_finish_rate']*100).'%',
                        '订单总数'=>$val['loan_total'],
                        '当日分派单数'=> $val['today_get_loan_total'],
                        '当日还款数' => $val['today_finish_total'],
                        '还款总数' => $val['finish_total'],
                        '还款率' => sprintf("%.2f", $val['finish_total_rate']*100).'%',
                        '滞纳金总金额' => number_format( $val['late_fee_total'] / 100,2),
                        '滞纳金收取金额' => number_format( $val['finish_late_fee'] / 100,2),
                        '滞纳金回收率'=>sprintf("%.2f", $val['finish_late_fee_rate']*100).'%',
                        '总催收金额' => number_format( $val['member_fee'] / 100,2),
                        '每日处理量'=>$val['operate_total'],
                    ];
                }

            }
        }
        // var_dump($items); die;
        echo $this->_array2csv($items);
        exit;
    }

    /*
    * 催收人员每日工作列表导出方法
    */
    private function _exportAdminTrack($loan_collection_statistics, $oustide, $setRealNameCollectionAdmin)
    {
        $this->_setcsvHeader($oustide . '催收人员每日工作列表导出.csv');
        $outside = UserCompany::getAll();
        $outsides = [];
        foreach ($outside as $val) {
            $outsides[$val['id']] = $val['title'];
        }
        $items = [];
        foreach ($loan_collection_statistics as $value) {
            $arr = [
                '日期' => date('Y-m-d',$value['dispatch_time']),
                '机构' => isset($outsides[$value['outside_id']])?$outsides[$value['outside_id']]: '--',
                '催收分组' => LoanCollectionOrder::$level[$value['loan_group']],
                '订单级别' => LoanCollectionOrder::$level[$value['order_level']],
                '姓名' => $value['admin_user_name'],
                '本金总额' => number_format($value['today_all_money'] / 100,2),
                '已还本金' => number_format($value['today_finish_money'] / 100,2),
                '剩余本金' => number_format($value['today_no_finish_money'] / 100,2),
                '总单数' => $value['loan_total'],
                '已还订单' => $value['loan_finish_total'],
                '还款率' => sprintf("%.2f", number_format(($value['today_finish_money']/$value['today_all_money']),4)*100).'%',
                '首日完成单数' => $value['oneday_total'],
                '首日完成率' => sprintf("%.2f", number_format(($value['oneday_money']/$value['today_all_money']),4)*100).'%',
                '完成滞纳金总金额' => number_format( $value['today_finish_late_fee'] / 100,2),
                '滞纳金收取金额' => number_format( $value['finish_late_fee'] / 100,2),
                '滞纳金回收率' => !empty($value['today_finish_late_fee'])?sprintf("%.2f", number_format(($value['finish_late_fee']/$value['today_finish_late_fee']),4)*100).'%':'--',
                '今日处理量' => number_format($value['operate_total']),
            ];
            if($setRealNameCollectionAdmin){
                $arr['real_name'] = $value['real_name'];
            }
            $items[] = $arr;
        }
        echo $this->_array2csv($items);
        exit;
    }
    /**
     * @name 催收员_每日跟踪
     */
    public function actionLoanCollectionAdminTrack()
    {
        $condition = $this->getAdminWorkListTrackFilter();
        $operator_id = Yii::$app->user->id;
        $user = AdminUser::findIdentity($operator_id);
        $is_outside = false;  //是自己人
        $outside = $this->request->get('outside');
        //取出所有启用的催收机构 默认是自营
        if($user && $user->outside > 0){
            $is_outside = true;
            $condition[] = ['outside_id' => $user->outside];
        }
        if($outside){
            $condition[] = ['outside_id' => $outside];
        }
        $flag = 1;
        $sort = [];
        if($sort_by =$this->request->get('btn_sort'))
        {
            $flag = (int)$this->request->get('flag');
            if($flag%2 ==0)
            {
                $sort[$sort_by] = SORT_DESC;
            }
            else
            {
                $sort[$sort_by] = SORT_ASC;
            }

            ++$flag;
        }
        $is_csv = false;
        $pageSize = Yii::$app->request->get('page_size',15);
        if (Yii::$app->request->get('submitcsv') == 'exportcsv') $is_csv = true;

        // 商户判断
        $nMerchantId = Yii::$app->request->get('merchant_id');

        if ($this->isNotMerchantAdmin && $nMerchantId >= 0 && $nMerchantId != '') {
            $condition[] = ['merchant_id' => $nMerchantId];
        } else {
            // 加上商户号判断
            if (is_array($this->merchantIds)) {
                $sMerchantIds = $this->merchantIds;
            } else {
                $sMerchantIds = explode(',', $this->merchantIds);
            }
            $condition[] = ['merchant_id' => $sMerchantIds];
        }

        $sort['admin_user_id'] = SORT_ASC;
        $sort['dispatch_time'] = SORT_DESC;
        $query = LoanCollectionStatisticNew::find()->where($condition)->orderBy($sort);
        $countQuery = clone $query;
        $pages = new Pagination(['totalCount' => $countQuery->count()]);
        $pages->pageSize = $pageSize;
        $outsideinfo = UserCompany::allOutsideRealName($this->merchantIds);
        //导出
        if($is_csv){
            $loan_collection_statistics = $query->asArray()->all(LoanCollectionStatisticNew::getDb_rd());
            $outsideinfo_outside = '所有';
            if (!empty($outside)) {
                $outsideinfo_outside = $outsideinfo[$outside];
            }
            $list = [$loan_collection_statistics,$outsideinfo_outside];
        }else{
            $loan_collection_statistics = $query->offset($pages->offset)->limit($pages->limit)->asArray()->all();
            $list =  [
                'loan_collection_statistics'=>$loan_collection_statistics,
                'outsides' => $outsideinfo,
                'pages' => $pages,
            ];
        }

        $sumDateData  = LoanCollectionStatisticNew::find()
            ->select([
                'FROM_UNIXTIME(dispatch_time,\'%Y-%m-%d\') as date',
                'sum(today_all_money) as today_all_money',
                'sum(today_finish_money) as today_finish_money',
                'sum(today_no_finish_money) as today_no_finish_money',
                'sum(loan_total) as loan_total',
                'sum(loan_finish_total) as loan_finish_total',
                'sum(all_late_fee) as all_late_fee',
                'sum(finish_late_fee) as finish_late_fee',
                'sum(operate_total) as operate_total',
                'sum(today_finish_late_fee) as today_finish_late_fee',
                'sum(oneday_total) as oneday_total',
                'sum(oneday_money) as oneday_money',
                'merchant_id'
            ])
            ->where($condition)
            ->groupBy('date')
            ->orderBy(['date' => SORT_DESC])
            ->asArray()
            ->all();
        foreach ($sumDateData as &$val){
            $val['Type'] = 2;
        }
        $sumData  = LoanCollectionStatisticNew::find()
            ->select([
                'sum(today_all_money) as today_all_money',
                'sum(today_finish_money) as today_finish_money',
                'sum(today_no_finish_money) as today_no_finish_money',
                'sum(loan_total) as loan_total',
                'sum(loan_finish_total) as loan_finish_total',
                'sum(all_late_fee) as all_late_fee',
                'sum(finish_late_fee) as finish_late_fee',
                'sum(operate_total) as operate_total',
                'sum(today_finish_late_fee) as today_finish_late_fee',
                'sum(oneday_total) as oneday_total',
                'sum(oneday_money) as oneday_money',
                'merchant_id'
            ])
            ->where($condition)
            ->asArray()
            ->all();
        foreach ($sumData as &$val){
            $val['Type'] = 1;
        }
        $dateData = array_merge($sumData,$sumDateData);
        $setRealNameCollectionAdmin = GlobalSetting::checkCollectionUsernameCanSetRealName(Yii::$app->user->identity);
        if (Yii::$app->request->get('submitcsv') == 'exportcsv') {
            return $this->_exportAdminTrack($list[0], $list[1],$setRealNameCollectionAdmin);
        }
        $teamList = CompanyTeam::getTeamsByOutside($outside);

        // 获取所有商户，只能是商户管理员的时候才在页面上显示
        if ($this->isNotMerchantAdmin) {
            $arrMerchant = Merchant::getMerchantId();
        } else {
            $arrMerchant = [];
        }

        return $this->render('track/collection-statistics-admin-track', [
            'dateData' => $dateData,
            'loan_collection_statistics' => $list['loan_collection_statistics'],
            'outsides' => $list['outsides'],
            'pages' => $list['pages'],
            'is_outside' => $is_outside,
            'flag'=> $flag,
            'teamList' => $teamList,
            'merchant_id' => $this->merchantIds,
            'arrMerchant' => $arrMerchant,
            'setRealNameCollectionAdmin' => $setRealNameCollectionAdmin
        ]);
    }


    /**
     * @name 催收员_累计跟踪
     */
    public function actionLoanCollectionAdminTotal()
    {
        $condition = $this->getAdminWorkListTrackFilter();
        //判断是否为委外机构员工 如果是就只显示他们机构的情况
        $operator_id = Yii::$app->user->id;
        $user = AdminUser::findIdentity($operator_id);
        $is_outside = false;  //是自己人
        $outside = $this->request->get('outside');
        //取出所有启用的催收机构 默认是自营
        if($user && $user->outside > 0){
            $is_outside = true;
            $condition[] = ['outside_id' => $user->outside];
        }
        if($outside){
            $condition[] = ['outside_id' => $outside];
        }

        // 商户判断
        $nMerchantId = Yii::$app->request->get('merchant_id');

        if ($this->isNotMerchantAdmin && $nMerchantId >= 0 && $nMerchantId != '') {
            $condition[] = ['merchant_id' => $nMerchantId];
        } else {
            // 加上商户号判断
            if (is_array($this->merchantIds)) {
                $sMerchantIds = $this->merchantIds;
            } else {
                $sMerchantIds = explode(',', $this->merchantIds);
            }
            $condition[] = ['merchant_id' => $sMerchantIds];
        }

        if (Yii::$app->request->get('submitcsv') == 'exportcsv') $is_csv = true;
        //排序
        $sort['admin_user_id'] = SORT_ASC;
        $sort['dispatch_time'] = SORT_DESC;
        $query = LoanCollectionStatisticNew::find()->where($condition)->orderBy($sort);
        $outsideinfo = UserCompany::allOutsideRealName($this->merchantIds);
        $loan_collection_statistics = $query->asArray()->all();
        $list = [$loan_collection_statistics,$outsideinfo,];
        if(!empty($list['0']))
        {
            foreach ($list['0'] as $key=>$value)
            {
                if(!isset($dataArr[$value['admin_user_id']]))
                {
                    $dataArr[$value['admin_user_id']]['today_all_money'] = 0;
                    $dataArr[$value['admin_user_id']]['today_finish_money'] = 0;
                    $dataArr[$value['admin_user_id']]['today_no_finish_money'] = 0;
                    $dataArr[$value['admin_user_id']]['loan_total'] = 0;
                    $dataArr[$value['admin_user_id']]['loan_finish_total'] = 0;
                    $dataArr[$value['admin_user_id']]['all_late_fee'] = 0;
                    $dataArr[$value['admin_user_id']]['finish_late_fee'] = 0;
                    $dataArr[$value['admin_user_id']]['operate_total'] = 0;
                    $dataArr[$value['admin_user_id']]['today_finish_late_fee'] = 0;
                    $dataArr[$value['admin_user_id']]['oneday_money'] = 0;
                    $dataArr[$value['admin_user_id']]['oneday_total'] = 0;
                    $dataArr[$value['admin_user_id']]['merchant_id'] = $value['merchant_id'];
                }
                $dataArr[$value['admin_user_id']]['admin_user_name'] = $value['admin_user_name'];
                $dataArr[$value['admin_user_id']]['real_name'] = $value['real_name'];
                $dataArr[$value['admin_user_id']]['loan_group'] = $value['loan_group'];
                $dataArr[$value['admin_user_id']]['outside_id'] = $value['outside_id'];
                $dataArr[$value['admin_user_id']]['order_level'] = $value['order_level'];
                $dataArr[$value['admin_user_id']]['today_all_money'] += $value['today_all_money'];
                $dataArr[$value['admin_user_id']]['today_finish_money'] += $value['today_finish_money'];
                $dataArr[$value['admin_user_id']]['today_no_finish_money'] += $value['today_no_finish_money'];
                $dataArr[$value['admin_user_id']]['loan_total'] += $value['loan_total'];
                $dataArr[$value['admin_user_id']]['loan_finish_total'] += $value['loan_finish_total'];
                $dataArr[$value['admin_user_id']]['all_late_fee'] += $value['all_late_fee'];
                $dataArr[$value['admin_user_id']]['finish_late_fee'] += $value['finish_late_fee'];
                $dataArr[$value['admin_user_id']]['operate_total'] += $value['operate_total'];
                $dataArr[$value['admin_user_id']]['today_finish_late_fee'] += $value['today_finish_late_fee'];
                $dataArr[$value['admin_user_id']]['oneday_money'] += $value['oneday_money'];
                $dataArr[$value['admin_user_id']]['oneday_total'] += $value['oneday_total'];
            }
        }
        else
        {
            $dataArr =[];
        }
        $result = $this->SortFilter($dataArr);
        $dataArr = !empty($result[1])?$result[1]:$dataArr;
        $setRealNameCollectionAdmin = GlobalSetting::checkCollectionUsernameCanSetRealName(Yii::$app->user->identity);
        if (Yii::$app->request->get('submitcsv') == 'exportcsv') {
            return $this->_exportAdminTotal($dataArr,$setRealNameCollectionAdmin);
        }
        $pages = new Pagination(['totalCount' => count($dataArr)]);
        $page = $this->request->get('page');
        $page = !empty($page)?$page:1;
        $page_size = Yii::$app->request->get('page_size',15);
        $pages->pageSize = $page_size;
        $data = array_values($dataArr);
        $offset = ($page-1)*$page_size;
        $arr = array_slice($data,$offset,$page_size);
        $teamList = CompanyTeam::getTeamsByOutside($outside);

        // 获取所有商户，只能是商户管理员的时候才在页面上显示
        if ($this->isNotMerchantAdmin) {
            $arrMerchant = Merchant::getMerchantId();
        } else {
            $arrMerchant = [];
        }


        return $this->render('track/collection-statistics-admin-total', [
            'loan_collection_statistics' => $arr,
            'outsides' => $list['1'],
            'pages' => $pages,
            'is_outside' => $is_outside,
            'flag' =>$result[0],
            'teamList' => $teamList,
            'merchant_id' => $this->merchantIds,
            'arrMerchant' => $arrMerchant,
            'setRealNameCollectionAdmin' => $setRealNameCollectionAdmin
        ]);
    }

    /*
   * 催收人员时间段工作列表导出方法
   */
    private function _exportAdminTotal($loan_collection_statistics,$setRealNameCollectionAdmin)
    {
        $this->_setcsvHeader( '所有催收人员时间段工作列表导出.csv');
        $outside = UserCompany::getAll();
        $outsides = [];
        foreach ($outside as $val) {
            $outsides[$val['id']] = $val['title'];
        }
        $items = [];
        foreach ($loan_collection_statistics as $value) {
            $arr = [
                '机构' => isset($outsides[$value['outside_id']])?$outsides[$value['outside_id']]: '--',
                '催收分组' => isset(LoanCollectionOrder::$level[$value['loan_group']])?LoanCollectionOrder::$level[$value['loan_group']]:'--',
                '订单级别' => isset(LoanCollectionOrder::$level[$value['order_level']])?LoanCollectionOrder::$level[$value['order_level']]:'--',
                '姓名' => $value['admin_user_name'],
                '本金总额' => number_format($value['today_all_money'] / 100,2),
                '已还本金' => number_format($value['today_finish_money'] / 100,2),
                '剩余本金' => number_format($value['today_no_finish_money'] / 100,2),
                '总单数' => $value['loan_total'],
                '已还订单' => $value['loan_finish_total'],
                '还款率' => sprintf("%.2f", number_format(($value['today_finish_money']/$value['today_all_money']),4)*100).'%',
                '首日完成单数'=> $value['oneday_total'],
                '首日完成率'=> sprintf("%.2f", number_format(($value['oneday_money']/$value['today_all_money']),4)*100).'%',
                '滞纳金总金额' => number_format( $value['today_finish_late_fee'] / 100,2),
                '滞纳金收取金额' => number_format( $value['finish_late_fee'] / 100,2),
                '滞纳金回收率' => !empty($value['today_finish_late_fee'])?sprintf("%.2f", number_format(($value['finish_late_fee']/$value['today_finish_late_fee']),4)*100).'%':'--',
                '今日处理量' => number_format($value['operate_total']),
            ];
            if($setRealNameCollectionAdmin){
                $arr['real_name'] = $value['real_name'];
            }
            $items[] = $arr;

        }
        echo $this->_array2csv($items);
        exit;
    }

    /**
     * 催收机构每日工作导出方法
     */
    private function _exportOutsideTrack($outsideData)
    {
        $this->_setcsvHeader('催收机构每日工作列表导出.csv');
        $items = [];
        foreach ($outsideData as $val) {
            $items[] = [
                '日期' => $val['date'],
                '机构' => $val['outside_id'],//$val['outside'],
                '催收分组' => isset(LoanCollectionOrder::$level[$val['loan_group']])?LoanCollectionOrder::$level[$val['loan_group']]:'--',
                '订单级别' => isset(LoanCollectionOrder::$level[$val['order_level']])?LoanCollectionOrder::$level[$val['order_level']]:'--',
                '本金总额' => number_format($val['today_all_money'] / 100,2),
                '已还本金' => number_format($val['today_finish_money'] / 100,2),
                '剩余本金' => number_format($val['today_no_finish_money'] / 100,2),
                '总单数' => $val['loan_total'],
                '已还订单' => $val['loan_finish_total'],
                '还款率' => sprintf("%.2f", number_format(($val['today_finish_money']/$val['today_all_money']),4)*100).'%',
                '首日完成率'=> sprintf("%.2f", number_format(($val['oneday_money']/$val['today_all_money']),4)*100).'%',
                '完成滞纳金总金额' => number_format( $val['today_finish_late_fee'] / 100,2),
                '滞纳金收取金额' => number_format( $val['finish_late_fee'] / 100,2),
                '滞纳金回收率' => !empty($val['today_finish_late_fee'])?sprintf("%.2f", number_format(($val['finish_late_fee']/$val['today_finish_late_fee']),4)*100).'%':'--',
                '今日处理量' => number_format($val['operate_total']),
            ];
        }
        echo $this->_array2csv($items);
        exit;
    }

    /**
     * 催收机构累计导出方法
     */
    private function _exportOutsideTotal($outside_data)
    {
        $this->_setcsvHeader('催收机构工作累计列表导出.csv');
        $items = [];
        foreach ($outside_data as $group) {
            foreach ($group as $key => $value) {
                foreach ($value as $val) {
                    $items[] = [
                        '机构' => $val['outside_id'],// $val['outside'],
                        '催收分组' => isset(LoanCollectionOrder::$level[$val['loan_group']])?LoanCollectionOrder::$level[$val['loan_group']]:'--',
                        '订单级别' => isset(LoanCollectionOrder::$level[$val['order_level']])?LoanCollectionOrder::$level[$val['order_level']]:'--',
                        '本金总额' => number_format($val['today_all_money'] / 100,2),
                        '已还本金' => number_format($val['today_finish_money'] / 100,2),
                        '剩余本金' => number_format($val['today_no_finish_money'] / 100,2),
                        '总单数' => $val['loan_total'],
                        '已还订单' => $val['loan_finish_total'],
                        '还款率' => sprintf("%.2f", number_format(($val['today_finish_money']/$val['today_all_money']),4)*100).'%',
                        '首日完成率'=> sprintf("%.2f", number_format(($val['oneday_money']/$val['today_all_money']),4)*100).'%',
                        '完成滞纳金总金额' => number_format( $val['today_finish_late_fee'] / 100,2),
                        '滞纳金收取金额' => number_format( $val['finish_late_fee'] / 100,2),
                        '滞纳金回收率' => !empty($val['today_finish_late_fee'])?sprintf("%.2f", number_format(($val['finish_late_fee']/$val['today_finish_late_fee']),4)*100).'%':'--',
                        '今日处理量' => number_format($val['operate_total']),
                    ];
                }

            }
        }
        echo $this->_array2csv($items);
        exit;
    }

    /**
     * @name 机构_每日跟踪
     * @return [type] [description]
     */
    public function actionLoanCollectionOutsideTrack()
    {
        $condition = $this->getAdminWorkListTrackFilter();
        //判断是否为委外机构员工 如果是就只显示他们机构的情况
        $operator_id = Yii::$app->user->id;
        $user = AdminUser::findIdentity($operator_id);
        $is_outside = false;  //是自己人
        $outside = $this->request->get('outside');
        //取出所有启用的催收机构 默认是自营
        if($user && $user->outside > 0){
            $is_outside = true;
            $condition[] = ['outside_id' => $user->outside];
        }
        if($outside){
            $condition[] = ['outside_id' => $outside];
        }

        // 商户判断
        $nMerchantId = Yii::$app->request->get('merchant_id');

        if ($this->isNotMerchantAdmin && $nMerchantId >= 0 && $nMerchantId != '') {
            $condition[] = ['merchant_id' => $nMerchantId];
        } else {
            // 加上商户号判断
            if (is_array($this->merchantIds)) {
                $sMerchantIds = $this->merchantIds;
            } else {
                $sMerchantIds = explode(',', $this->merchantIds);
            }
            $condition[] = ['merchant_id' => $sMerchantIds];
        }

        $pageSize = Yii::$app->request->get('page_size',15);
        //排序

        $flag = 1;
        $sort = [];
        if($sort_by =$this->request->get('btn_sort'))
        {
            $flag = (int)$this->request->get('flag');
            if($flag%2 ==0)
            {
                $sort[$sort_by] = SORT_DESC;
            }
            else
            {
                $sort[$sort_by] = SORT_ASC;
            }

            ++$flag;
        }
        $sort['date'] = SORT_DESC;
        $sort['outside_id'] = SORT_ASC;
        $sort['loan_group'] = SORT_ASC;
        $query = LoanCollectionStatisticNew::find()
            ->select([
                'outside_id',
                'order_level',
                'loan_group',
                'today_all_money' => 'sum(today_all_money)',
                'loan_total' => 'sum(loan_total)',
                'today_finish_money' => 'sum(today_finish_money)',
                'today_no_finish_money' => 'sum(today_no_finish_money)',
                'operate_total' => 'sum(operate_total)',
                'loan_finish_total' => 'sum(loan_finish_total)',
                'all_late_fee' => 'sum(all_late_fee)',
                'finish_late_fee' => 'sum(finish_late_fee)',
                'oneday_money' => 'sum(oneday_money)',
                'today_finish_late_fee' => 'sum(today_finish_late_fee)',
                'merchant_id',
                'date' => 'FROM_UNIXTIME(`dispatch_time`,"%Y-%m-%d")'])
            ->where($condition)
            ->groupBy(['date','outside_id','loan_group'])
            ->orderBy($sort);
        $countQuery = clone $query;
        $pages = new Pagination(['totalCount' => $countQuery->count()]);
        $pages->pageSize = $pageSize;

        //导出
        if ($this->request->get('submitcsv') == 'exportcsv') {
            $outsideData = $query->asArray()->all(LoanCollectionStatisticNew::getDb_rd());
            return $this->_exportOutsideTrack($outsideData);
        }
        $outsideData = $query->offset($pages->offset)->limit($pages->limit)->asArray()->all();

        $outsideinfo = UserCompany::allOutsideRealName($this->merchantIds);

        $sumDateData  = LoanCollectionStatisticNew::find()
            ->select([
                'FROM_UNIXTIME(dispatch_time,\'%Y-%m-%d\') as date',
                'sum(today_all_money) as today_all_money',
                'sum(today_finish_money) as today_finish_money',
                'sum(today_no_finish_money) as today_no_finish_money',
                'sum(loan_total) as loan_total',
                'sum(loan_finish_total) as loan_finish_total',
                'sum(all_late_fee) as all_late_fee',
                'sum(finish_late_fee) as finish_late_fee',
                'sum(operate_total) as operate_total',
                'sum(today_finish_late_fee) as today_finish_late_fee',
                'sum(oneday_money) as oneday_money'
            ])
            ->where($condition)
            ->groupBy('date')
            ->orderBy(['date' => SORT_DESC])
            ->asArray()
            ->all();
        foreach ($sumDateData as &$val){
            $val['Type'] = 2;
        }
        $sumData  = LoanCollectionStatisticNew::find()
            ->select([
                'sum(today_all_money) as today_all_money',
                'sum(today_finish_money) as today_finish_money',
                'sum(today_no_finish_money) as today_no_finish_money',
                'sum(loan_total) as loan_total',
                'sum(loan_finish_total) as loan_finish_total',
                'sum(all_late_fee) as all_late_fee',
                'sum(finish_late_fee) as finish_late_fee',
                'sum(operate_total) as operate_total',
                'sum(today_finish_late_fee) as today_finish_late_fee',
                'sum(oneday_money) as oneday_money'
            ])
            ->where($condition)
            ->asArray()
            ->all();
        foreach ($sumData as &$val){
            $val['Type'] = 1;
        }
        $dateData = array_merge($sumData,$sumDateData);
        $teamList = CompanyTeam::getTeamsByOutside($outside);

        // 获取所有商户，只能是商户管理员的时候才在页面上显示
        if ($this->isNotMerchantAdmin) {
            $arrMerchant = Merchant::getMerchantId();
        } else {
            $arrMerchant = [];
        }

        return $this->render('track/collection-statistics-outside-track', [
            'dateData' => $dateData,
            'outsides' => $outsideinfo,
            'outside_data' => $outsideData,
            'is_outside' => $is_outside,
            'pages'=>$pages,
            'flag'=>$flag,
            'teamList' => $teamList,
            'merchant_id' => $this->merchantIds,
            'arrMerchant' => $arrMerchant
        ]);
    }

    /**
     * @name 机构_累计跟踪
     * @return [type] [description]
     */
    public function actionLoanCollectionOutsideTotal()
    {
        $condition = $this->getAdminWorkListTrackFilter();
        $operator_id = Yii::$app->user->id;
        $user = AdminUser::findIdentity($operator_id);
        $is_outside = false;  //是自己人
        $outside = $this->request->get('outside');
        //取出所有启用的催收机构 默认是自营
        if($user && $user->outside > 0){
            $is_outside = true;
            $condition[] = ['outside_id' => $user->outside];
        }
        if($outside){
            $condition[] = ['outside_id' => $outside];
        }

        // 商户判断
        $nMerchantId = Yii::$app->request->get('merchant_id');

        if ($this->isNotMerchantAdmin && $nMerchantId >= 0 && $nMerchantId != '') {
            $condition[] = ['merchant_id' => $nMerchantId];
        } else {
            // 加上商户号判断
            if (is_array($this->merchantIds)) {
                $sMerchantIds = $this->merchantIds;
            } else {
                $sMerchantIds = explode(',', $this->merchantIds);
            }
            $condition[] = ['merchant_id' => $sMerchantIds];
        }

        if (Yii::$app->request->get('submitcsv') == 'exportcsv') $is_csv = true;
        //排序
        $sort['admin_user_id'] = SORT_ASC;
        $sort['dispatch_time'] = SORT_DESC;
        $query = LoanCollectionStatisticNew::find()->where($condition)->orderBy($sort);
        $outsideinfo = UserCompany::allOutsideRealName($this->merchantIds);
        $loan_collection_statistics = $query->asArray()->all();

        $list = [$loan_collection_statistics,$outsideinfo,];

        if($list[0])
        {
            foreach ($list[0] as $one_person) {
                $outside = $one_person['outside_id'];
                $loan_group = $one_person['loan_group'];
                $order_level = $one_person['order_level'];
                if(!isset($outside_data[$outside][$loan_group][$order_level])){
                    $outside_data[$outside][$loan_group][$order_level]=[
                        'outside'=>isset($outsideinfo[$outside])?$outsideinfo[$outside]:'--',  //机构
                        'outside_id'=>$outside,  //机构
                        'loan_group'=>$loan_group,       //分组
                        'order_level'=> $order_level,    //订单级别
                        'today_all_money'=>0,            //本金总额
                        'loan_total'=>0,                 //总单数
                        'today_finish_money'=>0,         //今日还款本金总额
                        'today_no_finish_money'=>0,      //未完成金额
                        'operate_total'=>0,              //处理过的订单个数，包括有催收记录的订单（last_collection_time不为空）
                        'loan_finish_total'=>0,          //当日还款单数
                        'all_late_fee'=>0,               //滞纳金总额
                        'finish_late_fee'=>0,            //滞纳金收取金额  已收取的滞纳金总额
                        'oneday_money'=>0,            //首日完成金额
                        'today_finish_late_fee'=>0,
                        'merchant_id' => $one_person['merchant_id']
                    ];
                }
                //本金总额  总单数  处理过的订单个数
                $outside_data[$outside][$loan_group][$order_level]['loan_total'] += $one_person['loan_total'];
                $outside_data[$outside][$loan_group][$order_level]['operate_total'] += $one_person['operate_total'];
                //还款本金总额  还款总数  催收成功的单子的本金总额  滞纳金收取金额  本应缴纳的滞纳金总额
                $outside_data[$outside][$loan_group][$order_level]['today_finish_money'] += $one_person['today_finish_money'];
                $outside_data[$outside][$loan_group][$order_level]['today_all_money'] += $one_person['today_all_money'];
                $outside_data[$outside][$loan_group][$order_level]['today_no_finish_money'] += $one_person['today_no_finish_money'];
                $outside_data[$outside][$loan_group][$order_level]['all_late_fee'] += $one_person['all_late_fee'];     //实际还款金额-本金
                $outside_data[$outside][$loan_group][$order_level]['finish_late_fee'] += $one_person['finish_late_fee'];
                //今日还款本金总额  当日还款单数
                $outside_data[$outside][$loan_group][$order_level]['loan_finish_total'] += $one_person['loan_finish_total'];
                $outside_data[$outside][$loan_group][$order_level]['today_finish_late_fee'] += $one_person['today_finish_late_fee'];
                $outside_data[$outside][$loan_group][$order_level]['oneday_money'] += $one_person['oneday_money'];
            }
        }else
        {
            $outside_data = [];
        }

        //导出
        if ($this->request->get('submitcsv') == 'exportcsv') {
            return $this->_exportOutsideTotal($outside_data);
        }
        $regroup = [];
        foreach ($outside_data as $val1)
        {
            foreach ($val1 as $val2 )
            {
                foreach ($val2 as $val3)
                {
                    $regroup[] = $val3;
                }
            }
        }
        $result = $this->SortFilter($regroup); //排序
        $loan_group = Yii::$app->request->get('loan_group',0);
        $order_level = Yii::$app->request->get('order_level',0);
        $group_game = Yii::$app->request->get('group_game',0);
        $total = $this->_TotalData($regroup,$loan_group,$order_level,$group_game);
        $regroup = !empty($result[1])?$result[1]:$regroup;
        $pages = new Pagination(['totalCount' => count($regroup)]);
        $page = $this->request->get('page');
        $page_size = Yii::$app->request->get('page_size',15);
        $pages->pageSize = $page_size;
        $page = !empty($page)?$page:1;
        $offset = ($page-1)*$page_size;
        $regroup = array_values($regroup);
        $arr = array_slice($regroup,$offset,$page_size);
        $teamList = CompanyTeam::getTeamsByOutside($outside);

        // 获取所有商户，只能是商户管理员的时候才在页面上显示
        if ($this->isNotMerchantAdmin) {
            $arrMerchant = Merchant::getMerchantId();
        } else {
            $arrMerchant = [];
        }

        return $this->render('track/collection-statistics-outside-total', [
            'outsides' => $outsideinfo,
            'outside_data' => $arr,
            'is_outside' => $is_outside,
            'pages'=>$pages,
            'flag'=>$result[0],
            'loan_group'=>$loan_group,
            'order_level'=>$order_level,
            'group_game'=>$group_game,
            'result'=>$total,
            'teamList' => $teamList,
            'merchant_id' => $this->merchantIds,
            'arrMerchant' => $arrMerchant
        ]);
    }

    /**
     * @name 机构_每日统计
     * @return [type] [description]
     */
    public function actionLoanCollectionOutsideWorkList(){
        $condition = $this->getAdminWorkListFilter();
        $is_outside = false;  //是自己人
        $outside = $this->request->get('outside');
        //取出所有启用的催收机构 默认是自营
        if($outside){
            $condition[] = ['outside' => $outside];
        }

        // 商户判断
        $nMerchantId = Yii::$app->request->get('merchant_id');

        if ($this->isNotMerchantAdmin && $nMerchantId >= 0 && $nMerchantId != '') {
            $condition[] = ['merchant_id' => $nMerchantId];
        } else {
            // 加上商户号判断
            if (is_array($this->merchantIds)) {
                $sMerchantIds = $this->merchantIds;
            } else {
                $sMerchantIds = explode(',', $this->merchantIds);
            }
            $condition[] = ['merchant_id' => $sMerchantIds];
        }

        $loan_collection_statistics = LoanCollectionStatistics::find()
            ->where($condition)
            ->orderBy(['loan_group'=>SORT_ASC])
            ->all();

        //获取催收机构列表
        $outsideinfo = UserCompany::allOutsideRealName($this->merchantIds);
        if ($loan_collection_statistics) {
            foreach ($loan_collection_statistics as $one_person) {
                $outside = $one_person->outside;
                $loan_group = $one_person->loan_group;
                $order_level = $one_person->order_level;
                //计算展期金额
                $dis_money = $one_person->dis_money;
                if(!isset($outside_data[$loan_group][$order_level][$outside])){
                    $outside_data[$loan_group][$order_level][$outside]=[
                        'outside'=>$outsideinfo[$outside]??'--',  //机构
                        'outside_id'=>$outside,  //机构
                        'loan_group'=>$loan_group,  //分组
                        'order_level'=> $order_level,  //订单级别
                        'total_money'=>0,       //本金总额
                        'loan_total'=>0,        //总单数
                        'today_finish_total_money'=>0,      //今日还款本金总额
                        'finish_total_money'=>0,    //还款本金总额
                        'no_finish_total_money'=>0,    //剩余本金总额
                        'operate_total'=>0,    //处理过的订单个数，包括有催收记录的订单（last_collection_time不为空）
                        'today_finish_total'=>0,        //当日还款单数
                        'finish_total'=>0,          //还款总数
                        'finish_total_rate'=>0,          //还款率  还款个数/订单个数
                        'no_finish_rate'=>0,          //迁徙率  剩余本金/本金总额
                        'finish_late_fee'=>0,          //滞纳金收取金额  已收取的滞纳金总额
                        'late_fee_total'=>0,          //本应缴纳的滞纳金总额
                        'finish_late_fee_rate'=>0,        //滞纳金回收率  滞纳金收取金额/本应缴纳的滞纳金总额
                        'huankuan_total_money'=>0,
                        'today_get_total_money'=>0,
                        'today_get_loan_total'=>0,
                        'dis_total_money'=>0,       //含展期总金额
                        'dis_finish_money'=>0,  //含展期完成金额
                        'dis_finish_rate'=>0,   //含展期还款率
                        'member_fee'=>0,
                        'created_at'=>0,
                        'merchant_id' => $one_person['merchant_id']
                    ];//拨打量 通话数没有统计
                }
                //本金总额  总单数  处理过的订单个数
                $outside_data[$loan_group][$order_level][$outside]['total_money'] += $one_person['total_money'];
                $outside_data[$loan_group][$order_level][$outside]['dis_total_money'] += $one_person['total_money']+$dis_money;
                $outside_data[$loan_group][$order_level][$outside]['loan_total'] += $one_person['loan_total'];
                $outside_data[$loan_group][$order_level][$outside]['operate_total'] += $one_person['operate_total'];
                $outside_data[$loan_group][$order_level][$outside]['member_fee'] += $one_person['member_fee'];
                //还款本金总额  还款总数  催收成功的单子的本金总额  滞纳金收取金额  本应缴纳的滞纳金总额
                $outside_data[$loan_group][$order_level][$outside]['finish_total_money'] += $one_person['finish_total_money'];
                $outside_data[$loan_group][$order_level][$outside]['finish_total'] += $one_person['finish_total'];
                $outside_data[$loan_group][$order_level][$outside]['huankuan_total_money'] += $one_person['huankuan_total_money'];
                $outside_data[$loan_group][$order_level][$outside]['dis_finish_money'] += $one_person['huankuan_total_money']+$dis_money;
                $outside_data[$loan_group][$order_level][$outside]['finish_late_fee'] += $one_person['finish_late_fee'];     //实际还款金额-本金
                $outside_data[$loan_group][$order_level][$outside]['late_fee_total'] += $one_person['late_fee_total'];
                //今日还款本金总额  当日还款单数
                $outside_data[$loan_group][$order_level][$outside]['today_finish_total_money'] += $one_person['today_finish_total_money'];
                $outside_data[$loan_group][$order_level][$outside]['today_finish_total'] += $one_person['today_finish_total'];
                //剩余本金总额
                $outside_data[$loan_group][$order_level][$outside]['no_finish_total_money'] += $one_person['no_finish_total_money'];
                $outside_data[$loan_group][$order_level][$outside]['today_get_total_money'] += $one_person['today_get_total_money'];
                $outside_data[$loan_group][$order_level][$outside]['today_get_loan_total'] += $one_person['today_get_loan_total'];
                $outside_data[$loan_group][$order_level][$outside]['created_at'] = $one_person['created_at'];
            }
            //统计 各种率
            foreach($outside_data as $kk=>$one_outside){
                foreach ($one_outside as $key=>$one_group) {
                    foreach ($one_group as $k => $val) {
                        //还款率(含展期)  还款本金总额/本金总额
                        if($outside_data[$kk][$key][$k]['dis_total_money']){
                            $outside_data[$kk][$key][$k]['dis_finish_rate'] =sprintf("%0.4f",$outside_data[$kk][$key][$k]['dis_finish_money']/$outside_data[$kk][$key][$k]['dis_total_money']);
                        }else{
                            $outside_data[$kk][$key][$k]['dis_finish_rate'] = "0.00";
                        }
                        //还款率  还款本金总额/本金总额
                        if($outside_data[$kk][$key][$k]['loan_total']){
                            $outside_data[$kk][$key][$k]['finish_total_rate'] =sprintf("%0.4f",$outside_data[$kk][$key][$k]['huankuan_total_money']/$outside_data[$kk][$key][$k]['total_money']);
                        }else{
                            $outside_data[$kk][$key][$k]['finish_total_rate'] = "0.00";
                        }
                        //迁徙率  剩余本金/本金总额
                        if($outside_data[$kk][$key][$k]['total_money']){
                            $outside_data[$kk][$key][$k]['no_finish_rate'] =sprintf("%0.4f",$outside_data[$kk][$key][$k]['no_finish_total_money']/$outside_data[$kk][$key][$k]['total_money']);
                        }else{
                            $outside_data[$kk][$key][$k]['no_finish_rate'] = "0.00";
                        }
                        //滞纳金回收率  滞纳金收取金额/本应缴纳的滞纳金总额
                        if($outside_data[$kk][$key][$k]['late_fee_total']){
                            $outside_data[$kk][$key][$k]['finish_late_fee_rate'] =sprintf("%0.4f",$outside_data[$kk][$key][$k]['finish_late_fee']/$outside_data[$kk][$key][$k]['late_fee_total']);
                        }else{
                            $outside_data[$kk][$key][$k]['finish_late_fee_rate'] = "0.00";
                        }
                    }
                }
            }
        }else{
            $outside_data = [];
        }
        $list = ['outside_data'=>$outside_data,'outsideinfo'=>$outsideinfo];
        $outside_data = $list['outside_data'];
        $outsideinfo = $list['outsideinfo'];
        $btn_sort = $this->request->get('btn_sort');
        $sort_type = '';
        $is_sort = false;
        if (!empty($btn_sort)) {
            $sort_arr = [];
            $sort_type = $this->request->get('sort_type');
            foreach ($outside_data as $key => $data) {
                if ($sort_type === 'desc') {
                    foreach ($data as $key => $value) {
                        if ($btn_sort === 'finish_total_rate') {
                            usort($value, function($a, $b){
                                return $a['finish_total_rate']*1000 > $b['finish_total_rate']*1000 ? -1 : 1;//还款率排序
                            });
                        }
                        if ($btn_sort === 'no_finish_rate') {
                            usort($value, function($a, $b){
                                return $a['no_finish_rate']*1000 > $b['no_finish_rate']*1000 ? -1 : 1;//迁徙率排序
                            });
                        }
                        $sort_arr[] = $value;
                    }
                }else{
                    foreach ($data as $key => $value) {
                        if ($btn_sort === 'finish_total_rate') {
                            usort($value, function($a, $b){
                                return $a['finish_total_rate']*1000 > $b['finish_total_rate']*1000 ? 1 : -1;//还款率排序
                            });
                        }
                        if ($btn_sort === 'no_finish_rate') {
                            usort($value, function($a, $b){
                                return $a['no_finish_rate']*1000 > $b['no_finish_rate']*1000 ? 1 : -1;//迁徙率排序
                            });
                        }
                        $sort_arr[] = $value;
                    }
                }
            }
            $outside_data = $sort_arr;
            $is_sort = true;
            $sort_type = $sort_type === 'desc' ? 'asc' : 'desc';
        }
        $loan_group = Yii::$app->request->get('loan_group',0);
        $order_level = Yii::$app->request->get('order_level',0);
        $group_game = Yii::$app->request->get('group_game',0);
        $total_all_money = 0;
        $total_all_total = 0;
        $total_yes_money = 0;
        $total_today_money = 0;
        $total_today_total = 0;
        $today_get_loan_total = 0;
        $today_get_total_money = 0;
        $total_yes_total = 0;
        $total_all_late = 0;
        $finish_fee = 0;
        $no_finish_fee = 0;
        $late_fee = 0;
        $total_yes_late = 0;
        $member_fee = 0;
        $total_operate_total =  0 ;
        $updated_time = 0;
        if(!empty($loan_group) || !empty($order_level) || !empty($group_game) || !empty($outside) || !empty($fund_id)){
            $all_data =  LoanCollectionStatistics::find()
                ->where($condition)
                ->orderBy(['loan_group'=>SORT_ASC])
                ->all();
            if($all_data){
                foreach ($all_data as $val){
                    $total_all_money += $val['total_money'];
                    $total_all_total += $val['loan_total'];
                    $total_yes_money += $val['finish_total_money'];
                    $total_today_money += $val['today_finish_total_money'];
                    $total_today_total += $val['today_finish_total'];
                    $today_get_loan_total += $val['today_get_loan_total'];
                    $today_get_total_money += $val['today_get_total_money'];
                    $total_all_late += $val['late_fee_total'];
                    $total_yes_late += $val['finish_late_fee'];
                    $total_operate_total += $val['operate_total']  ;
                    $total_yes_total += $val['finish_total'];
                    $updated_time = $val['updated_at'];
                    $loan_group = $val['loan_group'];
                    $order_level = $val['order_level'];
                    $member_fee+= isset($val['member_fee'])?$val['member_fee']:0;
                    $finish_fee = $total_all_money? $total_yes_money/$total_all_money:'0';
                    $no_finish_fee = 1-$finish_fee;
                    $late_fee = $total_all_late? $total_yes_late/$total_all_late : '0';

                }
            }
        }
        //导出
        if($this->request->get('submitcsv') == 'exportcsv'){
            return $this->_exportOutsideWorkInfos($outside_data,$outsideinfo);
        }
        $teamList = CompanyTeam::getTeamsByOutside($outside);

        // 获取所有商户，只能是商户管理员的时候才在页面上显示
        if ($this->isNotMerchantAdmin) {
            $arrMerchant = Merchant::getMerchantId();
        } else {
            $arrMerchant = [];
        }

        return $this->render('outside-statistics',[
            'outsides'=>$outsideinfo,
            'outside_data'=>$outside_data,
            'is_outside'=>$is_outside,
            'is_sort'=>$is_sort,
            'sort_type'=>$sort_type,
            'is_workdesc'=>false,
            'total_all_money'=>$total_all_money,
            'total_all_total'=>$total_all_total,
            'total_yes_money'=>$total_yes_money,
            'total_today_money'=>$total_today_money,
            'total_today_total'=>$total_today_total,
            'today_get_loan_total'=>$today_get_loan_total,
            'today_get_total_money'=>$today_get_total_money,
            'total_all_late'=>$total_all_late,
            'total_yes_late'=>$total_yes_late,
            'total_operate_total'=>$total_operate_total,
            'total_yes_total' =>$total_yes_total,
            'loan_group'=>$loan_group,
            'order_level'=>$order_level,
            'group_game'=>$group_game,
            'outside'=>$outside,
            'updated_time'=>$updated_time,
            'finish_fee'=>$finish_fee,
            'no_finish_fee'=>$no_finish_fee,
            'late_fee'=>$late_fee,
            'member_fee'=>$member_fee,
            'teamList' =>  $teamList,
            'merchant_id' => $this->merchantIds,
            'arrMerchant' => $arrMerchant
        ]);
    }

    /**
     *@name 催收订单统计(未实现)
     */
    public function actionOrderStatusAndGroup(){
        try {
            $get = $this->request->get();
            if (isset($get['start']) && isset($get['end'])) {
                $start = $get['start'] / 1000;//将毫秒转换成秒
                $end = $get['end'] / 1000;
            } else {
                $start = strtotime(date('Y-m-d 0:0:0'));
                $end = strtotime(date('Y-m-d 23:59:59'));
            }
            $time = $this->request->get('time', '');

            // 加上商户号判断
            if (is_array($this->merchantIds)) {
                $sMerchantIds = $this->merchantIds;
            } else {
                $sMerchantIds = explode(',', $this->merchantIds);
            }

            $total = OrderStatisticsByStatus::total($time, $sMerchantIds);
            $groupTotal = OrderStatisticsByGroup::total($time, $sMerchantIds);
            $groupTotal2 = OrderStatisticsByGroup::total_2($sMerchantIds);
            $daily = OrderStatisticsByDay::lists($start, $end, $sMerchantIds);

            $groupTotalData = [];
            foreach ($groupTotal as $group => $value){
                $groupTotalData[$group]['totalAmount'] = 0;
                $groupTotalData[$group]['totalPrincipal'] = 0;
                foreach ($value as $status => $val){
                    $groupTotalData[$group][$status]['amount'] =  $val['amount'];
                    $groupTotalData[$group][$status]['principal'] =  $val['principal'];
                    $groupTotalData[$group]['totalAmount'] += $val['amount'];
                    $groupTotalData[$group]['totalPrincipal'] += $val['principal'];
                }
            }
            $groupTotalData2 = [];
            foreach ($groupTotal2 as $group => $value){
                $groupTotalData2[$group]['totalAmount'] = 0;
                $groupTotalData2[$group]['totalPrincipal'] = 0;
                foreach ($value as $status => $val){
                    $groupTotalData2[$group][$status]['amount'] =  $val['amount'];
                    $groupTotalData2[$group][$status]['principal'] =  $val['principal'];
                    $groupTotalData2[$group]['totalAmount'] += $val['amount'];
                    $groupTotalData2[$group]['totalPrincipal'] += $val['principal'];
                }
            }
            return $this->render('order-status-and-group', array(
                'yesterday'=>$total,
                'groupTotal'=>$groupTotal,
                'groupTotalData' => $groupTotalData,
                'groupTotalData2' => $groupTotalData2,
                'groupTotal2'=>$groupTotal2,
                'daily'=>$daily,
            ));
        }catch(\Exception $e){
            echo $e->getFile().$e->getLine().$e->getMessage();
        }
    }

    /**
     * @name CollectionStatisticsController 逾期天数出催率(按单数)
     * @return string
     */
    public function actionInputOverdueDayOut(){
       return self::inputOverdueDayOut();
    }

    /**
     * @name CollectionStatisticsController 逾期天数出催率(按单数-全标签)
     * @return string
     */
    public function actionInputOverdueDayOutAllLabel(){
        return self::inputOverdueDayOut(1);
    }

    //逾期天数出催率按单数
    private function inputOverdueDayOut($isAllLabel = 1){
        $isShowOverApr = false;
        if(Yii::$app->user->getId() == 2){
            $isShowOverApr = true;
        }

        $condition[] = 'and';
        $startDate = $this->request->get('start_date',date('Y-m-d',strtotime('-10 day')));
        $endDate = $this->request->get('end_date',date('Y-m-d'));
        $userType = $this->request->get('user_type',0);
        $package_name = $this->request->get('package_name',[]);
        $pageSize = Yii::$app->request->get('page_size',15);
        $condition[] = ['user_type' => intval($userType)];
        // 商户判断
        $nMerchantId = Yii::$app->request->get('merchant_id');

        if ($this->isNotMerchantAdmin && $nMerchantId >= 0 && $nMerchantId != '') {
            $condition[] = ['merchant_id' => explode(',', $nMerchantId)];
        } else {
            // 加上商户号判断
            if (is_array($this->merchantIds)) {
                $sMerchantIds = $this->merchantIds;
            } else {
                $sMerchantIds = explode(',', $this->merchantIds);
            }
            $condition[] = ['merchant_id' => $sMerchantIds];
        }

        if(!empty($package_name)){
            $condition[] = ['package_name' => $package_name];
        }
        $condition2 = $condition;
        if (!empty($startDate)) {
            $condition[] = ['>=', 'date', $startDate];
        }
        if (!empty($endDate)) {
            $condition[] = ['<=', 'date', $endDate];
        }
        $select = [
            'sum(input_count) as input_count',
            'sum(overday_total_count) as overday_total_count',
        ];

        foreach (LoanCollectionOrder::$level as $lv => $val){
            if($lv >= LoanCollectionOrder::LEVEL_M2){
                $select[] = 'sum(overlevel'.$lv.'_count) as overlevel'.$lv.'_count';
            }
        }
        for ($day = 1; $day <= 30; $day++){
            $select[] = 'sum(overday'.$day.'_count) as overday'.$day.'_count';
        }
        $query = InputOverdayOut::find()
            ->where($condition);
        $totalQuery = clone $query;

        $totalData = $totalQuery->select($select)->asArray()->one();
        $select[] = 'date';
        $list = $query
            ->select($select)
            ->groupBy('date')
            ->orderBy(['date' => SORT_DESC])
            ->asArray()
            ->all();
        $totalInputData = [];
        InputOverdueOutService::setTotalInputCountData($list,$totalData,$totalInputData);


        if (!empty($startDate)) {
            $condition2[] = ['>=', 'date', date('Y-m-d', strtotime($startDate) - 86400)];
        }
        if (!empty($endDate)) {
            $condition2[] = ['<=', 'date', date('Y-m-d', strtotime($endDate) - 86400)];
        }
        $data =  [];
        $totalExpireNum = 0;
        $totalRepayNum   = 0;
        $totalRepayZcNum = 0;
        $type = 0;
        if($isShowOverApr){
            $info = StatisticsDayData::find()
                ->where($condition2)
                ->select("sum(expire_num) as expire_num,
                          sum(repay_num) as repay_num,
                          sum(repay_zc_num) as repay_zc_num,
                          date")
                ->groupBy('date')
                ->orderBy('date DESC')
                ->asArray()
                ->all();
            foreach($info as $value){
                $date=$value['date'];
                //按天
                $expire_num = $value['expire_num'] ?? 0;
                $repay_num = $value['repay_num'] ?? 0;
                $repay_zc_num = $value['repay_zc_num'] ?? 0;
                $totalExpireNum += $expire_num;
                $totalRepayNum  += $repay_num;
                $totalRepayZcNum+= $repay_zc_num;
                $data[$date]['expire_num_'.$type] = $expire_num;
                $data[$date]['repay_num_'.$type] = $repay_num;
                $data[$date]['repay_zc_num_'.$type] = $repay_zc_num;
            }
        }
        $totalData['expire_num_'.$type]  = $totalExpireNum;
        $totalData['repay_num_'.$type]  = $totalRepayNum;
        $totalData['repay_zc_num_'.$type]  = $totalRepayZcNum;

        // 获取所有商户，只能是商户管理员的时候才在页面上显示
        if ($this->isNotMerchantAdmin) {
            $arrMerchant = Merchant::getMerchantId();
        } else {
            $arrMerchant = [];
        }

        return $this->render('input-overdue-day-out', array(
            'list'=>$list,
            'data' => $data,
            'totalData' => $totalData,
            'totalInputData' => $totalInputData,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'arrMerchant' => $arrMerchant,
            'packageNameList' =>   PackageSetting::getAllLoanPackageNameMap($this->merchantIds),
            'isShowOverApr' => $isShowOverApr,
            'user_type_map' => $isAllLabel ? InputOverdayOut::$all_user_type_map : InputOverdayOut::$user_type_map
        ));
    }

    /**
     * @name CollectionStatisticsController 逾期天数出催率(按单数图表)
     * @return string
     */
    public function actionInputOverdueDayOutChart(){
        $isShowOverApr = false;
        if(Yii::$app->user->getId() == 2){
            $isShowOverApr = true;
        }
        $condition[] = 'and';
        $startDate = $this->request->get('start_date',date('Y-m-d',strtotime('-15 day')));
        $endDate = $this->request->get('end_date',date('Y-m-d'));
        $userType = $this->request->get('user_type',0);
        $package_name = $this->request->get('package_name',[]);
        $condition[] = ['user_type' => intval($userType)];
        // 加上商户号判断
        if (is_array($this->merchantIds)) {
            $sMerchantIds = $this->merchantIds;
        } else {
            $sMerchantIds = explode(',', $this->merchantIds);
        }
        $condition[] = ['merchant_id' => $sMerchantIds];

        if(!empty($package_name)){
            $condition[] = ['package_name' => $package_name];
        }
        $condition2 = $condition;
        if (!empty($startDate)) {
            $condition[] = ['>=', 'date', $startDate];
        }
        if (!empty($endDate)) {
            $condition[] = ['<=', 'date', $endDate];
        }

        $select = [
            'date',
            'sum(input_count) as input_count',
            'sum(overday_total_count) as overday_total_count',
        ];

        foreach (LoanCollectionOrder::$level as $lv => $val){
            if($lv >= LoanCollectionOrder::LEVEL_M2){
                $select[] = 'sum(overlevel'.$lv.'_count) as overlevel'.$lv.'_count';
            }
        }
        for ($day = 1; $day <= 30; $day++){
            $select[] = 'sum(overday'.$day.'_count) as overday'.$day.'_count';
        }
        $query = InputOverdayOut::find()
            ->select($select)
            ->where($condition)
            ->groupBy('date')
            ->orderBy(['date' => SORT_ASC]);

        $list = $query
            ->asArray()
            ->all();
        InputOverdueOutService::setChatCountLevelByDayData($list);
        if (!empty($startDate)) {
            $condition2[] = ['>=', 'date', date('Y-m-d', strtotime($startDate) - 86400)];
        }
        if (!empty($endDate)) {
            $condition2[] = ['<=', 'date', date('Y-m-d', strtotime($endDate) - 86400)];
        }

        $data =  [];
        $totalExpireNum = 0;
        $totalRepayNum   = 0;
        $totalRepayZcNum = 0;
        $type = 0;
        if($isShowOverApr){
            $info = StatisticsDayData::find()
                ->where($condition2)
                ->select("sum(expire_num) as expire_num,
                          sum(repay_num) as repay_num,
                          sum(repay_zc_num) as repay_zc_num,
                          date")
                ->groupBy('date')
                ->orderBy('date DESC')
                ->asArray()
                ->all();
            foreach($info as $value){
                $date=$value['date'];
                //按天
                $expire_num = $value['expire_num'] ?? 0;
                $repay_num = $value['repay_num'] ?? 0;
                $repay_zc_num = $value['repay_zc_num'] ?? 0;
                $totalExpireNum += $expire_num;
                $totalRepayNum  += $repay_num;
                $totalRepayZcNum+= $repay_zc_num;
                $data[$date]['expire_num_'.$type] = $expire_num;
                $data[$date]['repay_num_'.$type] = $repay_num;
                $data[$date]['repay_zc_num_'.$type] = $repay_zc_num;
            }
        }

        $totalData['expire_num_'.$type]  = $totalExpireNum;
        $totalData['repay_num_'.$type]  = $totalRepayNum;
        $totalData['repay_zc_num_'.$type]  = $totalRepayZcNum;

        $xDate = [];
        //图表上方说明
        if($isShowOverApr){
            $legendTotal = ['首逾','逾期率','累计催回率','D1','D1-3','D4','D4-7','S1','S2','S1+S2','M1','M2','M3','M3+'];
        }else{
            $legendTotal = ['累计催回率','D1','D1-3','D4','D4-7','S1','S2','S1+S2','M1','M2','M3','M3+'];
        }
        $seriesTotal = [];
        foreach ($legendTotal as $key => $value){
            $seriesTotal[$key] = ['name'=> $value,'type' => 'line','data'=>[]];
        }

        foreach ($list as $k => $value){
            $xDate[] = $value['date'];
            $arr = [];
            if($isShowOverApr){
                $before = date('Y-m-d',strtotime($value['date']) - 86400);
                $arr[] = isset($data[$before]) ? ((empty($data[$before]['expire_num_0']) ? '0' : number_format(($data[$before]['expire_num_0'] - $data[$before]['repay_zc_num_0']) / $data[$before]['expire_num_0'] * 100,2))) : '0';
                $arr[] = isset($data[$before]) ? ((empty($data[$before]['expire_num_0']) ? '0' : number_format(($data[$before]['expire_num_0'] - $data[$before]['repay_num_0']) / $data[$before]['expire_num_0'] * 100,2))) : '0';
            }
            $arr[] = $value['input_count'] == 0 ? 0 : number_format($value['overday_total_count'] / $value['input_count'] * 100,2);
            $arr[] = $value['input_count'] == 0 ? 0 : number_format($value['overday_total_count'] / $value['input_count'] * 100,2);
            $arr[] = $value['input_count'] == 0 ? 0 : number_format($value['overday1_count'] / $value['input_count'] * 100,2);
            $arr[] = $value['input_count'] == 0 ? 0 : number_format($value['overday1_3_count'] / $value['input_count'] * 100,2);
            $arr[] = $value['input_count'] == 0 ? 0 : number_format($value['overday4_count'] / $value['input_count'] * 100,2);
            $arr[] = $value['input_count'] == 0 ? 0 : number_format($value['overday4_7_count'] / $value['input_count'] * 100,2);
            $arr[] = $value['input_count'] == 0 ? 0 : number_format($value['overday1_7_count'] / $value['input_count'] * 100,2);
            $arr[] = $value['input_count'] == 0 ? 0 : number_format($value['overday8_15_count'] / $value['input_count'] * 100,2);
            $arr[] = $value['input_count'] == 0 ? 0 : number_format($value['overday1_15_count'] / $value['input_count'] * 100,2);
            $arr[] = $value['input_count'] == 0 ? 0 : number_format($value['overday16_30_count'] / $value['input_count'] * 100,2);
            $arr[] = $value['input_count'] == 0 ? 0 : number_format($value['overlevel7_count'] / $value['input_count'] * 100,2);
            $arr[] = $value['input_count'] == 0 ? 0 : number_format($value['overlevel8_count'] / $value['input_count'] * 100,2);
            $arr[] = $value['input_count'] == 0 ? 0 : number_format($value['overlevel9_count'] / $value['input_count'] * 100,2);
            foreach ($arr as $k => $v){
                $seriesTotal[$k]['data'][] = $v;
            }
        }
        // 获取所有商户，只能是商户管理员的时候才在页面上显示
        if ($this->isNotMerchantAdmin) {
            $arrMerchant = Merchant::getMerchantId();
        } else {
            $arrMerchant = [];
        }
        return $this->render('input-overdue-day-out-chart', array(
            'legendTotal' => $legendTotal,
            'seriesTotal' => $seriesTotal,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'xDate' => $xDate,
            'arrMerchant' => $arrMerchant,
            'packageNameList' =>   PackageSetting::getAllLoanPackageNameMap($this->merchantIds)
        ));
    }


    /**
     * @name CollectionStatisticsController 逾期天数出催率(按金额)
     * @return string
     */
    public function actionInputOverdueDayOutAmount(){
        return self::inputOverdueDayOutAmount();
    }

    /**
     * @name CollectionStatisticsController 逾期天数出催率(按金额-全标签)
     * @return string
     */
    public function actionInputOverdueDayOutAmountAllLabel(){
        return self::inputOverdueDayOutAmount(1);
    }

    //逾期天数出催率按金额
    private function inputOverdueDayOutAmount($isAllLabel = 1){
        $isShowOverApr = false;
        if(Yii::$app->user->getId() == 2){
            $isShowOverApr = true;
        }
        $condition[] = 'and';
        $startDate = $this->request->get('start_date',date('Y-m-d',strtotime('-10 day')));
        $endDate = $this->request->get('end_date',date('Y-m-d'));
        $userType = $this->request->get('user_type',0);
        $package_name = $this->request->get('package_name',[]);
        $pageSize = Yii::$app->request->get('page_size',15);
        $condition[] = ['user_type' => intval($userType)];
        // 商户判断
        $nMerchantId = Yii::$app->request->get('merchant_id');

        if ($this->isNotMerchantAdmin && $nMerchantId >= 0 && $nMerchantId != '') {
            $condition[] = ['merchant_id' => explode(',', $nMerchantId)];
        } else {
            // 加上商户号判断
            if (is_array($this->merchantIds)) {
                $sMerchantIds = $this->merchantIds;
            } else {
                $sMerchantIds = explode(',', $this->merchantIds);
            }
            $condition[] = ['merchant_id' => $sMerchantIds];
        }

        if(!empty($package_name)){
            $condition[] = ['package_name' => $package_name];
        }
        $condition2 = $condition;
        if (!empty($startDate)) {
            $condition[] = ['>=', 'date', $startDate];
        }
        if (!empty($endDate)) {
            $condition[] = ['<=', 'date', $endDate];
        }

        $select = [
            'sum(input_amount) as input_amount',
            'sum(overday_total_amount) as overday_total_amount',
        ];

        foreach (LoanCollectionOrder::$level as $lv => $val){
            if($lv >= LoanCollectionOrder::LEVEL_M2){
                $select[] = 'sum(overlevel'.$lv.'_amount) as overlevel'.$lv.'_amount';
            }
        }
        for ($day = 1; $day <= 30; $day++){
            $select[] = 'sum(overday'.$day.'_amount) as overday'.$day.'_amount';
        }
        $query = InputOverdayOutAmount::find()
            ->where($condition)
            ->orderBy(['date' => SORT_DESC]);
        $totalQuery = clone $query;
        $totalData = $totalQuery->select($select)->asArray()->one();
        $select[] = 'date';
        $list = $query
            ->select($select)
            ->groupBy('date')
            ->asArray()
            ->all();
        $totalInputData = [];
        InputOverdueOutService::setTotalInputAmountData($list,$totalData,$totalInputData);

        if (!empty($startDate)) {
            $condition2[] = ['>=', 'date', date('Y-m-d', strtotime($startDate) - 86400)];
        }
        if (!empty($endDate)) {
            $condition2[] = ['<=', 'date', date('Y-m-d', strtotime($endDate) - 86400)];
        }

        $data =  [];
        $totalExpireMoney = 0;
        $totalRepayMoney   = 0;
        $totalRepayZcMoney = 0;
        $type = 0;
        if($isShowOverApr){
            $info = StatisticsDayData::find()
                ->where($condition2)
                ->select("sum(expire_money) as expire_money,
                          sum(repay_money) as repay_money,
                          sum(repay_zc_money) as repay_zc_money,
                          date")
                ->groupBy('date')
                ->orderBy('date DESC')
                ->asArray()
                ->all();
            foreach($info as $value){
                $date=$value['date'];
                //按天
                $expire_money = $value['expire_money'] ?? 0;
                $repay_money = $value['repay_money'] ?? 0;
                $repay_zc_money = $value['repay_zc_money'] ?? 0;
                $totalExpireMoney += $expire_money;
                $totalRepayMoney  += $repay_money;
                $totalRepayZcMoney+= $repay_zc_money;
                $data[$date]['expire_money_'.$type] = $expire_money;
                $data[$date]['repay_money_'.$type] = $repay_money;
                $data[$date]['repay_zc_money_'.$type] = $repay_zc_money;
            }
        }
        $totalData['expire_money_'.$type]  = $totalExpireMoney;
        $totalData['repay_money_'.$type]  = $totalRepayMoney;
        $totalData['repay_zc_money_'.$type]  = $totalRepayZcMoney;

        // 获取所有商户，只能是商户管理员的时候才在页面上显示
        if ($this->isNotMerchantAdmin) {
            $arrMerchant = Merchant::getMerchantId();
        } else {
            $arrMerchant = [];
        }

        return $this->render('input-overdue-day-out-amount', array(
            'list'=>$list,
            'data' => $data,
            'totalData' => $totalData,
            'totalInputData' => $totalInputData,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'arrMerchant' => $arrMerchant,
            'packageNameList' =>   PackageSetting::getAllLoanPackageNameMap($this->merchantIds),
            'isShowOverApr' => $isShowOverApr,
            'user_type_map' => $isAllLabel ? InputOverdayOut::$all_user_type_map : InputOverdayOut::$user_type_map
        ));
    }

    /**
     * @name CollectionStatisticsController 逾期天数出催率(按金额图表)
     * @return string
     */
    public function actionInputOverdueDayOutAmountChart(){
        $isShowOverApr = false;
        if(Yii::$app->user->getId() == 2){
            $isShowOverApr = true;
        }
        $condition[] = 'and';
        $startDate = $this->request->get('start_date',date('Y-m-d',strtotime('-15 day')));
        $endDate = $this->request->get('end_date',date('Y-m-d'));
        $userType = $this->request->get('user_type',0);
        $package_name = $this->request->get('package_name',[]);
        $condition[] = ['user_type' => intval($userType)];
        // 加上商户号判断
        if (is_array($this->merchantIds)) {
            $sMerchantIds = $this->merchantIds;
        } else {
            $sMerchantIds = explode(',', $this->merchantIds);
        }
        $condition[] = ['merchant_id' => $sMerchantIds];

        if(!empty($package_name)){
            $condition[] = ['package_name' => $package_name];
        }
        $condition2 = $condition;
        if (!empty($startDate)) {
            $condition[] = ['>=', 'date', $startDate];
        }
        if (!empty($endDate)) {
            $condition[] = ['<=', 'date', $endDate];
        }

        $select = [
            'date',
            'sum(input_amount) as input_amount',
            'sum(overday_total_amount) as overday_total_amount',
        ];

        foreach (LoanCollectionOrder::$level as $lv => $val){
            if($lv >= LoanCollectionOrder::LEVEL_M2){
                $select[] = 'sum(overlevel'.$lv.'_amount) as overlevel'.$lv.'_amount';
            }
        }
        for ($day = 1; $day <= 30; $day++){
            $select[] = 'sum(overday'.$day.'_amount) as overday'.$day.'_amount';
        }
        $query = InputOverdayOutAmount::find()
            ->select($select)
            ->where($condition)
            ->groupBy('date')
            ->orderBy(['date' => SORT_ASC]);

        $list = $query
            ->asArray()
            ->all();
        InputOverdueOutService::setChatAmountLevelByDayData($list);
        if (!empty($startDate)) {
            $condition2[] = ['>=', 'date', date('Y-m-d', strtotime($startDate) - 86400)];
        }
        if (!empty($endDate)) {
            $condition2[] = ['<=', 'date', date('Y-m-d', strtotime($endDate) - 86400)];
        }

        $data =  [];
        $totalExpireMoney = 0;
        $totalRepayMoney   = 0;
        $totalRepayZcMoney = 0;
        $type = 0;
        if($isShowOverApr){
            $info = StatisticsDayData::find()
                ->where($condition2)
                ->select("sum(expire_money) as expire_money,
                          sum(repay_money) as repay_money,
                          sum(repay_zc_money) as repay_zc_money,
                          date")
                ->groupBy('date')
                ->orderBy('date DESC')
                ->asArray()
                ->all();
            foreach($info as $value){
                $date=$value['date'];
                //按天
                $expire_money = $value['expire_money'] ?? 0;
                $repay_money = $value['repay_money'] ?? 0;
                $repay_zc_money = $value['repay_zc_money'] ?? 0;
                $totalExpireMoney += $expire_money;
                $totalRepayMoney  += $repay_money;
                $totalRepayZcMoney+= $repay_zc_money;
                $data[$date]['expire_money_'.$type] = $expire_money;
                $data[$date]['repay_money_'.$type] = $repay_money;
                $data[$date]['repay_zc_money_'.$type] = $repay_zc_money;
            }
        }

        $totalData['expire_money_'.$type]  = $totalExpireMoney;
        $totalData['repay_money_'.$type]  = $totalRepayMoney;
        $totalData['repay_zc_money_'.$type]  = $totalRepayZcMoney;

        $xDate = [];

        //图表上方说明
        if($isShowOverApr){
            $legendTotal = ['首逾','逾期率','累计催回率','D1','D1-3','D4','D4-7','S1','S2','S1+S2','M1','M2','M3','M3+'];
        }else{
            $legendTotal = ['累计催回率','D1','D1-3','D4','D4-7','S1','S2','S1+S2','M1','M2','M3','M3+'];
        }
        $seriesTotal = [];
        foreach ($legendTotal as $key => $value){
            $seriesTotal[$key] = ['name'=> $value,'type' => 'line','data'=>[]];
        }

        foreach ($list as $k => $value){
            $xDate[] = $value['date'];
            $arr = [];
            if($isShowOverApr){
                $before = date('Y-m-d',strtotime($value['date']) - 86400);
                $arr[] = isset($data[$before]) ? ((empty($data[$before]['expire_money_0']) ? '0' : number_format(($data[$before]['expire_money_0'] - $data[$before]['repay_zc_money_0']) / $data[$before]['expire_money_0'] * 100,2))) : '0';
                $arr[] = isset($data[$before]) ? ((empty($data[$before]['expire_money_0']) ? '0' : number_format(($data[$before]['expire_money_0'] - $data[$before]['repay_money_0']) / $data[$before]['expire_money_0'] * 100,2))) : '0';
            }
            $arr[] = $value['input_amount'] == 0 ? 0 : number_format($value['overday_total_amount'] / $value['input_amount'] * 100,2);
            $arr[] = $value['input_amount'] == 0 ? 0 : number_format($value['overday1_amount'] / $value['input_amount'] * 100,2);
            $arr[] = $value['input_amount'] == 0 ? 0 : number_format($value['overday1_3_amount'] / $value['input_amount'] * 100,2);
            $arr[] = $value['input_amount'] == 0 ? 0 : number_format($value['overday4_amount'] / $value['input_amount'] * 100,2);
            $arr[] = $value['input_amount'] == 0 ? 0 : number_format($value['overday4_7_amount'] / $value['input_amount'] * 100,2);
            $arr[] = $value['input_amount'] == 0 ? 0 : number_format($value['overday1_7_amount'] / $value['input_amount'] * 100,2);
            $arr[] = $value['input_amount'] == 0 ? 0 : number_format($value['overday8_15_amount'] / $value['input_amount'] * 100,2);
            $arr[] = $value['input_amount'] == 0 ? 0 : number_format($value['overday1_15_amount'] / $value['input_amount'] * 100,2);
            $arr[] = $value['input_amount'] == 0 ? 0 : number_format($value['overday16_30_amount'] / $value['input_amount'] * 100,2);
            $arr[] = $value['input_amount'] == 0 ? 0 : number_format($value['overlevel7_amount'] / $value['input_amount'] * 100,2);
            $arr[] = $value['input_amount'] == 0 ? 0 : number_format($value['overlevel8_amount'] / $value['input_amount'] * 100,2);
            $arr[] = $value['input_amount'] == 0 ? 0 : number_format($value['overlevel9_amount'] / $value['input_amount'] * 100,2);
            foreach ($arr as $k => $v){
                $seriesTotal[$k]['data'][] = $v;
            }
        }
        // 获取所有商户，只能是商户管理员的时候才在页面上显示
        if ($this->isNotMerchantAdmin) {
            $arrMerchant = Merchant::getMerchantId();
        } else {
            $arrMerchant = [];
        }
        return $this->render('input-overdue-day-out-amount-chart', array(
            'legendTotal' => $legendTotal,
            'seriesTotal' => $seriesTotal,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'xDate' => $xDate,
            'arrMerchant' => $arrMerchant,
            'packageNameList' =>   PackageSetting::getAllLoanPackageNameMap($this->merchantIds)
        ));
    }


    /**
     *@name 机构每日订单快照
     *@author wangpeng
     */
    public function actionOutsideDayData(){
        $condition[] = 'and';
        $outside = $this->request->get('outside');
        $startDate = $this->request->get('start_date',date('Y-m-d',strtotime('-10 day')));
        $endDate = $this->request->get('end_date',date('Y-m-d'));
        $pageSize = Yii::$app->request->get('page_size',15);
        if($outside){
            $condition .= ' and outside='.$outside;
            $condition[] = ['outside' => $outside];
        }
        if (!empty($startDate)) {
            $condition[] = ['>=', 'date', $startDate];
        }
        if (!empty($endDate)) {
            $condition[] = ['<=', 'date', $endDate];
        }

        $pages = new Pagination(['totalCount' => 99999]);
        $pages->pageSize = $pageSize;
        $query = OutsideDayData::find()
            ->where($condition)
            ->orderBy(['date' => SORT_DESC, 'outside' => SORT_ASC]);
        $list = $query
            ->offset($pages->offset)
            ->limit($pages->limit)
            ->asArray()
            ->all();
        $outsideInfo = UserCompany::allOutsideRealName($this->merchantIds);
        return $this->render('outside-day-data', array(
            'list'=>$list,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'pages' => $pages,
            'outsideInfo' => $outsideInfo,
            'merchant_id' => $this->merchantIds
        ));
    }

    /**
     *@name 机构每日订单统计
     *@author wangpeng
     */
    public function actionDispatchOutsideFinish(){
        $condition[] = 'and';
        $outside = $this->request->get('outside');
        $startDate = $this->request->get('start_date',date('Y-m-d',strtotime('-7 day')));
        $endDate = $this->request->get('end_date',date('Y-m-d'));
        $pageSize = Yii::$app->request->get('page_size',15);
        if($outside){
            $condition[] = ['outside' => $outside];
        }
        if (!empty($startDate)) {
            $condition[] = ['>=', 'date', $startDate];
        }
        if (!empty($endDate)) {
            $condition[] = ['<=', 'date', $endDate];
        }

        // 商户判断
        $nMerchantId = Yii::$app->request->get('merchant_id');

        if ($this->isNotMerchantAdmin && $nMerchantId >= 0 && $nMerchantId != '') {
            $condition[] = ['merchant_id' => $nMerchantId];
        } else {
            // 加上商户号判断
            if (is_array($this->merchantIds)) {
                $sMerchantIds = $this->merchantIds;
            } else {
                $sMerchantIds = explode(',', $this->merchantIds);
            }
            $condition[] = ['merchant_id' => $sMerchantIds];
        }

        $pages = new Pagination(['totalCount' => 99999]);
        $pages->pageSize = $pageSize;
        $query = DispatchOutsideFinish::find()
            ->where($condition)
            ->orderBy(['date' => SORT_DESC, 'outside' => SORT_ASC]);
        $list = $query
            ->offset($pages->offset)
            ->limit($pages->limit)
            ->asArray()
            ->all();
        $outsideInfo = UserCompany::allOutsideRealName($this->merchantIds);

        // 获取所有商户，只能是商户管理员的时候才在页面上显示
        if ($this->isNotMerchantAdmin) {
            $arrMerchant = Merchant::getMerchantId();
        } else {
            $arrMerchant = [];
        }

        return $this->render('dispatch-outside-finish', array(
            'list'=>$list,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'pages' => $pages,
            'outsideInfo' => $outsideInfo,
            'merchant_id' => $this->merchantIds,
            'arrMerchant' => $arrMerchant
        ));
    }

    /**
     * @name 每日催回率
     */
    public function actionEveryDayChart()
    {
        $start_time = $this->request->get('start_time',date('Y-m-d',strtotime('-10 day')));
        $end_time = $this->request->get('end_time',0);
        $sub_order_type = $this->request->get('sub_order_type',1);
        $db_assist =  'db_assist_read';
        if(empty($end_time)){
            $end_time = time();
        }else{
            $end_time = strtotime($end_time) + 86400;
        }

        // 商户判断
        $nMerchantId = Yii::$app->request->get('merchant_id');

        if ($this->isNotMerchantAdmin && $nMerchantId >= 0 && $nMerchantId != '') {
            $sMerchantIds = $nMerchantId;
        } else {
            // 加上商户号判断
            if (is_array($this->merchantIds)) {
                $sMerchantIds = $this->merchantIds;
            } else {
                $sMerchantIds = explode(',', $this->merchantIds);
            }
        }

        $rates = OrderStatisticsByRate::lists_new(strtotime($start_time), $end_time,$db_assist,$sub_order_type, $sMerchantIds);
        $dates = [];
        $arr = [];
        $overdue_days = OrderStatisticsByRate::$rate_days;
        $series=[];
        $totals = [];
        if(!empty($rates)){
            foreach ($rates as $key=>$val){
                $date = date('Y-m-d',$val['create_at']);
                $rate[$date] = $val;
            }
            $dates = array_keys($rate);
            foreach ($rate as $k=>$v){
                $total = 0;
                foreach ($overdue_days as $k1=>$day){
                    if(!is_int($day)){
                        $range = explode('-', $day);
                        $day = $range[1];
                    }
                    $total += $v['repay_'.$day.'_amount'];
                    $arr[$k][] = empty($v['collection_amount']) || empty($v['repay_'.$day.'_amount'])? 0:number_format($v['repay_'.$day.'_amount']/$v['collection_amount'], 4)*100;
                }
                $totals[$k]['time'] = $k;
                $totals[$k]['finish_money'] = $total/100;
                $totals[$k]['all_money'] = $v['collection_amount']/100;
                $totals[$k]['rate'] = empty($v['collection_amount']) ? 0 : number_format($total/$v['collection_amount'],4)*100;
            }
        }
        foreach ($arr as $k=>$val)
        {
            $series[] = array(
                'name' => $k,
                'type' => 'line',
                'data' => $val,
            );
        }

        // 获取所有商户，只能是商户管理员的时候才在页面上显示
        if ($this->isNotMerchantAdmin) {
            $arrMerchant = Merchant::getMerchantId();
        } else {
            $arrMerchant = [];
        }

        return $this->render('every-day-chart',[
            'days_total'=>$overdue_days,
            'series_total'=>$series,
            'legend_total'=>$dates,
            'total' =>$totals,
            'arrMerchant' => $arrMerchant
        ]);
    }


    /**
     * @name CollectionStatisticsController 催收员按逾期天派分数据
     * @return string
     */
    public function actionDispatchOverdueDaysFinish(){
        $username = $this->request->get('username');
        $startTime = $this->request->get('start_time',date('Y-m-d',strtotime('-7 day')));
        $endTime = $this->request->get('end_time',date('Y-m-d'));
        $loanGroup = $this->request->get('loan_group');
        $outside = $this->request->get('outside');
        $groupGame = $this->request->get('group_game',[]);
        $overdueDay = $this->request->get('overdue_day');
        $condition[] = 'and';
        $sumSelectArr = [
            'outside' => 'CONCAT("-")',
            'group_game' => 'CONCAT("-")',
            'group' => 'CONCAT("-")',
            'username' => 'CONCAT("-")',
            'overdue_day' => 'CONCAT("-")',
            'dispatch_count' => 'SUM(dispatch_count)',
            'dispatch_amount' => 'SUM(dispatch_amount)',
            'today_repay_count' => 'SUM(today_repay_count)',
            'today_repay_amount' => 'SUM(today_repay_amount)',
            'total_repay_count' => 'SUM(total_repay_count)',
            'total_repay_amount' => 'SUM(total_repay_amount)'
        ];
        if(!empty($startTime)){
            $condition[] = ['>=', 'date', $startTime];
        }
        if(!empty($endTime)){
            $condition[] = ['<=', 'date', $endTime];
        }
        if(!empty($outside)){
            $sumSelectArr['outside'] = 'userCompany.real_title';
            $condition[] = ['outside' => intval($outside)];
        }
        if(!empty($groupGame)){
            $sumSelectArr['group_game'] = 'adminUser.group_game';
            $condition[] = ['adminUser.group_game' => $groupGame];
        }
        if(!empty($username)){
            $sumSelectArr['username'] = 'adminUser.username';
            $condition[] = ['adminUser.username' => $username];
        }
        if(!empty($loanGroup)){
            $sumSelectArr['group'] = 'adminUser.group';
            $condition[] = ['adminUser.group' => intval($loanGroup)];
        }
        if(!empty($overdueDay)){
            $overdueDayArr = explode('-',$overdueDay);
            $sumSelectArr['overdue_day'] = 'CONCAT("'.$overdueDay.'")';
            if(count($overdueDayArr) == 2){
                $condition[] = ['between', 'overdue_day', intval($overdueDayArr[0]), intval($overdueDayArr[1])];
            }else{
                $condition[] = ['overdue_day' => intval($overdueDayArr[0])];
            }

        }
        $query = DispatchOverdueDaysFinish::find()
            ->leftJoin(AdminUser::tableName().' adminUser','adminUser.id = admin_user_id')
            ->leftJoin(UserCompany::tableName(). ' userCompany','userCompany.id = adminUser.outside')
            ->where($condition)
            ->andWhere(['adminUser.merchant_id' => $this->merchantIds]);

        if(!empty($groupGame)){
            $query->andWhere(['adminUser.group_game' => $groupGame]);
        }
        $totalQuery = clone $query;


        $query = $query->select([
            'date',
            'outside' => 'userCompany.real_title',
            'group_game' => 'adminUser.group_game',
            'group' => 'adminUser.group',
            'username' => 'adminUser.username',
            'overdue_day',
            'dispatch_count',
            'dispatch_amount',
            'today_repay_count' ,
            'today_repay_amount',
            'total_repay_count',
            'total_repay_amount'
        ]);
        //导出
        if($this->request->get('submitcsv') == 'exportcsv'){
            $data = $query
                ->orderBy(['date' => SORT_DESC])
                ->asArray()
                ->all();
            return $this->_exportDispatchOverdueDays($data);
        }

        $totalData = $totalQuery->select($sumSelectArr)->asArray()->one();
        $pages = new Pagination(['totalCount' => 99999]);
        $pages->pageSize =  Yii::$app->request->get('page_size',15);;
        $data = $query
            ->orderBy(['date' => SORT_DESC])
            ->offset($pages->offset)
            ->limit($pages->limit)
            ->asArray()
            ->all();
//        var_dump($data);exit;
        $teamList = CompanyTeam::getTeamsByOutside($outside);
        return $this->render('dispatch-overdue-days-finish',[
            'totalData' => $totalData,
            'data' => $data,
            'pages' => $pages,
            'teamList' => $teamList,
            'merchant_id' => $this->merchantIds
        ]);
    }


    /**
     * 逾期天数分派数据导出方法
     * @param $data
     */
    private function _exportDispatchOverdueDays($data){
        $this->_setcsvHeader('逾期天数分派数据列表导出'.date('YmdHi').'.csv');
        $items = [];
        foreach($data as $val){
            $items[] = [
                '分派日期'      => $val['date'],
                '分派催收员'     => $val['username'],
                '机构'        => $val['outside'],
                '小组分组'      => AdminUser::$group_games[$val['group_game']] ?? '-',
                '催收员分组'     => LoanCollectionOrder::$level[$val['group']],  //分组
                '分派订单的逾期天数' => $val['overdue_day'],
                '分派订单数'     => $val['dispatch_count'],
                '当日分派且完成单数' => $val['today_repay_count'],
                '分派完成单数'    => $val['total_repay_count']
            ];
        }
        // var_dump($items); die;
        echo $this->_array2csv($items);
        exit;
    }

    /**
     * @name CollectionStatisticsController 催收员work app api出勤率
     * @return string
     */
    public function actionCollectorAttendanceDayData(){
        $startTime = $this->request->get('start_time',date('Y-m-d',strtotime('-7 day')));
        $endTime = $this->request->get('end_time',date('Y-m-d'));
        $loanGroup = $this->request->get('group');
        $outside = $this->request->get('outside');
        $groupGame = $this->request->get('group_game',[]);
        $condition[] = 'and';
        if(!empty($startTime)){
            $condition[] = ['>=', 'date', $startTime];
        }
        if(!empty($endTime)){
            $condition[] = ['<=', 'date', $endTime];
        }
        if(!empty($outside)){
            $condition[] = ['A.outside' => intval($outside)];
        }
        if(!empty($loanGroup)){
            $condition[] = ['A.group' => intval($loanGroup)];
        }
        $query = CollectorAttendanceDayData::find()
            ->from(CollectorAttendanceDayData::tableName().' A')
            ->leftJoin(UserCompany::tableName(). ' B','A.outside = B.id')
            ->where($condition)
            ->andWhere(['B.merchant_id' => $this->merchantIds]);
        if(!empty($groupGame)){
            $query->andWhere(['A.group_game' => $groupGame]);
        }

        $totalQuery = clone $query;
        $dateQuery = clone $query;

        $totalData = $totalQuery
            ->select([
                'date'           => 'CONCAT("汇总")',
                'total_num'      => 'SUM(A.total_num)',
                'today_add_num'  => 'SUM(A.today_add_num)',
                'attendance_num' => 'SUM(A.attendance_num)',
                'total_count'    => 'COUNT(1)',
            ])
            ->asArray()
            ->all();
        $dateData = $dateQuery
            ->select([
                'date',
                'total_num'      => 'SUM(A.total_num)',
                'today_add_num'  => 'SUM(A.today_add_num)',
                'attendance_num' => 'SUM(A.attendance_num)',
            ])
            ->groupBy(['date'])
            ->orderBy(['date' => SORT_DESC])
            ->asArray()
            ->all();

        $pages = new Pagination(['totalCount' => $totalData[0]['total_count']]);
        $pages->pageSize =  Yii::$app->request->get('page_size',15);;
        $totalData = array_merge($totalData,$dateData);
        $data = $query
            ->select([
                'A.date',
                'outside_name' => 'B.real_title',
                'A.group_game',
                'A.group',
                'A.total_num',
                'A.today_add_num',
                'A.attendance_num',
            ])
            ->orderBy(['date' => SORT_DESC])
            ->offset($pages->offset)
            ->limit($pages->limit)
            ->asArray()
            ->all();

        $teamList = CompanyTeam::getTeamsByOutside($outside);
        return $this->render('collector-attendance-day-data',[
            'totalData' => $totalData,
            'data' => $data,
            'pages' => $pages,
            'teamList' => $teamList,
            'merchant_id' => $this->merchantIds,
            'strategyOperating' => $this->strategyOperating
        ]);
    }

    /**
     * @name CollectionStatisticsController 催收员坐席电话记录
     * @return string
     * @throws
     */
    public function actionCollectorNxPhoneData()
    {
        $startTime = strtotime(Yii::$app->request->get('start_time', ''));
        $endTime = strtotime(Yii::$app->request->get('end_time', ''));
        $phone = $this->request->get('phone');
        $order_id = $this->request->get('order_id');
        $collector = $this->request->get('username');

        $sort['A.id'] = SORT_DESC;

        $query = NxPhoneLog::find()
            ->select([
                'A.order_id',
                'A.nx_name',
                'A.phone',
                'A.duration',
                'A.record_url',
                'A.start_time',
                'A.answer_time',
                'A.end_time',
                'A.hangup_cause',
                'B.username',
            ])
            ->from(NxPhoneLog::tableName() . ' A')
            ->leftJoin(AdminUser::tableName() . ' B', 'A.collector_id = B.id')
            ->where(['A.direction' => 1, 'A.call_type' => NxPhoneLog::CALL_COLLECTION]);

        if (!empty($startTime)) {
            $query->andWhere(['>=', 'A.created_at', $startTime]);
        }
        if (!empty($endTime)) {
            $query->andWhere(['<=', 'A.created_at', $endTime]);
        }
        if (!empty($phone)) {
            $query->andWhere(['A.phone' => $phone]);
        }
        if (!empty($order_id)) {
            $query->andWhere(['A.order_id' => $order_id]);
        }
        if (!empty($collector) && $collector != '') {
            $query->andWhere(['like', 'B.username', $collector]);
        }

        $pages = new Pagination(['totalCount' => $query->count()]);
        $pages->pageSize = Yii::$app->request->get('page_size', 15);
        $data = $query
            ->orderBy($sort)
            ->offset($pages->offset)
            ->limit($pages->limit)
            ->asArray()
            ->all();

        return $this->render('collector-nx-phone-data', [
            'data' => $data,
            'pages' => $pages,
        ]);
    }

    /**
     * @name CollectionStatisticsController 催收员通话通次
     * @return string
     */
    public function actionCollectorCallData(){
        $startTime = $this->request->get('start_time',date('Y-m-d',strtotime('-7 day')));
        $endTime = $this->request->get('end_time',date('Y-m-d'));
        $loanGroup = $this->request->get('group');
        $outside = $this->request->get('outside');
        $groupGame = $this->request->get('group_game',[]);
        $collector = $this->request->get('username');
        $phoneType = $this->request->get('phone_type');
        $realName = $this->request->get('real_name');
        $condition[] = 'and';
        if(!empty($startTime)){
            $condition[] = ['>=', 'A.date', $startTime];
        }
        if(!empty($endTime)){
            $condition[] = ['<=', 'A.date', $endTime];
        }
        if(!empty($outside)){
            $condition[] = ['B.outside' => intval($outside)];
        }
        if(!empty($loanGroup)){
            $condition[] = ['B.group' => intval($loanGroup)];
        }
        if (!empty($phoneType)){
            $condition[] = ['A.phone_type' => intval($phoneType)];
        }

        $select = [
            'total_person' => 'COUNT(1)',
            'total_times' => 'SUM(A.times)',
            'total_duration' => 'SUM(A.duration)',

            'invalid_total_person' => 'SUM(IF(A.is_valid = '.CollectorCallData::INVALID.',1,0))',
            'invalid_total_times' => 'SUM(IF(A.is_valid = '.CollectorCallData::INVALID.',A.times,0))',

            'oneself_person' => 'SUM(IF(A.type = '.CollectorCallData::TYPE_ONE_SELF.',1,0))',
            'oneself_times' => 'SUM(IF(A.type = '.CollectorCallData::TYPE_ONE_SELF.',A.times,0))',
            'oneself_duration' => 'SUM(IF(A.type = '.CollectorCallData::TYPE_ONE_SELF.',A.duration,0))',

            'invalid_oneself_person' => 'SUM(IF(A.type = '.CollectorCallData::TYPE_ONE_SELF.' and A.is_valid = '.CollectorCallData::INVALID.',1,0))',
            'invalid_oneself_times' => 'SUM(IF(A.type = '.CollectorCallData::TYPE_ONE_SELF.' and A.is_valid = '.CollectorCallData::INVALID.',A.times,0))',

            'contact_person' => 'SUM(IF(A.type = '.CollectorCallData::TYPE_CONTACT.',1,0))',
            'contact_times' => 'SUM(IF(A.type = '.CollectorCallData::TYPE_CONTACT.',A.times,0))',
            'contact_duration' => 'SUM(IF(A.type = '.CollectorCallData::TYPE_CONTACT.',A.duration,0))',

            'invalid_contact_person' => 'SUM(IF(A.type = '.CollectorCallData::TYPE_CONTACT.' and A.is_valid = '.CollectorCallData::INVALID.',1,0))',
            'invalid_contact_times' => 'SUM(IF(A.type = '.CollectorCallData::TYPE_CONTACT.' and A.is_valid = '.CollectorCallData::INVALID.',A.times,0))',

            'address_book_person' => 'SUM(IF(A.type = '.CollectorCallData::TYPE_ADDRESS_BOOK.',1,0))',
            'address_book_times' => 'SUM(IF(A.type = '.CollectorCallData::TYPE_ADDRESS_BOOK.',A.times,0))',
            'address_book_duration' => 'SUM(IF(A.type = '.CollectorCallData::TYPE_ADDRESS_BOOK.',A.duration,0))',

            'invalid_address_book_person' => 'SUM(IF(A.type = '.CollectorCallData::TYPE_ADDRESS_BOOK.' and A.is_valid = '.CollectorCallData::INVALID.',1,0))',
            'invalid_address_book_times' => 'SUM(IF(A.type = '.CollectorCallData::TYPE_ADDRESS_BOOK.' and A.is_valid = '.CollectorCallData::INVALID.',A.times,0))',
        ];
        $query = CollectorCallData::find()
            ->from(CollectorCallData::tableName().' A')
            ->leftJoin(AdminUser::tableName(). ' B','A.user_id = B.id')
            ->leftJoin(UserCompany::tableName(). ' C','B.outside = C.id')
            ->where($condition)
            ->andWhere(['B.merchant_id' => $this->merchantIds]);
        if(!empty($groupGame)){
            $query->andWhere(['B.group_game' => $groupGame]);
        }
        if(!empty($collector) && $collector != ''){
            $query->andWhere(['like','B.username',$collector]);
        }
        if(!empty($realName) && $realName != ''){
            $query->andWhere(['like','B.real_name',$realName]);
        }
        $setRealNameCollectionAdmin = GlobalSetting::checkCollectionUsernameCanSetRealName(Yii::$app->user->identity);
        //导出
        if($this->request->get('submitcsv') == 'exportcsv'){
            $select[] = 'A.date';
            $select['outside_name'] = 'C.real_title';
            $select[] = 'B.group_game';
            $select[] = 'B.group';
            $select[] = 'B.username';
            $select[] = 'B.real_name';
            $data = $query->select($select)
                ->groupBy(['A.date','A.user_id'])
                ->orderBy(['A.date' => SORT_DESC])
                ->asArray()
                ->all();

            return $this->_exportCollectorCallData($data,$setRealNameCollectionAdmin);
        }
        $totalQuery = clone $query;
        $totalData = $totalQuery->select(array_merge($select,['date' => 'CONCAT("汇总")']))->asArray()
            ->all();
        $dateQuery = clone $query;
        $select[] = 'A.date';
        $dateData = $dateQuery->select($select)->asArray()
            ->groupBy(['A.date'])
            ->all();
        $totalData = array_merge($totalData,$dateData);
        $pages = new Pagination(['totalCount' => 999999]);
        $pages->pageSize =  Yii::$app->request->get('page_size',15);
        $select['outside_name'] = 'C.real_title';
        $select[] = 'B.group_game';
        $select[] = 'B.group';
        $select[] = 'B.username';
        $select[] = 'B.real_name';

        $data = $query
            ->select($select)
            ->groupBy(['A.date','A.user_id'])
            ->orderBy(['A.date' => SORT_DESC])
            ->offset($pages->offset)
            ->limit($pages->limit)
            ->asArray()
            ->all();

        $teamList = CompanyTeam::getTeamsByOutside($outside);
        return $this->render('collector-call-data',[
            'totalData' => $totalData,
            'data' => $data,
            'pages' => $pages,
            'teamList' => $teamList,
            'merchant_id' => $this->merchantIds,
            'setRealNameCollectionAdmin' => $setRealNameCollectionAdmin
        ]);
    }

    /**
     * 催收员通话通次导出方法
     * @param $data
     * @param $setRealNameCollectionAdmin
     */
    private function _exportCollectorCallData($data,$setRealNameCollectionAdmin){
        $this->_setcsvHeader('催收员通话通次'.date('YmdHi').'.csv');
        $items = [];
        foreach($data as $val){
            $arr = [
                'date'      => $val['date'],
                'company'      => $val['outside_name'],
                '小组分组'      => AdminUser::$group_games[$val['group_game']] ?? '-',
                '催收员分组'     => LoanCollectionOrder::$level[$val['group']],  //分组
                'username'     => $val['username'],
                '总拨打人数'    =>  $val['total_person'],
                '总拨打次数'    =>  $val['total_times'],
                '总拨打分钟'    =>  floor($val['total_duration'] / 60).'分'.($val['total_duration'] % 60).'秒',
                '拨打本人人数'    =>  $val['oneself_person'],
                '拨打本人次数'    =>  $val['oneself_times'],
                '拨打本人时长'    =>  floor($val['oneself_duration'] / 60).'分'.($val['oneself_duration'] % 60).'秒',
                '无效拨打本人人数'    =>  $val['invalid_oneself_person'],
                '无效拨打本人次数'    =>  $val['invalid_oneself_times'],
                '拨打联系人人数'    =>  $val['contact_person'],
                '拨打联系人次数'    =>  $val['contact_times'],
                '拨打联系人时长'    =>  floor($val['contact_duration'] / 60).'分'.($val['contact_duration'] % 60).'秒',
                '无效拨打联系人人数'    =>  $val['invalid_contact_person'],
                '无效拨打联系人次数'    =>  $val['invalid_contact_times'],
                '拨打通讯录人数'    =>  $val['address_book_person'],
                '拨打通讯录次数'    =>  $val['address_book_times'],
                '拨打通讯录时长'    =>   floor($val['address_book_duration'] / 60).'分'.($val['address_book_duration'] % 60).'分',
                '无效拨打通讯录人数'    =>  $val['invalid_address_book_person'],
                '无效拨打通讯录次数'    =>  $val['invalid_address_book_times'],
            ];
            if($setRealNameCollectionAdmin){
                $arr['real_name'] = $val['real_name'];
            }
            $items[] = $arr;
        }
        // var_dump($items); die;
        echo $this->_array2csv($items);
        exit;
    }

    /**
     * @name CollectionStatisticsController 催收员日催回金额
     * @return string
     */
    public function actionCollectorBackMoneyData2(){
        $startTime = $this->request->get('start_time',date('Y-m-d',strtotime('-7 day')));
        $endTime = $this->request->get('end_time',date('Y-m-d'));
        $loanGroup = $this->request->get('group');
        $outside = $this->request->get('outside');
        $groupGame = $this->request->get('group_game',[]);
        $username = $this->request->get('username');
        $realName = $this->request->get('real_name');
        $condition[] = 'and';
        if(!empty($startTime)){
            $condition[] = ['>=', 'A.date', $startTime];
        }
        if(!empty($endTime)){
            $condition[] = ['<=', 'A.date', $endTime];
        }
        if(!empty($outside)){
            $condition[] = ['B.outside' => intval($outside)];
        }
        if(!empty($loanGroup)){
            $condition[] = ['B.group' => intval($loanGroup)];
        }
        if(!empty($username)){
            $condition[] = ['like', 'B.username', $username];
        }
        if(!empty($realName)){
            $condition[] = ['like', 'B.real_name', $realName];
        }
        $flag = 1;
        $sort = [];
        if($sort_by =$this->request->get('btn_sort'))
        {
            $flag = (int)$this->request->get('flag');
            if($flag%2 ==0)
            {
                $sort[$sort_by] = SORT_DESC;
            }
            else
            {
                $sort[$sort_by] = SORT_ASC;
            }
            ++$flag;
        }
        $sort['A.date'] = SORT_DESC;
        $sort['A.id'] = SORT_DESC;
        $query = CollectorBackMoney::find()
            ->from(CollectorBackMoney::tableName().' A')
            ->leftJoin(AdminUser::tableName(). ' B','A.admin_user_id = B.id')
            ->leftJoin(UserCompany::tableName(). ' C','B.outside = C.id')
            ->where($condition)
            ->andWhere(['B.merchant_id' => $this->merchantIds]);;
        if(!empty($groupGame)){
            $query->andWhere(['B.group_game' => $groupGame]);
        }
        $select = [
            'A.date',
            'outside_name' => 'C.real_title',
            'B.group_game',
            'B.group',
            'B.username',
            'B.real_name',
            'A.back_money',
            'A.delay_money',
            'A.delay_order_count',
            'A.extend_money',
            'A.extend_order_count',
            'A.finish_order_count'
        ];
        $setRealNameCollectionAdmin = GlobalSetting::checkCollectionUsernameCanSetRealName(Yii::$app->user->identity);
        //导出
        if($this->request->get('submitcsv') == 'exportcsv'){
            $data = $query
                ->select($select)
                ->orderBy($sort)
                ->asArray()
                ->all(CollectorBackMoney::getDb_rd());
            return $this->_exportCollectorBackMoneyData($data,$setRealNameCollectionAdmin);
        }
        $totalQuery = clone $query;
        $totalData = $totalQuery
            ->select([
                'date'               => 'CONCAT("汇总")',
                'back_money'         => 'SUM(A.back_money)',
                'delay_money'        => 'SUM(A.delay_money)',
                'delay_order_count'  => 'SUM(A.delay_order_count)',
                'extend_money'       => 'SUM(A.extend_money)',
                'extend_order_count' => 'SUM(A.extend_order_count)',
                'finish_order_count' => 'SUM(A.finish_order_count)',
            ])->asArray()
            ->all();
        $dateQuery = clone $query;
        $dateData = $dateQuery
            ->select([
                'A.date',
                'back_money'         => 'SUM(A.back_money)',
                'delay_money'        => 'SUM(A.delay_money)',
                'delay_order_count'  => 'SUM(A.delay_order_count)',
                'extend_money'       => 'SUM(A.extend_money)',
                'extend_order_count' => 'SUM(A.extend_order_count)',
                'finish_order_count' => 'SUM(A.finish_order_count)',
            ])->asArray()
            ->groupBy(['A.date'])
            ->all();
        $totalData = array_merge($totalData,$dateData);
        $pages = new Pagination(['totalCount' => $totalQuery->count()]);
        $pages->pageSize =  Yii::$app->request->get('page_size',15);
        $data = $query
            ->select($select)
            ->orderBy($sort)
            ->offset($pages->offset)
            ->limit($pages->limit)
            ->asArray()
            ->all();

        $teamList = CompanyTeam::getTeamsByOutside($outside);
        return $this->render('collector-back-money-data',[
            'totalData' => $totalData,
            'data' => $data,
            'pages' => $pages,
            'teamList' => $teamList,
            'flag' => $flag,
            'merchant_id' => $this->merchantIds,
            'setRealNameCollectionAdmin' => $setRealNameCollectionAdmin
        ]);
    }

    /**
     * 催收员日催回金额导出方法
     * @param $data
     * @param $setRealNameCollectionAdmin
     */
    private function _exportCollectorBackMoneyData($data,$setRealNameCollectionAdmin){
        $this->_setcsvHeader('催收员日催回金额'.date('YmdHi').'.csv');
        $items = [];
        foreach($data as $val){
            $arr = [
                Yii::T('common', 'date')      => $val['date'],
                Yii::T('common', 'Collector')     => $val['username'],
                Yii::T('common', 'agency')      => $val['outside_name'],
                Yii::T('common', 'Grouping')      => AdminUser::$group_games[$val['group_game']] ?? '-',
                Yii::T('common', 'Order group')    => LoanCollectionOrder::$level[$val['group']],  //分组
                Yii::T('common', 'Recall amount (including partial repayment)')    =>  number_format($val['back_money'] / 100,2),
                Yii::T('common', 'Delay order count')    =>  $val['delay_order_count'],
                Yii::T('common', 'Delay money')    =>  number_format($val['delay_money'] / 100,2),
                Yii::T('common', 'Extend order count')    =>  $val['extend_order_count'],
                Yii::T('common', 'Extend money')    =>  number_format($val['extend_money'] / 100,2),
                Yii::T('common', 'Finish order count')    =>  $val['finish_order_count'],
            ];
            if($setRealNameCollectionAdmin){
                $arr['real_name'] = $val['real_name'];
            }
            $items[] = $arr;
        }
        // var_dump($items); die;
        echo $this->_array2csv($items);
        exit;
    }

    /**
     * @name CollectionStatisticsController 催收员D1数据
     * @return string
     */
    public function actionCollectorDayOneData(){

        $loanGroup = $this->request->get('group');
        $outside = $this->request->get('outside');
        $groupGame = $this->request->get('group_game',[]);
        $username = $this->request->get('username');
        $realName = $this->request->get('real_name');
        $start_time = strtotime(date('Y-m-d',time()));
        $query = LoanCollectionOrder::find()
            ->from(LoanCollectionOrder::tableName().' A')
            ->leftJoin(AdminUser::tableName(). ' B','A.current_collection_admin_user_id = B.id')
            ->leftJoin(UserCompany::tableName(). ' C','B.outside = C.id')
            ->leftJoin(UserLoanOrderRepayment::getDbName().'.'.UserLoanOrderRepayment::tableName() . ' D', 'A.user_loan_order_repayment_id = D.id')
            ->andWhere(['D.overdue_day' => 1])
            ->andWhere(['>=','A.dispatch_time',$start_time])
            ->andWhere(['<','A.dispatch_time',$start_time+86400])
            ->andFilterWhere(['B.group' => $loanGroup])
            ->andFilterWhere(['B.group_game' => $groupGame])
            ->andFilterWhere(['B.outside' => $outside])
            ->andFilterWhere(['like','B.username',$username])
            ->andFilterWhere(['like','B.real_name',$realName]);

        $select = [
            'outside_name' => 'C.real_title',
            'B.group_game',
            'B.group',
            'B.username',
            'B.real_name',
            'total_num' => 'COUNT(1)',
            'complete_num' => 'SUM(IF(D.status = '.UserLoanOrderRepayment::STATUS_REPAY_COMPLETE.',1,0))',
        ];
        $data = $query
            ->select($select)
            ->asArray()
            ->groupBy(['A.current_collection_admin_user_id'])
            ->all();
        //导出
        if($this->request->get('submitcsv') == 'exportcsv'){
            return $this->_exportCollectorDayOneData($data);
        }
        $teamList = CompanyTeam::getTeamsByOutside($outside);
        return $this->render('collection-day-one-data',[
            'data' => $data,
            'teamList' => $teamList,
            'merchant_id' => $this->merchantIds,
        ]);
    }

    /**
     * 催收员D1数据导出方法
     * @param $data
     */
    private function _exportCollectorDayOneData($data){
        $this->_setcsvHeader('催收员D1数据'.date('YmdHi').'.csv');
        $items = [];
        foreach($data as $val){
            $items[] = [
                'username'     => $val['username'],
                'real_name'     => $val['real_name'],
                'company'      => $val['outside_name'],
                '小组分组'      => AdminUser::$group_games[$val['group_game']] ?? '-',
                '催收员分组'     => LoanCollectionOrder::$level[$val['group']] ?? '-',  //分组
                'D1派单数'    =>  $val['total_num'],
                'D1还款单数'    =>  $val['complete_num'],
                'D1还款率'    =>  sprintf("%01.2f", $val['complete_num']/$val['total_num']),
            ];
        }
        echo $this->_array2csv($items);
        exit;
    }

    /**
     * @name CollectionStatisticsController 催收员打卡统计
     * @return string
     */
    public function actionCollectorPunchCardData(){
        $startTime = $this->request->get('start_time',date('Y-m-d',strtotime('-7 day')));
        $endTime = $this->request->get('end_time',date('Y-m-d'));
        $loanGroup = $this->request->get('group');
        $outside = $this->request->get('outside');
        $groupGame = $this->request->get('group_game',[]);
        $collector = $this->request->get('username');
        $realName = $this->request->get('real_name');
        $addressType = $this->request->get('address_type','');

        $queryEnd = CollectionCheckinLog::find()
            ->select(['date','user_id','xbsj' => 'MAX(created_at)'])
            ->where(['type' => CollectionCheckinLog::TYPE_OFF_WORK])
            ->groupBy(['date','user_id']);

        $queryStart = CollectionCheckinLog::find()
            ->select(['id','date','user_id','address_type','sbsj' => 'min(created_at)'])
            ->where(['type' => CollectionCheckinLog::TYPE_START_WORK])
            ->groupBy(['date','user_id']);


        $joinFlag = false;
        $setRealNameCollectionAdmin = GlobalSetting::checkCollectionUsernameCanSetRealName(Yii::$app->user->identity);
        if($this->request->get('submitcsv') == 'joinexportcsv'){
            //联合导出
            $joinFlag = true;

            $select = [
                'date',
                'user_id',
                'total_person' => 'COUNT(1)',
                'total_times' => 'SUM(times)',
                'total_duration' => 'SUM(duration)',

                'invalid_total_person' => 'SUM(IF(is_valid = '.CollectorCallData::INVALID.',1,0))',
                'invalid_total_times' => 'SUM(IF(is_valid = '.CollectorCallData::INVALID.',times,0))',

                'oneself_person' => 'SUM(IF(type = '.CollectorCallData::TYPE_ONE_SELF.',1,0))',
                'oneself_times' => 'SUM(IF(type = '.CollectorCallData::TYPE_ONE_SELF.',times,0))',
                'oneself_duration' => 'SUM(IF(type = '.CollectorCallData::TYPE_ONE_SELF.',duration,0))',

                'invalid_oneself_person' => 'SUM(IF(type = '.CollectorCallData::TYPE_ONE_SELF.' and is_valid = '.CollectorCallData::INVALID.',1,0))',
                'invalid_oneself_times' => 'SUM(IF(type = '.CollectorCallData::TYPE_ONE_SELF.' and is_valid = '.CollectorCallData::INVALID.',times,0))',

                'contact_person' => 'SUM(IF(type = '.CollectorCallData::TYPE_CONTACT.',1,0))',
                'contact_times' => 'SUM(IF(type = '.CollectorCallData::TYPE_CONTACT.',times,0))',
                'contact_duration' => 'SUM(IF(type = '.CollectorCallData::TYPE_CONTACT.',duration,0))',

                'invalid_contact_person' => 'SUM(IF(type = '.CollectorCallData::TYPE_CONTACT.' and is_valid = '.CollectorCallData::INVALID.',1,0))',
                'invalid_contact_times' => 'SUM(IF(type = '.CollectorCallData::TYPE_CONTACT.' and is_valid = '.CollectorCallData::INVALID.',times,0))',

                'address_book_person' => 'SUM(IF(type = '.CollectorCallData::TYPE_ADDRESS_BOOK.',1,0))',
                'address_book_times' => 'SUM(IF(type = '.CollectorCallData::TYPE_ADDRESS_BOOK.',times,0))',
                'address_book_duration' => 'SUM(IF(type = '.CollectorCallData::TYPE_ADDRESS_BOOK.',duration,0))',

                'invalid_address_book_person' => 'SUM(IF(type = '.CollectorCallData::TYPE_ADDRESS_BOOK.' and is_valid = '.CollectorCallData::INVALID.',1,0))',
                'invalid_address_book_times' => 'SUM(IF(type = '.CollectorCallData::TYPE_ADDRESS_BOOK.' and is_valid = '.CollectorCallData::INVALID.',times,0))',
            ];
            $callQueryStart = CollectorCallData::find()
                ->select($select)
                ->groupBy(['date','user_id'])
                ->where([]);


        }

        if(!empty($startTime)){
            $queryEnd->andWhere(['>=','date',$startTime]);
            $queryStart->andWhere(['>=','date',$startTime]);
            if($joinFlag){
                $callQueryStart->andWhere(['>=','date',$startTime]);
            }

        }
        if(!empty($endTime)){
            $queryEnd->andWhere(['<=','date',$endTime]);
            $queryStart->andWhere(['<=','date',$endTime]);
            if($joinFlag){
                $callQueryStart->andWhere(['<=','date',$endTime]);
            }
        }

        if(isset($addressType) && $addressType != ''){
            $queryEnd->andWhere(['address_type' => $addressType]);
            $queryStart->andWhere(['address_type' => $addressType]);
        }

        $queryCheck = CollectionCheckinLog::find()
            ->select([
                'A.id',
                'A.date',
                'A.user_id',
                'A.address_type',
                'A.sbsj',
                'B.xbsj',
            ])
            ->from(['A' => $queryStart])
            ->leftJoin(['B' => $queryEnd],'A.date = B.date AND A.user_id = B.user_id');

        $query = CollectionCheckinLog::find()
            ->select([
                'A.id',
                'A.date',
                'A.address_type',
                'C.real_title',
                'B.group',
                'B.group_game',
                'B.username',
                'B.real_name',
                'A.sbsj',
                'A.xbsj'
            ])
            ->from(['A' => $queryCheck])
            ->leftJoin(AdminUser::tableName(). ' B','A.user_id = B.id')
            ->leftJoin(UserCompany::tableName(). ' C','B.outside = C.id')
            ->where(['B.merchant_id' => $this->merchantIds])
            ->orderBy(['A.date' => SORT_DESC,'A.id' => SORT_DESC]);

        if(!empty($outside)){
            $query->andWhere(['B.outside' => $outside]);
        }
        if(!empty($loanGroup)){
            $query->andWhere(['B.group' => $loanGroup]);
        }
        if(!empty($groupGame)){
            $query->andWhere(['B.group_game' => $groupGame]);
        }
        if(!empty($collector) && $collector != ''){
            $query->andWhere(['like','B.username',$collector]);
        }
        if(!empty($realName) && $realName != ''){
            $query->andWhere(['like','B.real_name',$realName]);
        }
        //联合导出
        if($joinFlag){
            $joinSelect = [
                'B.id','B.address_type','B.sbsj','B.xbsj','A.total_person','A.total_times','A.total_duration',
                'A.invalid_total_person','A.invalid_total_times',
                'A.oneself_person','A.oneself_times','A.oneself_duration',
                'A.invalid_oneself_person','A.invalid_oneself_times',
                'A.contact_person','A.contact_times','A.contact_duration',
                'A.invalid_contact_person','A.invalid_contact_times',
                'A.address_book_person','A.address_book_times','A.address_book_duration',
                'A.invalid_address_book_person','A.invalid_address_book_times'
            ];
            $callQuery = CollectorCallData::find()
                ->select(array_merge(['A.date','A.user_id'],$joinSelect))
                ->from(['A' => $callQueryStart])
                ->leftJoin(['B' => $queryCheck],'A.date = B.date AND A.user_id = B.user_id');

            $callRightQuery = CollectorCallData::find()
                ->select(array_merge(['B.date','B.user_id'],$joinSelect))
                ->from(['A' => $callQueryStart])
                ->rightJoin(['B' => $queryCheck],'A.date = B.date AND A.user_id = B.user_id');
//            echo $callRightQuery->createCommand()->getRawSql();exit;
            $queryAll = $callQuery->union($callRightQuery);

            $queryJoin = (new Query())->from(['A' => $queryAll])
                ->select(['A.*','B.username','B.real_name','C.real_title','B.group','B.group_game'])
                ->leftJoin(AdminUser::tableName(). ' B','A.user_id = B.id')
                ->leftJoin(UserCompany::tableName(). ' C','B.outside = C.id')
                ->where(['B.merchant_id' => $this->merchantIds]);

            if(!empty($outside)){
                $queryJoin->andWhere(['B.outside' => $outside]);
            }
            if(!empty($loanGroup)){
                $queryJoin->andWhere(['B.group' => $loanGroup]);
            }
            if(!empty($groupGame)){
                $queryJoin->andWhere(['B.group_game' => $groupGame]);
            }
            if(!empty($collector) && $collector != ''){
                $queryJoin->andWhere(['like','B.username',$collector]);
            }
            if(!empty($realName) && $realName != ''){
                $queryJoin->andWhere(['like','B.real_name',$realName]);
            }
            if(isset($addressType) && $addressType != ''){
                $queryJoin->andWhere(['A.address_type' => $addressType]);
            }
            $data = $queryJoin->orderBy(['A.date' => SORT_DESC,'A.id' => SORT_DESC])->all(CollectorCallData::getDb_rd());

            return $this->_exportJoinCollectorPunchCardAndCallData($data,$setRealNameCollectionAdmin);
        }


        //导出
        if($this->request->get('submitcsv') == 'exportcsv'){
            $data = $query
                ->asArray()
                ->all();
            return $this->_exportCollectorPunchCardData($data,$setRealNameCollectionAdmin);
        }


        $pages = new Pagination(['totalCount' => $query->count()]);
        $pages->pageSize =  Yii::$app->request->get('page_size',15);
        $data = $query
            ->offset($pages->offset)
            ->limit($pages->limit)
            ->asArray()
            ->all();

        $teamList = CompanyTeam::getTeamsByOutside($outside);
        return $this->render('collector-punch-card-data',[
            'data' => $data,
            'pages' => $pages,
            'teamList' => $teamList,
            'merchant_id' => $this->merchantIds,
            'setRealNameCollectionAdmin' => $setRealNameCollectionAdmin
        ]);
    }

    /**
     * @name CollectionStatisticsController 催收员打卡编辑
     * @return string
     */
    public function actionCollectorPunchCardEdit(){
        $id = $this->request->get('id');

        /** @var CollectionCheckinLog $startModel */
        $startModel = CollectionCheckinLog::find()
            ->where(['type' => CollectionCheckinLog::TYPE_START_WORK,'id' => $id])
            ->one();
        if(is_null($startModel)){
            return $this->redirectMessage('fail',self::MSG_ERROR, Url::toRoute('collection-statistics/collector-punch-card-data'));
        }
        $adminUser = AdminUser::find()->select(['username'])->where(['id' => $startModel->user_id,'merchant_id' => $this->merchantIds])->one();
        if(is_null($adminUser)){
            return $this->redirectMessage('fail',self::MSG_ERROR,Url::toRoute('collection-statistics/collector-punch-card-data'));
        }
        if($this->request->isPost){
            $params = $this->request->post('CollectionCheckinLog',[]);
            $addressType = $params['address_type'] ?? '';
            if(isset(CollectionCheckinLog::$address_type_map[$addressType])){
                $startModel->address_type = $addressType;
                $startModel->save();
                return $this->redirectMessage('success',self::MSG_SUCCESS,'',-2);
            }else{
                return $this->redirectMessage('fail',self::MSG_ERROR,'',-2);
            }
        }


        $name = $adminUser->username;
        return $this->render('collector-punch-card-edit',[
            'model' => $startModel,
            'name' => $name
        ]);
    }

    /**
     * 催收员打卡导出方法
     * @param $data
     * @param $setRealNameCollectionAdmin
     */
    private function _exportCollectorPunchCardData($data,$setRealNameCollectionAdmin){
        $this->_setcsvHeader('催收员打卡数据'.date('YmdHi').'.csv');
        $items = [];
        foreach($data as $val){
            $arr = [
                '日期'        => $val['date'],
                '机构'        => $val['real_title'] ?? '--',
                '小组分组'     => AdminUser::$group_games[$val['group_game']] ?? '--',
                '催收员分组'   => LoanCollectionOrder::$level[$val['group']] ?? '--',  //分组
                '催收员'      => $val['username'],
                '打卡地址' => CollectionCheckinLog::$address_type_map[$val['address_type']] ?? '--',
                '上班打卡时间' => !empty($val['sbsj']) ? date('Y-m-d H:i:s', $val['sbsj']) : '--',
                '下班打卡时间' => !empty($val['xbsj']) ? date('Y-m-d H:i:s', $val['xbsj']) : '--'
            ];

            if($setRealNameCollectionAdmin){
                $arr['real_name'] = $val['real_name'];
            }
            $items[] = $arr;
        }
        // var_dump($items); die;
        echo $this->_array2csv($items);
        exit;
    }

    /**
     * 催收员打卡通时通次联合导出方法
     * @param $data
     * @param $setRealNameCollectionAdmin
     */
    private function _exportJoinCollectorPunchCardAndCallData($data,$setRealNameCollectionAdmin){
        $this->_setcsvHeader('催收员打卡通时通次联合数据'.date('YmdHi').'.csv');
        $items = [];
        foreach($data as $val){
            $arr = [
                '日期'        => $val['date'],
                '机构'        => $val['real_title'] ?? '--',
                '小组分组'     => AdminUser::$group_games[$val['group_game']] ?? '--',
                '催收员分组'   => LoanCollectionOrder::$level[$val['group']] ?? '--',  //分组
                '催收员'      => $val['username'],
                '打卡地址' => CollectionCheckinLog::$address_type_map[$val['address_type']] ?? '--',
                '上班打卡时间' => !empty($val['sbsj']) ? date('Y-m-d H:i:s', $val['sbsj']) : '--',
                '下班打卡时间' => !empty($val['xbsj']) ? date('Y-m-d H:i:s', $val['xbsj']) : '--',
                '总拨打人数'    =>  $val['total_person'] ?? '--',
                '总拨打次数'    =>  $val['total_times'] ?? '--',
                '总拨打秒数'    =>  $val['total_duration'] ?? '--',
                '拨打本人人数'    =>  $val['oneself_person'] ?? '--',
                '拨打本人次数'    =>  $val['oneself_times'] ?? '--',
                '拨打本人时长'    =>  $val['oneself_duration'] ?? '--',
                '无效拨打本人人数'    =>  $val['invalid_oneself_person'] ?? '--',
                '无效拨打本人次数'    =>  $val['invalid_oneself_times'] ?? '--',
                '拨打联系人人数'    =>  $val['contact_person'] ?? '--',
                '拨打联系人次数'    =>  $val['contact_times'] ?? '--',
                '拨打联系人时长'    =>  $val['contact_duration'] ?? '--',
                '无效拨打联系人人数'    =>  $val['invalid_contact_person'] ?? '--',
                '无效拨打联系人次数'    =>  $val['invalid_contact_times'] ?? '--',
                '拨打通讯录人数'    =>  $val['address_book_person'] ?? '--',
                '拨打通讯录次数'    =>  $val['address_book_times'] ?? '--',
                '拨打通讯录时长'    =>   $val['address_book_duration'] ?? '--',
                '无效拨打通讯录人数'    =>  $val['invalid_address_book_person'] ?? '--',
                '无效拨打通讯录次数'    =>  $val['invalid_address_book_times'] ?? '--',
            ];
            if($setRealNameCollectionAdmin){
                $arr['real_name'] = $val['real_name'];
            }
            $items[] = $arr;
        }
        // var_dump($items); die;
        echo $this->_array2csv($items);
        exit;
    }
}



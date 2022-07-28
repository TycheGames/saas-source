<?php

namespace callcenter\controllers;
use backend\models\Merchant;
use callcenter\models\AdminUser;
use callcenter\models\CompanyTeam;
use callcenter\models\loan_collection\LoanCollectionOrder;
use callcenter\models\loan_collection\UserCompany;
use callcenter\models\LoanCollectionTrackStatistic;
use common\models\GlobalSetting;
use Yii;
use yii\data\Pagination;

class CollectionStatisticsNewController extends  BaseController{

    /**
     * @name 跟踪统计(新)
     */
    public function actionTotalAdminTrack()
    {
        $select = [
            'today_all_money' => 'SUM(A.today_all_money)',  //当日分派订单到期金额
            'loan_total' => 'SUM(A.loan_total)',   //当日分派订单数
            'all_late_fee' => 'SUM(A.all_late_fee)',   //当日分派订单的应还滞纳金
            'operate_total' => 'SUM(A.operate_total)',  //当日分派后有操作(有写催记)单数
            'today_finish_money' => 'SUM(A.today_finish_money)',  //当日派单后在手中完成的到期金额(和已还去最小)
            'loan_finish_total' => 'SUM(A.loan_finish_total)',  //当日分派后在手中完结的单数
            'true_total_money' => 'SUM(A.true_total_money)',  //当日分派后在手中完结订单的已还款金额
            'today_finish_late_fee' => 'SUM(A.today_finish_late_fee)', //当日分派后在手中完结订单的应还滞纳金总额
            'finish_late_fee' => 'SUM(A.finish_late_fee)',  //当日分派后在手中完结订单的滞纳金min(已还金额-到期金额 ,0)，减免时小
            'oneday_money' => 'SUM(A.oneday_money)',  //当日分派后且在当日完成到期金额(和已还去最小)
            'oneday_total' => 'SUM(A.oneday_total)'  //当日分派后且在当日完成单数
        ];
        $query = LoanCollectionTrackStatistic::find()
            ->from(LoanCollectionTrackStatistic::tableName().' A')
            ->leftJoin(UserCompany::tableName().' B','A.outside_id = B.id')
            ->leftJoin(AdminUser::tableName().' C','A.admin_user_id = C.id');

        $username = $this->request->get('username');
        if(!empty($username) && $username != ''){
            $query->andWhere(['like','C.username',$username]);
        }
        $start_time = $this->request->get('start_time');
        if(!empty($start_time) && $start_time != ''){
            $query->andWhere(['>=','A.dispatch_date',$start_time]);
        }
        $end_time = $this->request->get('end_time');
        if(!empty($end_time) && $end_time != ''){
            $query->andWhere(['<=','A.dispatch_date',$end_time]);
        }
        $loan_group = $this->request->get('loan_group');
        if(!empty($loan_group) && $loan_group != ''){
            $query->andWhere(['A.loan_group' => $loan_group]);
        }
        $order_level = $this->request->get('order_level');
        if(!empty($order_level) && $order_level != ''){
            $query->andWhere(['A.order_level' => $order_level]);
        }
        $outside = $this->request->get('outside');
        if(!empty($outside) && $outside != ''){
            $query->andWhere(['A.outside_id' => $outside]);
        }
        $group_game = $this->request->get('group_game');
        if(!empty($group_game) && $group_game != ''){
            $query->andWhere(['C.group_game' => $group_game]);
        }
        $realName  = $this->request->get('real_name');
        if(!empty($realName) && $realName != ''){
            $query->andWhere(['like','C.real_name',$realName]);
        }
        $realName  = $this->request->get('real_name');
        if(!empty($realName) && $realName != ''){
            $query->andWhere(['like','C.real_name',$realName]);
        }
        // 商户判断
        $userMerchantId  = $this->request->get('user_merchant_id');
        if ($this->isNotMerchantAdmin && $userMerchantId >= 0 && $userMerchantId != '') {
            $query->andWhere(['user_merchant_id' => $userMerchantId]);
        } else {
            $query->andWhere(['user_merchant_id' => $this->merchantIds]);
        }
        $totalQuery = clone $query;
        $dateQuery = clone $query;
        $totalData = $totalQuery->select($select)->asArray()->all();
        $totalData[0]['dispatch_date'] = 'total';
        $dateData = $dateQuery->select(array_merge($select,['A.dispatch_date']))->groupBy(['A.dispatch_date'])->asArray()->all();
        $totalData = array_merge($totalData,$dateData);

        $trackType = $this->request->get('track_type',LoanCollectionTrackStatistic::TRACK_TYPE_DAY);
        $objType = $this->request->get('obj_type',LoanCollectionTrackStatistic::OBJ_TYPE_COLLECTOR);
        $select[] = 'B.real_title';
        $select[] = 'A.user_merchant_id';
        $groupBy = ['A.user_merchant_id','A.outside_id'];
        if($objType == LoanCollectionTrackStatistic::OBJ_TYPE_COLLECTOR){
            $select[] = 'C.username';
            $select[] = 'C.real_name';
            $select[] = 'A.loan_group';
            $select[] = 'A.order_level';
            $select[] = 'C.group_game';
            $groupBy[] = 'A.admin_user_id';
        }elseif($objType == LoanCollectionTrackStatistic::OBJ_TYPE_TEAM){
            $select[] = 'A.loan_group';
            $select[] = 'A.order_level';
            $select[] = 'C.group_game';
            $groupBy[] = 'C.group_game';
            $groupBy[] = 'C.group';
        }
        if($trackType == LoanCollectionTrackStatistic::TRACK_TYPE_DAY){
            $select[] = 'A.dispatch_date';
            $groupBy[] = 'A.dispatch_date';
            $query->orderBy(['A.dispatch_date' => SORT_DESC]);
        }
        $query->groupBy($groupBy);
        $setRealNameCollectionAdmin = GlobalSetting::checkCollectionUsernameCanSetRealName(Yii::$app->user->identity);
        $teamList = CompanyTeam::getTeamsByOutside($outside);

        if (Yii::$app->request->get('submitcsv') == 'exportcsv') {
            $list =  $query->select($select)->asArray()->all();
            return $this->_exportAdminTrack($list,$setRealNameCollectionAdmin,$teamList);
        }

        $pages = new Pagination(['totalCount' => $query->count()]);
        $pages->pageSize =  Yii::$app->request->get('page_size',15);;
        $list =  $query->select($select)
            ->offset($pages->offset)
            ->limit($pages->limit)
            ->asArray()
            ->all();


        return $this->render('total-admin-track', [
            'totalData' => $totalData,
            'list' => $list,
            'pages' => $pages,
            'teamList' => $teamList,
            'merchant_id' => $this->merchantIds,
            'setRealNameCollectionAdmin' => $setRealNameCollectionAdmin,
            'trackType' => $trackType,
            'arrMerchant' => $this->isNotMerchantAdmin ? Merchant::getMerchantId() : [],
            'companyList' => UserCompany::outsideRealName($this->merchantIds)

        ]);
    }

    /*
   * 催收人员每日工作列表导出方法
   */
    private function _exportAdminTrack($list, $setRealNameCollectionAdmin, $teamList)
    {
        $this->_setcsvHeader('跟踪统计(新)导出.csv');
        $items = [];
        foreach ($list as $value) {
            $arr = [
                Yii::T('common', 'date') => $value['dispatch_date'] ?? '-',
                Yii::T('common', 'agency') => $value['real_title'],
                Yii::T('common', 'Collection Groups') => isset($value['loan_group']) ? (LoanCollectionOrder::$level[$value['loan_group']]) ?? '-' : '-',
                Yii::T('common', 'Order group') => isset($value['order_level']) ? LoanCollectionOrder::$level[$value['order_level']] ?? '-' : '-',
                Yii::T('common', 'Grouping') => isset($value['group_game']) ? $teamList[$value['group_game']] ?? '-' : '-',
                Yii::T('common', 'Collector') => $value['username'] ?? '-',
                Yii::T('common', 'Amount due-total') => number_format($value['today_all_money'] / 100,2),
                Yii::T('common', 'Amount due-paid') => number_format($value['today_finish_money'] / 100,2),
                Yii::T('common', 'number of order') => $value['loan_total'],
                Yii::T('common', 'Orders returned') => $value['loan_finish_total'],
                Yii::T('common', 'Repayment rate (amount)') => !empty($value['today_all_money']) ? sprintf("%.2f", number_format(($value['today_finish_money']/$value['today_all_money']),4)*100).'%' : '--',
                Yii::T('common', 'Complete the odd number on the first day') => $value['oneday_total'],
                Yii::T('common', 'First day completion rate') => !empty($value['today_all_money']) ? sprintf("%.2f", number_format(($value['oneday_money']/$value['today_all_money']),4)*100).'%' : '--',
                Yii::T('common', 'Total amount of late payment') => number_format( $value['today_finish_late_fee'] / 100,2),
                Yii::T('common', 'Late payment amount') => number_format( $value['finish_late_fee'] / 100,2),
                Yii::T('common', 'Late payment recovery rate') => !empty($value['today_finish_late_fee'])?sprintf("%.2f", number_format(($value['finish_late_fee']/$value['today_finish_late_fee']),4)*100).'%':'--',
                Yii::T('common', 'Processing capacity') => number_format($value['operate_total']),
            ];
            if($setRealNameCollectionAdmin){
                $arr[Yii::T('common', 'Real Name')] = $value['real_name'] ?? '';
            }
            $items[] = $arr;
        }
        echo $this->_array2csv($items);
        exit;
    }
}



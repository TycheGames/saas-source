<?php

namespace callcenter\service;

use callcenter\models\AdminUser;
use callcenter\models\loan_collection\LoanCollectionOrder;
use callcenter\models\loan_collection\LoanCollectionRecord;
use callcenter\models\loan_collection\UserCompany;
use common\models\ClientInfoLog;
use common\models\order\UserLoanOrder;
use common\models\order\UserLoanOrderExtraRelation;
use common\models\order\UserLoanOrderRepayment;
use common\models\user\LoanPerson;
use common\models\user\UserActiveTime;
use common\models\user\UserWorkInfo;
use Yii;
use yii\data\Pagination;

class CollectionPublicService
{

    /**
     * 根据条件获取订单列表
     * @param $condition
     * @param array $request
     * @param array
     * @return array
     */
    public static function getCollectionOrderList($condition, $merchantId, $request=[])
    {
        $query = LoanCollectionOrder::find()
            ->select('A.*,B.username,B.real_name,C.customer_type,C.name,C.phone,
            D.overdue_fee,
            D.true_total_money,
            D.plan_repayment_time,
            D.total_money,
            D.coupon_money,
            D.delay_reduce_amount,
            D.loan_time,
            D.closing_time,
            D.principal,
            D.cost_fee,
            D.overdue_day,
            D.status as cuishou_status,
            O.is_first,
            G.last_active_time,
            G.last_pay_time,
            G.last_money_sms_time,
            G.max_money,
            G.level_change_call_success_time
            ')
            ->from(LoanCollectionOrder::tableName() . ' A')
            ->leftJoin(AdminUser::tableName() . ' B','A.current_collection_admin_user_id = B.id')
            ->leftJoin(LoanPerson::getDbName().'.'.LoanPerson::tableName() . ' C','A.user_id = C.id')
            ->leftJoin(UserLoanOrderRepayment::getDbName().'.'.UserLoanOrderRepayment::tableName() . ' D', 'A.user_loan_order_repayment_id = D.id')
            ->leftJoin(UserLoanOrder::getDbName().'.'.UserLoanOrder::tableName() . ' O', 'A.user_loan_order_id = O.id')
            ->leftJoin(UserActiveTime::getDbName().'.'.UserActiveTime::tableName() . ' G', 'A.user_id = G.user_id');;
        if(isset($request['state']) && $request['state'] != ''){
            $condition[] = ['F.residential_address_code1' => $request['state']];
            $query->leftJoin(UserLoanOrderExtraRelation::getDbName().'.'.UserLoanOrderExtraRelation::tableName() . 'E', 'A.user_loan_order_id = E.order_id')
                ->leftJoin(UserWorkInfo::getDbName().'.'.UserWorkInfo::tableName() . 'F', 'E.user_work_info_id = F.id');
        }
        if(isset($request['state']) && $request['state'] != '' && isset($search['city']) && $request['city'] != ''){
            $condition[] = ['F.residential_address_code2' => $request['city']];
        }

        $query->where(['A.merchant_id' => $merchantId]);

        foreach ($condition as $item)
        {
            if (is_string($item)) {
                $query->andWhere($item);
            } else {
                $query->andFilterWhere($item);
            }
        }

        if(!empty($request['is_summary']) && $request['is_summary'] == 1){
            $pages = new Pagination(['totalCount' => $query->cache(60)->count('A.id')]);
        }else{
            $pages = new Pagination(['totalCount' => 999999]);
        }
        $pages->page = ($request['page'] ?? 1) - 1;
        $pages->pageSize = $request['per-page'] ?? 15;
        $sortKey = $request['sort_key'] ?? 'A.dispatch_time';
        $sortFlag = $request['sort_val'] ?? 0;
        $sortVal = $sortFlag ? SORT_ASC : SORT_DESC;
        $sort = array_merge([$sortKey => $sortVal],['A.id' => SORT_DESC]);
        $loan_collection_list = $query->orderBy($sort)
            ->offset($pages->offset)
            ->limit($pages->limit)
            ->asArray()->all();
        $ids = array_column($loan_collection_list,'id');
        return['order'=>$loan_collection_list,'page'=>$pages, 'ids' => $ids];
    }

    /**
     * 根据条件获取订单列表
     * @param $condition
     * @param array $request
     * @param array
     * @return array
     */
    public static function getCollectionOrderListReport($condition, $merchantId, $request=[])
    {
        $query = LoanCollectionOrder::find()
            ->select('A.*,B.username,B.real_name,C.customer_type,C.name,C.phone,
            D.overdue_fee,
            D.true_total_money,
            D.plan_repayment_time,
            D.total_money,
            D.coupon_money,
            D.delay_reduce_amount,
            D.loan_time,
            D.closing_time,
            D.principal,
            D.cost_fee,
            D.overdue_day,
            D.status as cuishou_status,
            O.is_first,
            O.is_export,
            H.package_name,
            H.app_market,
            G.last_active_time,
            G.last_pay_time,
            G.last_money_sms_time,
            G.max_money
            ')
            ->from(LoanCollectionOrder::tableName() . ' A')
            ->leftJoin(AdminUser::tableName() . ' B','A.current_collection_admin_user_id = B.id')
            ->leftJoin(LoanPerson::getDbName().'.'.LoanPerson::tableName() . ' C','A.user_id = C.id')
            ->leftJoin(UserLoanOrderRepayment::getDbName().'.'.UserLoanOrderRepayment::tableName() . ' D', 'A.user_loan_order_repayment_id = D.id')
            ->leftJoin(UserLoanOrder::getDbName().'.'.UserLoanOrder::tableName() . ' O', 'A.user_loan_order_id = O.id')
            ->leftJoin(UserActiveTime::getDbName().'.'.UserActiveTime::tableName() . ' G', 'A.user_id = G.user_id')
            ->leftJoin(ClientInfoLog::getDbName().'.'.ClientInfoLog::tableName() . ' H', 'A.user_loan_order_id = H.event_id AND H.event = '.ClientInfoLog::EVENT_APPLY_ORDER);
        if(!empty($request['state'])){
            $condition[] = ['F.residential_address_code1' => $request['state']];
            $query
                ->leftJoin(UserLoanOrderExtraRelation::getDbName().'.'.UserLoanOrderExtraRelation::tableName() . 'E', 'A.user_loan_order_id = E.order_id')
                ->leftJoin(UserWorkInfo::getDbName().'.'.UserWorkInfo::tableName() . 'F', 'E.user_work_info_id = F.id');
        }
        if(!empty($request['state'])  && !empty($search['city'])){
            $condition[] = ['F.residential_address_code2' => $request['city']];
        }
        $query->where(['A.merchant_id' => $merchantId]);
        foreach ($condition as $item)
        {
            if (is_string($item)) {
                $query->andWhere($item);
            } else {
                $query->andFilterWhere($item);
            }
        }
        $sortKey = Yii::$app->request->get('sort_key','A.dispatch_time');
        $sortVal = Yii::$app->request->get('sort_val');
        $sortVal = $sortVal ? SORT_ASC : SORT_DESC;
        $sort = array_merge([$sortKey => $sortVal],['A.id' => SORT_DESC]);
        $loan_collection_list = $query->orderBy($sort)->asArray()->all(LoanCollectionOrder::getDb_rd());
        return $loan_collection_list;
    }


    /**
     * 根据条件获取催记列表
     * @param $condition
     * @param array $merchantId
     * @param array
     * @return array
     */
    public static function getCollectionRecordListReport($condition,$merchantId)
    {
        $query = LoanCollectionRecord::find()
            ->select('A.*,B.username,C.real_title,B.real_name')
            ->from(LoanCollectionRecord::tableName() . ' A')
            ->leftJoin(AdminUser::tableName() . ' B','A.operator = B.id')
            ->leftJoin(UserCompany::tableName(). ' C','B.outside = C.id')
            ->where(['A.merchant_id' => $merchantId]);

        foreach ($condition as $item)
        {
            $query->andFilterWhere($item);
        }
        $totalQuery = clone $query;
        if($totalQuery->count() > 30000){
            return [];
        }
        $list = $query->orderBy(["A.id" => SORT_DESC])->asArray()->all(LoanCollectionRecord::getDb_rd());
        $ids = array_column($list, "order_id");
        $collection_order = LoanCollectionOrder::find()->where(['id'=>$ids])->orderBy(["id" => SORT_DESC])->all();
        $loan_person_ids = array_column($collection_order, 'user_id');
        $base_info = LoanPerson::baseInfoIds($loan_person_ids);
        foreach($collection_order as $v){
            $loanPerson = isset($base_info[$v['user_id']]) ? $base_info[$v['user_id']] : '--';
            $loan_persons[$v['id']]['loan_name'] = !empty($loanPerson['name']) ?$loanPerson['name'] :'--';
        }
        foreach ($list as $key => $value)
        {
            $list[$key]['loan_name'] = isset($loan_persons[$value['order_id']]['loan_name'])?$loan_persons[$value['order_id']]['loan_name']:'--';
        }
        return $list;
    }


    /**
     * user 记录列表
     * @param $condition
     * @param $from  1管理，2工作台
     * @param $merchantId
     * @return array
     */
    public static function collectionRecordInfo($condition, $from, $merchantId)
    {

        $operator_outside = Yii::$app->user->identity->outside;
        if($from==1){
            if($operator_outside > 0){
                $condition[] = ['B.outside' => $operator_outside];
            }
        }elseif ($from==2){
            if($operator_outside > 0){
                $condition[] = ['A.operator' => Yii::$app->user->id];
            }
        }
        $count =  99999;
        $pages = new Pagination(['totalCount' => $count]);
        $page_size = Yii::$app->request->get('page_size',15);
        $pages->pageSize = $page_size;
        $companyData = UserCompany::find()->select(['id', 'real_title'])->indexBy('id')->asArray()->all();
        $query = LoanCollectionRecord::find()
            ->select('A.*,B.username,B.outside,B.real_name')
            ->from(LoanCollectionRecord::tableName() . ' A')
            ->leftJoin(AdminUser::tableName() . ' B','A.operator = B.id')
            ->where(['A.merchant_id' => $merchantId])
            ->orderBy(["A.id" => SORT_DESC]);

        foreach ($condition as $item) {
            $query->andFilterWhere($item);
        }
        $loan_collectionRecord = $query
            ->offset($pages->offset)
            ->limit($pages->limit)
            ->asArray()
            ->all();

        $ids = array_column($loan_collectionRecord, "order_id");
        $collection_order = LoanCollectionOrder::find()->where(['id'=> $ids ])->orderBy(["id" => SORT_DESC])->all();
        $loan_person_ids = array_column($collection_order, 'user_id');
        $base_info = LoanPerson::baseInfoIds($loan_person_ids);
        foreach($collection_order as $v){
            $loanPerson = isset($base_info[$v['user_id']]) ? $base_info[$v['user_id']] : '--';
            $loan_persons[$v['id']]['loan_name'] = !empty($loanPerson['name']) ?$loanPerson['name'] :'--';
        }
        foreach ($loan_collectionRecord as $key => $value)
        {
            $loan_collectionRecord[$key]['loan_name'] = isset($loan_persons[$value['order_id']]['loan_name'])?$loan_persons[$value['order_id']]['loan_name']:'--';
            $loan_collectionRecord[$key]['real_title'] = $companyData[$value['outside']]['real_title'] ?? '';
        }
        return array(
            'record' => $loan_collectionRecord,
            'pages' => $pages,
        );
    }
}
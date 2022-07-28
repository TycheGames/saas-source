<?php


namespace callcenter\controllers;
use backend\models\Merchant;
use callcenter\models\AdminUser;
use callcenter\models\AdminUserRole;
use callcenter\models\CollectionOrderDispatchLog;
use callcenter\models\CompanyTeam;
use callcenter\models\loan_collection\LoanCollectionRecord;
use callcenter\models\loan_collection\LoanCollectionStatusChangeLog;
use callcenter\models\loan_collection\LoanCollectionSuggestionChangeLog;
use callcenter\models\loan_collection\StopRegainInputOrder;
use callcenter\models\loan_collection\UserCompany;
use callcenter\models\search\AdminRecordListSearch;
use callcenter\models\search\CollectionOrderListSearch;
use callcenter\service\CollectionPublicService;
use callcenter\models\loan_collection\LoanCollectionOrder;
use callcenter\service\DispatchService;
use callcenter\service\LoanCollectionService;
use common\helpers\CommonHelper;
use common\models\enum\City;
use common\models\GlobalSetting;
use common\models\order\UserLoanOrder;
use common\models\order\UserLoanOrderRepayment;
use common\models\user\LoanPerson;
use common\models\user\UserActiveTime;
use common\services\message\WeWorkService;
use common\services\repayment\ReductionService;
use Yii;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\data\Pagination;
use yii\base\Exception;


class CollectionController extends  BaseController{
    public $enableCsrfValidation = false;

    /**
     * @name 催收员首页-当前催收员任务
     * @return string
     */
    public function actionMyWork(){
        $mission = LoanCollectionOrder::missionUser();
        return $this->render('my-work', array(
            'mission' => $mission,
        ));
    }

    /**
     * @name 催收记录-列表
     * @return string
     */
    public function actionCollectionRecordList(){
        $search = Yii::$app->request->get();
        $searchForm = new AdminRecordListSearch();
        $condition = $searchForm->search($search);
        $condition = array_merge($condition, AdminUserRole::getConditionNew(Yii::$app->user->identity,'B'));

        $setRealNameCollectionAdmin = GlobalSetting::checkCollectionUsernameCanSetRealName(Yii::$app->user->identity);
        if($this->request->get('submitcsv') == 'exportData' && $this->strategyOperating){
            ini_set('memory_limit', '1024M');
            $date = date('YmdHis');
            $data = CollectionPublicService::getCollectionRecordListReport($condition, $this->merchantIds);
            $this->_setcsvHeader("collectionRecordList{$date}.csv");
            $items = [];
            foreach($data as $value){
                $arr = [
                    'ID' => $value['id'] ?? 0,
                    'Order ID' => $value['order_id'] ?? 0,
                    'Borrower' => $value['loan_name'] ?? '---',
                    'Contacts' => $value['loan_name'] ?? '---',
                    'Relation' => $value['relation'] ?? '---',
                    'Phone' => $value['contact_phone'] ?? '---',
                    'Order level' => isset(LoanCollectionOrder::$level[$value['order_level']])?LoanCollectionOrder::$level[$value['order_level']]:"--",
                    'Operation type' => isset(LoanCollectionRecord::$label_operate_type[$value['operate_type']])?LoanCollectionRecord::$label_operate_type[$value['operate_type']]:"--",
                    'Promise repay time' => empty($value['promise_repayment_time']) ? '--' : date('Y-m-d H:i:s',$value['promise_repayment_time']),
                    'Connect status' => !empty($value['is_connect'])?LoanCollectionRecord::$is_connect[$value['is_connect']]:"--",
                    'Reminder result' => !empty($value['risk_control']) && isset(LoanCollectionRecord::$risk_controls[$value['risk_control']]) ?LoanCollectionRecord::$risk_controls[$value['risk_control']]:"--",
                    'Remarks' => $value['remark'] ?? '',
                    'Status' => isset(LoanCollectionOrder::$status_list[$value['order_state']])?LoanCollectionOrder::$status_list[$value['order_state']]:"--",
                    'Operation time' => !empty($value['operate_at'])?date("Y-m-d H:i:s",$value['operate_at']):"--",
                    'Operator' => $value['username'] ?? "--",
                    'Operator Company' => $value['real_title'] ?? "--",

                ];
                if($setRealNameCollectionAdmin){
                    $arr['Operator real name'] = empty($value['real_name']) ? '--':$value['real_name'];
                }
                $items[] = $arr;
            }
            echo $this->_array2csv($items);
            exit;
        }
        $recordInfo = CollectionPublicService::collectionRecordInfo($condition,1, $this->merchantIds);
        $outside = $this->request->get('outside',0);
        $companyList = UserCompany::outsideRealName($this->merchantIds);
        $teamList = CompanyTeam::getTeamsByOutside($outside);
        return $this->render('/collection/collection-record-list', array(
            'recordInfo' => $recordInfo,
            'from'=>Yii::$app->user->identity->outside > 0 ? false : true,
            'list_type'=>false,
            'companyList'=>$companyList,
            'teamList' => $teamList,
            'setRealNameCollectionAdmin' => $setRealNameCollectionAdmin,
            'strategyOperating' => $this->strategyOperating
        ));
    }

    /**
     * @name 订单状态转换-列表
     * @return string
     */
    public function actionCollectionStatusChangeList(){
        $where = ['merchant_id' => $this->merchantIds];
        if($this->request->get('search_submit')) {        //过滤
            $search = $this->request->get();
            if(!empty($search['id'])) {
                $where['loan_collection_order_id'] = intval($search['id']);
            }
            if(!empty($search['type'])) {
                $where['type'] = intval($search['type']);
            }
        }
        $query = LoanCollectionStatusChangeLog::find()
            ->where($where)
            ->orderBy(['id'=>SORT_DESC]);
        $count = 9999;
        $pages = new Pagination(['totalCount' => $count]);
        $page_size = Yii::$app->request->get('page_size',15);
        $pages->pageSize = $page_size;
        $loan_collection_status_change_log = $query->offset($pages->offset)->limit($pages->limit)->all();
        return $this->render('collection-status-change-list', array(
            'loan_collection_status_change_log' => $loan_collection_status_change_log,
            'pages' => $pages,
        ));

    }

    /**
     * @name 订单借款建议-列表
     * @return string
     */
    public function actionAdminCollectionStatusSuggestList()
    {
        $condition = [];
        if(Yii::$app->request->get('search_submit')) {
            $search = Yii::$app->request->get();
            if(!empty($search['collection_id'])) {
                $condition['loanCollectionSuggestionChangeLog.collection_id'] = intval($search['collection_id']);
            }
            if(!empty($search['order_id'])) {
                $condition['loanCollectionSuggestionChangeLog.order_id'] = intval($search['order_id']);
            }
            if($search['suggestion']!=='') {
                $condition['loanCollectionSuggestionChangeLog.suggestion'] = intval($search['suggestion']);
            }
            if ($search['outside']  !=='') {
                $condition['loanCollectionSuggestionChangeLog.outside'] = intval($search['outside']);
            }

        }
        $query = LoanCollectionSuggestionChangeLog::find()
            ->from(LoanCollectionSuggestionChangeLog::tableName() . ' as loanCollectionSuggestionChangeLog')
            ->select([
                'loanCollectionSuggestionChangeLog.collection_id',
                'loanCollectionSuggestionChangeLog.order_id',
                'loanCollectionSuggestionChangeLog.suggestion_before',
                'loanCollectionSuggestionChangeLog.suggestion',
                'AdminUser.username',
                'UserCompany.real_title',
                'loanCollectionSuggestionChangeLog.outside',
                'loanCollectionOrder.user_id',
                'loanCollectionSuggestionChangeLog.created_at',
                'loanCollectionSuggestionChangeLog.remark'
            ])
            ->leftJoin(LoanCollectionOrder::tableName(). ' as loanCollectionOrder', 'loanCollectionOrder.id = loanCollectionSuggestionChangeLog.collection_id')
            ->leftJoin(AdminUser::tableName(). ' as AdminUser', 'AdminUser.username = loanCollectionSuggestionChangeLog.operator_name')
            ->leftJoin(UserCompany::tableName(). ' as UserCompany', 'UserCompany.id = AdminUser.outside')
            ->where($condition)
            ->andWhere(['loanCollectionSuggestionChangeLog.merchant_id' => $this->merchantIds])
            ->orderBy(['loanCollectionSuggestionChangeLog.id' => SORT_DESC]);
        $count = 999999;
        $pages = new Pagination(['totalCount' => $count]);
        $page_size = Yii::$app->request->get('page_size',15);
        $pages->pageSize = $page_size;
        $loan_collection_suggest_change_log = $query
            ->offset($pages->offset)
            ->limit($pages->limit)
            ->asArray()
            ->all();
        // 通过订单oreder_ids列表返回对应申请人ids
        $loanConllectionPersonIds     = array_column($loan_collection_suggest_change_log, 'user_id');
        $loanConllectionPersonList  = empty($loanConllectionPersonIds) ? array() : LoanPerson::baseInfoIds($loanConllectionPersonIds);

        // 进行借款建议列表增加用户id，操作者的真实姓名
        foreach ($loan_collection_suggest_change_log as $key => $value) {
            $user  = isset($loanConllectionPersonList[$value['user_id']]) ? $loanConllectionPersonList[$value['user_id']] : array();
            if(empty($user)){
               $info['user_name']   = '--';
               $info['phone']       = '--';
            }else{
               $info['user_name']   = $user['name'];
               $info['phone']       = $user['phone'];
            }
            $info = array_merge($info, $value);
            $loan_collection_suggest_change_log[$key] = $info;
            unset($info);
        }

        return $this->render('collection-status-suggest-list', array(
            'loan_collection_suggest_change_log' => $loan_collection_suggest_change_log,
            'pages' => $pages,
            'merchant_id' => $this->merchantIds
        ));
    }


    /**
     * @name 全部订单-列表
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function actionCollectionOrderList(){
        if ($this->getRequest()->getIsPost() &&  $this->request->post('action') == 'update') {
            $where = ['open_status' => AdminUser::$usable_status];
            $where['outside'] = $this->request->post('outside');
            if(Yii::$app->user->identity->outside > 0){
                $where['outside'] = Yii::$app->user->identity->outside;
            }
            $g = $this->request->post('group_game',0);
            $c = $this->request->post('current_overdue_level',0);
            if($g > 0){
                $where['group_game'] = $g;
            }
            if($c > 0){
                $where['group'] = $c;
            }
            $loanCollection = AdminUser::find()
                ->select(['id','username'])
                ->where($where)->asArray()->all();

            echo Json::encode(CommonHelper::HtmlEncodeToArray(array_column($loanCollection,'username','id')));
            exit;
        }
        $search = Yii::$app->request->get();
        $searchForm = new CollectionOrderListSearch();
        $condition = $searchForm->search($search);

        $condition = array_merge($condition, AdminUserRole::getConditionNew(Yii::$app->user->identity,'B'));
        if($this->request->get('submitcsv') == 'exportData') {
            if (empty($condition)) {
                return $this->redirectMessage(Yii::T('common', 'Please select conditions before exporting'), self::MSG_ERROR);
            }
        }
        $is_self = true;
        if(Yii::$app->user->identity->outside > 0){
            $is_self = false;
            $condition[] = ['A.outside' => Yii::$app->user->identity->outside];
        }
        $setRealNameCollectionAdmin = GlobalSetting::checkCollectionUsernameCanSetRealName(Yii::$app->user->identity);
        $companyList = UserCompany::outsideRealName($this->merchantIds);
        if($this->request->get('submitcsv') == 'exportData' && $this->strategyOperating){
            $date = date('YmdHis');
            $data = CollectionPublicService::getCollectionOrderListReport($condition, $this->merchantIds, $search);
            $this->_setcsvHeader("all_list_data{$date}.csv");
            $items = [];
            if(count($data) > 10000){
                echo Yii::T('common', 'The amount of data is too large, please export in stages');exit;
            }
            foreach($data as $value){
                $arr = [
                    'ID' => $value['id'] ?? 0,
                    'Order id' => $value['user_loan_order_id'] ?? 0,
                    'Name' => $value['name'] ?? '---',
                    'OldCustomer' => LoanPerson::$customer_type_list[$value['customer_type']] ?? '-',
                    'Phone' => $this->isHiddenPhone ?  substr_replace($value['phone'],'****',3,4) : $value['phone'],
                    'Money' => ($value['total_money']-$value['overdue_fee']-$value['coupon_money'])/100,
                    'Overdue days' => $value['overdue_day'],
                    'Overdue fee' => $value['overdue_fee']/100,
                    'Scheduled payment amount' => ($value['total_money'] -  $value['true_total_money'] - $value['coupon_money'] - $value['delay_reduce_amount'])/100,
                    'Should repayment time' => empty($value['plan_repayment_time'])?"--":date("Y-m-d",(int)$value['plan_repayment_time']),
                    'Overdue level' => LoanCollectionOrder::$level[$value['current_overdue_level']],
                    'Status' => isset(LoanCollectionOrder::$status_list[$value['status']])?LoanCollectionOrder::$status_list[$value['status']]:"",
                    'promise repayment time' =>$value['promise_repayment_time'] ? date('Y-m-d H:i',$value['promise_repayment_time']) : '-',
                    'Repayment status' => isset(UserLoanOrderRepayment::$repayment_status_map[$value['cuishou_status']])?UserLoanOrderRepayment::$repayment_status_map[$value['cuishou_status']]:"",
                    'Last collection time' => empty($value['last_collection_time'])?"--":date("m/d",$value['last_collection_time']).' '.date("H:i",$value['last_collection_time']),
                    'Repaid amount' => $value['true_total_money']/100,
                    'Repayment complete time' => empty($value['closing_time'])?"--":date("Y-m-d H:i",$value['closing_time']),
                    'next loan suggest' => empty($value['next_loan_advice']) ? LoanCollectionOrder::$next_loan_advice[0] : LoanCollectionOrder::$next_loan_advice[$value['next_loan_advice']],
                    'Current collector' => empty($value['username']) ? '--':$value['username'],
                    'Company' =>  $companyList[$value['outside']] ?? '--',
                    'Dispatch Collector time' => empty($value['dispatch_time'])?"--":date("y/m/d H:i",$value['dispatch_time']),
                    'Dispatch Company time' => empty($value['dispatch_outside_time'])?"--":date("y/m/d H:i",$value['dispatch_outside_time']),
                    'Package name' => $value['is_export'] == UserLoanOrder::IS_EXPORT_YES ? explode('_',$value['app_market'])[1] : $value['package_name'],
                ];
                if($setRealNameCollectionAdmin){
                    $arr['collector real name'] = empty($value['real_name']) ? '--':$value['real_name'];
                }
                $items[] = $arr;
            }
            echo $this->_array2csv($items);
            exit;
        }
        $collection_lists = CollectionPublicService::getCollectionOrderList($condition, $this->merchantIds, $search);
        $repayCount = [];
        $user_ids = array_column($collection_lists['order'],'user_id');
        if(!empty($user_ids)){
            $repayCount = UserLoanOrderRepayment::find()->select(['user_id','count' => 'COUNT(id)'])
                ->where([
                    'user_id' => $user_ids,
                    'status' => UserLoanOrderRepayment::STATUS_REPAY_COMPLETE,
                    'merchant_id' => $this->merchantIds
                    ])
                ->groupBy(['user_id'])
                ->indexBy(['user_id'])
                ->asArray()
                ->all();
        }

        //催记次数
        $orderIds = array_column($collection_lists['order'],'id');
        $recordCount = LoanCollectionRecord::getCollectionRecordCount($orderIds);

        //申请减免信息
        $reductionService = new ReductionService();
        foreach ($collection_lists['order'] as &$item){
            $loanCollectionOrder = LoanCollectionOrder::findOne($item['id']);
            $item['isCanReduce'] = $reductionService->operateCheck($loanCollectionOrder);
        }

        $adminUserList = [];
        $teamList = [];
        if($outside = Yii::$app->request->get('outside',0)){
            $teamList = CompanyTeam::getTeamsByOutside($outside);
            $where = ['open_status' => AdminUser::$usable_status];
            $where['outside'] = $outside;
            if($groupGame = Yii::$app->request->get('group_game',0)){
                $where['group_game'] = $groupGame;
            }
            if($group = Yii::$app->request->get('current_overdue_level',0)){
                $where['group'] = $group;
            }
            $adminUserList = array_column(AdminUser::find()
                ->select(['id','username'])
                ->where($where)
                ->asArray()->all(),'username','id');
        }
        return $this->render('_collection_order_list', array(
            'loan_collection_list' => $collection_lists['order'],
            'pages' => $collection_lists['page'],
            'is_self'=>$is_self,
            'repayCount' => $repayCount,
            'recordCount' => $recordCount,
            'city' => City::formatForDropdownBoxData(),
            'companyList' => $companyList,
            'openSearchLabel' => Yii::$app->user->identity->open_search_label == AdminUser::CAN_SEARCH_LABEL ?? false,
            'setRealNameCollectionAdmin' => $setRealNameCollectionAdmin,
            'strategyOperating' => $this->strategyOperating,
            'isNotMerchantAdmin' => $this->isNotMerchantAdmin,
            'teamList' => $teamList,
            'adminUserList' => $adminUserList
        ));
    }

    /**
     * @name 按规则派单-机构
     * @return string
     */
    public function actionDispatchToCompanyByRule()
    {
        $loanCollectionService = new LoanCollectionService(Yii::$app->user->id);
        $dispatchService = new DispatchService();
        if($this->getRequest()->getIsPost()){
            $action = $this->request->post('action');
            $merchantId = $this->request->post('merchant_id');
            $activeType = $this->request->post('active_type',0);
            if($this->isNotMerchantAdmin){
                if(empty($merchantId)){
                    $companyMerchantIds = 0;
                    $merchantId = $this->merchantIds;  //array
                }else{
                    $companyMerchantIds = array_merge([$merchantId],[0]);
                }
            }else{
                $companyMerchantIds = $this->merchantIds;
                $merchantId = $this->merchantIds;
            }
            if(!empty($activeType) && $this->strategyOperating && isset(UserActiveTime::$colorMap[$activeType])){
                $activeArr[] = $activeType;
            }else{
                $activeArr = [];
            }

            if($action == 'update'){  //选择获取用户列表
                $companyList = UserCompany::lists($companyMerchantIds);
                $totalCountArray = $dispatchService->getDispatchCount(0, $merchantId, $activeArr);
                echo Json::encode(CommonHelper::HtmlEncodeToArray([ 'code' => 0, 'companyList' => $companyList ,'levelCount' => $totalCountArray]));
                exit;
            }
            $currentOverdueLevel = $this->request->post('current_overdue_level');
            $levelArr = explode('_',$currentOverdueLevel);
            $groupLevel = $levelArr[0];
            $overdueArr = [];
            if(isset($levelArr[1])){
                $overdueArr = explode('-',$levelArr[1]);
            }

            $dispatchCount = $this->request->post('dispatch_count',[]);
            $isFirstArr = $this->request->post('is_first',[]);
            if(empty($dispatchCount)){
                echo Json::encode([ 'code' => -1, 'message' => 'Please select company']);
            }
            //检查派单可行性
            $res = $dispatchService->checkDispatch(0,$groupLevel,$dispatchCount,$isFirstArr,$overdueArr, $merchantId, $activeArr);
            if($res['code'] != 0){
                echo Json::encode($res);exit;
            }

            //添加分派锁 防多人同时分派
            if(!LoanCollectionOrder::lockCollectionDispatchMerchant($merchantId,60)){
                echo json_encode([ 'code' => -1, 'message' => 'Distribution is in progress, please wait']);exit;
            }
            $res = $dispatchService->dispatchCompanyByRule($loanCollectionService,$groupLevel,$dispatchCount,$isFirstArr,$overdueArr, $merchantId, $activeArr);
            if($res['code'] == 0){
                $totalCountArray = $dispatchService->getDispatchCount(0, $merchantId, $activeArr);
                //释放分派锁
                LoanCollectionOrder::releaseCollectionDispatchMerchantLock($merchantId);
                echo Json::encode([ 'code' => 0, 'message' => 'success', 'result' => $res['result'] ,'levelCount' => $totalCountArray]);exit;
            }else{
                //释放分派锁
                LoanCollectionOrder::releaseCollectionDispatchMerchantLock($merchantId);
                echo Json::encode($res);exit;
            }

        }

        $levelArr = [];
        foreach (LoanCollectionOrder::$current_level as $key => $item){
            $levelArr[$key] = $item;
        }
        return $this->render('dispatch-to-company-by-rule', array(
            'levelArr' => $levelArr,
            'isNotMerchantAdmin' => $this->isNotMerchantAdmin,
            'arrMerchantIds' => Merchant::getMerchantByIds($this->merchantIds,false),
            'strategyOperating' => $this->strategyOperating

        ));
    }


    /**
     * @name 待派分订单列表-机构
     * @return string
     */
    public function actionCompanyDispatchOrderList()
    {
        $search = Yii::$app->request->get();
        $searchForm = new CollectionOrderListSearch();
        $condition = $searchForm->search($search);
        $condition[] = ['A.outside' => 0, 'A.status' => LoanCollectionOrder::STATUS_WAIT_COLLECTION];
        $collection_lists = CollectionPublicService::getCollectionOrderList($condition, $this->merchantIds, $search);
        return $this->render('_company_dispatch_order_list', array(
            'loan_collection_list' => $collection_lists['order'],
            'pages' => $collection_lists['page'],
            'openSearchLabel' => Yii::$app->user->identity->open_search_label == AdminUser::CAN_SEARCH_LABEL ?? false,
        ));
    }

    /**
     * @name (功能)订单派分操作-机构
     * @return string
     */
    public function actionLoanCollectionDispatchCompany()
    {
        //得到当前催收订单的信息
        if($this->request->get("order_id")){
            $order_id[] = intval($this->request->get("order_id"),0);
        }
        //群转派
        if($this->request->get('ids')){
            $order_ids = $this->request->get('ids');
            $order_id = explode(',',$order_ids);
        }

        $loanOrders = LoanCollectionOrder::ids($order_id);
        $admin_id = Yii::$app->user->getId();
        $company_list = UserCompany::lists($this->merchantIds);
        foreach ($order_id as $key => $_id) {
            $_order = $loanOrders[$_id];//new
            if($_order['outside'] > 0){
                return $this->redirectMessage('The company have been allocated', self::MSG_ERROR);
            }
            if($_order['status'] != LoanCollectionOrder::STATUS_WAIT_COLLECTION){
                return $this->redirectMessage('order '.$_id.' cannot be assigned', self::MSG_ERROR);
            }
        }
        $loanCollectionService = new LoanCollectionService($admin_id);

        //提交修改
        if ($this->getRequest()->getIsPost()) {
            $outside = intval($this->request->post('outside'),0);
            //添加分派锁 防多人同时分派
            if(!LoanCollectionOrder::lockCollectionDispatchMerchant($_order['merchant_id'],60)){
                return $this->redirectMessage('fail！Distribution is in progress, please wait', self::MSG_ERROR,Url::toRoute('collection/company-dispatch-order-list'));
            }
            foreach ($loanOrders as $order){
                //订单指派
                $res = $loanCollectionService->dispatchToCompany($order['id'],$outside);
                if($res['code'] == DispatchService::ERROR_CODE){
                    LoanCollectionOrder::releaseCollectionDispatchMerchantLock($_order['merchant_id']);
                    return $this->redirectMessage('fail！'.$res['message'], self::MSG_ERROR,Url::toRoute('collection/company-dispatch-order-list'));
                }
            }
            LoanCollectionOrder::releaseCollectionDispatchMerchantLock($_order['merchant_id']);
            return $this->redirectMessage('success！', self::MSG_SUCCESS,Url::toRoute('collection/company-dispatch-order-list'));
        }
        $order_id = implode(',',$order_id);
        return $this->render('loan-collection-outside-dispatch-company', [
            'company_list' => $company_list,
            'collection_order_id'=>$order_id,
        ]);
    }


    /**
     * @name 待派分订单列表-组员
     * @return string
     */
    public function actionDispatchOrderList()
    {
        $search = Yii::$app->request->get();
        $searchForm = new CollectionOrderListSearch();
        $condition = $searchForm->search($search);

        //没有催收人且已分配机构才可以被派分
        $condition[] = ['A.current_collection_admin_user_id' => 0, 'A.status' => LoanCollectionOrder::STATUS_WAIT_COLLECTION];
        $condition[] = ['>', 'A.outside', 0];
        $is_self = true;
        if(Yii::$app->user->identity->outside > 0) {
            $is_self = false;
            $condition[] = ['A.outside' => Yii::$app->user->identity->outside];
        }
        $collection_lists = CollectionPublicService::getCollectionOrderList($condition, $this->merchantIds, $search);

        $companyList = UserCompany::outsideRealName($this->merchantIds);
        return $this->render('_dispatch_order_list', array(
            'loan_collection_list' => $collection_lists['order'],
            'pages' => $collection_lists['page'],
            'is_self' => $is_self,
            'companyList' => $companyList
        ));
    }

    /**
     * @name (功能)按规则派分订单-组员
     * @return string
     */
    public function actionDispatchToPersonByRule()
    {
        $loanCollectionService = new LoanCollectionService(Yii::$app->user->id);
        $isManager = $loanCollectionService->operator->outside == 0;
        $arrMerchantIds = Merchant::getMerchantByIds($this->merchantIds,false);
        if($this->getRequest()->getIsPost()){
            $action = $this->request->post('action');
            $currentOverdueLevel = $this->request->post('current_overdue_level');
            $activeType = $this->request->post('active_type',0);
            if(!empty($activeType) && $this->strategyOperating && isset(UserActiveTime::$colorMap[$activeType])){
                $activeArr[] = $activeType;
            }else{
                $activeArr = [];
            }

            $levelArr = explode('_',$currentOverdueLevel);
            $groupLevel = $levelArr[0];
            $overdueArr = [];
            if(isset($levelArr[1])){
                $overdueArr = explode('-',$levelArr[1]);
            }

            $groupGame = $this->request->post('group_game',0);
            $outside = $isManager ? $this->request->post('outside') : $loanCollectionService->operator->outside;
            if($isManager){
                $outsideArr = $outside ? [0,$outside]: [0];
            }else{
                $outsideArr = [$outside];
            }
            if($this->isNotMerchantAdmin){
                $merchantId = $this->request->post('merchant_id');
            }else{
                $merchantId = $this->merchantIds;
            }
            if(!isset($arrMerchantIds[$merchantId])){
                echo Json::encode( [ 'code' => -1, 'message' => 'merchant failure！']);exit;
            }
            $dispatchService = new DispatchService();

            if($action == 'update'){  //选择获取用户列表
                $loanCollector = $dispatchService->getDispatchCollector($outside,$groupLevel,$groupGame,$merchantId);
                $totalCountArray = $dispatchService->getDispatchCount($outsideArr, $merchantId, $activeArr);
                echo Json::encode(CommonHelper::HtmlEncodeToArray([ 'code' => 0, 'collectionList' => $loanCollector ,'levelCount' => $totalCountArray]));
                exit;
            }

            $dispatchCount = $this->request->post('dispatch_count',[]);
            $isFirstArr = $this->request->post('is_first',[]);

            //检查派单可行性
            $res = $dispatchService->checkDispatch($outsideArr,$groupLevel,$dispatchCount,$isFirstArr,$overdueArr, $merchantId,$activeArr);
            if($res['code'] != 0){
                echo Json::encode($res);exit;
            }
            //添加分派锁 防多人同时分派
            if(!LoanCollectionOrder::lockCollectionDispatchMerchant($merchantId,60)){
                echo json_encode([ 'code' => -1, 'message' => 'Distribution is in progress, please wait']);exit;
            }
            //开始分派
            $result = $dispatchService->dispatchCompanyToOperatorByRule($loanCollectionService,$outsideArr,$groupLevel,$dispatchCount,$isFirstArr,$overdueArr,$merchantId,$activeArr);
            if($result['code'] == 0){
                $totalCountArray = $dispatchService->getDispatchCount($outsideArr, $merchantId, $activeArr);
                //释放分派锁
                LoanCollectionOrder::releaseCollectionDispatchMerchantLock($merchantId);
                echo Json::encode([ 'code' => 0, 'message' => 'success', 'result' => $result['result'], 'levelCount' => $totalCountArray]);exit;
            }else{
                LoanCollectionOrder::releaseCollectionDispatchMerchantLock($merchantId);
                echo Json::encode($result);exit;
            }
        }


        $companyList = [];
        $userCompany = UserCompany::find()
            ->select(['id','real_title','merchant_id'])
            ->where(['merchant_id' => $this->merchantIds,'status' => UserCompany::USING])
            ->asArray()->all();
        foreach ($userCompany as $item){
            $companyList[$item['merchant_id']][$item['id']] = $item;
        }
        $levelArr = [];
        foreach (LoanCollectionOrder::$current_level as $key => $item){
            $levelArr[$key] = $item;
        }
        return $this->render('dispatch-to-person-by-rule', array(
            'companyList' => $companyList,
            'isManager' => $isManager,
            'levelArr' => $levelArr,
            'arrMerchantIds' => $arrMerchantIds,
            'isNotMerchantAdmin' => $this->isNotMerchantAdmin,
            'strategyOperating' => $this->strategyOperating

        ));
    }


    /**
     * @name (功能)组员订单派分操作-组员
     * @return string
     */
    public function actionLoanCollectionDispatch()
    {
        //得到当前催收订单的信息
        if($this->request->get("order_id")){
            $orderId[] = intval($this->request->get("order_id"),0);
        }
        //群转派
        if($this->request->get('ids')){
            $orderIds = $this->request->get('ids');
            $orderId = explode(',',$orderIds);
        }
        $orderOutside = array();
        $orderGroup = array();
        $orderLevel = array();
        $loanOrders = LoanCollectionOrder::ids($orderId);
        $adminId = Yii::$app->user->getId();

        foreach ($orderId as $key => $_id) {
            $_order = $loanOrders[$_id];//new
            if($_order['status'] != LoanCollectionOrder::STATUS_WAIT_COLLECTION){
                return $this->redirectMessage('order cannot be assigned', self::MSG_ERROR);
            }
            if(!empty($orderGroup) && !in_array($_order['current_overdue_group'], $orderGroup)){
                return $this->redirectMessage('The groups must be the same', self::MSG_ERROR);
            }
            $orderGroup[] = $_order['current_overdue_group'];
            if($_order['outside'] == 0){
                return $this->redirectMessage('Undistributed company', self::MSG_ERROR);
            }
            if(!empty($orderOutside) && !in_array($_order['outside'], $orderOutside)){
                return $this->redirectMessage('The order must belong to the same company', self::MSG_ERROR);
            }
            $orderOutside[] = $_order['outside'];
            if(!empty($orderLevel) && !in_array($_order['current_overdue_level'], $orderLevel)){
                return $this->redirectMessage('Order grade must be the same', self::MSG_ERROR);
            }
            $orderLevel[] = $_order['current_overdue_level'];
        }

        $outsideAdmin = AdminUser::find()->where(['outside' => $orderOutside[0],'group' => $orderLevel[0]])->asArray()->all();//得到当前催收机构的员工
        if(empty($outsideAdmin)){
            return $this->redirectMessage('no assigned personnel', self::MSG_ERROR);
        }

        $dispatchUids = [];
        foreach ($outsideAdmin as $val){
            $dispatchUids[$val['group_game']][$val['id']] = $val;
        }

        //提交修改
        if ($this->getRequest()->getIsPost()) {
            $dispatchUid = intval($this->request->post('dispatch_uid'),0);
            $loanCollectionService = new LoanCollectionService($adminId);
            //添加分派锁 防多人同时分派
            if(!LoanCollectionOrder::lockCollectionDispatchMerchant($_order['merchant_id'],60)){
                return $this->redirectMessage('fail！Distribution is in progress, please wait', self::MSG_ERROR,Url::toRoute('collection/dispatch-order-list'));
            }
            foreach ($loanOrders as $order){
                //订单指派
                $res = $loanCollectionService->dispatchToOperator($order->id,$dispatchUid);
                if($res['code'] == DispatchService::ERROR_CODE){
                    LoanCollectionOrder::releaseCollectionDispatchMerchantLock($_order['merchant_id']);
                    return $this->redirectMessage('fail！'.$res['message'], self::MSG_ERROR,Url::toRoute('collection/dispatch-order-list'));
                }
            }
            LoanCollectionOrder::releaseCollectionDispatchMerchantLock($_order['merchant_id']);
            return $this->redirectMessage('success！', self::MSG_SUCCESS,Url::toRoute('collection/dispatch-order-list'));
        }
        $orderId = implode(',',$orderId);
        $company = UserCompany::id($orderOutside[0]);
        return $this->render('loan-collection-outside-dispatch', [
            'outside_other_admin'=>$outsideAdmin,
            'collection_order_id'=>$orderId,
            'order_level' => $orderLevel[0],
            'company' => $company
        ]);
    }


    /**
     * @name 管理-回收订单、批量回收操作
     * @return string
     */
    public function actionLoanCollectionBack()
    {
        $admin_id = Yii::$app->user->getId();
        $order_ids = $this->request->get('ids');
        $order_id = explode(',',$order_ids);
        try{
            $loanOrders = LoanCollectionOrder::ids($order_id);
            $loanCollectionService = new LoanCollectionService($admin_id);
            $arr = [];
            $merchantArr = [1 => 'bigshark',2 => 'moneyclick'];
            $merchantName = '';
            foreach ($loanOrders as $key => $item) {
                if(in_array($item['merchant_id'],[1,2])){
                    $arr[] = $item['id'];
                    $merchantName = $merchantArr[$item['merchant_id']];
                }
                $res = $loanCollectionService->collectionBack($item);
                if($res['code'] != 0){
                    throw new Exception($res['message']);
                }
            }
            if(!empty($arr) && YII_ENV_PROD){
                $weWorkService = new WeWorkService();
                $username = Yii::$app->user->identity->username;
                $weWorkMessage = "用户ID{$admin_id},{$username}操作回收商户{$merchantName}订单,订单号：".implode(',',$arr);
                $weWorkService->sendText(['yanzhenlin'],$weWorkMessage);
            }
        } catch (\Exception $e) {
             return $this->redirectMessage('Recovery of failure！'.$e->getMessage(), self::MSG_ERROR,Url::toRoute('collection/collection-order-list'));
        }
        return $this->redirectMessage('Recycling success！', self::MSG_SUCCESS,Url::toRoute('collection/collection-order-list'));
    }

    /**
     * @name CollectionController 订单分派日志
     * @return string
     */
    public function actionCollectionOrderDispatchLog()
    {
        $condition = [];
        if($this->request->get('search_submit')) {
            $condition[] = 'and';
            $search = $this->request->get();
            if(isset($search['collection_order_id'])&&!empty($search['collection_order_id'])) {
                $condition[] = ['A.collection_order_id' => trim(intval($search['collection_order_id']))];
            }
            if(isset($search['type'])&&!empty($search['type'])) {
                $condition[] = ['A.type' => intval($search['type'])];
            }
            if(isset($search['outside'])&&!empty($search['outside'])) {
                $condition[] = ['A.outside' => intval($search['outside'])];
            }
            if(isset($search['overdue_day'])&&!empty($search['overdue_day'])) {
                $condition[] = ['A.overdue_day' => intval($search['overdue_day'])];
            }
            //派单时间过滤
            if (!empty($search['s_dispatch_time'])) {
                $condition[] = ['>=', 'A.created_at', strtotime($search['s_dispatch_time'])];
            }
            if (!empty($search['e_dispatch_time'])) {
                $condition[] = ['<=', 'A.created_at', strtotime($search['e_dispatch_time'])];
            }
        }
        $query = CollectionOrderDispatchLog::find()
            ->select([
                'A.id',
                'A.collection_order_id',
                'A.operator_id',
                'A.type',
                'A.admin_user_id',
                'B.username',
                'A.overdue_day',
                'A.created_at',
                'C.real_title',
                'D.status'
            ])
            ->from(CollectionOrderDispatchLog::tableName(). ' A')
            ->leftJoin(AdminUser::tableName(). ' B','A.admin_user_id = B.id')
            ->leftJoin(UserCompany::tableName(). ' C','A.outside = C.id')
            ->leftJoin(LoanCollectionOrder::tableName(). ' D',' A.collection_order_id = D.id')
            ->where($condition)
            ->andWhere(['A.merchant_id'=> $this->merchantIds]);

        $count = 9999999;
        $pages = new Pagination(['totalCount' => $count]);
        $page_size = Yii::$app->request->get('page_size',15);
        $pages->pageSize = $page_size;
        $collectionOrderDispatchLog = $query
            ->offset($pages->offset)
            ->limit($pages->limit)
            ->asArray()
            ->orderBy(['A.id'=>SORT_DESC])
            ->all();
        return $this->render('collection-order-dispatch-log', array(
            'collectionOrderDispatchLog' => $collectionOrderDispatchLog,
            'pages' => $pages,
            'merchant_id' => $this->merchantIds
        ));
    }

    /**
     * @name CollectionController 订单停催
     * @return string
     */
    public function actionCollectionStopList()
    {
        $condition = [];
        if ($this->getRequest()->getIsGet()) {
            $search = $this->request->get();
            $condition[] = 'and';
            if (isset($search['order_id']) && $search['order_id'] != '') {
                $condition[] = ['A.user_loan_order_id' => intval($search['order_id'])];
            }
            if (isset($search['start_time']) && !empty($search['start_time'])) {
                $condition[] = ['>=', 'A.updated_at', strtotime($search['start_time'])];
            }
            if (isset($search['end_time']) && !empty($search['end_time'])) {
                $condition[] = ['<=', 'A.updated_at', strtotime($search['end_time'])];
            }
            if (isset($search['phone']) && !empty($search['phone'])) {
                $condition[] = ['C.phone' => trim($search['phone'])];
            }
        }
        $query = LoanCollectionOrder::find()
            ->select([
                'A.id',
                'A.user_loan_order_id',
                'A.user_loan_order_repayment_id',
                'C.phone',
                'C.name',
                'A.updated_at',
                'B.next_input_time'
            ])
            ->from(LoanCollectionOrder::tableName().' A')
            ->leftJoin(StopRegainInputOrder::tableName().' B','A.id = B.collection_order_id AND B.status = '.StopRegainInputOrder::STATUS_INVALID)
            ->leftJoin(LoanPerson::getDbName().'.'.LoanPerson::tableName().' C','A.user_id = C.id')
            ->where($condition)
            ->andWhere(['A.status' => LoanCollectionOrder::STATUS_STOP_URGING])
            ->andWhere(['A.merchant_id' => $this->merchantIds]);

        $pages = new Pagination(['totalCount' => $query->count('A.id')]);
        $page_size = \Yii::$app->request->get('page_size',15);
        $pages->pageSize = $page_size;
        $loanCollectionOrder = $query->orderBy(['A.updated_at' => SORT_DESC])
            ->offset($pages->offset)
            ->limit($pages->limit)
            ->asArray()
            ->all();
        return $this->render('collection-stop-list',[
            'pages'=>$pages,
            'loanCollectionOrder' => $loanCollectionOrder,
        ]);
    }

    /**
     * @name CollectionController 订单添加停催
     * @return string
     */
    public function actionCollectionStopAdd()
    {
        $adminId = Yii::$app->user->getId();
        if($this->request->getIsPost()){
            $order_id = $this->request->post('order_id');
            $isSetInputDate = $this->request->post('is_set_input_date');
            $inputDate = $this->request->post('input_date');
            /** @var LoanCollectionOrder $loanCollectionOrder */
            $loanCollectionOrder = LoanCollectionOrder::find()
                ->where(['user_loan_order_id' => $order_id,'status' => LoanCollectionOrder::STATUS_WAIT_COLLECTION])
                ->andWhere(['merchant_id' => $this->merchantIds])
                ->one();
            if(!$loanCollectionOrder){
                return $this->redirectMessage('collection stop fail', self::MSG_ERROR, Url::toRoute(['collection/collection-stop-list']));
            }
            $loanCollectionService = new LoanCollectionService($adminId);
            if($isSetInputDate == 1){
                $inputTime = strtotime($inputDate) ?? 0;
            }else{
                $inputTime = 0;
            }
            if($loanCollectionService->collectionStop($loanCollectionOrder,$inputTime)){
                return $this->redirectMessage('collection stop success', self::MSG_SUCCESS, Url::toRoute(['collection/collection-stop-list']));
            }else{
                return $this->redirectMessage('collection stop fail', self::MSG_ERROR, Url::toRoute(['collection/collection-stop-list']));
            }
        }
        return $this->render('collection-stop-add',[

        ]);
    }

    /**
     * @name CollectionController 订单停催恢复
     * @return string
     */
    public function actionCollectionStopRecovery($id)
    {
        $adminId = Yii::$app->user->getId();
        /** @var LoanCollectionOrder $loanCollectionOrder */
        $loanCollectionOrder = LoanCollectionOrder::find()
            ->where(['id' => $id,'status' => LoanCollectionOrder::STATUS_STOP_URGING])
            ->andWhere(['merchant_id' => $this->merchantIds])
            ->one();
        if(!$loanCollectionOrder){
            return $this->redirectMessage('collection stop recovery fail', self::MSG_ERROR, Url::toRoute(['collection/collection-stop-list']));
        }
        $loanCollectionService = new LoanCollectionService($adminId);
        if($loanCollectionService->collectionRecovery($loanCollectionOrder)){
            return $this->redirectMessage('collection stop recovery success', self::MSG_SUCCESS, Url::toRoute(['collection/collection-stop-list']));
        }else{
            return $this->redirectMessage('collection stop recovery fail', self::MSG_ERROR, Url::toRoute(['collection/collection-stop-list']));
        }
    }

}


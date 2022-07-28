<?php
namespace backend\controllers;

use backend\models\AdminNxUser;
use backend\models\AdminUser;
use backend\models\AdminUserCaptcha;
use backend\models\AdminUserRole;
use backend\models\Merchant;
use backend\models\remind\RemindAdmin;
use backend\models\remind\ReminderClassSchedule;
use backend\models\remind\RemindGroup;
use backend\models\remind\RemindLog;
use backend\models\remind\RemindOrder;
use backend\models\remind\RemindSetting;
use backend\models\remind\RemindSmsTemplate;
use backend\models\RemindCheckinLog;
use backend\models\ReminderCallData;
use backend\models\search\RemindOrderListSearch;
use callcenter\models\CollectorCallData;
use common\helpers\CommonHelper;
use common\helpers\MessageHelper;
use common\helpers\RedisQueue;
use common\models\ClientInfoLog;
use common\models\manual_credit\ManualCreditLog;
use common\models\manual_credit\ManualCreditModule;
use common\models\manual_credit\ManualCreditRules;
use common\models\manual_credit\ManualCreditType;
use common\models\message\NxPhoneLog;
use common\models\order\FinancialLoanRecord;
use common\models\order\UserLoanOrder;
use common\models\order\UserLoanOrderRepayment;
use common\models\order\UserOrderLoanCheckLog;
use common\models\package\PackageSetting;
use common\models\product\ProductSetting;
use common\models\risk\RiskBlackList;
use common\models\stats\RemindDayData;
use common\models\stats\RemindReachRepay;
use common\models\user\LoanPerson;
use common\models\user\UserVerification;
use common\models\workOrder\UserApplyComplaint;
use common\models\workOrder\UserApplyReduction;
use common\models\workOrder\UserWorkOrderAcceptLog;
use common\services\customer_remind\CustomerRemindService;
use common\services\order\OrderService;
use common\services\pay\RazorpayService;
use common\services\user\UserComplaintService;
use yii\helpers\Url;
use Yii;
use yii\data\Pagination;
use yii\base\Exception;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class CustomerController extends BaseController {
    public $enableCsrfValidation=false;

    /**
     * @name -Loan order search
     * @name-cn 客服管理-借款数据搜索
     * @return
     */
    public function actionLoanOrder(){
        $where = [];
        $data = [];
        $pages = new Pagination(['totalCount' => 0]);
        if ($this->getRequest()->getIsGet()) {
            $search = $this->request->get();
            if (!empty($search['name'])) {
                $where['loanPerson.name'] = $search['name'];
            }
            if (!empty($search['phone'])) {
                $where['loanPerson.phone'] = $search['phone'];
            }
        }

        if(!empty($where)){
            //总单数
            $query = UserLoanOrder::find()
                ->from(UserLoanOrder::tableName() . ' as userLoanOrder')
                ->leftJoin(UserLoanOrderRepayment::tableName() . 'as userLoanOrderRepayment', 'userLoanOrderRepayment.order_id = userLoanOrder.id')
                ->leftJoin(LoanPerson::tableName() . 'as loanPerson', 'loanPerson.id = userLoanOrder.user_id')
                ->leftJoin(ClientInfoLog::tableName() . 'as clientInfoLog', 'clientInfoLog.event_id = userLoanOrder.id and clientInfoLog.event = '.ClientInfoLog::EVENT_APPLY_ORDER)
                ->leftJoin(ProductSetting::tableName() . 'as productSetting', 'productSetting.id = userLoanOrder.product_id')
                ->where($where)->andWhere(['loanPerson.merchant_id' => $this->merchantIds]);

            $count = 9999999;
            $pages = new Pagination(['totalCount' => $count]);
            $pages->pageSize = \yii::$app->request->get('per-page', 15);
            $data = $query
                ->select(
                    [
                        'userLoanOrder.id', //订单号
                        'userLoanOrder.amount', //订单金额
                        'userLoanOrder.interests', //订单利息
                        'userLoanOrder.user_id', //用户ID
                        'userLoanOrder.loan_term', //订单期限
                        'userLoanOrder.loan_method', //期限单位
                        'userLoanOrder.periods', //订单期数
                        'userLoanOrder.order_time', //下单时间
                        'userLoanOrder.loan_time', //放款时间
                        'userLoanOrder.status', //订单状态
                        'userLoanOrder.audit_status', //审核状态
                        'userLoanOrder.is_export', //是否导流订单
                        'clientInfoLog.package_name', //下单包名
                        'clientInfoLog.app_market', //下单app market
                        'loanPerson.name', //姓名
                        'loanPerson.pan_code', //panCode
                        'loanPerson.phone', //手机号
                        'loanPerson.customer_type', //是否为老用户
                        'userLoanOrderRepayment.closing_time', //订单完结时间
                        'productSetting.product_name'
                    ]
                )
                ->offset($pages->offset)
                ->limit($pages->limit)
                ->orderBy(['userLoanOrder.id' => SORT_DESC])
                ->asArray()
                ->all();
        }


        $status_data = [];
        foreach ($data as $item) {
            $status_data[$item['id']] = isset(UserLoanOrder::$order_status_map[$item['status']]) ? UserLoanOrder::$order_status_map[$item['status']] : '';
        }
        return $this->render('loan-order', array(
            'data_list' => $data,
            'status_data'=>$status_data,
            'pages' => $pages,
        ));
    }

    /**
     * @name -Loan order detail
     * @param $id
     * @return string
     * @name-cn 客服管理-借款数据详情-查看
     * @throws NotFoundHttpException
     */
    public function actionLoanDetail($id)
    {
        $id = CommonHelper::idDecryption($id);

        /** @var UserLoanOrder $order */
        $order = UserLoanOrder::find()->where(['id' => $id, 'merchant_id' => $this->merchantIds])->one();
        if (empty($order)) {
            throw new NotFoundHttpException(Yii::T('common', 'Order does not exist'));
        }
        $service = new OrderService($order);
        $informationAll = $service->getOrderDetailAllInfo();
        $information = $service->getOrderDetailInfo();
        $verification = UserVerification::findOne(['user_id'=>$information['loanPerson']['id']]);
        $information['overdue_day'] = 0;
        // 查询订单逾期天数
        $repayment_info = UserLoanOrderRepayment::find()->where(['order_id' => $id])->asArray()->one();
        if ($repayment_info) {
            $information['overdue_day'] = $repayment_info['overdue_day'];
        }
        $manualQuestion = [];
        $conversionRules = [];
        $logs = ManualCreditLog::find()->where(['order_id'=> $id,'merchant_id' => $this->merchantIds])->all();
        if($logs){
            $arr = [];
            foreach ($logs as $log){
                $queArr = json_decode($log['que_info'],true);
                if(!empty($queArr)){
                    $arr = $arr + $queArr;
                }
            }
            if(!empty($arr)){
                $ids = array_keys($arr);
                $rules = ManualCreditRules::find()
                    ->select('A.*,B.module_id,B.type_name,C.head_code,C.head_name')
                    ->from(ManualCreditRules::tableName(). ' A')
                    ->leftJoin(ManualCreditType::tableName() . ' B','B.id = A.type_id')
                    ->leftJoin(ManualCreditModule::tableName() . ' C','C.id = B.module_id')
                    ->where(['A.id' => $ids])
//                    ->andWhere(['B.status' => ManualCreditType::STATUS_NO,'C.status' => ManualCreditModule::STATUS_NO])
                    ->asArray()
                    ->all();
                $conversionRules =  ManualCreditRules::conversionRules($rules);
                $allRules = [];
                foreach ($rules as $item){
                    $allRules[$item['id']] = $item;
                }

                foreach ($arr as $rule_id => $value){
                    $rules = $allRules[$rule_id];
                    $q = [];
                    if($rules['type'] == ManualCreditRules::TYPE_MULTI){
                        $ques = json_decode($rules['questions'],true);
                        foreach ($ques as $i => $v){
                            if(isset($value[$i])){
                                $q[$i] = ['question' => $v, 'res' => $value[$i]];
                            }
                        }
                    }elseif($rules['type'] == ManualCreditRules::TYPE_SINGLE){
                        $q[] = ['question' => $rules['rule_name'], 'res' => $value];
                    }

                    $manualQuestion[$rules['module_id']][$rules['type_id']][$rule_id] = $q;
                }
            }
        }
        return $this->render('/loan-order/view', array(
            'informationAll' => $informationAll,
            'information' => $information,
            'verification' => $verification,
            'manualQuestion' => $manualQuestion,
            'conversionRules' => $conversionRules
        ));
    }

    /**
     * @name CustomerController DisburseOrder
     * @return string
     */
    public function actionDisburseOrder() {
        $data = [];
        $where = [];
        $pages = new Pagination(['totalCount' => 0]);
        if ($this->getRequest()->getIsGet()) {
            $search = $this->request->get();
            if (!empty($search['name'])) {
                $where['p.name'] = $search['name'];
            }
            if (!empty($search['phone'])) {
                $where['p.phone'] = $search['phone'];
            }

        }
        if(!empty($where)){
            $query = FinancialLoanRecord::find()
                ->from(FinancialLoanRecord::tableName().' as l')
                ->where($where)
                ->andWhere(['l.merchant_id' => $this->merchantIds])
                ->select([
                    'l.*','l.id as rid','p.name','p.source_id','p.phone',
                    'u.loan_term','u.amount','u.loan_method','u.fund_id',
                    'u.order_time'
                ])
                ->leftJoin(LoanPerson::tableName().' as p','l.user_id=p.id')
                ->leftJoin(UserLoanOrder::tableName().' as u','l.business_id=u.id')
                ->orderBy(['l.id'=>SORT_DESC]);
            $count = 9999999;
            $pages = new Pagination(['totalCount' => $count]);
            $pages->pageSize = \yii::$app->request->get('per-page', 15);

            $data = $query->offset($pages->offset)->limit($pages->limit)->asArray()->all();
        }


        return $this->render('disburse-order', [
            'withdraws' => $data,
            'pages' => $pages,
        ]);
    }

    /**
     * @name -Repayment order search
     * @name-cn 客服管理-借款数据搜索
     * @return
     */
    public function actionRepayOrder()
    {
        $where = [];
        $info = [];
        $pages = new Pagination(['totalCount' => 0]);
        if ($this->getRequest()->getIsGet()) {
            $search = $this->request->get();
            if (isset($search['name']) && !empty($search['name'])) {
                $where['loanPerson.name'] = $search['name'];
            }
            if (isset($search['phone']) && !empty($search['phone'])) {
                $where['loanPerson.phone'] = $search['phone'];
            }
            if (isset($search['order_uuid']) && $search['order_uuid'] != '') {
                $where['userLoanOrder.order_uuid'] = $search['order_uuid'];
            }
        }
        if(!empty($where)){
            $query = UserLoanOrderRepayment::find()
                ->from(UserLoanOrderRepayment::tableName() . ' as userLoanOrderRepayment')
                ->where($where)->andWhere(['loanPerson.merchant_id' => $this->merchantIds])
                ->leftJoin(UserLoanOrder::tableName() . 'as userLoanOrder', 'userLoanOrder.id = userLoanOrderRepayment.order_id')
                ->leftJoin(LoanPerson::tableName() . 'as loanPerson', 'loanPerson.id = userLoanOrderRepayment.user_id');
            $count = 99999999;
            $pages = new Pagination(['totalCount' => $count]);
            $pages->pageSize = 15;
            $info = $query
                ->select('
            userLoanOrderRepayment.*,
            loanPerson.name,
            loanPerson.pan_code,
            loanPerson.phone,
            loanPerson.customer_type
            ')
                ->offset($pages->offset)
                ->limit($pages->limit)
                ->orderBy(['userLoanOrderRepayment.id' => SORT_DESC])
                ->asArray()
                ->all();
        }

        return $this->render('repay-order', array(
            'info' => $info,
            'pages' => $pages
        ));
    }

    /**
     * @name -Reapyment detail
     * @param $id
     * @return string
     * @name-cn 借款管理-用户借款管理-借款列表-查看
     */
    public function actionRepayDetail($id)
    {
        /** @var UserLoanOrderRepayment $repayment */
        $repayment = UserLoanOrderRepayment::find()->where(['id' => $id, 'merchant_id' => $this->merchantIds])->one();
        if(!isset($repayment->userLoanOrder)){
            return $this->redirectMessage(Yii::T('common', 'Order does not exist'), self::MSG_ERROR);
        }
        $service = new OrderService($repayment->userLoanOrder);
        $information = $service->getOrderDetailInfo();
        $information['userLoanOrderRepayment'] = $repayment;
        $information['overdue_day'] = 0;
        // 查询订单逾期天数
        $repayment_info = UserLoanOrderRepayment::find()->where(['order_id' => $id])->asArray()->one();
        if ($repayment_info) {
            $information['overdue_day'] = $repayment_info['overdue_day'];
        }
        $checkLog = UserOrderLoanCheckLog::find()
            ->where(['order_id' => $repayment->order_id])
            ->orderBy(['id' => SORT_ASC])
            ->all();
        $info['userOrderLoanCheckLog'] = $checkLog;
        //虚拟账号
        return $this->render('/repay-order/detail', array(
            'information' => $information,
            'virtualAccount' => [
                'va_account' => '-',
                'va_name' => '-',
                'va_ifsc' => '-',
                'address' => '-',
            ]
        ));
    }

    /**
     * @name -用户列表
     * @name-cn 用户列表
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function actionUserList() {
        $where = [];
        $info = [];
        if ($this->getRequest()->getIsGet()) {
            $search = $this->request->get();
            if (isset($search['phone']) && !empty($search['phone'])) {
                $where['p.phone'] = $search['phone'];
            }
        }

        if(!empty($where)){
            $query = LoanPerson::find()
                ->from(LoanPerson::tableName(). ' as p')
                ->select(['p.*','l.black_status'])
                ->leftJoin(RiskBlackList::tableName(). ' as l', 'p.id=l.user_id')
                ->where($where)->andWhere(['p.merchant_id' => $this->merchantIds])->orderBy(['p.id'=>SORT_DESC]);
            $info = $query->asArray()->all();
        }

        return $this->render('user-list', [
            'loan_person' => $info,
        ]);
    }

    /**
     * @name 投诉工单
     * @return string
     */
    public function actionComplaintOrder(){
        $where = [];
        if ($this->getRequest()->getIsGet()) {
            $search = $this->request->get();
            if (!empty($search['name'])) {
                $where['loanPerson.name'] = $search['name'];
            }
            if (!empty($search['phone'])) {
                $where['loanPerson.phone'] = $search['phone'];
            }
            if (isset($search['accept_status']) && $search['accept_status'] != '') {
                $where['userApplyComplaint.accept_status'] = $search['accept_status'];
            }
        }

        //总单数
        $query = UserApplyComplaint::find()
            ->from(UserApplyComplaint::tableName() . ' as userApplyComplaint')
            ->leftJoin(LoanPerson::tableName() . 'as loanPerson', 'loanPerson.id = userApplyComplaint.user_id')
            ->where($where)
            ->andWhere(['userApplyComplaint.merchant_id' => $this->merchantIds]);
        $count = 9999999;
        $pages = new Pagination(['totalCount' => $count]);
        $pages->pageSize = \yii::$app->request->get('per-page', 15);
        $data = $query
            ->select(
                [
                    'userApplyComplaint.id', //id
                    'userApplyComplaint.user_id', //用户ID
                    'userApplyComplaint.accept_status',
                    'loanPerson.name', //姓名
                    'loanPerson.pan_code', //panCode
                    'loanPerson.phone', //手机号
                    'loanPerson.customer_type', //是否为老用户
                    'userApplyComplaint.problem_id', //投诉项目
                    'userApplyComplaint.description', //描述
                    'userApplyComplaint.contact_information', //联系信息
                ]
            )
            ->offset($pages->offset)
            ->limit($pages->limit)
            ->orderBy(['userApplyComplaint.id' => SORT_DESC])
            ->asArray()
            ->all();

        return $this->render('complaint-order', array(
            'list' => $data,
            'pages' => $pages,
        ));
    }

    /**
     * @name 处理投诉的工单
     * @return string
     */
    public function actionAcceptComplaintOrder($id){
        /** @var UserApplyComplaint $userApplyComplaint */
        $userApplyComplaint = UserApplyComplaint::find()->where(['id' => $id,'merchant_id' => $this->merchantIds])->one();
        if(is_null($userApplyComplaint)){
            return $this->redirectMessage('error : accept order not exist', self::MSG_ERROR);
        }
        $userId = Yii::$app->user->getId();
        $info = LoanPerson::find()->where(['id' => $userApplyComplaint->user_id])->asArray()->one();
        if(YII_ENV_PROD){
            $expire = 600;
        }else{
            $expire = 120;
        }
        $info['problem_text'] = UserComplaintService::$problem_map[$userApplyComplaint->problem_id] ?? '-';
        $redis = Yii::$app ->redis;
        if($uid = $redis->executeCommand('GET', ['accept_complaint_order'.$id])){
            if($uid != $userId){
                return $this->redirectMessage('order is lock', self::MSG_ERROR);
            }
        }else{
            $redis->executeCommand('SET', ['accept_complaint_order'.$id, $userId]);
            $redis->executeCommand('EXPIRE', ['accept_complaint_order'.$id, $expire]);
        }
        if($this->request->getIsPost()){
            $result = $this->request->post('result');
            $remark = $this->request->post('remark');
            if(!in_array($result,array_keys(UserWorkOrderAcceptLog::$result_map))){
                return $this->redirectMessage('error : result not exist', self::MSG_ERROR);
            }
            if($userApplyComplaint->accept_status == UserApplyComplaint::ACCEPT_FINISH_STATUS){
                return $this->redirectMessage('error : this work order has finish', self::MSG_ERROR);
            }
            if($result == UserWorkOrderAcceptLog::RESULT_ACCEPT_COMPLETED){
                $userApplyComplaint->accept_status = UserApplyComplaint::ACCEPT_FINISH_STATUS;
            }
            $userApplyComplaint->last_accept_user_id = Yii::$app->user->getId();
            $userApplyComplaint->last_accept_time = time();
            $userApplyComplaint->save();


            $userWorkOrderAcceptLog = new UserWorkOrderAcceptLog();
            $userWorkOrderAcceptLog->type = UserWorkOrderAcceptLog::TYPE_COMPLAINT;
            $userWorkOrderAcceptLog->apply_id = $userApplyComplaint->id;
            $userWorkOrderAcceptLog->accept_user_id = Yii::$app->user->getId();
            $userWorkOrderAcceptLog->remark = $remark;
            $userWorkOrderAcceptLog->result = $result;
            $userWorkOrderAcceptLog->save();
            return $this->redirectMessage('success', self::MSG_SUCCESS, -2);
        }

        return $this->render('accept-complaint-order', array(
            'userApplyComplaint' => $userApplyComplaint,
            'info' => $info,
        ));
    }

    /**
     * @name 投诉的工单详情记录
     * @return string
     */
    public function actionAcceptComplaintOrderDetail($id){
        $userApplyComplaint = UserApplyComplaint::findOne($id);
        if(is_null($userApplyComplaint)){
            return $this->redirectMessage('error : accept order not exist', self::MSG_ERROR);
        }
        $info = LoanPerson::find()->where(['id' => $userApplyComplaint->user_id])->asArray()->one();
        $info['problem_text'] = UserComplaintService::$problem_map[$userApplyComplaint->problem_id] ?? '-';

        $acceptLog = UserWorkOrderAcceptLog::find()
            ->select([
                'A.result',
                'A.remark',
                'A.created_at',
                'B.username as operator_name'
            ])
            ->from(UserWorkOrderAcceptLog::tableName().' A')
            ->leftJoin(AdminUser::tableName(). ' B','A.accept_user_id = B.id')
            ->where(['A.type' => UserWorkOrderAcceptLog::TYPE_COMPLAINT,'A.apply_id' => $id])
            ->orderBy(['A.id' => SORT_DESC])->asArray()->all();
        return $this->render('accept-complaint-order-detail', array(
            'userApplyComplaint' => $userApplyComplaint,
            'acceptLog' => $acceptLog,
            'info' => $info,
        ));
    }

    /**
     * @name All提醒订单列表
     * @return string
     */
    public function actionAllRemindOrderList(){
        $sort = [];
        $search = $this->request->get();
        if (isset($search['sort_key']) && $search['sort_key']!='' && isset($search['sort_val']) && $search['sort_val']!='') {
            $sortVal = $search['sort_val'] ? SORT_ASC : SORT_DESC;
            $sort = [$search['sort_key'] => $sortVal];
        }
        $sort['A.id'] = SORT_DESC;

        $query = RemindOrder::find()
            ->select([
                'A.id as remind_id',
                'A.status as remind_status',
                'A.remind_count',
                'A.dispatch_status',
                'A.dispatch_time',
                'A.customer_group',
                'A.remind_return',
                'A.payment_after_days',
                'B.*',
                'C.name',
                'C.phone',
                'C.customer_type',
                'E.username',
                'F.is_first',
                'F.is_export',
                'G.package_name',
                'G.app_market'
            ])
            ->from(RemindOrder::tableName() . ' A')
            ->leftJoin(UserLoanOrderRepayment::tableName(). ' B',' A.repayment_id = B.id')
            ->leftJoin(LoanPerson::tableName(). ' C',' B.user_id = C.id')
            ->leftJoin(AdminUser::tableName(). ' E',' A.customer_user_id = E.id')
            ->leftJoin(UserLoanOrder::tableName(). ' F',' B.order_id = F.id')
            ->leftJoin(ClientInfoLog::tableName(). ' G',' G.event_id = B.order_id and G.event =' . ClientInfoLog::EVENT_APPLY_ORDER)
            ->where(['A.merchant_id' => $this->merchantIds]);

        if ('search' == yii::$app->request->get('search_submit')) {
            $search = $this->request->get();
            $searchForm = new RemindOrderListSearch();
            $searchArray = $searchForm->search($search);
            foreach ($searchArray as $item)
            {
                $query->andFilterWhere($item);
            }

        }
        $count = 9999999;
        $pages = new Pagination(['totalCount' => $count]);
        $pages->pageSize = \yii::$app->getRequest()->get('per-page', 15);
        $info = $query->orderBy($sort)->offset($pages->offset)->limit($pages->limit)->asArray()->all();

        $user_ids = array_column($info,'user_id');
        $repayCount = [];
        if(!empty($user_ids)){
            $repayCount = UserLoanOrderRepayment::find()->select(['user_id','count' => 'COUNT(id)'])
                ->where(['user_id' => $user_ids, 'status' => UserLoanOrderRepayment::STATUS_REPAY_COMPLETE, 'merchant_id' => $this->merchantIds])
                ->groupBy(['user_id'])
                ->indexBy(['user_id'])
                ->asArray()
                ->all();
        }
        $remindGroup = RemindGroup::find()->indexBy('id')->asArray()->all();
        return $this->render('all-remind-order-list',['list' => $info,'pages' => $pages,'repayCount' => $repayCount, 'remindGroup' => $remindGroup, 'nx_phone' => $this->canUseNx]);

    }

    /**
     * @name 我的提醒订单列表
     * @return string
     */
    public function actionMyRemindOrderList(){
        $operator = Yii::$app->user->getId();

        $sort = [];
        $search = $this->request->get();

        if (isset($search['sort_key']) && $search['sort_key']!='' && isset($search['sort_val']) && $search['sort_val']!='') {
            $sortVal = $search['sort_val'] ? SORT_ASC : SORT_DESC;
            $sort = [$search['sort_key'] => $sortVal];
        }

        $sort['A.id'] = SORT_DESC;
        $query = RemindOrder::find()
            ->select([
                'A.id as remind_id',
                'A.status as remind_status',
                'A.remind_count',
                'A.remind_return',
                'A.payment_after_days',
                'A.dispatch_time',
                'B.*',
                'C.name',
                'C.phone',
                'C.customer_type',
                'F.is_first',
                'F.is_export',
                'G.package_name',
                'G.app_market'
            ])
            ->from(RemindOrder::tableName() . ' A')
            ->leftJoin(UserLoanOrderRepayment::tableName(). ' B',' A.repayment_id = B.id')
            ->leftJoin(LoanPerson::tableName(). ' C',' B.user_id = C.id')
            ->leftJoin(UserLoanOrder::tableName(). ' F',' B.order_id = F.id')
            ->leftJoin(ClientInfoLog::tableName(). ' G',' G.event_id = B.order_id and G.event =' . ClientInfoLog::EVENT_APPLY_ORDER)
            ->where(['B.is_overdue' => UserLoanOrderRepayment::IS_OVERDUE_NO,'A.customer_user_id' => $operator])
            ->andWhere(['F.merchant_id' => $this->merchantIds]);

        if ('search' == yii::$app->request->get('search_submit')) {
            $searchForm = new RemindOrderListSearch();
            $searchArray = $searchForm->search($search);
            foreach ($searchArray as $item)
            {
                $query->andFilterWhere($item);
            }

        }
        $count = 9999999;
        $pages = new Pagination(['totalCount' => $count]);
        $pages->pageSize = \yii::$app->getRequest()->get('per-page', 15);
        $info = $query->orderBy($sort)->offset($pages->offset)->limit($pages->limit)->asArray()->all();

        $user_ids = array_column($info,'user_id');
        $repayCount = [];
        if(!empty($user_ids)){
            $repayCount = UserLoanOrderRepayment::find()->select(['user_id','count' => 'COUNT(id)'])
                ->where(['user_id' => $user_ids, 'status' => UserLoanOrderRepayment::STATUS_REPAY_COMPLETE, 'merchant_id' => $this->merchantIds])
                ->groupBy(['user_id'])
                ->indexBy(['user_id'])
                ->asArray()
                ->all();
        }
        return $this->render('my-remind-order-list',['list' => $info,'pages' => $pages,'repayCount' => $repayCount, 'nx_phone'=>$this->canUseNx]);
    }

    /**
     * @name 我的提醒订单详情
     * @return string
     */
    public function actionRemindDetail(){
        $operator = Yii::$app->user->getId();
        $remindId = $this->request->get('remind_id');
        /** @var RemindAdmin $remindAdmin */
        $remindAdmin = RemindAdmin::find()->where(['admin_user_id' => $operator,'merchant_id' => $this->merchantIds])->one();
        $where = ['A.id' => $remindId,'A.dispatch_status' => RemindOrder::STATUS_FINISH_DISPATCH];
        if($remindAdmin){
            if($remindAdmin->remind_group > 0){
                $where['A.customer_user_id'] = $operator;
            }
            $where['B.is_overdue'] = UserLoanOrderRepayment::IS_OVERDUE_NO;
        }
        $remindOrderInfo = RemindOrder::find()
            ->select([
                'B.*',
                'C.name',
                'C.phone'
            ])
            ->from(RemindOrder::tableName() . ' A')
            ->leftJoin(UserLoanOrderRepayment::tableName(). ' B',' A.repayment_id = B.id')
            ->leftJoin(LoanPerson::tableName(). ' C',' B.user_id = C.id')
            ->where($where)
            ->andWhere(['A.merchant_id' => $this->merchantIds])
            ->asArray()
            ->one();
        if(is_null($remindOrderInfo)){
            return $this->redirectMessage('order expired or not exist',
                self::MSG_ERROR);
        }
        /** @var UserLoanOrderRepayment $repayment */
        $repayment = UserLoanOrderRepayment::find()->where(['order_id' => $remindOrderInfo['order_id']])->one();
        $order = UserLoanOrder::findOne($remindOrderInfo['order_id']);
        if($this->request->getIsPost()){
            $remindTurn = $this->request->post('remind_turn');
            $paymentAfterDays = $this->request->post('payment_after_days');
            $remindRemark = $this->request->post('remind_remark');
            $smsTemplate = $this->request->post('sms_template');
            if($remindOrderInfo['is_overdue'] == UserLoanOrderRepayment::IS_OVERDUE_YES){
                return $this->redirectMessage('remind fail',
                    self::MSG_ERROR);
            }

            /** @var RemindOrder $remindOrder */
            $remindOrder = RemindOrder::find()->where(['id' => $remindId])->one();
            $msg ='';
            if($remindOrder){
                if(!in_array($remindTurn,array_keys(RemindOrder::$remind_return_map_all))){
                    return $this->redirectMessage('remind fail',
                        self::MSG_ERROR);
                }
                if(!in_array($paymentAfterDays,array_keys(RemindOrder::$payment_after_days_map))){
                    return $this->redirectMessage('remind fail',
                        self::MSG_ERROR);
                }
                if($remindTurn != RemindOrder::REMIND_RETURN_PAYMENT_AFTER_DAYS){
                    $paymentAfterDays = 0;
                }

                if($smsTemplate > 0){
                    if($order->is_export == UserLoanOrder::IS_EXPORT_YES){
                        $packageName = explode('_',$order->clientInfoLog->app_market)[1];
                    }else{
                        $packageName = $order->clientInfoLog->package_name;
                    }
                    /** @var RemindSmsTemplate $remindSmsTemplate */
                    $remindSmsTemplate = RemindSmsTemplate::find()->where(['id' => $smsTemplate,'status' => RemindSmsTemplate::STATUS_USABLE ,'package_name' => $packageName])->one();
                    if(is_null($remindSmsTemplate)){
                        return $this->redirectMessage('remind fail: sms template error',
                            self::MSG_ERROR);
                    }
                    $send_message = str_replace(['#username#','#total_money#','#should_repay_date#','#remind_date#'],
                        [$repayment->loanPerson->name,$repayment->getAmountInExpiryDate() / 100,date('d/m/Y',$repayment->plan_repayment_time),date('d/m/Y')],$remindSmsTemplate->content); // 处理文案内容信息替换
                    //联动世纪
                    $smsLianDongConfigList = [
                        'bigshark' => 'smsService_Nxtele_GigShark',
                        'moneyclick' => 'smsService_Nxtele_MoneyClick_NOTICE',
                    ];
                    if(!isset($smsLianDongConfigList[$order->clientInfoLog->package_name])){
                        return $this->redirectMessage('remind fail : sms not exist',
                            self::MSG_ERROR);
                    }
                    $smsParamsName = $smsLianDongConfigList[$order->clientInfoLog->package_name];
                    MessageHelper::sendAll($remindOrderInfo['phone'],$send_message,$smsParamsName);
                }else{
                    $smsTemplate = 0;
                }

                $remindOrder->status = RemindOrder::STATUS_REMINDED;
                $remindOrder->remind_return = $remindTurn;
                $remindOrder->payment_after_days = $paymentAfterDays;
                $remindOrder->remind_remark = $remindRemark;
                $remindOrder->remind_count = $remindOrder->remind_count + 1;
                if(!$remindOrder->save()){
                    $msg .= json_encode($remindOrder->getErrors());
                }

                $remindLog = new RemindLog();
                $remindLog->remind_id = $remindOrder->id;
                $remindLog->customer_user_id = $remindOrder->customer_user_id;
                $remindLog->operator_user_id = $operator;
                $remindLog->remind_return = $remindTurn;
                $remindLog->payment_after_days = $paymentAfterDays;
                $remindLog->sms_template = $smsTemplate;
                $remindLog->remind_remark = $remindRemark;
                if(!$remindLog->save()){
                    $msg .= json_encode($remindLog->getErrors());
                }

                return $this->redirectMessage('remind success'.$msg,
                    self::MSG_SUCCESS, -2);
            } else{
                return $this->redirectMessage('remind fail : order not exist',
                    self::MSG_ERROR);
            }

        }
        $templateList = RemindSmsTemplate::getTemplate($repayment,$this->merchantIds);
        $remindLog = RemindLog::find()
            ->select(['A.*','B.username as customer_name','C.username as operator_name'])
            ->from(RemindLog::tableName() .' A')
            ->leftJoin(AdminUser::tableName() . 'B','A.customer_user_id = B.id')
            ->leftJoin(AdminUser::tableName() . 'C','A.operator_user_id = C.id')
            ->where(['remind_id' => $remindId])
            ->asArray()
            ->all();
        return $this->render('remind-detail',['remindLog' => $remindLog,'remindOrder' => $remindOrderInfo, 'templateList' => $templateList,'order' => $order,'nx_phone'=>$this->canUseNx]);
    }

    /**
     * @name 提醒设置
     * @return string
     */
    public function actionRemindSettingList(){
        $query = RemindSetting::find()->where(['merchant_id' => $this->merchantIds]);
        $count = $query->count();
        $pages = new Pagination(['totalCount' => $count]);
        $pages->pageSize = $this->request->get('per-page', 15);
        $remindSetting = $query->offset($pages->offset)
            ->limit($pages->limit)
            ->orderBy('id desc')->all();
        return $this->render('remind-setting-list',['list' => $remindSetting, 'pages' => $pages,'isNotMerchantAdmin'=>$this->isNotMerchantAdmin]);
    }

    /**
     * @name 提醒添加设置
     * @return
     */
    public function actionRemindSettingAdd(){
        if($this->request->post()){
            try {
                $post = $this->request->post();
                $setting_info = new RemindSetting();
                if(!$this->isNotMerchantAdmin){
                    $setting_info->merchant_id = Yii::$app->user->identity->merchant_id;
                }else{
                    $setting_info->merchant_id = $post['merchant_id'];
                }
                $setting_info->run_time = strtotime($post['run_time']);
                $setting_info->plan_date_before_day = $post['plan_date_before_day'];
                $setting_info->save();
                return $this->redirectMessage('add success', self::MSG_SUCCESS, Url::toRoute(['customer/remind-setting-list']));

            } catch (\Exception $e) {
                return $this->redirectMessage('add fail,'.$e->getMessage(), self::MSG_ERROR);
            }
        }

        return $this->render('remind-setting-edit',[
            'isNotMerchantAdmin'=>$this->isNotMerchantAdmin,
        ]);
    }

    /**
     * @name 提醒订单派分功能
     * @return string
     */
    public function actionRemindDispatch(){
        $customerRemindService = new CustomerRemindService();
        $merchantList = [];
        if($this->isNotMerchantAdmin){
            $merchantList = Merchant::getMerchantByIds($this->merchantIds,false);
            $merchantId = $this->request->get('merchant_id',array_key_first($merchantList));
            if(!isset($merchantList[$merchantId])){
                return $this->redirectMessage('error', self::MSG_ERROR);
            }
        }else{
            $merchantId = $this->merchantIds;
        }
        if($this->request->getIsPost()){
            $dispatchCount = $this->request->post('dispatch_count',[]);
            if(empty($dispatchCount)){
                echo json_encode([ 'code' => -1, 'message' => 'Please select workers']);exit;
            }

            $dispatchType = $this->request->post('dispatch_type', '');
            if(empty($dispatchType)){
                echo json_encode([ 'code' => -1, 'message' => 'Please refresh']);exit;
            }
            $dispatchTypeArr = explode('_',$dispatchType);
            $planDateBeforeDay = $dispatchTypeArr[0];
            $userType = $dispatchTypeArr[1];
            $res = $customerRemindService->dispatch($dispatchCount,$planDateBeforeDay,$userType,$merchantId);
            echo json_encode($res);exit;
        }
        $count = $customerRemindService->getDispatchCount($merchantId);
        $sumCount = 0;
        foreach ($count as $c){
            $sumCount += $c[CustomerRemindService::USER_TYPE_ALL];
        }

        $adminListA = RemindAdmin::find()
            ->select([
                'A.admin_user_id',
                'B.username',
                'B.phone',
                'A.remind_group'
            ])
            ->from(RemindAdmin::tableName() . ' A')
            ->leftJoin(AdminUser::tableName() . ' B','A.admin_user_id = B.id')
            ->leftJoin(ReminderClassSchedule::tableName().' schedule','A.admin_user_id = schedule.admin_id AND schedule.date = "'.date('Y-m-d').'"')
            ->where(['B.open_status' => AdminUser::$usable_status,'B.merchant_id' => $merchantId])
            ->andWhere(['>','A.remind_group',0])
            ->andWhere(['OR',['schedule.status' => ReminderClassSchedule::STATUS_DEL],['IS','schedule.status',null]])
            ->orderBy(['A.remind_group' => SORT_ASC])
            ->asArray()
            ->all();
        $adminListB = RemindAdmin::find()
            ->select([
                'A.admin_user_id',
                'B.username',
                'B.phone',
                'A.remind_group'
            ])
            ->from(RemindAdmin::tableName() . ' A')
            ->leftJoin(AdminUser::tableName() . ' B','A.admin_user_id = B.id')
            ->leftJoin(ReminderClassSchedule::tableName().' schedule','A.admin_user_id = schedule.admin_id AND schedule.date = "'.date('Y-m-d').'"')
            ->where(['B.open_status' => AdminUser::$usable_status,'B.merchant_id' => $merchantId])
            ->andWhere(['A.remind_group' => 0])
            ->andWhere(['OR',['schedule.status' => ReminderClassSchedule::STATUS_DEL],['IS','schedule.status',null]])
            ->asArray()
            ->all();
        $adminList = array_merge($adminListA,$adminListB);

        //当天分派过的进行中的单数 按人汇总
        $canRecycleData = array_column(RemindOrder::find()
            ->select(['customer_user_id','count' => 'COUNT(1)'])
            ->where([
                'dispatch_status' => RemindOrder::STATUS_FINISH_DISPATCH,
                'merchant_id' => $merchantId,
                'is_test' => RemindOrder::NOT_TEST_CAN_DISPATCH
            ])
            ->groupBy(['customer_user_id'])
            ->asArray()
            ->all(),'count','customer_user_id');
        $remindGroup = RemindGroup::find()->indexBy('id')->asArray()->all();
        return $this->render('remind-dispatch',[
            'count' => $count,
            'sumCount' => $sumCount,
            'adminList' => $adminList,
            'remindGroup' => $remindGroup,
            'canRecycleData' => $canRecycleData,
            'strategyOperating' => $this->strategyOperating,
            'merchantList' => $merchantList,
            'merchantId' => $merchantId
        ]);
    }

    /**
     * @name 提醒订单派分后回收
     * @return array
     */
    public function actionRemindRecycle(){
        if($this->request->isPost){
            $admin_id = $this->request->post('admin_id','');
            $recycleCount = $this->request->post('recycle_count',0);
            $todayDate = date('Y-m-d');
            $this->response->format = Response::FORMAT_JSON;
            $canRecycleData = RemindOrder::find()
                ->select(['id'])
                ->where([
                    'customer_user_id' => $admin_id,
                    'dispatch_status' => RemindOrder::STATUS_FINISH_DISPATCH,
                    'merchant_id' => $this->merchantIds,
                    'is_test' => RemindOrder::NOT_TEST_CAN_DISPATCH
                ])
                ->asArray()
                ->all();
            if(empty($canRecycleData)){
                return ['code' => -1,'message' => 'no data'];
            }

            $remindIds = array_column($canRecycleData,'id','id');

            if($recycleCount > 0){
                if($recycleCount > count($remindIds)){
                    return ['code' => -1,'message' => 'recycle count more than order count'];
                };

                $remindIds = array_rand($remindIds,$recycleCount);
            }else{
                return ['code' => -1,'message' => 'recycle count more is error'];
            }

            RemindOrder::updateAll(
                [
                    'customer_group' => 0,
                    'customer_user_id' => 0,
                    'dispatch_status' => RemindOrder::STATUS_WAIT_DISPATCH
                ],
                [
                    'id' => $remindIds
                ]
            );
            //回收后删除统计数据
            RemindDayData::deleteAll(['date' => $todayDate,'admin_user_id' => $admin_id]);
            return ['code' => 0,'count' => $recycleCount,'message' => 'success'];
        }
    }

    /**
     * @name 提醒角色-列表
     * @return string
     */
    public function actionRemindAdminList(){
        $list = RemindAdmin::find()
            ->select([
                'A.id',
                'A.admin_user_id',
                'B.username',
                'B.phone',
                'C.name',
                'D.name as merchant_name'
                ])
            ->from(RemindAdmin::tableName(). ' A')
            ->leftJoin(AdminUser::tableName(). ' B','A.admin_user_id = B.id')
            ->leftJoin(RemindGroup::tableName(). 'C','A.remind_group = C.id')
            ->leftJoin(Merchant::tableName(). ' D','B.merchant_id = D.id')
            ->where(['B.merchant_id' => $this->merchantIds])
            ->asArray()
            ->all();
        return $this->render('remind-admin-list',[
            'list' => $list,
            'isHiddenPhone' => $this->isHiddenPhone,
            'isNotMerchantAdmin' => $this->isNotMerchantAdmin
        ]);
    }

    /**
     * @name 提醒角色-添加
     * @return string
     */
    public function actionRemindAdminAdd(){
        $model = new RemindAdmin();
        $postData = $this->request->post();
        if ($postData && $model->load($postData)) {
            if($this->isNotMerchantAdmin){
                if( !in_array($model->merchant_id, $this->merchantIds)){
                    return $this->redirectMessage('merchant no exist', self::MSG_ERROR);
                }
            }else{
                $model->merchant_id = $this->merchantIds;
            }
            if($model->validate()){
                if ($model->save()) {
                    return $this->redirectMessage(Yii::T('common', 'add success'), self::MSG_SUCCESS,  Url::toRoute(['remind-admin-list']));
                } else {
                    return $this->redirectMessage(Yii::T('common', 'add fail'), self::MSG_ERROR);
                }
            }
        }
        $groups = [];
        $remindGroup = RemindGroup::find()
            ->select(['id','name','merchant_id'])
            ->where(['merchant_id' => $this->merchantIds])->asArray()->all();
        foreach ($remindGroup as $item){
            $groups[$item['merchant_id']][$item['id']] = $item['name'];
        }
        if($this->isNotMerchantAdmin){
            $remindGroups = [0 => Yii::T('common', 'No grouping')] + ($groups[0] ?? []);
        }else{
            $remindGroups = [0 => Yii::T('common', 'No grouping')] + ($groups[$this->merchantIds] ?? []);
        }

        $merchants = Merchant::getMerchantByIds($this->merchantIds,$this->isNotMerchantAdmin);
        return $this->render('remind-admin-add', [
            'model' => $model,
            'groups' => $groups,
            'remindGroups' => $remindGroups,
            'merchants' => $merchants,
            'isNotMerchantAdmin' => $this->isNotMerchantAdmin
        ]);
    }

    /**
     * @name 提醒角色-编辑
     * @return string
     */
    public function actionRemindAdminEdit(int $id){
        $model = RemindAdmin::findOne(['id' => $id,'merchant_id' => $this->merchantIds]);
        if(!$model){
            return $this->redirectMessage('Remind Admin no exist', self::MSG_ERROR);
        }

        $data = $this->request->post();
        if ($data && $model->load($data)) {
            if($this->isNotMerchantAdmin){
                if( !in_array($model->merchant_id, $this->merchantIds)){
                    return $this->redirectMessage('merchant no exist', self::MSG_ERROR);
                }
            }else{
                $model->merchant_id = $this->merchantIds;
            }

            if($model->validate()){
                if ($model->save()) {
                    return $this->redirectMessage(Yii::T('common', 'Update success'), self::MSG_SUCCESS,  Url::toRoute(['remind-admin-list']));
                } else {
                    return $this->redirectMessage(Yii::T('common', 'Update fail'), self::MSG_ERROR);
                }
            }
        }
        $groups = [];
        $remindGroup = RemindGroup::find()
            ->select(['id','name','merchant_id'])
            ->where(['merchant_id' => $this->merchantIds])->asArray()->all();
        foreach ($remindGroup as $item){
            $groups[$item['merchant_id']][$item['id']] = $item['name'];
        }
        $remindGroups = [0 => Yii::T('common', 'No grouping')] + ($groups[$model->merchant_id] ?? []);
        $merchants = Merchant::getMerchantByIds($this->merchantIds,$this->isNotMerchantAdmin);
        /** @var AdminUser $adminUser */
        $adminUser = AdminUser::find()->where(['id' => $model->admin_user_id,'merchant_id' => $this->merchantIds])->one();
        $model->username = $adminUser->username;
        return $this->render('remind-admin-edit', [
            'model' => $model,
            'groups' => $groups,
            'remindGroups' => $remindGroups,
            'merchants' => $merchants,
            'isNotMerchantAdmin' => $this->isNotMerchantAdmin
        ]);
    }

    /**
     * @name 提醒角色-删除
     * @param int $id
     * @return string
     * @throws \Throwable
     */
    public function actionRemindAdminDel(int $id){
        try{
            $model = RemindAdmin::find()->where(['id' => $id,'merchant_id' => $this->merchantIds])->one();
            if($model->delete()){
                return $this->redirectMessage(Yii::T('common', 'Delete success'), self::MSG_SUCCESS,  Url::toRoute(['remind-admin-list']));
            }else{
                return $this->redirectMessage(Yii::T('common', 'Delete fail'), self::MSG_ERROR,  Url::toRoute(['remind-admin-list']));
            }

        }catch (Exception $e){
            return $this->redirectMessage($e->getMessage(), self::MSG_ERROR);
        }
    }

    /**
     * @name CustomerController 提醒员班表(daily work plan)
     * @return string
     */
    public function actionClassSchedule(){
        $startDate = Yii::$app->request->get('start_date',date('Y-m-d'));
        $endDate = Yii::$app->request->get('end_date',date('Y-m-d',strtotime('+7 days')));
        /** @var AdminUser $adminUser */
        $adminUser = Yii::$app->user->identity;
        $str = "CASE ";
        AdminNxUser::$type_map;
        foreach (AdminNxUser::$type_map as $type => $value){
            $str.= "WHEN `type` = {$type} THEN '{$value}:' ";
        }
        $str.="END";
        $split = '<br/>';
        if($this->request->get('submitcsv') == 'exportData'){
            $split = PHP_EOL;
        }
        $queryNx = AdminNxUser::find()
            ->select([
                'collector_id',
                'nx_name_str' => "GROUP_CONCAT({$str},nx_name ORDER BY type asc SEPARATOR '{$split}' )",
                'nx_password_str' => "GROUP_CONCAT({$str},password ORDER BY type asc SEPARATOR '{$split}' )"
            ])
            ->where(['status' => AdminNxUser::STATUS_ENABLE])->groupBy('collector_id');
        $query = RemindAdmin::find()
            ->select([
                'U.username',
                'U.phone',
                'U.merchant_id',
                'A.admin_user_id',
                'A.remind_group',
                'group_name' => 'G.name',
                'E.nx_name_str',
                'E.nx_password_str',
                'U.open_status',
                'U.updated_at',
                'U.created_at'
            ])
            ->alias('A')
            ->leftJoin(AdminUser::tableName(). ' U','A.admin_user_id = U.id')
            ->leftJoin(RemindGroup::tableName(). ' G','A.remind_group = G.id')
            ->leftJoin(AdminUserRole::tableName().' R','U.role = R.name')
            ->leftJoin(['E' => $queryNx], 'E.collector_id = A.admin_user_id')
            ->where([
                'R.groups' => AdminUserRole::TYPE_SERVICE,'U.merchant_id' => $this->merchantIds
            ])
            ->andWhere(['<','U.created_at',strtotime($endDate) + 86400])
            ->andWhere([
                'OR',
                [
                    //当前已离职
                    'AND',
                    ['U.open_status' => AdminUser::OPEN_STATUS_OFF],
                    ['>','U.updated_at',strtotime($startDate)]
                ],
                ['!=','U.open_status',AdminUser::OPEN_STATUS_OFF]
            ]);
        $myRemindGroup = RemindGroup::find()->select(['id'])->where("FIND_IN_SET({$adminUser->id}, team_leader_id)")->asArray()->all();

        if($myRemindGroup){
            $isManager = 0;
            $query->andWhere(['A.remind_group' => array_column($myRemindGroup,'id')]);
        }else{
            $isManager = 1;
        }
        //查询条件
        $search = Yii::$app->request->get();
        if (isset($search['phone']) && $search['phone'] != '') {
            $search['phone'] = str_replace("'","", $search['phone']);
            $query->andWhere(['like','U.phone',trim($search['phone'])]);
        }
        if (isset($search['username']) && $search['username'] != '') {
            $search['username'] = str_replace("'","", $search['username']);
            $query->andWhere(['like','U.username',trim($search['username'])]);
        }
        if (isset($search['group']) && $search['group'] != '') {
            $query->andWhere(['A.remind_group' => intval($search['group'])]);
        }
        if (isset($search['status']) && $search['status'] != '') {
            $query->andWhere(['U.open_status' => intval($search['status'])]);
        }
        if ($this->isNotMerchantAdmin) {
            if (isset($search['merchant_id']) && $search['merchant_id'] != '') {
                $query->andWhere(['U.merchant_id' => intval($search['merchant_id'])]);
            }else{
                $query->andWhere(['U.merchant_id' => 0]);
            }
        }
        $dateArr = [];
        $startTime = strtotime($startDate);
        $endTime = strtotime($endDate);

        $resArr = ReminderClassSchedule::find()
            ->select(['date','admin_id','status','type','remark'])
            ->where(['status' => ReminderClassSchedule::STATUS_OPEN])
            ->andWhere(['>=', 'date', $startDate])
            ->andWhere(['<=', 'date', $endDate])
            ->asArray()
            ->all();
        foreach ($resArr as $value){
            $dateArr[$value['date']][$value['admin_id']] = ['status' => $value['status'],'type' => $value['type'],'remark' => $value['remark']];
        }
        while ($startTime <= $endTime){
            $date = date('Y-m-d',$startTime);
            if(!isset($dateArr[$date])){
                $dateArr[$date] = [];
            }
            $startTime += 86400;
        }
        ksort($dateArr);
        $list = $query->orderBy(['A.id' => SORT_DESC])->asArray()->all();
        //var_dump($list);exit;
        if($this->request->get('submitcsv') == 'exportData'){
            $date = date('YmdHis');
            $this->_setcsvHeader("class_schedule{$date}.csv");
            $items = [];
            foreach($list as $value){
                $arr = [
                    'group' => $value['group_name'] ?? '--',
                    'id' => $value['admin_user_id'],
                    'username' => $value['username'],
                    'phone' => $value['phone'],
                    'NX_name' => $value['nx_name_str'] ?? '-',
                    'NX_password' => $value['nx_password_str'] ?? '-',
                ];

                foreach ($dateArr as $date => $val) {
                    if(($value['open_status'] == 0 && date('Y-m-d',$value['updated_at']) <= $date) || date('Y-m-d',$value['created_at']) > $date) {
                        if (isset($val[$value['admin_user_id']])) {
                            $arr[$date] = '×(' . (ReminderClassSchedule::$absence_type_map[$val[$value['admin_user_id']]['type']] ?? '-') . ')';
                        }else{
                            $arr[$date] = '--';
                        }
                    } else {
                        if (isset($val[$value['admin_user_id']])) {
                            $arr[$date] = '×(' . (ReminderClassSchedule::$absence_type_map[$val[$value['admin_user_id']]['type']] ?? '-') . ')';
                        } else {
                            $arr[$date] = '√';
                        }
                    }
                }
                $items[] = $arr;
            }
            echo $this->_array2csv($items);
            exit;
        }
        // 获取所有商户，只能是商户管理员的时候才在页面上显示
        if ($this->isNotMerchantAdmin) {
            $merchantList = Merchant::getMerchantId();
        } else {
            $merchantList = [];
        }
        return $this->render('class-schedule',array(
            'list' => $list,
            'dateArr' => $dateArr,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'remindGroup' => RemindGroup::allGroupName(),
            'merchantList' => $merchantList,
            'isManager' => $isManager
        ));
    }

    /**
     * @name CustomerController 提醒员班表获取信息
     * @param int $id
     * @param string $date
     * @return array
     */
    public function actionClassScheduleView($id,$date){
        Yii::$app->response->format = Response::FORMAT_JSON;

        $remindAdmin = RemindAdmin::find()
            ->select(['A.admin_user_id','U.username'])
            ->alias('A')
            ->leftJoin(AdminUser::tableName().' U','A.admin_user_id = U.id')
            ->leftJoin(AdminUserRole::tableName().' R','U.role = R.name')
            ->where([
                'A.admin_user_id' => $id,
                'U.open_status' => AdminUser::$usable_status,
                'R.groups' => AdminUserRole::TYPE_SERVICE,
                'U.merchant_id' => $this->merchantIds
            ])->asArray()->one();
        if($remindAdmin){
            $type = 0;
            $remark = '';
            /** @var ReminderClassSchedule $reminderClassSchedule */
            $reminderClassSchedule = ReminderClassSchedule::find()->where(['date' => $date,'admin_id' => $id])->one();
            if($reminderClassSchedule && $reminderClassSchedule->status == ReminderClassSchedule::STATUS_OPEN){
                $type = $reminderClassSchedule->type;
                $remark = $reminderClassSchedule->remark;
            }
            return [
                'code' => 0,
                'message' => 'success',
                'data' => ['admin_id' => $remindAdmin['admin_user_id'],'username' => $remindAdmin['username'],'date' => $date, 'type' => $type, 'remark' => $remark ,'is_today' => $date == date('Y-m-d')]
            ];
        }else{
            return ['code' => -1,'message' => 'fail'];
        }
    }

    /**
     * @name CustomerController 提醒员班表更新
     * @param int $id
     * @param string $date
     * @param int $is_absence
     * @param string $type
     * @param string $remark
     * @return array
     */
    public function actionClassScheduleEdit($id,$date,$is_absence,$type,$remark){
        Yii::$app->response->format = Response::FORMAT_JSON;
        $data = ['is_absence' => $is_absence,'type' => $type,'remark' => $remark];
        /** @var ReminderClassSchedule $reminderClassSchedule */
        if($is_absence && !isset(ReminderClassSchedule::$absence_type_map[$type])){  //
            return ['code' => -1, 'message' => 'type error'];
        }
        if(date('Y-m-d') > $date){
            return ['code' => -1, 'message' => 'date error'];
        }

        $query = RemindAdmin::find()
            ->select(['A.admin_user_id','U.username'])
            ->alias('A')
            ->leftJoin(AdminUser::tableName().' U','A.admin_user_id = U.id')
            ->leftJoin(AdminUserRole::tableName().' R','U.role = R.name')
            ->where([
                'A.admin_user_id' => $id,
                'U.open_status' => AdminUser::$usable_status,
                'R.groups' => AdminUserRole::TYPE_SERVICE,
                'U.merchant_id' => $this->merchantIds
            ]);

        /** @var AdminUser $currentUser */
        $currentUser = Yii::$app->user->identity;
        $remindGroup = RemindGroup::find()->select(['id'])->where(['team_leader_id' => $currentUser->id])->asArray()->all();
        if($remindGroup){
            $query->andWhere(['A.remind_group' => array_column($remindGroup,'id')]);
        }

        $remindAdmin = $query->asArray()->one();
        if(!$remindAdmin) {
            return ['code' => -1, 'message' => 'user error'];
        }
        //班表限制
        $todayDate = date('Y-m-d');
        $tip = false;
        $tipMessage = 'success';
        if($is_absence && $type == ReminderClassSchedule::WEEK_OFF_TYPE){
            $nextDate = date('Y-m-d',strtotime('+7 days'));
            $reminderClassSchedule = ReminderClassSchedule::find()
                ->where(['admin_id' => $id, 'type' => ReminderClassSchedule::WEEK_OFF_TYPE,'status' => ReminderClassSchedule::STATUS_OPEN])
                ->andWhere(['>', 'date', $todayDate])
                ->andWhere(['<=', 'date', $nextDate])
                ->andWhere(['!=','date',$date])
                ->exists();
            if($reminderClassSchedule){
                $tip = true;
                $tipMessage = 'Week off existed from tomorrow to next week';
            }else{
                if($remindAdmin['remind_group'] > 0){
                    $groupWoffCount = ReminderClassSchedule::find()
                        ->alias('c')
                        ->leftJoin(RemindAdmin::tableName().' u','c.admin_id = u.admin_user_id')
                        ->where([
                            'c.date' => $date,
                            'c.type' => ReminderClassSchedule::WEEK_OFF_TYPE,
                            'c.status' => ReminderClassSchedule::STATUS_OPEN,
                            'u.remind_group' => $remindAdmin['remind_group']
                        ])
                        ->count();
                    $groupCount = RemindAdmin::getGroupSize($remindAdmin['remind_group']);

                    if(empty($groupCount) || ($groupWoffCount / $groupCount) > (1/7)){
                        $tip = true;
                        $tipMessage = 'Too many people work off in the group';
                    }
                }
            }
        }

        if($remindGroup){
            $time = time();
            $week = date('w',$time);
            $hour = date('H',$time);
            if($date > date('Y-m-d') && $week == 2 && $hour >= 13 &&  $hour <= 18){
                if($tip){
                    return ['code' => -1, 'message' => $tipMessage];
                }
            }else{
                return ['code' => -1, 'message' => 'can\'t edit today'];
            }
        }
        $reminderClassSchedule = ReminderClassSchedule::find()->where(['admin_id' => $id,'date' => $date])->one();
        if($reminderClassSchedule){
            if($is_absence){  //缺勤
                $reminderClassSchedule->type = $type;
                $reminderClassSchedule->remark = $remark;
                $reminderClassSchedule->operator_id = Yii::$app->user->getId();
                $reminderClassSchedule->status = ReminderClassSchedule::STATUS_OPEN;

                if(date('Y-m-d') == $date){
                    RemindOrder::updateAll(
                        [
                            'customer_group' => 0,
                            'customer_user_id' => 0,
                            'dispatch_status' => RemindOrder::STATUS_WAIT_DISPATCH
                        ],
                        [
                            'customer_user_id' => $id,
                            'dispatch_status' => RemindOrder::STATUS_FINISH_DISPATCH,
                        ]
                    );
                    //回收后删除当天统计数据
                    RemindDayData::deleteAll(['date' => $date,'admin_user_id' => $id]);
                }
            }else{
                $reminderClassSchedule->operator_id = Yii::$app->user->getId();
                $reminderClassSchedule->status = ReminderClassSchedule::STATUS_DEL;
            }
            $reminderClassSchedule->save();
        }else{
            if($is_absence) {  //缺勤
                if(date('Y-m-d') == $date){
                    RemindOrder::updateAll(
                        [
                            'customer_group' => 0,
                            'customer_user_id' => 0,
                            'dispatch_status' => RemindOrder::STATUS_WAIT_DISPATCH
                        ],
                        [
                            'customer_user_id' => $id,
                            'dispatch_status' => RemindOrder::STATUS_FINISH_DISPATCH,
                        ]
                    );
                    //回收后删除当天统计数据
                    RemindDayData::deleteAll(['date' => $date,'admin_user_id' => $id]);
                }
                $reminderClassSchedule = new ReminderClassSchedule();
                $reminderClassSchedule->date = $date;
                $reminderClassSchedule->admin_id = $id;
                $reminderClassSchedule->type = $type;
                $reminderClassSchedule->remark = $remark;
                $reminderClassSchedule->operator_id = Yii::$app->user->getId();
                $reminderClassSchedule->status = ReminderClassSchedule::STATUS_OPEN;
                $reminderClassSchedule->save();
            }
        }
        return ['code' => 0,'message' => $tipMessage,'data' => $data];

    }

    /**
     * @name 提醒短信模板-列表
     * @return string
     */
    public function actionRemindSmsTemplate(){
        $query = RemindSmsTemplate::find()->where(['merchant_id' => $this->merchantIds]);
        $count = $query->count();
        $pages = new Pagination(['totalCount' => $count]);
        $pages->pageSize = $this->request->get('per-page', 15);
        $list = $query->offset($pages->offset)
            ->limit($pages->limit)
            ->orderBy('id desc')->all();
        return $this->render('remind-sms-template',[
            'list' => $list,
            'pages' => $pages
        ]);
    }

    /**
     * @name 提醒短信模板-添加
     * @return string
     */
    public function actionRemindTemplateAdd(){
        $model = new RemindSmsTemplate();
        $arrPackage = PackageSetting::getAllLoanPackageNameMap($this->merchantIds);
        $postData = $this->request->post();
        if ($postData && $model->load($postData) && $model->validate()) {
            $model->merchant_id = Yii::$app->user->identity->merchant_id;
            if ($model->save()) {
                return $this->redirectMessage('add success', self::MSG_SUCCESS,  Url::toRoute(['remind-sms-template']));
            } else {
                return $this->redirectMessage('add fail', self::MSG_ERROR);
            }
        }
        return $this->render('remind-template-add', [
            'model' => $model,
            'arrPackage' => $arrPackage
        ]);
    }

    /**
     * @name 提醒短信模板-编辑
     * @return string
     */
    public function actionRemindTemplateEdit(int $id){
        $model = RemindSmsTemplate::findOne(['id' => $id,'merchant_id' => $this->merchantIds]);
        $arrPackage = PackageSetting::getAllLoanPackageNameMap($this->merchantIds);
        $postData = $this->request->post();
        if ($postData && $model->load($postData) && $model->validate()) {
            if ($model->save()) {
                return $this->redirectMessage('edit success', self::MSG_SUCCESS,  Url::toRoute(['remind-sms-template']));
            } else {
                return $this->redirectMessage('edit fail', self::MSG_ERROR);
            }
        }
        return $this->render('remind-template-add', [
            'model' => $model,
            'arrPackage' => $arrPackage
        ]);
    }

    /**
     * @name 根据手机号或用户名获取admin id
     * @return string
     */
    public function actionGetAdminIdByPhone($phone){
        $adminUser = AdminUser::find()->where(['username' => $phone,'merchant_id' => $this->merchantIds])->one();
        if($adminUser){
            echo json_encode(['code' => 0,'admin_id' => $adminUser->id]);exit;
        }else{
            $adminUser = AdminUser::find()->where(['phone' => $phone,'merchant_id' => $this->merchantIds])->one();
            if($adminUser){
                echo json_encode(['code' => 0,'admin_id' => $adminUser->id]);exit;
            }else{
                echo json_encode(['code' => -1,'msg' => Yii::T('common', 'No user')]);exit;
            }
        }

    }

    /**
     * @name 提醒每日数据
     * @return string
     */
    public function actionRemindDayData(){
        $addStart = $this->request->get('add_start',date('Y-m-d',strtotime('-7 day')));
        $addEnd = $this->request->get('add_end',date('Y-m-d'));
        $remindGroup = $this->request->get('remind_group','');
        $remindName = $this->request->get('remind_name','');
        $condition[] = 'and';
        if ($addStart) {
            $condition[] = ['>=', 'date', $addStart];
        }
        if ($addEnd) {
            $condition[] = ['<=', 'date', $addEnd];
        }
        if ($remindGroup != '') {
            $condition[] = ['remind_group' => intval($remindGroup)];
        }
        if ($remindName != '') {
            /** @var AdminUser $admin */
            $admin = AdminUser::find()->where(['username' => $remindName])->one();
            if($admin){
                $condition[] = ['admin_user_id' => $admin->id];
            }else{
                $condition[] = ['admin_user_id' => 0];
            }
        }
        $query = RemindDayData::find()->where($condition)->andWhere(['merchant_id' => $this->merchantIds]);
        $totalQuery = clone $query;
        $totalData =  $totalQuery->select(
            [
                'sum(today_dispatch_num) as today_dispatch_num',
                'sum(today_dispatch_remind_num) as today_dispatch_remind_num',
                'sum(today_repay_num) as today_repay_num'
            ])->asArray()->all();
        $totalData[0]['date'] = '总汇total';
        $totalData[0]['Type'] = 1; //汇总

        $pages = new Pagination(['totalCount' => 99999]);
        $pages->pageSize = yii::$app->request->get('per-page', 15);
        $dateData =  $totalQuery->select(
            [
                'date',
                'sum(today_dispatch_num) as today_dispatch_num',
                'sum(today_dispatch_remind_num) as today_dispatch_remind_num',
                'sum(today_repay_num) as today_repay_num'
            ])->groupBy(['date'])->orderBy(['date' => SORT_DESC])->asArray()->all();
        foreach ($dateData as &$val){
            $val['Type'] = 2;
        }
        $totalData = array_merge($totalData,$dateData);

        $data = $query->orderBy(['id' => SORT_DESC])->offset($pages->offset)->limit($pages->limit)->asArray()->all();
        $group = [];
        $remindGroup = RemindGroup::find()->select(['id','name'])->asArray()->all();
        foreach ($remindGroup as $item){
            $group[$item['id']] = $item['name'];
        }
        $userIds = [];
        foreach ($data as $v){
            $userIds[] = $v['admin_user_id'];
        }
        $adminAll = AdminUser::find()->where(['id' => $userIds])->all();
        $adminNames = [];
        foreach ($adminAll as $admin){
            $adminNames[$admin->id] = $admin->username;
        }
        return $this->render('remind-day-data', [
            'totalData' => $totalData,
            'data' => $data,
            'pages' => $pages,
            'add_start' => $addStart,
            'add_end' => $addEnd,
            'group' => $group,
            'adminNames' => $adminNames
         ]);
    }

    /**
     * @name 提醒触达还款的统计
     * @return string
     */
    public function actionRemindReachRepayData(){
        $addStart = $this->request->get('add_start',date('Y-m-d',strtotime('-7 day')));
        $addEnd = $this->request->get('add_end',date('Y-m-d'));
        $userType = $this->request->get('user_type',0);
        $condition[] = 'and';
        if ($addStart) {
            $condition[] = ['>=', 'date', $addStart];
        }
        if ($addEnd) {
            $condition[] = ['<=', 'date', $addEnd];
        }
        $condition[] = ['user_type' => intval($userType)];
        $query = RemindReachRepay::find()->where($condition)->andWhere(['merchant_id' => $this->merchantIds]);
        $totalQuery = clone $query;
        $totalData =  $totalQuery->select(
            [
                'sum(remind_num) as remind_num',
                'sum(reach_num) as reach_num',
                'sum(repay_num) as repay_num'
            ])->asArray()->all();

        $pages = new Pagination(['totalCount' => 99999]);

        $data = $query->orderBy(['id' => SORT_DESC])->offset($pages->offset)->limit($pages->limit)->asArray()->all();
        return $this->render('remind-reach-repay-data', [
            'totalData' => $totalData,
            'data' => $data,
            'pages' => $pages,
            'add_start' => $addStart,
            'add_end' => $addEnd,
        ]);
    }

    /**
     * @name CustomerController 提醒分组
     */
    public function actionRemindGroup(){
        $list = RemindGroup::find()
            ->select(['A.id','A.name','A.created_at','A.updated_at','B.name as merchant_name','A.team_leader_id'])
            ->from(RemindGroup::tableName().' A')
            ->leftJoin(Merchant::tableName().' B','A.merchant_id = B.id')
            ->where(['A.merchant_id' => $this->merchantIds])
            ->orderBy(['A.id' => SORT_DESC])
            ->asArray()->all();
        $pages = new Pagination(['totalCount' => RemindGroup::find()->count()]);
        return $this->render('remind-group', [
            'list' => $list,
            'pages' => $pages,
            'isNotMerchantAdmin' => $this->isNotMerchantAdmin
        ]);
    }

    /**
     * @name CustomerController 提醒分组添加
     */
    public function actionRemindGroupAdd(){
        $model = new RemindGroup();
        $postData = $this->request->post();
        if ($postData && $model->load($postData) && $model->validate()) {
            if(!$this->isNotMerchantAdmin){
                $model->merchant_id = Yii::$app->user->identity->merchant_id;
            }
            if ($model->save()) {
                return $this->redirectMessage('add success', self::MSG_SUCCESS,  Url::toRoute(['remind-group']));
            } else {
                return $this->redirectMessage('add fail', self::MSG_ERROR);
            }
        }
        $merchants = Merchant::getMerchantByIds($this->merchantIds,$this->isNotMerchantAdmin);
        return $this->render('remind-group-add', [
            'model' => $model,
            'merchants' => $merchants,
            'isNotMerchantAdmin' => $this->isNotMerchantAdmin
        ]);
    }

    /**
     * @name CustomerController 提醒分组编辑
     */
    public function actionRemindGroupEdit($id){
        $model = RemindGroup::findOne(['id' => $id,'merchant_id' => $this->merchantIds]);
        $postData = $this->request->post();
        if ($postData && $model->load($postData)) {
            if($this->isNotMerchantAdmin){
                if(empty($model->team_leader_id)){
                    $model->team_leader_id = '';
                }else{
                    $teamLeaderUsername = explode(',',$model->team_leader_id);
                    $adminUsers = AdminUser::find()->select(['id'])->where(['username' => $teamLeaderUsername])->asArray()->all();
                    $model->team_leader_id = implode(',',array_column($adminUsers,'id'));
                }
            }
            if($model->validate()){
                if ($model->save()) {
                    return $this->redirectMessage('edit success', self::MSG_SUCCESS,  Url::toRoute(['remind-group']));
                } else {
                    return $this->redirectMessage('edit fail', self::MSG_ERROR);
                }
            }
        }
        if(!empty($model->team_leader_id)){
            $teamLeaderIds = explode(',',$model->team_leader_id);
            $adminUsers = AdminUser::find()->select(['username'])->where(['id' => $teamLeaderIds])->asArray()->all();
            $model->team_leader_id = implode(',',array_column($adminUsers,'username'));
        }
        $merchants = Merchant::getMerchantByIds($this->merchantIds,$this->isNotMerchantAdmin);
        return $this->render('remind-group-edit', [
            'model' => $model,
            'merchants' => $merchants,
            'isNotMerchantAdmin' => $this->isNotMerchantAdmin
        ]);
    }

    /**
     * @name CustomerController 获取登录验证码发送结果
     * @return string
     */
    public function actionGetLoginSmsCode(){
        $result = '';
        $phone = '';
        if($this->request->isPost){
            $phone = $this->request->post('phone','');
            $adminUserRole = AdminUserRole::find()
                ->select(['name'])
                ->where(['!=','groups',AdminUserRole::TYPE_DEFAULT])->all();
            $roleNames = array_column($adminUserRole,'name');
            $adminUser = AdminUser::find()->select(['phone'])->where(['role' => $roleNames,'phone' => $phone,'merchant_id' => $this->merchantIds])->one();
            if($adminUser){
                $adminUserCaptcha = AdminUserCaptcha::find()->where(['phone' => $phone,'type' => AdminUserCaptcha::TYPE_ADMIN_LOGIN])->one();
                if($adminUserCaptcha){
                    $result = 'username:'.$adminUser['username'].',code:'.$adminUserCaptcha['captcha'];
                }else{
                    $result = 'code not exist';
                }
            }else{
                $result = 'get fail';
            }
        }
        return $this->render('get-login-sms-code',['result' => $result,'phone' => $phone]);
    }

    /**
     * @name (menu)客服牛信坐席电话
     * @return array
     */
    public function actionCallPhone()
    {
        $this->getResponse()->format = Response::FORMAT_JSON;
        $phone = trim($this->request->get('phone'));
        $collector_id = Yii::$app->user->identity->getId();

        if (!$phone) {
            return ['code' => -1, 'message' => 'phone is incorrect'];
        }
        $type = trim($this->request->get('type',CollectorCallData::TYPE_ONE_SELF));
        if(!in_array($type,[CollectorCallData::TYPE_ONE_SELF,CollectorCallData::TYPE_CONTACT])){
            return ['code' => -1, 'message' => 'type is error'];
        }
        $adminInfo = AdminNxUser::find()
            ->where(['collector_id' => $collector_id, 'status' => AdminNxUser::STATUS_ENABLE, 'type' => AdminNxUser::TYPE_PC])
            ->asArray()
            ->one();

        if (!$adminInfo) {
            return ['code' => -1, 'message' => 'No match to Nioxin account'];
        }
        $nx_orderid = 'saas'.time().$phone.$adminInfo['nx_name'];
        try {
            $nxPhoneLogMod = new NxPhoneLog();
            $nxPhoneLogMod->nx_orderid = $nx_orderid;
            $nxPhoneLogMod->collector_id  = $collector_id;
            $nxPhoneLogMod->nx_name  = $adminInfo['nx_name'];
            $nxPhoneLogMod->phone  = $phone;
            $nxPhoneLogMod->type   = $type;
            $nxPhoneLogMod->status = NxPhoneLog::STATUS_NO;
            $nxPhoneLogMod->call_type = NxPhoneLog::CALL_CUSTOMER;
            $nxPhoneLogMod->phone_type = CollectorCallData::NIUXIN_PC;
            $nxPhoneLogMod->save();
        } catch (\Exception $e) {
            exit;
        }
        return ['code' => 0, 'orderid' => $nx_orderid];

    }

    /**
     * @name 客服牛信电话查询
     * @return string
     */
    public function actionNxPhoneData()
    {

        $startTime = strtotime(Yii::$app->request->get('start_time', ''));
        $endTime = strtotime(Yii::$app->request->get('end_time', ''));
        $phone = $this->request->get('phone');
        $order_id = $this->request->get('order_id');
        $collector = $this->request->get('username');

        $sort['A.id'] = SORT_DESC;

        $query = NxPhoneLog::find()
            ->select([
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
            ->leftJoin('saas.tb_admin_user' . ' B', 'A.collector_id = B.id')
            ->where(['A.direction' => 1, 'A.call_type' => NxPhoneLog::CALL_CUSTOMER]);

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

        return $this->render('nx-phone-data', [
            'data' => $data,
            'pages' => $pages,
        ]);
    }


    /**
     * @name CustomerController 提醒员通话通次
     * @return string
     */
    public function actionReminderCallData(){
        $startTime = $this->request->get('start_time',date('Y-m-d',strtotime('-7 day')));
        $endTime = $this->request->get('end_time',date('Y-m-d'));
        $remindGroup = $this->request->get('remind_group',[]);
        $reminder = $this->request->get('username');
        $phoneType = $this->request->get('phone_type');
        $condition[] = 'and';
        if(!empty($startTime)){
            $condition[] = ['>=', 'A.date', $startTime];
        }
        if(!empty($endTime)){
            $condition[] = ['<=', 'A.date', $endTime];
        }
        if(!empty($phoneType)){
            $condition[] = ['A.phone_type' => intval($phoneType)];
        }
        $select = [
            'total_person' => 'COUNT(1)',
            'total_times' => 'SUM(A.times)',
            'total_duration' => 'SUM(A.duration)',

            'invalid_total_person' => 'SUM(IF(A.is_valid = '.ReminderCallData::INVALID.',1,0))',
            'invalid_total_times' => 'SUM(IF(A.is_valid = '.ReminderCallData::INVALID.',A.times,0))',

            'oneself_person' => 'SUM(IF(A.type = '.ReminderCallData::TYPE_ONE_SELF.',1,0))',
            'oneself_times' => 'SUM(IF(A.type = '.ReminderCallData::TYPE_ONE_SELF.',A.times,0))',
            'oneself_duration' => 'SUM(IF(A.type = '.ReminderCallData::TYPE_ONE_SELF.',A.duration,0))',

            'invalid_oneself_person' => 'SUM(IF(A.type = '.ReminderCallData::TYPE_ONE_SELF.' and A.is_valid = '.ReminderCallData::INVALID.',1,0))',
            'invalid_oneself_times' => 'SUM(IF(A.type = '.ReminderCallData::TYPE_ONE_SELF.' and A.is_valid = '.ReminderCallData::INVALID.',A.times,0))',

            'contact_person' => 'SUM(IF(A.type = '.ReminderCallData::TYPE_CONTACT.',1,0))',
            'contact_times' => 'SUM(IF(A.type = '.ReminderCallData::TYPE_CONTACT.',A.times,0))',
            'contact_duration' => 'SUM(IF(A.type = '.ReminderCallData::TYPE_CONTACT.',A.duration,0))',

            'invalid_contact_person' => 'SUM(IF(A.type = '.ReminderCallData::TYPE_CONTACT.' and A.is_valid = '.ReminderCallData::INVALID.',1,0))',
            'invalid_contact_times' => 'SUM(IF(A.type = '.ReminderCallData::TYPE_CONTACT.' and A.is_valid = '.ReminderCallData::INVALID.',A.times,0))',
        ];
        $query = ReminderCallData::find()
            ->from(ReminderCallData::tableName().' A')
            ->leftJoin(AdminUser::tableName(). ' B','A.user_id = B.id')
            ->leftJoin(RemindAdmin::tableName(). ' C','A.user_id = C.admin_user_id')
            ->leftJoin(RemindGroup::tableName(). ' D','C.remind_group = D.id')
            ->where($condition)
            ->andWhere(['B.merchant_id' => $this->merchantIds]);;;
        if(!empty($remindGroup)){
            $query->andWhere(['C.remind_group' => $remindGroup]);
        }
        if(!empty($reminder) && $reminder != ''){
            $query->andWhere(['like','B.username',$reminder]);
        }
        //导出
        if($this->request->get('submitcsv') == 'exportcsv'){
            $select[] = 'A.date';
            $select[] = 'D.name';
            $select[] = 'B.username';
            $data = $query->select($select)
                ->groupBy(['A.date','A.user_id'])
                ->orderBy(['A.date' => SORT_DESC])
                ->asArray()
                ->all(ReminderCallData::getDb_rd());

            return $this->_exportReminderCallData($data);
        }
        $totalQuery = clone $query;
        $totalData = $totalQuery->select(array_merge($select,['date' => 'CONCAT("汇总")']))->asArray()
            ->all(ReminderCallData::getDb_rd());
        $dateQuery = clone $query;
        $select[] = 'A.date';
        $dateData = $dateQuery->select($select)->asArray()
            ->groupBy(['A.date'])
            ->all(ReminderCallData::getDb_rd());
        $totalData = array_merge($totalData,$dateData);
        $pages = new Pagination(['totalCount' => 999999]);
        $pages->pageSize =  Yii::$app->request->get('page_size',15);
        $select[] = 'D.name';
        $select[] = 'B.username';
        $data = $query
            ->select($select)
            ->groupBy(['A.date','A.user_id'])
            ->orderBy(['A.date' => SORT_DESC])
            ->offset($pages->offset)
            ->limit($pages->limit)
            ->asArray()
            ->all(ReminderCallData::getDb_rd());

        return $this->render('reminder-call-data',[
            'totalData' => $totalData,
            'data' => $data,
            'pages' => $pages,
            'teamList' => []
        ]);
    }

    /**
     * 提醒员通话通次导出方法
     * @param $data
     */
    private function _exportReminderCallData($data){
        $this->_setcsvHeader('提醒员通话通次'.date('YmdHi').'.csv');
        $items = [];
        foreach($data as $val){
            $items[] = [
                'date'      => $val['date'],
                '分组'     => $val['name'],
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
            ];
        }
        // var_dump($items); die;
        echo $this->_array2csv($items);
        exit;
    }

    /**
     * @name CustomerController 提醒员打卡统计
     * @return string
     */
    public function actionReminderPunchCardData(){
        $startTime = $this->request->get('start_time',date('Y-m-d',strtotime('-7 day')));
        $endTime = $this->request->get('end_time',date('Y-m-d'));
        $remindGroup = $this->request->get('remind_group',[]);
        $reminder = $this->request->get('username');
        $addressType = $this->request->get('address_type','');

        $queryEnd = RemindCheckinLog::find()
            ->select(['date','user_id','xbsj' => 'MAX(created_at)'])
            ->where(['type' => RemindCheckinLog::TYPE_OFF_WORK])
            ->groupBy(['date','user_id']);

        $queryStart = RemindCheckinLog::find()
            ->select(['id','date','user_id','address_type','sbsj' => 'min(created_at)'])
            ->where(['type' => RemindCheckinLog::TYPE_START_WORK])
            ->groupBy(['date','user_id']);


        $joinFlag = false;
        if($this->request->get('submitcsv') == 'joinexportcsv'){
            //联合导出
            $joinFlag = true;

            $select = [
                'date',
                'user_id',
                'total_person' => 'COUNT(1)',
                'total_times' => 'SUM(times)',
                'total_duration' => 'SUM(duration)',

                'invalid_total_person' => 'SUM(IF(is_valid = '.ReminderCallData::INVALID.',1,0))',
                'invalid_total_times' => 'SUM(IF(is_valid = '.ReminderCallData::INVALID.',times,0))',

                'oneself_person' => 'SUM(IF(type = '.ReminderCallData::TYPE_ONE_SELF.',1,0))',
                'oneself_times' => 'SUM(IF(type = '.ReminderCallData::TYPE_ONE_SELF.',times,0))',
                'oneself_duration' => 'SUM(IF(type = '.ReminderCallData::TYPE_ONE_SELF.',duration,0))',

                'invalid_oneself_person' => 'SUM(IF(type = '.ReminderCallData::TYPE_ONE_SELF.' and is_valid = '.ReminderCallData::INVALID.',1,0))',
                'invalid_oneself_times' => 'SUM(IF(type = '.ReminderCallData::TYPE_ONE_SELF.' and is_valid = '.ReminderCallData::INVALID.',times,0))',

                'contact_person' => 'SUM(IF(type = '.ReminderCallData::TYPE_CONTACT.',1,0))',
                'contact_times' => 'SUM(IF(type = '.ReminderCallData::TYPE_CONTACT.',times,0))',
                'contact_duration' => 'SUM(IF(type = '.ReminderCallData::TYPE_CONTACT.',duration,0))',

                'invalid_contact_person' => 'SUM(IF(type = '.ReminderCallData::TYPE_CONTACT.' and is_valid = '.ReminderCallData::INVALID.',1,0))',
                'invalid_contact_times' => 'SUM(IF(type = '.ReminderCallData::TYPE_CONTACT.' and is_valid = '.ReminderCallData::INVALID.',times,0))',
            ];
            $callQueryStart = ReminderCallData::find()
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

        $queryCheck = RemindCheckinLog::find()
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

        $query = RemindCheckinLog::find()
            ->select([
                'A.date',
                'A.address_type',
                'D.name',
                'B.username',
                'A.sbsj',
                'A.xbsj'
            ])
            ->from(['A' => $queryCheck])
            ->leftJoin(AdminUser::tableName(). ' B','A.user_id = B.id')
            ->leftJoin(RemindAdmin::tableName(). ' C','A.user_id = C.admin_user_id')
            ->leftJoin(RemindGroup::tableName(). ' D','D.id = C.remind_group')
            ->where(['B.merchant_id' => $this->merchantIds])
            ->orderBy(['A.date' => SORT_DESC,'A.id' => SORT_DESC]);

        if(!empty($remindGroup)){
            $query->andWhere(['C.remind_group' => $remindGroup]);
        }
        if(!empty($reminder) && $reminder != ''){
            $query->andWhere(['like','B.username',$reminder]);
        }

        //导出
        if($this->request->get('submitcsv') == 'exportcsv'){
            $data = $query
                ->asArray()
                ->all();
            return $this->_exportCollectorPunchCardData($data);
        }

        $pages = new Pagination(['totalCount' => $query->count()]);
        $pages->pageSize =  Yii::$app->request->get('page_size',15);
        $data = $query
            ->offset($pages->offset)
            ->limit($pages->limit)
            ->asArray()
            ->all();
        $groupList = RemindGroup::allGroupName();
        return $this->render('reminder-punch-card-data',[
            'data' => $data,
            'pages' => $pages,
            'groupList' => $groupList
        ]);
    }

    /**
     * 提醒员打卡导出方法
     * @param $data
     */
    private function _exportCollectorPunchCardData($data){
        $this->_setcsvHeader('提醒员打卡数据'.date('YmdHi').'.csv');
        $items = [];
        foreach($data as $val){
            $items[] = [
                'date'        => $val['date'],
                'group'        => $val['name'] ?? '--',
                'reminder'      => $val['username'],
                Yii::T('common', 'Check-in Address Type') => RemindCheckinLog::$address_type_map[$val['address_type']] ?? '--',
                'workOnTime' => !empty($val['sbsj']) ? date('Y-m-d H:i:s', $val['sbsj']) : '--',
                'workOffTime' => !empty($val['xbsj']) ? date('Y-m-d H:i:s', $val['xbsj']) : '--'
            ];
        }
        // var_dump($items); die;
        echo $this->_array2csv($items);
        exit;
    }
}

<?php
namespace backend\controllers;

use Carbon\Carbon;
use common\helpers\CommonHelper;
use common\models\ClientInfoLog;
use common\models\enum\kudos\LoanStatus;
use common\models\kudos\LoanKudosOrder;
use common\models\kudos\LoanKudosPerson;
use common\models\kudos\LoanKudosTranche;
use common\models\manual_credit\ManualCreditLog;
use common\models\manual_credit\ManualCreditModule;
use common\models\manual_credit\ManualCreditRules;
use common\models\manual_credit\ManualCreditType;
use common\models\order\UserLoanOrder;
use common\models\order\UserLoanOrderExtraRelation;
use common\models\order\UserLoanOrderRepayment;
use common\models\package\PackageSetting;
use common\models\product\ProductPeriodSetting;
use common\models\user\LoanPerson;
use common\models\user\UserBasicInfo;
use common\models\user\UserVerification;
use common\services\order\OrderService;
use common\services\pay\RazorpayService;
use common\services\repayment\RepaymentService;
use yii\helpers\Url;
use Yii;
use yii\data\Pagination;
use yii\base\Exception;
use yii\web\NotFoundHttpException;

class LoanOrderController extends BaseController {

    //借款列表过滤
    private function getFilter() {
        $condition[] = 'and';
        if ($this->getRequest()->getIsGet()) {
            $search = $this->request->get();
            if (!empty($search['status']) && $search['status'] != '') {
                $condition[] = ['userLoanOrder.status' => intval($search['status'])];
            }
            if (isset($search['loan_status']) && $search['loan_status'] != '') {
                $condition[] = ['userLoanOrder.loan_status' => intval($search['loan_status'])];
            }
            if (!empty($search['id'])) {
                $condition[] = ['userLoanOrder.id' => CommonHelper::idDecryption($search['id'])];
            }
            if (!empty($search['uid'])) {
                $condition[] = ['userLoanOrder.user_id' => CommonHelper::idDecryption($search['uid'])];
            }
            if (!empty($search['name'])) {
                $condition[] = ['like', 'loanPerson.name', $search['name']];
            }
            if (!empty($search['phone'])) {
                $condition[] = ['loanPerson.phone' => $search['phone']];
            }
            if (!empty($search['source_id'])) {
                $condition[] = ['loanPerson.source_id' => intval($search['source_id'])];
            }
            if (!empty($search['old_user'])) {
                if ($search['old_user'] == 1){
                    $condition[] = ['loanPerson.customer_type' => LoanPerson::CUSTOMER_TYPE_OLD];
                } elseif ($search['old_user'] == -1){
                    $condition[] = ['loanPerson.customer_type' => LoanPerson::CUSTOMER_TYPE_NEW];
                }
            }
            if (!empty($search['loan_term'])) {
                $condition[] = ['userLoanOrder.loan_term' => $search['loan_term']];
            }
            if (!empty($search['loan_method'])) {
                $condition[] = ['userLoanOrder.loan_method' => $search['loan_method']];
            }
            if (!empty($search['amount_min'])) {
                $condition[] = ['>=', 'userLoanOrder.amount', intval($search['amount_min']*100)];
            }
            if (!empty($search['amount_max'])) {
                $condition[] = ['<=', 'userLoanOrder.amount', intval($search['amount_max']*100)];
            }
            if (!empty($search['begintime'])) {//申请时间
                $condition[] = ['>=', 'userLoanOrder.order_time', strtotime($search['begintime'])];
            }
            if (!empty($search['endtime'])) {//申请时间
                $condition[] = ['<=', 'userLoanOrder.order_time', strtotime($search['endtime'])];
            }
            if (!empty($search['begintime2'])) {//放款时间
                $condition[] = ['>=', 'userLoanOrderRepayment.loan_time', strtotime($search['begintime2'])];
            }
            if (!empty($search['endtime2'])) {//放款时间
                $condition[] = ['<=', 'userLoanOrderRepayment.loan_time', strtotime($search['endtime2'])];
            }
            if (!empty($search['plan_repayment_time'])) {
                $plan_repayment_time_s = strtotime($search['plan_repayment_time']);
                $plan_repayment_time_e = strtotime($search['plan_repayment_time'])+86400;
                $condition[] = ['between', 'userLoanOrderRepayment.plan_repayment_time', $plan_repayment_time_s, $plan_repayment_time_e];
            }
            if (isset($search['time']) && !empty($search['time'])) {
                $begintime = strtotime($search['time']);
                $endtime = $begintime + 86400;
                $condition[] = ['between', 'userLoanOrderRepayment.loan_time', $begintime, $endtime];
            }

            // 商户筛选，防止拼接参数来绕过判断
            if (!empty($this->isNotMerchantAdmin) && !empty($search['merchant_id']) && $search['merchant_id'] != '') {
                $condition[] = ['userLoanOrder.merchant_id' => intval($search['merchant_id'])];
            }else {
                // 没有搜索条件，各看各的公司
                if (is_array($this->merchantIds)) {
                    $sMerchantIds = $this->merchantIds;
                } else {
                    $sMerchantIds = explode(',', $this->merchantIds);
                }
                $condition[] = ['userLoanOrder.merchant_id' => $sMerchantIds];
            }

            if(!empty($search['order_app'])){
                $condition[] = [
                    'or',
                    [
                        'and',
                        ['clientInfoLog.package_name' => $search['order_app']],
                        ['userLoanOrder.is_export' => UserLoanOrder::IS_EXPORT_NO],
                    ],
                    [
                        'and',
                        ['like', 'clientInfoLog.app_market', "external_{$search['order_app']}%", false],
                        ['userLoanOrder.is_export' => UserLoanOrder::IS_EXPORT_YES],
                    ],
                ];
            }
        }
        return $condition;
    }

    /**
     * @name Loan order list
     * @name-cn 借款管理-风控管理-借款列表
     * @return
     */
    public function actionList(){
        $condition = self::getFilter();
        //总单数
        $query = UserLoanOrder::find()
            ->from(UserLoanOrder::tableName() . ' as userLoanOrder')
            ->leftJoin(UserLoanOrderRepayment::tableName() . 'as userLoanOrderRepayment', 'userLoanOrderRepayment.order_id = userLoanOrder.id')
            ->leftJoin(LoanPerson::tableName() . 'as loanPerson', 'loanPerson.id = userLoanOrder.user_id')
            ->leftJoin(ClientInfoLog::tableName() . 'as clientInfoLog', 'clientInfoLog.event_id = userLoanOrder.id and clientInfoLog.event = '.ClientInfoLog::EVENT_APPLY_ORDER)
            ->where($condition);

        $isShow = false;
        if(in_array(Yii::$app->user->identity->getId(),[1,3,7,21,79])){
            $isShow = true;
        }
        $package_setting = array_flip(PackageSetting::getSourceIdMap($this->merchantIds));
        if($this->request->get('submitcsv') == 'exportData' && $isShow){
            if(count($condition) == 1){
                return $this->redirectMessage(Yii::T('common', 'Please select conditions before exporting'), self::MSG_ERROR);
            }
            $data = $query->select(
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
                        'userLoanOrder.loan_status', //订单状态
                        'userLoanOrder.audit_status', //审核状态
                        'userLoanOrder.is_export', //是否导流订单
                        'loanPerson.name', //姓名
                        'loanPerson.phone', //手机号
                        'loanPerson.source_id', //用户来源
                        'clientInfoLog.package_name', //下单包名
                        'clientInfoLog.app_market', //下单app market
                        'loanPerson.customer_type', //是否为老用户
                        'userLoanOrderRepayment.closing_time', //订单完结时间
                        'userBasicInfo.email_address'
                    ])
                ->leftJoin(UserLoanOrderExtraRelation::tableName() . 'as userLoanOrderExtraRelation', 'userLoanOrderExtraRelation.order_id = userLoanOrder.id')
                ->leftJoin(UserBasicInfo::tableName() . 'as userBasicInfo', 'userBasicInfo.id = userLoanOrderExtraRelation.user_basic_info_id')
                ->orderBy(['userLoanOrder.id' => SORT_DESC])
                ->asArray()->all(Yii::$app->get('db_read_1'));
            return $this->_exportOrderData($data,$package_setting);
        }

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
                    'userLoanOrder.loan_status', //订单状态
                    'userLoanOrder.audit_status', //审核状态
                    'userLoanOrder.is_export', //是否导流订单
                    'loanPerson.name', //姓名
                    'loanPerson.pan_code', //panCode
                    'loanPerson.phone', //手机号
                    'loanPerson.source_id', //用户来源
                    'clientInfoLog.package_name', //下单包名
                    'clientInfoLog.app_market', //下单app market
                    'loanPerson.customer_type', //是否为老用户
                    'userLoanOrderRepayment.closing_time', //订单完结时间
                ]
            )
            ->offset($pages->offset)
            ->limit($pages->limit)
            ->orderBy(['userLoanOrder.id' => SORT_DESC])
            ->asArray()
            ->all();

        $status_data = [];
        foreach ($data as $item) {
            $status_data[$item['id']] = isset(UserLoanOrder::$order_status_map[$item['status']]) ? UserLoanOrder::$order_status_map[$item['status']] : '';
        }
        return $this->render('list', array(
            'data_list' => $data,
            'status_data'=>$status_data,
            'pages' => $pages,
            'isShow' => $isShow,
            'package_setting' => $package_setting,
            'package_name_list' => PackageSetting::getAllLoanPackageNameMap($this->merchantIds),
            'isNotMerchantAdmin' => $this->isNotMerchantAdmin
        ));
    }

    /**
     * 导出订单列表
     * @param $data
     * @param $package_setting
     */
    private function _exportOrderData($data,$package_setting){
        $this->_setcsvHeader(Yii::T('common', 'Order list data.csv'));
        $items = [];
        if(count($data) > 10000){
            echo Yii::T('common', 'The amount of data is too large, please export in stages');exit;
        }
        foreach($data as $value){
            // 判断是否来源于导流
            $items[] = [
                Yii::T('common', 'orderId') => CommonHelper::idEncryption($value['id'], 'order') ?? 0,
                Yii::T('common', 'userId') => CommonHelper::idEncryption($value['user_id'], 'user') ?? 0,
                Yii::T('common', 'name') => $value['name'] ?? 0,
                Yii::T('common', 'phone') => $value['phone'] ?? 0,
                Yii::T('common', 'email') => $value['email_address'] ?? '--',
                Yii::T('common', 'sourceId') => $package_setting[$value['source_id']] ?? '--',
                Yii::T('common', 'Order APP') => $value['is_export'] == UserLoanOrder::IS_EXPORT_YES ? explode('_',$value['app_market'])[1] : $value['package_name'],
                Yii::T('common', 'New and old users') => isset(LoanPerson::$customer_type_list[$value['customer_type']]) ? LoanPerson::$customer_type_list[$value['customer_type']]:"--",
                Yii::T('common', 'Loan term') => ($value['loan_term']*$value['periods']).ProductPeriodSetting::$loan_method_map[$value['loan_method']],
                Yii::T('common', 'Borrowing amount (Rs)') => $value['amount'] == 0 ? '--' : sprintf("%0.2f",($value['amount'] + $value['interests'])/100),
                Yii::T('common', 'status') =>  UserLoanOrder::$order_status_map[$value['status']] ?? '-',
                Yii::T('common', 'Payment status') =>  UserLoanOrder::$order_loan_status_map[$value['loan_status']] ?? '-',
                Yii::T('common', 'application time') => empty($value['order_time']) ? '--' : date('Y-m-d H:i:s',$value['order_time']),
                Yii::T('common', 'Lending time') => empty($value['loan_time']) ? '--' : date('Y-m-d H:i:s',$value['loan_time']),
                Yii::T('common', 'Settlement time') => empty($value['closing_time'])?'--':date('Y-m-d H:i:s',$value['closing_time'])
            ];
        }
        echo $this->_array2csv($items);
        exit;
    }

    /**
     * @name Loan order detail
     * @param $id
     * @return string
     * @name-cn 借款管理-用户借款管理-借款列表-查看
     */
    public function actionDetail($id)
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

        $logs = ManualCreditLog::find()->where(['order_id'=> $id])->all();
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
        return $this->render('view', array(
            'informationAll' => $informationAll,
            'information' => $information,
            'verification' => $verification,
            'manualQuestion' => $manualQuestion,
            'conversionRules' => $conversionRules
        ));
    }

    /**
     * @name LoanOrderController kudos-订单批次确认列表
     * @return string
     */
    public function actionKudosTranche()
    {
        $query = (new Yii\db\Query())
            ->select([
                'ko.kudos_tranche_id as id',
                'kt.kudos_tranche_id as tranche_id',
                'kt.kudos_status',
                'count(ko.id) as tranche_num',
                'sum(ko.disbursement_amt) as tranche_amt',
                'kt.created_at',
            ])
            ->from(['ko' => 'tb_loan_kudos_order'])
            ->leftJoin('tb_loan_kudos_tranche kt', 'ko.kudos_tranche_id = kt.id')
            ->where(['ko.kudos_status' => LoanStatus::LOAN_REPAYMENT_SCHEDULE()->getValue(), 'ko.merchant_id' => $this->merchantIds])
            ->andWhere(['kt.kudos_status' => [0,1]])
            ->groupBy('ko.kudos_tranche_id');
        $countQuery = clone $query;
        $pages = new Pagination(['totalCount' => $countQuery->count()]);
        $pages->setPageSize(Yii::$app->request->get('per-page', 15));

        $data = $query
            ->orderBy(['id' => SORT_DESC])
            ->offset($pages->offset)
            ->limit($pages->limit)
            ->all();

        return $this->render('kudos-tranche-list', [
            'data_list'   => $data,
            'pages'       => $pages,
        ]);
    }

    /**
     * @name LoanOrderController kudos-订单批次确认提交
     * @return string
     */
    public function actionKudosTrancheConfirm(string $id)
    {
        $currentTime = intval(Carbon::now()->format('Hi'));
        if ($currentTime < 1400 or $currentTime > 1430) {
            return $this->redirectMessage(Yii::T('common', 'Same day batch please operate in India at 14: 00 ~ 14: 30 on the same day'), self::MSG_ERROR);
        }

        $kudosTranche = LoanKudosTranche::findOne(['id' => $id, 'merchant_id' => $this->merchantIds]);
        if (!Carbon::createFromTimestamp($kudosTranche->created_at)->isCurrentDay()) {
            return $this->redirectMessage(Yii::T('common', 'Same day batch please operate in India at 14: 00 ~ 14: 30 on the same day'), self::MSG_ERROR);
        }
        $kudosTranche->kudos_status = 1;
        if($kudosTranche->save()) {
            return $this->redirectMessage(Yii::T('common', 'Submission successful'), self::MSG_SUCCESS, Url::toRoute(['loan-order/kudos-tranche']));
        } else {
            return $this->redirectMessage(Yii::T('common', 'Update fail'), self::MSG_ERROR);
        }
    }

    /**
     * @name LoanOrderController kudos-订单批次打款列表
     * @return string
     */
    public function actionKudosDisburse()
    {
        $query = (new Yii\db\Query())
            ->select([
                'ko.kudos_tranche_id as id',
                'kt.kudos_tranche_id as tranche_id',
                'kt.kudos_status',
                'count(ko.id) as tranche_num',
                'sum(ko.disbursement_amt) as tranche_amt',
                'kt.created_at',
            ])
            ->from(['ko' => 'tb_loan_kudos_order'])
            ->leftJoin('tb_loan_kudos_tranche kt', 'ko.kudos_tranche_id = kt.id')
            ->where(['ko.kudos_status' => LoanStatus::TRANCHE_APPEND()->getValue(), 'ko.merchant_id' => $this->merchantIds])
            ->andWhere(['kt.kudos_status' => [1,2]])
            ->groupBy('ko.kudos_tranche_id');
        $countQuery = clone $query;
        $pages = new Pagination(['totalCount' => $countQuery->count()]);
        $pages->setPageSize(Yii::$app->request->get('per-page', 15));

        $data = $query
            ->orderBy(['id' => SORT_DESC])
            ->offset($pages->offset)
            ->limit($pages->limit)
            ->all();

        return $this->render('kudos-disburse-list', [
            'data_list'   => $data,
            'pages'       => $pages,
        ]);
    }

    /**
     * @name LoanOrderController kudos-订单批次打款提交
     * @return string
     * @throws \Exception
     */
//    public function actionKudosDisburseConfirm(string $id)
//    {
//        $currentTime = intval(Carbon::now()->format('Hi'));
//        if ($currentTime < 1500) {
//            return $this->redirectMessage('当日批次请在印度当天的时间15：00之后操作', self::MSG_ERROR);
//        }
//
//        $kudosTranche = LoanKudosTranche::findOne($id);
//        if ($kudosTranche->kudos_status != 1) {
//            return $this->redirectMessage('该批次请先确认之后再提交打款', self::MSG_ERROR);
//        }
//        $kudosTranche->kudos_status = 2;
//        $kudosOrders = $kudosTranche->kudosOrders;
//        $transaction = Yii::$app->db->beginTransaction();
//        $operator = Yii::$app->user->identity->id;
//        try {
//            foreach ($kudosOrders as $order) {
//                /**
//                 * @var LoanKudosOrder $order
//                 */
//                $orderService = new OrderService($order->userLoanOrder);
//                $orderService->changeOrderAllStatus([
//                    'after_loan_status' => UserLoanOrder::LOAN_STATUS_WAIT
//                ], 'backend-KudosDisburseConfirm', $operator);
//            }
//            $transaction->commit();
//            return $this->redirectMessage('提交成功', self::MSG_SUCCESS, Url::toRoute(['loan-order/kudos-disburse']));
//        } catch (\Exception $exception) {
//            $transaction->rollBack();
//            throw $exception;
//        }
//    }


    /**
     * @return string|void
     * @name 订单列表
     */
    public function actionOrderList()
    {
        $condition[] = 'and';
        if ($this->getRequest()->getIsGet()) {
            $search = $this->request->get();
            if (isset($search['user_id']) && !empty($search['user_id'])) {
                $condition[] = ['k.user_id' => CommonHelper::idDecryption($search['user_id'])];
            }
            if (isset($search['order_id']) && !empty($search['order_id'])) {
                $condition[] = ['k.order_id' => CommonHelper::idDecryption($search['order_id'])];
            }
            if (isset($search['status']) && '' !== $search['status']) {
                $condition[] = ['o.status' => intval($search['status'])];
            }
            if (isset($search['kudos_account_status']) && '' !== $search['kudos_account_status']) {
                $condition[] = ['p.kudos_account_status' => $search['kudos_account_status']];
            }
            if (isset($search['phone']) && !empty($search['phone'])) {
                $condition[] = ['p.phone' => $search['phone']];
            }
            if (isset($search['begintime']) && !empty($search['begintime'])) {
                $condition[] = ['>=', 'k.created_at', strtotime($search['begintime'])];
            }
            if (isset($search['endtime']) && !empty($search['endtime'])) {
                $condition[] = ['<=', 'k.created_at', strtotime($search['endtime'])];
            }
        }

        $pageSize = yii::$app->request->get('per-page', 15);
        $query = LoanKudosOrder::find()
            ->from(LoanKudosOrder::tableName(). ' as k')
            ->leftJoin(UserLoanOrder::tableName() . ' as o', 'k.order_id = o.id')
            ->leftJoin(LoanKudosPerson::tableName() . ' as p', 'p.order_id = k.order_id')
            ->where($condition);

        $queryClone = clone $query;

        $count = $queryClone->count();
        $pages = new Pagination(['totalCount' => $count]);
        $pages->pageSize = $pageSize;
        $info = $query
            ->select(['k.*', 'p.kudos_va_acc', 'p.kudos_account_status', 'o.status'])
            ->offset($pages->offset)
            ->limit($pages->limit)
            ->orderBy(['k.id' => SORT_DESC])
            ->asArray()
            ->all();

        return $this->render('order-list', array(
            'info' => $info,
            'pages' => $pages,
        ));
    }
}

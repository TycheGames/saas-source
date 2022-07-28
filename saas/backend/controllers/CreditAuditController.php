<?php
namespace backend\controllers;

use backend\models\AdminNxUser;
use backend\models\AdminUser;
use callcenter\models\CollectorCallData;
use common\helpers\CommonHelper;
use common\helpers\RedisQueue;
use common\helpers\Util;
use common\models\manual_credit\ManualCreditLog;
use common\models\manual_credit\ManualCreditModule;
use common\models\manual_credit\ManualCreditRules;
use common\models\manual_credit\ManualCreditType;
use common\models\manual_credit\ManualSecondMobile;
use common\models\message\NxPhoneLog;
use common\models\order\UserLoanOrder;
use common\models\order\UserLoanOrderRepayment;
use common\models\order\UserOrderLoanCheckLog;
use common\models\risk\RiskResultSnapshot;
use common\models\user\LoanPerson;
use common\models\user\UserVerification;
use common\services\order\OrderService;
use yii\helpers\Url;
use Yii;
use yii\data\Pagination;
use yii\base\Exception;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class CreditAuditController extends BaseController {

    //借款列表过滤
    private function getFilter($default_status = -100) {
        $condition[] = 'and';
        if ($this->getRequest()->getIsGet()) {
            $search = $this->request->get();
            $search['status'] = isset($search['status']) ? $search['status'] : $default_status;
            if (!empty($search['status']) && $default_status != $search['status']) {
                $condition[] = ['userLoanOrder.status' => intval($search['status'])];
            }
            if (!empty($search['id'])) {
                $condition[] = ['userLoanOrder.id' => CommonHelper::idDecryption($search['id'])];
            }
            if (!empty($search['uid'])) {
                $condition[] = ['userLoanOrder.user_id' => CommonHelper::idDecryption($search['uid'])];
            }
            if (!empty($search['name'])) {
                $condition[] = ['loanPerson.name' => $search['name']];
            }
            if (!empty($search['phone'])) {
                $condition[] = ['loanPerson.phone' => $search['phone']];
            }
            if (!empty($search['old_user'])) {
                if ($search['old_user'] == 1){
                    $condition[] = ['loanPerson.customer_type' => LoanPerson::CUSTOMER_TYPE_OLD];
                } elseif ($search['old_user'] == -1){
                    $condition[] = ['loanPerson.customer_type' => LoanPerson::CUSTOMER_TYPE_NEW];
                }
            }
            if (!empty($search['loan_term'])) {
                $condition[] = ['userLoanOrder.loan_term' => $search['loan_term'] ];
            }
            if (!empty($search['loan_method'])) {
                $condition[] = ['userLoanOrder.loan_method' => $search['loan_method']];
            }
            if (!empty($search['amount_min'])) {
                $condition[] = ['>=', 'userLoanOrder.amount', intval($search['amount_min'] * 100)];
            }
            if (!empty($search['amount_max'])) {
                $condition[] = ['<=', 'userLoanOrder.amount', intval($search['amount_max'] * 100)];
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
        $time = time() - 3600;
        //总单数
        $condition[] = [
            'or',
            ['userLoanOrder.audit_status' => UserLoanOrder::AUDIT_STATUS_GET_ORDER],
            [
                'and',
                ['userLoanOrder.audit_status' => UserLoanOrder::AUDIT_STATUS_MANUAL_CHECK],
                ['<', 'userLoanOrder.audit_begin_time', $time],
            ],
        ];
        $query = UserLoanOrder::find()
            ->from(UserLoanOrder::tableName() . ' as userLoanOrder')
            ->leftJoin(UserLoanOrderRepayment::tableName() . 'as userLoanOrderRepayment', 'userLoanOrderRepayment.order_id = userLoanOrder.id')
            ->leftJoin(LoanPerson::tableName() . 'as loanPerson', 'loanPerson.id = userLoanOrder.user_id')
            ->where(
                [
                    'userLoanOrder.status' => UserLoanOrder::STATUS_CHECK,
                    'userLoanOrder.merchant_id' => $this->merchantIds,
                ]
            )
            ->andWhere($condition);

        $totalQuery = clone $query;
        $pages = new Pagination(['totalCount' => $totalQuery->count()]);
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
                    'loanPerson.name', //姓名
                    'loanPerson.pan_code', //panCode
                    'loanPerson.phone', //手机号
                    'loanPerson.customer_type', //是否为老用户
                    'userLoanOrderRepayment.closing_time', //订单完结时间
                ]
            )
            ->offset($pages->offset)
            ->limit($pages->limit)
            ->orderBy(['userLoanOrder.id' => SORT_ASC])
            ->asArray()
            ->all();

        $status_data = [];
        foreach ($data as $key => $item) {
            $status_data[$item['id']] = isset(UserLoanOrder::$order_status_map[$item['status']]) ? UserLoanOrder::$order_status_map[$item['status']] : '';
            $data[$key]['phone'] = isset($item['phone']) ? substr($item['phone'], 0, 3).'****'.substr($item['phone'], 7) :'-';
            $operateKey = sprintf('%s:%s', RedisQueue::BEFORE_MANUAL_CREDIT_ORDER_ALERT,$item['id']);
            $data[$key]['can_get'] = RedisQueue::get(['key' => $operateKey]) ? false : true;
        }
        return $this->render('list', array(
            'data_list' => $data,
            'status_data'=>$status_data,
            'pages' => $pages,
        ));
    }


    /**
     * @name Loan order list
     * @name-cn 借款管理-风控管理-借款列表
     * @return
     */
    public function actionReviewList(){
        $condition = self::getFilter();
        //总单数
        $query = UserLoanOrder::find()
            ->from(UserLoanOrder::tableName() . ' as userLoanOrder')
            ->leftJoin(LoanPerson::tableName() . 'as loanPerson', 'loanPerson.id = userLoanOrder.user_id')
            ->where(
                [
                    'userLoanOrder.status' => UserLoanOrder::STATUS_CHECK,
                    'userLoanOrder.merchant_id' => $this->merchantIds,
                    'userLoanOrder.audit_status' => UserLoanOrder::AUDIT_STATUS_MANUAL_CHECK,
                    'userLoanOrder.audit_operator' => Yii::$app->user->getId(),
                ]
            )
//            ->andWhere(['>=' , 'userLoanOrder.audit_begin_time', time() - 3600])
            ->andWhere($condition);
        $count = 9999999;//写死个值
        $pages = new Pagination(['totalCount' => $count]);
        $pages->pageSize = \yii::$app->request->get('per-page', 15);
        $data = $query
            ->select('
            userLoanOrder.*,
            loanPerson.name,
            loanPerson.pan_code,
            loanPerson.phone,
            loanPerson.customer_type
            ')
            ->offset($pages->offset)
            ->limit($pages->limit)
            ->orderBy(['userLoanOrder.id' => SORT_ASC])
            ->asArray()
            ->all();
        return $this->render('review-list', array(
            'data_list' => $data,
            'pages' => $pages,
            'nx_phone' => $this->canUseNx
        ));
    }

    /**
     * @name ToMyReview
     * @name-cn 订单审核抢单
     *
     */
    public function actionToMyReview($id){

        $id = CommonHelper::idDecryption($id);

        try{
            //加锁
            if(!UserLoanOrder::lockReviewOrder($id)){
                throw  new Exception(Yii::T('common', 'The order is being processed. Please try again later.'));
            }
            /** @var UserLoanOrder $order */
            $order = UserLoanOrder::find()->where(['id' => $id, 'merchant_id' => $this->merchantIds])->one();
            if(!$order){
                throw  new Exception('Order does not exist');
            }
            $service = new OrderService($order);
            $audit_operator = Yii::$app->user->getId();
            //判断管理员是否可以抢单审核
            if(!$service->getAuditOrder($audit_operator)){
                throw new Exception($service->getError());
            }
            //释放锁
            UserLoanOrder::releaseReviewOrderLock($id);
            return $this->redirectMessage(Yii::T('common', 'success，you get a review order, and this order can not review to be other person'),
                self::MSG_SUCCESS, Url::toRoute('credit-audit/review-list'));
        }catch (Exception $e){
            //释放锁
            UserLoanOrder::releaseReviewOrderLock($id);
            return $this->redirectMessage($e->getMessage(), self::MSG_ERROR);
        }

    }

    /**
     * @name Review
     * @name-cn 订单审核
     *
     */
    public function actionReview($id){

        $id = CommonHelper::idDecryption($id);

        /** @var UserLoanOrder $order */
        $order = UserLoanOrder::find()->where(['id' => $id, 'merchant_id' => $this->merchantIds])->one();
        if (empty($order)) {
            throw new NotFoundHttpException(Yii::T('common', 'Order does not exist'));
        }
        $service = new OrderService($order);
        $service->setOperator(Yii::$app->user->identity->getId());
        //todo 判断该订单所属module1 / module2
        /** @var RiskResultSnapshot $riskResultSnapshot */
        $riskResultSnapshot = RiskResultSnapshot::find()
            ->select('manual_node')
            ->where(['order_id' => $order->id, 'result' => 'manual'])->one();

        $moudules = [];
        if($riskResultSnapshot){
            if($manualNode = json_decode($riskResultSnapshot->manual_node, true)){
                if($manualNode){
                    $moudules = array_keys($manualNode);
                }
            }
        }

        if(empty($moudules)){
            $moudules = ['Module1']; //兼容老订单
        }

        $finishArr = [];
        $oldModules = $moudules;
        foreach ($moudules as $key => $moudule){
            if($res = $service->manualCheckLogFinish($moudule)){
                unset($moudules[$key]);
                $finishArr[$res] = strval(ManualCreditRules::QUESTION_PASS);
            }
        }

        if(empty($moudules)){  //如果都是空 照旧审核
            $moudules = $oldModules;
        }

        $list = ManualCreditRules::getAllManualRules($moudules);
        $allRules = ManualCreditRules::conversionRules($list);
        $remarkCode = ManualCreditRules::getRemarkCode($list);

        if ($this->request->getIsPost()) {
            $operation = $this->request->post('operation');
            $auditRemark = $this->request->post('audit_remark');
            $code = $this->request->post('code');
            $question = $this->request->post('question', []);
            $loanAction = $this->request->post('loan_action', 1);
            $secondMobile = $this->request->post('second_mobile','');
            if($finishArr){
                $auditRemark .= '(default finish ruleId:'.key($finishArr).')';
            }
            try {
                if(!UserLoanOrder::lockReviewOrder($id)){
                    throw new Exception('The order is locked');
                }
                if($secondMobile){
                    if(!preg_match(Util::getPhoneMatchForReg(), $secondMobile)){
                        throw new Exception('second mobile not a valid number');
                    };
                }
                if(UserLoanOrder::REVIEW_PASS == $operation){  //过审   所有module 必须都通过
                    $checkRes = ManualCreditRules::passQuestionCheck($question,$list);
                    if($checkRes['code'] != 0){
                        throw new Exception($checkRes['msg']);
                    }
                    if(!$service->manualCheckApprove($auditRemark, $finishArr + $question)){
                        throw new Exception($service->getError());
                    }
                }elseif (UserLoanOrder::REVIEW_REJECT == $operation){ //拒审
                    $checkRes = ManualCreditRules::rejectQuestionCheck($code,$question,$list);
                    if($checkRes['code'] != 0){
                        throw new Exception($checkRes['msg']);
                    }
                    if($loanAction == UserOrderLoanCheckLog::CAN_LOAN){
                        $interval = 0;
                    }elseif($loanAction == UserOrderLoanCheckLog::MONTH_LOAN){
                        $interval = 30;
                    }elseif($loanAction == UserOrderLoanCheckLog::WEEK_LOAN){
                        $interval = 7;
                    }else{
                        $interval = 10000;
                    }
                    if(!$service->manualCheckReject($auditRemark, $interval, $checkRes['rule'], $finishArr + $question)){
                        throw new Exception($service->getError());
                    }
                }else{
                    throw new Exception('operation is error');
                }
                if($secondMobile){
                    ManualSecondMobile::updateSecondMobile($order,$secondMobile);
                }
                UserLoanOrder::releaseReviewOrderLock($id);
                return $this->redirectMessage('operate successfully',
                    self::MSG_SUCCESS, Url::toRoute('credit-audit/review-list'));
            } catch (\Exception $e) {
                UserLoanOrder::releaseReviewOrderLock($id);
                return $this->redirectMessage($e->getMessage(), self::MSG_ERROR);
            }
        }
        $informationAll = $service->getOrderDetailAllInfo();
        $information = $service->getOrderDetailInfo();
        $verification = UserVerification::findOne(['user_id'=>$information['loanPerson']['id']]);
        $information['overdue_day'] = 0;
        // 查询订单逾期天数
        $repayment_info = UserLoanOrderRepayment::find()->where(['order_id' => $id])->asArray()->one();
        if ($repayment_info) {
            $information['overdue_day'] = $repayment_info['overdue_day'];
        }
        //echo '<pre>';print_r($allRules);exit;
        return $this->render('review-view', array(
            'informationAll'=> $informationAll,
            'information' => $information,
            'verification' => $verification,
            'allRules' => $allRules,
            'remarkCode' => $remarkCode,
            'moudules' => $moudules
        ));
    }

    //借款列表过滤
    private function getBankFilter() {
        $condition[] = 'and';
        if ($this->getRequest()->getIsGet()) {
            $search = $this->request->get();
            if (!empty($search['status'])) {
                $condition[] = ['o.status' => intval($search['status'])];
            }
            if (!empty($search['id'])) {
                $condition[] = ['o.id' => CommonHelper::idDecryption($search['id'])];
            }
            if (!empty($search['uid'])) {
                $condition[] = ['o.user_id' => CommonHelper::idDecryption($search['uid'])];
            }
            if (!empty($search['name'])) {
                $condition[] = ['p.name' => $search['name']];
            }
            if (!empty($search['phone'])) {
                $condition[] = ['p.phone' => $search['phone']];
            }
            if (!empty($search['old_user'])) {
                if ($search['old_user'] == 1){
                    $condition[] = ['p.customer_type' => LoanPerson::CUSTOMER_TYPE_OLD];
                } elseif ($search['old_user'] == -1){
                    $condition[] = ['p.customer_type' => LoanPerson::CUSTOMER_TYPE_NEW];
                }
            }
            if (!empty($search['loan_term'])) {
                $condition[] = ['o.loan_term' => $search['loan_term']];
            }
            if (!empty($search['loan_method'])) {
                $condition[] = ['o.loan_method' => $search['loan_method']];
            }
            if (!empty($search['amount_min'])) {
                $condition[] = ['>=', 'o.amount', intval($search['amount_min'] * 100)];
            }
            if (!empty($search['amount_max'])) {
                $condition[] = ['<=', 'o.amount', intval($search['amount_max'] * 100)];
            }
            if (!empty($search['begintime'])) {//申请时间
                $condition[] = ['>=', 'o.order_time', strtotime($search['begintime'])];
            }
            if (!empty($search['endtime'])) {//申请时间
                $condition[] = ['<=', 'o.order_time', strtotime($search['endtime'])];
            }
            if (!empty($search['begintime2'])) {//放款时间
                $condition[] = ['>=', 'r.loan_time', strtotime($search['begintime2'])];
            }
            if (!empty($search['endtime2'])) {//放款时间
                $condition[] = ['<=', 'r.loan_time', strtotime($search['endtime2'])];
            }
        }
        return $condition;
    }

    /**
     * @name CreditAudit-CreditAudit-Banklist
     * @return
     */
    public function actionBankList(){
        $condition = self::getBankFilter();
        $time = time() - 3600;
        //总单数
        $condition[] = [
            'or',
            ['o.audit_bank_status' => UserLoanOrder::AUDIT_BANK_STATUS_GET_ORDER],
            [
                'and',
                ['o.audit_bank_status' => UserLoanOrder::AUDIT_BANK_STATUS_MANUAL_CHECK],
                ['<', 'o.audit_bank_begin_time', $time],
            ]
        ];
        $query = UserLoanOrder::find()
            ->from(UserLoanOrder::tableName() . ' as o')
            ->leftJoin(UserLoanOrderRepayment::tableName() . 'as r', 'r.order_id = o.id')
            ->leftJoin(LoanPerson::tableName() . 'as p', 'p.id = o.user_id')
            ->where(
                [
                    'o.status' => UserLoanOrder::STATUS_WAIT_DEPOSIT,
                    'o.loan_status' => UserLoanOrder::LOAN_STATUS_BIND_CARD_CHECK,
                    'o.merchant_id' => $this->merchantIds,
                ]
            )
            ->andWhere($condition);

        $totalQuery = clone $query;
        $pages = new Pagination(['totalCount' => $totalQuery->count()]);
        $pages->pageSize = \yii::$app->request->get('per-page', 15);
        $data = $query
            ->select(
                [
                    'o.id', //订单号
                    'o.amount', //订单金额
                    'o.interests', //订单利息
                    'o.user_id', //用户ID
                    'o.loan_term', //订单期限
                    'o.loan_method', //期限单位
                    'o.periods', //订单期数
                    'o.order_time', //下单时间
                    'o.loan_time', //放款时间
                    'o.status', //订单状态
                    'o.audit_status', //审核状态
                    'p.name', //姓名
                    'p.pan_code', //panCode
                    'p.phone', //手机号
                    'p.customer_type', //是否为老用户
                    'r.closing_time', //订单完结时间
                ]
            )
            ->offset($pages->offset)
            ->limit($pages->limit)
            ->orderBy(['o.id' => SORT_ASC])
            ->asArray()
            ->all();

        foreach ($data as $key => $item) {
            $data[$key]['phone'] = isset($item['phone']) ? substr($item['phone'], 0, 3).'****'.substr($item['phone'], 7) :'-';
            $operateKey = sprintf('%s:%s', RedisQueue::BEFORE_MANUAL_CREDIT_BANK_ORDER_ALERT,$item['id']);
            $data[$key]['can_get'] = RedisQueue::get(['key' => $operateKey]) ? false : true;
        }
        return $this->render('bank-list', array(
            'data_list' => $data,
            'pages' => $pages,
        ));
    }


    /**
     * @name ToMyBankReview
     * @name-cn 订单审核抢单
     *
     */
    public function actionToMyBankReview($id){

        $id = CommonHelper::idDecryption($id);

        try{
            //加锁
            if(!UserLoanOrder::lockReviewOrder($id)){
                throw new Exception(Yii::T('common', 'The order is being processed. Please try again later.'));
            }
            /** @var UserLoanOrder $order */
            $order = UserLoanOrder::find()->where(['id' => $id, 'merchant_id' => $this->merchantIds])->one();
            if(!$order){
                throw  new Exception('Order does not exist');
            }
            $service = new OrderService($order);
            $audit_operator = Yii::$app->user->getId();
            //判断管理员是否可以抢单审核
            if(!$service->getAuditBankOrder($audit_operator)){
                throw new Exception($service->getError());
            }
            //释放锁
            UserLoanOrder::releaseReviewOrderLock($id);
            return $this->redirectMessage(Yii::T('common', 'success，you get a review order, and this order can not review to be other person'),
                self::MSG_SUCCESS, Url::toRoute('credit-audit/review-bank-list'));
        }catch (Exception $e){
            //释放锁
            UserLoanOrder::releaseReviewOrderLock($id);
            return $this->redirectMessage($e->getMessage(), self::MSG_ERROR);
        }
    }


    /**
     * @name CreditAudit-CreditAudit-ReviewBankList
     * @return
     */
    public function actionReviewBankList(){
        $condition = self::getBankFilter();
        //总单数
        $query = UserLoanOrder::find()
            ->from(UserLoanOrder::tableName() . ' as o')
            ->leftJoin(LoanPerson::tableName() . 'as p', 'p.id = o.user_id')
            ->where(
                [
                    'o.status' => UserLoanOrder::STATUS_WAIT_DEPOSIT,
                    'o.loan_status' => UserLoanOrder::LOAN_STATUS_BIND_CARD_CHECK,
                    'o.merchant_id' => $this->merchantIds,
                    'o.audit_bank_status' => UserLoanOrder::AUDIT_BANK_STATUS_MANUAL_CHECK,
                    'o.audit_bank_operator' => Yii::$app->user->getId(),
                ]
            )
            ->andWhere($condition);
        $count = 9999999;//写死个值
        $pages = new Pagination(['totalCount' => $count]);
        $pages->pageSize = \yii::$app->request->get('per-page', 15);
        $data = $query
            ->select([
                'o.*',
                'p.name',
                'p.pan_code',
                'p.phone',
                'p.customer_type'])
            ->offset($pages->offset)
            ->limit($pages->limit)
            ->orderBy(['o.id' => SORT_ASC])
            ->asArray()
            ->all();
        return $this->render('review-bank-list', array(
            'data_list' => $data,
            'pages' => $pages,
        ));
    }


    /**
     * @name ReviewBank
     * @name-cn 订单审核
     */
    public function actionReviewBank($id){

        $id = CommonHelper::idDecryption($id);

        /** @var UserLoanOrder $order */
        $order = UserLoanOrder::find()->where(['id' => $id, 'merchant_id' => $this->merchantIds])->one();
        if(empty($order)){
            throw new NotFoundHttpException(Yii::T('common', 'Order does not exist'));
        }
        $service = new OrderService($order);
        $service->setOperator(Yii::$app->user->identity->getId());

        $moudules = ['Module3']; // 绑卡审核 固定
        $list = ManualCreditRules::getAllManualRules($moudules);
        $allRules = ManualCreditRules::conversionRules($list);
        $remarkCode = ManualCreditRules::getRemarkCode($list);
        if ($this->request->getIsPost()) {
            $operation = $this->request->post('operation');
            $auditRemark = $this->request->post('audit_remark');
            $code = $this->request->post('code');
            $question = $this->request->post('question', []);
            try {
                if(!UserLoanOrder::lockReviewOrder($id)){
                    throw new Exception('The order is locked');
                }
                if(UserLoanOrder::REVIEW_PASS == $operation){  //过审
                    $checkRes = ManualCreditRules::passQuestionCheck($question,$list);
                    if($checkRes['code'] != 0){
                        throw new Exception($checkRes['msg']);
                    }
                    if(!$service->bankCheckApprove($auditRemark,$question)){
                        throw new Exception($service->getError());
                    }
                }elseif (UserLoanOrder::REVIEW_REJECT == $operation){ //拒审
                    $checkRes = ManualCreditRules::rejectQuestionCheck($code,$question,$list);
                    if($checkRes['code'] != 0){
                        throw new Exception($checkRes['msg']);
                    }
                    if(!$service->bankCheckReject($auditRemark,$checkRes['rule'], $question)){
                        throw new Exception($service->getError());
                    }
                }else{
                    throw new Exception('operation is error');
                }

                UserLoanOrder::releaseReviewOrderLock($id);
                return $this->redirectMessage('operate successfully',
                    self::MSG_SUCCESS, Url::toRoute('credit-audit/review-bank-list'));
            } catch (\Exception $e) {
                UserLoanOrder::releaseReviewOrderLock($id);
                return $this->redirectMessage($e->getMessage(), self::MSG_ERROR);
            }
        }
        $informationAll = $service->getOrderDetailAllInfo();
        $information = $service->getOrderDetailInfo();
        $verification = UserVerification::findOne(['user_id'=>$information['loanPerson']['id']]);
        $information['overdue_day'] = 0;
        // 查询订单逾期天数
        $repayment_info = UserLoanOrderRepayment::find()->where(['order_id' => $id])->asArray()->one();
        if ($repayment_info) {
            $information['overdue_day'] = $repayment_info['overdue_day'];
        }
        return $this->render('review-bank-view', array(
            'informationAll'=> $informationAll,
            'information' => $information,
            'verification' => $verification,
            'allRules' => $allRules,
            'remarkCode' => $remarkCode,
            'moudules' => $moudules
        ));
    }


    /**
     * @name 人审模块
     */
    public function actionManualModuleList(){
        if(!$this->isNotMerchantAdmin){
            throw new ForbiddenHttpException(Yii::T('common', 'Insufficient permissions'));
        }
        $moduleList = ManualCreditModule::find()
            ->asArray()
            ->all();
        return $this->render('manual-module-list', array(
            'list' => $moduleList,
        ));

    }

    /**
     * @name 人审模块add
     */
    public function actionManualModuleAdd(){
        if(!$this->isNotMerchantAdmin){
            throw new ForbiddenHttpException(Yii::T('common', 'Insufficient permissions'));
        }
        $model = new ManualCreditModule();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->save()) {
                return $this->redirectMessage('success', self::MSG_SUCCESS, Url::toRoute('credit-audit/manual-module-list'));
            } else {
                return $this->redirectMessage('fail', self::MSG_ERROR);
            }
        }
        return $this->render('manual-module-add', array(
            'model' => $model,
        ));
    }

    /**
     * @name 人审模块编辑
     */
    public function actionManualModuleEdit($id){
        if(!$this->isNotMerchantAdmin){
            throw new ForbiddenHttpException(Yii::T('common', 'Insufficient permissions'));
        }
        $model = ManualCreditModule::findOne($id);
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->save()) {
                return $this->redirectMessage('success', self::MSG_SUCCESS, Url::toRoute('credit-audit/manual-module-list'));
            } else {
                return $this->redirectMessage('fail', self::MSG_ERROR);
            }
        }
        return $this->render('manual-module-edit', array(
            'model' => $model,
        ));
    }


    /**
     * @name 人审模块类型列表
     */
    public function actionManualTypeList(){
        if(!$this->isNotMerchantAdmin){
            throw new ForbiddenHttpException(Yii::T('common', 'Insufficient permissions'));
        }
        $typeList = ManualCreditType::find()
            ->select('A.*,B.head_code,B.head_name')
            ->from(ManualCreditType::tableName(). ' A')
            ->leftJoin(ManualCreditModule::tableName(). 'B', 'A.module_id = B.id')
            ->where(['B.status' => ManualCreditModule::STATUS_NO])
            ->asArray()
            ->all();

        return $this->render('manual-type-list', array(
            'list' => $typeList,
        ));
    }

    /**
     * @name 人审模块type add
     */
    public function actionManualTypeAdd(){
        if(!$this->isNotMerchantAdmin){
            throw new ForbiddenHttpException(Yii::T('common', 'Insufficient permissions'));
        }
        $model = new ManualCreditType();
        $manualCreditModuleList = ManualCreditModule::find()
            ->select(['id','head_code'])
            ->where(['status' => ManualCreditModule::STATUS_NO])
            ->asArray()
            ->all();
        $moduleIds = [];
        foreach ($manualCreditModuleList as $item){
            $moduleIds[$item['id']] = $item['head_code'];
        }

        if($model->load($this->request->post())){
            if ($model->save()) {
                return $this->redirectMessage('success', self::MSG_SUCCESS, Url::toRoute('credit-audit/manual-type-list'));
            } else {
                return $this->redirectMessage('fail', self::MSG_ERROR);
            }
        }
        return $this->render('manual-type-add', array(
            'model' => $model,
            'moduleIds' => $moduleIds
        ));
    }

    /**
     * @name 人审模块type edit
     */
    public function actionManualTypeEdit($id){
        if(!$this->isNotMerchantAdmin){
            throw new ForbiddenHttpException(Yii::T('common', 'Insufficient permissions'));
        }
        $model = ManualCreditType::findOne($id);
        $manualCreditModuleList = ManualCreditModule::find()
            ->select(['id','head_code'])
            ->where(['status' => ManualCreditModule::STATUS_NO])
            ->asArray()
            ->all();
        $moduleIds = [];
        foreach ($manualCreditModuleList as $item){
            $moduleIds[$item['id']] = $item['head_code'];
        }

        if($model->load($this->request->post())){
            if ($model->save()) {
                return $this->redirectMessage('success', self::MSG_SUCCESS, Url::toRoute('credit-audit/manual-type-list'));
            } else {
                return $this->redirectMessage('fail', self::MSG_ERROR);
            }
        }
        return $this->render('manual-type-edit', array(
            'model' => $model,
            'moduleIds' => $moduleIds
        ));
    }

    /**
     * @name 人审模块Rule列表
     */
    public function actionManualRulesList(){
        if(!$this->isNotMerchantAdmin){
            throw new ForbiddenHttpException(Yii::T('common', 'Insufficient permissions'));
        }
        $typeList = ManualCreditRules::find()
            ->select('A.*,B.module_id,B.type_name,C.head_code,C.head_name')
            ->from(ManualCreditRules::tableName(). ' A')
            ->leftJoin(ManualCreditType::tableName(). ' B','B.id = A.type_id')
            ->leftJoin(ManualCreditModule::tableName(). 'C', 'C.id = B.module_id')
            ->where(['C.status' => ManualCreditModule::STATUS_NO])
            ->asArray()
            ->all();

        return $this->render('manual-rules-list', array(
            'list' => $typeList,
        ));
    }

    /**
     * @name 人审模块Rule add
     */
    public function actionManualRulesAdd(){
        if(!$this->isNotMerchantAdmin){
            throw new ForbiddenHttpException(Yii::T('common', 'Insufficient permissions'));
        }
        $model = new ManualCreditRules();
        $manualCreditModuleList = ManualCreditModule::find()
            ->select(['id','head_code'])
            ->where(['status' => ManualCreditModule::STATUS_NO])
            ->asArray()
            ->all();
        $moduleIds = [];
        $mids = [];
        foreach ($manualCreditModuleList as $item){
            $moduleIds[$item['id']] = $item['head_code'];
            $mids[] = $item['id'];
        }

        $manualCreditTypeList = ManualCreditType::find()
            ->select(['id','type_name','module_id'])
            ->where(['module_id'=> $mids, 'status' => ManualCreditType::STATUS_NO])
            ->asArray()
            ->all();
        $typeIds = [];
        foreach ($manualCreditTypeList as $item2){
            $typeIds[$item2['module_id']][] = $item2;
        }

        if($model->load($this->request->post())){
            if ($model->save()) {
                return $this->redirectMessage('success', self::MSG_SUCCESS, Url::toRoute('credit-audit/manual-rules-list'));
            } else {
                return $this->redirectMessage('fail', self::MSG_ERROR);
            }
        }
        return $this->render('manual-rules-add', array(
            'model' => $model,
            'moduleIds' => $moduleIds,
            'typeIds' => $typeIds,
        ));
    }

    /**
     * @name 人审模块rule edit
     */
    public function actionManualRulesEdit($id){
        if(!$this->isNotMerchantAdmin){
            throw new ForbiddenHttpException(Yii::T('common', 'Insufficient permissions'));
        }
        $model = ManualCreditRules::findOne($id);

        $manualCreditModuleList = ManualCreditModule::find()
            ->select(['id','head_code'])
            ->where(['status' => ManualCreditModule::STATUS_NO])
            ->asArray()
            ->all();
        $moduleIds = [];
        $mids = [];

        foreach ($manualCreditModuleList as $item){
            $moduleIds[$item['id']] = $item['head_code'];
            $mids[] = $item['id'];
        }

        $manualCreditTypeList = ManualCreditType::find()
            ->select(['id','type_name','module_id'])
            ->where(['module_id'=> $mids, 'status' => ManualCreditType::STATUS_NO])
            ->asArray()
            ->all();
        $typeIds = [];
        $current_moudule_id = 0;
        foreach ($manualCreditTypeList as $item2){
            $typeIds[$item2['module_id']][] = $item2;
            if($model->type_id == $item2['id']){
                $current_moudule_id = $item2['module_id'];
            }
        }

        if($model->load($this->request->post())){
            if ($model->save()) {
                return $this->redirectMessage('success', self::MSG_SUCCESS, Url::toRoute('credit-audit/manual-rules-list'));
            } else {
                return $this->redirectMessage('fail', self::MSG_ERROR);
            }
        }
        return $this->render('manual-rules-edit', array(
            'model' => $model,
            'moduleIds' => $moduleIds,
            'typeIds' => $typeIds,
            'current_moudule_id' => $current_moudule_id
        ));
    }

    /**
     * @name 信审牛信坐席电话
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
            $nxPhoneLogMod->call_type = NxPhoneLog::CALL_CREDITAUDIT;
            $nxPhoneLogMod->phone_type = CollectorCallData::NIUXIN_PC;
            $nxPhoneLogMod->save();
        } catch (\Exception $e) {
            exit;
        }
        return ['code' => 0, 'orderid' => $nx_orderid];
    }

    /**
     * @name 信审牛信电话查询
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
            ->where(['A.direction' => 1, 'A.call_type' => NxPhoneLog::CALL_CREDITAUDIT]);

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
     * @name Loan order credit log
     * @name-cn 订单-审核日志
     * @return
     */
    public function actionCreditLogList(){
        $search = $this->request->get();
        $query = ManualCreditLog::find()
            ->select([
                'log.id',
                'log.order_id',
                'log.action',
                'log.type',
                'log.created_at',
                'user.username'
            ])
            ->alias('log')
            ->leftJoin(AdminUser::tableName().' user','log.operator_id = user.id')
            ->where(['log.merchant_id' => $this->merchantIds]);
        if(!empty($search['action'])){
            $query->andWhere(['log.action' => $search['action']]);
        }
        if(!empty($search['type'])){
            $query->andWhere(['log.type' => $search['type']]);
        }
        if(isset($search['is_auto']) && $search['is_auto'] != ''){
            $query->andWhere(['log.is_auto' => $search['is_auto']]);
        }
        if(!empty($search['begintime'])){
            $query->andWhere(['>=','log.created_at',strtotime($search['begintime'])]);
        }
        if(!empty($search['endtime'])){
            $query->andWhere(['<=','log.created_at',strtotime($search['endtime'])]);
        }
        if(!empty($search['operator'])){
            $query->andWhere(['like','user.username',$search['operator']]);
        }
        if(!empty($search['order_id'])){
            $query->andWhere(['log.order_id' => $search['order_id']]);
        }

        $totalQuery = clone $query;
        $pages = new Pagination(['totalCount' => $totalQuery->count()]);
        $pages->pageSize = \yii::$app->request->get('per-page', 15);
        $data = $query
            ->offset($pages->offset)
            ->limit($pages->limit)
            ->orderBy(['log.id' => SORT_DESC])
            ->asArray()
            ->all();
        return $this->render('credit-log-list', array(
            'data_list' => $data,
            'pages' => $pages,
        ));
    }

    /**
     * @name Loan order credit log detail
     * @name-cn 订单-审核日志-详情
     * @return
     */
    public function actionCreditLogDetail($id){
        /** @var ManualCreditLog $manualCreditLog */
        $manualCreditLog = ManualCreditLog::find()->where(['id' => $id,'merchant_id' => $this->merchantIds])->one();
        if(!$manualCreditLog){
            return $this->redirectMessage('log not exist', self::MSG_ERROR);
        }
        /** @var UserLoanOrder $order */
        $order = UserLoanOrder::find()->where(['id' => $manualCreditLog->order_id])->one();
        $service = new OrderService($order);
        $manualQuestion = [];
        $conversionRules = [];
        if($manualCreditLog){
            $arr = json_decode($manualCreditLog['que_info'],true);
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
        $informationAll = $service->getOrderDetailAllInfo();
        $information = $service->getOrderDetailInfo();
        $verification = UserVerification::findOne(['user_id'=>$information['loanPerson']['id']]);
        $information['overdue_day'] = 0;

        return $this->render('credit-log-view', array(
            'informationAll'=> $informationAll,
            'information' => $information,
            'verification' => $verification,
            'conversionRules' => $conversionRules,
            'manualQuestion' => $manualQuestion,
            'manualCreditLog' => $manualCreditLog
        ));
    }
}

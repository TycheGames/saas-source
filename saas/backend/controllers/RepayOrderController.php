<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2016/5/18
 * Time: 9:49
 */
namespace backend\controllers;

use backend\models\AdminUser;
use backend\models\search\FinancialPaymentRecordSearch;
use backend\models\search\RazorpayVirtualAccountSearch;
use common\models\ClientInfoLog;
use common\models\financial\FinancialPaymentOrder;
use common\models\fund\LoanFund;
use common\models\kudos\LoanKudosOrder;
use common\models\order\FinancialLoanRecord;
use common\models\order\UserLoanOrderExtraRelation;
use common\models\order\UserRepaymentLog;
use common\models\order\UserRepaymentReducedLog;
use common\models\order\UserTransferLogExternal;
use common\models\user\LoanPerson;
use common\models\order\UserLoanOrder;
use common\models\order\UserLoanOrderRepayment;
use common\models\user\UserBasicInfo;
use common\services\order\OrderService;
use common\services\pay\RazorpayService;
use common\services\repayment\OrderRepaymentLogService;
use common\services\repayment\ReductionService;
use common\services\repayment\RepaymentService;
use yii\data\Pagination;
use yii\helpers\Url;
use yii\web\IdentityInterface;
use yii;
use yii\db\Query;
use common\helpers\CommonHelper;
use common\models\package\PackageSetting;

class RepayOrderController extends  BaseController
{
    /**
     * @param string $type
     * @return string|void
     * @name 借款管理-贷后管理-零钱包还款列表
     */
    public function actionList($type='list')
    {
        $condition = $this->getTrailFilter();
        $isShowFinancial = false;
        if(in_array(Yii::$app->user->identity->getId(),[1])){
            $isShowFinancial = true;
        }
        $query = UserLoanOrderRepayment::find()
            ->from(UserLoanOrderRepayment::tableName() . ' as userLoanOrderRepayment')
            ->leftJoin(UserLoanOrder::tableName() . 'as userLoanOrder', 'userLoanOrder.id = userLoanOrderRepayment.order_id')
            ->leftJoin(LoanPerson::tableName() . 'as loanPerson', 'loanPerson.id = userLoanOrderRepayment.user_id')
            ->leftJoin(FinancialPaymentOrder::tableName() . ' financialPaymentOrder','financialPaymentOrder.order_id = userLoanOrderRepayment.order_id AND financialPaymentOrder.status = '.FinancialPaymentOrder::STATUS_SUCCESS)
            ->leftJoin(ClientInfoLog::tableName() . 'as clientInfoLog', 'clientInfoLog.event_id = userLoanOrderRepayment.order_id and clientInfoLog.event = '.ClientInfoLog::EVENT_APPLY_ORDER)
            ->where($condition);
        //导出财务数据
        if($this->request->get('submitcsv') == 'exportFinancialData' && $isShowFinancial){
            if(count($condition) == 1){
                return $this->redirectMessage(Yii::T('common', 'Please select conditions before exporting'), self::MSG_ERROR);
            }
            ini_set('memory_limit', '2018M');
            $data = $query
                ->select('
            userLoanOrderRepayment.id as repayment_id,
            userLoanOrderRepayment.order_id,
            userLoanOrderRepayment.user_id,
            loanPerson.name,
            loanPerson.phone,
            loanPerson.customer_type,
            userLoanOrderRepayment.principal,
            userLoanOrderRepayment.interests,
            userLoanOrderRepayment.overdue_fee,
            userLoanOrderRepayment.cost_fee,
            userLoanOrderRepayment.true_total_money,
            userLoanOrderRepayment.loan_time,
            userLoanOrderRepayment.plan_repayment_time,
            userLoanOrderRepayment.closing_time,
            userLoanOrderRepayment.is_overdue,
            userLoanOrderRepayment.overdue_day,
            userLoanOrderRepayment.status,
            loanKudosOrder.partner_loan_id,
            financialPaymentOrder.pay_order_id,
            financialPaymentOrder.pay_payment_id,
            financialPaymentOrder.amount,
            financialPaymentOrder.success_time as callback_time,
            financialLoanRecord.trade_no,
            loanFund.name as fund_name
            ')
                ->leftJoin(LoanFund::tableName() . ' loanFund','loanFund.id = userLoanOrder.fund_id')
                ->leftJoin(LoanKudosOrder::tableName() . ' loanKudosOrder', 'loanKudosOrder.order_id = userLoanOrderRepayment.order_id')
                ->leftJoin(FinancialLoanRecord::tableName() . ' financialLoanRecord', 'financialLoanRecord.business_id = userLoanOrderRepayment.order_id AND financialLoanRecord.status = '.FinancialLoanRecord::UMP_PAY_SUCCESS)
                ->orderBy(['userLoanOrderRepayment.id' => SORT_DESC])
                ->asArray()
                ->all();
            return $this->_exportRepayFinancialData($data);
        }
        $isShow = false;
        if(Yii::$app->user->identity->getIsSuperAdmin()){
            $isShow = true;
        }
        $search = $this->request->get();
        if ((isset($search['u_begintime']) && !empty($search['u_begintime'])) ||
            (isset($search['u_endtime']) && !empty($search['u_endtime']))){
//            $query->leftJoin(FinancialPaymentOrder::tableName() . ' financialPaymentOrder','financialPaymentOrder.order_id = userLoanOrderRepayment.order_id AND financialPaymentOrder.status = '.FinancialPaymentOrder::STATUS_SUCCESS);
        }
        $packageSetting = array_flip(PackageSetting::getSourceIdMap($this->merchantIds));
        //普通导出
        if($this->request->get('submitcsv') == 'exportData' && $isShow){
            if($condition == '1 = 1 '){
                return $this->redirectMessage(Yii::T('common', 'Please select conditions before exporting'), self::MSG_ERROR);
            }
            $data = $query
                ->select('
            userLoanOrderRepayment.id,
            userLoanOrderRepayment.order_id,
            userLoanOrderRepayment.user_id,
            loanPerson.name,
            loanPerson.phone,
            loanPerson.customer_type,
            userLoanOrderRepayment.principal,
            userLoanOrderRepayment.interests,
            userLoanOrderRepayment.overdue_fee,
            userLoanOrderRepayment.cost_fee,
            userLoanOrderRepayment.true_total_money,
            userLoanOrderRepayment.loan_time,
            userLoanOrderRepayment.plan_repayment_time,
            userLoanOrderRepayment.closing_time,
            userLoanOrderRepayment.is_overdue,
            userLoanOrderRepayment.overdue_day,
            userLoanOrderRepayment.status,
            userLoanOrderRepayment.is_delay_repayment,
            userLoanOrderRepayment.delay_repayment_time,
            userLoanOrderRepayment.delay_repayment_number,
            userLoanOrder.is_export,
            clientInfoLog.package_name,
            clientInfoLog.app_market,
            userBasicInfo.email_address,
            loanPerson.source_id
            ')
                ->leftJoin(UserLoanOrderExtraRelation::tableName() . 'as userLoanOrderExtraRelation', 'userLoanOrderExtraRelation.order_id = userLoanOrderRepayment.order_id')
                ->leftJoin(UserBasicInfo::tableName() . 'as userBasicInfo', 'userBasicInfo.id = userLoanOrderExtraRelation.user_basic_info_id')
                ->orderBy(['userLoanOrderRepayment.id' => SORT_DESC])
                ->asArray()
                ->all(Yii::$app->get('db_read_1'));
            return $this->_exportRepayData($data,$packageSetting);
        }

        $cloneQuery = clone $query;
        if(!empty($search['is_summary']) && $search['is_summary'] == 1){
            $count = $cloneQuery->cache(120)->count();
            $pages = new Pagination(['totalCount' => $count]);
        }else{
            $pages = new Pagination(['totalCount' => 999999]);
        }
        $pages->pageSize = \yii::$app->request->get('per-page', 15);;
        $info = $query
            ->select('
            userLoanOrderRepayment.*,
            loanPerson.name,
            loanPerson.pan_code,
            loanPerson.phone,
            loanPerson.customer_type,
            userLoanOrder.is_first,
            financialPaymentOrder.pay_account_id,
            userLoanOrder.is_export,
            clientInfoLog.package_name,
            clientInfoLog.app_market,
            loanPerson.source_id
            ')
            ->offset($pages->offset)
            ->limit($pages->limit)
            ->orderBy(['userLoanOrderRepayment.id' => SORT_DESC])
            ->asArray()
            ->all();
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
        return $this->render('list', array(
            'isShow' => $isShow,
            'isShowFinancial' => $isShowFinancial,
            'info' => $info,
            'pages' => $pages,
            'type' => $type,
            'repayCount' => $repayCount,
            'isNotMerchantAdmin' => $this->isNotMerchantAdmin,
            'package_setting' => $packageSetting,
            'package_name_list' => PackageSetting::getAllLoanPackageNameMap($this->merchantIds),

        ));
    }

    /**
     * 导出还款列表
     * @param $data
     * @param $packageSetting
     */
    private function _exportRepayData($data,$packageSetting){
        $this->_setcsvHeader(Yii::T('common', 'Repayment order list data.csv'));
        $items = [];
        $user_ids = array_column($data,'user_id');
        if(count($user_ids) > 20000){
            echo Yii::T('common', 'The amount of data is too large, please export in stages');exit;
        }
        $repayCount = [];
        if(!empty($user_ids)){
            $repayCount = UserLoanOrderRepayment::find()->select(['user_id','count' => 'COUNT(id)'])
                ->where(['user_id' => $user_ids, 'status' => UserLoanOrderRepayment::STATUS_REPAY_COMPLETE])
                ->groupBy(['user_id'])
                ->indexBy(['user_id'])
                ->asArray()
                ->all(Yii::$app->get('db_read_1'));
        }
        foreach($data as $value){
            // 判断是否来源于导流
            $items[] = [
                '还款订单ID' => $value['id'] ?? 0,
                '订单号' => $value['order_id'] ?? 0,
                '用户源' => $packageSetting[$value['source_id']] ?? '--',
                '放款包' => $value['is_export'] == UserLoanOrder::IS_EXPORT_YES ? explode('_',$value['app_market'])[1] : $value['package_name'],
                '用户ID' => $value['user_id'] ?? 0,
                '姓名' => $value['name'] ?? 0,
                '手机号' => $value['phone'] ?? 0,
                '邮箱' => $value['email_address'] ?? '---',
                '新老户' => isset(LoanPerson::$customer_type_list[$value['customer_type']]) ? LoanPerson::$customer_type_list[$value['customer_type']]:"---",
                '本金' => (!empty($value['principal'])) ? sprintf("%0.2f",$value['principal']/100) : "---",
                '利息' => (!empty($value['interests'])) ? sprintf("%0.2f",$value['interests']/100) : "---",
                '滞纳金' => (!empty($value['overdue_fee'])) ? sprintf("%0.2f",$value['overdue_fee']/100) : "---",
                '手续费' => (!empty($value['cost_fee'])) ? sprintf("%0.2f",$value['cost_fee']/100) : "---",
                '已还金额' =>(!empty($value['true_total_money'])) ? sprintf("%0.2f",$value['true_total_money']/100) : "---",
                '放款日期' => date('Y-m-d H:i:s',$value['loan_time']),
                '应还日期' => date('Y-m-d',$value['plan_repayment_time']),
                '结清日期' => $value['closing_time'] ? date('Y-m-d H:i:s',$value['closing_time']) : '---',
                '是否逾期' => $value['is_overdue'] == UserLoanOrderRepayment::IS_OVERDUE_YES ? "是" : "否",
                '逾期天数' => $value['overdue_day'],
                '还款次数' => $repayCount[$value['user_id']]['count'] ?? 0,
                '是否在延期中' => $value['is_delay_repayment'] ? '是' : '否',
                '延期中到期时间' => date('Y-m-d',$value['delay_repayment_time']),
                '延期次数' => $value['delay_repayment_number'],
                '状态' =>  isset(UserLoanOrderRepayment::$repayment_status_map[$value['status']])?UserLoanOrderRepayment::$repayment_status_map[$value['status']]:"---",
            ];
        }
        echo $this->_array2csv($items);
        exit;
    }

    /**
     * 导出财务还款列表
     * @param $data
     */
    private function _exportRepayFinancialData($data){
        $this->_setcsvHeader(Yii::T('common', 'Repayment order list data.csv'));
        $items = [];
//        $user_ids = array_column($data,'user_id');
//        if(count($user_ids) > 20000){
//            echo '数据量太大，请分次导出';exit;
//        }
        foreach($data as $value){
            // 判断是否来源于导流
            $items[] = [
                'trade_no' => $value['trade_no'] ?? '--',
                'partner_loan_id' => $value['partner_loan_id'] ?? '--',
                'pay_order_id' => $value['pay_order_id'] ?? '--',
                'pay_payment_id' => $value['pay_payment_id'] ?? '--',
                '资方' => $value['fund_name'] ?? '--',
                '还款订单ID' => $value['repayment_id'] ?? 0,
                '订单号' => $value['order_id'] ?? 0,
                '用户ID' => $value['user_id'] ?? 0,
                '姓名' => $value['name'] ?? 0,
                '本金' => (!empty($value['principal'])) ? sprintf("%0.2f",$value['principal']/100) : "---",
                '利息' => (!empty($value['interests'])) ? sprintf("%0.2f",$value['interests']/100) : "---",
                '滞纳金' => (!empty($value['overdue_fee'])) ? sprintf("%0.2f",$value['overdue_fee']/100) : "---",
                '手续费' => (!empty($value['cost_fee'])) ? sprintf("%0.2f",$value['cost_fee']/100) : "---",
                '已还金额' =>(!empty($value['true_total_money'])) ? sprintf("%0.2f",$value['true_total_money']/100) : "---",
                '本次还款' =>(!empty($value['amount'])) ? sprintf("%0.2f",$value['amount']/100) : "---",
                '本次还款回调时间' => date('Y-m-d H:i:s',$value['callback_time']),
                '放款日期' => date('Y-m-d H:i:s',$value['loan_time']),
                '应还日期' => date('Y-m-d',$value['plan_repayment_time']),
                '结清日期' => $value['closing_time'] ? date('Y-m-d H:i:s',$value['closing_time']) : '---',
                '是否逾期' => $value['is_overdue'] == UserLoanOrderRepayment::IS_OVERDUE_YES ? "是" : "否",
                '逾期天数' => $value['overdue_day'],
                '状态' =>  isset(UserLoanOrderRepayment::$repayment_status_map[$value['status']])?UserLoanOrderRepayment::$repayment_status_map[$value['status']]:"---",
            ];
        }
        echo $this->_array2csv($items,'2048M');
        exit;
    }

    /**
     * 还款列表过滤
     * @return string
     */
    protected function getTrailFilter() {
        $condition[] = 'and';
        if ($this->getRequest()->getIsGet()) {
            $search = $this->request->get();
            if (isset($search['id']) && !empty($search['id'])) {
                $condition[] = ['userLoanOrderRepayment.id' => CommonHelper::idDecryption($search['id'])];
            }
            if (isset($search['user_id']) && !empty($search['user_id'])) {
                $condition[] = ['loanPerson.id' => CommonHelper::idDecryption($search['user_id'])];
            }
            if (isset($search['customer_type']) && $search['customer_type']!='') {
                $condition[] = ['loanPerson.customer_type' => intval($search['customer_type'])];
            }
            if (isset($search['order_id']) && !empty($search['order_id'])) {
                $condition[] = ['userLoanOrderRepayment.order_id' =>  CommonHelper::idDecryption($search['order_id'])];
            }
            if (isset($search['status']) && ($search['status'])!== '') {
                $condition[] = ['userLoanOrderRepayment.status' => intval($search['status'])];
            }
            if (isset($search['name']) && !empty($search['name'])) {
                $condition[] = ['loanPerson.name' => $search['name']];
            }
            if (isset($search['phone']) && !empty($search['phone'])) {
                $condition[] = ['loanPerson.phone' => $search['phone']];
            }
            if (isset($search['begintime']) && !empty($search['begintime'])) {
                $condition[] = ['>=', 'userLoanOrderRepayment.plan_repayment_time', strtotime($search['begintime'])];
            }
            if (isset($search['endtime']) && !empty($search['endtime'])) {
                $condition[] = ['<=', 'userLoanOrderRepayment.plan_repayment_time', strtotime($search['endtime'])];
            }
            if (isset($search['r_begintime']) && !empty($search['r_begintime'])) {
                $condition[] = ['>=', 'userLoanOrderRepayment.closing_time', strtotime($search['r_begintime'])];
            }
            if (isset($search['r_endtime']) && !empty($search['r_endtime'])) {
                $condition[] = ['<= ', 'userLoanOrderRepayment.closing_time', strtotime($search['r_endtime'])];
            }
            if (isset($search['u_begintime']) && !empty($search['u_begintime'])) {
                $condition[] = ['>=', 'financialPaymentOrder.updated_at', strtotime($search['u_begintime'])];
            }
            if (isset($search['u_endtime']) && !empty($search['u_endtime'])) {
                $condition[] = ['<=', 'financialPaymentOrder.updated_at', strtotime($search['u_endtime'])];
            }
            if (isset($search['is_overdue']) && $search['is_overdue'] !== '') {
                $condition[] = ['userLoanOrderRepayment.is_overdue' => intval($search['is_overdue'])];
            }
            if (isset($search['is_first']) && $search['is_first'] !== '') {
                $condition[] = ['userLoanOrder.is_first' => intval($search['is_first'])];
            }
            if (isset($search['overdue_day']) && $search['overdue_day'] !== '') {
                $overdue_day = explode('-',$search['overdue_day']);
                if(count($overdue_day) > 1){
                    $condition[] = ['between', 'userLoanOrderRepayment.overdue_day', intval($overdue_day[0]), intval($overdue_day[1])];
                }else{
                    $condition[] = ['userLoanOrderRepayment.overdue_day' => intval($overdue_day[0])];
                }
            }

            // 商户筛选，防止拼接参数来绕过判断
            if (!empty($this->isNotMerchantAdmin) && !empty($search['merchant_id']) && $search['merchant_id'] != '') {
                $condition[] = ['userLoanOrderRepayment.merchant_id' => intval($search['merchant_id'])];
            }else {
                // 没有搜索条件，各看各的公司
                if (is_array($this->merchantIds)) {
                    $sMerchantIds = $this->merchantIds;
                } else {
                    $sMerchantIds = explode(',', $this->merchantIds);
                }
                $condition[] = ['userLoanOrderRepayment.merchant_id' => $sMerchantIds];
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
            if (!empty($search['source_id'])) {
                $condition[] = ['loanPerson.source_id' => intval($search['source_id'])];
            }
            if (isset($search['is_delay_repayment']) && $search['is_delay_repayment'] !== '') {
                if($search['is_delay_repayment'] == 1){
                    $condition[] = ['userLoanOrderRepayment.is_delay_repayment' => UserLoanOrderRepayment::IS_DELAY_YES];
                }else{
                    $condition[] = ['!=', 'userLoanOrderRepayment.is_delay_repayment', UserLoanOrderRepayment::IS_DELAY_YES];
                }
            }
            if (isset($search['d_begintime']) && !empty($search['d_begintime'])) {
                $condition[] = ['>=', 'userLoanOrderRepayment.delay_repayment_time', strtotime($search['d_begintime'])];
            }
            if (isset($search['d_endtime']) && !empty($search['d_endtime'])) {
                $condition[] = ['<=', 'userLoanOrderRepayment.delay_repayment_time', strtotime($search['d_endtime'])];
            }
        }
        return $condition;
    }

    /**
     * @name reapyment detail
     * @param $id
     * @return string
     * @name-cn 借款管理-用户借款管理-借款列表-查看
     */
    public function actionDetail($id)
    {
        $id = CommonHelper::idDecryption($id);
        /** @var UserLoanOrderRepayment $repayment */
        $repayment = UserLoanOrderRepayment::find()->where(['id' => $id])->one();
        if(!isset($repayment->userLoanOrder)){
            return $this->redirectMessage(Yii::T('common', 'Order does not exist'), self::MSG_ERROR);
        }
        $service = new OrderService($repayment->userLoanOrder);
        $information = $service->getOrderDetailInfo();
        $information['userLoanOrderRepayment'] = $repayment;

        //虚拟账号
//        $service = new RazorpayService($repayment->userLoanOrder->loanFund->payAccountSetting);
//        $model = $service->createUPIAddress($repayment->userLoanOrder->id, $repayment->userLoanOrder->user_id);

        return $this->render('detail', array(
            'information' => $information,
            'virtualAccount' => [
                'va_account' => '',//$model->va_account,
                'va_name' => '',//$model->va_name,
                'va_ifsc' => '',//$model->va_ifsc,
                'address' => '',//$model->address,
            ]
        ));
    }

    /**
     * @name 手动还款
     * @params $id
     * @return string
     */
    public function actionFinishDebit($id)
    {
        $id = CommonHelper::idDecryption($id);
        /** @var UserLoanOrderRepayment $orderRepayment */
        $orderRepayment = UserLoanOrderRepayment::find()->where(['id' => $id, 'merchant_id' => $this->merchantIds])->one();
        if($this->request->getIsPost()){
            /** @var IdentityInterface $operator */
            $operator = \Yii::$app->user->identity->getId();
            $repaymentType = $this->request->post('repayment_type');
            $remark = $this->request->post('remark');
            if(empty($remark)) {
                return $this->redirectMessage(Yii::T('common', 'Notes cannot be empty'), self::MSG_ERROR);
            }

            if($repaymentType ==0 ){
                return $this->redirectMessage(Yii::T('common', 'Please select repayment method'), self::MSG_ERROR);
            }
            $money = intval(bcmul($this->request->post('money'),100));
            if($money <= 0){
                return $this->redirectMessage(Yii::T('common', 'The actual repayment amount cannot be empty'), self::MSG_ERROR);
            }
            $repaymentService = new RepaymentService();
            $res =  $repaymentService->repaymentHandle($orderRepayment->order_id, $money, $repaymentType, $operator);
            if($res){
                return $this->redirectMessage('repayment success', self::MSG_SUCCESS, Url::toRoute(['repay-order/list']));
            }else{
                return $this->redirectMessage('repayment error:'.$repaymentService->getError(), self::MSG_ERROR);
            }
        }

        $order = UserLoanOrder::findOne($orderRepayment->order_id);
        if(in_array($order->clientInfoLog->package_name, ['bigshark', 'dhancash', 'hindmoney']))
        {
            $scheduledPaymentAmount = CommonHelper::UnitToCents(floor(CommonHelper::CentsToUnit($orderRepayment->getScheduledPaymentAmount())) + 0.1);
        }else{
            $scheduledPaymentAmount = $orderRepayment->getScheduledPaymentAmount();
        }
        $repaymentInfo = [
            'totalMoney' => $orderRepayment->total_money,
            'scheduledPaymentAmount' => $scheduledPaymentAmount,
            'trueTotalMoney' => $orderRepayment->true_total_money,
        ];
        return $this->render('finish-debit', [
            'repaymentInfo' => $repaymentInfo
        ]);
    }

    /**
     * @name 减免
     * @params int $id
     * @return string
     */
    public function actionReduced($id)
    {
        $id = CommonHelper::idDecryption($id);

        /** @var UserLoanOrderRepayment $orderRepayment */
        $orderRepayment = UserLoanOrderRepayment::find()->where(['id' => $id, 'merchant_id' => $this->merchantIds])->one();
        if($this->request->getIsPost()){
            /** @var IdentityInterface $operator */
            $operator = \Yii::$app->user->identity->getId();
            $remark = $this->request->post('remark');
            if(empty($remark)) {
                return $this->redirectMessage(Yii::T('common', 'Notes cannot be empty'), self::MSG_ERROR);
            }

            $reductionService = new ReductionService();
            $res = $reductionService->reductionHandle($orderRepayment->order_id, $operator, $remark, UserRepaymentReducedLog::FROM_ADMIN_SYSTEM);
            if($res){
                return $this->redirectMessage('repayment success', self::MSG_SUCCESS, Url::toRoute(['repay-order/list']));
            }else{
                return $this->redirectMessage('repayment error:'.$reductionService->getError(), self::MSG_ERROR);
            }
        }
        $repaymentInfo = [
            'totalMoney' => $orderRepayment->total_money,
            'principal' => $orderRepayment->principal,
            'scheduledPaymentAmount' => $orderRepayment->getScheduledPaymentAmount(),
            'trueTotalMoney' => $orderRepayment->true_total_money,
        ];
        return $this->render('reduced', [
            'repaymentInfo' => $repaymentInfo
        ]);
    }

    /**
     * @param string $type
     * @return string|void
     * @name 减免日志
     */
    public function actionReducedLog()
    {
        $condition[] = 'and';
        if ($this->getRequest()->getIsGet()) {
            $search = $this->request->get();
            if (isset($search['user_id']) && !empty($search['user_id'])) {
                $condition[] = ['user.id' => CommonHelper::idDecryption($search['user_id'])];
            }
            if (isset($search['order_id']) && !empty($search['order_id'])) {
                $condition[] = ['reducedLog.order_id' => CommonHelper::idDecryption($search['order_id'])];
            }
            if (isset($search['repayment_id']) && !empty($search['repayment_id'])) {
                $condition[] = ['reducedLog.repayment_id' => CommonHelper::idDecryption($search['repayment_id'])];
            }
            if (isset($search['name']) && !empty($search['name'])) {
                $condition[] = ['user.name' => $search['name']];
            }
            if (isset($search['phone']) && !empty($search['phone'])) {
                $condition[] = ['user.phone' => $search['phone']];
            }
            if (isset($search['begintime']) && !empty($search['begintime'])) {
                $condition[] = ['>=', 'reducedLog.created_at', strtotime($search['begintime'])];
            }
            if (isset($search['endtime']) && !empty($search['endtime'])) {
                $condition[] = ['<=', 'reducedLog.created_at', strtotime($search['endtime'])];
            }
            // 商户筛选，防止拼接参数来绕过判断
            if (!empty($this->isNotMerchantAdmin) && !empty($search['merchant_id']) && $search['merchant_id'] != '') {
                $condition[] = ['reducedLog.merchant_id' => intval($search['merchant_id'])];
            }else {
                // 没有搜索条件，各看各的公司
                if (is_array($this->merchantIds)) {
                    $sMerchantIds = $this->merchantIds;
                } else {
                    $sMerchantIds = explode(',', $this->merchantIds);
                }
                $condition[] = ['reducedLog.merchant_id' => $sMerchantIds];
            }
        }
        $query = UserRepaymentReducedLog::find()
            ->from(UserRepaymentReducedLog::tableName() . ' as reducedLog')
            ->leftJoin(LoanPerson::tableName() . 'as user', 'user.id = reducedLog.user_id')
            ->where($condition);
        $count = 99999999;
        $pages = new Pagination(['totalCount' => $count]);
        $pages->pageSize = 15;
        $info = $query
            ->select('
            reducedLog.*,
            user.name,
            user.phone
            ')
            ->offset($pages->offset)
            ->limit($pages->limit)
            ->orderBy(['reducedLog.id' => SORT_DESC])
            ->asArray()
            ->all();
        $backendAdminIds = [];
        $callcenterAdminIds = [];
        foreach ($info as $val){
            if($val['from'] == UserRepaymentReducedLog::FROM_ADMIN_SYSTEM){
                $backendAdminIds[] = $val['operator_id'];
            }elseif($val['from'] == UserRepaymentReducedLog::FROM_CS_SYSTEM){
                $callcenterAdminIds[] = $val['operator_id'];
            }
        }
        $adminName = [];
        $adminUser =  AdminUser::find()->where(['id' => $backendAdminIds])->all();
        foreach ($adminUser as $admin){
            $adminName[UserRepaymentReducedLog::FROM_ADMIN_SYSTEM.'_'.$admin['id']] = $admin['username'];
        }
        $adminUser = \callcenter\models\AdminUser::find()->where(['id' => $callcenterAdminIds])->all();
        foreach ($adminUser as $admin){
            $adminName[UserRepaymentReducedLog::FROM_CS_SYSTEM.'_'.$admin['id']] = $admin['username'];
        }
        foreach ($info as &$val){
            $val['operator_name'] = $adminName[$val['from'].'_'.$val['operator_id']] ?? ($val['from'].'_'.$val['operator_id']);
        }

        return $this->render('reduced-log', array(
            'info' => $info,
            'pages' => $pages,
            'isNotMerchantAdmin' => $this->isNotMerchantAdmin
        ));
    }


    /**
     * @param string $type
     * @return string|void
     * @name 支付订单
     */
    public function actionPayOrderList()
    {
        $searchModel = new FinancialPaymentRecordSearch();
        $dataProvider = $searchModel->search($this->merchantIds, Yii::$app->request->get());
        return $this->render('pay-order-list-new', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'isNotMerchantAdmin' => $this->isNotMerchantAdmin
        ]);

    }

    /**
     * @name 还款日志
     * @return string|void
     */
    public function actionRepaymentLogList()
    {
        $condition[] = 'and';
        if ($this->getRequest()->getIsGet()) {
            $search = $this->request->get();
            if (isset($search['id']) && !empty($search['id'])) {
                $condition[] = ['l.id' => CommonHelper::idDecryption($search['id'])];
            }
            if (isset($search['user_id']) && !empty($search['user_id'])) {
                $condition[] = ['p.id' => CommonHelper::idDecryption($search['user_id'])];
            }
            if (isset($search['order_id']) && !empty($search['order_id'])) {
                $condition[] = ['l.order_id' => CommonHelper::idDecryption($search['order_id'])];
            }
            if (isset($search['phone']) && !empty($search['phone'])) {
                $condition[] = ['p.phone' => $search['phone']];
            }
            if (isset($search['type']) && !empty($search['type'])) {
                $condition[] = ['l.type' => intval($search['type'])];
            }
            if (isset($search['begintime']) && !empty($search['begintime'])) {
                $condition[] = ['>=', 'l.created_at', strtotime($search['begintime'])];
            }
            if (isset($search['endtime']) && !empty($search['endtime'])) {
                $condition[] = ['<=', 'l.created_at', strtotime($search['endtime'])];
            }
            if (isset($search['collector_id']) && !empty($search['collector_id'])) {
                $condition[] = ['l.collector_id' => intval($search['collector_id'])];
            }
            if (isset($search['collector_name']) && !empty(trim($search['collector_name']))) {
                $collectorID = \callcenter\models\AdminUser::find()->select(['id'])
                    ->where(['username' => trim($search['collector_name'])])->scalar();
                if(!empty($collectorID))
                {
                    $condition[] = ['l.collector_id' => $collectorID];
                }
            }
            // 商户筛选，防止拼接参数来绕过判断
            if (!empty($this->isNotMerchantAdmin) && !empty($search['merchant_id']) && $search['merchant_id'] != '') {
                $condition[] = ['l.merchant_id' => intval($search['merchant_id'])];
            }else {
                // 没有搜索条件，各看各的公司
                if (is_array($this->merchantIds)) {
                    $sMerchantIds = $this->merchantIds;
                } else {
                    $sMerchantIds = explode(',', $this->merchantIds);
                }
                $condition[] = ['l.merchant_id' => $sMerchantIds];
            }
        }
        $pageSize = yii::$app->request->get('per-page', 15);
        $query = UserRepaymentLog::find()
            ->from(UserRepaymentLog::tableName(). ' as l')
            ->leftJoin(LoanPerson::tableName() . ' as p', 'l.user_id = p.id')
            ->where($condition);

        $queryClone = clone $query;

        $count = $queryClone->count();
        $pages = new Pagination(['totalCount' => $count]);
        $pages->pageSize = $pageSize;
        $info = $query
            ->select(['l.*', 'p.name', 'p.phone'])
            ->offset($pages->offset)
            ->limit($pages->limit)
            ->orderBy(['l.id' => SORT_DESC])
            ->asArray()
            ->all();

        return $this->render('repayment-log-list', array(
            'info' => $info,
            'pages' => $pages,
            'isNotMerchantAdmin' => $this->isNotMerchantAdmin
        ));
    }


    /**
     * @name 还款管理-虚拟账号
     * @return string
     */
    public function actionVirtualAccountList()
    {
        $searchModel = new RazorpayVirtualAccountSearch();
        $dataProvider = $searchModel->search($this->merchantIds, Yii::$app->request->get());
        return $this->render('virtual-account-list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'isNotMerchantAdmin' => $this->isNotMerchantAdmin
        ]);

    }


    /**
     * @name RepayOrderController 确认退款
     * @return array
     */
    public function actionConfirmRefund()
    {
        $this->response->format = yii\web\Response::FORMAT_JSON;
        if(Yii::$app->request->isPost){
            if(!$this->isNotMerchantAdmin)
            {
                return false;
            }
            $id = intval(Yii::$app->request->post('id', 0));
            $remark = Yii::$app->request->post('remark');
            if(empty($id))
            {
                return [
                    'code' => -1,
                    'message' => '参数错误'
                ];
            }
            /** @var FinancialPaymentOrder $record */
            $record = FinancialPaymentOrder::find()->where([
                'id' => $id,
                'status' => FinancialPaymentOrder::STATUS_SUCCESS,
                'is_refund' => FinancialPaymentOrder::IS_REFUND_NO,
                'is_booked' => FinancialPaymentOrder::IS_BOOKED_NO
            ])->one();
            if(is_null($record))
            {
                return [
                    'code' => -1,
                    'message' => '无对应记录'
                ];
            }
            $record->is_refund = FinancialPaymentOrder::IS_REFUND_YES;
            $record->remark = $remark;
            if($record->save())
            {
                return [
                    'code' => 0,
                    'message' => '操作成功'
                ];
            }else{
                return [
                    'code' => -1,
                    'message' => '操作失败'
                ];
            }
        }
    }


    /**
     * @name 手动还款
     * @params $id
     * @return string
     */
    public function actionFinishDebitNew($id)
    {
        $id = CommonHelper::idDecryption($id);
        /** @var UserLoanOrderRepayment $orderRepayment */
        $orderRepayment = UserLoanOrderRepayment::find()->where(['id' => $id, 'merchant_id' => $this->merchantIds])->one();
        if($this->request->getIsPost()){
            /** @var IdentityInterface $operator */
            $operator = \Yii::$app->user->identity->getId();
            $uuid = trim($this->request->post('uuid'));
            if(empty($uuid)) {
                return $this->redirectMessage('流水号不能位空', self::MSG_ERROR);
            }
            $remark = trim($this->request->post('remark'));
            if(empty($remark)) {
                return $this->redirectMessage('备注不能为空', self::MSG_ERROR);
            }
            $money = intval(bcmul($this->request->post('money'),100));
            if($money <= 0){
                return $this->redirectMessage('实际还款金额不能为空', self::MSG_ERROR);
            }


            $check = FinancialPaymentOrder::find()->where([
                'pay_order_id' => $uuid,
                'service_type' => FinancialPaymentOrder::SERVICE_TYPE_PAYU,
                'status' => FinancialPaymentOrder::STATUS_SUCCESS
            ])->exists();

            if($check)
            {
                return $this->redirectMessage('该流水号已入账', self::MSG_ERROR);
            }
            $repaymentService = new RepaymentService();
            $res =  $repaymentService->repaymentHandle($orderRepayment->order_id, $money, UserRepaymentLog::TYPE_ACTIVE, $operator);
            if($res){
                // 添加手动还款记录
                $financialPaymentOrder = new FinancialPaymentOrder();
                $financialPaymentOrder->order_id = $orderRepayment->order_id;
                $financialPaymentOrder->user_id = $orderRepayment->user_id;
                $financialPaymentOrder->pay_order_id = $uuid;
                $financialPaymentOrder->amount = $money;
                $financialPaymentOrder->status = FinancialPaymentOrder::STATUS_SUCCESS;
                $financialPaymentOrder->success_time = time();
                $financialPaymentOrder->remark = $remark;
                $financialPaymentOrder->is_booked = FinancialPaymentOrder::IS_BOOKED_YES;
                $financialPaymentOrder->service_type = FinancialPaymentOrder::SERVICE_TYPE_PAYU;
                $financialPaymentOrder->save();

                return $this->redirectMessage('repayment success', self::MSG_SUCCESS, Url::toRoute(['repay-order/list']));
            }else{
                return $this->redirectMessage('repayment error:'.$repaymentService->getError(), self::MSG_ERROR);
            }
        }

        $order = UserLoanOrder::findOne($orderRepayment->order_id);
        if(in_array($order->clientInfoLog->package_name, ['bigshark', 'dhancash', 'hindmoney']))
        {
            $scheduledPaymentAmount = CommonHelper::UnitToCents(floor(CommonHelper::CentsToUnit($orderRepayment->getScheduledPaymentAmount())) + 0.1);
        }else{
            $scheduledPaymentAmount = $orderRepayment->getScheduledPaymentAmount();
        }


            $repaymentInfo = [
            'totalMoney' => $orderRepayment->total_money,
            'scheduledPaymentAmount' => $scheduledPaymentAmount,
            'trueTotalMoney' => $orderRepayment->true_total_money,
        ];
        return $this->render('finish-debit-new', [
            'repaymentInfo' => $repaymentInfo
        ]);
    }

    /**
     * @name 导流订单的用户转账上传信息
     * @return string|void
     */
    public function actionExternalUserTransferLog()
    {
        $condition[] = 'and';
        if ($this->getRequest()->getIsGet()) {
            $search = $this->request->get();
            if (isset($search['order_id']) && !empty($search['order_id'])) {
                $condition[] = ['e.order_id' => CommonHelper::idDecryption($search['order_id'])];
            }
            if (isset($search['begintime']) && !empty($search['begintime'])) {
                $condition[] = ['>=', 'e.created_at', strtotime($search['begintime'])];
            }
            if (isset($search['endtime']) && !empty($search['endtime'])) {
                $condition[] = ['<=', 'e.created_at', strtotime($search['endtime'])];
            }
        }
        $pageSize = yii::$app->request->get('per-page', 15);
        $query = UserTransferLogExternal::find()
            ->alias('e')
            ->leftJoin(UserLoanOrderRepayment::tableName() . ' as r', 'e.order_id = r.order_id')
            ->where($condition)
            ->andWhere(['r.merchant_id' => $this->merchantIds]);

        $queryClone = clone $query;

        $count = $queryClone->count();
        $pages = new Pagination(['totalCount' => $count]);
        $pages->pageSize = $pageSize;
        $info = $query
            ->select(['e.*', 'repayment_id' => 'r.id','r.status'])
            ->offset($pages->offset)
            ->limit($pages->limit)
            ->orderBy(['e.id' => SORT_DESC])
            ->asArray()
            ->all();

        return $this->render('external-user-transfer-log', array(
            'info' => $info,
            'pages' => $pages,
        ));
    }
}
<?php

namespace callcenter\service;

use callcenter\models\AdminManagerRelation;
use callcenter\models\AdminUser;
use callcenter\models\AdminUserRole;
use callcenter\models\CollectionOrderDispatchLog;
use callcenter\models\CollectorClassSchedule;
use callcenter\models\loan_collection\LoanCollectionOrder;
use callcenter\models\loan_collection\LoanCollectionOrderAll;
use callcenter\models\loan_collection\LoanCollectionRecord;
use callcenter\models\loan_collection\LoanCollectionStatusChangeLog;
use callcenter\models\loan_collection\StopRegainInputOrder;
use callcenter\models\loan_collection\UserCompany;
use callcenter\models\SmsTemplate;
use common\helpers\CommonHelper;
use common\helpers\MessageHelper;
use common\helpers\RedisQueue;
use common\models\enum\Relative;
use common\models\manual_credit\ManualSecondMobile;
use common\models\message\ExternalOrderMessageForm;
use common\models\order\UserLoanOrder;
use common\models\order\UserLoanOrderExternal;
use common\models\order\UserLoanOrderRepayment;
use common\models\user\LoanPerson;
use common\models\user\MgUserMobileContacts;
use common\services\FileStorageService;
use common\services\message\ExternalOrderMessageService;
use common\services\message\SendMessageService;
use common\services\message\WeWorkService;
use yii\base\Exception;
use yii;
use yii\web\UploadedFile;

class LoanCollectionService
{
    const SUCCESS_CODE = 0;
    const ERROR_CODE = -1;

    const OPERATOR_SYSTEM = 'system';
    const OPERATOR_SUPER_ADMIN = 'superadmin';

    /** @var AdminUser  */
    public $operated;  //被操作人

    /** @var LoanCollectionOrder */
    public $loanCollectionOrder;

    /** @var AdminUser */
    public $operator;

    public $operatorRoleGroups;

    public $canDispatch = [];

    public function __construct($operatorId = '')  //admin_user_id
    {
        if(empty($operatorId)){
            $this->operator = null;
        }else{
            $this->operator = AdminUser::findIdentity(($operatorId));
            if($this->operator->role == AdminUser::SUPER_ROLE){
                $this->operatorRoleGroups = 0;
            }else{
                $group = AdminUserRole::find()->select('groups')->where(['name' => $this->operator->role])->asArray()->one();
                if($group){
                    $this->operatorRoleGroups = $group['groups'];
                }
            }
        }
    }

    /**
     * @name 获取能否按班表派单
     * @return bool
     */
    public function getCanDispatchSchedule(){
        if(isset($this->canDispatch[$this->operated->id])){
            return $this->canDispatch[$this->operated->id];
        }else{
            return $this->setCanDispatchSchedule();
        }
    }

    /**
     * @name 设置能否按班表派单
     * @return bool
     */
    public function setCanDispatchSchedule(){
        if( $this->operated->can_dispatch == AdminUser::CAN_ONT_DISPATCH ){
            $this->canDispatch[$this->operated->id] = false;
            return false;
        }else{
            $collectorClassSchedule =  CollectorClassSchedule::find()
                ->where(['date' => date('Y-m-d'), 'admin_id' => $this->operated->id, 'status' => CollectorClassSchedule::STATUS_OPEN])
                ->one();
            if($collectorClassSchedule){
                $this->canDispatch[$this->operated->id] = false;
                return false;
            }
        }
        $this->canDispatch[$this->operated->id] = true;
        return true;
    }

    /**
     * @name AdminUser 检查操作人能否操作催收员的订单
     * @return bool
     */
    public function checkCanOperatedCollector($operatedId){
        if(is_null($this->operated)){
            $this->operated = AdminUser::findIdentity($operatedId);
        }
        if($this->operator->role == AdminUser::SUPER_ROLE){
            return true;
        }else{
            if($this->operator->merchant_id > 0 && $this->operator->merchant_id != $this->operated->merchant_id){
                return false;
            }
            if(AdminUserRole::TYPE_SUPER_MANAGER == $this->operatorRoleGroups){
                return true;
                //超级管理可分派全部
            }elseif (AdminUserRole::TYPE_COMPANY_MANAGER == $this->operatorRoleGroups){
                //机构管理可分派本机构
                if($this->operator->outside == $this->operated->outside){
                    return true;
                }
            }elseif (AdminUserRole::TYPE_SMALL_TEAM_MANAGER == $this->operatorRoleGroups){
                //机构管理可分派本机构
                if($this->operator->outside == $this->operated->outside && $this->operator->group_game == $this->operated->group_game){
                    return true;
                }
            }elseif (AdminUserRole::TYPE_BIG_TEAM_MANAGER == $this->operatorRoleGroups){
                if($this->operator->outside != $this->operated->outside){
                    return false;
                }
                $res = AdminManagerRelation::find()
                    ->where(['admin_id' => $this->operator->id,'group' => $this->operated->group,'group_game' => $this->operated->group_game])
                    ->exists();
                return $res;
            }elseif (AdminUserRole::TYPE_SUPER_TEAM == $this->operatorRoleGroups){
                $res = AdminManagerRelation::find()
                    ->where(['admin_id' => $this->operator->id, 'outside' => $this->operated->outside,'group' => $this->operated->group,'group_game' => $this->operated->group_game])
                    ->exists();
                return $res;
            }
        }

        return false;
    }

    /**
     * 订单分配给公司
     * @param $loanCollectionOrderId
     * @param $outSide
     * @return array
     */
    public function dispatchToCompany($loanCollectionOrderId ,$outSide)
    {
        try{
            $this->loanCollectionOrder = LoanCollectionOrder::findOne($loanCollectionOrderId);
            $company = UserCompany::findOne($outSide);
            if(empty($this->loanCollectionOrder)|| empty($company))  {
                throw new Exception('LoanCollectionOrder or company is empty');
            }
            if($this->loanCollectionOrder->outside != 0){
                throw new Exception('The order has been distributed');
            }
            if($company->merchant_id > 0 && $company->merchant_id != $this->loanCollectionOrder->merchant_id){
                throw new Exception('The this company can not get the merchant order');
            }
            //判断订单状态
            if($this->loanCollectionOrder->status != LoanCollectionOrder::STATUS_WAIT_COLLECTION){
                throw new Exception('CollectionOrderId：' . $this->loanCollectionOrder->id . ',status is error');
            }
            $this->loanCollectionOrder->outside = $outSide;//修改催收公司
            $this->loanCollectionOrder->dispatch_outside_time = time();//分派催收公司时间
            if(!$this->loanCollectionOrder->save()){
                throw new Exception("Distributor failure，ID：".$this->loanCollectionOrder->id."，company ID：".$outSide);
            }

            //添加分派订单日志type TO_COMPANY_TYPE
            $repayment = UserLoanOrderRepayment::findOne($this->loanCollectionOrder->user_loan_order_repayment_id);
            $dispatchLog = new CollectionOrderDispatchLog();
            $dispatchLog->collection_order_id = $this->loanCollectionOrder->id;
            $dispatchLog->collection_order_level = $this->loanCollectionOrder->current_overdue_level;
            $dispatchLog->type = CollectionOrderDispatchLog::TO_COMPANY_TYPE;
            $dispatchLog->outside = $this->loanCollectionOrder->outside;
            //$dispatchLog->admin_user_id = 0;
            $dispatchLog->operator_id = $this->operator->id ?? 0;
            $dispatchLog->merchant_id = $this->loanCollectionOrder->merchant_id ?? 0;
            $dispatchLog->order_repayment_id = $this->loanCollectionOrder->user_loan_order_repayment_id;
            $dispatchLog->overdue_day = $repayment->overdue_day;
            $dispatchLog->overdue_fee = $repayment->overdue_fee;
            $dispatchLog->save();
            return ['code'=>self::SUCCESS_CODE, 'message' => 'success'];
        }catch(Exception $e){
            return ['code'=>self::ERROR_CODE, 'message' => $e->getMessage()];
        }
    }


//    /**
//     * 按查询条件批量订单分配给公司
//     * @param string $condition
//     * @param int $outSide
//     * @return bool
//     */
//    public function batchDispatchToCompany($condition ,$outSide)
//    {
//        try{
//            $rows = LoanCollectionOrder::updateAll(['outside' => $outSide],$condition);
//            if($rows > 0){
//                return true;
//            }
//            return false;
//        }catch(Exception $e){
//            echo $e->getMessage().PHP_EOL;
//        }
//        return false;
//    }


    /**
     * 订单指派指定人
     * @param $collectionOrderId
     * @param $operatedId
     * @return array
     */
    public function dispatchToOperator($collectionOrderId, $operatedId){
        try {
            $this->loanCollectionOrder = LoanCollectionOrder::findOne($collectionOrderId);
            if(!$this->loanCollectionOrder){
                throw new Exception('CollectionOrderId：' . $this->loanCollectionOrder->id . ' is error');
            }
            //判断订单状态
            if($this->loanCollectionOrder->status != LoanCollectionOrder::STATUS_WAIT_COLLECTION){
                throw new Exception('CollectionOrderId：' . $this->loanCollectionOrder->id . ',status is error');
            }
            $this->operated = AdminUser::findIdentity($operatedId);
            if(!$this->operated){
                throw new Exception('CollectionOrderId：' . $this->loanCollectionOrder->id . ',Cannot be assigned (the operator does not exist) operatedId'.$operatedId);
            }
            if($this->operated->open_status == AdminUser::OPEN_STATUS_OFF){
                throw new Exception('CollectionOrderId：' . $this->loanCollectionOrder->id . ',Cannot be assigned (the operator not open) operatedId'.$operatedId);
            }
            if(!$this->getCanDispatchSchedule()){
                throw new Exception('CollectionOrderId：' . $this->loanCollectionOrder->id . ',Cannot be assigned (the operator can not dispatch) operatedId'.$operatedId);
            }
            if(!$this->operated->checkCollectorOrderMerchant($this->loanCollectionOrder->merchant_id)){
                throw new Exception('CollectionOrderId：' . $this->loanCollectionOrder->id . ',Cannot be assigned (the operator not get this merchant order) operatedId'.$operatedId);
            }

            if(is_null($this->operator)){
                $operatorName = 'system';
            }else{
                $operatorName = $this->operator->username;
            }
            //判断订单是已绑定其他人
            if ($this->loanCollectionOrder->current_collection_admin_user_id != 0) {
                throw new Exception('CollectionOrderId：' . $this->loanCollectionOrder->id . ',The order has been assigned');
            }

            $beforeStatus = $this->loanCollectionOrder->status;
            $status = LoanCollectionOrder::STATUS_COLLECTION_PROGRESS;

            $dispatchType = CollectionOrderDispatchLog::TO_ADMIN_USER_TYPE;
            if($this->loanCollectionOrder->outside == 0){
                //直接分派至公司里的人
                $this->loanCollectionOrder->dispatch_outside_time = time();
                $dispatchType = CollectionOrderDispatchLog::TO_JUMP_COMPANY_TO_USER_TYPE;
            }
            $this->loanCollectionOrder->outside = $this->operated->outside;
            $this->loanCollectionOrder->updated_at = time();
            $this->loanCollectionOrder->before_status = $beforeStatus;
            $this->loanCollectionOrder->status = $status;
            $this->loanCollectionOrder->operator_name = $operatorName;//操作人
            $this->loanCollectionOrder->dispatch_name = $this->operated->username;//派单人
            $this->loanCollectionOrder->dispatch_time = time();//派单时间
            $this->loanCollectionOrder->last_dispatch_time = time();//最新派单时间 用于统计
            $this->loanCollectionOrder->current_collection_admin_user_id = $this->operated->id;//当前催收员ID
            $this->loanCollectionOrder->current_overdue_group = $this->operated->group;
            if (!$this->loanCollectionOrder->save()) {
                throw new Exception("dispatch fail，order ID：" . $this->loanCollectionOrder->id . "，The collector admin_id ：" . $this->operated->id);
            }

            //添加派分日志 type TO_ADMIN_USER_TYPE
            $repayment = UserLoanOrderRepayment::findOne($this->loanCollectionOrder->user_loan_order_repayment_id);
            $dispatchLog = new CollectionOrderDispatchLog();
            $dispatchLog->collection_order_id = $this->loanCollectionOrder->id;
            $dispatchLog->collection_order_level = $this->loanCollectionOrder->current_overdue_level;
            $dispatchLog->type = $dispatchType;
            $dispatchLog->outside = $this->loanCollectionOrder->outside;
            $dispatchLog->admin_user_id = $this->operated->id;
            $dispatchLog->operator_id = $this->operator->id ?? 0;
            $dispatchLog->merchant_id = $this->loanCollectionOrder->merchant_id ?? 0;
            $dispatchLog->order_repayment_id = $this->loanCollectionOrder->user_loan_order_repayment_id;
            $dispatchLog->overdue_day = $repayment->overdue_day;
            $dispatchLog->overdue_fee = $repayment->overdue_fee;
            $dispatchLog->save();

            //添加催收订单状态转变日志
            $loan_collection_status_change_log = new LoanCollectionStatusChangeLog();
            $loan_collection_status_change_log->loan_collection_order_id = $this->loanCollectionOrder->id;//催收表ID
            $loan_collection_status_change_log->before_status = $beforeStatus;
            $loan_collection_status_change_log->after_status = $status;
            $loan_collection_status_change_log->type = LoanCollectionOrder::TYPE_DISPATCH_COLLECTION;//类型：派单
            $loan_collection_status_change_log->created_at = time();
            $loan_collection_status_change_log->operator_name = $operatorName;
            $loan_collection_status_change_log->merchant_id = $this->loanCollectionOrder->merchant_id ?? 0;
            $loan_collection_status_change_log->remark = "dispatch，collectionOrderId：" . $this->loanCollectionOrder->id . "，The collector admin_id：" . $this->operated->id;
            if (!$loan_collection_status_change_log->save()) {
                throw new Exception("state flow transition record failed，collectionOrderId：" . $this->loanCollectionOrder->id . "，The collector admin_id：" . $this->operated->id);
            }
            return ['code'=>self::SUCCESS_CODE, 'message' => 'success'];
        }catch (Exception $e){
            return ['code'=>self::ERROR_CODE, 'message' => $e->getMessage()];
        }
    }


    /**
     * 订单转派
     * @param LoanCollectionOrder $loanCollectionOrder
     * @param  $operatedId
     * @return array
     */
    public function transfer(LoanCollectionOrder $loanCollectionOrder, $operatedId){
        try {
            $this->loanCollectionOrder = $loanCollectionOrder;
            $beforeStatus = $this->loanCollectionOrder->status;
            $this->operated = AdminUser::findIdentity($operatedId);
            if(!$this->operated){
                throw new Exception('订单：' . $this->loanCollectionOrder->id . ',无法转派（操作者不存在）');
            }
            if ($this->loanCollectionOrder->current_collection_admin_user_id == 0
                || $this->loanCollectionOrder->outside == 0
                || $this->loanCollectionOrder->current_overdue_group == 0) {
                throw new Exception("订单还未分配，无法转派,单ID：" . $this->loanCollectionOrder->id);
            }
            if($this->loanCollectionOrder->outside != $this->operated->outside){
                throw new Exception("订单无法转派，订单机构和被指派人不匹配，单ID：" . $this->loanCollectionOrder->id);
            }
            if($this->loanCollectionOrder->current_overdue_group != $this->operated->group){
                throw new Exception("订单无法转派，订单当前组和被指派人不匹配，单ID：" . $this->loanCollectionOrder->id);
            }

            $this->loanCollectionOrder->current_collection_admin_user_id = $this->operated->admin_user_id;
            $this->loanCollectionOrder->before_status = $beforeStatus;
            $this->loanCollectionOrder->status = LoanCollectionOrder::STATUS_WAIT_COLLECTION;
            $this->loanCollectionOrder->save();

            $loan_collection_status_change_log = new LoanCollectionStatusChangeLog();
            $loan_collection_status_change_log->loan_collection_order_id = $this->loanCollectionOrder->id;//催收表ID
            $loan_collection_status_change_log->before_status = $beforeStatus;
            $loan_collection_status_change_log->after_status = $this->loanCollectionOrder->status;
            $loan_collection_status_change_log->type = LoanCollectionOrder::TYPE_RECYCLE;//类型：派单
            $loan_collection_status_change_log->created_at = time();
            $loan_collection_status_change_log->operator_name = $this->operator->username;
            $loan_collection_status_change_log->merchant_id = $this->loanCollectionOrder->merchant_id ?? 0;
            $loan_collection_status_change_log->remark = "订单回收，单ID：" . $this->loanCollectionOrder->id . "，操作人：" . $this->operator->username;
            if (!$loan_collection_status_change_log->save()) {
                throw new Exception("订单回收记录失败，单ID：" . $this->loanCollectionOrder->id);
            }
            return ['code'=>self::SUCCESS_CODE, 'message' => 'success'];
        }catch (Exception $e){
            return ['code'=>self::ERROR_CODE, 'message' => $e->getMessage()];
        }
    }

    /**
     * 订单回收
     * @param LoanCollectionOrder $loanCollectionOrder
     * @return array
     */
    public function collectionBack(LoanCollectionOrder $loanCollectionOrder){
        try {
            $this->loanCollectionOrder = $loanCollectionOrder;
            $beforeStatus = $this->loanCollectionOrder->status;
            if(in_array($beforeStatus, LoanCollectionOrder::$end_status)){
                throw new Exception("订单已完结，单ID最终状态：" . LoanCollectionOrder::$status_list[$beforeStatus]);
            }
            if ($this->loanCollectionOrder->current_collection_admin_user_id == 0
                && $this->loanCollectionOrder->status == LoanCollectionOrder::STATUS_WAIT_COLLECTION) {
                throw new Exception("订单还未分配，单ID：" . $this->loanCollectionOrder->id);
            }

            $beforeCollector = $this->loanCollectionOrder->dispatch_name;
            $this->loanCollectionOrder->outside = ($this->operator->outside ?? 0);;
            $this->loanCollectionOrder->current_overdue_group = 0;
            $this->loanCollectionOrder->current_collection_admin_user_id = 0;
            $this->loanCollectionOrder->before_status = $beforeStatus;
            $this->loanCollectionOrder->status = LoanCollectionOrder::STATUS_WAIT_COLLECTION;
            $this->loanCollectionOrder->save();

            $loan_collection_status_change_log = new LoanCollectionStatusChangeLog();
            $loan_collection_status_change_log->loan_collection_order_id = $this->loanCollectionOrder->id;//催收表ID
            $loan_collection_status_change_log->before_status = $beforeStatus;
            $loan_collection_status_change_log->after_status = $this->loanCollectionOrder->status;
            $loan_collection_status_change_log->type = LoanCollectionOrder::TYPE_RECYCLE;//回收
            $loan_collection_status_change_log->created_at = time();
            $loan_collection_status_change_log->operator_name = ($this->operator->username ?? 'system');
            $loan_collection_status_change_log->merchant_id = $this->loanCollectionOrder->merchant_id ?? 0;
            $loan_collection_status_change_log->remark = "订单回收，单ID：" . $this->loanCollectionOrder->id . "，操作人：" . ($this->operator->username ?? 'system') .'， collector :'. $beforeCollector;
            if (!$loan_collection_status_change_log->save()) {
                throw new Exception("订单回收记录失败，单ID：" . $this->loanCollectionOrder->id);
            }
            return ['code'=>self::SUCCESS_CODE, 'message' => 'success'];
        }catch (Exception $e){
            return ['code'=>self::ERROR_CODE, 'message' => $e->getMessage()];
        }
    }

    /**
     * 订单停催
     * @param LoanCollectionOrder $loanCollectionOrder
     * @param int $inputTime
     * @return array
     */
    public function collectionStop(LoanCollectionOrder $loanCollectionOrder,$inputTime){
        try {
            $this->loanCollectionOrder = $loanCollectionOrder;
            $beforeStatus = $this->loanCollectionOrder->status;
            if($beforeStatus != LoanCollectionOrder::STATUS_WAIT_COLLECTION){
                throw new Exception("订单状态错误，单ID最终状态：" . LoanCollectionOrder::$status_list[$beforeStatus]);
            }

            $stopRegainInputOrder = StopRegainInputOrder::find()->where(['collection_order_id' => $this->loanCollectionOrder->id])->one();
            if(!$stopRegainInputOrder){
                $stopRegainInputOrder = new StopRegainInputOrder();
                $stopRegainInputOrder->collection_order_id = $this->loanCollectionOrder->id;
                $stopRegainInputOrder->loan_order_id       = $this->loanCollectionOrder->user_loan_order_id;
                $stopRegainInputOrder->loan_repayment_id   = $this->loanCollectionOrder->user_loan_order_repayment_id;
            }
            $stopRegainInputOrder->operator_id         = $this->operator->id;
            $stopRegainInputOrder->status              = StopRegainInputOrder::STATUS_INVALID;
            $stopRegainInputOrder->next_input_time     = $inputTime;
            $stopRegainInputOrder->save();

            $this->loanCollectionOrder->status = LoanCollectionOrder::STATUS_STOP_URGING;
            $this->loanCollectionOrder->save();

            $loan_collection_status_change_log = new LoanCollectionStatusChangeLog();
            $loan_collection_status_change_log->loan_collection_order_id = $this->loanCollectionOrder->id;//催收表ID
            $loan_collection_status_change_log->before_status = $beforeStatus;
            $loan_collection_status_change_log->after_status = $this->loanCollectionOrder->status;
            $loan_collection_status_change_log->type = LoanCollectionOrder::TYPE_STOP_URGING;//停催
            $loan_collection_status_change_log->created_at = time();
            $loan_collection_status_change_log->operator_name = $this->operator->username;
            $loan_collection_status_change_log->remark = "订单停催，单ID：" . $this->loanCollectionOrder->id . "，操作人：" . $this->operator->username;
            if (!$loan_collection_status_change_log->save()) {
                throw new Exception("订单状态记录失败，单ID：" . $this->loanCollectionOrder->id);
            }
            return ['code'=>self::SUCCESS_CODE, 'message' => 'success'];
        }catch (Exception $e){
            return ['code'=>self::ERROR_CODE, 'message' => $e->getMessage()];
        }
    }

    /**
     * 延期订单停催
     * @param int $orderId
     * @return array
     */
    public function delayCollectionStop($orderId){
        try {
            $loanCollectionOrder = LoanCollectionOrder::find()->where(['user_loan_order_id' => $orderId])->one();
            if (!$loanCollectionOrder) {
                return ['code' => self::SUCCESS_CODE, 'message' => '没有可停催订单'];
            }
            $this->loanCollectionOrder = $loanCollectionOrder;
            if($this->loanCollectionOrder->repaymentOrder->status == UserLoanOrderRepayment::STATUS_REPAY_COMPLETE){
                return ['code' => self::SUCCESS_CODE, 'message' => '订单已结清，完成还款，无需停催'];
            }
            if($this->loanCollectionOrder->repaymentOrder->is_delay_repayment != UserLoanOrderRepayment::IS_DELAY_YES &&
                $this->loanCollectionOrder->repaymentOrder->is_extend == UserLoanOrderRepayment::IS_EXTEND_NO
            ){
                throw new Exception("订单不在延期或展期状态，无法停催,单ID：" . $this->loanCollectionOrder->id);
            }
            $beforeStatus = $this->loanCollectionOrder->status;
            $this->loanCollectionOrder->status = LoanCollectionOrder::STATUS_DELAY_STOP_URGING;
            $this->loanCollectionOrder->save();

            $loan_collection_status_change_log = new LoanCollectionStatusChangeLog();
            $loan_collection_status_change_log->loan_collection_order_id = $this->loanCollectionOrder->id;//催收表ID
            $loan_collection_status_change_log->before_status = $beforeStatus;
            $loan_collection_status_change_log->after_status = $this->loanCollectionOrder->status;
            $loan_collection_status_change_log->type = LoanCollectionOrder::TYPE_DELAY_STOP_URGING;//延期停催
            $loan_collection_status_change_log->created_at = time();
            $loan_collection_status_change_log->operator_name = 'system';
            $loan_collection_status_change_log->remark = "订单延期停催，单ID：" . $this->loanCollectionOrder->id . "，系统操作";
            if (!$loan_collection_status_change_log->save()) {
                throw new Exception("订单状态记录失败，单ID：" . $this->loanCollectionOrder->id);
            }
            //延期还款短信
            try{
                $sendMessageService = new SendMessageService();
                $messageService     = new ExternalOrderMessageService();
                $messageForm        = new ExternalOrderMessageForm();
                $productName        = $messageService->getProductName($this->loanCollectionOrder->loanOrder->is_export,
                    $this->loanCollectionOrder->loanOrder->clientInfoLog->app_market);
                $sendMessageService->phone       = $this->loanCollectionOrder->loanOrder->loanPerson->phone;
                $sendMessageService->packageName = $this->loanCollectionOrder->loanOrder->is_export ?
                    $this->loanCollectionOrder->loanOrder->productSetting->product_name : $this->loanCollectionOrder->loanOrder->clientInfoLog->package_name;
                $sendMessageService->productName = $productName;

                $messageForm->merchantId  = $this->loanCollectionOrder->loanOrder->merchant_id;
                $messageForm->userId      = $this->loanCollectionOrder->loanOrder->user_id;
                $messageForm->phone       = $this->loanCollectionOrder->loanOrder->loanPerson->phone;
                $messageForm->orderUuid   = $this->loanCollectionOrder->loanOrder->order_uuid;
                $messageForm->packageName = $this->loanCollectionOrder->loanOrder->clientInfoLog->package_name;
                $messageForm->productName = $productName;
                $messageForm->title       = ExternalOrderMessageForm::TITLE_DELAY_REPAYMENT;
                $messageForm->message     = $sendMessageService->getMsgContent($this->loanCollectionOrder->loanOrder->is_export,'delayRepayment');
                $messageService->pushToMessageQueue($messageForm);

                //推送区分内外部
                if(UserLoanOrder::IS_EXPORT_YES == $this->loanCollectionOrder->loanOrder->is_export)
                {
                    $messageService->pushToExternalPushQueue($messageForm);
                }else{
                    $messageService->pushToInsidePushQueue($messageForm);
                }

            }catch (\Exception $e){
                $service = new WeWorkService();
                $service->send($e->getMessage().' in '.$e->getTraceAsString());
            }
            return ['code'=>self::SUCCESS_CODE, 'message' => 'success'];
        }catch (Exception $e){
            $service = new WeWorkService();
            $message = '【'.YII_ENV.'】延期订单停催异常:'.$e->getMessage().$e->getTraceAsString();
            $service->send($message);
            return ['code'=>self::ERROR_CODE, 'message' => $e->getMessage()];
        }
    }

    /**
     * 订单停催复原
     * @param LoanCollectionOrder $loanCollectionOrder
     * @return array
     */
    public function collectionRecovery(LoanCollectionOrder $loanCollectionOrder){
        try {
            $this->loanCollectionOrder = $loanCollectionOrder;
            $beforeStatus = $this->loanCollectionOrder->status;
            if($beforeStatus != LoanCollectionOrder::STATUS_STOP_URGING){
                throw new Exception("订单状态错误，单ID最终状态：" . LoanCollectionOrder::$status_list[$beforeStatus]);
            }

            if(is_null($this->operator)){
                $operatorId = 0;
                $operatorName = 'system';
            }else{
                $operatorId = $this->operator->id;
                $operatorName = $this->operator->username;
            }

            /** @var StopRegainInputOrder $stopRegainInputOrder */
            $stopRegainInputOrder = StopRegainInputOrder::find()->where(['collection_order_id' => $this->loanCollectionOrder->id])->one();
            if(!$stopRegainInputOrder){
                //兼容之前，添加之前停催再恢复的记录
                $stopRegainInputOrder = new StopRegainInputOrder();
                $stopRegainInputOrder->collection_order_id = $this->loanCollectionOrder->id;
                $stopRegainInputOrder->loan_order_id       = $this->loanCollectionOrder->user_loan_order_id;
                $stopRegainInputOrder->loan_repayment_id   = $this->loanCollectionOrder->user_loan_order_repayment_id;
                $stopRegainInputOrder->next_input_time     = strtotime('today');
                $stopRegainInputOrder->operator_id         = $operatorId;
            }
            $stopRegainInputOrder->status              = StopRegainInputOrder::STATUS_UNAVAILABLE;
            $stopRegainInputOrder->save();

            $this->loanCollectionOrder->status = LoanCollectionOrder::STATUS_WAIT_COLLECTION;
            $this->loanCollectionOrder->save();

            $loan_collection_status_change_log = new LoanCollectionStatusChangeLog();
            $loan_collection_status_change_log->loan_collection_order_id = $this->loanCollectionOrder->id;//催收表ID
            $loan_collection_status_change_log->before_status = $beforeStatus;
            $loan_collection_status_change_log->after_status = $this->loanCollectionOrder->status;
            $loan_collection_status_change_log->type = LoanCollectionOrder::TYPE_STOP_URGING_RECOVERY;//停催恢复
            $loan_collection_status_change_log->created_at = time();
            $loan_collection_status_change_log->operator_name = $operatorName;
            $loan_collection_status_change_log->remark = "订单停催恢复，单ID：" . $this->loanCollectionOrder->id . "，操作人：" . $operatorName;
            if (!$loan_collection_status_change_log->save()) {
                throw new Exception("订单状态记录失败，单ID：" . $this->loanCollectionOrder->id);
            }
            return ['code'=>self::SUCCESS_CODE, 'message' => 'success'];
        }catch (Exception $e){
            return ['code'=>self::ERROR_CODE, 'message' => $e->getMessage()];
        }
    }

    /**
     * 订单的催收
     * @param LoanCollectionOrder $loanCollectionOrder
     * @param $params
     * @param $merchantIds
     * @return array
     */
    public function collect(LoanCollectionOrder $loanCollectionOrder,$params,$merchantIds){
        try {
            //
            $time = time();
            $this->loanCollectionOrder = $loanCollectionOrder;
            //参数
            $operateType = $params['operate_type'] ?? LoanCollectionRecord::OPERATE_TYPE_CALL;
            $remark = $params['remark'] ?? '';
            $cuishoutype = $params['cuishoutype'] ?? '';
            $sendNote = 1; //发送状态
            $riskControl = 0;
            $isConnect = 0;

            //效验
            $loan_person = LoanPerson::findById($this->loanCollectionOrder->user_id);
            if (!in_array($this->loanCollectionOrder->status, LoanCollectionOrder::$collection_status)) {
                throw new Exception("The order cannot be processed in this status，order ID：" . $this->loanCollectionOrder->id);
            }
            if(!in_array($operateType, array_keys(LoanCollectionRecord::$label_operate_type))){
                throw new Exception("Operation type error");
            }


            //操作类型
            if($operateType == LoanCollectionRecord::OPERATE_TYPE_CALL){
                if(!in_array($cuishoutype, ['csjl','txl','lxr'])){
                    throw new Exception("type error");
                }
                $riskControl = intval($params['risk_control']?? 0);
                $isConnect = intval($params['is_connect'] ?? 0);
                $promiseRepaymentTime = $params['promise_repayment_time'] ?? ''; //承诺还款的时间
                $content = '';
                $contactPhones = $params['contact_phone'] ?? [];  //电话催收提醒 传参
                //去重
                $contactPhones = array_unique($contactPhones);
                if(empty($contactPhones)){
                    throw new Exception("Please select the number to dial");
                }
                //如果沟通结果是 有偿还意愿 则更新
                $promise_repayment_time = 0;
                if($riskControl == LoanCollectionRecord::RISK_CONTROL_PROMISED_PAYMENT )
                {
                    if(empty($promiseRepaymentTime))
                    {
                        throw new Exception("Please fill in the promised repayment time！");
                    }
                    $promise_repayment_time = strtotime($promiseRepaymentTime);
                    $this->loanCollectionOrder->promise_repayment_time = $promise_repayment_time;
                    $this->loanCollectionOrder->before_status = $this->loanCollectionOrder->status;
                    $this->loanCollectionOrder->status = LoanCollectionOrder::STATUS_COLLECTION_PROMISE;
                    $this->loanCollectionOrder->is_purpose = 1;
                }elseif ($riskControl == LoanCollectionRecord::RISK_CONTROL_USER_PAYMENT){
                    $filesInfo = UploadedFile::getInstancesByName('fileList');
                    if(!isset($params['user_amount']) || !isset($params['user_utr']) || empty($filesInfo)){
                        throw new Exception("Please finish all label！");
                    }
                    $userAmount = $params['user_amount'] > 0 ? CommonHelper::UnitToCents($params['user_amount']) : 0;
                    $userUtr = $params['user_utr'] ?? 0;
                    if ($filesInfo) {
                        $fArr = [];
                        $service = new FileStorageService();
                        foreach ($filesInfo as $file){
                            $url = $service->uploadFile(
                                'india/saas_assist',
                                $file->tempName,
                                $file->getExtension());
                            if($file->size / (1024 * 1024) > 3){
                                throw new Exception("file too large！");
                            }
                            if(($s = $file->size / 1024) > 1024 && $s ){
                                $size = (sprintf('%0.2f',$s / 1024). 'Mb');
                            }else{
                                $size = (sprintf('%0.2f',$s). 'Kb');
                            }
                            $fs = ['name' => $file->name, 'size' => $size , 'url' => $url];
                            $fArr[] = $fs;
                        }
                        $userPic = $fArr;
                    }
                }else{
                    $this->loanCollectionOrder->is_purpose = 0;
                }
            }elseif($operateType == LoanCollectionRecord::OPERATE_TYPE_SMS){
                $templateId = intval($params['template_id'] ?? -1);
                $promise_repayment_time = 0;
                $contactPhones = [$loan_person->phone]; //只发生本人手机号
//                if(!empty($contactPhones['0']) && $contactPhones['0'] != $loan_person->phone)
//                {
//                    throw new Exception("The text message is only sent to the user himself! Can be unchecked!");
//                }
//                if(count($contactPhones) > 3){
//                    throw new Exception("The number of senders cannot be greater than 3!");
//                }
                $smsRecordCount = LoanCollectionRecord::find()
                    ->where(['order_id' => $loanCollectionOrder->id,'operate_type' => LoanCollectionRecord::OPERATE_TYPE_SMS])
                    ->andWhere(['>=','created_at',strtotime('today')])
                    ->andWhere(['<','created_at',strtotime('today') + 86400])
                    ->count();
                if($smsRecordCount >= 3){
                    throw new Exception("Send SMS today to reach 3 times");
                }
                $smsList = SmsTemplate::getTemplateList($loanCollectionOrder,$merchantIds,$templateId);
                if(!isset($smsList['content'][$templateId])){
                    throw new Exception("SMS template is invalid");
                }
                $content = $smsList['content'][$templateId];
                //联动世纪
                $smsLianDongConfigList = [
                    'bigshark' => 'smsService_Nxtele_GigShark',
                    'moneyclick' => 'smsService_Nxtele_MoneyClick_NOTICE',
                ];
                if(!isset($smsLianDongConfigList[$loanCollectionOrder->loanOrder->clientInfoLog->package_name])){
                    throw new Exception("fail : sms not exist");
                }
                $smsParamsName = $smsLianDongConfigList[$loanCollectionOrder->loanOrder->clientInfoLog->package_name];
                if(YII_ENV_PROD){
                    MessageHelper::sendAll($contactPhones,$content,$smsParamsName);//联动营销
                }
            }else{
                throw new Exception("operateType error！");
            }

            $this->loanCollectionOrder->last_collection_time = $time;
            if(!$this->loanCollectionOrder->save()){
                throw new Exception("server error！");
            }

            //通话对象信息
            $person_contact = [];
            //所选的是催收记录
            if ($cuishoutype == 'csjl') {
                $where = ['order_id' => $this->loanCollectionOrder->id, 'contact_phone' => $contactPhones];
                if($this->operatorRoleGroups == AdminUserRole::TYPE_COLLECTION){
                    $where['operator'] = $this->operator->id;
                }
                $loan_collection_record = LoanCollectionRecord::find()->where($where)->groupBy('contact_phone')->asArray()->all();
                foreach ($loan_collection_record as $value) {
                    $person_contact[$value['contact_phone']]['phone'] = $value['contact_phone'];
                    $person_contact[$value['contact_phone']]['contact_type'] = $value['contact_type'];
                    $person_contact[$value['contact_phone']]['name'] = $value['contact_name'];
                    $person_contact[$value['contact_phone']]['relation'] = $value['relation'];
                    $person_contact[$value['contact_phone']]['contact_id'] = $value['contact_id'];
                }
            }
            //获取通讯录
            if ($cuishoutype == 'txl') {
                $db = null;
                $personId = $loanCollectionOrder->user_id;
                if($loanCollectionOrder->loanOrder->is_export == UserLoanOrder::IS_EXPORT_YES){
                    $db = MgUserMobileContacts::getLoanDb();
                    /** @var UserLoanOrderExternal $externalOrder */
                    $externalOrder = UserLoanOrderExternal::find()->where(['order_uuid' => $loanCollectionOrder->loanOrder->order_uuid])->one();
                    $personId = $externalOrder->user_id;
                }
                $loan_mobile_contacts_list = MgUserMobileContacts::find()->where(['user_id'=>$personId])->asArray()->all($db);
                foreach ($contactPhones as $v) {
                    foreach ($loan_mobile_contacts_list as $value) {
                        if ($v == $value['mobile']) {
                            $person_contact[$v]['phone'] = $value['mobile'];
                            $person_contact[$v]['contact_type'] = LoanCollectionRecord::CONTACT_TYPE_ADDRESS_BOOK;
                            $person_contact[$v]['name'] = $value['name'];
                            $person_contact[$v]['relation'] = '';
                            $person_contact[$v]['contact_id'] = 1;
                            break;
                        }
                    }
                }
            }
            //获取联系人
            if ($cuishoutype == 'lxr') {
                /** @var  UserLoanOrder $userLoanOrder */
                $userLoanOrder = UserLoanOrder::find()->where(['id' => $this->loanCollectionOrder->user_loan_order_id])->one();
                foreach ($contactPhones as $phone) {
                    if(!empty($userLoanOrder->userContact->phone) && $phone == $userLoanOrder->userContact->phone){
                        $person_contact[$phone]['phone'] = $phone;
                        $person_contact[$phone]['contact_type'] = LoanCollectionRecord::CONTACT_TYPE_URGENT;
                        $person_contact[$phone]['name'] = $userLoanOrder->userContact->name;
                        $person_contact[$phone]['relation'] = Relative::$map[$userLoanOrder->userContact->relative_contact_person];
                    }
                    if(!empty($userLoanOrder->userContact->other_phone) && $phone == $userLoanOrder->userContact->other_phone){
                        $person_contact[$phone]['phone'] = $phone;
                        $person_contact[$phone]['contact_type'] = LoanCollectionRecord::CONTACT_TYPE_URGENT;
                        $person_contact[$phone]['name'] = $userLoanOrder->userContact->other_name;
                        $person_contact[$phone]['relation'] = Relative::$map[$userLoanOrder->userContact->other_relative_contact_person];
                    }
                    //人审第二手机号
                    $manualSecondMobile = ManualSecondMobile::find()->where(['order_id' => $userLoanOrder->id,'mobile' => $phone])->one();
                    if($manualSecondMobile){
                        $person_contact[$phone]['phone'] = $phone;
                        $person_contact[$phone]['contact_type'] = LoanCollectionRecord::CONTACT_TYPE_SELF;
                        $person_contact[$phone]['name'] = $loan_person->name.'(second mobile)';
                        $person_contact[$phone]['relation'] = 'Oneself';
                    }
                }
            }

            foreach($contactPhones as $key => $contact_phone){
                //新催收记录
                $record = new LoanCollectionRecord();
                $record->order_id = $this->loanCollectionOrder->id;//
                $record->operator = $this->operator->id;//
                $record->contact_type = isset($person_contact[$contact_phone]['contact_type'])? $person_contact[$contact_phone]['contact_type']: 0;
                $record->contact_name = isset($person_contact[$contact_phone]['name'])? $person_contact[$contact_phone]['name'] : $loan_person->name;
                $record->relation = isset($person_contact[$contact_phone]['relation']) ? $person_contact[$contact_phone]['relation']: 'Oneself';
                $record->contact_phone = isset($person_contact[$contact_phone]['phone']) ? $person_contact[$contact_phone]['phone']: $loan_person->phone;  //拿到了他 通过他找别的信息
                $record->order_level = $this->loanCollectionOrder->current_overdue_level;//
                $record->order_state = $this->loanCollectionOrder->status;//
                $record->operate_type = $operateType;//
                $record->content = $content ?? '';//
                $record->remark = $remark;//
                $record->operate_at = $time;//
                $record->send_note = $sendNote;
                $record->created_at = time();
                $record->updated_at = time();
                $record->promise_repayment_time = $promise_repayment_time;   //承诺还款时间
                $record->risk_control = $riskControl;   //沟通结果
                $record->is_connect = $isConnect;   //是否沟通
                $record->loan_user_id = $this->loanCollectionOrder->user_id;  //用户id
                $record->loan_order_id = $this->loanCollectionOrder->user_loan_order_id;  //借款订单id
                $record->merchant_id = $this->loanCollectionOrder->merchant_id;
                if(isset($userAmount)){
                    $record->user_amount = $userAmount;
                }
                if(isset($userUtr)){
                    $record->user_utr = $userUtr;
                }
                if(isset($userPic)){
                    $record->user_pic = $userPic;
                }
                $tag_save=$record->save();
                if(empty($tag_save))
                {
                    throw new Exception("Failed to save collection record！".json_encode($record->getErrors()));
                }

                RedisQueue::push([RedisQueue::PUSH_LOAN_COLLECTION_RECORD_DATA, $record->id]);
            }
            return ['code'=>self::SUCCESS_CODE, 'message' => 'success'];
        }catch (Exception $e){
            return ['code'=>self::ERROR_CODE, 'message' => $e->getMessage()];
        }
    }

    /**
     * 用户完成还款更新订单信息
     * @param int $repaymentId
     * @return bool
     */
    public function repaymentCompleteUpdate($repaymentId){
        try{
            /** @var LoanCollectionOrder $loanCollectionOrder */
            $loanCollectionOrder = LoanCollectionOrder::find()->where(['user_loan_order_repayment_id' => $repaymentId])->one();
            if(!$loanCollectionOrder){
                //借款订单还未进入
                return false;
            }
            if(!isset(LoanCollectionOrder::$level[$loanCollectionOrder->current_overdue_level])){
                throw new Exception('current_overdue_level is error');
            }
            if($loanCollectionOrder->status == LoanCollectionOrder::STATUS_COLLECTION_FINISH){
                throw new Exception('LoanCollectionOrder status already changed');
            }
            $beforeStatus = $loanCollectionOrder->status;
            $afterStatus = LoanCollectionOrder::STATUS_COLLECTION_FINISH;

            $loanCollectionOrder->updated_at = time();
            $loanCollectionOrder->before_status = $beforeStatus;
            $loanCollectionOrder->status = $afterStatus;
            $loanCollectionOrder->operator_name = 'system';
            if(!$loanCollectionOrder->save()){
                throw new Exception('LoanCollectionOrder save fail');
            }

            //状态流转换
            $type = LoanCollectionOrder::TYPE_LEVEL_FINISH;
            $loan_collection_status_change_log = new LoanCollectionStatusChangeLog();
            $loan_collection_status_change_log->loan_collection_order_id = $loanCollectionOrder->id;
            $loan_collection_status_change_log->before_status = $beforeStatus;
            $loan_collection_status_change_log->after_status = $afterStatus;
            $loan_collection_status_change_log->type = $type;
            $loan_collection_status_change_log->operator_name = 'system';
            $loan_collection_status_change_log->remark = "repaymentCompleteUpdate,".LoanCollectionOrder::$type[$type];
            $loan_collection_status_change_log->merchant_id = $loanCollectionOrder->merchant_id;
            $loan_collection_status_change_log->created_at = time();
            if(!$loan_collection_status_change_log->save()){
                throw new Exception('LoanCollectionStatusChangeLog save fail');
            }
            return true;
        }catch (Exception $e){
            yii::error("file:{$e->getFile()}, line:{$e->getLine()}, message:{$e->getMessage()}, trace:{$e->getTraceAsString()}");
            return false;
        }
    }


    /**
     * 触发器运行方法
     * @param $event
     */
    //loanCollectionOrder  dispatch_time 改变时触发器
    //添加  loanCollectionOrderAll
    public static function LoanCollectionOrderEventHandler($event){
        /** @var LoanCollectionOrder $loanCollectionOrder */
        $loanCollectionOrder = $event->sender;
        /** @var LoanCollectionOrderAll $loanCollectionOrderAll */
        switch ($event->name) {
            //分派时，添加LoanCollectionOrderAll 永久保存订单，更新该订单上次记录状态
            case LoanCollectionOrder::EVENT_DISPATCH_TO_COLLECTION:
                LoanCollectionOrderAll::updateAll(
                    [
                        'status' => LoanCollectionOrderAll::REDEPLOY_YES
                    ],
                    [
                        'AND',
                        ['user_loan_order_repayment_id' => $loanCollectionOrder->user_loan_order_repayment_id],
                        ['status' => LoanCollectionOrderAll::REDEPLOY_NO],
                    ]
                );

                $loan_collection_order_all = new LoanCollectionOrderAll();
                $loan_collection_order_all->user_id = $loanCollectionOrder->user_id;  //用户id
                $loan_collection_order_all->loan_collection_order_id = $loanCollectionOrder->id;  //催收表id
                $loan_collection_order_all->user_loan_order_repayment_id = $loanCollectionOrder->user_loan_order_repayment_id; //还款表id
                $loan_collection_order_all->dispatch_time = $loanCollectionOrder->dispatch_time;  //派单时间
                $loan_collection_order_all->current_collection_admin_user_id = $loanCollectionOrder->current_collection_admin_user_id; //催收人id
                $loan_collection_order_all->outside_id = $loanCollectionOrder->outside;                   //催收机构id
                $loan_collection_order_all->current_overdue_level = $loanCollectionOrder->current_overdue_level; //订单等级
                $loan_collection_order_all->current_overdue_group = $loanCollectionOrder->current_overdue_group;  //催收分组
                $loan_collection_order_all->created_at = time();
                $loan_collection_order_all->save();
                break;
            case LoanCollectionOrder::EVENT_LAST_COLLECTION_TIME_CHANGE:
                //催记触发
                $loanCollectionOrderAll = LoanCollectionOrderAll::find()
                    ->where([
                        'loan_collection_order_id'=>$loanCollectionOrder->id,
                        'status'=>LoanCollectionOrderAll::REDEPLOY_NO
                    ])->one();
                if($loanCollectionOrderAll)
                {
                    $loanCollectionOrderAll->last_collection_time = time();
                    $loanCollectionOrderAll->save();
                }
                break;
            case LoanCollectionOrder::EVENT_TO_WAIT_COLLECTION:
                //回收触发
                $today = strtotime('today');
                LoanCollectionOrderAll::updateAll(
                    [
                        'that_day_status' => LoanCollectionOrderAll::THAT_DAY_STATUS_RETURN
                    ],
                    [
                        'AND',
                        ['user_loan_order_repayment_id' => $loanCollectionOrder->user_loan_order_repayment_id],
                        ['that_day_status' => LoanCollectionOrderAll::THAT_DAY_STATUS_IN_HANDS],
                        ['>=','created_at',$today],
                        ['<','created_at',$today + 86400]
                    ]
                );
                break;
            default:
                break;

        }
    }

}
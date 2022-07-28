<?php

namespace console\controllers;

use backend\models\Merchant;
use callcenter\models\AbsenceApply;
use callcenter\models\AdminUser;
use callcenter\models\AdminUserMasterSlaverRelation;
use callcenter\models\AdminUserRole;
use callcenter\models\CollectionCallRecords;
use callcenter\models\CollectionCheckinLog;
use callcenter\models\CollectionOrderDispatchLog;
use callcenter\models\CollectorAttendanceDayData;
use callcenter\models\CollectorBackMoney;
use callcenter\models\CollectorClassSchedule;
use callcenter\models\DispatchOutsideFinish;
use callcenter\models\DispatchOverdueDaysFinish;
use callcenter\models\InputOverdayOut;
use callcenter\models\InputOverdayOutAmount;
use callcenter\models\LevelChangeDailyCall;
use callcenter\models\loan_collection\LoanCollectionOrder;
use callcenter\models\loan_collection\LoanCollectionOrderAll;
use callcenter\models\loan_collection\LoanCollectionRecord;
use callcenter\models\loan_collection\LoanCollectionStatusChangeLog;
use callcenter\models\loan_collection\StopRegainInputOrder;
use callcenter\models\loan_collection\UserCompany;
use callcenter\models\loan_collection\UserSchedule;
use callcenter\models\LoanCollectionDayStatistics;
use callcenter\models\LoanCollectionStatisticNew;
use callcenter\models\LoanCollectionStatistics;
use callcenter\models\LoanCollectionTrackStatistic;
use callcenter\models\order_statistics\OrderStatisticsByDay;
use callcenter\models\order_statistics\OrderStatisticsByGroup;
use callcenter\models\order_statistics\OrderStatisticsByRate;
use callcenter\models\order_statistics\OrderStatisticsByStatus;
use callcenter\models\OrderStatistics;
use callcenter\models\OutsideDayData;
use callcenter\models\ScriptTaskLog;
use callcenter\service\DispatchService;
use callcenter\service\LoanCollectionService;
use common\helpers\MessageHelper;
use common\helpers\RedisQueue;
use common\helpers\Util;
use common\models\order\UserLoanOrder;
use common\models\order\UserLoanOrderExtendLog;
use common\models\order\UserLoanOrderExtraRelation;
use common\models\order\UserLoanOrderRepayment;
use common\models\order\UserRepaymentLog;
use common\models\package\PackageSetting;
use common\models\user\LoanPerson;
use common\models\user\UserActiveTime;
use common\models\user\UserContact;
use common\services\collection_stats\InputOverdueOutService;
use common\services\export\BackendReportService;
use common\services\message\WeWorkService;
use Yii;
use yii\base\Exception;
use yii\console\ExitCode;
use yii\helpers\ArrayHelper;

class CollectionController extends BaseController{

    /**
     * 停催重新入催收
     */
    public function actionCollectionStopRegainInput() {
        $this->printMessage( "开始执行");
        $errorArr = [];
        $stopRegainInputOrders = StopRegainInputOrder::find()
            ->where(['status' => StopRegainInputOrder::STATUS_INVALID])
            ->andWhere(['>','next_input_time',0])
            ->andWhere(['<=','next_input_time',time()])
            ->asArray()
            ->all();
        foreach ($stopRegainInputOrders as $item){
            $collectionOrderId = $item['collection_order_id'];
            $stopRegainInputOrderId = $item['id'];
            $loanCollectionOrder = LoanCollectionOrder::findOne($collectionOrderId);
            if($loanCollectionOrder->status == LoanCollectionOrder::STATUS_COLLECTION_FINISH){
                //如果已完成则更新状态，且不进行回收
                $stopRegainInputOrder = StopRegainInputOrder::findOne($stopRegainInputOrderId);
                $stopRegainInputOrder->status = StopRegainInputOrder::STATUS_UNAVAILABLE;
                $stopRegainInputOrder->save();
            }else{
                $loanCollectionService = new LoanCollectionService();
                $res = $loanCollectionService->collectionRecovery($loanCollectionOrder);
                if($res['code'] == LoanCollectionService::SUCCESS_CODE){
                    $this->printMessage( "催收订单ID".$loanCollectionOrder->id.',停催到期，回收，待分派');
                }else{
                    $errorArr[] = $loanCollectionOrder->id;
                    $this->printMessage( "催收订单ID".$loanCollectionOrder->id.',停催到期回收失败:'.$res['message']);
                }
            }
        }
        if($errorArr && YII_ENV_PROD){
            $errorIdStr = implode(',',$errorArr);
            $service = new WeWorkService();
            $service->sendText(['wangpeng'],'催收订单ID:'.$errorIdStr.',停催到期回收失败');
        }
        $this->printMessage( "执行完毕");
    }

    /**
     * 更新催收订单的用户最后访问时间
     */
    public function actionUpdateUserLastAccessTime()
    {
        if (!$this->lock())
        {
            return ExitCode::UNSPECIFIED_ERROR;
        }
        $startTime = time();
        while (true) {
            if (time() - $startTime > 300) {
                $this->printMessage('运行满5分钟，关闭当前脚本');
                exit;
            }
            $userId = RedisQueue::pop([RedisQueue::PUSH_COLLECTION_LAST_ACCESS_USER]);
            if(!$userId)
            {
                sleep(2);
                continue;
            }
            $this->printMessage('处理用户id：'.$userId);

            //更新大盘活跃记录
            $activeModel = UserActiveTime::find()->where(['user_id' => $userId])->one();
            if(is_null($activeModel))
            {
                $activeModel = new UserActiveTime();
            }
            $activeModel->user_id = $userId;
            $activeModel->last_active_time = time();
            $activeModel->save();
        }
    }

    /**
     * 更新大盘用户最后放款时间
     */
    public function actionUpdateUserLastLoanTime()
    {
        if (!$this->lock())
        {
            return ExitCode::UNSPECIFIED_ERROR;
        }
        $db = \Yii::$app->db_loan;
        $startTime = time();
        while (true) {
            if (time() - $startTime > 300) {
                $this->printMessage('运行满5分钟，关闭当前脚本');
                exit;
            }
            $data = RedisQueue::pop([RedisQueue::PUSH_PAN_CODE_LAST_LOAN_TIME_DATA]);
            if(!$data)
            {
                $this->printMessage('没有可处理的数据，继续等待');
                sleep(2);
                continue;
            }

            $data = json_decode($data, true);

            $this->printMessage('处理pan_code：'.$data['pan_code']);
            //同步到催收中心
            RedisQueue::push([RedisQueue::PUSH_PAN_CODE_LAST_LOAN_TIME_DATA, json_encode(['pan_code' => $data['pan_code'],'loan_time' => $data['loan_time']])],'redis_assist_center');

            $userIds = array_column(LoanPerson::find()->select(['id'])->where(['pan_code' => $data['pan_code']])->asArray()->all(Yii::$app->db_loan),'id');

            if($userIds){
                $userIdStr = implode(',',$userIds);
                $sql = "UPDATE `tb_user_active_time` SET `last_loan_time` = {$data['loan_time']} WHERE `user_id` in ({$userIdStr})";
                $result = $db->createCommand($sql)->execute();
                var_dump($result);
            }
        }
    }

    /**
     * 账龄更新发送短信
     */
    public function actionLevelChangeSend() {
        $query = LevelChangeDailyCall::find()
            ->select(['id','loan_order_id','user_id'])
            ->where(['send_status' => LevelChangeDailyCall::SEND_STATUS_DEFAULT])
            ->limit(5000)
            ->orderBy(['id' => SORT_ASC]);
        $maxId = 0;
        $levelChangeDailyCall = $query->andWhere(['> ','id',$maxId])->asArray()->all();
        while ($levelChangeDailyCall){
            //获取对应包
            $userIds = [];
            foreach ($levelChangeDailyCall as $item){
                $userIds[] = $item['user_id'];
            }

            foreach ($levelChangeDailyCall as $item){
                $maxId = $item['id'];

                /** @var LevelChangeDailyCall $re */
                $re = LevelChangeDailyCall::find()
                    ->where(['id'=> $maxId,'send_status' => LevelChangeDailyCall::SEND_STATUS_DEFAULT])->one();

                $voice_url = 'https://nxcloudhk.oss-cn-hongkong.aliyuncs.com/voice_group/1592981998328.ogg';
                $result = MessageHelper::sendAll($re->user_phone,$voice_url,'smsService_NxVoiceGroup_MoneyClick');
                if($result[0]){
                    $re->send_status = LevelChangeDailyCall::SEND_STATUS_SENDING;
                    if($result[0]['messageid']){
                        $re->send_id = $result[0]['messageid'];
                    }
                    $re->save();
                }
            }
            $levelChangeDailyCall = $query->andWhere(['> ','id',$maxId])->asArray()->all();
        }
    }


    /**
     * 账龄更新发送短信结果查询
     */
    public function actionLevelChangeSendQuery() {
        if (!$this->lock())
        {
            return ExitCode::UNSPECIFIED_ERROR;
        }
        $query = LevelChangeDailyCall::find()
            ->select(['id','send_id','user_id'])
            ->where(['send_status' => LevelChangeDailyCall::SEND_STATUS_SENDING])
            ->limit(5000)
            ->orderBy(['id' => SORT_ASC]);
        $maxId = 0;
        $levelChangeDailyCall = $query->andWhere(['> ','id',$maxId])->asArray()->all();
        while ($levelChangeDailyCall){
            foreach ($levelChangeDailyCall as $item){
                $maxId = $item['id'];
                $sendId = $item['send_id'];
                $userId = $item['user_id'];

                /** @var LevelChangeDailyCall $re */
                $re = LevelChangeDailyCall::find()
                    ->where(['id'=> $maxId,'send_status' => LevelChangeDailyCall::SEND_STATUS_SENDING])->one();
                if($re){
                    //注： 牛信参数start_time，end_time 为北京时间
                    $startTime = date('Y-m-d H:i:s',$re->created_at + 9000);
                    $endTime = date('Y-m-d H:i:s',$re->created_at + 86400 + 9000);
                    $res = MessageHelper::querySendResult('smsService_NxVoiceGroup_MoneyClick',$sendId,[
                        'start_time' => $startTime,
                        'end_time' => $endTime,
                        'page_size' => 10,
                        'page' => 1
                    ]);
                    if(isset($res['info']['rows'][0])){
                        if($res['info']['rows'][0]['second'] > 0){
                            $re->send_status = LevelChangeDailyCall::SEND_STATUS_SUCCESS;
                            $re->remark = $res['info']['rows'][0]['result'] ?? '查询成功';
                            $level_change_call_success_time = strtotime($res['info']['rows'][0]['time']) - 3600 * 2.5;

                            /** @var UserActiveTime $userActiveTime */
                            $userActiveTime = UserActiveTime::find()->where(['user_id' => $userId])->one();
                            if($userActiveTime){
                                $userActiveTime->level_change_call_success_time = $level_change_call_success_time;
                                $userActiveTime->save();
                            }
                        }else{
                            $re->send_status = LevelChangeDailyCall::SEND_STATUS_FAIL;
                            $re->remark = $res['info']['rows'][0]['result'] ?? '查询失败';
                            /** @var UserActiveTime $userActiveTime */
                            $userActiveTime = UserActiveTime::find()->where(['user_id' => $userId])->one();
                            if($userActiveTime){
                                $userActiveTime->level_change_call_success_time = 0;
                                $userActiveTime->save();
                            }
                        }
                        $re->save();
                    }
                }
            }
            $levelChangeDailyCall = $query->andWhere(['> ','id',$maxId])->asArray()->all();
        }
    }

    /**
     * 入催、更新逾期等级、回收订单
     */
    public function actionUpdateOverdueLevel($process = 1) {
        while ($repayment = RedisQueue::pop([RedisQueue::COLLECTION_RESET_OVERDUE_LIST])){
            if(!empty($repayment)){
                $repayment = json_decode($repayment,true);
                $repayment_id = $repayment['repayment_id'];
                $level = $repayment['level'];
                $this->printMessage( "开始执行，还款订单号:{$repayment_id},逾期等级{$level}");
                /**
                 * @var UserLoanOrderRepayment $repayment_order
                 */
                $repayment_order = UserLoanOrderRepayment::find()
                    ->where(['id'=>$repayment_id])->limit(1)->one();
                if( !empty($repayment_order)){
                    $overdueDays = $repayment_order->overdue_day;
                    if(UserLoanOrderRepayment::STATUS_REPAY_COMPLETE == $repayment_order->status)
                    {
                        $this->printMessage("还款订单号:{$repayment_id},改订单已还款，跳过处理");
                        continue;
                    }
                    /** @var  LoanCollectionOrder $item */
                    $item = LoanCollectionOrder::findOne(['user_loan_order_repayment_id'=>$repayment_id]);
                    /** @var  \yii\db\Connection $connection */
                    $connection = Yii::$app->db_assist;
                    $transaction= $connection->beginTransaction();//创建事务

                    try{
                        //如果催收订单为空，则创建催收订单表
                        if(is_null($item))
                        {
                            $loanPerson = LoanPerson::findOne($repayment_order->user_id);

                            $item = new LoanCollectionOrder();
                            $item->user_id = $repayment_order->user_id;
                            $item->user_loan_order_id = $repayment_order->order_id;
                            $item->user_loan_order_repayment_id = $repayment_order->id;
                            $item->dispatch_name = "";
                            $item->dispatch_time = 0;
                            $item->current_collection_admin_user_id = 0;
                            $item->current_overdue_level = $level;
                            $item->status = LoanCollectionOrder::STATUS_WAIT_COLLECTION;
                            $item->promise_repayment_time = 0;
                            $item->last_collection_time = 0;
                            $item->next_loan_advice = 0;
                            $item->operator_name = "auto shell";
                            $item->remark = "auto input";
                            $item->current_overdue_group = 0;
                            $item->merchant_id = $repayment_order->merchant_id;
                            $item->customer_type = $loanPerson->customer_type;
                            if(!$item->save())
                            {
                                throw new Exception("还款订单ID:{$repayment_id},逾期等级{$level},催收订单创建失败");
                            }
                            $before_status = LoanCollectionOrder::STATUS_WAIT_COLLECTION;
                            $after_status = LoanCollectionOrder::STATUS_WAIT_COLLECTION;
                            $type = LoanCollectionOrder::TYPE_INPUT_COLLECTION;
                            $remark = "auto input";

                        }elseif(!empty($item) && !in_array($item->status,LoanCollectionOrder::$end_status))
                        {
                            $old_level = $item->current_overdue_level;//旧的等级
//                            if($old_level > $level){
//                                throw new Exception("更新订单等级时遇到异常订单：collection ID：".$item->id."，借款订单ID：".$item->user_loan_order_id.",原等级：".$old_level.",新等级：".$level);
//                            }

                            if($old_level == $level)
                            {
                                throw new Exception("还款订单ID:{$repayment_id},逾期等级{$level},与历史数据一致，跳过处理");
                            }else{
                                $type = LoanCollectionOrder::TYPE_LEVEL_CHANGE;
                                $item->current_overdue_level = $level;//新的等级
                            }
                            $before_status = $item->status;
                            $after_status = LoanCollectionOrder::STATUS_WAIT_COLLECTION;

                            $remark = "order level update and back";
                            $item->outside = 0;
                            $item->current_overdue_group = 0;
                            $item->current_collection_admin_user_id = 0;
                            $item->status = $after_status;
                            $item->merchant_id = $repayment_order->merchant_id;
                            if(!$item->save()){
                                throw new Exception("还款订单ID:{$repayment_id},逾期等级{$level},催收订单表更新失败");
                            }
                        }elseif(!empty($item) && $item->status == LoanCollectionOrder::STATUS_DELAY_STOP_URGING)
                        {
                            $item->current_overdue_level = $level;//新的等级
                            $before_status = $item->status;
                            if($repayment_order->is_delay_repayment == UserLoanOrderRepayment::IS_DELAY_YES){
                                //延期中未到期 ,只更新订单账龄
                                $type = LoanCollectionOrder::TYPE_LEVEL_CHANGE;
                                $after_status = $item->status;
                                $remark = "order level update";
                                if(!$item->save()){
                                    throw new Exception("还款订单ID:{$repayment_id},逾期等级{$level},催收订单表更新失败");
                                }
                            }else{
                                $type = LoanCollectionOrder::TYPE_DELAY_STOP_URGING_RECOVERY;
                                $after_status = LoanCollectionOrder::STATUS_WAIT_COLLECTION;
                                $remark = "delay recovery";
                                $item->outside = 0;
                                $item->current_overdue_group = 0;
                                $item->current_collection_admin_user_id = 0;
                                $item->status = $after_status;
                                if(!$item->save()){
                                    throw new Exception("还款订单ID:{$repayment_id},逾期等级{$level},催收订单表更新失败");
                                }
                            }
                        }elseif(!empty($item) && $item->status == LoanCollectionOrder::STATUS_STOP_URGING) {
                            $item->current_overdue_level = $level;//新的等级
                            $before_status = $item->status;
                            $after_status = $item->status;
                            //只更新订单账龄
                            $type = LoanCollectionOrder::TYPE_LEVEL_CHANGE;
                            $remark = "only order level update";
                            if(!$item->save()){
                                throw new Exception("还款订单ID:{$repayment_id},逾期等级{$level},催收订单表更新失败");
                            }

                        }else{
                            throw new Exception("还款订单ID:{$repayment_id},逾期等级{$level},未命中操作区间，跳过处理");
                        }

                        //仅moneyclick
                        if($item->merchant_id = 2 &&
                            $type == LoanCollectionOrder::TYPE_LEVEL_CHANGE &&
                            $level >= LoanCollectionOrder::LEVEL_M1
                        ) {
                            $levelChangeDailyCall = new LevelChangeDailyCall();
                            $levelChangeDailyCall->collection_order_id = $item->id;
                            $levelChangeDailyCall->loan_order_id = $item->user_loan_order_id;
                            $levelChangeDailyCall->repayment_id = $item->user_loan_order_repayment_id;
                            $levelChangeDailyCall->over_level = $item->current_overdue_level;
                            $levelChangeDailyCall->user_id = $item->user_id;
                            $levelChangeDailyCall->user_phone = $repayment_order->loanPerson->phone;
                            $levelChangeDailyCall->save();
                        }

                        //状态流转换
                        $loan_collection_status_change_log = new LoanCollectionStatusChangeLog();
                        $loan_collection_status_change_log->loan_collection_order_id = $item->id;
                        $loan_collection_status_change_log->before_status = $before_status;
                        $loan_collection_status_change_log->after_status = $after_status;
                        $loan_collection_status_change_log->type = $type;
                        $loan_collection_status_change_log->operator_name = "auto shell";
                        $loan_collection_status_change_log->remark = $remark;
                        $loan_collection_status_change_log->merchant_id = $repayment_order->merchant_id;
                        if (!$loan_collection_status_change_log->save()) {
                            throw new Exception("还款订单ID:{$repayment_id},逾期等级{$level},状态流转表保存失败");
                        }
                        $transaction->commit();
                        $this->printMessage("还款订单ID:{$repayment_id},逾期等级{$level},订单处理成功");
                    }catch (\Exception $exception)
                    {
                        $this->printMessage("处理异常,文件：{$exception->getFile()},行号：{$exception->getLine()},异常信息：{$exception->getMessage()}");
                        $transaction->rollBack();
                    }
                }
            }
        }
    }

    /**
     * @name CollectionController 自动分派
     * @params int $merchantId
     * @params int $outside
     * @return
     */
    public function actionAutoDispatchToOperator() {
        $collectorRoles = AdminUserRole::getRolesByGroup(AdminUserRole::TYPE_COLLECTION);
        if(empty($collectorRoles)){
            $this->printMessage('没有可分派角色');
            return ExitCode::UNSPECIFIED_ERROR;
        }

        Util::cliLimitChange(1024);
        $html = '';
        $merchantList = Merchant::getMerchantId(false);
        foreach ($merchantList as $merchantId => $merchantName){
            $this->printMessage('start merchant'.$merchantId);
            $userCompany = UserCompany::autoDispatchListByMerchant([0,$merchantId]);
            if(empty($userCompany)){
                $this->printMessage('无设置分派公司.结束');
                continue;
            }
            $outsides = array_column($userCompany,'id');
            $dispatchSuccessData = [];
            $dispatchFailData = [];
            $loanCollectionService = new LoanCollectionService();
            $levelArr = LoanCollectionOrder::$current_level;
            if(!LoanCollectionOrder::lockCollectionDispatchMerchant($merchantId,600)){
                $this->printMessage('有分派在进行中.结束');
                continue;
            }



            foreach ($levelArr as $orderLevel => $val){
                $this->printMessage($val);
                $query = LoanCollectionOrder::find()
                    ->alias('co')
                    ->select(['co.id','co.outside'])
                    ->leftJoin(UserLoanOrderRepayment::getDbName().'.'.UserLoanOrderRepayment::tableName() . ' ry', 'co.user_loan_order_repayment_id = ry.id')
                    ->where([
                        'co.merchant_id' => $merchantId,
                        'co.current_overdue_level'=> $orderLevel,
                        'co.outside' =>  0,
                        'co.status'=> LoanCollectionOrder::STATUS_WAIT_COLLECTION
                    ]);

                $userQuery = AdminUser::find()
                    ->alias('user')
                    ->select(['user.id'])
                    ->leftJoin(CollectorClassSchedule::tableName().' schedule','schedule.admin_id = user.id AND schedule.date = "'.date('Y-m-d').'"')
                    ->where([
                        'user.role' => $collectorRoles,
                        'user.merchant_id' => [0,$merchantId],
                        'user.open_status' => AdminUser::$usable_status,
                        'user.can_dispatch' => AdminUser::CAN_DISPATCH,
                        'user.group' => $orderLevel,
                        'user.outside' => $outsides
                    ])
                    ->andWhere("FIND_IN_SET({$merchantId}, to_view_merchant_id) or FIND_IN_SET(0, to_view_merchant_id)")
                    ->andWhere(['OR',['IS','schedule.id',NULL],['schedule.status' => CollectorClassSchedule::STATUS_DEL]]);

                $adminUsers = $userQuery->asArray()->all();

                if(empty($adminUsers)){
                    //无分派人
                    $this->printMessage('无可分派催收员');
                    continue;
                }


                $adminIds = array_column($adminUsers, 'id');
                $currentAdminIds = $adminIds;
                if(floor(5000 / count($adminIds)) > 0){
                    $limit = floor(5000 / count($adminIds)) * count($adminIds);
                }else{
                    $limit = count($adminIds);
                }

                $this->printMessage('limit:'.$limit);
                $loanCollectionOrder = $query->orderBy(['ry.overdue_day' => SORT_ASC])->limit($limit)->asArray()->all();
                while ($loanCollectionOrder){
                    foreach ($loanCollectionOrder as $item){
                        $cid = $item['id'];
                        if(empty($currentAdminIds)){
                            $currentAdminIds = $adminIds;
                        }
                        //随机分配
                        $r_key = array_rand($currentAdminIds);
                        $operatorId = $currentAdminIds[$r_key];
                        unset($currentAdminIds[$r_key]);

                        $res = $loanCollectionService->dispatchToOperator($cid,$operatorId);
                        //$res['code'] = 0;
                        if($res['code'] != LoanCollectionService::SUCCESS_CODE){
                            if(count($dispatchFailData) < 10){
                                $dispatchFailData[$cid.'_'.$operatorId] = $res['message'];
                            }
                        }else{
                            if(isset($dispatchSuccessData[$loanCollectionService->operated->outside][$loanCollectionService->operated->group][$operatorId])){
                                $dispatchSuccessData[$loanCollectionService->operated->outside][$loanCollectionService->operated->group][$operatorId] += 1;
                            }else{
                                $dispatchSuccessData[$loanCollectionService->operated->outside][$loanCollectionService->operated->group][$operatorId] = 1;
                            }
                        }
                        //echo $maxId.'-'.$operatorId.PHP_EOL;


                    }
                    $loanCollectionOrder = $query->orderBy(['ry.overdue_day' => SORT_ASC])->limit($limit)->asArray()->all();
                }
            }
            //释放分派锁
            LoanCollectionOrder::releaseCollectionDispatchMerchantLock($merchantId);
            $html .= "
<div style='text-align:center; '><b><font size='5'>".date('Y-m-d')."商户".$merchantName."分派情况</font></b></div>
<table cellpadding='0' cellspacing='2' border='1' style='width:800px;text-align:center;font:12px arial;color:#000000;margin:0 auto;'>
<thead><th>机构</th><th>分组</th><th>分组人数</th><th>派单量</th><th>人均单量</th></thead>
<tbody>";
            foreach ($dispatchSuccessData as $outside => $groupData){
                $company = UserCompany::findOne($outside);
                foreach ($groupData as $group => $operatorData){
                    $totalOrderNum = 0;
                    $totalOperatorNum = 0;
                    foreach ($operatorData as $operatorId => $num){
                        $totalOrderNum += $num;
                        $totalOperatorNum += 1;
                    }
                    $html .= "<tr><td>{$company->real_title}</td><td>{$levelArr[$group]}</td><td>{$totalOperatorNum}</td><td>{$totalOrderNum}</td><td>". (empty($totalOperatorNum) ? 0 : round($totalOrderNum/$totalOperatorNum, 2))."</td></tr>";
                }
            }
            $html .="
</tbody>
</table>
<div style='text-align:center; '><b><font size='5'>分派失败信息</font></b></div>
<table cellpadding='0' cellspacing='2' border='1' style='width:800px;text-align:center;font:12px arial;color:#000000;margin:0 auto;'>
<thead><th>订单-催收员</th><th>原因</th></thead>
<tbody>";
            foreach ($dispatchFailData as $key => $message){
                $html .= "<tr><td>{$key}</td><td>{$message}</td></tr>";
            }
            $html .="
</tbody>
</table>";
        }

        $to = "978010084@qq.com";
        if(YII_ENV_PROD){
            $to = "yanzhenlin@vedatlas.com";
        }
        $subject = "早上好啊!(saas)";
        $mailer = Yii::$app->mailer->compose();
        $mailer->setTo($to);
        $mailer->setSubject($subject);
        $mailer->setHtmlBody($html);
        $status = $mailer->send();
        var_dump($status) ;
        $this->printMessage('end');
    }

    /**
     * @return int
     * @throws \Exception
     */
    public function actionManualDispatchScriptTask() {
        if (!$this->lock()) {
            $this->printMessage('ManualDispatchScriptTask 已经运行中,关闭脚本');
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $task = ScriptTaskLog::find()
            ->where([
                'exec_status' => ScriptTaskLog::STATUS_INIT,
                'script_type' => ScriptTaskLog::SCRIPT_TYPE_DISPATCH,
            ])
            ->orderBy('id ASC')
            ->limit(1)
            ->one();
        if (empty($task)) {
            $this->printMessage('ManualDispatchScriptTask 没有任务,关闭脚本');
            return ExitCode::UNSPECIFIED_ERROR;
        }

        /**
         * @var ScriptTaskLog $task
         */
        $task->exec_start_time = time();
        $task->exec_status = ScriptTaskLog::STATUS_EXECUTING;
        $task->save();
        try {
            $this->actionAutoDispatchToOperator();
        } catch (\Exception $exception) {
            $task->exec_status = ScriptTaskLog::STATUS_ERROR;
            $task->exec_end_time = time();
            $task->save();
            throw $exception;
        }

        $task->exec_status = ScriptTaskLog::STATUS_COMPLETE;
        $task->exec_end_time = time();
        $task->save();

        return ExitCode::OK;
    }

    /**
     *提供贷款建议
     *条件：已催收成功，已还款，无贷款建议
     *建议通过：逾期天数 <= 5  且  爽约次数 <= 2
     *建议审核：6 =< 逾期天数 <= 10   且  爽约次数 <= 2
     *建议拒绝：逾期天数 >= 11  或  爽约次数 >= 3 ;  建议拒绝（爽约次数 = 承诺还款次数-1）
     */
    public function actionAutoSuggestion(){

        $loanOrders = LoanCollectionOrder::noSuggest();
        $i = 1;
        $amount = count($loanOrders);
        foreach ($loanOrders as $key => $order) {
            echo '贷款建议进度：'.$i++."/".$amount."\r\n";
            $repayOrder = UserLoanOrderRepayment::findOne($order['user_loan_order_repayment_id']);
            if($repayOrder['overdue_day'] >= 11){
                //建议拒绝
                echo '建议拒绝(逾期超过10天), 借款ID：'.$order['user_loan_order_id'].", 催收ID：".$order['id']."\r\n";
                LoanCollectionOrder::updateNextLoanAdvice($order['id'], LoanCollectionOrder::RENEW_REJECT, '逾期超过10天');
                continue;
            }

            $promiseRecords = LoanCollectionRecord::promiseTimeCollectionOrderId($order['id']);
            $breakAmount = 0;//爽约次数
            $rank = array();
            if(!empty($promiseRecords)){
                foreach ($promiseRecords as $key => $promiseTime) {
                    $date = date("Y-m-d", $promiseTime);
                    if(in_array($date, $rank))    continue;
                    if($date < date("Y-m-d", $repayOrder['true_repayment_time']))    $breakAmount++;//同一天，最多算一次‘承诺还款’
                    $rank[] = $date;
                }
            }
            if($breakAmount >= 3){
                //建议拒绝
                echo '建议拒绝(违约次数超过2次), 借款ID：'.$order['user_loan_order_id'].", 催收ID：".$order['id']."\r\n";
                LoanCollectionOrder::updateNextLoanAdvice($order['id'], LoanCollectionOrder::RENEW_REJECT, '违约次数超过2次');
                continue;

            }

            if($repayOrder['overdue_day'] >=6 && $repayOrder['overdue_day'] <= 10 && $breakAmount <=2 ){
                //建议审核
                echo '建议审核(逾期天数在6~10天， 违约次数未超过2次), 借款ID：'.$order['user_loan_order_id'].", 催收ID：".$order['id']."\r\n";
                LoanCollectionOrder::updateNextLoanAdvice($order['id'], LoanCollectionOrder::RENEW_CHECK, '逾期天数在6~10天， 违约次数未超过2次');
                continue;
            }

            if($repayOrder['overdue_day'] <= 5 && $breakAmount <=2){
                //建议通过
                echo '建议通过(逾期天数未超过5天，违约次数未超过2次), 借款ID：'.$order['user_loan_order_id'].", 催收ID：".$order['id']."\r\n";
                LoanCollectionOrder::updateNextLoanAdvice($order['id'], LoanCollectionOrder::RENEW_PASS, '逾期天数未超过5天，违约次数未超过2次');
                continue;
            }
        }
    }


    /**
     * @name CollectionController 催收员每日统计脚本
     */
    public function actionAllAdminStatistic(){
        if(!$this->lock()){
            return;
        }
        ini_set('max_execution_time', '0');
        ini_set('memory_limit', '1024M');
        echo '开始'.PHP_EOL;
        $maxId = 0;

        $s1Time = max(strtotime(date("Y-m-d",strtotime("-180 day"))),strtotime("2020-07-20"));
        $query = LoanCollectionOrder::find()
            ->select([
                'id',
                'current_collection_admin_user_id',
                'user_loan_order_repayment_id',
                'current_overdue_group',
                'last_dispatch_time',
                'last_collection_time'
            ])
            ->where(['>','current_collection_admin_user_id',0])
            ->andWhere([
                'OR',
                [
                    'AND',
                    ['>','last_dispatch_time',strtotime(date("Y-m-d",strtotime("-180 day")))],
                    ['NOT IN','current_overdue_group',[LoanCollectionOrder::LEVEL_S1_1_3DAY,LoanCollectionOrder::LEVEL_S1_4_7DAY]]
                ],
                [
                    'AND',
                    ['>','last_dispatch_time',$s1Time],
                    ['IN','current_overdue_group',[LoanCollectionOrder::LEVEL_S1_1_3DAY,LoanCollectionOrder::LEVEL_S1_4_7DAY]]
                ]
            ]);
        $cloneQuery = clone $query;
        $loanCollectionOrder = $cloneQuery->andWhere(['>','id',$maxId])
            ->limit(1000)->orderBy(['id'=>SORT_ASC])
            ->asArray()
            ->all(LoanCollectionOrder::getDb_rd());

        if ($loanCollectionOrder) {
            $data = [];
            $loanAdminUsers = [];
            //找出所有的催收人员表中的催收人信息
            $adminUsers = AdminUser::find()->where(['open_status' => AdminUser::$usable_status])
                ->select(['id','outside','group','username','real_name','merchant_id'])
                ->orderBy(['id'=>SORT_ASC])
                ->asArray()
                ->all(AdminUser::getDb_rd());
            foreach ($adminUsers as $value) {
                $loanAdminUsers[$value['id']] = $value;
            }
            $todaytime = strtotime('today');
            $towtime = $todaytime + 86400;
            $adminRepaymentIds = [];
            while ($loanCollectionOrder) {
                $currRayment = [];
                //找到这个单子所有的催收人 所有的还款记录ID
                foreach($loanCollectionOrder as $ite){
                    /** @var LoanCollectionOrder $ite */
                    if (empty($ite['current_collection_admin_user_id'])) continue;
                    $currRayment[] = $ite['user_loan_order_repayment_id'];
                }
                //找出所有的还款记录
                $currRaymentss = UserLoanOrderRepayment::find()
                    ->select(['id','true_total_money','overdue_fee','status','closing_time','principal','interests'])
                    ->where(['id'=>array_values($currRayment)])
                    ->asArray()
                    ->all();
                $currRayments = [];
                foreach ($currRaymentss as $raymentss){
                    $currRayments[$raymentss['id']] = $raymentss;
                }
                //2、循环催收单子，统计每人的单子数
                foreach($loanCollectionOrder as $item){
                    /** @var LoanCollectionOrder $item */
                    $adminUserId = $item['current_collection_admin_user_id'];
                    if (!in_array($adminUserId,array_keys($loanAdminUsers))){
                        continue; //催收人不在催收人员列表中（催收人离职）
                    }
                    /** @var AdminUser $adminUser */
                    $adminUser = $loanAdminUsers[$adminUserId];
                    $adminUserName = $adminUser['username'];
                    $adminRealName = $adminUser['real_name'];
                    $adminUserOutside = $adminUser['outside'];
                    $adminGroup = $adminUser['group'];
                    $adminUserGroup = $item['current_overdue_group'];   //派单时的分组
                    /** @var UserLoanOrderRepayment $userLoanOrderRepayment */
                    $userLoanOrderRepayment = $currRayments[$item['user_loan_order_repayment_id']];
                    $itemLevel = $adminUserGroup;
                    if(!isset($data[$adminUserId][$itemLevel])){
                        $data[$adminUserId][$itemLevel]=[
                            'admin_user_id'=>$adminUserId,
                            'username'=>$adminUserName,
                            'real_name' => $adminRealName,
                            'outside'=>$adminUserOutside,  //机构
                            'loan_group'=>$adminGroup,  //当前催收人所在分组
                            'order_level'=>$itemLevel,  //单子催收级别 派单时的分组
                            'total_money'=>0,       //本金总额     就让这俩等于每天累加
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
                            'finish_late_fee_rate'=>0,         //滞纳金回收率  滞纳金收取金额/本应缴纳的滞纳金总额
                            'huankuan_total_money'=>0,         //催收成功的单子的本金总额
                            'today_get_loan_total'=>0,
                            'today_get_total_money'=>0,
                            'total_money_m'=>0,
                            'loan_total_m'=>0,
                            'back_fee'=>0, //总催回金额
                            'dis_money'=>0,
                        ];//拨打量 通话数没有统计
                    }
                    ##################当日处理###################
                    //today_get_loan_total  today_get_total_money total_money_m  loan_total_m operate_total  当日量
                    $amountInExpiryDate = $userLoanOrderRepayment['principal'] + $userLoanOrderRepayment['interests'];
                    // 今日入手单子
                    $dispatchTime = $item['last_dispatch_time'];
                    if ($dispatchTime > $todaytime && $dispatchTime < $towtime) {
                        $data[$adminUserId][$itemLevel]['today_get_loan_total'] += 1;
                        $data[$adminUserId][$itemLevel]['today_get_total_money'] += $amountInExpiryDate;
                    }
                    $data[$adminUserId][$itemLevel]['total_money_m'] += $amountInExpiryDate;   //入手金额
                    $data[$adminUserId][$itemLevel]['loan_total_m'] +=1 ;       //  入手总订单数
                    //当日的单子处理量 也就是最后催收时间是今天的
                    if($item['last_collection_time'] >= $todaytime){
                        $data[$adminUserId][$itemLevel]['operate_total'] += 1;
                    }


                    ################已还款处理#####################
                    //还款本金总额    催收成功的单子的本金总额+部分还款的实际还款金额总额
                    if ( $userLoanOrderRepayment['status'] == UserLoanOrderRepayment::STATUS_REPAY_COMPLETE) {
                        $adminRepaymentIds[$adminUserId][] = $userLoanOrderRepayment['id'];   //已还订单还款Id
                        $data[$adminUserId][$itemLevel]['finish_total'] += 1;
                        $data[$adminUserId][$itemLevel]['huankuan_total_money'] += min($amountInExpiryDate,$userLoanOrderRepayment['true_total_money']);  //催收成功的单子的 金额总额 不含逾期费
                        //还款本金总额 还款总数  滞纳金收取金额  本应缴纳的滞纳金总额
                        $data[$adminUserId][$itemLevel]['finish_total_money'] += min($amountInExpiryDate,$userLoanOrderRepayment['true_total_money']);
                        $finishLateFee = max($userLoanOrderRepayment['true_total_money'] - $amountInExpiryDate,0);
                        //$rid_info = RidOverdueLog::find()->where(['repayment_id'=>$userLoanOrderRepayment['id'],'type'=>RidOverdueLog::TYPE_ADMIN_SYSTEM,'repayment_type'=>RidOverdueLog::REPAYMENT_TYPE])->one();
                        $backFee = $userLoanOrderRepayment['true_total_money'];
                        //催收回的额外费用
                        //催回金额
                        $data[$adminUserId][$itemLevel]['back_fee'] += $backFee;

                        if ($finishLateFee > $userLoanOrderRepayment['overdue_fee']) {
                            $finishLateFee = $userLoanOrderRepayment['overdue_fee'];
                        }
                        //实际还款金额-本金
                        $data[$adminUserId][$itemLevel]['finish_late_fee'] += $finishLateFee;
//                        if($rid_info){
//                            $data[$adminUserId][$itemLevel]['late_fee_total'] += ($userLoanOrderRepayment['late_fee']-$rid_info['rid_money']);
//                        }else{
                            $data[$adminUserId][$itemLevel]['late_fee_total'] += $userLoanOrderRepayment['overdue_fee'];
//                        }
                        if($data[$adminUserId][$itemLevel]['finish_late_fee']>$data[$adminUserId][$itemLevel]['late_fee_total']){
                            $data[$adminUserId][$itemLevel]['finish_late_fee'] = $data[$adminUserId][$itemLevel]['late_fee_total'];
                        }
                    }
                    //剩余本金总额
                    if (UserLoanOrderRepayment::STATUS_REPAY_COMPLETE != $userLoanOrderRepayment['status']) {
                        $data[$adminUserId][$itemLevel]['no_finish_total_money'] += $amountInExpiryDate;
                    }
                    //今日还款本金总额  当日还款单数   这里用updated_at是因为可能部分还款(统一成closing_time)
                    if ($userLoanOrderRepayment['closing_time'] >= $todaytime && UserLoanOrderRepayment::STATUS_REPAY_COMPLETE == $userLoanOrderRepayment['status'] ) {
                        $data[$adminUserId][$itemLevel]['today_finish_total'] += 1;
                        $data[$adminUserId][$itemLevel]['today_finish_total_money'] += min($amountInExpiryDate,$userLoanOrderRepayment['true_total_money']);
                    }
                }
                $maxId = $item['id'];
                $cloneQuery = clone $query;
                $loanCollectionOrder = $cloneQuery->andWhere(['>','id',$maxId])->limit(1000)->orderBy(['id'=>SORT_ASC])->asArray()->all(LoanCollectionOrder::getDb_rd());
            }
            //统计 各种率
            foreach($data as $key=>$item){
                foreach ($item as $k => $value) {
                    $adminUserId = $key;

                    //取出上次的这个人这个级别的数据
                    //$yesterday = $todaytime-3600*24;
                    /** @var LoanCollectionStatistics $yesterdayLoanStatistic */
                    if(in_array($k,[LoanCollectionOrder::LEVEL_S1_1_3DAY,LoanCollectionOrder::LEVEL_S1_4_7DAY])){
                        $yesterdayLoanStatistic = LoanCollectionStatistics::find()
                            ->where(['admin_user_id' => $adminUserId,'order_level' => $k])
                            ->andWhere(['<','created_at',$todaytime])
                            ->andWhere(['>','created_at',strtotime('2020-07-20')])
                            ->orderBy(['created_at' => SORT_DESC])
                            ->limit(1)
                            ->one();
                    }else{
                        $yesterdayLoanStatistic = LoanCollectionStatistics::find()
                            ->where(['admin_user_id' => $adminUserId,'order_level' => $k])
                            ->andWhere(['<','created_at',$todaytime])
                            ->orderBy(['created_at' => SORT_DESC])
                            ->limit(1)
                            ->one();
                    }

                    $yesterdayLoanStatisticTotalMoney = 0;
                    $yesterdayLoanStatisticLoanTotal = 0;
                    if($yesterdayLoanStatistic){
                        $yesterdayLoanStatisticTotalMoney = $yesterdayLoanStatistic->total_money;
                        $yesterdayLoanStatisticLoanTotal = $yesterdayLoanStatistic->loan_total;
                    }

                    //赋值
                    $data[$key][$k]['total_money'] = $yesterdayLoanStatisticTotalMoney+$data[$key][$k]['today_get_total_money'];
                    $data[$key][$k]['loan_total'] = $yesterdayLoanStatisticLoanTotal+$data[$key][$k]['today_get_loan_total'];
                    $data[$key][$k]['dis_money'] =  0;
                    //还款率  还款到期应还总额/到期应还总额
                    if($data[$key][$k]['total_money']){
                        $data[$key][$k]['finish_total_rate'] =sprintf("%0.4f",$data[$key][$k]['huankuan_total_money']/$data[$key][$k]['total_money']);
                    }else{
                        $data[$key][$k]['finish_total_rate'] = "0.00";
                    }
                    //迁徙率  剩余到期应还金额/到期应还金额总额
                    if($data[$key][$k]['total_money']){
                        $data[$key][$k]['no_finish_rate'] =sprintf("%0.4f",$data[$key][$k]['no_finish_total_money']/$data[$key][$k]['total_money']);
                    }else{
                        $data[$key][$k]['no_finish_rate'] = "0.00";
                    }
                    //滞纳金回收率  滞纳金收取金额/本应缴纳的滞纳金总额
                    if($data[$key][$k]['late_fee_total']){
                        $data[$key][$k]['finish_late_fee_rate'] =sprintf("%0.4f",$data[$key][$k]['finish_late_fee']/$data[$key][$k]['late_fee_total']);
                    }else{
                        $data[$key][$k]['finish_late_fee_rate'] = "0.00";
                    }
                }
            }
            //写入 更新数据库
            foreach ($data as $key=>$value) {
                foreach ($value as $k=>$val) {
                    echo 'admin_id:'.$key.PHP_EOL;
                    //查出所有的统计表中的每人今天的信息 如果有就执行更新 没有就执行插入
                    $transaction = Yii::$app->db_assist->beginTransaction();
                    try{
                        $adminUserId = $key;
                        $orderLevel = $k;
                        $orderGroup = $val['loan_group'];
                        /** @var LoanCollectionStatistics $loanCollectionStatistic */
                        $loanCollectionStatistic = LoanCollectionStatistics::find()
                            ->where(['admin_user_id' => $adminUserId,'order_level' => $orderLevel])
                            ->andWhere(['>=','created_at',$todaytime])
                            ->andWhere(['<','created_at',$towtime])->one();

                        if (empty($loanCollectionStatistic)) {
                            $loanCollectionStatistic = new LoanCollectionStatistics;
                            $loanCollectionStatistic->created_at = time();
                            $loanCollectionStatistic->admin_user_id = $adminUserId;
                            $loanCollectionStatistic->username = $val['username'];
                            $loanCollectionStatistic->real_name = $val['real_name'];
                            $loanCollectionStatistic->outside = $val['outside'];
                            $loanCollectionStatistic->loan_group = $orderGroup;
                            $loanCollectionStatistic->order_level = $orderLevel;
                        }
                        $loanCollectionStatistic->merchant_id = $loanAdminUsers[$adminUserId]['merchant_id'];
                        $loanCollectionStatistic->total_money = $val['total_money'];
                        $loanCollectionStatistic->loan_total = $val['loan_total'];
                        $loanCollectionStatistic->today_get_loan_total = $val['today_get_loan_total'];
                        $loanCollectionStatistic->today_get_total_money = $val['today_get_total_money'];
                        $loanCollectionStatistic->today_finish_total_money = $val['today_finish_total_money'];
                        $loanCollectionStatistic->finish_total_money = $val['finish_total_money'];
                        $loanCollectionStatistic->no_finish_total_money = $val['no_finish_total_money'];
                        $loanCollectionStatistic->operate_total = $val['operate_total'];
                        $loanCollectionStatistic->today_finish_total = $val['today_finish_total'];
                        $loanCollectionStatistic->finish_total = $val['finish_total'];
                        $loanCollectionStatistic->finish_total_rate = $val['finish_total_rate'];
                        $loanCollectionStatistic->no_finish_rate = $val['no_finish_rate'];
                        $loanCollectionStatistic->finish_late_fee = $val['finish_late_fee'];
                        $loanCollectionStatistic->late_fee_total = $val['late_fee_total'];
                        $loanCollectionStatistic->finish_late_fee_rate = $val['finish_late_fee_rate'];
                        $loanCollectionStatistic->huankuan_total_money = $val['huankuan_total_money'];
                        $loanCollectionStatistic->member_fee = $val['back_fee'];
                        $loanCollectionStatistic->dis_money = $val['dis_money'];
                        $loanCollectionStatistic->updated_at = time();
                        $loanCollectionStatistic->save();
                        $transaction->commit();
                    }catch(\Exception $e){
                        echo $e->getMessage();
                        $transaction->rollBack();
                    }
                }
            }
        }
        echo '结束'.PHP_EOL;
    }

//    /**
//     * @name CollectionController 催收员每日统计脚本new
//     * @return
//     */
//    public function actionAllAdminStatisticNew(){
//        if(!$this->lock()){
//            return;
//        }
//        ini_set('max_execution_time', '0');
//        ini_set('memory_limit', '1024M');
//        echo '开始'.PHP_EOL;
//
//        $data = [];
//        //初始所有催收员数据
//        $loanAdminUserIds = [];
//        //找出所有的催收人员表中的催收人信息
//        $adminUsers = AdminUser::find()
//            ->where(['open_status' => AdminUser::OPEN_STATUS_ON])
//            ->andWhere(['>','outside',0])
//            ->andWhere(['>','group',0])
//            ->select(['id','outside','group','username','merchant_id'])->orderBy(['id'=>SORT_ASC])->all(AdminUser::getDb_rd());
//
//        /** @var AdminUser $adminUser */
//        foreach ($adminUsers as $adminUser) {
//            $loanAdminUserIds[] = $adminUser->id;
//            $data[$adminUser->id]=[
//                'merchant_id'=> $adminUser->merchant_id,
//                'username'=> $adminUser->username,
//                'outside'=> $adminUser->outside,  //机构
//                'loan_group'=> $adminUser->group,  //当前催收人所在分组
//                'today_finish_total_money'=>0,     //当日还款本金总额
//                'today_finish_total'=>0,        //当日还款单数
//                'no_finish_total_money'=>0,    //剩余本金总额
//                'operate_total'=>0,         //处理过的订单个数，包括有催收记录的订单（last_collection_time不为空）
//                'finish_late_fee'=>0,          //滞纳金收取金额  已收取的滞纳金总额
//                'late_fee_total'=>0,          //本应缴纳的滞纳金总额
//                'today_get_loan_total'=>0,
//                'today_get_total_money'=>0,
//                'total_money_m'=>0,
//                'loan_total_m'=>0,
//                'dis_money'=>0,
//            ];
//        }
//
//        $todaytime = strtotime('today');
//        $towtime = $todaytime + 86400;
//
//        //当日分派
//        $maxId = 0;
//        $query = LoanCollectionOrder::find()
//            ->where([
//                'and',
//                ['>=','last_dispatch_time',$todaytime],
//                ['<','last_dispatch_time',$towtime],
//            ])
//            ->orWhere([
//                'and',
//                ['>', 'last_collection_time', $todaytime],
//                ['<', 'last_collection_time', $towtime],
//            ])
//            ->orWhere(['status' => LoanCollectionOrder::$not_end_status]);;
//        $cloneQuery = clone $query;
//        $loanCollectionOrder = $cloneQuery->andWhere(['>','id',$maxId])
//            ->limit(1000)->orderBy(['id'=>SORT_ASC])
//            ->all(LoanCollectionOrder::getDb_rd());
//        if ($loanCollectionOrder) {
//            while ($loanCollectionOrder) {
//                $currRayment = [];
//                //找到这个单子所有的催收人 所有的还款记录ID
//                foreach($loanCollectionOrder as $ite){
//                    /** @var LoanCollectionOrder $ite */
//                    if (empty($ite->current_collection_admin_user_id)) continue;
//                    $currRayment[] = $ite->user_loan_order_repayment_id;
//                }
//                //找出所有的还款记录
//                $currRaymentss = UserLoanOrderRepayment::find()
//                    ->where(['id'=>array_values($currRayment)])->all();
//                $currRayments = [];
//                foreach ($currRaymentss as $raymentss){
//                    $currRayments[$raymentss['id']] = $raymentss;
//                }
//                //2、循环催收单子，统计每人的单子数
//                /** @var LoanCollectionOrder $item */
//                foreach($loanCollectionOrder as $item){
//                    /** @var UserLoanOrderRepayment $userLoanOrderRepayment */
//                    $userLoanOrderRepayment = $currRayments[$item->user_loan_order_repayment_id];
//                    // 今日入手单子
//                    $data[$item->current_collection_admin_user_id]['today_get_loan_total'] += 1;
//                    $data[$item->current_collection_admin_user_id]['today_get_total_money'] += $userLoanOrderRepayment->getAmountInExpiryDate();
//                    //当日的单子处理量 也就是最后催收时间是今天的
//                    if($item->last_collection_time >= $todaytime){
//                        $data[$item->current_collection_admin_user_id]['operate_total'] += 1;
//                    }
//                    //当日的单子完成量
//                    if ( $userLoanOrderRepayment->status == UserLoanOrderRepayment::STATUS_REPAY_COMPLETE) {
//                        $data[$item->current_collection_admin_user_id]['today_get_finish_total'] += 1;
//                        $data[$item->current_collection_admin_user_id]['today_get_finish_total_money'] += min($userLoanOrderRepayment->getAmountInExpiryDate(),$userLoanOrderRepayment->true_total_money);
//                    }
//                }
//                $maxId = $item->id;
//                $cloneQuery = clone $query;
//                echo $maxId.PHP_EOL;
//                $loanCollectionOrder = $cloneQuery->andWhere(['>','id',$maxId])->limit(1000)->orderBy(['id'=>SORT_ASC])->all(LoanCollectionOrder::getDb_rd());
//            }
//        }
//
//        //当日总完成
//        $todayFinishArr = LoanCollectionOrder::find()
//            ->from(LoanCollectionOrder::tableName() . ' A')
//            ->select(['A.current_collection_admin_user_id','today_finish_total' => 'COUNT(1)','today_finish_total_money' => 'SUM(B.principal+B.interests)'])
//            ->leftJoin(UserLoanOrderRepayment::getDbName().'.'.UserLoanOrderRepayment::tableName() . ' B','A.user_loan_order_repayment_id = B.id')
//            ->where(['A.current_collection_admin_user_id' => $loanAdminUserIds,'A.status' => LoanCollectionOrder::STATUS_COLLECTION_FINISH])
//            ->andWhere(['>=','B.closing_time',$todaytime])
//            ->andWhere(['<','B.closing_time',$towtime])
//            ->groupBy(['A.current_collection_admin_user_id'])
//            ->asArray()
//            ->all();
//        foreach ($todayFinishArr as $todayFinish){
//            $data[$todayFinish['current_collection_admin_user_id']]['today_finish_total'] = $todayFinish['today_finish_total'];
//            $data[$todayFinish['current_collection_admin_user_id']]['today_finish_total_money'] = $todayFinish['today_finish_total_money'];
//        }
//
//        //当前未完成
//        $noFinishArr = LoanCollectionOrder::find()
//            ->from(LoanCollectionOrder::tableName() . ' A')
//            ->select(['A.current_collection_admin_user_id','no_finish_total' => 'COUNT(1)','no_finish_total_money' => 'SUM(B.principal+B.interests)'])
//            ->leftJoin(UserLoanOrderRepayment::getDbName().'.'.UserLoanOrderRepayment::tableName() . ' B','A.user_loan_order_repayment_id = B.id')
//            ->where(['A.current_collection_admin_user_id' => $loanAdminUserIds,'A.status' => LoanCollectionOrder::$not_end_status])
//            ->groupBy(['A.current_collection_admin_user_id'])
//            ->asArray()
//            ->all();
//        foreach ($noFinishArr as $noFinish){
//            $data[$noFinish['current_collection_admin_user_id']]['no_finish_total_money'] = $noFinish['no_finish_total_money'];
//        }
//
//        //写入 更新数据库
//        foreach ($data as $key=>$val) {
//            echo 'admin_id:'.$key.PHP_EOL;
//            //查出所有的统计表中的每人今天的信息 如果有就执行更新 没有就执行插入
//            $transaction = Yii::$app->db_assist->beginTransaction();
//            try{
//                $adminUserId = $key;
//                /** @var LoanCollectionStatistics $loanCollectionStatistic */
//                $loanCollectionStatistic = LoanCollectionStatistics::find()
//                    ->where(['admin_user_id' => $adminUserId])
//                    ->andWhere(['>=','created_at',$todaytime])
//                    ->andWhere(['<','created_at',$towtime])->one();
//
//                if (empty($loanCollectionStatistic)) {
//                    $loanCollectionStatistic = new LoanCollectionStatistics;
//                    $loanCollectionStatistic->created_at = time();
//                    $loanCollectionStatistic->admin_user_id = $adminUserId;
//                    $loanCollectionStatistic->merchant_id = $val['merchant_id'];
//                    $loanCollectionStatistic->username = $val['username'];
//                    $loanCollectionStatistic->outside = $val['outside'];
//                    $loanCollectionStatistic->loan_group = $val['loan_group'];
//                }
//                $loanCollectionStatistic->today_get_loan_total = $val['today_get_loan_total'];
//                $loanCollectionStatistic->today_get_total_money = $val['today_get_total_money'];
//                $loanCollectionStatistic->today_finish_total_money = $val['today_finish_total_money'];
//                $loanCollectionStatistic->no_finish_total_money = $val['no_finish_total_money'];
//                $loanCollectionStatistic->operate_total = $val['operate_total'];
//                $loanCollectionStatistic->today_finish_total = $val['today_finish_total'];
//                $loanCollectionStatistic->finish_late_fee = $val['finish_late_fee'];
//                $loanCollectionStatistic->late_fee_total = $val['late_fee_total'];
//                $loanCollectionStatistic->updated_at = time();
//                $loanCollectionStatistic->save();
//                $transaction->commit();
//            }catch(\Exception $e){
//                echo $e->getMessage();
//                $transaction->rollBack();
//            }
//        }
//        echo '结束'.PHP_EOL;
//    }

    /**
     * 每日派单业绩统计(每日跟踪和累计跟踪)
     * @throws \yii\base\InvalidConfigException
     */
    public function actionTotalStatistics(){
        if(!$this->lock()){
            return;
        }
        ini_set('max_execution_time', '0');
        ini_set('memory_limit', '1024M');
        echo '开始'.PHP_EOL;

        $allAdminUsers = AdminUser::find()->select(['id','outside','group','username','merchant_id','real_name'])->orderBy(['id'=>SORT_ASC])->asArray()->all(AdminUser::getDb_rd());
        $loanAdminUsers = [];
        foreach ($allAdminUsers as $value) {
            $loanAdminUsers[$value['id']] = $value;
        }
        $mix_id = 0;
        $startTime = strtotime(date("Y-m-d",strtotime("-180 day")));
        $condition = 'dispatch_time>'.$startTime.' and dispatch_time<='.time();
        $query = LoanCollectionOrderAll::find()
            ->select([
                'id',
                'user_loan_order_repayment_id',
                'dispatch_time',
                'current_collection_admin_user_id',
                'current_overdue_group',
                'current_overdue_level',
                'outside_id',
                'last_collection_time',
                'status'
            ])
            ->where($condition);
        $loanCollectionOrder = $query->andWhere(['>','id',$mix_id])->limit(1000)->orderBy(['id'=>SORT_ASC])->asArray()->all(LoanCollectionOrderAll::getDb_rd());
        $allData = [];
        if($loanCollectionOrder)
        {
            while ($loanCollectionOrder)
            {
                $repaymentIds = [];
                $collectionData = [];
                foreach ($loanCollectionOrder as $item) {
                    /** @var LoanCollectionOrderAll $item */
                    if (empty($item['user_loan_order_repayment_id'])) continue;
                    $repaymentIds[] = $item['user_loan_order_repayment_id'];
                }
                foreach ($loanCollectionOrder as $item)
                {
                    /** @var LoanCollectionOrderAll $item */
                    $time = date('Y-m-d',$item['dispatch_time']);
                    if(empty($item['current_collection_admin_user_id'])) continue;
                    $collectionData[$item['current_collection_admin_user_id']][$time][] = $item;
                }
                $currRepayments = UserLoanOrderRepayment::find()
                    ->select(['id','status','true_total_money','overdue_fee','merchant_id','principal','interests','closing_time'])
                    ->where(['id'=>array_values($repaymentIds)])
                    ->indexBy('id')->asArray()->all(Yii::$app->get('db_read_1'));
                foreach ($collectionData as $adminId=>$v)
                {
                    /** @var AdminUser $adminUser */
                    $adminUser = $loanAdminUsers[$adminId];
                    foreach ($v as $date=>$v2)  // $k2 date('Y-m-d',$val['dispatch_time']) $v2 $loanCollectionOrder
                    {
                        foreach ($v2 as $v3)
                        {
                            /** @var LoanCollectionOrderAll $v3 */
                            if(!isset($allData[$adminId][$date]))
                            {
                                $allData[$adminId][$date]['admin_user_id'] = $adminId;
                                $allData[$adminId][$date]['admin_user_name'] = $adminUser['username'];
                                $allData[$adminId][$date]['real_name'] = $adminUser['real_name'];
                                $allData[$adminId][$date]['loan_group'] = $v3['current_overdue_group'];  //催收分组
                                $allData[$adminId][$date]['outside'] = 0;                                  //催收机构
                                $allData[$adminId][$date]['order_level'] = $v3['current_overdue_level'];   //订单级别
                                $allData[$adminId][$date]['today_all_money'] = 0;                          //今日入催本金
                                $allData[$adminId][$date]['loan_finish_total'] = 0;                        //当日入催完成单数
                                $allData[$adminId][$date]['loan_total'] = 0;                               //入催单数
                                $allData[$adminId][$date]['today_finish_money'] = 0;                       //完成本金
                                $allData[$adminId][$date]['all_late_fee'] = 0;                             //总滞纳金
                                $allData[$adminId][$date]['finish_late_fee'] = 0;                          //完成的滞纳金
                                $allData[$adminId][$date]['dispatch_time'] = 0;                             //派单时间
                                $allData[$adminId][$date]['operate_total'] = 0;                            //当日操作数
                                $allData[$adminId][$date]['today_no_finish_money'] = 0;                    //当日未完成金额
                                $allData[$adminId][$date]['true_total_money'] = 0;                         //实际还款金额
                                $allData[$adminId][$date]['today_finish_late_fee'] = 0;                    //今日完成订单的滞纳金总额
                                $allData[$adminId][$date]['oneday_total'] = 0;                            //首日完成单数
                                $allData[$adminId][$date]['oneday_money'] = 0;                            //首日完成金额
                                $allData[$adminId][$date]['status'] = 0;
                            }

                            if($val = $currRepayments[$v3['user_loan_order_repayment_id']]){
                                /** @var UserLoanOrderRepayment $val */
                                if($v3['user_loan_order_repayment_id'] == $val['id'])
                                {
                                    $amountInExpiryDate = $val['principal'] + $val['interests'];
                                    if($v3['status'] == 1)
                                    {
                                        $allData[$adminId][$date]['status'] = $v3['status'];
                                    }
                                    $allData[$adminId][$date]['admin_user_id'] = $adminId;
                                    $allData[$adminId][$date]['admin_user_name'] = $adminUser['username'];
                                    $allData[$adminId][$date]['loan_group'] = $v3['current_overdue_group'];
                                    $allData[$adminId][$date]['outside'] = $v3['outside_id'];
                                    $allData[$adminId][$date]['order_level'] = $v3['current_overdue_level'];
                                    $allData[$adminId][$date]['today_all_money'] += $amountInExpiryDate;
                                    if($val['status'] == UserLoanOrderRepayment::STATUS_REPAY_COMPLETE && $v3['status'] == LoanCollectionOrderAll::REDEPLOY_NO)
                                    {
                                        $allData[$adminId][$date]['loan_finish_total'] += 1;
                                        $allData[$adminId][$date]['today_finish_money'] += min($amountInExpiryDate,$val['true_total_money']);
//                                        $rid_money = RidOverdueLog::find()->where(['repayment_id'=>$val['id'],'type'=>RidOverdueLog::TYPE_ADMIN_SYSTEM,'repayment_type'=>RidOverdueLog::REPAYMENT_TYPE])->one(Yii::$app->get($db));
                                        $allData[$adminId][$date]['true_total_money']  += $val['true_total_money'];
//                                        if($rid_money){
//                                            $allData[$adminId][$date]['today_finish_late_fee']  += ($val['late_fee']-$rid_money['rid_money']);
//                                        }else{
                                        $allData[$adminId][$date]['today_finish_late_fee']  += $val['overdue_fee'];
//                                        }
                                        $finishLateFee = max($val['true_total_money']-$amountInExpiryDate,0);
                                        if($finishLateFee>$val['overdue_fee']){
                                            $finishLateFee = $val['overdue_fee'];
                                        }
                                        $allData[$adminId][$date]['finish_late_fee']+=$finishLateFee;
                                        //计算首日完成金额
                                        if(date('Y-m-d',$val['closing_time']) == $date){
                                            $allData[$adminId][$date]['oneday_money'] += min($amountInExpiryDate,$val['true_total_money']);
                                            $allData[$adminId][$date]['oneday_total'] +=1 ;
                                        }
                                    }
                                    $allData[$adminId][$date]['loan_total'] +=1 ;
                                    $allData[$adminId][$date]['all_late_fee'] += $val['overdue_fee'];
                                    $allData[$adminId][$date]['today_no_finish_money'] = $allData[$adminId][$date]['today_all_money'] - $allData[$adminId][$date]['today_finish_money'];
                                    $allData[$adminId][$date]['dispatch_time'] = $date;
                                    if($v3['last_collection_time']>strtotime($date))
                                    {
                                        $allData[$adminId][$date]['operate_total'] +=1 ;
                                    }
                                }
                            }
                        }
                    }
                }
                $mix_id = $item['id'];
                $loanCollectionOrder = $query->andWhere(['>','id',$mix_id])->limit(1000)->orderBy(['id'=>SORT_ASC])->asArray()->all(LoanCollectionOrderAll::getDb_rd());
            }
        }

        foreach ($allData as $key=>$val)
        {
            foreach ($val as $key2=>$val2)
            {
                echo 'adminId:'.$key.';date:'.$key2.PHP_EOL;
                $adminUseId = $key;
                $dispatchTime = strtotime($key2);
                $orderLevel = $val2['order_level'];
                $loanGroup = $val2['loan_group'];
                $status = $val2['status'];
                $adminUser = $loanAdminUsers[$adminUseId];

                if($dispatchTime<=0 || empty($orderLevel) || empty($loanGroup)){
                    continue;
                }
                $orderLevel = $loanGroup;
                $condition = [
                    'admin_user_id' => $adminUseId,
                    'dispatch_time' => $dispatchTime,
                    'order_level' => $orderLevel,
                    'loan_group' => $loanGroup,
                ];
                /** @var LoanCollectionStatisticNew $loanCollectionStatisticNew */
                $loanCollectionStatisticNew = LoanCollectionStatisticNew::find()->where($condition)->one();
                if(empty($loanCollectionStatisticNew))
                {
                    $loanCollectionStatisticNew = new LoanCollectionStatisticNew();
                    $loanCollectionStatisticNew->admin_user_id = $val2['admin_user_id'];
                    $loanCollectionStatisticNew->admin_user_name = $val2['admin_user_name'];
                    $loanCollectionStatisticNew->real_name = $val2['real_name'];
                    $loanCollectionStatisticNew->loan_group = $loanGroup;
                    $loanCollectionStatisticNew->outside_id = $val2['outside'];
                    $loanCollectionStatisticNew->true_total_money = $val2['true_total_money'];
                    $loanCollectionStatisticNew->order_level = $orderLevel;
                    $loanCollectionStatisticNew->today_all_money = $val2['today_all_money'];
                    $loanCollectionStatisticNew->loan_finish_total = $val2['loan_finish_total'];
                    $loanCollectionStatisticNew->loan_total = $val2['loan_total'];
                    $loanCollectionStatisticNew->today_finish_money = $val2['today_finish_money'];
                    $loanCollectionStatisticNew->all_late_fee = $val2['all_late_fee'];
                    $loanCollectionStatisticNew->finish_late_fee = $val2['finish_late_fee'];
                    $loanCollectionStatisticNew->dispatch_time = $dispatchTime;
                    $loanCollectionStatisticNew->operate_total = $val2['operate_total'];
                    $loanCollectionStatisticNew->oneday_money = $val2['oneday_money'];
                    $loanCollectionStatisticNew->oneday_total = $val2['oneday_total'];
                    $loanCollectionStatisticNew->today_no_finish_money = $val2['today_no_finish_money'];
                    $loanCollectionStatisticNew->today_finish_late_fee = $val2['today_finish_late_fee'];
                    $loanCollectionStatisticNew->merchant_id = $adminUser['merchant_id'];
                    if($val2['today_all_money']>0)
                    {
                        $loanCollectionStatisticNew->finish_total_rate = sprintf("%0.4f",$val2['today_finish_money']/$val2['today_all_money']);
                        $loanCollectionStatisticNew->no_finish_rate = sprintf("%0.4f",$val2['today_no_finish_money']/$val2['today_all_money']);
                    }
                    $loanCollectionStatisticNew->created_at = time();
                    $loanCollectionStatisticNew->updated_at = time();
                }
                else
                {
                    $loanCollectionStatisticNew->true_total_money = $val2['true_total_money'];
                    $loanCollectionStatisticNew->today_all_money = $val2['today_all_money'];
                    $loanCollectionStatisticNew->loan_total = $val2['loan_total'];
                    $loanCollectionStatisticNew->loan_finish_total = $val2['loan_finish_total'];
                    $loanCollectionStatisticNew->today_finish_money = $val2['today_finish_money'];
                    $loanCollectionStatisticNew->all_late_fee = $val2['all_late_fee'];
                    $loanCollectionStatisticNew->finish_late_fee = $val2['finish_late_fee'];
                    $loanCollectionStatisticNew->operate_total = $val2['operate_total'];
                    $loanCollectionStatisticNew->today_no_finish_money = $val2['today_no_finish_money'];
                    $loanCollectionStatisticNew->today_finish_late_fee = $val2['today_finish_late_fee'];
                    $loanCollectionStatisticNew->oneday_money = $val2['oneday_money'];
                    $loanCollectionStatisticNew->oneday_total = $val2['oneday_total'];
                    $loanCollectionStatisticNew->merchant_id = $adminUser['merchant_id'];
                    if($val2['today_all_money']>0){
                        $loanCollectionStatisticNew->finish_total_rate = sprintf("%0.4f",$val2['today_finish_money']/$val2['today_all_money']);
                        $loanCollectionStatisticNew->no_finish_rate = sprintf("%0.4f",$val2['today_no_finish_money']/$val2['today_all_money']);
                    }
                    $loanCollectionStatisticNew->updated_at = time();
                }
                $loanCollectionStatisticNew->save();
            }
        }
        echo '结束'.PHP_EOL;
    }

    /**
     * 每日派单业绩统计(每日跟踪和累计跟踪)v2
     * @throws \yii\base\InvalidConfigException
     */
    public function actionTotalTrackStatistics(){
        if(!$this->lock()){
            return;
        }
        ini_set('max_execution_time', '0');
        ini_set('memory_limit', '512M');
        $this->printMessage('开始');

        $allAdminUsers = AdminUser::find()->select(['id','outside','group','username','merchant_id'])->orderBy(['id'=>SORT_ASC])->asArray()->all(AdminUser::getDb_rd());
        $loanAdminUsers = [];
        foreach ($allAdminUsers as $value) {
            $loanAdminUsers[$value['id']] = $value;
        }
        $mix_id = 0;
        $startTime = max(strtotime(date("Y-m-d",strtotime("-180 day"))),strtotime('2020-04-30'));
        $query = LoanCollectionOrderAll::find()
            ->select([
                'id',
                'user_loan_order_repayment_id',
                'dispatch_time',
                'current_collection_admin_user_id',
                'current_overdue_group',
                'current_overdue_level',
                'outside_id',
                'last_collection_time',
                'status'
            ])
            ->where(['that_day_status' => LoanCollectionOrderAll::THAT_DAY_STATUS_IN_HANDS])
            ->andWhere(['>','dispatch_time',$startTime])
            ->andWhere(['<','dispatch_time',time()]);
        $loanCollectionOrder = $query->andWhere(['>','id',$mix_id])->limit(1000)->orderBy(['id'=>SORT_ASC])->asArray()->all(LoanCollectionOrderAll::getDb_rd());
        $allData = [];
        if($loanCollectionOrder)
        {
            while ($loanCollectionOrder)
            {
                $repaymentIds = [];
                $collectionData = [];
                foreach ($loanCollectionOrder as $item) {
                    if (empty($item['user_loan_order_repayment_id'])) continue;
                    $repaymentIds[] = $item['user_loan_order_repayment_id'];
                }
                foreach ($loanCollectionOrder as $item)
                {
                    $time = date('Y-m-d',$item['dispatch_time']);
                    if(empty($item['current_collection_admin_user_id'])) continue;
                    $collectionData[$item['current_collection_admin_user_id']][$time][] = $item;
                }
                $currRepayments = UserLoanOrderRepayment::find()
                    ->select(['id','status','true_total_money','overdue_fee','merchant_id','principal','interests','closing_time'])
                    ->where(['id'=>array_values($repaymentIds)])
                    ->indexBy('id')->asArray()->all(Yii::$app->get('db_read_1'));
                foreach ($collectionData as $adminId=>$v)
                {
                    $adminUser = $loanAdminUsers[$adminId];
                    foreach ($v as $date=>$v2)
                    {
                        foreach ($v2 as $v3)
                        {
                            $val = $currRepayments[$v3['user_loan_order_repayment_id']];
                            $orderMerchantId = $val['merchant_id'];

                            if(!isset($allData[$adminId][$date][$orderMerchantId]))
                            {
                                $allData[$adminId][$date][$orderMerchantId]['admin_user_id'] = $adminId;
                                $allData[$adminId][$date][$orderMerchantId]['admin_user_name'] = $adminUser['username'];
                                $allData[$adminId][$date][$orderMerchantId]['loan_group'] = $v3['current_overdue_group'];  //催收分组
                                $allData[$adminId][$date][$orderMerchantId]['outside'] = 0;                                  //催收机构
                                $allData[$adminId][$date][$orderMerchantId]['order_level'] = $v3['current_overdue_level'];   //订单级别

                                $allData[$adminId][$date][$orderMerchantId]['today_all_money'] = 0;                          //当日分派订单到期金额
                                $allData[$adminId][$date][$orderMerchantId]['loan_total'] = 0;                               //当日分派订单数
                                $allData[$adminId][$date][$orderMerchantId]['all_late_fee'] = 0;                             //当日分派订单的应还总滞纳金

                                $allData[$adminId][$date][$orderMerchantId]['operate_total'] = 0;                            //当日分派后有操作(有写催记)单数

                                $allData[$adminId][$date][$orderMerchantId]['loan_finish_total'] = 0;                        //当日分派后在手中完结的单数
                                $allData[$adminId][$date][$orderMerchantId]['today_finish_money'] = 0;                       //当日派单后在手中完成的到期金额和已还取最小
                                $allData[$adminId][$date][$orderMerchantId]['true_total_money'] = 0;                         //当日分派后在手中完结订单的已还款金额
                                $allData[$adminId][$date][$orderMerchantId]['today_finish_late_fee'] = 0;                    //当日分派后在手中完结订单的应还滞纳金总额
                                $allData[$adminId][$date][$orderMerchantId]['finish_late_fee'] = 0;                          //当日分派后在手中完结订单的已还滞纳金，减免时小于上面

                                $allData[$adminId][$date][$orderMerchantId]['oneday_total'] = 0;                            //首日完成单数
                                $allData[$adminId][$date][$orderMerchantId]['oneday_money'] = 0;                            //首日完成到期金额和已还取最小
                            }

                            if($v3['user_loan_order_repayment_id'] == $val['id'])
                            {
                                $amountInExpiryDate = $val['principal'] + $val['interests'];
                                $allData[$adminId][$date][$orderMerchantId]['admin_user_id'] = $adminId;
                                $allData[$adminId][$date][$orderMerchantId]['admin_user_name'] = $adminUser['username'];
                                $allData[$adminId][$date][$orderMerchantId]['loan_group'] = $v3['current_overdue_group'];
                                $allData[$adminId][$date][$orderMerchantId]['outside'] = $v3['outside_id'];
                                $allData[$adminId][$date][$orderMerchantId]['order_level'] = $v3['current_overdue_level'];
                                $allData[$adminId][$date][$orderMerchantId]['today_all_money'] += $amountInExpiryDate;
                                $allData[$adminId][$date][$orderMerchantId]['loan_total'] +=1 ;
                                $allData[$adminId][$date][$orderMerchantId]['all_late_fee'] += $val['overdue_fee'];
                                $allData[$adminId][$date][$orderMerchantId]['order_merchant_id'] = $val['merchant_id'];
                                if($v3['last_collection_time'] > strtotime($date))
                                {
                                    $allData[$adminId][$date][$orderMerchantId]['operate_total'] +=1 ;
                                }
                                //订单完成 且 为有效
                                if($val['status'] == UserLoanOrderRepayment::STATUS_REPAY_COMPLETE && $v3['status'] == LoanCollectionOrderAll::REDEPLOY_NO)
                                {
                                    $allData[$adminId][$date][$orderMerchantId]['loan_finish_total'] += 1;
                                    $allData[$adminId][$date][$orderMerchantId]['today_finish_money'] += min($amountInExpiryDate,$val['true_total_money']);
                                    $allData[$adminId][$date][$orderMerchantId]['true_total_money']  += $val['true_total_money'];
                                    $allData[$adminId][$date][$orderMerchantId]['today_finish_late_fee']  += $val['overdue_fee'];
                                    $finishLateFee = max($val['true_total_money']-$amountInExpiryDate,0);
                                    if($finishLateFee > $val['overdue_fee']){
                                        $finishLateFee = $val['overdue_fee'];
                                    }
                                    $allData[$adminId][$date][$orderMerchantId]['finish_late_fee']+=$finishLateFee;
                                    //计算首日完成金额
                                    if(date('Y-m-d',$val['closing_time']) == $date){
                                        $allData[$adminId][$date][$orderMerchantId]['oneday_money'] += min($amountInExpiryDate,$val['true_total_money']);
                                        $allData[$adminId][$date][$orderMerchantId]['oneday_total'] +=1 ;
                                    }
                                }
                            }
                        }
                    }
                }
                $mix_id = $item['id'];
                $loanCollectionOrder = $query->andWhere(['>','id',$mix_id])->limit(1000)->orderBy(['id'=>SORT_ASC])->asArray()->all(LoanCollectionOrderAll::getDb_rd());
            }
        }

        foreach ($allData as $adminUseId => $val)
        {
            foreach ($val as $date => $val2)
            {
                foreach ($val2 as $orderMerchantId => $val3)
                {
                    $this->printMessage('adminId:'.$adminUseId.';date:'.$date.';merchant:'.$orderMerchantId);
                    $orderLevel = $val3['order_level'];
                    $loanGroup = $val3['loan_group'];
                    $adminUser = $loanAdminUsers[$adminUseId];
                    $userMerchantId = $adminUser['merchant_id'];

                    if(empty($orderLevel) || empty($loanGroup)){
                        continue;
                    }
                    $orderLevel = $loanGroup;
                    $condition = [
                        'admin_user_id' => $adminUseId,
                        'dispatch_date' => $date,
                        'order_level' => $orderLevel,
                        'loan_group' => $loanGroup,
                        'order_merchant_id' => $orderMerchantId,
                        'user_merchant_id' => $userMerchantId
                    ];
                    /** @var LoanCollectionTrackStatistic $loanCollectionTrackStatistic */
                    $loanCollectionTrackStatistic = LoanCollectionTrackStatistic::find()->where($condition)->one();
                    if(empty($loanCollectionTrackStatistic))
                    {
                        $loanCollectionTrackStatistic = new LoanCollectionTrackStatistic();
                        $loanCollectionTrackStatistic->admin_user_id = $val3['admin_user_id'];
                        $loanCollectionTrackStatistic->admin_user_name = $val3['admin_user_name'];
                        $loanCollectionTrackStatistic->loan_group = $loanGroup;
                        $loanCollectionTrackStatistic->order_level = $orderLevel;
                        $loanCollectionTrackStatistic->dispatch_date = $date;
                        $loanCollectionTrackStatistic->outside_id = $val3['outside'];
                        $loanCollectionTrackStatistic->order_merchant_id = $orderMerchantId;
                        $loanCollectionTrackStatistic->user_merchant_id = $userMerchantId;
                    }

                    $loanCollectionTrackStatistic->today_all_money = $val3['today_all_money'];  //当日分派订单到期金额
                    $loanCollectionTrackStatistic->loan_total = $val3['loan_total'];            //当日分派订单数
                    $loanCollectionTrackStatistic->all_late_fee = $val3['all_late_fee'];   //当日分派订单的应还总滞纳金

                    $loanCollectionTrackStatistic->operate_total = $val3['operate_total'];  //当日分派后有操作(有写催记)单数

                    $loanCollectionTrackStatistic->loan_finish_total = $val3['loan_finish_total']; //当日分派后在手中完结的单数
                    $loanCollectionTrackStatistic->today_finish_money = $val3['today_finish_money']; //当日派单后在手中完成的到期金额和已还取最小
                    $loanCollectionTrackStatistic->true_total_money = $val3['true_total_money']; //当日分派后在手中完结订单的已还款金额
                    $loanCollectionTrackStatistic->today_finish_late_fee = $val3['today_finish_late_fee'];  //当日分派后在手中完结订单的应还滞纳金总额
                    $loanCollectionTrackStatistic->finish_late_fee = $val3['finish_late_fee'];  //当日分派后在手中完结订单的已还滞纳金(已还金额-到期金额 = 滞纳金)，减免时小于上面

                    $loanCollectionTrackStatistic->oneday_money = $val3['oneday_money'];  //当日分派后且在当日完成到期金额 和已还取最小（首日完成金额
                    $loanCollectionTrackStatistic->oneday_total = $val3['oneday_total'];  //当日分派后且在当日完成单数（首日完成单数）

                    $loanCollectionTrackStatistic->save();
                }
            }
        }
        $needClearAdminUserArr = LoanCollectionOrderAll::find()
            ->alias('A')
            ->select([
                'A.current_collection_admin_user_id',
                'order_merchant_id' => 'B.merchant_id',
                'user_merchant_id' => 'C.merchant_id',
                'A.current_overdue_level',
                'A.current_overdue_group',
                'min_that_day_status' => 'MIN(A.that_day_status)'
            ])
            ->leftJoin(LoanCollectionOrder::tableName().' B','A.loan_collection_order_id = B.id')
            ->leftJoin(AdminUser::tableName().' C','A.current_collection_admin_user_id = C.id')
            ->where(['>=','A.created_at',strtotime('today')])
            ->groupBy(['A.current_collection_admin_user_id','B.merchant_id','C.merchant_id','A.current_overdue_level','A.current_overdue_group'])
            ->having(['min_that_day_status' => LoanCollectionOrderAll::THAT_DAY_STATUS_RETURN])
            ->asArray()
            ->all();
        foreach ($needClearAdminUserArr as $value){
            $condition = [
                'admin_user_id' => $value['current_collection_admin_user_id'],
                'dispatch_date' => date('Y-m-d'),
                'order_merchant_id' => $value['order_merchant_id'],
                'user_merchant_id' => $value['user_merchant_id'],
                'order_level' => $value['current_overdue_level'],
                'loan_group' => $value['current_overdue_group'],
            ];
            $loanCollectionTrackStatistic = LoanCollectionTrackStatistic::find()->where($condition)->one();
            if($loanCollectionTrackStatistic){
                $loanCollectionTrackStatistic->today_all_money = 0;  //当日分派订单到期金额
                $loanCollectionTrackStatistic->loan_total = 0;            //当日分派订单数
                $loanCollectionTrackStatistic->all_late_fee = 0;   //当日分派订单的应还总滞纳金
                $loanCollectionTrackStatistic->save();
            }
        }
        $this->printMessage('结束');
    }

    /**
     * @name 催收员每日统计
     */
    public function actionWorkerDayStatistics(){
        ini_set('max_execution_time', '0');
        ini_set('memory_limit', '1024M');
        $this->printMessage('start');

        for ($i=30; $i >= 0;$i--){
            $today = strtotime('-'.$i.' day');
            $this->printMessage('day '.date('Y-m-d',$today));
            $today_start = strtotime(date('Y-m-d',$today));
            $today_end   = $today_start + 86400;
            /////入催
            $LoanCollectionOrderAlls = LoanCollectionOrderAll::find()
                ->select(['A.current_collection_admin_user_id,
                    count(A.id) as get_total_count,
                    sum(R.total_money) as get_total_money'
                ])
                ->from('saas_assist.'. LoanCollectionOrderAll::tableName() . '  A')
                ->leftJoin(UserLoanOrderRepayment::getDbName().'.'.UserLoanOrderRepayment::tableName() . '  R','R.id = A.user_loan_order_repayment_id')
                ->where(['>=','A.dispatch_time',$today_start])
                ->andWhere(['<','A.dispatch_time',$today_end])
                ->groupBy(['A.current_collection_admin_user_id'])
                ->asArray()->all();
            $arr = [];
            //这天内派单数和 派单金额
            foreach ($LoanCollectionOrderAlls as $collectionOrderAll){
                $arr[$collectionOrderAll['current_collection_admin_user_id']]['get_total_count'] = $collectionOrderAll['get_total_count'];
                $arr[$collectionOrderAll['current_collection_admin_user_id']]['get_total_money'] = $collectionOrderAll['get_total_money'];
            }

            //////当日处理量
            $LoanCollectionOrderAlls = LoanCollectionOrderAll::find()
                ->select(['current_collection_admin_user_id,
                    count(id) as operate_total'
                ])
                ->where(['>=','dispatch_time',$today_start])
                ->andWhere(['<','dispatch_time',$today_end])
                ->andWhere('last_collection_time > dispatch_time')
                ->andWhere(['<','last_collection_time',$today_end])
                ->groupBy(['current_collection_admin_user_id'])
                ->asArray()->all();
            //这天内派单数和 派单金额
            foreach ($LoanCollectionOrderAlls as $collectionOrderAll){
                $arr[$collectionOrderAll['current_collection_admin_user_id']]['operate_total'] = $collectionOrderAll['operate_total'];
            }

            ///////催回
            $LoanCollectionOrderAlls = LoanCollectionOrder::find()
                ->select(['A.current_collection_admin_user_id,count(A.id) as finish_total_count,sum(R.true_total_money) as finish_total_money'])
                ->from('saas_assist.'. LoanCollectionOrder::tableName() . '  A')
                ->leftJoin('saas_assist.'.LoanCollectionOrderAll::tableName() . '  B','B.loan_collection_order_id = A.id')
                ->leftJoin(UserLoanOrderRepayment::getDbName().'.'.UserLoanOrderRepayment::tableName() . '  R','R.id = A.user_loan_order_repayment_id')
                ->where(['R.status' => UserLoanOrderRepayment::STATUS_REPAY_COMPLETE])
                ->andWhere(['B.status' => LoanCollectionOrderAll::REDEPLOY_NO])
                ->andWhere(['>','B.last_collection_time',0])
                ->andWhere(['>=','A.dispatch_time',$today_start])
                ->andWhere(['<','A.dispatch_time',$today_end])
                ->andWhere(['>','A.current_collection_admin_user_id',0])
                ->groupBy(['A.current_collection_admin_user_id'])
                ->asArray()->all();
            //这天内还款单数 还款金额
            foreach ($LoanCollectionOrderAlls as $LoanCollectionOrderAll){
                $arr[$LoanCollectionOrderAll['current_collection_admin_user_id']]['finish_total_count'] = $LoanCollectionOrderAll['finish_total_count'];
                $arr[$LoanCollectionOrderAll['current_collection_admin_user_id']]['finish_total_money'] = $LoanCollectionOrderAll['finish_total_money'];
            }
            if(empty($arr)){
                $this->printMessage('no data');
                continue;
            }

            $adminUserIds = array_keys($arr);
//
//            $LoanCollectionRecords = LoanCollectionRecord::find()
//                ->select(['B.admin_user_id'])
//                ->from(LoanCollectionRecord::tableName() . '  A')
//                ->leftJoin(LoanCollection::tableName() . '  B','B.id = A.operator')
//                ->where(['>','A.operator',0])
//                ->andWhere(['B.admin_user_id' => $adminUserIds])
//                ->andWhere(['>=','A.created_at',$today_start])
//                ->andWhere(['<','A.created_at',$today_end])
//                ->groupBy(['B.admin_user_id','A.order_id'])
//                ->asArray()->all();
//
////            var_dump($LoanCollectionRecords);
//            if($LoanCollectionRecords){
//                foreach ($LoanCollectionRecords as $LoanCollectionRecord){
//                    if(isset($arr[$LoanCollectionRecord['admin_user_id']]['operate_total'])){
//                        $arr[$LoanCollectionRecord['admin_user_id']]['operate_total'] += 1;
//                    }else{
//                        $arr[$LoanCollectionRecord['admin_user_id']]['operate_total'] = 1;
//                    }
//                }
//            }


            $loanCollectionArr = [];
            /** @var AdminUser $loanCollections */
            $loanCollections = AdminUser::find()->where(['id' => $adminUserIds])->all();
            foreach ($loanCollections as $loanCollection){
                echo $loanCollection->id;
                $loanCollectionArr[$loanCollection->id] = $loanCollection;
            }
            foreach ($arr as $collection_admin_user_id => $val){
                //查出所有的统计表中的每人今天的信息 如果有就执行更新 没有就执行插入
                $loan_collection_statistic = LoanCollectionDayStatistics::find()->where(['admin_user_id' => $collection_admin_user_id,'date' => date('Y-m-d',$today)])->one();
                if (empty($loan_collection_statistic)) {
                    $loan_collection_statistic = new LoanCollectionDayStatistics;
                    $loan_collection_statistic->date = date('Y-m-d',$today);
                    $loan_collection_statistic->admin_user_id = $collection_admin_user_id;
                    $loan_collection_statistic->username = $loanCollectionArr[$collection_admin_user_id]->username;
                    $loan_collection_statistic->outside = $loanCollectionArr[$collection_admin_user_id]->outside;
                    $loan_collection_statistic->group = $loanCollectionArr[$collection_admin_user_id]->group;
                    $loan_collection_statistic->group_game = $loanCollectionArr[$collection_admin_user_id]->group_game;
                    $loan_collection_statistic->merchant_id = $loanCollectionArr[$collection_admin_user_id]->merchant_id;
                }

                foreach ($val as $k => $v){
                    $loan_collection_statistic->{$k} = $v;
                }
                $loan_collection_statistic->save();
            }
            $this->printMessage('complete!');
        }


    }

    /**
     * 订单总的状况每日统计
     * @param string $start_time
     */
    public function actionOrderStatistics($start_time=''){
        if(!$this->lock()){
            return;
        }
        $db_assist = \Yii::$app->db_assist;

        // 获取所有商户
        $oMerchant = Merchant::find()->all();

        foreach ($oMerchant as $item)
        {
            $begin_time = $start_time ? strtotime($start_time) : strtotime("today");
            $time = strtotime("today");

            if(date('H',time()) < 1){
                $begin_time -= 86400;
            }
            while ($begin_time <= $time){
                $date = date('Y-m-d', $begin_time);
                echo $date.PHP_EOL;
                $end_time = $begin_time + 86400;

                //入催单数
                $sql = "
                select count(1) as cnt
                from tb_loan_collection_order
                where created_at >= {$begin_time}
                    and created_at < {$end_time}
                    and merchant_id = {$item->id}
            ";
                $loan = $db_assist->createCommand($sql)->queryOne();

                //出催单数
                $sql = "
                select count(1) as cnt from tb_loan_collection_status_change_log
                where after_status = ".LoanCollectionOrder::STATUS_COLLECTION_FINISH."
                    and created_at >= {$begin_time}
                    and created_at < {$end_time}
                    and merchant_id = {$item->id}
            ";
                $repay = $db_assist->createCommand($sql)->queryOne();

                $order_statistics = OrderStatistics::findOne(['date'=>$date,'merchant_id' => $item->id]);
                if(empty($order_statistics)){
                    $order_statistics = new OrderStatistics();
                    $order_statistics->date = $date;
                    $order_statistics->merchant_id = $item->id;
                }
                $order_statistics->loan_num  = $loan['cnt'];
                $order_statistics->repay_num = $repay['cnt'];
                if(!$order_statistics->save()){
                    echo "订单分布统计：" . $date . "的数据保存失败";
                }

                $begin_time += 86400;
            }
        }

    }


    /**
     *脚本五(第一部分），订单概览统计
     *获取截止到目前，【催收中】、【承诺还款】、【催收成功】的“订单数”、“本金”、“实际滞纳金”、“应还滞纳金”
     *, '`last_collection_time` > '.strtotime(date('Y-m-d 0:0:0')).' AND `last_collection_time` < '.strtotime(date('Y-m-d 23:59:59'))
     *脚本执行时间建议：上午9:00
     */

    public function actionOrderStatusAndGroupStatistics(){
        try{

            $array_status = [
                LoanCollectionOrder::STATUS_COLLECTION_PROGRESS, //催收中
                LoanCollectionOrder::STATUS_COLLECTION_PROMISE, //承诺还款
                LoanCollectionOrder::STATUS_COLLECTION_FINISH, //催收成功
            ];
            foreach ($array_status as $status){
                $month_day = strtotime(date('Y-m-01'));
                $where = "`status` = ".$status.' and dispatch_time>'.$month_day;
                $warning = array();
                $limit = 1000;
                //催收成功特殊处理  用上月最后一天的数据累加当月派单且催收成功的
                if($status == LoanCollectionOrder::STATUS_COLLECTION_FINISH){
                    $lim =7;
                    $byStatus = OrderStatisticsByStatus::find()
                        ->where(['stage_type'=>0,'order_status'=>$status])
                        ->andWhere(['<','create_at',$month_day])
                        ->orderBy(['id'=>SORT_DESC])->limit(1)->one(LoanCollectionOrder::getDb_rd());
                    $by_group = OrderStatisticsByGroup::find()
                        ->where(['stage_type'=>0,'order_status'=>$status])
                        ->andWhere(['<','create_at',$month_day])
                        ->orderBy(['id'=>SORT_DESC])->limit($lim)->all(LoanCollectionOrder::getDb_rd());
                    if(empty($byStatus) || empty($by_group)){
                        $arr = array('principal'=>0, 'overdue_fee'=>0, 'true_overdue_fee'=>0, 'amount'=>0, 'status'=>$status);//根据订单状态统计
                        $records = array('status'=>$status,'groups'=>array());//根据催收分组统计
                    }else{
                        $arr = array('principal'=>$byStatus['principal'], 'overdue_fee'=>$byStatus['overdue_fee'], 'true_overdue_fee'=>$byStatus['true_overdue_fee'], 'amount'=>$byStatus['amount'], 'status'=>$status);
                        $records = array('status'=>$status,'groups'=>array());
                    }
                    foreach ($by_group as $group){
                        $records['group'][$group['group']]['id'] =$group['group'];
                        $records['group'][$group['group']]['amount'] =$group['amount'];
                        $records['group'][$group['group']]['principal'] =$group['principal'];
                    }
                    $max_id=0;
                }else{
                    $max_id = 0;
                    $arr = array('principal'=>0, 'overdue_fee'=>0, 'true_overdue_fee'=>0, 'amount'=>0, 'status'=>$status);//根据订单状态统计
                    $records = array('status'=>$status,'groups'=>array());//根据催收分组统计
                }
                $query = LoanCollectionOrder::find()->where($where);


                // 分成所有商户的订单分组
                $oMerchant = Merchant::find()->all();
                foreach ($oMerchant as $item) {
                    // 查询该商户下的订单数据
                    $oOrder      = clone $query;
                    $oLoanOrders = $oOrder->andWhere(['>','id',$max_id])->andWhere(['=', 'merchant_id', $item->id])->orderBy(['id'=>SORT_ASC])->all(LoanCollectionOrder::getDb_rd());

                    // 判断当前商户下是否有订单
                    if (!empty($oLoanOrders))
                    {
                        while (!empty($oLoanOrders))
                        {
                            /** @var LoanCollectionOrder $v */
                            foreach ($oLoanOrders as $v)
                            {
                                /** @var UserLoanOrderRepayment $repayOrder */
                                $repayOrder = UserLoanOrderRepayment::find()->where(['order_id'=>$v['user_loan_order_id']])->one(Yii::$app->db_read_1);
                                $arr['principal'] += $repayOrder->getAmountInExpiryDate();
                                $arr['overdue_fee'] += $repayOrder->overdue_fee;
                                $arr['true_overdue_fee'] += $repayOrder->true_total_money > $repayOrder->getAmountInExpiryDate() ? ($repayOrder->true_total_money - $repayOrder->getAmountInExpiryDate()) : 0;
                                $arr['amount']++;
                                $arr['merchant_id'] = $item->id;

                                $records['groups'][$v->current_overdue_group]['id'] = $v->current_overdue_group;
                                $records['groups'][$v->current_overdue_group]['merchant_id'] = $item->id;
                                isset($records['groups'][$v->current_overdue_group]['amount']) ? ($records['groups'][$v->current_overdue_group]['amount'] ++) : $records['groups'][$v->current_overdue_group]['amount'] =1;//订单数
                                isset($records['groups'][$v->current_overdue_group]['principal']) ? ($records['groups'][$v->current_overdue_group]['principal'] += $repayOrder->principal): ($records['groups'][$v->current_overdue_group]['principal'] = $repayOrder->getAmountInExpiryDate());//本金
                                if(!array_key_exists($v->current_overdue_group, LoanCollectionOrder::$level)){
                                    $warning[] = $v->user_loan_order_id;
                                }

                            }
                            $max_id = $v->id;
                            $oLoanOrders = $oOrder->andWhere(['>','id',$max_id])->andWhere(['=', 'merchant_id', $item->id])->orderBy(['id'=>SORT_ASC])->limit($limit)->all(LoanCollectionOrder::getDb_rd());
                        }
                        OrderStatisticsByStatus::collectionInputStatistics(array($arr));
                        OrderStatisticsByGroup::collectionInputStatistics($records);
                    }
                }

//                $loanOrders = $query->andWhere(['>','id',$max_id])->orderBy(['id'=>SORT_ASC])->limit($limit)->all(LoanCollectionOrder::getDb_rd());
//                while (!empty($loanOrders)) {
//                    /** @var LoanCollectionOrder $v */
//                    foreach ($loanOrders as $v){
//                        /** @var UserLoanOrderRepayment $repayOrder */
//                        $repayOrder = UserLoanOrderRepayment::find()->where(['order_id'=>$v['user_loan_order_id']])->one(Yii::$app->db_read_1);
//                        $arr['principal'] += $repayOrder->getAmountInExpiryDate();
//                        $arr['overdue_fee'] += $repayOrder->overdue_fee;
//                        $arr['true_overdue_fee'] += $repayOrder->true_total_money > $repayOrder->getAmountInExpiryDate() ? ($repayOrder->true_total_money - $repayOrder->getAmountInExpiryDate()) : 0;
//                        $arr['amount']++;
//                        $arr['merchant_id'] = $v->merchant_id;
//                        var_dump('order_id：' . $v['user_loan_order_id']);
//                        var_dump($repayOrder->principal);
//                        var_dump($repayOrder->getAmountInExpiryDate());
//
//                        $records['groups'][$v->current_overdue_group]['id'] = $v->current_overdue_group;
//                        $records['groups'][$v->current_overdue_group]['merchant_id'] = $v->merchant_id;
//                        isset($records['groups'][$v->current_overdue_group]['amount']) ? ($records['groups'][$v->current_overdue_group]['amount'] ++) : $records['groups'][$v->current_overdue_group]['amount'] =1;//订单数
//                        isset($records['groups'][$v->current_overdue_group]['principal']) ? ($records['groups'][$v->current_overdue_group]['principal'] += $repayOrder->principal): ($records['groups'][$v->current_overdue_group]['principal'] = $repayOrder->getAmountInExpiryDate());//本金
//                        if(!array_key_exists($v->current_overdue_group, LoanCollectionOrder::$level)){
//                            $warning[] = $v->user_loan_order_id;
//                        }
//
//                    }
//                    $max_id = $v->id;
//                    $loanOrders = $query->andWhere(['>','id',$max_id])->orderBy(['id'=>SORT_ASC])->limit($limit)->all(LoanCollectionOrder::getDb_rd());
//                }
//                OrderStatisticsByStatus::collectionInputStatistics(array($arr));
//                OrderStatisticsByGroup::collectionInputStatistics($records);

            }

        }catch(\Exception $e){
            echo $e->getFile().$e->getLine().$e->getMessage();
        }
    }

    /**
     *脚本五(第二部分），每日催收统计
     *功能一：获取每天新增还款订单数、还款到期应还金额、还款滞纳金
     *功能二：统计当天到期订单数
     */

    public function actionStatisticsDaily(){
        //昨天新增还款数量、到期应还金额、滞纳金：
        try{
            $transaction= Yii::$app->db_assist->beginTransaction();//创建事务

            $dates = array(array('createTime'=>date('Y-m-d', strtotime('-1 day'))));
            $i = 1;
            $amount = count($dates);
            foreach ($dates as $key => $day) {
                echo '更新每天新增还款单数，到期应还金额，滞纳金：'.$i++.'/'.$amount."(".$day['createTime'].")\r\n";

                $res = LoanCollectionOrder::find()
                    ->select(["principal" => "sum(B.principal + B.interests)", "late_fee" => "sum(B.overdue_fee)", "amount" => "count(1),A.merchant_id"])
                    ->from(LoanCollectionOrder::tableName(). ' A')
                    ->leftJoin(UserLoanOrderRepayment::getDbName().'.'.UserLoanOrderRepayment::tableName() . ' B','A.user_loan_order_repayment_id = B.id')
                    ->where(['A.status' => LoanCollectionOrder::STATUS_COLLECTION_FINISH])
                    ->andWhere(['>=','B.closing_time', strtotime($day['createTime'])])
                    ->andWhere(['<','B.closing_time', strtotime($day['createTime']) + 86400])
                    ->asArray()
                    ->one(LoanCollectionOrder::getDb_rd());

                if($res['amount'] <= 0){
                    $res['principal'] = 0;
                    $res['late_fee'] = 0;
                }
                OrderStatisticsByDay::collectionInputStatistics(['repay_principal'=>$res['principal'],'repay_amount'=>$res['amount'],'repay_late_fee'=>$res['late_fee'],'create_at'=>$day['createTime'], 'merchant_id'=>$res['merchant_id'] ?? 0]);
            }
            $transaction->commit();
        }catch(\Exception $e){
            $transaction->rollBack();
            echo $e->getMessage().$e->getLine();

        }
        //// 今天到期应还本金：
        // 【昨天】到期应还本金（为了校正本金催回率统计结果，同一时间统计当日入催本金及前一天到期应还金额）
        try{
            $transaction= Yii::$app->db_assist->beginTransaction();//创建事务
            $yesterday = strtotime('-1 day');
            $time_start = strtotime(date("Y-m-d 0:0:0", $yesterday));
            $time_end = strtotime(date("Y-m-d 23:59:59", $yesterday));

            $arrMerchant = Merchant::find()->all();

            foreach ($arrMerchant as $item)
            {
                $res = UserLoanOrderRepayment::find()
                    ->where(['>=','plan_repayment_time',$time_start])
                    ->andWhere(['<','plan_repayment_time',$time_end])
                    ->andWhere(['=','merchant_id',$item['id']])
                    ->select(["amount"=>"SUM(principal+interests)"])
                    ->asArray()
                    ->all(Yii::$app->db_read_1);
                $amount = $res[0]['amount'];
                OrderStatisticsByRate::rateAmount(['deadline_amount'=>$amount,'create_at'=>strtotime(date('Y-m-d',strtotime('-1 day'))), 'merchant_id'=>$item['id']],0);
            }

            $transaction->commit();
        }catch(\Exception $e){
            $transaction->rollBack();
            echo $e->getMessage().$e->getLine();
            exit;

        }
        //今天入催订单总到期应还金额， 入催订单量：
        try{
            $transaction= Yii::$app->db_assist->beginTransaction();//创建事务
            //获取今天入催时间：(每天执行一次，即可更新当天数据)
            $dates = strtotime(date('Y-m-d'));
            $res = LoanCollectionOrder::find()
                ->select('sum(B.principal + B.interests) as principal, sum(B.overdue_fee) as late_fee, count(1) as amount, A.merchant_id')
                ->from(LoanCollectionOrder::tableName(). ' A')
                ->leftJoin(UserLoanOrderRepayment::getDbName().'.'.UserLoanOrderRepayment::tableName() . ' B','A.user_loan_order_repayment_id = B.id')
                ->where(['>=','A.created_at',$dates])
                ->andWhere(['<','A.created_at',$dates+86400])
                ->asArray()
                ->one(LoanCollectionOrder::getDb_rd());
            $amount = $res['amount'] ?? 0;
            $principal = $res['principal'] ?? 0;
            OrderStatisticsByRate::rateAmount(['collection_amount'=>$principal,'create_at'=>$dates, 'merchant_id'=>$res['merchant_id']],0);
            OrderStatisticsByDay::collectionInputStatistics(['new_principal'=>$principal,'new_amount'=>$amount,'create_at'=>$dates, 'merchant_id'=>$res['merchant_id'] ?? 0]);
            $transaction->commit();
        }catch(\Exception $e){
            $transaction->rollBack();
            echo $e->getMessage().$e->getLine();

        }
        //最新逾期天数的逾期催回率：(统计不同逾期天数的催收成功订单额)
        try{
            $transaction= Yii::$app->db_assist->beginTransaction();//创建事务
            $day_today =  strtotime(date('Y-m-d'),time());
            $day_start = $day_today - 3600 * 24 * 91;//更新91天内数据

            for ($day=$day_start; $day < $day_today; $day+=3600*24) {
                echo '更新日期：'.date("Y-m-d", $day)."\r\n";

                $arrMerchant = Merchant::find()->all();
                foreach ($arrMerchant as $item)
                {
                    $collection_order = LoanCollectionOrder::find()->select(['user_loan_order_repayment_id'])
                        ->where(['>=','created_at',$day])
                        ->andWhere(['<','created_at',$day+86400])
                        ->andWhere(['=', 'merchant_id', $item['id']])
                        ->orderBy(['id'=>SORT_DESC])->all(LoanCollectionOrder::getDb_rd());
                    $repaymentIds = [];
                    foreach ($collection_order as $value){
                        $repaymentIds[] = $value['user_loan_order_repayment_id'];
                    }
                    if(empty($repaymentIds)) continue;

                    $res = UserLoanOrderRepayment::find()
                        ->select(["total"=>"IF(true_total_money>principal+interests, principal+interests, true_total_money)", 'OverdueDays'=>"overdue_day"])
                        ->where(['id' => $repaymentIds,'status' => UserLoanOrderRepayment::STATUS_REPAY_COMPLETE])
                        ->orderBy(['overdue_day'=>SORT_ASC])
                        ->asArray()->all(Yii::$app->db_read_1);

                    $res['merchant_id'] = $item['id'];

                    if(!empty($res)){
                        $rate = [
                            'create_at'=>$day,
                            'repay_1_amount'=>0,
                            'repay_2_amount'=>0,
                            'repay_3_amount'=>0,
                            'repay_4_amount'=>0,
                            'repay_5_amount'=>0,
                            'repay_6_amount'=>0,
                            'repay_7_amount'=>0,
                            'repay_10_amount'=>0,
                            'repay_30_amount'=>0,
                            'repay_60_amount'=>0,
                            'repay_90_amount'=>0,
                            'repay_999_amount'=>0,
                        ];
                        foreach ($res as $key => $v) {
                            if($v['OverdueDays'] <=1){
                                //逾期0,1天
                                $rate['repay_1_amount'] += (int)$v['total'] ??0;

                            }elseif ($v['OverdueDays'] <=2) {
                                //逾期2天
                                $rate['repay_2_amount'] += (int)$v['total'] ??0;

                            }elseif ($v['OverdueDays'] <=3) {
                                //逾期3天
                                $rate['repay_3_amount'] += (int)$v['total'] ??0;

                            }elseif ($v['OverdueDays'] <=4) {
                                //逾期4天
                                $rate['repay_4_amount'] += (int)$v['total'] ??0;

                            }elseif ($v['OverdueDays'] <=5) {
                                //逾期5天
                                $rate['repay_5_amount'] += (int)$v['total'] ??0;

                            }elseif ($v['OverdueDays'] <=6) {
                                //逾期6天
                                $rate['repay_6_amount'] += (int)$v['total'] ??0;

                            }elseif ($v['OverdueDays'] <=7) {
                                //逾期7天
                                $rate['repay_7_amount'] += (int)$v['total'] ??0;

                            }elseif ($v['OverdueDays'] <=10) {
                                //逾期8~10天
                                $rate['repay_10_amount'] += (int)$v['total'] ??0;

                            }elseif ($v['OverdueDays'] <=30) {
                                //逾期11~30天
                                $rate['repay_30_amount'] += (int)$v['total'] ??0;

                            }elseif ($v['OverdueDays'] <=60) {
                                //逾期31~60天
                                $rate['repay_60_amount'] += (int)$v['total'] ??0;

                            }elseif ($v['OverdueDays'] <=90) {
                                //逾期61~90天
                                $rate['repay_90_amount'] += (int)$v['total'] ??0;

                            }else{
                                //逾期91天以上
                                $rate['repay_999_amount'] += (int)$v['total'] ??0;

                            }
                        }

                        OrderStatisticsByRate::rateAmount($rate,0);
                    }
                }
            }
            $transaction->commit();
        }catch(\Exception $e){
            echo $e->getMessage().$e->getLine();
            $transaction->rollBack();
        }
    }

    /**
     *统计每天入催以及不同逾期天数的出崔率（按单和按金额）
     */
    public function actionInputOverdueOut(){
        if(!$this->lock()){
            return ExitCode::UNSPECIFIED_ERROR;
        }
        echo 'start'.PHP_EOL;
        ini_set('memory_limit', '512M');
        $endTime = strtotime(date("Y-m-d",strtotime("+1 day")));//当天结束时间
        $startTime = max(strtotime(date("Y-m-d",strtotime("-180 day"))), strtotime(date('2019-10-01')));

        $inputOverdueOutService = new InputOverdueOutService();
        while ($startTime < $endTime)
        {
            $startDate = date('Y-m-d',$startTime);
            echo $startDate.PHP_EOL;
            $inputOverdueOutService->runInputOverdueOut($startDate);
            $startTime += 86400;
        }
        echo 'end'.PHP_EOL;
    }


    //机构每日快照统计
    public function actionOutsideSnapshotDay(){
        if(!$this->lock()){
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $today = strtotime("today");
        if(date('H',time()) == 0){ //更新前一天的
            $today -= 86400;
        }

        $todayDate = date('Y-m-d',$today);
        $todayEnd = $today + 86400;
        $data = [];
        echo 'start:'.$todayDate.PHP_EOL;

        $outsideList = UserCompany::find()->where(['status'=>UserCompany::USING])->all(UserCompany::getDb_rd());
        $allOutside = [0];
        /** @var UserCompany $outside */
        foreach ($outsideList as $outside){
            // 初始化会减少的值
            $data[$outside->id]['current_progress_num'] = 0;
            $data[$outside->id]['current_progress_amount'] = 0;
            $data[$outside->id]['today_dispatch_num'] = 0;
            $data[$outside->id]['today_dispatch_amount'] = 0;
            $allOutside[] = $outside->id;
        }

        //当前进行中的催收订单
        $currentProgressOrder = LoanCollectionOrder::find()
            ->select(['A.outside','amount' => 'SUM(D.principal+D.interests)','num' => 'COUNT(1)'])
            ->from(LoanCollectionOrder::tableName() . ' A')
            ->leftJoin(UserLoanOrderRepayment::getDbName().'.'.UserLoanOrderRepayment::tableName() . ' D', 'A.user_loan_order_repayment_id = D.id')
            ->where(['A.status' => [LoanCollectionOrder::STATUS_WAIT_COLLECTION,
                LoanCollectionOrder::STATUS_COLLECTION_PROGRESS,LoanCollectionOrder::STATUS_COLLECTION_PROMISE]])
            ->groupBy(['A.outside'])
            ->asArray()
            ->all(LoanCollectionOrder::getDb_rd());
        foreach ($currentProgressOrder as $value){
            $data[$value['outside']]['current_progress_num'] =  $value['num'];
            $data[$value['outside']]['current_progress_amount'] =  $value['amount'];
        }

        //在当天派分的催收订单
        $todayDispatch = LoanCollectionOrder::find()
            ->select(['A.outside','amount' => 'SUM(D.principal+D.interests)','num' => 'COUNT(1)'])
            ->from(LoanCollectionOrder::tableName() . ' A')
            ->leftJoin(UserLoanOrderRepayment::getDbName().'.'.UserLoanOrderRepayment::tableName() . ' D', 'A.user_loan_order_repayment_id = D.id')
            ->where(['>=','A.dispatch_time',$today])
            ->andWhere(['<','A.dispatch_time',$todayEnd])
            ->groupBy(['A.outside'])
            ->asArray()
            ->all(LoanCollectionOrder::getDb_rd());
        foreach ($todayDispatch as $value){
            $data[$value['outside']]['today_dispatch_num'] =  $value['num'];
            $data[$value['outside']]['today_dispatch_amount'] =  $value['amount'];
        }

        //在当天完成的催收订单
        $todayFinish = LoanCollectionOrder::find()
            ->select(['A.outside','amount' => 'SUM(D.principal+D.interests)','num' => 'COUNT(1)'])
            ->from(LoanCollectionOrder::tableName() . ' A')
            ->leftJoin(UserLoanOrderRepayment::getDbName().'.'.UserLoanOrderRepayment::tableName() . ' D', 'A.user_loan_order_repayment_id = D.id')
            ->where(['A.status' => LoanCollectionOrder::STATUS_COLLECTION_FINISH, 'D.status' => UserLoanOrderRepayment::STATUS_REPAY_COMPLETE])
            ->andWhere(['>=','D.closing_time',$today])
            ->andWhere(['<','D.closing_time',$todayEnd])
            ->groupBy(['A.outside'])
            ->asArray()
            ->all(LoanCollectionOrder::getDb_rd());
        foreach ($todayFinish as $value){
            $data[$value['outside']]['today_finish_num'] =  $value['num'];
            $data[$value['outside']]['today_finish_amount'] =  $value['amount'];
        }

        //已完成的所有催收订单

        $yesterdayDate = date('Y-m-d',$today - 86400);
        $yesterdayData = OutsideDayData::find()->where(['date' => $yesterdayDate])->indexBy('outside')->groupBy(['outside'])->asArray()->all();

        if($yesterdayData){
            foreach ($allOutside as $outside){
                //昨天加上今天
                $data[$outside]['total_finish_num'] = ($yesterdayData[$outside]['total_finish_num'] ?? 0) + ($data[$outside]['today_finish_num'] ?? 0) ;
                $data[$outside]['total_finish_amount'] = ($yesterdayData[$outside]['total_finish_amount'] ?? 0) + ($data[$outside]['today_finish_amount'] ?? 0);
            }

        }else{
            //第一次跑进入
            $totalFinish = LoanCollectionOrder::find()
                ->select(['A.outside','amount' => 'SUM(D.principal+D.interests)','num' => 'COUNT(1)'])
                ->from(LoanCollectionOrder::tableName() . ' A')
                ->leftJoin(UserLoanOrderRepayment::getDbName().'.'.UserLoanOrderRepayment::tableName() . ' D', 'A.user_loan_order_repayment_id = D.id')
                ->where(['A.status' => LoanCollectionOrder::STATUS_COLLECTION_FINISH, 'D.status' => UserLoanOrderRepayment::STATUS_REPAY_COMPLETE])
                ->groupBy(['A.outside'])
                ->asArray()
                ->all(LoanCollectionOrder::getDb_rd());
            foreach ($totalFinish as $value){
                $data[$value['outside']]['total_finish_num'] =  $value['num'];
                $data[$value['outside']]['total_finish_amount'] =  $value['amount'];
            }
        }


        foreach ($data as $outside => $outsideData){
            $outsideDayData = OutsideDayData::find()->where(['date' => $todayDate,'outside' => $outside])->one();
            if(is_null($outsideDayData)){
                $outsideDayData = new OutsideDayData();
                $outsideDayData->date = $todayDate;
                $outsideDayData->outside = $outside;
            }
            foreach ($outsideData as $key => $val){
                $outsideDayData->$key = $val;
            }
            $outsideDayData->save();
        }
        echo 'END';
    }

    /**
     * 统计机构每日分派订单（机构每日订单统计》需要看每天派单后，该派单日给各个机构派的单子分账龄（S1、S2、M1、M2、M3、M3+）催回情况统计）
     */
    public function actionOutsideDayOrderData(){
        if(!$this->lock()){
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $this->printMessage('start');

        $oMerchant = Merchant::find()->all();

        foreach ($oMerchant as $item)
        {
            $merchantId = $item['id'];
            $data = [];
            $endTime = strtotime(date("Y-m-d",strtotime("+1 day")));//当天结束时间
            $startTime = strtotime(date("Y-m-d",strtotime("-180 day")));
            while ($startTime < $endTime){
                $startDate = date('Y-m-d',$startTime);
                $this->printMessage($startDate);

                $collectionOrderDispatchLogs = CollectionOrderDispatchLog::find()
                    ->select(['max(id) as mid'])
                    ->where(['>=','created_at',$startTime])
                    ->andWhere(['<','created_at',$startTime+86400])
                    ->andWhere(['=', 'merchant_id', $item->id])
                    ->groupBy(['outside','collection_order_id'])
                    ->orderBy(['created_at' => SORT_DESC])
                    ->asArray()
                    ->all();

                foreach ($collectionOrderDispatchLogs as $collectionOrderDispatchLog) {
                    /** @var CollectionOrderDispatchLog $collectionOrderDispatchLog */
                    $collectionOrderDispatchLog = CollectionOrderDispatchLog::find()->where(['id' => $collectionOrderDispatchLog['mid']])->one();
                    //分派单数
                    /** @var UserLoanOrderRepayment $repayment */
                    $repayment = UserLoanOrderRepayment::find()->where(['id' => $collectionOrderDispatchLog->order_repayment_id])->one(Yii::$app->db_read_1);

                    $data[$startDate][$collectionOrderDispatchLog->outside]['merchant_id'] = $item->id;

                    if(isset($data[$startDate][$collectionOrderDispatchLog->outside]['total_dispatch_num'])){
                        $data[$startDate][$collectionOrderDispatchLog->outside]['total_dispatch_num'] += 1;
                    }else{
                        $data[$startDate][$collectionOrderDispatchLog->outside]['total_dispatch_num'] = 1;
                    }
                    if(isset($data[$startDate][$collectionOrderDispatchLog->outside]['total_dispatch_amount'])){
                        $data[$startDate][$collectionOrderDispatchLog->outside]['total_dispatch_amount'] += $repayment->getAmountInExpiryDate();
                    }else{
                        $data[$startDate][$collectionOrderDispatchLog->outside]['total_dispatch_amount'] = $repayment->getAmountInExpiryDate();
                    }
                    //不同逾期等级分配的单数
                    if(isset($data[$startDate][$collectionOrderDispatchLog->outside]['overlevel'.$collectionOrderDispatchLog->collection_order_level.'_dispatch_num'])){
                        $data[$startDate][$collectionOrderDispatchLog->outside]['overlevel'.$collectionOrderDispatchLog->collection_order_level.'_dispatch_num'] += 1;
                    }else{
                        $data[$startDate][$collectionOrderDispatchLog->outside]['overlevel'.$collectionOrderDispatchLog->collection_order_level.'_dispatch_num'] = 1;
                    }
                    if(isset($data[$startDate][$collectionOrderDispatchLog->outside]['overlevel'.$collectionOrderDispatchLog->collection_order_level.'_dispatch_amount'])){
                        $data[$startDate][$collectionOrderDispatchLog->outside]['overlevel'.$collectionOrderDispatchLog->collection_order_level.'_dispatch_amount'] += $repayment->getAmountInExpiryDate();
                    }else{
                        $data[$startDate][$collectionOrderDispatchLog->outside]['overlevel'.$collectionOrderDispatchLog->collection_order_level.'_dispatch_amount'] = $repayment->getAmountInExpiryDate();
                    }
                    //不同逾期天数范围分配的单数
                    if($collectionOrderDispatchLog->overdue_day == 1){
                        if(isset($data[$startDate][$collectionOrderDispatchLog->outside]['overday1_dispatch_num'])){
                            $data[$startDate][$collectionOrderDispatchLog->outside]['overday1_dispatch_num'] += 1;
                        }else{
                            $data[$startDate][$collectionOrderDispatchLog->outside]['overday1_dispatch_num'] = 1;
                        }
                        if(isset($data[$startDate][$collectionOrderDispatchLog->outside]['overday1_dispatch_amount'])){
                            $data[$startDate][$collectionOrderDispatchLog->outside]['overday1_dispatch_amount'] += $repayment->getAmountInExpiryDate();
                        }else{
                            $data[$startDate][$collectionOrderDispatchLog->outside]['overday1_dispatch_amount'] = $repayment->getAmountInExpiryDate();
                        }
                    }
                    //不同逾期天数范围分配的单数 1-3
                    if($collectionOrderDispatchLog->overdue_day >= 1 && $collectionOrderDispatchLog->overdue_day <= 3 ){
                        if(isset($data[$startDate][$collectionOrderDispatchLog->outside]['overday1_3_dispatch_num'])){
                            $data[$startDate][$collectionOrderDispatchLog->outside]['overday1_3_dispatch_num'] += 1;
                        }else{
                            $data[$startDate][$collectionOrderDispatchLog->outside]['overday1_3_dispatch_num'] = 1;
                        }
                        if(isset($data[$startDate][$collectionOrderDispatchLog->outside]['overday1_3_dispatch_amount'])){
                            $data[$startDate][$collectionOrderDispatchLog->outside]['overday1_3_dispatch_amount'] += $repayment->getAmountInExpiryDate();
                        }else{
                            $data[$startDate][$collectionOrderDispatchLog->outside]['overday1_3_dispatch_amount'] = $repayment->getAmountInExpiryDate();
                        }
                    }
                    //不同逾期天数范围分配的单数 1-3
                    if($collectionOrderDispatchLog->overdue_day >= 1 && $collectionOrderDispatchLog->overdue_day <= 5 ){
                        if(isset($data[$startDate][$collectionOrderDispatchLog->outside]['overday1_5_dispatch_num'])){
                            $data[$startDate][$collectionOrderDispatchLog->outside]['overday1_5_dispatch_num'] += 1;
                        }else{
                            $data[$startDate][$collectionOrderDispatchLog->outside]['overday1_5_dispatch_num'] = 1;
                        }
                        if(isset($data[$startDate][$collectionOrderDispatchLog->outside]['overday1_5_dispatch_amount'])){
                            $data[$startDate][$collectionOrderDispatchLog->outside]['overday1_5_dispatch_amount'] += $repayment->getAmountInExpiryDate();
                        }else{
                            $data[$startDate][$collectionOrderDispatchLog->outside]['overday1_5_dispatch_amount'] = $repayment->getAmountInExpiryDate();
                        }
                    }


                    /** @var CollectionOrderDispatchLog $lastDispatchLog */
                    $lastDispatchLog = CollectionOrderDispatchLog::find()
                        ->where(['collection_order_id' => $collectionOrderDispatchLog->collection_order_id])
                        ->orderBy(['created_at' => SORT_DESC])
                        ->limit(1)
                        ->one();
                    //判断是否未最后一次派单
                    if($lastDispatchLog->id == $collectionOrderDispatchLog->id){
                        /** @var LoanCollectionOrder $loanCollectionOrder */
                        $loanCollectionOrder = LoanCollectionOrder::find()->where(['id' => $collectionOrderDispatchLog->collection_order_id])->one(LoanCollectionOrder::getDb_rd());
                        if(is_null($loanCollectionOrder)){
                            $this->printMessage('collection_order_id：'.$collectionOrderDispatchLog->collection_order_id.',不存在');
                            continue;
                        }
                        if($loanCollectionOrder->status == LoanCollectionOrder::STATUS_COLLECTION_FINISH
                            && $repayment->status == UserLoanOrderRepayment::STATUS_REPAY_COMPLETE
                            && $loanCollectionOrder->outside > 0){   //机构为0的时候不计算，此时订单在未分配下完成的,所以这里需要 != 0
                            //完成的单数
                            if(isset($data[$startDate][$collectionOrderDispatchLog->outside]['total_repay_num'])){
                                $data[$startDate][$collectionOrderDispatchLog->outside]['total_repay_num'] += 1;
                            }else{
                                $data[$startDate][$collectionOrderDispatchLog->outside]['total_repay_num'] = 1;
                            }
                            if(isset($data[$startDate][$collectionOrderDispatchLog->outside]['total_repay_amount'])){
                                $data[$startDate][$collectionOrderDispatchLog->outside]['total_repay_amount'] += $repayment->getAmountInExpiryDate();
                            }else{
                                $data[$startDate][$collectionOrderDispatchLog->outside]['total_repay_amount'] = $repayment->getAmountInExpiryDate();
                            }
                            //不同逾期等级完成的单数
                            if(isset($data[$startDate][$collectionOrderDispatchLog->outside]['overlevel'.$loanCollectionOrder->current_overdue_level.'_repay_num'])){
                                $data[$startDate][$collectionOrderDispatchLog->outside]['overlevel'.$loanCollectionOrder->current_overdue_level.'_repay_num'] += 1;
                            }else{
                                $data[$startDate][$collectionOrderDispatchLog->outside]['overlevel'.$loanCollectionOrder->current_overdue_level.'_repay_num'] = 1;
                            }
                            if(isset($data[$startDate][$collectionOrderDispatchLog->outside]['overlevel'.$loanCollectionOrder->current_overdue_level.'_repay_amount'])){
                                $data[$startDate][$collectionOrderDispatchLog->outside]['overlevel'.$loanCollectionOrder->current_overdue_level.'_repay_amount'] += $repayment->getAmountInExpiryDate();
                            }else{
                                $data[$startDate][$collectionOrderDispatchLog->outside]['overlevel'.$loanCollectionOrder->current_overdue_level.'_repay_amount'] = $repayment->getAmountInExpiryDate();
                            }

                            //不同逾期天数完成的单数
                            if($repayment->overdue_day == 1){
                                if(isset($data[$startDate][$collectionOrderDispatchLog->outside]['overday1_repay_num'])){
                                    $data[$startDate][$collectionOrderDispatchLog->outside]['overday1_repay_num'] += 1;
                                }else{
                                    $data[$startDate][$collectionOrderDispatchLog->outside]['overday1_repay_num'] = 1;
                                }
                                if(isset($data[$startDate][$collectionOrderDispatchLog->outside]['overday1_repay_amount'])){
                                    $data[$startDate][$collectionOrderDispatchLog->outside]['overday1_repay_amount'] += $repayment->getAmountInExpiryDate();
                                }else{
                                    $data[$startDate][$collectionOrderDispatchLog->outside]['overday1_repay_amount'] = $repayment->getAmountInExpiryDate();
                                }
                            }
                            if($repayment->overdue_day >= 1 && $repayment->overdue_day <= 3 ){
                                if(isset($data[$startDate][$collectionOrderDispatchLog->outside]['overday1_3_repay_num'])){
                                    $data[$startDate][$collectionOrderDispatchLog->outside]['overday1_3_repay_num'] += 1;
                                }else{
                                    $data[$startDate][$collectionOrderDispatchLog->outside]['overday1_3_repay_num'] = 1;
                                }
                                if(isset($data[$startDate][$collectionOrderDispatchLog->outside]['overday1_3_repay_amount'])){
                                    $data[$startDate][$collectionOrderDispatchLog->outside]['overday1_3_repay_amount'] += $repayment->getAmountInExpiryDate();
                                }else{
                                    $data[$startDate][$collectionOrderDispatchLog->outside]['overday1_3_repay_amount'] = $repayment->getAmountInExpiryDate();
                                }
                            }
                            if($repayment->overdue_day >= 1 && $repayment->overdue_day <= 5 ){
                                if(isset($data[$startDate][$collectionOrderDispatchLog->outside]['overday1_5_repay_num'])){
                                    $data[$startDate][$collectionOrderDispatchLog->outside]['overday1_5_repay_num'] += 1;
                                }else{
                                    $data[$startDate][$collectionOrderDispatchLog->outside]['overday1_5_repay_num'] = 1;
                                }
                                if(isset($data[$startDate][$collectionOrderDispatchLog->outside]['overday1_5_repay_amount'])){
                                    $data[$startDate][$collectionOrderDispatchLog->outside]['overday1_5_repay_amount'] += $repayment->getAmountInExpiryDate();
                                }else{
                                    $data[$startDate][$collectionOrderDispatchLog->outside]['overday1_5_repay_amount'] = $repayment->getAmountInExpiryDate();
                                }
                            }
                        }
                    }

                }

                $startTime += 86400;
            }

            foreach ($data as $date => $datum){
                foreach ($datum as $outside => $value){
                    $dispatchOutsideFinish =  DispatchOutsideFinish::find()->where(['date' => $date,'outside' => $outside,'merchant_id' => $merchantId])->one();
                    if(is_null($dispatchOutsideFinish)){
                        $dispatchOutsideFinish = new DispatchOutsideFinish();
                        $dispatchOutsideFinish->date = $date;
                        $dispatchOutsideFinish->outside = $outside;
                        $dispatchOutsideFinish->merchant_id = $merchantId;
                    }
                    foreach ($value as $key => $val){
                        $dispatchOutsideFinish->$key = $val;
                    }
                    $dispatchOutsideFinish->save();
                }
            }
        }
        //更新数
        $this->printMessage('end');
    }


    /**
     * 统计逾期天数分派订单
     */
    public function actionDispatchOverdueDayStatistics(){
        if(!$this->lock()){
            return ExitCode::UNSPECIFIED_ERROR;
        }
        ini_set('memory_limit', '1024M');
        $endTime = strtotime(date("Y-m-d",strtotime("+1 day")));//当天结束时间
        $startTime = strtotime(date("Y-m-d",strtotime("-180 day")));

        $this->printMessage('Start');
        while ($startTime < $endTime){
            $startDate = date('Y-m-d',$startTime);
            $this->printMessage($startDate);
            $data = [];

            $collectionOrderDispatchLogs = CollectionOrderDispatchLog::find()
                ->select(['max(id) as mid','order_repayment_id','collection_order_id','admin_user_id','overdue_day'])
                ->where(['type' => [CollectionOrderDispatchLog::TO_ADMIN_USER_TYPE,CollectionOrderDispatchLog::TO_JUMP_COMPANY_TO_USER_TYPE]])
                ->andWhere(['<','created_at',$startTime+86400])
                ->andWhere(['>=','created_at',$startTime])
                ->groupBy(['admin_user_id','collection_order_id'])
                ->orderBy(['created_at' => SORT_DESC])
                ->asArray()
                ->all(CollectionOrderDispatchLog::getDb_rd());
            $repaymentIdArr = [];
            $collectionOrderIdArr = [];
            foreach ($collectionOrderDispatchLogs as $collectionOrderDispatchLog) {
                $repaymentIdArr[] = $collectionOrderDispatchLog['order_repayment_id'];
                $collectionOrderIdArr[] = $collectionOrderDispatchLog['collection_order_id'];
            }
            if($collectionOrderDispatchLogs) {
                $repayments = UserLoanOrderRepayment::find()
                    ->select([
                        'A.id',
                        'A.status',
                        'A.closing_time',
                        'B.is_first',
                        'amount_in_expiry_date' => '(A.principal + A.interests)'
                    ])
                    ->from(UserLoanOrderRepayment::tableName().' A')
                    ->leftJoin(UserLoanOrder::tableName(). ' B','A.order_id = B.id')
                    ->where(['A.id' => $repaymentIdArr])
                    ->asArray()
                    ->indexBy('id')
                    ->all(Yii::$app->db_read_1);
                unset($repaymentIdArr);
                $lastDispatchLogIdsArr = CollectionOrderDispatchLog::find()->select(['max(id) as last_id'])->where(['collection_order_id' => $collectionOrderIdArr])->groupBy(['collection_order_id'])->indexBy('last_id')->asArray()->all(CollectionOrderDispatchLog::getDb_rd());
                $loanCollectionOrders = LoanCollectionOrder::find()->select(['id','status','outside'])->where(['id' => $collectionOrderIdArr])->indexBy('id')->asArray()->all(LoanCollectionOrder::getDb_rd());
                unset($collectionOrderIdArr);
                /** @var CollectionOrderDispatchLog $collectionOrderDispatchLog */
                foreach ($collectionOrderDispatchLogs as $collectionOrderDispatchLog) {
                    //分派单数
                    /** @var UserLoanOrderRepayment $repayment */
                    $repayment = $repayments[$collectionOrderDispatchLog['order_repayment_id']];
                    $isFirst = $repayment['is_first'] ?? 0;
                    if(isset($data[$collectionOrderDispatchLog['admin_user_id']][$collectionOrderDispatchLog['overdue_day']]['dispatch_count'])){
                        $data[$collectionOrderDispatchLog['admin_user_id']][$collectionOrderDispatchLog['overdue_day']]['dispatch_count'] += 1;
                    }else{
                        $data[$collectionOrderDispatchLog['admin_user_id']][$collectionOrderDispatchLog['overdue_day']]['dispatch_count'] = 1;
                    }
                    if(isset($data[$collectionOrderDispatchLog['admin_user_id']][$collectionOrderDispatchLog['overdue_day']]['dispatch_amount'])){
                        $data[$collectionOrderDispatchLog['admin_user_id']][$collectionOrderDispatchLog['overdue_day']]['dispatch_amount'] += $repayment['amount_in_expiry_date'];
                    }else{
                        $data[$collectionOrderDispatchLog['admin_user_id']][$collectionOrderDispatchLog['overdue_day']]['dispatch_amount'] = $repayment['amount_in_expiry_date'];
                    }
                    if($isFirst == UserLoanOrder::FIRST_LOAN_IS){
                        if(isset($data[$collectionOrderDispatchLog['admin_user_id']][$collectionOrderDispatchLog['overdue_day']]['new_dispatch_count'])){
                            $data[$collectionOrderDispatchLog['admin_user_id']][$collectionOrderDispatchLog['overdue_day']]['new_dispatch_count'] += 1;
                        }else{
                            $data[$collectionOrderDispatchLog['admin_user_id']][$collectionOrderDispatchLog['overdue_day']]['new_dispatch_count'] = 1;
                        }
                        if(isset($data[$collectionOrderDispatchLog['admin_user_id']][$collectionOrderDispatchLog['overdue_day']]['new_dispatch_amount'])){
                            $data[$collectionOrderDispatchLog['admin_user_id']][$collectionOrderDispatchLog['overdue_day']]['new_dispatch_amount'] += $repayment['amount_in_expiry_date'];
                        }else{
                            $data[$collectionOrderDispatchLog['admin_user_id']][$collectionOrderDispatchLog['overdue_day']]['new_dispatch_amount'] = $repayment['amount_in_expiry_date'];
                        }
                    }else{
                        if(isset($data[$collectionOrderDispatchLog['admin_user_id']][$collectionOrderDispatchLog['overdue_day']]['old_dispatch_count'])){
                            $data[$collectionOrderDispatchLog['admin_user_id']][$collectionOrderDispatchLog['overdue_day']]['old_dispatch_count'] += 1;
                        }else{
                            $data[$collectionOrderDispatchLog['admin_user_id']][$collectionOrderDispatchLog['overdue_day']]['old_dispatch_count'] = 1;
                        }
                        if(isset($data[$collectionOrderDispatchLog['admin_user_id']][$collectionOrderDispatchLog['overdue_day']]['old_dispatch_amount'])){
                            $data[$collectionOrderDispatchLog['admin_user_id']][$collectionOrderDispatchLog['overdue_day']]['old_dispatch_amount'] += $repayment['amount_in_expiry_date'];
                        }else{
                            $data[$collectionOrderDispatchLog['admin_user_id']][$collectionOrderDispatchLog['overdue_day']]['old_dispatch_amount'] = $repayment['amount_in_expiry_date'];
                        }
                    }

                    //判断是否未最后一次派单
                    if(isset($lastDispatchLogIdsArr[$collectionOrderDispatchLog['mid']])){
                        /** @var LoanCollectionOrder $loanCollectionOrder */
                        $loanCollectionOrder = $loanCollectionOrders[$collectionOrderDispatchLog['collection_order_id']];
                        if($loanCollectionOrder['status'] == LoanCollectionOrder::STATUS_COLLECTION_FINISH
                            && $repayment['status'] == UserLoanOrderRepayment::STATUS_REPAY_COMPLETE
                            && $loanCollectionOrder['outside'] > 0){   //机构为0的时候不计算，此时订单在未分配下完成的,所以这里需要 != 0

                            //分派后 完成的单数
                            if(isset($data[$collectionOrderDispatchLog['admin_user_id']][$collectionOrderDispatchLog['overdue_day']]['total_repay_count'])){
                                $data[$collectionOrderDispatchLog['admin_user_id']][$collectionOrderDispatchLog['overdue_day']]['total_repay_count'] += 1;
                            }else{
                                $data[$collectionOrderDispatchLog['admin_user_id']][$collectionOrderDispatchLog['overdue_day']]['total_repay_count'] = 1;
                            }
                            if(isset($data[$collectionOrderDispatchLog['admin_user_id']][$collectionOrderDispatchLog['overdue_day']]['total_repay_amount'])){
                                $data[$collectionOrderDispatchLog['admin_user_id']][$collectionOrderDispatchLog['overdue_day']]['total_repay_amount'] += $repayment['amount_in_expiry_date'];
                            }else{
                                $data[$collectionOrderDispatchLog['admin_user_id']][$collectionOrderDispatchLog['overdue_day']]['total_repay_amount'] = $repayment['amount_in_expiry_date'];
                            }

                            if($isFirst == UserLoanOrder::FIRST_LOAN_IS){
                                if(isset($data[$collectionOrderDispatchLog['admin_user_id']][$collectionOrderDispatchLog['overdue_day']]['new_total_repay_count'])){
                                    $data[$collectionOrderDispatchLog['admin_user_id']][$collectionOrderDispatchLog['overdue_day']]['new_total_repay_count'] += 1;
                                }else{
                                    $data[$collectionOrderDispatchLog['admin_user_id']][$collectionOrderDispatchLog['overdue_day']]['new_total_repay_count'] = 1;
                                }
                                if(isset($data[$collectionOrderDispatchLog['admin_user_id']][$collectionOrderDispatchLog['overdue_day']]['new_total_repay_amount'])){
                                    $data[$collectionOrderDispatchLog['admin_user_id']][$collectionOrderDispatchLog['overdue_day']]['new_total_repay_amount'] += $repayment['amount_in_expiry_date'];
                                }else{
                                    $data[$collectionOrderDispatchLog['admin_user_id']][$collectionOrderDispatchLog['overdue_day']]['new_total_repay_amount'] = $repayment['amount_in_expiry_date'];
                                }
                            }else{
                                if(isset($data[$collectionOrderDispatchLog['admin_user_id']][$collectionOrderDispatchLog['overdue_day']]['old_total_repay_count'])){
                                    $data[$collectionOrderDispatchLog['admin_user_id']][$collectionOrderDispatchLog['overdue_day']]['old_total_repay_count'] += 1;
                                }else{
                                    $data[$collectionOrderDispatchLog['admin_user_id']][$collectionOrderDispatchLog['overdue_day']]['old_total_repay_count'] = 1;
                                }
                                if(isset($data[$collectionOrderDispatchLog['admin_user_id']][$collectionOrderDispatchLog['overdue_day']]['old_total_repay_amount'])){
                                    $data[$collectionOrderDispatchLog['admin_user_id']][$collectionOrderDispatchLog['overdue_day']]['old_total_repay_amount'] += $repayment['amount_in_expiry_date'];
                                }else{
                                    $data[$collectionOrderDispatchLog['admin_user_id']][$collectionOrderDispatchLog['overdue_day']]['old_total_repay_amount'] = $repayment['amount_in_expiry_date'];
                                }
                            }

                            //判断当天分派完成的
                            if(date('Y-m-d',$repayment['closing_time']) == $startDate){
                                if(isset($data[$collectionOrderDispatchLog['admin_user_id']][$collectionOrderDispatchLog['overdue_day']]['today_repay_count'])){
                                    $data[$collectionOrderDispatchLog['admin_user_id']][$collectionOrderDispatchLog['overdue_day']]['today_repay_count'] += 1;
                                }else{
                                    $data[$collectionOrderDispatchLog['admin_user_id']][$collectionOrderDispatchLog['overdue_day']]['today_repay_count'] = 1;
                                }
                                if(isset($data[$collectionOrderDispatchLog['admin_user_id']][$collectionOrderDispatchLog['overdue_day']]['today_repay_amount'])){
                                    $data[$collectionOrderDispatchLog['admin_user_id']][$collectionOrderDispatchLog['overdue_day']]['today_repay_amount'] += $repayment['amount_in_expiry_date'];
                                }else{
                                    $data[$collectionOrderDispatchLog['admin_user_id']][$collectionOrderDispatchLog['overdue_day']]['today_repay_amount'] = $repayment['amount_in_expiry_date'];
                                }

                                if($isFirst == UserLoanOrder::FIRST_LOAN_IS){
                                    if(isset($data[$collectionOrderDispatchLog['admin_user_id']][$collectionOrderDispatchLog['overdue_day']]['new_today_repay_count'])){
                                        $data[$collectionOrderDispatchLog['admin_user_id']][$collectionOrderDispatchLog['overdue_day']]['new_today_repay_count'] += 1;
                                    }else{
                                        $data[$collectionOrderDispatchLog['admin_user_id']][$collectionOrderDispatchLog['overdue_day']]['new_today_repay_count'] = 1;
                                    }
                                    if(isset($data[$collectionOrderDispatchLog['admin_user_id']][$collectionOrderDispatchLog['overdue_day']]['new_today_repay_amount'])){
                                        $data[$collectionOrderDispatchLog['admin_user_id']][$collectionOrderDispatchLog['overdue_day']]['new_today_repay_amount'] += $repayment['amount_in_expiry_date'];
                                    }else{
                                        $data[$collectionOrderDispatchLog['admin_user_id']][$collectionOrderDispatchLog['overdue_day']]['new_today_repay_amount'] = $repayment['amount_in_expiry_date'];
                                    }
                                }else{
                                    if(isset($data[$collectionOrderDispatchLog['admin_user_id']][$collectionOrderDispatchLog['overdue_day']]['old_today_repay_count'])){
                                        $data[$collectionOrderDispatchLog['admin_user_id']][$collectionOrderDispatchLog['overdue_day']]['old_today_repay_count'] += 1;
                                    }else{
                                        $data[$collectionOrderDispatchLog['admin_user_id']][$collectionOrderDispatchLog['overdue_day']]['old_today_repay_count'] = 1;
                                    }
                                    if(isset($data[$collectionOrderDispatchLog['admin_user_id']][$collectionOrderDispatchLog['overdue_day']]['old_today_repay_amount'])){
                                        $data[$collectionOrderDispatchLog['admin_user_id']][$collectionOrderDispatchLog['overdue_day']]['old_today_repay_amount'] += $repayment['amount_in_expiry_date'];
                                    }else{
                                        $data[$collectionOrderDispatchLog['admin_user_id']][$collectionOrderDispatchLog['overdue_day']]['old_today_repay_amount'] = $repayment['amount_in_expiry_date'];
                                    }
                                }
                            }
                        }
                    }

                }
            }

            foreach ($data as $adminUserId => $overdueData){
                foreach ($overdueData as $overdueDay => $value){
                    $dispatchOverdueDaysFinish =  DispatchOverdueDaysFinish::find()->where([
                            'date' => $startDate,
                            'admin_user_id' => $adminUserId,
                            'overdue_day' => $overdueDay
                        ]
                    )->one();
                    if(is_null($dispatchOverdueDaysFinish)){
                        $dispatchOverdueDaysFinish = new DispatchOverdueDaysFinish();
                        $dispatchOverdueDaysFinish->date = $startDate;
                        $dispatchOverdueDaysFinish->admin_user_id = $adminUserId;
                        $dispatchOverdueDaysFinish->overdue_day = $overdueDay;
                    }
                    foreach ($value as $key => $val){
                        $dispatchOverdueDaysFinish->$key = $val;
                    }
                    $dispatchOverdueDaysFinish->save();
                }
            }
            $startTime += 86400;
        }

        //更新数
        $this->printMessage('End');
    }


    /**
     * app出勤率统计脚本
     */
    public function actionCollectorAppAttendance(){
        if(!$this->lock()){
            return ExitCode::UNSPECIFIED_ERROR;
        }
        $this->printMessage('Start');
        $date = date('Y-m-d');
        $this->printMessage($date);
        $todayTime = strtotime('today');

        $collectorRoles = AdminUserRole::getRolesByGroup(AdminUserRole::TYPE_COLLECTION);
        if(empty($collectorRoles)){
            $this->printMessage('没有可分派角色');
            return ExitCode::UNSPECIFIED_ERROR;
        }

        if(date('H',time()) < 1){ //1点之前统计总人数
            $allCollectorList = AdminUser::find()
                ->select([
                    'outside',
                    'group',
                    'group_game',
                    'total_num' => 'COUNT(1)'
                ])
                ->where(['open_status' => AdminUser::$usable_status,'role' => $collectorRoles])
                ->groupBy(['outside','group','group_game'])
                ->asArray()
                ->all(AdminUser::getDb_rd());
            foreach ($allCollectorList as $value){
                $collectorAttendanceDayData = CollectorAttendanceDayData::find()->where([
                    'date' => $date,
                    'outside' => $value['outside'],
                    'group' => $value['group'],
                    'group_game' => $value['group_game']
                ])->one();
                if(!$collectorAttendanceDayData){
                    $collectorAttendanceDayData = new CollectorAttendanceDayData();
                    $collectorAttendanceDayData->date = $date;
                    $collectorAttendanceDayData->outside = $value['outside'];
                    $collectorAttendanceDayData->group = $value['group'];
                    $collectorAttendanceDayData->group_game = $value['group_game'];
                }
                $collectorAttendanceDayData->total_num = $value['total_num'];
                $collectorAttendanceDayData->save();
            }

        }else{
            //今天添加的计算在内 更新出勤
            $allCollectorList = AdminUser::find()
                ->select([
                    'outside',
                    'group',
                    'group_game',
                    'total_num' => 'COUNT(1)'
                ])
                ->where(['role' => $collectorRoles])
                ->andWhere(['>','created_at',$todayTime + 3600])
                ->andWhere(['<','created_at',$todayTime + 86400])
                ->groupBy(['outside','group','group_game'])
                ->asArray()
                ->all(AdminUser::getDb_rd());
            foreach ($allCollectorList as $value){
                $collectorAttendanceDayData = CollectorAttendanceDayData::find()->where([
                    'date' => $date,
                    'outside' => $value['outside'],
                    'group' => $value['group'],
                    'group_game' => $value['group_game']
                ])->one();
                if(!$collectorAttendanceDayData){
                    $collectorAttendanceDayData = new CollectorAttendanceDayData();
                    $collectorAttendanceDayData->date = $date;
                    $collectorAttendanceDayData->outside = $value['outside'];
                    $collectorAttendanceDayData->group = $value['group'];
                    $collectorAttendanceDayData->group_game = $value['group_game'];
                }
                $collectorAttendanceDayData->today_add_num = $value['total_num'];
                $collectorAttendanceDayData->save();
            }

            $attendanceList = CollectionCheckinLog::find()
                ->select([
                    'B.outside',
                    'B.group',
                    'B.group_game',
                    'attendance_num' => 'COUNT(DISTINCT(A.user_id))'
                ])
                ->from(CollectionCheckinLog::tableName(). ' A')
                ->leftJoin(AdminUser::tableName(). ' B','A.user_id = B.id')
                ->groupBy(['B.outside','B.group','B.group_game'])
                ->where(['A.type' => CollectionCheckinLog::TYPE_START_WORK,'B.role' => $collectorRoles])
                ->andWhere(['>=','A.created_at',strtotime($date)])
                ->andWhere(['<','A.created_at',strtotime($date) + 86400])
                ->asArray()
                ->all(AdminUser::getDb_rd());

            foreach ($attendanceList as $val){
                /** @var CollectorAttendanceDayData $collectorAttendanceDayData */
                $collectorAttendanceDayData = CollectorAttendanceDayData::find()->where([
                    'date' => $date,
                    'outside' => $val['outside'],
                    'group' => $val['group'],
                    'group_game' => $val['group_game']
                ])->one();
                if($collectorAttendanceDayData){
                    $collectorAttendanceDayData->attendance_num = $val['attendance_num'];
                    $collectorAttendanceDayData->save();
                }
            }
        }

        //更新数
        $this->printMessage('End');
    }

    /**
     * 每日催回金额脚本(部分还款，金额也算在内)
     */
    public function actionCollectorBackDayData($startDate = '', $endDate = ''){
        if(!$this->lock()){
            return ExitCode::UNSPECIFIED_ERROR;
        }
        $this->printMessage('Start');
        if(empty($startDate)){
            $leftTime = strtotime("today");
            if(date('H',time()) == 0 && date('i',time()) < 20){ //更新前一天的
                $leftTime -= 86400;
            }
        }else{
            $leftTime = strtotime($startDate);
        }
        if(empty($endDate)){
            $rightTime = $leftTime + 86400;
        }else{
            $rightTime = strtotime($endDate) + 86400;
        }
        $leftTime = max($leftTime,strtotime('2020-05-01'));
        while($rightTime > $leftTime){
            $today = date('Y-m-d',$leftTime);
            $this->printMessage($today);
            $data = [];
            $repaymentLog = UserRepaymentLog::find()
                ->select(['collector_id','today_money' => 'SUM(amount)'])
                ->where(['>','collector_id',0])
                ->andWhere(['>=','success_time',$leftTime])
                ->andWhere(['<','success_time',$leftTime + 86400])
                ->groupBy(['collector_id'])
                ->asArray()
                ->all();
            foreach ($repaymentLog as $item){
                $data[$item['collector_id']]['back_money'] = $item['today_money'];
            }
            $repaymentDelayLog = UserRepaymentLog::find()
                ->select([
                    'collector_id',
                    'today_money' => 'SUM(amount)',
                    'delay_order_count' => 'COUNT(DISTINCT(order_id))',
                ])
                ->where(['is_delay_repayment' => UserRepaymentLog::IS_DELAY_YES])
                ->andWhere(['>','collector_id',0])
                ->andWhere(['>=','success_time',$leftTime])
                ->andWhere(['<','success_time',$leftTime + 86400])
                ->groupBy(['collector_id'])
                ->asArray()
                ->all();
            foreach ($repaymentDelayLog as $item){
                $data[$item['collector_id']]['delay_money'] = $item['today_money'];
                $data[$item['collector_id']]['delay_order_count'] = $item['delay_order_count'];
            }
            $orderExtendLog = UserLoanOrderExtendLog::find()
                ->select([
                    'collector_id',
                    'today_money' => 'SUM(amount)',
                    'extend_order_count' => 'COUNT(DISTINCT(order_id))'
                ])
                ->where(['>','collector_id',0])
                ->andWhere(['>=','created_at',$leftTime])
                ->andWhere(['<','created_at',$leftTime + 86400])
                ->groupBy(['collector_id'])
                ->asArray()
                ->all();
            foreach ($orderExtendLog as $item){
                $data[$item['collector_id']]['extend_money'] = $item['today_money'];
                $data[$item['collector_id']]['extend_order_count'] = $item['extend_order_count'];
            }

            $finishOrder = LoanCollectionOrder::find()
                ->select(['A.current_collection_admin_user_id','finish_order_count' => 'COUNT(1)'])
                ->from(LoanCollectionOrder::tableName().' A')
                ->leftJoin(UserLoanOrderRepayment::getDbName().'.'.UserLoanOrderRepayment::tableName().' B','A.user_loan_order_repayment_id = B.id')
                ->where(['A.status' => LoanCollectionOrder::STATUS_COLLECTION_FINISH,'B.status' => UserLoanOrderRepayment::STATUS_REPAY_COMPLETE])
                ->andWhere(['>','A.current_collection_admin_user_id',0])
                ->andWhere(['>=','B.closing_time',$leftTime])
                ->andWhere(['<','B.closing_time',$leftTime + 86400])
                ->groupBy(['A.current_collection_admin_user_id'])
                ->asArray()
                ->all();
            foreach ($finishOrder as $item){
                $data[$item['current_collection_admin_user_id']]['finish_order_count'] = $item['finish_order_count'];
            }

            foreach ($data as $collectorId => $value){
                /** @var CollectorBackMoney $collectorBackMoney */
                $collectorBackMoney = CollectorBackMoney::find()
                    ->where(['date' => $today,'admin_user_id' => $collectorId])->one();
                if(!$collectorBackMoney){
                    $collectorBackMoney = new CollectorBackMoney();
                    $collectorBackMoney->date = $today;
                    $collectorBackMoney->admin_user_id = $collectorId;
                }
                foreach ($value as $k => $val){
                    $collectorBackMoney->$k = $val;
                }
                $collectorBackMoney->save();
            }
            $leftTime += 86400;
        }
        $this->printMessage('结束');
    }

    public function actionCollectorSalaryCalcBackend($beginDate, $endDate)
    {
        $s = new BackendReportService();
        $s->collectorSalaryCalcBackend($beginDate, $endDate);
    }


    /**
     * 班表计划操作回收订单队列
     */
    public function actionBackClassScheduleOrder()
    {
        if (!$this->lock())
        {
            return ExitCode::UNSPECIFIED_ERROR;
        }
        $startTime = time();
        while (true) {
            if (time() - $startTime > 300) {
                $this->printMessage('运行满5分钟，关闭当前脚本');
                exit;
            }
            $data = RedisQueue::pop([RedisQueue::SCHEDULE_LOAN_COLLECTION_ORDER_BACK_LIST]);
            if(!$data)
            {
                $this->printMessage('没有可处理的数据，继续等待');
                sleep(2);
                continue;
            }

            $data = json_decode($data, true);

            /** @var LoanCollectionOrder $loanCollectionOrder */
            $loanCollectionOrder = LoanCollectionOrder::find()->where([
                'id' => $data['collection_order_id'],
                'current_collection_admin_user_id' => $data['collector_id'],
                'status' => LoanCollectionOrder::$not_end_status
            ])->one();

            if($loanCollectionOrder){
                $loanCollectionService = new LoanCollectionService();
                $res = $loanCollectionService->collectionBack($loanCollectionOrder);
                if($res['code'] == 0){
                    $this->printMessage('处理：'.$data['collector_id'] .'_'. $data['collection_order_id'].'成功');
                }else{
                    $this->printMessage('处理：'.$data['collector_id'] .'_'. $data['collection_order_id'].'失败：'.$res['message']);
                }
            }
        }
    }

    /**
     * 缺勤申请审核
     */
    public function actionAuditAbsence()
    {
        if (!$this->lock())
        {
            return ExitCode::UNSPECIFIED_ERROR;
        }
        $begin_absence = AbsenceApply::find()
            ->where(['status'=>AbsenceApply::STATUS_WAIT])
            ->andWhere(['<=','created_at', time() - 1800])
            ->asArray()
            ->all();

        if($begin_absence){
            foreach ($begin_absence as $v){
                $absenceModel = AbsenceApply::findOne($v['id']);
                $absenceModel->status = AbsenceApply::STATUS_NO;
                $absenceModel->save();
            }
        }

        $finish_absence = AbsenceApply::find()
            ->where(['status'=>AbsenceApply::STATUS_YES,'finish_status'=>AbsenceApply::STATUS_WAIT])
            ->andWhere(['<=','updated_at', time() - 1800])
            ->asArray()
            ->all();

        if($finish_absence){
            foreach($finish_absence as $v){
                $absenceModel = AbsenceApply::findOne($v['id']);
                $absenceModel->finish_status = AbsenceApply::TYPE_TEAM;
                $absenceModel->save();

                //写入班表
                $collectorClassSchedule = CollectorClassSchedule::find()->where(['admin_id' => $v['collector_id'],'date' => $v['date']])->one();
                if($collectorClassSchedule){
                    $collectorClassSchedule->type = $v['type'];
                    $collectorClassSchedule->status = CollectorClassSchedule::STATUS_OPEN;
                    $collectorClassSchedule->save();
                }else{
                    $collectorClassSchedule = new CollectorClassSchedule();
                    $collectorClassSchedule->date = $v['date'];
                    $collectorClassSchedule->admin_id = $v['collector_id'];
                    $collectorClassSchedule->type = $v['type'];
                    $collectorClassSchedule->status = CollectorClassSchedule::STATUS_OPEN;
                    $collectorClassSchedule->save();
                }
            }
        }
        $this->printMessage('执行结束');
    }

    /**
     * 缺勤回收和分派
     */
    public function actionAbsenceRecycleDispatch()
    {
        if (!$this->lock())
        {
            return ExitCode::UNSPECIFIED_ERROR;
        }
        $this->printMessage('开始执行脚本');
        $startTime = time();
        while (true) {
            if (time() - $startTime > 300) {
                $this->printMessage('运行满5分钟，关闭当前脚本');
                exit;
            }
            $absence = AbsenceApply::find()
                ->where(['execute_status'=>AbsenceApply::EXECUTE_NO, 'date'=>date('Y-m-d',time())])
                ->andWhere(['>', 'finish_status', AbsenceApply::STATUS_NO])
                ->orderBy(['id' => SORT_ASC])
                ->asArray()
                ->all();

            if(!$absence){
                $this->printMessage('没有可处理的数据，继续等待');
                sleep(5);
                continue;
            }
            $loanCollectionService = new LoanCollectionService();
            foreach ($absence as $v){
                $adminUser = AdminUser::findOne($v['collector_id']);
                $userAry = array();
                $dispatch_time = '';
                //只有离职的抽所有单
                if(CollectorClassSchedule::RESIGNATION_TYPE != $v['type']){
                    $dispatch_time = strtotime('today');
                }
                $loanCollectionOrder = LoanCollectionOrder::find()
                    ->where([
                        'current_collection_admin_user_id' => $v['collector_id'],
                        'status' => LoanCollectionOrder::$not_end_status])
                    ->andFilterWhere(['>=','dispatch_time',$dispatch_time])
                    ->asArray()->all();
                $order_num = count($loanCollectionOrder);
                foreach($loanCollectionOrder as $vv){
                    $order = LoanCollectionOrder::findOne($vv['id']);
                    $res = $loanCollectionService->collectionBack($order);
                    if($res['code'] == 0){
                        $this->printMessage('处理：'.$v['collector_id'] .'_'.$vv['id'].'成功');
                    }else{
                        $this->printMessage('处理：'.$v['collector_id'] .'_'.$vv['id'].'失败：'.$res['message']);
                    }
                }

                if(AbsenceApply::TYPE_PERSON == $v['finish_status']){
                    $userAry = explode(',',$v['to_person']);
                }
                elseif(AbsenceApply::TYPE_TEAM == $v['finish_status']){
                    //查询所有组员
                    $team = AdminUser::find()
                        ->where(['open_status'=>[AdminUser::OPEN_STATUS_LOCK,AdminUser::OPEN_STATUS_ON], 'outside' => $adminUser->outside, 'group'=> $adminUser->group, 'group_game'=>$adminUser->group_game, 'role' => 'collection'])
                        ->andWhere(['!=','id',$adminUser->id])
                        ->asArray()->all();
                    foreach($team as $value){
                        $absent = CollectorClassSchedule::find()
                            ->where([
                                'date'=>$v['date'], 'admin_id'=>$value['id'],
                                'status'=>CollectorClassSchedule::STATUS_OPEN])
                            ->exists();
                        if(!$absent){
                            $userAry[] = $value['id'];
                        }
                    }
                }else{
                    //查询相同账龄和机构人员
                    $outside = AdminUser::find()
                        ->where(['open_status'=>[AdminUser::OPEN_STATUS_LOCK,AdminUser::OPEN_STATUS_ON], 'outside' => $adminUser->outside, 'group'=> $adminUser->group, 'role' => 'collection'])
                        ->andWhere(['!=','id',$adminUser->id])
                        ->asArray()->all();
                    foreach($outside as $value){
                        $absent = CollectorClassSchedule::find()
                            ->where([
                                'date'=>$v['date'], 'admin_id'=>$value['id'],
                                'status'=>CollectorClassSchedule::STATUS_OPEN])
                            ->exists();
                        if(!$absent){
                            $userAry[] = $value['id'];
                        }
                    }
                }
                $collector_num = count($userAry);
                shuffle($userAry);
                if($order_num > 0){
                    for($i=0; $i<$order_num; $i++){
                        $j = $i % $collector_num;
                        $res = $loanCollectionService->dispatchToOperator($loanCollectionOrder[$i]['id'],$userAry[$j]);
                        if($res['code'] != LoanCollectionService::SUCCESS_CODE){
                            $this->printMessage($res['message']);
                        }
                    }
                }
                $absenceApply = AbsenceApply::findOne($v['id']);
                $absenceApply->execute_status = AbsenceApply::EXECUTE_YES;
                $absenceApply->save();

                //离职修改账号状态
                if(CollectorClassSchedule::RESIGNATION_TYPE == $v['type']){
                    $adminUser = AdminUser::findOne($v['collector_id']);
                    $adminUser->open_status = AdminUser::OPEN_STATUS_OFF;
                    $adminUser->save(false);
                }

            }
        }
    }

    /**
     * 每天根据班表开始更新副手权限，即添加副手权限标识
     */
    public function actionUpdateDeputyUserRole(){
        $this->printMessage('START');
        $todayDate = date('Y-m-d');
        $tomorrowTime = strtotime('today') + 86400;
        $roles = AdminUserRole::getRolesByGroup(AdminUserRole::$team_leader_groups);
        //当天休息的组长
        $list = CollectorClassSchedule::find()
            ->alias('c')
            ->select(['c.date','c.admin_id','c.status','c.type','c.remark'])
            ->leftJoin(AdminUser::tableName().' u','c.admin_id = u.id')
            ->where([
                'c.status' => CollectorClassSchedule::STATUS_OPEN,
                'u.role' => $roles,
                'c.date' => $todayDate,
                'c.type' => CollectorClassSchedule::$absence_type_back_today_list
            ])
            ->asArray()
            ->all();

        foreach ($list as $item){
            //该组长是否有副手
            /** @var AdminUserMasterSlaverRelation $adminUserMasterSlaverRelation */
            $adminUserMasterSlaverRelation = AdminUserMasterSlaverRelation::find()
                ->where(['admin_id' => $item['admin_id']])
                ->andWhere(['>','slave_admin_id',0])
                ->one();
            if($adminUserMasterSlaverRelation){
                //有副手
                //给予其权限标识
                $cacheKey = sprintf('%s:%s:%s', RedisQueue::TEAM_LEADER_SLAVER_CACHE, $todayDate, $adminUserMasterSlaverRelation->slave_admin_id);
                $this->printMessage($cacheKey);
                RedisQueue::set([
                    'expire' => $tomorrowTime - time(),
                    'key'    => $cacheKey,
                    'value'  => $adminUserMasterSlaverRelation->admin_id,
                ]);

            }
        }
        $this->printMessage('END');
    }
}

<?php

namespace console\controllers;

use backend\models\remind\RemindLog;
use backend\models\remind\RemindOrder;
use callcenter\models\loan_collection\LoanCollectionRecord;
use common\helpers\CommonHelper;
use common\helpers\RedisDelayQueue;
use common\helpers\RedisQueue;
use common\models\ClientInfoLog;
use common\models\coupon\UserCouponInfo;
use common\models\enum\kudos\LoanStatus;
use common\models\enum\kudos\NoteIssueType;
use common\models\enum\kudos\ValidationStatus;
use common\models\enum\mg_user_content\UserContentType;
use common\models\fund\LoanFund;
use common\models\fund\LoanFundDayQuota;
use common\models\GlobalSetting;
use common\models\kudos\LoanKudosOrder;
use common\models\kudos\LoanKudosTranche;
use common\models\manual_credit\ManualCreditLog;
use common\models\message\ExternalOrderMessageForm;
use common\models\order\UserLoanOrder;
use common\models\order\UserLoanOrderRepayment;
use common\models\package\PackageSetting;
use common\models\risk\RiskBlackList;
use common\models\risk\RiskResultSnapshot;
use common\models\risk\RuleVersion;
use common\models\user\LoanPerson;
use common\models\user\LoanPersonExternal;
use common\services\fund\FundService;
use common\services\KudosService;
use common\services\loan_collection\LoanCollectionOrderService;
use common\services\message\FirebasePushService;
use common\services\message\WeWorkService;
use common\services\order\ExternalOrderPushData;
use common\services\order\OrderExtraService;
use common\services\order\OrderService;
use common\services\order\PushOrderRiskService;
use common\services\repayment\RepaymentService;
use common\services\risk\RiskBlackListService;
use common\services\risk\RiskDataDemoService;
use common\services\risk\RiskTreeService;
use common\services\user\UserCreditLimitService;
use common\services\user\UserOverdueContactService;
use common\services\message\SendMessageService;
use common\helpers\MessageHelper;
use yii\db\Exception;
use yii;
use yii\console\ExitCode;

class OrderController extends BaseController {

    /**
     * 展期过期
     */
    public function actionExtendExpiry()
    {
        if(false && !$this->lock())
        {
            return;
        }
        $today = date('Y-m-d');
        $this->printMessage('脚本开始');

        $start_id = 0;
        $sql = UserLoanOrderRepayment::find()
            ->from(UserLoanOrderRepayment::tableName().' as a')
            ->select('id')
            ->where(['!=', 'status', UserLoanOrderRepayment::STATUS_REPAY_COMPLETE])
            ->andWhere(['is_extend' => UserLoanOrderRepayment::IS_EXTEND_YES])
            ->andWhere(['<', 'extend_end_date', $today]);

        $query = clone $sql;

        $all_ids = $query->andWhere(['>', 'id', $start_id])
            ->orderBy(['id' => SORT_ASC])->asArray()
            ->limit(5000)->all();

        if(empty($all_ids))
        {
            $this->printMessage('无匹配数据，关闭脚本');
            exit;
        }

        while($all_ids){
            foreach($all_ids as $id){
                $id = $id['id'];
                $item = UserLoanOrderRepayment::findOne(['id'=>$id]);
                $status = $item->status;
                $this->printMessage("订单号:{$item->order_id} 开始执行");
                if (in_array($status, [UserLoanOrderRepayment::STATUS_REPAY_COMPLETE]))
                {
                    $this->printMessage( "订单号:{$item->order_id},该订单已还款，不需要更新");
                    continue;
                }

                if(UserLoanOrderRepayment::IS_EXTEND_NO == $item->is_extend)
                {
                    $this->printMessage( "订单号:{$item->order_id},该订单展期状态已失效，不需要更新");
                    continue;
                }

                $item->is_extend = UserLoanOrderRepayment::IS_EXTEND_NO;
                $item->save();

                //重新入催
                $this->printMessage("订单号".$item->order_id.", 还款id:".$item->id.",推入队列");
                LoanCollectionOrderService::pushOverdueOrder($item->id,$item->overdue_day,true);

            }
            $start_id = $id;
            $query = clone $sql;
            $all_ids = $query->andWhere(['>', 'id', $start_id])
                ->orderBy(['id' => SORT_ASC])->asArray()
                ->limit(5000)->all();
        }

        $this->printMessage('脚本结束');

    }

    /**
     * 滞纳金计算脚本
     */
    public function actionCalcInterest($orderId = null){
        if(false && !$this->lock())
        {
            return;
        }
        $today = strtotime(date("Y-m-d",time())); // 今天

        $this->printMessage('脚本开始');

        $start_id = 0;
        $sql = UserLoanOrderRepayment::find()
            ->from(UserLoanOrderRepayment::tableName().' as a')
            ->select('id')
            ->where(['!=', 'status', UserLoanOrderRepayment::STATUS_REPAY_COMPLETE])
            ->andWhere(['is_extend' => UserLoanOrderRepayment::IS_EXTEND_NO])
            ->andWhere(['<=', 'interest_time', $today])
            ->andWhere(['<=', 'plan_fee_time', $today]);
        if(!is_null($orderId))
        {
            $sql->andWhere(['a.order_id' => $orderId]);
        }
        $query = clone $sql;

        $all_ids = $query->andWhere(['>', 'id', $start_id])
            ->orderBy(['id' => SORT_ASC])->asArray()
            ->limit(5000)->all();

        if(empty($all_ids))
        {
            $this->printMessage('无匹配数据，关闭脚本');
            exit;
        }
        while($all_ids){
            foreach($all_ids as $id){
                $id = $id['id'];
                $item = UserLoanOrderRepayment::findOne(['id'=>$id]);
                $status = $item->status;
                $this->printMessage("订单号:{$item->order_id} 开始执行");
                if (in_array($status, [UserLoanOrderRepayment::STATUS_REPAY_COMPLETE]))
                {
                    $this->printMessage( "订单号:{$item->order_id},该订单已还款，不需要更新");
                    continue;
                }
                if ($today == strtotime(date('Y-m-d',$item->interest_time)) && $today < $item->interest_time)
                {

                    $this->printMessage( "订单号:{$item->order_id},该利息今天已经更新过，不需要更新");
                    continue;
                }

                $plan_fee_time = strtotime(date('Y-m-d',$item->plan_fee_time));
                if ($plan_fee_time > $today) //逾期计息
                {
                    $this->printMessage( "订单号:{$item->order_id},未到计息时间，跳过");
                    continue;
                }
                $transaction = Yii::$app->db->beginTransaction();
                try{
                    $overdue_fee = $operate_money = $item->calcOverdueFee();
                    $interest_time = time();
                    $maxOverdueDay = (strtotime(date('Y-m-d',time()))-strtotime(date('Y-m-d',$item->plan_repayment_time)))/24/3600;
                    $overdueDay = $item -> overdue_day + 1;
                    $item->interest_time = $interest_time;
                    $item->is_overdue = UserLoanOrderRepayment::IS_OVERDUE_YES;
                    $item->overdue_day = min($overdueDay,$maxOverdueDay);
                    $item->overdue_fee = $item->overdue_fee + $overdue_fee;
                    $item->total_money = $item->total_money + $overdue_fee;
                    $isStopOver = false;
                    if($item->is_delay_repayment == UserLoanOrderRepayment::IS_DELAY_YES){
                        //已延期
                        if($item->delay_repayment_time < $today){
                            //已延期且到期,更新延期状态，重置在催逾期天数,并入是s1
                            $item->is_delay_repayment = UserLoanOrderRepayment::IS_DELAY_NO;
                            $item->collection_overdue_day = 1;
                            $isStopOver = true;
                        }
                    }else{
                        //正常未延期  计算在催逾期天数
                        $item->collection_overdue_day = $item->collection_overdue_day + 1;
                    }
                    if (!$item->save())
                    {
                        throw new Exception('UserLoanOrderRepayment保存失败');
                    }
                    $this->printMessage("订单号:{$item->order_id},执行完成");
                    //逾期天数达到更新等级时 入催收待回收订单队列
                    $this->printMessage("订单号".$item->order_id.", 还款id:".$item->id.",推入队列");

                    LoanCollectionOrderService::pushOverdueOrder($item->id,$item->overdue_day,$isStopOver);
                    //逾期当天 添加逾期联系人记录
                    if( 1 == $item->overdue_day ){
                        $this->printMessage("订单号:{$item->order_id} 添加紧急联系人入逾期关联表");
                        $service = new UserOverdueContactService();
                        $service->addContact($item->order_id);
                    }

                    if(30 == $item->overdue_day){
                        RedisQueue::push([RedisQueue::RISK_BLACK_LIST, $item->user_id]);
                    }

                    //逾期通知
                    $userLoanOrder = UserLoanOrder::findOne($item->order_id);
                    $fundService = new FundService();
                    $fundService->overdueNotify($userLoanOrder);

                    $params = [
                        'user_id'      => $userLoanOrder->user_id,
                        'order_id'     => $userLoanOrder->id,
                        'app_name'     => $userLoanOrder->clientInfoLog->package_name,
                        'overdue_fee'  => $item->overdue_fee,
                        'overdue_day'  => $item->overdue_day,
                        'total_money'  => $item->total_money,
                        'data_version' => time(),
                    ];
                    RedisQueue::push([RedisQueue::PUSH_ORDER_OVERDUE_DATA, json_encode($params)]);

                    RedisQueue::push([RedisQueue::REMIND_ORDER_CHANGE_STATUS, json_encode(['id' => $item->id, 'status' => RemindOrder::STATUS_IS_OVERDUE])]);

                    $transaction->commit();

                    if($item->is_push_assist == UserLoanOrderRepayment::IS_PUSH_ASSIST_NO){
                        //入催
                        RedisQueue::push([RedisQueue::PUSH_ORDER_ASSIST_APPLY, $item->id]);
                    }else{
                        //更新逾期信息
                        RedisQueue::push([RedisQueue::PUSH_ORDER_ASSIST_OVERDUE, $item->id]);
                    }
                }catch (\Exception $e){
                    $this->printMessage("执行异常，文件:{$e->getFile()}, 行号:{$e->getLine()},异常信息:{$e->getMessage()}");
                    $transaction->rollBack();
                }
            }
            $start_id = $id;
            $all_ids = $query->andWhere(['>', 'id', $start_id])
                ->orderBy(['id' => SORT_ASC])->asArray()
                ->limit(5000)->all();
        }

        $this->printMessage('脚本结束');

    }


    /**
     * 逾期30+加入黑名单
     * @return int
     * @throws \Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function actionAddRiskBlackList()
    {
        if (!$this->lock())
        {
            return ExitCode::UNSPECIFIED_ERROR;
        }
        $now = time();
        while (true) {
            if (time() - $now > 300) {
                $this->printMessage('运行满5分钟，关闭当前脚本');
                exit;
            }

            $id = RedisQueue::pop([RedisQueue::RISK_BLACK_LIST]);
            if(empty($id)){
                $this->printMessage('没有可处理的数据，继续等待');
                exit;
            }

            /** @var LoanPerson $item */
            $item = LoanPerson::findOne($id);
            if(empty($item)){
                $this->printMessage("user_id:{$id}不存在");
                continue;
            }

            $blackList = RiskBlackList::findOne(['user_id' => $id]);
            if (!empty($blackList)) {
                if($blackList->black_status == RiskBlackList::STATUS_YES){
                    $this->printMessage("user_id：{$id}, 已经在黑名单中，跳过");
                    continue;
                }
            }else{
                $blackList = new RiskBlackList();
            }
            $blackList->user_id = $id;
            $blackList->black_status = RiskBlackList::STATUS_YES;
            $blackList->source = 3;
            $blackList->operator_id = 0;
            $blackList->black_remark = '逾期30天加入黑名单';

            $transaction = Yii::$app->db->beginTransaction();
            try{
                if (!$blackList->save()) {
                    throw new \Exception('保存黑名单失败');
                }

                $service = new RiskBlackListService();
                $params = $service->addListByLoanPerson($item,3,0);

                $pushService = new PushOrderRiskService();
                $result = $pushService->pushRiskBlack($params);
                if($result['code'] != 0){
                    throw new \Exception($result['message']);
                }
                $transaction->commit();
                $this->printMessage("user_id：{$id}, 加入黑名单成功");
            }catch (\Exception $exception)
            {
                $transaction->rollBack();
                Yii::error([
                    'user_id' => $id,
                    'code'    => $exception->getCode(),
                    'message' => $exception->getMessage(),
                    'line'    => $exception->getLine(),
                    'file'    => $exception->getFile(),
                    'trace'   => $exception->getTraceAsString()
                ], 'AddRiskBlackList');
                $service = new WeWorkService();
                $message = sprintf('[%s][%s]异常[user_id:%s] : %s in %s:%s',
                    \yii::$app->id, Yii::$app->requestedRoute, $id, $exception->getMessage(), $exception->getFile(), $exception->getLine());
                $service->send($message);
            }
        }
    }

    /**
     * 获取用户前置数据
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function actionGetData($id=1)
    {
        if(!$this->lock()){
            return;
        }

        $now = time();

        while( true)
        {
            if (time() - $now > 300) {
                $this->printMessage('运行满5分钟，关闭当前脚本');
                exit;
            }

            $order_id = RedisQueue::pop([RedisQueue::CREDIT_GET_DATA_SOURCE_PREFIX]);
            if (empty($order_id)) {
                $this->printMessage('无需处理订单，继续等待');
                sleep(1);
                continue;
            }

            $this->printMessage("订单{$order_id}开始数据采集");

            /**
             * @var UserLoanOrder $order
             */
            $order = UserLoanOrder::findOne($order_id);
            if (empty($order)) {
                sleep(1);
                $order = UserLoanOrder::findOne($order_id);
                if(empty($order))
                {
                    $this->printMessage("订单ID:{$order_id}不存在");
                    continue;
                }
            }

            if (UserLoanOrder::STATUS_CHECK != $order->status
                || UserLoanOrder::AUDIT_STATUS_GET_DATA != $order->audit_status
            ) {
                $_notice = sprintf("order_{$order_id} 非采集状态[%s-%s], skip.",
                    UserLoanOrder::STATUS_CHECK != $order->status,
                    UserLoanOrder::AUDIT_STATUS_GET_DATA != $order->audit_status);
                $this->printMessage($_notice);
                continue;
            }

            try{
                if(GlobalSetting::checkUserInSkipCheckList($order->loanPerson->id)){
                    $orderService = new OrderService($order);
                    $orderService->changeOrderAllStatus([ 'after_audit_status' => UserLoanOrder::AUDIT_STATUS_AUTO_CHECK],'');
                    $orderService->autoCheckApprove();
                    continue;
                }

                $pushService = new PushOrderRiskService();
                $result = $pushService->pushOrder($order);
                if($result['code'] != 0){
                    throw new \Exception($result['message']);
                }

                $orderService = new OrderService($order);
                $orderService->changeOrderAllStatus([ 'after_audit_status' => UserLoanOrder::AUDIT_STATUS_AUTO_CHECK],'');
            }catch (\Exception $exception) {
                Yii::error([
                    'order_id' => $order_id,
                    'code'     => $exception->getCode(),
                    'message'  => $exception->getMessage(),
                    'line'     => $exception->getLine(),
                    'file'     => $exception->getFile(),
                    'trace'    => $exception->getTraceAsString()
                ], 'GetData');
                $service = new WeWorkService();
                $message = sprintf('[%s][%s]异常[order_id:%s] : %s in %s:%s',
                    \yii::$app->id, Yii::$app->requestedRoute, $order_id, $exception->getMessage(), $exception->getFile(), $exception->getLine());
                $service->send($message);
            }
        }
    }

    /**
     * 推送用户登陆信息
     * @return int
     * @throws \Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function actionPushLoginLog()
    {
        if (!$this->lock())
        {
            return ExitCode::UNSPECIFIED_ERROR;
        }
        $now = time();
        while (true) {
            if (time() - $now > 300) {
                $this->printMessage('运行满5分钟，关闭当前脚本');
                exit;
            }

            $logId = RedisQueue::pop([RedisQueue::PUSH_USER_LOGIN_DATA]);
            if(empty($logId)){
                $this->printMessage('没有可处理的数据，继续等待');
                sleep(1);
                continue;
            }

            /** @var ClientInfoLog $item */
            $item = ClientInfoLog::findOne($logId);
            if(empty($item)){
                $this->printMessage("ClientInfoLog id:{$logId}不存在");
                continue;
            }

            $this->printMessage("ClientInfoLog id：{$logId}, 开始运行");
            try{
                $service = new PushOrderRiskService();
                $result = $service->pushLoginLog($item);
                if($result['code'] != 0){
                    throw new \Exception($result['message']);
                }
            }catch (\Exception $exception)
            {
                Yii::error([
                    'client_info_log_id' => $logId,
                    'code'               => $exception->getCode(),
                    'message'            => $exception->getMessage(),
                    'line'               => $exception->getLine(),
                    'file'               => $exception->getFile(),
                    'trace'              => $exception->getTraceAsString()
                ], 'PushLoginLog');
                $service = new WeWorkService();
                $message = sprintf('[%s][%s]异常[client_info_log_id:%s] : %s in %s:%s',
                    \yii::$app->id, Yii::$app->requestedRoute, $logId, $exception->getMessage(), $exception->getFile(), $exception->getLine());
                $service->send($message);
            }
        }
    }

    /**
     * 推送订单驳回信息
     * @return int
     * @throws \Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function actionPushOrderReject()
    {
        if (!$this->lock())
        {
            return ExitCode::UNSPECIFIED_ERROR;
        }
        $now = time();
        while (true) {
            if (time() - $now > 300) {
                $this->printMessage('运行满5分钟，关闭当前脚本');
                exit;
            }

            $data = RedisQueue::pop([RedisQueue::PUSH_ORDER_REJECT_DATA]);
            if(empty($data)){
                $this->printMessage('没有可处理的数据，继续等待');
                sleep(1);
                continue;
            }

            $data = json_decode($data, true);

            $this->printMessage("订单号：{$data['order_id']}, 开始运行");
            try{
                $service = new PushOrderRiskService();
                $result = $service->pushOrderReject($data);
                if($result['code'] != 0){
                    throw new \Exception($result['message']);
                }
            }catch (\Exception $exception)
            {
                if(!in_array($exception->getMessage(), [
                    '数据不存在',
                    '数据版本号低于当前记录:',
                    'status错误'
                ])){
                    RedisDelayQueue::pushDelayQueue(RedisQueue::PUSH_ORDER_REJECT_DATA, json_encode($data),120);
                }
                Yii::error([
                    'order_id' => $data['order_id'],
                    'params'   => json_encode($data),
                    'code'     => $exception->getCode(),
                    'message'  => $exception->getMessage(),
                    'line'     => $exception->getLine(),
                    'file'     => $exception->getFile(),
                    'trace'    => $exception->getTraceAsString()
                ], 'PushOrderReject');
                $service = new WeWorkService();
                $message = sprintf('[%s][%s]异常[order_id:%s] : %s in %s:%s',
                    \yii::$app->id, Yii::$app->requestedRoute, $data['order_id'], $exception->getMessage(), $exception->getFile(), $exception->getLine());
                $service->send($message);
            }
        }
    }

    /**
     * 推送订单放款信息
     * @return int
     * @throws \Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function actionPushOrderLoanSuccess()
    {
        if (!$this->lock())
        {
            return ExitCode::UNSPECIFIED_ERROR;
        }
        $now = time();
        while (true) {
            if (time() - $now > 300) {
                $this->printMessage('运行满5分钟，关闭当前脚本');
                exit;
            }

            $data = RedisQueue::pop([RedisQueue::PUSH_ORDER_LOAN_SUCCESS_DATA]);
            if(empty($data)){
                $this->printMessage('没有可处理的数据，继续等待');
                sleep(1);
                continue;
            }

            $data = json_decode($data, true);
            $this->printMessage("订单号：{$data['order_id']}, 开始运行");
            try{
                $service = new PushOrderRiskService();
                $result = $service->pushOrderLoanSuccess($data);
                if($result['code'] != 0){
                    throw new \Exception($result['message']);
                }
            }catch (\Exception $exception)
            {
                if(!in_array($exception->getMessage(), [
                    '数据不存在',
                    '数据版本号低于当前记录:',
                ])){
                    RedisDelayQueue::pushDelayQueue(RedisQueue::PUSH_ORDER_LOAN_SUCCESS_DATA, json_encode($data),120);
                }
                Yii::error([
                    'order_id' => $data['order_id'],
                    'params'   => json_encode($data),
                    'code'     => $exception->getCode(),
                    'message'  => $exception->getMessage(),
                    'line'     => $exception->getLine(),
                    'file'     => $exception->getFile(),
                    'trace'    => $exception->getTraceAsString()
                ], 'PushOrderLoanSuccess');
                $service = new WeWorkService();
                $message = sprintf('[%s][%s]异常[order_id:%s] : %s in %s:%s',
                    \yii::$app->id, Yii::$app->requestedRoute, $data['order_id'], $exception->getMessage(), $exception->getFile(), $exception->getLine());
                $service->send($message);
            }
        }
    }

    /**
     * 推送订单还款信息
     * @return int
     * @throws \Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function actionPushOrderRepaymentSuccess()
    {
        if (!$this->lock())
        {
            return ExitCode::UNSPECIFIED_ERROR;
        }
        $now = time();
        while (true) {
            if (time() - $now > 300) {
                $this->printMessage('运行满5分钟，关闭当前脚本');
                exit;
            }

            $data = RedisQueue::pop([RedisQueue::PUSH_ORDER_REPAYMENT_SUCCESS_DATA]);
            if(empty($data)){
                $this->printMessage('没有可处理的数据，继续等待');
                sleep(1);
                continue;
            }

            $data = json_decode($data, true);
            $this->printMessage("订单号：{$data['order_id']}, 开始运行");
            try{
                $service = new PushOrderRiskService();
                $result = $service->pushOrderRepaymentSuccess($data);
                if($result['code'] != 0){
                    throw new \Exception($result['message']);
                }
            }catch (\Exception $exception)
            {
                if(!in_array($exception->getMessage(), [
                    '数据不存在',
                    '数据版本号低于当前记录:',
                ])){
                    RedisDelayQueue::pushDelayQueue(RedisQueue::PUSH_ORDER_REPAYMENT_SUCCESS_DATA, json_encode($data),120);
                }
                Yii::error([
                    'order_id' => $data['order_id'],
                    'params'   => json_encode($data),
                    'code'     => $exception->getCode(),
                    'message'  => $exception->getMessage(),
                    'line'     => $exception->getLine(),
                    'file'     => $exception->getFile(),
                    'trace'    => $exception->getTraceAsString()
                ], 'PushOrderRepaymentSuccess');
                $service = new WeWorkService();
                $message = sprintf('[%s][%s]异常[order_id:%s] : %s in %s:%s',
                    \yii::$app->id, Yii::$app->requestedRoute, $data['order_id'], $exception->getMessage(), $exception->getFile(), $exception->getLine());
                $service->send($message);
            }
        }
    }

    /**
     * 推送订单逾期信息
     * @return int
     * @throws \Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function actionPushOrderOverdue($i=1)
    {
        if (!$this->lock())
        {
            return ExitCode::UNSPECIFIED_ERROR;
        }
        $now = time();
        while (true) {
            if (time() - $now > 300) {
                $this->printMessage('运行满5分钟，关闭当前脚本');
                exit;
            }

            $data = RedisQueue::pop([RedisQueue::PUSH_ORDER_OVERDUE_DATA]);
            if(empty($data)){
                $this->printMessage('没有可处理的数据，退出脚本');
                exit;
            }

            $data = json_decode($data, true);
            $this->printMessage("订单号：{$data['order_id']}, 开始运行");
            try{
                $service = new PushOrderRiskService();
                $result = $service->pushOrderOverdue($data);
                if($result['code'] != 0){
                    throw new \Exception($result['message']);
                }
            }catch (\Exception $exception)
            {
                Yii::error([
                    'order_id' => $data['order_id'],
                    'params'   => json_encode($data),
                    'code'     => $exception->getCode(),
                    'message'  => $exception->getMessage(),
                    'line'     => $exception->getLine(),
                    'file'     => $exception->getFile(),
                    'trace'    => $exception->getTraceAsString()
                ], 'PushOrderOverdue');
                $service = new WeWorkService();
                $message = sprintf('[%s][%s]异常[order_id:%s] : %s in %s:%s',
                    \yii::$app->id, Yii::$app->requestedRoute, $data['order_id'], $exception->getMessage(), $exception->getFile(), $exception->getLine());
                $service->send($message);
            }
        }
    }

    /**
     * 推送催收建议拒绝
     * @return int
     * @throws \Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function actionPushCollectionSuggestion()
    {
        if (!$this->lock())
        {
            return ExitCode::UNSPECIFIED_ERROR;
        }
        $now = time();
        while (true) {
            if (time() - $now > 300) {
                $this->printMessage('运行满5分钟，关闭当前脚本');
                exit;
            }

            $orderId = RedisQueue::pop([RedisQueue::PUSH_COLLECTION_SUGGESTION_DATA]);
            if(empty($orderId)){
                $this->printMessage('没有可处理的数据');
                return ExitCode::UNSPECIFIED_ERROR;
            }

            $order = UserLoanOrder::findOne($orderId);
            if(empty($order)){
                $this->printMessage("订单号:{$orderId}不存在");
                continue;
            }

            $this->printMessage("订单号：{$orderId}, 开始运行");
            try{
                $service = new PushOrderRiskService();
                $result = $service->pushCollectionSuggestion($order);
                if($result['code'] != 0){
                    throw new \Exception($result['message']);
                }
            }catch (\Exception $exception)
            {
                Yii::error([
                    'order_id' => $orderId,
                    'code'     => $exception->getCode(),
                    'message'  => $exception->getMessage(),
                    'line'     => $exception->getLine(),
                    'file'     => $exception->getFile(),
                    'trace'    => $exception->getTraceAsString()
                ], 'PushCollectionSuggestion');
                $service = new WeWorkService();
                $message = sprintf('[%s][%s]异常[order_id:%s] : %s in %s:%s',
                    \yii::$app->id, Yii::$app->requestedRoute, $orderId, $exception->getMessage(), $exception->getFile(), $exception->getLine());
                $service->send($message);
            }
        }
    }

    /**
     * 推送催收建议拒绝
     * @return int
     * @throws \Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function actionPushLoanCollectionRecord()
    {
        if (!$this->lock())
        {
            return ExitCode::UNSPECIFIED_ERROR;
        }
        $now = time();
        while (true) {
            if (time() - $now > 300) {
                $this->printMessage('运行满5分钟，关闭当前脚本');
                exit;
            }

            $id = RedisQueue::pop([RedisQueue::PUSH_LOAN_COLLECTION_RECORD_DATA]);
            if(empty($id)){
                $this->printMessage('没有可处理的数据');
                return ExitCode::UNSPECIFIED_ERROR;
            }

            $record = LoanCollectionRecord::findOne($id);
            if(empty($record)){
                $this->printMessage("loanCollectionRecord Id:{$id}不存在");
                continue;
            }

            $loanPerson = LoanPerson::findOne($record['loan_user_id']);
            if(empty($loanPerson)){
                $this->printMessage('用户不存在');
                continue;
            }

            $order = UserLoanOrder::findOne($record['loan_order_id']);
            if(empty($order)){
                $this->printMessage('订单不存在');
                continue;
            }

            $this->printMessage("loanCollectionRecord Id：{$id}, 开始运行");
            try{
                $service = new PushOrderRiskService();
                $result = $service->pushLoanCollectionRecord($record, $order, $loanPerson);
                if($result['code'] != 0){
                    throw new \Exception($result['message']);
                }
            }catch (\Exception $exception)
            {
                Yii::error([
                    'loanCollectionRecord_id' => $id,
                    'code'     => $exception->getCode(),
                    'message'  => $exception->getMessage(),
                    'line'     => $exception->getLine(),
                    'file'     => $exception->getFile(),
                    'trace'    => $exception->getTraceAsString()
                ], 'PushLoanCollectionRecord');
                $service = new WeWorkService();
                $message = sprintf('[%s][%s]异常[loanCollectionRecord_id:%s] : %s in %s:%s',
                    \yii::$app->id, Yii::$app->requestedRoute, $id, $exception->getMessage(), $exception->getFile(), $exception->getLine());
                $service->send($message);
            }
        }
    }

    /**
     * 推送提醒订单
     * @param int $maxId
     * @param string $date
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws yii\base\InvalidConfigException
     */
    public function actionPushRemindOrder($maxId = 0, $date=''){
        if(!$this->lock()){
            $this->printMessage("进程执行中，关闭");
            return;
        }
        $db = Yii::$app->get('db_read_1');
        $begin_time = !empty($date) ? strtotime($date) : time() - 600;

        $query = RemindOrder::find()
            ->alias('ro')
            ->leftJoin(UserLoanOrderRepayment::tableName().' as r', 'r.id=ro.repayment_id')
            ->leftJoin(ClientInfoLog::tableName().' as c', 'r.order_id=c.event_id and c.event='.ClientInfoLog::EVENT_APPLY_ORDER)
            ->select([
                'ro.id',
                'ro.status',
                'ro.remind_return',
                'ro.payment_after_days',
                'ro.remind_count',
                'ro.created_at',
                'ro.updated_at',
                'r.order_id',
                'r.user_id',
                'c.package_name',
            ])
            ->where(['>', 'ro.updated_at', $begin_time])
            ->orderBy(['ro.id' => SORT_ASC])->limit(1000);
        $cloneQuery = clone $query;
        $datas = $cloneQuery->andWhere(['>', 'ro.id', $maxId])->asArray()->all($db);
        while ($datas){
            $this->printMessage('maxId:'.$maxId);
            foreach ($datas as $v){
                $maxId = $v['id'];
                if(empty($v['package_name'])){
                    $this->printMessage('订单ID：'.$v['order_id'].'没有app_name,跳过');
                    continue;
                }

                try{
                    $data = [
                        'request_id'         => $v['id'],
                        'app_name'           => $v['package_name'],
                        'order_id'           => $v['order_id'],
                        'user_id'            => $v['user_id'],
                        'status'             => $v['status'],
                        'remind_return'      => $v['remind_return'],
                        'payment_after_days' => $v['payment_after_days'],
                        'remind_count'       => $v['remind_count'],
                        'created_at'         => $v['created_at'],
                        'updated_at'         => $v['updated_at'],
                    ];
                    $service = new PushOrderRiskService();
                    $result = $service->pushRemindOrder($data);
                    if($result['code'] != 0){
                        throw new \Exception($result['message']);
                    }
                }catch (\Exception $exception)
                {
                    Yii::error([
                        'order_id' => $data['order_id'],
                        'params'   => json_encode($data),
                        'code'     => $exception->getCode(),
                        'message'  => $exception->getMessage(),
                        'line'     => $exception->getLine(),
                        'file'     => $exception->getFile(),
                        'trace'    => $exception->getTraceAsString()
                    ], 'PushRemindOrder');
                    $service = new WeWorkService();
                    $message = sprintf('[%s][%s]异常[order_id:%s] : %s in %s:%s',
                        \yii::$app->id, Yii::$app->requestedRoute, $data['order_id'], $exception->getMessage(), $exception->getFile(), $exception->getLine());
                    $service->send($message);
                }
            }

            $cloneQuery = clone $query;
            $datas = $cloneQuery->andWhere(['>', 'ro.id', $maxId])->asArray()->all($db);
        }

        $this->printMessage('脚本结束');
    }

    /**
     * 推送提醒日志
     * @param int $maxId
     * @param string $date
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws yii\base\InvalidConfigException
     */
    public function actionPushRemindLog($maxId = 0, $date=''){
        if(!$this->lock()){
            $this->printMessage("进程执行中，关闭");
            return;
        }
        $db = Yii::$app->get('db_read_1');
        $begin_time = !empty($date) ? strtotime($date) : time() - 600;

        $query = RemindLog::find()
            ->alias('l')
            ->leftJoin(RemindOrder::tableName().' as ro', 'ro.id=l.remind_id')
            ->leftJoin(UserLoanOrderRepayment::tableName().' as r', 'r.id=ro.repayment_id')
            ->leftJoin(ClientInfoLog::tableName().' as c', 'r.order_id=c.event_id and c.event='.ClientInfoLog::EVENT_APPLY_ORDER)
            ->select([
                'l.id',
                'l.remind_return',
                'l.payment_after_days',
                'l.created_at',
                'l.updated_at',
                'r.order_id',
                'r.user_id',
                'c.package_name',
            ])
            ->where(['>', 'ro.updated_at', $begin_time])
            ->orderBy(['ro.id' => SORT_ASC])->limit(1000);
        $cloneQuery = clone $query;
        $datas = $cloneQuery->andWhere(['>', 'l.id', $maxId])->asArray()->all($db);
        while ($datas){
            $this->printMessage('maxId:'.$maxId);
            foreach ($datas as $v){
                $maxId = $v['id'];
                if(empty($v['package_name'])){
                    $this->printMessage('订单ID：'.$v['order_id'].'没有app_name,跳过');
                    continue;
                }

                try{
                    $data = [
                        'request_id'         => $v['id'],
                        'app_name'           => $v['package_name'],
                        'order_id'           => $v['order_id'],
                        'user_id'            => $v['user_id'],
                        'source'             => 'saas',
                        'remind_return'      => $v['remind_return'],
                        'payment_after_days' => $v['payment_after_days'],
                        'created_at'         => $v['created_at'],
                        'updated_at'         => $v['updated_at'],
                    ];
                    $service = new PushOrderRiskService();
                    $result = $service->pushRemindLog($data);
                    if($result['code'] != 0){
                        throw new \Exception($result['message']);
                    }
                }catch (\Exception $exception)
                {
                    Yii::error([
                        'order_id' => $data['order_id'],
                        'params'   => json_encode($data),
                        'code'     => $exception->getCode(),
                        'message'  => $exception->getMessage(),
                        'line'     => $exception->getLine(),
                        'file'     => $exception->getFile(),
                        'trace'    => $exception->getTraceAsString()
                    ], 'PushRemindLog');
                    $service = new WeWorkService();
                    $message = sprintf('[%s][%s]异常[order_id:%s] : %s in %s:%s',
                        \yii::$app->id, Yii::$app->requestedRoute, $data['order_id'], $exception->getMessage(), $exception->getFile(), $exception->getLine());
                    $service->send($message);
                }
            }

            $cloneQuery = clone $query;
            $datas = $cloneQuery->andWhere(['>', 'l.id', $maxId])->asArray()->all($db);
        }

        $this->printMessage('脚本结束');
    }
//
//    /**
//     * 获取用户前置数据
//     * @throws \GuzzleHttp\Exception\GuzzleException
//     */
//    public function actionGetData($id=1)
//    {
//        $now = time();
//        while( true)
//        {
//            if (time() - $now > 300) {
//                $this->printMessage('运行满5分钟，关闭当前脚本');
//                exit;
//            }
//
//            $order_id = RedisQueue::pop([RedisQueue::CREDIT_GET_DATA_SOURCE_PREFIX]);
//            if (empty($order_id)) {
//                $this->printMessage('无需处理订单，继续等待');
//                sleep(1);
//                continue;
//            }
//
//            $this->printMessage("订单{$order_id}开始数据采集");
//
//            /**
//             * @var UserLoanOrder $order
//             */
//            $order = UserLoanOrder::findOne($order_id);
//            if (empty($order)) {
//                $this->printMessage("订单ID:{$order_id}不存在");
//                continue;
//            }
//
//            if (UserLoanOrder::STATUS_CHECK != $order->status
//                || UserLoanOrder::AUDIT_STATUS_GET_DATA != $order->audit_status
//            ) {
//                $_notice = sprintf("order_{$order_id} 非采集状态[%s-%s], skip.",
//                    UserLoanOrder::STATUS_CHECK != $order->status,
//                    UserLoanOrder::AUDIT_STATUS_GET_DATA != $order->audit_status);
//                $this->printMessage($_notice);
//                continue;
//            }
//
//            try{
//                $pushService = new PushOrderRiskService();
//                $result = $pushService->pushOrder($order);
//                if($result['code'] != 0){
//                    throw new \Exception($result['message']);
//                }
//
//                $tree = 'T101';
//                $orderService = new OrderService($order);
//                if(GlobalSetting::checkUserInSkipCheckList($order->loanPerson->id)){
//                    $orderService->changeOrderAllStatus([ 'after_audit_status' => UserLoanOrder::AUDIT_STATUS_AUTO_CHECK],'');
//                    if($order->is_first == UserLoanOrder::FIRST_LOAN_IS){
//                        RedisQueue::push([RedisQueue::CREDIT_AUTO_CHECK, $order_id]);
//                    }else{
//                        RedisQueue::push([RedisQueue::CREDIT_AUTO_CHECK_OLD, $order_id]);
//                    }
//                    continue;
//                }
//                //订单维度
//                $orderExtraService = new OrderExtraService($order);
//                $orderData = [
//                    'order'                 => $order,
//                    'loanPerson'            => $order->loanPerson,
//                    'userWorkInfo'          => $orderExtraService->getUserWorkInfo(),
//                    'userBasicInfo'         => $orderExtraService->getUserBasicInfo(),
//                    'userContact'           => $orderExtraService->getUserContact(),
//                    'userQuestionReport'    => $orderExtraService->getUserQuestionReport(),
//                ];
//                $data = new RiskDataDemoService($orderData);
//                $riskTree = new RiskTreeService($data);
//                $result = $riskTree->exploreNodeValue($tree);
//                //添加快照
//                $riskTree->insertRiskResultSnapshot($order->id, $order->loanPerson->id, [$tree => $result]);
//                $riskTree->insertRiskResultSnapshotToDb($order->id, $order->loanPerson->id, $tree,$result);
//                if($result['result'] == 'reject'){
//                    $orderService->getDataReject($result['txt'], $result['head_code'], $result['back_code'], $result['interval']);
//                    continue;
//                }
//                $orderService->changeOrderAllStatus([ 'after_audit_status' => UserLoanOrder::AUDIT_STATUS_AUTO_CHECK],'');
//
//                if($order->is_first == UserLoanOrder::FIRST_LOAN_IS){
//                    RedisQueue::push([RedisQueue::CREDIT_AUTO_CHECK, $order_id]);
//                }else{
//                    RedisQueue::push([RedisQueue::CREDIT_AUTO_CHECK_OLD, $order_id]);
//                }
//            }catch (\Exception $exception) {
//                Yii::error([
//                    'order_id' => $order_id,
//                    'code'     => $exception->getCode(),
//                    'message'  => $exception->getMessage(),
//                    'line'     => $exception->getLine(),
//                    'file'     => $exception->getFile(),
//                    'trace'    => $exception->getTraceAsString()
//                ], 'GetData');
//                $service = new WeWorkService();
//                $message = sprintf('[%s][%s]异常[order_id:%s] : %s in %s:%s',
//                    \yii::$app->id, Yii::$app->requestedRoute, $order_id, $exception->getMessage(), $exception->getFile(), $exception->getLine());
//                $message .= $exception->getTraceAsString();
//                $service->send($message);
//
//            }
//        }
//    }

//
//    /**
//     * 风控审核脚本
//     */
//    public function actionAutoCheck($is_first=1,$id=1)
//    {
//        $now = time();
//        $time = rand(2,5);
//        while( true)
//        {
//            if (time() - $now > $time * 60) {
//                $this->printMessage('运行满'.$time.'分钟，关闭当前脚本');
//                exit;
//            }
//
//            if($is_first == 1){
//                $order_id = RedisQueue::pop([RedisQueue::CREDIT_AUTO_CHECK]);
//            }else{
//                $order_id = RedisQueue::pop([RedisQueue::CREDIT_AUTO_CHECK_OLD]);
//            }
//            if (empty($order_id)) {
//                $this->printMessage('无需处理订单，继续等待');
//                sleep(1);
//                continue;
//            }
//
//            $this->printMessage("订单{$order_id}开始数据采集");
//
//            /**
//             * @var UserLoanOrder $order
//             */
//            $order = UserLoanOrder::findOne($order_id);
//            if (empty($order)) {
//                $this->printMessage("订单ID:{$order_id}不存在");
//                continue;
//            }
//
//            if (UserLoanOrder::STATUS_CHECK != $order->status
//                || UserLoanOrder::AUDIT_STATUS_AUTO_CHECK != $order->audit_status
//            ) {
//                $_notice = sprintf("order_{$order_id} 非待机审状态[%s-%s], skip.",
//                    UserLoanOrder::STATUS_CHECK != $order->status,
//                    UserLoanOrder::AUDIT_STATUS_AUTO_CHECK != $order->audit_status);
//                $this->printMessage($_notice);
//                continue;
//            }
//
//            try{
//                if(GlobalSetting::checkUserInSkipCheckList($order->loanPerson->id)){
//                    $orderService = new OrderService($order);
//                    $orderService->autoCheckApprove();
//                    continue;
//                }
//
//                $tree = 'T102';
//                //订单维度
//                $orderExtraService = new OrderExtraService($order);
//                $orderData = [
//                    'order'                 => $order,
//                    'loanPerson'            => $order->loanPerson,
//                    'userWorkInfo'          => $orderExtraService->getUserWorkInfo(),
//                    'userBasicInfo'         => $orderExtraService->getUserBasicInfo(),
//                    'userBankAccount'       => $order->userBankAccount,
//                    'userContact'           => $orderExtraService->getUserContact(),
//                    'userAadhaarReport'     => $orderExtraService->getUserOcrAadhaarReport(),
//                    'userPanReport'         => $orderExtraService->getUserOcrPanReport(),
//                    'userFrReport'          => $orderExtraService->getUserFrReport(),
//                    'userPanVerifyReport'   => $orderExtraService->getUserVerifyPanReport(),
//                    'userFrFrReport'        => $orderExtraService->getUserFrFrReport(),
//                    'userFrPanReport'       => $orderExtraService->getUserFrPanReport(),
//                    'userFrCompareReport'   => $orderExtraService->getUserFrCompareReport(),
//                    'userQuestionReport'    => $orderExtraService->getUserQuestionReport(),
//                ];
//                $data = new RiskDataDemoService($orderData);
//                $riskTree = new RiskTreeService($data);
//                /** @var array $result */
//                $result = $riskTree->exploreNodeValue($tree);
//                //添加快照
//                $riskTree->insertRiskResultSnapshot($order->id, $order->loanPerson->id, [$tree => $result]);
//                $riskTree->insertRiskResultSnapshotToDb($order->id, $order->loanPerson->id,$tree ,$result );
//                switch ($result['result'])
//                {
//                    case 'reject':
//                        $orderService = new OrderService($order);
//                        $orderService->autoCheckReject($result['txt'], $result['head_code'], $result['back_code'], $result['interval']);
//                        break;
//                    case 'manual':
//                        $orderService = new OrderService($order);
//                        $orderService->autoCheckManual($result['txt'], $result['head_code'], $result['back_code']);
//                        break;
//                    case 'approve':
//                        $orderService = new OrderService($order);
//                        $orderService->autoCheckApprove();
//                        break;
//                    default:
//
//                }
//
//                if(($result['result'] == 'manual' || $result['result'] == 'approve')
//                    && $order->amount == 0){
//                    $riskTree = new RiskTreeService($data);
//                    $tree = 'C101';
//                    /** @var array $result */
//                    $result = $riskTree->exploreNodeValue($tree);
//                    //添加快照
//                    $riskTree->insertRiskResultSnapshot($order->id, $order->loanPerson->id, [$tree => $result]);
//                    $riskTree->insertRiskResultSnapshotToDb($order->id, $order->loanPerson->id,$tree ,$result);
//
//                    $limit_service = new UserCreditLimitService();
//                    $limit_service->changeLimit($order->loanPerson->id, $result['result'] * 100);
//                    $order->credit_limit = $result['result'] * 100;
//                    $order->save();
//                }
//
//                $version = RuleVersion::getGrayVersion();
//                if(!empty($version)){
//                    $riskTree = new RiskTreeService($data);
//                    $tree = 'T102';
//                    $riskTree->setRuleVersion($version);
//                    /** @var array $result */
//                    $result = $riskTree->exploreNodeValue($tree);
//                    $riskTree->insertRiskResultSnapshotGrayToDb($order->id, $order->loanPerson->id, $tree, $result);
//                }
//
//            }catch (\Exception $exception) {
//                Yii::error([
//                    'order_id' => $order_id,
//                    'code'     => $exception->getCode(),
//                    'message'  => $exception->getMessage(),
//                    'file'     => $exception->getFile(),
//                    'line'     => $exception->getLine(),
//                    'trace'    => $exception->getTraceAsString()
//                ], 'RiskAutoCheck');
//                if($exception->getCode() == 1001){
//                    if($is_first == 1){
//                        RedisDelayQueue::pushDelayQueue(RedisQueue::CREDIT_AUTO_CHECK, $order_id, 120);
//                    }else{
//                        RedisDelayQueue::pushDelayQueue(RedisQueue::CREDIT_AUTO_CHECK_OLD, $order_id, 10);
//                    }
//                }else{
//                    $service = new WeWorkService();
//                    $message = sprintf('[%s][%s]异常[order_id:%s]: %s in %s:%s',
//                        \yii::$app->id, Yii::$app->requestedRoute, $order_id, $exception->getMessage(), $exception->getFile(), $exception->getLine());
//                    $message .= $exception->getTraceAsString();
//                    $service->send($message);
//                }
//            }
//        }
//    }

    /**
     * 推送Kudos-创建kudos订单
     * @return int
     * @throws \Exception
     */
    public function actionKudosOrderGenerate()
    {
        if (!$this->lock())
        {
            $this->printMessage('KudosOrderGenerate 已经运行中,关闭脚本');
            return ExitCode::UNSPECIFIED_ERROR;
        }
        $startTime = time();
        while ($orderId = RedisQueue::pop([RedisQueue::LIST_KUDOS_CREATE_ORDER])) {
            $this->printMessage("订单号：{$orderId}, 开始运行");
            try{
                /** @var UserLoanOrder $item */
                $item = UserLoanOrder::findOne($orderId);
                $loanAccountSetting = $item->loanFund->loanAccountSetting;
                $service = new KudosService($loanAccountSetting);
                $this->printMessage("订单号:{$item->id}开始处理");
                if(UserLoanOrder::LOAN_STATUS_LOAN_SUCCESS != $item->loan_status)
                {
                    $this->printMessage("订单号:{$item->id}状态已变更，跳过处理");
                    continue;
                }
                $r = $service->createKudosOrder($item);
                if($r){
                    $this->printMessage("订单号:{$item->id}KudosOrderGenerate成功");
                }else{
                    throw new yii\base\Exception('createKudosOrder失败');
                }

            }catch (\Exception $exception)
            {
                \Yii::error("订单号：{$orderId},异常退出，重入队列.{$exception->getMessage()}", 'kudos_order_generate');
                RedisDelayQueue::pushDelayQueue(RedisQueue::LIST_KUDOS_CREATE_ORDER, $orderId, 120);
            }
            if((time() - $startTime) >= 300)
            {
                $this->printMessage("脚本运行超过5分钟，退出脚本");
                return ExitCode::UNSPECIFIED_ERROR;
            }


        }
    }


    /**
     * 推送Kudos-Loan Request
     * @return int
     * @throws \Exception
     */
    public function actionKudosOrderLoanRequest()
    {
        if (!$this->lock())
        {
            $this->printMessage('KudosOrderGenerate 已经运行中,关闭脚本');
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $maxId = 0;
        $this->printMessage('KudosOrderGenerate 开始运行');

        $query = LoanKudosOrder::find()
            ->select(['id', 'kudos_status', 'updated_at'])
            ->where(['kudos_status' => LoanStatus::INIT()->getValue()])
            ->limit(500)
            ->orderBy(['id' => SORT_ASC]);

        $cloneQuery = clone $query;
        $kudosOrders = $cloneQuery->andWhere(['>', 'id', $maxId])->all();

        while ($kudosOrders)
        {
            /** @var LoanKudosOrder $order */
            foreach ($kudosOrders as $order) {
                $maxId = $order->id;
                $kudosOrder = LoanKudosOrder::findOne($order->id);
                if(
                    $kudosOrder->kudos_status != $order->kudos_status
                    || $kudosOrder->updated_at != $order->updated_at
                )
                {
                    $this->printMessage("kudosOrder订单号:{$kudosOrder->id},状态已变更，跳过处理");
                    continue;
                }
                /** @var UserLoanOrder $item */
                $item = UserLoanOrder::findOne($kudosOrder->order_id);
                $this->printMessage("订单号:{$item->id}开始处理");
                $service = new KudosService($kudosOrder->payAccountSetting);
                $r = $service->loanRequest($item);
                if($r){
                    $this->printMessage("订单号:{$item->id}loanRequest成功");
                }else{
                    $this->printMessage("订单号:{$item->id}loanRequest失败");
                }
            }
            $cloneQuery = clone $query;
            $kudosOrders = $cloneQuery->andWhere(['>', 'id', $maxId])->all();

        }


        $this->printMessage('KudosOrderGenerate 正常结束');
        return ExitCode::OK;
    }


    /**
     * 推送Kudos-BorrowerInfo
     * @return int
     * @throws \Exception
     */
    public function actionKudosOrderBorrowerInfo()
    {
        if (!$this->lock())
        {
            $this->printMessage('KudosOrderBorrowerInfo 已经运行中,关闭脚本');
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $maxId = 0;
        $this->printMessage('KudosOrderBorrowerInfo 开始运行');

        $query = LoanKudosOrder::find()
            ->select(['id', 'kudos_status', 'updated_at', 'pay_account_id'])
            ->where(['kudos_status' => LoanStatus::LOAN_REQUEST()->getValue()])
            ->limit(500)->orderBy(['id' => SORT_ASC]);

        $cloneQuery = clone $query;
        $kudosOrders = $cloneQuery->andWhere(['>' , 'id', $maxId])->all();

        while ($kudosOrders)
        {
            /** @var LoanKudosOrder $order */
            foreach ($kudosOrders as $order) {
                $maxId = $order->id;
                $kudosOrder = LoanKudosOrder::findOne($order->id);
                if(
                    $kudosOrder->kudos_status != $order->kudos_status
                    || $kudosOrder->updated_at != $order->updated_at
                )
                {
                    $this->printMessage("kudosOrder订单号:{$kudosOrder->id},状态已变更，跳过处理");
                    continue;
                }
                /** @var UserLoanOrder $item */
                $item = UserLoanOrder::findOne($kudosOrder->order_id);
                $this->printMessage("订单号:{$item->id}开始处理");
                $service = new KudosService($kudosOrder->payAccountSetting);
                $r = $service->borrowerInfo($item);
                if($r){
                    $this->printMessage("订单号:{$item->id}borrowerInfo成功");
                }else{
                    $this->printMessage("订单号:{$item->id}borrowerInfo失败");
                }
            }

            $cloneQuery = clone $query;
            $kudosOrders = $cloneQuery->andWhere(['>' , 'id', $maxId])->all();
        }


        $this->printMessage('KudosOrderBorrowerInfo 正常结束');
        return ExitCode::OK;
    }

    /**
     * 推送Kudos-Upload Document
     * @return int
     * @throws \Exception
     */
    public function actionKudosOrderUploadDocument()
    {
        if (!$this->lock())
        {
            $this->printMessage('KudosOrderUploadDocument 已经运行中,关闭脚本');
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $maxId = 0;
        ini_set('memory_limit', '512M');
        $this->printMessage('KudosOrderUploadDocument 开始运行');

        $query = LoanKudosOrder::find()
            ->select(['id', 'kudos_status', 'updated_at'])
            ->where(['kudos_status' => LoanStatus::BORROWER_INFO()->getValue()])
            ->limit(500)
            ->orderBy(['id' => SORT_ASC]);

        $cloneQuery = clone $query;
        $kudosOrders = $cloneQuery->andWhere(['>', 'id', $maxId])->all();


        while ($kudosOrders)
        {
            /** @var LoanKudosOrder $order */
            foreach ($kudosOrders as $order) {
                $maxId = $order->id;
                $kudosOrder = LoanKudosOrder::findOne($order->id);
                if(
                    $kudosOrder->kudos_status != $order->kudos_status
                    || $kudosOrder->updated_at != $order->updated_at
                )
                {
                    $this->printMessage("kudosOrder订单号:{$kudosOrder->id},状态已变更，跳过处理");
                    continue;
                }
                /** @var UserLoanOrder $item */
                $item = UserLoanOrder::findOne($kudosOrder->order_id);
                $this->printMessage("订单号:{$item->id}开始处理");
                $service = new KudosService($kudosOrder->payAccountSetting);
                $r = $service->uploadDocument($item);
                if($r){
                    $this->printMessage("订单号:{$item->id}uploadDocument成功");
                }else{
                    $this->printMessage("订单号:{$item->id}uploadDocument失败");
                }
            }

            $cloneQuery = clone $query;
            $kudosOrders = $cloneQuery->andWhere(['>', 'id', $maxId])->all();
        }


        $this->printMessage('KudosOrderUploadDocument 正常结束');
        return ExitCode::OK;
    }

    /**
     * 推送Kudos-ValidationGet
     * @return int
     * @throws \Exception
     */
    public function actionKudosOrderValidationGet()
    {
        if (!$this->lock())
        {
            $this->printMessage('KudosOrderValidationGet 已经运行中,关闭脚本');
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $maxId = 0;
        $this->printMessage('KudosOrderValidationGet 开始运行');

        $query = LoanKudosOrder::find()
            ->select(['id', 'kudos_status', 'updated_at'])
            ->where(['kudos_status' => LoanStatus::UPLOAD_DOCUMENT()->getValue()])
            ->limit(500)
            ->orderBy(['id' => SORT_ASC]);

        $cloneQuery = clone $query;
        $kudosOrders = $cloneQuery->andWhere(['>', 'id', $maxId])->all();

        while ($kudosOrders)
        {
            /** @var LoanKudosOrder $order */
            foreach ($kudosOrders as $order) {
                $maxId = $order->id;
                $kudosOrder = LoanKudosOrder::findOne($order->id);
                if(
                    $kudosOrder->kudos_status != $order->kudos_status
                    || $kudosOrder->updated_at != $order->updated_at
                )
                {
                    $this->printMessage("kudosOrder订单号:{$kudosOrder->id},状态已变更，跳过处理");
                    continue;
                }
                /** @var UserLoanOrder $item */
                $item = UserLoanOrder::findOne($kudosOrder->order_id);
                $this->printMessage("订单号:{$item->id}开始处理");
                $service = new KudosService($kudosOrder->payAccountSetting);
                $r = $service->validationGet($item);
                if($r){
                    $this->printMessage("订单号:{$item->id}validationGet成功");
                }else{
                    $this->printMessage("订单号:{$item->id}validationGet失败");
                }
            }

            $cloneQuery = clone $query;
            $kudosOrders = $cloneQuery->andWhere(['>', 'id', $maxId])->all();
        }


        $this->printMessage('KudosOrderValidationGet 正常结束');
        return ExitCode::OK;
    }

    /**
     * 推送Kudos-LoanRepaymentSchedule
     * @return int
     * @throws \Exception
     */
    public function actionKudosOrderLoanRepaymentSchedule()
    {
        if (!$this->lock())
        {
            $this->printMessage('KudosOrderLoanRepaymentSchedule 已经运行中,关闭脚本');
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $maxId = 0;
        $this->printMessage('KudosOrderLoanRepaymentSchedule 开始运行');

        $query = LoanKudosOrder::find()
            ->select(['id', 'kudos_status', 'updated_at'])
            ->where(['kudos_status' => LoanStatus::VALIDATION_GET()->getValue()])
            ->limit(500)
            ->orderBy(['id' => SORT_ASC]);

        $cloneQuery = clone $query;
        $kudosOrders = $cloneQuery->andWhere(['>', 'id', $maxId])->all();

        while ($kudosOrders)
        {
            /** @var LoanKudosOrder $order */
            foreach ($kudosOrders as $order) {
                $maxId = $order->id;
                $kudosOrder = LoanKudosOrder::findOne($order->id);
                if(
                    $kudosOrder->kudos_status != $order->kudos_status
                    || $kudosOrder->updated_at != $order->updated_at
                )
                {
                    $this->printMessage("kudosOrder订单号:{$kudosOrder->id},状态已变更，跳过处理");
                    continue;
                }
                /** @var UserLoanOrder $item */
                $item = UserLoanOrder::findOne($kudosOrder->order_id);
                $this->printMessage("订单号:{$item->id}开始处理");
                $service = new KudosService($kudosOrder->payAccountSetting);
                if( $service->loanRepaymentSchedule($item))
                {
                    $this->printMessage("订单号:{$item->id}已处理成功");
                }else{
                    $this->printMessage("订单号:{$item->id}处理失败");
                }
            }

            $cloneQuery = clone $query;
            $kudosOrders = $cloneQuery->andWhere(['>', 'id', $maxId])->all();
        }


        $this->printMessage('KudosOrderLoanRepaymentSchedule 正常结束');
        return ExitCode::OK;
    }

    /**
     * Kudos订单24小时候校验数据
     * @return int
     */
    public function actionKudosOrderValidation()
    {

        if (!$this->lock())
        {
            echo date('Y-m-d H:i:s') . ' KudosOrderValidation 已经运行中' . PHP_EOL;
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $maxId = 0;
        echo date('Y-m-d H:i:s') . ' KudosOrderValidation 开始运行' . PHP_EOL;

        $query = LoanKudosOrder::find()
            ->select(['id', 'next_validation_time', 'validation_status'])
            ->where(['validation_status' => ValidationStatus::WAIT_VALIDATION()->getValue()])
            ->andWhere(['<=' , 'next_validation_time', time()])
            ->limit(500)
            ->orderBy(['id' => SORT_ASC]);

        $cloneQuery = clone $query;
        $orders = $cloneQuery->andWhere(['>', 'id', $maxId])->all();

        while ($orders)
        {
            /** @var LoanKudosOrder $order */
            foreach($orders as $order)
            {
                $maxId = $order->id;
                /** @var LoanKudosOrder $item */
                $item = LoanKudosOrder::findOne($order->id);
                if(! ($item->next_validation_time == $order->next_validation_time  && $item->validation_status == $order->validation_status))
                {
                    $this->printMessage("kudos订单号:{$item->id} 状态发生变更，跳过操作");
                    continue;
                }
                $service = new KudosService($item->payAccountSetting);
                if($service->validationGetTwo($item))
                {
                    $this->printMessage("kudos订单号:{$item->id} 验证成功");
                }else{
                    $this->printMessage("kudos订单号:{$item->id} 验证成功");
                }
            }


            $cloneQuery = clone $query;
            $orders = $cloneQuery->andWhere(['>', 'id', $maxId])->all();

        }

        echo date('Y-m-d H:i:s') . ' KudosOrderValidation 正常结束' . PHP_EOL;
        return ExitCode::OK;
    }


    /**
     * kudos状态检查
     * @return int
     */
    public function actionKudosOrderCheckStatus()
    {
        if (!$this->lock())
        {
            echo date('Y-m-d H:i:s') . ' KudosOrderCheckStatus 已经运行中' . PHP_EOL;
            return ExitCode::UNSPECIFIED_ERROR;
        }
        $maxId = 0;
        echo date('Y-m-d H:i:s') . ' KudosOrderCheckStatus 开始运行' . PHP_EOL;

        $query = LoanKudosOrder::find()
            ->select(['id', 'next_check_status_time', 'need_check_status'])
            ->where(['need_check_status' => 1])
            ->andWhere(['<=', 'next_check_status_time', time()])
            ->orderBy(['id' => SORT_ASC]);

        $cloneQuery = clone $query;
        $orders = $cloneQuery->andWhere(['>', 'id', $maxId])->all();

        while ($orders)
        {
            /** @var LoanKudosOrder $order */
            foreach($orders as $order)
            {
                $maxId = $order->id;
                /** @var LoanKudosOrder $item */
                $item = LoanKudosOrder::findOne($order->id);
                if($item->next_check_status_time != $order->next_check_status_time
                    || $item->need_check_status != $order->need_check_status
                )
                {
                    $this->printMessage("kudos订单号:{$item->id} 状态发生变更，跳过操作");
                    continue;
                }
                $this->printMessage("kudos订单号:{$item->id} 开始执行");
                $service = new KudosService($item->payAccountSetting);
                if($service->statusCheck($item))
                {
                    $this->printMessage("kudos订单号:{$item->id} 状态检查成功");
                }else{
                    $this->printMessage("kudos订单号:{$item->id} 状态检查成功");
                }
            }

            $cloneQuery = clone $query;
            $orders = $cloneQuery->andWhere(['>', 'id', $maxId])->all();
        }


        echo date('Y-m-d H:i:s') . ' KudosOrderCheckStatus 正常结束' . PHP_EOL;
        return ExitCode::OK;
    }


    /**
     * 推送Kudos-当天放款计划
     * @return int
     */
    public function actionPushOrderSumToKudos()
    {
        if (!$this->lock())
        {
            $this->printMessage('PushOrderSumToKudos 已经运行中,退出脚本');
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $this->printMessage('PushOrderSumToKudos 开始运行');

        $models = LoanKudosTranche::find()
            ->select(['id', 'updated_at'])
            ->where(['kudos_status' => 0])
            ->andWhere(['<', 'date', date('Y-m-d')])
            ->all();

        /** @var LoanKudosOrder $model */
        foreach ($models as $model)
        {
            $item = LoanKudosTranche::findOne($model->id);
            if($item->updated_at != $model->updated_at)
            {
                $this->printMessage("kudos订单号：{$item->id}状态变更，跳过处理");
                continue;
            }

            $trancheId = $item->kudos_tranche_id;
            $date = $item->date;
            $num = LoanKudosOrder::find()->where([
                'kudos_tranche_id' => $item->id,
                'merchant_id' => $item->merchant_id,
                'pay_account_id' => $item->pay_account_id
            ])->count();
            $amount = intval(CommonHelper::CentsToUnit(LoanKudosOrder::find()->where([
                'kudos_tranche_id' => $item->id,
                'merchant_id' => $item->merchant_id,
                'pay_account_id' => $item->pay_account_id
            ])->sum('disbursement_amt')));

            $this->printMessage("TrancheId:{$trancheId}, date:{$date} num:{$num}, amount:{$amount} ,pay_account_id:{$item->pay_account_id}开始推送");
            $params = [
                'tranche_id'       => $trancheId,
                'disbursement_dte' => $date,
                'tranche_num'      => intval($num),
                'tranche_amt'      => strval($amount),
            ];
            $service = new KudosService($item->payAccountSetting);
            if($service->loanTrancheAppend($item, $params))
            {
                $this->printMessage("TrancheId:{$trancheId}, date:{$date} num:{$num}, amount:{$amount} ,pay_account_id:{$item->pay_account_id} 推送成功");
            }else{
                $this->printMessage("TrancheId:{$trancheId}, date:{$date} num:{$num}, amount:{$amount} ,pay_account_id:{$item->pay_account_id} 推送失败");
            }

        }

        $this->printMessage('PushOrderSumToKudos 结束运行');

        return Exitcode::OK;
    }

    /**
     * 推送kudos-用户还款（每次支付）
     */
    public function actionKudosRepayment()
    {
        $startTime = time();
        while ($orderData = RedisQueue::pop([RedisQueue::LIST_KUDOS_USER_REPAYMENT])) {
            $orderArr = json_decode($orderData, true);
            $this->printMessage("订单号：{$orderArr['order_id']},还款金额{$orderArr['paid_amount']}开始运行");
            try{
                /** @var UserLoanOrder $order */
                $order = UserLoanOrder::findOne($orderArr['order_id']);
                $kudosOrder = LoanKudosOrder::findOne(['order_id' => $orderArr['order_id']]);
                $service = new KudosService($kudosOrder->payAccountSetting);
                if($service->pgTransactionNotify($order, $orderArr['paid_amount'], time()))
                {
                    $this->printMessage("订单号：{$orderArr['order_id']},还款金额{$orderArr['paid_amount']}运行成功");
                }else{
                    $this->printMessage("订单号：{$orderArr['order_id']},还款金额{$orderArr['paid_amount']}开始失败");
                }
            }catch (\Exception $exception)
            {
                Yii::error("订单号：{$orderArr['order_id']},还款金额{$orderArr['paid_amount']}异常退出，重入队列.{$exception->getMessage()}", 'kudos');
                RedisDelayQueue::pushDelayQueue(RedisQueue::LIST_KUDOS_USER_REPAYMENT, $orderData, 120);
            }
            if((time() - $startTime) >= 300)
            {
                $this->printMessage("脚本运行超过5分钟，退出脚本");
                return;
            }


        }
    }

    /**
     * 推送kudos-用户还款完成-到期还款
     */
    public function actionKudosOrderClosure()
    {
        $orderData = RedisQueue::pop([RedisQueue::LIST_KUDOS_USER_ORDER_CLOSURE]);

        while (!empty($orderData)) {
            $orderArr = json_decode($orderData, true);
            $this->printMessage("订单号：{$orderArr['order_id']},状态{$orderArr['kudos_status']}开始运行");
            $order = UserLoanOrder::findOne($orderArr['order_id']);
            $service = new KudosService($order->loanFund->payAccountSetting);
            if($service->reconciliation($order, $orderArr['kudos_status'])){
                $this->printMessage("订单号：{$orderArr['order_id']},状态{$orderArr['kudos_status']}运行成功");
            }else{
                $this->printMessage("订单号：{$orderArr['order_id']},状态{$orderArr['kudos_status']}运行失败");
            }
            $orderData = RedisQueue::pop([RedisQueue::LIST_KUDOS_USER_ORDER_CLOSURE]);
        }
    }


    /**
     * 对账kudos-对账接口
     */
    public function actionKudosOrderLoanStatementRequest()
    {

        $order = UserLoanOrder::findOne(13761);
        $service = new KudosService($order->loanFund->payAccountSetting);
        $r = $service->loanStatementRequest($order);
        var_dump($r);
    }

    /**
     * 推送kudos-用户逾期-逾期两天
     */
    public function actionKudosOrderIssued()
    {
        $orderData = RedisQueue::pop([RedisQueue::LIST_KUDOS_USER_ORDER_ISSUED]);

        while (!empty($orderData)) {
            $orderArr = json_decode($orderData, true);
            $order = UserLoanOrder::findOne($orderArr['id']);
            $service = new KudosService($order->loanFund->payAccountSetting);
            $service->loanDemand($orderArr['id'], NoteIssueType::ISSUED());
            $orderData = RedisQueue::pop([RedisQueue::LIST_KUDOS_USER_ORDER_ISSUED]);
        }
    }

    /**
     * 推送kudos-用户逾期-逾期七天
     */
    public function actionKudosOrderRaised()
    {
        $orderData = RedisQueue::pop([RedisQueue::LIST_KUDOS_USER_ORDER_RAISED]);

        while (!empty($orderData)) {
            $orderArr = json_decode($orderData, true);
            $order = UserLoanOrder::findOne($orderArr['id']);
            $service = new KudosService($order->loanFund->payAccountSetting);
            $service->loanDemand($orderArr['id'], NoteIssueType::RAISED());
            $orderData = RedisQueue::pop([RedisQueue::LIST_KUDOS_USER_ORDER_RAISED]);
        }
    }


    /**
     * 推送kudos-优惠券功能
     * @return int
     */
    public function actionKudosOrderCoupon()
    {
        if (!$this->lock())
        {
            $this->printMessage('KudosOrderCoupon 已经运行中,退出脚本');
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $this->printMessage('KudosOrderCoupon 开始运行');
        $kudosOrders = LoanKudosOrder::find()->where(['need_coupon_request' => 1])->all();
        /** @var LoanKudosOrder $kudosOrder */
        foreach($kudosOrders as $kudosOrder)
        {
            /** @var LoanKudosOrder $order */
            $order = LoanKudosOrder::findOne($kudosOrder->id);
            if($order->updated_at != $kudosOrder->updated_at)
            {
                $this->printMessage("kudos订单号：{$order->id}状态变更，跳过处理");
                continue;
            }

            $this->printMessage("kudos_id:{$order->id}, coupon_amount: {$order->coupon_amount} 开始推送");
            $service = new KudosService($order->payAccountSetting);
            if($service->borrowerInfoCoupon($order)){
                $this->printMessage("kudos_id:{$order->id}, coupon_amount: {$order->coupon_amount} 推送成功");
            }else{
                $this->printMessage("kudos_id:{$order->id}, coupon_amount: {$order->coupon_amount} 推送失败");
            }

        }

        $this->printMessage('KudosOrderCoupon 结束运行');
    }

    /**
     * 资方分配脚本
     * @param int $limit
     * @param int $orderId
     * @return int
     */
    public function actionUpdateLoan($merchantId, $limit = 1500, $orderId = null)
    {
        if (!$this->lock()) {
            return;
        }

        $maxId = 0;
        $query = UserLoanOrder::find()
            ->where([
                'status' => UserLoanOrder::STATUS_LOANING ,
                'loan_status' => UserLoanOrder::LOAN_STATUS_FUND_MATCH,
                'merchant_id' => $merchantId
                ]);
        if (!is_null($orderId)) {
            $query->andWhere(['id'=> $orderId]);
        }

        $cloneQuery = clone $query;
        $queues = $cloneQuery->andWhere(['>', 'id', $maxId])->limit($limit)->orderBy(['id' => SORT_ASC])->all();
        if (empty($queues)) {
            $this->printMessage(sprintf('%s none order.', date('Y-m-d H:i'))) ;
            return;
        }

        while ($queues)
        {
            /** @var UserLoanOrder $item */
            foreach ($queues as $item) {
                $maxId = $item->id;
                //订单状态判断
                if ( !(UserLoanOrder::STATUS_LOANING == $item->status && UserLoanOrder::LOAN_STATUS_FUND_MATCH == $item->loan_status)) {
                    $this->printMessage("order-{$item['id']}, order status error");
                    continue;
                }


                $this->printMessage("order-{$item['id']} start.");
                $loan_person = $item->loanPerson;

                if (empty($loan_person)) {
                    $this->printMessage("order-{$item['id']}, loan_person error.");
                    continue;
                }



                $order_service = new OrderService($item);
                $todayTime = time() - 86400 * 2;
//                $todayTime = strtotime('today') + 8 * 3600;
                if($todayTime > $item->created_at )
                {
                    $this->printMessage("订单号:{$item->id} 资方分配超时，驳回订单");
                    $order_service->updateLoanTimeoutReject('资方分配超时');
                    continue;
                }

                if(!in_array($item->loan_term, [6,7]))
                {
                    $this->printMessage("订单号:{$item->id} 非7天订单不放款");
                    if(time() - $item->created_at > 86400)
                    {
                        $order_service->updateLoanTimeoutReject('非7天订单不放款');
                    }
                    continue;
                }


                //资方分配
                $ret = $order_service->reviewPass();
                if ($ret['code'] != 0) {
                    $this->printMessage(sprintf('order %s 分配不到资方: %s', $item['id'], json_encode($ret, JSON_UNESCAPED_UNICODE)));
                    continue;
                } else {
                    //复审通过(分配资方),触发待放款回调
                    $this->printMessage("order {$item['id']} success");
                }
            }


            $cloneQuery = clone $query;
            $queues = $cloneQuery->andWhere(['>', 'id', $maxId])->limit($limit)->orderBy(['id' => SORT_ASC])->all();
        }


        return self::EXIT_CODE_NORMAL;
    }


    /**
     * razorpay创建虚拟账户
     */
    public function actionCreateRazorpayVirtualAccount()
    {
        if(!$this->lock())
        {
            $this->printMessage("已有脚本进行中，退出处理");
            return;
        }
        $startTime = time();
        while ($orderId = intval(RedisQueue::pop([RedisQueue::LIST_RAZORPAY_CREATE_VIRTUAL_ACCOUNT]))) {
            continue; //临时关闭虚拟账号功能
            $this->printMessage("订单id：{$orderId},开始运行");
            try{
                /** @var UserLoanOrder $order */
                $order = UserLoanOrder::findOne($orderId);
                if(is_null($order))
                {
                    $this->printMessage("订单id：{$orderId},订单不存在,跳过处理");
                    continue;
                }
                $loanPerson = LoanPerson::findOne($order->user_id);
                if(is_null($loanPerson))
                {
                    $this->printMessage("订单id：{$orderId},用户id：{$order->user_id},用户不存在,跳过处理");
                    continue;
                }
                $service = new RepaymentService();
                if($service->createRazorpayUpiAddress($orderId, $order->user_id))
                {
                    $this->printMessage("订单id：{$orderId},创建成功");
                }
            }catch (\Exception $exception)
            {
                Yii::error("订单id：{$orderId},异常退出，重入队列.{$exception->getMessage()}", 'razorpay_virtual_account');
                RedisDelayQueue::pushDelayQueue(RedisQueue::LIST_RAZORPAY_CREATE_VIRTUAL_ACCOUNT, $orderId, 30);
            }
            if((time() - $startTime) >= 300)
            {
                $this->printMessage("脚本运行超过5分钟，退出脚本");

                return;
            }


        }
    }
    /**
     * 绑卡超时订单驳回
     */
    public function actionBindCardTimeout(){
        if(!$this->lock()){
            return;
        }

        $end_time = strtotime('-1 day');
        $orders = UserLoanOrder::find()
            ->where(['status' => UserLoanOrder::STATUS_WAIT_DEPOSIT])
            ->andWhere(['in', 'loan_status', [UserLoanOrder::LOAN_STATUS_WAIT_BIND_CARD,UserLoanOrder::LOAN_STATUS_BIND_CARD_CHECK]])
            ->andWhere(['<', 'order_time', $end_time])
            ->all();
        if(empty($orders)){
            $this->printMessage(sprintf('%s none order.', date('Y-m-d H:i')));
            return;
        }
        /** @var UserLoanOrder $order */
        foreach ($orders as $order){
            $order_service = new OrderService($order);
            $order_service->bankTimeoutReject('绑卡超时');
        }
        $this->printMessage('脚本结束');
    }

    /**
     * 人审超时订单驳回
     */
    public function actionManualCheckTimeout(){
        if(!$this->lock()){
            return;
        }

        $end_time = strtotime('-2 day');
        $orders = UserLoanOrder::find()
            ->where(['status' => UserLoanOrder::STATUS_CHECK])
            ->andWhere(['in', 'audit_status', [UserLoanOrder::AUDIT_STATUS_GET_ORDER,UserLoanOrder::AUDIT_STATUS_MANUAL_CHECK]])
            ->andWhere(['<', 'order_time', $end_time])
            ->all();
        if(empty($orders)){
            $this->printMessage(sprintf('%s none order.', date('Y-m-d H:i')));
            return;
        }
        /** @var UserLoanOrder $order */
        foreach ($orders as $order){
            $order_service = new OrderService($order);
            $order_service->manualTimeoutReject('人工审核超时');
        }
        $this->printMessage('脚本结束');
    }

    /**
     * 优惠券超时修改状态
     */
    public function actionUserCouponExpired(){
        if(!$this->lock()){
            return;
        }

        $userCoupons = UserCouponInfo::find()
            ->where(['is_use' => UserCouponInfo::STATUS_FALSE])
            ->andWhere(['<=', 'end_time', time()])
            ->all();
        if(empty($userCoupons)){
            $this->printMessage(sprintf('%s none UserCouponInfo.', date('Y-m-d H:i')));
            return;
        }

        /** @var UserCouponInfo $userCoupon */
        foreach ($userCoupons as $userCoupon){
            /** @var UserLoanOrderRepayment $user_repayment */
            $user_repayment = UserLoanOrderRepayment::find()->where(['coupon_id' => $userCoupon->id])
                ->andWhere(['!=', 'status', UserLoanOrderRepayment::STATUS_REPAY_COMPLETE])
                ->one();
            if(!empty($user_repayment)){
                $user_repayment->coupon_id = 0;
                $user_repayment->coupon_money = 0;

                if(!$user_repayment->save()){
                    Yii::error('order_id:'.$user_repayment->order_id.' save fail', 'UserCouponExpired');
                    continue;
                }
            }

            $userCoupon->is_use = UserCouponInfo::STATUS_INVALID;
            $userCoupon->save();
        }

        $this->printMessage('脚本结束');
    }


    /**
     * 资方配额报警
     */
    public function actionLoanFundQuotaAlert()
    {
        $loanFunds = LoanFund::find()
            ->where(['status' => LoanFund::STATUS_ENABLE])
            ->andWhere(['>' , 'alert_quota', 0])
            ->all();


        $date = date('Y-m-d');
        $weWorkService = new WeWorkService();
        /** @var LoanFund $loanFund */
        foreach ($loanFunds as $loanFund)
        {
            if(!empty($loanFund->alert_phones))
            {
                /** @var LoanFundDayQuota $quota */
                $quota = LoanFundDayQuota::find()->where([
                    'date' => $date,
                    'fund_id' => $loanFund->id
                ])->one();

                if(!empty($quota) && ($quota->remaining_quota < $loanFund->alert_quota))
                {
                    $remainingQuota = intval($quota->remaining_quota / 100);
                    $alertQuota = intval($loanFund->alert_quota / 100);
                    $message = "资方名称{$loanFund->name},剩余配额不足{$alertQuota}元，当前剩余配额为{$remainingQuota}元";

                    $users = explode(",", $loanFund->alert_phones);
                    if(!is_array($users))
                    {
                        $users = [$loanFund->alert_phones];
                    }
                    $weWorkService->sendText($users, $message);
                }

            }

        }
    }

    /**
     * 用户提现提醒通知-30分钟
     */
    public function actionRemindDrawMoneyAuto()
    {
        if (!$this->lock()) {
            return;
        }
        $now = time();
        $orderDataStr = RedisQueue::pop([RedisQueue::QUEUE_REMIND_ORDER_DRAW_MONEY_AUTO]);

        $delayTimeList = [20, 40, 60, 120, 240];

        while ($orderDataStr) {
            $orderData = json_decode($orderDataStr, true);
            $orderId = $orderData['order_id'];
            $delayMinute = $orderData['delay_minute'];
            $checkOrder = UserLoanOrder::findOne($orderId);

            if ($checkOrder &&
                $checkOrder->status == UserLoanOrder::STATUS_WAIT_DRAW_MONEY &&
                $checkOrder->loan_status == UserLoanOrder::LOAN_STATUS_DRAW_MONEY
            ) {
                $packageName = $checkOrder->clientInfoLog->package_name;
                $service = new OrderService($checkOrder);
                $service->sendMsgAndPushByOrderApprove($packageName, $delayMinute);
                $delayKey = array_search($delayMinute, $delayTimeList);
                if (isset($delayTimeList[$delayKey + 1])) {
                    $delayNextMinute = $delayTimeList[$delayKey + 1];
                    $pushOrderDataStr = json_encode([
                        'order_id'     => $orderId,
                        'delay_minute' => $delayNextMinute,
                    ]);
                    $delayDifferenceSecond = ($delayNextMinute - $delayMinute) * 60;
                    RedisDelayQueue::pushDelayQueue(
                        RedisQueue::QUEUE_REMIND_ORDER_DRAW_MONEY_AUTO,
                        $pushOrderDataStr,
                        $delayDifferenceSecond);
                }
            }
            if (time() - $now > 300) {
                $this->printMessage('运行满5分钟，关闭当前脚本');
                exit;
            }
            $orderDataStr = RedisQueue::pop([RedisQueue::QUEUE_REMIND_ORDER_DRAW_MONEY_AUTO]);
        }
    }

    /**
     * 用户提现提醒通知-30分钟
     */
    public function actionRemindDrawMoneyByHalf()
    {
        if(!$this->lock()){
            return;
        }
        $orderId = RedisQueue::pop([RedisQueue::QUEUE_REMIND_ORDER_DRAW_MONEY]);

        while ($orderId)
        {
            $checkOrder = UserLoanOrder::findOne($orderId);
            if ($checkOrder->status != UserLoanOrder::STATUS_WAIT_DRAW_MONEY ||
                $checkOrder->loan_status != UserLoanOrder::LOAN_STATUS_DRAW_MONEY
            ) {
                //检查最新的订单状态，不符合跳过
                $orderId = RedisQueue::pop([RedisQueue::QUEUE_REMIND_ORDER_DRAW_MONEY]);
                continue;
            } else {
                $packageName = $checkOrder->clientInfoLog->package_name;
                $service = new OrderService($checkOrder);
                $service->sendMsgAndPushByOrderApprove($packageName);

                $queueName = RedisQueue::QUEUE_REMIND_ORDER_DRAW_MONEY . '_1';
                RedisDelayQueue::pushDelayQueue($queueName, $orderId, 1800);
            }

            $orderId = RedisQueue::pop([RedisQueue::QUEUE_REMIND_ORDER_DRAW_MONEY]);
        }
    }

    /**
     * 用户提现提醒通知-1小时
     */
    public function actionRemindDrawMoneyBy1()
    {
        if(!$this->lock()){
            return;
        }
        $queueName = RedisQueue::QUEUE_REMIND_ORDER_DRAW_MONEY . '_1';
        $orderId = RedisQueue::pop([$queueName]);

        while ($orderId)
        {
            $checkOrder = UserLoanOrder::findOne($orderId);
            if ($checkOrder->status != UserLoanOrder::STATUS_WAIT_DRAW_MONEY ||
                $checkOrder->loan_status != UserLoanOrder::LOAN_STATUS_DRAW_MONEY
            ) {
                //检查最新的订单状态，不符合跳过
                $orderId = RedisQueue::pop([$queueName]);
                continue;
            } else {
                $packageName = $checkOrder->clientInfoLog->package_name;
                $service = new OrderService($checkOrder);
                $service->sendMsgAndPushByOrderApprove($packageName);

                $queueNameNext = RedisQueue::QUEUE_REMIND_ORDER_DRAW_MONEY . '_2';
                RedisDelayQueue::pushDelayQueue($queueNameNext, $orderId, 3600);
            }

            $orderId = RedisQueue::pop([$queueName]);
        }
    }

    /**
     * 用户提现提醒通知-2小时
     */
    public function actionRemindDrawMoneyBy2()
    {
        if(!$this->lock()){
            return;
        }
        $queueName = RedisQueue::QUEUE_REMIND_ORDER_DRAW_MONEY . '_2';
        $orderId = RedisQueue::pop([$queueName]);

        while ($orderId)
        {
            $checkOrder = UserLoanOrder::findOne($orderId);
            if ($checkOrder->status != UserLoanOrder::STATUS_WAIT_DRAW_MONEY ||
                $checkOrder->loan_status != UserLoanOrder::LOAN_STATUS_DRAW_MONEY
            ) {
                //检查最新的订单状态，不符合跳过
                $orderId = RedisQueue::pop([$queueName]);
                continue;
            } else {
                $packageName = $checkOrder->clientInfoLog->package_name;
                $service = new OrderService($checkOrder);
                $service->sendMsgAndPushByOrderApprove($packageName);

                $queueNameNext = RedisQueue::QUEUE_REMIND_ORDER_DRAW_MONEY . '_4';
                RedisDelayQueue::pushDelayQueue($queueNameNext, $orderId, 7200);
            }

            $orderId = RedisQueue::pop([$queueName]);
        }
    }

    /**
     * 用户提现提醒通知-4小时
     */
    public function actionRemindDrawMoneyBy4()
    {
        if(!$this->lock()){
            return;
        }
        $queueName = RedisQueue::QUEUE_REMIND_ORDER_DRAW_MONEY . '_4';
        $orderId = RedisQueue::pop([$queueName]);

        while ($orderId)
        {
            $checkOrder = UserLoanOrder::findOne($orderId);
            if ($checkOrder->status != UserLoanOrder::STATUS_WAIT_DRAW_MONEY ||
                $checkOrder->loan_status != UserLoanOrder::LOAN_STATUS_DRAW_MONEY
            ) {
                //检查最新的订单状态，不符合跳过
                $orderId = RedisQueue::pop([$queueName]);
                continue;
            } else {
                $packageName = $checkOrder->clientInfoLog->package_name;
                $service = new OrderService($checkOrder);
                $service->sendMsgAndPushByOrderApprove($packageName);

                $queueNameNext = RedisQueue::QUEUE_REMIND_ORDER_DRAW_MONEY . '_8';
                RedisDelayQueue::pushDelayQueue($queueNameNext, $orderId, 14400);
            }

            $orderId = RedisQueue::pop([$queueName]);
        }
    }
    /**
     * 用户提现提醒通知-8小时
     */
    public function actionRemindDrawMoneyBy8()
    {
        if(!$this->lock()){
            return;
        }
        $queueName = RedisQueue::QUEUE_REMIND_ORDER_DRAW_MONEY . '_8';
        $orderId = RedisQueue::pop([$queueName]);

        while ($orderId)
        {
            $checkOrder = UserLoanOrder::findOne($orderId);
            if ($checkOrder->status != UserLoanOrder::STATUS_WAIT_DRAW_MONEY ||
                $checkOrder->loan_status != UserLoanOrder::LOAN_STATUS_DRAW_MONEY
            ) {
                //检查最新的订单状态，不符合跳过
                $orderId = RedisQueue::pop([$queueName]);
                continue;
            } else {
                $packageName = $checkOrder->clientInfoLog->package_name;
                $service = new OrderService($checkOrder);
                $service->sendMsgAndPushByOrderApprove($packageName);

                $queueNameNext = RedisQueue::QUEUE_REMIND_ORDER_DRAW_MONEY . '_12';
                RedisDelayQueue::pushDelayQueue($queueNameNext, $orderId, 3600);
            }

            $orderId = RedisQueue::pop([$queueName]);
        }
    }
    /**
     * 用户提现提醒通知-12小时
     */
    public function actionRemindDrawMoneyBy12()
    {
        if(!$this->lock()){
            return;
        }
        $queueName = RedisQueue::QUEUE_REMIND_ORDER_DRAW_MONEY . '_12';
        $orderId = RedisQueue::pop([$queueName]);

        while ($orderId)
        {
            $checkOrder = UserLoanOrder::findOne($orderId);
            if ($checkOrder->status != UserLoanOrder::STATUS_WAIT_DRAW_MONEY ||
                $checkOrder->loan_status != UserLoanOrder::LOAN_STATUS_DRAW_MONEY
            ) {
                //检查最新的订单状态，不符合跳过
                $orderId = RedisQueue::pop([$queueName]);
                continue;
            } else {
                $packageName = $checkOrder->clientInfoLog->package_name;
                $service = new OrderService($checkOrder);
                $service->sendMsgAndPushByOrderApprove($packageName);

                $queueNameNext = RedisQueue::QUEUE_REMIND_ORDER_DRAW_MONEY . '_16';
                RedisDelayQueue::pushDelayQueue($queueNameNext, $orderId, 3600);
            }

            $orderId = RedisQueue::pop([$queueName]);
        }
    }
    /**
     * 用户提现提醒通知-16小时
     */
    public function actionRemindDrawMoneyBy16()
    {
        if(!$this->lock()){
            return;
        }
        $queueName = RedisQueue::QUEUE_REMIND_ORDER_DRAW_MONEY . '_16';
        $orderId = RedisQueue::pop([$queueName]);

        while ($orderId)
        {
            $checkOrder = UserLoanOrder::findOne($orderId);
            if ($checkOrder->status != UserLoanOrder::STATUS_WAIT_DRAW_MONEY ||
                $checkOrder->loan_status != UserLoanOrder::LOAN_STATUS_DRAW_MONEY
            ) {
                //检查最新的订单状态，不符合跳过
                $orderId = RedisQueue::pop([$queueName]);
                continue;
            } else {
                $packageName = $checkOrder->clientInfoLog->package_name;
                $service = new OrderService($checkOrder);
                $service->sendMsgAndPushByOrderApprove($packageName);

                $queueNameNext = RedisQueue::QUEUE_REMIND_ORDER_DRAW_MONEY . '_20';
                RedisDelayQueue::pushDelayQueue($queueNameNext, $orderId, 3600);
            }

            $orderId = RedisQueue::pop([$queueName]);
        }
    }
    /**
     * 用户提现提醒通知-20小时
     */
    public function actionRemindDrawMoneyBy20()
    {
        if(!$this->lock()){
            return;
        }
        $queueName = RedisQueue::QUEUE_REMIND_ORDER_DRAW_MONEY . '_20';
        $orderId = RedisQueue::pop([$queueName]);

        while ($orderId)
        {
            $checkOrder = UserLoanOrder::findOne($orderId);
            if ($checkOrder->status != UserLoanOrder::STATUS_WAIT_DRAW_MONEY ||
                $checkOrder->loan_status != UserLoanOrder::LOAN_STATUS_DRAW_MONEY
            ) {
                //检查最新的订单状态，不符合跳过
                $orderId = RedisQueue::pop([$queueName]);
                continue;
            } else {
                $packageName = $checkOrder->clientInfoLog->package_name;
                $service = new OrderService($checkOrder);
                $service->sendMsgAndPushByOrderApprove($packageName);
            }

            $orderId = RedisQueue::pop([$queueName]);
        }
    }

    /**
     * 用户还款未复借提醒通知-60分钟
     */
    public function actionRemindNoLoanAfterRepayAuto()
    {
        if (!$this->lock()) {
            return;
        }
        $now = time();
        $orderDataStr = RedisQueue::pop([RedisQueue::QUEUE_REMIND_NO_LOAN_AFTER_REPAY_AUTO]);

        $delayTimeList = [60];

        while ($orderDataStr) {
            $orderData = json_decode($orderDataStr, true);
            $orderId = $orderData['order_id'];
            $userId = $orderData['user_id'];
            $delayMinute = $orderData['delay_minute'];
            /** @var UserLoanOrder $checkOrder */
            $checkOrder = UserLoanOrder::find()->where(['id' => $orderId])->orderBy(['id' => SORT_DESC])->limit(1)->one();

            if ($checkOrder && $orderId == $checkOrder->id) {
                $packageName = $checkOrder->clientInfoLog->package_name;
                $service = new OrderService($checkOrder);
                $service->sendMsgAndPushByNoLoanAfterRepay();
                $delayKey = array_search($delayMinute, $delayTimeList);
                if (isset($delayTimeList[$delayKey + 1])) {
                    $delayNextMinute = $delayTimeList[$delayKey + 1];
                    $pushOrderDataStr = json_encode([
                        'order_id'     => $orderId,
                        'user_id'      => $userId,
                        'delay_minute' => $delayNextMinute,
                    ]);
                    $delayDifferenceSecond = ($delayNextMinute - $delayMinute) * 60;
                    RedisDelayQueue::pushDelayQueue(
                        RedisQueue::QUEUE_REMIND_NO_LOAN_AFTER_REPAY_AUTO,
                        $pushOrderDataStr,
                        $delayDifferenceSecond);
                }
            }
            if (time() - $now > 300) {
                $this->printMessage('运行满5分钟，关闭当前脚本');
                exit;
            }
            $orderDataStr = RedisQueue::pop([RedisQueue::QUEUE_REMIND_NO_LOAN_AFTER_REPAY_AUTO]);
        }
    }

    /**
     * 提现超时
     */
    public function actionWithdrawalTimeoutReject()
    {
        if(!$this->lock()){
            return;
        }
        $this->printMessage('脚本开始');

        $maxId = 0;
        $endTime = time() - 86400 * 2;

        $query = UserLoanOrder::find()
            ->where(['status' => UserLoanOrder::STATUS_WAIT_DRAW_MONEY])
            ->andWhere(['<', 'order_time', $endTime])
            ->orderBy(['id' => SORT_ASC])->limit(200);

        $cloneQuery = clone $query;
        $orders = $cloneQuery->andWhere(['>', 'id', $maxId])->all();

        while ($orders)
        {
            /** @var UserLoanOrder $order */
            foreach ($orders as $order)
            {
                $maxId = $order->id;
                $this->printMessage("订单号:{$order->id}，开始处理");
                $item = UserLoanOrder::findOne($order->id);
                if(UserLoanOrder::STATUS_WAIT_DRAW_MONEY != $item->status)
                {
                    $this->printMessage("订单号:{$order->id}，当前状态不为待提现，跳过处理");
                    continue;
                }
                $service = new OrderService($item);
                if($service->withdrawalTimeoutReject())
                {
                    $this->printMessage("订单号:{$order->id}，操作成功");
                }else{
                    $this->printMessage("订单号:{$order->id}，操作失败");
                }
            }

            $cloneQuery = clone $query;
            $orders = $cloneQuery->andWhere(['>', 'id', $maxId])->all();

        }

        $this->printMessage('脚本执行结束');

    }

    public function actionPushExternalOrderCanLoanTime($code = null)
    {
        if(!$this->lock()){
            $this->printMessage('PushExternalOrderCanLoanTime 已经运行中,关闭脚本');
            return ExitCode::UNSPECIFIED_ERROR;
        }
        $startTime = time();
        $execTime = mt_rand(240, 300);
        $this->printMessage('PushExternalOrderCanLoanTime 脚本开始');
        while (true) {
            if ((time() - $startTime) > $execTime) {
                $this->printMessage("脚本运行超过{$execTime}秒，退出脚本");
                return ExitCode::UNSPECIFIED_ERROR;
            }
            $item = RedisQueue::pop([RedisQueue::LIST_EXTERNAL_ORDER_CAN_LOAN_TIME]);
            if (!$item) {
                sleep(5);
                continue;
            }
            $itemData = json_decode($item, true);
            $service = new ExternalOrderPushData();
            $this->printMessage("订单号：{$itemData['orderUuid']}, 开始运行");
            try {
                $result = $service->pushCanLoanTime($itemData);
                if (!isset($result['code']) || $result['code'] != 0) {
                    throw new \Exception(json_encode($result));
                }
            } catch (\Exception $exception) {
                $this->printMessage("订单号：{$itemData['orderUuid']},异常退出.{$exception->getMessage()}");
                yii::error("订单号：{$itemData['orderUuid']},异常退出.{$exception->getMessage()}", 'push_order_set_can_loan_time');
            }
            $this->printMessage("订单号：{$itemData['orderUuid']}, 结束运行");
        }
        return ExitCode::OK;
    }

    /**
     * 事件短信
     */
    public function actionEventMessage()
    {
        $startTime = time();
        while (true) {
            if (time() - $startTime > 300) {
                $this->printMessage('运行满5分钟，关闭当前脚本');
                exit;
            }
            $orderData = RedisQueue::pop([RedisQueue::LIST_EXTERNAL_ORDER_MESSAGE]);
            if(!$orderData)
            {
                sleep(2);
                continue;
            }
            $orderArr    = json_decode($orderData, true);
            $form        = new ExternalOrderMessageForm();
            $form->load($orderArr,'');
            $orderId     = $form->orderUuid;
            $merchantId  = $form->merchantId;
            $this->printMessage("订单号：{$orderId},商户号:{$merchantId}开始运行");

            /** @var UserLoanOrder $order */
            $order = UserLoanOrder::find()->where(['order_uuid'=>$orderId])->one();
            if(!$order)
            {
                $this->printMessage("订单号：{$orderId}未找到!");
                continue;
            }

            //短信
            MessageHelper::sendAll($form->phone,$form->message,SendMessageService::$smsNotifyConfigList[$form->packageName]);

        }
    }
    /**
     * 事件推送
     */
    public function actionInsideEventPush()
    {
        $startTime = time();
        while (true) {
            if (time() - $startTime > 300) {
                $this->printMessage('运行满5分钟，关闭当前脚本');
                exit;
            }
            $orderData = RedisQueue::pop([RedisQueue::LIST_INSIDE_ORDER_PUSH]);
            if(!$orderData)
            {
                sleep(2);
                continue;
            }
            $orderArr    = json_decode($orderData, true);
            $form        = new ExternalOrderMessageForm();
            $form->load($orderArr,'');
            $orderId     = $form->orderUuid;
            $merchantId  = $form->merchantId;
            $this->printMessage("订单号：{$orderId},商户号:{$merchantId}开始运行");

            $pushService = new FirebasePushService($form->packageName);

            //推送消息
            $pushService->pushToUser($form->userId, $form->title, $form->message);
        }
    }

    /**
     * 人审绑卡拒绝,提示绑卡脚本
     */
    public function actionBindCardReject()
    {
        if (!$this->lock()) {
            return;
        }
        $now = time();
        $delayTimeList = [30,60];

        while (true) {
            if (time() - $now > 300) {
                $this->printMessage('运行满5分钟，关闭当前脚本');
                exit;
            }
            $orderDataStr = RedisQueue::pop([RedisQueue::QUEUE_BIND_CARD_REJECT]);
            if(!$orderDataStr)
            {
                $this->printMessage('无需处理订单，继续等待');
                sleep(1);
                continue;
            }
            $orderData    = json_decode($orderDataStr, true);
            $orderId      = $orderData['order_id'];
            $userId       = $orderData['user_id'];
            $delayMinute  = $orderData['delay_minute'];
            /** @var UserLoanOrder $checkOrder */
            $checkOrder = UserLoanOrder::find()->where(['id' => $orderId])->one();

            if ($checkOrder && UserLoanOrder::STATUS_WAIT_DEPOSIT == $checkOrder->status && UserLoanOrder::LOAN_STATUS_WAIT_BIND_CARD == $checkOrder->loan_status) {
                $service = new OrderService($checkOrder);
                $service->sendMsgAndPushByBindCardReject($delayMinute);
                $delayKey = array_search($delayMinute, $delayTimeList);
                if (isset($delayTimeList[$delayKey + 1])) {
                    $delayNextMinute = $delayTimeList[$delayKey + 1];
                    $pushOrderDataStr = json_encode([
                        'order_id'     => $orderId,
                        'user_id'      => $userId,
                        'delay_minute' => $delayNextMinute,
                    ]);
                    $delayDifferenceSecond = ($delayNextMinute - $delayMinute) * 60;
                    RedisDelayQueue::pushDelayQueue(
                        RedisQueue::QUEUE_BIND_CARD_REJECT,
                        $pushOrderDataStr,
                        $delayDifferenceSecond);
                }
            }
        }
    }

    public function actionFixOrderAllFirst()
    {
        if(!$this->lock()){
            return;
        }
        $this->printMessage('脚本开始');

        $maxId = 0;

        $query = UserLoanOrder::find()
            ->orderBy(['id' => SORT_ASC])
            ->limit(200);

        $cloneQuery = clone $query;
        $orders = $cloneQuery->where(['>', 'id', $maxId])->all();

        while ($orders)
        {
            /** @var UserLoanOrder $order */
            foreach ($orders as $order)
            {
                $maxId = $order->id;
                $this->printMessage("订单号:{$order->id}，开始处理");
                $user = $order->loanPerson;
                if(empty($user->pan_code))
                {
                    $this->printMessage("订单号:{$order->id}，当前pan卡为空，跳过处理");
                    continue;
                }
                $isAllFirst = LoanPersonExternal::isAllPlatformNewCustomerByTime($user->pan_code, $order->order_time) ?
                    UserLoanOrder::FIRST_LOAN_IS : UserLoanOrder::FIRST_LOAN_NO;
                $order->detachBehaviors();
                $order->is_all_first = $isAllFirst;
                $order->save();
            }

            $cloneQuery = clone $query;
            $orders = $cloneQuery->where(['>', 'id', $maxId])->all();

        }

        $this->printMessage('脚本执行结束');
    }


    /**
     * 自动提现脚本
     */
    public function actionAutoDraw()
    {
        if (!$this->lock()) {
            return;
        }

        $orders = UserLoanOrder::find()->select(['id'])->where([
            'status' => UserLoanOrder::STATUS_WAIT_DRAW_MONEY,
            'loan_status' => UserLoanOrder::LOAN_STATUS_DRAW_MONEY,
            'auto_draw' => UserLoanOrder::AUTO_DRAW_YES,
        ])->andWhere(['<=', 'auto_draw_time', time()])->limit(1000)->asArray()->all();
        foreach ($orders as $order)
        {
            $item = UserLoanOrder::findOne($order['id']);
            if(!(UserLoanOrder::STATUS_WAIT_DRAW_MONEY == $item->status && UserLoanOrder::LOAN_STATUS_DRAW_MONEY == $item->loan_status))
            {
                $this->printMessage("order_id:{$order['id']},状态已变更，跳过处理");
                continue;
            }
            $orderService = new OrderService($item);
            if(!$orderService->applyDraw('system auto draw'))
            {
                $this->printMessage("order_id:{$order['id']},操作失败");
            }
        }
    }


    /**
     * 手动驳回待分配资方状态的订单
     * @param $orderID
     */
    public function actionOrderFundReject($orderID)
    {
        $order = UserLoanOrder::findOne($orderID);
        $service = new OrderService($order);
        $t = $service->updateLoanTimeoutReject('资方分配超时');
        var_dump($t);
    }



    /**
     * 初始化资方配额
     */
    public function actionInitFundQuota()
    {
        $models = LoanFund::find()->all();
        foreach ($models as $model)
        {
            $model->getTodayRemainingQuota();
        }
    }


    public function actionLoanFundQuotaRedisSet()
    {
        if (!$this->lock()) {
            return;
        }

        $maxRunTime = time() + 5 * 60 - 5;

        $this->printMessage("脚本开始, 将于" . date('H:i:s', $maxRunTime) . '结束进程');

        while (($maxRunTime - time()) >= 0)
        {
            $date = date('Y-m-d');

            //商户id和包名的对应关系
            $merchantMap = [];
            $packages = PackageSetting::find()->select(['package_name', 'merchant_id'])->all();
            foreach ($packages as $package)
            {
                $merchantMap[$package['merchant_id']] = $package['package_name'];
            }

            //资方客户类型的对应关系
            $customerTypeMap = [
                LoanFundDayQuota::TYPE_NEW => '_new', //全新本新
                LoanFundDayQuota::TYPE_OLD => '_self_new_all_old', //全老本新
                LoanFundDayQuota::TYPE_REAL_OLD => '_old', //全老本老
            ];


            $sql = "select q.merchant_id as merchant_id,q.type as type,sum(if(f.`status`= 0, q.remaining_quota, 0)) as remaining_quota from tb_loan_fund_day_quota as q
LEFT JOIN tb_loan_fund as f
on q.fund_id = f.id
where f.is_export = 1 and date = '{$date}'
GROUP BY q.merchant_id,q.type";

            $datas = Yii::$app->db->createCommand($sql)->queryAll();

            foreach ($datas as $data) {
                $saasPackageName = $merchantMap[$data['merchant_id']];
                $customerType = $customerTypeMap[$data['type']];
                $fundMoneyKey = 'fund_quota_' . $saasPackageName . $customerType;
                RedisQueue::set(['expire'=> 86400,'key'=> $fundMoneyKey, 'value'=> $data['remaining_quota']]);
            }

            sleep(5);
        }

        $this->printMessage('脚本结束');

    }


    public function actionFixFundMatched()
    {
        $orders  = UserLoanOrder::find()
            ->select(['id'])
            ->where([
                'status' => UserLoanOrder::STATUS_LOANING,
                'loan_status' => UserLoanOrder::LOAN_STATUS_FUND_MATCHED
            ])->andWhere([
                '<' , 'updated_at', time() - 86400 / 2
            ])->all();

        foreach ($orders as $order)
        {
            $item = UserLoanOrder::findOne($order['id']);
            if( ! (UserLoanOrder::STATUS_LOANING == $item->status && UserLoanOrder::LOAN_STATUS_FUND_MATCHED == $item->loan_status))
            {
                $this->printMessage("orderID:{$item->id} ,状态不正确");
                continue;
            }
            $service = new OrderService($item);
            $service->orderLoanReject(0, 'bank name is null');
        }
    }

    public function actionBeforeManualCredit()
    {
        if (!$this->lock()) {
            return;
        }
        $startTime = time();
        while (true) {
            if (time() - $startTime > 300) {
                $this->printMessage('运行满5分钟，关闭当前脚本');
                exit;
            }
            $orderData = RedisQueue::pop([RedisQueue::PUSH_MANUAL_CREDIT_ORDER_DATA]);
            if(!$orderData)
            {
                sleep(2);
                continue;
            }
            $orderArr  = json_decode($orderData, true);
            $orderId = $orderArr['order_id'];
            $packageName = $orderArr['package_name'];
            $panCode = $orderArr['pan_code'];

            /** @var UserLoanOrder $userLoanOrder */
            $userLoanOrder = UserLoanOrder::find()
                ->where([
                    'id' => $orderId,
                    'status' => UserLoanOrder::STATUS_CHECK,
                    'audit_status' => UserLoanOrder::AUDIT_STATUS_GET_ORDER
                ])->one();

            $operateKey = sprintf('%s:%s', RedisQueue::BEFORE_MANUAL_CREDIT_ORDER_ALERT,$orderId);
            if(!$userLoanOrder){
                $this->printMessage('orderId:'.$orderId.'无order跳过');
                RedisQueue::del(['key' => $operateKey]);
                continue;
            }

            /** @var RiskResultSnapshot $riskResultSnapshot */
            $riskResultSnapshot = RiskResultSnapshot::find()->where(['order_id' => $orderId,'result' => 'manual'])->one();
            if(!$riskResultSnapshot){
                $this->printMessage('orderId:'.$userLoanOrder->id.'无RiskResult跳过');
                RedisQueue::del(['key' => $operateKey]);
                continue;
            }

            $modules = [];
            if($manualNode = json_decode($riskResultSnapshot->manual_node, true)){
                if($manualNode){
                    $modules = array_keys($manualNode);
                }
            }

            $result = [];
            $isReject = false;
            $rejectRule = [];
            $noOther = true;
            $moduleRes = [];
            foreach ($modules as $module){
                if($module == 'Module2') {  //Name Match
                    $creditRes = [];
                    $mRes = [];
                    //查上一次 同包同pan_code的审核日志
                    $resLoan = ManualCreditLog::find()
                        ->select(['r' => '(que_info->\'$."7"\')','created_at'])
                        ->where(['pan_code' => $panCode,'package_name' => $packageName,'action' => ManualCreditLog::ACTION_AUDIT_CREDIT])
                        ->andWhere('`que_info`->\'$."7"\' IN ("1","2")')
                        ->orderBy(['id' => SORT_DESC])
                        ->limit(1)
                        ->asArray()
                        ->one();
                    if($resLoan){
                        $creditRes[$resLoan['created_at']] = $resLoan['r'];
                    }
                    $resSaas = ManualCreditLog::find()
                        ->select(['r' => '(que_info->\'$."7"\')','created_at'])
                        ->where(['pan_code' => $panCode,'package_name' => $packageName,'action' => ManualCreditLog::ACTION_AUDIT_CREDIT])
                        ->andWhere('`que_info`->\'$."7"\' IN ("1","2")')
                        ->orderBy(['id' => SORT_DESC])
                        ->limit(1)
                        ->asArray()
                        ->one(\Yii::$app->get('db_loan'));
                    if($resSaas){
                        $creditRes[$resSaas['created_at']] = $resSaas['r'];
                    }

                    if($creditRes){
                        $maxKey = max(array_keys($creditRes));
                        $res = $creditRes[$maxKey];
                        $result['7'] = '1';
                        if($res == '"2"'){  //审核拒绝
                            $result['7'] = '2';
                            $rejectRule = ['id' => 7,'head_code' => 'Module2','back_code' => '10'];
                            $isReject = true;
                        }
                        $mRes = ['7' => $result['7']];
                    }
                    $moduleRes[$module] = $mRes;
                }elseif ($module == 'Module4') {  //Face Photo Comparison
                    $creditRes = [];
                    $mRes = [];
                    $resLoan = ManualCreditLog::find()
                        ->select(['r' => '(que_info->\'$."9"\')','created_at'])
                        ->where(['pan_code' => $panCode,'package_name' => $packageName,'action' => ManualCreditLog::ACTION_AUDIT_CREDIT])
                        ->andWhere([
                            'AND',
                            ['>','created_at',time() - 3600],
                            '`que_info`->\'$."9"\' IN ("1","2")',
                        ])
                        ->orderBy(['id' => SORT_DESC])
                        ->limit(1)
                        ->asArray()
                        ->one();
                    if($resLoan){
                        $creditRes[$resLoan['created_at']] = $resLoan['r'];
                    }
                    $resSaas = ManualCreditLog::find()
                        ->select(['r' => '(que_info->\'$."9"\')','created_at'])
                        ->where(['pan_code' => $panCode,'package_name' => $packageName,'action' => ManualCreditLog::ACTION_AUDIT_CREDIT])
                        ->andWhere([
                            'AND',
                            ['>','created_at',time() - 3600],
                            '`que_info`->\'$."9"\' IN ("1","2")',
                        ])
                        ->orderBy(['id' => SORT_DESC])
                        ->limit(1)
                        ->asArray()
                        ->one(\Yii::$app->get('db_loan'));
                    if($resSaas){
                        $creditRes[$resSaas['created_at']] = $resSaas['r'];
                    }
                    if($creditRes){
                        $maxKey = max(array_keys($creditRes));
                        $res = $creditRes[$maxKey];
                        $result['9'] = '1';
                        if($res == '"2"'){  //审核拒绝
                            $result['9'] = '2';
                            $rejectRule = ['id' => 9,'head_code' => 'Module4','back_code' => '10'];
                            $isReject = true;
                        }
                        $mRes = ['9' => $result['9']];
                    }
                    $moduleRes[$module] = $mRes;
                }else{
                    $noOther = false;
                }
            }

            $service = new OrderService($userLoanOrder);
            $service->setOperator(0);
            if($isReject){
                $res = $service->manualCheckReject('自动人审拒绝',7,$rejectRule,$result,1);
                if($res){
                    $this->printMessage('orderId:'.$userLoanOrder->id.'拒绝');
                }else{
                    $this->printMessage('orderId:'.$service->getError());
                }
            }else{
                if($noOther && !empty($result)){
                    $isAllPass = true;
                    $isPassArr = [];
                    foreach ($moduleRes as $moduleName => $val){
                        if(empty($val)){
                            $isAllPass = false;
                        }else{
                            $isPassArr[$moduleName] = $val;
                        }
                    }
                    if($isAllPass){
                        $res = $service->manualCheckApprove('自动人审通过',$result,1);
                        if($res){
                            $this->printMessage('orderId:'.$userLoanOrder->id.'通过');
                        }else{
                            $this->printMessage('orderId:'.$service->getError());
                        }
                    }
                }
            }
            RedisQueue::del(['key' => $operateKey]);

        }
    }

    public function actionBeforeManualBankCredit()
    {
        if (!$this->lock()) {
            return;
        }
        $startTime = time();
        while (true) {
            if (time() - $startTime > 300) {
                $this->printMessage('运行满5分钟，关闭当前脚本');
                exit;
            }
            $orderData = RedisQueue::pop([RedisQueue::PUSH_MANUAL_CREDIT_BANK_ORDER_DATA]);
            if(!$orderData)
            {
                sleep(2);
                continue;
            }
            $orderArr  = json_decode($orderData, true);
            $orderId = $orderArr['order_id'];
            $panCode = $orderArr['pan_code'];
            $bankAccount = $orderArr['bank_account'];

            /** @var UserLoanOrder $userLoanOrder */
            $userLoanOrder = UserLoanOrder::find()
                ->where([
                    'id' => $orderId,
                    'status' => UserLoanOrder::STATUS_WAIT_DEPOSIT,
                    'loan_status' => UserLoanOrder::LOAN_STATUS_BIND_CARD_CHECK
                ])->one();

            $operateKey = sprintf('%s:%s', RedisQueue::BEFORE_MANUAL_CREDIT_BANK_ORDER_ALERT,$orderId);
            if(!$userLoanOrder){
                $this->printMessage('orderId:'.$orderId.'无order跳过');
                RedisQueue::del(['key' => $operateKey]);
                continue;
            }

            $result = [];
            $isReject = false;
            $rejectRule = [];

            $creditRes = [];
            $resLoan = ManualCreditLog::find()
                ->select(['r' => '(que_info->\'$."8"\')','created_at'])
                ->where(['pan_code' => $panCode,'bank_account' => $bankAccount,'action' => ManualCreditLog::ACTION_AUDIT_BANK])
                ->andWhere('`que_info`->\'$."8"\' IN ("1","2")')
                ->orderBy(['id' => SORT_DESC])
                ->limit(1)
                ->asArray()
                ->one();
            if($resLoan){
                $creditRes[$resLoan['created_at']] = $resLoan['r'];
            }
            $resSaas = ManualCreditLog::find()
                ->select(['r' => '(que_info->\'$."8"\')','created_at'])
                ->where(['pan_code' => $panCode,'bank_account' => $bankAccount,'action' => ManualCreditLog::ACTION_AUDIT_BANK])
                ->andWhere('`que_info`->\'$."8"\' IN ("1","2")')
                ->orderBy(['id' => SORT_DESC])
                ->limit(1)
                ->asArray()
                ->one(\Yii::$app->get('db_loan'));
            if($resLoan){
                $creditRes[$resSaas['created_at']] = $resSaas['r'];
            }

            if($creditRes){
                $maxKey = max(array_keys($creditRes));
                $res = $creditRes[$maxKey];
                $result['8'] = '1';
                if($res == '"2"'){  //审核拒绝
                    $result['8'] = '2';
                    $rejectRule = ['id' => 8,'head_code' => 'Module3','back_code' => '10'];
                    $isReject = true;
                }
            }

            $service = new OrderService($userLoanOrder);
            $service->setOperator(0);
            if($isReject){
                $res = $service->bankCheckReject('自动人审绑卡拒绝',$rejectRule,$result,1);
                if($res){
                    $this->printMessage('orderId:'.$userLoanOrder->id.'拒绝');
                }else{
                    $this->printMessage('orderId:'.$service->getError());
                }
            }else{
                if(!empty($result)){
                    $res = $service->bankCheckApprove('自动人审绑卡通过',$result,1);
                    if($res){
                        $this->printMessage('orderId:'.$userLoanOrder->id.'通过');
                    }else{
                        $this->printMessage('orderId:'.$service->getError());
                    }
                }
            }
            RedisQueue::del(['key' => $operateKey]);

        }
    }
}


<?php

namespace console\controllers;

use backend\models\remind\RemindAdmin;
use backend\models\remind\RemindDispatchLog;
use backend\models\remind\RemindOrder;
use backend\models\remind\RemindSetting;
use backend\models\ReminderCallData;
use common\helpers\RedisDelayQueue;
use common\helpers\RedisQueue;
use common\models\order\UserLoanOrder;
use common\models\order\UserLoanOrderExtraRelation;
use common\models\order\UserLoanOrderRepayment;
use common\models\user\LoanPerson;
use common\models\user\UserActiveTime;
use common\models\user\UserContact;
use common\services\message\WeWorkService;
use common\services\order\PushOrderRemindService;
use Yii;
use yii\console\ExitCode;

class RemindController extends BaseController {

    public static $merchant_remind_config = [
//        2 => 5,  //百分至50不提醒  moneyclick
//        6 => 5,  //RupeeFanta
        15 => 5,  //NewCash
    ];

    /**
     * 到期日入提醒并推入队列
     */
    public function actionOrderRemind(){
        if(!$this->lock())
        {
            return;
        }
        $today = strtotime(date("Y-m-d",time())); // 今天

        $this->printMessage('脚本开始');
        $db_read = \Yii::$app->get('db_read_1');
        for ($i=0; $i<=1; $i++){
            $beforeTime = $today + 86400 * $i;
            $endTime = $beforeTime + 86400;
            $start_id = 0;
            $sql = UserLoanOrderRepayment::find()
                ->from(UserLoanOrderRepayment::tableName().' as A')
                ->leftJoin(UserLoanOrder::tableName(). ' as B','A.order_id = B.id')
                ->select('A.id,B.is_all_first,B.is_first,A.merchant_id')
                ->where(['A.status' => UserLoanOrderRepayment::STATUS_NORAML])
                ->andWhere(['<','A.plan_repayment_time',$endTime])
                ->andWhere(['>=','A.plan_repayment_time',$beforeTime]);
            $query = clone $sql;

            $all_ids = $query->andWhere(['>', 'A.id', $start_id])
                ->orderBy(['A.id' => SORT_ASC])->asArray()
                ->limit(5000)->all($db_read);

            if(empty($all_ids))
            {
                $this->printMessage('无匹配数据，跳过:'.$i);
                continue;
            }


            while($all_ids){
                $allFirstNoArr = [];
                foreach($all_ids as $id){
                    $isAllFirst = $id['is_all_first'];
                    $isFirst = $id['is_first'];
                    $merchantId = $id['merchant_id'];
                    $id = $id['id'];
                    $isTest = 0;
                    if ($i == 0 && $isAllFirst == UserLoanOrder::FIRST_LOAN_NO && $isFirst == UserLoanOrder::FIRST_LOAN_NO && isset(self::$merchant_remind_config[$merchantId])){
                        if(isset($allFirstNoArr[$merchantId])){
                            $allFirstNoArr[$merchantId]++;
                        }else{
                            $allFirstNoArr[$merchantId] = 1;
                        }
                        $ys = $allFirstNoArr[$merchantId] % 10;
                        if($ys <= self::$merchant_remind_config[$merchantId] && $ys > 0){
                            $isTest = 1;
                        }
                    }
                    echo '加入队列repaymentId:'.$id.PHP_EOL;
                    RedisQueue::push([RedisQueue::REMIND_ORDER_LIST,json_encode(['repayment_id'=>$id, 'admin_user_id'=> 0, 'plan_date_before_day' => $i, 'is_test' => $isTest])]);

                }
                $start_id = $id;
                $all_ids = $query->andWhere(['>', 'A.id', $start_id])
                    ->orderBy(['A.id' => SORT_ASC])->asArray()
                    ->limit(5000)->all($db_read);
            }
        }


        $this->printMessage('脚本结束');

    }


    public function actionRemindDispatch(){
        if(!$this->lock())
        {
            return;
        }
        $db_read = \Yii::$app->get('db_read_1');
        $this->printMessage('脚本开始');
        //定时任务推入队列
        $remindSettings = RemindSetting::find()
            ->where(['run_status' => RemindSetting::RUN_STATUS_DEFAULT])
            ->andWhere(['<','run_time',time()])
            ->all($db_read);

        /** @var RemindSetting $remindSetting */
        foreach ($remindSettings as $remindSetting){
            echo '定时任务ID'.$remindSetting->id.'开始导入...'.PHP_EOL;
            $remindSetting->run_status = RemindSetting::RUN_STATUS_FINISH;
            $remindSetting->save();

            $beforeTime = strtotime('today') + 86400 * $remindSetting->plan_date_before_day;
            $endTime = $beforeTime + 86400;

            $start_id = 0;
            $sql = UserLoanOrderRepayment::find()
                ->from(UserLoanOrderRepayment::tableName().' as A')
                ->leftJoin(UserLoanOrder::tableName(). ' as B','A.order_id = B.id')
                ->select('A.id,B.is_all_first,B.is_first,A.merchant_id')
                ->where(['A.status' => UserLoanOrderRepayment::STATUS_NORAML,'A.merchant_id' => $remindSetting->merchant_id])
                ->andWhere(['<','A.plan_repayment_time',$endTime])
                ->andWhere(['>=','A.plan_repayment_time',$beforeTime]);
            $query = clone $sql;
            $all_ids = $query->andWhere(['>', 'A.id', $start_id])
                ->orderBy(['A.id' => SORT_ASC])->asArray()
                ->limit(5000)->all($db_read);

            if(empty($all_ids))
            {
                echo '无匹配数据'.PHP_EOL;
            }
            while($all_ids){
                foreach($all_ids as $id){
                    $id = $id['id'];
                    echo '加入队列repaymentId:'.$id.PHP_EOL;
                    RedisQueue::push([RedisQueue::REMIND_ORDER_LIST,json_encode(['repayment_id'=>$id, 'admin_user_id'=> 0, 'plan_date_before_day' => $remindSetting->plan_date_before_day])]);
                }
                $start_id = $id;
                $all_ids = $query->andWhere(['>', 'A.id', $start_id])
                    ->orderBy(['A.id' => SORT_ASC])->asArray()
                    ->limit(5000)->all($db_read);
            }
            echo '定时任务ID'.$remindSetting->id.'导入完成'.PHP_EOL;
        }

        echo '开始分配提醒订单'.PHP_EOL;
        while ($dispatchJsonInfo = RedisQueue::pop([RedisQueue::REMIND_ORDER_LIST])){
            $dispatchInfo = json_decode($dispatchJsonInfo,true);
            $repaymentId = $dispatchInfo['repayment_id'];
            $adminUserId = $dispatchInfo['admin_user_id'];

            $repayment = UserLoanOrderRepayment::findOne($repaymentId);
            /** @var RemindOrder $remindOrder */
            $remindOrder = RemindOrder::find()->where(['repayment_id' => $repaymentId])->one();
            if(is_null($remindOrder)){
                //添加提醒订单
                $remindOrder = new RemindOrder();
                $remindOrder->repayment_id = $repaymentId;
                $remindOrder->customer_user_id = $adminUserId;
                $remindOrder->status = RemindOrder::STATUS_WAIT_REMIND;
                $remindOrder->dispatch_status = RemindOrder::STATUS_WAIT_DISPATCH;
                $remindOrder->merchant_id = $repayment->merchant_id;
                if(isset($dispatchInfo['plan_date_before_day'])){
                    $remindOrder->plan_date_before_day = $dispatchInfo['plan_date_before_day'];
                }
                $remindOrder->is_test = $dispatchInfo['is_test'] ?? 0;
                $remindOrder->save();

                echo 'SUCCESS repaymentId:'.$repaymentId.' adminUserId:'.$adminUserId.PHP_EOL;

            }else{
                if(isset($dispatchInfo['plan_date_before_day'])){
                    $remindOrder->plan_date_before_day = $dispatchInfo['plan_date_before_day'];
                    if($dispatchInfo['plan_date_before_day'] == 0 && isset($dispatchInfo['is_test'])){
                        $remindOrder->is_test = $dispatchInfo['is_test'];
                    }
                    $remindOrder->save();
                }
                //分派更新订单
                if($remindOrder->customer_user_id == $adminUserId){
                    echo 'Skip 1 repayment_id:'.$repaymentId.' adminUserId:'.$adminUserId.PHP_EOL;
                    //已分配过跳过
                    continue;
                }
                if($remindOrder->dispatch_status != RemindOrder::STATUS_WAIT_DISPATCH){
                    echo 'Skip 2 repayment_id:'.$repaymentId.' adminUserId:'.$adminUserId.PHP_EOL;
                    //已分配过跳过
                    continue;
                }
                $before_customer_user_id = $remindOrder->customer_user_id;
                $before_customer_group = $remindOrder->customer_group;

                /** @var RemindAdmin $remindAdmin */
                $remindAdmin = RemindAdmin::find()->where(['admin_user_id' => $adminUserId])->one();
                if($remindAdmin){
                    $after_customer_group = $remindAdmin->remind_group;
                }else{
                    $after_customer_group = 0;
                }

                //分配后
                $remindOrder->dispatch_time = time();
                $remindOrder->customer_user_id = $adminUserId;
                $remindOrder->customer_group = $after_customer_group;
                $remindOrder->dispatch_status = RemindOrder::STATUS_FINISH_DISPATCH;
                $remindOrder->save();


                $remindDispatchLog = new RemindDispatchLog();
                $remindDispatchLog->remind_id = $remindOrder->id;
                $remindDispatchLog->before_customer_user_id = $before_customer_user_id;
                $remindDispatchLog->after_customer_user_id = $adminUserId;
                $remindDispatchLog->before_customer_group = $before_customer_group;
                $remindDispatchLog->after_customer_group = $after_customer_group;
                $remindDispatchLog->save();

                echo 'SUCCESS update repaymentId:'.$repaymentId.' adminUserId:'.$adminUserId.PHP_EOL;
            }


        }
        echo '分配提醒订单结束'.PHP_EOL;
        $this->printMessage('脚本结束');
    }

    public function actionRemindOrderChangeStatus(){
        if(!$this->lock()){
            return;
        }

        $now = time();
        while(true)
        {
            if (time() - $now > 300) {
                $this->printMessage('运行满5分钟，关闭当前脚本');
                exit;
            }

            $data = RedisQueue::pop([RedisQueue::REMIND_ORDER_CHANGE_STATUS]);
            if (empty($data)) {
                $this->printMessage('无需处理订单，继续等待');
                exit;
            }

            $data = json_decode($data, true);
            $this->printMessage("repaymentId:{$data['id']}开始处理");

            /**
             * @var RemindOrder $order
             */
            $order = RemindOrder::findOne(['repayment_id' => $data['id']]);
            if (empty($order)) {
                $this->printMessage("还款订单ID:{$data['id']}不存在");
                continue;
            }

            if($data['status'] == RemindOrder::STATUS_REPAY_COMPLETE && $order->dispatch_status == RemindOrder::STATUS_IS_OVERDUE){
                continue;
            }

            $order->dispatch_status = $data['status'];
            $order->save();
        }
    }

    public function actionRemindRecycle(){
        if(!$this->lock()){
            return;
        }

        $this->printMessage('脚本开始');

        RemindOrder::updateAll([
            'customer_user_id' => 0,
            'customer_group' => 0,
            'dispatch_status' => RemindOrder::STATUS_WAIT_DISPATCH
        ], [
            'dispatch_status' => RemindOrder::STATUS_FINISH_DISPATCH
        ]);
        $this->printMessage('脚本结束');
    }

    /**
     * 识别用户短信关键字队列
     */
    public function actionSelectUserMessageKeyword()
    {
        if (!$this->lock()) {
            return;
        }
        $now = time();
        while (true) {
            if (time() - $now > 300) {
                $this->printMessage('运行满5分钟，关闭当前脚本');
                exit;
            }
            $data = RedisQueue::pop([RedisQueue::QUEUE_SELECT_USER_MESSAGE_KEYWORD]);
            if(!$data)
            {
                $this->printMessage('无需处理用户，继续等待');
                sleep(1);
                continue;
            }
            $userData     = json_decode($data, true);
            $userId       = $userData['user_id'];
            $max_amount   = $userData['max_money'];
            $last_loan_time = $userData['last_money_sms_time'];

            /** @var UserActiveTime $user_active */
            $user_active = UserActiveTime::find()->where(['user_id' => $userId])->one();
            if($user_active){
                $user_active->max_money = $max_amount;
                $user_active->last_money_sms_time = $last_loan_time;
            }else{
                $user_active = new UserActiveTime();
                $user_active->user_id = $userId;
                $user_active->max_money = $max_amount;
                $user_active->last_money_sms_time = $last_loan_time;
            }
            $user_active->save();
        }
    }

    /**
     * 提醒app通话记录
     */
    public function actionRemindAppCallRecord()
    {
        if (!$this->lock()) {
            return;
        }
        $now = time();

        while (true) {
            if (time() - $now > 300) {
                $this->printMessage('运行满5分钟，关闭当前脚本');
                exit;
            }
            $callDataStr = RedisQueue::pop([RedisQueue::LIST_REMIND_APP_CALL_RECORDS]);
            if(!$callDataStr)
            {
                $this->printMessage('无需处理提醒通话记录，继续等待');
                sleep(2);
                continue;
            }

            $callData    = json_decode($callDataStr, true);
            $this->printMessage('uid:'.$callData['user_id'].';phone:'.$callData['call_number']);
            $userId      = $callData['user_id'];
            $callNumber       = $callData['call_number'];
            $callDuration      = $callData['call_duration'];
            $callName      = $callData['call_name'];
            $date = $callData['date'];

            $isValid = $callDuration > 0 ? 1 : 0;
            $isOneSelfNumber = RemindOrder::find()
                ->from(RemindOrder::tableName().' A')
                ->leftJoin(UserLoanOrderRepayment::tableName().' B','A.repayment_id = B.id')
                ->leftJoin(LoanPerson::tableName(). ' C','B.user_id = C.id')
                ->where([
                    'A.customer_user_id' => $userId,
                    'A.dispatch_status' => RemindOrder::STATUS_FINISH_DISPATCH,
                    'C.phone' => $callNumber
                ])
                ->exists();

            $type = null;
            if($isOneSelfNumber){
                $type = ReminderCallData::TYPE_ONE_SELF;
            }else{
                $isContactNumber = RemindOrder::find()
                    ->from(RemindOrder::tableName().' A')
                    ->leftJoin(UserLoanOrderRepayment::tableName().' B','A.repayment_id = B.id')
                    ->leftJoin(UserLoanOrderExtraRelation::getDbName().'.'.UserLoanOrderExtraRelation::tableName().' C', 'C.order_id = B.order_id')
                    ->leftJoin(UserContact::getDbName().'.'.UserContact::tableName().' D','C.user_contact_id = D.id')
                    ->where([
                        'A.customer_user_id' => $userId,
                        'A.dispatch_status' => RemindOrder::STATUS_FINISH_DISPATCH,
                    ])
                    ->andWhere(['OR',['D.phone' => $callNumber],['D.other_phone' => $callNumber]])
                    ->exists();
                if($isContactNumber){
                    $type = ReminderCallData::TYPE_CONTACT;
                }
            }


            if($type !== null){
                //添加或更新催收员当天拨打数据
                /** @var ReminderCallData $reminderCallData */
                $reminderCallData = ReminderCallData::find()->where([
                    'date' => $date,
                    'user_id' => $userId,
                    'phone' => $callNumber,
                    'type' => $type,
                    'phone_type' => ReminderCallData::NATIVE
                ])->one();
                if (!$reminderCallData) {
                    $reminderCallData = new ReminderCallData();
                    $reminderCallData->date = $date;
                    $reminderCallData->user_id = $userId;
                    $reminderCallData->phone = $callNumber;
                    $reminderCallData->type = $type;
                    $reminderCallData->is_valid = $isValid;
                }else{
                    $reminderCallData->is_valid = ($isValid || $reminderCallData->is_valid) ? ReminderCallData::VALID : ReminderCallData::INVALID;
                }
                $reminderCallData->phone_type = ReminderCallData::NATIVE;
                $reminderCallData->name = $callName;
                $reminderCallData->times += 1;
                $reminderCallData->duration += $callDuration;
                $reminderCallData->save();
            }
        }
    }

    /**
     * 需要入提醒订单脚本
     */
    public function actionOrderToRemind(){
        if(!$this->lock())
        {
            return;
        }
        $today = strtotime('today'); // 今天

        $this->printMessage('脚本开始');
        $db_read = \Yii::$app->get('db_read_1');
        $startTime = $today;
        $endTime = $startTime + 86400;

        $merchantIds = [2,6,11,13,15,16,17,18,19,20,21,22,23,24,25,26,27,29];

        $sql = UserLoanOrderRepayment::find()
            ->alias('r')
            ->leftJoin(UserLoanOrder::tableName().' as o', 'o.id=r.order_id')
            ->select(['r.id'])
            ->where([
                'r.status'         => UserLoanOrderRepayment::STATUS_NORAML,
                'r.is_push_remind' => UserLoanOrderRepayment::IS_PUSH_REMIND_NO,
                'r.merchant_id'    => $merchantIds,
//                'o.is_first'       => UserLoanOrder::FIRST_LOAN_IS
            ])
            ->andWhere(['>=', 'r.plan_repayment_time', $startTime])
            ->andWhere(['<', 'r.plan_repayment_time', $endTime])
            ->orderBy(['r.id' => SORT_ASC])
            ->limit(5000);

        $maxId = 0;
        $query = clone $sql;
        $data  = $query->andWhere(['>', 'r.id', $maxId])
            ->asArray()
            ->all($db_read);

        while ($data) {
            $this->printMessage('maxId:' . $maxId);
            foreach ($data as $value) {
                $maxId = $value['id'];
                RedisQueue::push([RedisQueue::PUSH_ORDER_REMIND_APPLY, $value['id']]);
            }

            $query = clone $sql;
            $data  = $query->andWhere(['>', 'r.id', $maxId])
                ->asArray()
                ->all($db_read);
        }

//        foreach ($merchantIds as $merchantId) {
//            $sql = UserLoanOrderRepayment::find()
//                ->alias('r')
//                ->leftJoin(UserLoanOrder::tableName().' as o', 'o.id=r.order_id')
//                ->select(['r.id'])
//                ->where([
//                    'r.status'         => UserLoanOrderRepayment::STATUS_NORAML,
//                    'r.is_push_remind' => UserLoanOrderRepayment::IS_PUSH_REMIND_NO,
//                    'r.merchant_id'    => $merchantId,
//                    'o.is_first'       => UserLoanOrder::FIRST_LOAN_NO
//                ])
//                ->andWhere(['>=', 'r.plan_repayment_time', $startTime])
//                ->andWhere(['<', 'r.plan_repayment_time', $endTime])
//                ->orderBy(['r.id' => SORT_ASC])
//                ->limit(5000);
//
//            $maxId = 0;
//            $query = clone $sql;
//            $data  = $query->andWhere(['>', 'r.id', $maxId])
//                ->asArray()
//                ->all($db_read);
//
//            while ($data) {
//                $this->printMessage('maxId:' . $maxId);
//                foreach ($data as $key => $value) {
//                    $maxId = $value['id'];
//
//                    if(in_array($merchantId, [15,16,21,11,17])){
//                        RedisQueue::push([RedisQueue::PUSH_ORDER_REMIND_APPLY, $value['id']]);
//                    }else{
//                        if($key % 2 == 1){
//                            //全老本老50%推送
//                            continue;
//                        }
//                        RedisQueue::push([RedisQueue::PUSH_ORDER_REMIND_APPLY, $value['id']]);
//                    }
//                }
//
//                $query = clone $sql;
//                $data  = $query->andWhere(['>', 'r.id', $maxId])
//                    ->asArray()
//                    ->all($db_read);
//            }
//        }

        $this->printMessage('脚本结束');
    }

    /**
     * 推送提醒订单到提醒中心
     * @param int $id
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function actionPushOrderRemind($id=1)
    {
        if(!$this->lock()){
            return;
        }

        $now = time();
        $appName = array_keys(Yii::$app->params['RemindCenter']);
        while(true)
        {
            if (time() - $now > 300) {
                $this->printMessage('运行满5分钟，关闭当前脚本');
                exit;
            }

            $repayment_id = RedisQueue::pop([RedisQueue::PUSH_ORDER_REMIND_APPLY]);
            if (empty($repayment_id)) {
                $this->printMessage('无需处理订单，退出脚本');
                exit;
            }

            /**
             * @var UserLoanOrderRepayment $repayment
             */
            $repayment = UserLoanOrderRepayment::findOne($repayment_id);
            if (empty($repayment)) {
                $this->printMessage("还款订单ID:{$repayment_id}不存在");
                continue;
            }

            if (UserLoanOrderRepayment::IS_PUSH_REMIND_YES == $repayment->is_push_remind
                || UserLoanOrderRepayment::STATUS_REPAY_COMPLETE == $repayment->status
                || UserLoanOrderRepayment::IS_OVERDUE_YES == $repayment->is_overdue
            ) {
                $_notice = "order_{$repayment->order_id} 非推送状态, skip";
                $this->printMessage($_notice);
                continue;
            }

            if(!in_array($repayment->userLoanOrder->clientInfoLog->package_name, $appName)){
                $this->printMessage('repayment_id:'.$repayment_id.'没有对应的token');
                continue;
            }

            $this->printMessage("还款订单ID:{$repayment_id}, 开始运行");
            try{
                $pushService = new PushOrderRemindService();
                $result = $pushService->pushOrder($repayment);
                if($result['code'] != 0){
                    throw new \Exception($result['message']);
                }

                $repayment->is_push_remind = UserLoanOrderRepayment::IS_PUSH_REMIND_YES;
                $repayment->save();
            } catch (\Exception $exception) {
                Yii::error([
                    'repayment_id' => $repayment_id,
                    'code'         => $exception->getCode(),
                    'message'      => $exception->getMessage(),
                    'line'         => $exception->getLine(),
                    'file'         => $exception->getFile(),
                    'trace'        => $exception->getTraceAsString()
                ], 'PushOrderRemind');
                $service = new WeWorkService();
                $message = sprintf('[%s][%s]异常[repayment_id:%s] : %s in %s:%s',
                    \yii::$app->id, Yii::$app->requestedRoute, $repayment_id, $exception->getMessage(), $exception->getFile(), $exception->getLine());
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
    public function actionPushOrderRemindRepayment($id=1)
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

            $data = RedisQueue::pop([RedisQueue::PUSH_ORDER_REMIND_REPAYMENT]);
            if(empty($data)){
                $this->printMessage('没有可处理的数据，退出脚本');
                exit;
            }

            $parsms = json_decode($data, true);
            $this->printMessage("订单号：{$parsms['order_id']}, 开始运行");
            try{
                $service = new PushOrderRemindService();
                $result = $service->pushOrderRemindRepayment($parsms);
                if($result['code'] != 0){
                    throw new \Exception($result['message']);
                }
            }catch (\Exception $exception)
            {
                if(!in_array($exception->getMessage(), ['The order is finish', 'The order is overdue', 'The order does not exist'])){
                    RedisDelayQueue::pushDelayQueue(RedisQueue::PUSH_ORDER_REMIND_REPAYMENT, $data, 180);
                }
                Yii::error([
                    'order_id' => $parsms['order_id'],
                    'params'   => $data,
                    'code'     => $exception->getCode(),
                    'message'  => $exception->getMessage(),
                    'line'     => $exception->getLine(),
                    'file'     => $exception->getFile(),
                    'trace'    => $exception->getTraceAsString()
                ], 'PushOrderRemindRepayment');
                $service = new WeWorkService();
                $message = sprintf('[%s][%s]异常[order_id:%s] : %s in %s:%s',
                    \yii::$app->id, Yii::$app->requestedRoute, $parsms['order_id'], $exception->getMessage(), $exception->getFile(), $exception->getLine());
                $service->send($message);
            }
        }
    }
}


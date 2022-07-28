<?php

namespace console\controllers;

use common\helpers\RedisDelayQueue;
use common\helpers\RedisQueue;
use common\helpers\Util;
use common\models\order\UserLoanOrder;
use common\models\order\UserLoanOrderRepayment;
use common\models\user\LoanPerson;
use common\services\message\WeWorkService;
use common\services\order\PushOrderAssistService;
use yii;
use yii\console\ExitCode;

class AssistController extends BaseController {

    /**
     * 推送逾期订单到催收中心
     * @param int $id
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function actionPushOrderAssist($id=1)
    {
        if(!$this->lock()){
            return;
        }

        $now = time();
        $appName = array_keys(Yii::$app->params['AssistCenter']);
        while(true)
        {
            if (time() - $now > 300) {
                $this->printMessage('运行满5分钟，关闭当前脚本');
                exit;
            }

            $repayment_id = RedisQueue::pop([RedisQueue::PUSH_ORDER_ASSIST_APPLY]);
            if (empty($repayment_id)) {
                $this->printMessage('无需处理订单，继续等待');
                sleep(1);
                continue;
            }

            /**
             * @var UserLoanOrderRepayment $repayment
             */
            $repayment = UserLoanOrderRepayment::findOne($repayment_id);
            if (empty($repayment)) {
                $this->printMessage("还款订单ID:{$repayment_id}不存在");
                continue;
            }

            if (UserLoanOrderRepayment::IS_PUSH_ASSIST_YES == $repayment->is_push_assist
                || UserLoanOrderRepayment::STATUS_REPAY_COMPLETE == $repayment->status
                || UserLoanOrderRepayment::IS_DELAY_NO == $repayment->is_overdue
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
                $pushService = new PushOrderAssistService();
                $result = $pushService->pushOrder($repayment);
                if($result['code'] != 0){
                    throw new \Exception($result['message']);
                }

                $params = [
                    'user_id' => $repayment->user_id,
                    'order_id' => $repayment->order_id,
                    'app_name' => $repayment->userLoanOrder->clientInfoLog->package_name
                ];
                RedisQueue::push([RedisQueue::PUSH_USER_CONTACTS, json_encode($params)]);

                $repayment->is_push_assist = UserLoanOrderRepayment::IS_PUSH_ASSIST_YES;
                $repayment->save();
            } catch (\Exception $exception) {
                Yii::error([
                    'repayment_id' => $repayment_id,
                    'code'         => $exception->getCode(),
                    'message'      => $exception->getMessage(),
                    'line'         => $exception->getLine(),
                    'file'         => $exception->getFile(),
                    'trace'        => $exception->getTraceAsString()
                ], 'PushOrderAssist');
                $service = new WeWorkService();
                $message = sprintf('[%s][%s]异常[repayment_id:%s] : %s in %s:%s',
                    \yii::$app->id, Yii::$app->requestedRoute, $repayment_id, $exception->getMessage(), $exception->getFile(), $exception->getLine());
                $service->send($message);
            }
        }
    }

    public function actionGetPanOrderAssist(){
        if(!$this->lock()){
            return;
        }

        Util::cliLimitChange(2048);

        $data = UserLoanOrderRepayment::find()
            ->alias('r')
            ->leftJoin(LoanPerson::tableName().' as p', 'p.id=r.user_id')
            ->select(['r.id'])
            ->where(['r.status' => UserLoanOrderRepayment::STATUS_NORAML, 'r.is_overdue' => UserLoanOrderRepayment::IS_OVERDUE_YES])
            ->groupBy(['p.pan_code'])
            ->asArray()
            ->all();

        foreach ($data as $v){
            RedisQueue::push([RedisQueue::PUSH_PAN_ORDER_ASSIST_APPLY, $v['id']]);
        }
    }

    /**
     * 推送逾期订单到催收中心
     * @param int $id
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function actionPushPanOrderAssist($id=1)
    {
        if(!$this->lock()){
            return;
        }

        $app_name = 'saas';

        while(true)
        {
            $repayment_id = RedisQueue::pop([RedisQueue::PUSH_PAN_ORDER_ASSIST_APPLY]);
            if (empty($repayment_id)) {
                $this->printMessage('无需处理订单，关闭脚本');
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

            if (UserLoanOrderRepayment::STATUS_REPAY_COMPLETE == $repayment->status
                || UserLoanOrderRepayment::IS_DELAY_NO == $repayment->is_overdue
            ) {
                $_notice = "order_{$repayment->order_id} 非推送状态, skip";
                $this->printMessage($_notice);
                continue;
            }

            $this->printMessage("还款订单ID:{$repayment_id}, 开始运行");
            try{
                $pushService = new PushOrderAssistService();
                $result = $pushService->pushOrder($repayment, $app_name);
                if($result['code'] != 0){
                    throw new \Exception($result['message']);
                }

                $params = [
                    'user_id' => $repayment->user_id,
                    'order_id' => $repayment->order_id,
                    'app_name' => $app_name
                ];
                RedisQueue::push([RedisQueue::PUSH_USER_CONTACTS, json_encode($params)]);

            } catch (\Exception $exception) {
                Yii::error([
                    'repayment_id' => $repayment_id,
                    'code'         => $exception->getCode(),
                    'message'      => $exception->getMessage(),
                    'line'         => $exception->getLine(),
                    'file'         => $exception->getFile(),
                    'trace'        => $exception->getTraceAsString()
                ], 'PushPanOrderAssist');
                $service = new WeWorkService();
                $message = sprintf('[%s][%s]异常[repayment_id:%s] : %s in %s:%s',
                    \yii::$app->id, Yii::$app->requestedRoute, $repayment_id, $exception->getMessage(), $exception->getFile(), $exception->getLine());
                $service->send($message);
            }
        }
    }

    /**
     * 推送用户通讯录
     * @return int
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function actionPushUserContacts($id=1)
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

            $data = RedisQueue::pop([RedisQueue::PUSH_USER_CONTACTS]);
            if(empty($data)){
                $this->printMessage('没有可处理的数据，继续等待');
                sleep(1);
                continue;
            }

            $params = json_decode($data, true);
            $this->printMessage("user_id：{$params['user_id']}, 开始运行");
            try{
                $service = new PushOrderAssistService();
                $result = $service->pushUserContacts($params);
                if($result['code'] != 0){
                    throw new \Exception($result['message']);
                }
            }catch (\Exception $exception)
            {
                RedisDelayQueue::pushDelayQueue(RedisQueue::PUSH_USER_CONTACTS, $data, 180);
                Yii::error([
                    'user_id' => $params['user_id'],
                    'code'    => $exception->getCode(),
                    'message' => $exception->getMessage(),
                    'line'    => $exception->getLine(),
                    'file'    => $exception->getFile(),
                    'trace'   => $exception->getTraceAsString()
                ], 'PushUserContacts');
                $service = new WeWorkService();
                $message = sprintf('[%s][%s]异常[user_id:%s] : %s in %s:%s',
                    \yii::$app->id, Yii::$app->requestedRoute, $params['user_id'], $exception->getMessage(), $exception->getFile(), $exception->getLine());
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
    public function actionPushOrderAssistOverdue($id=1)
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

            $repayment_id = RedisQueue::pop([RedisQueue::PUSH_ORDER_ASSIST_OVERDUE]);
            if(empty($repayment_id)){
                $this->printMessage('没有可处理的数据，继续等待');
                sleep(1);
                continue;
            }

            /**
             * @var UserLoanOrderRepayment $repayment
             */
            $repayment = UserLoanOrderRepayment::findOne($repayment_id);
            if (empty($repayment)) {
                $this->printMessage("还款订单ID:{$repayment_id}不存在");
                continue;
            }

            if (UserLoanOrderRepayment::STATUS_REPAY_COMPLETE == $repayment->status
                || UserLoanOrderRepayment::IS_DELAY_NO == $repayment->is_overdue
            ) {
                $_notice = "order_{$repayment->order_id} 非推送状态, skip";
                $this->printMessage($_notice);
                continue;
            }

            $this->printMessage("还款订单ID:{$repayment_id}, 开始运行");
            try{
                $service = new PushOrderAssistService();
                $result = $service->pushOrderOverdue($repayment);
                if($result['code'] != 0){
                    throw new \Exception($result['message']);
                }
            }catch (\Exception $exception)
            {
                Yii::error([
                    'repayment_id' => $repayment_id,
                    'code'         => $exception->getCode(),
                    'message'      => $exception->getMessage(),
                    'line'         => $exception->getLine(),
                    'file'         => $exception->getFile(),
                    'trace'        => $exception->getTraceAsString()
                ], 'PushOrderAssistOverdue');
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
    public function actionPushOrderAssistRepayment($id=1)
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

            $data = RedisQueue::pop([RedisQueue::PUSH_ORDER_ASSIST_REPAYMENT]);
            if(empty($data)){
                $this->printMessage('没有可处理的数据，继续等待');
                sleep(1);
                continue;
            }

            $parsms = json_decode($data, true);
            $this->printMessage("订单号：{$parsms['order_id']}, 开始运行");
            try{
                $service = new PushOrderAssistService();
                $result = $service->pushOrderAssistRepayment($parsms);
                if($result['code'] != 0){
                    throw new \Exception($result['message']);
                }
            }catch (\Exception $exception)
            {
                if(!in_array($exception->getMessage(), [
                    'The order is finish',
                    'request_id error'
                ])){
                    RedisDelayQueue::pushDelayQueue(RedisQueue::PUSH_ORDER_ASSIST_REPAYMENT, $data, 180);
                }
                Yii::error([
                    'order_id' => $parsms['order_id'],
                    'params'   => $data,
                    'code'     => $exception->getCode(),
                    'message'  => $exception->getMessage(),
                    'line'     => $exception->getLine(),
                    'file'     => $exception->getFile(),
                    'trace'    => $exception->getTraceAsString()
                ], 'PushOrderAssistRepayment');
                $service = new WeWorkService();
                $message = sprintf('[%s][%s]异常[order_id:%s] : %s in %s:%s',
                    \yii::$app->id, Yii::$app->requestedRoute, $parsms['order_id'], $exception->getMessage(), $exception->getFile(), $exception->getLine());
                $service->send($message);
            }
        }
    }

    public function actionGetOrderLink($maxId=0){
        if(!$this->lock()){
            return;
        }
        $this->printMessage('start');

        $sql = UserLoanOrderRepayment::find()
            ->where([
                'is_push_assist' => UserLoanOrderRepayment::IS_PUSH_ASSIST_YES,
                'status' => UserLoanOrderRepayment::STATUS_NORAML
            ])
            ->orderBy(['id' => SORT_ASC])
            ->limit(1000);

        $query = clone $sql;
        $data = $query->andWhere(['>', 'id', $maxId])->all();
        while ($data) {
            $this->printMessage('maxId:'.$maxId);
            /** @var UserLoanOrderRepayment $repayment */
            foreach ($data as $repayment){
                $maxId = $repayment->id;
                RedisQueue::push([RedisQueue::PUSH_ORDER_ASSIST_LINK, $repayment->id]);
            }

            $query = clone $sql;
            $data = $query->andWhere(['>', 'id', $maxId])->all();
        }

        $this->printMessage('end');
    }

    public function actionPushOrderAssistLink($id=1){
        if(!$this->lock()){
            return;
        }
        $this->printMessage('start');
        $appName = array_keys(Yii::$app->params['AssistCenter']);
        while(true)
        {
            $repayment_id = RedisQueue::pop([RedisQueue::PUSH_ORDER_ASSIST_LINK]);
            if (empty($repayment_id)) {
                $this->printMessage('无需处理订单，继续等待');
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

            if (UserLoanOrderRepayment::STATUS_REPAY_COMPLETE == $repayment->status
            ) {
                $_notice = "order_{$repayment->order_id} 非推送状态, skip";
                $this->printMessage($_notice);
                continue;
            }

            if($repayment->userLoanOrder->is_export != UserLoanOrder::IS_EXPORT_YES){
                continue;
            }

            if(!in_array($repayment->userLoanOrder->clientInfoLog->package_name, $appName)){
                $this->printMessage('repayment_id:'.$repayment_id.'没有对应的token');
                continue;
            }

            $this->printMessage("还款订单ID:{$repayment_id}, 开始运行");
            try{
                $pushService = new PushOrderAssistService();
                $result = $pushService->pushOrderLink($repayment);
                if($result['code'] != 0){
                    throw new \Exception($result['message']);
                }
            } catch (\Exception $exception) {
                Yii::error([
                    'repayment_id' => $repayment->id,
                    'code'         => $exception->getCode(),
                    'message'      => $exception->getMessage(),
                    'line'         => $exception->getLine(),
                    'file'         => $exception->getFile(),
                    'trace'        => $exception->getTraceAsString()
                ], 'PushOrderAssistLink');
                $service = new WeWorkService();
                $message = sprintf('[%s][%s]异常[repayment_id:%s] : %s in %s:%s',
                    \yii::$app->id, Yii::$app->requestedRoute, $repayment->id, $exception->getMessage(), $exception->getFile(), $exception->getLine());
                $service->send($message);
            }
        }

        $this->printMessage('end');
    }

}


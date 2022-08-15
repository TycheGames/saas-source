<?php

namespace console\controllers;

use common\helpers\CommonHelper;
use common\helpers\RedisDelayQueue;
use common\helpers\RedisQueue;
use common\helpers\Util;
use common\models\InfoDevice;
use common\models\InfoOrder;
use common\models\InfoRepayment;
use common\models\InfoUser;
use common\models\ModelScore;
use common\models\risk\RiskResultSnapshot;
use common\models\RiskOrder;
use common\services\message\WeWorkService;
use common\services\order\OrderExtraService;
use common\services\order\PushOrderRiskService;
use common\services\risk\AssistDataService;
use common\services\risk\RiskDataDemoService;
use common\services\risk\RiskTreeService;
use yii\db\Exception;
use yii;
use yii\console\ExitCode;

class OrderController extends BaseController {

    /**
     * 风控审核脚本
     * @param int $id
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function actionAutoCheck($is_first = 1, $id = 1)
    {
        if(!$this->lock()){
            return;
        }
        Util::cliLimitChange(512);
        $now = time();
        $time = rand(3,5);
        while(true)
        {
            if (time() - $now > $time * 60) {
                $this->printMessage('运行满'.$time.'分钟，关闭当前脚本');
                exit;
            }

            if($is_first == 1){
                $order_id = RedisQueue::pop([RedisQueue::CREDIT_AUTO_CHECK]);
            }else{
                $order_id = RedisQueue::pop([RedisQueue::CREDIT_AUTO_CHECK_OLD]);
            }
            if (empty($order_id)) {
                $this->printMessage('无需处理订单，继续等待');
                sleep(1);
                continue;
            }

            $this->printMessage("订单{$order_id}开始数据采集");

            /**
             * @var RiskOrder $riskOrder
             */
            $riskOrder = RiskOrder::findOne($order_id);
            if (empty($riskOrder)) {
                $this->printMessage("订单ID:{$order_id}不存在");
                continue;
            }

            if (RiskOrder::STATUS_WAIT_CHECK != $riskOrder->status) {
                $_notice = sprintf("order_{$order_id} 非采集状态[%s], skip.",
                    RiskOrder::STATUS_WAIT_CHECK != $riskOrder->status);
                $this->printMessage($_notice);
                continue;
            }

            try{
                $tree = 'T102';
                //订单维度
                $orderExtraService = new OrderExtraService($riskOrder);
                $orderData = [
                    'order'           => $riskOrder,
                    'infoUser'        => $orderExtraService->getInfoUser(),
                    'infoDevice'      => $orderExtraService->getInfoDevice(),
                    'infoOrder'       => $orderExtraService->getInfoOrder(),
                    'infoPictureMeta' => $orderExtraService->getInfoPictureMetadata(),
                ];
                $data = new RiskDataDemoService($orderData);
                $riskTree = new RiskTreeService($data);
                /** @var array $result */
                $result = $riskTree->exploreNodeValue($tree);
                //添加快照
                $res_risk = $riskTree->insertRiskResultSnapshotToDb($riskOrder->order_id, $riskOrder->user_id, $riskOrder->app_name, $tree, $result);
                switch ($result['result'])
                {
                    case 'reject':
                        $riskOrder->changeStatus(RiskOrder::STATUS_CHECK_REJECT);
                        break;
                    case 'manual':
                        $riskOrder->changeStatus(RiskOrder::STATUS_CHECK_MANUAL);
                        break;
                    case 'approve':
                        $riskOrder->changeStatus(RiskOrder::STATUS_CHECK_SUCCESS);
                        break;
                    default:
                }

                $params = [];
                if(($result['result'] == 'manual' || $result['result'] == 'approve')){
                    $riskTree = new RiskTreeService($data);
                    $tree = 'C101';
                    /** @var array $result */
                    $result = $riskTree->exploreNodeValue($tree);
                    //添加快照
                    $res_money = $riskTree->insertRiskResultSnapshotToDb($riskOrder->order_id, $riskOrder->user_id, $riskOrder->app_name, $tree, $result);
                    $params['amount'] = $res_money;
                }

                $params['risk'] = $res_risk;

                $pushService = new PushOrderRiskService($riskOrder->infoOrder->product_source);
                $res = $pushService->pushOrderRisk($riskOrder->order_id, $params);
                if(isset($res['code']) && $res['code'] == 0){
                    $riskOrder->is_push = RiskOrder::IS_PUSH_YES;
                    $riskOrder->save();
                }else{
                    throw new \Exception('风控订单回调失败，需手动处理');
                }

            }catch (\Exception $exception) {
                Yii::error([
                    'order_id' => $order_id,
                    'code'     => $exception->getCode(),
                    'message'  => $exception->getMessage(),
                    'file'     => $exception->getFile(),
                    'line'     => $exception->getLine(),
                    'trace'    => $exception->getTraceAsString()
                ], 'RiskAutoCheck');
                if($exception->getCode() == 1001){
                    RedisDelayQueue::pushDelayQueue(RedisQueue::CREDIT_AUTO_CHECK, $order_id, 120);
                }else{
                    $service = new WeWorkService();
                    $message = sprintf('[%s][%s]异常[order_id:%s]: %s in %s:%s',
                        \yii::$app->id, Yii::$app->requestedRoute, $order_id, $exception->getMessage(), $exception->getFile(), $exception->getLine());
                    $service->send($message);
                }
            }
        }
    }

    /**
     * 用户额度计算
     * @param int $id
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function actionUserCredit($id = 1)
    {
        if(!$this->lock()){
            return;
        }
        $now = time();
        $time = rand(3,5);
        while(true)
        {
            if (time() - $now > $time * 60) {
                $this->printMessage('运行满'.$time.'分钟，关闭当前脚本');
                exit;
            }

            $order_id = RedisQueue::pop([RedisQueue::CREDIT_USER_CREDIT_CALC]);
            if (empty($order_id)) {
                $this->printMessage('无需处理订单，继续等待');
                sleep(1);
                continue;
            }

            $this->printMessage("订单{$order_id}开始数据采集");

            /**
             * @var RiskOrder $riskOrder
             */
            $riskOrder = RiskOrder::findOne($order_id);
            if (empty($riskOrder)) {
                $this->printMessage("订单ID:{$order_id}不存在");
                continue;
            }

            if (RiskOrder::STATUS_WAIT_CHECK != $riskOrder->status) {
                $_notice = sprintf("order_{$order_id} 非采集状态[%s], skip.",
                    RiskOrder::STATUS_WAIT_CHECK != $riskOrder->status);
                $this->printMessage($_notice);
                continue;
            }

            try{
                $tree = 'RepayC101';
                //订单维度
                $orderExtraService = new OrderExtraService($riskOrder);
                $orderData = [
                    'order'           => $riskOrder,
                    'infoUser'        => $orderExtraService->getInfoUser(),
                    'infoDevice'      => $orderExtraService->getInfoDevice(),
                    'infoOrder'       => $orderExtraService->getInfoOrder(),
                    'infoPictureMeta' => $orderExtraService->getInfoPictureMetadata(),
                ];
                $data = new RiskDataDemoService($orderData);
                $riskTree = new RiskTreeService($data);
                /** @var array $result */
                $result = $riskTree->exploreNodeValue($tree);
                //添加快照
                $res_credit = $riskTree->insertRiskResultSnapshotToDb($riskOrder->order_id, $riskOrder->user_id, $riskOrder->app_name, $tree, $result);
                $riskOrder->changeStatus(RiskOrder::STATUS_USER_CREDIT);

                $params['credit'] = $res_credit;

                $pushService = new PushOrderRiskService($riskOrder->infoOrder->product_source);
                $res = $pushService->pushOrderRisk($riskOrder->order_id, $params);
                if(isset($res['code']) && $res['code'] == 0){
                    $riskOrder->is_push = RiskOrder::IS_PUSH_YES;
                    $riskOrder->save();
                }else{
                    throw new \Exception('用户额度回调失败，需手动处理');
                }
            }catch (\Exception $exception) {
                Yii::error([
                    'order_id' => $order_id,
                    'code'     => $exception->getCode(),
                    'message'  => $exception->getMessage(),
                    'file'     => $exception->getFile(),
                    'line'     => $exception->getLine(),
                    'trace'    => $exception->getTraceAsString()
                ], 'UserCredit');
                $service = new WeWorkService();
                $message = sprintf('[%s][%s]异常[order_id:%s]: %s in %s:%s',
                    \yii::$app->id, Yii::$app->requestedRoute, $order_id, $exception->getMessage(), $exception->getFile(), $exception->getLine());
                $service->send($message);
            }
        }
    }

    /**
     * 获取需要跑模型分的用户
     */
    public function actionGetModelScoreList(){
        if(!$this->lock()){
            return;
        }

        Util::cliLimitChange();
        $this->printMessage('start');

        $data = InfoUser::find()
            ->alias('u')
            ->leftJoin(InfoOrder::tableName().' as o', 'o.app_name = u.app_name and o.user_id = u.user_id and o.order_id = u.order_id')
            ->select(['u.pan_code'])
            ->where(['o.is_first' => InfoOrder::ENUM_IS_FIRST_N])
            ->groupBy(['u.pan_code'])
            ->asArray()
            ->all();

        $count = 0;
        foreach ($data as $v){
            $pan_code = $v['pan_code'];
            $is_overdue = InfoRepayment::find()
                ->alias('r')
                ->leftJoin(InfoUser::tableName().' as u', 'u.user_id=r.user_id and u.order_id=r.order_id and u.app_name=r.app_name')
                ->where(['u.pan_code' => $pan_code])
                ->andWhere([
                    'OR',
                    ['r.status' => InfoRepayment::STATUS_PENDING, 'r.is_overdue' => InfoRepayment::OVERDUE_YES],
                    ['>', 'r.overdue_day', 5]
                ])
                ->exists();
            if($is_overdue){
                continue;
            }
            $count++;
            RedisQueue::push([RedisQueue::GET_MODEL_SCORE_LIST, $pan_code]);
        }

        $this->printMessage('符合条件的数量：'.$count);
        $this->printMessage('end');
    }

    public function actionGetModelScore($id=1){
        if(!$this->lock()){
            return;
        }

        $now = time();
        $time = rand(8,10);
        while (true){
            if (time() - $now > $time * 60) {
                $this->printMessage('运行满'.$time.'分钟，关闭当前脚本');
                exit;
            }

            $pan_code = RedisQueue::pop([RedisQueue::GET_MODEL_SCORE_LIST]);
            if (empty($pan_code)) {
                $this->printMessage('无需处理pan_code，退出脚本');
                exit;
            }

            $this->printMessage("pan_code:{$pan_code}开始数据采集");

            try {
                $params = [
                    'pan_code' => $pan_code,
                ];
                $service = new AssistDataService();
                $result = $service->getModelScoreRiskData($params);

                if(isset($result['code']) && $result['code'] == 0){
                    $assistData = $result['data'];
                }else{
                    throw new Exception('模型分催收数据获取失败，'.$result['message']);
                }
            } catch (\Exception $exception) {
                RedisDelayQueue::pushDelayQueue(RedisQueue::GET_MODEL_SCORE_LIST, $pan_code, 120);
                continue;
            }

            $score = 0;
            $v1425 = $assistData['avgTrueTotalMoney'];
            switch (true){
                case $v1425 < 0:
                    $score += 69;
                    break;
                case $v1425 < 280000:
                    $score += 16;
                    break;
                case $v1425 < 500000:
                    $score += 38;
                    break;
                case $v1425 < 600000:
                    $score += 57;
                    break;
                case $v1425 >= 600000:
                    $score += 79;
                    break;
            }

            $v1415 = $assistData['sumOverdueDay'];
            switch (true){
                case $v1415 < 2:
                    $score += 72;
                    break;
                case $v1415 < 5:
                    $score += 52;
                    break;
                case $v1415 >= 5:
                    $score += 19;
                    break;
            }

            $data = InfoOrder::find()
                ->alias('o')
                ->leftJoin(InfoUser::tableName().' as u', 'u.user_id=o.user_id and u.order_id=o.order_id and u.app_name=o.app_name')
                ->select(['o.order_time'])
                ->where(['u.pan_code' => $pan_code])
                ->andWhere(['>', 'o.loan_time', 0])
                ->orderBy(['o.order_time' => SORT_ASC])
                ->asArray()
                ->one();
            $v1198 = (strtotime('today') - strtotime(date('Y-m-d', $data['order_time'])))/86400;
            switch (true){
                case $v1198 < 10:
                    $score += 62;
                    break;
                case $v1198 < 70:
                    $score += 38;
                    break;
                case $v1198 < 110:
                    $score += 56;
                    break;
                case $v1198 >= 110:
                    $score += 77;
                    break;
            }

            $data = InfoRepayment::find()
                ->alias('r')
                ->leftJoin(InfoUser::tableName().' as u', 'r.order_id=u.order_id and r.user_id=u.user_id and r.app_name=u.app_name')
                ->where(['u.pan_code' => $pan_code,
                         'r.status' => InfoRepayment::STATUS_CLOSED])
                ->orderBy(['r.loan_time' => SORT_DESC])
                ->one();

            if(empty($data)){
                $v726 = 0;
            }else{
                $v726 = (strtotime(date('Y-m-d', $data['closing_time'])) - strtotime(date('Y-m-d', $data['plan_repayment_time'])))/86400;
            }
            switch (true){
                case $v726 < -2:
                    $score += 57;
                    break;
                case $v726 < 0:
                    $score += 75;
                    break;
                case $v726 < 1:
                    $score += 52;
                    break;
                case $v726 >= 1:
                    $score += 1;
                    break;
            }

            $user = InfoUser::find()->where(['pan_code' => $pan_code])->orderBy(['id' => SORT_DESC])->one();
            $v206 =  intval(CommonHelper::CentsToUnit($user->monthly_salary));
            switch (true){
                case $v206 < 22000:
                    $score += 44;
                    break;
                case $v206 < 30000:
                    $score += 35;
                    break;
                case $v206 < 56000:
                    $score += 64;
                    break;
                case $v206 >= 56000:
                    $score += 89;
                    break;
            }

            $v103 = $user->education_level;
            switch (true){
                case $v103 < 4:
                    $score += 32;
                    break;
                case $v103 < 6:
                    $score += 58;
                    break;
                case $v103 >= 6:
                    $score += 55;
                    break;
            }

            $snapshot = RiskResultSnapshot::find()
                ->select(['base_node'])
                ->where([
                    'app_name' => $user->app_name,
                    'order_id' => $user->order_id,
                    'user_id' => $user->user_id,
                    'tree_code' => 'T102'])
                ->asArray()
                ->one();
            if(empty($snapshot)){
                $score += 47 + 45;
            }else {
                $base_node = json_decode($snapshot['base_node'], true);
                if(!isset($base_node['556'])){
                    $score += 47;
                }else {
                    $v556 = $base_node['556'];
                    switch (true) {
                        case $v556 < 5:
                            $score += 81;
                            break;
                        case $v556 < 13:
                            $score += 72;
                            break;
                        case $v556 < 34:
                            $score += 38;
                            break;
                        case $v556 >= 34:
                            $score += -2;
                            break;
                    }
                }

                if(!isset($base_node['142'])){
                    $score += 45;
                }else {
                    $v142 = $base_node['142'];
                    switch (true){
                        case $v142 < 100:
                            $score += 13;
                            break;
                        case $v142 < 350:
                            $score += 45;
                            break;
                        case $v142 < 1150:
                            $score += 55;
                            break;
                        case $v142 >= 1150:
                            $score += 66;
                            break;
                    }
                }
            }

            $device = InfoDevice::find()->where(['pan_code' => $pan_code])->orderBy(['id' => SORT_DESC])->one();
            if(empty($device->szlm_query_id)){
                $v703 = -1;
            }else{
                $begin_time = $now - 7 * 86400;
                $v703 = InfoOrder::find()
                    ->alias('o')
                    ->leftJoin(InfoDevice::tableName().' as d', 'o.user_id=d.user_id and o.order_id=d.order_id and o.app_name=d.app_name')
                    ->where(['d.szlm_query_id' => $device->szlm_query_id])
                    ->andWhere(['>=', 'o.order_time', $begin_time])
                    ->andWhere(['<=', 'o.order_time', $now])
                    ->count();
            }
            switch (true){
                case $v703 < 2:
                    $score += 60;
                    break;
                case $v703 < 5:
                    $score += 70;
                    break;
                case $v703 < 13:
                    $score += 45;
                    break;
                case $v703 >= 13:
                    $score += 31;
                    break;
            }

            $v588 = InfoRepayment::find()
                ->alias('r')
                ->leftJoin(InfoUser::tableName().' as u', 'r.user_id=u.user_id and r.order_id=u.order_id and r.app_name=u.app_name')
                ->where(['u.pan_code' => $pan_code,
                         'r.status' => InfoRepayment::STATUS_PENDING])
                ->count();
            switch (true){
                case $v588 < 2:
                    $score += 71;
                    break;
                case $v588 < 3:
                    $score += 59;
                    break;
                case $v588 < 6:
                    $score += 48;
                    break;
                case $v588 >= 6:
                    $score += 41;
                    break;
            }

            $data = InfoRepayment::find()
                ->alias('r')
                ->leftJoin(InfoUser::tableName().' as u', 'u.user_id=r.user_id and u.order_id=r.order_id and u.app_name=r.app_name')
                ->select(['r.principal', 'r.cost_fee'])
                ->where([
                    'u.pan_code' => $pan_code,
                ])
                ->orderBy(['r.id' => SORT_DESC])
                ->one();
            if(empty($data)){
                $v485 = 1700;
            }else{
                $v485 = intval(CommonHelper::CentsToUnit($data['principal'] - $data['cost_fee']));
            }
            switch (true){
                case $v485 < 1900:
                    $score += 55;
                    break;
                case $v485 < 2100:
                    $score += 52;
                    break;
                case $v485 < 4200:
                    $score += 48;
                    break;
                case $v485 >= 4200:
                    $score += 67;
                    break;
            }

            $lastTime = strtotime('last month');
            $v580 = InfoOrder::find()
                ->alias('o')
                ->leftJoin(InfoUser::tableName().' as u', 'o.order_id=u.order_id and o.user_id=u.user_id and o.app_name=u.app_name')
                ->where(['o.status' => [InfoOrder::STATUS_RISK_AUTO_REJECT, InfoOrder::STATUS_RISK_MANUAL_REJECT],
                         'u.pan_code' => $pan_code])
                ->andWhere(['>=', 'o.order_time', $lastTime])
                ->andWhere(['<=', 'o.order_time', $now])
                ->count();
            switch (true){
                case $v580 < 1:
                    $score += 57;
                    break;
                case $v580 < 4:
                    $score += 41;
                    break;
                case $v580 >= 4:
                    $score += 33;
                    break;
            }

            $model = ModelScore::find()->where(['pan_code' => $pan_code])->one();
            if(empty($model)){
                $model = new ModelScore();
                $model->pan_code = $pan_code;
            }
            $model->score = $score;

            if(!$model->save()){
                RedisDelayQueue::pushDelayQueue(RedisQueue::GET_MODEL_SCORE_LIST, $pan_code, 120);
            }
        }
    }


}


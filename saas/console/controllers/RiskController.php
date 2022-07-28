<?php


namespace console\controllers;


use callcenter\models\loan_collection\LoanCollectionOrder;
use callcenter\models\loan_collection\LoanCollectionRecord;
use callcenter\models\loan_collection\LoanCollectionSuggestionChangeLog;
use common\helpers\RedisQueue;
use common\helpers\Util;
use common\models\ClientInfoLog;
use common\models\InfoCollectionSuggestion;
use common\models\InfoDevice;
use common\models\InfoOrder;
use common\models\InfoRepayment;
use common\models\InfoUser;
use common\models\LoanCollectionRecordOther;
use common\models\LoginLog;
use common\models\order\UserLoanOrder;
use common\models\order\UserLoanOrderRepayment;
use common\models\risk\RiskBlackListAadhaar;
use common\models\risk\RiskBlackListDeviceid;
use common\models\risk\RiskBlackListPan;
use common\models\risk\RiskBlackListPhone;
use common\models\risk\RiskBlackListSzlm;
use common\models\RiskBlackListAadhaarOther;
use common\models\RiskBlackListDeviceidOther;
use common\models\RiskBlackListPanOther;
use common\models\RiskBlackListPhoneOther;
use common\models\RiskBlackListSzlmOther;
use common\models\user\LoanPerson;
use common\models\user\UserCreditReportFrLiveness;
use common\models\user\UserCreditReportFrVerify;
use common\services\message\WeWorkService;
use common\services\order\OrderExtraService;
use yii\console\Controller;

class RiskController extends BaseController
{
    public function actionPushLoanCollectionRecord($endId = 1318794){
        if (!$this->lock()) {
            return;
        }
        $id = RedisQueue::newGet('loan_collection_log');
        if($id){
            $maxId = $id;
        }else{
            $maxId = 0;
        }

        $this->printMessage("脚本开始");

        $now = time();
        try{
            $query = LoanCollectionRecord::find()
                ->where(['<=', 'id', $endId])
                ->orderBy(['id' => SORT_ASC])
                ->limit(1000);
            $clone_query = clone $query;
            $data = $clone_query->andWhere(['>', 'id', $maxId])->all();

            while ($data){
                foreach ($data as $loanCollectionRecord){
                    $maxId = $loanCollectionRecord['id'];
                    $this->printMessage('log_id:'.$loanCollectionRecord['id'].'正在处理');
                    $loanPerson = LoanPerson::findOne($loanCollectionRecord['loan_user_id']);
                    if(empty($loanPerson)){
                        continue;
                    }
                    $order = UserLoanOrder::findOne($loanCollectionRecord['loan_order_id']);
                    if(empty($order)){
                        continue;
                    }

                    if(empty($order->clientInfoLog)){
                        $clientInfoLog = json_decode($order->client_info, true);
                        if(empty($clientInfoLog['packageName'])){
                            $this->printMessage('log_id：'.$loanCollectionRecord['id'].'没有app_name,跳过');
                            continue;
                        }
                        $app_name = $clientInfoLog['packageName'];
                    }else{
                        $app_name = $order->clientInfoLog->package_name;
                        if(empty($app_name)) {
                            $this->printMessage('log_id：'.$loanCollectionRecord['id'].'没有app_name,跳过');
                            continue;
                        }
                    }

                    $model = new LoanCollectionRecordOther();
                    $params = [
                        'order_id'               => $loanCollectionRecord['loan_order_id'],
                        'app_name'               => $app_name,
                        'user_id'                => $loanCollectionRecord['loan_user_id'],
                        'request_id'             => $loanCollectionRecord['id'],
                        'pan_code'               => $loanPerson['pan_code'],
                        'contact_type'           => $loanCollectionRecord['contact_type'],
                        'order_level'            => $loanCollectionRecord['order_level'],
                        'operate_type'           => $loanCollectionRecord['operate_type'],
                        'operate_at'             => $loanCollectionRecord['operate_at'],
                        'promise_repayment_time' => $loanCollectionRecord['promise_repayment_time'],
                        'risk_control'           => $loanCollectionRecord['risk_control'],
                        'is_connect'             => $loanCollectionRecord['is_connect'],
                    ];

                    $model->load($params, '');
                    if(!$model->save()){
                        $this->printMessage('logId:'.$loanCollectionRecord['id'].'保存失败');
                        RedisQueue::newSet('loan_collection_log', $loanCollectionRecord['id']-1);
                        exit;
                    }

                }

                if(time() - $now > 300)
                {
                    RedisQueue::newSet('loan_collection_log', $loanCollectionRecord['id']);
                    $this->printMessage("运行满5分钟，关闭当前脚本");
                    return;
                }

                $clone_query = clone $query;
                $data = $clone_query->andWhere(['>', 'id', $maxId])->all();
            }

            if(!empty($loanCollectionRecord['id'])){
                RedisQueue::newSet('loan_collection_log', $loanCollectionRecord['id']);
            }

            $this->printMessage('脚本结束');
            $service = new WeWorkService();
            $message = '催收记录同步完成，请关闭脚本';
            $service->sendText(['meiyunfei'],$message);
        }catch (\Exception $exception){
            RedisQueue::newSet('loan_collection_log', $loanCollectionRecord['id']-1);

            $service = new WeWorkService();
            $message = sprintf('[%s][%s]异常[loan_collection_log:%s] : %s in %s:%s',
                \yii::$app->id, \Yii::$app->requestedRoute, $loanCollectionRecord['id'], $exception->getMessage(), $exception->getFile(), $exception->getLine());
            $message .= $exception->getTraceAsString();
            $service->sendText(['meiyunfei'],$message);
        }
    }

    public function actionPushLoginLog(){
        if (!$this->lock()) {
            return;
        }
        $id = RedisQueue::newGet('login_log');
        if($id){
            $maxId = $id;
        }else{
            $maxId = 0;
        }

        $this->printMessage("脚本开始");

        $now = time();

        try{
            $query = ClientInfoLog::find()
                ->where(['event' => ClientInfoLog::EVENT_LOGIN])
                ->orderBy(['id' => SORT_ASC])
                ->limit(1000);
            $clone_query = clone $query;
            $data = $clone_query->andWhere(['>', 'id', $maxId])->all();

            while ($data){
                foreach ($data as $clientInfoLog){
                    $loanPerson = LoanPerson::findOne($clientInfoLog['user_id']);
                    if(empty($loanPerson) || empty($clientInfoLog['package_name'])){
                        continue;
                    }
                    $model = LoginLog::findOne(['app_name' => $clientInfoLog['package_name'], 'request_id' => $clientInfoLog['id']]);
                    if(!empty($model)){
                        continue;
                    }
                    $model = new LoginLog();
                    $model->app_name      = $clientInfoLog['package_name'];
                    $model->request_id    = $clientInfoLog['id'];
                    $model->phone         = $loanPerson['phone'];
                    $model->user_id       = $clientInfoLog['user_id'];
                    $model->client_type   = $clientInfoLog['client_type'];
                    $model->os_version    = $clientInfoLog['os_version'];
                    $model->app_version   = $clientInfoLog['app_version'];
                    $model->device_name   = $clientInfoLog['device_name'];
                    $model->app_market    = $clientInfoLog['app_market'];
                    $model->device_id     = $clientInfoLog['device_id'];
                    $model->brand_name    = $clientInfoLog['brand_name'];
                    $model->bundle_id     = $clientInfoLog['bundle_id'];
                    $model->latitude      = $clientInfoLog['latitude'];
                    $model->longitude     = $clientInfoLog['longitude'];
                    $model->szlm_query_id = $clientInfoLog['szlm_query_id'];
                    $model->screen_width  = $clientInfoLog['screen_width'];
                    $model->screen_height = $clientInfoLog['screen_height'];
                    $model->ip            = $clientInfoLog['ip'];
                    $model->client_time   = $clientInfoLog['client_time'];
                    $model->event_time    = $clientInfoLog['created_at'];

                    if(!$model->save()){
                        $this->printMessage('logId:'.$clientInfoLog['id'].'保存失败');
                        RedisQueue::newSet('login_log', $clientInfoLog['id']-1);
                        $service = new WeWorkService();
                        $service->send('登陆数据同步logId:'.$clientInfoLog['id'].'保存失败');
                        exit;
                    }
                }

                if(time() - $now > 300)
                {
                    RedisQueue::newSet('login_log', $clientInfoLog['id']);
                    $this->printMessage("运行满5分钟，关闭当前脚本");
                    return;
                }

                $maxId = $clientInfoLog['id'];
                $clone_query = clone $query;
                $data = $clone_query->andWhere(['>', 'id', $maxId])->all();
            }

            RedisQueue::newSet('login_log', $clientInfoLog['id']);
            $service = new WeWorkService();
            $service->sendText(['meiyunfei'], '登陆数据已同步完毕，需要关闭脚本');
        }catch (\Exception $exception){
            RedisQueue::newSet('login_log', $clientInfoLog['id']-1);

            $service = new WeWorkService();
            $message = sprintf('[%s][%s]异常[log_id:%s] : %s in %s:%s',
                \yii::$app->id, \Yii::$app->requestedRoute, $clientInfoLog['id'], $exception->getMessage(), $exception->getFile(), $exception->getLine());
            $message .= $exception->getTraceAsString();
            $service->sendText(['meiyunfei'],$message);
        }
    }

    public function actionPushOrderInfo(){
        if (!$this->lock()) {
            return;
        }

        Util::cliLimitChange();

        $id = RedisQueue::newGet('user_loan_order');
        if($id){
            $maxId = $id;
        }else{
            $maxId = 0;
        }

        $this->printMessage("脚本开始");

        $now = time();

        try{
            $query = UserLoanOrder::find()
                ->where('1=1')
                ->orderBy(['id' => SORT_ASC])
                ->limit(1000);

            $clone_query = clone $query;
            $data = $clone_query->andWhere(['>', 'id', $maxId])->all();

            while ($data){
                /** @var UserLoanOrder $order */
                foreach ($data as $order){
                    $this->printMessage('order_id:'.$order->id.'正在处理');
                    if(empty($order->loanPerson)){
                        continue;
                    }
                    if(empty($order->clientInfoLog)){
                        $clientInfoLog = json_decode($order->client_info, true);
                        if(empty($clientInfoLog['packageName'])){
                            $this->printMessage('订单ID：'.$order->id.'没有app_name,跳过');
                            continue;
                        }
                        $app_name = $clientInfoLog['packageName'];
                        $params['client_type']   = $clientInfoLog['clientType'] ?? '';
                        $params['os_version']    = $clientInfoLog['osVersion'] ?? '';
                        $params['app_version']   = $clientInfoLog['appVersion'] ?? '';
                        $params['device_name']   = $clientInfoLog['deviceName'] ?? '';
                        $params['app_market']    = $clientInfoLog['appMarket'] ?? '';
                        $params['device_id']     = $clientInfoLog['deviceId'] ?? '';
                        $params['brand_name']    = $clientInfoLog['brandName'] ?? '';
                        $params['bundle_id']     = $clientInfoLog['bundleId'] ?? '';
                        $params['longitude']     = $clientInfoLog['longitude'] ?? '';
                        $params['latitude']      = $clientInfoLog['latitude'] ?? '';
                        $params['szlm_query_id'] = $clientInfoLog['szlmQueryId'] ?? '';
                        $params['screen_width']  = $clientInfoLog['screenWidth'] ?? 0;
                        $params['screen_height'] = $clientInfoLog['screenHeight'] ?? 0;
                        $params['ip']            = $clientInfoLog['ip'] ?? 0;
                        $params['client_time']   = isset($clientInfoLog['timestamp']) ? intval($clientInfoLog['timestamp'] / 1000) : 0;
                    }else{
                        $app_name = $order->clientInfoLog->package_name;
                        if(empty($app_name)){
                            $this->printMessage('订单ID：'.$order->id.'没有app_name,跳过');
                            continue;
                        }
                        $params['client_type']   = $order->clientInfoLog->client_type;
                        $params['os_version']    = $order->clientInfoLog->os_version;
                        $params['app_version']   = $order->clientInfoLog->app_version;
                        $params['device_name']   = $order->clientInfoLog->device_name;
                        $params['app_market']    = $order->clientInfoLog->app_market;
                        $params['device_id']     = $order->clientInfoLog->device_id;
                        $params['brand_name']    = $order->clientInfoLog->brand_name;
                        $params['bundle_id']     = $order->clientInfoLog->bundle_id;
                        $params['longitude']     = $order->clientInfoLog->longitude;
                        $params['latitude']      = $order->clientInfoLog->latitude;
                        $params['szlm_query_id'] = $order->clientInfoLog->szlm_query_id;
                        $params['screen_width']  = $order->clientInfoLog->screen_width;
                        $params['screen_height'] = $order->clientInfoLog->screen_height;
                        $params['ip']            = $order->clientInfoLog->ip;
                        $params['client_time']   = $order->clientInfoLog->client_time;
                    }
                    $orderExtraService = new OrderExtraService($order);
                    $workInfo          = $orderExtraService->getUserWorkInfo();
                    $basicInfo         = $orderExtraService->getUserBasicInfo();
                    $contact           = $orderExtraService->getUserContact();
                    $ocrPanReport      = $orderExtraService->getUserOcrPanReport();
                    $PanVerReport      = $orderExtraService->getUserVerifyPanReport();
                    $ocrAadhaarReport  = $orderExtraService->getUserOcrAadhaarReport();
                    $questionReport    = $orderExtraService->getUserQuestionReport();
                    $frLivenessReport  = $orderExtraService->getUserFrReport();
                    $frCompareReport   = $orderExtraService->getUserFrCompareReport();

                    if($order->status == UserLoanOrder::STATUS_PAYMENT_COMPLETE){
                        $status = 'closed_repayment';
                    }

                    if($order->status == UserLoanOrder::STATUS_LOAN_COMPLETE){
                        $status = 'pending_repayment';
                    }

                    if($order->status == UserLoanOrder::STATUS_CHECK_REJECT){
                        if(in_array($order->audit_status, [
                                UserLoanOrder::AUDIT_STATUS_GET_ORDER,
                                UserLoanOrder::AUDIT_STATUS_MANUAL_CHECK,
                                UserLoanOrder::AUDIT_STATUS_MANUAL_CHECK_FINISH]
                        )){
                            $status = 'reject_risk_manual';
                        }else{
                            $status = 'reject_risk_auto';
                        }
                    }

                    if(in_array($order->status, [
                        UserLoanOrder::STATUS_DEPOSIT_REJECT,
                        UserLoanOrder::STATUS_WAIT_DRAW_MONEY_TIMEOUT,
                        UserLoanOrder::STATUS_LOAN_REJECT
                    ])){
                        $status = 'reject_loan';
                    }

                    $infoOrder = InfoOrder::find()->where([
                        'app_name' => $app_name,
                        'order_id' => $order->id,
                        'user_id' => $order->user_id,
                    ])->exists();
                    if(!$infoOrder)
                    {
                        $infoOrder                    = new InfoOrder();
                        $infoOrder->app_name          = $app_name;
                        $infoOrder->order_id          = $order->id;
                        $infoOrder->user_id           = $order->user_id;
                        $infoOrder->is_external       = $order->is_export == UserLoanOrder::IS_EXPORT_YES ? 'y' : 'n';
                        $infoOrder->external_app_name = '';
                        $infoOrder->principal         = $order->amount;
                        $infoOrder->day_rate          = $order->day_rate;
                        $infoOrder->overdue_rate      = $order->overdue_rate;
                        $infoOrder->cost_rate         = $order->cost_rate;
                        $infoOrder->periods           = $order->periods;
                        $infoOrder->status            = $status ?? 'default';
                        $infoOrder->order_time        = $order->order_time;
                        $infoOrder->loan_time         = $order->loan_time;
                        $infoOrder->is_first          = $order->is_first == UserLoanOrder::FIRST_LOAN_IS ? 'y' : 'n';
                        $infoOrder->is_all_first      = $order->is_all_first == UserLoanOrder::FIRST_LOAN_IS ? 'y' : 'n';
                        $infoOrder->is_external_first = 'n';

                        if(!$infoOrder->save()){
                            $this->printMessage('orderId:'.$order['id'].'保存失败');
                            RedisQueue::newSet('user_loan_order', $order['id']-1);
                            $service = new WeWorkService();
                            $service->send('订单数据同步orderId:'.$order['id'].'保存失败');
                            exit;
                        }
                    }

                    $infoUser = InfoUser::find()->where([
                        'app_name' => $app_name,
                        'order_id' => $order->id,
                        'user_id' => $order->user_id,
                    ])->exists();
                    if(!$infoUser)
                    {
                        $infoUser                             = new InfoUser();
                        $infoUser->app_name                   = $app_name;
                        $infoUser->order_id                   = $order->id;
                        $infoUser->user_id                    = $order->user_id;
                        $infoUser->phone                      = $order->loanPerson->phone;
                        $infoUser->pan_code                   = $order->loanPerson->pan_code;
                        $infoUser->gender                     = $order->loanPerson->gender;
                        $infoUser->aadhaar_md5                = $order->loanPerson->aadhaar_md5;
                        $infoUser->filled_name                = $basicInfo->full_name ?? '';
                        $infoUser->pan_ocr_name               = $ocrPanReport->full_name ?? '';
                        $infoUser->aadhaar_ocr_name           = $ocrAadhaarReport->full_name ?? '';
                        $infoUser->pan_verify_name            = $order->loanPerson->name;
                        $infoUser->filled_birthday            = $basicInfo->birthday ?? '';
                        $infoUser->pan_birthday               = $order->loanPerson->birthday;
                        $infoUser->aadhaar_birthday           = $ocrAadhaarReport->date_info ?? '';
                        $infoUser->education_level            = $workInfo->educated ?? '';
                        $infoUser->occupation                 = $workInfo->industry ?? '';
                        $infoUser->residential_detail_address = $workInfo->residential_detail_address ?? '';
                        $infoUser->residential_address        = $workInfo->residential_address1 ?? '';
                        $infoUser->residential_city           = $workInfo->residential_address2 ?? '';
                        $infoUser->aadhaar_address            = $ocrAadhaarReport->address ?? '';
                        $infoUser->aadhaar_ocr_pin_code       = $ocrAadhaarReport->pin ?? '';
                        $infoUser->aadhaar_filled_city        = $basicInfo->aadhaar_address2 ?? '';
                        $infoUser->aadhaar_pin_code           = $basicInfo->aadhaar_pin_code ?? '';
                        $infoUser->monthly_salary             = $workInfo->monthly_salary ?? '';
                        $infoUser->contact1_mobile_number     = $contact->phone ?? '';
                        $infoUser->contact2_mobile_number     = $contact->other_phone ?? '';
                        $infoUser->fr_liveness_source         = $frLivenessReport->type == UserCreditReportFrLiveness::SOURCE_ACCUAUTH ? 'accu_auth' : 'advance_ai';
                        $infoUser->fr_liveness_score          = $frLivenessReport->score;
                        $infoUser->fr_verify_source           = $frCompareReport->type == UserCreditReportFrVerify::SOURCE_ACCUAUTH ? 'accu_auth' : 'advance_ai';
                        $infoUser->fr_verify_type             = $frCompareReport->report_type == UserCreditReportFrVerify::TYPE_FR_COMPARE_PAN ? 'pan' : 'fr';
                        $infoUser->fr_verify_score            = $frCompareReport->score;
                        $infoUser->language_need_check        = !empty($questionReport) ? 'y' : 'n';
                        $infoUser->language_correct_number    = $questionReport->correct_num ?? 0;
                        $infoUser->language_time              = !empty($questionReport) ? ($questionReport->submit_time - $questionReport->enter_time) : 0;
                        $infoUser->register_time              = $order->loanPerson->created_at ?? '';
                        $infoUser->pan_ocr_code               = $PanVerReport->pan_ocr ?? '';

                        if(!$infoUser->save()){
                            $this->printMessage('用户信息保存失败：orderId:'.$order['id']);
                            RedisQueue::newSet('user_loan_order', $order['id']-1);
                            $service = new WeWorkService();
                            $service->send('用户数据同步orderId:'.$order['id'].'保存失败');
                            exit;
                        }
                    }

                    $infoDevice = InfoDevice::find()->where([
                        'app_name' => $app_name,
                        'order_id' => $order->id,
                        'user_id' => $order->user_id,
                    ])->exists();
                    if(!$infoDevice)
                    {
                        $infoDevice                = new InfoDevice();
                        $infoDevice->app_name      = $app_name;
                        $infoDevice->order_id      = $order->id;
                        $infoDevice->user_id       = $order->user_id;
                        $infoDevice->phone         = $order->loanPerson->phone;
                        $infoDevice->pan_code      = $order->loanPerson->pan_code;
                        $infoDevice->client_type   = $params['client_type'] ?? '';
                        $infoDevice->os_version    = $params['os_version'] ?? '';
                        $infoDevice->app_version   = $params['app_version'] ?? '';
                        $infoDevice->device_name   = $params['device_name'] ?? '';
                        $infoDevice->app_market    = $params['app_market'] ?? '';
                        $infoDevice->device_id     = $params['device_id'] ?? '';
                        $infoDevice->brand_name    = $params['brand_name'] ?? '';
                        $infoDevice->bundle_id     = $params['bundle_id'] ?? '';
                        $infoDevice->longitude     = $params['longitude'] ?? '';
                        $infoDevice->latitude      = $params['latitude'] ?? '';
                        $infoDevice->szlm_query_id = $params['szlm_query_id'] ?? '';
                        $infoDevice->screen_width  = $params['screen_width'] ?? 0;
                        $infoDevice->screen_height = $params['screen_height'] ?? 0;
                        $infoDevice->ip            = $params['ip'];
                        $infoDevice->client_time   = $params['client_time'];
                        $infoDevice->event_time    = $order->order_time;

                        if(!$infoDevice->save()){
                            $this->printMessage('设备信息保存失败：orderId:'.$order['id']);
                            RedisQueue::newSet('user_loan_order', $order['id']-1);
                            $service = new WeWorkService();
                            $service->send('设备数据同步orderId:'.$order['id'].'保存失败');
                            exit;
                        }
                    }

                    if(empty($order->userLoanOrderRepayment)){
                        continue;
                    }
                    $infoRepayment = InfoRepayment::find()->where([
                        'app_name' => $app_name,
                        'order_id' => $order->id,
                        'user_id' => $order->user_id,
                    ])->exists();
                    if(!$infoRepayment)
                    {
                        $infoRepayment                      = new InfoRepayment();
                        $infoRepayment->app_name            = $app_name;
                        $infoRepayment->order_id            = $order->id;
                        $infoRepayment->user_id             = $order->user_id;
                        $infoRepayment->total_money         = $order->userLoanOrderRepayment->total_money;
                        $infoRepayment->true_total_money    = $order->userLoanOrderRepayment->true_total_money;
                        $infoRepayment->principal           = $order->userLoanOrderRepayment->principal;
                        $infoRepayment->interests           = $order->userLoanOrderRepayment->interests;
                        $infoRepayment->cost_fee            = $order->userLoanOrderRepayment->cost_fee;
                        $infoRepayment->overdue_fee         = $order->userLoanOrderRepayment->overdue_fee;
                        $infoRepayment->is_overdue          = $order->userLoanOrderRepayment->is_overdue == UserLoanOrderRepayment::IS_OVERDUE_YES ? 'y' : 'n';
                        $infoRepayment->overdue_day         = $order->userLoanOrderRepayment->overdue_day;
                        $infoRepayment->status              = $order->userLoanOrderRepayment->status == UserLoanOrderRepayment::STATUS_REPAY_COMPLETE ? 'closed' : 'pending';
                        $infoRepayment->loan_time           = $order->userLoanOrderRepayment->loan_time;
                        $infoRepayment->plan_repayment_time = $order->userLoanOrderRepayment->plan_repayment_time;
                        $infoRepayment->closing_time        = $order->userLoanOrderRepayment->closing_time;

                        if(!$infoRepayment->save()){
                            $this->printMessage('还款信息保存失败：orderId:'.$order['id']);
                            RedisQueue::newSet('user_loan_order', $order['id']-1);
                            $service = new WeWorkService();
                            $service->send('还款数据同步orderId:'.$order['id'].'保存失败');
                            exit;
                        }
                    }
                }

                if(time() - $now > 300)
                {
                    RedisQueue::newSet('user_loan_order', $order['id']);
                    $this->printMessage("运行满5分钟，关闭当前脚本");
                    return;
                }

                $maxId = $order['id'];
                $clone_query = clone $query;
                $data = $clone_query->andWhere(['>', 'id', $maxId])->all();
            }

            RedisQueue::newSet('user_loan_order', $order['id']);
            $service = new WeWorkService();
            $service->sendText(['meiyunfei'], '订单数据已同步完毕，需要关闭脚本');
        }catch (\Exception $exception){
            RedisQueue::newSet('user_loan_order', $order['id']-1);

            $service = new WeWorkService();
            $message = sprintf('[%s][%s]异常[order_id:%s] : %s in %s:%s',
                \yii::$app->id, \Yii::$app->requestedRoute, $order['id'], $exception->getMessage(), $exception->getFile(), $exception->getLine());
            $message .= $exception->getTraceAsString();
            $service->sendText(['meiyunfei'],$message);
        }
    }

    public function actionPushSuggestion(){
        if (!$this->lock()) {
            return;
        }
        $id = RedisQueue::newGet('suggestion');
        if($id){
            $maxId = $id;
        }else{
            $maxId = 0;
        }

        $this->printMessage("脚本开始");

        $now = time();

        try{
            $query = LoanCollectionSuggestionChangeLog::find()
                ->where(['suggestion' => LoanCollectionOrder::RENEW_REJECT])
                ->orderBy(['id' => SORT_ASC])
                ->limit(1000);
            $clone_query = clone $query;
            $data = $clone_query->andWhere(['>', 'id', $maxId])->all();

            while ($data){
                foreach ($data as $v){
                    $order = UserLoanOrder::findOne($v['order_id']);
                    if(empty($order)){
                        continue;
                    }

                    if(empty($order->clientInfoLog)){
                        $clientInfoLog = json_decode($order->client_info, true);
                        if(empty($clientInfoLog['packageName'])){
                            continue;
                        }
                        $app_name = $clientInfoLog['packageName'];
                    }else{
                        $app_name = $order->clientInfoLog->package_name;
                    }

                    $infoCollection = InfoCollectionSuggestion::findOne([
                        'app_name' => $app_name,
                        'user_id'  => $order->user_id,
                        'order_id' => $order->id
                    ]);
                    if(!empty($infoCollection))
                    {
                        continue;
                    }

                    $model = new InfoCollectionSuggestion();
                    $model->app_name = $app_name;
                    $model->order_id = $order->id;
                    $model->user_id = $order->user_id;
                    $model->phone = $order->loanPerson->phone;
                    $model->pan_code = $order->loanPerson->pan_code;
                    $model->szlm_query_id = $order->did;

                    if(!$model->save()){
                        $this->printMessage('催收建议拒绝logId:'.$v['id'].'保存失败');
                        RedisQueue::newSet('suggestion', $v['id']-1);
                        $service = new WeWorkService();
                        $service->send('催收建议拒绝数据同步logId:'.$v['id'].'保存失败');
                        exit;
                    }
                }

                if(time() - $now > 300)
                {
                    RedisQueue::newSet('suggestion', $v['id']);
                    $this->printMessage("运行满5分钟，关闭当前脚本");
                    return;
                }

                $maxId = $v['id'];
                $clone_query = clone $query;
                $data = $clone_query->andWhere(['>', 'id', $maxId])->all();
            }

            RedisQueue::newSet('suggestion', $v['id']);
            $service = new WeWorkService();
            $service->send('催收建议拒绝已同步完毕，需要关闭脚本');
        }catch (\Exception $exception){
            RedisQueue::newSet('suggestion', $v['id']-1);

            $service = new WeWorkService();
            $message = sprintf('[%s][%s]异常[log_id:%s] : %s in %s:%s',
                \yii::$app->id, \Yii::$app->requestedRoute, $v['id'], $exception->getMessage(), $exception->getFile(), $exception->getLine());
            $message .= $exception->getTraceAsString();
            $service->sendText(['meiyunfei'],$message);
        }
    }

    public function actionPushRiskBlack(){
        if (!$this->lock()) {
            return;
        }

        $this->printMessage("脚本开始");

        try{
            $data = RiskBlackListDeviceid::find()->all();
            foreach ($data as $v){
                $model = RiskBlackListDeviceidOther::find()->where(['value' => $v['value']])->exists();
                if(!$model){
                    $model = new RiskBlackListDeviceidOther();
                    $model->value = $v['value'];
                    if(!$model->save()){
                        $this->printMessage('设备id黑名单保存失败');
                        return;
                    }
                }
            }

            $data = RiskBlackListSzlm::find()->all();
            foreach ($data as $v){
                $model = RiskBlackListSzlmOther::find()->where(['value' => $v['value']])->exists();
                if(!$model){
                    $model = new RiskBlackListSzlmOther();
                    $model->value = $v['value'];
                    if(!$model->save()){
                        $this->printMessage('数盟id黑名单保存失败');
                        return;
                    }
                }
            }

            $data = RiskBlackListPhone::find()->all();
            foreach ($data as $v){
                $model = RiskBlackListPhoneOther::find()->where(['value' => $v['value']])->exists();
                if(!$model){
                    $model = new RiskBlackListPhoneOther();
                    $model->value = $v['value'];
                    if(!$model->save()){
                        $this->printMessage('手机号黑名单保存失败');
                        return;
                    }
                }
            }

            $data = RiskBlackListAadhaar::find()->all();
            foreach ($data as $v){
                $model = RiskBlackListAadhaarOther::find()->where(['value' => $v['value']])->exists();
                if(!$model){
                    $model = new RiskBlackListAadhaarOther();
                    $model->value = $v['value'];
                    if(!$model->save()){
                        $this->printMessage('aadhaar黑名单保存失败');
                        return;
                    }
                }
            }

            $data = RiskBlackListPan::find()->all();
            foreach ($data as $v){
                $model = RiskBlackListPanOther::find()->where(['value' => $v['value']])->exists();
                if(!$model){
                    $model = new RiskBlackListPanOther();
                    $model->value = $v['value'];
                    if(!$model->save()){
                        $this->printMessage('pan黑名单保存失败');
                        return;
                    }
                }
            }

            $this->printMessage('脚本结束');

        }catch (\Exception $exception){
            $service = new WeWorkService();
            $message = sprintf('[%s][%s]异常 : %s in %s:%s',
                \yii::$app->id, \Yii::$app->requestedRoute, $exception->getMessage(), $exception->getFile(), $exception->getLine());
            $message .= $exception->getTraceAsString();
            $service->sendText(['meiyunfei'],$message);
        }
    }

    public function actionPushUpdateOrderInfo($time='2020-04-28'){
        if (!$this->lock()) {
            return;
        }

        $this->printMessage("脚本开始");

        $begin_time = strtotime($time);
        $end_time = $begin_time + 86400;

        try{
            $data = UserLoanOrder::find()
                ->where(['>=', 'updated_at', $begin_time])
                ->andWhere(['<', 'updated_at', $end_time])
                ->all();

            /** @var UserLoanOrder $order */
            foreach ($data as $order){
                $this->printMessage('order_id:'.$order->id.'正在处理');
                if(empty($order->clientInfoLog)){
                    $clientInfoLog = json_decode($order->client_info, true);
                    if(empty($clientInfoLog['packageName'])){
                        $this->printMessage('订单ID：'.$order->id.'没有app_name,跳过');
                        continue;
                    }
                    $app_name = $clientInfoLog['packageName'];
                }else{
                    $app_name = $order->clientInfoLog->package_name;
                    if(empty($app_name)){
                        $this->printMessage('订单ID：'.$order->id.'没有app_name,跳过');
                        continue;
                    }
                }

                if($order->status == UserLoanOrder::STATUS_PAYMENT_COMPLETE){
                    $status = 'closed_repayment';
                }

                if($order->status == UserLoanOrder::STATUS_LOAN_COMPLETE){
                    $status = 'pending_repayment';
                }

                if($order->status == UserLoanOrder::STATUS_CHECK_REJECT){
                    if(in_array($order->audit_status, [
                            UserLoanOrder::AUDIT_STATUS_GET_ORDER,
                            UserLoanOrder::AUDIT_STATUS_MANUAL_CHECK,
                            UserLoanOrder::AUDIT_STATUS_MANUAL_CHECK_FINISH]
                    )){
                        $status = 'reject_risk_manual';
                    }else{
                        $status = 'reject_risk_auto';
                    }
                }

                if(in_array($order->status, [
                    UserLoanOrder::STATUS_DEPOSIT_REJECT,
                    UserLoanOrder::STATUS_WAIT_DRAW_MONEY_TIMEOUT,
                    UserLoanOrder::STATUS_LOAN_REJECT
                ])){
                    $status = 'reject_loan';
                }

                /** @var InfoOrder $infoOrder */
                $infoOrder = InfoOrder::find()->where([
                    'app_name' => $app_name,
                    'order_id' => $order->id,
                    'user_id' => $order->user_id,
                ])->one();
                if(!empty($infoOrder))
                {
                    $infoOrder->principal = $order->amount;
                    $infoOrder->loan_time = $order->loan_time;
                    $infoOrder->status    = $status ?? 'default';
                    if(!$infoOrder->save()){
                        $this->printMessage('orderId:'.$order['id'].'保存失败');
                        exit;
                    }
                }
            }

            $this->printMessage("脚本结束");
        }catch (\Exception $exception){
            $service = new WeWorkService();
            $message = sprintf('[%s][%s]异常[order_id:%s] : %s in %s:%s',
                \yii::$app->id, \Yii::$app->requestedRoute, $order->id, $exception->getMessage(), $exception->getFile(), $exception->getLine());
            $message .= $exception->getTraceAsString();
            $service->sendText(['meiyunfei'],$message);
        }
    }

    public function actionPushUpdateRepaymentInfo($time='2020-04-28'){
        if (!$this->lock()) {
            return;
        }

        $this->printMessage("脚本开始");
        $begin_time = strtotime($time);
        $end_time = $begin_time + 86400;

        try{
            $query = UserLoanOrderRepayment::find()
                ->where(['<', 'updated_at', $end_time])
                ->orderBy(['updated_at' => SORT_ASC])
                ->limit(1000);

            $query_clone = clone $query;
            $data = $query_clone->andWhere(['>=', 'updated_at', $begin_time])->all();

            while ($data) {
                /** @var UserLoanOrderRepayment $repayment */
                foreach ($data as $repayment){
                    $this->printMessage('order_id:'.$repayment->order_id.'正在处理');
                    $order = $repayment->userLoanOrder;
                    if(empty($order)){
                        continue;
                    }
                    if(empty($order->clientInfoLog)){
                        $clientInfoLog = json_decode($order->client_info, true);
                        if(empty($clientInfoLog['packageName'])){
                            $this->printMessage('订单ID：'.$order->id.'没有app_name,跳过');
                            continue;
                        }
                        $app_name = $clientInfoLog['packageName'];
                    }else{
                        $app_name = $order->clientInfoLog->package_name;
                        if(empty($app_name)){
                            $this->printMessage('订单ID：'.$order->id.'没有app_name,跳过');
                            continue;
                        }
                    }

                    /** @var InfoRepayment $infoRepayment */
                    $infoRepayment = InfoRepayment::find()->where([
                        'app_name' => $app_name,
                        'order_id' => $order->id,
                        'user_id' => $order->user_id,
                    ])->one();
                    if(!empty($infoRepayment))
                    {
                        $infoRepayment->total_money      = $repayment->total_money;
                        $infoRepayment->true_total_money = $repayment->true_total_money;
                        $infoRepayment->overdue_fee      = $repayment->overdue_fee;
                        $infoRepayment->is_overdue       = $repayment->is_overdue == UserLoanOrderRepayment::IS_OVERDUE_YES ? 'y' : 'n';
                        $infoRepayment->overdue_day      = $repayment->overdue_day;
                        $infoRepayment->status           = $repayment->status == UserLoanOrderRepayment::STATUS_REPAY_COMPLETE ? 'closed' : 'pending';
                        $infoRepayment->closing_time     = $repayment->closing_time;

                        if(!$infoRepayment->save()){
                            $this->printMessage('还款信息保存失败：orderId:'.$order['id']);
                            exit;
                        }
                    }
                }

                $begin_time = $repayment->updated_at;
                $query_clone = clone $query;
                $data = $query_clone->andWhere(['>=', 'updated_at', $begin_time])->all();
            }

            $this->printMessage("脚本结束");
        }catch (\Exception $exception){
            $service = new WeWorkService();
            $message = sprintf('[%s][%s]异常[order_id:%s] : %s in %s:%s',
                \yii::$app->id, \Yii::$app->requestedRoute, $order->id, $exception->getMessage(), $exception->getFile(), $exception->getLine());
            $message .= $exception->getTraceAsString();
            $service->sendText(['meiyunfei'],$message);
        }
    }
}
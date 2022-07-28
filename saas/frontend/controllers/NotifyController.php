<?php


namespace frontend\controllers;

use backend\models\ReminderCallData;
use callcenter\models\CollectorCallData;
use callcenter\models\LevelChangeDailyCall;
use common\helpers\CommonHelper;
use common\models\enum\ErrorCode;
use common\models\kudos\LoanKudosOrder;
use common\models\kudos\LoanKudosPerson;
use common\models\message\NxPhoneLog;
use common\models\order\UserLoanOrder;
use common\models\order\UserRepaymentLog;
use common\models\order\UserRepaymentReducedLog;
use common\models\user\UserActiveTime;
use common\services\message\WeWorkService;
use common\services\order\OrderService;
use common\services\pay\CashFreePaymentService;
use common\services\pay\JolosoftPayoutService;
use common\services\pay\JpayService;
use common\services\pay\MojoService;
use common\services\pay\MpursePayoutService;
use common\services\pay\MpurseService;
use common\services\pay\QimingPayoutService;
use common\services\pay\QimingService;
use common\services\pay\QuanqiupayService;
use common\services\pay\RazorpayPayoutService;
use common\services\pay\RazorpayService;
use common\services\pay\RpayService;
use common\services\pay\SifangService;
use common\services\repayment\ReductionService;
use common\services\repayment\RepaymentService;
use common\services\user\UserService;
use frontend\models\notify\KudosOfflineTransactionForm;
use frontend\models\notify\PushOrderNotifyForm;
use frontend\models\notify\PushOrderUserInfoForm;
use frontend\models\notify\ReduceOrderForm;
use Yii;
use yii\web\Response;

class NotifyController extends BaseController
{
    /**
     * 风控回调
     * @return array
     */
    public function actionOrderRiskNotify()
    {
        $validateModel = new PushOrderNotifyForm();
        if (!$validateModel->load(Yii::$app->request->post(), '')) {
            return $this->return
                ->setData([])
                ->returnFailed(ErrorCode::ERROR_COMMON());
        }

        if (!$validateModel->validate()) {
            return $this->return
                ->returnFailed(ErrorCode::ERROR_COMMON(), $validateModel->getErrorSummary(false)[0]);
        }

        $order = UserLoanOrder::findOne($validateModel->order_id);
        if(empty($order)){
            return $this->return
                ->setData([])
                ->returnFailed(ErrorCode::ERROR_COMMON());
        }

        $service = new OrderService($order);
        if ($service->orderRiskNotify($validateModel->data)) {
            return $this->return->setData([])->returnOK();
        } else {
            return $this->return
                ->returnFailed(ErrorCode::ERROR_COMMON(), $service->getError());
        }
    }

    /**
     * 催收订单减免通知
     * @return array
     */
    public function actionReduceOrder(){
        $validateModel = new ReduceOrderForm();
        if ($validateModel->load(Yii::$app->request->post(), '') && $validateModel->validate()) {
            $order = UserLoanOrder::findOne($validateModel->order_id);
            if(empty($order) || $order->user_id != $validateModel->user_id || $order->clientInfoLog->package_name != $validateModel->app_name){
                return $this->return
                    ->returnFailed(ErrorCode::ERROR_COMMON(), '订单不存在');
            }

            $service = new ReductionService();
            if ($service->reductionHandle($order->id, 0, '', UserRepaymentReducedLog::FROM_CS_SYSTEM)) {
                return $this->return->setData([])->returnOK();
            } else {
                return $this->return
                    ->returnFailed(ErrorCode::ERROR_COMMON(), $service->getError());
            }
        }else{
            $this->return->returnFailed(ErrorCode::ERROR_COMMON(),
                $this->getError($validateModel->getErrorSummary(false)));
        }
    }

    public function actionPushOrderUserInfoNotice()
    {
        $validateModel = new PushOrderUserInfoForm();
        if (!$validateModel->load(Yii::$app->request->post(), '')) {
            return $this->return
                ->setData([])
                ->returnFailed(ErrorCode::ERROR_COMMON());
        }

        if (!$validateModel->validate()) {
            return $this->return
                ->returnFailed(ErrorCode::ERROR_COMMON(), $validateModel->getErrorSummary(false)[0]);
        }

        $validateModel->decodeData = json_decode(gzuncompress(base64_decode($validateModel->data)), true);
        $userService = new UserService();
        //同步用户认证
        if ($userService->verificationByPushData($validateModel)) {
            return $this->return->setData([])->returnOK();
        } else {
            return $this->return
                ->returnFailed(ErrorCode::ERROR_COMMON(), $userService->getError());
        }
    }

    public function actionPushOrderDeviceInfoNotice()
    {
        $validateModel = new PushOrderUserInfoForm();
        if (!$validateModel->load(Yii::$app->request->post(), '')) {
            return $this->return
                ->setData([])
                ->returnFailed(ErrorCode::ERROR_COMMON());
        }

        if (!$validateModel->validate()) {
            return $this->return
                ->returnFailed(ErrorCode::ERROR_COMMON(), $validateModel->getErrorSummary(false)[0]);
        }

        $validateModel->decodeData = json_decode(gzuncompress(base64_decode($validateModel->data)), true);
        $userService = new UserService();
        //用户注册
        if (!$userService->registerByPushData($validateModel)) {
            return $this->return
                ->returnFailed(ErrorCode::ERROR_COMMON(), $userService->getError());
        } else {
            return $this->return->setData([
                'userID' => $userService->getResult()['user_id'] ?? 0
            ])->returnOK();
        }

        //同步设备信息
//        $userId = $userService->getResult()['user_id'];
//        if ($userService->deviceInfoByPushData($validateModel, $userId)) {
//            return $this->return->setData([])->returnOK();
//        } else {
//            return $this->return
//                ->returnFailed(ErrorCode::ERROR_COMMON(), $userService->getError());
//        }
    }

    /**
     * razorpay 打款还款回调
     * @return array
     */
    public function actionOrderNotify(){
        $params = Yii::$app->request->post();
        $pay_account_id = Yii::$app->request->get('account_id');
        $payoutID = intval(yii::$app->request->get('payout_id'));

        if(!isset($params['event']))
        {
            yii::error([
                'params' => $params,
                'pay_account_id' => $pay_account_id,
                'payout_id' => $payoutID
            ], 'actionOrderNotify');
        }


        switch ($params['event']){
            case 'order.paid':
                return $this->actionOrderRepaymentNotify($pay_account_id);
                break;
            case 'payout.processed':
            case 'payout.reversed':
                return $this->actionOrderPayNotify($pay_account_id, $payoutID);
                break;
            case 'payment.authorized':
                return ['message' => 'success'];
//                return $this->actionPaymentAuthorized($pay_account_id);
                break;
            case 'virtual_account.credited':
                return $this->actionVirtualAccountRepayment($pay_account_id);
            default:
                break;
        }
        Yii::$app->getResponse()->setStatusCode(400);
        return ['message' => 'fail'];
    }


    /**
     * razorpay 虚拟账号还款回调
     * @param $source_id
     * @return array
     */
    private function actionVirtualAccountRepayment($payAccountId)
    {
        $params = Yii::$app->request->post();
        Yii::info([
            'post' => $params,
            'headers' => Yii::$app->request->headers
        ], 'razorpay_virtual_account');

        try {
            $service = RazorpayService::getInstanceByPayAccountId($payAccountId);
            if(!$service->orderVirtualAccountRepaymentNotify($params)){
                throw new \Exception('处理失败');
            }
            return ['message' => 'success'];
        } catch (\Exception $e){
            $service = new WeWorkService();
            $message = sprintf("%s:Razorpay Virtual Account: \n %s %s",YII_ENV,json_encode($params), $e->getMessage());
            $service->send($message);
            Yii::$app->getResponse()->setStatusCode(400);
            return ['message' => 'fail'];
        }
    }



    /**
     * razorpay 授权回调
     * @return array
     */
    private function actionPaymentAuthorized($payAccountId){
        $params = Yii::$app->request->post();
        Yii::info($params, 'authorized');

        try {
            if(empty($params['payload']['payment']['entity']['order_id']))
            {
                return ['message' => 'success'];
            }
            $sign = Yii::$app->request->headers->get('x-razorpay-signature');
            $service = RazorpayService::getInstanceByPayAccountId($payAccountId);
            $service->verifyWebhookSignature($sign, $params);
            if(!$service->orderPaymentAuthorized($params['payload']['payment']['entity']['order_id'])){
                throw new \Exception();
            }
            return ['message' => 'success'];
        } catch (\Exception $e){
            $service = new WeWorkService();
            $message = sprintf("%s:PaymentAuthorized: \n %s",YII_ENV,json_encode($params));
            $service->send($message);
            Yii::$app->getResponse()->setStatusCode(400);
            return ['message' => 'fail'];
        }
    }

    /**
     * razorpay 打款回调
     * @return array
     */
    private function actionOrderPayNotify($payAccountId, $payoutID){
        $params = Yii::$app->request->post();


        $testID = $params['payload']['payout']['entity']['notes']['id'] ?? 0;
        if($testID)
        {
            yii::info([$testID, $payoutID], 'razorpay_payment_notify_test_id');
        }

        $sign = Yii::$app->request->headers->get('x-razorpay-signature');

        if(empty($payoutID))
        {
            $service = RazorpayService::getInstanceByPayAccountId($payAccountId);
        }else{
            $service = RazorpayPayoutService::getInstanceByPayAccountId($payoutID);
        }
        $service->verifyWebhookSignature($sign, $params);
        try {
            if($service->payoutNotify($params))
            {
                return ['message' => 'success'];
            }else{
                Yii::$app->getResponse()->setStatusCode(400);
                return ['message' => 'fail'];
            }
        } catch (\Exception $e) {
            \Yii::error(sprintf("%s:OrderPayNotify:\n %s", YII_ENV,print_r($params,true)),__METHOD__);
            Yii::$app->getResponse()->setStatusCode(400);
            return ['message' => 'fail'];
        }
    }

    /**
     * razorpay 还款回调
     * @return array
     */
    private function actionOrderRepaymentNotify($payAccountId){
        $params = Yii::$app->request->post();

        $testID = $params['payload']['order']['entity']['notes']['id'] ?? 0;
        try {
            if($params['payload']['order']['entity']['status'] == 'paid'){
                $sign = Yii::$app->request->headers->get('x-razorpay-signature');
                $service = RazorpayService::getInstanceByPayAccountId($payAccountId);
                $service->verifyWebhookSignature($sign, $params);
                if(!$service->orderRepaymentNotify($params['payload']['order']['entity']['id'], $params['payload']['payment']['entity']['id'])){
                    throw new \Exception("{$payAccountId} 回调失败");
                }
            }
            return ['message' => 'success'];

        } catch (\Exception $e) {
            $service = new WeWorkService();
            $message = sprintf("%s:%s:OrderRepaymentNotify: \n %s \n %s",YII_ENV, $payAccountId, $e->getMessage(), json_encode($params));
            $service->send($message);
            Yii::$app->getResponse()->setStatusCode(400);
            return ['message' => 'fail'];
        }
    }

    /**
     * mpurse 打款回调
     * @return string
     */
    public function actionMpursePayout(){
        $payAccountId = Yii::$app->request->get('id');
        $params = Yii::$app->request->getRawBody();

        $service = MpursePayoutService::getInstanceByPayAccountId($payAccountId);
        try {
            if($service->payoutNotify($params))
            {
                echo 'SUCCESS';die;
            }else{
                echo 'FAIL';die;
            }
        } catch (\Exception $e) {
            Yii::error(sprintf("%s:MpursePayout:\n %s", YII_ENV,print_r($params,true)),__METHOD__);
            echo 'FAIL';die;
        }
    }

    /**
     * jolosoft打款回调
     * @return string[]
     */
    public function actionJolosoftPayout(){
        $payAccountId = Yii::$app->request->get('id');
        $params = Yii::$app->request->post();
        Yii::info([
            'params' => $params,
        ], 'JolosoftPayout');

        $service = JolosoftPayoutService::getInstanceByPayAccountId($payAccountId);
        try {
            if($service->payoutNotify($params)){
                return ['message' => 'success'];
            }else{
                Yii::$app->getResponse()->setStatusCode(400);
                return ['message' => 'fail'];
            }
        } catch (\Exception $e) {
            Yii::error(sprintf("%s:JolosoftPayout:\n %s", YII_ENV,print_r($params,true)),__METHOD__);
            Yii::$app->getResponse()->setStatusCode(400);
            return ['message' => 'fail'];
        }
    }

    /**
     * qiming 打款回调
     * @return string
     */
    public function actionQimingPayout(){
        $payAccountId = Yii::$app->request->get('id');
        $params = Yii::$app->request->post();
        $sign = Yii::$app->request->headers->get('sign');

        Yii::info(['params' => $params, 'sign' => $sign], 'QimingPayout');

        $service = QimingPayoutService::getInstanceByPayAccountId($payAccountId);
        try {
            if(!$service->checkSign($params, $sign)){
                echo "fail";die;
            }
            if(!$service->payoutNotify($params)){
                throw new \Exception();
            }
            echo "success";die;
        } catch (\Exception $e) {
            Yii::error(sprintf("%s:QimingPayout:\n %s", YII_ENV,print_r($params,true)),__METHOD__);
            echo "fail";die;
        }
    }

    /**
     * mpurse 还款回调
     * @return string
     */
    public function actionMpurseRepayment(){
        $params = Yii::$app->request->getRawBody();
        $payAccountId = Yii::$app->request->get('account_id');

        $service = MpurseService::getInstanceByPayAccountId($payAccountId);
        try {
            if(!$service->orderRepaymentNotify($params)){
                throw new \Exception();
            }
            echo "SUCCESS";die;
        } catch (\Exception $e) {
            Yii::error(sprintf("%s:MpurseRepayment:\n %s", YII_ENV,print_r($params,true)),__METHOD__);
            echo "FAIL";die;
        }
    }

    /**
     * sifang 还款回调
     * @return string
     */
    public function actionSifangRepayment(){
        $params = Yii::$app->request->post();
        $payAccountId = Yii::$app->request->get('account_id');

        Yii::info($params, 'SifangRepayment');

        $service = SifangService::getInstanceByPayAccountId($payAccountId);
        try {
            if(!$service->orderRepaymentNotify($params)){
                throw new \Exception();
            }
            echo "OK";die;
        } catch (\Exception $e) {
            Yii::error(sprintf("%s:SifangRepayment:\n %s", YII_ENV,print_r($params,true)),__METHOD__);
            echo "FAIL";die;
        }
    }

    /**
     * qiming 还款回调
     * @return string
     */
    public function actionQimingRepayment(){
        $params = Yii::$app->request->post();
        $sign = Yii::$app->request->headers->get('sign');
        $payAccountId = Yii::$app->request->get('account_id');

        Yii::info(['params' => $params, 'sign' => $sign], 'QimingRepayment');

        $service = QimingService::getInstanceByPayAccountId($payAccountId);
        try {
            if(!$service->checkSign($params, $sign)){
                echo "fail";die;
            }
            if(!$service->orderRepaymentNotify($params)){
                throw new \Exception();
            }
            echo "success";die;
        } catch (\Exception $e) {
            Yii::error(sprintf("%s:QimingRepayment:\n %s", YII_ENV,print_r($params,true)),__METHOD__);
            echo "fail";die;
        }
    }

    /**
     * quanqiupay 还款回调
     * @return string
     */
    public function actionQuanqiupayRepayment(){
        $params = Yii::$app->request->post();
        $payAccountId = Yii::$app->request->get('account_id');

        Yii::info($params, 'QuanqiupayRepayment');

        $service = QuanqiupayService::getInstanceByPayAccountId($payAccountId);
        try {
            if(!$service->orderRepaymentNotify($params)){
                throw new \Exception();
            }
            echo "success";die;
        } catch (\Exception $e) {
            Yii::error(sprintf("%s:QuanqiupayRepayment:\n %s", YII_ENV,print_r($params,true)),__METHOD__);
            echo "fail";die;
        }
    }

    /**
     * rpay 还款回调
     * @return string
     */
    public function actionRpayRepayment(){
        $params = Yii::$app->request->post();
        $payAccountId = Yii::$app->request->get('account_id');

        Yii::info($params, 'RpayRepayment');

        $service = RpayService::getInstanceByPayAccountId($payAccountId);
        try {
            if(!$service->orderRepaymentNotify($params)){
                throw new \Exception();
            }
            echo "success";die;
        } catch (\Exception $e) {
            Yii::error(sprintf("%s:RpayRepayment:\n %s", YII_ENV,print_r($params,true)),__METHOD__);
            Yii::$app->getResponse()->setStatusCode(400);
            echo "fail";die;
        }
    }

    /**
     * mojo 还款回调
     * @return string
     */
    public function actionMojoRepayment(){
        $params = Yii::$app->request->post();
        $payAccountId = Yii::$app->request->get('account_id');

        Yii::info($params, 'MojoRepayment');

        $service = MojoService::getInstanceByPayAccountId($payAccountId);
        try {
            if(!$service->orderRepaymentNotify($params)){
                throw new \Exception();
            }
            echo "OK";die;
        } catch (\Exception $e) {
            Yii::error(sprintf("%s:MojoRepayment:\n %s", YII_ENV,print_r($params,true)),__METHOD__);
            echo "FAIL";die;
        }
    }

    /**
     * jpay 还款回调
     * @return string
     */
    public function actionJpayRepayment(){
        $params = Yii::$app->request->post();
        $payAccountId = Yii::$app->request->get('account_id');

        Yii::info($params, 'JpayRepayment');

        $service = JpayService::getInstanceByPayAccountId($payAccountId);
        try {
            if(!$service->orderRepaymentNotify($params)){
                throw new \Exception();
            }
            echo "success";die;
        } catch (\Exception $e) {
            Yii::error(sprintf("%s:JpayRepayment:\n %s", YII_ENV,print_r($params,true)),__METHOD__);
            echo "failed";die;
        }
    }


    /**
     * cashfree还款回调
     * @return array
     */
    public function actionCashFreeNotify()
    {
        $id = yii::$app->request->get('id');
        $postData = Yii::$app->request->post();
        yii::info($postData, 'cash_free_payment_notify');
        $service = CashFreePaymentService::getInstanceByPayAccountId($id);
        if($service->paymentNotify($postData))
        {
            return ['message' => 'success'];
        }else{
            Yii::$app->getResponse()->setStatusCode(400);
            return ['message' => 'Signature error'];
        }
    }

    /**
     * cashfree还款回跳地址
     * @return string
     */
    public function actionCashFreeReturn()
    {
        yii::$app->response->format = Response::FORMAT_HTML;
        $id = yii::$app->request->get('id');
        $postData = Yii::$app->request->post();
        yii::info($postData, 'cash_free_payment_return');
        $service = CashFreePaymentService::getInstanceByPayAccountId($id);
        $service->paymentNotify($postData);
        return $this->render('cash-free-return');

    }

    /**
     * 牛信语音群呼回调
     */
    public function actionNxVoiceGroup()
    {
        $params = Yii::$app->request->get();
        $phone = $params['phone'];
        $duration = $params['duration'];
//        $voiceType = $params['voice_type'];
        $messageid = $params['messageid'];

        $phone = substr($phone,-10);

        //催收账龄转变发语音结果更新
        /** @var LevelChangeDailyCall $levelChangeDailyCall */
        $levelChangeDailyCall = LevelChangeDailyCall::find()
            ->where(['send_id' => $messageid,'user_phone' => $phone,'send_status' => LevelChangeDailyCall::SEND_STATUS_SENDING])->one();
        if ($levelChangeDailyCall){
            if($duration > 0){
                $levelChangeDailyCall->send_status = LevelChangeDailyCall::SEND_STATUS_SUCCESS;
                $levelChangeDailyCall->remark = '回调成功接通';
                /** @var UserActiveTime $userActiveTime */
                $userActiveTime = UserActiveTime::find()->where(['user_id' => $levelChangeDailyCall->user_id])->one();
                if($userActiveTime){
                    $userActiveTime->level_change_call_success_time = time();
                    $userActiveTime->save();
                }
            }else{
                $levelChangeDailyCall->send_status = LevelChangeDailyCall::SEND_STATUS_FAIL;
                $levelChangeDailyCall->remark = '回调接通失败';
                /** @var UserActiveTime $userActiveTime */
                $userActiveTime = UserActiveTime::find()->where(['user_id' => $levelChangeDailyCall->user_id])->one();
                if($userActiveTime){
                    $userActiveTime->level_change_call_success_time = 0;
                    $userActiveTime->save();
                }
            }
            $levelChangeDailyCall->save();
            return 'success';
        }else{
            return 'error';
        }
    }

    /**
     * 牛信坐席回调
     */
    public function actionNxPhone()
    {
        $params = Yii::$app->request->post();
        /**
         * @var NxPhoneLog $log
         */
        $log = NxPhoneLog::find()
            ->where([
                'nx_orderid' => $params['orderid'],
                'status' => NxPhoneLog::STATUS_NO
            ])
            ->one();

        if (!$log) {
            return $this->return->returnFailed(ErrorCode::ERROR_COMMON(), 'Log does not exist!');
        }

        try {
            if($log->call_type == NxPhoneLog::CALL_COLLECTION)
            {
                //新增催收员当天拨打数据
                /** @var CollectorCallData $collectorCallData */
                $collectorCallData = CollectorCallData::find()->where([
                    'date' => date('Y-m-d'),
                    'user_id' => $log->collector_id,
                    'phone' => $log->phone,
                    'type' => $log->type,
                    'phone_type' => $log->phone_type
                ])->one();

                if (!$collectorCallData) {
                    $collectorCallData = new CollectorCallData();
                    $collectorCallData->date = date('Y-m-d');
                    $collectorCallData->user_id = $log->collector_id;
                    $collectorCallData->phone = $log->phone;
                    $collectorCallData->type = $log->type;
                    $collectorCallData->phone_type = $log->phone_type;
                }
                if($params['duration'] > 0)
                {
                    $collectorCallData->is_valid = 1;
                }
                $collectorCallData->times += 1;
                $collectorCallData->duration += $params['duration'];
                $collectorCallData->save();
            }elseif($log->call_type == NxPhoneLog::CALL_CUSTOMER)
            {
                //新增提醒员当天拨打数据
                /** @var ReminderCallData $reminderCallData */
                $reminderCallData = ReminderCallData::find()->where([
                    'date' => date('Y-m-d'),
                    'user_id' => $log->collector_id,
                    'phone' => $log->phone,
                    'type' => $log->type,
                    'phone_type' => $log->phone_type
                ])->one();

                if (!$reminderCallData) {
                    $reminderCallData = new ReminderCallData();
                    $reminderCallData->date = date('Y-m-d');
                    $reminderCallData->user_id = $log->collector_id;
                    $reminderCallData->phone = $log->phone;
                    $reminderCallData->type = $log->type;
                    $reminderCallData->phone_type = $log->phone_type;
                }
                if($params['duration'] > 0)
                {
                    $reminderCallData->is_valid = 1;
                }
                $reminderCallData->times += 1;
                $reminderCallData->duration += $params['duration'];
                $reminderCallData->save();
            }

            $log->duration = $params['duration'];
            $log->direction = $params['direction'];
            $log->record_url = $params['record_url'];
            $log->start_time = $params['start_time'];
            $log->answer_time = $params['answer_time'];
            $log->end_time = $params['end_time'];
            $log->hangup_cause = $params['hangup_cause'];
            $log->status = NxPhoneLog::STATUS_YES;
            $log->save();

            return $this->return->setData([])->returnOK();
        } catch (\Exception $exception) {
            return $this->return->returnFailed(ErrorCode::ERROR_COMMON(), $exception->getMessage());
        }
    }


}
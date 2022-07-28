<?php
namespace console\controllers;

use backend\models\Merchant;
use callcenter\models\loan_collection\LoanCollectionOrder;
use common\helpers\RedisQueue;
use common\helpers\System;
use common\models\ClientInfoLog;
use common\models\product\ProductSetting;
use common\services\message\FirebasePushService;
use Yii;
use common\helpers\MessageHelper;
use common\models\user\UserBankAccount;
use common\models\user\LoanPerson;
use common\models\order\UserLoanOrder;
use common\models\order\UserLoanOrderRepayment;
use common\models\message\MessageTimeTask;
use common\models\message\NoticeSms;

/*
 * 短信&语音发送任务
 * created_at 2018-04-26
 */
class MessageTimeTaskController extends BaseController {
    // 00:00 还款提醒
    public function actionSendRepaymentTaskQ($is_export)
    {
        $this->_doTask($is_export,MessageTimeTask::TIME_Q); // 00:00任务时间类型
    }
    // 00:30 还款提醒
    public function actionSendRepaymentTaskQQ($is_export)
    {
        $this->_doTask($is_export,MessageTimeTask::TIME_QQ); // 00:30任务时间类型
    }
    // 01:00 还款提醒
    public function actionSendRepaymentTaskR($is_export)
    {
        $this->_doTask($is_export,MessageTimeTask::TIME_R);
    }
    // 01:30 还款提醒
    public function actionSendRepaymentTaskRR($is_export)
    {
        $this->_doTask($is_export,MessageTimeTask::TIME_RR);
    }
    // 02:00 还款提醒
    public function actionSendRepaymentTaskS($is_export)
    {
        $this->_doTask($is_export,MessageTimeTask::TIME_S);
    }
    // 02:30 还款提醒
    public function actionSendRepaymentTaskSS($is_export)
    {
        $this->_doTask($is_export,MessageTimeTask::TIME_SS);
    }
    // 03:00 还款提醒
    public function actionSendRepaymentTaskT($is_export)
    {
        $this->_doTask($is_export,MessageTimeTask::TIME_T);
    }
    // 03:30 还款提醒
    public function actionSendRepaymentTaskTT($is_export)
    {
        $this->_doTask($is_export,MessageTimeTask::TIME_TT);
    }
    // 04:00 还款提醒
    public function actionSendRepaymentTaskU($is_export)
    {
        $this->_doTask($is_export,MessageTimeTask::TIME_U);
    }
    // 04:30 还款提醒
    public function actionSendRepaymentTaskUU($is_export)
    {
        $this->_doTask($is_export,MessageTimeTask::TIME_UU);
    }
    // 05:00 还款提醒
    public function actionSendRepaymentTaskV($is_export)
    {
        $this->_doTask($is_export,MessageTimeTask::TIME_V);
    }
    // 05:30 还款提醒
    public function actionSendRepaymentTaskVV($is_export)
    {
        $this->_doTask($is_export,MessageTimeTask::TIME_VV);
    }
    // 06:00 还款提醒
    public function actionSendRepaymentTaskW($is_export)
    {
        $this->_doTask($is_export,MessageTimeTask::TIME_W);
    }
    // 06:30 还款提醒
    public function actionSendRepaymentTaskWW($is_export)
    {
        $this->_doTask($is_export,MessageTimeTask::TIME_WW);
    }
    // 07:00 还款提醒
    public function actionSendRepaymentTaskX($is_export)
    {
        $this->_doTask($is_export,MessageTimeTask::TIME_X);
    }
    // 07:30 还款提醒
    public function actionSendRepaymentTaskXX($is_export)
    {
        $this->_doTask($is_export,MessageTimeTask::TIME_XX);
    }


    // 8:00 还款提醒
    public function actionSendRepaymentTaskA($is_export)
    {
        $this->_doTask($is_export,MessageTimeTask::TIME_A); // 8:00任务时间类型
    }
    // 8:30 还款提醒
    public function actionSendRepaymentTaskAA($is_export)
    {
        $this->_doTask($is_export,MessageTimeTask::TIME_AA);
    }
    // 9:00 还款提醒
    public function actionSendRepaymentTaskB($is_export)
    {
        $this->_doTask($is_export,MessageTimeTask::TIME_B); // 9:00任务时间类型
    }
    // 9:30 还款提醒
    public function actionSendRepaymentTaskBB($is_export)
    {
        $this->_doTask($is_export,MessageTimeTask::TIME_BB);
    }
    // 10:00 还款提醒
    public function actionSendRepaymentTaskC($is_export)
    {
        $this->_doTask($is_export,MessageTimeTask::TIME_C); // 10:00任务时间类型
    }
    // 10:30 还款提醒
    public function actionSendRepaymentTaskCC($is_export)
    {
        $this->_doTask($is_export,MessageTimeTask::TIME_CC);
    }
    // 11:00 还款提醒
    public function actionSendRepaymentTaskD($is_export)
    {
        $this->_doTask($is_export,MessageTimeTask::TIME_D); // 11:00任务时间类型
    }
    // 11:30 还款提醒
    public function actionSendRepaymentTaskDD($is_export)
    {
        $this->_doTask($is_export,MessageTimeTask::TIME_DD);
    }
    // 12:00 还款提醒
    public function actionSendRepaymentTaskE($is_export)
    {
        $this->_doTask($is_export,MessageTimeTask::TIME_E); // 12:00任务时间类型
    }
    // 12:30 还款提醒
    public function actionSendRepaymentTaskEE($is_export)
    {
        $this->_doTask($is_export,MessageTimeTask::TIME_EE);
    }
    // 13:00 还款提醒
    public function actionSendRepaymentTaskF($is_export)
    {
        $this->_doTask($is_export,MessageTimeTask::TIME_F); // 13:00任务时间类型
    }
    // 13:30 还款提醒
    public function actionSendRepaymentTaskFF($is_export)
    {
        $this->_doTask($is_export,MessageTimeTask::TIME_FF);
    }
    // 14:00 还款提醒
    public function actionSendRepaymentTaskG($is_export)
    {
        $this->_doTask($is_export,MessageTimeTask::TIME_G); // 14:00任务时间类型
    }
    // 14:30 还款提醒
    public function actionSendRepaymentTaskGG($is_export)
    {
        $this->_doTask($is_export,MessageTimeTask::TIME_GG);
    }
    // 15:00 还款提醒
    public function actionSendRepaymentTaskH($is_export)
    {
        $this->_doTask($is_export,MessageTimeTask::TIME_H); // 15:00任务时间类型
    }
    // 15:30 还款提醒
    public function actionSendRepaymentTaskHH($is_export)
    {
        $this->_doTask($is_export,MessageTimeTask::TIME_HH);
    }
    // 16:00 还款提醒
    public function actionSendRepaymentTaskI($is_export)
    {
        $this->_doTask($is_export,MessageTimeTask::TIME_I); // 16:00任务时间类型
    }
    // 16:30 还款提醒
    public function actionSendRepaymentTaskII($is_export)
    {
        $this->_doTask($is_export,MessageTimeTask::TIME_II);
    }
    // 17:00 还款提醒
    public function actionSendRepaymentTaskJ($is_export)
    {
        $this->_doTask($is_export,MessageTimeTask::TIME_J); // 17:00任务时间类型
    }
    // 17:30 还款提醒
    public function actionSendRepaymentTaskJJ($is_export)
    {
        $this->_doTask($is_export,MessageTimeTask::TIME_JJ);
    }
    // 18:00 还款提醒
    public function actionSendRepaymentTaskK($is_export)
    {
        $this->_doTask($is_export,MessageTimeTask::TIME_K); // 18:00任务时间类型
    }
    // 18:30 还款提醒
    public function actionSendRepaymentTaskKK($is_export)
    {
        $this->_doTask($is_export,MessageTimeTask::TIME_KK);
    }
    // 19:00 还款提醒
    public function actionSendRepaymentTaskL($is_export)
    {
        $this->_doTask($is_export,MessageTimeTask::TIME_L); // 19:00任务时间类型
    }
    // 19:30 还款提醒
    public function actionSendRepaymentTaskLL($is_export)
    {
        $this->_doTask($is_export,MessageTimeTask::TIME_LL);
    }
    // 20:00 还款提醒
    public function actionSendRepaymentTaskM($is_export)
    {
        $this->_doTask($is_export,MessageTimeTask::TIME_M); // 20:00任务时间类型
    }
    // 20:30 还款提醒
    public function actionSendRepaymentTaskMM($is_export)
    {
        $this->_doTask($is_export,MessageTimeTask::TIME_MM);
    }
    // 21:00 还款提醒
    public function actionSendRepaymentTaskN($is_export)
    {
        $this->_doTask($is_export,MessageTimeTask::TIME_N); // 21:00任务时间类型
    }
    // 21:30 还款提醒
    public function actionSendRepaymentTaskNN($is_export)
    {
        $this->_doTask($is_export,MessageTimeTask::TIME_NN);
    }
    // 22:00 还款提醒
    public function actionSendRepaymentTaskO($is_export)
    {
        $this->_doTask($is_export,MessageTimeTask::TIME_O); // 22:00任务时间类型
    }
    // 22:30 还款提醒
    public function actionSendRepaymentTaskOO($is_export)
    {
        $this->_doTask($is_export,MessageTimeTask::TIME_OO);
    }
    // 23:00 还款提醒
    public function actionSendRepaymentTaskP($is_export)
    {
        $this->_doTask($is_export,MessageTimeTask::TIME_P); // 22:00任务时间类型
    }
    // 23:30 还款提醒
    public function actionSendRepaymentTaskPP($is_export)
    {
        $this->_doTask($is_export,MessageTimeTask::TIME_PP);
    }
    // 测试 还款提醒
    public function actionSendRepaymentTaskTest($is_export,$task_time)
    {
        $this->_doTask($is_export,$task_time,true); // 8:00任务时间类型
    }

    // 私有方法 - 查询任务信息
    private function _doTask($is_export,$the_time,$is_test = false)
    {
        $the_time = intval($the_time); // 任务定时时间点
        $task_status = MessageTimeTask::STATUS_ON; // 任务开启状态
        $task_info = MessageTimeTask::find()->where([
            'task_status' => $task_status,
            'is_export' => $is_export,
            'task_time' => $the_time,
        ])->all();
        if(!$task_info){
            $this->printMessage("今日".MessageTimeTask::$task_time_map[$the_time]."任务未开启/未创建。");
            return;
        }

        // 处理任务借款类型
        foreach($task_info as $task){
            $this->_doNomalTask($task['id'],$is_test);
        }
    }

    // 私有方法 - 普通借款（短信&语音）
    private function _doNomalTask($task_id,$is_test = false)
    {
        /* 查询并处理MSG任务信息
        -------------------------------------------------------------- */

        /** @var MessageTimeTask $task_info */
        $task_info = MessageTimeTask::find()->where([
            'id' => $task_id,
            'task_status' => MessageTimeTask::STATUS_ON
        ])->limit(1)->one();
        if(is_null($task_info)){
            $this->printMessage("任务".$task_id."查询有误。");
            return;
        }

        $tips_type = $task_info->tips_type; // 提醒类型  1-当日 2-提前 3-逾期 4-待提现
        $days_type = $task_info->days_type; // 天数 - 用于提前或逾期
        $is_app_notice = $task_info->is_app_notice; // 是否置为app消息
        $is_export = $task_info->is_export; // 是否内部订单 0-内部 1-外部
        $merchant_id = $task_info->merchant_id; // 商户id
        $config = json_decode($task_info->config,true); // App包对应通道对应文案模板等配置
        if(!$config){
            $this->printMessage("任务".$task_id."的congif配置有误。");
            return;
        }
        $no_config_app = []; // 未配置发送通道信息app
        //参数校验
        foreach($config as $k => $v)
        {
            if(
                empty($v['pack_name']) ||
                empty($v['aisle_type']) ||
                empty($v['content'])  ||
                MessageTimeTask::smsService_None == $v['aisle_type']
            )
            {
                $no_config_app[] = $k;
            }
        }

        $send_log = $task_info->send_log ? json_decode($task_info->send_log,true) : []; // 发送记录

        /* 拼接查询条件并查询借款信息
        -------------------------------------------------------------- */
        $today = time();
        $where = '';
        switch ($tips_type) {
            case MessageTimeTask::TIPS_TODAY:
                // 当日 - 拼接查询条件
                $plan_start_time = strtotime(date('Y-m-d',$today)); // 今日0点
                $plan_end_time = strtotime(date('Y-m-d',$today)) + 24*3600; // 次日0点
                $where = " AND r.plan_repayment_time >= ".$plan_start_time." AND r.plan_repayment_time < ".$plan_end_time;
                break;
            case MessageTimeTask::TIPS_FORWARD:
                // 提前 - 拼接查询条件
                if($days_type > 0){
                    $plan_start_time = strtotime(date('Y-m-d',$today)) + 24*3600*$days_type; // 提前天数0点
                    $plan_end_time = strtotime(date('Y-m-d',$today)) + 24*3600*($days_type + 1); // 提前天数次日0点
                    $where = " AND r.plan_repayment_time >= ".$plan_start_time." AND r.plan_repayment_time < ".$plan_end_time;
                }
                break;
            case MessageTimeTask::TIPS_OVERDUE:
                /**
                 * 逾期 - 拼接查询条件
                 * 如果参数中带有-符号，说明是范围搜索
                 * 如果参数中带有,符号，说明是多选
                 */
                if(0 < strpos($days_type, '-'))
                {
                    $daysTypeArray = explode('-' , $days_type);
                    if(count($daysTypeArray) == 2)
                    {
                        $where = " AND  r.overdue_day >= {$daysTypeArray[0]} AND r.overdue_day <= {$daysTypeArray[1]}"; // 逾期天数
                    }
                }elseif (0 < strpos($days_type, ','))
                {
                    $where = " AND r.overdue_day in ({$days_type})";
                }
                else{
                    if($days_type > 0){
                        $where = ' AND  r.overdue_day = '.$days_type; // 逾期天数
                    }
                }
                break;
            case MessageTimeTask::TIPS_DRAW_MONEY:
                // 待提现 - 拼接查询条件
                $draw_money_time = time() - 3600*$days_type; // 待提现时间小时为单位
                $where = " AND o.order_time >= ".$draw_money_time. " AND o.auto_draw = ".'"n"';

                break;

            case MessageTimeTask::TIPS_DRAW_MONEY_AUTO:
                // 待提现自动提现 - 拼接查询条件
                $draw_money_time = time() - 3600*$days_type; // 待提现时间小时为单位
                $where = " AND o.order_time >= ".$draw_money_time. " AND o.auto_draw = ".'"y"';

                break;
            default:
                break;
        }

        if(!$where){
            $this->printMessage("任务".$task_id."提醒类型数据存在问题，请检测。");
            return;
        }

        switch ($task_info->user_type) {
            case MessageTimeTask::USER_TYPE_NEW:
                $where .= " AND o.is_first = " . UserLoanOrder::FIRST_LOAN_IS;
                break;
            case MessageTimeTask::USER_TYPE_OLD:
                $where .= " AND o.is_first = " . UserLoanOrder::FIRST_LOAN_NO;
                break;
            default:
                break;
        }
        if(!$where){
            $this->printMessage("任务".$task_id."用户类型数据存在问题，请检测。");
            return;
        }

        //查询待提现数据
        if( MessageTimeTask::TIPS_DRAW_MONEY == $tips_type  || MessageTimeTask::TIPS_DRAW_MONEY_AUTO == $tips_type)
        {
            $maxId = 0;
            $limit = 1000;
            $query = UserLoanOrder::find()
                ->from(UserLoanOrder::tableName().' as o')
                ->andWhere('o.status ='.UserLoanOrder::STATUS_WAIT_DRAW_MONEY . $where)
                ->andWhere( ['o.is_export' =>$is_export])
                ->select([
                    'o.id','o.user_id'
                ]);
            $user_loan_order = $query->andWhere(['>','o.id',$maxId])->limit($limit)->asArray()->all();
            if(!$user_loan_order){
                $this->printMessage("任务".$task_id."待提现数据为空。");
                return;
            }

            /* 处理待提现款数据并发送消息
            -------------------------------------------------------------- */
            $send_total_num = 0;
            $send_true_num = 0;
            while($user_loan_order){
                $send_total_num += count($user_loan_order);

                // 获取所有用户user_id
                $users_info = [];
                $user_ids = [];
                foreach($user_loan_order as $loan_order){
                    $maxId = $loan_order['id'];
                    $user_ids[] = $loan_order['user_id'];
                    $users_info[$loan_order['user_id']]['user_id'] = $loan_order['user_id']; // 用户id
                }
                // 获取用户信息
                $get_users = LoanPerson::find()->where([
                    'id' => $user_ids
                ])->select([
                    'id','name','phone'
                ])->asArray()->all();
                foreach($get_users as $user){
                    $users_info[$user['id']]['username'] = $user['name']; // 用户姓名
                    $users_info[$user['id']]['phone'] = $user['phone']; // 用户手机号
                }

                $batchSend = [];
                // 进入数据
                foreach($user_loan_order as $loan_order){
                    // 获取配置通道/文案等信息
                    $order = UserLoanOrder::findOne($loan_order['id']);
                    /** @var ClientInfoLog $clientInfo */
                    $clientInfo = $order->clientInfoLog;
                    if(is_null($clientInfo) || empty($clientInfo->package_name)){
                        $packageName = 'icredit';
                    }else{
                        $packageName =  $clientInfo->package_name;
                    }

                    $pushService = new FirebasePushService($packageName);
                    $packageService = $pushService->getPackageService();
                    $appName = $packageService->getName();
                    if(in_array("apps_{$packageName}", $no_config_app)){
                        $this->printMessage("任务".$task_id."未配置apps_".$packageName."发送通道信息，不进行发送。");
                        continue;
                    }
                    if(!isset($config['apps_'.$packageName]) || !isset($config['apps_'.$packageName]['aisle_type']))
                    {
                        continue;
                    }
                    $aisle_type = $config['apps_'.$packageName]['aisle_type']; // 通道标识
                    $content = trim($config['apps_'.$packageName]['content']); // 文案内容
                    $is_batch = isset($config['apps_'.$packageName]['batch_send']) ? $config['apps_'.$packageName]['batch_send'] : MessageTimeTask::SEND_SINGLE;//批量标识
                    $sms_channel = $aisle_type; // 通道配置key

                    // 短信任务 - 发出
                    if(
                        !isset($users_info[$loan_order['user_id']]) ||
                        !isset($users_info[$loan_order['user_id']]['username']) ||
                        !isset($users_info[$loan_order['user_id']]['phone'])
                    ){
                        $this->printMessage("用户".$loan_order['user_id']."数据信息有误，请检测。");
                        continue;
                    }
                    if(!$content){
                        $this->printMessage("任务".$task_id."的文案为空。");
                        continue;
                    }

                    $phone = $users_info[$loan_order['user_id']]['phone']; // 手机号
                    if($is_batch == MessageTimeTask::SEND_BATCH){
                        if($order->is_export == UserLoanOrder::IS_EXPORT_YES && $is_export == 1){
                            $exportPackageName = explode('_',$clientInfo->app_market)[1] ?? '';
                            $showProductName = $this->getLoanExportProductName($packageName,$exportPackageName);
                            if($showProductName == 'none'){
                                continue;
                            }
                            $batchSend[$packageName][$exportPackageName][$showProductName][] = $phone;
                        }else{
                            $batchSend[$packageName][] = $phone;
                            //$this->printMessage("任务{$task_id}，导入批量发送,订单号:{$order_id}");
                        }
                        continue;
                    }

                    if($is_export == 1){
                        $exportPackageName = explode('_',$clientInfo->app_market)[1] ?? '';
                        $showProductName = $this->getLoanExportProductName($packageName,$exportPackageName);
                        if($showProductName == 'none'){
                            continue;
                        }
                        $content = str_replace(['export_package_name','show_product_name'],[$exportPackageName,$showProductName],$content); // 处理文案内容信息替换
                    }

                    // 发送
                    try{
                        if($is_test){
                            //test账号
                            $ret = MessageHelper::sendAll($phone, $content, $sms_channel);
                            if($is_app_notice) {
                                $pushService->pushToUser($loan_order['user_id'], $appName, $content);
                            }
                            $this->printMessage('Here Test!');
                        }else{
                            if(YII_ENV_PROD){
                                $ret = MessageHelper::sendAll($phone, $content, $sms_channel);
                                if($is_app_notice) {
                                    $pushService->pushToUser($loan_order['user_id'], $appName, $content);
                                }
                            }else{
                                $ret = 1;
                            }
                        }

                        $this->printMessage("任务".$task_id."，用户id".$loan_order['user_id']."，手机号".$phone."，发送结果：".json_encode($ret));
                        $this->printMessage($content);
                        $send_true_num++;
                    }catch(\Exception $e){
                        $this->printMessage("任务".$task_id."，用户id".$loan_order['user_id']."，手机号".$phone."，发送Exception".$e->getMessage().$e->getFile().$e->getLine());
                    }
                }

                //批量发送
                if($is_export == 1){
                    foreach ($batchSend as $packageName => $exportList) {
                        foreach ($exportList as $exportPackageName => $showProductNameList) {
                            $channel = $config['apps_' . $packageName]['aisle_type']; // 通道标识
                            foreach ($showProductNameList as $showProductName => $phoneList){
                                $content = str_replace(['export_package_name','show_product_name'],[$exportPackageName,$showProductName],trim($config['apps_' . $packageName]['content'])); // 处理文案内容信息替换
                                try {
                                    if(YII_ENV_PROD){
                                        $ret = MessageHelper::sendAll($phoneList, $content, $channel);
                                    }else{
                                        $ret = 1;
                                    }
                                    if($ret){
                                        $send_true_num+=count($phoneList);
                                        $this->printMessage("任务".$task_id."，包名".$packageName.",通道".$channel.",导流包".$exportPackageName.",显示产品".$showProductName.",批量发送,,批次数量".count($phoneList)."，发送结果：".json_encode($ret));
                                    }else{
                                        $this->printMessage("任务".$task_id."，批量发送失败");
                                    }
                                }
                                catch (\Exception $ex) {
                                    $this->printMessage("任务".$task_id."，批量发送Exception".$ex->getMessage().$ex->getFile().$ex->getLine());
                                }
                            }
                        }
                    }
                }else{
                    foreach ($batchSend as $packageName => $phoneList) {
                        $content = trim($config['apps_' . $packageName]['content']); // 文案内容
                        $channel = $config['apps_' . $packageName]['aisle_type']; // 通道标识
                        try {
                            if(YII_ENV_PROD){
                                $ret = MessageHelper::sendAll($phoneList, $content, $channel);
                            }else{
                                $ret = 1;
                            }
                            if($ret){
                                $send_true_num+=count($phoneList);
                                $this->printMessage("任务".$task_id."，包名".$packageName.",通道".$channel.",批量发送,,批次数量".count($phoneList)."，发送结果：".json_encode($ret));
                            }else{
                                $this->printMessage("任务".$task_id."，批量发送失败");
                            }
                        }
                        catch (\Exception $ex) {
                            $this->printMessage("任务".$task_id."，批量发送Exception".$ex->getMessage().$ex->getFile().$ex->getLine());
                        }

                    }
                }
                $user_loan_order = $query->andWhere(['>','o.id',$maxId])->limit($limit)->asArray()->all();
            }
        }
        else{
            // 查询借款数据
            $maxId = 0;
            $limit = 1000;
            $query = UserLoanOrderRepayment::find()
                ->from(UserLoanOrderRepayment::tableName().' as r')
                ->leftJoin(UserLoanOrder::tableName(). ' as o','r.order_id = o.id')
                ->where('r.status ='.UserLoanOrderRepayment::STATUS_NORAML  . $where )
                ->andWhere( ['o.is_export' =>$is_export])
                ->select([
                    'r.id','r.user_id','r.principal','r.overdue_fee','r.overdue_day',
                    'r.interests','r.total_money','r.true_total_money',
                    'r.plan_repayment_time','r.order_id','r.cost_fee'
                ]);
            if(!empty($merchant_id))
            {
                $query->andWhere( ['o.merchant_id' => $merchant_id]);
            }
            $user_loan_order_repayment = $query->andWhere(['>','r.id',$maxId])->limit($limit)->asArray()->all();
            if(!$user_loan_order_repayment){
                $this->printMessage("任务".$task_id."借款数据为空。");
                return;
            }
            /* 处理借款数据并发送消息
        -------------------------------------------------------------- */
            $send_total_num = 0;
            $send_true_num = 0;
            while($user_loan_order_repayment){
                // 获取所有用户user_id以及订单order_id
                $users_info = [];
                $user_ids = [];
                $order_ids = [];
                foreach($user_loan_order_repayment as $repayment){
                    $maxId = $repayment['id'];
                    $user_ids[] = $repayment['user_id'];
                    $order_ids[] = $repayment['order_id'];

                    $users_info[$repayment['user_id']]['user_id'] = $repayment['user_id']; // 用户id
                    $users_info[$repayment['user_id']]['order_id'] = $repayment['order_id']; // 订单id
                }
                // 获取用户信息
                $get_users = LoanPerson::find()->where([
                    'id' => $user_ids
                ])->select([
                    'id','name','phone'
                ])->asArray()->all();
                foreach($get_users as $user){
                    $users_info[$user['id']]['username'] = $user['name']; // 用户姓名
                    $users_info[$user['id']]['phone'] = $user['phone']; // 用户手机号
                }
                /* 逾期短信时 判断停催的订单不发送*/
                if($tips_type == MessageTimeTask::TIPS_OVERDUE){
                    if(!empty($order_ids)){
                        $collectionOrderArr = array_flip($order_ids);
                        $loanCollectionOrder = LoanCollectionOrder::find()
                            ->select(['user_loan_order_id'])
                            ->where([
                                'status' => LoanCollectionOrder::$end_status,
                                'user_loan_order_id' => $order_ids
                            ])->asArray()->all();
                        foreach ($loanCollectionOrder as $value){
                            unset($user_loan_order_repayment[$collectionOrderArr[$value['user_loan_order_id']]]);
                        }
                    }
                }
                $send_total_num += count($user_loan_order_repayment);
                // 获取订单信息
                $card_ids = [];
                $get_orders = UserLoanOrder::find()->where([
                    'id' => $order_ids
                ])->select([
                    'id','user_id','card_id'
                ])->asArray()->all();
                foreach($get_orders as $order){
                    $card_ids[] = $order['card_id'];
                    $users_info[$order['user_id']]['card_id'] = $order['card_id']; // 银行卡id
                }
                // 获取用户银行卡信息
                $get_cards = UserBankAccount::find()->where([
                    'id' => $card_ids
                ])->select([
                    'id','user_id','account'
                ])->asArray()->all();
                foreach($get_cards as $card){
                    $users_info[$card['user_id']]['card_no'] = substr($card['account'],-4); // 银行卡尾号
                }

                $batchSend = [];
                // 进入数据
                foreach($user_loan_order_repayment as $order_repay){
                    // 获取配置通道/文案模板等信息
                    $order_id = $order_repay['order_id'];
                    $order = UserLoanOrder::findOne($order_id);
                    /** @var ClientInfoLog $clientInfo */
                    $clientInfo = $order->clientInfoLog;
                    if(is_null($clientInfo) || empty($clientInfo->package_name)){
                        $packageName = 'icredit';
                    }else{
                        $packageName =  $clientInfo->package_name;
                    }

                    $pushService = new FirebasePushService($packageName);
                    $packageService = $pushService->getPackageService();
                    $appName = $packageService->getName();
                    if(in_array("apps_{$packageName}", $no_config_app)){
                        $this->printMessage("任务".$task_id."未配置apps_".$packageName."发送通道信息，不进行发送。");
                        continue;
                    }

                    if(empty($config['apps_'.$packageName]) || empty($config['apps_'.$packageName]['aisle_type']))
                    {
                        continue;
                    }

                    $aisle_type = $config['apps_'.$packageName]['aisle_type']; // 通道标识
                    $content = trim($config['apps_'.$packageName]['content']); // 文案内容
                    $is_batch = isset($config['apps_'.$packageName]['batch_send']) ? $config['apps_'.$packageName]['batch_send'] : MessageTimeTask::SEND_SINGLE;//批量标识
                    $sms_channel = $aisle_type; // 通道配置key

                    // 短信任务 - 发出
                    if(
                        !isset($users_info[$order_repay['user_id']]) ||
                        !isset($users_info[$order_repay['user_id']]['username']) ||
                        !isset($users_info[$order_repay['user_id']]['phone']) ||
                        !isset($users_info[$order_repay['user_id']]['card_no'])
                    ){
                        $this->printMessage("用户".$order_repay['user_id']."数据信息有误，请检测。");
                        continue;
                    }
                    if(!$content){
                        $this->printMessage("任务".$task_id."的文案为空。");
                        continue;
                    }

                    $phone = $users_info[$order_repay['user_id']]['phone']; // 手机号
                    if($is_batch == MessageTimeTask::SEND_BATCH){
                        if($order->is_export == UserLoanOrder::IS_EXPORT_YES && $is_export == 1){
                            $exportPackageName = explode('_',$clientInfo->app_market)[1] ?? '';
                            $showProductName = $this->getLoanExportProductName($packageName,$exportPackageName);
                            if($showProductName == 'none'){
                                continue;
                            }
                            $batchSend[$packageName][$exportPackageName][$showProductName][] = $phone;
                        }else{
                            $batchSend[$packageName][] = $phone;
                            //$this->printMessage("任务{$task_id}，导入批量发送,订单号:{$order_id}");
                        }
                        continue;
                    }
                    // 金额信息
                    $loan_money = $order_repay['principal']/100; // 本金
                    $overdue_fee = $order_repay['overdue_fee']/100; // 逾期费
                    $repay_money = ($order_repay['total_money'] - $order_repay['true_total_money'])/100; // 需还总额
                    $repayment_date = date('j F, Y',$order_repay['plan_repayment_time']);

                    // 获取发送通道
                    $username = $users_info[$order_repay['user_id']]['username']; // 用户姓名
                    $card_no = $users_info[$order_repay['user_id']]['card_no']; // 用户银行卡尾号
                    $searchArr = ['username','loan_money','overdue_fee','repay_money','card_no','repayment_date'];
                    $replaceArr = [$username,$loan_money,$overdue_fee,$repay_money,$card_no,$repayment_date];
                    if($is_export == 1){
                        $exportPackageName = explode('_',$clientInfo->app_market)[1] ?? '';
                        $searchArr[] = 'export_package_name';
                        $replaceArr[] = $exportPackageName;
                        $showProductName = $this->getLoanExportProductName($packageName,$exportPackageName);
                        if($showProductName == 'none'){
                            continue;
                        }
                        $searchArr[] = 'show_product_name';
                        $replaceArr[] = $showProductName;
                    }
                    $send_message = str_replace($searchArr,$replaceArr,$content); // 处理文案内容信息替换

                    // 发送
                    try{

                        if($is_test){
                            //test账号
                            $ret = '';
                            if($phone == '8929045081'){
                                $ret = MessageHelper::sendAll($phone, $send_message, $sms_channel);
                                if($is_app_notice) {
                                    $pushService->pushToUser($order_repay['user_id'], $appName, $send_message);
                                }
                            }
                            $this->printMessage('Here Test!');
                        }else{
                            if(YII_ENV_PROD){
                                $ret = MessageHelper::sendAll($phone, $send_message, $sms_channel);
                                if($is_app_notice) {
                                    $pushService->pushToUser($order_repay['user_id'], $appName, $send_message);
                                }
                            }else{
                                $ret = 1;
                            }
                        }

                        $this->printMessage("任务".$task_id."，订单".$order_id."，用户".$phone."，发送结果：".json_encode($ret));
                        $this->printMessage($send_message);
                        $send_true_num++;
                    }catch(\Exception $e){
                        $this->printMessage("任务".$task_id."，订单".$order_id."，用户".$phone."，发送Exception".$e->getMessage().$e->getFile().$e->getLine());
                    }
                }

                //批量发送
                if($is_export == 1){
                    foreach ($batchSend as $packageName => $exportList) {
                        foreach ($exportList as $exportPackageName => $showProductNameList) {
                            $channel = $config['apps_' . $packageName]['aisle_type']; // 通道标识
                            foreach ($showProductNameList as $showProductName => $phoneList) {
                                $content = str_replace(['export_package_name','show_product_name'], [$exportPackageName,$showProductName], trim($config['apps_' . $packageName]['content'])); // 处理文案内容信息替换
                                try {
                                    if(YII_ENV_PROD){
                                        $ret = MessageHelper::sendAll($phoneList, $content, $channel);
                                    }else{
                                        $ret = 1;
                                    }
                                    if ($ret) {
                                        $send_true_num += count($phoneList);
                                        $this->printMessage("任务".$task_id."，包名".$packageName.",通道".$channel.",导流包".$exportPackageName.",显示产品".$showProductName.",批量发送,,批次数量".count($phoneList)."，发送结果：".json_encode($ret));
                                    } else {
                                        $this->printMessage("任务" . $task_id . "，批量发送失败");
                                    }
                                } catch (\Exception $ex) {
                                    $this->printMessage("任务" . $task_id . "，批量发送Exception" . $ex->getMessage() . $ex->getFile() . $ex->getLine());
                                }
                            }
                        }
                    }
                }else{
                    foreach ($batchSend as $packageName => $phoneList) {
                        $content = trim($config['apps_' . $packageName]['content']); // 文案内容
                        $channel = $config['apps_' . $packageName]['aisle_type']; // 通道标识
                        try {
                            if(YII_ENV_PROD){
                                $ret = MessageHelper::sendAll($phoneList, $content, $channel);
                            }else{
                                $ret = 1;
                            }
                            if($ret){
                                $send_true_num+=count($phoneList);
                                $this->printMessage("任务".$task_id."，包名".$packageName.",通道".$channel.",批量发送,,批次数量".count($phoneList)."，发送结果：".json_encode($ret));
                            }else{
                                $this->printMessage("任务".$task_id."，批量发送失败");
                            }
                        }
                        catch (\Exception $ex) {
                            $this->printMessage("任务".$task_id."，批量发送Exception".$ex->getMessage().$ex->getFile().$ex->getLine());
                        }

                    }
                }

                $user_loan_order_repayment = $query->andWhere(['>','r.id',$maxId])->limit($limit)->asArray()->all();
            }
        }

        $send_log[] = [
            'start_time' => date('Y-m-d H:i:s',$today),
            'time' => date('Y-m-d H:i:s'),
            'total_num' => $send_total_num,
            'true_num' => $send_true_num
        ];
        $task_info->send_log = json_encode($send_log);
        $save_ret = $task_info->save();
        if(!$save_ret){
            $task_info->save();
        }
    }

    /**
     * @name 获取导流订单在大盘的产品
     * @param $packageName
     * @param $exportPackageName
     * @return mixed|string
     */
    private function getLoanExportProductName($packageName,$exportPackageName){
        $cacheKey = sprintf('%s:%s:%s', RedisQueue::MESSAGE_TIME_TASK_PRODUCT_NAME_CACHE, $packageName, $exportPackageName);
        $productNameCache = RedisQueue::get(['key' => $cacheKey]);
        if($productNameCache){
            $showProductName = $productNameCache;
        }else{
            $showProductName = 'none';
            /** @var ProductSetting $productSetting */
            $productSetting = ProductSetting::find()
                ->select(['product_name'])
                ->where(['package_name' => $packageName, 'is_internal' => ProductSetting::IS_EXTERNAL_YES])
                ->limit(1)
                ->one();
            if($productSetting){
                $loanProductSetting = ProductSetting::find()
                    ->alias('p')
                    ->select(['p.product_name'])
                    ->leftJoin(Merchant::tableName().' m','p.merchant_id = m.id')
                    ->where(['p.package_name' => $exportPackageName,'p.real_product_name' => $productSetting->product_name,'m.merchant_type' => 2])
                    ->limit(1)
                    ->asArray()
                    ->one(Yii::$app->get('db_loan'));
                if($loanProductSetting){
                    $showProductName = $loanProductSetting['product_name'];
                }
            }
            RedisQueue::set(['key' => $cacheKey,'value' => $showProductName, 'expire'=> 86400]);
        }
        return $showProductName;
    }

    /**
     * 输出信息到控制台，并记录log
     * @param string $message
     * @param bool $log 是否记录日志，默认否
     */
    public function message($message, $log = false) {
        if (System::isWindowsOs()) {
            echo sprintf("%s info: %s\n", date('ymd H:i:s '), $message);
        } else {
            echo sprintf("%s(%s) info: %s\n", date('ymd H:i:s '), posix_getpid(), $message);
        }

        if ($log) {
            $trc = debug_backtrace();
            if (isset($trc[0])) {
                $message .= sprintf("\n  ↖ Logged At: %s:%s", $trc[0]['file'], $trc[0]['line']);
            }
        }
    }
}

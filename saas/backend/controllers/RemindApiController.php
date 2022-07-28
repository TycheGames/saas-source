<?php
namespace backend\controllers;

use backend\models\AdminNxUser;
use backend\models\AdminUser;
use backend\models\Merchant;
use backend\models\remind\RemindAdmin;
use backend\models\remind\RemindLog;
use backend\models\remind\RemindOrder;
use backend\models\remind\RemindSmsTemplate;
use backend\models\RemindAppScreenShot;
use backend\models\RemindCheckinLog;
use backend\models\ReminderCallData;
use common\helpers\MessageHelper;
use common\helpers\RedisQueue;
use common\models\ClientInfoLog;
use common\models\enum\Relative;
use common\models\GlobalSetting;
use common\models\message\NxPhoneLog;
use common\models\order\UserLoanOrder;
use common\models\order\UserLoanOrderExtraRelation;
use common\models\order\UserLoanOrderRepayment;
use common\models\user\LoanPerson;
use common\models\user\UserActiveTime;
use common\models\user\UserContact;
use common\services\message\SendMessageService;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Yii;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;
use yii\web\Response;

class RemindApiController extends BaseApiController {

    public $enableCsrfValidation=false;

    /**
     * @name 提醒订单列表-remind-api/get-remind-list
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function actionGetRemindList(){
        $operator = Yii::$app->user->getId();
        $where = ['B.is_overdue' => UserLoanOrderRepayment::IS_OVERDUE_NO,'A.customer_user_id' => $operator];
        $condition[] = 'and';
        $sort = [];
        if ($this->getRequest()->getIsPost()) {
            $search = $this->request->post();
            if (isset($search['phone']) && !empty($search['phone'])) {
                $where['C.phone'] = $search['phone'];
            }
            if (isset($search['remindStatus']) && $search['remindStatus']!='') {
                $where['A.status'] = $search['remindStatus'];
            }
            if (isset($search['repaymentStatus']) && $search['repaymentStatus']!='') {
                $where['B.status'] = $search['repaymentStatus'];
            }

            if (isset($search['reachStatus']) && $search['reachStatus']!='') {
                if(intval($search['reachStatus']) == RemindOrder::REMIND_REACH){
                    $condition[] = ['>', 'A.remind_return', 0];
                }elseif (intval($search['reachStatus']) == RemindOrder::REMIND_NO_REACH){
                    $condition[] = ['<', 'A.remind_return', 0];
                }else{
                    $condition[] = ['=', 'A.remind_return', 0];
                }
            }
            if (isset($search['startTime']) && $search['startTime']!='') {
                $condition[] = ['>=', 'B.plan_repayment_time', strtotime($search['startTime'])];
            }
            if (isset($search['endTime']) && $search['endTime']!='') {
                $condition[] = ['<=', 'B.plan_repayment_time', strtotime($search['endTime'])];
            }
            if (isset($search['remindReturnStatus']) && $search['remindReturnStatus']!='') {
                $condition[] = ['A.remind_return' => intval($search['remindReturnStatus'])];
            }
            if(isset($search['willingBlinkerStatus']) && !empty($search['willingBlinkerStatus']) && is_array($search['willingBlinkerStatus'])){
                $blinkerCondition = UserActiveTime::colorBlinkerConditionNew($search['willingBlinkerStatus'],'D.','B.');
                $condition[] = $blinkerCondition;
            }
        }
        $sort['A.id'] = SORT_DESC;
        $query = RemindOrder::find()
            ->select([
                'A.id',
                'A.repayment_id',
                'A.status as remind_status',
                'A.dispatch_status',
                'A.remind_count',
                'A.remind_return',
                'A.payment_after_days',
                'D.last_active_time',
                'D.last_pay_time',
                'D.last_money_sms_time',
                'D.max_money',
                'D.level_change_call_success_time',
                'B.order_id',
                'B.principal',
                'B.interests',
                'B.cost_fee',
                'B.true_total_money',
                'B.plan_repayment_time',
                'B.closing_time',
                'B.status',
                'C.name',
                'C.phone',
                'C.customer_type',
                'F.is_first',
                'B.user_id'
            ])
            ->from(RemindOrder::tableName() . ' A')
            ->leftJoin(UserLoanOrderRepayment::tableName(). ' B',' A.repayment_id = B.id')
            ->leftJoin(LoanPerson::tableName(). ' C',' B.user_id = C.id')
            ->leftJoin(UserActiveTime::tableName(). 'D','B.user_id = D.user_id')
            ->leftJoin(UserLoanOrder::tableName(). ' F',' B.order_id = F.id')
            ->where($where)
            ->andWhere($condition)
            ->andWhere(['B.merchant_id' => $this->merchantIds]);
        $pages = new Pagination(['totalCount' => $query->count()]);
        $search['per-page'] = $search['per-page'] ?? 10;
        $pages->page = ($search['page'] ?? 1) - 1;
        $pages->pageSize = $search['per-page'];
        $info = $query->orderBy($sort)->offset($pages->offset)->limit($pages->limit)->asArray()->all();

        $user_ids = array_column($info,'user_id');
        $repayCount = [];
        if(!empty($user_ids)){
            $repayCount = UserLoanOrderRepayment::find()->select(['user_id','count' => 'COUNT(id)'])
                ->where(['user_id' => $user_ids, 'status' => UserLoanOrderRepayment::STATUS_REPAY_COMPLETE])
                ->groupBy(['user_id'])
                ->indexBy(['user_id'])
                ->asArray()
                ->all();
        }
        $list = [];
        foreach ($info as $item){
            $arr = UserActiveTime::colorBlinkerShow($item);
            $list[] = [
                'id' => $item['id'],
                'repayOrderNo' => $item['repayment_id'],
                'orderNo' => $item['order_id'],
                'name' => $item['name'],
                'phone' => base64_encode($item['phone']),
                'repayCount' => $repayCount[$item['user_id']]['count'] ?? 0,
                'principal' => sprintf("%0.2f",$item['principal']/100),
                'interest' => sprintf("%0.2f",$item['interests']/100),
                'costFee' => sprintf("%0.2f",$item['cost_fee']/100),
                'completedMoney' => sprintf("%0.2f",$item['true_total_money']/100),
                'shouldRepayDate' => date('Y-m-d',$item['plan_repayment_time']),
                'completedDate' => $item['closing_time'] ? date('Y-m-d',$item['closing_time']) : '-',
                'remindCount' => $item['remind_count'],
                'repayStatus' => isset(UserLoanOrderRepayment::$repayment_status_map[$item['status']])?UserLoanOrderRepayment::$repayment_status_map[$item['status']]:"",
                'remindStatus' =>  RemindOrder::$status_map[$item['remind_status']],
                'reach/noReach' => $item['remind_return'] > 0 ? 'Reach' : ($item['remind_return'] < 0 ? 'No Reach' : '-'),
                'remindReturn' => (RemindOrder::$remind_return_map_all[$item['remind_return']] ?? '-') . ($item['payment_after_days'] > 0 ? '('.$item['payment_after_days'].')': '') ,
                'isRepayment' => isset($arr[UserActiveTime::BLUE_COLOR]) ? true : false,
                'isConfirmRepayment' => isset($arr[UserActiveTime::RED_COLOR]) ? true : false,
                'isJustCall' => isset($arr[UserActiveTime::BLACK_COLOR]) ? true : false,
                'isEndlessCall' => isset($arr[UserActiveTime::DAZZLING_COLOR]) ? true : false,
                'isTelConnection' => isset($arr[UserActiveTime::GREEN_COLOR]) ? true : false
            ];
        }

        $returnCode = RemindCheckinLog::checkStartWork(yii::$app->user->id) ? 0 : -4;
        return [
            'code' => $returnCode,
            'data' => [
                'totalPage' => $pages->getPageCount(),
                'list' => ArrayHelper::htmlEncode($list),
                'isShowUserRepaymentIntentionSelectGroup' => true
            ],
            'message' => 'success'
        ];
    }


    /**
     * @name 提醒-订单订单还款信息-remind-api/get-remind-payment-info
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function actionGetRemindPaymentInfo()
    {
        $operator = Yii::$app->user->getId();
        $remindId = $this->request->post('id');
        /** @var RemindAdmin $remindAdmin */
        $remindAdmin = RemindAdmin::find()->where(['admin_user_id' => $operator])->one();
        $where = ['A.id' => $remindId,'A.dispatch_status' => RemindOrder::STATUS_FINISH_DISPATCH];
        if($remindAdmin){
            if($remindAdmin->remind_group > 0){
                $where['A.customer_user_id'] = $operator;
            }
            $where['B.is_overdue'] = UserLoanOrderRepayment::IS_OVERDUE_NO;
        }
        $remindOrderInfo = RemindOrder::find()
            ->select([
                'A.plan_date_before_day',
                'B.*',
                'C.name',
                'C.phone',
                'E.relative_contact_person',
                'E.name as contact_name',
                'E.phone as contact_phone',
                'E.other_relative_contact_person',
                'E.other_name as other_contact_name',
                'E.other_phone as other_contact_phone',
            ])
            ->from(RemindOrder::tableName() . ' A')
            ->leftJoin(UserLoanOrderRepayment::tableName(). ' B',' A.repayment_id = B.id')
            ->leftJoin(LoanPerson::tableName(). ' C',' B.user_id = C.id')
            ->leftJoin(UserLoanOrderExtraRelation::tableName(). ' D',' B.order_id = D.order_id')
            ->leftJoin(UserContact::tableName(). ' E',' D.user_contact_id = E.id')
            ->where($where)
            ->andWhere(['B.merchant_id' => $this->merchantIds])
            ->asArray()
            ->one();
        if(is_null($remindOrderInfo)){
            return ['code' => -1,'data' => [],'message' => 'remind order no exist or expired'];
        }
        $userLoanOrder = UserLoanOrder::findOne($remindOrderInfo['order_id']);
        $repaymentInformation = [
            'repayOrderNO' => $remindOrderInfo['id'],
            'name' => $remindOrderInfo['name'],
            'phone' => $remindOrderInfo['phone'],
            'principal' => sprintf("%0.2f",($remindOrderInfo['principal'])/100),
            'interests' => sprintf("%0.2f",($remindOrderInfo['interests'])/100),
            'finishRepaymentDate' => !empty($remindOrderInfo['closing_time']) ? date('Y-m-d',$remindOrderInfo['closing_time']) : '--',
            'completedMoney' => sprintf("%0.2f",$remindOrderInfo['true_total_money']/100),
            'loanProduct' => $userLoanOrder->clientInfoLog['package_name'] ?? '--',
            'fromApp' => $userLoanOrder->is_export == UserLoanOrder::IS_EXPORT_YES ? (explode('_',$userLoanOrder->clientInfoLog['app_market'])[1] ?? '--') : ($userLoanOrder->clientInfoLog['package_name'] ?? '--'),
        ];

        if($remindOrderInfo['plan_date_before_day'] == 0 && $userLoanOrder->merchant->is_hidden_contacts == Merchant::NOT_HIDDEN){
            $repaymentInformation['firstRelativeContactPerson'] = Relative::$map[$remindOrderInfo['relative_contact_person']] ?? '--';
            $repaymentInformation['firstContactName'] = $remindOrderInfo['contact_name'] ?? '--';
            $repaymentInformation['firstContactPhone'] = $remindOrderInfo['contact_phone'] ?? '--';
            $repaymentInformation['secondRelativeContactPerson'] = Relative::$map[$remindOrderInfo['other_relative_contact_person']] ?? '--';
            $repaymentInformation['secondContactName'] = $remindOrderInfo['other_contact_name'] ?? '--';
            $repaymentInformation['secondContactPhone'] = $remindOrderInfo['other_contact_phone'] ?? '--';
        }
        $returnCode = RemindCheckinLog::checkStartWork(yii::$app->user->id) ? 0 : -4;
        return  [
            'code' => $returnCode,
            'data' => ArrayHelper::htmlEncode($repaymentInformation),
            'message' => 'success'
        ];
    }


    /**
     * @name 提醒-订单历史-remind-api/get-remind-history
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function actionGetRemindHistory()
    {
        $operator = Yii::$app->user->getId();
        $remindId = $this->request->post('id');
        /** @var RemindAdmin $remindAdmin */
        $remindAdmin = RemindAdmin::find()->where(['admin_user_id' => $operator])->one();
        $where = ['A.id' => $remindId,'A.dispatch_status' => RemindOrder::STATUS_FINISH_DISPATCH];
        if($remindAdmin){
            if($remindAdmin->remind_group > 0){
                $where['A.customer_user_id'] = $operator;
            }
            $where['B.is_overdue'] = UserLoanOrderRepayment::IS_OVERDUE_NO;
        }
        $remindOrderInfo = RemindOrder::find()
            ->select([
                'A.plan_date_before_day',
                'B.*',
                'C.name',
                'C.phone',
                'D.package_name'
            ])
            ->from(RemindOrder::tableName() . ' A')
            ->leftJoin(UserLoanOrderRepayment::tableName(). ' B',' A.repayment_id = B.id')
            ->leftJoin(LoanPerson::tableName(). ' C',' B.user_id = C.id')
            ->leftJoin(ClientInfoLog::tableName(). ' D',' B.order_id = D.event_id AND D.event = '.ClientInfoLog::EVENT_APPLY_ORDER)
            ->where($where)
            ->andWhere(['B.merchant_id' => $this->merchantIds])
            ->asArray()
            ->one();
        if(is_null($remindOrderInfo)){
            return ['code' => -1,'data' => [],'message' => 'remind order no exist or expired'];
        }

        $downList = [0 => 'don\'t send'];
        $template = RemindSmsTemplate::find()->where(['status' => RemindSmsTemplate::STATUS_USABLE,'package_name' => $remindOrderInfo['package_name'],'merchant_id' => $this->merchantIds])->all();
        /** @var RemindSmsTemplate $item */
        foreach ($template as $item){
            $downList[$item->id] = $item->name;
        }

        $remindLog = RemindLog::find()
            ->select(['A.*','B.username as customer_name','C.username as operator_name'])
            ->from(RemindLog::tableName() .' A')
            ->leftJoin(AdminUser::tableName() . 'B','A.customer_user_id = B.id')
            ->leftJoin(AdminUser::tableName() . 'C','A.operator_user_id = C.id')
            ->where(['remind_id' => $remindId])
            ->asArray()
            ->all();

        $list = [];
        foreach ($remindLog as $item){
            $list[] = [
                'dispatchUser' => $item['customer_name'],
                'operatorUser' => $item['operator_name'],
                'whetherReach' => in_array($item['remind_return'],RemindOrder::$remind_reach_return) ? 'reach' : 'no reach',
                'reachResult' => RemindOrder::$remind_return_map_all[$item['remind_return']] . ($item['remind_return'] == RemindOrder::REMIND_RETURN_PAYMENT_AFTER_DAYS ? ': '.$item['payment_after_days'].'days' : ''),
                'sendSms' => $downList[$item['sms_template']] ?? '-',
                'remindRemark' => $item['remind_remark'],
                'createdTime' => date("Y-m-d H:i:s",$item['created_at'])
            ];
        }
        $returnCode = RemindCheckinLog::checkStartWork(yii::$app->user->id) ? 0 : -4;
        return  [
            'code' => $returnCode,
            'data' => ArrayHelper::htmlEncode($list),
            'message' => 'success'
        ];
    }

    /**
     * 提醒提交信息-remind-api/get-submit-info
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function actionGetSubmitInfo(){
        $operator = Yii::$app->user->getId();
        $remindId = $this->request->post('id');
        /** @var RemindAdmin $remindAdmin */
        $remindAdmin = RemindAdmin::find()->where(['admin_user_id' => $operator])->one();
        $where = ['A.id' => $remindId,'A.dispatch_status' => RemindOrder::STATUS_FINISH_DISPATCH];
        if($remindAdmin){
            if($remindAdmin->remind_group > 0){
                $where['A.customer_user_id'] = $operator;
            }
            $where['B.is_overdue'] = UserLoanOrderRepayment::IS_OVERDUE_NO;
        }
        $remindOrderInfo = RemindOrder::find()
            ->select([
                'A.plan_date_before_day',
                'B.*',
                'C.name',
                'C.phone',
                'D.package_name'
            ])
            ->from(RemindOrder::tableName() . ' A')
            ->leftJoin(UserLoanOrderRepayment::tableName(). ' B',' A.repayment_id = B.id')
            ->leftJoin(LoanPerson::tableName(). ' C',' B.user_id = C.id')
            ->leftJoin(ClientInfoLog::tableName(). ' D',' B.order_id = D.event_id AND D.event = '.ClientInfoLog::EVENT_APPLY_ORDER)
            ->where($where)
            ->andWhere(['B.merchant_id' => $this->merchantIds])
            ->asArray()
            ->one();
        if(is_null($remindOrderInfo)){
            return ['code' => -1,'data' => [],'message' => 'remind order no exist or expired'];
        }

        $reachResultList = [];
        foreach (RemindOrder::$remind_reach_return as $key => $val){
            if($key == RemindOrder::REMIND_RETURN_PAYMENT_AFTER_DAYS){
                $dayList = [];
                foreach (RemindOrder::$payment_after_days_map as $day => $name){
                    $dayList[] = ['id' => (string)$day,'label' => $name];
                }
                $reachResultList[] = ['id' => (string)$key,'label' => $val,'dayList' => $dayList];
            }else{
                $reachResultList[] = ['id' => (string)$key,'label' => $val];
            }
        }
        $noReachResultList = [];
        foreach (RemindOrder::$remind_no_reach_return as $key => $val){
            $noReachResultList[] = ['id' => (string)$key,"label" => $val];
        }

        $reachList = [
            ['id' => "1","label" => "reach","resultList" => $reachResultList],
            ['id' => "2","label" => "no reach","resultList" => $noReachResultList],
        ];

        $count = RemindLog::find()->where(['remind_id' => $remindOrderInfo['id']])
            ->andWhere(['>','sms_template',0])
            ->andWhere(['>=','created_at',strtotime('today')])
            ->andWhere(['<','created_at',strtotime('today') + 86400])
            ->count();
        $canSend = true;
        if($count >= 3){
            $canSend = false;
        }

        $smsList = [['id' => '0','label' => 'don\'t send','component' => '']];
        if($canSend){
            $template = RemindSmsTemplate::find()->where(['status' => RemindSmsTemplate::STATUS_USABLE,'package_name' => $remindOrderInfo['package_name'],'merchant_id' => $this->merchantIds])->asArray()->all();
            /** @var RemindSmsTemplate $item */
            foreach ($template as $item){
                $downList[$item['id']] = $item['name'];///$this->principal + $this->interests;
                $send_message = str_replace(['#username#','#total_money#','#should_repay_date#','#remind_date#'],
                    [$remindOrderInfo['name'],($remindOrderInfo['principal'] + $remindOrderInfo['interests']) / 100,date('d/m/Y',$remindOrderInfo['plan_repayment_time']),date('d/m/Y')],$item['content']); // 处理文案内容信息替换
                $smsList[] = ['id' => $item['id'],'label' => $item['name'],'component' => $send_message];
            }
        }
        return [
            'code' => 0,
            'data' => ArrayHelper::htmlEncode([
                'reachList' => $reachList,
                'smsList' => $smsList
            ]),
            'message' => 'success'
        ];
    }



    /**
     * 提醒提交-remind-api/submit
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function actionSubmit(){
        $operator = Yii::$app->user->getId();
        $remindId = $this->request->post('id');
        $remindTurn = $this->request->post('reachResult');
        $paymentAfterDays = $this->request->post('afterDays',0);
        $remindRemark = $this->request->post('remark');
        $smsTemplate = $this->request->post('sms');

        /** @var RemindAdmin $remindAdmin */
        $remindAdmin = RemindAdmin::find()->where(['admin_user_id' => $operator])->one();
        $where = ['A.id' => $remindId,'A.dispatch_status' => RemindOrder::STATUS_FINISH_DISPATCH];
        if($remindAdmin){
            if($remindAdmin->remind_group > 0){
                $where['A.customer_user_id'] = $operator;
            }
            $where['B.is_overdue'] = UserLoanOrderRepayment::IS_OVERDUE_NO;
        }
        $remindOrderInfo = RemindOrder::find()
            ->select([
                'A.plan_date_before_day',
                'B.*',
                'C.name',
                'C.phone',
                'D.package_name'
            ])
            ->from(RemindOrder::tableName() . ' A')
            ->leftJoin(UserLoanOrderRepayment::tableName(). ' B',' A.repayment_id = B.id')
            ->leftJoin(LoanPerson::tableName(). ' C',' B.user_id = C.id')
            ->leftJoin(ClientInfoLog::tableName(). ' D',' B.order_id = D.event_id AND D.event = '.ClientInfoLog::EVENT_APPLY_ORDER)
            ->where($where)
            ->asArray()
            ->one();
        if(is_null($remindOrderInfo)){
            return ['code' => -1,'data' => [],'message' => 'remind order no exist or expired'];
        }
        if(!in_array($remindTurn,array_keys(RemindOrder::$remind_return_map_all))){
            return ['code' => -1,'data' => [],'message' => 'remind turn fail'];
        }

        if($remindTurn != RemindOrder::REMIND_RETURN_PAYMENT_AFTER_DAYS){
            $paymentAfterDays = 0;
        }else{
            if(!in_array($paymentAfterDays,array_keys(RemindOrder::$payment_after_days_map))){
                return ['code' => -1,'data' => [],'message' => 'remind payment after days fail'];
            }
        }

        $count = RemindLog::find()->where(['remind_id' => $remindOrderInfo['id']])
            ->andWhere(['>','sms_template',0])
            ->andWhere(['>=','created_at',strtotime('today')])
            ->andWhere(['<','created_at',strtotime('today') + 86400])
            ->count();


        if($smsTemplate > 0){
            if($count >= 3){
                return ['code' => -1,'data' => [],'message' => 'send sms template more than 3'];
            }
            /** @var RemindSmsTemplate $remindSmsTemplate */
            $remindSmsTemplate = RemindSmsTemplate::find()
                ->where([
                    'id'           => $smsTemplate,
                    'status'       => RemindSmsTemplate::STATUS_USABLE,
                    'package_name' => $remindOrderInfo['package_name'],
                    'merchant_id'  => $this->merchantIds,
                ])
                ->one();
            if(is_null($remindSmsTemplate)){
                return ['code' => -1,'data' => [],'message' => 'sms template error'];
            }

            $send_message = str_replace(['#username#','#total_money#','#should_repay_date#','#remind_date#'],
                [$remindOrderInfo['name'],($remindOrderInfo['principal'] + $remindOrderInfo['interests']) / 100,date('d/m/Y',$remindOrderInfo['plan_repayment_time']),date('d/m/Y')],$remindSmsTemplate->content); // 处理文案内容信息替换

            if(YII_ENV_PROD){
                $smsParamsName = SendMessageService::$smsMKTConfigList[$remindOrderInfo['package_name']];
                MessageHelper::sendAll($remindOrderInfo['phone'],$send_message,$smsParamsName);
            }
        }else{
            $smsTemplate = 0;
        }
        /** @var RemindOrder $remindOrder */
        $remindOrder = RemindOrder::find()->where(['id' => $remindId])->one();
        $remindOrder->status = RemindOrder::STATUS_REMINDED;
        $remindOrder->remind_return = $remindTurn;
        $remindOrder->payment_after_days = $paymentAfterDays;
        $remindOrder->remind_remark = $remindRemark;
        $remindOrder->remind_count = $remindOrder->remind_count + 1;
        $remindOrder->save();

        $remindLog = new RemindLog();
        $remindLog->remind_id = $remindOrder->id;
        $remindLog->customer_user_id = $remindOrder->customer_user_id;
        $remindLog->operator_user_id = $operator;
        $remindLog->remind_return = $remindTurn;
        $remindLog->payment_after_days = $paymentAfterDays;
        $remindLog->sms_template = $smsTemplate;
        $remindLog->remind_remark = $remindRemark;
        $remindLog->save();

        return [
            'code' => 0,
            'data' => [],
            'message' => 'success'
        ];
    }

    /**
     * @name 获取打卡信息
     * @return array
     */
    public function actionGetAttendenceInfo()
    {
        $data = [
            'startWorkTime' => '',
            'offWorkTime' => '',
            'userName' => yii::$app->user->identity->username,
            'nowTime' => date('Y-m-d')
        ];
        /** @var RemindCheckinLog $startWork */
        $startWork = RemindCheckinLog::find()
            ->where([
                'user_id' => yii::$app->user->getId(),
                'type' => RemindCheckinLog::TYPE_START_WORK,
                'date' => date('Y-m-d')
            ])
            ->limit(1)->one();
        if(!is_null($startWork))
        {
            $data['startWorkTime'] = date('H:i', $startWork->created_at);
        }

        /** @var RemindCheckinLog $offWork */
        $offWork = RemindCheckinLog::find()
            ->where([
                'user_id' => yii::$app->user->getId(),
                'type' => RemindCheckinLog::TYPE_OFF_WORK,
                'date' => date('Y-m-d')
            ])
            ->orderBy(['id' => SORT_DESC])->limit(1)->one();
        if(!is_null($offWork))
        {
            $data['offWorkTime'] = date('H:i', $offWork->created_at);
        }

        return [
            'code' => 0,
            'data' => ArrayHelper::htmlEncode($data)
        ];

    }


    /**
     * @name 打卡
     * @return array
     */
    public function actionAttendence()
    {
        $type = Yii::$app->request->post('type');
        $addressType = Yii::$app->request->post('addressType',RemindCheckinLog::TYPE_DEFAULT);
        if(!in_array($type, array_values(RemindCheckinLog::$type_map)))
        {
            return [
                'code' => -1,
                'message' => 'type parameter err'
            ];
        }
        if(!isset(RemindCheckinLog::$address_type_map[$addressType]))
        {
            return [
                'code' => -1,
                'message' => 'address type parameter err'
            ];
        }

        $userID = yii::$app->user->id;
        $date = date('Y-m-d');
        $redisKey = "remind_app:{$type}_{$userID}_{$date}";
        if(RemindCheckinLog::$type_map[RemindCheckinLog::TYPE_START_WORK] == $type && !RedisQueue::lock($redisKey, 86500))
        {
            /** @var RemindCheckinLog $log */
            $log = RemindCheckinLog::find()
                ->where(['type' => RemindCheckinLog::TYPE_START_WORK, 'date' => $date, 'user_id' => $userID])
                ->orderBy(['id' => SORT_ASC])
                ->one();
            if($log){
                return [
                    'code' => 0,
                    'message' => 'success',
                    'data' => [
                        'time' => date('H:i', $log->created_at)
                    ]
                ];
            }
        }
        if(RemindCheckinLog::$type_map[RemindCheckinLog::TYPE_OFF_WORK] == $type){
            /** @var RemindCheckinLog $log */
            $log = RemindCheckinLog::find()
                ->where(['type' => RemindCheckinLog::TYPE_START_WORK, 'user_id' => $userID])
                ->orderBy(['id' => SORT_DESC])
                ->one();
            $addressType = $log->address_type;
        }

        $log = new RemindCheckinLog();
        $log->user_id = $userID;
        $log->type = array_flip(RemindCheckinLog::$type_map)[$type];
        $log->address_type = $addressType;
        $log->date = $date;
        if($log->save())
        {
            return [
                'code' => 0,
                'message' => 'success',
                'data' => [
                    'time' => date('H:i', $log->created_at)
                ]
            ];
        }else{
            return [
                'code' => -1,
                'message' => 'save fail',
                'data' => [
                    'time' => date('H:i', $log->created_at)
                ]
            ];
        }
    }

    /**
     * @name (menu)提醒app牛信坐席电话
     * @return array
     */
    public function actionCallPhone()
    {
        $this->getResponse()->format = Response::FORMAT_JSON;

        $phone      = trim($this->request->post('phone'));
        $type       = trim($this->request->post('type',ReminderCallData::TYPE_ONE_SELF));
        $isSdk      = $this->request->post(('isSdk'));
        $collector_id = Yii::$app->user->identity->getId();
        $phone_type = $isSdk == true ? AdminNxUser::TYPE_SDK : AdminNxUser::TYPE_ANDROID;
        $call_phone_type = $isSdk == true ? ReminderCallData::NIUXIN_SDK : ReminderCallData::NIUXIN_APP;
        if (!$phone) {
            return ['code' => -1, 'message' => 'phone is incorrect'];
        }

        if(!in_array($type,[ReminderCallData::TYPE_ONE_SELF,ReminderCallData::TYPE_CONTACT])){
            return ['code' => -1, 'message' => 'type is error'];
        }
        $adminInfo = AdminNxUser::find()
            ->where(['collector_id' => $collector_id, 'status' => AdminNxUser::STATUS_ENABLE, 'type' => $phone_type])
            ->asArray()
            ->one();

        if (!$adminInfo) {
            return ['code' => -1, 'message' => 'No match to Nioxin account'];
        }
        $nx_orderid = 'saas'.time().$adminInfo['nx_name'];
        try {
            $nxPhoneLogMod = new NxPhoneLog();
            $nxPhoneLogMod->nx_orderid = $nx_orderid;
            $nxPhoneLogMod->collector_id  = $collector_id;
            $nxPhoneLogMod->nx_name  = $adminInfo['nx_name'];
            $nxPhoneLogMod->phone  = $phone;
            $nxPhoneLogMod->type   = $type;
            $nxPhoneLogMod->status = NxPhoneLog::STATUS_NO;
            $nxPhoneLogMod->call_type = NxPhoneLog::CALL_CUSTOMER;
            $nxPhoneLogMod->phone_type = $call_phone_type;
            $nxPhoneLogMod->save();
        } catch (\Exception $e) {
            exit;
        }
        return [
            'code' => 0,
            'data' => ['id' => $nx_orderid],
            'message' => 'success'
        ];
    }

    /**
     * @name (menu)工作台-牛信坐席电话开关
     * @return array
     */
    public function actionShowCallNx()
    {
        $model = GlobalSetting::find()->where(['key' => GlobalSetting::KEY_NX_PHONE_CONFIG_LIST])->one();
        $model1 = GlobalSetting::find()->where(['key' => GlobalSetting::KEY_NX_PHONE_SDK_CONFIG_LIST])->one();
        $value = is_null($model) ? 0 : $model->value;
        $sdkValue = is_null($model1) ? 0 : $model1->value;
        $status = 1 == $value ? true : false;
        $sdkStatus = 1 == $sdkValue ? true : false;
        return [
            'code' => 0,
            'data' => ['isShowCallNX' => $status,'isShowCallNXSDK' => $sdkStatus],
            'message' => 'success'
        ];
    }

    /**
     * @name (menu)工作台-牛信坐席电话
     * @return array
     */
    public function actionGetNxAccount()
    {
        $user_id = yii::$app->user->getId();
        $app = AdminNxUser::find()->where(['collector_id' => $user_id, 'status' => AdminNxUser::STATUS_ENABLE, 'type' => AdminNxUser::TYPE_ANDROID])->one();
        $app_nx_name = empty($app->nx_name) ? '' : 'nxcall:'.$app->nx_name;
        return [
            'code' => 0,
            'data' => array(
                'items' => array(
                    'title'=>$app_nx_name,
                )
            ),
            'message' => 'success'
        ];
    }

    /**
     * @name RemindApiController 上报截屏
     * @return array
     */
    public function actionUploadScreenshots(){
        $user_id = yii::$app->user->getId();
        $appScreenShot = new RemindAppScreenShot();
        $appScreenShot->user_id = $user_id;
        if($appScreenShot->save()){
            return [
                'code' => 0,
                'data' => [],
                'message' => 'success'
            ];
        }else{
            return [
                'code' => -1,
                'data' => [],
                'message' => 'fail'
            ];
        }
    }
}

<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2016/10/4
 * Time: 17:59
 */
namespace callcenter\controllers;

use backend\models\Merchant;
use callcenter\models\AdminNxUser;
use callcenter\models\AdminUser;
use callcenter\models\AdminUserRole;
use callcenter\models\CollectorCallData;
use callcenter\models\loan_collection\LoanCollectionOrder;
use callcenter\models\loan_collection\LoanCollectionRecord;
use callcenter\models\search\AdminRecordListSearch;
use callcenter\models\search\CollectionOrderListSearch;
use callcenter\models\SmsTemplate;
use callcenter\service\CallStatisticsService;
use callcenter\service\CollectionPublicService;
use callcenter\service\LoanCollectionService;
use callcenter\service\StatisticsService;
use common\models\enum\Relative;
use common\models\manual_credit\ManualSecondMobile;
use common\models\message\NxPhoneLog;
use common\models\order\UserLoanOrder;
use common\models\order\UserLoanOrderExternal;
use common\models\order\UserLoanOrderRepayment;
use common\models\razorpay\RazorpayVirtualAccount;
use common\models\user\LoanPerson;
use common\models\user\MgUserMobileContacts;
use common\services\GuestService;
use common\services\loan_collection\LoanCollectionOrderService;
use common\services\order\OrderExtraService;
use common\services\order\OrderService;
use common\services\pay\RazorpayService;
use common\services\repayment\ReductionService;
use common\services\user\UserExtraService;
use Yii;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\data\Pagination;
use yii\helpers\Json;
use yii\base\Exception;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class WorkDeskController extends  BaseController{
    public $enableCsrfValidation = false;

    /**
     * @name 当前催收员任务
     * @date 2017-5-26
     * @author 胡浩
     * @use 用于催收员登录显示当前订单待处理概况
     */
    public function actionMyWork(){
        $mission = LoanCollectionOrder::missionUser();
        return $this->render('my-work', array(
            'mission' => $mission,
        ));
    }

    /**
     * @name (menu)工作台-我的催收记录
     * @return string
     */
    public function actionAdminRecordList(){
        $searchForm = new AdminRecordListSearch();
        $condition = $searchForm->search(Yii::$app->request->get());
        $recordInfo = CollectionPublicService::collectionRecordInfo($condition,2 , $this->merchantIds);
        return $this->render('collection-record-list', array(
            'recordInfo' => $recordInfo,
            'from'=>false,
            'list_type'=>true,
        ));
    }


    /**
     * @name (menu)工作台-我的订单列表
     * @return bool|string
     */
    public function actionAdminCollectionOrderList(){
        $admin_user_id = Yii::$app->user->id;
        if(!$admin_user_id){
            return false;
        }
        $search = Yii::$app->request->get();
        $searchForm = new CollectionOrderListSearch();
        $condition = $searchForm->search($search);
        $condition[] = ["A.current_collection_admin_user_id" => $admin_user_id];
        $collection_lists = CollectionPublicService::getCollectionOrderList($condition, $this->merchantIds, $search);

        //申请减免信息
        $reductionService = new ReductionService();
        foreach ($collection_lists['order'] as &$item){
            $loanCollectionOrder = LoanCollectionOrder::findOne($item['id']);
            $item['isCanReduce'] = $reductionService->operateCheck($loanCollectionOrder);
        }
        return $this->render('_collection_order_list', array(
            'loan_collection_list' => $collection_lists['order'],
            'pages' => $collection_lists['page'],
            'openSearchLabel' => Yii::$app->user->identity->open_search_label == AdminUser::CAN_SEARCH_LABEL ?? false
        ));
    }

    /**
     * @name (action)工作台-催收
     * @return string
     */
    public function actionCollectionView()
    {
        try{
            $operator_id = Yii::$app->user->id;     //当前催收人ID
            $orderId = intval(Yii::$app->request->get('order_id', 0));
            /** @var LoanCollectionOrder $order */
            $order = LoanCollectionOrder::find()->where([
                'id'=>$orderId ,
                'merchant_id' => $this->merchantIds
            ])->one();

            $personId = $order->user_id;   //借款人ID
            $user_loan_order_id = Yii::$app->request->get('loan_order_id', 0);
            $db_array = ['db' => 'db'];
            if($order['current_collection_admin_user_id'] == 0){
                throw new NotFoundHttpException('The order cannot be processed 1');
            }
            if(!in_array($order['status'],LoanCollectionOrder::$collection_status)){
                throw new NotFoundHttpException('The order status can not be processed 2');
            }
            if($operator_id != $order['current_collection_admin_user_id']){
                $loanCollectionService = new LoanCollectionService($operator_id);
                $res = $loanCollectionService->checkCanOperatedCollector($order['current_collection_admin_user_id']);
                if(!$res){
                    throw new NotFoundHttpException('The order cannot be processed');
                }
            }
            /** @var UserLoanOrder $userLoanOrder */
            $userLoanOrder = UserLoanOrder::find()->where(['id' => $order->user_loan_order_id])->one();
            $historyOrder = UserLoanOrderRepayment::find()
                ->where(['user_id'=>$personId])
                ->andWhere(['!=','order_id',$user_loan_order_id])
                ->asArray()->all(Yii::$app->get($db_array['db']));
            $count = count($historyOrder)+1;

            //ajax——通讯录
            $asy_contact_list = $this->request->get('contact_list');
            if (!empty($asy_contact_list)) {
                if($userLoanOrder->merchant->is_hidden_address_book == Merchant::IS_HIDDEN){
                    $TXL = ['loan_mobile_contacts_list'=>[0=>[]],'all_loan_mobile_contacts'=>[]];
                    return Json::encode($TXL);
                }
                $db = null;
                if($userLoanOrder->is_export == UserLoanOrder::IS_EXPORT_YES){
                    $db = MgUserMobileContacts::getLoanDb();
                    /** @var UserLoanOrderExternal $externalOrder */
                    $externalOrder = UserLoanOrderExternal::find()->where(['order_uuid' => $userLoanOrder->order_uuid])->one();
                    $personId = $externalOrder->user_id;
                }
                $TXL = LoanCollectionOrderService::getTXL($personId,$db);
                return Json::encode($TXL);
            }
            //ajax获取还款基础信息
            $asy_base_info = $this->request->get('base_info');
            if (!empty($asy_base_info)) {
                $repayInfo = LoanCollectionOrderService::getULOR($order);
                return Json::encode($repayInfo);
            }
            //获取催收记录
            $search = $this->request->get();
            $searchForm = new AdminRecordListSearch();
            $condition = $searchForm->search($search);
            $query = LoanCollectionRecord::find()
                ->select('A.*,B.username as operator_name')
                ->from(LoanCollectionRecord::tableName() . ' A')
                ->leftJoin(AdminUser::tableName() . ' B','A.operator = B.id');

            foreach ($condition as $item)
            {
                $query->andFilterWhere($item);
            }

            $loan_collection_record_list = $query
                ->orderBy(["id" => SORT_DESC])
                ->asArray()
                ->all();


            $loan_collection_record = [];
            //群发operate_at相同的说明是群发的
            foreach ($loan_collection_record_list as $key => &$value)
            {
                $loan_collection_record[$value['operate_at']][] = $value;
            }

            $filter = $this->request->get('filter');
            if (!empty($filter)) {
                return Json::encode(['success'=>2,'loan_collection_record'=>$loan_collection_record]);
            }
            $type = $this->request->get('page_type');
            if(!empty($type) && $this->getRequest()->getIsAjax()){
                return Json::encode(['success'=>2,'loan_collection_record'=>$loan_collection_record]);
            }

            $orderExtraService = new OrderExtraService($userLoanOrder);
            $loanPerson = LoanPerson::findOne($order->user_id);
            $personInfo['userWorkInfos'] = $orderExtraService->getUserWorkInfo() ?? null;
            $personInfo['userBasicInfo'] = $orderExtraService->getUserBasicInfo() ?? null;
            $personInfo['permanentAddress'] = $orderExtraService->getUserOcrAadhaarReport()->address ?? null;
            $personInfo['loanPerson'] = $loanPerson;
            $personInfo['oper_group'] = $order->current_overdue_group ? $order->current_overdue_group : $order->current_overdue_level;   //根据当前逾期级别 获取对应短信模板内容
            $personInfo['order_level'] = $order->current_overdue_level;
            $personInfo['cuishou_status'] = $order->status;
            $personInfo['promise_repayment_time'] = $order->promise_repayment_time > 0 ? date('Y-m-d H:i:s',$order->promise_repayment_time) : '';
//            $service = new RazorpayService($userLoanOrder->loanFund->payAccountSetting);
//            $personInfo['razorpay_upi_address'] =  $service->createUPIAddress($userLoanOrder->id, $userLoanOrder->user_id);
//            $personInfo['open_app_apply_reduction'] = $order->open_app_apply_reduction;
            $getJxl = $this->request->get('get_lxr');
            if (!empty($getJxl)) {
                //本人
                $personContact = [
                    [
                        'contact_type' => LoanCollectionRecord::CONTACT_TYPE_SELF,
                        'name' => $loanPerson['name'],
                        'relation' => 'oneself',
                        'phone' => $loanPerson['phone'],
                    ]
                ];
                if($userLoanOrder->merchant->is_hidden_contacts == Merchant::NOT_HIDDEN) {
                    //本人第二手机
                    /** @var ManualSecondMobile $manualSecondMobile */
                    $manualSecondMobile = ManualSecondMobile::find()->where(['order_id' => $userLoanOrder->id])->one();
                    if ($manualSecondMobile) {
                        $personContact[] = [
                            'contact_type' => LoanCollectionRecord::CONTACT_TYPE_SELF,
                            'name'         => $loanPerson['name'] . '(second mobile)',
                            'relation'     => 'oneself',
                            'phone'        => $manualSecondMobile->mobile,
                        ];
                    }

                    $urgentContact = $userLoanOrder->userContact;
                    if (!empty($urgentContact->phone)) {
                        $phoneArr = explode(':', $urgentContact->phone);
                        foreach ($phoneArr as $phone) {
                            $personContact[] = [
                                'contact_type' => LoanCollectionRecord::CONTACT_TYPE_URGENT,
                                'name'         => Html::encode($urgentContact->name),
                                'relation'     => Relative::$map[$urgentContact->relative_contact_person],
                                'phone'        => Html::encode($phone),
                            ];
                        }
                    }
                    if (!empty($urgentContact->other_phone)) {
                        $phoneArr = explode(':', $urgentContact->other_phone);
                        foreach ($phoneArr as $phone) {
                            $personContact[] = [
                                //其他联系人
                                'contact_type' => LoanCollectionRecord::CONTACT_TYPE_URGENT,
                                'name'         => Html::encode($urgentContact->other_name),
                                'relation'     => Relative::$map[$urgentContact->other_relative_contact_person],
                                'phone'        => Html::encode($phone),
                            ];
                        }
                    }
                }

                return Json::encode(['jxl_contact'=>$personContact]);
            }
            $smsTemplateList = SmsTemplate::getTemplateList($order,$this->merchantIds);
        }catch(Exception $e){
            return $this->redirectMessage($e->getMessage(), self::MSG_ERROR);
        }
        $orderService = new OrderService($userLoanOrder);
        $delayData = $orderService->checkDelayStatus();
        $extendData = $orderService->checkExtendStatus();
        $info = AdminUser::findOne($operator_id);

        //生成还款链接
        $guestService = new GuestService();
        $paymentLink = $guestService->generatePaymentLink($userLoanOrder);

        return $this->render('loan-collection-vieww', [
            'orderId' => $orderId,
            'personInfo' => $personInfo,
            'loan_collection_record'=>$loan_collection_record,
            'count' => $count,
            'historyOrder'=>$historyOrder,
            'user_loan_order_id'=>$user_loan_order_id,
            'userLoanOrder' => $userLoanOrder,
            'smsTemplateList' => $smsTemplateList,
            'delayData' => $delayData,
            'extendData' => $extendData,
            'nx_phone' => $info->nx_phone,
            'paymentLink' => $paymentLink,
        ]);
    }


    /**
     * @name (action)工作台-获取催收短信内容
     * @return string
     */
    public function actionGetSmsFill(){
        $typeId = Yii::$app->request->get('sms_type');
        $operator_id = Yii::$app->user->id;     //当前催收人ID
        $orderId = intval(Yii::$app->request->get('order_id', 0));
        /** @var LoanCollectionOrder $order */
        $order = LoanCollectionOrder::find()->where([
            'user_loan_order_id'=>$orderId
        ])->one();

        if($order['current_collection_admin_user_id'] == 0){
            throw new NotFoundHttpException('The order cannot be processed');
        }
        if(!in_array($order['status'],LoanCollectionOrder::$collection_status)){
            throw new NotFoundHttpException('The order status can not be processed');
        }
        if($operator_id != $order['current_collection_admin_user_id']){
            $loanCollectionService = new LoanCollectionService($operator_id);
            $res = $loanCollectionService->checkCanOperatedCollector($order['current_collection_admin_user_id']);
            if(!$res){
                throw new NotFoundHttpException('The order cannot be processed');
            }
        }
        $res = SmsTemplate::getTemplateList($order,$typeId);
        return Json::encode(['error'=>0,'content'=> $res['content'][$typeId]]);
    }

    /**
     * @name xybt-工作台-电话、短信催收
     * @date 2017-05-26
     * @author 胡浩
     * use 管理和工作台共用的催收方法
     * param $sub_type string 切库用  白条和秒还卡区分
     */
    public function actionCollectLoan()
    {
        $adminId = Yii::$app->user->id;
        if(empty($adminId))
        {
            echo Json::encode([ 'code' => 1001, 'msg' => 'Please login again' ]);
            return;
        }
        //获取参数
        $orderId = intval(Yii::$app->request->post('order_id', 0));
        /** @var LoanCollectionOrder $order */
        $order = LoanCollectionOrder::find()->where(['id'=>$orderId])->one();
        if(empty($order))
        {
            echo Json::encode([ 'code' => 1001, 'msg' => 'Order not found' ]);
            return;
        }
        $loanCollectionService = new LoanCollectionService($adminId);
        $res = $loanCollectionService->collect($order, Yii::$app->request->post(),$this->merchantIds);
        echo Json::encode([ 'code' => $res['code'], 'msg' => $res['message'] ]);
    }

    /**
     * @name xybt-查看更多通讯录
     * @date 2018-11-28
     * @author 胡浩
     * use 管理和工作台共用方法
     * param order_id int 催收订单id
     */
    public function actionGetContact()
    {
        $order_id = Yii::$app->request->get('order_id',0);
        $order_info = LoanCollectionOrder::find()->select('user_id')->where(['id'=>$order_id])->limit(1)->one(Yii::$app->get('db_assist'));
        $contacts = [];
        if(!empty($order_info)){
            $loan_person = LoanPerson::find()->select('id_number')->where(['id'=>$order_info->user_id])->limit(1)->one(Yii::$app->get('db_read_2'));
            if(!empty($loan_person)){
                $loan_persons = LoanPerson::find()->select('id')->where(['id_number'=>$loan_person->id_number])->all(Yii::$app->get('db_read_2'));
                $user_ids = [];
                foreach ($loan_persons as $person){
                    $user_ids[] = (string)$person->id;
                }
                $contacts = MgUserMobileContacts::find()->where(['user_id'=>$user_ids])->asArray()->all();
            }
        }
        return $this->render('user-contact-detail',[
            'contacts'=>$contacts
        ]);
    }

    /**
     * @name xybt-工作台-续借建议修改
     * @return string
     * @throws Exception
     */
    public function actionNextLoanAdvice(){
        return LoanCollectionOrderService::nextLoanAdvice($this->request->get());
    }



    /**
     * @name (menu)工作台-牛信坐席电话
     * @return array
     */
    public function actionCallPhone()
    {
        $this->getResponse()->format = Response::FORMAT_JSON;
        $collector_id = Yii::$app->user->identity->getId();
        $phone = trim($this->request->get('phone'));
        $order_id = trim($this->request->get('order_id'));
        if (!$phone) {
            return ['code' => -1, 'message' => 'phone is incorrect'];
        }
        $adminInfo = AdminNxUser::find()
            ->where(['collector_id' => $collector_id, 'status' => AdminNxUser::STATUS_ENABLE, 'type' => AdminNxUser::TYPE_PC])
            ->asArray()
            ->one();
        if (!$adminInfo) {
            return ['code' => -1, 'message' => 'No match to Nioxin account'];
        }

        $callService = new CallStatisticsService();
        $type  = $callService->searchPhoneType($collector_id, $phone);
        $nx_orderid = 'saas'.time().$adminInfo['nx_name'];

        try {
            $nxPhoneLogMod = new NxPhoneLog();
            $nxPhoneLogMod->order_id = $order_id;
            $nxPhoneLogMod->nx_orderid = $nx_orderid;
            $nxPhoneLogMod->collector_id  = $collector_id;
            $nxPhoneLogMod->nx_name  = $adminInfo['nx_name'];
            $nxPhoneLogMod->phone  = $phone;
            $nxPhoneLogMod->type   = $type;
            $nxPhoneLogMod->status = NxPhoneLog::STATUS_NO;
            $nxPhoneLogMod->call_type = NxPhoneLog::CALL_COLLECTION;
            $nxPhoneLogMod->phone_type = CollectorCallData::NIUXIN_PC;
            $nxPhoneLogMod->save();
        } catch (\Exception $e) {
            exit;
        }
        return ['code' => 0, 'orderid' => $nx_orderid];

    }

}

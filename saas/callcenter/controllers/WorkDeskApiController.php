<?php
namespace callcenter\controllers;

use backend\models\Merchant;
use callcenter\models\loan_collection\UserCompany;
use callcenter\models\AdminMessage;
use callcenter\models\AdminNxUser;
use callcenter\models\AdminUser;
use callcenter\models\AppScreenShot;
use callcenter\models\CollectionCheckinLog;
use callcenter\models\CollectorCallData;
use callcenter\models\CompanyTeam;
use callcenter\models\loan_collection\LoanCollectionOrder;
use callcenter\models\loan_collection\LoanCollectionRecord;
use callcenter\models\search\AdminRecordListSearch;
use callcenter\models\search\CollectionOrderListSearch;
use callcenter\models\SmsTemplate;
use callcenter\service\CallStatisticsService;
use callcenter\service\CollectionPublicService;
use callcenter\service\LoanCollectionService;
use callcenter\service\roles\AdminRoleService;
use Carbon\Carbon;
use common\helpers\RedisQueue;
use common\models\enum\Education;
use common\models\enum\Gender;
use common\models\enum\Marital;
use common\models\enum\Relative;
use common\models\GlobalSetting;
use common\models\manual_credit\ManualSecondMobile;
use common\models\message\NxPhoneLog;
use common\models\order\UserLoanOrder;
use common\models\order\UserLoanOrderExternal;
use common\models\order\UserLoanOrderRepayment;
use common\models\product\ProductSetting;
use common\models\user\LoanPerson;
use common\models\user\MgUserMobileContacts;
use common\models\user\UserActiveTime;
use common\services\FileStorageService;
use common\services\GuestService;
use common\services\loan_collection\LoanCollectionOrderService;
use common\services\message\WeWorkService;
use common\services\order\OrderExtraService;
use common\services\order\OrderService;
use common\services\repayment\CustomerReductionService;
use common\services\repayment\ReductionService;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\UploadedFile;
use function GuzzleHttp\Psr7\str;
use Yii;
use yii\data\Pagination;
use yii\base\Exception;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class WorkDeskApiController extends  BaseApiController {
    public $enableCsrfValidation = false;

    /**
     * @name 工作台-我的订单列表
     * @return array|bool
     */
    public function actionGetOrderList(){
        $admin_user_id = Yii::$app->user->id;
        $search = Yii::$app->request->post();
        $search['order_id'] = $search['orderId'] ?? '';
        $search['name'] = $search['customerName'] ?? '';
        $search['loan_phone'] = $search['phone'] ?? '';

        if(yii::$app->user->identity->open_search_label && isset($search['isWillingnessToRepay']) && $search['isWillingnessToRepay']){
            if(isset($search['isRepayment']) && $search['isRepayment']){
                $search['willing_blinker'][] = UserActiveTime::BLUE_COLOR;
            };
            if(isset($search['isConfirmRepayment']) && $search['isConfirmRepayment']){
                $search['willing_blinker'][] = UserActiveTime::RED_COLOR;
            };
            if(isset($search['isJustCall']) && $search['isJustCall']){
                $search['willing_blinker'][] = UserActiveTime::BLACK_COLOR;
            };
            if(isset($search['isEndlessCall']) && $search['isEndlessCall']){
                $search['willing_blinker'][] = UserActiveTime::DAZZLING_COLOR;
            };
            if(isset($search['isTelConnection']) && $search['isTelConnection']){
                $search['willing_blinker'][] = UserActiveTime::GREEN_COLOR;
            };
        }
        $searchForm = new CollectionOrderListSearch();
        $condition = $searchForm->search($search);
        $condition[] = ['A.current_collection_admin_user_id' => $admin_user_id];
        $search['is_summary'] = 1;
        $collection_lists = CollectionPublicService::getCollectionOrderList($condition, $this->merchantIds, $search);

        //申请减免信息
        $reductionService = new ReductionService();
        foreach ($collection_lists['order'] as &$item){
            $loanCollectionOrder = LoanCollectionOrder::findOne($item['id']);
            $item['isCanReduce'] = $reductionService->operateCheck($loanCollectionOrder);
        }

        $list = [];
        foreach ($collection_lists['order'] as $value){
            $arr = UserActiveTime::colorBlinkerShow($value);

            $val = [];
            $val['id'] = $value['id'];
            $val['orderId'] = $value['user_loan_order_id'];
            $val['phone'] = base64_encode($value['phone']);
            $val['money'] = sprintf("%0.2f",($value['total_money']-$value['overdue_fee']-$value['coupon_money'])/100);
            $val['overdueDays'] = $value['overdue_day'];
            $val['overdueFee'] = sprintf("%0.2f",$value['overdue_fee']/100);
            $val['scheduledPaymentAmount'] = sprintf("%0.2f",($value['total_money'] -  $value['true_total_money'] - $value['coupon_money'] - $value['delay_reduce_amount'])/100);
            $val['shouldRepaymentTime'] = empty($value['plan_repayment_time'])?"--":date("Y-m-d",(int)$value['plan_repayment_time']);
            $val['collectionStatus'] = isset(LoanCollectionOrder::$status_list[$value['status']])?LoanCollectionOrder::$status_list[$value['status']]:"";
            $val['repaymentStatus'] = isset(UserLoanOrderRepayment::$repayment_status_map[$value['cuishou_status']])?UserLoanOrderRepayment::$repayment_status_map[$value['cuishou_status']]:"";
            $val['promiseRepaymentTime'] = $value['promise_repayment_time'] ? date('Y-m-d H:i',$value['promise_repayment_time']) : '-';
            $val['finishTime'] = empty($value['closing_time'])?"--":date("Y-m-d H:i",$value['closing_time']);
            $val['isCanCollection'] = in_array($value['status'],LoanCollectionOrder::$collection_status);
            $val['isCanReduce'] = $value['isCanReduce'] == 1 ? true : false;
            $val['isCanCall'] = in_array($value['status'],LoanCollectionOrder::$end_status) ? false : true;
            $val['isRepayment'] = isset($arr[UserActiveTime::BLUE_COLOR]) ? true : false;
            $val['isConfirmRepayment'] = isset($arr[UserActiveTime::RED_COLOR]) ? true : false;
            $val['isJustCall'] = isset($arr[UserActiveTime::BLACK_COLOR]) ? true : false;
            $val['isEndlessCall'] = isset($arr[UserActiveTime::DAZZLING_COLOR]) ? true : false;
            $val['isTelConnection'] = isset($arr[UserActiveTime::GREEN_COLOR]) ? true : false;
            $list[] = $val;
        }

        $returnCode = CollectionCheckinLog::checkStartWork(yii::$app->user->id) ? 0 : -4;

        return [
            'code' => $returnCode,
            'data' => [
                'totalPage' => $collection_lists['page']->getPageCount(),
                'list' => ArrayHelper::htmlEncode($list),
                'isShowUserRepaymentIntentionSelectGroup' => yii::$app->user->identity->open_search_label ? true : false
            ],
            'message' => 'success'
        ];
    }

    /**
     * @name 工作台订单-详情-basicInfo
     * @return array
     */
    public function actionGetBasicInfo()
    {
        try{
            $operator_id = Yii::$app->user->id;     //当前催收人ID
            $orderId = intval(Yii::$app->request->post('id', 0));
            /** @var LoanCollectionOrder $order */
            $order = LoanCollectionOrder::find()->where([
                'id'=>$orderId ,
                'merchant_id' => $this->merchantIds
            ])->one();
            if($order['current_collection_admin_user_id'] == 0){
                throw new NotFoundHttpException('The order cannot be processed, please refresh');
            }
            if(!in_array($order['status'],LoanCollectionOrder::$collection_status)){
                throw new NotFoundHttpException('The order status can not be processed, The order may have been completed, please refresh');
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
            $orderExtraService = new OrderExtraService($userLoanOrder);
            $loanPerson = LoanPerson::findOne($order->user_id);
            $personInfo['userWorkInfos'] = $orderExtraService->getUserWorkInfo() ?? null;
            $personInfo['userBasicInfo'] = $orderExtraService->getUserBasicInfo() ?? null;
            $personInfo['permanentAddress'] = $orderExtraService->getUserOcrAadhaarReport()->address ?? null;
            $personInfo['loanPerson'] = $loanPerson;
            $orderService = new OrderService($userLoanOrder);
            $delayData = $orderService->checkDelayStatus();
            $extendData = $orderService->checkExtendStatus();
        }catch(Exception $e){
            return ['code' => -1,'data' => [],'message' => $e->getMessage()];
        }

        $AppUrl = '';
        $fromApp = $userLoanOrder->clientInfoLog['package_name'] ?? '--';
        if($userLoanOrder->is_export == UserLoanOrder::IS_EXPORT_YES){
            $userLoanOrder->clientInfoLog['package_name'];
            $sourceFrom = explode('_', $userLoanOrder->clientInfoLog['app_market'])[1] ?? '--';
            $productName = ProductSetting::getLoanExportProductName($fromApp, $sourceFrom);
            $fromApp = $sourceFrom;
            $AppUrl = Yii::$app->params['appUrl'][$sourceFrom] ?? '';
        }else{
            $productName = $userLoanOrder->productSetting['product_name'] ?? '--';
        }
        $basicInformation = [
            'name' => $personInfo['loanPerson']['name'],
            'phone' => $personInfo['loanPerson']['phone'],
            'gender' => Gender::$map[$personInfo['loanPerson']['gender']] ?? '-',
            'birthday' => $personInfo['loanPerson']['birthday'] ?? '-',
            'educated' => Education::$map[$personInfo['userWorkInfos']['educated']] ?? '-',
            'marital' => Marital::$map[$personInfo['userBasicInfo']['marital_status']] ?? '-',
            'residentialAddress' => Html::encode(($personInfo['userWorkInfos']['residential_address1'] ?? '-').($personInfo['userWorkInfos']['residential_address2'] ?? '-')),
            'loanProduct' => $productName,
            'fromApp' => $fromApp,
            'permanentAddress' => Html::encode($personInfo['permanentAddress'] ?? '--'),
            'applyRelief' => false
        ];
        if(!empty($AppUrl)){
            $basicInformation['AppUrl'] = $AppUrl;
        }


//        $customerReductionService = new CustomerReductionService();
//        $basicInformation['applyRelief'] = $customerReductionService->operateApplyCheck($order,$operator_id);
        if($delayData['delaySwitch']){
            $basicInformation['minimumAmountForApplyingPartialDeferral'] = $delayData['delayMoney'];
        }
        if($extendData['extendSwitch']){
            $basicInformation['minimumAmountForApplyingExtend'] = $extendData['extendMoney'];
        }

        $returnCode = CollectionCheckinLog::checkStartWork(yii::$app->user->id) ? 0 : -4;

        return  [
            'code' => $returnCode,
            'data' => ArrayHelper::htmlEncode($basicInformation),
            'message' => 'success'
        ];
    }

    /**
     * @name 工作台-催收详情-repaymentInfo
     * @return array
     */
    public function actionGetRepaymentInfo()
    {
        try{
            $operator_id = Yii::$app->user->id;     //当前催收人ID
            $orderId = intval(Yii::$app->request->post('id', 0));
            /** @var LoanCollectionOrder $order */
            $order = LoanCollectionOrder::find()->where([
                'id'=>$orderId ,
                'merchant_id' => $this->merchantIds
            ])->one();
            if($order['current_collection_admin_user_id'] == 0){
                throw new NotFoundHttpException('The order cannot be processed, please refresh');
            }
            if(!in_array($order['status'],LoanCollectionOrder::$collection_status)){
                throw new NotFoundHttpException('The order status can not be processed, The order may have been completed, please refresh');
            }
            if($operator_id != $order['current_collection_admin_user_id']){
                $loanCollectionService = new LoanCollectionService($operator_id);
                $res = $loanCollectionService->checkCanOperatedCollector($order['current_collection_admin_user_id']);
                if(!$res){
                    throw new NotFoundHttpException('The order cannot be processed');
                }
            }
        }catch(Exception $e){
            return ['code' => -1,'data' => [],'message' => $e->getMessage()];
        }

        //生成还款链接
        $userLoanOrder = UserLoanOrder::findOne($order->user_loan_order_id);
        $guestService = new GuestService();
        $paymentLink = $guestService->generatePaymentLink($userLoanOrder);

        $repayInfo = LoanCollectionOrderService::getULOR($order);
        $repayInfo = $repayInfo['repayInfo'];
        $repaymentInformation = [
            'loanOrderId' => $repayInfo['order_id'],
            'principal' => sprintf("%0.2f",$repayInfo['amount']/100),
            'overdueFee' => sprintf("%0.2f",$repayInfo['overdue_fee']/100),
            'totalMoney' => sprintf("%0.2f",$repayInfo['total_money']/100),
            'overdueDay' => $repayInfo['overdue_day'],
            'interests' => sprintf("%0.2f",$repayInfo['interests']/100),
            'repaidAmount' => sprintf("%0.2f",$repayInfo['true_total_money']/100),
            'loanTime' => $repayInfo['loan_time'],
            'costFee' => sprintf("%0.2f",$repayInfo['cost_fee']/100),
            'expireTimeOfRepay' => $repayInfo['plan_repayment_time'],
            'couponMoney' => sprintf("%0.2f",$repayInfo['coupon_money']/100),
            'delayReduceMoney' => sprintf("%0.2f",$repayInfo['delay_reduce_amount']/100),
            'remainAmount' => sprintf("%0.2f",$repayInfo['surplus_money']/100),
            'paymentStatus' => $repayInfo['ulor_status'],
            'paymentLink' => $paymentLink
        ];

        $returnCode = CollectionCheckinLog::checkStartWork(yii::$app->user->id) ? 0 : -4;

        return  [
            'code' => $returnCode,
            'data' => ArrayHelper::htmlEncode($repaymentInformation),
            'message' => 'success'
        ];
    }


    /**
     * @name 工作台-催收详情-contacts
     * @return array
     */
    public function actionGetContacts()
    {
        try{
            $operator_id = Yii::$app->user->id;     //当前催收人ID
            $orderId = intval(Yii::$app->request->post('id', 0));
            /** @var LoanCollectionOrder $order */
            $order = LoanCollectionOrder::find()->where([
                'id'=>$orderId ,
                'merchant_id' => $this->merchantIds
            ])->one();

            if($order['current_collection_admin_user_id'] == 0){
                throw new NotFoundHttpException('The order cannot be processed, please refresh');
            }
            if(!in_array($order['status'],LoanCollectionOrder::$collection_status)){
                throw new NotFoundHttpException('The order status can not be processed, The order may have been completed, please refresh');
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
            $loanPerson = LoanPerson::findOne($order->user_id);

            //本人
            $personContact = [
                [
                    'name' => Html::encode($loanPerson['name']),
                    'relation' => 'oneself',
                    'phone' => Html::encode($loanPerson['phone']),
                ]
            ];

            if($userLoanOrder->merchant->is_hidden_contacts == Merchant::NOT_HIDDEN) {
                //本人第二手机
                /** @var ManualSecondMobile $manualSecondMobile */
                $manualSecondMobile = ManualSecondMobile::find()->where(['order_id' => $userLoanOrder->id])->one();
                if ($manualSecondMobile) {
                    $personContact[] = [
                        'name'     => Html::encode($loanPerson['name'] . '(second mobile)'),
                        'relation' => 'oneself',
                        'phone'    => Html::encode($manualSecondMobile->mobile),
                    ];
                }

                $urgentContact = $userLoanOrder->userContact;
                if (!empty($urgentContact->phone)) {
                    $phoneArr = explode(':', $urgentContact->phone);
                    foreach ($phoneArr as $phone) {
                        $personContact[] = [
                            'name'     => Html::encode($urgentContact->name),
                            'relation' => Relative::$map[$urgentContact->relative_contact_person],
                            'phone'    => Html::encode($phone),
                        ];
                    }
                }
                if (!empty($urgentContact->other_phone)) {
                    $phoneArr = explode(':', $urgentContact->other_phone);
                    foreach ($phoneArr as $phone) {
                        $personContact[] = [
                            //其他联系人
                            'name'     => Html::encode($urgentContact->other_name),
                            'relation' => Relative::$map[$urgentContact->other_relative_contact_person],
                            'phone'    => Html::encode($phone),
                        ];
                    }
                }
            }

        }catch(Exception $e){
            return ['code' => -1,'data' => [],'message' => $e->getMessage()];
        }

        $returnCode = CollectionCheckinLog::checkStartWork(yii::$app->user->id) ? 0 : -4;

        return  [
            'code' => $returnCode,
            'data' => ArrayHelper::htmlEncode($personContact),
            'message' => 'success'
        ];
    }


    /**
     * @name 工作台-催收详情-collectionRecords
     * @return array
     */
    public function actionGetCollectionRecords()
    {
        try{
            $operator_id = Yii::$app->user->id;     //当前催收人ID
            $orderId = intval(Yii::$app->request->post('id', 0));
            /** @var LoanCollectionOrder $order */
            $order = LoanCollectionOrder::find()->where([
                'id'=>$orderId ,
                'merchant_id' => $this->merchantIds
            ])->one();
            if($order['current_collection_admin_user_id'] == 0){
                throw new NotFoundHttpException('The order cannot be processed, please refresh');
            }
            if(!in_array($order['status'],LoanCollectionOrder::$collection_status)){
                throw new NotFoundHttpException('The order status can not be processed, The order may have been completed, please refresh');
            }
            if($operator_id != $order['current_collection_admin_user_id']){
                $loanCollectionService = new LoanCollectionService($operator_id);
                $res = $loanCollectionService->checkCanOperatedCollector($order['current_collection_admin_user_id']);
                if(!$res){
                    throw new NotFoundHttpException('The order cannot be processed');
                }
            }

            $search = $this->request->post();
            unset($search['id']);
            $search['order_id'] = $order->id;
            $searchForm = new AdminRecordListSearch();
            $condition = $searchForm->search($search);
            $query = LoanCollectionRecord::find()
                ->select([
                    'A.id',
                    'contacts' => 'A.contact_name',
                    'A.relation',
                    'phone' => 'A.contact_phone',
                    'orderLevel' => 'A.order_level',
                    'operateType' => 'A.operate_type',
                    'promiseRepayTime' => 'A.promise_repayment_time',
                    'contactStatus' => 'A.is_connect',
                    'contactResult' => 'A.risk_control',
                    'A.remark',
                    'collectionStatus' => 'A.order_state',
                    'collectionTime' => 'A.operate_at',
                    'nameOfCollector' => 'B.username',
                ])
                ->from(LoanCollectionRecord::tableName() . ' A')
                ->leftJoin(AdminUser::tableName() . ' B','A.operator = B.id');

            foreach ($condition as $item)
            {
                $query->andFilterWhere($item);
            }

            $totalQuery = clone $query;
            $pages = new Pagination(['totalCount' => $totalQuery->count('A.id',LoanCollectionOrder::getDb_rd())]);
            $pages->page = Yii::$app->request->post('page', 1) - 1;
            $pages->pageSize = Yii::$app->request->post('per-page', 15);
            $loan_collection_record_list = $query
                ->offset($pages->offset)
                ->limit($pages->limit)
                ->orderBy(["A.id" => SORT_DESC])
                ->asArray()
                ->all();
            $loan_collection_record = [];

            foreach ($loan_collection_record_list as $key => $value)
            {
                $arr = [];
                $arr['id'] = $value['id'];
                $arr['contacts'] = $value['contacts'];
                $arr['relation'] = $value['relation'];
                $arr['phone'] = $value['phone'];
                $arr['orderLevel'] = isset(LoanCollectionOrder::$level[$value['orderLevel']])?LoanCollectionOrder::$level[$value['orderLevel']]:"--";
                $arr['operateType'] = isset(LoanCollectionRecord::$label_operate_type[$value['operateType']])?LoanCollectionRecord::$label_operate_type[$value['operateType']]:"--";
                $arr['promiseRepayTime'] = empty($value['promiseRepayTime']) ? '--' : date('Y-m-d H:i:s',$value['promiseRepayTime']);
                $arr['contactStatus'] = !empty($value['contactStatus'])?LoanCollectionRecord::$is_connect[$value['contactStatus']]:"--";
                $arr['contactResult'] = !empty($value['contactResult'])?LoanCollectionRecord::$risk_controls[$value['contactResult']]:"--";
                $arr['remark'] = $value['remark'];
                $arr['collectionStatus'] = isset(LoanCollectionOrder::$status_list[$value['collectionStatus']])?LoanCollectionOrder::$status_list[$value['collectionStatus']]:"--";
                $arr['collectionTime'] = !empty($value['collectionTime'])?date("Y-m-d H:i:s",$value['collectionTime']):"--";
                $loan_collection_record[] = $arr;
            }
        }catch(Exception $e){
            return ['code' => -1,'data' => [],'message' => $e->getMessage()];
        }

        $returnCode = CollectionCheckinLog::checkStartWork(yii::$app->user->id) ? 0 : -4;

        return  [
            'code' => $returnCode,
            'data' => [
                'totalPage' => $pages->getPageCount(),
                'list' => ArrayHelper::htmlEncode($loan_collection_record)
            ],
            'message' => 'success'
        ];
    }

    /**
     * @name 工作台-催收详情-addressBooks
     * @return array
     */
    public function actionGetAddressBooks()
    {
        try{
            $operator_id = Yii::$app->user->id;     //当前催收人ID
            $orderId = intval(Yii::$app->request->post('id', 0));
            /** @var LoanCollectionOrder $order */
            $order = LoanCollectionOrder::find()->where([
                'id'=>$orderId ,
                'merchant_id' => $this->merchantIds
            ])->one();

            $personId = $order->user_id;   //借款人ID
            if($order['current_collection_admin_user_id'] == 0){
                throw new NotFoundHttpException('The order cannot be processed, please refresh');
            }
            if(!in_array($order['status'],LoanCollectionOrder::$collection_status)){
                throw new NotFoundHttpException('The order status can not be processed, The order may have been completed, please refresh');
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

            if($userLoanOrder->merchant->is_hidden_address_book == Merchant::NOT_HIDDEN) {
                $db = null;
                if ($userLoanOrder->is_export == UserLoanOrder::IS_EXPORT_YES) {
                    $db = MgUserMobileContacts::getLoanDb();
                    /** @var UserLoanOrderExternal $externalOrder */
                    $externalOrder = UserLoanOrderExternal::find()->where(['order_uuid' => $userLoanOrder->order_uuid])->one();
                    $personId      = $externalOrder->user_id;
                }
                $query     = MgUserMobileContacts::find()
                    ->where(['user_id' => $personId]);
                $searchVal = trim(Yii::$app->request->post('searchVal', ''));
                if ($searchVal) {
                    if (is_numeric($searchVal)) {
                        $query->andWhere(['like', 'mobile', $searchVal]);
                    } else {
                        $query->andWhere(['like', 'name', $searchVal]);
                    }
                }
                $totalQuery      = clone $query;
                $pages           = new Pagination(['totalCount' => $totalQuery->count('_id', $db)]);
                $pages->page     = Yii::$app->request->post('page', 1) - 1;
                $pages->pageSize = Yii::$app->request->post('per-page', 15);
                $mobileContacts  = $query
                    ->offset($pages->offset)
                    ->limit($pages->limit)
                    ->asArray()
                    ->all($db);


                $mobileContactsList = [];

                foreach ($mobileContacts as $key => $value) {
                    $arr                  = [];
                    $arr['id']            = $value['_id'];
                    $arr['name']          = Html::encode($value['name']);
                    $arr['phone']         = Html::encode($value['mobile']);
                    $mobileContactsList[] = $arr;
                }
            }else{
                $pages = new Pagination(['totalCount' => 0]);
                $mobileContactsList = [];
            }
        }catch(Exception $e){
            return ['code' => -1,'data' => [],'message' => $e->getMessage()];
        }

        $returnCode = CollectionCheckinLog::checkStartWork(yii::$app->user->id) ? 0 : -4;

        return  [
            'code' => $returnCode,
            'data' => [
                'totalPage' => $pages->getPageCount(),
                'list' => ArrayHelper::htmlEncode($mobileContactsList)
            ],
            'message' => 'success'
        ];
    }

    /**
     * @name 电话催收
     * @return array
     */
    public function actionSubmitCollectPhone()
    {
        $adminId = Yii::$app->user->id;
        if(empty($adminId))
        {
            return  [ 'code' => -2, 'message' => 'Please login'];
        }
        //获取参数
        $orderId = intval(Yii::$app->request->post('id', 0));
        /** @var LoanCollectionOrder $order */
        $order = LoanCollectionOrder::find()->where([
            'id'=>$orderId ,
            'merchant_id' => $this->merchantIds
        ])->one();
        if(empty($order))
        {
            yii::error(Yii::$app->request->post(), 'collection_app_phone');
            return [ 'code' => -1, 'message' => 'Order not found 1' ];
        }
        $loanCollectionService = new LoanCollectionService($adminId);
        $params['operate_type'] = LoanCollectionRecord::OPERATE_TYPE_CALL;
        $params['remark'] = Yii::$app->request->post('remark','');
        $params['cuishoutype'] = Yii::$app->request->post('fromType','');
        $params['is_connect'] = Yii::$app->request->post('connectStatus'); //1 已联系，  2 未联系
        //1 promised payment;2 want to repay;3 insolvency;4 reject repayment
        //11 no answer;12 shutdown or null
        $params['risk_control'] = Yii::$app->request->post('collectionResult');
        $params['promise_repayment_time'] = Yii::$app->request->post('promiseRepayTime','');
        $phone = Yii::$app->request->post('phone','');
        $params['contact_phone'] = [$phone];
        $params['user_amount'] = Yii::$app->request->post('amount', '');
        $params['user_utr'] = Yii::$app->request->post('utr', '');
        $res = $loanCollectionService->collect($order, $params,$this->merchantIds);

        $returnCode = CollectionCheckinLog::checkStartWork(yii::$app->user->id) ? 0 : -4;

        return [ 'code' => $res['code'] == 0 ? $returnCode : $res['code'], 'message' => $res['message'] ];
    }

    /**
     * @name 短信催收模板
     * @return array
     */
    public function actionGetSmsTemplate()
    {
        $adminId = Yii::$app->user->id;
        if(empty($adminId))
        {
            return  [ 'code' => -2, 'message' => 'Please login'];
        }
        //获取参数
        $orderId = intval(Yii::$app->request->post('id', 0));
        /** @var LoanCollectionOrder $order */
        $order = LoanCollectionOrder::find()->where([
            'id'=>$orderId ,
            'merchant_id' => $this->merchantIds
        ])->one();
        if(empty($order))
        {
            yii::error([
                'id'=>$orderId ,
                'merchant_id' => $this->merchantIds,
                'admin' => $adminId], 'collection_app_phone');
            return [ 'code' => -1, 'message' => 'Order not found 2' ];
        }
        $smsTemplateList = SmsTemplate::getTemplateList($order,$this->merchantIds);
        $templateList = [];
        foreach ($smsTemplateList['name'] as $id => $val){
            $arr['id'] = $id;
            $arr['name'] = $val;
            $arr['template'] = $smsTemplateList['content'][$id];
            $templateList[] = $arr;
        }

        $returnCode = CollectionCheckinLog::checkStartWork(yii::$app->user->id) ? 0 : -4;
        return [ 'code' => $returnCode,'data' => ArrayHelper::htmlEncode($templateList), 'message' => 'success' ];

    }

    /**
     * @name 短信催收
     * @return array
     */
    public function actionSubmitCollectSms()
    {
        $adminId = Yii::$app->user->id;
        if(empty($adminId))
        {
            return  [ 'code' => -2, 'message' => 'Please login'];
        }
        //获取参数
        $orderId = intval(Yii::$app->request->post('id', 0));
        /** @var LoanCollectionOrder $order */
        $order = LoanCollectionOrder::find()->where([
            'id'=>$orderId ,
            'merchant_id' => $this->merchantIds
        ])->one();
        if(empty($order))
        {
            yii::error([
                'id'=>$orderId ,
                'merchant_id' => $this->merchantIds,
                'admin' => $adminId], 'collection_app_phone');
            return [ 'code' => -1, 'message' => 'Order not found 3' ];
        }
        $loanCollectionService = new LoanCollectionService($adminId);
        $params['operate_type'] = LoanCollectionRecord::OPERATE_TYPE_SMS;
        $params['remark'] = Yii::$app->request->post('remark','');
        $params['template_id'] = Yii::$app->request->post('smsId',0);
        $res = $loanCollectionService->collect($order, $params,$this->merchantIds);

        $returnCode = CollectionCheckinLog::checkStartWork(yii::$app->user->id) ? 0 : -4;

        return [ 'code' => $res['code'] == 0 ? $returnCode : $res['code'],'data' => [], 'message' => $res['message'] ];
    }

    /**
     * @name 工作台-续借建议修改
     * @return array
     * @throws Exception
     */
    public function actionNextLoanAdvice(){
        $res =  LoanCollectionOrderService::nextLoanAdvice($this->request->post());
        return ArrayHelper::htmlEncode(json_decode($res, true));
    }


    /**
     * @name 减免-申请
     */
    public function actionApplyReduce(){
        $this->response->format = Response::FORMAT_JSON;
        $admin_id = \Yii::$app->user->identity->getId();
        $id = $this->request->post('id',0);
        /** @var LoanCollectionOrder $loanCollectionOrder */
        $loanCollectionOrder = LoanCollectionOrder::find()->where(['id' => $id, 'merchant_id'=>$this->merchantIds])->one();
        $reductionService = new ReductionService();

        $returnCode = CollectionCheckinLog::checkStartWork(yii::$app->user->id) ? 0 : -4;

        if($this->request->getIsPost()){
            $reduceRemark = $this->request->post('remark','');
            $res = $reductionService->collectionApplyReduce($loanCollectionOrder,$admin_id,$reduceRemark);
            if($res){
                if(YII_ENV_PROD){
                    $service = new WeWorkService();
                    $message = '有减免催收订单的申请需要处理，催收订单ID:'.$loanCollectionOrder->id.',申请人：'.\Yii::$app->user->identity->username;
                    $service->sendText(['yanzhenlin','xionghuakun','zhufangqi'],$message);
                }
                return ['code' => $returnCode,'message' => 'success'];
            }else{
                return ['code' => -1,'message' => $reductionService->getError()];
            }
        }else{
            return ['code' => -1,'message' => 'need post'];
        }
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
        /** @var CollectionCheckinLog $startWork */
        $startWork = CollectionCheckinLog::find()
            ->where([
                'user_id' => yii::$app->user->getId(),
                'type' => CollectionCheckinLog::TYPE_START_WORK,
                'date' => date('Y-m-d')
            ])
            ->limit(1)->one();
        if(!is_null($startWork))
        {
            $data['startWorkTime'] = date('H:i', $startWork->created_at);
        }

        /** @var CollectionCheckinLog $startWork */
        $offWork = CollectionCheckinLog::find()
            ->where([
                'user_id' => yii::$app->user->getId(),
                'type' => CollectionCheckinLog::TYPE_OFF_WORK,
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
        $addressType = Yii::$app->request->post('addressType',CollectionCheckinLog::TYPE_DEFAULT);
        if(!in_array($type, array_values(CollectionCheckinLog::$type_map)))
        {
            return [
                'code' => -1,
                'message' => 'type parameter err'
            ];
        }
        if(!isset(CollectionCheckinLog::$address_type_map[$addressType]))
        {
            return [
                'code' => -1,
                'message' => 'address type parameter err'
            ];
        }
        $userID = yii::$app->user->id;
        $date = date('Y-m-d');
        $redisKey = "collection_app:{$type}_{$userID}_{$date}";
        if(CollectionCheckinLog::$type_map[CollectionCheckinLog::TYPE_START_WORK] == $type && !RedisQueue::lock($redisKey, 86500))
        {
            /** @var CollectionCheckinLog $log */
            $log = CollectionCheckinLog::find()->where(['type' => CollectionCheckinLog::TYPE_START_WORK, 'date' => $date, 'user_id' => $userID])->orderBy(['id' => SORT_ASC])->one();
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
        if(CollectionCheckinLog::$type_map[CollectionCheckinLog::TYPE_OFF_WORK] == $type){
            /** @var CollectionCheckinLog $log */
            $log = CollectionCheckinLog::find()->where(['type' => CollectionCheckinLog::TYPE_START_WORK, 'user_id' => $userID])->orderBy(['id' => SORT_DESC])->one();
            $addressType = $log->address_type;
        }

        $log = new CollectionCheckinLog();
        $log->user_id = $userID;
        $log->type = array_flip(CollectionCheckinLog::$type_map)[$type];
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
     * 催收开启客户app主动减免信息提交-申请
     * @return array
     */
    public function actionApplyRelief(){
        $id = intval(Yii::$app->request->post('id'));
        $reduceRemark = intval(Yii::$app->request->post('remark'));
        $adminId = \Yii::$app->user->identity->getId();
        $loanCollectionOrder = LoanCollectionOrder::findOne($id);
        $reductionService = new CustomerReductionService();
        if($this->request->getIsPost()){
            $res = $reductionService->collectionApplyOpenAppReduction($loanCollectionOrder,$adminId,$reduceRemark);
            if($res){
                if(YII_ENV_PROD){
                    $service = new WeWorkService();
                    $message = '催收员开启客户app可提交减免信息申请，催收订单ID:'.$loanCollectionOrder->id.',申请人：'.\Yii::$app->user->identity->username;
                    $service->sendText(['yanzhenlin'],$message);
                }
                $returnCode = CollectionCheckinLog::checkStartWork($adminId) ? 0 : -4;
                return  [
                    'code' => $returnCode,
                    'data' => null,
                    'message' => 'success'
                ];
            }else{
                return  [
                    'code' => -1,
                    'data' => null,
                    'message' => $reductionService->getError()
                ];
            }
        }
        return  [
            'code' => -1,
            'data' => null,
            'message' => 'success'
        ];
    }


    /**
     * @name (menu)工作台-牛信坐席电话
     * @return array
     */
    public function actionCallPhone()
    {
        $this->getResponse()->format = Response::FORMAT_JSON;
        $phone = trim($this->request->post('phone'));
        $order_id = trim($this->request->post('orderId'));
        $isSdk = $this->request->post(('isSdk'));
        $phone_type = $isSdk == true ? AdminNxUser::TYPE_SDK : AdminNxUser::TYPE_ANDROID;
        $call_phone_type = $isSdk == true ? CollectorCallData::NIUXIN_SDK : CollectorCallData::NIUXIN_APP;
        $collector_id = Yii::$app->user->identity->getId();
        if (!$phone) {
            return ['code' => -1,'data'=>null, 'message' => 'phone is incorrect'];
        }
        $adminInfo = AdminNxUser::find()
            ->where(['collector_id' => $collector_id, 'status' => AdminNxUser::STATUS_ENABLE, 'type' => $phone_type])
            ->asArray()
            ->one();

        if (!$adminInfo) {
            return ['code' => -1, 'data'=>null, 'message' => 'No match to Nioxin account'];
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
            'data'    => [
                'items' =>
                    [
                        [
                            'title' => Html::encode($app_nx_name),
                        ],
                        [
                            'title' => 'My Profile',
                            'jump' => ['path'=>'/h5/webview', 'url'=> Yii::$app->getRequest()->getHostInfo().'/h5/#/myProfile']
                        ],
                        [
                        'title' => 'Electronic work card',
                        'jump' => ['path'=>'/h5/webview', 'url'=> Yii::$app->getRequest()->getHostInfo().'/h5/#/workpass']
                        ],
                        [
                            'title' => 'My Income',
                            'jump' => ['path'=>'/h5/webview', 'url'=> Yii::$app->getRequest()->getHostInfo().'/h5/#/income']
                        ],
                        [
                            'title' => 'My Ranking',
                            'jump' => ['path'=>'/h5/webview', 'url'=> Yii::$app->getRequest()->getHostInfo().'/h5/#/ranking']
                        ],
                    ]
            ],
            'message' => 'success'
        ];
    }

    /**
     * @name WorkDeskApiController 上报截屏
     * @return array
     */
    public function actionUploadScreenshots(){
        $user_id = yii::$app->user->getId();
        $appScreenShot = new AppScreenShot();
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

    /**
     * @name 我的团队
     * @return array
     */
    public function actionGetMyTeam()
    {
        /**
         * @var AdminUser $user
         */
        $user = yii::$app->user->identity;

        $service = new AdminRoleService($user);

        $result = $service->setOrganization();
        if($result){
            return [
                'code'    => 0,
                'message' => 'success',
                'data'    => ArrayHelper::htmlEncode($result),
            ];
        }else{
            return [
                'code'    => -1,
                'message' => 'User role error!',
                'data'    => [],
            ];
        }
    }

    /**
     * @name 获取管理页面信息
     * @return array
     */
    public function actionGetManageList(){
        $userId = yii::$app->user->getId();

        RedisQueue::delSet(RedisQueue::COLLECTION_NEW_MESSAGE_TEAM_TL_UID,$userId);
        $query = AdminMessage::find()->where(['admin_id' => $userId]);

        $totalQuery = clone $query;
        $pages = new Pagination(['totalCount' => $totalQuery->count('id')]);
        $pages->page = Yii::$app->request->post('page', 1) - 1;
        $pages->pageSize = Yii::$app->request->post('pageSize', 10);

        $result = [];
        $list = $query->offset($pages->offset)->limit($pages->limit)->orderBy(['id' => SORT_DESC])->asArray()->all();

        foreach ($list as $item){
            $result[] = ['id' => $item['id'],'date' => date('Y-m-d',$item['created_at']),'content' => $item['content'],'isRead' => ($item['status'] == AdminMessage::STATUS_READ)];
        }

        $data = ['totalPage' => $pages->getPageCount(), 'list' => ArrayHelper::htmlEncode($result)];
        return [
            'code'    => 0,
            'message' => 'success',
            'data'    => $data,
        ];
    }

    /**
     * @name 管理页面的消息已读操作
     * @return array
     */
    public function actionReadManage(){
        $id = Yii::$app->request->post('id', 0);
        $userId = yii::$app->user->getId();
        RedisQueue::delSet(RedisQueue::COLLECTION_NEW_MESSAGE_TEAM_TL_UID,$userId);
        /** @var AdminMessage $model */
        $model = AdminMessage::find()->where(['admin_id' => $userId,'id' => $id])->one();
        if($model){
            if($model->status == AdminMessage::STATUS_NEW){
                $model->status = AdminMessage::STATUS_READ;
                $model->save();
            }
            return[
                'code'    => 0,
                'message' => 'success'
            ];
        }else{
            return[
                'code'    => -1,
                'message' => 'message no exist'
            ];
        }
    }

    /**
     * @name self My Profile
     * @return array
     */
    public function actionMyProfile()
    {
        $adminId = yii::$app->user->id;
        if (empty($adminId)) {
            return ['code' => -2, 'message' => 'Please login'];
        }
        $adminUser = AdminUser::findOne($adminId);
        if (empty($adminUser->job_number)){
            return ['code' => -1, 'message' => 'You don\'t have a job number.'];
        }

        $url = 'https://api-collection.smallflyelephantsaas.com/collection/collector-info';
        $params['job_number'] = $adminUser->job_number;
        $client = new Client([
            RequestOptions::TIMEOUT => 60,
            RequestOptions::HEADERS => [
            ],
        ]);
        $response = $client->request('POST', $url, [
            RequestOptions::JSON => $params
        ]);
        $result = 200 == $response->getStatusCode() ? json_decode($response->getBody()->getContents(), true) : [];

        if (!$result) {
            return ['code' => -1, 'message' => 'No employee information is configured.'];
        }

        $data = array(
            'name' => $result['data']['name'] ?? null,
            'jobNumber' => $result['data']['jobNumber'] ?? null,
            'phoneNumber' => $result['data']['phoneNumber'] ?? null,
            'gender' => $result['data']['gender'] ?? null,
            'company' => $result['data']['company'] ?? null,
            'bankAccount' => $result['data']['bankAccount'] ?? null,
            'beneficiaryName' => $result['data']['beneficiaryName'] ?? null,
            'ifscCode' => $result['data']['ifscCode'] ?? null,
            'status' => $result['data']['status'] ?? null,
        );

        return ['code' => 0, 'data' => $data, 'message' => 'success'];
    }

    /**
     * @name self 电子工卡
     * @return array
     */
    public function actionElectronicWorkCard()
    {
        $adminId = yii::$app->user->id;
        if (empty($adminId)) {
            return ['code' => -2, 'message' => 'Please login'];
        }
        $adminUser = AdminUser::findOne($adminId);
        if (empty($adminUser->job_number)){
            return ['code' => -1, 'message' => 'You don\'t have a job number.'];
        }

        //$url = 'https://pre-api-collection.smallflyelephantsaas.com/collection/electronic-work-card';
        $url = 'https://api-collection.smallflyelephantsaas.com/collection/electronic-work-card';
        //$url = 'https://test1-api-collection.smallflyelephantsaas.com:9081/collection/electronic-work-card';
        $params['job_number'] = $adminUser->job_number;
        $client = new Client([
            RequestOptions::TIMEOUT => 60,
            RequestOptions::HEADERS => [
            ],
        ]);
        $response = $client->request('POST', $url, [
            RequestOptions::JSON => $params
        ]);
        $result = 200 == $response->getStatusCode() ? json_decode($response->getBody()->getContents(), true) : [];

        if (!$result) {
            return ['code' => -1, 'message' => 'No employee information is configured.'];
        }

        $allCompany = UserCompany::outsideRealName($this->merchantIds);
        $teamList = CompanyTeam::getTeamsByOutside($adminUser->outside);
        $office[] = 'saas-'. ($allCompany[$adminUser->outside] ?? '') .'-'. (LoanCollectionOrder::$level[$adminUser->group] ?? '') .'-'. ($teamList[$adminUser->group_game] ?? '');
        $data = array(
            'lastUpdateTime' => date('Y-m-d H:i:s',time()),
            'avatar' => $result['data']['avatar'] ?? '',
            'number' => $result['data']['number'] ?? '',
            'name' => $result['data']['name'] ?? '',
            'phone' => $result['data']['phone'] ?? '',
            'city' => $result['data']['city'] ?? '',
            'sex' => $result['data']['sex'] ?? '',
            'office' => $office
        );

        return ['code' => 0, 'data' => $data, 'message' => 'success'];
    }

    /**
     * @name self 电子工卡-上传头像
     * @return array
     */
    public function actionAvatarUpload()
    {
        $adminId = yii::$app->user->id;
        if (empty($adminId)) {
            return ['code' => -2, 'message' => 'Please login'];
        }
        $adminUser = AdminUser::findOne($adminId);
        if (empty($adminUser->job_number)){
            return ['code' => -1, 'message' => 'You don\'t have a job number.'];
        }
        $avatar = UploadedFile::getInstanceByName('avatar');
        $s3_service = new FileStorageService();
        $avatar_url = $s3_service->uploadFile(
            'collector/avatar',
            $avatar->tempName,
            $avatar->getExtension(),
            true
        );
        $params = array(
            'job_number' => $adminUser->job_number,
            'url' => $s3_service->getSignedUrl($avatar_url,7*86400),
        );
        //$url = 'https://pre-api-collection.smallflyelephantsaas.com/collection/avatar-upload';
        $url = 'https://api-collection.smallflyelephantsaas.com/collection/avatar-upload';
        //$url = 'https://test1-api-collection.smallflyelephantsaas.com:9081/collection/avatar-upload';
        $client = new Client([
            RequestOptions::TIMEOUT => 60,
            RequestOptions::HEADERS => [
            ],
        ]);
        $response = $client->request('POST', $url, [
            RequestOptions::JSON => $params
        ]);
        $result = 200 == $response->getStatusCode() ? json_decode($response->getBody()->getContents(), true) : [];

        if (!$result) {
            return ['code' => -1, 'message' => 'No employee information is configured.'];
        }
        return ['code' => 0, 'data' => [], 'message' => 'success'];
    }

    /**
     * 个人中心-我的收入
     * @return array
     */
    public function actionGetMyIncomeData(): array
    {
        /**
         * @var AdminUser $user
         */
        $user = Yii::$app->user->identity;
        if (empty($user->job_number)) {
            return ['code' => -1, 'message' => 'You don\'t have a job number.'];
        }

        $url = 'https://api-collection.smallflyelephantsaas.com/collection/collector-wage-imported';
        $params['job_number'] = $user->job_number;
        $params['pay_day'] = Carbon::yesterday()->toDateString();
        $client = new Client([
            RequestOptions::TIMEOUT => 60,
        ]);
        $response = $client->request('POST', $url, [
            RequestOptions::JSON => $params
        ]);
        $result = 200 == $response->getStatusCode() ? json_decode($response->getBody()->getContents(), true) : [];

        return [
            'code'    => 0,
            'message' => 'success',
            'data'    => [
                'income' => $result['data']['income'] ?? '--',
            ],
        ];
    }

    /**
     * 个人中心-我的排名
     * @return array
     */
    public function actionGetMyRankingData(): array
    {
        /**
         * @var AdminUser $user
         */
        $user = Yii::$app->user->identity;
        $listMemberKey = sprintf('%s:%s:%s', RedisQueue::Z_LIST_MERCHANT_AGEING_REPAY_ORDER, $user->outside, $user->group);
        $listScoreKey = sprintf('%s:%s:%s', RedisQueue::Z_LIST_MERCHANT_AGEING_REPAY_ORDER_SCORE, $user->outside, $user->group);
        $myRepayOrderNum = RedisQueue::getZScore($listMemberKey, $user->id);
        if (is_null($myRepayOrderNum)) {
            $myRanking = '--';
            $beforeRankingScore = RedisQueue::getZRange($listScoreKey, 0, 0);
            $rankingDiff = $beforeRankingScore[0] ?? '--';
        } else {
            $myRankingLocation = RedisQueue::getZRevRank($listScoreKey, $myRepayOrderNum);
            $myRanking = $myRankingLocation + 1;
            $beforeRankingLocation = $myRankingLocation == 0 ? 0 : $myRankingLocation - 1;
            $beforeRankingScore = RedisQueue::getZRevRange($listScoreKey, $beforeRankingLocation, $beforeRankingLocation);
            $rankingDiff = $beforeRankingScore[0] - $myRepayOrderNum;
        }

        return [
            'code'    => 0,
            'message' => 'success',
            'data'    => [
                'ranking'     => $myRanking,
                'rankingDiff' => $rankingDiff,
            ],
        ];
    }
}

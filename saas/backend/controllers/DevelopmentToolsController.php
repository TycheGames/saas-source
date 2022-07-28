<?php
/**
 * Created by loan
 * User: wangpeng
 * Date: 2019/7/5 0005
 * Time：14:41
 */

namespace backend\controllers;

use backend\models\AdminUserCaptcha;
use backend\models\Merchant;
use callcenter\models\AdminUser as CcAdminUser;
use callcenter\models\AdminUserMasterSlaverRelation;
use callcenter\models\loan_collection\LoanCollectionOrder;
use common\helpers\CommonHelper;
use common\helpers\RedisQueue;
use common\models\CollectionTask;
use common\models\GlobalSetting;
use common\models\order\UserLoanOrder;
use common\models\order\UserRepaymentLog;
use common\models\user\LoanPerson;
use common\models\user\UserBankAccount;
use common\models\user\UserCaptcha;
use common\services\order\OrderService;
use yii;
use yii\web\Response;
use yii\web\ForbiddenHttpException;


class DevelopmentToolsController extends BaseController
{

    public function beforeAction($action)
    {
        if(parent::beforeAction($action))
        {
            if(Yii::$app->user->identity->merchant_id){
                throw new ForbiddenHttpException(Yii::T('common', 'Insufficient permissions'));
            }
            return true;
        }else{
            return false;
        }
    }

    /**
     * @name string 表结构缓存清理
     * @return array|string
     */
    public function actionClearSchemaCache()
    {
        if(yii::$app->request->isPost)
        {
            yii::$app->response->format = Response::FORMAT_JSON;
            if(CommonHelper::clearSchemaCache()){
                return [
                    'code' => 0,
                    'msg' => Yii::T('common', 'Cleaned up success')
                ];
            }else{
                return [
                    'code' => -1,
                    'msg' => Yii::T('common', 'Cleaned up fail')
                ];
            }
        }
        return $this->render('clear-schema-cache');
    }

    /**
     * @name 开发工具-重入风控队列
     */
    public function actionPushRedis()
    {
        $list = [
            RedisQueue::CREDIT_GET_DATA_SOURCE_PREFIX => Yii::T('common', 'Data Collection Queue-Predecision Tree'),
            RedisQueue::PUSH_ORDER_ASSIST_OVERDUE => '推送逾期信息到催收中心队列',
        ];
        if ($this->getRequest()->isPost) {
            $params = $this->getRequest()->post();

            if (empty($params['list_type'] || !isset($list[$params['list_type']]))) {
                return $this->redirectMessage(Yii::T('common', 'Queue name error'), self::MSG_ERROR);
            }

            if($params['submit_btn'] == '提交' || $params['submit_btn'] == 'submit'){
                if (empty($params['ids'])) {
                    return $this->redirectMessage(Yii::T('common', 'ids cannot be empty'), self::MSG_ERROR);
                }

                $ids = explode(PHP_EOL, $params['ids']);

                foreach ($ids as $id) {
                    $id = trim($id);
                    if (empty($id)) {
                        continue;
                    }
                    if (!is_numeric($id)) {
                        return $this->redirectMessage(\sprintf(Yii::T('common', 'id:% s type error'), $id), self::MSG_ERROR);
                    }

                    RedisQueue::push([$params['list_type'], $id]);
                }
            }

            return $this->redirectMessage(Yii::T('common', 'push success'), self::MSG_SUCCESS);

        }
        return $this->render('push-redis', [
            'list' => $list
        ]);

    }

    /**
     * @name 开发工具-跳过机审
     */
    public function actionSkipCheck()
    {
        if (Yii::$app->request->isPost) {
            if(YII_ENV_PROD){
                return $this->redirectMessage(Yii::T('common', 'Production environment cannot skip machine audit'), self::MSG_ERROR);
            }

            $params = $this->getRequest()->post();
            $params['id'] = CommonHelper::idDecryption($params['id']);

            if(empty($params['id'])){
                return $this->redirectMessage(Yii::T('common', 'Order id cannot be empty'), self::MSG_ERROR);
            }

            $order = UserLoanOrder::findOne(intval($params['id']));
            if(!$order){
                return $this->redirectMessage(Yii::T('common', 'Loan record does not exist'),self::MSG_ERROR);
            }

            if (UserLoanOrder::STATUS_CHECK != $order->status
                || UserLoanOrder::AUDIT_STATUS_GET_DATA != $order->audit_status
            ) {
                return $this->redirectMessage(Yii::T('common', 'Borrowing order is not pending'),self::MSG_ERROR);
            }

            $orderService = new OrderService($order);
            if(empty($order->card_id))
            {
                $afterAllStatus = [
                    'after_status' => UserLoanOrder::STATUS_WAIT_DEPOSIT
                ];
            }else{
                $bankAccount = UserBankAccount::find()
                    ->select(['id'])
                    ->where([
                        'id' => $order->card_id,
                        'user_id' => $order->user_id,
                        'status' => UserBankAccount::STATUS_SUCCESS
                    ])
                    ->one();
                //如果绑定卡是已认证通过的，则直接进入分配资方环节，否则进入人审
                if(is_null($bankAccount))
                {
                    $afterAllStatus = [
                        'after_status' => UserLoanOrder::STATUS_WAIT_DEPOSIT,
                        'after_loan_status' => UserLoanOrder::LOAN_STATUS_BIND_CARD_CHECK,
                    ];
                }else{
                    if (0 == $order->amount) {
                        $afterAllStatus = [
                            'after_status'      => UserLoanOrder::STATUS_WAIT_DRAW_MONEY,
                            'after_loan_status' => UserLoanOrder::LOAN_STATUS_DRAW_MONEY,
                        ];
                    } else {
                        $afterAllStatus = [
                            'after_status'      => UserLoanOrder::STATUS_LOANING,
                            'after_loan_status' => UserLoanOrder::LOAN_STATUS_FUND_MATCH,
                        ];
                    }
                }
            }
            $result = $orderService->changeOrderAllStatus($afterAllStatus,Yii::T('common', 'Manual operation skips machine review'));
            if($result){
                return $this->redirectMessage('order:'.$order->id.'Skip machine review successfully', self::MSG_SUCCESS);
            }else{
                return $this->redirectMessage('order:'.$order->id.'Skip machine review failed',self::MSG_ERROR);
            }
        }
        return $this->render('skip-check');
    }


    /**
     * @name 开发工具-设置ID显示状态
     * @return array|string
     */
    public function actionSetIdDisplayStatus()
    {
        $oSetting = GlobalSetting::find()->where(['key'=>'id_display_status'])->one();

        if(Yii::$app->request->get('status'))
        {
            $sStatus = Yii::$app->request->get('status', 'encryption');
            if ($sStatus == 'clear') {
                $oSetting->value = 'clear';
            } elseif ($sStatus == 'encryption') {
                $oSetting->value = 'encryption';
            }

            $oSetting->save();

        }
        return $this->render('set-id-display-status', ['oSetting' => $oSetting]);

    }// END actionSetIdDisplayStatus


    /**
     * @name 开发工具-ID解密
     */
    public function actionIdDecryption()
    {
        if (Yii::$app->request->isAjax && !empty(Yii::$app->request->post('id'))) {
            $nId = CommonHelper::idDecryption(Yii::$app->request->post('id'));
            if (empty($nId)) {
                return 0;
            } else {
                return $nId;
            }
        }
        return $this->render('id-decryption');

    }// END actionIdDecryption


    /**
     * @name 开发工具-ID加密
     */
    public function actionIdEncryption()
    {
        if (Yii::$app->request->isAjax && !empty(Yii::$app->request->post('id'))) {
            $nId = CommonHelper::idEncryption(Yii::$app->request->post('id'), 'order');
            if (empty($nId)) {
                return 0;
            } else {
                return $nId;
            }
        }
        return $this->render('id-encryption');

    }// END actionIdDecryption

    /**
     * @return string
     * @name 开发工具-跳过风控
     */
    public function actionSkipCheckList()
    {
        $model = GlobalSetting::find()->where(['key' => GlobalSetting::KEY_SKIP_CHECK_LIST])->one();
        if(is_null($model))
        {
            $model = new GlobalSetting();
            $model->key = GlobalSetting::KEY_SKIP_CHECK_LIST;
        }

        $data = $this->request->post();
        if ($data && $model->load($data)  && $model->validate()) {
            if ($model->save()) {
                return $this->redirectMessage('添加成功', self::MSG_SUCCESS);
            } else {
                return $this->redirectMessage('添加失败', self::MSG_ERROR);
            }
        }
        return $this->render('skip-check-add', [
            'model' => $model
        ]);
    }


    /**
     * @name  首页-管理中心首页
     * @return string
     */
    public function actionRedisList()
    {
        $redisList = [
            ['key' => RedisQueue::LIST_USER_MOBILE_APPS_UPLOAD, 'name' => '上报app名字'],
            ['key' => RedisQueue::LIST_USER_MOBILE_CONTACTS_UPLOAD, 'name' => '上报通讯录'],
            ['key' => RedisQueue::LIST_USER_MOBILE_SMS_UPLOAD, 'name' => '上报短信'],
            ['key' => RedisQueue::LIST_USER_MOBILE_CALL_RECORDS_UPLOAD, 'name' => '上报通话记录'],
            ['key' => RedisQueue::LIST_USER_MOBILE_PHOTO_UPLOAD, 'name' => '上报相册'],
            ['key' => RedisQueue::CREDIT_GET_DATA_SOURCE_PREFIX, 'name' => '前置风控'],
            ['key' => RedisQueue::PUSH_ORDER_ASSIST_APPLY, 'name' => '推送入催订单到催收中心'],
            ['key' => RedisQueue::PUSH_ORDER_ASSIST_OVERDUE, 'name' => '推送逾期信息到催收中心'],
            ['key' => RedisQueue::PUSH_ORDER_ASSIST_REPAYMENT, 'name' => '推送还款信息到催收中心'],
            ['key' => RedisQueue::PUSH_USER_CONTACTS, 'name' => '推送用户通讯录到催收中心'],
            ['key' => RedisQueue::PUSH_ORDER_REMIND_APPLY, 'name' => '推送提醒订单到提醒中心'],
            ['key' => RedisQueue::PUSH_ORDER_REMIND_REPAYMENT, 'name' => '推送还款信息到提醒中心'],
        ];
        foreach ($redisList as $key => $val) {
            $redisList[$key]['length'] = RedisQueue::getLength([$val['key']]);
        }
        return $this->render('redis-list', [
            'redisList' => $redisList,
        ]);
    }



    /**
     * @name 开发工具-催收工具
     */
    public function actionCollection()
    {
        $list = CollectionTask::$type_map;
        if ($this->getRequest()->isPost) {
            $params = $this->getRequest()->post();

            if (empty($params['list_type'] || !isset($list[$params['list_type']]))) {
                return $this->redirectMessage('请选择功能', self::MSG_ERROR);
            }

            if (empty($params['ids'])) {
                return $this->redirectMessage('内容不能为空', self::MSG_ERROR);
            }

            $ids = explode(PHP_EOL, $params['ids']);

            $text = [];
            foreach ($ids as $id) {
                $id = trim($id);
                if (empty($id)) {
                    continue;
                }
                $text[] = $id;
            }

            if(empty($text))
            {
                return $this->redirectMessage('内容不能为空2', self::MSG_ERROR);
            }

            $task = new CollectionTask();
            $task->admin_user_id = yii::$app->user->id;
            $task->type = $params['list_type'];
            $task->text = implode(',', $text);
            $task->status = CollectionTask::STATUS_DEFAULT;
            $task->save();


            return $this->redirectMessage('提交成功', self::MSG_SUCCESS);

        }
        return $this->render('collection', [
            'list' => $list
        ]);

    }


    /**
     * @name 开发工具-催收批量操作列表
     * @return string
     */
    public function actionCollectionList()
    {
        $list = CollectionTask::find()->orderBy(['id' => SORT_DESC])->all();
        return $this->render(
            'collection-list', [
                'list' => $list
            ]
        );
    }


    /**
     * @name 开发工具-催收批量操作-通过
     * @return string
     */
    public function actionCollectionPass()
    {
        yii::$app->response->format = Response::FORMAT_JSON;
        $id = intval(yii::$app->request->post('id'));
        $task = CollectionTask::findOne($id);
        if($task->status != CollectionTask::STATUS_DEFAULT)
        {
            return [
                'code' => -1,
                'msg' => '该数据已处理，请勿重复操作'
            ];
        }
        $task->status = CollectionTask::STATUS_SUCCESS;
        $task->save();
        $text = $task->text;
        $now = time();
        $username = explode(',',$text);
        $r = 0;
        switch ($task->type)
        {
            case 1:
                $loanCollectionOrders = LoanCollectionOrder::find()
                    ->select(['A.id','A.current_collection_admin_user_id'])
                    ->alias('A')
                    ->leftJoin(CcAdminUser::tableName().' B','A.current_collection_admin_user_id = B.id')
                    ->where(['A.status' => LoanCollectionOrder::$collection_status])
                    ->andWhere(['B.username'=> $username])
                    ->asArray()
                    ->all();
                foreach ($loanCollectionOrders as $loanCollectionOrder){
                    $r++;
                    RedisQueue::push([RedisQueue::SCHEDULE_LOAN_COLLECTION_ORDER_BACK_LIST, json_encode(['collector_id' => $loanCollectionOrder['current_collection_admin_user_id'], 'collection_order_id' => $loanCollectionOrder['id']])]);
                }
                break;
            case 4:
                $userIds = array_column(CcAdminUser::find()->select(['id'])->where(['username' => $username])->asArray()->all(),'id');
                if($userIds){
                    //更新副手
                    AdminUserMasterSlaverRelation::updateAll(
                        ['slave_admin_id' => 0,'updated_at' => $now],
                        ['OR',['admin_id' => $userIds],['slave_admin_id' => $userIds]]
                    );
                }
                $r = CcAdminUser::updateAll(['open_status' => CcAdminUser::OPEN_STATUS_OFF,'updated_at' => $now],['username' => $username]);
                break;
        }

        if($r)
        {
            return [
                'code' => 0,
                'msg' => "处理成功{$r}条数据"
            ];
        }else{
            return [
                'code' => -1,
                'msg' => "未找到匹配数据"
            ];
        }
    }


    /**
     * @name 开发工具-催收批量操作-驳回
     * @return string
     */
    public function actionCollectionReject()
    {
        yii::$app->response->format = Response::FORMAT_JSON;
        $id = intval(yii::$app->request->post('id'));
        $task = CollectionTask::findOne($id);
        if($task->status != CollectionTask::STATUS_DEFAULT)
        {
            return [
                'code' => -1,
                'msg' => '该数据已处理，请勿重复操作'
            ];
        }
        $task->status = CollectionTask::STATUS_FAIL;
        $task->save();
        return [
            'code' => 0,
            'msg' => '操作成功'
        ];

    }

    /**
     * @name DevelopmentToolsController 获取登录验证码发送结果
     * @return string
     */
    public function actionGetUserOtp(){
        $result = '';
        $phone = '';
        if($this->request->isPost){
            $phone = $this->request->post('phone','');
            $userCaptcha = UserCaptcha::find()->where(['phone' => $phone,'type' => [UserCaptcha::TYPE_REGISTER,UserCaptcha::TYPE_USER_LOGIN]])->asArray()->all();
            $adminCaptcha = AdminUserCaptcha::find()->where(['phone' => $phone,'type' => [AdminUserCaptcha::TYPE_ADMIN_LOGIN,AdminUserCaptcha::TYPE_ADMIN_CS_LOGIN]])->asArray()->all();
            foreach ($adminCaptcha as $k=>$v)
            {
                $result .= $v['type'].': '.$v['captcha'].'  ';
            }
            foreach ($userCaptcha as $k=>$v)
            {
                $result .= $v['type'].': '.$v['captcha'].'  ';
            }

        }
        if(!$result){
            $result = 'code not exist';
        }
        return $this->render('get-user-otp',['result' => $result,'phone' => $phone]);
    }

    /**
     * @name 数据拉取-手机号时间段内在用户源或产品中还款信息
     * @return string
     */
    public function actionPhoneFinishAmountPull(){
        $phoneStr = $this->request->post('phone','');
        $startDate = $this->request->post('start_date',date('Y-m-d'));
        $endDate = $this->request->post('end_date',date('Y-m-d'));
        $merchantId = $this->request->post('merchant_id',0);

        $result = null;
        if($this->request->isPost){
            $phoneArr = explode("\r\n", $phoneStr);

            $query = UserRepaymentLog::find()->alias('log')
                ->select([
                    'person_count' => 'COUNT(DISTINCT(log.user_id))',
                    'total_money' => 'SUM(log.amount)'
                ])
                ->leftJoin(LoanPerson::tableName().' p','log.user_id = p.id')
                ->where(['>=','log.success_time',strtotime($startDate)])
                ->andWhere(['<','log.success_time',strtotime($endDate) + 86400])
                ->andWhere(['p.phone' => $phoneArr]);

            if($merchantId > 0){
                $query->andWhere(['log.merchant_id' => $merchantId]);
            }
            $result = $query->asArray()->one(\Yii::$app->db_read_1);
        }

        $views = 'phone-finish-amount-pull';
        return $this->render($views, [
            'phoneStr' => $phoneStr,
            'merchantNameList' => Merchant::getMerchantId(false),
            'startDate' => $startDate,
            'endDate' => $endDate,
            'merchantId' => $merchantId,
            'result' => $result
        ]);
    }
}
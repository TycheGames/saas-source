<?php
namespace backend\controllers;

use common\helpers\CommonHelper;
use common\helpers\RedisQueue;
use common\models\package\PackageSetting;
use common\models\risk\RiskBlackList;
use common\services\order\PushOrderRiskService;
use common\services\risk\RiskBlackListService;
use common\services\user\UserExtraService;
use Yii;
use common\models\user\LoanPerson;
use yii\data\Pagination;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Class UserController     用户控制器
 * @package backend\controllers
 */
class UserController extends BaseController {

    /**
     * @name User list
     * @name-cn 用户管理-用户管理-用户列表
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function actionList() {

        $db = Yii::$app->get('db');
        $condition = $this->actionLoanPersonFilter();
        $query = LoanPerson::find()
            ->from(LoanPerson::tableName(). ' as p')
            ->select(['p.*','l.black_status'])
            ->leftJoin(RiskBlackList::tableName(). ' as l', 'p.id=l.user_id')
            ->where($condition)->andWhere(['p.merchant_id' => $this->merchantIds])->orderBy(['p.id'=>SORT_DESC]);
        $count = 9999999;
        $pages = new Pagination(['totalCount' => $count]);
        $pages->pageSize = \yii::$app->getRequest()->get('per-page', 15);
        $loan_person = $query->offset($pages->offset)->limit($pages->limit)->asArray()->all($db);

        return $this->render('list', [
            'loan_person' => $loan_person,
            'pages' => $pages,
            'package_setting' => array_flip(PackageSetting::getSourceIdMap($this->merchantIds))
        ]);
    }

    /**
     * @name 加入黑名单
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function actionAddBlackList()
    {
        $this->response->format = Response::FORMAT_JSON;
        try {
            $nId = CommonHelper::idDecryption($this->request->get('id'));

            $black_remark = $this->request->get('mark');
            $loanPerson = LoanPerson::findOne(['id' => $nId, 'merchant_id' => $this->merchantIds]);
            if (!$black_remark) {
                return [
                    'code' => -1,
                    'message' => Yii::T('common', 'Must fill in notes')
                ];
            }
            if (is_null($loanPerson)) {
                return [
                    'code' => -1,
                    'message' => Yii::T('common', 'User does not exist')
                ];
            }
            $transaction = Yii::$app->db->beginTransaction();
            $blackList = RiskBlackList::findOne(['user_id' => $nId]);
            if (is_null($blackList)) {
                $blackList = new RiskBlackList();
            }
            $blackList->user_id = $nId;
            $blackList->black_status = RiskBlackList::STATUS_YES;
            $blackList->source = 2;
            $blackList->operator_id = Yii::$app->user->identity->id;
            $blackList->black_remark = $black_remark;
            if (!$blackList->save()) {
                throw new \Exception(Yii::T('common', 'Failed to save blacklist'));
            }

            $service = new RiskBlackListService();
            $params = $service->addListByLoanPerson($loanPerson,2,1);

            $pushService = new PushOrderRiskService();
            $result      = $pushService->pushRiskBlack($params);
            if ($result['code'] != 0) {
                throw new \Exception('保存黑名单失败');
            }

            $transaction->commit();
            return [
                'code' => 0,
                'message' => Yii::T('common', 'Add success')
            ];
        } catch (\Exception $e) {
            $transaction->rollBack();
            return [
                'code' => -1,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * @name 取消黑名单
     **/
    public function actionDelBlackList()
    {
        $this->response->format = Response::FORMAT_JSON;
        try {
            $nId = CommonHelper::idDecryption($this->request->get('id'));

            $loanPerson = LoanPerson::findOne(['id' => $nId, 'merchant_id' => $this->merchantIds]);
            if (is_null($loanPerson)) {
                return [
                    'code' => -1,
                    'message' => Yii::T('common', 'User does not exist')
                ];
            }

            $blackList = RiskBlackList::findOne(['user_id' => $nId]);
            if (is_null($blackList)) {
                return [
                    'code' => -1,
                    'message' => Yii::T('common', 'Borrower is not on the blacklist')
                ];
            }
            $transaction = Yii::$app->db->beginTransaction();
            $blackList->black_status = RiskBlackList::STATUS_NO;
            $blackList->operator_id = Yii::$app->user->identity->id;;

            if (!$blackList->save()) {
                throw new \Exception(Yii::T('common', 'Failed to save blacklist'));
            }

            $service = new RiskBlackListService();
            $service->delListByLoanPerson($loanPerson->id, $loanPerson->merchant_id);

            $transaction->commit();
            return [
                'code' => 0,
                'message' => Yii::T('common', 'Delete success')
            ];
        } catch (\Exception $e) {
            $transaction->rollBack();
            return [
                'code' => -1,
                'message' => $e->getMessage()
            ];
        }

    }

    /**
     * @name 筛选条件
     * @return string
     */
    private function actionLoanPersonFilter() {
        $condition[] = 'and';
        $condition[] = ['>=', 'p.status', LoanPerson::PERSON_STATUS_DELETE];
        if ($this->request->get()) {        //过滤
            $search = $this->request->get();
            if (!empty($search['id'])) {
                $condition[] = ['p.id' => CommonHelper::idDecryption($search['id'])];
            }
            if (!empty($search['name'])) {
                $condition[] = ['like', 'p.name', $search['name']];
            }
            if (!empty($search['source_id'])) {
                $condition[] = ['p.source_id' => intval($search['source_id'])];
            }
            if (!empty($search['phone'])) {
                $condition[] = ['p.phone' => $search['phone']];
            }
            if (!empty($search['aadhaar_number'])) {
                $condition[] = ['p.aadhaar_number' => $search['aadhaar_number']];
            }
            if (!empty($search['pan_code'])) {
                $condition[] = ['p.pan_code' => $search['pan_code']];
            }
            if (!empty($search['begintime'])) {
                $condition[] = ['>=', 'p.created_at', strtotime($search['begintime'])];
            }
            if (!empty($search['endtime'])) {
                $condition[] = ['<', 'p.created_at', strtotime($search['endtime'])];
            }
            if (isset($search['black_status']) && $search['black_status'] !== '') {
                if ($search['black_status'] == RiskBlackList::STATUS_YES){
                    $condition[] = ['l.black_status' => $search['black_status']];
                } else{
                    $condition[] = [
                        'or',
                        ['l.black_status' => 0],
                        ['is', 'l.black_status', null],
                    ];
                }
            }
        }
        return $condition;
    }

    /**
     * @name User view
     * @name-cn 用户管理-用户管理-用户列表-查看
     * @param $id
     */
    public function actionUserView($id) {

        $nId = CommonHelper::idDecryption($id);

        /** @var LoanPerson $loan_person */
        $loan_person = LoanPerson::find()->where(['id' => $nId, 'merchant_id' => $this->merchantIds])->one();
        if (!isset($loan_person) && empty($loan_person)) {
            throw new NotFoundHttpException(\sprintf('The requested page does not exist (%s|%s).', empty($loan_person), empty($verify)));
        }

        $userInfoService = new UserExtraService($loan_person);
        $info = $userInfoService->getUserExtraInfo(true);
        $info['loanPerson'] = $loan_person;
        return $this->render('view', [
            'information' => $info,
        ]);
    }

//    /**
//     * @name User edit
//     * @param $id
//     * @name-cn 用户管理-用户管理-用户列表-编辑
//     */
//    public function actionUserEdit($id){
//
//        $nId = CommonHelper::idDecryption($id);
//
//        $transaction = Yii::$app->db->beginTransaction();
//        $loan_person = LoanPerson::find()->where(['id' => intval($nId), 'merchant_id' => $this->merchantIds])->one();
//
//        if ($this->getRequest()->getIsPost()) {
//            $loan_person->load($this->request->post());
//            //加锁（限制只能由一个用户进行修改操作）
//            $lock_key = sprintf("%s:%s", RedisQueue::USER_OPERATE_LOCK, 'LPEDIT:'.$nId);
//            if (1 == RedisQueue::inc([$lock_key, 1])) {
//                RedisQueue::expire([$lock_key, 30]);
//                if ($loan_person->save()){
//                    $transaction->commit();
//                    return $this->redirectMessage(Yii::T('common', 'Update success'), self::MSG_SUCCESS, Url::toRoute(['user/list']));
//                } else {
//                    return $this->redirectMessage(Yii::T('common', 'Update fail'), self::MSG_ERROR);
//                }
//            }else{
//                return $this->redirectMessage(Yii::T('common', 'Please do it later'), self::MSG_ERROR);
//            }
//        }
//
//        return $this->render('edit',[
//            'loan_person' => $loan_person,
//        ]);
//    }

    /**
     * @name Can loan time update
     * @date 2016-12-06
     * @name 用户管理--用户列表--重置可再借时间
     *
     **/
    public function actionCanLoanTimeUpdate($id){

        $nId = CommonHelper::idDecryption($id);

        if ($this->getRequest()->getIsPost())
        {
            if ($this->request->post('submit_btn')){
                $loan_date=$this->request->post('loan_date');
                //echo $loan_date;
                //echo $loan_date;
                $loan_person = LoanPerson::find()->where(['id' => $nId, 'merchant_id' => $this->merchantIds])->one();
                if ($loan_date!=='never_borrow'&&$loan_date!=='0'){
                    if ($loan_person->can_loan_time==0||$loan_person->can_loan_time>=429496729)//若之前是随时可借或者永不再借状态时，则初始日期取系统当前时间
                        $current_time = time();
                    else
                        $current_time = $loan_person->can_loan_time;
                    $loan_person->can_loan_time = $current_time+24*60*60*intval($loan_date);
                } elseif ($loan_date==='0'){//若等于0，则表示随时可再借
                    $loan_person->can_loan_time = 0;
                } elseif ($loan_date==='never_borrow'){//永不再借
                    $loan_person->can_loan_time = 4294967295;
                }

                $loan_person->save();
                return $this->redirectMessage('reset borrow again time success', self::MSG_SUCCESS, Url::toRoute(['user/list']));
            }
        }
        for($i=0;$i<=31;$i++){
            $can_loan_date[$i] =$i;
        }
        $can_loan_date['never_borrow']='never_borrow';
        return $this->render('can-loan-time-update',[
            'can_loan_date'=>$can_loan_date
        ]);
    }

    /**
     * @name 注销（不可登录）
     */
    public function actionUserDisable($id){
        $this->response->format = Response::FORMAT_JSON;
        $loanPerson = LoanPerson::find()->where(['id' => $id,'merchant_id' => $this->merchantIds])->one();
        if($loanPerson->status != LoanPerson::PERSON_STATUS_PASS){
            return [
                'code' => -1,
                'message' => 'fail'
            ];
        }
        $loanPerson->status = LoanPerson::PERSON_STATUS_DISABLE;
        $loanPerson->save();
        return [
            'code' => 0,
            'message' => 'success'
        ];
    }
}

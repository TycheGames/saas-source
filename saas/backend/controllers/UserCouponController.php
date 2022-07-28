<?php

namespace backend\controllers;

use backend\models\search\SlowListSearch;
use common\helpers\CommonHelper;
use common\models\coupon\UserCouponInfo;
use common\models\coupon\UserRedPacketsSlow;
use common\models\user\LoanPerson;
use common\services\user\UserCouponService;
use Yii;
use yii\base\Exception;
use yii\data\Pagination;
use yii\helpers\Url;
use yii\web\ForbiddenHttpException;

/**
 * author ron
 * date 2016-09-22
 *
 * 红包设置以及使用
 *
 */
class UserCouponController extends BaseController {

    /**
     * @name 内容管理-运营管理-劵模板
     */
    public function actionListSlow()
    {
        $query = UserRedPacketsSlow::find()->where(['merchant_id' => $this->merchantIds])->orderBy([
            'id' => SORT_DESC
        ]);

        $searchForm = new SlowListSearch();
        $searchArray = $searchForm->search(yii::$app->request->get());
        foreach ($searchArray as $item)
        {
            $query->andFilterWhere($item);
        }

        $queryClone = clone $query;
        $pages = new Pagination(['totalCount' => $queryClone->count('*')]);
        $pages->pageSize = 15;
        $data = $query->offset($pages->offset)->limit($pages->limit)->all();
        foreach ($data as $key => $value){
            if (!$value['use_type']) {
                $data[$key]["expire_str"] = sprintf("%d天", intval($value["user_use_days"]));
            } else {
                $data[$key]["expire_str"] = sprintf("%s~%s", date('Y-m-d', $value->use_start_time), date('Y-m-d', $value->use_end_time));
            }
        }
        return $this->render('list-slow', array(
            'data_list' => $data,
            'pages' => $pages,
            'isNotMerchantAdmin' => $this->isNotMerchantAdmin,
        ));
    }

    /**
     * @return string
     * @name 内容管理-运营管理-券模板添加
     */
    public function actionAddSlow() {
        if($this->isNotMerchantAdmin){
            throw new ForbiddenHttpException(Yii::T('common', 'Insufficient permissions'));
        }
        $model = new UserRedPacketsSlow();
        if ($model->load($this->request->post()) && $model->validate()) {
            $post = $this->request->post();
            $model->code_pre = !empty($model->code_pre) ? $model->code_pre : "dkq";
            $model->user_admin = Yii::$app->user->identity->username;
            $model->status = UserRedPacketsSlow::STATUS_FALSE;
            $model->use_type = $post['sel_date'];

            $model->amount = (!empty($model->amount)) ? intval($model->amount) : 0;
            $model->amount = $model->amount * 100;
            $model->use_start_time = strtotime($model->use_start_time);
            $model->use_end_time = strtotime($model->use_end_time);
            $model->merchant_id = Yii::$app->user->identity->merchant_id;
            if ($model->save()) {
                return $this->redirectMessage(Yii::T('common', 'Add success'), self::MSG_SUCCESS, Url::toRoute('list-slow'));
            } else {
                return $this->redirectMessage(Yii::T('common', 'Add fail'), self::MSG_ERROR);
            }
        }
        return $this->render('add-slow', [
            'model' => $model,
        ]);
    }

    /**
     * @return string
     * @name 内容管理-运营管理-券模板编辑
     */
    public function actionEditSlow() {
        if($this->isNotMerchantAdmin){
            throw new ForbiddenHttpException(Yii::T('common', 'Insufficient permissions'));
        }
        $id = intval($this->request->get('id', 0));
        $model = UserRedPacketsSlow::findOne(['id' => $id, 'merchant_id' => $this->merchantIds]);

        if ($model->load($this->request->post()) && $model->validate()) {
            $post = $this->request->post();
            $model->code_pre = !empty($model->code_pre) ? $model->code_pre : "dkq";
            $model->use_type = $post['sel_date'];
            $model->user_admin = Yii::$app->user->identity->username;
            $model->amount = $model->amount * 100;
            $model->use_start_time = strtotime($model->use_start_time);
            $model->use_end_time = strtotime($model->use_end_time);
            if ($model->save()) {
                return $this->redirectMessage(Yii::T('common', 'Update success'), self::MSG_SUCCESS, Url::toRoute('list-slow'));
            } else {
                return $this->redirectMessage(Yii::T('common', 'Update fail'), self::MSG_ERROR);
            }
        }

        $model->amount = $model->amount / 100;

        if ($model->use_start_time) {
            $model->use_start_time = date("Y-m-d H:i:s", $model->use_start_time);
        }else{
            $model->use_start_time = '';
        }

        if ($model->use_end_time) {
            $model->use_end_time = date("Y-m-d H:i:s", $model->use_end_time);
        }else{
            $model->use_end_time = '';
        }
        return $this->render('edit-slow', [
            'model' => $model,
        ]);
    }

    /**
     * 更新启用状态
     * @name 内容管理-运营管理-更新启用券模版状态
     */
    public function actionUpdateSlow() {
        if($this->isNotMerchantAdmin){
            throw new ForbiddenHttpException(Yii::T('common', 'Insufficient permissions'));
        }
        $id = intval($this->request->get('id', 0));
        $model = UserRedPacketsSlow::findOne(['id' => $id, 'merchant_id' => $this->merchantIds]);

        if ($model->load($this->request->post())) {
            $model->user_admin = Yii::$app->user->identity->username;
            if ($model->save()) {
                return $this->redirectMessage(Yii::T('common', 'Update success'), self::MSG_SUCCESS, Url::toRoute('list-slow'));
            } else {
                return $this->redirectMessage(Yii::T('common', 'Update fail'), self::MSG_ERROR);
            }
        }

        return $this->render('update-slow', [
            'model' => $model,
        ]);
    }

    /**
     * @name 内容管理-运营管理-立即生效
     */
    public function actionShowOnce() {
        if($this->isNotMerchantAdmin){
            throw new ForbiddenHttpException(Yii::T('common', 'Insufficient permissions'));
        }
        $id = intval($this->request->get('id', 0));
        $model = UserRedPacketsSlow::findOne(['id' => $id, 'merchant_id' => $this->merchantIds]);
        $model->user_admin = Yii::$app->user->identity->username;
        $model->status = UserRedPacketsSlow::STATUS_SUCCESS;
        if($model->save()){
            return $this->redirectMessage(Yii::T('common', 'Update success'), self::MSG_SUCCESS, Url::toRoute('list-slow'));
        }else{
            return $this->redirectMessage(Yii::T('common', 'Update fail'), self::MSG_ERROR);
        }
    }



    /**
     * @name 内容管理-运营管理-劵领用列表
     */
    public function actionList() {
        $condition = $this->getListFilter();
        $query = UserCouponInfo::find()->where($condition)->andWhere(['merchant_id' => $this->merchantIds])->orderBy([
            'id' => SORT_DESC,
        ]);
        // 获取券列表
        $coupon_tmp = UserRedPacketsSlow::find()->where(['merchant_id' => $this->merchantIds])->asArray()->all();
        $tmp_list = [];
        foreach ($coupon_tmp as $v) {
            $tmp_list[$v['id']] = $v['id'] .'/' .$v['title'];
        }

        $totalCount = $query->count('*');
        $pages = new Pagination(['totalCount' => $totalCount]);
        $pages->pageSize = 15;

        $dataSt = [];
        if ($this->request->get('is_summary')==1) {
            $countQuery = clone $query;
            $info = $countQuery->select(['count(1) as num','is_use'])->groupBy('is_use')->asArray()->all();
            $dataSt['num'] = 0;
            $dataSt['use_num'] = 0;
            foreach ($info as $v){
                $dataSt['num'] += $v['num'];
                if($v['is_use'] == UserCouponInfo::STATUS_SUCCESS){
                    $dataSt['use_num'] = $v['num'];
                }
            }
        }

        $data = $query->offset($pages->offset)->limit($pages->limit)->all();
        $temp_data = [];
        foreach ($data as $key => $value) {
            //券模板
            $temp_data[$key]["coupon_title"] = $tmp_list[$value['coupon_id']] ?? "";

            // 有效期显示
            $data[$key]["expire_str"] = sprintf("%s~%s", date('Y-m-d', $value->start_time), date('Y-m-d', $value->end_time));
        }

        return $this->render('list', array(
            'temp_data' => $temp_data,
            'data_list' => $data,
            'pages' => $pages,
            'tmp_list' => $tmp_list,
            'dataSt' => $dataSt,
            'isNotMerchantAdmin' => $this->isNotMerchantAdmin,
        ));
    }

    /**
     * @name 内容管理-运营管理-借款劵补偿方法二
     */
    public function actionInsertForLoan() {
        if($this->isNotMerchantAdmin){
            throw new ForbiddenHttpException(Yii::T('common', 'Insufficient permissions'));
        }
        $model = new UserCouponInfo();
        if ($coupon_info = $this->request->post("UserCouponInfo")) {
            $user_id = $coupon_info['user_id'];
            if (!$user_id) {
                return $this->redirectMessage(Yii::T('common', 'Please enter user id'), self::MSG_ERROR);
            }

            if($coupon_info['coupon_id']) {
                $template_id = intval($coupon_info['coupon_id']);
            } else {
                return $this->redirectMessage(Yii::T('common', 'Please select a coupon template'), self::MSG_ERROR);
            }

            $user_id_arr = explode(PHP_EOL, $user_id);

            $error = [];
            foreach ($user_id_arr as $v){
                $uid = trim($v);
                if(empty($uid)){
                    continue;
                }
                $user_info = LoanPerson::find()->where([
                    'status' => LoanPerson::PERSON_STATUS_PASS,
                    'id' => $uid,
                    'merchant_id' => $this->merchantIds,
                ])->limit(1)->one();
                if (!$user_info) {
                    $error[] = $uid;
                    continue;
                }

                $res = UserCouponService::sendCouponByAct($template_id, $uid, $user_info->merchant_id);
                if (!$res) {
                    $error[] = $uid;
                    continue;
                }
            }

            if(empty($error)){
                return $this->redirectMessage(Yii::T('common', 'Ticket issued success'), self::MSG_SUCCESS, Url::toRoute('list'));
            }

            $msg = 'user_ids:'.implode(',', $error).Yii::T('common', 'Ticket issued fail');
            return $this->redirectMessage($msg, self::MSG_ERROR);
        }

        // 获取所有可用的红包模板
        $userRedPacketSlows = UserRedPacketsSlow::find()->where("status=1")->andWhere(['merchant_id' => $this->merchantIds])->select(["id", "title"])->orderBy("id desc")->all(Yii::$app->get('db'));
        $userRedPacketArr = [];
        if ($userRedPacketSlows) {
            foreach ($userRedPacketSlows as $value) {
                $userRedPacketArr[$value["id"]] = $value["id"]."/".$value["title"];
            }
        }

        return $this->render('add-user-simple', [
            'model' => $model,
            'userRedPacketSlows' => $userRedPacketArr
        ]);
    }

    /**
     * 列表筛选
     */
    private function getListFilter() {
        $condition[] = 'and';
        if ($this->getRequest()->getIsGet()) {
            $search = $this->request->get();
            if (isset($search['id']) && !empty($search['id'])) {
                $condition[] = ['user_id' => CommonHelper::idDecryption($search['id'])];
            }
            if (isset($search['phone']) && !empty($search['phone'])) {
                $condition[] = ['phone' => intval($search['phone'])];
            }
            if (isset($search['use_case']) && !empty($search['use_case'])) {
                $condition[] = ['use_case' => intval($search['use_case'])];
            }
            if (isset($search['start_time']) && !empty($search['start_time'])) {
                $condition[] = ['>=', 'start_time', strtotime($search['start_time'])];
            }
            if (isset($search['end_time']) && !empty($search['end_time'])) {
                $condition[] = ['<=', 'end_time', strtotime($search['end_time'])];
            }
            if (isset($search['start_created_at']) && !empty($search['start_created_at'])) {
                $condition[] = ['>=', 'created_at', strtotime($search['start_created_at'])];
            }
            if (isset($search['end_created_at']) && !empty($search['end_created_at'])) {
                $condition[] = ['<=', 'created_at', strtotime($search['end_created_at'])];
            }
            if (isset($search['start_use_time']) && !empty($search['start_use_time'])) {
                $condition[] = ['>=', 'use_time', strtotime($search['start_use_time'])];
            }
            if (isset($search['end_use_time']) && !empty($search['end_use_time'])) {
                $condition[] = ['<=', 'use_time', strtotime($search['end_use_time'])];
            }
            if (isset($search['coupon_id']) && !empty($search['coupon_id'])) {
                $condition[] = ['coupon_id' => $search['coupon_id']];
            }
            if (isset($search['is_use'])) {
                if($search['is_use'] != ''){
                    $condition[] = ['is_use' => intval($search['is_use'])];
                }
            }
        }
        return $condition;
    }


}

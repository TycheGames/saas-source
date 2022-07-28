<?php
namespace backend\controllers;

use backend\models\Merchant;
use common\models\pay\PayAccountSetting;
use common\models\pay\PayoutAccountInfo;
use common\models\pay\PayoutAccountSetting;
use Yii;
use yii\data\ActiveDataProvider;
use yii\helpers\Url;
use yii\data\Pagination;
use yii\web\ForbiddenHttpException;


class MerchantSettingController extends BaseController {

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
     * @name 系统管理-系统管理员-商户管理
     * @return string
     */
    public function actionMerchantList(){
        $query = Merchant::find()->orderBy('id desc');
        $countQuery = clone $query;
        $pages = new Pagination(['totalCount' => $countQuery->count()]);
        $pages->pageSize = 15;
        $list = $query->offset($pages->offset)->limit($pages->limit)->all();
        return $this->render('merchant-list',[
            'merchant' => $list,
            'pages' => $pages
        ]);
    }

    /**
     *@name 系统管理-系统管理员-新增商户
     */
    public function actionMerchantAdd(){
        $model = new Merchant();
        if ($model->load($this->request->post()) && $model->validate()) {
            $model->operator = Yii::$app->user->identity->username;
            if ($model->save()) {
                return $this->redirectMessage('success', self::MSG_SUCCESS, Url::toRoute('merchant-setting/merchant-list'));
            } else {
                return $this->redirectMessage('fail', self::MSG_ERROR);
            }
        }

        return $this->render('merchant-add', [
            'model' => $model
        ]);
    }


    /**
     * @name 商户管理-商户管理-修改商户
     * @param $id
     * @return string
     */
    public function actionMerchantEdit($id){
        $model = Merchant::findOne(intval($id));
        if (!$model) {
            return $this->redirectMessage(Yii::T('common', 'Merchant does not exist'), self::MSG_ERROR);
        }
        if ($model->load($this->request->post()) && $model->validate()) {
            $model->operator = Yii::$app->user->identity->username;
            if ($model->save()) {
                return $this->redirectMessage('success', self::MSG_SUCCESS, Url::toRoute('merchant-setting/merchant-list'));
            } else {
                return $this->redirectMessage('fail', self::MSG_ERROR);
            }
        }

        return $this->render('merchant-add', [
            'model' => $model
        ]);
    }


    /**
     * @name  商户管理-账户管理-账号列表
     * @return string
     */
    public function actionAccountList()
    {

//        $query = PayAccountSetting::find();
//        $dataProvider = new ActiveDataProvider([
//            'query' => $query,
//            'pagination' => [
//                'pageSize' => 15
//            ]
//        ]);
//
//        return $this->render('account-list',[
//            'dataProvider' => $dataProvider,
//        ]);
    }


    /**
     * @name 商户管理-账户管理-添加账号
     * @param $type
     * @return string
     */
    public function actionAccountAdd($type)
    {
//        $accountModel = new PayAccountSetting();
//        $class = PayAccountSetting::$service_map[$type];
//        $model = $class::formModel();
//
//        if(Yii::$app->request->isPost)
//        {
//            if ($model->load($this->request->post()) && $model->validate()) {
//                $accountModel->account_info = json_encode($model->toArray(), JSON_UNESCAPED_UNICODE);
//                $accountModel->service_type = $type;
//                if($accountModel->load($this->request->post()) && $accountModel->validate() && $accountModel->save())
//                {
//                    return $this->redirectMessage('success', self::MSG_SUCCESS, Url::toRoute('merchant-setting/account-list'));
//                }else {
//                    return $this->redirectMessage('fail', self::MSG_ERROR);
//
//                }
//            }
//        }
//
//        return $this->render('account-add', [
//            'model' => $model,
//            'accountModel' => $accountModel
//        ]);
    }


    /**
     * @name 商户管理-账户管理-修改账户
     * @param $id
     * @return string
     */
    public function actionAccountEdit($id){
//        $accountModel = PayAccountSetting::findOne(intval($id));
//        if (!$accountModel) {
//            return $this->redirectMessage(Yii::T('common', 'Account does not exist'), self::MSG_ERROR);
//        }
//
//        $class = PayAccountSetting::$service_map[$accountModel->service_type];
//        $model = $class::formModel();
//        $model->load(json_decode($accountModel->account_info, true), '');
//
//        if(yii::$app->request->isPost)
//        {
//            if ($model->load($this->request->post()) && $model->validate()) {
//                $accountModel->account_info = json_encode($model->toArray(), JSON_UNESCAPED_UNICODE);
//                if($accountModel->load($this->request->post()) && $accountModel->validate() && $accountModel->save())
//                {
//                    return $this->redirectMessage('success', self::MSG_SUCCESS, Url::toRoute('merchant-setting/account-list'));
//                }else {
//                    return $this->redirectMessage('fail', self::MSG_ERROR);
//
//                }
//            }
//        }
//
//
//
//        return $this->render('account-add', [
//            'model' => $model,
//            'accountModel' => $accountModel
//        ]);
    }


    /**
     * @name 商户管理-账户管理-查看账户
     * @param $id
     * @return string
     */
    public function actionAccountDetail($id){
//        $accountModel = PayAccountSetting::findOne(intval($id));
//        if (!$accountModel) {
//            return $this->redirectMessage('账户不存在', self::MSG_ERROR);
//        }
//
//        $class = PayAccountSetting::$service_map[$accountModel->service_type];
//        $model = $class::formModel();
//        $model->load(json_decode($accountModel->account_info, true), '');
//
//        return $this->render('account-detail', [
//            'model' => $model,
//            'accountModel' => $accountModel
//        ]);

    }// END actionAccountDetail


    /**
     * @name  商户管理-账户管理-账号列表
     * @return string
     */
    public function actionPayoutAccountList()
    {

//        $query = PayoutAccountInfo::find();
//        $dataProvider = new ActiveDataProvider([
//            'query' => $query,
//            'pagination' => [
//                'pageSize' => 15
//            ]
//        ]);
//
//        return $this->render('payout-account-list',[
//            'dataProvider' => $dataProvider,
//        ]);
    }

    /**
     * @name 商户管理-账户管理-添加账号
     * @param $type
     * @return string
     */
    public function actionPayoutAccountAdd($type)
    {
//        $accountModel = new PayoutAccountInfo();
//        $accountModel->service_type = intval($type);
//        $model = $accountModel->getForm();
//
//        if(Yii::$app->request->isPost)
//        {
//            if ($model->load($this->request->post()) && $model->validate()) {
//                $accountModel->account_info = json_encode($model->toArray(), JSON_UNESCAPED_UNICODE);
//                if($accountModel->load($this->request->post()) && $accountModel->validate() && $accountModel->save())
//                {
//                    return $this->redirectMessage('success', self::MSG_SUCCESS, Url::toRoute('merchant-setting/payout-account-list'));
//                }else {
//                    return $this->redirectMessage('fail', self::MSG_ERROR);
//
//                }
//            }
//        }
//
//        return $this->render('payout-account-add', [
//            'model' => $model,
//            'accountModel' => $accountModel
//        ]);
    }



    /**
     * @name 商户管理-账户管理-修改账户
     * @param $id
     * @return string
     */
    public function actionPayoutAccountEdit($id){
//        $accountModel = PayoutAccountInfo::findOne(intval($id));
//        if (!$accountModel) {
//            return $this->redirectMessage('账户不存在', self::MSG_ERROR);
//        }
//
//        $model = $accountModel->getForm();
//        $model->load(json_decode($accountModel->account_info, true), '');
//
//        if(yii::$app->request->isPost)
//        {
//            if ($model->load($this->request->post()) && $model->validate()) {
//                $accountModel->account_info = json_encode($model->toArray(), JSON_UNESCAPED_UNICODE);
//                if($accountModel->load($this->request->post()) && $accountModel->validate() && $accountModel->save())
//                {
//                    return $this->redirectMessage('success', self::MSG_SUCCESS, Url::toRoute('merchant-setting/payout-account-list'));
//                }else {
//                    return $this->redirectMessage('fail', self::MSG_ERROR);
//
//                }
//            }
//        }
//
//
//
//        return $this->render('payout-account-add', [
//            'model' => $model,
//            'accountModel' => $accountModel
//        ]);
    }


    /**
     * @name 商户管理-账户管理-查看账户
     * @param $id
     * @return string
     */
    public function actionPayoutAccountDetail($id)
    {
//        $accountModel = PayoutAccountInfo::findOne(intval($id));
//        if (!$accountModel) {
//            return $this->redirectMessage('账户不存在', self::MSG_ERROR);
//        }
//
//        $model = $accountModel->getForm();
//        $model->load(json_decode($accountModel->account_info, true), '');
//
//        return $this->render('payout-account-detail', [
//            'model' => $model,
//            'accountModel' => $accountModel
//        ]);

    }// END actionAccountDetail


    /**
     * @name  商户管理-账户管理-账号列表
     * @return string
     */
    public function actionPayoutSettingList()
    {

//        $query = PayoutAccountSetting::find();
//        $dataProvider = new ActiveDataProvider([
//            'query' => $query,
//            'pagination' => [
//                'pageSize' => 15
//            ]
//        ]);
//
//        return $this->render('payout-setting-list',[
//            'dataProvider' => $dataProvider,
//        ]);
    }


    /**
     * @name 商户管理-账户管理-添加账号
     * @param $type
     * @return string
     */
    public function actionPayoutSettingAdd()
    {
//        $accountModel = new PayoutAccountSetting();
//
//        if(Yii::$app->request->isPost)
//        {
//            if($accountModel->load($this->request->post()) && $accountModel->validate() && $accountModel->save())
//            {
//                return $this->redirectMessage('success', self::MSG_SUCCESS, Url::toRoute('merchant-setting/payout-setting-list'));
//            }else {
//                return $this->redirectMessage('fail', self::MSG_ERROR);
//
//            }
//        }
//
//        return $this->render('payout-setting-add', [
//            'accountModel' => $accountModel,
//            'accountMap' => PayoutAccountInfo::getListMap(),
//            'statusMap' => PayoutAccountSetting::$status_map
//        ]);
    }


    /**
     * @name 商户管理-账户管理-修改账户
     * @param $id
     * @return string
     */
    public function actionPayoutSettingEdit($id){
//        $accountModel = PayoutAccountSetting::findOne(intval($id));
//        if (!$accountModel) {
//            return $this->redirectMessage('账户不存在', self::MSG_ERROR);
//        }
//
//
//        if(yii::$app->request->isPost)
//        {
//            if($accountModel->load($this->request->post()) && $accountModel->validate() && $accountModel->save())
//            {
//                return $this->redirectMessage('success', self::MSG_SUCCESS, Url::toRoute('merchant-setting/payout-setting-list'));
//            }else {
//                return $this->redirectMessage('fail', self::MSG_ERROR);
//
//            }
//        }
//
//
//
//        return $this->render('payout-setting-add', [
//            'accountModel' => $accountModel,
//            'accountMap' => PayoutAccountInfo::getListMap(),
//            'statusMap' => PayoutAccountSetting::$status_map
//        ]);
    }
}

<?php
namespace backend\controllers;

use common\models\CheckVersion;
use common\models\GlobalSetting;
use common\models\third_data\ValidationRule;
use yii\data\Pagination;
use yii\helpers\Url;
use yii\web\ForbiddenHttpException;
use yii;

/**
 * AdminUser controller
 */
class AppController extends BaseController
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
    //配置列表
    /**
     * @return string
     * @name 系统管理-版本配置
     */
    public function actionList()
    {
        $query = CheckVersion::find();
        $countQuery = clone $query;
        $pages = new Pagination(['totalCount' => $countQuery->count('*')]);
        $pages->pageSize = 15;
        $list = $query->offset($pages->offset)->limit($pages->limit)->orderBy('id DESC')->asArray()->all();
        return $this->render('list', array(
            'list' => $list,
            'type_name' => '',
            'status_name' => '',
            'pages' => $pages,
        ));
    }

    // 添加配置
    /**
     * @return string
     * @name 通用密码登录
     */
    public function actionNoPasswordLogin()
    {
        $model = GlobalSetting::find()->where(['key' => GlobalSetting::KEY_NO_PASSWORD_LOGIN_LIST])->one();
        if(is_null($model))
        {
            $model = new GlobalSetting();
            $model->key = GlobalSetting::KEY_NO_PASSWORD_LOGIN_LIST;
        }

        $data = $this->request->post();
        if ($data && $model->load($data)  && $model->validate()) {
            if ($model->save()) {
                return $this->redirectMessage(Yii::T('common', 'Add success'), self::MSG_SUCCESS);
            } else {
                return $this->redirectMessage(Yii::T('common', 'Add fail'), self::MSG_ERROR);
            }
        }
        return $this->render('no-password-login-add', [
            'model' => $model
        ]);
    }

    // 添加配置
    /**
     * @return string
     * @name 设置可见催收员真实姓名催收管理员
     */
    public function actionSetRealNameCollectionAdmin()
    {
        $model = GlobalSetting::find()->where(['key' => GlobalSetting::KEY_SET_REAL_NAME_COLLECTION_ADMIN_LIST])->one();
        if(is_null($model))
        {
            $model = new GlobalSetting();
            $model->key = GlobalSetting::KEY_SET_REAL_NAME_COLLECTION_ADMIN_LIST;
        }

        $data = $this->request->post();
        if ($data && $model->load($data)  && $model->validate()) {
            if ($model->save()) {
                return $this->redirectMessage(Yii::T('common', 'Add success'), self::MSG_SUCCESS);
            } else {
                return $this->redirectMessage(Yii::T('common', 'Add fail'), self::MSG_ERROR);
            }
        }
        return $this->render('set-real-name-collection-admin', [
            'model' => $model
        ]);
    }

    //修改配置
    /**
     * @return string
     * @name 修改配置
     */
    public function actionEdit(int $id)
    {
        $type = 'edit';
        $app_url = CheckVersion::$app_url;
        $model = CheckVersion::findOne(['id' => $id]);
        $data = $this->request->post();
        if ($data && $model->load($data)) {
            if ($model->save() && $model->validate()) {
                return $this->redirectMessage(Yii::T('common', 'Update success'), self::MSG_SUCCESS, Url::toRoute('list'));
            } else {
                return $this->redirectMessage(Yii::T('common', 'Update fail'), self::MSG_ERROR);
            }
        }
        return $this->render('add', [
            'model' => $model,
            'app_url' => $app_url,
            'type' => $type
        ]);
    }
    /**
     * @return string
     * @name 删除配置
     */
    //删除配置
    public function actionDel(int $id)
    {
        $model = CheckVersion::findOne($id);
        if (!$model) {
            return $this->redirectMessage(Yii::T('common', 'banner deleted'), self::MSG_ERROR);
        }
        if ($model->delete($id)) {
            return $this->redirectMessage(Yii::T('common', 'Delete success'), self::MSG_SUCCESS, Url::toRoute('list'));
        } else {
            return $this->redirectMessage(Yii::T('common', 'Delete fail'), self::MSG_ERROR);
        }
    }

    /**
     * @name AppController 认证服务路由规则-列表
     * @return string
     */
    public function actionValidationSwitchRule()
    {
        $query = ValidationRule::find();

        $search = $this->request->get();
        if (!empty($search['validation_type'])) {
            $query->where(['validation_type' => $search['validation_type']]);
        }
        if (isset($search['is_used']) && $search['is_used'] != '') {
            $query->where(['is_used' => intval($search['is_used'])]);
        }

        $countQuery = clone $query;
        $pages = new Pagination(['totalCount' => $countQuery->count('id')]);
        $pages->pageSize = 15;

        $models = $query
            ->offset($pages->offset)
            ->limit($pages->limit)
            ->all();

        return $this->render('validation-rule-list', [
            'models' => $models,
            'pages'  => $pages,
        ]);
    }

    /**
     * @name AppController 认证服务路由规则-编辑
     * @return string
     */
    public function actionValidationSwitchRuleEdit()
    {
        $id = $this->request->get('id', 0);

        $model = ValidationRule::findOne($id);
        if (empty($model)) {
            return $this->redirectMessage(Yii::T('common', 'Rule does not exist'),self::MSG_ERROR);
        }
        $postData = $this->request->post();
        if ($postData && $model->load($postData) && $model->validate()) {
            if($model->save()) {
                return $this->redirectMessage(Yii::T('common', 'Update rules success'),self::MSG_SUCCESS, Url::toRoute('validation-switch-rule'));
            } else {
                return $this->redirectMessage(Yii::T('common', 'Update rules fail'),self::MSG_ERROR);
            }
        }

        return $this->render('validation-rule-edit', [
            'model' => $model
        ]);
    }

    /**
     * @name AppController 认证服务路由规则-新增
     * @return string
     */
    public function actionValidationSwitchRuleAdd()
    {
        $model = new ValidationRule();

        $postData = $this->request->post();
        if ($postData && $model->load($postData) && $model->validate()) {
            if($model->save()) {
                return $this->redirectMessage(Yii::T('common', 'Add rules success'),self::MSG_SUCCESS, Url::toRoute('validation-switch-rule'));
            } else {
                return $this->redirectMessage(Yii::T('common', 'Add rules fail'),self::MSG_ERROR);
            }
        }

        return $this->render('validation-rule-add', [
            'model' => $model
        ]);
    }

    /**
     * @name AppController 认证服务路由规则-是否启用
     * @return string
     */
    public function actionValidationSwitchRuleUsed()
    {
        $id = $this->request->get('id', 0);
        $isUsed =  $this->request->get('is_used', 0);
        $model = ValidationRule::findOne($id);
        if (empty($model)) {
            return $this->redirectMessage(Yii::T('common', 'Rule does not exist'),self::MSG_ERROR);
        }
        $model->is_used = intval($isUsed);
        if ($model->save()) {
            return $this->redirectMessage(Yii::T('common', 'Update rules success'), self::MSG_SUCCESS, Url::toRoute('validation-switch-rule'));
        } else {
            return $this->redirectMessage(Yii::T('common', 'Update rules fail'), self::MSG_ERROR);
        }
    }

    /**
     * @return string
     * @name 牛信电话开关
     */
    public function actionNxPhoneConfig()
    {
        $model = GlobalSetting::find()->where(['key' => GlobalSetting::KEY_NX_PHONE_CONFIG_LIST])->one();
        if(is_null($model))
        {
            $model = new GlobalSetting();
            $model->key = GlobalSetting::KEY_NX_PHONE_CONFIG_LIST;
        }

        $data = $this->request->post();
        if ($data && $model->load($data)  && $model->validate()) {
            if ($model->save()) {
                return $this->redirectMessage('添加成功', self::MSG_SUCCESS);
            } else {
                return $this->redirectMessage('添加失败', self::MSG_ERROR);
            }
        }
        return $this->render('nx-phone-add', [
            'model' => $model
        ]);
    }

    /**
     * @return string
     * @name 牛信电话SDK开关
     */
    public function actionNxPhoneSdkConfig()
    {
        $model = GlobalSetting::find()->where(['key' => GlobalSetting::KEY_NX_PHONE_SDK_CONFIG_LIST])->one();
        if(is_null($model))
        {
            $model = new GlobalSetting();
            $model->key = GlobalSetting::KEY_NX_PHONE_SDK_CONFIG_LIST;
        }

        $data = $this->request->post();
        if ($data && $model->load($data)  && $model->validate()) {
            if ($model->save()) {
                return $this->redirectMessage('添加成功', self::MSG_SUCCESS);
            } else {
                return $this->redirectMessage('添加失败', self::MSG_ERROR);
            }
        }
        return $this->render('nx-phone-sdk-add', [
            'model' => $model
        ]);
    }
}

<?php
namespace backend\controllers;

use Yii;
use yii\helpers\Url;
use yii\data\Pagination;
use common\models\CheckVersion;
use yii\web\ForbiddenHttpException;

/**
 * AdminUser controller
 */
class VersionController extends BaseController
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
     * @name 配置管理
     */
    public function actionAdd()
    {
        $model = new CheckVersion();
        $data = $this->request->post();
        if ($data && $model->load($data)) {
            if ($model->save() && $model->validate()) {
                return $this->redirectMessage(Yii::T('common', 'Add success'), self::MSG_SUCCESS, Url::toRoute('list'));
            } else {
                return $this->redirectMessage(Yii::T('common', 'Add fail'), self::MSG_ERROR);
            }
        }
        return $this->render('add', [
            'model' => $model,
            'type' => 'edit',
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
                return $this->redirectMessage(Yii::T('common', 'Update success'), self::MSG_ERROR);
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
}

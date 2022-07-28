<?php
namespace backend\controllers;

use backend\models\Merchant;
use backend\models\remind\RemindAdmin;
use common\helpers\RedisQueue;
use Yii;
use yii\helpers\Url;
use yii\data\Pagination;
use backend\models\AdminUserRole;
use backend\models\AdminUser;

/**
 * BackEndAdmin controller
 */
class AdminUserController extends BaseController {


    /**
     * @name Admin list
     * @name-CN 系统管理-系统管理员-管理员管理
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function actionList()
    {
        $search = [];
        $condition[] = 'and';
        $where[] = 'and';
        $merchantId = Yii::$app->user->identity->merchant_id;
        if(!empty($merchantId)){
            $condition[] = ['merchant_id' => $merchantId];
            $where[] = ['merchant_id' => $merchantId];
        }
        if ($this->request->get('search_submit')) { // 过滤
            $search = $this->request->get();
            $search['username'] = str_replace("'","", $search['username']);
            $search['phone'] = str_replace("'","", $search['phone']);
            if (isset($search['phone']) && $search['phone'] != '') {
                $condition[] = ['like', 'phone', trim($search['phone'])];
            }
            if (isset($search['username']) && $search['username'] != '') {
                $condition[] = ['like', 'username', trim($search['username'])];
            }
            if (isset($search['merchant_id']) && $search['merchant_id'] != '') {
                $condition[] = ['merchant_id' => $search['merchant_id']];
            }
            if (isset($search['role']) && $search['role'] != '') {
                $condition[] = ['like', 'role', trim($search['role'])];
            }
            if (isset($search['created_user']) && $search['created_user'] != '') {
                $condition[] = ['created_user' => $search['created_user']];
            }
            if (isset($search['open_status']) && $search['open_status'] != '') {
                $condition[] = ['open_status' => $search['open_status']];
            }
        }

        $query = AdminUser::find()->where($condition);
        if($this->isNotMerchantAdmin){
            $query->orderBy('id desc');
        }else{
            $query->andWhere(['open_status'=>[AdminUser::OPEN_STATUS_ON,AdminUser::OPEN_STATUS_LOCK]])->orderBy('id desc');
        }

        $countQuery = clone $query;
        $pages = new Pagination(['totalCount' => $countQuery->count('id',Yii::$app->get('db'))]);
        $pages->pageSize = 15;
        $users = $query->offset($pages->offset)->limit($pages->limit)->all(Yii::$app->get('db'));
        $role_lsit = [];
        $role_lsit_temp = AdminUserRole::find()->where($where)->select(['name','title'])->asArray()->all(Yii::$app->get('db'));
        foreach ($role_lsit_temp as $v) {
            $role_lsit[$v['name']] = $v['name'].'------'.$v['title'];
        }
        return $this->render('list', [
            'users' => $users,
            'role_lsit' => $role_lsit,
            'pages' => $pages,
            'isHiddenPhone' => $this->isHiddenPhone,
            'strategyOperating' => $this->strategyOperating,
            'isNotMerchantAdmin' => $this->isNotMerchantAdmin
        ]);
    }

    /**
     * 添加管理员
     *
     * @name Add admin
     */
    public function actionAdd()
    {
        $model = new AdminUser();
        $roles = $this->request->post('roles');
        if($roles){
            $model->role = implode(',',$roles);
        }
        if ($model->load($this->request->post()) && $model->validate()) {
            $model->created_user = Yii::$app->user->identity->username;
            $model->password = Yii::$app->security->generatePasswordHash($model->password);
            if(Yii::$app->user->identity->merchant_id){
               $model->merchant_id = Yii::$app->user->identity->merchant_id;
            }

            if (empty($this->request->post('to_view_merchant_id'))) {
                $model->to_view_merchant_id = Yii::$app->user->identity->merchant_id;
            } else {
                $model->to_view_merchant_id = implode(',', $this->request->post('to_view_merchant_id'));
            }

            if ($model->save(false)) {
                return $this->redirectMessage('success', self::MSG_SUCCESS, Url::toRoute('admin-user/list'));
            } else {
                return $this->redirectMessage('fail', self::MSG_ERROR);
            }
        }

        $roles = AdminUserRole::findAllSelected();
        $current_roles_arr = $current_user_groups_arr =[];
        $current_roles = Yii::$app->user->identity->role;
        if(!empty($current_roles)){
            $current_roles_arr = explode(',',$current_roles);
            $current_user_groups_arr = AdminUserRole::groups_of_roles($current_roles);
        }
        if(!$this->isNotMerchantAdmin){
            unset($roles[AdminUserRole::TYPE_DEFAULT]);
        }
        $arrMerchantIds = Merchant::getMerchantId();

        return $this->render('add', [
            'model' => $model,
            'roles' => $roles,
            'current_roles_arr' => $current_roles_arr,
            'current_user_groups_arr' => $current_user_groups_arr,
            'is_super_admin' => Yii::$app->user->identity->getIsSuperAdmin(),
            'arrMerchantIds' => $arrMerchantIds,
            'isNotMerchantAdmin' => $this->isNotMerchantAdmin,
            'strategyOperating' => $this->strategyOperating,
        ]);
    }

    /**
     * 编辑管理员
     *
     * @name Edit admin
     */
    public function actionEdit($id)
    {
        $model = AdminUser::findOne(intval($id));
        if (!$model || $model->username == AdminUser::SUPER_USERNAME) {
            return $this->redirectMessage('Non-existent or non-editable', self::MSG_ERROR);
        }

        if(Yii::$app->user->identity->merchant_id && Yii::$app->user->identity->merchant_id != $model->merchant_id){
            return $this->redirectMessage('Non-existent or non-editable', self::MSG_ERROR);
        }

        // 不验证密码
        $roles = $this->request->post('roles');
        if($roles){
            $model->role = implode(',',$roles);
        }
        $oldPhone = $model->phone;
        $starPhone = substr_replace($oldPhone,'*****',0,5);
        if ($model->load($this->request->post())) {
            if($this->isHiddenPhone && $starPhone == $model->phone){
                $model->phone = $oldPhone;
            }
            $message = '';
            /** @var RemindAdmin $remindAdmin */
            $remindAdmin = RemindAdmin::find()->where(['admin_user_id' => $model->id])->one();
            if($remindAdmin){
                if($remindAdmin->merchant_id !=  $model->merchant_id){
                    $remindAdmin->merchant_id = $model->merchant_id;
                    $remindAdmin->remind_group = 0;
                    $remindAdmin->save(false);
                    $message = ',user is remind admin,Due to changes in the merchant, the reminder group will automatically change to none;';
                }
            }
            if($model->validate(['role','phone'])){
                if (empty($this->request->post('to_view_merchant_id'))) {
                    $model->to_view_merchant_id = Yii::$app->user->identity->merchant_id;
                } else {
                    $model->to_view_merchant_id = implode(',', $this->request->post('to_view_merchant_id'));
                }
//            $model->merchant_id = $merchant_id;
                if ($model->save(false)) {
                    return $this->redirectMessage('success'.$message, self::MSG_SUCCESS, Url::toRoute('admin-user/list'));
                } else {
                    return $this->redirectMessage('fail', self::MSG_ERROR);
                }
            }
        }

        $roles = AdminUserRole::findAllSelected();
        $current_roles_arr = $current_user_groups_arr =[];
        $current_roles = Yii::$app->user->identity->role;
        if(!empty($current_roles)){
            $current_roles_arr = explode(',',$current_roles);
            $current_user_groups_arr = AdminUserRole::groups_of_roles($current_roles);
        }

        if(!$this->isNotMerchantAdmin){
            unset($roles[AdminUserRole::TYPE_DEFAULT]);
        }
        $arrMerchantIds = Merchant::getMerchantId();
        if($this->isHiddenPhone){
            $model->phone = $starPhone;
        }
        return $this->render('edit', [
            'model' => $model,
            'roles' => $roles,
            'current_roles_arr' => $current_roles_arr,
            'current_user_groups_arr' => $current_user_groups_arr,
            'is_super_admin' => Yii::$app->user->identity->getIsSuperAdmin(),
            'arrMerchantIds' => $arrMerchantIds,
            'isNotMerchantAdmin' => $this->isNotMerchantAdmin,
            'strategyOperating' => $this->strategyOperating,
        ]);
    }

    /**
     * 修改密码
     *
     * @name Change pwd
     */
    public function actionChangePwd($id)
    {
        $model = AdminUser::findOne(intval($id));
        if (!$model) {
            return $this->redirectMessage('Non-existent', self::MSG_ERROR);
        }

        if(Yii::$app->user->identity->merchant_id && Yii::$app->user->identity->merchant_id != $model->merchant_id){
            return $this->redirectMessage('Non-existent or non-editable', self::MSG_ERROR);
        }

        $model->password = '';
        if ($model->load($this->request->post()) && $model->validate(['password'])) {
            $model->password = Yii::$app->security->generatePasswordHash($model->password);
            if ($model->save(false)) {
                return $this->redirectMessage('success', self::MSG_SUCCESS, Url::toRoute('admin-user/list'));
            } else {
                return $this->redirectMessage('fail', self::MSG_ERROR);
            }
        }

        return $this->render('change-pwd', [
            'model' => $model,
        ]);
    }

    /**
     * 删除管理员
     *
     * @name Delete admin
     */
    public function actionDelete($id)
    {
        $model = AdminUser::findOne(intval($id));
        if (!$model || $model->username == AdminUser::SUPER_USERNAME) {
            return $this->redirectMessage('Non-existent or non-editable', self::MSG_ERROR);
        }
        if(Yii::$app->user->identity->merchant_id && Yii::$app->user->identity->merchant_id != $model->merchant_id){
            return $this->redirectMessage('Non-existent or non-editable', self::MSG_ERROR);
        }
        $model->open_status = AdminUser::OPEN_STATUS_OFF;
        $model->mark = $model->mark . '('.Yii::$app->user->identity->username.' del at '.date('Y-m-d H:i:s').')';
        $model->save(false);
        return $this->redirect(['admin-user/list']);
    }

    /**
     * @name AdminUserController 管理员账号恢复
     * @return string
     */
    public function actionRecovery($id)
    {
        $model = AdminUser::findOne(intval($id));
        if (!$model || $model->open_status != AdminUser::OPEN_STATUS_OFF) {
            return $this->redirectMessage('Non-existent or non-editable', self::MSG_ERROR);
        }
        $model->open_status = AdminUser::OPEN_STATUS_ON;
        $model->mark = $model->mark . '('.Yii::$app->user->identity->username.' recovery at '.date('Y-m-d H:i:s').')';
        $model->save(false);
        return $this->redirect(['admin-user/list']);
    }

    /**
     * 账号解锁
     *
     * @name unlock admin
     */
    public function actionUnlock($id)
    {
        $model = AdminUser::findOne(intval($id));
        if (!$model || $model->username == AdminUser::SUPER_USERNAME) {
            return $this->redirectMessage('Non-existent or non-editable', self::MSG_ERROR);
        }
        $model->open_status = 1;
        if($model->save(false))
        {
            $key = 'error_password_backend_'.$model->username;
            RedisQueue::newDel($key);
        }
        return $this->redirect(['admin-user/list']);
    }
}

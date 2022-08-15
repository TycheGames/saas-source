<?php
namespace backend\controllers;

use backend\models\AdminOperateLog;
use Yii;
use yii\helpers\Url;
use yii\data\Pagination;
use backend\models\AdminUserRole;
use backend\models\ActionModel;
use backend\models\AdminUser;
use yii\web\ForbiddenHttpException;
use yii\web\Response;

/**
 * BackEndAdmin controller
 */
class BackEndAdminUserController extends BaseController {

    /**
     * @name Admin list
     * @name-CN 系统管理-系统管理员-管理员管理
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function actionList()
    {
        $condition = ['AND'];
        if ($this->request->get('search_submit')) { // 过滤
            $search = $this->request->get();
            $search['username'] = str_replace("'","", $search['username']);
            $search['phone'] = str_replace("'","", $search['phone']);
            if (isset($search['phone']) && $search['phone'] != '') {
                $condition[] = ['like', 'phone', $search['phone']];
            }
            if (isset($search['username']) && $search['username'] != '') {
                $condition[] = ['like', 'username', $search['username']];
            }
            if (isset($search['role']) && $search['role'] != '') {
                $condition[] = ['like', 'role', $search['role']];
            }
        }

        $query = AdminUser::find()->where($condition)->andWhere(['open_status'=>AdminUser::OPEN_STATUS_ON])->orderBy('id desc');
        $countQuery = clone $query;
        $pages = new Pagination(['totalCount' => $countQuery->count('id',Yii::$app->get('db'))]);
        $pages->pageSize = 15;
        $users = $query->offset($pages->offset)->limit($pages->limit)->all(Yii::$app->get('db'));
        $role_lsit = [];
        $role_lsit_temp = AdminUserRole::find()->select(['name','title'])->asArray()->all(Yii::$app->get('db'));
        foreach ($role_lsit_temp as $v) {
            $role_lsit[$v['name']] = $v['name'].'------'.$v['title'];
        }
        return $this->render('list', [
            'users' => $users,
            'role_lsit' => $role_lsit,
            'pages' => $pages,
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

            if ($model->save(false)) {
                return $this->redirectMessage('success', self::MSG_SUCCESS, Url::toRoute('back-end-admin-user/list'));
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

        return $this->render('add', [
            'model' => $model,
            'roles' => $roles,
            'current_roles_arr' => $current_roles_arr,
            'current_user_groups_arr' => $current_user_groups_arr,
            'is_super_admin' => Yii::$app->user->identity->getIsSuperAdmin(),
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

        // 不验证密码
        $roles = $this->request->post('roles');
        if($roles){
            $model->role = implode(',',$roles);
        }

        if ($model->load($this->request->post()) && $model->validate(['role','phone'])) {
            if ($model->save(false)) {
                return $this->redirectMessage('success', self::MSG_SUCCESS, Url::toRoute('back-end-admin-user/list'));
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

        return $this->render('edit', [
            'model' => $model,
            'roles' => $roles,
            'current_roles_arr' => $current_roles_arr,
            'current_user_groups_arr' => $current_user_groups_arr,
            'is_super_admin' => Yii::$app->user->identity->getIsSuperAdmin(),
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

        $model->password = '';
        if ($model->load($this->request->post()) && $model->validate(['password'])) {
            $model->password = Yii::$app->security->generatePasswordHash($model->password);
            if ($model->save(false)) {
                return $this->redirectMessage('success', self::MSG_SUCCESS, Url::toRoute('back-end-admin-user/list'));
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

        $model->open_status = 0;
        $model->save(false);
        return $this->redirect(['back-end-admin-user/list']);
    }


    /**
     * @name Role list
     * @name-CN 系统管理-系统管理员-角色管理
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function actionRoleList()
    {
        $condition = ['AND', ['open_status' => 1]];

        if ($this->request->get('search_submit')) { // 过滤
            $search = $this->request->get();
            if (isset($search['group_id']) && $search['group_id']!='') {
                $condition[] = ['groups' => $search['group_id']];
            }
        }
        $roles = AdminUserRole::find()->where($condition)->all(Yii::$app->get('db'));
        $groups = AdminUserRole::$status;
        return $this->render('role-list', [
            'roles' => $roles,
            'groups' => $groups,
        ]);
    }

    /**
     * @name Role details
     *@name-CN 成员列表
     */
    public function actionRoleDetails()
    {
        $role = Yii::$app->request->get('role');
        if(!empty($role))
        {
            $adminUser = AdminUser::find()->where(['open_status' => AdminUser::OPEN_STATUS_ON])->asArray()->all();
            $arr = [];
            foreach ($adminUser as $key=>$val)
            {
                if($val['role'])
                {
                    $arr[$key]['role'] =explode(',',$val['role']);
                    $arr[$key]['username'] = $val['username'];
                    $arr[$key]['mark'] = $val['mark'];
                    $arr[$key]['phone'] = $val['phone'];
                    $arr[$key]['id'] =$val['id'];
                    $arr[$key]['created_at'] = $val['created_at'];
                }
            }
            $result = [];
            foreach ($arr as $k=>$v)
            {
                if(in_array($role,$v['role']))
                {
                    $result[] = $v;
                }
            }
        }
        return $this->render('role-detail',[
            'result'=> $result,
            'count' =>count($result),
        ]);
    }

    /**
     * 添加角色
     *
     * @name Add role
     */
    public function actionRoleAdd()
    {
        $model = new AdminUserRole();

        if ($model->load($this->request->post())) {
            $postPermissions = $this->request->post('permissions');
            $model->permissions = $postPermissions ? json_encode($postPermissions) : '';
            $model->created_user = Yii::$app->user->identity->username;
            $AdminUserRole = $this->request->post('AdminUserRole');
            $model->groups =$AdminUserRole['groups']?intval($AdminUserRole['groups']):0;

            $role = AdminUserRole::findOne(['name' => $model->name]);
            if(!empty($role)){
                return $this->redirectMessage('fail', self::MSG_ERROR);
            }
            if ($model->validate()) {
                if ($model->save()) {
                    return $this->redirectMessage('success', self::MSG_SUCCESS, Url::toRoute('back-end-admin-user/role-list'));
                } else {
                    return $this->redirectMessage('fail', self::MSG_ERROR);
                }
            }
        }

        $controllers = Yii::$app->params['permissionControllers'];
        $permissions = [];
        foreach ($controllers as $controller => $label) {
            $actions = [];
            $rf = new \ReflectionClass("backend\\controllers\\{$controller}");
            $methods = $rf->getMethods(\ReflectionMethod::IS_PUBLIC);
            foreach ($methods as $method) {
                if (strpos($method->name, 'action') === false || $method->name == 'actions') {
                    continue;
                }
                $actions[] = new ActionModel($method);
            }
            $permissions[$controller] = [
                'label' => $label,
                'actions' =>$actions,
            ];
        }

        $permissionChecks = $model->permissions ? json_decode($model->permissions, true) : [];

        $groups = AdminUserRole::$status;

        return $this->render('role-add', [
            'model' => $model,
            'permissions' => $permissions,
            'permissionChecks' => $permissionChecks,
            'groups' => $groups,
        ]);
    }

    /**
     * 编辑角色
     *
     * @name Edit role
     */
    public function actionRoleEdit($id)
    {
        $model = AdminUserRole::findOne(intval($id));
        if (!$model || $model->name == AdminUser::SUPER_ROLE) {
            return $this->redirectMessage('Non-existent or non-editable', self::MSG_ERROR);
        }

        if ($model->load($this->request->post())) {
            $postPermissions = $this->request->post('permissions');
            $model->permissions = $postPermissions ? json_encode($postPermissions) : '';
            if ($model->validate()) {
                if ($model->save()) {
                    return $this->redirectMessage('success', self::MSG_SUCCESS, Url::toRoute('back-end-admin-user/role-list'));
                } else {
                    return $this->redirectMessage('fail', self::MSG_ERROR);
                }
            }
        }

        $controllers = Yii::$app->params['permissionControllers'];
        unset($controllers['SmsController']);
        $permissions = [];
        foreach ($controllers as $controller => $label) {
            $actions = [];
            $rf = new \ReflectionClass("backend\\controllers\\{$controller}");
            $methods = $rf->getMethods(\ReflectionMethod::IS_PUBLIC);
            foreach ($methods as $method) {
                if (strpos($method->name, 'action') === false || $method->name == 'actions') {
                    continue;
                }
                $actions[] = new ActionModel($method);
            }
            $permissions[$controller] = [
                'label' => $label,
                'actions' =>$actions,
            ];
        }

        $permissionChecks = $model->permissions ? json_decode($model->permissions, true) : [];

        return $this->render('role-edit', [
            'model' => $model,
            'permissions' => $permissions,
            'permissionChecks' => $permissionChecks,
        ]);
    }

    /**
     * 删除角色
     *
     * @name Delete role
     */
    public function actionRoleDelete($id)
    {
        $model = AdminUserRole::findOne(intval($id));
        if (!$model || $model->name == AdminUser::SUPER_ROLE) {
            return $this->redirectMessage(Yii::T('common', 'Does not exist or cannot be deleted'), self::MSG_ERROR);
        }

        AdminUser::updateAll(['role' => ''], ['role' => $model->name]);
        $model->delete();
        return $this->redirect(['back-end-admin-user/role-list']);
    }


    /**
     * @name Visit list
     * @name-CN 系统管理-系统管理员-访问记录
     * @return string
     */
    public function actionVisitList() {
        $username = Yii::$app->request->get('username','');
        $url = Yii::$app->request->get('url','');
        $request_param= Yii::$app->request->get('request_param','');
        $admin_user_id = Yii::$app->request->get('admin_user_id','');
        $startTime = strtotime(Yii::$app->request->get('visit-start-time',''));
        $endTime = strtotime(Yii::$app->request->get('visit-end-time',''));
        $condition = ['AND'];
        if(!empty($username))
        {
            $condition[] = ['like', 'admin_user_name', $username];
        }
        if(!empty($admin_user_id))
        {
            $condition[] = ['admin_user_id' => $admin_user_id];
        }
        if(!empty($url))
        {
            $condition[] = ['like', 'route', $url];
        }
        if(!empty($request_param))
        {
            $condition[] = ['like', 'request_params', $request_param];
        }
        if(!empty($startTime))
        {
            $condition[] = ['>=', 'created_at', $startTime];
        }
        if(!empty($endTime))
        {
            $condition[] = ['<=', 'created_at', $endTime];
        }
        $adminOperateLog = AdminOperateLog::find()->where($condition);
        $pages = new Pagination(['totalCount' => $adminOperateLog->count('*',Yii::$app->get('db'))]);
        $page_size = Yii::$app->request->get('per-page',15);
        $pages->pageSize = $page_size;
        $visitLogs = $adminOperateLog->orderby('created_at DESC')->offset($pages->offset)->limit($pages->limit)->all(Yii::$app->get('db'));;
        return $this->render('visit-list',array(
            'visitLogs' => $visitLogs,
            'pages' => $pages,
        ));
    }
    /**
     * @name Visit detail
     * @name-CN 获取访问详情
     */
    public function actionVisitDetail()
    {
        $this->response->format = Response::FORMAT_JSON;
        $id = intval(Yii::$app->request->get('id'));
        if(!empty($id))
        {
            $visitInfo = AdminOperateLog::find()->where(['id'=>$id])->asArray()->one(Yii::$app->get('db'));
            //$create_name = AdminUser::getName($problemInfo['creater_id']);
            echo json_encode(array(
                'code' => 0,
                'user_id' => $visitInfo['admin_user_id'],
                'user_name' => $visitInfo['admin_user_name'],
                'request_type' => $visitInfo['request'],
                'route' => $visitInfo['route'],
                'params' => $visitInfo['request_params'],
                'ip' => long2ip($visitInfo['ip']),
                'created_at' => date('Y-m-d H:i:s',$visitInfo['created_at']),
            ));
        }
    }

}

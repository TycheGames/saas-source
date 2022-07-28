<?php
namespace backend\controllers;

use backend\models\AdminLoginErrorLog;
use backend\models\AdminOperateLog;
use backend\models\AdminNxUser;
use backend\models\Merchant;
use Yii;
use yii\helpers\Url;
use yii\data\Pagination;
use backend\models\AdminUserRole;
use backend\models\ActionModel;
use backend\models\AdminUser;
use yii\web\ForbiddenHttpException;
use yii\web\Response;
use yii\web\UploadedFile;

/**
 * BackEndAdmin controller
 */
class BackEndAdminUserController extends BaseController {

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
        if (!empty($merchantId)) {
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
            if (isset($search['role']) && $search['role'] != '') {
                $condition[] = ['like', 'role', trim($search['role'])];
            }
        }

        $query = AdminUser::find()->where($condition)->andWhere(['open_status'=>AdminUser::OPEN_STATUS_ON])->orderBy('id desc');
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

        $arrMerchantIds = Merchant::getMerchantId();

        return $this->render('add', [
            'model' => $model,
            'roles' => $roles,
            'current_roles_arr' => $current_roles_arr,
            'current_user_groups_arr' => $current_user_groups_arr,
            'is_super_admin' => Yii::$app->user->identity->getIsSuperAdmin(),
            'arrMerchantIds' => $arrMerchantIds
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

        if ($model->load($this->request->post()) && $model->validate(['role','phone'])) {

            if (empty($this->request->post('to_view_merchant_id'))) {
                $model->to_view_merchant_id = Yii::$app->user->identity->merchant_id;
            } else {
                $model->to_view_merchant_id = implode(',', $this->request->post('to_view_merchant_id'));
            }

//            $model->merchant_id = $merchant_id;
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

        $arrMerchantIds = Merchant::getMerchantId();

        return $this->render('edit', [
            'model' => $model,
            'roles' => $roles,
            'current_roles_arr' => $current_roles_arr,
            'current_user_groups_arr' => $current_user_groups_arr,
            'is_super_admin' => Yii::$app->user->identity->getIsSuperAdmin(),
            'arrMerchantIds' => $arrMerchantIds
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
        if(Yii::$app->user->identity->merchant_id && Yii::$app->user->identity->merchant_id != $model->merchant_id){
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
        $search = [];
        $condition[] = 'and';
        $condition[] = ['open_status' => 1];
        if(Yii::$app->user->identity->merchant_id){
            $condition[] = ['merchant_id' => Yii::$app->user->identity->merchant_id];
        }
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
            $condition[] = 'and';
            if(Yii::$app->user->identity->merchant_id){
                $condition[] = ['merchant_id' => Yii::$app->user->identity->merchant_id];
            }
            $adminUser = AdminUser::find()->where($condition)->andWhere(['open_status' => AdminUser::OPEN_STATUS_ON])->asArray()->all();
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
            $model->merchant_id = Yii::$app->user->identity->merchant_id;

            $role = AdminUserRole::findOne(['name' => $model->name, 'merchant_id' => $model->merchant_id]);
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
        if(Yii::$app->user->identity->merchant_id && $model->merchant_id != Yii::$app->user->identity->merchant_id){
            return $this->redirectMessage(Yii::T('common', 'Does not exist or cannot be deleted'), self::MSG_ERROR);
        }
        AdminUser::updateAll(['role' => ''], ['role' => $model->name, 'merchant_id' => $model->merchant_id]);
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
        $condition[] = 'and';
        if(Yii::$app->user->identity->merchant_id){
            $condition[] = ['merchant_id' => Yii::$app->user->identity->merchant_id];
        }
        if(!empty($username))
        {
            $condition[] = ['like', 'admin_user_name', trim($username)];
        }
        if(!empty($admin_user_id))
        {
            $condition[] = ['admin_user_id' => $admin_user_id];
        }
        if(!empty($url))
        {
            $condition[] = ['like', 'route', trim($url)];
        }
        if(!empty($request_param))
        {
            $condition[] = ['like', 'request_params', trim($request_param)];
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


    /**
     * @name Admin nx-list
     * @name-CN 系统管理-催收员牛信账号绑定
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function actionNxList()
    {
        $username = $this->request->get('username');
        $nx_name = $this->request->get('nx_name');
        $query = AdminNxUser::find()
            ->from(AdminNxUser::tableName().' A')
            ->select(['B.username','A.id','A.collector_id','A.nx_name','A.password','A.status','A.type'])
            ->leftJoin(AdminUser::tableName(). ' B','A.collector_id = B.id')
            ->andFilterWhere(['B.username' => $username])
            ->andFilterWhere(['nx_name' => $nx_name])
            ->orderBy('A.id desc');

        $countQuery = clone $query;
        $pages = new Pagination(['totalCount' => $countQuery->count('A.id')]);
        $pages->pageSize = $this->request->get('page_size',15);
        $users = $query->offset($pages->offset)->limit($pages->limit)->asArray()->all();


        return $this->render('nx-list', [
            'users' => $users,
            'pages' => $pages,
            'url' => $this->request->url
        ]);
    }

    /**
     * @name 系统管理-催收员牛信账号绑定-添加账号
     * @return string
     */
    public function actionNxAdd()
    {
        $nxModel = new AdminNxUser();

        if(Yii::$app->request->isPost)
        {
            $nxModel->load($this->request->post());
            $collector_id = $nxModel->collector_id;
            $type     = $nxModel->type;
            $nx_name  = $nxModel->nx_name;
            $user_info = AdminNxUser::find()
                ->where(['collector_id' => $collector_id, 'status' => AdminNxUser::STATUS_ENABLE, 'type' => $type])
                ->one();
            if($user_info) {
                return $this->redirectMessage('已存在启用的牛信账号,不可添加,请禁用或者修改原账号!', self::MSG_ERROR);
            }
            $user_info1 = AdminNxUser::find()
                ->where(['nx_name' => $nx_name])
                ->one();
            if($user_info1) {
                return $this->redirectMessage('该牛信账号已被使用!', self::MSG_ERROR);
            }
            if($nxModel->validate() && $nxModel->save())
            {
                return $this->redirectMessage('success', self::MSG_SUCCESS, Url::toRoute('back-end-admin-user/nx-list'));
            }else {
                return $this->redirectMessage('fail', self::MSG_ERROR);
            }
        }
        return $this->render('nx-add', [
            'accountModel' => $nxModel
        ]);
    }

    /**
     * @name 系统管理-牛信账号绑定-修改账号
     * @param $id
     * @return string
     */
    public function actionNxEdit($id){
        $nxModel = AdminNxUser::findOne(intval($id));
        if (!$nxModel) {
            return $this->redirectMessage('数据不存在', self::MSG_ERROR);
        }
        if(yii::$app->request->isPost)
        {
            if($nxModel->load($this->request->post()) && $nxModel->validate() && $nxModel->save())
            {
                return $this->redirectMessage('success', self::MSG_SUCCESS, Url::toRoute('back-end-admin-user/nx-list'));
            }else {
                return $this->redirectMessage('fail', self::MSG_ERROR);
            }
        }

        return $this->render('nx-add', [
            'accountModel' => $nxModel
        ]);
    }

    /**
     * @name 系统管理-牛信账号绑定-删除账号
     * @param $id
     * @return string
     */
    public function actionNxDel($id){
        $nxModel = AdminNxUser::findOne(intval($id));
        if (!$nxModel) {
            return $this->redirectMessage('数据不存在', self::MSG_ERROR);
        }
        if($nxModel->delete()){
            return $this->redirectMessage('删除成功', self::MSG_SUCCESS,  Url::toRoute(['nx-list']));
        }else{
            return $this->redirectMessage('删除失败', self::MSG_ERROR,  Url::toRoute(['nx-list']));
        }
    }

    /**
     * @name 批量绑定催收员与牛信账号
     */
    public function actionNxBatchAdd()
    {
        if ($this->request->post()) {
            $file = UploadedFile::getInstanceByName('files');
            if (!$file) {
                return $this->redirectMessage('上传失败', self::MSG_ERROR);
            }
            $extension = $file->extension;
            if ($extension != 'csv') {
                return $this->redirectMessage('请导入正确格式文件', self::MSG_ERROR);
            }
            $path = \Yii::getAlias('@backend/web/tmp/');
            if (!file_exists($path)) {
                \yii\helpers\BaseFileHelper::createDirectory($path);
            }
            $url = $path . $file->baseName . date("YmdHis") . '.' . $file->extension;
            $re = $file->saveAs($url);
            if (!$re) {
                return $this->redirectMessage('上传失败', self::MSG_ERROR);
            }
            $file = fopen($url, "r");
            $i = $y = $f = 0; //定义标识
            $error_data = [];
            while (!feof($file)) {
                $tmp = fgetcsv($file);
                if ($f < 1) {
                    $f++;
                    continue;
                }
                $y++;
                //整理数据
                $new_tmp = AdminNxUser::getNewTmp($tmp);
                //查询催收员是否已经存在开启账号
                $_nx_collection = AdminNxUser::queryNxAdmin($new_tmp['collector_id'], $new_tmp['type']);
                //查询催收员是否存在
                $_admin_collection = AdminUser::find()->where(['id' => $new_tmp['collector_id']])->asArray()->all();
                //查询牛信账号是否被使用
                $_nx_username = AdminNxUser::find()->where(['nx_name' => $new_tmp['nx_name']])->asArray()->all();
                //数据不在定义范围内跳过
                if (count($new_tmp) != 5 || $_nx_collection || !$_admin_collection || $_nx_username) {
                    $error_data[] = $new_tmp; //将失败的数据放入数组中
                    continue;
                }
                $admin_user = AdminNxUser::queryNxAdmin($new_tmp['collector_id'], $new_tmp['type']);
                if (empty($admin_user)) {

                    $model = new AdminNxUser();
                    if ($model->load($new_tmp, '')) {
                        if (!$model->validate()) {
                            $error_data[] = $new_tmp; //将失败的数据放入数组中
                            continue;
                        }
                        $model->collector_id = $new_tmp['collector_id'];
                        $model->nx_name = $new_tmp['nx_name'];
                        $model->password = $new_tmp['password'];
                        $model->status = $new_tmp['status'];
                        $model->type = $new_tmp['type'];
                        if (!$model->save(false)) {
                            $error_data[] = $new_tmp; //将失败的数据放入数组中
                            continue;
                        }
                    }
                    $admin_user = AdminNxUser::queryNxAdmin($new_tmp['collector_id'],$new_tmp['type']);
                    if (empty($admin_user)) {
                        $error_data[] = $new_tmp; //将失败的数据放入数组中
                        continue;
                    }
                    $i++;
                }
            }
            $url = Url::toRoute(['back-end-admin-user/error-export','data'=>$error_data]);
            if(($y - 1) != $i){
                return $this->redirectMessage('导入' . ($y - 1) . '条,成功' . $i . '条!   <a href="'.$url.'">下载失败数据</a>', self::MSG_SUCCESS);
            }else{
                return $this->redirectMessage('导入' . ($y - 1) . '条,成功' . $i . '条!', self::MSG_SUCCESS,Url::toRoute(['back-end-admin-user/nx-list']));
            }

        }
    }

    /**
     * @name 失败数据导出
     */
    public function actionErrorExport(){
        $data = $this->request->get('data');
        if($data){
            $this->_setcsvHeader('失败数据导出.csv');
            $items = [];
            foreach ($data as $k=>$value) {
                $items[] = [
                    '催收员id'=>$value['collector_id'],
                    '牛信用户名' =>$value['nx_name'],
                    '牛信密码'=>$value['password'],
                    '状态'=>$value['status'],
                    '类型' =>$value['type'],
                ];
            }
            echo $this->_array2csv($items);
            exit;
        }
    }


    /**
     * @name login failed record
     * @name-CN 系统管理-系统管理员-登陆失败记录
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function actionLoginRecords()
    {
        $condition = [];
        if ($this->request->get('search_submit')) { // 过滤
            $search = $this->request->get();
            if (!empty($search['username'])) {
                $condition[] = ['username' => trim($search['username'])];
            }
        }
        $query = AdminLoginErrorLog::find();
        foreach ($condition as $item)
        {
            $query->andFilterWhere($item);
        }
        $countQuery = clone $query;
        $pages = new Pagination(['totalCount' => $countQuery->count('id')]);
        $pages->pageSize = $this->request->get('page_size',15);
        $models = $query->orderBy(['id' => SORT_DESC])->offset($pages->offset)->limit($pages->limit)->all();

        return $this->render('login-records', [
            'models' => $models,
            'pages' => $pages,
        ]);
    }


    /**
     * @name Admin lock-list
     * @name-CN 系统管理-账号解锁-账号解锁
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function actionLockList()
    {
        $condition[] = 'and';
        $query = AdminUser::find()->where($condition)->andWhere(['open_status'=>AdminUser::OPEN_STATUS_LOCK])->orderBy('id desc');
        $countQuery = clone $query;
        $pages = new Pagination(['totalCount' => $countQuery->count('id',Yii::$app->get('db'))]);
        $pages->pageSize = $this->request->get('page_size',15);;
        $users = $query->offset($pages->offset)->limit($pages->limit)->all(Yii::$app->get('db'));
        $role_lsit = [];
        $role_lsit_temp = AdminUserRole::find()->select(['name','title'])->asArray()->all(Yii::$app->get('db'));
        foreach ($role_lsit_temp as $v) {
            $role_lsit[$v['name']] = $v['name'].'------'.$v['title'];
        }
        return $this->render('lock-list', [
            'users' => $users,
            'role_lsit' => $role_lsit,
            'pages' => $pages,
        ]);
    }

}

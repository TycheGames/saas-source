<?php
namespace callcenter\controllers;

use backend\models\Merchant;
use callcenter\models\AdminLoginErrorLog;
use callcenter\models\AdminNxUser;
use callcenter\models\loan_collection\LoanCollectionOrderAll;
use callcenter\models\loan_collection\UserCompany;
use callcenter\models\CollectionAdminOperateLog;
use callcenter\models\search\AdminListSearch;
use callcenter\models\search\CollectionOperateListSearch;
use common\helpers\RedisQueue;
use Yii;
use yii\helpers\Url;
use yii\data\Pagination;
use callcenter\models\AdminUserRole;
use callcenter\models\ActionModel;
use callcenter\models\AdminUser;
use yii\web\ForbiddenHttpException;
use yii\web\UploadedFile;

/**
 * AdminUser controller
 */
class AdminUserController extends BaseController
{
    public function beforeAction($action)
    {
        if(parent::beforeAction($action))
        {
            if(Yii::$app->user->identity->merchant_id){
                throw new ForbiddenHttpException('权限不足');
            }
            return true;
        }else{
            return false;
        }
    }

	/**
	 * 管理员列表
	 * 
	 * @name 系统管理员-管理员管理
     * @return string
	 */
	public function actionList()
	{

        $query = AdminUser::find()->where([
            'merchant_id' => $this->merchantIds,
        ]);


        if ($this->request->get('search_submit')) { // 过滤
            $searchForm = new AdminListSearch();
            $searchArray = $searchForm->search(yii::$app->request->get());
            foreach ($searchArray as $item)
            {
                $query->andFilterWhere($item);
            }
        }
		$roleList = [];
        $roleListTemp = AdminUserRole::find()
            ->select(['name','title'])
            ->cache(600)
            ->asArray()
            ->all();
        foreach ($roleListTemp as $v) {
            $roleList[$v['name']] = $v['name'].'---'.$v['title'];
        }
        $totalQuery = clone $query;
        $pages = new Pagination(['totalCount' => $totalQuery->count()]);
        $pages->pageSize = $this->request->get('page_size',15);
        $users = $query
            ->orderBy(['id' => SORT_DESC])
            ->offset($pages->offset)
            ->limit($pages->limit)
            ->all();
        return $this->render('list', [
            'users'         => $users,
            'role_lsit'     => $roleList,
            'pages'         => $pages,
            'isHiddenPhone' => $this->isHiddenPhone,
            'isNotMerchantAdmin' => $this->isNotMerchantAdmin
        ]);
	}


    /**
     * 添加管理员(这里只添加经理)
     * @name 系统管理员-添加管理员
     * @return string
     * @throws
     */
    public function actionAdd()
    {
        $model = new AdminUser();
        $roles = $this->request->post('roles');
        if ($roles) {
            $model->role = $roles;
        }
        if ($model->load($this->request->post(), null, $this->strategyOperating) && $model->validate()) {
            $model->created_user = Yii::$app->user->identity->username;
            $model->outside = empty($model->outside) ? 0 : $model->outside;
            $model->group = empty($model->group) ? 0 : $model->group;
            $model->group_game = empty($model->group_game) ? 0 : $model->group_game;
            $model->password = Yii::$app->security->generatePasswordHash($model->password);
            if ($this->isNotMerchantAdmin) {
                $model->to_view_merchant_id = implode(',', $this->request->post('to_view_merchant_id'));
            } else {
                $model->merchant_id = $this->merchantIds;
                $model->to_view_merchant_id = $this->merchantIds;
            }
            if ($model->save(false)) {
                return $this->redirectMessage('添加成功', self::MSG_SUCCESS, Url::toRoute('admin-user/list'));
            } else {
                return $this->redirectMessage('添加失败', self::MSG_ERROR);
            }
        }
        $roles = AdminUserRole::findAllSelected();
        $current_roles_arr = $current_user_groups_arr = [];
        $current_roles = Yii::$app->user->identity->role;
        if (!empty($current_roles)) {
            $current_roles_arr = explode(',', $current_roles);
            $current_user_groups_arr = AdminUserRole::groups_of_roles($current_roles);
        }
        $companies = [];
        $userCompany = UserCompany::find()
            ->select(['id', 'real_title', 'merchant_id'])
            ->where(['merchant_id' => $this->merchantIds])->asArray()->all();
        foreach ($userCompany as $item) {
            $companies[$item['merchant_id']][$item['id']] = $item['real_title'];
        }
        $defaultCompanies = $companies[Yii::$app->user->identity->merchant_id] ?? [];
        return $this->render('add', [
            'model'                   => $model,
            'roles'                   => $roles,
            'current_roles_arr'       => $current_roles_arr,
            'current_user_groups_arr' => $current_user_groups_arr,
            'is_super_admin'          => Yii::$app->user->identity->getIsSuperAdmin(),
            'isNotMerchantAdmin'      => $this->isNotMerchantAdmin,
            'strategyOperating'       => $this->strategyOperating,
            'companys'                => $companies,
            'defaultCompanys'         => $defaultCompanies,
            'arrMerchantIds'          => Merchant::getMerchantByIds($this->merchantIds, $this->isNotMerchantAdmin),
        ]);
    }

    /**
     * 编辑管理员
     *
     * @name self 编辑管理员
     * @return string
     */
    public function actionEdit($id)
    {
        $model = AdminUser::findOne(intval($id));
        if (!$model || $model->username == AdminUser::SUPER_USERNAME) {
            return $this->redirectMessage('不存在或者不可编辑', self::MSG_ERROR);
        }

        // 不验证密码
        $roles = $this->request->post('roles');
        if ($roles) {
            $model->role = $roles;
        }
        $oldPhone = $model->phone;
        $starPhone = substr_replace($oldPhone, '*****', 0, 5);
        if ($model->load($this->request->post(), null, $this->strategyOperating)) {
            if ($this->isHiddenPhone && $starPhone == $model->phone) {
                $model->phone = $oldPhone;
            }
            if (empty($this->request->post('to_view_merchant_id'))) {
                $model->to_view_merchant_id = Yii::$app->user->identity->merchant_id;
            } else {
                $model->to_view_merchant_id = implode(',', $this->request->post('to_view_merchant_id'));
            }
            if ($model->validate(['role', 'phone', 'outside', 'group', 'group_game'])) {
                if ($model->save(false)) {
                    return $this->redirectMessage('编辑成功', self::MSG_SUCCESS, Url::toRoute('admin-user/list'));
                } else {
                    return $this->redirectMessage('编辑失败', self::MSG_ERROR);
                }
            }
        }

        $roles = AdminUserRole::findAllSelected();
        $current_roles_arr = $current_user_groups_arr = [];
        $current_roles = Yii::$app->user->identity->role;
        if (!empty($current_roles)) {
            $current_roles_arr = explode(',', $current_roles);
            $current_user_groups_arr = AdminUserRole::groups_of_roles($current_roles);
        }
        if ($this->isHiddenPhone) {
            $model->phone = $starPhone;
        }
        $companies = [];
        $userCompany = UserCompany::find()
            ->select(['id', 'real_title', 'merchant_id'])
            ->where(['merchant_id' => $this->merchantIds])
            ->asArray()
            ->all();
        foreach ($userCompany as $item) {
            $companies[$item['merchant_id']][$item['id']] = $item['real_title'];
        }
        $defaultCompanies = $companies[$model->merchant_id] ?? [];
        return $this->render('edit', [
            'model'                   => $model,
            'roles'                   => $roles,
            'current_roles_arr'       => $current_roles_arr,
            'current_user_groups_arr' => $current_user_groups_arr,
            'is_super_admin'          => Yii::$app->user->identity->getIsSuperAdmin(),
            'isNotMerchantAdmin'      => $this->isNotMerchantAdmin,
            'companys'                => $companies,
            'defaultCompanys'         => $defaultCompanies,
            'strategyOperating'       => $this->strategyOperating,
            'arrMerchantIds'          => Merchant::getMerchantByIds($this->merchantIds, $this->isNotMerchantAdmin),
        ]);
    }

    /**
     * 修改密码
     *
     * @name self 修改密码
     * @return string
     * @throws
     */
    public function actionChangePwd($id)
    {
        $model = AdminUser::findOne(intval($id));
        if (!$model) {
            return $this->redirectMessage('不存在', self::MSG_ERROR);
        }

        $model->password = '';
        if ($model->load($this->request->post()) && $model->validate(['password'])) {
            $model->password = Yii::$app->security->generatePasswordHash($model->password);
            if ($model->save(false)) {
                return $this->redirectMessage('修改成功', self::MSG_SUCCESS, Url::toRoute('admin-user/list'));
            } else {
                return $this->redirectMessage('修改失败', self::MSG_ERROR);
            }
        }

        return $this->render('change-pwd', [
            'model' => $model,
        ]);
    }


    /**
	 * 删除管理员
	 *
	 * @name self 删除管理员
     * @return string
     * @throws
	 */
	public function actionDelete($id)
	{
		$model = AdminUser::findOne(intval($id));
		if (!$model || $model->username == AdminUser::SUPER_USERNAME) {
			return $this->redirectMessage('不存在或者不可删除', self::MSG_ERROR);
		}
        if(LoanCollectionOrderAll::find()->where(['current_collection_admin_user_id' => $id])->exists()){
            return $this->redirectMessage('有分派订单不可删除', self::MSG_ERROR);
        }
        $model->open_status = AdminUser::OPEN_STATUS_OFF;
        $model->mark = $model->mark . '('.Yii::$app->user->identity->username.'于'.date('Y-m-d H:i:s').'操作删除)';
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
        $model->mark = $model->mark . '('.Yii::$app->user->identity->username.'于'.date('Y-m-d H:i:s').'操作恢复)';
        $model->save(false);
        return $this->redirect(['admin-user/list']);
    }

    /**
     * 账号解锁
     *
     * @name self 账号解锁
     * @return string
     */
    public function actionUnlock($id)
    {
        $model = AdminUser::findOne(intval($id));
        if (!$model || $model->username == AdminUser::SUPER_USERNAME) {
            return $this->redirectMessage('不存在或者不可删除', self::MSG_ERROR);
        }
        $model->open_status = 1;
        if($model->save(false))
        {
            $key = 'error_password_callcenter_'.$model->username;
            RedisQueue::newDel($key);
        }
        return $this->redirect(['admin-user/list']);
    }
	
	/**
	 * 角色列表
	 * 不用分页
	 * 
	 * @name self 角色管理
     * @return string
	 */
	public function actionRoleList()
	{
		$roles = AdminUserRole::find()->all();
		$groups = AdminUserRole::$groups_map;
		return $this->render('role-list', [
			'roles' => $roles,
			'groups' => $groups,
		]);
	}
	
	/**
	 * 添加角色
	 * 
	 * @name self 添加角色
     * @return string
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
			
			if ($model->validate()) {
				if ($model->save()) {
					return $this->redirectMessage('添加成功', self::MSG_SUCCESS, Url::toRoute('admin-user/role-list'));
				} else {
					return $this->redirectMessage('添加失败', self::MSG_ERROR);
				}
			}
		}
		
		$controllers = Yii::$app->params['permissionControllers'];
		$permissions = [];
		foreach ($controllers as $controller => $label) {
			$actions = [];
			$rf = new \ReflectionClass("callcenter\\controllers\\{$controller}");
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
		
		$groups = AdminUserRole::$groups_map;

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
	 * @name self 编辑角色
     * @return string
	 */
	public function actionRoleEdit($id)
	{
		$model = AdminUserRole::findOne(intval($id));
		if (!$model || $model->name == AdminUser::SUPER_ROLE) {
			return $this->redirectMessage('不存在或者不可编辑', self::MSG_ERROR);
		}
	
		if ($model->load($this->request->post())) {
			$postPermissions = $this->request->post('permissions');
			$model->permissions = $postPermissions ? json_encode($postPermissions) : '';
			if ($model->validate()) {
				if ($model->save()) {
					return $this->redirectMessage('编辑成功', self::MSG_SUCCESS, Url::toRoute('admin-user/role-list'));
				} else {
					return $this->redirectMessage('编辑失败', self::MSG_ERROR);
				}
			}
		}
	
		$controllers = Yii::$app->params['permissionControllers'];
		$permissions = [];
		foreach ($controllers as $controller => $label) {
			$actions = [];
			$rf = new \ReflectionClass("callcenter\\controllers\\{$controller}");
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
	 * @name self 删除角色
     * @return string
     * @throws
	 */
	public function actionRoleDelete($id)
	{
		$model = AdminUserRole::findOne(intval($id));
		if (!$model || $model->name == AdminUser::SUPER_ROLE) {
			return $this->redirectMessage('不存在或者不可删除', self::MSG_ERROR);
		}
		AdminUser::updateAll(['role' => ''], ['role' => $model->name]);
		$model->delete();
		return $this->redirect(['admin-user/role-list']);
	}

	/**
	 * @name self 根据手机号获取登录账户信息
     * @return string
	 */
	public function actionPhoneAjax(){
		$phone = $this->request->get('phone');
		$user = array();
		$res = AdminUser::phone($phone);
		if(!empty($res)){
			$user['username'] = $res->username;
			$user['phone'] = $res->phone;
		}
		return json_encode($user);
	}

    /**
     * @name self 访问记录
     * @return string
     */
    public function actionCollectionOperateList() {

        if(Yii::$app->request->get('search_submit')){
            $searchForm = new CollectionOperateListSearch();
            $searchArray = $searchForm->search(yii::$app->request->get());
        }else{
            $searchArray = [
                ['>=', 'created_at', strtotime('today')]
            ];
        }

        $loanCollection = AdminUser::find()->cache(120)->all();
        $outsides = UserCompany::find()->where(['status'=>UserCompany::USING])->all();
        $outside_list = [];
        foreach ($outsides as $val)
        {
            $outside_list[$val['id']] = $val['title'];
        }
        $collection_list = [];
        foreach ($loanCollection as $val)
        {
            $collection_list[$val['id']] = $val;
        }
        $adminOperateLog = CollectionAdminOperateLog::find();

        foreach ($searchArray as $item)
        {
            $adminOperateLog->andFilterWhere($item);
        }
        $pages = new Pagination(['totalCount' => 99999]);
        $page_size = Yii::$app->request->get('page_size',15);
        $pages->pageSize = $page_size;
        $visitLogs = $adminOperateLog
            ->orderby(['created_at' => SORT_DESC])
            ->offset($pages->offset)
            ->limit($pages->limit)
            ->all();;
        return $this->render('collection-operate-list',array(
            'visitLogs' => $visitLogs,
            'pages' => $pages,
            'outside_list'=>$outside_list,
            'collection_list'=>$collection_list
        ));
    }

    /**
     * @name 系统管理-催收员牛信账号绑定
     * @return string
     * @throws
     */
    public function actionNxList()
    {
        if(Yii::$app->user->identity->merchant_id){
            throw new ForbiddenHttpException(Yii::T('common', 'Insufficient permissions'));
        }
        $username = $this->request->get('username');
        $nx_name = $this->request->get('nx_name');
        $collector_id = $this->request->get('collector_id');
        $query = AdminNxUser::find()
            ->from(AdminNxUser::tableName().' A')
            ->select(['B.username','A.id','A.collector_id','A.nx_name','A.password','A.status','A.type'])
            ->leftJoin(AdminUser::tableName(). ' B','A.collector_id = B.id')
            ->andFilterWhere(['like', 'B.username', $username])
            ->andFilterWhere(['nx_name' => $nx_name])
            ->andFilterWhere(['collector_id' => $collector_id])
            ->orderBy('A.id desc');

        $countQuery = clone $query;
        $pages = new Pagination(['totalCount' => $countQuery->cache(60)->count('A.id')]);
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
     * @throws
     */
    public function actionNxAdd()
    {
        if(Yii::$app->user->identity->merchant_id){
            throw new ForbiddenHttpException(Yii::T('common', 'Insufficient permissions'));
        }
        $nxModel = new AdminNxUser();
        if(Yii::$app->request->isPost)
        {
            $nxModel->load($this->request->post());
            $collector_id = $nxModel->collector_id;
            $type     = $nxModel->type;
            $nx_name  = $nxModel->nx_name;
            $user_info = AdminNxUser::find()
                ->where([
                    'collector_id' => $collector_id,
                    'status' => AdminNxUser::STATUS_ENABLE,
                    'type' => $type
                ])
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
                return $this->redirectMessage('success', self::MSG_SUCCESS, Url::toRoute('admin-user/nx-list'));
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
     * @return string
     * @throws
     */
    public function actionNxEdit($id){
        if(Yii::$app->user->identity->merchant_id){
            throw new ForbiddenHttpException(Yii::T('common', 'Insufficient permissions'));
        }
        $nxModel = AdminNxUser::findOne(intval($id));
        if (!$nxModel) {
            return $this->redirectMessage('数据不存在', self::MSG_ERROR);
        }
        if(yii::$app->request->isPost)
        {
            if($nxModel->load($this->request->post()) && $nxModel->validate() && $nxModel->save())
            {
                return $this->redirectMessage('success', self::MSG_SUCCESS, Url::toRoute('admin-user/nx-list'));
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
     * @return string
     * @throws
     */
    public function actionNxDel($id){
        if(Yii::$app->user->identity->merchant_id){
            throw new ForbiddenHttpException(Yii::T('common', 'Insufficient permissions'));
        }
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
     * @name self 批量绑定催收员与牛信账号
     * @return string
     * @throws
     */
    public function actionNxBatchAdd()
    {
        if(Yii::$app->user->identity->merchant_id){
            throw new ForbiddenHttpException(Yii::T('common', 'Insufficient permissions'));
        }
        if ($this->request->post()) {
            $file = UploadedFile::getInstanceByName('files');
            if (!$file) {
                return $this->redirectMessage('上传失败', self::MSG_ERROR);
            }
            $extension = $file->extension;
            if ($extension != 'csv') {
                return $this->redirectMessage('请导入正确格式文件', self::MSG_ERROR);
            }
            $path = \Yii::getAlias('@callcenter/web/tmp/');
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
                //$fileType = mb_detect_encoding($new_tmp['real_name'], array('UTF-8', 'GBK', 'LATIN1', 'BIG5', 'GB2312'));
//                if ($fileType != 'UTF-8') {
//                    $new_tmp['real_name'] = mb_convert_encoding($new_tmp['real_name'], 'utf-8', $fileType);
//                }
                //整理数据
                $new_tmp = AdminNxUser::getNewTmp($tmp);
                //查询催收员是否已经存在开启账号
                $_nx_collection = AdminNxUser::queryNxAdmin($new_tmp['collector_id'], $new_tmp['type']);
                //查询催收员是否存在
                $_admin_collection = AdminUser::find()->where(['id' => $new_tmp['collector_id']])->asArray()->one();
                //查询牛信账号是否被使用
                $_nx_username = AdminNxUser::find()->where(['nx_name' => $new_tmp['nx_name']])->asArray()->one();
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
            $url = Url::toRoute(['admin-user/error-export','data'=>$error_data]);
            if(($y - 1) != $i){
                return $this->redirectMessage('导入' . ($y - 1) . '条,成功' . $i . '条!   <a href="'.$url.'">下载失败数据</a>', self::MSG_SUCCESS);
            }else{
                return $this->redirectMessage('导入' . ($y - 1) . '条,成功' . $i . '条!', self::MSG_SUCCESS,Url::toRoute(['admin-user/nx-list']));
            }

        }
    }

    /**
     * @name self 失败数据导出
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
     * @name Admin lock-list
     * @name-CN 系统管理-账号解锁-账号解锁
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function actionLockList()
    {
        $role = $this->request->get('role','');
        $users = AdminUser::find()
            ->where(['open_status' => AdminUser::OPEN_STATUS_LOCK])
            ->orderBy('id desc')
            ->all();
        $role_lsit = [];
        $role_lsit_temp = AdminUserRole::find()->select(['name','title'])->asArray()->all();
        foreach ($role_lsit_temp as $v) {
            $role_lsit[$v['name']] = $v['title'];
        }
        $user_collection_new =[];
        if($users)
        {
            foreach ($users as $key=>$val)
            {
                $arr = explode(',',$val['role']);
                if($role && !in_array($role,$arr))
                {
                    continue;
                }
                $string = '';
                foreach ($arr as $v)
                {
                    $string .= isset($role_lsit[$v])? $role_lsit[$v] .',':'';
                }
                $val['role'] = $string;
                $user_collection_new[$key] =$val;
            }
        }
        $pages = new Pagination(['totalCount' => count($user_collection_new)]);
        $pages->pageSize = $this->request->get('page_size',15);
        $page = $this->request->get('page');
        $page = !empty($page)?$page:1;
        $offset = ($page-1)*15;
        $users = array_slice($user_collection_new,$offset,15);
        return $this->render('lock-list', [
            'users' => $users,
            'role_lsit' => $role_lsit,
            'pages' => $pages,
        ]);
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
}
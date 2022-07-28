<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2016/10/4
 * Time: 10:53
 */
namespace callcenter\controllers;

use backend\models\AdminUserCaptcha;
use backend\models\Merchant;
use callcenter\models\AbsenceApply;
use callcenter\models\AdminLoginLog;
use callcenter\models\AdminManagerRelation;
use callcenter\models\AdminMessage;
use callcenter\models\AdminNxUser;
use callcenter\models\AdminUser;
use callcenter\models\AdminUserMasterSlaverRelation;
use callcenter\models\AdminUserRole;
use callcenter\models\CollectorClassSchedule;
use callcenter\models\CompanyTeam;
use callcenter\service\roles\RoleBigTeamService;
use callcenter\service\roles\RoleSmallTeamService;
use callcenter\service\roles\RoleSuperTeamService;
use common\helpers\CommonHelper;
use common\helpers\RedisQueue;
use common\models\GlobalSetting;
use common\services\message\WeWorkService;
use Yii;
use yii\base\Exception;
use yii\data\Pagination;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\Response;
use callcenter\models\loan_collection\LoanCollectionOrder;
use callcenter\models\loan_collection\UserCompany;
use yii\web\UploadedFile;


class UserCollectionController extends  BaseController{
    public $enableCsrfValidation = false;

    /**
     * @name 催收人员列表
     **/
    public function actionUserList()
    {
        $condition[] = 'and';
        if ($this->request->get('search_submit')) { // 过滤
            $search = $this->request->get();
            $search['username'] = str_replace("'","", $search['username']);
            $search['phone'] = str_replace("'","", $search['phone']);
            if (isset($search['phone']) && $search['phone'] != '') {
                $condition[] = ['like', 'A.phone', trim($search['phone'])];
            }
            if (isset($search['username']) && $search['username'] != '') {
                $condition[] = ['like', 'A.username', trim($search['username'])];
            }
            if (isset($search['real_name']) && $search['real_name'] != '' && GlobalSetting::checkCollectionUsernameCanSetRealName(Yii::$app->user->identity)) {
                $condition[] = ['like', 'A.real_name', trim($search['real_name'])];
            }
            if (isset($search['role']) && $search['role'] != '') {
                $condition[] = ['like', 'A.role', trim($search['role'])];
            }
            if (isset($search['outside']) && $search['outside'] != '') {
                $condition[] = ['A.outside' => $search['outside']];
            }
            if (isset($search['group']) && $search['group'] != '') {
                $condition[] = ['A.group' => intval($search['group'])];
            }
            if (isset($search['group_game']) && $search['group_game'] != '') {
                $condition[] = ['A.group_game' => intval($search['group_game'])];
            }
            if (isset($search['can_dispatch']) && $search['can_dispatch'] != '') {
                $condition[] = ['A.can_dispatch' => intval($search['can_dispatch'])];
            }
            if($this->strategyOperating){
                if (isset($search['open_search_label']) && $search['open_search_label'] != '') {
                    $condition[] = ['A.open_search_label' => intval($search['open_search_label'])];
                }
                if (isset($search['login_app']) && $search['login_app'] != '') {
                    $condition[] = ['A.login_app' => intval($search['login_app'])];
                }
                if (isset($search['nx_phone']) && $search['nx_phone'] != '') {
                    $condition[] = ['A.nx_phone' => intval($search['nx_phone'])];
                }
            }
            // 防止拼接参数来绕过判断
            if ($this->isNotMerchantAdmin) {
                if (isset($search['merchant_id']) && $search['merchant_id'] != '') {
                    $condition[] = ['A.merchant_id' => intval($search['merchant_id'])];
                }
            }
        }
        $is_self = true;
        if(Yii::$app->user->identity->outside > 0){
            $is_self = false;
        }
        $condition = array_merge($condition, AdminUserRole::getCondition(Yii::$app->user->identity,'A'));
        $collectorRoles = AdminUserRole::getRolesByGroup(AdminUserRole::TYPE_COLLECTION);
        if($collectorRoles){
            $query = AdminUser::find()
                ->select('A.*,B.real_title')
                ->from(AdminUser::tableName() .' A')
                ->leftJoin(UserCompany::tableName() . ' B', 'A.outside = B.id')
                ->where(['A.merchant_id' => $this->merchantIds])
                ->andWhere($condition)
                ->andWhere([
                    'A.open_status'=>AdminUser::$usable_status,
                    'A.role' => $collectorRoles
                ])
                ->andWhere(['>','A.outside',0])
                ->andWhere(['>','A.group',0])
                ->andWhere(['>','A.group_game',0])
                ->orderBy('A.id desc');
            if($is_self == false){
                $query->andWhere(['A.outside' => Yii::$app->user->identity->outside]);
            }
            $countQuery = clone $query;
            $pages = new Pagination(['totalCount' => $countQuery->count('A.id')]);
            $pages->pageSize = $this->request->get('page_size',15);
            if ($this->request->get('submitcsv') == 'export_direct') {
                $users = $query->asArray()->all();
            }else{
                $users = $query->offset($pages->offset)->limit($pages->limit)->asArray()->all();
            }
        }else{
            $pages = new Pagination(['totalCount' => 0]);
            $pages->pageSize = $this->request->get('page_size',15);
            $users = [];
        }

        $setRealNameCollectionAdmin = GlobalSetting::checkCollectionUsernameCanSetRealName(Yii::$app->user->identity);
        if($is_self){
            if ($this->request->get('submitcsv') == 'export_direct') {
                return $this->_exportCollectionPersonnel($this->merchantIds,$this->isHiddenPhone,$users,$setRealNameCollectionAdmin);
            }
            if ($this->request->get('exportcsv') == 'export_tmp') {
                return $this->_ExportTemplate($this->strategyOperating);
            }
        }

        $compamyList = UserCompany::outsideRealName($this->merchantIds);

        // 获取所有商户，只能是商户管理员的时候才在页面上显示
        if ($this->isNotMerchantAdmin) {
            $arrMerchant = Merchant::getMerchantId();
        } else {
            $arrMerchant = [];
        }

        return $this->render('user-collection-list', [
            'users' => $users,
            'pages' => $pages,
            'is_self' => $is_self,
            'compamyList' => $compamyList,
            'arrMerchant' => $arrMerchant,
            'isHiddenPhone' => $this->isHiddenPhone,
            'strategyOperating' => $this->strategyOperating,
            'setRealNameCollectionAdmin' => $setRealNameCollectionAdmin,
        ]);
    }


    /**
     * @name 添加催收人员
     * @return string
     */
    public function actionUserAdd(){
        $model = new AdminUser();
        $is_self = true;
        if(Yii::$app->user->identity->outside > 0){ //Monitor
            $is_self = false;
        }
        $companys = [];
        $outsideRealName = [];
        $userCompany = UserCompany::find()
            ->select(['id', 'real_title', 'merchant_id'])
            ->where(['merchant_id' => $this->merchantIds])->asArray()->all();
        foreach ($userCompany as $item) {
            $outsideRealName[$item['id']] = $item['real_title'];
            $companys[$item['merchant_id']][$item['id']] = $item['real_title'];
        }
        if($this->request->isPost)
        {
            $model->load($this->request->post(),null,$this->strategyOperating);
            $model->role = AdminUserRole::$groups_default_role_map[AdminUserRole::TYPE_COLLECTION];
            $model->created_user = Yii::$app->user->identity->username;
            if(Yii::$app->user->identity->outside > 0){  //Monitor
                $model->outside = Yii::$app->user->identity->outside;
            }else{  //admin manager
                //检查机构
                if(!isset($outsideRealName[$model->outside])){
                    return $this->redirectMessage('outside error', self::MSG_ERROR);
                }
            }

            if ($this->isNotMerchantAdmin) {
                $model->to_view_merchant_id = implode(',', $this->request->post('to_view_merchant_id'));
            } else {
                $model->merchant_id = Yii::$app->user->identity->merchant_id;
                $model->to_view_merchant_id = Yii::$app->user->identity->to_view_merchant_id;
            }

            $model->can_dispatch = AdminUser::CAN_DISPATCH;
            if ($model->validate()) {
                $model->password = Yii::$app->security->generatePasswordHash($model->password);
                if ($model->save(false)) {
                    return $this->redirectMessage('add success', self::MSG_SUCCESS, '',-2);
                } else {
                    return $this->redirectMessage('add fail', self::MSG_ERROR);
                }
            }

        }

        //生成随机密码
        $password = CommonHelper::make_password(12);
        $defaultCompanys = $companys[Yii::$app->user->identity->merchant_id] ?? [];
        return $this->render('user-add', array(
            'model' => $model,
            'defaultCompanys' => $defaultCompanys,
            'companys' => $companys,
            'is_self' => $is_self,
            'isNotMerchantAdmin' => $this->isNotMerchantAdmin,
            'arrMerchantIds' => Merchant::getMerchantByIds($this->merchantIds,$this->isNotMerchantAdmin),
            'password' => $password,
            'strategyOperating' => $this->strategyOperating,
        ));
    }


    /**
     * @name 编辑催收人员
     * @return string
     */
    public function actionEdit($id){
        $merchantId = $this->merchantIds;
        $companys = [];
        $outsideRealName = [];
        $userCompany = UserCompany::find()
            ->select(['id', 'real_title', 'merchant_id'])
            ->where(['merchant_id' => $this->merchantIds])->asArray()->all();
        foreach ($userCompany as $item) {
            $outsideRealName[$item['id']] = $item['real_title'];
            $companys[$item['merchant_id']][$item['id']] = $item['real_title'];
        }

        $collectorRoles = AdminUserRole::getRolesByGroup(AdminUserRole::TYPE_COLLECTION);
        if(empty($collectorRoles)){
            return $this->redirectMessage('not role', self::MSG_ERROR);
        }
        $query = AdminUser::find()->where(['id' => $id,'merchant_id' => $merchantId,'role' => $collectorRoles]);
        $is_self = true;
        if(Yii::$app->user->identity->outside > 0){
            $is_self = false;
            $query->andWhere(['outside' => Yii::$app->user->identity->outside]);
        }
        /** @var AdminUser $model */
        $model = $query->one();
        if(!$model){
            return $this->redirectMessage('can\'t edit', self::MSG_ERROR);
        }
        $oldPhone = $model->phone;
        $starPhone = substr_replace($oldPhone,'*****',0,5);
        if ($model->load($this->request->post(),null,$this->strategyOperating)) {
            if($this->isHiddenPhone && $starPhone == $model->phone){
                $model->phone = $oldPhone;
            }
            if ($this->isNotMerchantAdmin) {
                $model->to_view_merchant_id = implode(',', $this->request->post('to_view_merchant_id'));
            }
            //检查机构
            if(!isset($outsideRealName[$model->outside])){
                return $this->redirectMessage('outside error', self::MSG_ERROR);
            }
            if ($model->validate(['phone','username','outside'])) {
                if ($model->save(false)) {
                    return $this->redirectMessage('edit success', self::MSG_SUCCESS,'',-2);
                } else {
                    return $this->redirectMessage('edit fail', self::MSG_ERROR);
                }
            }
        }
        if($this->isHiddenPhone){
            $model->phone = $starPhone;
        }
        $defaultCompanys = $companys[$model->merchant_id] ?? [];
        return $this->render('edit', array(
            'model' => $model,
            'defaultCompanys' => $defaultCompanys,
            'companys' => $companys,
            'is_self' => $is_self,
            'isNotMerchantAdmin' => $this->isNotMerchantAdmin,
            'arrMerchantIds' => Merchant::getMerchantByIds($this->merchantIds,$this->isNotMerchantAdmin),
            'strategyOperating' => $this->strategyOperating,
            'setRealNameCollectionAdmin' => GlobalSetting::checkCollectionUsernameCanSetRealName(Yii::$app->user->identity),
        ));
    }

    /**
     * @name 催收人员是否可分派
     */
    public function actionUpdate($id,$status){
        $this->response->format = Response::FORMAT_JSON;
        $merchantId = Yii::$app->user->identity->merchant_id;
        $query = AdminUser::find()->where(['id' => $id,'merchant_id' => $merchantId]);
        if(Yii::$app->user->identity->outside > 0){
            $query->andWhere(['outside' => Yii::$app->user->identity->outside]);
        }
        /** @var AdminUser $model */
        $model = $query->one();
        if(!$model){
            return  [
                'code'=>-1,
                'message'=>'can\'t update',
            ];
        }
        if($status){
            $model->can_dispatch = AdminUser::CAN_DISPATCH;
        }else{
            $model->can_dispatch = AdminUser::CAN_ONT_DISPATCH;
        }
        if(!$model->save(false)){
            return  [
                'code'=>-1,
                'message'=>'save fail',
            ];
        }
        return  [
            'code'=>0,
            'message'=>'success',
        ];
    }


    /**
     * @name 小组组长列表
     **/
    public function actionTeamLeaderList()
    {
        $condition[] = 'and';
        if ($this->request->get('search_submit')) { // 过滤
            $search = $this->request->get();
            $search['username'] = str_replace("'","", $search['username']);
            $search['phone'] = str_replace("'","", $search['phone']);
            if (isset($search['phone']) && $search['phone'] != '') {
                $condition[] = ['like', 'A.phone', trim($search['phone'])];
            }
            if (isset($search['username']) && $search['username'] != '') {
                $condition[] = ['like', 'A.username', trim($search['username'])];
            }
            if (isset($search['real_name']) && $search['real_name'] != '' && GlobalSetting::checkCollectionUsernameCanSetRealName(Yii::$app->user->identity)) {
                $condition[] = ['like', 'A.real_name', trim($search['real_name'])];
            }
            if (isset($search['role']) && $search['role'] != '') {
                $condition[] = ['like', 'A.role', trim($search['role'])];
            }
            if (isset($search['outside']) && $search['outside'] != '') {
                $condition[] = ['A.outside' => $search['outside']];
            }
            if (isset($search['group']) && $search['group'] != '') {
                $condition[] = ['A.group' => intval($search['group'])];
            }
            if (isset($search['group_game']) && $search['group_game'] != '') {
                $condition[] = ['A.group_game' => intval($search['group_game'])];
            }
            if (isset($search['can_dispatch']) && $search['can_dispatch'] != '') {
                $condition[] = ['A.can_dispatch' => intval($search['can_dispatch'])];
            }
            if($this->strategyOperating){
                if (isset($search['open_search_label']) && $search['open_search_label'] != '') {
                    $condition[] = ['A.open_search_label' => intval($search['open_search_label'])];
                }
                if (isset($search['login_app']) && $search['login_app'] != '') {
                    $condition[] = ['A.login_app' => intval($search['login_app'])];
                }
                if (isset($search['nx_phone']) && $search['nx_phone'] != '') {
                    $condition[] = ['A.nx_phone' => intval($search['nx_phone'])];
                }
            }
            // 防止拼接参数来绕过判断
            if ($this->isNotMerchantAdmin) {
                if (isset($search['merchant_id']) && $search['merchant_id'] != '') {
                    $condition[] = ['A.merchant_id' => intval($search['merchant_id'])];
                }
            }
        }
        $is_self = true;
        if(Yii::$app->user->identity->outside > 0){
            $is_self = false;
        }
        $condition = array_merge($condition, AdminUserRole::getCondition(Yii::$app->user->identity,'A'));
        $teamRoles = AdminUserRole::getRolesByGroup(AdminUserRole::TYPE_SMALL_TEAM_MANAGER);
        if($teamRoles){
            $query = AdminUser::find()
                ->select('A.*,B.real_title,C.alias')
                ->from(AdminUser::tableName() .' A')
                ->leftJoin(UserCompany::tableName() . ' B', 'A.outside = B.id')
                ->leftJoin(CompanyTeam::tableName(). ' C','A.outside = C.outside AND A.group_game = C.team')
                ->where(['A.merchant_id' => $this->merchantIds])
                ->andWhere($condition)
                ->andWhere([
                    'A.open_status'=>AdminUser::$usable_status,
                    'A.role' => $teamRoles
                ])
                ->andWhere(['>','A.outside',0])
                ->andWhere(['>','A.group',0])
                ->andWhere(['>','A.group_game',0])
                ->orderBy('A.id desc');
            if($is_self == false){
                $query->andWhere(['A.outside' => Yii::$app->user->identity->outside]);
            }
            $countQuery = clone $query;
            $pages = new Pagination(['totalCount' => $countQuery->count('A.id')]);
            $pages->pageSize = $this->request->get('page_size',15);
            $users = $query->offset($pages->offset)->limit($pages->limit)->asArray()->all();
        }else{
            $pages = new Pagination(['totalCount' => 0]);
            $pages->pageSize = $this->request->get('page_size',15);
            $users = [];
        }

        $setRealNameCollectionAdmin = GlobalSetting::checkCollectionUsernameCanSetRealName(Yii::$app->user->identity);
        $compamyList = UserCompany::outsideRealName($this->merchantIds);

        // 获取所有商户，只能是商户管理员的时候才在页面上显示
        if ($this->isNotMerchantAdmin) {
            $arrMerchant = Merchant::getMerchantId();
        } else {
            $arrMerchant = [];
        }
        return $this->render('team-leader-list', [
            'users' => $users,
            'pages' => $pages,
            'is_self' => $is_self,
            'compamyList' => $compamyList,
            'arrMerchant' => $arrMerchant,
            'isHiddenPhone' => $this->isHiddenPhone,
            'strategyOperating' => $this->strategyOperating,
            'setRealNameCollectionAdmin' => $setRealNameCollectionAdmin,
        ]);
    }

    /**
     * @name 添加小组组长
     * @return string
     */
    public function actionTeamLeaderAdd(){
        $model = new AdminUser();
        $is_self = true;
        if(Yii::$app->user->identity->outside > 0){ //Monitor
            $is_self = false;
        }
        $companyList = [];
        $userCompany = UserCompany::lists($this->merchantIds);
        foreach ($userCompany as $item) {
            $companyList[$item['merchant_id']][$item['id']] = $item['real_title'];
        }
        $defaultCompanyList = $companyList[Yii::$app->user->identity->merchant_id] ?? [];
        if($this->request->isPost)
        {
            $model->load($this->request->post(),null,$this->strategyOperating);
            $model->role = AdminUserRole::$groups_default_role_map[AdminUserRole::TYPE_SMALL_TEAM_MANAGER];
            $model->created_user = Yii::$app->user->identity->username;

            $merchantId = $this->isNotMerchantAdmin ? $model->merchant_id : Yii::$app->user->identity->merchant_id;
            //检查商户机构
            if(!isset($companyList[$merchantId][$model->outside])){
                return $this->redirectMessage('outside error', self::MSG_ERROR);
            }

            if ($this->isNotMerchantAdmin) {
                $model->to_view_merchant_id = implode(',', $this->request->post('to_view_merchant_id'));
            } else {
                $model->to_view_merchant_id = Yii::$app->user->identity->to_view_merchant_id;
                $model->merchant_id = Yii::$app->user->identity->merchant_id;
            }
            if ($model->validate()) {
                $model->password = Yii::$app->security->generatePasswordHash($model->password);
                if ($model->save(false)) {
                    return $this->redirectMessage('add success', self::MSG_SUCCESS, '',-2);
                } else {
                    return $this->redirectMessage('add fail', self::MSG_ERROR);
                }
            }

        }
        //生成随机密码
        $password = CommonHelper::make_password(12);
        return $this->render('team-leader-add', array(
            'model' => $model,
            'companyList' => $companyList,
            'defaultCompanyList' => $defaultCompanyList,
            'is_self' => $is_self,
            'isNotMerchantAdmin' => $this->isNotMerchantAdmin,
            'arrMerchantIds' => Merchant::getMerchantByIds($this->merchantIds,$this->isNotMerchantAdmin),
            'password' => $password,
            'strategyOperating' => $this->strategyOperating,
        ));
    }

    /**
     * @name 编辑小组组长
     * @return string
     */
    public function actionTeamLeaderEdit($id){
        $collectorRoles = AdminUserRole::getRolesByGroup(AdminUserRole::TYPE_SMALL_TEAM_MANAGER);
        if(empty($collectorRoles)){
            return $this->redirectMessage('not role', self::MSG_ERROR);
        }
        $query = AdminUser::find()->where(['id' => $id,'merchant_id' => $this->merchantIds,'role' => $collectorRoles]);
        $is_self = true;
        if(Yii::$app->user->identity->outside > 0){
            $is_self = false;
            $query->andWhere(['outside' => Yii::$app->user->identity->outside]);
        }
        /** @var AdminUser $model */
        $model = $query->one();
        if(!$model){
            return $this->redirectMessage('can\'t edit', self::MSG_ERROR);
        }
        $oldPhone = $model->phone;
        $starPhone = substr_replace($oldPhone,'*****',0,5);
        $companyList = [];
        $userCompany = UserCompany::lists($this->merchantIds);
        foreach ($userCompany as $item) {
            $companyList[$item['merchant_id']][$item['id']] = $item['real_title'];
        }
        $defaultCompanyList = $companyList[$model->merchant_id] ?? [];
        if ($model->load($this->request->post(),null,$this->strategyOperating)) {
            if($this->isHiddenPhone && $starPhone == $model->phone){
                $model->phone = $oldPhone;
            }
            $model->created_user = Yii::$app->user->identity->username;
            if ($this->isNotMerchantAdmin) {
                $model->to_view_merchant_id = implode(',', $this->request->post('to_view_merchant_id'));
            }
            $merchantId = $this->isNotMerchantAdmin ? $model->merchant_id : Yii::$app->user->identity->merchant_id;
            //检查商户机构
            if(!isset($companyList[$merchantId][$model->outside])){
                return $this->redirectMessage('outside error', self::MSG_ERROR);
            }

            if ($model->validate(['phone','username','outside'])) {
                if ($model->save(false)) {
                    return $this->redirectMessage('edit success', self::MSG_SUCCESS,'',-2);
                } else {
                    return $this->redirectMessage('edit fail', self::MSG_ERROR);
                }
            }
        }
        if($this->isHiddenPhone){
            $model->phone = $starPhone;
        }
        return $this->render('team-leader-edit', array(
            'model' => $model,
            'is_self' => $is_self,
            'companyList' => $companyList,
            'defaultCompanyList' => $defaultCompanyList,
            'isNotMerchantAdmin' => $this->isNotMerchantAdmin,
            'arrMerchantIds' => Merchant::getMerchantByIds($this->merchantIds,$this->isNotMerchantAdmin),
            'strategyOperating' => $this->strategyOperating,
            'setRealNameCollectionAdmin' => GlobalSetting::checkCollectionUsernameCanSetRealName(Yii::$app->user->identity),
        ));
    }

    /**
     * @name 设置小组长副手
     * @return string
     */
    public function actionSetSmallTeamLeaderDeputy($id){
        $stRoles = AdminUserRole::getRolesByGroup(AdminUserRole::TYPE_SMALL_TEAM_MANAGER);
        if(empty($stRoles)){
            return $this->redirectMessage('not role', self::MSG_ERROR);
        }
        /** @var AdminUser $model */
        $model = AdminUser::find()->where(['id' => $id, 'merchant_id' => $this->merchantIds, 'role' => $stRoles])->one();
        $deputyModel = AdminUserMasterSlaverRelation::find()->where(['admin_id' => $model->id])->one();

        if(is_null($deputyModel)){
            $deputyModel = new AdminUserMasterSlaverRelation();
        }
        $deputyModel->admin_id = $model->id;
        $service = new RoleSmallTeamService($model);
        if($this->request->isPost)
        {
            $adminUserMasterSlaverRelation = $this->request->post('AdminUserMasterSlaverRelation');
            $deputyModel->slave_admin_id = $adminUserMasterSlaverRelation['slave_admin_id'];
            if($deputyModel->validate()){
                if($deputyModel->slave_admin_id > 0){
                    if($service->checkCollectorOnTeam($deputyModel->slave_admin_id)){
                        $deputyModel->save();
                    }
                } else{
                    if(!$deputyModel->isNewRecord){
                        $deputyModel->save();
                    }
                }
                return $this->redirectMessage('edit success', self::MSG_SUCCESS,'', -2);
            }
            return $this->redirectMessage('edit fail：'.json_encode($deputyModel->getErrors()), self::MSG_ERROR, '', -1);
        }

        $teamCollectorList = $service->getTeamCollectorList(); //小组长他的team list
        return $this->render('set-small-team-leader-deputy', array(
            'model' => $model,
            'deputyModel' => $deputyModel,
            'teamCollectorList' => $teamCollectorList
        ));
    }

    /**
     * @name 删除小组组长
     * @return string
     */
    public function actionTeamLeaderDel(){
        if ( !empty($uid = $this->request->get('id'))) {
            $res = $this->delUser($uid,AdminUserRole::TYPE_SMALL_TEAM_MANAGER);
            if($res['code'] == 0){
                return $this->redirectMessage('delete success', self::MSG_SUCCESS);
            }else{
                return $this->redirectMessage('delete fail：,'.$res['message'], self::MSG_ERROR);
            }
        }
        echo 'oops';
    }

    /**
     * @name 大组组长(经理)列表
     **/
    public function actionBigTeamLeaderList()
    {
        $condition[] = 'and';
        if ($this->request->get('search_submit')) { // 过滤
            $search = $this->request->get();
            $search['username'] = str_replace("'","", $search['username']);
            $search['phone'] = str_replace("'","", $search['phone']);
            if (isset($search['phone']) && $search['phone'] != '') {
                $condition[] = ['like', 'A.phone', trim($search['phone'])];
            }
            if (isset($search['username']) && $search['username'] != '') {
                $condition[] = ['like', 'A.username', trim($search['username'])];
            }
            if (isset($search['real_name']) && $search['real_name'] != '' && GlobalSetting::checkCollectionUsernameCanSetRealName(Yii::$app->user->identity)) {
                $condition[] = ['like', 'A.real_name', trim($search['real_name'])];
            }
            if (isset($search['role']) && $search['role'] != '') {
                $condition[] = ['like', 'A.role', trim($search['role'])];
            }
            if (isset($search['outside']) && $search['outside'] != '') {
                $condition[] = ['like', 'A.outside', $search['outside']];
            }
            if (isset($search['group']) && $search['group'] != '') {
                $condition[] = ['A.group' => intval($search['group'])];
            }
            if (isset($search['group_game']) && $search['group_game'] != '') {
                $condition[] = ['A.group_game' => intval($search['group_game'])];
            }
            if (isset($search['can_dispatch']) && $search['can_dispatch'] != '') {
                $condition[] = ['A.can_dispatch' => intval($search['can_dispatch'])];
            }
            if($this->strategyOperating){
                if (isset($search['open_search_label']) && $search['open_search_label'] != '') {
                    $condition[] = ['A.open_search_label' => intval($search['open_search_label'])];
                }
                if (isset($search['login_app']) && $search['login_app'] != '') {
                    $condition[] = ['A.login_app' => intval($search['login_app'])];
                }
                if (isset($search['nx_phone']) && $search['nx_phone'] != '') {
                    $condition[] = ['A.nx_phone' => intval($search['nx_phone'])];
                }
            }
            // 防止拼接参数来绕过判断
            if ($this->isNotMerchantAdmin) {
                if (isset($search['merchant_id']) && $search['merchant_id'] != '') {
                    $condition[] = ['A.merchant_id' => intval($search['merchant_id'])];
                }
            }
        }
        $is_self = true;
        if(Yii::$app->user->identity->outside > 0){
            $is_self = false;
        }
        $teamRoles = AdminUserRole::getRolesByGroup(AdminUserRole::TYPE_BIG_TEAM_MANAGER);
        if($teamRoles){
            $query = AdminUser::find()
                ->select('A.*,B.real_title')
                ->from(AdminUser::tableName() .' A')
                ->leftJoin(UserCompany::tableName() . ' B', 'A.outside = B.id')
                ->where(['A.merchant_id' => $this->merchantIds])
                ->andWhere($condition)
                ->andWhere([
                    'A.open_status'=>AdminUser::$usable_status,
                    'A.role' => $teamRoles
                ])
                ->andWhere(['>','A.outside',0])
                ->orderBy('A.id desc');
            if($is_self == false){
                $query->andWhere(['A.outside' => Yii::$app->user->identity->outside]);
            }
//            echo $query->createCommand()->getRawSql();exit;
            $countQuery = clone $query;
            $pages = new Pagination(['totalCount' => $countQuery->count('A.id')]);
            $pages->pageSize = $this->request->get('page_size',15);
            $users = $query->offset($pages->offset)->limit($pages->limit)->asArray()->all();
        }else{
            $pages = new Pagination(['totalCount' => 0]);
            $pages->pageSize = $this->request->get('page_size',15);
            $users = [];
        }

        $setRealNameCollectionAdmin = GlobalSetting::checkCollectionUsernameCanSetRealName(Yii::$app->user->identity);
        $compamyList = UserCompany::outsideRealName($this->merchantIds);

        // 获取所有商户，只能是商户管理员的时候才在页面上显示
        if ($this->isNotMerchantAdmin) {
            $arrMerchant = Merchant::getMerchantId();
        } else {
            $arrMerchant = [];
        }
        return $this->render('big-team-leader-list', [
            'users' => $users,
            'pages' => $pages,
            'is_self' => $is_self,
            'compamyList' => $compamyList,
            'arrMerchant' => $arrMerchant,
            'isHiddenPhone' => $this->isHiddenPhone,
            'strategyOperating' => $this->strategyOperating,
            'setRealNameCollectionAdmin' => $setRealNameCollectionAdmin,
        ]);
    }

    /**
     * @name 添加大组组长(经理)
     * @return string
     */
    public function actionBigTeamLeaderAdd(){
        $model = new AdminUser();
        $managerRelationModel = new AdminManagerRelation();
        $is_self = true;
        if(Yii::$app->user->identity->outside > 0){ //Monitor
            $is_self = false;
        }
        $labelArr = [];
        $companyList = [];
        $userCompany = UserCompany::lists($this->merchantIds);
        foreach ($userCompany as $item) {
            $companyList[$item['merchant_id']][$item['id']] = $item['real_title'];
        }
        $defaultCompanyList = $companyList[Yii::$app->user->identity->merchant_id] ?? [];
        if($this->request->isPost)
        {
            $model->load($this->request->post(),null,$this->strategyOperating);
            $managerRelationModel->load($this->request->post());

            $model->role = AdminUserRole::$groups_default_role_map[AdminUserRole::TYPE_BIG_TEAM_MANAGER];
            $model->created_user = Yii::$app->user->identity->username;
            if(Yii::$app->user->identity->outside > 0){  //Monitor
                $model->outside = Yii::$app->user->identity->outside;
            }else{  //admin manager
                $merchantId = $this->isNotMerchantAdmin ? $model->merchant_id : Yii::$app->user->identity->merchant_id;
                //检查商户机构
                if(!isset($companyList[$merchantId][$model->outside])){
                    return $this->redirectMessage('outside error', self::MSG_ERROR);
                }
            }

            if ($this->isNotMerchantAdmin) {
                $model->to_view_merchant_id = implode(',', $this->request->post('to_view_merchant_id'));
            } else {
                $model->to_view_merchant_id = Yii::$app->user->identity->to_view_merchant_id;
                $model->merchant_id = Yii::$app->user->identity->merchant_id;
            }
            $relationArr = [];
            if ($model->validate()) {
                $model->password = Yii::$app->security->generatePasswordHash($model->password);

                $managerRelationValidate = true;
                foreach ($managerRelationModel->group as $key => $group){
                    if(!isset(LoanCollectionOrder::$level[$group])){
                        $managerRelationModel->addError('group'.$key,'group error');
                        $managerRelationValidate = false;
                        break;
                    }
                    if(!isset($managerRelationModel->group_game[$key]) || !isset(AdminUser::$group_games[$managerRelationModel->group_game[$key]])){
                        $managerRelationModel->addError('group_game'.$key,'group_game error');
                        $managerRelationValidate = false;
                        break;
                    }
                    if(isset($relationArr[$group][$managerRelationModel->group_game[$key]])){
                        $managerRelationModel->addError('group_game'.$key,'group and team need unique');
                        $managerRelationValidate = false;
                        break;
                    }else{
                        $relationArr[$group][$managerRelationModel->group_game[$key]] = 1;
                    }
                }

                if($managerRelationValidate){
                    if ($model->save(false)) {
                        foreach ($relationArr as $group => $groupGameData){
                            foreach ($groupGameData as $groupGame => $v){
                                $managerRelationModelNew = new AdminManagerRelation();
                                $managerRelationModelNew->admin_id = $model->id;
                                $managerRelationModelNew->group = $group;
                                $managerRelationModelNew->group_game = $groupGame;
                                $managerRelationModelNew->save(false);
                            }
                        }
                        return $this->redirectMessage('add success', self::MSG_SUCCESS, '',-2);
                    } else {
                        return $this->redirectMessage('add fail', self::MSG_ERROR);
                    }
                }
            }
            foreach ($managerRelationModel->group as $key => $group){
                if(!isset(LoanCollectionOrder::$level[$group])){
                    break;
                }
                if(!isset($managerRelationModel->group_game[$key]) || !isset(AdminUser::$group_games[$managerRelationModel->group_game[$key]])){
                    break;
                }
                $labelArr[$key] = ['group' => $group, 'group_game' => $managerRelationModel->group_game[$key]];
            };
        }
        //生成随机密码
        $password = CommonHelper::make_password(12);
        return $this->render('big-team-leader-add', array(
            'model' => $model,
            'managerRelationModel' => $managerRelationModel,
            'companyList' => $companyList,
            'defaultCompanyList' => $defaultCompanyList,
            'is_self' => $is_self,
            'isNotMerchantAdmin' => $this->isNotMerchantAdmin,
            'arrMerchantIds' => Merchant::getMerchantByIds($this->merchantIds,$this->isNotMerchantAdmin),
            'password' => $password,
            'strategyOperating' => $this->strategyOperating,
            'labelArr' => $labelArr
        ));
    }

    /**
     * @name 编辑大组组长
     * @return string
     */
    public function actionBigTeamLeaderEdit($id){
        $collectorRoles = AdminUserRole::getRolesByGroup(AdminUserRole::TYPE_BIG_TEAM_MANAGER);
        if(empty($collectorRoles)){
            return $this->redirectMessage('not role', self::MSG_ERROR);
        }
        $query = AdminUser::find()->where(['id' => $id,'merchant_id' => $this->merchantIds,'role' => $collectorRoles]);
        $is_self = true;
        if(Yii::$app->user->identity->outside > 0){
            $is_self = false;
            $query->andWhere(['outside' => Yii::$app->user->identity->outside]);
        }
        /** @var AdminUser $model */
        $model = $query->one();
        if(!$model){
            return $this->redirectMessage('can\'t edit', self::MSG_ERROR);
        }
        $companyList = [];
        $userCompany = UserCompany::lists($this->merchantIds);
        foreach ($userCompany as $item) {
            $companyList[$item['merchant_id']][$item['id']] = $item['real_title'];
        }
        $merchantId = $this->isNotMerchantAdmin ? $model->merchant_id : Yii::$app->user->identity->merchant_id;
        $defaultCompanyList = $companyList[$merchantId] ?? [];
        $oldPhone = $model->phone;
        $starPhone = substr_replace($oldPhone,'*****',0,5);

        $labelArr = [];
        $existRelationArr = [];
        $managerRelationModel = new AdminManagerRelation();
        $managerRelationModels = AdminManagerRelation::find()->where(['admin_id' => $id])->all();
        foreach ($managerRelationModels as $key => $item){
            /** @var AdminManagerRelation $item */
            $labelArr[$key] = ['group' => $item->group, 'group_game' => $item->group_game];
            $existRelationArr[$item->group][] = $item->group_game;
        }
        if ($model->load($this->request->post(),null,$this->strategyOperating)) {

            $managerRelationModel->load($this->request->post());

            if($this->isHiddenPhone && $starPhone == $model->phone){
                $model->phone = $oldPhone;
            }
            $model->created_user = Yii::$app->user->identity->username;
            if ($this->isNotMerchantAdmin) {
                $model->to_view_merchant_id = implode(',', $this->request->post('to_view_merchant_id'));
            }
            $merchantId = $this->isNotMerchantAdmin ? $model->merchant_id : Yii::$app->user->identity->merchant_id;
            //检查商户机构
            if(!isset($companyList[$merchantId][$model->outside])){
                return $this->redirectMessage('outside error', self::MSG_ERROR);
            }
            $relationAddArr = [];
            $relationDelArr = [];
            if ($model->validate(['phone','username','outside'])) {
                $managerRelationValidate = true;
                foreach ($managerRelationModel->group as $key => $group){
                    if(!isset(LoanCollectionOrder::$level[$group])){
                        $managerRelationModel->addError('group'.$key,'group error');
                        $managerRelationValidate = false;
                        break;
                    }
                    if(!isset($managerRelationModel->group_game[$key]) || !isset(AdminUser::$group_games[$managerRelationModel->group_game[$key]])){
                        $managerRelationModel->addError('group_game'.$key,'group_game error');
                        $managerRelationValidate = false;
                        break;
                    }
                    if(isset($relationArr[$group][$managerRelationModel->group_game[$key]])){
                        $managerRelationModel->addError('group_game'.$key,'group and team need unique');
                        $managerRelationValidate = false;
                        break;
                    }else{
                        if (!isset($existRelationArr[$group]) || !in_array($managerRelationModel->group_game[$key], $existRelationArr[$group])) {
                            $relationAddArr[$group][$managerRelationModel->group_game[$key]] = 1;
                        }
                    }
                }

                foreach ($existRelationArr as $group => $groupGames) {
                    foreach ($groupGames as $groupGame) {
                        if (!in_array($group, $managerRelationModel->group) || !in_array($groupGame, $managerRelationModel->group_game)) {
                            $relationDelArr[$group][$groupGame] = 1;
                        }
                    }
                }

                if($managerRelationValidate){
                    if ($model->save(false)) {
                        foreach ($relationAddArr as $group => $groupGameData){
                            foreach ($groupGameData as $groupGame => $v){
                                $managerRelationModelNew = new AdminManagerRelation();
                                $managerRelationModelNew->admin_id = $model->id;
                                $managerRelationModelNew->group = $group;
                                $managerRelationModelNew->group_game = $groupGame;
                                $managerRelationModelNew->save(false);
                            }
                        }
                        foreach ($relationDelArr as $group => $groupGameData){
                            foreach ($groupGameData as $groupGame => $v){
                                AdminManagerRelation::deleteAll(['admin_id' => $model->id,'group' => $group,'group_game' => $groupGame]);
                            }
                        }
                        return $this->redirectMessage('edit success', self::MSG_SUCCESS,'',-2);
                    } else {
                        return $this->redirectMessage('edit fail', self::MSG_ERROR);
                    }
                }
            }
        }

        if($this->isHiddenPhone){
            $model->phone = $starPhone;
        }
        return $this->render('big-team-leader-edit', array(
            'model' => $model,
            'managerRelationModel' => $managerRelationModel,
            'is_self' => $is_self,
            'companyList' => $companyList,
            'defaultCompanyList' => $defaultCompanyList,
            'isNotMerchantAdmin' => $this->isNotMerchantAdmin,
            'arrMerchantIds' => Merchant::getMerchantByIds($this->merchantIds,$this->isNotMerchantAdmin),
            'strategyOperating' => $this->strategyOperating,
            'setRealNameCollectionAdmin' => GlobalSetting::checkCollectionUsernameCanSetRealName(Yii::$app->user->identity),
            'labelArr' => $labelArr
        ));
    }

    /**
     * @name 设置大组组长副手
     * @return string
     */
    public function actionSetBigTeamLeaderDeputy($id){
        $btRoles = AdminUserRole::getRolesByGroup(AdminUserRole::TYPE_BIG_TEAM_MANAGER);
        if(empty($btRoles)){
            return $this->redirectMessage('not role', self::MSG_ERROR);
        }
        /** @var AdminUser $model */
        $model = AdminUser::find()->where(['id' => $id, 'merchant_id' => $this->merchantIds,'role' => $btRoles])->one();
        $deputyModel = AdminUserMasterSlaverRelation::find()->where(['admin_id' => $model->id])->one();

        if(is_null($deputyModel)){
            $deputyModel = new AdminUserMasterSlaverRelation();
        }
        $deputyModel->admin_id = $model->id;
        $service = new RoleBigTeamService($model);
        if($this->request->isPost)
        {
            $adminUserMasterSlaverRelation = $this->request->post('AdminUserMasterSlaverRelation');
            $deputyModel->slave_admin_id = $adminUserMasterSlaverRelation['slave_admin_id'];
            if($deputyModel->validate()){
                if($deputyModel->slave_admin_id > 0){
                    if($service->checkCollectorOnTeam($deputyModel->slave_admin_id)){
                        $deputyModel->save();
                    }
                } else{
                    if(!$deputyModel->isNewRecord){
                        $deputyModel->save();
                    }
                }
                return $this->redirectMessage('edit success', self::MSG_SUCCESS, '', -2);
            }
            return $this->redirectMessage('edit fail', self::MSG_ERROR, '', -1);
        }

        $teamCollectorList = $service->getTeamCollectorList(); //大组长他的team list
        return $this->render('set-big-team-leader-deputy', array(
            'model' => $model,
            'deputyModel' => $deputyModel,
            'teamCollectorList' => $teamCollectorList
        ));
    }

    /**
     * @name 删除大组组长
     * @return string
     */
    public function actionBigTeamLeaderDel(){
        if ( !empty($uid = $this->request->get('id'))) {
            $res = $this->delUser($uid,AdminUserRole::TYPE_BIG_TEAM_MANAGER);
            if($res['code'] == 0){
                return $this->redirectMessage('delete success', self::MSG_SUCCESS);
            }else{
                return $this->redirectMessage('delete fail：,'.$res['message'], self::MSG_ERROR);
            }
        }
        echo 'oops';
    }

    /**
     * @name 催收专员列表
     **/
    public function actionMonitorList()
    {
        $condition[] = 'and';
        if ($this->request->get('search_submit')) { // 过滤
            $search = $this->request->get();
            $search['username'] = str_replace("'","", $search['username']);
            $search['phone'] = str_replace("'","", $search['phone']);
            if (isset($search['phone']) && $search['phone'] != '') {
                $condition[] = ['like', 'A.phone', trim($search['phone'])];
            }
            if (isset($search['username']) && $search['username'] != '') {
                $condition[] = ['like', 'A.username', trim($search['username'])];
            }
            // 防止拼接参数来绕过判断
            if ($this->isNotMerchantAdmin) {
                if (isset($search['merchant_id']) && $search['merchant_id'] != '') {
                    $condition[] = ['A.merchant_id' => intval($search['merchant_id'])];
                }
            }
        }
        $monitorRoles = AdminUserRole::getRolesByGroup(AdminUserRole::TYPE_COMPANY_MANAGER);
        $is_self = true;
        if(Yii::$app->user->identity->outside > 0){
            $is_self = false;
        }
        if($monitorRoles){
            $query = AdminUser::find()
                ->select('A.*,B.real_title')
                ->from(AdminUser::tableName() .' A')
                ->leftJoin(UserCompany::tableName() . ' B', 'A.outside = B.id')
                ->where(['A.merchant_id' => $this->merchantIds])
                ->andWhere($condition)
                ->andWhere([
                    'A.open_status'=>AdminUser::$usable_status,
                    'A.role' => $monitorRoles
                ])
                ->andWhere(['>','A.outside',0])
                ->orderBy('A.id desc');
            if($is_self == false){
                $query->andWhere(['A.outside' => Yii::$app->user->identity->outside]);
            }
            $countQuery = clone $query;
            $pages = new Pagination(['totalCount' => $countQuery->count('A.id')]);
            $pages->pageSize = 15;
            $users = $query->offset($pages->offset)->limit($pages->limit)->asArray()->all();
        }else{
            $pages = new Pagination(['totalCount' => 0]);
            $pages->pageSize = 15;
            $users = [];
        }

        // 获取所有商户，只能是商户管理员的时候才在页面上显示
        if ($this->isNotMerchantAdmin) {
            $arrMerchant = Merchant::getMerchantId();
        } else {
            $arrMerchant = [];
        }

        return $this->render('monitor-list', [
            'users' => $users,
            'pages' => $pages,
            'is_self' => $is_self,
            'merchant_id' => $this->merchantIds,
            'arrMerchant' => $arrMerchant,
            'isHiddenPhone' => $this->isHiddenPhone,
            'strategyOperating' => $this->strategyOperating,
        ]);
    }

    /**
     * @name 添加催收专员
     * @return string
     */
    public function actionMonitorAdd(){

        $model = new AdminUser();
        $merchantId = Yii::$app->user->identity->merchant_id;
        $outsideRealName = UserCompany::outsideRealName($merchantId);
        if($this->request->isPost)
        {
            $model->load($this->request->post(),null,$this->strategyOperating);
            $model->role = AdminUserRole::$groups_default_role_map[AdminUserRole::TYPE_COMPANY_MANAGER];
            $model->created_user = Yii::$app->user->identity->username;
            if($model->outside <= 0 || !isset($outsideRealName[$model->outside])){
                return $this->redirectMessage('outside error', self::MSG_ERROR);
            }
            $model->merchant_id = $merchantId;
            $model->group = 0;
            $model->group_game = 0;
            if ($this->isNotMerchantAdmin) {
                $model->to_view_merchant_id = implode(',', $this->request->post('to_view_merchant_id'));
            } else {
                $model->to_view_merchant_id = $merchantId;
            }
            if ($model->validate()) {
                $model->password = Yii::$app->security->generatePasswordHash($model->password);
                if ($model->save(false)) {
                    return $this->redirectMessage('add success', self::MSG_SUCCESS, '',-2);
                } else {
                    return $this->redirectMessage('add fail', self::MSG_ERROR);
                }
            }
        }

        return $this->render('monitor-add', array(
            'model' => $model,
            'outsideRealName' => $outsideRealName,
            'isNotMerchantAdmin' => $this->isNotMerchantAdmin,
            'arrMerchantIds' => Merchant::getMerchantId(),
            'strategyOperating' => $this->strategyOperating,
        ));
    }

    /**
     * @name 编辑催收专员
     * @return string
     */
    public function actionMonitorEdit($id){
        $merchantId = Yii::$app->user->identity->merchant_id;
        $monitorRoles = AdminUserRole::getRolesByGroup(AdminUserRole::TYPE_COMPANY_MANAGER);
        if(empty($monitorRoles)){
            return $this->redirectMessage('not role', self::MSG_ERROR);
        }
        $model = AdminUser::findOne(['id' => intval($id), 'merchant_id' => $merchantId,'role' => $monitorRoles]);
        if(!$model){
            return $this->redirectMessage('can\'t edit', self::MSG_ERROR);
        }
        $oldPhone = $model->phone;
        $starPhone = substr_replace($oldPhone,'*****',0,5);
        $outsideRealName =  UserCompany::outsideRealName($merchantId);
        if($this->request->isPost)
        {
            $model->load($this->request->post(),null,$this->strategyOperating);
            if($this->isHiddenPhone && $starPhone == $model->phone){
                $model->phone = $oldPhone;
            }
            if ($this->isNotMerchantAdmin) {
                $model->to_view_merchant_id = implode(',', $this->request->post('to_view_merchant_id'));
            }
            if($model->outside <= 0){
                return $this->redirectMessage('outside error', self::MSG_ERROR);
            }
            if(!isset($outsideRealName[$model->outside])){
                return $this->redirectMessage('outside error', self::MSG_ERROR);
            }
            if($model->group != 0){
                return $this->redirectMessage('group error', self::MSG_ERROR);
            }
            if($model->group_game != 0){
                return $this->redirectMessage('group_game error', self::MSG_ERROR);
            }
            $model->created_user = Yii::$app->user->identity->username;
            if ($model->validate(['phone','username','outside'])) {
                if ($model->save(false)) {
                    return $this->redirectMessage('success', self::MSG_SUCCESS,'',-2);
                } else {
                    return $this->redirectMessage('fail', self::MSG_ERROR);
                }
            }

        }

        if($this->isHiddenPhone){
            $model->phone = $starPhone;
        }

        return $this->render('monitor-edit', array(
            'model' => $model,
            'outsideRealName' => $outsideRealName,
            'isNotMerchantAdmin' => $this->isNotMerchantAdmin,
            'arrMerchantIds' => Merchant::getMerchantId(),
            'strategyOperating' => $this->strategyOperating,
        ));
    }

    /**
     * @name 超级组长列表
     **/
    public function actionSuperTeamLeaderList()
    {
        $condition[] = 'and';
        if ($this->request->get('search_submit')) { // 过滤
            $search = $this->request->get();
            if (isset($search['phone']) && $search['phone'] != '') {
                $condition[] = ['like', 'phone', trim($search['phone'])];
            }
            if (isset($search['username']) && $search['username'] != '') {
                $condition[] = ['like', 'username', trim($search['username'])];
            }
            if (isset($search['merchant_id']) && $search['merchant_id'] != '') {
                $condition[] = ['merchant_id' => intval($search['merchant_id'])];
            }
        }
        $roles = AdminUserRole::getRolesByGroup(AdminUserRole::TYPE_SUPER_TEAM);
        if($roles){
            $query = AdminUser::find()
                ->where(['merchant_id' => $this->merchantIds])
                ->andWhere($condition)
                ->andWhere([
                    'open_status'=>AdminUser::$usable_status,
                    'role' => $roles
                ])
                ->orderBy('id desc');

            $countQuery = clone $query;
            $pages = new Pagination(['totalCount' => $countQuery->count('id')]);
            $pages->pageSize = 15;
            $users = $query->offset($pages->offset)->limit($pages->limit)->asArray()->all();
        }else{
            $pages = new Pagination(['totalCount' => 0]);
            $pages->pageSize = 15;
            $users = [];
        }

        // 获取所有商户，只能是商户管理员的时候才在页面上显示
        if ($this->isNotMerchantAdmin) {
            $arrMerchant = Merchant::getMerchantId();
        } else {
            $arrMerchant = [];
        }

        return $this->render('super-team-leader-list', [
            'users' => $users,
            'pages' => $pages,
            'arrMerchant' => $arrMerchant,
            'setRealNameCollectionAdmin' => GlobalSetting::checkCollectionUsernameCanSetRealName(Yii::$app->user->identity),
        ]);
    }

    /**
     * @name 添加超级组长
     * @return string
     */
    public function actionSuperTeamLeaderAdd(){
        $model = new AdminUser();
        $managerRelationModel = new AdminManagerRelation();
        $outsideRealName = UserCompany::outsideRealName($this->merchantIds);
        if($this->request->isPost)
        {
            $adminUserParams = $this->request->post('AdminUser');
            $model->username = $adminUserParams['username'];
            $model->phone = $adminUserParams['phone'];
            $model->password = $adminUserParams['password'];
            $model->real_name = $adminUserParams['real_name'];
            $model->job_number = $adminUserParams['job_number'];
            $model->role = AdminUserRole::$groups_default_role_map[AdminUserRole::TYPE_SUPER_TEAM];
            $model->created_user = Yii::$app->user->identity->username;
            $model->merchant_id = Yii::$app->user->identity->merchant_id;

            $relationParams = $this->request->post('AdminManagerRelation');
            $managerRelationModel->outside = $relationParams['outside'];
            $managerRelationModel->group = $relationParams['group'];
            $managerRelationModel->group_game = $relationParams['group_game'];

            if ($model->validate() && $managerRelationModel->validate(['outside','group','group_game'])) {
                $model->password = Yii::$app->security->generatePasswordHash($model->password);
                if ($model->save(false)) {
                    foreach ($managerRelationModel->labelArr as $item){
                        $managerRelationModelNew = new AdminManagerRelation();
                        $managerRelationModelNew->admin_id = $model->id;
                        $managerRelationModelNew->outside = $item['outside'];
                        $managerRelationModelNew->group = $item['group'];
                        $managerRelationModelNew->group_game = $item['group_game'];;
                        $managerRelationModelNew->save();
                    }
                    return $this->redirectMessage('add success', self::MSG_SUCCESS, Url::toRoute('user-collection/super-team-leader-list'));
                } else {
                    return $this->redirectMessage('add fail', self::MSG_ERROR);
                }
            }
        }

        //生成随机密码
        $password = CommonHelper::make_password(12);
        return $this->render('super-team-leader-form', array(
            'model' => $model,
            'managerRelationModel' => $managerRelationModel,
            'outsideRealName' => $outsideRealName,
            'password' => $password,
            'arrMerchantIds' => Merchant::getMerchantByIds($this->merchantIds,$this->isNotMerchantAdmin),
            'isNotMerchantAdmin' => $this->isNotMerchantAdmin,
            'labelArr' => $managerRelationModel->labelArr
        ));
    }

    /**
     * @name 编辑超级组长
     * @return string
     */
    public function actionSuperTeamLeaderEdit($id){
        $outsideRealName = UserCompany::outsideRealName($this->merchantIds);
        $roles = AdminUserRole::getRolesByGroup(AdminUserRole::TYPE_SUPER_TEAM);
        if(empty($roles)){
            return $this->redirectMessage('not role', self::MSG_ERROR);
        }
        /** @var AdminUser $model */
        $model = AdminUser::find()->where(['id' => $id, 'merchant_id' => $this->merchantIds])->one();

        if(empty($model) || !in_array($model->role, $roles)){
            return $this->redirectMessage('can\'t edit', self::MSG_ERROR);
        }

        $existRelationArr = [];
        $labelArr = [];
        $managerRelationModel = new AdminManagerRelation();
        $managerRelationModels = AdminManagerRelation::find()->where(['admin_id' => $id])->all();
        foreach ($managerRelationModels as $key => $item){
            /** @var AdminManagerRelation $item */
            $existRelationArr[$item->outside][$item->group][$item->group_game] = $item->id;
            $labelArr[$key] = ['outside' => $item->outside, 'group' => $item->group, 'group_game' => $item->group_game];
        }

        if($this->request->isPost)
        {
            $adminUserParams = $this->request->post('AdminUser');
            $model->username = $adminUserParams['username'];
            $model->phone = $adminUserParams['phone'];
            $model->real_name = $adminUserParams['real_name'];
            $model->job_number = $adminUserParams['job_number'];

            $relationParams = $this->request->post('AdminManagerRelation');
            $managerRelationModel->outside = $relationParams['outside'];
            $managerRelationModel->group = $relationParams['group'];
            $managerRelationModel->group_game = $relationParams['group_game'];
            if ($model->validate(['username','phone','real_name','job_number']) && $managerRelationModel->validate(['outside','group','group_game'])) {
                $relationAddArr = [];
                $existRelationIds = [];
                foreach ($managerRelationModel->labelArr as $item){
                    if (isset($existRelationArr[$item['outside']][$item['group']][$item['group_game']])) {
                        $existRelationIds[] = $existRelationArr[$item['outside']][$item['group']][$item['group_game']];
                    }else{
                        $relationAddArr[] = ['outside' => $item['outside'],'group' => $item['group'],'group_game' => $item['group_game']];
                    }
                }
                if ($model->save(false)) {
                    AdminManagerRelation::deleteAll([
                        'AND',
                        ['admin_id' => $model->id],
                        ['NOT IN','id',$existRelationIds]
                    ]);
                    foreach ($relationAddArr as $item){
                        $managerRelationModelNew = new AdminManagerRelation();
                        $managerRelationModelNew->admin_id = $model->id;
                        $managerRelationModelNew->outside = $item['outside'];
                        $managerRelationModelNew->group = $item['group'];
                        $managerRelationModelNew->group_game = $item['group_game'];
                        $managerRelationModelNew->save();
                    }
                    return $this->redirectMessage('edit success', self::MSG_SUCCESS, Url::toRoute('user-collection/super-team-leader-list'));
                } else {
                    return $this->redirectMessage('edit fail', self::MSG_ERROR);
                }
            }
            $labelArr = $managerRelationModel->labelArr;
        }

        return $this->render('super-team-leader-form', array(
            'model' => $model,
            'managerRelationModel' => $managerRelationModel,
            'isNotMerchantAdmin' => $this->isNotMerchantAdmin,
            'outsideRealName' => $outsideRealName,
            'arrMerchantIds' => Merchant::getMerchantByIds($this->merchantIds,$this->isNotMerchantAdmin),
            'labelArr' => $labelArr
        ));
    }

    /**
     * @name 设置超级组长副手
     * @return string
     */
    public function actionSetSuperTeamLeaderDeputy($id){
        $stRoles = AdminUserRole::getRolesByGroup(AdminUserRole::TYPE_SUPER_TEAM);
        if(empty($stRoles)){
            return $this->redirectMessage('not role', self::MSG_ERROR);
        }
        /** @var AdminUser $model */
        $model = AdminUser::find()->where(['id' => $id, 'merchant_id' => $this->merchantIds,'role' => $stRoles])->one();

        /** @var AdminUserMasterSlaverRelation $deputyModel */
        $deputyModel = AdminUserMasterSlaverRelation::find()->where(['admin_id' => $model->id])->one();

        if(is_null($deputyModel)){
            $deputyModel = new AdminUserMasterSlaverRelation();
        }
        $deputyModel->admin_id = $model->id;

        $role = AdminUserRole::getRolesByGroup([AdminUserRole::TYPE_SMALL_TEAM_MANAGER,AdminUserRole::TYPE_BIG_TEAM_MANAGER]);

        $service = new RoleSuperTeamService($model);
        $teamLeaderList = $service->getTeamLeaderList(true); //超级组长他的team list

        if($this->request->isPost)
        {
            $adminUserMasterSlaverRelation = $this->request->post('AdminUserMasterSlaverRelation');
            $deputyModel->slave_admin_id = !empty($adminUserMasterSlaverRelation['slave_admin_id']) ? $adminUserMasterSlaverRelation['slave_admin_id'] : 0;
            if($deputyModel->validate()){
                if($deputyModel->slave_admin_id > 0){
                    /** @var AdminUser $loanCollection */
                    $loanCollection = AdminUser::find()->select(['id','username','outside','group','group_game'])
                        ->where(['open_status' => AdminUser::$usable_status, 'role' => $role, 'id' => $deputyModel->slave_admin_id])
                        ->asArray()
                        ->one();
                    $key = $loanCollection['id'];
                    if(isset($teamLeaderList[$key])){
                        $deputyModel->save();
                    }
                } else{
                    if(!$deputyModel->isNewRecord){
                        $deputyModel->save();
                    }
                }
                return $this->redirectMessage('edit success', self::MSG_SUCCESS, '',-2);
            }
            return $this->redirectMessage('edit fail', self::MSG_ERROR, '',-1);
        }
        return $this->render('set-super-team-leader-deputy', array(
            'model' => $model,
            'teamLeaderList' => $teamLeaderList,
            'deputyModel' => $deputyModel,
        ));
    }

    /**
     * @name 删除超级组长
     * @return string
     */
    public function actionSuperTeamLeaderDel(){
        if ( !empty($uid = $this->request->get('id'))) {
            $res = $this->delUser($uid,AdminUserRole::TYPE_SUPER_TEAM);
            if($res['code'] == 0){
                return $this->redirectMessage('delete success', self::MSG_SUCCESS);
            }else{
                return $this->redirectMessage('delete fail：,'.$res['message'], self::MSG_ERROR);
            }
        }
        echo 'oops';
    }

    /**
     * @name UserCollectionController admin list
     **/
    public function actionAdminList()
    {
        $condition[] = 'and';
        if ($this->request->get('search_submit')) { // 过滤
            $search = $this->request->get();
            $search['username'] = str_replace("'","", $search['username']);
            $search['phone'] = str_replace("'","", $search['phone']);
            if (isset($search['phone']) && $search['phone'] != '') {
                $condition[] = ['like', 'A.phone', trim($search['phone'])];
            }
            if (isset($search['username']) && $search['username'] != '') {
                $condition[] = ['like', 'A.username', trim($search['username'])];
            }
            // 防止拼接参数来绕过判断
            if ($this->isNotMerchantAdmin) {
                if (isset($search['merchant_id']) && $search['merchant_id'] != '') {
                    $condition[] = ['A.merchant_id' => intval($search['merchant_id'])];
                }
            }
        }

        $managerRoles = AdminUserRole::getRolesByGroup(AdminUserRole::TYPE_SUPER_MANAGER);
        if($managerRoles){
            $query = AdminUser::find()
                ->select('A.*,B.real_title')
                ->from(AdminUser::tableName() .' A')
                ->leftJoin(UserCompany::tableName() . ' B', 'A.outside = B.id')
                ->where(['A.merchant_id' => $this->merchantIds])
                ->andWhere($condition)
                ->andWhere([
                    'A.open_status'=>AdminUser::$usable_status,
                    'A.role' => $managerRoles
                ])
                ->orderBy('A.id desc');
            $countQuery = clone $query;
            $pages = new Pagination(['totalCount' => $countQuery->count('A.id')]);
            $pages->pageSize = 15;
            $users = $query->offset($pages->offset)->limit($pages->limit)->asArray()->all();
        }else{
            $pages = new Pagination(['totalCount' => 0]);
            $pages->pageSize = 15;
            $users = [];
        }

        // 获取所有商户，只能是商户管理员的时候才在页面上显示
        if ($this->isNotMerchantAdmin) {
            $arrMerchant = Merchant::getMerchantId();
        } else {
            $arrMerchant = [];
        }

        return $this->render('admin-list', [
            'users' => $users,
            'pages' => $pages,
            'merchant_id' => $this->merchantIds,
            'arrMerchant' => $arrMerchant,
            'isHiddenPhone' => $this->isHiddenPhone
        ]);
    }

    /**
     * @name UserCollectionController Add admin
     **/
    public function actionAdminAdd(){

        $model = new AdminUser();
        if($this->request->isPost)
        {
            $managerRoles = AdminUserRole::getRolesByGroup(AdminUserRole::TYPE_SUPER_MANAGER);
            $model->load($this->request->post());
            $model->outside = 0;
            $model->role = in_array(Yii::$app->user->identity->role,$managerRoles)
                ? Yii::$app->user->identity->role : AdminUserRole::$groups_default_role_map[AdminUserRole::TYPE_SUPER_MANAGER];
            $model->created_user = Yii::$app->user->identity->username;
            if($this->isNotMerchantAdmin)
            {
                $model->merchant_id = 0;
                $model->to_view_merchant_id = 0;
            }else{
                $model->merchant_id = $this->merchantIds;
                $model->to_view_merchant_id = $this->merchantIds;
            }
            $model->group = 0;
            $model->group_game = 0;
            if ($model->validate()) {
                $model->password = Yii::$app->security->generatePasswordHash($model->password);
                if ($model->save(false)) {
                    return $this->redirectMessage('add success', self::MSG_SUCCESS, '',-2);
                } else {
                    return $this->redirectMessage('add fail', self::MSG_ERROR);
                }
            }

        }
        return $this->render('admin-add', array(
            'model' => $model,
        ));
    }

    /**
     * @name 编辑admin
     * @return string
     */
    public function actionAdminEdit($id){
        $managerRoles = AdminUserRole::getRolesByGroup(AdminUserRole::TYPE_SUPER_MANAGER);
        $model = AdminUser::findOne(['id' => intval($id), 'merchant_id' => $this->merchantIds,'role' => $managerRoles]);
        if(!$model){
            return $this->redirectMessage('can\'t edit', self::MSG_ERROR);
        }
        $oldPhone = $model->phone;
        $starPhone = substr_replace($oldPhone,'*****',0,5);
        if($this->request->isPost)
        {
            $model->load($this->request->post());
            if($this->isHiddenPhone && $starPhone == $model->phone){
                $model->phone = $oldPhone;
            }
            $model->created_user = Yii::$app->user->identity->username;
            if ($model->validate(['phone','username','outside'])) {
                if ($model->save(false)) {
                    return $this->redirectMessage('success', self::MSG_SUCCESS, '',-2);
                } else {
                    return $this->redirectMessage('fail', self::MSG_ERROR);
                }
            }
        }
        if($this->isHiddenPhone){
            $model->phone = $starPhone;
        }
        return $this->render('admin-edit', array(
            'model' => $model,
        ));
    }

    /**
     * @name 删除admin
     * @return string
     */
    public function actionAdminDel(){
        if ( !empty($uid = $this->request->get('id'))) {
            $res = $this->delUser($uid,AdminUserRole::TYPE_SUPER_MANAGER);
            if($res['code'] == 0){
                return $this->redirectMessage('delete success', self::MSG_SUCCESS);
            }else{
                return $this->redirectMessage('delete fail：,'.$res['message'], self::MSG_ERROR);
            }

        }
        echo 'oops';
    }

    /**
     * 修改密码
     *
     * @name 修改密码
     */
    public function actionChangePwd($id)
    {
        $id  = intval($id);
        $query = AdminUser::find()->where(['id' => $id, 'merchant_id' => $this->merchantIds]);
        if(Yii::$app->user->identity->outside > 0){
            $query->andWhere(['outside' => Yii::$app->user->identity->outside]);
        }
        $model = $query->one();
        if (!$model) {
            return $this->redirectMessage('User does not exist', self::MSG_ERROR);
        }
        $model->password = '';
        $model->load($this->request->post());
        if(!$this->isNotMerchantAdmin)
        {
            $model->merchant_id = $this->merchantIds;
        }
        if ($model->validate(['password'])) {
            $model->password = Yii::$app->security->generatePasswordHash($model->password);
            if ($model->save(false)) {
                return $this->redirectMessage('edit success', self::MSG_SUCCESS, '',-2);
            } else {
                return $this->redirectMessage('edit fail', self::MSG_ERROR);
            }
        }
        //生成随机密码
        $password = CommonHelper::make_password(12);
        return $this->render('change-pwd', [
            'model' => $model,
            'password' => $password,
        ]);
    }

    /**
     * @name 删除人员
     * @return string
     */
    public function actionDel(){
        if ( !empty($uid = $this->request->get('id'))) {
            $res = $this->delUser($uid,AdminUserRole::TYPE_COLLECTION);
            if($res['code'] == 0){
                return $this->redirectMessage('delete success', self::MSG_SUCCESS);
            }else{
                return $this->redirectMessage('delete fail：,'.$res['message'], self::MSG_ERROR);
            }
        }
        echo 'oops';
    }

    /**
     * @name 删除经理
     * @return string
     */
    public function actionMonitorDel(){
        if ( !empty($uid = $this->request->get('id'))) {
            $res = $this->delUser($uid,AdminUserRole::TYPE_COMPANY_MANAGER);
            if($res['code'] == 0){
                return $this->redirectMessage('delete success', self::MSG_SUCCESS);
            }else{
                return $this->redirectMessage('delete fail：,'.$res['message'], self::MSG_ERROR);
            }
        }
        echo 'oops';
    }

    /**
     * @name 批量添加催收员
     */
    public function actionBatchAdd()
    {
        if ($this->request->post()) {
            ini_set('max_execution_time', '100');
            $merchant_id = Yii::$app->user->identity->merchant_id;
            $file = UploadedFile::getInstanceByName('files');
            if (!$file) {
                return $this->redirectMessage('upload failed', self::MSG_ERROR);
            }
            $extension = $file->extension;
            if ($extension != 'csv') {
                return $this->redirectMessage('Please import the correct format file', self::MSG_ERROR);
            }
            $path = '/tmp/';
            if (!file_exists($path)) {
                \yii\helpers\BaseFileHelper::createDirectory($path);
            }
            $url = $path . $file->baseName . date("YmdHis") . '.' . $file->extension;
            $re = $file->saveAs($url);
            if (!$re) {
                return $this->redirectMessage('upload failed', self::MSG_ERROR);
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

                if(empty($tmp[0])){
                    continue;
                }
                $new_tmp = AdminUser::getNewTmp($tmp);
                $_user_collection = AdminUser::findByUsername($new_tmp['username']);

                if (!empty($_user_collection)){
                    $new_tmp['error_message'] = '用户名已存在';
                    $error_data[] = $new_tmp;
                    continue;
                }
                if (!in_array($new_tmp['group'],array_keys(LoanCollectionOrder::$level))){
                    $new_tmp['error_message'] = 'group不存在';
                    $error_data[] = $new_tmp;
                    continue;
                }
                if($this->strategyOperating){
                    if (isset($new_tmp['open_search_label']) && !in_array($new_tmp['open_search_label'],array_keys(AdminUser::$can_search_label_map))){
                        $new_tmp['error_message'] = 'open_search_label字段范围错误';
                        $error_data[] = $new_tmp;
                        continue;
                    }
                    if (isset($new_tmp['login_app']) && !in_array($new_tmp['login_app'],array_keys(AdminUser::$can_login_app_map))) {
                        $new_tmp['error_message'] = 'login_app字段范围错误';
                        $error_data[] = $new_tmp;
                        continue;
                    }
                    if (isset($new_tmp['nx_phone']) && !in_array($new_tmp['nx_phone'],array_keys(AdminUser::$can_use_nx_phone_map))) {
                        $new_tmp['error_message'] = 'nx_phone字段范围错误';
                        $error_data[] = $new_tmp;
                        continue;
                    }
                    if (isset($new_tmp['real_name']) && !in_array($new_tmp['nx_phone'],array_keys(AdminUser::$can_use_nx_phone_map))) {
                        $new_tmp['error_message'] = 'nx_phone字段范围错误';
                        $error_data[] = $new_tmp;
                        continue;
                    }
                }
                $admin_user = AdminUser::findByUsername($new_tmp['username']);
                if (empty($admin_user)) {
                    //创建登录账户：
                    $outside = UserCompany::id($new_tmp['outside']);
                    if (empty($outside)) {
                        $new_tmp['error_message'] = '公司ID不存在';
                        $error_data[] = $new_tmp;
                        continue;
                    }

                    $new_tmp['role'] = AdminUserRole::$groups_default_role_map[AdminUserRole::TYPE_COLLECTION];
                    if($this->isNotMerchantAdmin)
                    {
                        $new_tmp['merchant_id'] = 0;
                    }else{
                        if($merchant_id != $outside->merchant_id){
                            $new_tmp['error_message'] = '无法操作其他商户';
                            $error_data[] = $new_tmp;
                            continue;
                        }
                        $new_tmp['merchant_id'] = $this->merchantIds;
                    }

                    $model = new AdminUser();
                    if ($model->load($new_tmp, '', $this->strategyOperating)) {
                        if (!$model->validate()) {
                            $new_tmp['error_message'] = $model->getErrorSummary(false)[0];
                            $error_data[] = $new_tmp; //将失败的数据放入数组中
                            continue;
                        }
                        if($this->isNotMerchantAdmin)
                        {
                            $model->merchant_id = $outside->merchant_id;
                            $model->to_view_merchant_id = $outside->merchant_id;
                        }else{
                            $model->merchant_id = $this->merchantIds;
                            $model->to_view_merchant_id = $this->merchantIds;
                        }
                        $model->mark = $new_tmp['username'] . '/' . $outside['title'];
                        $model->role = $new_tmp['role'];
                        $model->created_user = Yii::$app->user->identity->username;
                        $model->password = Yii::$app->security->generatePasswordHash($new_tmp['password']);
                        $model->can_dispatch = AdminUser::CAN_DISPATCH;
                        if (!$model->save(false)) {
                            $new_tmp['error_message'] = '数据保存失败';
                            $error_data[] = $new_tmp; //将失败的数据放入数组中
                            continue;
                        }
                    }
                    $admin_user = AdminUser::findByUsername($new_tmp['username']);
                    if (empty($admin_user)) {
                        $new_tmp['error_message'] = '添加数据不存在';
                        $error_data[] = $new_tmp; //将失败的数据放入数组中
                        continue;
                    }
                    $i++;
                }
            }
            $url = Url::toRoute(['user-collection/error-export','data'=>$error_data]);
            if(($y - 1) != $i){
                return $this->redirectMessage('Import' . ($y - 1) . 'article,success' . $i . 'article!   <a href="'.$url.'">Download failed data</a>', self::MSG_SUCCESS);
            }else{
                return $this->redirectMessage('Import' . ($y - 1) . 'article,success' . $i . 'article!', self::MSG_SUCCESS,Url::toRoute(['user-collection/user-list']));
            }

        }
    }
    /**
     * @name 失败数据导出
     */
    public function actionErrorExport(){
        $data = $this->request->get('data');
        if($data){
            $this->_setcsvHeader('Failed_data_export.csv');
            $items = [];
            foreach ($data as $k=>$value) {
                $items[] = [
                    'outside_id'=>$value['outside'],
                    'group_id' =>$value['group'],
                    'group_game_id'=>$value['group_game'],
                    'phone'=>$value['phone'],
                    'username' =>$value['username'],
                    'password'=>$value['password'],
                    'error_message'=>$value['error_message'],
                    //'角色' =>$value['role'],
                ];
            }
            echo $this->_array2csv($items);
            exit;
        }
    }

    /**
     * @name 催收员导出文件
     * @date 2017-11-22
     * @use 催收人员列表导出
     * @param
     * @reutrn
     */
    public function _exportCollectionPersonnel($merchantIds,$isHiddenPhone,$arr,$setRealNameCollectionAdmin){
        \ini_set('memory_limit', "512m");
        $this->_setcsvHeader('collection list export.csv');
        echo "ID,用户名,".($setRealNameCollectionAdmin ? "真实名,":"")."联系方式,用户组,小组分组,机构,状态(是否启用),创建时间\n";
        foreach($arr as $key => $value){
            $str = '';
            $str .= $value['id'].',';
            $str .= (isset($value['username'])?$value['username']:'').',';
            if($setRealNameCollectionAdmin){
                $str .= (isset($value['real_name'])?$value['real_name']:'').',';
            }
            $str .= ($isHiddenPhone ? substr_replace($value['phone'],'*****',0,5) : $value['phone']).',';
            $str .= (isset($value['group'])?LoanCollectionOrder::$level[$value['group']]:'').',';
            $str .= (isset($value['group_game'])? AdminUser::$group_games[$value['group_game']]:'').',';
            //$str .= rtrim($value['role'],',').',';
            $str .= (isset($value['outside'])?UserCompany::outsideRealName($merchantIds,$value['outside']):'').',';
            $str .= (isset(AdminUser::$open_status_list[$value['open_status']]) ?AdminUser::$open_status_list[$value['open_status']]:"--" ).',';
            $str .= (isset($value['created_at'])?date("Y-m-d H:i:s" , $value['created_at']):'').',';
            $str .= "\n";
            echo $str;
        }die;
    }

    /**
     * @name 催收员批量导入模板
     */
    private function _ExportTemplate($strategyOperating){
        $this->_setcsvHeader('Account import template(Notice the notes!!!).csv');
        $items = [];
        if($strategyOperating){
            $data[] = [1,4,1,'zs001','15866778899',123456,1,1,1,'zhangsan',40001];
            $data[] = [1,4,1,'ls001','15866778888',123456,1,1,1,'lisi','40002'];
            $data[] = [2,5,1,'wx001','15866775566',123456,1,1,1,'wangxiao',40003];
            $data[] = [2,5,2,'zs002','15866771122',123456,0,0,0,'zhangsan2',40004];
            $data[] = [3,6,1,'ls002','15866772233',123456,0,0,0,'lisi',40005];
            $data[] = [3,6,3,'wx002','15866773344',123456,0,0,0,'wangxiao2',40006];
            foreach ($data as $k=>$value) {
                $items[] = [
                    'company id'=>$value[0],
                    'group id(4=s1,5=s2,6=s3)' =>$value[1],
                    'team id'=>$value[2],
                    'username' =>$value[3],
                    'phone'=>$value[4],
                    'password'=>$value[5],
                    'open_search_label'=>$value[6],
                    'login_app'=>$value[7],
                    'nx_phone' => $value[8],
                    'real_name' => $value[9],
                    'job_number' => $value[10],
                ];
            }
        }else{
            $data[] = [1,4,1,'zhangsan','15866778899',123456];
            $data[] = [1,4,1,'lisi','15866778888',123456];
            $data[] = [2,5,1,'wangxiao','15866775566',123456];
            $data[] = [2,5,2,'zhangsan2','15866771122',123456];
            $data[] = [3,6,1,'lisi2','15866772233',123456];
            $data[] = [3,6,3,'wangxiao2','15866773344',123456];
            foreach ($data as $k=>$value) {
                $items[] = [
                    'company id'=>$value[0],
                    'group id(4=s1,5=s2,6=s3)' =>$value[1],
                    'team id'=>$value[2],
                    'username' =>$value[3],
                    'phone'=>$value[4],
                    'password'=>$value[5],
//                'open_search_label'=>$value[6],
//                'login_app'=>$value[7],
                    //'role(cui_ren=催收员,cui_person=催收组长,cui_person_m=催收主管)' =>$value[7],
                ];
            }
        }




        echo $this->_array2csv($items);
        exit;
    }

    /**
     * @name UserCollectionController 公司小组备注
     * @return string|array
     */
    public function actionTeam(){
        if(Yii::$app->user->identity->outside > 0){
            $isSelf = false;
        }else{
            $isSelf = true;
        }
        if($this->request->isPost){
            if(Yii::$app->user->identity->outside > 0){
                $outside = Yii::$app->user->identity->outside;
            }else{
                $outside = $this->request->post('outside', '');
            }
            $companyTeam = CompanyTeam::find()->where(['outside' => $outside])->asArray()->all();
            $companyTeamArr = [];
            foreach ($companyTeam as $item){
                $companyTeamArr[$item['team']] = $item['alias'];
            }
            $this->response->format = Response::FORMAT_JSON;
            return $companyTeamArr;

        }
        $outsideRealName =  UserCompany::outsideRealName($this->merchantIds);
        return $this->render('team', array(
            'outsides' => $outsideRealName,
            'isSelf' => $isSelf
        ));
    }

    /**
     * @name UserCollectionController 公司编辑小组备注
     * @return string
     */
    public function actionTeamJsEdit(){

        $this->response->format = Response::FORMAT_JSON;
        if($this->request->isPost) {
            if(Yii::$app->user->identity->outside > 0){
                $outside = Yii::$app->user->identity->outside;
            }else{
                $outside = $this->request->post('outside', 0);
            }

            $team = $this->request->post('team', 0);
            $alias = $this->request->post('alias', '');
            if(!isset(AdminUser::$group_games[$team])){
                return ['code' => -1,'message' => 'group_games not exist'];
            }
            $company = UserCompany::findOne($outside);
            if(is_null($company)){
                return ['code' => -1,'message' => 'company not exist'];
            }
            /** @var CompanyTeam $model */
            $model = CompanyTeam::find()->where(['outside' => $outside,'team' => $team])->one();
            if(is_null($model)){
                $model = new CompanyTeam();
                $model->outside = $outside;
                $model->team = $team;
            }
            $model->alias = $alias;
            if ($model->save()) {
                return ['code' => 0];
            }else{
                return ['code' => -1,'message' => json_encode($model->getErrors())];
            }
        }
        return ['code' => -1];
    }

    /**
     * @name 获取对应公司的team
     * @return array
     */
    public function actionJsGetTeam(){
        $this->response->format = Response::FORMAT_JSON;
        if(Yii::$app->user->identity->outside > 0){
            $teamList = CompanyTeam::getTeamsByOutside(Yii::$app->user->identity->outside);
        }else{
            $outside = $this->request->get('outside',1);
            $teamList = CompanyTeam::getTeamsByOutside($outside);
        }
        return CommonHelper::HtmlEncodeToArray($teamList);
    }

    /**
     * @name UserCollectionController 获取登录验证码发送结果
     * @return string
     */
    public function actionGetLoginSmsCode(){
        $result = '';
        $phone = '';
        if($this->request->isPost){
            $phone = $this->request->post('phone','');

            $adminUser = AdminUser::find()->select(['phone'])->where([
                    'role' => AdminUserRole::getRolesByGroup([AdminUserRole::TYPE_COLLECTION,AdminUserRole::TYPE_SMALL_TEAM_MANAGER,AdminUserRole::TYPE_BIG_TEAM_MANAGER,AdminUserRole::TYPE_COMPANY_MANAGER]),
                    'phone' => $phone,'merchant_id' => $this->merchantIds
                ])->one();
            if($adminUser){
                $adminUserCaptcha = AdminUserCaptcha::find()->where(['phone' => $phone,'type' => AdminUserCaptcha::TYPE_ADMIN_CS_LOGIN])->one();
                if($adminUserCaptcha){
                    $result = 'username:'.$adminUser['username'].',code:'.$adminUserCaptcha['captcha'];
                }else{
                    $result = 'code not exist';
                }
            }else{
                $result = 'get fail';
            }
        }
        return $this->render('get-login-sms-code',['result' => $result,'phone' => $phone]);
    }

    /**
     * @name 登录日志
     * @return string
     */
    public function actionLoginLog(){
        $params = $this->request->get();
        $where = [];
        if(isset($params['user_id']) && intval($params['user_id'])){
            $where['A.user_id'] = intval($params['user_id']);
        }
        if(isset($params['username']) && trim($params['username'])){
            $where['A.username'] = trim($params['username']);
        }
        if(isset($params['phone']) && trim($params['phone'])){
            $where['A.phone'] = trim($params['phone']);
        }
        if(isset($params['username']) && trim($params['username'])){
            $where['A.username'] = trim($params['username']);
        }
        if(isset($params['outside']) && intval($params['outside'])){
            $where['B.outside'] = intval($params['outside']);
        }
        if(isset($params['group']) && intval($params['group'])){
            $where['B.group'] = intval($params['group']);
        }
        if(isset($params['group_game']) && intval($params['group_game'])){
            $where['B.group_game'] = intval($params['group_game']);
        }
        if(isset($params['group_game']) && intval($params['group_game'])){
            $where['B.group_game'] = intval($params['group_game']);
        }
        $collectorRoles = AdminUserRole::getRolesByGroup(AdminUserRole::TYPE_COLLECTION);
        $adminLoginLog = AdminLoginLog::find()
            ->select([
                'A.id',
                'A.user_id',
                'A.username',
                'A.phone',
                'C.real_title',
                'B.group',
                'B.group_game',
                'A.ip',
                'A.created_at',
            ])
            ->from(AdminLoginLog::tableName() .' A')
            ->leftJoin(AdminUser::tableName() .' B','A.user_id = B.id')
            ->leftJoin(UserCompany::tableName(). ' C','B.outside = C.id')
            ->where(['B.role' => $collectorRoles,'B.merchant_id' => $this->merchantIds])
            ->andWhere($where);
        if(isset($params['login-start-time']) && $params['login-start-time']!=''){
            $adminLoginLog->andWhere(['>=','A.created_at',strtotime($params['login-start-time'])]);
        }
        if(isset($params['login-end-time']) && $params['login-end-time']!=''){
            $adminLoginLog->andWhere(['<=','A.created_at',strtotime($params['login-end-time'])]);
        }

        if($this->request->get('exportcsv') == 'exportData'){
            $totalQuery = clone $adminLoginLog;
            $totalCount = $totalQuery->count();
            $this->_setcsvHeader("login-log.csv");
            if($totalCount > 10000){
                echo Yii::T('common', 'The amount of data is too large, please export in stages');exit;
            }
            $items = [];
            $data = $adminLoginLog->orderby('A.id DESC')->asArray()->all();
            foreach($data as $value){
                $items[] = [
                    'id' => $value['id'] ?? 0,
                    'user id' => $value['user_id'] ?? 0,
                    'username' => $value['username'],
                    'phone' => $value['phone'],
                    'merchant_name' => $value['merchant_name'] ?? 0,
                    'company' => $value['real_title']?? '--',
                    'group' => LoanCollectionOrder::$level[$value['group']] ?? '--',
                    'group_game' => AdminUser::$group_games[$value['group_game']] ?? '--',
                    'ip' => $value['ip'],
                    'login_time' => date('Y-m-d H:i:s', $value['created_at']),
                ];
            }
            echo $this->_array2csv($items);
            exit;
        }
        $pages = new Pagination(['totalCount' => 99999]);
        $page_size = Yii::$app->request->get('page_size',15);
        $pages->pageSize = $page_size;
        $list = $adminLoginLog->orderby('A.id DESC')
            ->offset($pages->offset)
            ->limit($pages->limit)
            ->asArray()
            ->all();
        $companyList = UserCompany::outsideRealName($this->merchantIds);
        return $this->render('login-log',array(
            'companyList' => $companyList,
            'list' => $list,
            'pages' => $pages,
        ));
    }

    /**
     * 删除用户
     * @param $userId
     * @param $group
     * @return array
     */
    private function delUser($userId,$group){
        try{
            $roles = AdminUserRole::getRolesByGroup($group);
            if(empty($roles)){
                return $this->redirectMessage('not role', self::MSG_ERROR);
            }
            $query = AdminUser::find()->where(['role' => $roles,'id' => $userId, 'merchant_id' => $this->merchantIds]);
            if(Yii::$app->user->identity->outside > 0){
                $query->andWhere(['outside' => Yii::$app->user->identity->outside]);
            }
            /** @var AdminUser $adminUser */
            $adminUser = $query->one();
            if(empty($adminUser)) throw new Exception("User does not exist");

            if(!$this->isNotMerchantAdmin)
            {
                $adminUser->merchant_id = $this->merchantIds;
            }
            $mission = LoanCollectionOrder::missionUser($adminUser['id']);

            if(!empty($mission[LoanCollectionOrder::STATUS_WAIT_COLLECTION]))   throw new Exception("[Pending order collection] exists for the current user and cannot be deleted");
            if(!empty($mission[LoanCollectionOrder::STATUS_COLLECTION_PROGRESS]))   throw new Exception("The current user has [collecting orders] and cannot be deleted");
            if(!empty($mission[LoanCollectionOrder::STATUS_COLLECTION_PROMISE]))   throw new Exception("[Commitment Repayment Order] exists for the current user and cannot be deleted");
            $adminUser->open_status = AdminUser::OPEN_STATUS_OFF;
            $adminUser->mark = $adminUser->mark . '('.Yii::$app->user->identity->username.' del at '.date('Y-m-d H:i:s').')';
            if(!$adminUser->save(false)){
                var_dump($adminUser->getErrors());
                throw  new \Exception("delete fail");
            }

            if(in_array($group,AdminUserRole::$team_leader_groups)){ //组长删除
                /** @var AdminUserMasterSlaverRelation $adminUserMasterSlaverRelation */
                $adminUserMasterSlaverRelation = AdminUserMasterSlaverRelation::find()->where(['admin_id' => $userId])->one();
                if($adminUserMasterSlaverRelation){
                    $adminUserMasterSlaverRelation->slave_admin_id = 0;
                    $adminUserMasterSlaverRelation->save();
                }
                /** @var AdminUserMasterSlaverRelation $adminUserMasterSlaverRelation */
                $adminUserMasterSlaverRelation = AdminUserMasterSlaverRelation::find()->where(['slave_admin_id' => $userId])->one();
                if($adminUserMasterSlaverRelation){
                    $adminUserMasterSlaverRelation->slave_admin_id = 0;
                    $adminUserMasterSlaverRelation->save();
                }
            }
            return ['code' => 0,'message' => 'success'];

        }catch (\Exception $e) {
            return ['code' => -1,'message' => $e->getMessage()];
        }
    }

    /**
     * @name 催收员班表(daily work plan)
     */
    public function actionClassSchedule(){
        $startDate = Yii::$app->request->get('start_date',date('Y-m-d'));
        $endDate = Yii::$app->request->get('end_date',date('Y-m-d',strtotime('+7 days')));
        /** @var AdminUser $adminUser */
        $adminUser = Yii::$app->user->identity;
        $str = "CASE ";
        AdminNxUser::$type_map;
        foreach (AdminNxUser::$type_map as $type => $value){
            $str.= "WHEN `type` = {$type} THEN '{$value}:' ";
        }
        $str.="END";
        $split = '<br/>';
        if($this->request->get('submitcsv') == 'exportData'){
            $split = PHP_EOL;
        }
        $queryNx = AdminNxUser::find()
            ->select([
                'collector_id',
                'nx_name_str' => "GROUP_CONCAT({$str},nx_name ORDER BY type asc SEPARATOR '{$split}' )",
                'nx_password_str' => "GROUP_CONCAT({$str},password ORDER BY type asc SEPARATOR '{$split}' )"
            ])
            ->where(['status' => AdminNxUser::STATUS_ENABLE])->groupBy('collector_id');
        $query = AdminUser::find()
            ->select([
                'A.id',
                'A.username',
                'A.phone',
                'A.real_name',
                'A.group',
                'A.group_game',
                'A.merchant_id',
                'C.real_title',
                'D.alias',
                'E.nx_name_str',
                'E.nx_password_str',
                'A.open_status',
                'A.can_dispatch',
                'A.updated_at',
                'A.created_at'
            ])
            ->alias('A')
            ->leftJoin(AdminUserRole::tableName().' B','A.role = B.name')
            ->leftJoin(UserCompany::tableName().' C','A.outside = C.id')
            ->leftJoin(CompanyTeam::tableName(). ' D','A.outside = D.outside AND A.group_game = D.team')
            ->leftJoin(['E' => $queryNx], 'E.collector_id = A.id')
            ->where(['B.groups' => AdminUserRole::TYPE_COLLECTION,'A.merchant_id' => $this->merchantIds])
            ->andWhere(['<','A.created_at',strtotime($endDate) + 86400])
            ->andWhere([
                'OR',
                [
                    //当前已离职
                    'AND',
                    ['A.open_status' => AdminUser::OPEN_STATUS_OFF],
                    ['>','A.updated_at',strtotime($startDate)]
                ],
                ['!=','A.open_status',AdminUser::OPEN_STATUS_OFF]

            ]);

        $roleGroup = AdminUserRole::getGroupByRoles($adminUser->role);
        $isManager = 0;
        if($roleGroup){
            switch ($roleGroup){
                case AdminUserRole::TYPE_SMALL_TEAM_MANAGER:
                    $query->andWhere(['A.outside' => $adminUser->outside,'A.group' => $adminUser->group,'A.group_game' => $adminUser->group_game]);
                    break;
                case AdminUserRole::TYPE_BIG_TEAM_MANAGER:
                    $query->andWhere(['A.outside' => $adminUser->outside]);
                    $adminManagerRelation = AdminManagerRelation::find()
                        ->select(['group','group_game'])
                        ->where(['admin_id' => $adminUser->id])
                        ->asArray()
                        ->all();
                    $orWhereArr = [];
                    foreach ($adminManagerRelation as $val){
                        $orWhereArr[] = ['A.group' => $val['group'], 'A.group_game' => $val['group_game']];
                    }
                    if($orWhereArr){
                        $orWhereArr = array_merge(['OR'],$orWhereArr);
                        $query->andWhere($orWhereArr);
                    }
                    break;
                case AdminUserRole::TYPE_COMPANY_MANAGER:
                    $query->andWhere(['A.outside' => $adminUser->outside]);
                    break;
                case AdminUserRole::TYPE_SUPER_MANAGER:
                    $isManager = 1;
                    break;
                case AdminUserRole::TYPE_COLLECTION:
                    $query->andWhere(['A.id' => $adminUser->id]);
                    break;
            }
        }else{
            if($adminUser->getIsSuperAdmin()){
                $isManager = 1;
            }
        }
        //查询条件
        $search = Yii::$app->request->get();
        if (isset($search['phone']) && $search['phone'] != '') {
            $search['phone'] = str_replace("'","", $search['phone']);
            $query->andWhere(['like','A.phone',trim($search['phone'])]);
        }
        if (isset($search['username']) && $search['username'] != '') {
            $search['username'] = str_replace("'","", $search['username']);
            $query->andWhere(['like','A.username',trim($search['username'])]);
        }
        if (isset($search['real_name']) && $search['real_name'] != '') {
            $query->andWhere(['like','A.real_name',trim($search['real_name'])]);
        }
        if (isset($search['outside']) && $search['outside'] != '') {
            $query->andWhere(['A.outside' => $search['outside']]);
        }
        if (isset($search['group']) && $search['group'] != '') {
            $query->andWhere(['A.group' => intval($search['group'])]);
        }
        if (isset($search['group_game']) && $search['group_game'] != '') {
            $query->andWhere(['A.group_game' => intval($search['group_game'])]);
        }
        if (isset($search['can_dispatch']) && $search['can_dispatch'] != '') {
            $query->andWhere(['A.can_dispatch' => intval($search['can_dispatch'])]);
        }
        if (isset($search['status']) && $search['status'] != '') {
            $query->andWhere(['A.open_status' => intval($search['status'])]);
        }
        if ($this->isNotMerchantAdmin) {
            if (isset($search['merchant_id']) && $search['merchant_id'] != '') {
                $query->andWhere(['A.merchant_id' => intval($search['merchant_id'])]);
            }else{
                $query->andWhere(['A.merchant_id' => 0]);
            }
        }

        $dateArr = [];
        $startTime = strtotime($startDate);
        $endTime = strtotime($endDate);

        $roles = AdminUserRole::getRolesByGroup([AdminUserRole::TYPE_COLLECTION]);
        $resArr = CollectorClassSchedule::find()
            ->alias('c')
            ->select(['c.date','c.admin_id','c.status','c.type','c.remark'])
            ->leftJoin(AdminUser::tableName().' u','c.admin_id = u.id')
            ->where(['c.status' => CollectorClassSchedule::STATUS_OPEN,'u.merchant_id' => $this->merchantIds,'u.role' => $roles])
            ->andWhere(['>=', 'c.date', $startDate])
            ->andWhere(['<=', 'c.date', $endDate])
            ->asArray()
            ->all();
        foreach ($resArr as $value){
            $dateArr[$value['date']][$value['admin_id']] = ['status' => $value['status'],'type' => $value['type'],'remark' => $value['remark']];
        }
        while ($startTime <= $endTime){
            $date = date('Y-m-d',$startTime);
            if(!isset($dateArr[$date])){
                $dateArr[$date] = [];
            }
            $startTime += 86400;
        }
        ksort($dateArr);

        $list = $query->orderBy(['A.id' => SORT_DESC])->asArray()->all();
        //var_dump($list);exit;
        if($this->request->get('submitcsv') == 'exportData'){
            $date = date('YmdHis');
            $this->_setcsvHeader("class_schedule{$date}.csv");
            $items = [];
            foreach($list as $value){
                $arr = [
                    'company' => $value['real_title'] ?? '--',
                    'group' => LoanCollectionOrder::$level[$value['group']] ?? '--',
                    'team' => (AdminUser::$group_games[$value['group_game']] ?? '--') . ($value['alias'] ? ':'.$value['alias'] :''),
                    'id' => $value['id'],
                    'username' => $value['username'],
                    'phone' => $value['phone'],
                    'real_name' => $value['real_name'],
                    'NX_name' => $value['nx_name_str'] ?? '-',
                    'NX_password' => $value['nx_password_str'] ?? '-',
                ];

                foreach ($dateArr as $date => $val) {
                    if(($value['open_status'] == 0 && date('Y-m-d',$value['updated_at']) <= $date) || date('Y-m-d',$value['created_at']) > $date) {
                        if (isset($val[$value['id']])) {
                            $arr[$date] = '×(' . (CollectorClassSchedule::$absence_type_map[$val[$value['id']]['type']] ?? '-') . ')';
                        }else{
                            $arr[$date] = '--';
                        }
                    } else {
                        if (isset($val[$value['id']])) {
                            $arr[$date] = '×(' . (CollectorClassSchedule::$absence_type_map[$val[$value['id']]['type']] ?? '-') . ')';
                        } else {
                            $arr[$date] = '√';
                        }
                    }
                }
                $items[] = $arr;
            }
            echo $this->_array2csv($items);
            exit;
        }
        // 获取所有商户，只能是商户管理员的时候才在页面上显示
        if ($this->isNotMerchantAdmin) {
            $merchantList = Merchant::getMerchantId();
        } else {
            $merchantList = [];
        }
        return $this->render('class-schedule',array(
            'list' => $list,
            'dateArr' => $dateArr,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'companyList' => UserCompany::outsideRealName($this->merchantIds),
            'merchantList' => $merchantList,
            'isManager' => $isManager
        ));
    }

    /**
     * @name 催收员班表获取信息
     * @param int $id
     * @param string $date
     */
    public function actionClassScheduleView($id,$date){
        Yii::$app->response->format = Response::FORMAT_JSON;
        /** @var AdminUser $adminUser */
        $roles =  AdminUserRole::getRolesByGroup([AdminUserRole::TYPE_COLLECTION]);
        $adminUser = null;
        if($roles){
            /** @var AdminUser $adminUser */
            $adminUser = AdminUser::find()->where([
                'id' => $id,
                'open_status' => AdminUser::$usable_status,
                'role' => $roles,
                'merchant_id' => $this->merchantIds
            ])->one();
        }
        if($adminUser){
            $type = 0;
            $remark = '';
            /** @var CollectorClassSchedule $collectorClassSchedule */
            $collectorClassSchedule = CollectorClassSchedule::find()->where(['date' => $date,'admin_id' => $id])->one();
            if($collectorClassSchedule && $collectorClassSchedule->status == CollectorClassSchedule::STATUS_OPEN){
                $type = $collectorClassSchedule->type;
                $remark = $collectorClassSchedule->remark;
            }
            return [
                'code' => 0,
                'message' => 'success',
                'data' => ['admin_id' => $adminUser->id,'username' => $adminUser->username,'date' => $date, 'type' => $type, 'remark' => $remark ,'is_today' => $date == date('Y-m-d')]
            ];
        }else{
            return ['code' => -1,'message' => 'fail'];
        }
    }

    /**
     * @name UserCollectionController 催收员班表更新
     * @param int $id
     * @param string $date
     * @param int $is_absence
     * @param string $type
     * @param string $remark
     * @return array
     */
    public function actionClassScheduleEdit($id,$date,$is_absence,$type,$remark){
        Yii::$app->response->format = Response::FORMAT_JSON;
        $data = ['is_absence' => $is_absence,'type' => $type,'remark' => $remark];
        /** @var CollectorClassSchedule $collectorClassSchedule */
        if($is_absence && !isset(CollectorClassSchedule::$absence_type_map[$type])){  //
            return ['code' => -1, 'message' => 'type error'];
        }
        if(date('Y-m-d') > $date){
            return ['code' => -1, 'message' => 'date error'];
        }

        $roles =  AdminUserRole::getRolesByGroup([AdminUserRole::TYPE_COLLECTION]);
        /** @var AdminUser $AdminUser */
        $AdminUser = null;
        if($roles){
            $AdminUser = AdminUser::find()->where([
                'id' => $id,
                'open_status' => AdminUser::$usable_status,
                'role' => $roles,
                'merchant_id' => $this->merchantIds
            ])->one();
        }
        if(!$AdminUser) {
            return ['code' => -1, 'message' => 'user error'];
        }

        //班表限制
        $todayDate = date('Y-m-d');
        $tip = false;
        $tipMessage = 'success';
        if($is_absence && $type == CollectorClassSchedule::WEEK_OFF_TYPE){
            $nextDate = date('Y-m-d',strtotime('+7 days'));
            $collectorClassSchedule = CollectorClassSchedule::find()
                ->where(['admin_id' => $id, 'type' => CollectorClassSchedule::WEEK_OFF_TYPE,'status' => CollectorClassSchedule::STATUS_OPEN])
                ->andWhere(['>', 'date', $todayDate])
                ->andWhere(['<=', 'date', $nextDate])
                ->andWhere(['!=', 'date', $date])
                ->exists();
            if($collectorClassSchedule){
                $tip = true;
                $tipMessage = 'Week off existed from tomorrow to next week';
            }else{
                $groupWoffCount = CollectorClassSchedule::find()
                    ->alias('c')
                    ->leftJoin(AdminUser::tableName().' u','c.admin_id = u.id')
                    ->where([
                        'c.date' => $date,
                        'c.type' => CollectorClassSchedule::WEEK_OFF_TYPE,
                        'c.status' => CollectorClassSchedule::STATUS_OPEN,
                        'u.outside' => $AdminUser->outside,
                        'u.group' => $AdminUser->group,
                        'u.group_game' => $AdminUser->group_game,
                        'u.role' => $roles,
                        'u.open_status' => AdminUser::$usable_status
                    ])
                    ->count();
                $groupCount = AdminUser::find()->where([
                    'outside' => $AdminUser->outside,
                    'group' => $AdminUser->group,
                    'group_game' => $AdminUser->group_game,
                    'role' => $roles,
                    'open_status' => AdminUser::$usable_status
                ])->count();

                if(empty($groupCount) || ($groupWoffCount / $groupCount) > (1/7)){
                    $tip = true;
                    $tipMessage = 'Too many people work off in the group';
                }
            }
        }
        /** @var AdminUser $currentUser */
        $currentUser = Yii::$app->user->identity;
        $roleGroup = AdminUserRole::getGroupByRoles($currentUser->role);
        if($roleGroup == AdminUserRole::TYPE_SUPER_MANAGER || $currentUser->getIsSuperAdmin()){

        }else{
            $time = time();
            $week = date('w',$time);
            $hour = date('H',$time);
            if($date > $todayDate && $week == 2 && $hour >= 13 &&  $hour <= 18){
                if($tip){
                    return ['code' => -1, 'message' => $tipMessage];
                }
            }else{
                return ['code' => -1, 'message' => 'can\'t edit today'];
            }
        }
        $collectorClassSchedule = CollectorClassSchedule::find()->where(['admin_id' => $id,'date' => $date])->one();
        if($collectorClassSchedule){
            if($is_absence){  //缺勤
                $collectorClassSchedule->type = $type;
                $collectorClassSchedule->remark = $remark;
                $collectorClassSchedule->operator_id = Yii::$app->user->getId();
                $collectorClassSchedule->status = CollectorClassSchedule::STATUS_OPEN;

                if(date('Y-m-d') == $date){
                    if(in_array($type,CollectorClassSchedule::$absence_type_back_today_list)){
                        $loanCollectionOrders = LoanCollectionOrder::find()->select(['id'])
                            ->where(['current_collection_admin_user_id' => $id,'status' => LoanCollectionOrder::$not_end_status])
                            ->andWhere(['>=','dispatch_time',strtotime('today')])->asArray()->all();
                        foreach ($loanCollectionOrders as $item) {
                            RedisQueue::push([RedisQueue::SCHEDULE_LOAN_COLLECTION_ORDER_BACK_LIST, json_encode(['collector_id' => $id,'collection_order_id' => $item['id']])]);
                        }
                    }elseif(in_array($type,CollectorClassSchedule::$absence_type_back_all_list)){
                        $loanCollectionOrders = LoanCollectionOrder::find()->select(['id'])
                            ->where(['current_collection_admin_user_id' => $id,'status' => LoanCollectionOrder::$not_end_status])
                            ->asArray()->all();
                        foreach ($loanCollectionOrders as $item) {
                            RedisQueue::push([RedisQueue::SCHEDULE_LOAN_COLLECTION_ORDER_BACK_LIST, json_encode(['collector_id' => $id,'collection_order_id' => $item['id']])]);
                        }
                    }
                }
            }else{
                $collectorClassSchedule->operator_id = Yii::$app->user->getId();
                $collectorClassSchedule->status = CollectorClassSchedule::STATUS_DEL;
            }
            $collectorClassSchedule->save();
        }else{
            if($is_absence) {  //缺勤
                if(date('Y-m-d') == $date){
                    if(in_array($type,CollectorClassSchedule::$absence_type_back_today_list)){
                        $loanCollectionOrders = LoanCollectionOrder::find()->select(['id'])
                            ->where(['current_collection_admin_user_id' => $id,'status' => LoanCollectionOrder::$not_end_status])
                            ->andWhere(['>=','dispatch_time',strtotime('today')])->asArray()->all();
                        foreach ($loanCollectionOrders as $item) {
                            RedisQueue::push([RedisQueue::SCHEDULE_LOAN_COLLECTION_ORDER_BACK_LIST, json_encode(['collector_id' => $id,'collection_order_id' => $item['id']])]);
                        }
                    }elseif(in_array($type,CollectorClassSchedule::$absence_type_back_all_list)){
                        $loanCollectionOrders = LoanCollectionOrder::find()->select(['id'])
                            ->where(['current_collection_admin_user_id' => $id,'status' => LoanCollectionOrder::$not_end_status])
                            ->asArray()->all();
                        foreach ($loanCollectionOrders as $item) {
                            RedisQueue::push([RedisQueue::SCHEDULE_LOAN_COLLECTION_ORDER_BACK_LIST, json_encode(['collector_id' => $id,'collection_order_id' => $item['id']])]);
                        }
                    }
                }
                $collectorClassSchedule = new CollectorClassSchedule();
                $collectorClassSchedule->date = $date;
                $collectorClassSchedule->admin_id = $id;
                $collectorClassSchedule->type = $type;
                $collectorClassSchedule->remark = $remark;
                $collectorClassSchedule->operator_id = Yii::$app->user->getId();
                $collectorClassSchedule->status = CollectorClassSchedule::STATUS_OPEN;
                $collectorClassSchedule->save();
            }
        }
        return ['code' => 0,'message' => $tipMessage,'data' => $data];

    }

    /**
     * @name 组长班表(daily work plan TL)
     */
    public function actionTeamLeaderClassSchedule(){
        $startDate = Yii::$app->request->get('start_date',date('Y-m-d'));
        $endDate = Yii::$app->request->get('end_date',date('Y-m-d',strtotime('+7 days')));
        /** @var AdminUser $adminUser */
        $adminUser = Yii::$app->user->identity;

        $query = AdminUser::find()
            ->select([
                'A.id',
                'A.username',
                'A.phone',
                'A.real_name',
                'A.group',
                'A.group_game',
                'A.merchant_id',
                'B.groups',
                'C.real_title',
                'D.alias',
                'A.open_status',
                'A.can_dispatch',
                'A.updated_at',
                'A.created_at'
            ])
            ->alias('A')
            ->leftJoin(AdminUserRole::tableName().' B','A.role = B.name')
            ->leftJoin(UserCompany::tableName().' C','A.outside = C.id')
            ->leftJoin(CompanyTeam::tableName(). ' D','A.outside = D.outside AND A.group_game = D.team')
            ->where([
                'B.groups' => AdminUserRole::$team_leader_groups,
                'A.merchant_id' => $this->merchantIds
            ])
            ->andWhere(['<','A.created_at',strtotime($endDate) + 86400])
            ->andWhere([
                'OR',
                [
                    //当前已离职
                    'AND',
                    ['A.open_status' => AdminUser::OPEN_STATUS_OFF],
                    ['>','A.updated_at',strtotime($startDate)]
                ],
                ['!=','A.open_status',AdminUser::OPEN_STATUS_OFF]

            ]);

        $roleGroup = AdminUserRole::getGroupByRoles($adminUser->role);
        $isManager = 0;
        if($roleGroup){
            switch ($roleGroup){
                case AdminUserRole::TYPE_SUPER_MANAGER:
                    $isManager = 1;
                    break;
                default:
                    $query->andWhere(['A.id' => 0]);
            }
        }else{
            if($adminUser->getIsSuperAdmin()){
                $isManager = 1;
            }
        }
        //查询条件
        $search = Yii::$app->request->get();
        if (isset($search['phone']) && $search['phone'] != '') {
            $search['phone'] = str_replace("'","", $search['phone']);
            $query->andWhere(['like','A.phone',trim($search['phone'])]);
        }
        if (isset($search['username']) && $search['username'] != '') {
            $search['username'] = str_replace("'","", $search['username']);
            $query->andWhere(['like','A.username',trim($search['username'])]);
        }
        if (isset($search['real_name']) && $search['real_name'] != '') {
            $query->andWhere(['like','A.real_name',trim($search['real_name'])]);
        }
        if (isset($search['outside']) && $search['outside'] != '') {
            $query->andWhere(['A.outside' => $search['outside']]);
        }
        if (isset($search['status']) && $search['status'] != '') {
            $query->andWhere(['A.open_status' => intval($search['status'])]);
        }
        if ($this->isNotMerchantAdmin) {
            if (isset($search['merchant_id']) && $search['merchant_id'] != '') {
                $query->andWhere(['A.merchant_id' => intval($search['merchant_id'])]);
            }else{
                $query->andWhere(['A.merchant_id' => 0]);
            }
        }

        $dateArr = [];
        $startTime = strtotime($startDate);
        $endTime = strtotime($endDate);

        $roles = AdminUserRole::getRolesByGroup(AdminUserRole::$team_leader_groups);
        $resArr = CollectorClassSchedule::find()
            ->alias('c')
            ->select(['c.date','c.admin_id','c.status','c.type','c.remark'])
            ->leftJoin(AdminUser::tableName().' u','c.admin_id = u.id')
            ->where(['c.status' => CollectorClassSchedule::STATUS_OPEN,'u.merchant_id' => $this->merchantIds,'u.role' => $roles])
            ->andWhere(['>=', 'c.date', $startDate])
            ->andWhere(['<=', 'c.date', $endDate])
            ->asArray()
            ->all();
        foreach ($resArr as $value){
            $dateArr[$value['date']][$value['admin_id']] = ['status' => $value['status'],'type' => $value['type'],'remark' => $value['remark']];
        }
        while ($startTime <= $endTime){
            $date = date('Y-m-d',$startTime);
            if(!isset($dateArr[$date])){
                $dateArr[$date] = [];
            }
            $startTime += 86400;
        }
        ksort($dateArr);

        $list = $query->orderBy(['A.id' => SORT_DESC])->asArray()->all();

        $bigTeamUserId = [];
        $superTeamUserId = [];
        foreach ($list as $val){
            if($val['groups'] == AdminUserRole::TYPE_BIG_TEAM_MANAGER){
                $bigTeamUserId[] = $val['id'];
            } elseif($val['groups'] == AdminUserRole::TYPE_SUPER_TEAM){
                $superTeamUserId[] = $val['id'];
            }
        }

        $bigTeamUserTeamArr = [];
        if($bigTeamUserId){
            $arr = AdminManagerRelation::find()
                ->alias('r')
                ->select(['r.admin_id','r.group','r.group_game','c.alias'])
                ->leftJoin(AdminUser::tableName().' u','u.id = r.admin_id')
                ->leftJoin(CompanyTeam::tableName().' c','u.outside = c.outside AND r.group_game = c.team')
                ->where(['r.admin_id' => $bigTeamUserId])
                ->asArray()
                ->all();
            foreach ($arr as $item){
                $str = Html::encode((LoanCollectionOrder::$current_level[$item['group']] ?? '-') . ':team'.$item['group_game'].' '.$item['alias']) .' <br/>';
                if(isset($bigTeamUserTeamArr[$item['admin_id']])){
                    $bigTeamUserTeamArr[$item['admin_id']] .= $str;
                }else{
                    $bigTeamUserTeamArr[$item['admin_id']] = $str;
                }
            }
        }
        $superTeamUserTeamArr = [];
        if($superTeamUserId){
            $arr = AdminManagerRelation::find()
                ->alias('r')
                ->select(['r.admin_id','uc.real_title','r.group','r.group_game','c.alias'])
                ->leftJoin(UserCompany::tableName().' uc','r.outside = uc.id')
                ->leftJoin(CompanyTeam::tableName().' c','r.outside = c.outside AND r.group_game = c.team')
                ->where(['r.admin_id' => $superTeamUserId])
                ->asArray()
                ->all();
            foreach ($arr as $item){
                $str = Html::encode($item['real_title'] .' '. (LoanCollectionOrder::$current_level[$item['group']] ?? '-') . ':team'.$item['group_game'].' '.$item['alias']) . '<br/>';
                if(isset($superTeamUserTeamArr[$item['admin_id']])){
                    $superTeamUserTeamArr[$item['admin_id']] .= $str;
                }else{
                    $superTeamUserTeamArr[$item['admin_id']] = $str;
                }
            }
        }

        foreach ($list as &$v){
            if($v['groups'] == AdminUserRole::TYPE_BIG_TEAM_MANAGER){
                $v['team_name'] = $bigTeamUserTeamArr[$v['id']] ?? '';
            }else if($v['groups'] == AdminUserRole::TYPE_SUPER_TEAM){
                $v['team_name'] = $superTeamUserTeamArr[$v['id']] ?? '';
            }else{
                $v['team_name'] = Html::encode((LoanCollectionOrder::$level[$v['group']] ?? '-').':team'.$v['group_game'].' '.$v['alias']);
            }
        }

        if($this->request->get('submitcsv') == 'exportData'){
            $date = date('YmdHis');
            $this->_setcsvHeader("class_schedule{$date}.csv");
            $items = [];
            foreach($list as $value){
                $arr = [
                    'company' => $value['real_title'] ?? '--',
                    'team_name' => $value['team_name'] ?? '--',
                    'id' => $value['id'],
                    'username' => $value['username'],
                    'phone' => $value['phone'],
                    'real_name' => $value['real_name'],
                ];

                foreach ($dateArr as $date => $val) {
                    if(($value['open_status'] == 0 && date('Y-m-d',$value['updated_at']) <= $date) || date('Y-m-d',$value['created_at']) > $date) {
                        $arr[$date] = '--';
                    } else {
                        if (isset($val[$value['id']])) {
                            $arr[$date] = '×(' . (CollectorClassSchedule::$absence_type_map[$val[$value['id']]['type']] ?? '-') . ')';
                        } else {
                            $arr[$date] = '√';
                        }
                    }
                }
                $items[] = $arr;
            }
            echo $this->_array2csv($items);
            exit;
        }
        // 获取所有商户，只能是商户管理员的时候才在页面上显示
        if ($this->isNotMerchantAdmin) {
            $merchantList = Merchant::getMerchantId();
        } else {
            $merchantList = [];
        }
        return $this->render('team-leader-class-schedule',array(
            'list' => $list,
            'dateArr' => $dateArr,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'companyList' => UserCompany::outsideRealName($this->merchantIds),
            'merchantList' => $merchantList,
            'isManager' => $isManager
        ));
    }

    /**
     * @name 组长班表获取信息
     * @param int $id
     * @param string $date
     */
    public function actionTeamClassScheduleView($id,$date){
        Yii::$app->response->format = Response::FORMAT_JSON;
        /** @var AdminUser $adminUser */
        $roles =  AdminUserRole::getRolesByGroup(AdminUserRole::$team_leader_groups);
        $adminUser = null;
        if($roles){
            /** @var AdminUser $adminUser */
            $adminUser = AdminUser::find()->where([
                'id' => $id,
                'open_status' => AdminUser::$usable_status,
                'role' => $roles,
                'merchant_id' => $this->merchantIds
            ])->one();
        }
        if($adminUser){
            $type = 0;
            $remark = '';
            /** @var CollectorClassSchedule $collectorClassSchedule */
            $collectorClassSchedule = CollectorClassSchedule::find()->where(['date' => $date,'admin_id' => $id])->one();
            if($collectorClassSchedule && $collectorClassSchedule->status == CollectorClassSchedule::STATUS_OPEN){
                $type = $collectorClassSchedule->type;
                $remark = $collectorClassSchedule->remark;
            }
            //该组长是否有副手
            /** @var AdminUserMasterSlaverRelation $adminUserMasterSlaverRelation */
            $adminUserMasterSlaverRelation = AdminUserMasterSlaverRelation::find()
                ->select(['u.username'])
                ->alias('r')
                ->leftJoin(AdminUser::tableName().' u','r.slave_admin_id = u.id')
                ->where(['r.admin_id' => $id])
                ->andWhere(['>','r.slave_admin_id',0])
                ->asArray()
                ->one();

            return [
                'code' => 0,
                'message' => 'success',
                'data' => ['admin_id' => $adminUser->id,'username' => $adminUser->username,'date' => $date, 'type' => $type,'adminUserMasterSlaverRelation' => $adminUserMasterSlaverRelation, 'remark' => $remark ,'is_today' => $date == date('Y-m-d')]
            ];
        }else{
            return ['code' => -1,'message' => 'fail'];
        }
    }

    /**
     * @name UserCollectionController 小组长申请缺勤
     * @return string
     */
    public function actionAbsenceApply()
    {
        $operator_id = Yii::$app->user->id;
        $absenceApplyModel = new AbsenceApply();
        if(Yii::$app->request->isPost)
        {
            $absenceApplyModel->load($this->request->post());
            if($absenceApplyModel->date < date('Y-m-d',time())){
                return $this->redirectMessage('Date fail!', self::MSG_ERROR);
            }
            $absenceApplyModel->team_leader_id = $operator_id;
            if($absenceApplyModel->validate() && $absenceApplyModel->save())
            {
                return $this->redirectMessage('success', self::MSG_SUCCESS, Url::toRoute('user-collection/absence-apply'));
            }else {
                return $this->redirectMessage('fail', self::MSG_ERROR);
            }
        }
        return $this->render('absence-apply', [
            'model' => $absenceApplyModel,
            'teamMember' => AdminUser::getTeamMember($operator_id),
        ]);
    }

    /**
     * @name UserCollectionController 缺勤初审列表
     * @return string
     */
    public function actionAuditApply()
    {
        $outside = Yii::$app->user->identity->outside;
        if($outside == 0){
            $outside = '';
        }
        $query = AbsenceApply::find()
            ->from(AbsenceApply::tableName().' A')
            ->select(['A.*','B.username','B.outside', 'B.group', 'B.group_game','B.phone'])
            ->leftJoin(AdminUser::tableName(). ' B','A.collector_id = B.id')
            ->where([ 'A.status' => AbsenceApply::STATUS_WAIT])
            ->andFilterWhere(['B.outside' => $outside])
            ->orderBy('A.id desc');

        $countQuery = clone $query;
        $pages = new Pagination(['totalCount' => $countQuery->count('A.id')]);
        $pages->pageSize = $this->request->get('page_size',15);
        $auditApply = $query->offset($pages->offset)->limit($pages->limit)->asArray()->all();

        $companyList = UserCompany::outsideRealName($this->merchantIds);
        return $this->render('audit-apply', [
            'auditApply' => $auditApply,
            'pages' => $pages,
            'url' => $this->request->url,
            'companyList' => $companyList,
        ]);
    }

    /**
     * @name UserCollectionController 缺勤终审列表
     * @return string
     */
    public function actionFinishAuditApply()
    {
        $query = AbsenceApply::find()
            ->from(AbsenceApply::tableName().' A')
            ->select(['A.*','B.username','B.outside', 'B.group', 'B.group_game','B.phone'])
            ->leftJoin(AdminUser::tableName(). ' B','A.collector_id = B.id')
            ->where(['A.status' => AbsenceApply::STATUS_YES, 'A.finish_status' => AbsenceApply::STATUS_WAIT])
            ->orderBy('A.id desc');

        $countQuery = clone $query;
        $pages = new Pagination(['totalCount' => $countQuery->count('A.id')]);
        $pages->pageSize = $this->request->get('page_size',15);
        $auditApply = $query->offset($pages->offset)->limit($pages->limit)->asArray()->all();

        $companyList = UserCompany::outsideRealName($this->merchantIds);
        return $this->render('finish-audit-apply', [
            'auditApply' => $auditApply,
            'pages' => $pages,
            'url' => $this->request->url,
            'companyList' => $companyList
        ]);
    }

    /**
     * @name UserCollectionController 机构管理员审批
     * @param $id
     * @param $status
     * @return string
     */
    public function actionAudit($id, $status){
        $absenceApplyModel = AbsenceApply::findOne(intval($id));
        if (!$absenceApplyModel) {
            return $this->redirectMessage('数据不存在', self::MSG_ERROR);
        }
        if(!in_array($status,[AbsenceApply::STATUS_YES,AbsenceApply::STATUS_NO])){
            return $this->redirectMessage('params error!', self::MSG_SUCCESS,  Url::toRoute(['audit-apply']));
        }
        $absenceApplyModel->status = $status;
        $absenceApplyModel->team_leader_id = $operator_id = Yii::$app->user->id;
        if($absenceApplyModel->save()){
            if(AbsenceApply::STATUS_YES == $status){
                $service = new WeWorkService();
                $message = '有缺勤终审需要处理,申请人id：'.$absenceApplyModel->collector_id;
                $service->sendText(['yanzhenlin','zhufangqi','xionghuakun','zhouchunlu','LiGuanHui'],$message);
            }
            return $this->redirectMessage('Success!', self::MSG_SUCCESS,  Url::toRoute(['audit-apply']));
        }else{
            return $this->redirectMessage('Error!', self::MSG_ERROR,  Url::toRoute(['audit-apply']));
        }
    }

    /**
     * @name UserCollectionController 最终审批
     * @return string
     */
    public function actionFinishAudit(){
        $id = $this->request->get('id');
        $type = $this->request->get('type',AbsenceApply::TYPE_YES);
        $user_id = $this->request->get('user_id','');
        $date = date('Y-m-d');

        $absenceApplyModel = AbsenceApply::findOne(intval($id));
        if (!$absenceApplyModel) {
            return $this->redirectMessage('数据不存在', self::MSG_ERROR);
        }
        $adminUser = AdminUser::findOne($absenceApplyModel->collector_id);

        if($date != $absenceApplyModel->date && in_array($type,[AbsenceApply::TYPE_PERSON,AbsenceApply::TYPE_TEAM,AbsenceApply::TYPE_ALL])){
            return $this->redirectMessage('仅当天的缺勤可抽派订单!', self::MSG_ERROR);
        }

        if(AbsenceApply::TYPE_PERSON == $type){
            if(!$user_id){
                return $this->redirectMessage('分派给个人需填写催收员id', self::MSG_ERROR);
            }
            //判断指定催收员与原催收员是否相同账龄
            $userAry = explode(',', $user_id);
            foreach($userAry as $user){
                $userInfo = AdminUser::findOne($user);
                if($adminUser->group != $userInfo->group){
                    return $this->redirectMessage($user.'与原始催收员不在相同账龄', self::MSG_ERROR);
                }
                $absent = CollectorClassSchedule::find()->where(['date'=>$date, 'admin_id'=>$user, 'status'=>CollectorClassSchedule::STATUS_OPEN])->exists();
                if($absent){
                    return $this->redirectMessage($user.'未出勤', self::MSG_ERROR);
                }
            }
        }elseif (AbsenceApply::TYPE_TEAM == $type){
            if($date == $absenceApplyModel->date){
                //查询所有组员
                $team = AdminUser::find()->where(['open_status'=>[AdminUser::OPEN_STATUS_LOCK,AdminUser::OPEN_STATUS_ON], 'outside' => $adminUser->outside, 'group'=> $adminUser->group, 'group_game'=>$adminUser->group_game, 'role' => 'collection'])->andWhere(['!=','id',$adminUser->id])->asArray()->all();
                $all_num = count($team);
                $absent_num = 0;
                foreach($team as $v){
                    $absent = CollectorClassSchedule::find()
                        ->where([
                            'date'=>$date, 'admin_id'=>$v['id'],
                            'status'=>CollectorClassSchedule::STATUS_OPEN,
                            'type'=> CollectorClassSchedule::$absence])
                        ->andWhere(['>=','created_at',strtotime($date)])
                        ->andWhere(['<=','created_at',strtotime($date) + 86400])
                        ->exists();
                    if($absent){
                        $absent_num ++;
                    }
                }
                if(sprintf("%.2f",$absent_num/$all_num) > 0.15){
                    return $this->redirectMessage('该小组出勤率不达标', self::MSG_ERROR);
                }
            }
        }
        $absenceApplyModel->finish_status = $type;
        $absenceApplyModel->to_person = $user_id;

        //拒绝不写入班表
        if(AbsenceApply::STATUS_NO != $type){
            //写入班表
            $collectorClassSchedule = CollectorClassSchedule::find()->where(['admin_id' => $absenceApplyModel->collector_id,'date' => $absenceApplyModel->date])->one();
            if($collectorClassSchedule){
                $collectorClassSchedule->type = $absenceApplyModel->type;
                $collectorClassSchedule->status = CollectorClassSchedule::STATUS_OPEN;
                $collectorClassSchedule->save();
            }else{
                $collectorClassSchedule = new CollectorClassSchedule();
                $collectorClassSchedule->date = $absenceApplyModel->date;
                $collectorClassSchedule->admin_id = $absenceApplyModel->collector_id;
                $collectorClassSchedule->type = $absenceApplyModel->type;
                $collectorClassSchedule->status = CollectorClassSchedule::STATUS_OPEN;
                $collectorClassSchedule->save();
            }
        }

        if($absenceApplyModel->save()){
            return $this->redirectMessage('Success!', self::MSG_SUCCESS,  Url::toRoute(['finish-audit-apply']));
        }else{
            return $this->redirectMessage('Error!', self::MSG_ERROR,  Url::toRoute(['finish-audit-apply']));
        }
    }

    /**
     * @name UserCollectionController 组长班表更新
     * @param int $id
     * @param string $date
     * @param int $is_absence
     * @param string $type
     * @param string $remark
     * @return array
     */
    public function actionTeamClassScheduleEdit($id,$date,$is_absence,$type,$remark){
        Yii::$app->response->format = Response::FORMAT_JSON;
        $data = ['is_absence' => $is_absence,'type' => $type,'remark' => $remark];
        /** @var CollectorClassSchedule $collectorClassSchedule */
        if($is_absence && !isset(CollectorClassSchedule::$absence_type_map[$type])){  //
            return ['code' => -1, 'message' => 'type error'];
        }
        if(date('Y-m-d') > $date){
            return ['code' => -1, 'message' => 'date error'];
        }

        $roles =  AdminUserRole::getRolesByGroup(AdminUserRole::$team_leader_groups);
        $AdminUser = null;
        if($roles){
            $AdminUser = AdminUser::find()->where([
                'id' => $id,
                'open_status' => AdminUser::$usable_status,
                'role' => $roles,
                'merchant_id' => $this->merchantIds
            ])->one();
        }
        if(!$AdminUser) {
            return ['code' => -1, 'message' => 'user error'];
        }
        /** @var AdminUser $currentUser */
        $currentUser = Yii::$app->user->identity;
        $roleGroup = AdminUserRole::getGroupByRoles($currentUser->role);
        if($roleGroup == AdminUserRole::TYPE_SUPER_MANAGER || $currentUser->getIsSuperAdmin()){

        }else{
            return ['code' => -1, 'message' => 'can\'t edit today'];
        }
        $collectorClassSchedule = CollectorClassSchedule::find()->where(['admin_id' => $id,'date' => $date])->one();
        if($collectorClassSchedule){
            if($is_absence){  //缺勤
                $collectorClassSchedule->type = $type;
                $collectorClassSchedule->remark = $remark;
                $collectorClassSchedule->operator_id = Yii::$app->user->getId();
                $collectorClassSchedule->status = CollectorClassSchedule::STATUS_OPEN;
            }else{
                $collectorClassSchedule->operator_id = Yii::$app->user->getId();
                $collectorClassSchedule->status = CollectorClassSchedule::STATUS_DEL;
            }
            $collectorClassSchedule->save();
        }else{
            if($is_absence) {  //缺勤
                $collectorClassSchedule = new CollectorClassSchedule();
                $collectorClassSchedule->date = $date;
                $collectorClassSchedule->admin_id = $id;
                $collectorClassSchedule->type = $type;
                $collectorClassSchedule->remark = $remark;
                $collectorClassSchedule->operator_id = Yii::$app->user->getId();
                $collectorClassSchedule->status = CollectorClassSchedule::STATUS_OPEN;
                $collectorClassSchedule->save();
            }
        }
        //当天
        if(date('Y-m-d') == $date){
            //该组长是否有副手
            /** @var AdminUserMasterSlaverRelation $adminUserMasterSlaverRelation */
            $adminUserMasterSlaverRelation = AdminUserMasterSlaverRelation::find()
                ->where(['admin_id' => $id])
                ->andWhere(['>','slave_admin_id',0])
                ->one();

            if($adminUserMasterSlaverRelation){
                $todayDate = date('Y-m-d');
                $tomorrowTime = strtotime('today') + 86400;
                //有副手
                //给予其权限标识
                $cacheKey = sprintf('%s:%s:%s', RedisQueue::TEAM_LEADER_SLAVER_CACHE, $todayDate, $adminUserMasterSlaverRelation->slave_admin_id);

                if($is_absence && isset(CollectorClassSchedule::$absence_type_today_after_map[$type])){
                    RedisQueue::set([
                        'expire' => $tomorrowTime - time(),
                        'key'    => $cacheKey,
                        'value'  => $adminUserMasterSlaverRelation->admin_id
                    ]);
                }else{
                    RedisQueue::del(["key" => $cacheKey]);
                }
            }
        }

        return ['code' => 0,'message' => 'success','data' => $data];

    }

    /**
     * @name UserCollectionController 我的信息列表（组长下面催收员情况消息）
     */
    public function actionMyMessageList(){
        $userId = Yii::$app->user->identity->getId();
        RedisQueue::delSet(RedisQueue::COLLECTION_NEW_MESSAGE_TEAM_TL_UID,$userId);
        $query = AdminMessage::find()->where(['admin_id' => $userId]);
        if(Yii::$app->request->get('search_submit') == 'search'){
            $search = Yii::$app->request->get();
            if(!empty($search['begintime'])){
                $query->andWhere(['>=','created_at',strtotime($search['begintime'])]);
            }
            if(!empty($search['endtime'])){
                $query->andWhere(['<=','created_at',strtotime($search['endtime'])]);
            }
            if(!empty($search['status']) && $search['status'] != ''){
                $query->andWhere(['status' => $search['status']]);
            }
        }
        $pages = new Pagination(['totalCount' => $query->cache(10)->count()]);
        $pages->pageSize = $this->request->get('page_size',15);


        $list = $query
            ->offset($pages->offset)
            ->limit($pages->limit)
            ->orderBy(['id' => SORT_DESC])
            ->asArray()
            ->all();

        return $this->render('my-message-list',array(
            'list' => $list,
            'pages' => $pages
        ));
    }

    /**
     * @name UserCollectionController 我的信息列表批量已读操作
     * @return string
     */
    public function actionMessageBatchRead()
    {
        $userId = Yii::$app->user->getId();
        $idsStr = $this->request->get('ids');
        $ids = explode(',',$idsStr);
        try{
            RedisQueue::delSet(RedisQueue::COLLECTION_NEW_MESSAGE_TEAM_TL_UID,$userId);
            if($ids){
                AdminMessage::updateAll(['status' => AdminMessage::STATUS_READ],['id' => $ids,'admin_id' => $userId,'status' => AdminMessage::STATUS_NEW]);
            }
        } catch (\Exception $e) {
            return $this->redirectMessage('error'.$e->getMessage(), self::MSG_ERROR);
        }
        return $this->redirectMessage('success！', self::MSG_SUCCESS);
    }
}

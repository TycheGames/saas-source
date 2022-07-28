<?php
namespace callcenter\controllers;

use backend\models\AdminUserCaptcha;
use callcenter\models\AdminMessage;
use callcenter\models\AdminNxUser;
use common\helpers\RedisQueue;
use common\services\ReCaptchaService;
use common\services\user\CaptchaService;
use callcenter\models\AdminCaptchaLog;
use callcenter\models\AdminLoginLog;
use Yii;
use yii\filters\AccessControl;
use yii\web\Response;
use callcenter\models\LoginForm;
use callcenter\models\AdminUserRole;
use callcenter\models\AdminUser;
use common\models\user\UserCaptcha;

/**
 * Main controller
 */
class MainController extends BaseController {

    public $verifyPermission = false;

    /**
     * @inheritdoc
     */
    public function behaviors(){
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['login', 'error', 'captcha', 'phone-captcha', 'new-message'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['index', 'logout', 'home', 'reset-role', 'language'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions() {
        return [
            'error' => [
                'class' => \yii\web\ErrorAction::class,
            ],
            'captcha' => [
                'class' => \yii\captcha\CaptchaAction::class,
                'testLimit' => 1,
                'height' => 35,
                'width' => 80,
                'padding' => 0,
                'minLength' => 4,
                'maxLength' => 4,
                'offset' => 1,
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * 登录
     */
    public function actionLogin() {
        // 已经登录则直接跳首页
        if (!\Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $this->layout = true;
        $model = new LoginForm();
        if ($model->load($this->request->post()) && $model->login()) {
            if ($model->getUser()->role && $roleModel = AdminUserRole::find()->where(['name' => explode(',',$model->getUser()->role)])->all()) {
                $arr = array();
                foreach ($roleModel as $val) {
                    if ($val->permissions) {
                        $arr = array_unique(array_merge($arr, json_decode($val->permissions)));
                    }
                }

                Yii::$app->getSession()->set('permissions', json_encode($arr));
            }

            $user = $model->getUser();
            //记录登录日志
            $admin_login_log = new AdminLoginLog();
            $admin_login_log->user_id = $user->id;
            $admin_login_log->ip =$this->request->getUserIP();
            $admin_login_log->username =$user->username;
            $admin_login_log->phone =$user->phone;
            $admin_login_log->save();

            UserCaptcha::deleteAll(['phone' => $model->getUser()->phone, 'type' => UserCaptcha::TYPE_ADMIN_LOGIN]);
            return $this->goHome();
        }


        if(isset($_SERVER['BSTYLE']) && $_SERVER['BSTYLE'] == '1'){
            $view = 'login-merchant';
        }else{
            $view = 'login';
        }

        $rService = new ReCaptchaService();
        //如果来源于中国则使用中国的谷歌地址，需要在cn-php-fpm.conf设置 fastcgi_param FROM_CN 1;
        if(isset($_SERVER['FROM_CN']) && $_SERVER['FROM_CN'] == '1'){
            $webUrl = $rService->getWebUrlCN();
        }else{
            $webUrl = $rService->getWebUrl();
        }

        return $this->render($view, [
            'model' => $model,
            'webUrl' => $webUrl,
            'webSecret' => $rService->getWebSecret()
        ]);
    }

    /**
     * 获取登录验证码
     */
    public function actionPhoneCaptcha(){
        $this->getResponse()->format = Response::FORMAT_JSON;

        if(yii::$app->request->isPost)
        {
            $username = trim($this->request->post('username'));
            if (!$username) {
                return ['code' => -1, 'message' => 'Username is incorrect'];
            }

            $s = new ReCaptchaService();
            $token = yii::$app->request->post('token');
            if(empty($token))
            {
                return ['code' => -1, 'message' => 'Man-machine verification failed, please refresh the page'];
            }
            $ip = yii::$app->request->getUserIP();
            if(!$s->verify($token, $ip))
            {
                return ['code' => -1, 'message' => 'Man-machine verification failed, please refresh the page'];
            }

            $user = AdminUser::findByUsername($username);

            if (!$user) {
                return ['code' => -1, 'message' => 'Username is incorrect'];
            }

            if(AdminUser::OPEN_STATUS_LOCK == $user->open_status)
            {
                return ['code' => -1, 'message' => 'Account has been locked. Please contact admin'];
            }

            $userService = new CaptchaService();
           if ($userService->generateAndSendCaptchaAdmin(trim($user->phone), AdminUserCaptcha::TYPE_ADMIN_CS_LOGIN)) {
                //记录发送验证码信息
                try{
                    $admin_captcha_log = new AdminCaptchaLog();
                    $admin_captcha_log->user_id = 0;
                    $admin_captcha_log->username =$username;
                    $admin_captcha_log->phone =trim($user->phone);
                    $admin_captcha_log->ip =$this->request->getUserIP();
                    $admin_captcha_log->type =AdminUserCaptcha::TYPE_ADMIN_CS_LOGIN;
                    $admin_captcha_log->save();
                }
                catch (\Exception $e){
                }

                return ['code' => 0];
            }
            else {
                return ['code' => -1, 'message' => 'OTP failed'];
            }

        }


    }

    /**
     * 外层框架首页
     */
    public function actionIndex() {
        $this->layout = false;
        include_once __DIR__ . '/../config/menu.php';
        $role = Yii::$app->user->identity->role;
        if (!Yii::$app->user->identity->getIsSuperAdmin()) {
//            $permissions = Yii::$app->getSession()->get('permissions');
//            if ($permissions) {
//                $permissions = json_decode($permissions, true);
//            } else {
//                $role = Yii::$app->user->identity->role;
//                if($role){
//                    $roleModel = AdminUserRole::find()->andWhere("name in('".implode("','",explode(',',$role))."')")->all();
//                    if($roleModel){
//                        $arr = array();
//                        foreach ($roleModel as $val) {
//                            if($val->permissions)
//                                $arr = array_unique(array_merge($arr,json_decode($val->permissions)));
//                        }
//                        Yii::$app->getSession()->set('permissions', json_encode($arr));
//                        $permissions = json_decode($permissions, true);
//                        $permissions = $arr;
//                    }
//                }
//            }
            $permissions = [];
            if($role){
                $roleModel = AdminUserRole::find()
                    ->where(['name' => explode(',',$role)])
                    ->all();
                if($roleModel){
                    $arr = array();
                    foreach ($roleModel as $val) {
                        if($val->permissions)
                            $arr = array_unique(array_merge($arr,json_decode($val->permissions)));
                    }
                    $permissions = $arr;
                }
            }

            foreach ($topmenu as $key => $value){
                $flag = false;
                $isStart = 0;
                $beginKey = '';
                foreach ($menu[$key] as $k => $val){
                    if($val[1] == 'groupbegin'){
                        $isStart = 1;
                        $beginKey = $k;
                        continue;
                    }
                    if($val[1] == 'groupend'){
                        if($isStart == 1){
                            unset($menu[$key][$beginKey]);
                            unset($menu[$key][$k]);
                        }
                        $isStart = 0;
                        continue;
                    }
                    $url = urldecode(str_replace('/index.php?r=','',$val[1]));
                    $smallFlag = false;
                    foreach ($permissions as $permission){
                        if($url == $permission){
                            $flag = true;
                            $smallFlag = true;
                            $isStart = 0;
                            break;
                        }
                    }
                    if($smallFlag == false){
                        unset($menu[$key][$k]);
                    }
                }

                if($flag == false){
                    unset($topmenu[$key]);
                }
            }
        }

        $merchant_id = Yii::$app->user->identity->merchant_id;
        if($merchant_id > 0){
            unset($topmenu['system']);
        }
        if(!$this->strategyOperating)
        {
            unset($menu['system']['menu_adminuser_nx_list']);
        }
        $user_id = Yii::$app->user->getId();
        $nx_info = AdminNxUser::find()->where(['collector_id' => $user_id,'status' => AdminNxUser::STATUS_ENABLE, 'type' => AdminNxUser::TYPE_PC])->asArray()->one();

        $showMessage = [];
        if(isset($_SERVER['BSTYLE']) && $_SERVER['BSTYLE'] == '1'){
            $view = 'index-merchant';
        }else{
            $groups = AdminUserRole::getGroupByRoles($role);
            if(in_array($groups,[AdminUserRole::TYPE_SMALL_TEAM_MANAGER,AdminUserRole::TYPE_BIG_TEAM_MANAGER])){
                $newNum = AdminMessage::find()->where(['admin_id' => $user_id,'status' => AdminMessage::STATUS_NEW])->count();
                $showMessage = ['num' => $newNum];
            }
            $view = 'index';
        }
        return $this->render($view, array(
           'topmenu'    => $topmenu,
           'menu'        => $menu,
           'showMessage' => $showMessage,
           'nx_admin' => isset($nx_info['nx_name']) ? $nx_info['nx_name'] : ''
        ));
    }

/**
* iframe里面首页
*/
    public function actionHome() {
//        if($this->is_voip){  //如果支持网络电话就签入
//            $service = new MiHuaService($this->sub_detail);
//            $service->signIn($this->work_name);//签入
//        }
//        return $this->redirect(['collection/my-work']);

        //当前人员只是催收组人员，首页显示当前任务
//        $userGroups = AdminUserRole::groups_of_roles(Yii::$app->user->identity->role);
//        if (count($userGroups) == 1 && $userGroups[0] == AdminUserRole::TYPE_COLLECTION){
//            return $this->redirect(['collection/my-work']);
//        }

        return $this->render('home');

    }


    /**
     * 退出
     */
    public function actionLogout(){
        Yii::$app->user->logout();
        return $this->redirect(['login']);
    }

    /**
     * 刷新权限
     */
    public function actionResetRole()
    {
        // 把权限信息存到session中
        if (Yii::$app->user->identity->role && $roleModel = AdminUserRole::find()->andWhere("name in('" . implode("','", explode(',', Yii::$app->user->identity->role)) . "')")->all()) {
            $arr = array();
            foreach ($roleModel as $val) {
                if ($val->permissions)
                    $arr = array_unique(array_merge($arr, json_decode($val->permissions)));
            }
            $permissionsKey = 'permissions';
            if(Yii::$app->user->identity->master_user_id){
                //副手权限
                $permissionsKey = 'permissions_'.Yii::$app->user->identity->master_user_id;
            }
            Yii::$app->getSession()->set($permissionsKey, json_encode($arr));
        }

        return $this->redirect(['main/index']);
    }

    /**
     * 语言切换
     */
    public function actionLanguage()
    {
        $language = \Yii::$app->request->cookies->getValue('language');
        var_dump($language);
        $language = $language == 'zh-CN' ? 'en-US' : 'zh-CN';
        Yii::$app->response->cookies->add(new yii\web\Cookie([
            'name' => 'language',
            'value' => $language,
        ]));

        return $this->redirect(['main/index']);
    }

    /**
     * 是否有新消息
     */
    public function actionNewMessage()
    {
        $this->getResponse()->format = Response::FORMAT_JSON;
        $newNum = 0;
        $code = -2;
        $haveNewMessage = false;
        if (!Yii::$app->user->isGuest && $role = Yii::$app->user->identity->role) {
            $groups = AdminUserRole::getGroupByRoles($role);
            if(in_array($groups,[AdminUserRole::TYPE_SMALL_TEAM_MANAGER,AdminUserRole::TYPE_BIG_TEAM_MANAGER])){
                $user_id = Yii::$app->user->identity->getId();
                $newNum = AdminMessage::find()->where(['admin_id' => $user_id,'status' => AdminMessage::STATUS_NEW])->count();
                if(RedisQueue::existSet(RedisQueue::COLLECTION_NEW_MESSAGE_TEAM_TL_UID,$user_id)){
                    $haveNewMessage = true;
                }
                $code = 0;
            }
        }

        return ['code' => $code ,'num' => $newNum,'have_new' => $haveNewMessage];
    }
}
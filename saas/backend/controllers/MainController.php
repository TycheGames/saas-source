<?php
namespace backend\controllers;

use backend\models\AdminCaptchaLog;
use backend\models\AdminUserCaptcha;
use common\helpers\RedisQueue;
use common\models\user\UserCaptcha;
use common\services\ReCaptchaService;
use common\services\SlideService;
use common\services\user\CaptchaService;
use Yii;
use yii\filters\AccessControl;
use yii\web\Response;

use backend\models\LoginForm;
use backend\models\AdminUserRole;
use backend\models\AdminUser;
use backend\models\AdminLoginLog;
/**
 * Main controller
 */
class MainController extends BaseController {

    public $verifyPermission = false;
    public $source_ids;
    public function init()
    {
        parent::init(); // TODO: Change the autogenerated stub
    }
    /**
     * @inheritdoc
     */
    public function behaviors() {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['login', 'error', 'captcha', 'phone-captcha'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['index', 'logout', 'home', 'get-list', 'reset-role', 'menu-collection', 'language'],
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


    public function actionSelectLanguage()
    {
        Yii::$app->response->cookies->add(new yii\web\Cookie([
            'name' => 'language',
            'value' => 'zh-CN',
            'expire'=> time() + 86400 * 30
        ]));
    }



    /**
     *  @name MainController 登录
     * @return string|Response
     */
    public function actionLogin() {
        // 已经登录则直接跳首页
        if (!\Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $this->layout = true;
        $model = new LoginForm();
        if ($model->load($this->request->post()) && $model->login()) {
            // 把权限信息存到session中
            if ($model->getUser()->role && $roleModel = AdminUserRole::find()->where(['name' => explode(',',$model->getUser()->role)])->all()) {
                $arr = array();
                foreach ($roleModel as $val) {
                    /**
                     * @var AdminUserRole $val
                     */
                    if ($val->permissions)
                        $arr = array_unique(array_merge($arr, json_decode($val->permissions)));
                }
                Yii::$app->getSession()->set('permissions', json_encode($arr));
            }

            $user = $model->getUser();
            //记录登录日志
            $admin_login_log = new AdminLoginLog();
            $admin_login_log->user_id = $user->id;
            $admin_login_log->ip = $this->request->getUserIP();
            $admin_login_log->username = $user->username;
            $admin_login_log->phone = $user->phone;
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
     * @name MainController 获取登录验证码
     * @return array
     * @throws \Exception
     */
    public function actionPhoneCaptcha()
    {
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

            $service = new CaptchaService();
            if ($service->generateAndSendCaptchaAdmin(trim($user->phone), AdminUserCaptcha::TYPE_ADMIN_LOGIN)) {
                //记录发送验证码信息
                try {
                    $admin_captcha_log = new AdminCaptchaLog();
                    $admin_captcha_log->user_id = 0;
                    $admin_captcha_log->username = $username;
                    $admin_captcha_log->phone = trim($user->phone);
                    $admin_captcha_log->ip = $this->request->getUserIP();
                    $admin_captcha_log->type = UserCaptcha::TYPE_ADMIN_LOGIN;
                    $admin_captcha_log->save();
                } catch (\Exception $e) {

                }
                return ['code' => 0];
            } else {
                return ['code' => -1, 'message' => 'OTP failed'];
            }
        }

    }

    /**
     * 外层框架首页
     */
    public function actionIndex()
    {
        if($this->isNotMerchantAdmin)
        {
            include_once __DIR__ . '/../config/menu.php';
        }else{
            include_once __DIR__ . '/../config/menu_merchant.php';
        }
        //指定人可见菜单
        if(!$this->strategyOperating)
        {
            unset($menu['system']['menu_adminuser_nx_list']);
            unset($menu['system']['menu_nx_phone_config']);
            unset($menu['system']['menu_nx_phone_sdk_config']);
            unset($menu['customer']['menu_nx_phone_data']);
            unset($menu['creditAudit']['menu_credit_nx_phone_data']);
            unset($menu['developmentTools']['menu_development_get_user_otp']);
        }
        $this->layout = false;

        if(isset($_SERVER['BSTYLE']) && $_SERVER['BSTYLE'] == '1'){
            $view = 'index-merchant';
        }else{
            $view = 'index';
        }
        return $this->render($view, array(
            'topmenu' => $topmenu,
            'menu' => $menu,
        ));
    }

    /**
     * @name  首页-管理中心首页
     * @return string
     */
    public function actionHome()
    {
        $redisList = [
            //['key' => RedisQueue::LIST_CREDIT_USER_DETAIL_RECORD, 'name' => '#计算额度  @347', 'actionName' => 'credit-line/set-credit-line'],
        ];
        $redisIncr = [
            //['key' => sprintf('credit:order_count:%s', date('ymd')), 'name' => '当日普通用户放款量', 'actionName' => ''],
        ];
        foreach ($redisList as $key => $val) {

            if(in_array($key, [
//                RedisQueue::CHANEL_BAIKA_UPLOAD_DATA_LIST_A,
//                RedisQueue::CHANEL_BAIKA_UPLOAD_DATA_LIST_B,
//                RedisQueue::CHANEL_BAIKA_UPLOAD_DATA_LIST_C
            ]))
            {
                $redisList[$key]['length'] = RedisQueue::getLength([$val['key']], 'risk-chanel');
            }
            else
            {
                $redisList[$key]['length'] = RedisQueue::getLength([$val['key']]);
            }
        }
        foreach ($redisIncr as $k => $v) {
            $redisIncr[$k]['length'] = RedisQueue::get(['key' => $v['key']]);
        }

        return $this->render('home', [
            'redisList' => $redisList,
            'redisIncr' => $redisIncr,
        ]);
    }



    /**
     * 退出
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();
        return $this->redirect(['login']);
    }

    /**
     * 刷新权限
     */
    public function actionResetRole()
    {
        // 把权限信息存到session中
        if (Yii::$app->user->identity->role && $roleModel = AdminUserRole::find()->where(['name' => explode(',', Yii::$app->user->identity->role)])->all()) {
            $arr = array();
            foreach ($roleModel as $val) {
                /**
                 * @var AdminUserRole $val
                 */
                if ($val->permissions)
                    $arr = array_unique(array_merge($arr, json_decode($val->permissions)));
            }
            Yii::$app->getSession()->set('permissions', json_encode($arr));
        }

        return $this->redirect(['main/index']);
    }

    /**
     * 语言切换
     */
    public function actionLanguage()
    {
        $language = \Yii::$app->request->cookies->getValue('language');
        $language = $language == 'zh-CN' ? 'en-US' : 'zh-CN';
            Yii::$app->response->cookies->add(new yii\web\Cookie([
                'name' => 'language',
                'value' => $language,
            ]));

        return $this->redirect(['main/index']);
    }

}

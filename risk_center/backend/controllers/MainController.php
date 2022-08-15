<?php
namespace backend\controllers;

use backend\models\AdminCaptchaLog;
use backend\models\AdminUserCaptcha;
use common\helpers\RedisQueue;
use common\models\user\UserCaptcha;
use common\services\ReCaptchaService;
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
                        'actions' => ['login', 'error', 'captcha', 'phone-captcha', 'test'],
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


   public function actionTest()
   {
       echo Yii::t('common', 'hello');
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
            if ($model->getUser()->role && $roleModel = AdminUserRole::find()->where(['name' => explode(',', $model->getUser()->role)])->all()) {
                $arr = array();
                foreach ($roleModel as $val) {
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

            return $this->goHome();
        }

        $rService = new ReCaptchaService();
        $isCN = strtolower(yii::$app->request->get('a')) == 'cn';

        return $this->render('login', [
            'model' => $model,
            'webUrl' => $isCN ? $rService->getWebUrlCN() : $rService->getWebUrl(),
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

        $username = trim($this->request->post('username'));
        if (!$username) {
            return ['code' => -1, 'message' => 'Username is incorrect'];
        }

        $token = yii::$app->request->post('token');
        if(empty($token))
        {
            return ['code' => -1, 'message' => 'Man-machine verification failed, please refresh the page'];
        }
        $s = new ReCaptchaService();
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
        if (!$user->phone) {
            return ['code' => -1, 'message' => 'Username is incorrect'];
        } else if ($service->generateAndSendCaptchaAdmin(trim($user->phone), AdminUserCaptcha::TYPE_ADMIN_LOGIN)) {
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
                var_dump($e);
                exit;
            }
            return ['code' => 0];
        } else {
            return ['code' => -1, 'message' => 'OTP failed'];
        }
    }

    /**
     * 外层框架首页
     */
    public function actionIndex()
    {
        include_once __DIR__ . '/../config/menu.php';
        $this->layout = false;

        return $this->render('index', array(
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
        if (Yii::$app->user->identity->role && $roleModel = AdminUserRole::find()->andWhere("name in('" . implode("','", explode(',', Yii::$app->user->identity->role)) . "')")->all()) {
            $arr = array();
            foreach ($roleModel as $val) {
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

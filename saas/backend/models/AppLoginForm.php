<?php
namespace backend\models;

use common\helpers\RedisQueue;
use common\services\user\CaptchaService;
use Yii;
use yii\base\Model;

/**
 * Login form
 */
class AppLoginForm extends Model {
    public $username;
    public $password;
    public $otp;

    private $_user = false;

    /**
     * @inheritdoc
     */
    public function rules() {
        if (!YII_ENV_PROD) {
            return [
                [['username', 'password'], 'required'],
                [['username', 'password', 'otp'], 'trim'],
                ['password', 'validatePassword'],
                ['otp', 'safe'],
                ['username', 'validateUser']
            ];
        }
        return [
            [['username', 'password', 'otp'], 'required'],
            [['username', 'password', 'otp'], 'trim'],
            ['otp', 'validatePhoneCaptcha'],
            ['password', 'validatePassword'],
            ['username', 'validateUser']

        ];
    }

    public function attributeLabels() {
        return [
            'username' => 'User name',
            'password' => 'Password',
            'otp' => 'OTP',
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute, $params)
    {
        if (empty($this->errors)) {
            $user = $this->getUser();
            if(is_null($user))
            {
                return false;
            }
            if(AdminUser::OPEN_STATUS_LOCK == $user->open_status)
            {
                return false;
            }
            if (!$user->validatePassword($this->password)) {
                $message = AdminUser::errorAndLock($user);
                if($message)
                {
                    $this->addError($attribute, $message);
                }else{
                    $this->addError($attribute, 'password incorrect!');
                }
                AdminLoginErrorLog::createLog($this->username, $this->otp, $this->password, Yii::$app->request->getUserIP(), AdminLoginErrorLog::TYPE_PASSWORD, AdminLoginErrorLog::SYSTEM_APP);

            }
        }
    }

    /**
     * Validates the phoneCaptcha.
     */
    public function validatePhoneCaptcha($attribute, $params) {
        if (empty($this->errors)) {
            $user = $this->getUser();
            if(is_null($user))
            {
                return false;
            }
            if(AdminUser::OPEN_STATUS_LOCK == $user->open_status)
            {
                return false;
            }
            $service = new CaptchaService();
            if (!$service->validatePhoneCaptchaAdmin($user->phone, $this->otp, CaptchaService::TYPE_ADMIN_LOGIN)) {
                $message = AdminUser::errorAndLock($user);
                if($message)
                {
                    $this->addError($attribute, $message);
                }else{
                    $this->addError($attribute, 'otp code incorrect!');
                }
                AdminLoginErrorLog::createLog($this->username, $this->otp, $this->password, Yii::$app->request->getUserIP(), AdminLoginErrorLog::TYPE_OTP, AdminLoginErrorLog::SYSTEM_APP);

            }
        }
    }

    public function validateUser($attribute, $params) {
        if (empty($this->errors)) {
            $user = $this->getUser();
            if(is_null($user))
            {
                $this->addError($attribute, 'username incorrect');
                return true;
            }
            if(AdminUser::OPEN_STATUS_LOCK == $user->open_status)
            {
                $this->addError($attribute, 'Account has been locked. Please contact admin');
                return true;
            }
        }
    }


    /**
     * Logs in a user using the provided username and password.
     *
     * @return boolean whether the user is logged in successfully
     */
    public function login() {
        if ($this->validate()) {
            //此时不能使用Yii::$app->user->id
            $lockKey = sprintf('%s:%s:%s', RedisQueue::USER_LOGIN_MOBILE_LOCK , 'backend', $this->getUser()->id);
            $listKey = sprintf('%s:%s:%s', RedisQueue::LIST_USER_MOBILE_SESSION , 'backend', $this->getUser()->id);
            if (!RedisQueue::lock($lockKey, 60)) {
                return false;
            }
            //获取当前Session对象的配置，用于计算销毁对象的key
            $currentSession = Yii::$app->getSession();
            while ($oldSessionID = RedisQueue::pop([$listKey])) {
                $currentSession->destroySession($oldSessionID);
            }
            $result = Yii::$app->user->login($this->getUser());
            //获取最新Session对象（登录时的session与登录后的session是不一致的）
            $currentSession = Yii::$app->getSession();
            RedisQueue::push([$listKey, $currentSession->id]);
            RedisQueue::unlock($lockKey);
            return $result;
        }
        yii::warning("username:{$this->username} login failed, password is {$this->password}, phoneCaptcha is {$this->otp}", 'backend_app_login');
        return false;
    }


    /**
     * @return bool|AdminUser|null
     */
    public function getUser() {
        if ($this->_user === false) {
            $this->_user = AdminUser::findByUsername($this->username);
        }
        return $this->_user;
    }

}

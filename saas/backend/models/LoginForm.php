<?php
namespace backend\models;

use common\helpers\RedisQueue;
use common\services\ReCaptchaService;
use common\services\user\CaptchaService;
use Yii;
use yii\base\Model;

/**
 * Login form
 */
class LoginForm extends Model
{
    public $username;
    public $password;
    public $phoneCaptcha;
    public $token;

    private $_user = false;

    /**
     * @inheritdoc
     */
    public function rules() {
        if(YII_ENV_DEV)
        {
            return [
                [['username',], 'required'],
                ['username', 'validateUser'],
            ];
        }else{
            return [
                [['username', 'phoneCaptcha', 'token'], 'required'],
                ['phoneCaptcha', 'validatePhoneCaptcha'],
                ['username', 'validateUser'],
                ['token', 'validateToken'],
            ];
        }
    }


    public function validateToken($attribute, $params)
    {
        if (empty($this->errors)) {
            $s = new ReCaptchaService();
            if(!$s->verify($this->token, yii::$app->request->getUserIP()))
            {
                $this->addError($attribute, 'Man-machine verification failed, please refresh the page');
            }
        }
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
            if(empty($user))
            {
                return false;
            }

            if(AdminUser::OPEN_STATUS_LOCK == $user->open_status)
            {
                return false;
            }
            if(!$user->validatePassword($this->password))
            {
                AdminLoginErrorLog::createLog($this->username, $this->phoneCaptcha, $this->password, Yii::$app->request->getUserIP(), AdminLoginErrorLog::TYPE_PASSWORD, AdminLoginErrorLog::SYSTEM_PC);
                $message = AdminUser::errorAndLock($user);
                if($message)
                {
                    $this->addError($attribute, $message);
                }else{
                    $this->addError($attribute, 'password incorrect!');
                }
            }

        }
    }

    /**
     * Validates the phoneCaptcha.
     */
    public function validatePhoneCaptcha($attribute, $params) {
        if (empty($this->errors)) {
            $user = $this->getUser();
            if(empty($user))
            {
                return false;
            }

            if(AdminUser::OPEN_STATUS_LOCK == $user->open_status)
            {
                return false;
            }
            $service = new CaptchaService();
            if(!$service->validatePhoneCaptchaAdmin($user->phone, $this->phoneCaptcha, CaptchaService::TYPE_ADMIN_LOGIN))
            {
                AdminLoginErrorLog::createLog($this->username, $this->phoneCaptcha, $this->password, Yii::$app->request->getUserIP(), AdminLoginErrorLog::TYPE_OTP, AdminLoginErrorLog::SYSTEM_PC);
                $message = AdminUser::errorAndLock($user);
                if($message)
                {
                    $this->addError($attribute, $message);
                }else{
                    $this->addError($attribute, 'otp code incorrect!');
                }
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
    public function login()
    {
        if ($this->validate()) {
            //此时不能使用Yii::$app->user->id
            $lockKey = sprintf('%s:%s:%s', RedisQueue::USER_LOGIN_PC_LOCK , 'backend', $this->getUser()->id);
            $listKey = sprintf('%s:%s:%s', RedisQueue::LIST_USER_PC_SESSION , 'backend', $this->getUser()->id);
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
        } else {
            yii::warning("username:{$this->username} login failed, password is {$this->password}, phoneCaptcha is {$this->phoneCaptcha}", 'backend_login');
            return false;
        }
    }

    /**
     * Finds user by [[username]]
     *
     * @return AdminUser|null
     */
    public function getUser()
    {
        if ($this->_user === false) {
            $this->_user = AdminUser::findByUsername($this->username);
        }

        return $this->_user;
    }
}

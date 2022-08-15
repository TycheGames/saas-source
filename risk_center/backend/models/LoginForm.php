<?php
namespace backend\models;

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
    public $verifyCode;
    public $phoneCaptcha;
    public $token;

    private $_user = false;

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['username', 'phoneCaptcha', 'token'], 'required'],
            ['phoneCaptcha', 'validatePhoneCaptcha'],
            ['username', 'validateUser'],
            ['token', 'validateToken'],
        ];
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
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, '用户名或密码错误');
            }
        }
    }

    /**
     * Validates the phoneCaptcha.
     */
    public function validatePhoneCaptcha($attribute, $params) {
        if(empty($this->errors)) {
            $user    = $this->getUser();
            if(empty($user)){
                return false;
            }
            $service = new CaptchaService();
            if (!$service->validatePhoneCaptchaAdmin($user->phone, $this->phoneCaptcha, CaptchaService::TYPE_ADMIN_LOGIN)) {
                $message = AdminUser::errorAndLock($user);
                if($message){
                    $this->addError($attribute, $message);
                }else {
                    $this->addError($attribute, '验证码错误');
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
            return Yii::$app->user->login($this->getUser());
        } else {
            yii::warning("username:{$this->username} login failed, password is {$this->password}, phoneCaptcha is {$this->phoneCaptcha}, verifyCode is {$this->verifyCode}", 'backend_login');
            return false;
        }
    }

    /**
     * Finds user by [[username]]
     *
     * @return User|null
     */
    public function getUser()
    {
        if ($this->_user === false) {
            $this->_user = AdminUser::findByUsername($this->username);
        }

        return $this->_user;
    }
}

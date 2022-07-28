<?php

namespace common\services\user;

use backend\models\AdminUserCaptcha;
use common\helpers\MessageHelper;
use common\helpers\RedisQueue;
use common\models\user\LoanPerson;
use common\models\user\UserCaptcha;
use common\services\message\SendMessageService;
use common\services\package\PackageService;
use yii\base\BaseObject;


class CaptchaService extends BaseObject
{

    const TYPE_USER_REGISTER = 1; //用户注册
    const  TYPE_APPLY_ORDER = 2; //用户下单
    const  TYPE_USER_RESET_PASS = 3; //用户重置密码
    const  TYPE_USER_LOGIN = 4; //用户登陆验证码

    const TYPE_ADMIN_LOGIN = 101; //管理后台登录验证码
    const TYPE_ADMIN_CS_LOGIN = 102; //催收后台登录验证码

    private static $type_map = [
        self::TYPE_USER_REGISTER => UserCaptcha::TYPE_REGISTER,
        self::TYPE_APPLY_ORDER => UserCaptcha::TYPE_APPLY_ORDER,
        self::TYPE_USER_RESET_PASS => UserCaptcha::TYPE_RESET_PASS,
        self::TYPE_USER_LOGIN => UserCaptcha::TYPE_USER_LOGIN,
        self::TYPE_ADMIN_LOGIN => UserCaptcha::TYPE_ADMIN_LOGIN,
        self::TYPE_ADMIN_CS_LOGIN => UserCaptcha::TYPE_ADMIN_CS_LOGIN,
    ];


    /**
     * 验证手机验证码
     * @param $phone
     * @param $code
     * @param $sourceId
     * @param $type
     * @return bool
     */
    public function validatePhoneCaptcha($phone, $code, $sourceId, $type)
    {
        //非正式环境 888888无需验证
        if(!YII_ENV_PROD && '888888' == $code){
            return true;
        }
        $result = UserCaptcha::findOne([
            'phone' => $phone,
            'captcha' => trim($code),
            'type' => self::$type_map[$type],
            'source_id' => $sourceId
        ]);
        if ($result) {
            UserCaptcha::deleteAll(['id' => $result->id]);
            return time() <= $result->expire_time;
        } else {
            return false;
        }
    }


    /**
     * 管理后台验证手机验证码
     * @param $phone
     * @param $code
     * @param $type
     * @return bool
     */
    public function validatePhoneCaptchaAdmin($phone, $code, $type)
    {
        $result = AdminUserCaptcha::findOne([
            'phone' => $phone,
            'captcha' => trim($code),
            'type' => self::$type_map[$type],
        ]);
        if ($result) {
            $cacheKey = sprintf('%s:%s:%s', RedisQueue::USER_ADMIN_CAPTCHA_CACHE, self::$type_map[$type], $phone);
            RedisQueue::del(["key" => $cacheKey]);
            AdminUserCaptcha::deleteAll(['id' => $result->id]);
            return time() <= $result->expire_time;
        } else {
            return false;
        }
    }

    /**
     * 生成并发送验证码
     * @param $phone
     * @param $type
     * @param $packageName
     * @return bool
     * @throws \Exception
     */
    public function generateAndSendCaptcha($phone, $type, $packageName = 'sashaktrupee')
    {
        $now = time();
        $packageService = new PackageService($packageName);
        $sourceId = $packageService->getSourceId();
        $captcha = UserCaptcha::find()->where([
            'phone' => $phone,
            'type' => self::$type_map[$type],
            'source_id' => $sourceId
        ])->orderBy(['id' => SORT_DESC])->one();
        if (is_null($captcha)) {
            $captcha = new UserCaptcha();
        }
        $captcha->source_id = $sourceId;
        $captcha->phone = $phone;
        $captcha->captcha = rand(100000, 999999);
        $captcha->type = self::$type_map[$type];
        $captcha->generate_time = $now;
        $captcha->expire_time = $captcha->generate_time + UserCaptcha::EXPIRE_SPACE;
        $captcha->save();

//        $packageService = new PackageService($packageName);
        if($packageService->getName() && isset(SendMessageService::$smsConfigList[$packageName])){
            $smsParamsName = SendMessageService::$smsConfigList[$packageName];
            $smsParamsNameBak = SendMessageService::$smsConfigListBak[$packageName] ?? '';
            if (self::TYPE_USER_REGISTER == $type) {
                $redisKey = "user_otp:get_count:{$packageName}:{$phone}";
                $otpGetCount = RedisQueue::inc([$redisKey, 1]);
                if ($otpGetCount < 2 && !empty($smsParamsNameBak)) {
                    RedisQueue::expire([$redisKey, 86400]);
                    $ret = MessageHelper::sendAll($phone, $captcha->getSMS($packageService->getName()), $smsParamsNameBak); //印度
                }
            }
            $ret = MessageHelper::sendAll($phone, $captcha->getSMS($packageService->getName()), $smsParamsName); //印度
        }else{
            //查不到默认
            $ret = MessageHelper::sendAll($phone, $captcha->getSMS('SashaktRupee'), 'smsService_LianDong_OTP_SASHAKTRUPEE'); //印度
        }

        //临时判断包名

        //todo 等待接入短信服务
        return true;

    }



    /**
     * 管理后台生成并发送验证码
     * @param $phone
     * @param $type
     * @param $packageName
     * @return bool
     * @throws \Exception
     */
    public function generateAndSendCaptchaAdmin($phone, $type = AdminUserCaptcha::TYPE_ADMIN_LOGIN)
    {
        $cacheKey = sprintf('%s:%s:%s', RedisQueue::USER_ADMIN_CAPTCHA_CACHE, $type, $phone);
        $captchaCache = RedisQueue::get(['key' => $cacheKey]);

        $now = time();

        if(YII_ENV_PROD)
        {
            $captchaCode = rand(100000, 999999);
        }else{
            $captchaCode = 8888;
        }

        $captcha = AdminUserCaptcha::find()->where([
            'phone' => $phone,
            'type' => $type,
        ])->orderBy(['id' => SORT_DESC])->one();
        if (is_null($captcha)) {
            $captcha = new AdminUserCaptcha();
        }
        $captcha->phone = $phone;
        $captcha->captcha = is_null($captchaCache) ? $captchaCode : $captchaCache;
        $captcha->type = $type;
        $captcha->generate_time = $now;
        $captcha->expire_time = $captcha->generate_time + AdminUserCaptcha::EXPIRE_SPACE;
        $captcha->save();

        if (is_null($captchaCache)) {
            RedisQueue::set([
                'expire' => AdminUserCaptcha::SAME_SPACE,
                'key'    => $cacheKey,
                'value'  => $captcha->captcha,
            ]);
        }

        if(preg_match('/^1\d{10}$/', $phone)){  //中国手机号
            $ret = MessageHelper::sendAll($phone, $captcha->getSMS('erp'), 'smsService_ZhChuangLan_backend_OTP');
        }else{
//            $ret = MessageHelper::sendAll($phone, $captcha->getSMS('SashaktRupee'), 'smsService_India_iCredit_OTP'); //印度
            $ret = MessageHelper::sendAll($phone, $captcha->getSMS('SAAS'), 'smsService_LianDong_OTP_SASHAKTRUPEE');
        }
        //todo 等待接入短信服务
        return true;

    }
}

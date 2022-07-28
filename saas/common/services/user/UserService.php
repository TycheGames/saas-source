<?php

namespace common\services\user;

use common\helpers\CommonHelper;
use common\helpers\EncryptData;
use common\helpers\RedisQueue;
use common\helpers\Util;
use common\models\enum\CreditReportStatus;
use common\models\enum\ErrorCode;
use common\models\enum\Gender;
use common\models\enum\mg_user_content\UserContentType;
use common\models\enum\VerificationItem;
use common\models\ClientInfoLog;
use common\models\order\UserLoanOrder;
use common\models\product\ProductSetting;
use common\models\question\UserQuestionVerification;
use common\models\user\LoanPerson;
use common\models\user\UserBankAccount;
use common\models\user\UserConversionData;
use common\models\user\UserCreditLimit;
use common\models\user\UserCreditReport;
use common\models\user\UserCreditReportFrLiveness;
use common\models\user\UserCreditReportFrVerify;
use common\models\user\UserCreditReportOcrAad;
use common\models\user\UserCreditReportOcrPan;
use common\models\user\UserLoginLog;
use common\models\user\UserPanCheckLog;
use common\models\user\UserRegisterInfo;
use common\models\user\UserVerification;
use common\models\user\UserVerificationLog;
use common\services\BaseService;
use common\services\FileStorageService;
use common\services\loan\LoanService;
use common\services\order\OrderExtraService;
use common\services\package\PackageService;
use frontend\models\loan\ApplyLoanForm;
use frontend\models\loan\PushOrderUserCheckForm;
use frontend\models\notify\PushOrderUserInfoForm;
use frontend\models\user\CaptchaLoginForm;
use frontend\models\user\GetLoginCaptchaForm;
use frontend\models\user\GetResetPassOtpForm;
use frontend\models\user\LoginForm;
use frontend\models\user\PhoneExistForm;
use frontend\models\user\RegGetCodeForm;
use frontend\models\user\RegisterForm;
use frontend\models\user\ResetPasswordForm;
use frontend\models\user\UserBasicInfoForm;
use frontend\models\user\UserBasicInfoExternalForm;
use frontend\models\user\UserContactForm;
use frontend\models\user\UserWorkInfoForm;
use yii;
use yii\db\Exception;

/**
 * 用户基本模块service
 */
class UserService extends BaseService
{

    const USER_REGISTER = 1; //用户注册
    const USER_BIND = 2; //用户绑卡
    const USER_REAL = 3; //用户实名



    /**
     * 验证手机验证码
     * @param RegisterForm $form
     * @return bool
     */
    public function validateRegCaptcha(RegisterForm $form)
    {
        $packageService = new PackageService($form->packageName);
        $sourceId = $packageService->getSourceId();
        $service = new CaptchaService();
        return $service->validatePhoneCaptcha($form->phone, $form->code, $sourceId, CaptchaService::TYPE_USER_REGISTER);
    }


    /**
     * 注册
     * @param RegisterForm $form
     * @return array|bool|LoanPerson|yii\db\ActiveRecord|null
     */
    public function registerByPhone(RegisterForm $form)
    {

        if (!$form->phone || !$form->password) {
            $this->setError('params err');
            return false;
        }

        if (!Util::verifyPhone($form->phone)) {
            $this->setError('please enter the correct phone number');
            return false;
        }

        if (!self::lockUserRegisterRecord($form->phone)) {
            $this->setError('please try again later');
            return false;
        }

        $packageService = new PackageService($form->packageName);
        $sourceId = $packageService->getSourceId();
        $merchantId = $packageService->getMerchantId();
        $conversionData = json_decode($form->conversionData,true);


        $transaction = Yii::$app->db->beginTransaction();
        try {
            $type = LoanPerson::PERSON_TYPE_PERSON;

            $user = LoanPerson::find()->where([
                'phone' => $form->phone,
                'source_id' => $sourceId
            ])->one();
            if ($user) {
                throw new Exception('User already exists');
            }

            $user = new LoanPerson();
            $user->merchant_id = $merchantId;
            $user->source_id = $sourceId;
            $user->phone = $form->phone;
            $user->status = LoanPerson::PERSON_STATUS_PASS;
            $user->type = $type;
            $user->created_ip = $form->clientInfo['ip'];

            // 先做完所有验证再save保证两个能同时保存成功

            if (!$user->save(false)) {
                throw new \Exception('fail to register,code:1264.');
            }

            $user->initPassword($form->password);//设置密码

            $userRegisterInfo = new UserRegisterInfo();
            $userRegisterInfo->user_id = $user->id;
            $userRegisterInfo->clientType = $form->clientInfo['clientType'];
            $userRegisterInfo->osVersion = $form->clientInfo['osVersion'];
            $userRegisterInfo->appVersion = $form->clientInfo['appVersion'];
            $userRegisterInfo->deviceName = $form->clientInfo['deviceName'];
            $userRegisterInfo->appMarket = $form->clientInfo['appMarket'];
            $userRegisterInfo->media_source = empty($conversionData['agency']) ? $form->mediaSource : $conversionData['agency'];
            $userRegisterInfo->headers = json_encode($form->clientInfo, JSON_UNESCAPED_UNICODE);
            $userRegisterInfo->deviceId = $form->clientInfo['deviceId'];
            $userRegisterInfo->did = $form->clientInfo['szlmQueryId'];
            $userRegisterInfo->af_status = $form->afStatus;
            $userRegisterInfo->apps_flyer_uid = $form->appsFlyerUID;
            $userRegisterInfo->date = date("Y-m-d", time());
            if (!$userRegisterInfo->save()) {
                throw new Exception('fail to register,code:2123.');
            }

            $userVerification = new UserVerification();
            $userVerification->user_id = $user->id;
            $userVerification->is_first_loan = UserVerification::FIRST_LOAN_YES;
            if(!$userVerification->save()){
                throw new Exception('fail to register,code:2233.');
            }

            $userCreditLimit = new UserCreditLimit();
            $userCreditLimit->user_id = $user->id;
            $userCreditLimit->max_limit = 170000;
            $userCreditLimit->min_limit = 170000;
            $userCreditLimit->type = UserCreditLimit::TYPE_7_DAY;
            if(!$userCreditLimit->save()){
                throw new Exception('fail to register,code:2444.');
            }

            //用户数据
            $userConversionData = new UserConversionData();
            $userConversionData->user_id = $user->id;
            $userConversionData->data = $form->conversionData;
            $userConversionData->created_at = time();
            if(!$userConversionData->save()){
                throw new Exception('fail to register,code:2445.');
            }

            ClientInfoLog::addLog($user->id, ClientInfoLog::EVENT_REGISTER, $user->id, $form->clientInfo);
            $transaction->commit();
            return $user;

        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error("msg:{$e->getMessage()} , tracer:{$e->getTraceAsString()}, line:{$e->getLine()}",'userRegister');
            $this->setError($e->getMessage());
            // 释放锁
            self::releaseRegisterLock($form->phone);
            return false;
        }
    }



    /**
     * 通过手机密码登陆
     * @param LoginForm $form
     * @return bool
     */
    public function loginByPhone(LoginForm $form)
    {
        $packageService = new PackageService($form->packageName);
        $sourceId = $packageService->getSourceId();
        $user = LoanPerson::findByPhone($form->phone, $sourceId);
        if (is_null($user)) {
            $this->setError('please enter the correct phone and password');
            return false;
        }
        if($user->status != LoanPerson::PERSON_STATUS_PASS){
            $this->setError('please enter the correct phone and password');
            return false;
        }
        // 密码登录
        $login_type = UserLoginLog::TYPE_NORMAL;
        if(!$user->validatePassword($form->password)){
            $this->setError('please enter the correct phone and password');
            return false;
        }

        return $this->login($user, $login_type, $form->clientInfo);
    }




    /**
     * 通过验证码登陆
     * @param CaptchaLoginForm $validateModel
     * @return bool
     */
    public function loginByCaptcha(CaptchaLoginForm $validateModel)
    {
        $packageService = new PackageService($validateModel->packageName);
        $sourceId = $packageService->getSourceId();
        $user = LoanPerson::findByPhone($validateModel->phone, $sourceId);
        if (is_null($user)) {
            $this->setError('please enter the correct phone and password');
            return false;
        }
        if($user->status != LoanPerson::PERSON_STATUS_PASS){
            $this->setError('please enter the correct phone and password');
            return false;
        }
        // 密码登录
        $login_type = UserLoginLog::TYPE_CAPTCHA;
        if(!$this->validateLoginCaptcha($validateModel->phone, $validateModel->code, $sourceId)){
            $this->setError('please enter the correct phone and otp');
            return false;
        }

        return $this->login($user, $login_type, $validateModel->clientInfo);
    }

    /**
     * 用户登录
     * @param LoanPerson $user
     * @param $login_type
     * @param array $params
     * @return boolean
     */
    public function login(LoanPerson $user, $login_type = UserLoginLog::TYPE_NORMAL, $params = [])
    {
        if (Yii::$app->user->login($user)) {
            // 记录登录日志
            $loginLog = new UserLoginLog();
            $loginLog->user_id = $user->id;
            $loginLog->created_at = time();
            $loginLog->created_ip = $params['ip'] ?? '';
            $loginLog->source = serialize($params);
            $loginLog->type = $login_type;
            $loginLog->device_id = $params['deviceId'] ?? '';
            $loginLog->push_token = $params['googlePushToken'] ?? '';
            if(!$loginLog->save()){
                yii::error($params,'login');
            }
            ClientInfoLog::addLog($user->id, ClientInfoLog::EVENT_LOGIN, $loginLog->id, $params);

            $data = [
                'username' => $user->phone,
                'sessionid' => Yii::$app->session->getId(),
            ];
            $this->setResult($data);
            return true;
        } else {
            $this->setError('server is too busy');
            return false;
        }

    }


    /**
     * 获取用户注册锁
     * @param $phone
     * @return bool
     */
    public static function lockUserRegisterRecord($phone)
    {
        if(YII_ENV_DEV){
            return true;
        }
        $lock_key = sprintf("%s%s:%s", RedisQueue::USER_OPERATE_LOCK, 'user:register:phone', $phone);
        $ret = RedisQueue::inc([$lock_key, 1]);
        RedisQueue::expire([$lock_key, 15]);
        return (1 == $ret);
    }


    /**
     * 释放用户注册锁
     * @param $phone
     */
    public static function releaseRegisterLock($phone)
    {
        $lock_key = sprintf("%s%s:%s", RedisQueue::USER_OPERATE_LOCK, "user:register:phone:", $phone);
        RedisQueue::del(["key" => $lock_key]);
    }


    /**
     * 获取用户注册验证码
     * @param RegGetCodeForm $form
     * @return bool
     * @throws \Exception
     */
    public function getRegGetCode(RegGetCodeForm $form)
    {
        if(!Util::verifyPhone($form->phone)){
            $this->setError('please enter a valid mobile phone number');
            return false;
        }
        $packageService = new PackageService($form->packageName);
        $sourceId = $packageService->getSourceId();
        $loanPerson = LoanPerson::findByPhone($form->phone, $sourceId);
        if($loanPerson){
            $this->setError('phone number has been registered');
            return false;
        }
        $service = new CaptchaService();
        return $service->generateAndSendCaptcha($form->phone, CaptchaService::TYPE_USER_REGISTER, $form->packageName);
    }


    /**
     * 获取用户登陆验证码
     * @param GetLoginCaptchaForm $validateModel
     * @return bool
     * @throws \Exception
     */
    public function getLoginCaptcha(GetLoginCaptchaForm $validateModel)
    {
        if(!Util::verifyPhone($validateModel->phone)){
            $this->setError('please enter a valid mobile phone number');
            return false;
        }
        $packageService = new PackageService($validateModel->packageName);
        $sourceId = $packageService->getSourceId();
        $loanPerson = LoanPerson::findByPhone($validateModel->phone, $sourceId);
        if(!$loanPerson){
            $this->setError('用户不存在');
            return false;
        }
        if($loanPerson->status != LoanPerson::PERSON_STATUS_PASS){
            $this->setError('valid account');
            return false;
        }
        $service = new CaptchaService();
        return $service->generateAndSendCaptcha($validateModel->phone, CaptchaService::TYPE_USER_LOGIN, $validateModel->packageName);
    }



    /**
     * 验证用户重置密码验证码
     * @param $phone
     * @param $code
     * @param $sourceId
     * @return bool
     */
    public function validateLoginCaptcha($phone, $code, $sourceId)
    {
        $service = new CaptchaService();
        return $service->validatePhoneCaptcha($phone, $code, $sourceId,CaptchaService::TYPE_USER_LOGIN);
    }



    /**
     * 获取用户重置密码验证码
     * @param GetResetPassOtpForm $validateModel
     * @return bool
     * @throws \Exception
     */
    public function getResetPassCode(GetResetPassOtpForm $validateModel)
    {
        if(!Util::verifyPhone($validateModel->phone)){
            $this->setError('please enter a valid mobile phone number');
            return false;
        }
        $packageService = new PackageService($validateModel->packageName);
        $sourceId = $packageService->getSourceId();
        $loanPerson = LoanPerson::findByPhone($validateModel->phone, $sourceId);
        if(!$loanPerson){
            $this->setError('用户不存在');
            return false;
        }
        $service = new CaptchaService();
        return $service->generateAndSendCaptcha($validateModel->phone, CaptchaService::TYPE_USER_RESET_PASS, $validateModel->packageName);
    }




    /**
     * 验证用户重置密码验证码
     * @param string $phone
     * @param string $code
     * @param int $sourceId
     * @return bool
     */
    public function validateResetPassCaptcha($phone, $code, $sourceId)
    {
        $service = new CaptchaService();
        return $service->validatePhoneCaptcha($phone, $code, $sourceId, CaptchaService::TYPE_USER_RESET_PASS);
    }


    /**
     * 用户重置密码
     * @param ResetPasswordForm $validateModel
     * @return bool
     * @throws yii\base\Exception
     */
    public function resetUserPass(ResetPasswordForm $validateModel)
    {
        $packageService = new PackageService($validateModel->packageName);
        $sourceId = $packageService->getSourceId();
        if(!$this->validateResetPassCaptcha($validateModel->phone, $validateModel->code, $sourceId)){
            $this->setError('请输入正确的otp');
            return false;
        }
        $loanPerson = LoanPerson::findByPhone($validateModel->phone, $sourceId);
        $userPassword = $loanPerson->userPassword;
        $userPassword->password = Yii::$app->security->generatePasswordHash($validateModel->password);
        if($userPassword->save()){
            return true;
        }else{
            return false;
        }
    }


    /**
     * 判断手机号是否已注册
     * @param PhoneExistForm $validateModel
     * @return bool
     */
    public function phoneIsRegistered(PhoneExistForm $validateModel)
    {
        try{
            $packageService = new PackageService($validateModel->packageName);
            $sourceId = $packageService->getSourceId();
            $check = LoanPerson::findByPhone($validateModel->phone, $sourceId);
            return $check ? true : false;
        }catch (\Exception $exception)
        {
            yii::error($validateModel->toArray(), 'phoneIsRegistered');
            return false;
        }
    }


    /**
     * 展示用户pan卡信息
     * @param $userId
     * @return array
     */
    public function getUserPanInfo($userId)
    {
        $loanPerson = LoanPerson::findOne($userId);

        $data = [
            'pan_code' => $loanPerson->pan_code,
            'full_name' => $loanPerson->name,
            'phone_number' => $loanPerson->phone,
        ];

        return $data;
    }

    /**
     * 获取用户身份认证状态
     * @param $userId
     * @return array
     */
    public function getUserIdentityAuthStatus($userId)
    {
        $service = new UserVerificationService($userId);
        $status = $service->getVerificationStatus();
        return [
            'aadhaarStatus' => boolval($status[VerificationItem::IDENTITY()->getValue()]),
            'panCardStatus' => boolval($status[VerificationItem::IDENTITY()->getValue()]),
            'voterIdCardStatus' => false,
        ];
    }

    public function registerByPushData(PushOrderUserInfoForm $form)
    {
        $packageService = new PackageService($form->packageName);
        $checkForm = new PushOrderUserCheckForm();
        $checkForm->phone = $form->phone;
        $checkForm->aadhaar = $form->aadhaar;
        $checkForm->pan = $form->pan;
        $checkForm->packageName = $form->packageName;
        if (!$this->checkPushOrderUserInfo($checkForm)) {
            return false;
        }

        $user = LoanPerson::find()
            ->where(['phone' => $form->phone])
            ->andWhere(['source_id' => $packageService->getSourceId()])
            ->one();
        if (!$user) {
            $registerData = $form->decodeData['registerInfo'];
            $clientInfoData= json_decode($registerData['headers'], true);
            $appMarket = $registerData['appMarket'] ?? ($clientInfoData['appMarket'] ?? '');
            $clientInfoData['appMarket'] = 'external_' . $appMarket;
            $clientInfoData['ip'] = $registerData['ip'] ?? ($clientInfoData['ip'] ?? null);
            $clientInfoData['clientType'] = $registerData['clientType'] ?? ($clientInfoData['clientType'] ?? null);
            $clientInfoData['osVersion'] = $registerData['osVersion'] ?? ($clientInfoData['osVersion'] ?? null);
            $clientInfoData['appVersion'] = $registerData['appVersion'] ?? ($clientInfoData['appVersion'] ?? null);
            $clientInfoData['deviceName'] = $registerData['deviceName'] ?? ($clientInfoData['deviceName'] ?? null);
            $clientInfoData['deviceId'] = $registerData['deviceId'] ?? ($clientInfoData['deviceId'] ?? null);
            $clientInfoData['szlmQueryId'] = $registerData['szlmQueryId'] ?? ($clientInfoData['szlmQueryId'] ?? null);
            $registerForm = new RegisterForm();
            $registerForm->phone = $form->phone;
            $registerForm->password = mt_rand(100000, 999999);
            $registerForm->packageName = $form->packageName;
            $registerForm->clientInfo = $clientInfoData;
            $registerForm->afStatus = '';
            $registerForm->appsFlyerUID = '';
            $mediaSource = empty($registerData['media_source']) ? '' : '_' . $registerData['media_source'];
            $registerForm->mediaSource = 'external' . $mediaSource;
            $user = $this->registerByPhone($registerForm);
        }
        if ($user) {
            $personData = $form->decodeData['personData'];
            $decryptedAadhaar = EncryptData::decrypt($form->encryptedAadhaar);
            $user->pan_code = $form->pan;
            $user->check_code = $form->encryptedAadhaar;
            $user->aadhaar_md5 = $form->aadhaar;
            $user->aadhaar_mask = substr($decryptedAadhaar, -4, 4);
            $user->name = $personData['name'];
            $user->father_name = $personData['father_name'];
            $user->gender = $personData['gender'];
            $user->birthday = $personData['birthday'];
            $user->save();
            $this->setResult(['user_id' => $user->id,]);
            return true;
        } else {
            return false;
        }
    }

    public function verificationByPushData(PushOrderUserInfoForm $form)
    {
        $orderData = $form->decodeData['orderData'];
        $isExist = UserLoanOrder::find()
            ->where(['order_uuid' => $orderData['order_uuid']])
            ->exists();
        if ($isExist) {
            $this->setResult([]);
            return true;
        }

        $packageService = new PackageService($form->packageName);
        $sourceId = $packageService->getSourceId();
        $user = LoanPerson::findByPhone($form->phone, $sourceId);
        $userId = $user->id;
        if (empty($user)) {
            $this->setError('User error!');
            return false;
        }
        /**
         * @var ProductSetting $product
         */
        $product = ProductSetting::find()
            ->where(['package_name' => $form->packageName])
            ->andWhere(['is_internal' => ProductSetting::IS_EXTERNAL_YES])
            ->limit(1)
            ->one();
        if(empty($product))
        {
            $this->setError('Product error!');
            return false;
        }
        $verification = $user->userVerification;

        //基本信息-basic
        $clientInfoParams = $form->decodeData['clientInfo'];
        $clientInfo['appMarket'] = 'external_' . $clientInfoParams['app_market'];
        $clientInfo['packageName'] = $form->packageName;
        $clientInfo['clientType'] = $clientInfoParams['client_type'] ?? null;
        $clientInfo['osVersion'] = $clientInfoParams['os_version'] ?? null;
        $clientInfo['appVersion'] = $clientInfoParams['app_version'] ?? null;
        $clientInfo['deviceName'] = $clientInfoParams['device_name'] ?? null;
        $clientInfo['deviceId'] = $clientInfoParams['device_id'] ?? null;
        $clientInfo['brandName'] = $clientInfoParams['brand_name'] ?? null;
        $clientInfo['bundleId'] = $clientInfoParams['bundle_id'] ?? null;
        $clientInfo['longitude'] = $clientInfoParams['longitude'] ?? null;
        $clientInfo['latitude'] = $clientInfoParams['latitude'] ?? null;
        $clientInfo['szlmQueryId'] = $clientInfoParams['szlm_query_id'] ?? null;
        $clientInfo['screenWidth'] = $clientInfoParams['screen_width'] ?? null;
        $clientInfo['screenHeight'] = $clientInfoParams['screen_height'] ?? null;
        $clientInfo['googlePushToken'] = $clientInfoParams['google_push_token'] ?? null;
        $clientInfo['tdBlackbox'] = $clientInfoParams['td_blackbox'] ?? null;
        $clientInfo['timestamp'] = $clientInfoParams['client_time'] ?? null;
        $clientInfo['ip'] = $clientInfoParams['ip'] ?? null;
        $basicForm = new UserBasicInfoForm();
        $basicData = $form->decodeData['basicData'];
        foreach ($basicForm->maps() as $key => $value) {
            $basicForm->$key = $basicData[$value] ?? null;
        }
        $basicForm->clientInfo = json_encode($clientInfo);
        $basicForm->aadhaarAddressId = $basicData['aadhaar_address_code1'] . ',' . $basicData['aadhaar_address_code2'];
        $basicForm->aadhaarAddressVal = $basicData['aadhaar_address1'] . ',' . $basicData['aadhaar_address2'];
        $basicService = new UserBasicInfoService();
        $basicResult = $basicService->saveUserBasicInfoByForm($basicForm, $userId);
        if (!$basicResult) {
            $this->setError('Save basic error!');
            return false;
        }
        $verification->verificationUpdate(UserVerification::TYPE_BASIC, UserVerificationLog::STATUS_VERIFY_SUCCESS, false);
        //基本信息-work
        $workForm = new UserWorkInfoForm();
        $workData = $form->decodeData['workData'];
        foreach ($workForm->maps() as $key => $value) {
            $workForm->$key = $workData[$value] ?? null;
        }
        $workForm->clientInfo = json_encode($clientInfo);
        $workForm->residentialAddressId = $workData['residential_address_code1'] . ',' . $workData['residential_address_code2'];
        $workForm->residentialAddressVal = $workData['residential_address1'] . ',' . $workData['residential_address2'];
        $workService = new UserWorkInfoService();
        $workResult = $workService->saveUserWorkInfoByForm($workForm, $userId);
        if (!$workResult) {
            $this->setError('Save work error!');
            return false;
        }
        $verification->verificationUpdate(UserVerification::TYPE_WORK, UserVerificationLog::STATUS_VERIFY_SUCCESS, false);

        //紧急联系人
        $contractForm = new UserContactForm();
        foreach ($contractForm->maps() as $key => $value) {
            $contractForm->$key = $form->decodeData['contactData'][$value] ?? null;
        }
        $contractForm->clientInfo = json_encode($clientInfo);
        $contractService = new UserContactService();
        $contractResult = $contractService->saveUserContactByForm($contractForm, $userId);
        if (!$contractResult) {
            $this->setError('Save contract error!');
            return false;
        }
        $verification->verificationUpdate(UserVerification::TYPE_CONTACT, UserVerificationLog::STATUS_VERIFY_SUCCESS, false);

        $fileService = new FileStorageService();
        //KYC-PanOCR
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $panOcrData = $form->decodeData['panOcrData'];
            $panOcrOriginalReport = new UserCreditReport();
            $panOcrOriginalReport->merchant_id = $user->merchant_id;
            switch ($panOcrData['type']) {
                case UserCreditReportOcrPan::SOURCE_ACCUAUTH:
                    $panOcrOriginalReport->source_type = UserCreditReport::SOURCE_EXPORT;
                    break;
                case UserCreditReportOcrPan::SOURCE_ADVANCE:
                    $panOcrOriginalReport->source_type = UserCreditReport::SOURCE_EXPORT_ADVANCE;
                    break;
            }
            $panOcrOriginalReport->report_type = UserCreditReport::TYPE_OCR_PAN;
            $panOcrOriginalReport->save();
            $panOcrReport = new UserCreditReportOcrPan();
            $panOcrReport->report_id = $panOcrOriginalReport->id;
            $panOcrReport->user_id = $userId;
            $panOcrReport->merchant_id = $user->merchant_id;
            $panOcrReport->img_front_path = empty($panOcrData['img_front_path']) ? null : $fileService->uploadFileByUrl('india/pan', $panOcrData['img_front_path']);
            $panOcrReport->img_back_path = empty($panOcrData['img_back_path']) ? null : $fileService->uploadFileByUrl('india/pan', $panOcrData['img_back_path']);
            $panOcrReport->date_type = $panOcrData['date_type'];
            $panOcrReport->date_info = $panOcrData['date_info'];
            $panOcrReport->father_name = $panOcrData['father_name'];
            $panOcrReport->full_name = $panOcrData['full_name'];
            $panOcrReport->report_status = $panOcrData['report_status'];
            $panOcrReport->data_status = $panOcrData['data_status'];
            $panOcrReport->type = $panOcrData['type'];
            $panOcrReport->save();
            $panOcrReport->setThisUsed();
            $transaction->commit();
        } catch (\Exception $exception) {
            $this->setError('Save pan ocr error!');
            $transaction->rollBack();
            return false;
        }
        $verification->verificationUpdate(UserVerification::TYPE_OCR_PAN, UserVerificationLog::STATUS_VERIFY_SUCCESS, false);

        //KYC-AadOCR
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $aadOcrData = $form->decodeData['aadOcrData'];
            $aadOcrOriginalReport = new UserCreditReport();
            $aadOcrOriginalReport->merchant_id = $user->merchant_id;
            switch ($aadOcrData['type']) {
                case UserCreditReportOcrAad::SOURCE_ACCUAUTH:
                    $aadOcrOriginalReport->source_type = UserCreditReport::SOURCE_EXPORT;
                    break;
                case UserCreditReportOcrAad::SOURCE_ADVANCE:
                    $aadOcrOriginalReport->source_type = UserCreditReport::SOURCE_EXPORT_ADVANCE;
                    break;
            }
            $aadOcrOriginalReport->report_type = UserCreditReport::TYPE_OCR_AAD;
            $aadOcrOriginalReport->save();
            $aadOcrReport = new UserCreditReportOcrAad();
            $aadOcrReport->report_id = $aadOcrOriginalReport->id;
            $aadOcrReport->user_id = $userId;
            $aadOcrReport->merchant_id = $user->merchant_id;
            $aadOcrReport->img_front_mask_path = empty($aadOcrData['img_front_mask_path']) ? null : $fileService->uploadFileByUrl('india/check_code', $aadOcrData['check_data_z_path']);
            $aadOcrReport->img_front_mask_path = empty($aadOcrData['img_front_mask_path']) ? null : $fileService->uploadFileByUrl('india/check_code', $aadOcrData['check_data_f_path']);
            $aadOcrReport->check_data_z_path = empty($aadOcrData['check_data_z_path']) ? null : $fileService->uploadFileByUrl('india/check_code', $aadOcrData['check_data_z_path']);
            $aadOcrReport->check_data_f_path = empty($aadOcrData['check_data_f_path']) ? null : $fileService->uploadFileByUrl('india/check_code', $aadOcrData['check_data_f_path']);
            $aadOcrReport->is_mask = 1;
            $aadOcrReport->is_mask_back = 1;
            $aadOcrReport->is_encode = 3; //已加密原数据，已删除原数据
            $aadOcrReport->card_no = '';
            $aadOcrReport->card_no_encode = $aadOcrData['card_no_encode'];
            $aadOcrReport->card_no_md5 = $aadOcrData['card_no_md5'];
            $aadOcrReport->card_no_mask = $aadOcrData['card_no_mask'];
            $aadOcrReport->date_type = $aadOcrData['date_type'];
            $aadOcrReport->date_info = $aadOcrData['date_info'];
            $aadOcrReport->full_name = $aadOcrData['full_name'];
            $aadOcrReport->gender = $aadOcrData['gender'];
            $aadOcrReport->address = $aadOcrData['address'];
            $aadOcrReport->mother_name = $aadOcrData['mother_name'];
            $aadOcrReport->father_name = $aadOcrData['father_name'];
            $aadOcrReport->phone_number = $aadOcrData['phone_number'];
            $aadOcrReport->pin = $aadOcrData['pin'];
            $aadOcrReport->state = $aadOcrData['state'];
            $aadOcrReport->city = $aadOcrData['city'];
            $aadOcrReport->report_status = $aadOcrData['report_status'];
            $aadOcrReport->data_front_status = $aadOcrData['data_front_status'];
            $aadOcrReport->data_back_status = $aadOcrData['data_back_status'];
            $aadOcrReport->type = $aadOcrData['type'];
            $aadOcrReport->save();
            $aadOcrReport->setThisUsed();
            $transaction->commit();
        } catch (\Exception $exception) {
            $this->setError('Save pan ocr error!');
            $transaction->rollBack();
            return false;
        }
        $verification->verificationUpdate(UserVerification::TYPE_OCR_AADHAAR, UserVerificationLog::STATUS_VERIFY_SUCCESS, false);

        //KYC-frVerify
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $frVerifyData = $form->decodeData['frVerify'];
            $frVerifyOriginalReport = new UserCreditReport();
            $frVerifyOriginalReport->merchant_id = $user->merchant_id;
            switch ($frVerifyData['type']) {
                case UserCreditReportFrVerify::SOURCE_ACCUAUTH:
                    $frVerifyOriginalReport->source_type = UserCreditReport::SOURCE_EXPORT;
                    break;
                case UserCreditReportFrVerify::SOURCE_ADVANCE:
                    $frVerifyOriginalReport->source_type = UserCreditReport::SOURCE_EXPORT_ADVANCE;
                    break;
            }
            $frVerifyOriginalReport->report_type = UserCreditReport::TYPE_FR_VERIFY;
            $frVerifyOriginalReport->save();
            $frVerifyReport = new UserCreditReportFrVerify();
            $frVerifyReport->report_id = $frVerifyOriginalReport->id;
            $frVerifyReport->merchant_id = $user->merchant_id;
            $frVerifyReport->img1_path = empty($frVerifyData['img1_path']) ? null : $fileService->uploadFileByUrl('india/fr_verify', $frVerifyData['img1_path']);
            $frVerifyReport->img2_path = empty($frVerifyData['img2_path']) ? null : $fileService->uploadFileByUrl('india/fr_verify', $frVerifyData['img2_path']);
            $frVerifyReport->user_id = $userId;
            $frVerifyReport->report_status = $frVerifyData['report_status'];
            $frVerifyReport->report_type = $frVerifyData['report_type'];
            $frVerifyReport->score = $frVerifyData['score'];
            $frVerifyReport->data_status = $frVerifyData['data_status'];
            $frVerifyReport->type = $frVerifyData['type'];
            $frVerifyReport->identical = $frVerifyData['identical'];
            $frVerifyReport->save();
            $frVerifyReport->setThisUsed();
            $transaction->commit();
        } catch (\Exception $exception) {
            $this->setError('Save frVerify error!');
            $transaction->rollBack();
            return false;
        }
        $verification->verificationUpdate(UserVerification::TYPE_FR_COMPARE_PAN, UserVerificationLog::STATUS_VERIFY_SUCCESS, false);

        //KYC-frLiveness
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $frLivenessData = $form->decodeData['frLiveness'];
            $frLivenessOriginalReport = new UserCreditReport();
            $frLivenessOriginalReport->merchant_id = $user->merchant_id;
            switch ($frLivenessData['type']) {
                case UserCreditReportFrLiveness::SOURCE_ACCUAUTH:
                    $frLivenessOriginalReport->source_type = UserCreditReport::SOURCE_EXPORT;
                    break;
                case UserCreditReportFrLiveness::SOURCE_ADVANCE:
                    $frLivenessOriginalReport->source_type = UserCreditReport::SOURCE_EXPORT_ADVANCE;
                    break;
            }
            $frLivenessOriginalReport->report_type = UserCreditReport::TYPE_FR_LIVENESS;
            $frLivenessOriginalReport->save();
            $frLivenessReport = new UserCreditReportFrLiveness();
            $frLivenessReport->report_id = $frLivenessOriginalReport->id;
            $frLivenessReport->merchant_id = $user->merchant_id;
            $frLivenessReport->img_fr_path = empty($frLivenessData['img_fr_path']) ? null : $fileService->uploadFileByUrl('india/fr', $frLivenessData['img_fr_path']);
            $frLivenessReport->data_fr_path = empty($frLivenessData['data_fr_path']) ? null : $fileService->uploadFileByUrl('india/fr', $frLivenessData['data_fr_path']);
            $frLivenessReport->user_id = $userId;
            $frLivenessReport->report_status = $frLivenessData['report_status'];
            $frLivenessReport->score = $frLivenessData['score'];
            $frLivenessReport->data_status = $frLivenessData['data_status'];
            $frLivenessReport->type = $frLivenessData['type'];
            $frLivenessReport->save();
            $frLivenessReport->setThisUsed();
            $transaction->commit();
        } catch (\Exception $exception) {
            $this->setError('Save frLiveness error!');
            $transaction->rollBack();
            return false;
        }
        $verification->verificationUpdate(UserVerification::TYPE_VERIFY, UserVerificationLog::STATUS_VERIFY_SUCCESS, false);

        //KYC-panVerify
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $panVerifyData = $form->decodeData['panVerify'];
            $panCheckLog = new UserPanCheckLog();
            $panCheckLog->user_id = $userId;
            $panCheckLog->merchant_id = $user->merchant_id;;
            $panCheckLog->pan_input = $panVerifyData['pan_input'];
            $panCheckLog->pan_ocr = $panVerifyData['pan_ocr'];
            $panCheckLog->ocr_report_id = $panOcrReport->id;
            $panCheckLog->report_status = $panVerifyData['report_status'];
            $panCheckLog->data_status = $panVerifyData['data_status'];
            $panCheckLog->full_name = $panVerifyData['full_name'];
            $panCheckLog->first_name = $panVerifyData['first_name'];
            $panCheckLog->middle_name = $panVerifyData['middle_name'];
            $panCheckLog->last_name = $panVerifyData['last_name'];
            $panCheckLog->report_data = $panVerifyData['report_data'];
            $panCheckLog->package_name = $clientInfoParams['package_name'] ?? null;
            $panCheckLog->check_third_source = UserPanCheckLog::SOURCE_EXPORT;
            $panCheckLog->save();
            $transaction->commit();
        } catch (\Exception $exception) {
            $this->setError('Save panVerify error!');
            $transaction->rollBack();
            return false;
        }
        $verification->verificationUpdate(UserVerification::TYPE_PAN, UserVerificationLog::STATUS_VERIFY_SUCCESS, false);

        //语言校验
        $languageVerifyData = $form->decodeData['languageVerify'];
        if (!empty($languageVerifyData)) {
            $userQuestionModel = new UserQuestionVerification();
            $userQuestionModel->user_id = $userId;
            $userQuestionModel->merchant_id = $user->merchant_id;
            $userQuestionModel->questions = $languageVerifyData['questions'];
            $userQuestionModel->answers = $languageVerifyData['answers'];
            $userQuestionModel->user_answers = $languageVerifyData['user_answers'];
            $userQuestionModel->enter_time = $languageVerifyData['enter_time'];
            $userQuestionModel->submit_time = $languageVerifyData['submit_time'];
            $userQuestionModel->correct_num = $languageVerifyData['correct_num'];
            $userQuestionModel->question_num = $languageVerifyData['question_num'];
            $userQuestionModel->data_status = $languageVerifyData['data_status'];
            if (!$userQuestionModel->save()) {
                $this->setError('Save languageVerify error!');
                return false;
            }
        }
        $verification->verificationUpdate(UserVerification::TYPE_LANGUAGE, UserVerificationLog::STATUS_VERIFY_SUCCESS, false);

        $bankCardData = $form->decodeData['bankCard'];
        $userBankAccount = UserBankAccount::find()
            ->where(['account' => $bankCardData['account']])
            ->andWhere(['source_id' => $user->source_id])
            ->andWhere(['user_id' => $userId])
            ->limit(1)
            ->one();
        if (!$userBankAccount) {
            $userBankAccount = new UserBankAccount();
        }
        $userBankAccount->user_id = $userId;
        $userBankAccount->source_id = $user->source_id;
        $userBankAccount->source_type = UserBankAccount::SOURCE_EXPORT;
        $userBankAccount->name = $user->name;
        $userBankAccount->report_account_name = $bankCardData['report_account_name'];
        $userBankAccount->account = $bankCardData['account'];
        $userBankAccount->ifsc = $bankCardData['ifsc'];
        $userBankAccount->status = $bankCardData['status']; //pan卡号相同，即姓名相同
        $userBankAccount->bank_name = $bankCardData['bank_name'];
        $userBankAccount->data = $bankCardData['data'];
        $userBankAccount->merchant_id = $user->merchant_id;
        $userBankAccount->save();
        $bankService = new UserBankInfoService();
        $bankService->changeMainCard($userBankAccount->id, $userId);


        //下单行为
//        $orderData = $form->decodeData['orderData'];
        $loanForm = new ApplyLoanForm();
        $loanForm->amount = intval(CommonHelper::CentsToUnit($orderData['amount'] - $orderData['cost_fee']));
        $loanForm->productId = $product->id;
        $loanForm->userId = $userId;
        $loanForm->clientInfo = $clientInfo;
        $loanForm->packageName = $form->packageName;
        $loanForm->isExport = true;
        $loanForm->orderUUID = $orderData['order_uuid'];
        $loanForm->bankCardId = $userBankAccount->id;
        $loanForm->isAllFirst = $orderData['is_all_first'] ?? null;
        $loanService = new LoanService();
        if($loanService->applyLoanForExternal($loanForm)) {
            $this->setResult([]);
            return true;
        } else {
            $this->setError($loanService->getError());
            return false;
        }
    }

    public function deviceInfoByPushData(PushOrderUserInfoForm $form, int $userId)
    {
        $user = LoanPerson::findById($userId);
        if (empty($user)) {
            $this->setError('User error!');
            return false;
        }
        $service = new MgUserContentService();

        $callRecordsData = $form->decodeData['callRecords'];
        if (!empty($callRecordsData)) {
            foreach ($callRecordsData as $datum) {
                $datum['user_id'] = $user->id;
                $datum['merchant_id'] = $user->merchant_id;
                $phoneNumbers = isset($datum['mobile']) ? explode(':', $datum['mobile']) : [];
                $item = $datum;
                foreach ($phoneNumbers as $phoneNumber) {
                    $item['mobile'] = substr($phoneNumber, -10);
                    $service->saveMgUserContentByFormToM(UserContentType::CONTACT(), $item);
                }
            }
        }

        $smsRecordsData = $form->decodeData['smsRecords'];
        if (!empty($smsRecordsData)) {
            foreach ($smsRecordsData as $datum) {
                $item = $datum;
                $item['user_id'] = $user->id;
                $item['merchant_id'] = $user->merchant_id;
                $service->saveMgUserContentByFormToM(UserContentType::SMS(), $item);
            }
        }

        $photoRecordsData = $form->decodeData['photoRecords'];
        if (!empty($photoRecordsData)) {
            foreach ($photoRecordsData as $datum) {
                $item = $datum;
                $item['user_id'] = $user->id;
                $item['merchant_id'] = $user->merchant_id;
                $service->saveMgUserPhoto($item);
            }
        }

        return true;
    }

    public function checkPushOrderUserInfo(PushOrderUserCheckForm $form)
    {
        $packageService = new PackageService($form->packageName);
        $users = LoanPerson::find()
            ->where(
                ['or', 'phone=:phone', 'pan_code=:pan'],
                [':phone' => $form->phone, ':pan' => $form->pan])
            ->andWhere(['source_id' => $packageService->getSourceId()])
            ->all();

        if (empty($users)) {
            $this->setResult([
                'isNewUser' => true,
                'msg'       => 'New User!',
            ]);
            return true;
        }

        if (count($users) > 1) {
            $this->setError('User phone and pan repeat!');
            return false;
        }

        /**
         * @var LoanPerson $user
         */
        $user = $users[0];
        if ($user->phone == $form->phone &&
            $user->pan_code == $form->pan &&
            $user->aadhaar_md5 == $form->aadhaar
        ) {
            $this->setResult([
                'isNewUser' => false,
                'userId'    => $user->id,
                'msg'       => 'Same user!',
            ]);
            return true;
        } elseif ($user->phone == $form->phone &&
            empty($user->pan_code) &&
            empty($user->aadhaar_md5)) {
            $this->setResult([
                'isNewUser' => false,
                'userId'    => $user->id,
                'msg'       => 'Same user but no certification!',
            ]);
            return true;
        }
        else {
            $this->setError('Different user!');
            return false;
        }
    }
}

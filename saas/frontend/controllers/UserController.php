<?php

namespace frontend\controllers;


use Carbon\Carbon;
use common\exceptions\UserExceptionExt;
use common\helpers\CommonHelper;
use common\helpers\RedisQueue;
use common\models\enum\AddressProofType;
use common\models\enum\City;
use common\models\enum\Education;
use common\models\enum\ErrorCode;
use common\models\enum\Industry;
use common\models\enum\Marital;
use common\models\enum\mg_user_content\UserContentType;
use common\models\enum\Relative;
use common\models\enum\VerificationItem;
use common\models\package\PackageSetting;
use common\models\user\LoanPerson;
use common\models\user\UserLoginLog;
use common\models\user\UserPhotoUrl;
use common\models\user\UserRegisterInfo;
use common\services\FileStorageService;
use common\services\loan\LoanService;
use common\services\order\UserLoanOrderService;
use common\services\package\PackageService;
use common\services\personal_center\PersonalCenterService;
use common\services\user\MgUserContentService;
use common\services\user\UserAddressService;
use common\services\user\UserBankInfoService;
use common\services\user\UserBasicInfoService;
use common\services\user\UserComplaintService;
use common\services\user\UserContactService;
use common\services\user\UserKYCService;
use common\services\user\UserQuestionService;
use common\services\user\UserService;
use common\services\user\UserVerificationService;
use common\services\user\UserWorkInfoService;
use frontend\models\user\ApplyComplaintForm;
use frontend\models\user\CaptchaLoginForm;
use frontend\models\user\GetLoginCaptchaForm;
use frontend\models\user\GetResetPassOtpForm;
use frontend\models\user\LoginForm;
use frontend\models\user\OrderForm;
use frontend\models\user\PhoneExistForm;
use frontend\models\user\RegGetCodeForm;
use frontend\models\user\RegisterForm;
use frontend\models\user\ResetPasswordForm;
use frontend\models\user\SelectMainCardForm;
use frontend\models\user\UserAadhaarForm;
use frontend\models\user\UserAddressProofOcrForm;
use frontend\models\user\UserAddressProofReportForm;
use frontend\models\user\UserBankAccountForm;
use frontend\models\user\UserBankAccountStatusForm;
use frontend\models\user\UserBasicInfoForm;
use frontend\models\user\UserContentForm;
use frontend\models\user\UserContactForm;
use frontend\models\user\UserFrForm;
use frontend\models\user\UserFrSecForm;
use frontend\models\user\UserKycForm;
use frontend\models\user\UserPanForm;
use frontend\models\user\UserPhotoForm;
use frontend\models\user\UserQuestionForm;
use frontend\models\user\UserWorkInfoForm;
use phpDocumentor\Reflection\File;
use Yii;
use yii\base\ErrorException;
use yii\captcha\CaptchaAction;
use yii\filters\AccessControl;
use yii\web\UploadedFile;

/**
 * User controller
 */
class UserController extends BaseController
{

    protected $userService;

    /**
     * 构造函数中注入UserService的实例到自己的成员变量中
     * 也可以通过Yii::$container->get('userService')的方式获得
     */
    public function __construct($id, $module, UserService $userService, $config = [])
    {
        $this->userService = $userService;

        parent::__construct($id, $module, $config);
    }

    public function behaviors()
    {
        return [
            'access' => [
                'class'  => AccessControl::class,
                // 除了下面的action其他都需要登录
                'except' => [
                    'register',
                    'reg-get-code',
                    'login',
                    'phone-is-registered',
                    'get-reset-pass-otp',
                    'reset-password',
                    'captcha-login',
                    'logout',
                    'get-login-captcha',
                    'get-personal-center-info'
                ],
                'rules'  => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function actions()
    {
        return [
            'captcha' => [
                'class'           => CaptchaAction::class,
                'testLimit'       => 1,
                'height'          => 35,
                'width'           => 80,
                'padding'         => 0,
                'minLength'       => 4,
                'maxLength'       => 4,
                'foreColor'       => 0x444444,
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }


    /**
     * @name UserController 注册 [user/register]
     * @method post
     * @param string phone 手机号
     * @param string code 验证码
     * @param string password 密码
     * @return array
     */
    public function actionRegister()
    {
        $form = new RegisterForm();
        if ($form->load(Yii::$app->request->post(), '') && $form->validate()) {
            $form->clientInfo = $this->getClientInfo();
            $form->packageName = $this->packageName();

//            if (!$this->userService->validateRegCaptcha($form)) {
//                return $this->return->returnFailed(
//                    ErrorCode::ERROR_COMMON(),
//                    'Please enter the correct otp code'
//                );
//            }

            if ($user = $this->userService->registerByPhone($form)) {
                $this->userService->login($user, UserLoginLog::TYPE_NORMAL, $form->clientInfo);
                $data = [
                    'username'  => $user->phone,
                    'sessionid' => \yii::$app->session->id,
                ];

                return $this->return->setData($data)->returnOK();
            } else {
                return $this->return->returnFailed(
                    ErrorCode::ERROR_COMMON(),
                    $this->userService->getError()
                );
            }
        } else {
            return $this->return->returnFailed(
                ErrorCode::ERROR_COMMON(),
                $this->getError($form->getErrorSummary(false))
            );
        }

    }


    /**
     * @name UserController 登录接口 [user/login]
     * @method post
     * @param string phone 用户名
     * @param string password 密码
     * @return array
     */
    public function actionLogin()
    {
        $form = new LoginForm();
        if ($form->load(Yii::$app->request->post(), '') && $form->validate()) {
            $form->clientInfo = $this->getClientInfo();
            $form->packageName = $this->packageName();
            if ($this->userService->loginByPhone($form)) {
//                $check = LoanService::haveOpeningOrderNoExport(Yii::$app->user->id);
//                if (!$check) {
//                    Yii::$app->user->logout();
//                    return $this->return->returnFailed(ErrorCode::ERROR_COMMON());
//                }
                return $this->return->setData($this->userService->getResult())->returnOK();
            } else {
                return $this->return->returnFailed(
                    ErrorCode::ERROR_COMMON(),
                    $this->userService->getError()
                );
            }
        } else {
            return $this->return->returnFailed(
                ErrorCode::ERROR_COMMON(),
                $this->getError($form->getErrorSummary(false))
            );
        }
    }


    /**
     * @name UserController 登出 [user/logout]
     * @method get
     * @return array
     */
    public function actionLogout()
    {
        if (!Yii::$app->user->isGuest) {
            Yii::$app->user->logout();
        }
        return $this->return->returnOK();
    }



    /**
     * @name UserController 获取注册验证码 [user/reg-get-code]
     * @method post
     * @param string phone
     * @return array
     * @throws \Exception
     */
    public function actionRegGetCode()
    {
        $form = new RegGetCodeForm();
        if ($form->load(Yii::$app->request->post(), '') && $form->validate()) {
            $form->packageName = $this->packageName();
            $r = $this->userService->getRegGetCode($form);
            if ($r) {
                return $this->return->returnOK();
            } else {
                return $this->return->returnFailed(
                    ErrorCode::ERROR_COMMON(),
                    $this->userService->getError()
                );
            }
        } else {
            return $this->return->returnFailed(
                ErrorCode::ERROR_COMMON(),
                'Please enter a valid mobile phone number'
            );
        }
    }

    /**
     * @name UserController 获取用户基本信息 [user/get-user-basic-info]
     * @method get
     * @return array
     */
    public function actionGetUserBasicInfo(): array
    {
        $result = [];

        $user = UserRegisterInfo::findOne(['user_id' => Yii::$app->user->id]);
        Yii::info(['user_id' => Yii::$app->user->id,'appMarket' => $user['appMarket'],'type' => 'get_basic_info','status' => 'success','msg' => 'success'],'auth_info');
        $service = new UserBasicInfoService();
        $model = $service->getUserBasicInfoByForm(Yii::$app->user->id);

        $service_work = new UserWorkInfoService();
        $model_work = $service_work->getUserWorkInfoByForm(Yii::$app->user->id);

        if (is_null($model) || is_null($model_work)) {
            return UserExceptionExt::throwCodeAndMsgExt('server is too busy');
        }

        $result['getData'] = [
//            'studentId'                   => $model->studentId ?? null,
            'fullName'                    => $model->fullName ?? null,
            'birthday'                    => $model->birthday ?? null,
//            'studentVal'                  => Student::$map[$model->studentId] ?? null,
            'maritalStatusId'             => $model->maritalStatusId ?? null,
            'maritalStatusVal'            => Marital::$map[$model->maritalStatusId] ?? null,
            'emailVal'                    => $model->emailVal ?? null,
            'zipCodeVal'                  => $model->zipCodeVal ?? null,
            'educationId'                 => $model_work->educationId ?? null,
            'educationVal'                => Education::$map[$model_work->educationId] ?? null,
            'industryId'                  => $model_work->industryId ?? null,
            'industryVal'                 => Industry::$map[$model_work->industryId] ?? null,
            'companyNameVal'              => $model_work->companyNameVal ?? null,
            'monthlySalaryVal'            => CommonHelper::CentsToUnit($model_work->monthlySalaryVal) ?? null,
            'residentialAddressId'        => $model_work->residentialAddressId ?? null,
            'residentialAddressVal'       => $model_work->residentialAddressVal ?? null,
            'residentialDetailAddressVal' => $model_work->residentialDetailAddressVal ?? null,
            'aadhaarPinCode'              => $model->aadhaarPinCode ?? null,
            'aadhaarAddressId'            => $model->aadhaarAddressId ?? null,
            'aadhaarAddressVal'           => $model->aadhaarAddressVal ?? null,
            'aadhaarDetailAddressVal'     => $model->aadhaarDetailAddressVal ?? null,
        ];

        //下拉框数据
        $result['selectData'] = [
//            'studentList'   => Student::formatForDropdownBoxData(Student::$map),
            'maritalList'   => Marital::formatForDropdownBoxData(Marital::$map),
            'educationList' => Education::formatForDropdownBoxData(Education::$map),
            'industryList'  => Industry::formatForDropdownBoxData(Industry::$map),
            'addressList'   => City::formatForDropdownBoxData(),
        ];

        return $this->return->setData($result)->returnOK();
    }

    /**
     * @name UserController 保存用户基本信息 [user/save-user-basic-info]
     * @method post
     * @param string birthday 出生日期
     * @param string fullName 姓名
     * @param int educationId 教育程度
     * @param int industryId 行业
     * @param int studentId 学生
     * @param int maritalStatusId 婚姻
     * @param string emailVal 邮箱
     * @param string zipCodeVal 邮编
     * @param string companyNameVal 公司名
     * @param int monthlySalaryVal 月薪
     * @param string residentialAddressId 居住区域编码（逗号分隔）
     * @param string residentialAddressVal 居住区域（逗号分隔）
     * @param string residentialDetailAddressVal 居住地址
     * @param string aadhaarPinCode Aad卡上的PinCode
     * @param string aadhaarAddressId Aad卡上的居住区域编码（逗号分隔）
     * @param string aadhaarAddressVal Aad卡上的居住区域（逗号分隔）
     * @param string aadhaarDetailAddressVal Aad卡上的居住地址
     * @return array
     */
    public function actionSaveUserBasicInfo(): array
    {
        $verificationService = new UserVerificationService(Yii::$app->user->id);
        $beforeItemStatus = $verificationService->checkBeforeVerificationItem(VerificationItem::BASIC());
        if(!$beforeItemStatus) {
            Yii::info(['user_id' => Yii::$app->user->id, 'url' => Yii::$app->uniqueId],'auth_process');
            return $this->return->returnFailed(ErrorCode::ERROR_COMMON(), 'Exit App and restart login process.');
        }

        $validateModel = new UserBasicInfoForm();
        $validateModel_work = new UserWorkInfoForm();

        if (!$validateModel->load(Yii::$app->request->post(), '') || !$validateModel_work->load(Yii::$app->request->post(), '')) {
            return $this->return->setData([])->returnFailed(ErrorCode::ERROR_COMMON());
        }

        $validateModel_work->monthlySalaryVal = CommonHelper::UnitToCents($validateModel_work->monthlySalaryVal);
        $clientInfo = $this->getClientInfo();
        $validateModel->clientInfo = $validateModel_work->clientInfo = json_encode($clientInfo, JSON_UNESCAPED_UNICODE);

        if (!$validateModel->validate()) {
            return $this->return
                ->setData([])
                ->returnFailed(ErrorCode::ERROR_COMMON(), $validateModel->getErrorSummary(false)[0]);
        }

        if (!$validateModel_work->validate()) {
            return $this->return
                ->setData([])
                ->returnFailed(ErrorCode::ERROR_COMMON(), $validateModel_work->getErrorSummary(false)[0]);
        }


        $service = new UserBasicInfoService();
        $service_work = new UserWorkInfoService();
        $user_register = UserRegisterInfo::findOne(['user_id' => Yii::$app->user->id]);
        if ($service->saveUserBasicInfoByForm($validateModel, Yii::$app->user->id) && $service_work->saveUserWorkInfoByForm($validateModel_work, Yii::$app->user->id)) {
            Yii::info(['user_id' => Yii::$app->user->id, 'appMarket' => $user_register['appMarket'],'type' => 'basic_info','status' => 'success','msg' => 'success'],'auth_info');
            $data = $verificationService->getNextVerificationItemPath(
                VerificationItem::BASIC(),
                Yii::$app->request->hostInfo,
                $clientInfo
            );
            return $this->return->setData($data)->returnOK();
        }
        Yii::info(['user_id' => Yii::$app->user->id,'appMarket' => $user_register['appMarket'],'type' => 'basic_info','status' => 'fail','msg' => 'save fail'],'auth_info');
        return UserExceptionExt::throwCodeAndMsgExt('server is too busy');
    }

    /**
     * @name UserController 获取用户银行卡列表 [user/get-bank-account-list]
     * @method get
     * @return array
     */
    public function actionGetBankAccountList()
    {
        $user = UserRegisterInfo::findOne(['user_id' => Yii::$app->user->id]);
        Yii::info(['user_id' => Yii::$app->user->id,'appMarket' => $user['appMarket'],'type' => 'get_bank_info','status' => 'success','msg' => 'success'],'auth_info');
        $userId = Yii::$app->user->identity->getId();
        $service = new UserBankInfoService();
        $list = $service->getUserBankAccounts($userId);
        return $this->return->setData([
            'list' => $list,
            'showAddBankCard' => count($list) < 3,
        ])->returnOK();
    }


    /**
     * @name UserController 绑卡接口 [user/save-bank-account]
     * @method post
     * @param string account 账户
     * @param string ifsc ifsc
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function actionSaveBankAccount()
    {
        // return $this->return->returnFailed(ErrorCode::ERROR_COMMON(),
        //     'system busy');
        $form = new UserBankAccountForm();
        $user = UserRegisterInfo::findOne(['user_id' => Yii::$app->user->id]);
        if ($form->load(Yii::$app->request->post(), '') && $form->validate()) {
            $service = new UserBankInfoService();
            $form->clientInfo = $this->getClientInfo();
            $form->name = Yii::$app->user->identity->name;
            $form->userId = Yii::$app->user->identity->getId();

            //元丁银行卡验证 verifyAndSaveBankInfo
            //AadhaarApi银行卡验证 verifyAndSaveBankInfoNew
            if ($service->asyncSaveBankInfo($form)) {
//                Yii::info(['user_id' => Yii::$app->user->id,'appMarket' => $user['appMarket'],'type' => 'bank_info','status' => 'success','msg' => 'success'],'auth_info');
                return $this->return->setData($service->getResult())->returnOK();
            } else {
//                Yii::info(['user_id' => Yii::$app->user->id,'appMarket' => $user['appMarket'],'type' => 'bank_info','status' => 'fail','msg' => $service->getError()],'auth_info');
                return $this->return->returnFailed(ErrorCode::ERROR_COMMON(),
                    $service->getError());
            }
        } else {
//            Yii::info(['user_id' => Yii::$app->user->id,'appMarket' => $user['appMarket'],'type' => 'bank_info','status' => 'fail','msg' => $this->getError($form->getErrorSummary(false))],'auth_info');
            return $this->return->returnFailed(ErrorCode::ERROR_COMMON(),
                $this->getError($form->getErrorSummary(false)));
        }
    }

    public function actionGetBankAccountStatus()
    {
        $form = new UserBankAccountStatusForm();
        $user = UserRegisterInfo::findOne(['user_id' => Yii::$app->user->id]);
        $form->userId = Yii::$app->user->identity->getId();
        if ($form->load(Yii::$app->request->post(), '') && $form->validate()) {
            $service = new UserBankInfoService();
            if ($service->asyncGetBankStatus($form)) {
                Yii::info(['user_id' => Yii::$app->user->id,'appMarket' => $user['appMarket'], 'media_source' => $user['media_source'], 'type' => 'bank_info','status' => 'success','msg' => 'success'],'auth_info');
                return $this->return->setData($service->getResult())->returnOK();
            } else {
                Yii::info(['user_id' => Yii::$app->user->id,'appMarket' => $user['appMarket'], 'media_source' => $user['media_source'], 'type' => 'bank_info','status' => 'fail','msg' => $service->getError()],'auth_info');
                return $this->return->returnFailed(ErrorCode::ERROR_COMMON(),
                    $service->getError());
            }
        } else {
            Yii::info(['user_id' => Yii::$app->user->id,'appMarket' => $user['appMarket'], 'media_source' => $user['media_source'], 'type' => 'bank_info','status' => 'fail','msg' => $this->getError($form->getErrorSummary(false))],'auth_info');
            return $this->return->returnFailed(ErrorCode::ERROR_COMMON(),
                $this->getError($form->getErrorSummary(false)));
        }
    }

    /**
     * @name UserController 切换主卡接口 [user/change-main-card]
     * @method post
     * @param int id 银行卡id
     * @return array
     */
    public function actionChangeMainCard()
    {
        $form = new SelectMainCardForm();
        $userId = Yii::$app->user->identity->getId();
        if ($form->load(Yii::$app->request->post(), '') && $form->validate()) {
            $service = new UserBankInfoService();
            if ($service->changeMainCard($form->id, $userId)) {
                return $this->return->returnOK();
            } else {
                return $this->return->returnFailed(ErrorCode::ERROR_COMMON(),
                    $service->getError());
            }
        } else {
            return $this->return->returnFailed(ErrorCode::ERROR_COMMON(),
                $this->getError($form->getErrorSummary(false)));
        }
    }


    /**
     * @name UserController 获取联系人信息 [user/get-user-contact]
     * @method get
     * @return array
     */
    public function actionGetUserContact()
    {
        $result = [];
        $user = UserRegisterInfo::findOne(['user_id' => Yii::$app->user->id]);
        Yii::info(['user_id' => Yii::$app->user->id,'appMarket' => $user['appMarket'],'type' => 'get_contact_info','status' => 'success','msg' => 'success'],'auth_info');

        $service = new UserContactService();
        $model = $service->getUserContactByForm(Yii::$app->user->id);

        $result['getData'] = [
            'relativeContactPersonId'       => $model->relativeContactPerson ?? null,
            'relativeContactPersonVal'      => Relative::$map[$model->relativeContactPerson] ?? null,
            'name'                          => $model->name ?? null,
            'phone'                         => $model->phone ?? null,
            'otherRelativeContactPersonId'  => $model->otherRelativeContactPerson ?? null,
            'otherRelativeContactPersonVal' => Relative::$map[$model->otherRelativeContactPerson] ?? null,
            'otherName'                     => $model->otherName ?? null,
            'otherPhone'                    => $model->otherPhone ?? null,
            'facebookAccount'               => $model->facebookAccount ?? null,
            'whatsAppAccount'               => $model->whatsAppAccount ?? null,
            'skypeAccount'                  => $model->skypeAccount ?? null,
        ];

        //下拉框数据
        $result['selectData'] = [
            'relativeList' => Relative::formatForDropdownBoxData(Relative::$map),
        ];

        return $this->return->setData($result)->returnOK();
    }

    /**
     * @name UserController 保存联系人信息 [user/save-user-contract]
     * @method post
     * @param int relativeContactPerson 紧急联系人关系
     * @param string name 紧急联系人姓名
     * @param string phone 紧急联系人电话
     * @param int otherRelativeContactPerson 其他联系人关系
     * @param string otherName 其他联系人姓名
     * @param string otherPhone 其他联系人电话
     * @param string facebookAccount facebook账号
     * @param string whatsAppAccount whatsApp账号
     * @param string skypeAccount skype账号
     * @return array
     */
    public function actionSaveUserContact(): array
    {
        $service = new UserContactService();
        $validateModel = new UserContactForm();
        $service->saveUserContactByForm($validateModel, Yii::$app->user->id);
        $verificationService = new UserVerificationService(Yii::$app->user->id);
        $clientInfo = $this->getClientInfo();
        $data = $verificationService->getNextVerificationItemPath(
            VerificationItem::CONTACT(),
            Yii::$app->request->hostInfo,
            $clientInfo
        );
        return $this->return->setData($data)->returnOK();







        $verificationService = new UserVerificationService(Yii::$app->user->id);
        $beforeItemStatus = $verificationService->checkBeforeVerificationItem(VerificationItem::CONTACT());
        if(!$beforeItemStatus) {
            Yii::info(['user_id' => Yii::$app->user->id, 'url' => Yii::$app->uniqueId],'auth_process');
            return $this->return->returnFailed(ErrorCode::ERROR_COMMON(), 'Exit App and restart login process.');
        }

        $validateModel = new UserContactForm();
        $user = UserRegisterInfo::findOne(['user_id' => Yii::$app->user->id]);
        if (!$validateModel->load(Yii::$app->request->post(), '')) {
            Yii::info(['user_id' => Yii::$app->user->id,'appMarket' => $user['appMarket'],'type' => 'contact_info','status' => 'fail','msg' => 'fail'],'auth_info');
            return $this->return->setData([])->returnFailed(ErrorCode::ERROR_COMMON());
        }
        $validateModel->userPhone = Yii::$app->user->identity->phone;
        $clientInfo = $this->getClientInfo();
        $validateModel->clientInfo = json_encode($clientInfo, JSON_UNESCAPED_UNICODE);

        if (!$validateModel->validate()) {
            Yii::info(['user_id' => Yii::$app->user->id,'appMarket' => $user['appMarket'],'type' => 'contact_info','status' => 'fail','msg' => $validateModel->getErrorSummary(false)[0]],'auth_info');
            return $this->return
                ->setData([])
                ->returnFailed(ErrorCode::ERROR_COMMON(), $validateModel->getErrorSummary(false)[0]);
        }

        $service = new UserContactService();
        if ($service->saveUserContactByForm($validateModel, Yii::$app->user->id)) {
            Yii::info(['user_id' => Yii::$app->user->id,'appMarket' => $user['appMarket'],'type' => 'contact_info','status' => 'success','msg' => 'success'],'auth_info');
            $data = $verificationService->getNextVerificationItemPath(
                VerificationItem::CONTACT(),
                Yii::$app->request->hostInfo,
                $clientInfo
            );
            return $this->return->setData($data)->returnOK();
        }

        Yii::info(['user_id' => Yii::$app->user->id,'appMarket' => $user['appMarket'],'type' => 'contact_info','status' => 'fail','msg' => 'server is too busy'],'auth_info');
        return UserExceptionExt::throwCodeAndMsgExt('server is too busy');
    }

    /**
     * @name UserController 上传用户数据信息 [user/upload-contents]
     * @method post
     * @param int type 3:通讯录 4:app
     * @param string data
     * @return array
     */
    public function actionUploadContents(): array
    {
        $validateModel = new UserContentForm();

        if (!$validateModel->load(Yii::$app->request->post(), '')) {
            return $this->return
                ->setData([])
                ->returnFailed(ErrorCode::ERROR_COMMON());
        }

        $key = 'upload_contents_'.Yii::$app->user->id.'_'.$validateModel->type;
        if(!RedisQueue::lock($key,3600 * 12)){
            return $this->return->setData([])->returnOK();
        }

        $validateModel->params = $this->getClientInfo();
        $validateModel->user_id = Yii::$app->user->id;
        $validateModel->merchant_id = Yii::$app->user->identity->merchant_id;
        if (!$validateModel->validate()) {
            return $this->return
                ->setData([])
                ->returnFailed(
                    ErrorCode::ERROR_COMMON(),
                    $validateModel->getErrorSummary(false)[0]);
        }

        $service = new MgUserContentService();
        if ($service->saveMgUserContentByFormToR(new UserContentType(intval($validateModel->type)), $validateModel)) {
            return $this->return->setData([])->returnOK();
        }

        return UserExceptionExt::throwCodeAndMsgExt('server is too busy');
    }

    /**
     * @name UserController 上传用户相册信息 [user/upload-metadata]
     * @method post
     * @param string data
     * @return array
     */
    public function actionUploadMetadata(): array
    {
        $key = Yii::$app->user->id.'_'.date('Y-m-d');
        $time = strtotime(date('Y-m-d')) + 86400;
        if(!RedisQueue::lock($key,$time - time())){
            return $this->return->setData([])->returnOK();
        }
        $validateModel = new UserPhotoForm();

        if (!$validateModel->load(Yii::$app->request->post(), '')) {
            return $this->return
                ->setData([])
                ->returnFailed(ErrorCode::ERROR_COMMON());
        }

        $validateModel->content = json_decode($validateModel->content, true);
        $validateModel->user_id = Yii::$app->user->id;
        $validateModel->merchant_id = Yii::$app->user->identity->merchant_id;
        $validateModel->date = date('Y-m-d');
        if (!$validateModel->validate()) {
            return $this->return
                ->setData([])
                ->returnFailed(
                    ErrorCode::ERROR_COMMON(),
                    $validateModel->getErrorSummary(false)[0]);
        }

        $service = new FileStorageService();
        $url = $service->uploadFilePhoto(
            'india/user_photo',
            json_encode($validateModel->toArray())
        );

        $model = new UserPhotoUrl();
        $model->user_id = Yii::$app->user->id;
        $model->url = $url;
        if($model->save()){
            return $this->return->setData([])->returnOK();
        }

        return UserExceptionExt::throwCodeAndMsgExt('server is too busy');
    }

    /**
     * @name UserController 获取用户KYC配置 [user/get-user-kyc-config]
     * @method get
     * @return array
     */
    public function actionGetUserKycConfig(): array
    {
        $user = UserRegisterInfo::findOne(['user_id' => Yii::$app->user->id]);
        Yii::info(['user_id' => Yii::$app->user->id,'appMarket' => $user['appMarket'],'type' => 'get_kyc_info','status' => 'success','msg' => 'success'],'auth_info');
        //与客户端约定，阈值为double
        return $this->return->setData([
            'aadhaarOCR'  => true,
            'aadhaarEKYC' => false,
            'fr'          => 0.98,
        ])->returnOK();
    }

    /**
     * @name UserController 保存用户人脸数据 [user/save-user-fr]
     * @method post
     * @param File frPic 人脸照片（静默检测框内图片）
     * @param File frData 人脸数据（liveness data）
     * @param string sign 规则：md5(md5(frPic) + 'loan' + md5(frData))
     * @return array
     */
    public function actionSaveUserFr(): array
    {
        return $this->return->returnFailed(ErrorCode::ERROR_COMMON(),
            'system busy');
        $validateModel = new UserFrForm();

        $user = UserRegisterInfo::findOne(['user_id' => Yii::$app->user->id]);
        if (!$validateModel->load(Yii::$app->request->post(), '')) {
            Yii::info(['user_id' => Yii::$app->user->id,'appMarket' => $user['appMarket'],'type' => 'fr_info','status' => 'fail','msg' => 'fail'],'auth_info');
            return $this->return
                ->setData([])
                ->returnFailed(ErrorCode::ERROR_COMMON());
        }

        $validateModel->frPic = UploadedFile::getInstanceByName('frPic');
        $validateModel->frData = UploadedFile::getInstanceByName('frData');

        $validateModel->params = json_encode($this->getClientInfo(), JSON_UNESCAPED_UNICODE);

        if (!$validateModel->validate()) {
            Yii::info(['user_id' => Yii::$app->user->id,'appMarket' => $user['appMarket'],'type' => 'fr_info','status' => 'fail','msg' => $validateModel->getErrorSummary(false)[0]],'auth_info');
            return $this->return
                ->setData([])
                ->returnFailed(
                    ErrorCode::ERROR_COMMON(),
                    $validateModel->getErrorSummary(false)[0]);
        }

        $creditechService = new UserKYCService();
        if (!$creditechService->saveUserFrByForm($validateModel, Yii::$app->user->id)) {
            Yii::info(['user_id' => Yii::$app->user->id,'appMarket' => $user['appMarket'],'type' => 'fr_info','status' => 'fail','msg' => $creditechService->getError()],'auth_info');
            return $this->return
                ->returnFailed(
                    ErrorCode::ERROR_COMMON(),
                    $creditechService->getError());
        }
        Yii::info(['user_id' => Yii::$app->user->id,'appMarket' => $user['appMarket'],'type' => 'fr_info','status' => 'success','msg' => 'success'],'auth_info');
        return $this->return->setData($creditechService->getResult())->returnOK();
    }

    /**
     * @name UserController 保存用户人脸复借数据 [user/save-user-fr-second]
     * @method post
     * @param File reportId 人脸照片报告
     * @return array
     */
    public function actionSaveUserFrSecond(): array
    {
        return $this->return->returnFailed(ErrorCode::ERROR_COMMON(),
            'system busy');
        $user = UserRegisterInfo::findOne(['user_id' => Yii::$app->user->id]);
        $validateModel = new UserFrSecForm();
        if (!$validateModel->load(Yii::$app->request->post(), '')) {
            Yii::info(['user_id' => Yii::$app->user->id,'appMarket' => $user['appMarket'],'type' => 'old_fr_ver_info','status' => 'fail','msg' => 'fail'],'auth_info');
            return $this->return
                ->setData([])
                ->returnFailed(ErrorCode::ERROR_COMMON());
        }
        $clientInfo = $this->getClientInfo();
        $validateModel->params = json_encode($clientInfo, JSON_UNESCAPED_UNICODE);

        $creditechService = new UserKYCService();
        if (!$creditechService->saveUserFrSecond($validateModel, Yii::$app->user->id)) {
            Yii::info(['user_id' => Yii::$app->user->id,'appMarket' => $user['appMarket'],'type' => 'old_fr_ver_info','status' => 'fail','msg' => $creditechService->getError()],'auth_info');
            return $this->return
                ->returnFailed(
                    ErrorCode::ERROR_COMMON(),
                    $creditechService->getError());
        }

        Yii::info(['user_id' => Yii::$app->user->id,'appMarket' => $user['appMarket'],'type' => 'old_fr_ver_info','status' => 'success','msg' => 'success'],'auth_info');
        //认证状态为成功
        $verificationService = new UserVerificationService(Yii::$app->user->id);
        $data = $verificationService->getNextVerificationItemPath(
            VerificationItem::IDENTITY(),
            Yii::$app->request->hostInfo,
            $clientInfo
        );
        return $this->return->setData($data)->returnOK();
    }

    /**
     * @name UserController 保存用户Pan卡数据-OCR [user/save-user-pan]
     * @method post
     * @param File panPic pan卡照(<=5M)
     * @return array
     */
    public function actionSaveUserPan(): array
    {
        return $this->return->returnFailed(ErrorCode::ERROR_COMMON(),
            'system busy');
        $validateModel = new UserPanForm();

        $validateModel->panPic = UploadedFile::getInstanceByName('panPic');
        $validateModel->params = json_encode($this->getClientInfo(), JSON_UNESCAPED_UNICODE);
        $user = UserRegisterInfo::findOne(['user_id' => Yii::$app->user->id]);
        if (!$validateModel->validate()) {
            Yii::info(['user_id' => Yii::$app->user->id,'appMarket' => $user['appMarket'],'type' => 'pan_info','status' => 'fail','msg' => $validateModel->getErrorSummary(false)[0]],'auth_info');
            return $this->return
                ->setData([])
                ->returnFailed(
                    ErrorCode::ERROR_COMMON(),
                    $validateModel->getErrorSummary(false)[0]);
        }

        $creditechService = new UserKYCService();
        if (!$creditechService->saveUserPanForOcrByFrom($validateModel, Yii::$app->user->id)) {
            Yii::info(['user_id' => Yii::$app->user->id,'appMarket' => $user['appMarket'],'type' => 'pan_info','status' => 'fail','msg' => $creditechService->getError()],'auth_info');
            return $this->return
                ->returnFailed(
                    ErrorCode::ERROR_COMMON(),
                    $creditechService->getError());
        }
        Yii::info(['user_id' => Yii::$app->user->id,'appMarket' => $user['appMarket'],'type' => 'pan_info','status' => 'success','msg' => 'success'],'auth_info');
        return $this->return->setData($creditechService->getResult())->returnOK();
    }

    /**
     * @name UserController 保存用户Aadhaar卡数据-OCR [user/save-user-aadhaar-ocr]
     * @method post
     * @param File aadhaarPicF aadhaar卡人像面照片(<=5M)
     * @param File aadhaarPicB aadhaar卡背面面照片(<=5M)
     * @return array
     */
    public function actionSaveUserAadhaarOcr(): array
    {
        return $this->return->returnFailed(ErrorCode::ERROR_COMMON(),
            'system busy');
        $validateModel = new UserAadhaarForm();

        $validateModel->aadhaarPicF = UploadedFile::getInstanceByName('aadhaarPicF');
        $validateModel->aadhaarPicB = UploadedFile::getInstanceByName('aadhaarPicB');
        $validateModel->params = json_encode($this->getClientInfo(), JSON_UNESCAPED_UNICODE);

        $user = UserRegisterInfo::findOne(['user_id' => Yii::$app->user->id]);
        if (!$validateModel->validate()) {
            Yii::info(['user_id' => Yii::$app->user->id,'appMarket' => $user['appMarket'],'type' => 'aad_info','status' => 'fail','msg' => $validateModel->getErrorSummary(false)[0]],'auth_info');
            return $this->return
                ->setData([])
                ->returnFailed(
                    ErrorCode::ERROR_COMMON(),
                    $validateModel->getErrorSummary(false)[0]);
        }

        $kycService = new UserKYCService();
        if (!$kycService->saveUserAadhaarForOcrByFrom($validateModel, Yii::$app->user->id)) {
            Yii::info(['user_id' => Yii::$app->user->id,'appMarket' => $user['appMarket'],'type' => 'aad_info','status' => 'fail','msg' => $kycService->getError()],'auth_info');
            return $this->return
                ->returnFailed(
                    ErrorCode::ERROR_COMMON(),
                    $kycService->getError());
        }
        Yii::info(['user_id' => Yii::$app->user->id,'appMarket' => $user['appMarket'],'type' => 'aad_info','status' => 'success','msg' => 'success'],'auth_info');
        return $this->return->setData($kycService->getResult())->returnOK();
    }


    /**
     * @name UserController 保存用户KYC [user/save-user-kyc]
     * @method post
     * @param string panReportId
     * @param string panCode 用户手动修正后的panCode
     * @param string frReportId
     * @param string aadReportId
     * @param string aadhaarType 值范围['ocr','ekyc']
     * @param string crossReportId 交叉对比报告编号（当用户选择aadOCR是第一次保存返回，默认值为字符串空）
     * @return array
     */
    public function actionSaveUserKyc(): array
    {
        $service = new UserKYCService();
        $validateModel = new UserKycForm();
        $service->saveUserKycByForm($validateModel, Yii::$app->user->id);
        $clientInfo = $this->getClientInfo();
        $verificationService = new UserVerificationService(Yii::$app->user->id);
        //认证状态为成功
        $data = $verificationService->getNextVerificationItemPath(
            VerificationItem::IDENTITY(),
            Yii::$app->request->hostInfo,
            $clientInfo
        );
        return $this->return->setData($data)->returnOK();






        
        $clientInfo = $this->getClientInfo();
        $verificationService = new UserVerificationService(Yii::$app->user->id);
        $beforeItemStatus = $verificationService->checkBeforeVerificationItem(VerificationItem::IDENTITY());
        if(!$beforeItemStatus) {
            Yii::info(['user_id' => Yii::$app->user->id, 'url' => Yii::$app->uniqueId],'auth_process');
            return $this->return->returnFailed(ErrorCode::ERROR_COMMON(), 'Exit App and restart login process.');
        }

        $validateModel = new UserKycForm();
        $user = UserRegisterInfo::findOne(['user_id' => Yii::$app->user->id]);

        if (!$validateModel->load(Yii::$app->request->post(), '')) {
            Yii::info(['user_id' => Yii::$app->user->id,'appMarket' => $user['appMarket'],'type' => 'kyc_info','status' => 'fail','msg' => 'fail'],'auth_info');
            return $this->return
                ->setData([])
                ->returnFailed(ErrorCode::ERROR_COMMON());
        }

        $validateModel->params = json_encode($clientInfo, JSON_UNESCAPED_UNICODE);

        if (!$validateModel->validate()) {
            Yii::info(['user_id' => Yii::$app->user->id,'appMarket' => $user['appMarket'],'type' => 'kyc_info','status' => 'fail','msg' => $validateModel->getErrorSummary(false)[0]],'auth_info');
            return $this->return
                ->setData([])
                ->returnFailed(ErrorCode::ERROR_COMMON(), $validateModel->getErrorSummary(false)[0]);
        }

        $service = new UserKYCService();
        if ($service->saveUserKycByForm($validateModel, Yii::$app->user->id)) {
            Yii::info(['user_id' => Yii::$app->user->id, 'appMarket' => $user['appMarket'], 'type' => 'kyc_info', 'status' => 'success', 'msg' => 'success'], 'auth_info');
            //认证状态为成功
            $data = $verificationService->getNextVerificationItemPath(
                VerificationItem::IDENTITY(),
                Yii::$app->request->hostInfo,
                $clientInfo
            );
            return $this->return->setData($data)->returnOK();
        }
        Yii::info(['user_id' => Yii::$app->user->id,'appMarket' => $user['appMarket'],'type' => 'kyc_info','status' => 'fail','msg' => $service->getError()],'auth_info');
        return $this->return->returnFailed(ErrorCode::ERROR_COMMON(), $service->getError());
    }

    /**
     * @name UserController 用户地址证明配置 [user/get-address-proof-config]
     * @method post
     * @param bool showSelectorDefault
     * @param array selectorTypes 1:VOTER_ID 2:PASSPORT 3:DRIVER 4:AADHAAR
     * @return array
     */
    public function actionGetAddressProofConfig(): array
    {
        return $this->return->returnFailed(ErrorCode::ERROR_COMMON(),
            'system busy');
        $user = UserRegisterInfo::findOne(['user_id' => Yii::$app->user->id]);
        //进入地址页面的PV
        Yii::info(['user_id' => Yii::$app->user->id,'appMarket' => $user['appMarket'],'type' => 'get_address_config','status' => 'success','msg' => 'success'],'auth_info');
        return $this->return->setData([
            'showSelectorDefault' => true,
            'selectorTypes'       => [
                AddressProofType::AADHAAR()->getValue(),
            ],
        ])->returnOK();
    }

    /**
     * @name UserController 用户地址证明识别 [user/upload-address-proof-ocr]
     * @method post
     * @param UploadedFile picFront
     * @param UploadedFile picBack
     * @param int addressProofType
     * @return array
     */
    public function actionUploadAddressProofOcr(): array
    {
        return $this->return->returnFailed(ErrorCode::ERROR_COMMON(),
            'system busy');
        $validateModel = new UserAddressProofOcrForm();
        $validateModel->picFront = UploadedFile::getInstanceByName('picFront');
        $validateModel->picBack = UploadedFile::getInstanceByName('picBack');
        $validateModel->params = json_encode($this->getClientInfo(), JSON_UNESCAPED_UNICODE);

        if (!$validateModel->load(Yii::$app->request->post(), '')) {
            return $this->return
                ->setData([])
                ->returnFailed(ErrorCode::ERROR_COMMON());
        }

        if (!$validateModel->validate()) {
            return $this->return
                ->setData([])
                ->returnFailed(ErrorCode::ERROR_COMMON(), $validateModel->getErrorSummary(false)[0]);
        }

        $service = new UserAddressService();
        if ($service->ocrUserAddressProof($validateModel, Yii::$app->user->id)) {
            return $this->return->setData($service->getResult())->returnOK();
        }

        return $this->return->returnFailed(ErrorCode::ERROR_COMMON(), $service->getError());
    }

    /**
     * @name UserController 用户地址证明识别 [user/save-address-proof-report]
     * @method post
     * @param string addressProofReportId
     * @param int addressProofType
     * @return array
     */
    public function actionSaveAddressProofReport(): array
    {
        $clientInfo = $this->getClientInfo();
        $verificationService = new UserVerificationService(Yii::$app->user->id);
        $service = new UserAddressService();
        $validateModel = new UserAddressProofReportForm();
        $validateModel->addressProofType=AddressProofType::AADHAAR()->getValue();
        $service->saveUserAddressProof($validateModel, Yii::$app->user->id);
        //认证状态为成功
        $data = $verificationService->getNextVerificationItemPath(
            VerificationItem::ADDRESS(),
            Yii::$app->request->hostInfo,
            $clientInfo
        );
        return $this->return->setData($data)->returnOK();




        return $this->return->returnFailed(ErrorCode::ERROR_COMMON(),
            'system busy');
        $clientInfo = $this->getClientInfo();
        $verificationService = new UserVerificationService(Yii::$app->user->id);
        $beforeItemStatus = $verificationService->checkBeforeVerificationItem(VerificationItem::ADDRESS(), $clientInfo);
        if(!$beforeItemStatus) {
            Yii::info(['user_id' => Yii::$app->user->id, 'url' => Yii::$app->uniqueId],'auth_process');
            return $this->return->returnFailed(ErrorCode::ERROR_COMMON(), 'Exit App and restart login process.');
        }

        $validateModel = new UserAddressProofReportForm();
        $validateModel->params = json_encode($clientInfo, JSON_UNESCAPED_UNICODE);
        $user = UserRegisterInfo::findOne(['user_id' => Yii::$app->user->id]);
        if (!$validateModel->load(Yii::$app->request->post(), '')) {
            return $this->return
                ->setData([])
                ->returnFailed(ErrorCode::ERROR_COMMON());
        }

        if (!$validateModel->validate()) {
            return $this->return
                ->setData([])
                ->returnFailed(ErrorCode::ERROR_COMMON(), $validateModel->getErrorSummary(false)[0]);
        }

        $service = new UserAddressService();
        if ($service->saveUserAddressProof($validateModel, Yii::$app->user->id)) {
            Yii::info(['user_id' => Yii::$app->user->id, 'appMarket' => $user['appMarket'], 'type' => 'ocr_address_submit', 'status' => 'success', 'msg' => 'success'], 'auth_info');
            //认证状态为成功
            $data = $verificationService->getNextVerificationItemPath(
                VerificationItem::ADDRESS(),
                Yii::$app->request->hostInfo,
                $clientInfo
            );
            return $this->return->setData($data)->returnOK();
        } else {
            Yii::info(['user_id' => Yii::$app->user->id, 'appMarket' => $user['appMarket'], 'type' => 'ocr_address_submit', 'status' => 'fail', 'msg' => 'fail'], 'auth_info');
        }

        return $this->return->returnFailed(ErrorCode::ERROR_COMMON(), $service->getError());
    }

    /**
     * @name UserController 获取重置密码验证码 [user/get-reset-pass-otp]
     * @method post
     * @param string phone 手机号
     * @return array
     * @throws \Exception
     */
    public function actionGetResetPassOtp()
    {
        $validateModel = new GetResetPassOtpForm();
        if ($validateModel->load(Yii::$app->request->post(), '')
            && $validateModel->validate()
        ) {
            $validateModel->packageName = $this->packageName();
            $service = new UserService();
            if ($service->getResetPassCode($validateModel)) {
                return $this->return->returnOK();
            } else {
                return $this->return
                    ->setData([])
                    ->returnFailed(ErrorCode::ERROR_COMMON(),
                        $service->getError());
            }
        } else {
            return $this->return
                ->setData([])
                ->returnFailed(ErrorCode::ERROR_COMMON(),
                    $this->getError($validateModel->getErrorSummary(false)));
        }
    }

    /**
     * @name UserController 重置密码 [user/reset-password]
     * @method post
     * @param string phone 手机号
     * @param string code 验证码
     * @param string password 密码
     * @return array
     * @throws \yii\base\Exception
     */
    public function actionResetPassword()
    {
        $validateModel = new ResetPasswordForm();
        if ($validateModel->load(Yii::$app->request->post(), '')
            && $validateModel->validate()) {
            $service = new UserService();
            $validateModel->packageName = $this->packageName();
            if ($service->resetUserPass($validateModel)) {
                return $this->return->returnOK();
            } else {
                return $this->return
                    ->setData([])
                    ->returnFailed(ErrorCode::ERROR_COMMON(),
                        $service->getError());
            }
        } else {
            return $this->return
                ->setData([])
                ->returnFailed(ErrorCode::ERROR_COMMON(),
                    $this->getError($validateModel->getErrorSummary(false)));
        }
    }

    /**
     * @name UserController 验证码登陆 [user/captcha-login]
     * @method post
     * @param string phone
     * @param string code
     * @return array
     */
    public function actionCaptchaLogin()
    {
        $validateModel = new CaptchaLoginForm();
        if ($validateModel->load(Yii::$app->request->post(), '')
            && $validateModel->validate()
        ) {
            $validateModel->packageName = $this->packageName();
            $validateModel->clientInfo = $this->getClientInfo();
            if ($this->userService->loginByCaptcha($validateModel)) {
//                $check = LoanService::haveOpeningOrderNoExport(Yii::$app->user->id);
//                if (!$check) {
//                    Yii::$app->user->logout();
//                    return $this->return->returnFailed(ErrorCode::ERROR_COMMON());
//                }
                return $this->return->setData($this->userService->getResult())->returnOK();
            } else {
                return $this->return
                    ->setData([])
                    ->returnFailed(ErrorCode::ERROR_COMMON(),
                        $this->userService->getError());
            }
        } else {
            return $this->return
                ->setData([])
                ->returnFailed(ErrorCode::ERROR_COMMON(),
                    $this->getError($validateModel->getErrorSummary(false)));
        }
    }

    /**
     * @name UserController 获取登陆验证码 [user/get-login-captcha]
     * @method post
     * @param string phone 手机号
     * @return array
     * @throws \Exception
     */
    public function actionGetLoginCaptcha()
    {
        $validateModel = new GetLoginCaptchaForm();
        if ($validateModel->load(Yii::$app->request->post(), '')
            && $validateModel->validate()
        ) {
            $service = new UserService();
            $validateModel->packageName = $this->packageName();
            if ($service->getLoginCaptcha($validateModel)) {
                return $this->return->returnOK();
            } else {
                return $this->return
                    ->setData([])
                    ->returnFailed(ErrorCode::ERROR_COMMON(),
                        $service->getError());
            }
        } else {
            return $this->return
                ->setData([])
                ->returnFailed(ErrorCode::ERROR_COMMON(),
                    $this->getError($validateModel->getErrorSummary(false)));
        }
    }


    /**
     * @name UserController 检测手机号是否已注册 [user/phone-is-registered]
     * @method post
     * @param string phone
     * @return array
     */
    public function actionPhoneIsRegistered()
    {
        $validateModel = new PhoneExistForm();
        if ($validateModel->load(Yii::$app->request->post(), '')
            && $validateModel->validate()
        ) {
            $validateModel->packageName = $this->packageName();
            $service = new UserService();
            $clientInfo = $this->getClientInfo();
            $isTrueUser = $this->isTrueUser();
            if (!$isTrueUser) {
                $hit = mt_rand(0,100) == 10;
                Yii::info([
                    'phone'        => $validateModel->phone,
                    'packageName'  => $validateModel->packageName,
                    'isRegistered' => $hit,
                    'clientInfo'   => $clientInfo,
                ], 'phone_is_registered_warning');
            } else {
                $hit = $service->phoneIsRegistered($validateModel);
            }
            Yii::info([
                'phone'        => $validateModel->phone,
                'packageName'  => $validateModel->packageName,
                'isRegistered' => $hit,
                'clientInfo'   => $clientInfo,
            ], 'phone_is_registered');
            return $this->return->setData(['hit' => $hit])->returnOK();
        } else {
            return $this->return
                ->setData([])
                ->returnFailed(ErrorCode::ERROR_COMMON(),
                    $this->getError($validateModel->getErrorSummary(false)));
        }
    }


    /**
     * @name UserController 征信报告-用户信息 [user/credit-report-user-info]
     * @method get
     * @return array
     */
    public function actionCreditReportUserInfo()
    {
        $userId = Yii::$app->user->identity->getId();
        $service = new UserService();
        return $this->return->setData($service->getUserPanInfo($userId))->returnOK();
    }



    /**
     * @name UserController 用户身份信息认证状态 [user/user-identity-auth-status]
     * @method get
     * @return array
     */
    public function actionUserIdentityAuthStatus()
    {
        $service = new UserService();
        $result = $service->getUserIdentityAuthStatus(Yii::$app->user->id);
        return $this->return->setData($result)->returnOK();
    }

    /**
     * @name UserController 获取用户认证问题列表 [user/get-user-question-list]
     * @method get
     * @return array
     */
    public function actionGetUserQuestionList()
    {
        $service = new UserQuestionService();
        $userID = Yii::$app->user->id;
        $clientInfo = $this->getClientInfo();
        $user = UserRegisterInfo::findOne(['user_id' => Yii::$app->user->id]);

        if($service->getRandomQuestion($userID, $clientInfo)) {
            Yii::info(['user_id' => Yii::$app->user->id, 'appMarket' => $user['appMarket'], 'type' => 'language_question_list', 'status' => 'success', 'msg' => 'success'], 'auth_info');
            return $this->return->setData($service->getResult())->returnOK();
        }

        Yii::info(['user_id' => Yii::$app->user->id, 'appMarket' => $user['appMarket'], 'type' => 'language_question_list', 'status' => 'fail', 'msg' => 'fail'], 'auth_info');
        return $this->return->returnFailed(ErrorCode::ERROR_COMMON(), $service->getError());
    }

    /**
     * @name UserController 提交用户认证问题答案 [user/save-user-question-answer]
     * @method post
     * @param array list
     * @param int paperId
     * @param int inPageTime
     * @param int outPageTime
     * @return array
     */
    public function actionSaveUserQuestionAnswer()
    {
        $clientInfo = $this->getClientInfo();
        $verificationService = new UserVerificationService(Yii::$app->user->id);
        $beforeItemStatus = $verificationService->checkBeforeVerificationItem(VerificationItem::LANGUAGE(), $clientInfo);
        if(!$beforeItemStatus) {
            Yii::info(['user_id' => Yii::$app->user->id, 'url' => Yii::$app->uniqueId],'auth_process');
            return $this->return->returnFailed(ErrorCode::ERROR_COMMON(), 'Exit App and restart login process.');
        }

        $validateModel = new UserQuestionForm();
        $validateModel->params = json_encode($clientInfo, JSON_UNESCAPED_UNICODE);
        if (!$validateModel->load(Yii::$app->request->post(), '')) {
            return $this->return->returnFailed(ErrorCode::ERROR_COMMON());
        }
        if (!$validateModel->validate()) {
            return $this->return
                ->returnFailed(ErrorCode::ERROR_COMMON(), $validateModel->getErrorSummary(false)[0]);
        }

        $userID = Yii::$app->user->id;
        $user = UserRegisterInfo::findOne(['user_id' => Yii::$app->user->id]);
        $questionService = new UserQuestionService();
        if($questionService->submitExaminationPaper($validateModel, $userID)) {
            Yii::info(['user_id' => Yii::$app->user->id, 'appMarket' => $user['appMarket'], 'type' => 'language_question_submit', 'status' => 'success', 'msg' => 'success'], 'auth_info');
            $data = $verificationService->getNextVerificationItemPath(
                VerificationItem::LANGUAGE(),
                Yii::$app->request->hostInfo,
                $clientInfo
            );
            return $this->return->setData($data)->returnOK();
        } else {
            Yii::info(['user_id' => Yii::$app->user->id, 'appMarket' => $user['appMarket'], 'type' => 'language_question_submit', 'status' => 'fail', 'msg' => 'fail'], 'auth_info');
        }

        return $this->return->returnFailed(ErrorCode::ERROR_COMMON(), $questionService->getError());
    }


    /**
     * @name UserController 获取个人中心信息 [user/get-personal-center-info]
     * @method get
     * @return array
     */
    public function actionGetPersonalCenterInfo()
    {
        $oPersonalCenterService = new PersonalCenterService(['packageName' => $this->packageName()]);

        $clickHeaderLogin = true;
        if (!Yii::$app->user->isGuest) {
            $userPhone = Yii::$app->user->identity->phone;
            $result = $oPersonalCenterService->getPersonalCenterList(true);
        } else {
            $packageService = new PackageService($this->packageName());
            $currentPackage = $packageService->getPackageSetting();
            //取消根据审核开关判断逻辑
//            if ($currentPackage->is_google_review == PackageSetting::GOOGLE_REVIEW_OPEN) {
            if (false) {
                $clickHeaderLogin = false;
                $userPhone = uniqid('user_');
                $result = $oPersonalCenterService->getPersonalCenterList(true);
            } else {
                $userPhone = '';
                $result = $oPersonalCenterService->getPersonalCenterList();
            }
        }

        return $this->return->setData([
            'clickHeaderLogin' => $clickHeaderLogin,
            'menuList'         => $result,
            'phone'            => $userPhone,
        ])->returnOK();

    }// END actionGetPersonalCenterInfo


    /**
     * @name UserController 根据订单ID获取用户信息 [user/get-user-info]
     * @param int orderId
     * @method post
     */
    public function actionGetUserInfo()
    {
        $oOrderFrom    = new OrderForm();
        $oOrderService = new UserLoanOrderService();

        if ($oOrderFrom->load(Yii::$app->request->post(), '') && $oOrderFrom->validate()) {
            $result = $oOrderService->useOrderIdForUserInfo(Yii::$app->user->identity, $oOrderFrom->orderId);

            if (empty($result)) {
                return $this->return->returnFailed(ErrorCode::ERROR_COMMON());
            }
            return $this->return->setData($result)->returnOK();

        } else {
            // 如果没有传订单ID就默认获取用户最近的一条信息
            $result = $oOrderService->useOrderIdForUserInfo(Yii::$app->user->identity);

            return $this->return->setData($result)->returnOK();
        }

    }// END actionGetUserInfo

    /**
     * @name UserController 用户投诉 [user/save-complaints-records]
     * @param int $problemId
     * @param string $idescription
     * @param string $fileList
     * @param string $contact
     * @method post
     */
    public function actionSaveComplaintsRecords()
    {
        $validateModel = new ApplyComplaintForm();
        $validateModel->userId = Yii::$app->user->identity->getId();
        $service = new UserComplaintService();
        if ($validateModel->load(Yii::$app->request->post(), '') && $validateModel->validate()) {
            $validateModel->userId = Yii::$app->user->identity->getId();
            $result = $service->saveUserComplaintInfo($validateModel);

            if ($result == false) {
                return $this->return->returnFailed(ErrorCode::ERROR_COMMON(), $service->getError());
            }

            return $this->return->setData([])->returnOK();

        } else {
            return $this->return->returnFailed(ErrorCode::ERROR_COMMON());
        }

    }


    /**
     * @name UserController 用户投诉记录 [user/get-complaints-records]
     * @param int $problemId
     * @param string $idescription
     * @param string $fileList
     * @param string $contact
     * @method post
     */
    public function actionGetComplaintsRecords()
    {
        $userId = Yii::$app->user->identity->getId();
        $service = new UserComplaintService();
        $result = $service->getUserComplaintRecord($userId);
        return $this->return->setData($result)->returnOK();
    }

    /**
     * @name UserController 用户投诉记录 [user/get-complaints-problem]
     * @param int $problemId
     * @param string $idescription
     * @param string $fileList
     * @param string $contact
     * @method post
     */
    public function actionGetComplaintsProblem()
    {
        $service = new UserComplaintService();
        $result = $service->getProblems();
        return $this->return->setData($result)->returnOK();
    }
}

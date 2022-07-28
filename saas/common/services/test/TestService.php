<?php

namespace common\services\test;

use common\helpers\CommonHelper;
use common\models\user\LoanPerson;
use common\models\user\UserBankAccount;
use common\models\user\UserVerification;
use common\services\loan\LoanService;
use common\services\user\UserBasicInfoService;
use common\services\user\UserContactService;
use common\services\user\UserKYCService;
use common\services\user\UserWorkInfoService;
use frontend\models\loan\ApplyLoanForm;
use frontend\models\user\UserAadhaarForm;
use frontend\models\user\UserBasicInfoForm;
use frontend\models\user\UserContactForm;
use frontend\models\user\UserKycForm;
use frontend\models\user\UserPanForm;
use frontend\models\user\UserWorkInfoForm;
use Yii;
use common\services\user\UserService;
use common\services\BaseService;
use frontend\models\user\RegisterForm;
use yii\web\UploadedFile;

set_time_limit(0);

class TestService extends BaseService
{
    const BASE64_IMAGE = 'data:image/png;base64,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 FFYx+Nvbzf4fMv/tnrpRtVAAAAAASUVORK5CYII= ';

    public static $sPackageName;
    public static $arrClientInfo;
    public static $nBankId;

    public function __construct(array $data)
    {

        self::$sPackageName  = $data['package_name'];
        self::$arrClientInfo = [
            'clientType'      => 'android',
            'osVersion'       => '9.9.9',
            'appVersion'      => '1.3.6',
            'deviceName'      => 'DEBUG 999',
            'appMarket'       =>  'bigshark_debug',
            'deviceId'        => '',
            'brandName'       => 'DEBUG' ,
            'bundleId'        => 'com.jc.bigshark',
            'longitude'       => '',
            'latitude'        => '',
            'configVersion'   => '1.3.6',
            'szlmQueryId'     => '', //数盟ID
            'screenWidth'     => 720,
            'screenHeight'    => 1344,
            'packageName'     => 'bigshark',
            'googlePushToken' => '',
            'tdBlackbox'      => '',
            'ip'              => '127.0.0.1',
            'clientTime'      => time() * 100
        ];
        self::checkIsImage();

        $_FILES = [
            'aadhaarPicF' => [
                'name'     => 'test.png',
                'type'     => 'image/png',
                'tmp_name' => '/var/code/test.png',
                'error'    => 0,
                'size'     => filesize('/var/code/test.png')
            ],
            'aadhaarPicB' => [
                'name'     => 'test.png',
                'type'     => 'image/png',
                'tmp_name' => '/var/code/test.png',
                'error'    => 0,
                'size'     => filesize('/var/code/test.png')
            ],
            'panPic' => [
                'name'     => 'test.png',
                'type'     => 'image/png',
                'tmp_name' => '/var/code/test.png',
                'error'    => 0,
                'size'     => filesize('/var/code/test.png')
            ],
        ];

    }// END __construct


    /**
     * 生成用户
     * @return array|bool|\common\models\user\LoanPerson|\yii\db\ActiveRecord|null
     */
    public function generateUser()
    {
        try {
            $oUserService = new UserService();
            $oForm        = new RegisterForm();

            $oForm->phone       = '9' . rand(0000000001, 1000000000);
            $oForm->password    = '888888';
            $oForm->clientInfo  = self::$arrClientInfo;
            $oForm->packageName = self::$sPackageName;

            $oUser = $oUserService->registerByPhone($oForm);

            // 避免在生成用户的时候字段验证失败导致抛异常
            if (empty($oUser)) {
                return false;
            } else {
                $this->generateUserInfo($oUser->id);
                $this->generateUserContact($oUser->id);
                $this->generateBankAccount($oUser->id);
                $this->generateUserAadhaarOcr($oUser->id);
                $this->generateUserPan($oUser->id);
                $this->generateUserKyc($oUser->id);
                $this->generateApplyLoan($oUser->id);

                return $oUser->id;
            }
        } catch (\Exception $exception) {
            var_dump('generateUser');
            die;
        }


    }// END generateUser


    /**
     * 生成用户基本信息
     */
    public function generateUserInfo($nId)
    {
        try {
            $arrData['birthday']                    = rand(1800, 2020) . '-' . rand(1, 12) . '-' . rand(1, 29);
            $arrData['fullName']                    = self::generateRandomString(5);
            $arrData['educationId']                 = '3';
            $arrData['industryId']                  = '7';
            $arrData['studentId']                   = '';
            $arrData['maritalStatusId']             = '1';
            $arrData['emailVal']                    = self::generateRandomString(10) . '@gmail.com';
            $arrData['zipCodeVal']                  = '123456';
            $arrData['companyNameVal']              = self::generateRandomString(5);
            $arrData['monthlySalaryVal']            = '';
            $arrData['residentialAddressId']        = 'Punjab';
            $arrData['residentialAddressVal']       = 'Punjab';
            $arrData['residentialDetailAddressVal'] = 'Punjab';
            $arrData['aadhaarPinCode']              = '123456';
            $arrData['aadhaarAddressId']            = '123456';
            $arrData['aadhaarAddressVal']           = '123456';
            $arrData['aadhaarDetailAddressVal']     = '123456';

            $validateModel       = new UserBasicInfoForm();
            $validateModel_work  = new UserWorkInfoForm();

            $validateModel->load($arrData, '');
            $validateModel_work->load($arrData, '');

            $validateModel_work->monthlySalaryVal = CommonHelper::UnitToCents($validateModel_work->monthlySalaryVal);
            $clientInfo = self::$arrClientInfo;
            $validateModel->clientInfo = $validateModel_work->clientInfo = json_encode($clientInfo, JSON_UNESCAPED_UNICODE);

            $service = new UserBasicInfoService();
            $service_work = new UserWorkInfoService();

            $service->saveUserBasicInfoByForm($validateModel, $nId);
            $service_work->saveUserWorkInfoByForm($validateModel_work, $nId);

            // 通过当前创建用户的所有验证项
            $oUserVerification = UserVerification::find()->where(['user_id'=>$nId])->one();

            $oUserVerification->real_verify_status = 1;
            $oUserVerification->real_fr_compare_pan_status = 1;
            $oUserVerification->real_fr_compare_fr_status = 1;
            $oUserVerification->real_basic_status = 1;
            $oUserVerification->real_work_status = 1;
            $oUserVerification->ocr_aadhaar_status = 1;
            $oUserVerification->ocr_pan_status = 1;
            $oUserVerification->real_pan_status = 1;
            $oUserVerification->real_contact_status = 1;
            $oUserVerification->real_language_status = 1;
            $oUserVerification->is_first_loan = 1;

            $oUserVerification->save();

        } catch (\Exception $exception) {
            var_dump('generateUserInfo');
            die;
        }


    }// END generateUserInfo


    /**
     * 添加紧急联系人
     * @param $nId
     */
    public function generateUserContact($nId)
    {
        try {
            $oUser        = LoanPerson::findOne($nId);
            $oUserContact = new UserContactForm();

            $arrData['relativeContactPerson']      = '1';
            $arrData['name']                       = 'Test';
            $arrData['phone']                      = '9' . rand(0000000001, 1000000000);
            $arrData['otherRelativeContactPerson'] = '2';
            $arrData['otherName']                  = 'Test';
            $arrData['otherPhone']                 = '9' . rand(0000000001, 1000000000);
            $arrData['facebookAccount']            = 'test-facebook';
            $arrData['whatsAppAccount']            = 'test-whats';
            $arrData['skypeAccount']               = 'test-skype';

            $oUserContact->load($arrData, '');
            $oUserContact->userPhone = $oUser->phone;
            $oUserContact->clientInfo = json_encode(self::$arrClientInfo, JSON_UNESCAPED_UNICODE);

            $service = new UserContactService();
            $service->saveUserContactByForm($oUserContact, $nId);

        } catch (\Exception $exception) {
            var_dump('generateUserContact');
            die;
        }

    }// END aveUserContact


    /**
     * 生成银行卡数据
     * @param $nId
     */
    public function generateBankAccount($nId)
    {
        try {
            $oUser            = LoanPerson::findOne($nId);
            $oUserBankAccount = new UserBankAccount();

            $arrData['account'] = rand(100000000, 999999999);
            $arrData['ifsc']    = self::generateRandomString(8);

            $oUserBankAccount->user_id = $nId;
            $oUserBankAccount->source_id = $oUser->source_id;
            $oUserBankAccount->source_type = 1;
            $oUserBankAccount->name = $oUser->name;
            $oUserBankAccount->account = $arrData['account'];
            $oUserBankAccount->ifsc = $arrData['ifsc'];
            $oUserBankAccount->main_card = UserBankAccount::MAIN_IS;
            $oUserBankAccount->status = UserBankAccount::STATUS_UNVERIFIED;
            $oUserBankAccount->report_account_name = self::generateRandomString(8);
            $oUserBankAccount->bank_name = self::generateRandomString(5);
            $oUserBankAccount->data = '{}';
            $oUserBankAccount->client_info = json_encode(self::$arrClientInfo,JSON_UNESCAPED_UNICODE);
            $oUserBankAccount->merchant_id = $oUser->merchant_id;

            $oUserBankAccount->save();
            self::$nBankId = $oUserBankAccount->id;

        } catch (\Exception $exception) {
            var_dump('generateBankAccount');
            die;
        }

    }// END generateBankAccount


    /**
     * 生成用户Aadhaar卡数据-OCR
     */
    public function generateUserAadhaarOcr($nId)
    {
        try {
            $validateModel = new UserAadhaarForm();
            self::checkIsImage();

            $validateModel->aadhaarPicF = UploadedFile::getInstanceByName('aadhaarPicF');
            $validateModel->aadhaarPicB = UploadedFile::getInstanceByName('aadhaarPicB');
            $validateModel->params = json_encode(self::$arrClientInfo, JSON_UNESCAPED_UNICODE);

            $creditechService = new UserKYCService();
            $creditechService->saveUserAadhaarForOcrByFrom($validateModel, $nId);

        } catch (\Exception $exception) {
            var_dump('generateUserAadhaarOcr');
            die;
        }

    }// END generateUserAadhaarOcr


    /**
     * 生成用户Pan卡数据-OCR
     */
    public function generateUserPan($nId)
    {
        try {
            $oUserPan = new UserPanForm();
            self::checkIsImage();

            $oUserPan->panPic = UploadedFile::getInstanceByName('panPic');
            $oUserPan->params = json_encode(self::$arrClientInfo, JSON_UNESCAPED_UNICODE);

            $creditechService = new UserKYCService();
            $creditechService->saveUserPanForOcrByFrom($oUserPan, $nId);

        } catch (\Exception $exception) {
            var_dump('generateUserPan');
            die;
        }

    }// END generateUserPan


    /**
     * 保存用户KYC数据
     */
    public function generateUserKyc($nId)
    {
        try {
            $oUserKyc = new UserKycForm();

            $arrData['panReportId']   = '';
            $arrData['panCode']       = 'DHDPA1838R';
            $arrData['frReportId']    = '';
            $arrData['aadReportId']   = '';
            $arrData['aadhaarType']   = 'ocr';
            $arrData['crossReportId'] = '';

            $oUserKyc->load($arrData, '');

            $oUserKyc->params = json_encode(self::$arrClientInfo, JSON_UNESCAPED_UNICODE);

            $service = new UserKYCService();
            $service->saveUserKycByForm($oUserKyc, $nId);

        } catch (\Exception $exception) {
            var_dump('generateUserKyc');
            die;
        }

    }// END generateUserKyc


    /**
     * 生成订单
     */
    public function generateApplyLoan($nId)
    {
        try {
            $oApplyLoanForm = new ApplyLoanForm();

            $arrData = [
                'amount'     => rand(100, 1000),
                'days'       => rand(10, 30),
                'productId'  => 4,
                'bankCardId' => self::$nBankId,
                'blackbox'   => '',
            ];

            $data = array_merge($arrData,[
                'userId'      => $nId,
                'clientInfo'  => self::$arrClientInfo,
                'packageName' => self::$sPackageName
            ]);

            $oApplyLoanForm->load($data, '');
            $oApplyLoanForm->validate();
            $oLoanService = new LoanService();
            $oLoanService->applyLoan($oApplyLoanForm);

        } catch (\Exception $exception) {
            var_dump('generateApplyLoan');
            die;
        }

    }// END generateApplyLoan


    /**
     * 随机生成字符串
     * @param int $length
     * @return string
     */
    public static function generateRandomString($length = 10)
    {
        $sCharacters   = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $sRandomString = '';
        for ($i = 0; $i < $length; $i++) {
            $sRandomString .= $sCharacters[rand(0, strlen($sCharacters) - 1)];
        }
        return $sRandomString;

    }// END generateRandomString


    /**
     * 检测测试图片是否存在，不存在则创建
     */
    public static function checkIsImage()
    {
        if (!file_exists('/var/code/test.png')) {
            self::base64IsConvertedToImages(self::BASE64_IMAGE, '/var/code', 'test.png');
        }

    }// END checkIsImage


    /**
     * 将Base64图片转换为图片文件
     * @param $base64_image_content
     * @param $path
     * @return bool|string
     */
    public static function base64IsConvertedToImages($base64_image_content,$path, $sFileName)
    {
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image_content, $result)){
            $new_file = $path."/";
            if(!file_exists($new_file)){
                //检查是否有该文件夹，如果没有就创建，并给予最高权限
                mkdir($new_file, 0700);
            }
            $new_file = $new_file . $sFileName;
            if (file_put_contents($new_file, base64_decode(str_replace($result[1], '', $base64_image_content)))){
                return true;
            }else{
                return false;
            }
        } else {
            return false;
        }

    }// END base64IsConvertedToImages

}// END CLASS
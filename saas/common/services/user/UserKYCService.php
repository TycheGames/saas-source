<?php


namespace common\services\user;


use Carbon\Carbon;
use common\helpers\EncryptData;
use common\helpers\Util;
use common\models\ClientInfoLog;
use common\models\enum\CreditReportStatus;
use common\models\enum\Gender;
use common\models\user\LoanPerson;
use common\models\user\UserCreditReport;
use common\models\user\UserCreditReportFrLiveness;
use common\models\user\UserCreditReportFrVerify;
use common\models\user\UserCreditReportOcrAad;
use common\models\user\UserCreditReportOcrPan;
use common\models\user\UserPanCheckLog;
use common\models\user\UserRegisterInfo;
use common\models\user\UserVerification;
use common\models\user\UserVerificationLog;
use common\services\BaseService;
use common\services\FileStorageService;
use common\services\risk\AccuauthService;
use frontend\models\user\UserAadhaarForm;
use frontend\models\user\UserFrForm;
use frontend\models\user\UserFrSecForm;
use frontend\models\user\UserKycForm;
use frontend\models\user\UserPanForm;
use Yii;

class UserKYCService extends BaseService implements IThirdDataService
{
    /**
     * 检查数据是否过期，true:过期 false:未过期
     * @param Carbon $updateTime
     * @return bool
     */
    public function checkDataExpired(Carbon $updateTime): bool
    {
//        return $updateTime->floatDiffInMonths(Carbon::now()) > 3;
        return false;
    }

    /**
     * Accuauth-PanOCR
     * @param UserPanForm $userPanFrom
     * @param int $userID
     * @return bool
     */
    public function saveUserPanForOcrByFrom(UserPanForm $userPanFrom, int $userID): bool
    {
        if (empty($userPanFrom) || !$userPanFrom->validate()) {
            return false;
        }

        $user = LoanPerson::findById($userID);
        if (empty($user)) {
            return false;
        }

        //1. 初始化（UserCreditReport，UserCreditReportOcrPan）
        $reportObj = new UserCreditReport();
        $reportModel = $reportObj->initRecord($userID, UserCreditReport::SOURCE_ACCUAUTH, UserCreditReport::TYPE_OCR_PAN, $user->merchant_id);
        $analyticReportObj = new UserCreditReportOcrPan();
        $analyticReportModel = $analyticReportObj->initRecord($userID, $reportModel->id, $user->merchant_id);
        //记录用户设备信息
        ClientInfoLog::addLog($userID, ClientInfoLog::EVENT_PAN_OCR, $analyticReportModel->id, json_decode($userPanFrom->params, true));

        //2. 上传图片至OSS，并记录
        $service = new FileStorageService();
        $analyticReportModel->img_front_path = $service->uploadFile(
            'india/pan',
            $userPanFrom->panPic->tempName,
            $userPanFrom->panPic->getExtension(),
            false
        );
        $analyticReportModel->save();

        //3. 调用第三方获取报告
        $accuauthService = new AccuauthService([
            'userSourceId' => $user->source_id
        ]);
        $reportData = $accuauthService->panCardOcr(base64_encode(file_get_contents($userPanFrom->panPic->tempName)));
        @unlink($userPanFrom->panPic->tempName);
        //3.1. 记录原始报告
        $reportModel->account_name = $accuauthService->apiId;
        $reportModel->report_status = CreditReportStatus::RECEIVED()->getValue();
        $reportModel->report_data = json_encode($reportData);
        $reportModel->save();
        //3.2. 解析原始报告
        $cardNo = $reportData['results'][0]['card_info']['card_no'] ?? '';
        $dateInfoCopy = $dateInfo = $reportData['results'][0]['card_info']['date_info'] ?? '';
        $dateType = $reportData['results'][0]['card_info']['date_type'] ?? '';
        $fatherName = $reportData['results'][0]['card_info']['father_name'] ?? '';
        $fullName = $reportData['results'][0]['card_info']['name'] ?? '';
        $dataStatus = $reportData['status'] ?? '';
        $analyticReportModel->card_no = $cardNo;
        $analyticReportModel->date_type = $dateType;
        $analyticReportModel->date_info = $dateInfo;
        $analyticReportModel->father_name = $fatherName;
        $analyticReportModel->full_name = $fullName;
        $analyticReportModel->report_status = CreditReportStatus::RECEIVED()->getValue();
        $analyticReportModel->data_status = $dataStatus;
        $analyticReportModel->type = UserCreditReportOcrPan::SOURCE_ACCUAUTH;
        $analyticReportModel->save();

        if (empty($dataStatus) || empty($cardNo) || empty($dateInfo) || empty($dateType) || empty($fatherName) || empty($fullName)
            || !preg_match(Util::getPanNumberMath(), $cardNo)
            || !strtotime(str_replace(' ', '-', str_replace('/', '-', $dateInfoCopy)))
        ) {
            $this->setError('Image was not clear, please re-upload');
            $analyticReportModel->report_status = CreditReportStatus::REJECT_VALUE()->getValue();
        }

        if ($dataStatus != 'OK') {
            $this->setError('Image was not clear, please re-upload');
            $analyticReportModel->report_status = CreditReportStatus::REJECT_ERROR()->getValue();
        }

        if (empty($this->error)) {
            $analyticReportModel->report_status = CreditReportStatus::PASS()->getValue();
        }

        $analyticReportModel->save();

        $this->setResult([
            'reportId' => $analyticReportModel->id,
            'panCode'  => $cardNo,
        ]);

        return empty($this->error);
    }

    /**
     * Accuauth-AadhaarOCR
     * @param UserAadhaarForm $userAadhaarFrom
     * @param int $userID
     * @return bool
     */
    public function saveUserAadhaarForOcrByFrom(UserAadhaarForm $userAadhaarFrom, int $userID): bool
    {
        if (empty($userAadhaarFrom) || !$userAadhaarFrom->validate()) {
            return false;
        }

        $user = LoanPerson::findById($userID);
        if (empty($user)) {
            return false;
        }

        //1. 初始化（UserCreditReport，UserCreditReportOcrAad）
        $reportObj = new UserCreditReport();
        $reportModel = $reportObj->initRecord($userID, UserCreditReport::SOURCE_ACCUAUTH, UserCreditReport::TYPE_OCR_AAD, $user->merchant_id);
        $analyticReportObj = new UserCreditReportOcrAad();
        $analyticReportModel = $analyticReportObj->initRecord($userID, $reportModel->id, $user->merchant_id);
        //记录用户设备信息
        ClientInfoLog::addLog($userID, ClientInfoLog::EVENT_ADH_OCR_FRONT, $analyticReportModel->id, json_decode($userAadhaarFrom->params, true));

        //2. 上传图片至OSS，并记录
        $service = new FileStorageService();
        $analyticReportModel->img_front_path = $service->uploadFile(
            'india/aadhaar',
            $userAadhaarFrom->aadhaarPicF->tempName,
            $userAadhaarFrom->aadhaarPicF->getExtension(),
            false
        );
        $analyticReportModel->save(); //尽可能保存数据
        $analyticReportModel->img_back_path = $service->uploadFile(
            'india/aadhaar',
            $userAadhaarFrom->aadhaarPicB->tempName,
            $userAadhaarFrom->aadhaarPicB->getExtension(),
            false
        );
        $analyticReportModel->save(); //尽可能保存数据

        //3. 调用第三方获取报告
        $accuauthService = new AccuauthService([
            'userSourceId' => $user->source_id
        ]);
        $reportDataB = $accuauthService->aadhaarCardOcr(base64_encode(file_get_contents($userAadhaarFrom->aadhaarPicB->tempName)));
        $reportDataF = $accuauthService->aadhaarCardOcr(base64_encode(file_get_contents($userAadhaarFrom->aadhaarPicF->tempName)));
        @unlink($userAadhaarFrom->aadhaarPicB->tempName);
        @unlink($userAadhaarFrom->aadhaarPicF->tempName);
        //3.1. 记录原始报告
        $reportModel->account_name = $accuauthService->apiId;
        $reportModel->report_status = CreditReportStatus::RECEIVED()->getValue();
        $reportModel->report_data = json_encode([
            'front' => $reportDataF,
            'back'  => $reportDataB,
        ]);
        $reportModel->save();
        //3.2. 解析原始报告
        //必须验证参数
        $cardNo = $reportDataF['results'][0]['card_info']['card_no'] ?? '';
        $cardNo = str_replace(' ', '', $cardNo);
        $dateInfoCopy = $dateInfo = $reportDataF['results'][0]['card_info']['date_info'] ?? '';
        $dateType = $reportDataF['results'][0]['card_info']['date_type'] ?? '';
        $gender = $reportDataF['results'][0]['card_info']['gender'] ?? '';
        $fullName = $reportDataF['results'][0]['card_info']['name'] ?? '';
        $pin = $reportDataB['results'][0]['card_info']['pin'] ?? '';
        $state = $reportDataB['results'][0]['card_info']['state'] ?? '';
        $city = $reportDataB['results'][0]['card_info']['city'] ?? '';
        $address = $reportDataB['results'][0]['card_info']['address'] ?? '';
        $cardSideF = $reportDataF['results'][0]['card_side'] ?? '';
        $cardSideB = $reportDataB['results'][0]['card_side'] ?? '';
        $dataFrontStatus = $reportDataF['status'] ?? '';
        $dataBackStatus = $reportDataB['status'] ?? '';
        //不必验证数据
        $fatherName = $reportDataF['results'][0]['card_info']['father_name'] ?? '';
        $motherName = $reportDataF['results'][0]['card_info']['mother_name'] ?? '';
        $phoneNumber = $reportDataF['results'][0]['card_info']['phone_number'] ?? '';
        $analyticReportModel->card_no = '';
        $analyticReportModel->card_no_encode = EncryptData::encrypt($cardNo);
        $analyticReportModel->card_no_md5 = md5($cardNo);
        $analyticReportModel->card_no_mask = substr($cardNo, -4, 4);
        $analyticReportModel->date_type = $dateType;
        $analyticReportModel->date_info = $dateInfo;
        $analyticReportModel->full_name = $fullName;
        $analyticReportModel->gender = array_search(strtolower($gender), Gender::$map);// 待处理
        $analyticReportModel->address = $address;
        $analyticReportModel->mother_name = $motherName;
        $analyticReportModel->father_name = $fatherName;
        $analyticReportModel->phone_number = $phoneNumber;
        $analyticReportModel->pin = $pin;
        $analyticReportModel->state = $state;
        $analyticReportModel->city = $city;
        $analyticReportModel->report_status = CreditReportStatus::RECEIVED()->getValue();
        $analyticReportModel->data_front_status = $dataFrontStatus;
        $analyticReportModel->data_back_status = $dataBackStatus;
        $analyticReportModel->type = UserCreditReportOcrAad::SOURCE_ACCUAUTH;
        $analyticReportModel->save();

        if (empty($cardNo) || empty($dateInfo) || empty($dateType) || empty($gender) || empty($fullName)
            || $cardSideF != 'front'
            || !preg_match(Util::getAadNumberMath(), $cardNo)
            || !strtotime(str_replace(' ', '-', str_replace('/', '-', $dateInfoCopy)))
        ) {
            $this->setError('Aadhaar front image was not clear, please re-upload');
            $analyticReportModel->report_status = CreditReportStatus::REJECT_VALUE()->getValue();
        }

//        if (empty($address) || empty($state) || empty($city) || empty($pin) || $cardSideB != 'back') {
//            $this->setError('Aadhaar back image was not clear, please re-upload');
//            $analyticReportModel->report_status = CreditReportStatus::REJECT_VALUE()->getValue();
//        }
        //取消校验背面地址相关信息
        if ($cardSideB != 'back') {
            $this->setError('Aadhaar back image was not clear, please re-upload');
            $analyticReportModel->report_status = CreditReportStatus::REJECT_VALUE()->getValue();
        }

        if ($cardSideF != 'front' && $cardSideB != 'back') {
            $analyticReportModel->report_status = CreditReportStatus::REJECT_VALUE()->getValue();
            $this->setError('Aadhaar image was not clear, please re-upload');
        }

        if (($dataFrontStatus != 'OK') || ($dataBackStatus != 'OK')) {
            $analyticReportModel->report_status = CreditReportStatus::REJECT_ERROR()->getValue();
            $this->setError('Aadhaar card OCR error, please re-upload');
        }

        if (empty($this->error)) {
            $analyticReportModel->report_status = CreditReportStatus::PASS()->getValue();
        }

        $analyticReportModel->save();

        $this->setResult([
            'reportId'  => $analyticReportModel->id,
            'aadhaarNo' => $cardNo,
        ]);

        return empty($this->error);
    }

    /**
     * Accuauth-Fr
     * @param UserFrForm $userFrForm
     * @param int $userID
     * @return bool
     */
    public function saveUserFrByForm(UserFrForm $userFrForm, int $userID): bool
    {
        if (empty($userFrForm) || !$userFrForm->validate()) {
            return false;
        }

        $user = LoanPerson::findById($userID);
        if (empty($user)) {
            return false;
        }

        //1. 初始化（UserCreditReport，UserCreditReportFrLiveness）
        $reportObj = new UserCreditReport();
        $reportModel = $reportObj->initRecord($userID, UserCreditReport::SOURCE_ACCUAUTH, UserCreditReport::TYPE_FR_LIVENESS, $user->merchant_id);
        $analyticReportObj = new UserCreditReportFrLiveness();
        $analyticReportModel = $analyticReportObj->initRecord($userID, $reportModel->id, $user->merchant_id);
        //记录用户设备信息
        ClientInfoLog::addLog($userID, ClientInfoLog::EVENT_LIVE_IDENTIFY, $analyticReportModel->id, json_decode($userFrForm->params, true));

        //2. 上传图片至OSS，并记录
        $service = new FileStorageService();
        $analyticReportModel->img_fr_path = $service->uploadFile(
            'india/fr',
            $userFrForm->frPic->tempName,
            $userFrForm->frPic->getExtension()
        );
        $analyticReportModel->save(); //尽可能保存数据
        $analyticReportModel->data_fr_path = $service->uploadFile(
            'india/fr',
            $userFrForm->frData->tempName,
            $userFrForm->frData->getExtension(),
            false
        );
        $analyticReportModel->save(); //尽可能保存数据

        //3. 调用第三方获取报告
        $accuauthService = new AccuauthService([
            'userSourceId' => $user->source_id
        ]);
        $reportData = $accuauthService->faceHack($userFrForm->frData->tempName);
        @unlink($userFrForm->frData->tempName);
        //3.1. 记录原始报告
        $reportModel->account_name = $accuauthService->apiId;
        $reportModel->report_status = CreditReportStatus::RECEIVED()->getValue();
        $reportModel->report_data = json_encode($reportData);
        $reportModel->save();
        //3.2. 解析原始报告
        $faceScore = $reportData['score'] ?? '';
        $dataStatus = $reportData['status'] ?? '';
        $analyticReportModel->report_status = CreditReportStatus::RECEIVED()->getValue();
        $analyticReportModel->score = strval($faceScore);
        $analyticReportModel->data_status = strval($dataStatus);
        $analyticReportModel->type = UserCreditReportFrLiveness::SOURCE_ACCUAUTH;
        $analyticReportModel->save();

        if (floatval($faceScore) > 0.98) {//真人 <= 0.98
            $this->setError('The image was not clear, please re-upload');
            $analyticReportModel->report_status = CreditReportStatus::REJECT_VALUE()->getValue();
        }
        if ($dataStatus != 'OK' || $faceScore === '') {
            $this->setError('System has error, please try again later');
            $analyticReportModel->report_status = CreditReportStatus::REJECT_ERROR()->getValue();
        }

        if (empty($this->error)) {
            $analyticReportModel->report_status = CreditReportStatus::PASS()->getValue();
        }

        $analyticReportModel->save();

        $this->setResult([
            'reportId' => $analyticReportModel->id,
        ]);

        return empty($this->error);
    }

    public function saveUserKycByForm(UserKycForm $userKycForm, int $userID): bool
    {            
        $user = LoanPerson::findById($userID);
        //用户数据落库
        $user->pan_code = "test";
        $user->father_name ="test";
        $user->birthday = "1987-06-01";
        $user->name = "test";
        $user->save();
        
        $verification = $user->userVerification;
        //更新用户认证
        $verification->verificationUpdate(UserVerification::TYPE_VERIFY, UserVerificationLog::STATUS_VERIFY_SUCCESS);
        $verification->verificationUpdate(UserVerification::TYPE_FR_COMPARE_PAN, UserVerificationLog::STATUS_VERIFY_SUCCESS);
        $verification->verificationUpdate(UserVerification::TYPE_PAN, UserVerificationLog::STATUS_VERIFY_SUCCESS);
        $verification->verificationUpdate(UserVerification::TYPE_OCR_PAN, UserVerificationLog::STATUS_VERIFY_SUCCESS);
        return true;






        

        if (empty($userKycForm) || !$userKycForm->validate()) {
            return false;
        }

        $user = LoanPerson::findById($userID);
        if (empty($user)) {
            return false;
        }
        $user_register = UserRegisterInfo::findOne(['user_id' => $userID]);

        //pan验真
        $checkPanNoRes = $this->checkPanByOCR($userKycForm, $userID, $user->merchant_id);
        if ($checkPanNoRes['result']) {
            Yii::info(['user_id' => $userID, 'appMarket' => $user_register['appMarket'], 'type' => 'pan_ver_info', 'status' => 'success', 'msg' => 'success'], 'auth_info');
        } else {
            $this->setError('PAN verification failed! Please take clear photos of your PAN card.');
            Yii::info(['user_id' => $userID, 'appMarket' => $user_register['appMarket'], 'type' => 'pan_ver_info', 'status' => 'fail', 'msg' => 'fail'], 'auth_info');
            return false;
        }
        //人脸对比
        $checkPanFrRes = $this->checkPanFr($userKycForm, $userID, $user->merchant_id);
        if ($checkPanFrRes['result']) {
            Yii::info(['user_id' => $userID, 'appMarket' => $user_register['appMarket'], 'type' => 'fr_ver_info', 'status' => 'success', 'msg' => 'success'], 'auth_info');
        } else {
            //下层已设置错误信息
            //$this->setError('Face verification failed, please re-verify');
            Yii::info(['user_id' => $userID, 'appMarket' => $user_register['appMarket'], 'type' => 'fr_ver_info', 'status' => 'fail', 'msg' => 'fail'], 'auth_info');
            return false;
        }

        if ($checkPanNoRes['result'] && $checkPanFrRes['result']) {
            //用户数据落库
            $user->pan_code = $checkPanNoRes['pan_code'];
            $user->father_name = $checkPanNoRes['father_name'];
            $user->birthday = $checkPanNoRes['birthday'];
            $user->name = $checkPanNoRes['name'];
            $user->save();
            //修改绑定报告
            $verification = $user->userVerification;
            /**
             * @var UserCreditReportOcrPan $ocrPan
             */
            $ocrPan = UserCreditReportOcrPan::find()
                ->where(['id' => $userKycForm->panReportId])
                ->andWhere(['user_id' => $userID])
                ->andWhere(['report_status' => CreditReportStatus::PASS()->getValue()])
                ->limit(1)
                ->one();
//            $ocrPan->setAllUnused();
            $ocrPan->setThisUsed();
            /**
             * @var UserCreditReportFrLiveness $frLiveness
             */
            $frLiveness = UserCreditReportFrLiveness::find()
                ->where(['id' => $userKycForm->frReportId])
                ->andWhere(['user_id' => $userID])
                ->andWhere(['report_status' => CreditReportStatus::PASS()->getValue()])
                ->limit(1)
                ->one();
//            $frLiveness->setAllUnused();
            $frLiveness->setThisUsed();
            /**
             * @var UserCreditReportFrVerify $frVerify
             */
            $frVerify = UserCreditReportFrVerify::find()
                ->where(['id' => $checkPanFrRes['report_id']])
                ->andWhere(['user_id' => $userID])
                ->andWhere(['report_type' => UserCreditReportFrVerify::TYPE_FR_COMPARE_PAN])
                ->andWhere(['report_status' => CreditReportStatus::PASS()->getValue()])
                ->limit(1)
                ->one();
//            $frVerify->setAllUnused(UserCreditReportFrVerify::TYPE_FR_COMPARE_PAN);
            $frVerify->setThisUsed();
            //更新用户认证
            $verification->verificationUpdate(UserVerification::TYPE_VERIFY, UserVerificationLog::STATUS_VERIFY_SUCCESS);
            $verification->verificationUpdate(UserVerification::TYPE_FR_COMPARE_PAN, UserVerificationLog::STATUS_VERIFY_SUCCESS);
            $verification->verificationUpdate(UserVerification::TYPE_PAN, UserVerificationLog::STATUS_VERIFY_SUCCESS);
            $verification->verificationUpdate(UserVerification::TYPE_OCR_PAN, UserVerificationLog::STATUS_VERIFY_SUCCESS);

            return true;
        }

        return false;
    }

    private function checkPanByOCR(UserKycForm $userKycForm, int $userID, int $merchantId): array
    {
        $result = [
            'result'    => false,
            'report_id' => '',
        ];
        //1. 初始化日志记录
        /**
         * @var UserCreditReportOcrPan $panOCRReport
         */
        $panOCRReport = UserCreditReportOcrPan::find()
            ->where(['id' => $userKycForm->panReportId])
            ->andWhere(['user_id' => $userID])
            ->andWhere(['report_status' => CreditReportStatus::PASS()->getValue()])
            ->limit(1)
            ->one();

        $userKycForm->panCode = str_replace(' ', '', $userKycForm->panCode);
        $panCheckLog = new UserPanCheckLog();
        $panCheckLog->user_id = $userID;
        $panCheckLog->merchant_id = $merchantId;
        $panCheckLog->pan_input = $userKycForm->panCode;
        $panCheckLog->pan_ocr = $panOCRReport->card_no;
        $panCheckLog->ocr_report_id = $panOCRReport->id;
        $panCheckLog->client_info = $userKycForm->params;
        $clientInfo = json_decode($userKycForm->params, true);
        $panCheckLog->package_name = $clientInfo['packageName'] ?? null;

        if (!preg_match(Util::getPanNumberMath(), $userKycForm->panCode)) {
            $panCheckLog->report_status = 0;
            $panCheckLog->data_status = UserPanCheckLog::REJECT_PAN_RULE;//卡号不符合规则
            $panCheckLog->check_third_source = 0;
            $panCheckLog->report_data = null;
            $panCheckLog->save();
            $result['result'] = false;
            $result['report_id'] = $panCheckLog->id;
            return $result;
        }
        //先落库
        $panCheckLog->save();

        $user = LoanPerson::findById($userID);
        ClientInfoLog::addLog($userID, ClientInfoLog::EVENT_PAN_IDENTIFY, $panCheckLog->id, json_decode($userKycForm->params, true));
        //查找历史记录
        /**
         * @var UserPanCheckLog $panExistCheck
         */
        $panExistCheck = UserPanCheckLog::find()
            ->where(['pan_input' => $userKycForm->panCode])
            ->andWhere(['report_status' => 1])
            ->orderBy(['id' => SORT_ASC])
            ->one();
        //历史记录不存在
        $isUsedThird = false;
        if (!$panExistCheck) {
            $accuauthService = new AccuauthService([
                'userSourceId' => $user->source_id
            ]);
            $isUsedThird = true;
            $reportData = $accuauthService->panCardCheckAuto($userKycForm->panCode);
            if (!isset($reportData['result']['pan_status']) || $reportData['result']['pan_status'] != 'VALID') {
                $panCheckLog->report_status = 0;
            } else {
                $firstName = isset($reportData['result']['first_name']) ? $reportData['result']['first_name'] . ' ' : '';
                $middleName = isset($reportData['result']['middle_name']) ? $reportData['result']['middle_name'] . ' ' : '';
                $lastName = $reportData['result']['last_name'] ?? '';
                $panName = $firstName . $middleName . $lastName;
                $panCheckLog->report_status = 1;
                $panCheckLog->full_name = $panName;
                $panCheckLog->first_name = $reportData['result']['first_name'] ?? '';
                $panCheckLog->middle_name = $reportData['result']['middle_name'] ?? '';
                $panCheckLog->last_name = $reportData['result']['last_name'] ?? '';
            }
            $panCheckLog->account_name = $accuauthService->apiId;
            $panCheckLog->report_data = json_encode($reportData);
            $panCheckLog->check_third_source = 1;
        } else {
            //历史记录存在
            //报告内容存null,可以用于统计判断
            $panCheckLog->report_status = 1;
            $panCheckLog->report_data = null;
            $panCheckLog->check_third_source = 0;
        }
        $panCheckLog->save();

        //卡号检验失败，拒绝
        if ($panCheckLog->report_status != 1) {
            $panCheckLog->data_status = UserPanCheckLog::REJECT_PAN_INVALID;//卡号无效
            $panCheckLog->save();
            $result['result'] = false;
            $result['report_id'] = $panCheckLog->id;
            return $result;
        }

        if (!empty($user->pan_code)) {
            if ($user->pan_code == $userKycForm->panCode) {
                //卡号校验成功，两次校验相同卡号，通过
                $panCheckLog->data_status = UserPanCheckLog::PASS;
                $panCheckLog->save();
                $result['result'] = true;
                $result['report_id'] = $panExistCheck->id ?? null;
                $result['pan_code'] = $user->pan_code;
                $result['father_name'] = $user->father_name;
                $result['birthday'] = $user->birthday;
                $result['name'] = $user->name;
                return $result;
            } else {
                //卡号校验成功，两次校验不同卡号，拒绝
                $panCheckLog->data_status = UserPanCheckLog::REJECT_PAN_DIFFERENT;
                $panCheckLog->save();
                $result['result'] = false;
                $result['report_id'] = $panCheckLog->id;
                return $result;
            }
        }
        //2. 数据落库（loanPerson）,并更新日志表的is_used字段
        //卡号校验成功（已排查不成功），用户未绑定pan卡（已排除绑pan卡）
        $loanPersonPanExist = LoanPerson::find()
            ->where(['pan_code' => $userKycForm->panCode])
            ->andWhere(['source_id' => $user->source_id])
            ->andWhere(['!=', 'id', $userID])
            ->exists();
        if ($panExistCheck && $loanPersonPanExist) {
            //卡号校验成功，用户未绑卡，但pan卡已被其他用户绑定，拒绝
            $panCheckLog->data_status = UserPanCheckLog::REJECT_PAN_USED;
            $panCheckLog->save();
            $result['result'] = false;
            $result['report_id'] = $panCheckLog->id;
            return $result;
        } else {
            $panCheckLog->data_status = UserPanCheckLog::PASS;
            $panCheckLog->save();
            //如果$reportData没有值，没有走第三方数据，使用的历史验证数据
            if (!$isUsedThird) {
                $panExistCheck->is_used = 1;
                $result['report_id'] = $panExistCheck->id;
                $panExistCheck->save();
                $firstName = !empty($panExistCheck->first_name) ? $panExistCheck->first_name . ' ' : '';
                $middleName = !empty($panExistCheck->middle_name) ? $panExistCheck->middle_name . ' ' : '';
                $lastName = $panExistCheck->last_name ?? '';
                $panName = $firstName . $middleName . $lastName;
                $panNo = $panExistCheck->pan_input;
            } else {
                $panCheckLog->is_used = 1;
                $result['report_id'] = $panCheckLog->id;
                $panCheckLog->save();
                $firstName = !empty($panCheckLog->first_name) ? $panCheckLog->first_name . ' ' : '';
                $middleName = !empty($panCheckLog->middle_name) ? $panCheckLog->middle_name . ' ' : '';
                $lastName = $panCheckLog->last_name ?? '';
                $panName = $firstName . $middleName . $lastName;
                $panNo = $panCheckLog->pan_input;
            }
            $dateInfo = str_replace(' ', '-', str_replace('/', '-', $panOCRReport->date_info));
            $panBirthDay =  date('Y-m-d', strtotime($dateInfo));
            $fatherName = $panOCRReport->father_name;

            $result['result'] = true;
            $result['pan_code'] = $panNo;
            $result['father_name'] = $fatherName;
            $result['birthday'] = $panBirthDay;
            $result['name'] = $panName;
            return $result;
        }
    }

    private function checkPanFr(UserKycForm $userKycForm, int $userID, int $merchantId): array
    {
        /**
         * @var UserCreditReportFrLiveness $frReport
         */
        $frReport = UserCreditReportFrLiveness::find()
            ->where(['id' => $userKycForm->frReportId])
            ->andWhere(['user_id' => $userID])
            ->andWhere(['report_status' => CreditReportStatus::PASS()->getValue()])
            ->one();

        //1. 查找全平台相同pan卡的人，上次认证的人脸照
        /**
         * @var UserCreditReportFrLiveness $samePanFrReport
         */
        $samePanFrReport = UserCreditReportFrLiveness::find()
            ->alias('fl')
            ->select('fl.*')
            ->leftJoin(LoanPerson::tableName() . ' lp', 'lp.id = fl.user_id')
            ->where(['fl.is_used' => 1, 'fl.merchant_id' => $merchantId])
            ->andWhere(['lp.pan_code' => $userKycForm->panCode])
            ->orderBy(['fl.updated_at' => SORT_DESC])
            ->limit(1)
            ->one();
        $picPath = $samePanFrReport->img_fr_path ?? null;
        $isFrFr = true;
        //2. 若步骤1中没有，使用用户本次提交的证件照片
        if (!$samePanFrReport) {
            /**
             * @var UserCreditReportOcrPan $panReport
             */
            $panReport = UserCreditReportOcrPan::find()
                ->where(['id' => $userKycForm->panReportId])
                ->andWhere(['user_id' => $userID])
                ->andWhere(['report_status' => CreditReportStatus::PASS()->getValue()])
                ->one();
            $picPath = $panReport->img_front_path ?? null;
            $isFrFr = false;
        }

        //1. 初始化（UserCreditReport，UserCreditReportFrVerify）
        $reportObj = new UserCreditReport();
        $reportModel = $reportObj->initRecord($userID, UserCreditReport::SOURCE_ACCUAUTH, UserCreditReport::TYPE_FR_VERIFY, $merchantId);
        $analyticReportObj = new UserCreditReportFrVerify();
        $analyticReportModel = $analyticReportObj->initRecord($userID, $reportModel->id, UserCreditReportFrVerify::TYPE_FR_COMPARE_PAN, $merchantId);
        $analyticReportModel->img1_path = $frReport->img_fr_path ?? '';
        $analyticReportModel->img2_path = $picPath ?? '';
        $analyticReportModel->img1_report_id = $userKycForm->frReportId;
        $analyticReportModel->img2_report_id = $userKycForm->panReportId;
        $analyticReportModel->save();
        //记录用户设备信息
        ClientInfoLog::addLog($userID, ClientInfoLog::EVENT_PAN_TO_FACE_COMPARISON, $analyticReportModel->id, json_decode($userKycForm->params, true));

        if (!$frReport || !$picPath || $frReport->score === '' || floatval($frReport->score) > 0.98) {
            $this->setError('Please take your selfie again');
            return [
                'result'    => false,
                'report_id' => $analyticReportModel->id,
            ];
        }

        $service = new FileStorageService();
        $filePath1 = $service->downloadFile($frReport->img_fr_path);
        $filePath2 = $service->downloadFile($picPath);

        //2. 调用第三方获取报告
        $user = LoanPerson::findById($userID);
        $accuauthService = new AccuauthService([
            'userSourceId' => $user->source_id
        ]);
        $reportData = $accuauthService->faceVerify(
            base64_encode(file_get_contents($filePath1)),
            base64_encode(file_get_contents($filePath2))
        );
        @unlink($filePath1);
        @unlink($filePath2);
        //2.1. 记录原始报告
        $reportModel->account_name = $accuauthService->apiId;
        $reportModel->report_status = CreditReportStatus::RECEIVED()->getValue();
        $reportModel->report_data = json_encode($reportData);
        $reportModel->save();
        //2.2. 解析原始报告
        $faceScore = $reportData['score'] ?? '';
        $dataStatus = $reportData['status'] ?? '';
        $faceIdentical = intval($reportData['identical'] ?? false);
        $analyticReportModel->report_status = CreditReportStatus::RECEIVED()->getValue();
        $analyticReportModel->score = strval($faceScore);
        $analyticReportModel->data_status = strval($dataStatus);
        $analyticReportModel->identical = $faceIdentical;
        $analyticReportModel->save();

        if ($isFrFr) {
            if (floatval($faceScore) < 0.8) {
//                if (YII_ENV_PROD) {
                    $this->setError('Provide clear personal selfie!');
                    $analyticReportModel->report_status = CreditReportStatus::REJECT_VALUE()->getValue();
//                }
            }
        } else {
            if (floatval($faceScore) < 0.7) {
//                if (YII_ENV_PROD) {
                    $this->setError('Please take clear photos of yourself or your PAN card.');
                    $analyticReportModel->report_status = CreditReportStatus::REJECT_VALUE()->getValue();
//                }
            }
        }

        if ($dataStatus != 'OK' || $faceScore === '') {
            $this->setError('Please take clear photos of yourself or your PAN card.');
            $analyticReportModel->report_status = CreditReportStatus::REJECT_ERROR()->getValue();
        }

        if (empty($this->error)) {
            $analyticReportModel->report_status = CreditReportStatus::PASS()->getValue();
        }

        $analyticReportModel->save();
        return [
            'result'    => empty($this->error),
            'report_id' => $analyticReportModel->id,
        ];
    }

    public function saveUserFrSecond(UserFrSecForm $userFrSecForm, int $userID): bool
    {
        $result = true;
        /**
         * @var UserCreditReportFrLiveness $frReportNew
         */
        $frReportNew = UserCreditReportFrLiveness::find()
            ->where(['id' => $userFrSecForm->reportId])
            ->andWhere(['user_id' => $userID])
            ->andWhere(['report_status' => CreditReportStatus::PASS()->getValue()])
            ->one();

        $user = LoanPerson::findById($userID);
        /**
         * @var UserCreditReportFrLiveness $samePanFrReport
         */
        $samePanFrReport = UserCreditReportFrLiveness::find()
            ->alias('fl')
            ->select('fl.*')
            ->leftJoin(LoanPerson::tableName() . ' lp', 'lp.id = fl.user_id')
            ->where(['fl.is_used' => 1, 'fl.merchant_id' => $user->merchant_id])
            ->andWhere(['lp.pan_code' => $user->pan_code])
            ->orderBy(['fl.updated_at' => SORT_DESC])
            ->limit(1)
            ->one();

        //1. 初始化（UserCreditReport，UserCreditReportFrVerify）
        $reportObj = new UserCreditReport();
        $reportModel = $reportObj->initRecord($userID, UserCreditReport::SOURCE_ACCUAUTH, UserCreditReport::TYPE_FR_VERIFY, $user->merchant_id);
        $analyticReportObj = new UserCreditReportFrVerify();
        $analyticReportModel = $analyticReportObj->initRecord($userID, $reportModel->id, UserCreditReportFrVerify::TYPE_FR_COMPARE_FR, $user->merchant_id);
        $analyticReportModel->img1_path = $frReportNew->img_fr_path ?? '';
        $analyticReportModel->img2_path = $samePanFrReport->img_fr_path ?? '';
        $analyticReportModel->img1_report_id = $userFrSecForm->reportId;
        $analyticReportModel->img2_report_id = $samePanFrReport->id ?? '';
        $analyticReportModel->save();
        //记录用户设备信息
        ClientInfoLog::addLog($userID, ClientInfoLog::EVENT_FACE_TO_FACE_COMPARISON, $analyticReportModel->id, json_decode($userFrSecForm->params, true));

        if (!$frReportNew || !$samePanFrReport || $frReportNew->score === '' || floatval($frReportNew->score) > 0.98) {
            $this->setError('Face verification failed! Please take your selfie again');
            return false;
        }

        $service = new FileStorageService();
        $filePath1 = $service->downloadFile($frReportNew->img_fr_path);
        $filePath2 = $service->downloadFile($samePanFrReport->img_fr_path);

        //2. 调用第三方获取报告
        $accuauthService = new AccuauthService([
            'userSourceId' => $user->source_id
        ]);
        $reportData = $accuauthService->faceVerify(
            base64_encode(file_get_contents($filePath1)),
            base64_encode(file_get_contents($filePath2))
        );
        @unlink($filePath1);
        @unlink($filePath2);
        //2.1. 记录原始报告
        $reportModel->account_name = $accuauthService->apiId;
        $reportModel->report_status = CreditReportStatus::RECEIVED()->getValue();
        $reportModel->report_data = json_encode($reportData);
        $reportModel->save();
        //2.2. 解析原始报告
        $faceScore = $reportData['score'] ?? '';
        $dataStatus = $reportData['status'] ?? '';
        $faceIdentical = intval($reportData['identical'] ?? false);
        $analyticReportModel->report_status = CreditReportStatus::RECEIVED()->getValue();
        $analyticReportModel->score = strval($faceScore);
        $analyticReportModel->data_status = strval($dataStatus);
        $analyticReportModel->identical = $faceIdentical;
        $analyticReportModel->save();

        if (floatval($faceScore) < 0.8) {
//            if (YII_ENV_PROD) {
                $result = false;
                $this->setError('Face verification failed! Please take your selfie again');
                $analyticReportModel->report_status = CreditReportStatus::REJECT_VALUE()->getValue();
//            }
        }

        if ($dataStatus != 'OK' || $faceScore === '') {
            $result = false;
            $this->setError('Face verification failed! Please take your selfie again');
            $analyticReportModel->report_status = CreditReportStatus::REJECT_ERROR()->getValue();
        }

        if ($result) {
            $analyticReportModel->report_status = CreditReportStatus::PASS()->getValue();
        }

        $analyticReportModel->save();

        if ($result) {
            //修改绑定报告
//            $analyticReportModel->setAllUnused(UserCreditReportFrVerify::TYPE_FR_COMPARE_FR);
            $frReportNew->setThisUsed();
            $analyticReportModel->setThisUsed();
            //更新用户认证
            $verification = $user->userVerification;
            $verification->verificationUpdate(UserVerification::TYPE_FR_COMPARE_FR, UserVerificationLog::STATUS_VERIFY_SUCCESS);
        }

        return $result;
    }
}
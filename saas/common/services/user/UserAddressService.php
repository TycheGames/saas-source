<?php


namespace common\services\user;


use Carbon\Carbon;
use common\helpers\EncryptData;
use common\helpers\Util;
use common\models\ClientInfoLog;
use common\models\enum\AddressProofType;
use common\models\enum\CreditReportStatus;
use common\models\enum\Gender;
use common\models\user\LoanPerson;
use common\models\user\UserCreditReport;
use common\models\user\UserCreditReportOcrAad;
use common\models\user\UserRegisterInfo;
use common\models\user\UserVerification;
use common\models\user\UserVerificationLog;
use common\services\BaseService;
use common\services\FileStorageService;
use common\services\risk\AccuauthService;
use frontend\models\user\UserAddressProofOcrForm;
use frontend\models\user\UserAddressProofReportForm;
use Yii;

class UserAddressService extends BaseService implements IThirdDataService
{
    public function checkDataExpired(Carbon $updateTime): bool
    {
        return false;
    }

    /**
     * 保存地址照片的报告
     * @param UserAddressProofReportForm $addressProofForm
     * @param int $userID
     * @return bool
     */
    public function saveUserAddressProof(UserAddressProofReportForm $addressProofForm, int $userID): bool
    {
        $user = UserRegisterInfo::findOne(['user_id' => Yii::$app->user->id]);

        switch ($addressProofForm->addressProofType) {
            case AddressProofType::AADHAAR()->getValue():
                $result = $this->saveUserAadhaar($userID, $addressProofForm);
                $logType = 'ocr_aadhaar_submit';
                break;
            case AddressProofType::PASSPORT()->getValue():
                $result = $this->saveUserPassport($userID, $addressProofForm);
                $logType = 'ocr_passport_submit';
                break;
            case AddressProofType::DRIVER()->getValue():
                $result = $this->saveUserDriver($userID, $addressProofForm);
                $logType = 'ocr_driver_submit';
                break;
            default:
                $result = false;
                $logType = 'ocr_unknown_submit';
                break;
        }

        if ($result) {
            Yii::info(['user_id' => Yii::$app->user->id, 'appMarket' => $user['appMarket'], 'type' => $logType, 'status' => 'success', 'msg' => 'success'], 'auth_info');
        } else {
            Yii::info(['user_id' => Yii::$app->user->id, 'appMarket' => $user['appMarket'], 'type' => $logType, 'status' => 'fail', 'msg' => $this->error], 'auth_info');
        }

        return $result;
    }

    /**
     * 识别地址照片图片并上传
     * @param UserAddressProofOcrForm $addressProofForm
     * @param int $userID
     * @return bool
     */
    public function ocrUserAddressProof(UserAddressProofOcrForm $addressProofForm, int $userID): bool
    {
        $user = UserRegisterInfo::findOne(['user_id' => Yii::$app->user->id]);

        switch ($addressProofForm->addressProofType) {
            case AddressProofType::AADHAAR()->getValue():
                $result = $this->ocrUserAadhaar($userID, $addressProofForm);
                $logType = 'ocr_aadhaar_info';
                break;
            case AddressProofType::PASSPORT()->getValue():
                $result = $this->ocrUserPassport($userID, $addressProofForm);
                $logType = 'ocr_passport_info';
                break;
            case AddressProofType::DRIVER()->getValue():
                $result = $this->ocrUserDriver($userID, $addressProofForm);
                $logType = 'ocr_driver_info';
                break;
            default:
                $result = false;
                $logType = 'ocr_unknown_info';
                break;
        }
        if ($result) {
            Yii::info(['user_id' => Yii::$app->user->id, 'appMarket' => $user['appMarket'], 'type' => $logType, 'status' => 'success', 'msg' => 'success'], 'auth_info');
        } else {
            Yii::info(['user_id' => Yii::$app->user->id, 'appMarket' => $user['appMarket'], 'type' => $logType, 'status' => 'fail', 'msg' => $this->error], 'auth_info');
        }

        return $result;
    }

    private function ocrUserAadhaar(int $userID, UserAddressProofOcrForm $addressProofForm): bool
    {
        $result = true;

        $user = LoanPerson::findOne($userID);

        //1. 初始化（UserCreditReport，UserCreditReportOcrAad）
        $reportObj = new UserCreditReport();
        $reportModel = $reportObj->initRecord($userID, UserCreditReport::SOURCE_ACCUAUTH, UserCreditReport::TYPE_OCR_AAD, $user->merchant_id);
        $analyticReportObj = new UserCreditReportOcrAad();
        $analyticReportModel = $analyticReportObj->initRecord($userID, $reportModel->id, $user->merchant_id);
        //记录用户设备信息
        ClientInfoLog::addLog($userID, ClientInfoLog::EVENT_ADH_OCR_FRONT, $analyticReportModel->id, json_decode($addressProofForm->params, true));

        //2. 上传图片至OSS，并记录
        $service = new FileStorageService();
        $analyticReportModel->img_front_path = $service->uploadFile(
            'india/aadhaar',
            $addressProofForm->picFront->tempName,
            $addressProofForm->picFront->getExtension(),
            false
        );
        $analyticReportModel->save(); //尽可能保存数据
        $analyticReportModel->img_back_path = $service->uploadFile(
            'india/aadhaar',
            $addressProofForm->picBack->tempName,
            $addressProofForm->picBack->getExtension(),
            false
        );
        $analyticReportModel->save(); //尽可能保存数据

        //3. 调用第三方获取报告
        $accuauthService = new AccuauthService([
            'userSourceId' => $user->source_id
        ]);
        $reportDataB = $accuauthService->aadhaarCardOcr(base64_encode(file_get_contents($addressProofForm->picBack->tempName)));
        $reportDataF = $accuauthService->aadhaarCardOcr(base64_encode(file_get_contents($addressProofForm->picFront->tempName)));
        @unlink($addressProofForm->picFront->tempName);
        @unlink($addressProofForm->picBack->tempName);
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
            $result = false;
        }

//        if (empty($address) || empty($state) || empty($city) || empty($pin) || $cardSideB != 'back') {
//            $this->setError('Aadhaar back image was not clear, please re-upload');
//            $analyticReportModel->report_status = CreditReportStatus::REJECT_VALUE()->getValue();
//            $result = false;
//        }
        //取消校验背面地址相关信息
        if ($cardSideB != 'back') {
            $this->setError('Aadhaar back image was not clear, please re-upload');
            $analyticReportModel->report_status = CreditReportStatus::REJECT_VALUE()->getValue();
            $result = false;
        }

        if ($cardSideF != 'front' && $cardSideB != 'back') {
            $analyticReportModel->report_status = CreditReportStatus::REJECT_VALUE()->getValue();
            $this->setError('Aadhaar image was not clear, please re-upload');
            $result = false;
        }

        if (($dataFrontStatus != 'OK') || ($dataBackStatus != 'OK')) {
            $analyticReportModel->report_status = CreditReportStatus::REJECT_ERROR()->getValue();
            $this->setError('Aadhaar card OCR error, please re-upload');
            $result = false;
        }

        if ($result) {
            $analyticReportModel->report_status = CreditReportStatus::PASS()->getValue();
        }

        $analyticReportModel->save();

        $this->setResult([
            'reportId'  => $analyticReportModel->id,
            'aadhaarNo' => $cardNo,
        ]);

        return $result;
    }

    private function ocrUserPassport(int $userID, UserAddressProofOcrForm $addressProofForm): bool
    {
        return false;
    }

    private function ocrUserDriver(int $userID, UserAddressProofOcrForm $addressProofForm): bool
    {
        return false;
    }

    private function saveUserAadhaar(int $userID, UserAddressProofReportForm $addressProofForm): bool
    {
        $user = LoanPerson::findById($userID);
        $resUser = LoanPerson::findById($userID);
        $resVerify = $user->userVerification->verificationUpdate(UserVerification::TYPE_OCR_AADHAAR, UserVerificationLog::STATUS_VERIFY_SUCCESS);
        return $resUser && $resVerify;







        /**
         * @var UserCreditReportOcrAad $aadReport
         */
        $aadReport = UserCreditReportOcrAad::find()
            ->where(['id' => $addressProofForm->addressProofReportId])
            ->andWhere(['user_id' => $userID])
            ->andWhere(['report_status' => CreditReportStatus::PASS()->getValue()])
            ->limit(1)
            ->one();

        if (!$aadReport) {
            return false;
        }

        $aadNo = EncryptData::decrypt($aadReport->card_no_encode);;
        $aadNoMd5 = md5($aadNo);
        $aadGender = $aadReport->gender;

        $user = LoanPerson::findById($userID);

        $exist = LoanPerson::find()
            ->where([
                'aadhaar_md5' => $aadNoMd5,
                'source_id' => $user->source_id
            ])
            ->andWhere(['!=', 'id', $userID])
            ->exists();
        if ($exist) {
            $this->setError('Aadhaar verification failed!');
            return false;
        }


        $user->aadhaar_number = '';
        $user->aadhaar_mask = substr($aadNo, -4, 4);
        $user->aadhaar_md5 = $aadNoMd5;
        $user->check_code = EncryptData::encrypt($aadNo);
        $user->gender = $aadGender;
        $resUser = $user->save();
//        $aadReport->setAllUnused();
        $aadReport->setThisUsed();
        $resVerify = $user->userVerification->verificationUpdate(UserVerification::TYPE_OCR_AADHAAR, UserVerificationLog::STATUS_VERIFY_SUCCESS);
        return $resUser && $resVerify;
    }

    private function saveUserPassport(int $userID, UserAddressProofReportForm $addressProofForm): bool
    {
        return false;
    }


    private function saveUserDriver(int $userID, UserAddressProofReportForm $addressProofForm): bool
    {
        return false;
    }
}
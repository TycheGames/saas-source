<?php

namespace common\services\user;

use Carbon\Carbon;
use common\models\ClientInfoLog;
use common\models\user\LoanPerson;
use common\models\user\UserVerification;
use common\models\user\UserVerificationLog;
use common\services\BaseService;
use common\services\FileStorageService;
use frontend\models\user\UserBasicInfoExternalForm;
use Yii;
use common\models\user\UserBasicInfo;
use frontend\models\user\UserBasicInfoForm;

class UserBasicInfoService extends BaseService implements IThirdDataService
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
     * @param UserBasicInfoForm $userBasicInfoForm
     * @param int $userId
     * @return bool
     * @throws
     */
    public function saveUserBasicInfoByForm(UserBasicInfoForm $userBasicInfoForm, int $userId): bool
    {
        if (empty($userBasicInfoForm) || !$userBasicInfoForm->validate()) {
            return false;
        }

        $user = LoanPerson::findById($userId);
        if(empty($user)) {
            return false;
        }

        $model = new UserBasicInfo();
        $model->user_id = $user->id;
        $model->merchant_id = $user->merchant_id;

        $aadhaarAddressIds = explode(',', $userBasicInfoForm->aadhaarAddressId);
        $aadhaarAddressVals = explode(',', $userBasicInfoForm->aadhaarAddressVal);
        $userBasicInfoForm->aadhaarAddressId1 = trim($aadhaarAddressIds[0] ?? $userBasicInfoForm->aadhaarAddressId);
        $userBasicInfoForm->aadhaarAddressId2 = trim($aadhaarAddressIds[1] ?? $userBasicInfoForm->aadhaarAddressId);
        $userBasicInfoForm->aadhaarAddressVal1 = trim($aadhaarAddressVals[0] ?? $userBasicInfoForm->aadhaarAddressVal);
        $userBasicInfoForm->aadhaarAddressVal2 = trim($aadhaarAddressVals[1] ?? $userBasicInfoForm->aadhaarAddressVal);

        foreach ($userBasicInfoForm->maps() as $key => $value) {
            $model->$value = $userBasicInfoForm->$key;
        }

        if ($model->save()) {
            $verification = $user->userVerification;
            $result = $verification->verificationUpdate(UserVerification::TYPE_BASIC, UserVerificationLog::STATUS_VERIFY_SUCCESS);

            ClientInfoLog::addLog($user->id, ClientInfoLog::EVENT_BASIC_INFO, $model->id,json_decode($userBasicInfoForm->clientInfo, true));
            return $result;
        }

        return false;
    }

    /**
     * @param UserBasicInfoExternalForm $userBasicInfoForm
     * @param int $userId
     * @return bool
     * @throws
     */
    public function saveUserBasicInfoExternalByForm(UserBasicInfoExternalForm $userBasicInfoForm, int $userId): bool
    {
        if (empty($userBasicInfoForm) || !$userBasicInfoForm->validate()) {
            return false;
        }

        $user = LoanPerson::findById($userId);
        if(empty($user)) {
            return false;
        }

        $model = new UserBasicInfo();
        $model->user_id = $user->id;
        $model->merchant_id = $user->merchant_id;

        $aadhaarAddressIds = explode(',', $userBasicInfoForm->aadhaarAddressId);
        $aadhaarAddressVals = explode(',', $userBasicInfoForm->aadhaarAddressVal);
        $userBasicInfoForm->aadhaarAddressId1 = trim($aadhaarAddressIds[0] ?? $userBasicInfoForm->aadhaarAddressId);
        $userBasicInfoForm->aadhaarAddressId2 = trim($aadhaarAddressIds[1] ?? $userBasicInfoForm->aadhaarAddressId);
        $userBasicInfoForm->aadhaarAddressVal1 = trim($aadhaarAddressVals[0] ?? $userBasicInfoForm->aadhaarAddressVal);
        $userBasicInfoForm->aadhaarAddressVal2 = trim($aadhaarAddressVals[1] ?? $userBasicInfoForm->aadhaarAddressVal);

        foreach ($userBasicInfoForm->maps() as $key => $value) {
            $model->$value = $userBasicInfoForm->$key;
        }

        if ($model->save()) {
            $verification = $user->userVerification;
            $result = $verification->verificationUpdate(UserVerification::TYPE_BASIC, UserVerificationLog::STATUS_VERIFY_SUCCESS);

            ClientInfoLog::addLog($user->id, ClientInfoLog::EVENT_BASIC_INFO, $model->id,json_decode($userBasicInfoForm->clientInfo, true));
            return $result;
        }

        return false;
    }

    /**
     * @param int $userID
     * @return UserBasicInfoForm
     */
    public function getUserBasicInfoByForm(int $userID): UserBasicInfoForm
    {
        $user = LoanPerson::findById($userID);
        $basicInfo = $user->userBasicInfo;

        if (empty($basicInfo)) {
            return new UserBasicInfoForm();
        }

        $model = new UserBasicInfoForm();
        foreach ($model->maps() as $key => $value) {
            $model->$key = $basicInfo->$value;
        }

        $model->aadhaarAddressId = empty($model->aadhaarAddressId1) ? '' : $model->aadhaarAddressId1 . ',' . $model->aadhaarAddressId2;
        $model->aadhaarAddressVal = empty($model->aadhaarAddressVal1) ? '' : $model->aadhaarAddressVal1 . ',' . $model->aadhaarAddressVal2;

        return $model;
    }
}
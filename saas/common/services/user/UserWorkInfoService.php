<?php


namespace common\services\user;


use Carbon\Carbon;
use common\models\ClientInfoLog;
use common\models\user\LoanPerson;
use common\models\user\UserVerification;
use common\models\user\UserVerificationLog;
use common\models\user\UserWorkInfo;
use common\services\BaseService;
use frontend\models\user\UserWorkInfoForm;
use common\services\FileStorageService;

class UserWorkInfoService extends BaseService implements IThirdDataService
{
    /**
     * 检查数据是否过期，true:过期 false:未过期
     * @param Carbon $updateTime
     * @return bool
     */
    public function checkDataExpired(Carbon $updateTime): bool
    {
        return false;
    }

    /**
     * @param UserWorkInfoForm $userWorkInfoForm
     * @param int $userID
     * @return bool
     * @throws
     */
    public function saveUserWorkInfoByForm(UserWorkInfoForm $userWorkInfoForm, int $userID): bool
    {
        if (empty($userWorkInfoForm) || !$userWorkInfoForm->validate()) {
            return false;
        }

        $user = LoanPerson::findById($userID);
        if(empty($user)) {
            return false;
        }

        $service = new FileStorageService();
        $companyDocsArr = [];
//        foreach ($userWorkInfoForm->companyDocsAddArr as $item) {
//            $data['id'] = $item['id'];
//            $data['url'] = $service->uploadFileByPictureBase64('india/work_info', $item['url']);
//            array_push($companyDocsArr, $data);
//        }
        $userWorkInfoForm->companyDocsAddArr = json_encode($companyDocsArr);

        $model = new UserWorkInfo();
        $model->user_id = $user->id;
        $model->merchant_id = $user->merchant_id;
        $residentialAddressIds = explode(',', $userWorkInfoForm->residentialAddressId);
        $residentialAddressVals = explode(',', $userWorkInfoForm->residentialAddressVal);
        $companyAddressIds = explode(',', $userWorkInfoForm->companyAddressId);
        $companyAddressVals = explode(',', $userWorkInfoForm->companyAddressVal);
        $userWorkInfoForm->residentialAddressId1 = trim($residentialAddressIds[0] ?? $userWorkInfoForm->residentialAddressId);
        $userWorkInfoForm->residentialAddressId2 = trim($residentialAddressIds[1] ?? $userWorkInfoForm->residentialAddressId);
        $userWorkInfoForm->residentialAddressVal1 = trim($residentialAddressVals[0] ?? $userWorkInfoForm->residentialAddressVal);
        $userWorkInfoForm->residentialAddressVal2 = trim($residentialAddressVals[1] ?? $userWorkInfoForm->residentialAddressVal);
        $userWorkInfoForm->companyAddressId1 = trim($companyAddressIds[0] ?? $userWorkInfoForm->companyAddressId);
        $userWorkInfoForm->companyAddressId2 = trim($companyAddressIds[1] ?? $userWorkInfoForm->companyAddressId);
        $userWorkInfoForm->companyAddressVal1 = trim($companyAddressVals[0] ?? $userWorkInfoForm->companyAddressVal);
        $userWorkInfoForm->companyAddressVal2 = trim($companyAddressVals[1] ?? $userWorkInfoForm->companyAddressVal);

        foreach ($userWorkInfoForm->maps() as $key => $value) {
            $model->$value = $userWorkInfoForm->$key;
        }

//        if($user->userWorkInfo && $this->checkRepeat(array_values($userWorkInfoForm->maps()), $model, $user->userWorkInfo)) {
//            return true;
//        }

        if ($model->save()) {
            $verification = $user->userVerification;
            $result = $verification->verificationUpdate(UserVerification::TYPE_WORK, UserVerificationLog::STATUS_VERIFY_SUCCESS);

            ClientInfoLog::addLog($user->id, ClientInfoLog::EVENT_WORK_INFO, $model->id,json_decode($userWorkInfoForm->clientInfo, true));

            return $result;
        }

        return false;
    }

    /**
     * @param int $userID
     * @return UserWorkInfoForm
     */
    public function getUserWorkInfoByForm(int $userID): UserWorkInfoForm
    {
        $user = LoanPerson::findById($userID);
        $workInfo = $user->userWorkInfo;

        if (empty($workInfo)) {
            return new UserWorkInfoForm();
        }

        $model = new UserWorkInfoForm();
        foreach ($model->maps() as $key => $value) {
            $model->$key = $workInfo->$value;
        }

        $model->companyAddressId = $model->companyAddressVal1 . ',' . $model->companyAddressId2;
        $model->companyAddressVal = $model->companyAddressVal1 . ',' . $model->companyAddressVal2;
        $model->residentialAddressId = $model->residentialAddressId1 . ',' . $model->residentialAddressId2;
        $model->residentialAddressVal = $model->residentialAddressVal1 . ',' . $model->residentialAddressVal2;

        return $model;
    }
}
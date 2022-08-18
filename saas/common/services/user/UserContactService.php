<?php


namespace common\services\user;

use Carbon\Carbon;
use Codeception\Module\Cli;
use common\models\ClientInfoLog;
use common\models\user\LoanPerson;
use common\models\user\UserContact;
use common\models\user\UserVerification;
use common\models\user\UserVerificationLog;
use common\services\BaseService;
use frontend\models\user\UserContactForm;
use Yii;

class UserContactService extends BaseService implements IThirdDataService
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
     * @param UserContactForm $userContractForm
     * @param int $userID
     * @return bool
     */
    public function saveUserContactByForm(UserContactForm $userContractForm, int $userID): bool
    {
        // if (empty($userContractForm) || !$userContractForm->validate()) {
        //     return false;
        // }

        $user = LoanPerson::findById($userID);
        if (empty($user)) {
            return false;
        }

        $model = new UserContact();
        $model->user_id = $user->id;
        $model->merchant_id = $user->merchant_id;
        foreach ($userContractForm->maps() as $key => $value) {
            $model->$value = $userContractForm->$key;
        }

//        if ($user->userContact && $this->checkRepeat(array_values($userContractForm->maps()), $model, $user->userContact)) {
//            return true;
//        }

        if ($model->save()) {
            $verification = $user->userVerification;
            $result = $verification->verificationUpdate(UserVerification::TYPE_CONTACT, UserVerificationLog::STATUS_VERIFY_SUCCESS);

            ClientInfoLog::addLog($userID, ClientInfoLog::EVENT_CONTACT, $model->id, json_decode($userContractForm->clientInfo, true));
            return $result;
        }

        return false;
    }

    public function getUserContactByForm(int $userID): UserContactForm
    {
        $user = LoanPerson::findById($userID);
        $userContact = $user->userContact;

        if (empty($userContact)) {
            return new UserContactForm();
        }

        $model = new UserContactForm();

        foreach ($model->maps() as $key => $value) {
            $model->$key = $userContact->$value;
        }

        return $model;
    }
}
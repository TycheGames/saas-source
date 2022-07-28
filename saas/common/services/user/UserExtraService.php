<?php


namespace common\services\user;


use common\models\user\LoanPerson;

class UserExtraService
{
    /**
     * @var LoanPerson $user
     */
    private $user;

    public function __construct(LoanPerson $user)
    {
        $this->user = $user;
    }

    /**
     * 获取用户认证信息
     *
     * @param bool $all
     * @return array
     */
    public function getUserExtraInfo(bool $all = false): array
    {
        $data = [
            'userWorkInfos'    => $this->getUserWorkInfo($all),
            'userBasicInfos'   => $this->getUserBasicInfo($all),
            'userBankAccounts' => $this->getUserBankAccount($all),
            'userContacts'     => $this->getUserContact($all),
        ];

        return $data;
    }


    public function getUserWorkInfo(bool $all = false)
    {
        return $all ? $this->user->userWorkInfos : $this->user->userWorkInfo;
    }

    public function getUserBasicInfo(bool $all = false)
    {
        return $all ? $this->user->userBasicInfos : $this->user->userBasicInfo;
    }

    public function getUserBankAccount(bool $all = false)
    {
        return $all ? $this->user->userBankAccounts : $this->user->userBankAccount;
    }

    public function getUserContact(bool $all = false)
    {
        return $all ? $this->user->userContacts : $this->user->userContact;
    }
}
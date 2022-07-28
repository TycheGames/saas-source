<?php


namespace common\services\user;

use common\models\user\LoanPerson;

/**
 * Class UserCanLoanCheckService
 * @package common\services\user
 * @property LoanPerson $loanPerson
 */
class UserCanLoanCheckService
{
    protected $loanPerson;

    public function __construct(LoanPerson $loanPerson)
    {
        $this->loanPerson = $loanPerson;
    }


    /**
     * 设置可再借时间
     * @param $interval int 天数
     * @return bool
     */
    public function setCanLoanTime($interval)
    {
        $interval = intval($interval);
        $canLoanTime = strtotime("+{$interval} days");
        $this->loanPerson->can_loan_time = $canLoanTime;
        return $this->loanPerson->save();
    }

    /**
     * 判断用户是否可借
     * @return bool
     */
    public function checkCanLoan()
    {
        return $this->loanPerson->can_loan_time > time();
    }

    /**
     * 获取可再借时间
     * @return string
     */
    public function getCanLoanDate()
    {
        if($this->checkCanLoan())
        {
            return date('Y-m-d', $this->loanPerson->can_loan_time);
        }else{
            return '';
        }
    }

}
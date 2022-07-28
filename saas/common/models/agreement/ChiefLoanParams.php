<?php


namespace common\models\agreement;

use yii\base\Model;


class ChiefLoanParams extends Model
{
    public $loanDate;  //下单时间
    public $loanTime;  //下单时间
    public $realName; //姓名
    public $fatherName;  //父亲姓名
    public $birthday; //出生日期
    public $gender; //性别
    public $maritalStatus; //婚姻状态
    public $aadhaarNo; //A卡
    public $panCardNo; //pan卡
    public $address; //地址
    public $phone;
    public $firstContactName;
    public $firstContactPhone;
    public $secondContactName;
    public $secondContactPhone;
    public $accountName;
    public $bankAccount;
    public $ifsc;
    public $loanId;
    public $city;
    public $loanAmount;
    public $rateOfInterest;
    public $loanPurpose; //消费
    public $processingFee;
    public $penalty;
    public $repaymentAmount;
    public $panImgUrl;
    public $aadhaarFrontImgUrl;
    public $aadhaarBackImgUrl;
    public $livingImgUrl;
    public $cycle; //期限

}
<?php


namespace common\models\agreement;

use yii\base\Model;


class SanctionLetterParams extends Model
{
    public $date; //Carbon::now()->format('M.d,Y')
    public $customerId;
    public $loanApplicationDate; //Carbon::now()->format('M.d,Y h:i a')
    public $companyName;
    public $companyAddr;
    public $gstNumber;
    public $companyPhone;
    public $customerName;
    public $customerAge; //Carbon::rawCreateFromFormat('Y-m-d', $loanPerson->birthday)->age
    public $residentialDetailAddress; //$workInfo = $loanPerson->userWorkInfo; $workInfo->residential_detail_address
    public $residentialAddress2; //$workInfo->residential_address2
    public $residentialAddress1; //$workInfo->residential_address1
    public $residentialPincode; //$workInfo->residential_pincode
    public $nbfcName; //Aglow Fintrade Private Limited
    public $nbfcAddr; //205, D.R Chamber, 12/56 Desh Bandhu Gupta Road Karol Bagh New Delhi- 110005
    public $nbfcShortName; //aglow
    public $offerValidityPeriod; //$params['offerValidityPeriod']
    public $loanAmountSanctioned; //$params['amount']
    public $availabilityPeriod; //$params['loanTerm']
    public $interest; //$params['totalInterestRate']
    public $totalInterestAmount; //$params['totalInterestAmount']
    public $processingFees; //$params['processingFees']
    public $repayment; //$params['repayment']
    public $delayedPaymentCharges; //$params['delayedPaymentCharges']
    public $DPNReferenceNO;
    public $termsAcceptedAt; //Carbon::now()->format('d-m-Y H:i:s')
    public $deviceName;
    public $deviceId;
    public $headerImg; ////1 aglow 2 pawan null kudos
    public $productName;
    public $appName;


}
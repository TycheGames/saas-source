<?php

namespace common\services\agreement;

use backend\models\Merchant;
use Carbon\Carbon;
use common\helpers\CommonHelper;
use common\models\aglow\LoanLicenceAglowOrder;
use common\models\agreement\SanctionLetterParams;
use common\models\enum\kudos\LoanStatus;
use common\models\kudos\LoanKudosOrder;
use common\models\order\UserLoanOrder;
use common\models\pay\PayAccountSetting;
use common\models\product\ProductSetting;
use common\models\user\LoanPerson;
use common\models\user\UserBankAccount;
use common\services\AglowService;
use common\services\BaseService;
use common\services\KudosService;
use frontend\models\agreement\LoanServiceForm;
use frontend\models\agreement\SanctionLetterApiForm;
use frontend\models\agreement\SanctionLetterForm;
use light\hashids\Hashids;


class AgreementService extends BaseService
{

    //nbfc信息
    public static $nbfcMap = [
        Merchant::NBFC_AGLOW => [
            'shortName' => 'Aglow Fintrade Pvt Ltd',
            'nbfcName' => 'Aglow Fintrade Private Limited',
            'nbfcAddr' => '205, D.R Chamber, 12/56 Desh Bandhu Gupta Road Karol Bagh New Delhi- 110005'
        ],
        Merchant::NBFC_PAWAN => [
            'shortName' => 'pawan',
            'nbfcName' => 'Pawan Finvest Private Limited',
            'nbfcAddr' => 'C-321, Eastern Business District, Neptune Magnet Mall, LBS Rd, Bhandup West, Mumbai, Maharashtra 400078, India'
        ],
        Merchant::NBFC_KUDOS => [
            'shortName' => 'Kudos Finance & Investment Pvt Ltd',
            'nbfcName' => 'Kudos Finance & Investment Pvt Ltd',
            'nbfcAddr' => '7th Floor, East Wing , Marisoft – 3 Marigold Premises, Kalyani Nagar, Pune, Maharashtra 411014'
        ],
        Merchant::NBFC_ACEMONEY => [
            'shortName' => 'Acemoney (India) Ltd',
            'nbfcName' => 'Acemoney (India) Limited',
            'nbfcAddr' => 'UG-1 Lusa Tower, Azadpur New Delhi North West DL 110033 IN'
        ],
        Merchant::NBFC_CARE => [
            'shortName' => 'CARE INDIA FINVEST LIMITED',
            'nbfcName' => 'CARE INDIA FINVEST LIMITED',
            'nbfcAddr' => 'SCO NO - 52,, SECTOR - 29,, FARIDABAD,, Faridabad, Haryana, 121002'
        ],
        Merchant::NBFC_AGLOW_FAKE => [
            'shortName' => 'Aglow Fintrade Private Limited',
            'nbfcName' => 'Aglow Fintrade Private Limited',
            'nbfcAddr' => '205 D R Chambers, 12/56, Desh Bandu Gupta Road, Karol Bagh, New Dehli-11005'
        ],
        Merchant::NBFC_BCL => [
            'shortName' => 'BCL ENTERPRISES LTD',
            'nbfcName' => 'BCL ENTERPRISES LIMITED',
            'nbfcAddr' => '510, ARUNACHAL BUILDING, 19, BARAKHAMBA ROAD, NEW DELHI, Central Delhi, Delhi, 110001'
        ],
        Merchant::NBFC_ZAVRON => [
            'shortName' => 'Zavron Finance Pvt LTD',
            'nbfcName' => 'Zavron Finance Pvt Limited',
            'nbfcAddr' => '125,MaharajBagh Road,Sitabuldi,Nagpur-440012'
        ],
    ];

    /**
     * 借款服务合同
     * @param $userId
     * @param $productId
     * @param $amount
     * @param $days
     * @return bool
     */
    public function getLoanServiceData($userId,$productId,$amount,$days){
        /**
         * @var LoanPerson $loanPerson
         */
        $loanPerson = LoanPerson::find()
            ->where(['id' => $userId])
            ->one();
        if(is_null($loanPerson)){
            $this->setError('params err');
            return false;
        }
        if(!in_array($amount,ProductSetting::$loan_amount_list)){
            $this->setError('amount err');
            return false;
        }
        /** @var  ProductSetting $productSetting */
        $productSetting = ProductSetting::find()->where(['id' => $productId])->one();
        if(is_null($productSetting)){
            $this->setError('product err');
            return false;
        }
        if($productSetting->productPeriodSetting->loan_term != $days){
            $this->setError('days err');
            return false;
        }
        /** @var UserBankAccount $userBankAccount */
        $userBankAccount = UserBankAccount::find()->where([
            'user_id' => $userId,
            'status' => UserBankAccount::STATUS_SUCCESS,
            'main_card' => UserBankAccount::MAIN_IS
        ])->limit(1)->one();
        if(is_null($userBankAccount)){
            $this->setError('bank account err');
            return false;
        }
        $data = [
            'contract_number' => date('YmdhHi').$userId,
            'borrower' => $loanPerson->name,
            'aadhaar_number' => $loanPerson->aadhaar_number,
            'phone' => $loanPerson->phone,
            'lender' => '',
            'service_provider' => '',
            'loan_amount' => CommonHelper::CentsToUnit($amount),
            'loan_interest_rate' => $productSetting->day_rate . '% daily of the loan amount',
            'term_of_loan' => $days,
            'loan_start_date' => date('Y-m-d', time()),
            'loan_expiring_date' => date('Y-m-d', time()+ $days * 86400),
            'account_number' => $userBankAccount->account,
            'account_name' => $userBankAccount->name,
            'bank_of_deposit' => $userBankAccount->bank_name,
            'signing_date' => date('Y-m-d', time()),
        ];
        $this->setResult($data);
        return true;
    }


    /**
     * 用户委托协议
     * @param $userId
     * @return bool
     */
    public function getUserCommissionedData($userId){
        /**
         * @var LoanPerson $loanPerson
         */
        $loanPerson = LoanPerson::find()
            ->where(['id' => $userId])
            ->one();
        if(is_null($loanPerson)){
            $this->setError('params err');
            return false;
        }

        /** @var UserBankAccount $userBankAccount */
        $userBankAccount = UserBankAccount::find()->where([
            'user_id' => $userId,
            'status' => UserBankAccount::STATUS_SUCCESS,
            'main_card' => UserBankAccount::MAIN_IS
        ])->limit(1)->one();

        if(is_null($userBankAccount)){
            $this->setError('bank account err');
            return false;
        }
        $data = [
            'bank_card_account_name' => $userBankAccount->name,
            'bank_card_opening_bank' => $userBankAccount->bank_name,
            'bank_card_account_number' => $userBankAccount->account,
            'aadhaar_number' => $loanPerson->aadhaar_number,
            'license_number' => '',
            'contact_phone' => $loanPerson->phone,
        ];
        $this->setResult($data);
        return true;
    }

    /**
     * @param int $userId
     * @param LoanServiceForm $form
     * @param array $clientInfo
     * @return bool
     */
    public function getDemandPromissoryNote(int $userId, LoanServiceForm $form, array $clientInfo)
    {
        $companyInfo = $this->getCompanyInfo($clientInfo['packageName']);
        /**
         * @var LoanPerson $loanPerson
         */
        $loanPerson = LoanPerson::find()
            ->where(['id' => $userId])
            ->one();
        $product = ProductSetting::findOne($form->productId);
        $interestRate = $product->day_rate * 365; //2019-9-20 下午 与卢山给的计算规则
        $result = [
            'name'            => $loanPerson->name,
            'company'         => $companyInfo['companyName'],
            'money'           => CommonHelper::CentsToUnit($form->amount),
            'interest'        => $interestRate,
            'date'            => Carbon::now()->format('M.d,Y'),
            'termsAcceptedAt' => Carbon::now()->format('d-m-Y H:i:s'),
            'device'          => $clientInfo['deviceName'],
            'deviceId'        => $clientInfo['deviceId'],
            'ipAddress'       => $clientInfo['ip'],
        ];
        $this->setResult($result);
        return true;
    }



    /**
     * @param SanctionLetterParams $sanctionLetterParams
     * @return bool
     */
    public function getSanctionLetter(SanctionLetterParams $sanctionLetterParams)
    {
        $result = [
            'date'                      => $sanctionLetterParams->date,
            'customerId'                => $sanctionLetterParams->customerId,
            'loanApplicationDate'       => $sanctionLetterParams->loanApplicationDate,
            'sanctionLetterDetail'      => "<p>{$sanctionLetterParams->companyName}</p><p>{$sanctionLetterParams->companyAddr}</p><p>GST Number:-{$sanctionLetterParams->gstNumber}</p><p>Customer Care Contact Number: +91 {$sanctionLetterParams->companyPhone}</p>",
            'borrowerDetail'            => "<p>{$sanctionLetterParams->customerName},</p>
<p>{$sanctionLetterParams->customerAge} Yrs,</p>
<p>{$sanctionLetterParams->residentialDetailAddress},</p>
<p>{$sanctionLetterParams->residentialAddress2},{$sanctionLetterParams->residentialAddress1},{$sanctionLetterParams->residentialPincode}</p>",
            'lenderDetail'              => "<p>{$sanctionLetterParams->nbfcName}</p><p>{$sanctionLetterParams->nbfcAddr}</p>",
            'offerValidityPeriod'       => $sanctionLetterParams->offerValidityPeriod,
            'loanPurpose'               => 'Personal',
            'loanAmountSanctioned'      => sprintf('Rs. %.2f (Including processing fees)*', $sanctionLetterParams->loanAmountSanctioned),
            'availabilityPeriod'        => "{$sanctionLetterParams->availabilityPeriod} days",
            'term'                      => "{$sanctionLetterParams->availabilityPeriod} days",
            'interest'                  => sprintf('%0.2f %%', $sanctionLetterParams->interest),
            'totalInterestAmount'       => sprintf("Rs. %.2f", $sanctionLetterParams->totalInterestAmount),
            'processingFees'            => sprintf('Rs. %.2f(Including GST)', $sanctionLetterParams->processingFees),
            'repayment'                 => sprintf('Rs. %.2f', $sanctionLetterParams->repayment),
            'monthlyInstallmentAmount'  => 'Rs. NA',
            'prepaymentCharges'         => 'Rs. 0.00',
            'delayedPaymentCharges'     => sprintf('%.2f %% per day on due amount from the due date', $sanctionLetterParams->delayedPaymentCharges),
            'ECSDishonourCharges'       => sprintf('Rs %.2f/- per dishonor of', 0),//待定
            'otherCharges'              => 'Insurance premia, stamp duty, legal expense, documentation charges and other incidental expenses incurred in connection with the loan shall be borne by the Borrower',
            'documentation'             => '<p>Submit to the Lender the following:</p><p>1. Most Important Documents (KYC)</p><p>2. Demand Promissory Note</p>',
            'loanDisbursement'          => 'Post completion of the documentation you will eligible for the loan disbursement.',
            'technicalServiceProviders' => $sanctionLetterParams->nbfcShortName,
            'name'                      => $sanctionLetterParams->customerName,
            'DPNReferenceNO'            => $sanctionLetterParams->DPNReferenceNO,
            'termsAcceptedAt'           => $sanctionLetterParams->termsAcceptedAt,
            'device'                    => $sanctionLetterParams->deviceName,
            'deviceId'                  => $sanctionLetterParams->deviceId,
            'headImg'                   => $sanctionLetterParams->headerImg, //1 aglow 2 pawan
            'productName'               => $sanctionLetterParams->productName,
        ];
        $this->setResult($result);
        return true;
    }

    private function getCompanyInfo($packageName)
    {
        switch($packageName) {
            default:
                $companyName = 'Huaye Technology India Private Limited';
                $companyAddr = '151, National Media Centre, Gurgaon, Haryana, India 122002';
        }

        return [
            'companyName' => $companyName ?? '',
            'companyAddr' => $companyAddr ?? '',
        ];
    }


    public function getLoanSanctionLetter(SanctionLetterForm $form)
    {
        //订单号为空，则为贷前
        if(empty($form->orderID))
        {
            return $this->getSanctionLetterBefore($form);
        }else{
            return $this->getSanctionLetterAfter($form);

        }
    }


    /**
     * 贷前 sanction letter
     * @param SanctionLetterForm $form
     * @param string $DPNReferenceNO
     * @return bool
     */
    public function getSanctionLetterBefore(SanctionLetterForm $form, $DPNReferenceNO = '')
    {
        $product = ProductSetting::findOne($form->productId);
        $merchant = Merchant::findOne($product->merchant_id);
        $companyName = $merchant->company_name;
        $companyAddr = $merchant->company_addr;
        $gstNumber = $merchant->gst_number;
        $companyPhone = $merchant->telephone;

        $userId = $form->userID;
        $clientInfo = $form->clientInfo;
        $loanPerson = LoanPerson::findOne($userId);
        $loanAmount = $form->amount;
        $productDetail = $product->getProductInfo();
        $amount = $product->amount($loanAmount);
        $totalInterestRate = $productDetail['day_rate'] * $productDetail['loan_term'];
        $personAge = Carbon::rawCreateFromFormat('Y-m-d', $loanPerson->birthday)->age;
        $workInfo = $loanPerson->userWorkInfo;

        $sanctionLetterParams = new SanctionLetterParams();
        $sanctionLetterParams->date = Carbon::now()->format('M.d,Y h:i a');
        $sanctionLetterParams->customerId =  'customer_' . (new Hashids(['salt'=> 'ag', 'minHashLength'=> 16]))->encode($userId);
        $sanctionLetterParams->loanApplicationDate = Carbon::now()->format('M.d,Y h:i a');
        $sanctionLetterParams->companyName = $companyName;
        $sanctionLetterParams->companyAddr = $companyAddr;
        $sanctionLetterParams->gstNumber = $gstNumber;
        $sanctionLetterParams->companyPhone = $companyPhone;
        $sanctionLetterParams->customerName = $loanPerson->name;
        $sanctionLetterParams->customerAge = $personAge;
        $sanctionLetterParams->residentialDetailAddress = $workInfo->residential_detail_address;
        $sanctionLetterParams->residentialAddress2 = $workInfo->residential_address2;
        $sanctionLetterParams->residentialAddress1 = $workInfo->residential_address1;
        $sanctionLetterParams->residentialPincode = $workInfo->residential_pincode;
        $sanctionLetterParams->nbfcName = self::$nbfcMap[$merchant->nbfc]['nbfcName'];
        $sanctionLetterParams->nbfcAddr = self::$nbfcMap[$merchant->nbfc]['nbfcAddr'];
        $sanctionLetterParams->nbfcShortName = self::$nbfcMap[$merchant->nbfc]['shortName'];
        $sanctionLetterParams->offerValidityPeriod = Carbon::now()->addDays(intval($productDetail['loan_term']))->format('M.d,Y');
        $sanctionLetterParams->loanAmountSanctioned = $amount;
        $sanctionLetterParams->availabilityPeriod = $productDetail['loan_term'];
        $sanctionLetterParams->interest = $totalInterestRate;
        $sanctionLetterParams->totalInterestAmount = $product->interestsCalc($loanAmount);
        $sanctionLetterParams->processingFees = $product->processingFeesGst($loanAmount);
        $sanctionLetterParams->repayment = $product->totalRepaymentAmount($loanAmount);
        $sanctionLetterParams->delayedPaymentCharges = $product->overdue_rate;
        $sanctionLetterParams->DPNReferenceNO = $DPNReferenceNO;
        $sanctionLetterParams->termsAcceptedAt = Carbon::now()->format('d-m-Y H:i:s');
        $sanctionLetterParams->deviceName = $clientInfo['deviceName'];
        $sanctionLetterParams->deviceId = $clientInfo['deviceId'];
        $sanctionLetterParams->headerImg = in_array($merchant->nbfc,[Merchant::NBFC_AGLOW,Merchant::NBFC_PAWAN]) ? $merchant->nbfc : null;
        $sanctionLetterParams->productName = $product->product_name;
        return $this->getSanctionLetter($sanctionLetterParams);
    }


    /**
     * 贷后 sanction letter
     * @param SanctionLetterForm $form
     * @return bool
     */
    public function getSanctionLetterAfter(SanctionLetterForm $form)
    {
        /** @var UserLoanOrder $order */
        $order = UserLoanOrder::find()->where(['user_id' => $form->userID, 'id' => $form->orderID])->one();
        if(is_null($order))
        {
            return false;
        }
        $loanAccountSetting = $order->loanFund->loanAccountSetting;

        //如果没有对应nbfc配置，则认为是pawan
        if(is_null($loanAccountSetting))
        {
            $form->productId = $order->product_id;
            $form->amount = CommonHelper::CentsToUnit($order->disbursalAmount());
            $form->clientInfo = [
                'deviceName' => $order->clientInfoLog->device_name,
                'deviceId' => $order->clientInfoLog->device_id,
            ];
            $DPNReferenceNO = 'order_'  . (new Hashids(['salt'=> 'ag', 'minHashLength'=> 16]))->encode($order->id);
            return $this->getSanctionLetterBefore($form, $DPNReferenceNO);
        }
        if(PayAccountSetting::SERVICE_TYPE_AGLOW == $loanAccountSetting->service_type)
        {
            /** @var LoanLicenceAglowOrder $aglowOrder */
            $aglowOrder = LoanLicenceAglowOrder::find()
                ->where(['user_id' => $form->userID, 'order_id' => $form->orderID])
                ->andWhere(['>=', 'status', LoanLicenceAglowOrder::STATUS_LOAN_STATUS_SUCCESS])
                ->one();
            if(is_null($aglowOrder))
            {
                return false;
            }
            $aglowService = new AglowService($aglowOrder->payAccountSetting);
            $sanctionLetterParams = $aglowService->getSanctionLetterParams($order, $aglowOrder->loan_account_no);
            return $this->getSanctionLetter($sanctionLetterParams);

        }elseif (in_array($loanAccountSetting->service_type, [PayAccountSetting::SERVICE_TYPE_KUDOS]))
        {
            /** @var LoanKudosOrder $kudosOrder */
            $kudosOrder = LoanKudosOrder::find()
                ->where(['user_id' => $form->userID, 'order_id' => $form->orderID])
                ->andWhere(['>=', 'kudos_status', LoanStatus::BORROWER_INFO()->getValue()])
                ->one();
            if(is_null($kudosOrder))
            {
                return false;
            }
            $kudosService = new KudosService($kudosOrder->payAccountSetting);
            $sanctionLetterParams = $kudosService->getSanctionLetterParams($order, $kudosOrder->kudos_loan_id);
            return $this->getSanctionLetter($sanctionLetterParams);
        }


    }



    /**
     * sanction letter api
     * @param SanctionLetterApiForm $form
     * @return bool
     */
    public function getLoanSanctionLetterApi(SanctionLetterApiForm $form)
    {
        /** @var UserLoanOrder $order */
        $order = UserLoanOrder::find()->where(['order_uuid' => $form->orderID])->one();
        if(is_null($order))
        {
            return false;
        }

        $loanAccountSetting = $order->loanFund->loanAccountSetting;
        //如果没有对应nbfc配置，则认为是pawan
        if(is_null($loanAccountSetting))
        {
            $sanctionForm = new SanctionLetterForm();
            $sanctionForm->userID = $order->user_id;
            $sanctionForm->productId = $order->product_id;
            $sanctionForm->amount = CommonHelper::CentsToUnit($order->disbursalAmount());
            $sanctionForm->clientInfo = [
                'deviceName' => $order->clientInfoLog->device_name,
                'deviceId' => $order->clientInfoLog->device_id,
            ];
            $DPNReferenceNO = 'order_'  . (new Hashids(['salt'=> 'ag', 'minHashLength'=> 16]))->encode($order->id);
            return $this->getSanctionLetterBefore($sanctionForm, $DPNReferenceNO);
        }

        if(PayAccountSetting::SERVICE_TYPE_AGLOW == $loanAccountSetting->service_type)
        {
            /** @var LoanLicenceAglowOrder $aglowOrder */
            $aglowOrder = LoanLicenceAglowOrder::find()
                ->where(['user_id' => $order->user_id, 'order_id' => $order->id])
                ->andWhere(['>=', 'status', LoanLicenceAglowOrder::STATUS_LOAN_STATUS_SUCCESS])
                ->one();
            if(is_null($aglowOrder))
            {
                return false;
            }
            $aglowService = new AglowService($aglowOrder->payAccountSetting);
            $sanctionLetterParams = $aglowService->getSanctionLetterParams($order, $aglowOrder->loan_account_no);
            return $this->getSanctionLetter($sanctionLetterParams);

        }elseif (in_array($loanAccountSetting->service_type, [PayAccountSetting::SERVICE_TYPE_KUDOS]))
        {
            /** @var LoanKudosOrder $kudosOrder */
            $kudosOrder = LoanKudosOrder::find()
                ->where(['user_id' => $order->user_id, 'order_id' => $order->id])
                ->andWhere(['>=', 'kudos_status', LoanStatus::BORROWER_INFO()->getValue()])
                ->one();
            if(is_null($kudosOrder))
            {
                return false;
            }
            $kudosService = new KudosService($kudosOrder->payAccountSetting);
            $sanctionLetterParams = $kudosService->getSanctionLetterParams($order, $kudosOrder->kudos_loan_id);
            return $this->getSanctionLetter($sanctionLetterParams);
        }

    }


    /**
     * 获取sanction letter pdf内容
     * @param SanctionLetterParams $sanctionLetterParams
     * @return string
     */
    public function getSanctionLetterPdfContent(SanctionLetterParams $sanctionLetterParams)
    {
        $this->getSanctionLetter($sanctionLetterParams);
        $fileData = $this->getResult();
        $result = <<<HTML
<html xmlns=http://www.w3.org/1999/xhtml> 
<head>
</head>
<body>
  <img src="http://sashakt-rupee.oss-ap-south-1.aliyuncs.com/sanctionletter/aglow.png">
  <div>
    <p>
      <span>Date: {$fileData['date']}</span>
    </p>
    <p>
      <span>Loan Account Number: {$fileData['DPNReferenceNO']}</span>
    </p>
    <h1>
      <span>Subject: Sanction Letter/Approval for Loan</span>
    </h1>
    <p>
      <span>Dear Customer,</span>
    </p>
    <p>
      <span>We are pleased to inform that you are eligible for a loan facility from us as per following terms:</span>
    </p>
    <table border="1" cellspacing="0" width="100%">
      <tr>
        <td width = 5%>
          <p>
                <span>1.</span>
          </p>
        </td>
        <td width = 40%>
          <p>
            <span>Loan application date</span>
          </p>
        </td>
        <td width = 55%>
          <p>
            <span>{$fileData['loanApplicationDate']}</span>
          </p>
        </td>
      </tr>
      <tr>
        <td>
          <p>
            <span>2.</span>
          </p>
        </td>
        <td>
          <p>
            <span>Details of the Digital/App Partner</span>
          </p>
        </td>
        <td>{$fileData['sanctionLetterDetail']}</td>
      </tr>
      <tr>
        <td>
          <p>
            <span>3.</span>
          </p>
        </td>
        <td>
          <p>
            <span>Details of Borrower(s)</span>
          </p>
        </td>
        <td>
          <p>
            <span>{$fileData['borrowerDetail']}</span>
          </p>
        </td>
      </tr>
      <tr>
        <td>
          <p>
            <span>4.</span>
          </p>
        </td>
        <td>
          <p>
            <span>Details of Lender</span>
          </p>
        </td>
        <td>
          <p>
            <span>{$fileData['lenderDetail']}</span>
          </p>
        </td>
      </tr>
      <tr>
        <td>
          <p>
            <span>5.</span>
          </p>
        </td>
        <td>
          <p>
            <span>Validity period of this offer</span>
          </p>
        </td>
        <td>
          <p>
            <span>{$fileData['offerValidityPeriod']}</span>
          </p>
        </td>
      </tr>
      <tr>
        <td>
          <p>
            <span>6.</span>
          </p>
        </td>
        <td>
          <p>
            <span>Purpose of the loan</span>
          </p>
        </td>
        <td>
          <p>
            <span>{$fileData['loanPurpose']}</span>
          </p>
        </td>
      </tr>
      <tr>
        <td>
          <p>
            <span>7.</span>
          </p>
        </td>
        <td>
          <p>
            <span>Loan amount sanctioned</span>
          </p>
        </td>
        <td>
          <p>
            <span>{$fileData['loanAmountSanctioned']}</span>
          </p>
        </td>
      </tr>
      <tr>
        <td>
          <p>
            <span>8.</span>
          </p>
        </td>
        <td>
          <p>
            <span>Availability period</span>
          </p>
        </td>
        <td>
          <p>
            <span>{$fileData['availabilityPeriod']}</span>
          </p>
        </td>
      </tr>
      <tr>
        <td>
          <p>
            <span>9.</span>
          </p>
        </td>
        <td>
          <p>
            <span>Term</span>
          </p>
        </td>
        <td>
          <p>
            <span>{$fileData['term']}</span>
          </p>
        </td>
      </tr>
      <tr>
        <td>
          <p>
            <span>10.</span>
          </p>
        </td>
        <td>
          <p>
            <span>Rate of Interest</span>
          </p>
        </td>
        <td>
          <p>
            <span>{$fileData['interest']}</span>
          </p>
        </td>
      </tr>
      <tr>
        <td>
          <p>
            <span>11.</span>
          </p>
        </td>
        <td>
          <p>
            <span>Total Interest Amount</span>
          </p>
        </td>
        <td>
          <p>
            <span>{$fileData['totalInterestAmount']}</span>
          </p>
        </td>
      </tr>
      <tr>
        <td>
          <p>
            <span>12.</span>
          </p>
        </td>
        <td>
          <p>
            <span>Processing fees charged by Digital/App Partner</span>
          </p>
        </td>
        <td>
          <p>
            <span>{$fileData['processingFees']}</span>
          </p>
        </td>
      </tr>
      <tr>
        <td>
          <p>
            <span>13.</span>
          </p>
        </td>
        <td>
          <p>
            <span>Repayment</span>
          </p>
        </td>
        <td>
          <p>
            <span>{$fileData['repayment']}</span>
          </p>
        </td>
      </tr>
      <tr>
        <td>
          <p>
            <span>14.</span>
          </p>
        </td>
        <td>
          <p>
            <span>Monthly installment amount</span>
          </p>
        </td>
        <td>
          <p>
            <span>{$fileData['monthlyInstallmentAmount']}</span>
          </p>
        </td>
      </tr>
      <tr>
        <td>
          <p>
            <span>15.</span>
          </p>
        </td>
        <td>
          <p>
            <span>Prepayment charges</span>
          </p>
        </td>
        <td>
          <p>
            <span>{$fileData['prepaymentCharges']}</span>
          </p>
        </td>
      </tr>
      <tr>
        <td>
          <p>
            <span>16.</span>
          </p>
        </td>
        <td>
          <p>
            <span>Overdue Panel Interest</span>
          </p>
        </td>
        <td>
          <p>
            <span>{$fileData['delayedPaymentCharges']}</span>
          </p>
        </td>
      </tr>
      <tr>
        <td>
          <p>
            <span>17.</span>
          </p>
        </td>
        <td>
          <p>
            <span>Cheque /ECS dishonour charges</span>
          </p>
        </td>
        <td>
          <p>
            <span>{$fileData['ECSDishonourCharges']}</span>
          </p>
        </td>
      </tr>
      <tr>
        <td>
          <p>
            <span>18.</span>
          </p>
        </td>
        <td>
          <p>
            <span>Other charges</span>
          </p>
        </td>
        <td>
          <p>
            <span>{$fileData['otherCharges']}</span>
          </p>
        </td>
      </tr>
      <tr>
        <td>
          <p>
            <span>19.</span>
          </p>
        </td>
        <td>
          <p>
            <span>Documentation</span>
          </p>
        </td>
        <td>
          <p>
            <span>{$fileData['documentation']}</span>
          </p>
        </td>
      </tr>
      <tr>
        <td>
          <p>
            <span>20.</span>
          </p>
        </td>
        <td>
          <p>
            <span>Loan disbursement</span>
          </p>
        </td>
        <td>
          <p>
            <span>{$fileData['loanDisbursement']}</span>
          </p>
        </td>
      </tr>
    </table>
    <p>
      <span>The terms of this loan sanction shall also be governed by General Terms and Conditions, copies of which is
        also available on {$fileData['technicalServiceProviders']} website, which you may kindly read before confirming your acceptance. The said
        documents are incorporated here. The Borrower's acceptance to the terms of this letter and the General Terms and
        Conditions should be informed to the Lender ({$fileData['technicalServiceProviders']}) by submission of a Most
        Important Documents (KYC) with the terms understood by the Borrower. Further, each of the Borrower shall be
        jointly and severally responsible for compliance to the terms of this loan sanction and for repayment of the
        loan amount disbursed.</span>
    </p>
    <p>
      <span>This sanction letter will only be a letter of offer and shall stand revoked and cancelled, if there are any
        material changes in the proposal for which the Loan is sanctioned or; If any event occurs which, in the {$fileData['technicalServiceProviders']}
        sole opinion is prejudicial to the {$fileData['technicalServiceProviders']} interest or is likely to affect the financial condition of the
        Borrower or his / her/ their ability to perform any obligations under the loan or; any statement made in the loan application or representation 
        made is found to be incorrect or untrue or material fact has concealed or; upon completion of the validity period of this offer unless extended by us in writing.</span>
    </p>
    <p>
      <span>We are pleased to inform that you are eligible for a loan facility from us as per following terms:</span>
    </p>
      </p>
      <p>
        <span>Agreed and Accepted by the Borrower:</span>
      </p>
      <p>
        <span>Name: {$fileData['name']}</span>
      </p>
      <p>
        <span>DPN Reference No: {$fileData['DPNReferenceNO']}</span>
      </p>
      <p>
        <span>Terms Accepted at: {$fileData['termsAcceptedAt']}</span>
      </p>
      <p>
        <span>Device: {$fileData['device']}</span>
      </p>
      <p>
        <span>Device ID: {$fileData['deviceId']}</span>
      </p>
      <div style="border:1px solid #000"></div>
      <div style="margin-top: 0.4rem;">
        <h2 style="text-align: center;">Aglow Fintrade Private Limited</h2>
        <p style="text-align: center;">
          Digital Lending Branch: - 205, D R Chambers, 12/56, Desh Bandu
          Gupta Road, Karol Bagh, New Delhi-110005
        </p>
        <p style="text-align: center;">UID:- U67190DL1994PTC060061, Website: www.aglowfin.com</p>
    </div>
   </div>
</body>
</html>
HTML;

        return $result;
    }
}

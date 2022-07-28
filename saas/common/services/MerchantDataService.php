<?php

namespace common\services;

use backend\models\Merchant;
use Carbon\Carbon;
use common\helpers\CommonHelper;
use common\helpers\EncryptData;
use common\models\agreement\ChiefLoanParams;
use common\models\agreement\SanctionLetterParams;
use common\models\enum\Gender;
use common\models\enum\Marital;
use common\models\order\UserLoanOrder;
use common\models\user\LoanPerson;
use common\models\user\UserCreditReportOcrPan;
use common\services\agreement\AgreementService;
use common\services\order\OrderService;
use kartik\mpdf\Pdf;
use light\hashids\Hashids;
use yii;

class MerchantDataService extends BaseService
{

    public function getPdfStr($title, $content)
    {
        $file = new Pdf([
            'mode'        => Pdf::MODE_CORE,
            'format'      => Pdf::FORMAT_A4,
            'orientation' => Pdf::ORIENT_PORTRAIT,
            'destination' => Pdf::DEST_STRING,
            'content'     => $content,
            'cssFile'     => '@vendor/kartik-v/yii2-mpdf/assets/kv-mpdf-bootstrap.min.css',
            'cssInline'   => '.kv-heading-1{font-size:18px}',
            'options'     => ['title' => 'Credit Report'],
            'methods'     => [
                'SetHeader' => [$title],
                'SetFooter' => ['{PAGENO}'],
            ],
        ]);

        return $file->render();
    }

    public function getSanctionLetterContent(UserLoanOrder $order)
    {
        $sanctionLetterParams = $this->getSanctionLetterParams($order);
        $result = $this->getSanctionLetterPdfContent($sanctionLetterParams);
        return $result;
    }

    public function getChiefLoanContent(UserLoanOrder $order, $pan_url, $aadhaar_front_url, $aadhaar_back_url, $livingImgUrl)
    {
        $chiefLoanParams = $this->getChiefLoanParams($order, $pan_url, $aadhaar_front_url, $aadhaar_back_url, $livingImgUrl);
        $result = $this->getChiefLoanPdfContent($chiefLoanParams);
        return $result;
    }

    /**
     * 获取sanction letter的参数
     * @param UserLoanOrder $order
     * @return SanctionLetterParams
     */
    public function getSanctionLetterParams(UserLoanOrder $order)
    {
        $loanPerson = LoanPerson::findOne($order->user_id);
        //$personAge = Carbon::rawCreateFromFormat('Y-m-d', $loanPerson->birthday)->age;
        $workInfo = $loanPerson->userWorkInfo;
        $nbfc = Merchant::NBFC_ACEMONEY;
        $orderService = new OrderService($order);

        $sanctionLetterParams = new SanctionLetterParams();
        $sanctionLetterParams->date = date('M.d,Y', $order->loan_time);
        $sanctionLetterParams->customerId =  'customer_' . (new Hashids(['salt'=> 'ag', 'minHashLength'=> 16]))->encode($order->user_id);
        $sanctionLetterParams->loanApplicationDate = date('M.d,Y h:i a', $order->loan_time);
        $sanctionLetterParams->companyName = 'Acemoney (India) Limited';
        $sanctionLetterParams->companyAddr = 'Office No 112A, First Floor, PP Trade Center, Above Kalyan Jewellers, Netaji Subhash Place, Delhi-110034';
        $sanctionLetterParams->customerName = $loanPerson->name;
        $sanctionLetterParams->customerAge = 10;
        $sanctionLetterParams->residentialDetailAddress = $workInfo->residential_detail_address ?? '--';
        $sanctionLetterParams->residentialAddress2 = $workInfo->residential_address2 ?? '--';
        $sanctionLetterParams->residentialAddress1 = $workInfo->residential_address1 ?? '--';
        $sanctionLetterParams->residentialPincode = $workInfo->residential_pincode ?? '--';
        $sanctionLetterParams->nbfcName = 'X10 Financial Services Limited';
        $sanctionLetterParams->nbfcAddr = '3rd Floor Kh No 385, Opp Corporation Bank, 100 Ft Road, Ghitorni, New Delhi 110030 India';
        $sanctionLetterParams->nbfcShortName = 'X10 Financial Services Ltd';
        $sanctionLetterParams->offerValidityPeriod = date('M.d,Y', strtotime($orderService->repaymentTime()));
        $sanctionLetterParams->loanAmountSanctioned = CommonHelper::CentsToUnit($orderService->loanAmount());
        $sanctionLetterParams->availabilityPeriod = $orderService->totalLoanTerm();
        $sanctionLetterParams->interest = $order->yearRate();
        $sanctionLetterParams->totalInterestAmount = CommonHelper::CentsToUnit($orderService->totalInterests());
        $sanctionLetterParams->processingFees = CommonHelper::CentsToUnit($orderService->processFeeAndGst());
        $sanctionLetterParams->repayment = CommonHelper::CentsToUnit($orderService->loanAmount() + $orderService->totalInterests());
        $sanctionLetterParams->delayedPaymentCharges = $order->overdue_rate;
        $sanctionLetterParams->DPNReferenceNO = $order->order_uuid;
        $sanctionLetterParams->termsAcceptedAt = date('d-m-Y H:i:s', $order->loan_time);
        $sanctionLetterParams->deviceName = $order->clientInfoLog->device_name ?? '--';
        $sanctionLetterParams->deviceId = $order->clientInfoLog->device_id ?? '--';
        $sanctionLetterParams->headerImg = $nbfc;
        $sanctionLetterParams->productName = $order->productSetting->product_name ?? '--';
        $sanctionLetterParams->appName = $order->clientInfoLog->package_name;

        return $sanctionLetterParams;
    }

    /**
     * 获取chiefloan的参数
     * @param UserLoanOrder $order
     * @param $pan_url
     * @param $aadhaar_front_url
     * @param $aadhaar_back_url
     * @param $livingImgUrl
     * @return chiefLoanParams
     */
    public function getChiefLoanParams(UserLoanOrder $order , $pan_url, $aadhaar_front_url, $aadhaar_back_url, $livingImgUrl)
    {
        $chiefLoanParams = new ChiefLoanParams();
        $chiefLoanParams->loanDate = date('Y-m-d', $order->loan_time);
        $chiefLoanParams->loanTime = date('H:i:s', $order->loan_time);
        $chiefLoanParams->realName = $order->loanPerson->name;
        $chiefLoanParams->fatherName = $order->userCreditechOCRPan->father_name ?? '';
        $chiefLoanParams->birthday = $order->loanPerson->birthday;
        $chiefLoanParams->gender = Gender::$map[$order->loanPerson->gender] ?? '';
        $chiefLoanParams->maritalStatus = Marital::$map[$order->userBasicInfo->marital_status] ?? '';
        $chiefLoanParams->aadhaarNo = $order->loanPerson->check_code ? EncryptData::decrypt($order->loanPerson->check_code) : '';
        $chiefLoanParams->panCardNo = $order->loanPerson->pan_code ?? '';
        $chiefLoanParams->address = $order->userCreditechOCRAadhaar->address ?? '';
        $chiefLoanParams->phone = $order->loanPerson->phone;
        $chiefLoanParams->firstContactName = $order->userContact->name ?? '';
        $chiefLoanParams->firstContactPhone = $order->userContact->phone ?? '';
        $chiefLoanParams->secondContactName = $order->userContact->other_name ?? '';
        $chiefLoanParams->secondContactPhone = $order->userContact->other_phone ?? '';
        $chiefLoanParams->accountName = $order->userBankAccount->bank_name ?? '';
        $chiefLoanParams->bankAccount = $order->userBankAccount->account ?? '';
        $chiefLoanParams->ifsc = $order->userBankAccount->ifsc ?? '';
        $chiefLoanParams->loanId = $order->order_uuid;
        $chiefLoanParams->city = $order->userCreditechOCRAadhaar->city ?? '';
        $chiefLoanParams->loanAmount = CommonHelper::CentsToUnit($order->amount);
        $chiefLoanParams->rateOfInterest = $order->day_rate;
        $chiefLoanParams->loanPurpose = 'Bills';
        $chiefLoanParams->processingFee = CommonHelper::CentsToUnit($order->cost_fee);
        $chiefLoanParams->penalty = $order->overdue_rate;
        $chiefLoanParams->repaymentAmount = CommonHelper::CentsToUnit($order->userLoanOrderRepayment->true_total_money);
        $chiefLoanParams->panImgUrl = $pan_url;
        $chiefLoanParams->aadhaarFrontImgUrl = $aadhaar_front_url;
        $chiefLoanParams->aadhaarBackImgUrl = $aadhaar_back_url;
        $chiefLoanParams->livingImgUrl = $livingImgUrl;
        $chiefLoanParams->cycle = $order->loan_term;
        return $chiefLoanParams;
    }



    /**
     * @param SanctionLetterParams $sanctionLetterParams
     * @return bool
     */
    public  function getSanctionLetter(SanctionLetterParams $sanctionLetterParams)
    {
        $result = [
            'date'                      => $sanctionLetterParams->date,
            'customerId'                => $sanctionLetterParams->customerId,
            'loanApplicationDate'       => $sanctionLetterParams->loanApplicationDate,
            'sanctionLetterDetail'      => "<p>{$sanctionLetterParams->companyName}</p><p>{$sanctionLetterParams->companyAddr}</p>",
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
            'documentation'             => '<p>Submit to the Lender the following:</p><p>1. Most Important Documents (KYC)</p>',
            'loanDisbursement'          => 'Post completion of the documentation you will eligible for the loan disbursement.',
            'technicalServiceProviders' => $sanctionLetterParams->nbfcShortName,
            'name'                      => $sanctionLetterParams->customerName,
            'DPNReferenceNO'            => $sanctionLetterParams->DPNReferenceNO,
            'termsAcceptedAt'           => $sanctionLetterParams->termsAcceptedAt,
            'device'                    => $sanctionLetterParams->deviceName,
            'deviceId'                  => $sanctionLetterParams->deviceId,
            'headImg'                   => $sanctionLetterParams->headerImg, //1 aglow 2 pawan
            'productName'               => $sanctionLetterParams->productName,
            'appName'                   => $sanctionLetterParams->appName,
        ];
        $this->setResult($result);
        return true;
    }

    /**
     * @param ChiefLoanParams $chiefLoanParams
     * @return bool
     */
    public  function getChiefLoan(ChiefLoanParams $chiefLoanParams)
    {
        $result = [
            'loanDate'                  => $chiefLoanParams->loanDate,
            'loanTime'                  => $chiefLoanParams->loanTime,
            'realName'                  => $chiefLoanParams->realName,
            'fatherName'                => $chiefLoanParams->fatherName,
            'birthday'                  => $chiefLoanParams->birthday,
            'gender'                    => $chiefLoanParams->gender,
            'maritalStatus'             => $chiefLoanParams->maritalStatus,
            'aadhaarNo'                 => $chiefLoanParams->aadhaarNo,
            'panCardNo'                 => $chiefLoanParams->panCardNo,
            'address'                   => $chiefLoanParams->address,
            'phone'                     => $chiefLoanParams->phone,
            'firstContactName'          => $chiefLoanParams->firstContactName,
            'firstContactPhone'         => $chiefLoanParams->firstContactPhone,
            'secondContactName'         => $chiefLoanParams->secondContactName,
            'secondContactPhone'        => $chiefLoanParams->secondContactPhone,
            'accountName'               => $chiefLoanParams->accountName,
            'bankAccount'               => $chiefLoanParams->bankAccount,
            'ifsc'                      => $chiefLoanParams->ifsc,
            'loanId'                    => $chiefLoanParams->loanId,
            'city'                      => $chiefLoanParams->city,
            'loanAmount'                => $chiefLoanParams->loanAmount,
            'rateOfInterest'            => $chiefLoanParams->rateOfInterest,
            'loanPurpose'               => $chiefLoanParams->loanPurpose,
            'processingFee'             => $chiefLoanParams->processingFee,
            'penalty'                   => $chiefLoanParams->penalty,
            'repaymentAmount'           => $chiefLoanParams->repaymentAmount,
            'panImgUrl'                 => $chiefLoanParams->panImgUrl,
            'aadhaarFrontImgUrl'        => $chiefLoanParams->aadhaarFrontImgUrl,
            'aadhaarBackImgUrl'         => $chiefLoanParams->aadhaarBackImgUrl,
            'livingImgUrl'              => $chiefLoanParams->livingImgUrl,
            'cycle'                     => $chiefLoanParams->cycle,
        ];
        $this->setResult($result);
        return true;
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
  <div>
    <p>
      <span>Date: {$fileData['date']}</span>
    </p>
    <p>
      <span>Loan Application ID: {$fileData['DPNReferenceNO']}</span>
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
        <td>{$fileData['appName']}</td>
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
            <span><b>Overdue Penal Interest</b></span>
          </p>
        </td>
        <td>
          <p>
            <span><b>{$fileData['delayedPaymentCharges']}</b></span>
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
      <span>This sanction letter will only be a letter of offer and shall stand revoked and cancelled, if there are any material changes
       in the proposal for which the Loan is sanctioned or; If any event occurs which, in the BCL Enterprises Limited sole opinion is prejudicial
        to the BCL Enterprises Limited interest or is likely to affect the financial condition of the Borrower or his / her/ their ability to perform
         any obligations under the loan or; any statement made in the loan application or representation made is found to be incorrect or untrue or material
          fact has concealed or; upon completion of the validity period of this offer unless extended by us in writing.
       </span>
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
        <span>Accepted at: {$fileData['termsAcceptedAt']}</span>
      </p>
      <p>
        <span>Device: {$fileData['device']}</span>
      </p>
      <p>
        <span>Device ID: {$fileData['deviceId']}</span>
      </p>
   </div>
</body>
</html>
HTML;

        return $result;
    }

    /**
     * 获取chief loan pdf内容
     * @param ChiefLoanParams $chiefLoanParams
     * @return string
     */
    public function getChiefLoanPdfContent(ChiefLoanParams $chiefLoanParams)
    {
        $this->getChiefLoan($chiefLoanParams);
        $fileData = $this->getResult();
        $result = <<<HTML
<html xmlns=http://www.w3.org/1999/xhtml> 
<head>
</head>
<body>
	<h3><center>LOAN AGREEMENT</center></h3>
<p>This Agreement is made on {$fileData['loanDate']}.</p>
<p>BV AND BETWEEN:</p>
<p>M/s Kailash Auto Finance Limited through its website ChiefLoan.</p>
<p>(hereinafter referred to as the “Kailash Auto Finance Limited” which expression shall, unless repugnant to or inconsistent with the context, mean and include their successors and permitted assignees of the FIRST PART).</p>
<p>and</p>
<p>Mr./Ms. {$fileData['realName']} (hereinafter referred to as the “Borrower” which expression shall, unless repugnant to or inconsistent with the context, mean and include their successors and permitted assignees of the SECOND PART).</p>
<p>[Borrower and Kailash Auto Finance Limited shall together be referred to as the “Parties” and severally as the “Party”]</p>
<p>Witnesses</p>
<p>Whereas, ChiefLoan is an online social lending platform by M/s Kailash Auto Finance Limited that provides loan facility as per the terms provided under the agreements made available on the application (viz app) and website of the company as the case may be, in relation to the lending/ borrowing transactions made through ChiefLoan website/platform.</p>
<p>Whereas, a person who creates an account with ChiefLoan can find a suitable Kailash Auto Finance Limited /borrower. On freezing of the loan transaction for the borrower or closure of the bid for the Kailash Auto Finance Limited, as the case may be the terms between the borrower a Kailash Auto Finance Limited are materialized by entering into this agreement which is binding upon both the parties.</p>
<table border="1" cellspacing="0" width="100%">
  <tr>
    <td colspan="3"><center><h3>LOAN APPLICATION FORM</h3></center></td>
  </tr>
  <tr>
    <td width="20%">ACCOUNT TVPE:</td>
    <td colspan="2">Save</td>
  </tr>
  <tr>
    <td width="20%">NAME:</td>
    <td colspan="2">{$fileData['realName']}</td>
  </tr>
  <tr>
    <td width="20%">FATHER/SPOUSE NAME:</td>
    <td colspan="2">{$fileData['fatherName']}</td>
  </tr>
  <tr>
    <td width="20%">DATE OF BIRTH:</td>
    <td colspan="2">{$fileData['birthday']}</td>
  </tr>
  <tr>
    <td width="20%">GENDER:</td>
    <td colspan="2">{$fileData['gender']}</td>
  </tr>
  <tr>
    <td width="20%">MARITAL STATUS:</td>
    <td colspan="2">{$fileData['maritalStatus']}</td>
  </tr>
  <tr>
    <td width="20%">OCCUPATION:</td>
    <td colspan="2"></td>
  </tr>
  <tr>
    <td width="20%">NATIONALITV:</td>
    <td colspan="2">Indian</td>
  </tr>
  <tr>
    <td width="20%">RESIDENTIAL STATUS:</td>
    <td colspan="2">OWNED</td>
  </tr>
  <tr>
    <td width="20%">PROOF OF IDENTITV:</td>
    <td colspan="2">{$fileData['aadhaarNo']}</td>
  </tr>
  <tr>
    <td width="20%">PAN:</td>
    <td colspan="2">{$fileData['panCardNo']}</td>
  </tr>
  <tr>
    <td width="20%">PERMANENT ADDRESS:</td>
    <td colspan="2"></td>
  </tr>
  <tr>
    <td width="20%">CURRENT ADDRESS:</td>
    <td colspan="2">{$fileData['address']}</td>
  </tr>
  <tr>
    <td width="20%">PHONE NUMBER:</td>
    <td colspan="2">{$fileData['phone']}</td>
  </tr>
  <tr>
    <td width="20%">Grantors Name No.1:</td>
    <td colspan="2">{$fileData['firstContactName']}</td>
  </tr>
  <tr>
    <td width="20%">Guarantor No.1’s Number:</td>
    <td colspan="2">{$fileData['firstContactPhone']}</td>
  </tr>
  <tr>
    <td width="20%">Guarantor Name No.2:</td>
    <td colspan="2">{$fileData['secondContactName']}</td>
  </tr>
  <tr>
    <td width="20%">Guarantor No.2’s Number:</td>
    <td colspan="2">{$fileData['secondContactPhone']}</td>
  </tr>
  <tr>
    <td width="20%">NAME OF BANH:</td>
    <td colspan="2">{$fileData['accountName']}</td>
  </tr>
  <tr>
    <td width="20%">BANH A/C NO:</td>
    <td colspan="2">{$fileData['bankAccount']}</td>
  </tr>
  <tr>
    <td width="20%">IFSC:</td>
    <td colspan="2">{$fileData['ifsc']}</td>
  </tr>
  <tr>
    <td width="20%">Any other bank account:</td>
    <td colspan="2"></td>
  </tr>
  <tr>
    <td width="20%">NAME OF BANH:</td>
    <td colspan="2"></td>
  </tr>
  <tr>
    <td width="20%">BANH A/C NO:</td>
    <td colspan="2"></td>
  </tr>
  <tr>
    <td width="20%">IFSC:</td>
    <td colspan="2"></td>
  </tr>
</table>

<table border="1" cellspacing="0" width="100%">
  <tr>
    <td colspan="3"><center><h3>SUMMARV OF THE LOAN TERMS</h3></center></td>
  </tr>
  <tr>
    <td width="10%">S. No.</td>
    <td width="30%">Particulars</td>
    <td>Details</td>
  </tr>
  <tr>
    <td width="10%">1.</td>
    <td width="30%">LOAN ID / SERIAL NO.</td>
    <td>{$fileData['loanId']}</td>
  </tr>
  <tr>
    <td width="10%">2.</td>
    <td width="30%">CITV</td>
    <td>{$fileData['city']}</td>
  </tr>
</table>
<p>I understand the terms of the loan to be provided to me, Kailash Auto Finance Limited if approved as per the internal policies and law shall be as specified below (“Loan”):</p>

<table border="1" cellspacing="0" width="100%">
  <tr>
    <td width="40%">PARTICULARS</td>
    <td colspan="4">Details</td>
  </tr>
  <tr>
    <td width="40%">Kailash Auto Finance Limited:</td>
    <td colspan="4">Kailash Auto Finance Limited</td>
  </tr>
  <tr>
    <td width="40%">Platform:</td>
    <td colspan="4">ChiefLoan</td>
  </tr>
  <tr>
    <td width="40%">Loan Amount:</td>
    <td colspan="4">{$fileData['loanAmount']}</td>
  </tr>
  <tr>
    <td width="40%">Rate of Interest:</td>
    <td colspan="4">{$fileData['rateOfInterest']} percent(%) per day</td>
  </tr>
  <tr>
    <td width="40%">Purpose of Loan:</td>
    <td colspan="4">Bills</td>
  </tr>
  <tr>
    <td width="40%">Processing Fees:</td>
    <td width="10%">{$fileData['processingFee']}</td>
    <td width="25%">Default Charges:</td>
    <td width="25%">{$fileData['penalty']} percent(%) per day</td>
  </tr>
  <tr>
    <td width="40%">Full Prepayment Charges:</td>
    <td width="10%">{$fileData['repaymentAmount']}</td>
    <td width="25%">Service Charges:</td>
    <td width="25%">None</td>
  </tr>
  <tr>
    <td width="40%">Banking Details for disbursal of Loan:</td>
    <td colspan="4">As specified in the form above.</td>
  </tr>
</table>

<p>The Borrowers agrees to submit the following documents for availing this facility.</p>

<table border="1" cellspacing="0" width="100%">
  <tr>
    <td width="10%">Sr.no.</td>
    <td width="70%">Documents from the Applicant and the Co- Applicant (if any)</td>
    <td>Status (Tick)</td>
  </tr>
  <tr>
    <td width="10%">1.</td>
    <td width="70%">Pan card or Form 60*</td>
    <td></td>
  </tr>
  <tr>
    <td width="10%">2.</td>
    <td width="70%">Last 3 months bank statements or other income proof</td>
    <td></td>
  </tr>
  <tr>
    <td width="10%">3.</td>
    <td width="70%">Any other document requested by Kailash Auto Finance Limited</td>
    <td></td>
  </tr>
</table>

<p>*Compulsory Requirement</p>
<p>I / We further acknowledge, understand and agree that Kailash Auto Finance Limited</p>
<p>has adopted risk-based pricing, which is arrived by taking into account, broad parameters like the customers financial and credit profile etc. I understand all the terms listed and hereby apply for the said Loan to Kailash Auto Finance Limited.</p>
<p>face recognition</p>
<p><img width="100%" src="{$fileData['panImgUrl']}"></p>
<p><img width="100%" src="{$fileData['aadhaarFrontImgUrl']}"></p>
<p><img width="100%" src="{$fileData['aadhaarBackImgUrl']}"></p>
<p><img width="100%" src="{$fileData['livingImgUrl']}"></p>
<p><b>SELF-DECELERATION AND UNDERTAHING:</b></p>
<p>I hereby apply for the Loan facility from the Kailash Auto Finance Limited as specified above.</p>
<p>I represent that the information and details provided in this Application Form and the documents submitted by me are true, correct and that I have not withheld/suppressed/misrepresented/mislead any information.</p>
<p>I have read and understood and accepted without any alteration, doubt, dispute, demure, the fees and charges applicable to the Loan that I may avail from time to time.</p>
<p>I confirm that no insolvency proceedings or civil suits or any other civil or criminal proceedings for recovery of outstanding dues or any other allegations civil or criminal in nature have been initiated and / or are pending against me.</p>
<p>I hereby authorize Kailash Auto Finance Limited to exchange or share information and details relating to this Application Form its group companies or any third party, as may be required or deemed fit, for the purpose of processing this loan application and/or related offerings or other products / services / recovery of money or any other purpose as they may deem fit which I may apply for from time to time.</p>
<p>I hereby consent to and authorize Kailash Auto Finance Limited to increase or decrease the credit limit assigned to me basis Kailash Auto Finance Limited internal credit policy.</p>
<p>By submitting this Application Form, I hereby expressly authorize Kailash Auto Finance Limited to send me communications regarding various financial products offered by or from Kailash Auto Finance Limited, its group companies and / or third parties through telephone calls / SMS / emails / post etc. including but not limited to promotional communications. And confirm that I shall not challenge receipt of such communications as unsolicited communication, defined under TRAI Regulations on Unsolicited Commercial Communications under the Do Not Call Registry.</p>
<p>In case, If the borrower fails to make the loan re payment in such case ChiefLoan/ Kailash Auto Finance Limited through its representatives/ employee etc. will have authority to call or initiate recovery procedure against me, my contacts references, friends, family, acquaintance or any other person, company, organization as may be available in my phone data, phone book, other source or any other details made available to Kailash Auto Finance Limited / ChiefLoan etc.</p>
<p>I understand and acknowledge that Kailash Auto Finance Limited has the absolute discretion, without assigning any reasons to reject my application and that Kailash Auto Finance Limited is not liable to provide me a reason for the same.</p>
<p>1.That Kailash Auto Finance Limited shall have the right to make disclosure of any information relating to me including personal information, details  in relation to Loan, defaults, security, etc. to the Credit Information Bureau of India (CIBIL) and/or any other governmental/regulatory/statutory or private agency / entity, credit bureau, RBI, CHVCR, including publishing the name as part of willful defaulter's list from time to time, as also use for HVC information verification, credit risk analysis, or for other related purposes.</p>
<p>I agree and accept that Kailash Auto Finance Limited may in its sole discretion, by its self or through authorized persons, advocate, agencies, bureau, etc. verify any information given, check credit references, employment details and obtain credit reports to determine creditworthiness from time to time.</p>
<p>That I have not taken any loan from any other bank/ finance company unless specifically declared by me.</p>
<p>That the funds shall be used for the Purpose specified in above and will not be used for speculative or antisocial purpose.</p>
<p>I have understood and accepted the late payment and other default charges listed above.</p>
<p>I hereby confirm that I contacted Kailash Auto Finance Limited for my requirement of personal loan and no representative of Kailash Auto Finance Limited has emphasized me directly / indirectly to make this application for the Loan.</p>
<p><b>STANDARD TERMS</b></p>
<p>Now therefore, in consideration of the mutual promises, covenants and conditions hereinafter set forth, the receipt and sufficiency of which is hereby acknowledged, the parties hereto agree as follows:
The Borrower can make a request for any loan amount from the platform named ChiefLoan which is secured app run and controlled by the Kailash Auto Finance Limited after scrutinizing the credibility of the borrower grants loan approval, disburse loans, conduct activities for recovery of the money disbursed and which will be governed by the terms and conditions mentioned below read together with the application form, drawdown request and MITC as exchanged between the parties (together referred to as “Transaction Document”)</p>
<p><b>Applicability:</b></p>
<p>The Standard Terms set out hereunder, shall if the Application Form so provides, be applicable to the Facility provided by the Kailash Auto Finance Limited.</p>
<p><b>Definitions and Interpretations:</b></p>
<p>In these Standard Terms unless there is anything repugnant to the subject or context thereof, the expressions listed below, if applicable, shall have the following meanings:</p>
<p>1. “Access Code(s)” means any authentication mode as approved, specified by the Kailash Auto Finance Limited including without limitation combination of user name and password.</p>
<p>2. "Account" means the bank account where the Loan disbursement is requested and more specifically provided under the Application Form or Draw-down Request;</p>
<p>3. "Application Form" means the loan application form submitted by the Borrower to the Kailash Auto Finance Limited for applying and availing of the Facility, together with all other information, particulars, clarifications and declarations, if any, furnished by the Borrower or any other persons from time to time in connection with the Facility</p>
<p>4."Availability Period" means the period between the date of approval of the loan and the date of repayment (or the extension date approved by the lender in its discretion);</p>
<p>5."Borrower" means jointly and severally each applicant and co-applicants (if any) and the term shall include their successors and permitted assigns;</p>
<p>6.“Business Day” means a day which is not a non-working Saturday, Sunday, Public or Bank holiday.</p>
<p>7."Default Rate" means the rate provided as such under the Application Form, the penalty charges/ overdue charges chargeable due to default in payments;</p>
<p>8.“Interest Rate” means the rate of interest at which the said loan facility is provided by the Kailash Auto Finance Limited to the borrower.</p>
<p>9."Draw-down Request" means a request from the Borrower in a form and manner acceptable to the Kailash Auto Finance Limited for seeking disbursement of Loan;</p>
<p>10."Drawing Power" means the threshold limit(s) assessed by the Kailash Auto Finance Limited, in its sole discretion from time to time which shall be within the overall sanctioned limit and shall determine the amount of draw-down that can be requested by the Borrower at any given time under the Facility.</p>
<p>11.Due Date" means such date(s) on which any payment becomes due and payable under the terms of the Transaction Documents (or otherwise);</p>
<p>12. “Facility" means a loan facility extended to the borrower for any purpose stated by him to the Kailash Auto Finance Limited.</p>
<p>13. "Increased Costs" means any cost which is charged to the borrower on account of expenses incurred by the Kailash Auto Finance Limited on account of default, recovery, etc.</p>
<p>14. A reduction in the rate of return from the Loan(s) or on the Kailash Auto Finance Limited's overall capital (including as a result of any reduction in the rate of return on capital brought about by more capital being required to be allocated by the Kailash Auto Finance Limited</p>
<p>15. any additional or increased cost including provisioning as may be required under or as may be set out in RBI regulations or any other such regulations from time to time;</p>
<p>16. A reduction of any amount due and payable under the Transaction Documents;</p>
<p>17.“Kailash Auto Finance Limited” means Kailash Auto Finance Limited as specified in Application Form and shall include its successors and assigns;</p>
<p>18.“Loan" means each disbursement made under the Facility and a fixed amount given by the Kailash Auto Finance Limited to the borrower for fixed time;</p>
<p>19.“MITC" means the most important terms and conditions reiterated by the Borrower at the time of availing the Facility;</p>
<p>20.“Portal” shall mean such platform or portal as described in the Application Form for availing this facility. i.e. ChiefLoan.</p>
<p>21.“Purpose” shall have the same meaning as is provided in the Application Form;</p>
<p>22.“Sanctioning Authority” includes the Reserve Bank of India, Office of Foreign Assets Control of the Department of Treasury of the United States of America, the United Nations Security Council, the European Union, Her Majesty’s Treasury of the United Kingdom or any combination of the foregoing;</p>
<p>23. “Tenure" means the period provided as such under the Application Form.</p>
<p>24. “Repayment” means the repayment of the principal amount and of loan interest thereon, commitment and/or any other charges, fees or other dues payable in terms of this agreement to the Kailash Auto Finance Limited.</p>
<p>25. “Pre-payment” means premature repayment of the loan in partial or full.</p>
<p>26. “Installment” means the amount of monthly payment over the period of loan.</p>
<p>27. “Post Dated Cheques” or “PDCs” means cheques for the amount of the installment drawn by the borrower in favor of the Kailash Auto Finance Limited bearing the dates to match the due date of each installment.</p>
<p>28. “EMI” means Equated Monthly Installments. i.e. fixed amount paid by the borrower every month.</p>
<p>29. “Working day” shall mean the day on which the Banks are open for business in India.
Capitalized terms used in these Standard Terms but not defined herein, shall have the meaning ascribed to such terms under the Application Form or Drawdown Request.</p>
<p><b>Important Clauses of Loan Agreement</b></p>
<p>1. Commencement</p>
<p>This agreement shall come into effect from the date of acceptance of this agreement, by way of clicking the accept option below.</p>
<p>2. Purpose</p>
<p>The borrower hereby confirms that the amount borrowed shall be used for the purpose mentioned by the Borrower in the Application form and no other except otherwise stated by the borrower as alternate purpose. The borrower also understands that misleading the company can lead the borrower into problem.</p>
<p>3. Agreement and terms of the loan</p>
<p>The Kailash Auto Finance Limited has agreed to grant the loan to the borrower a sum of Rs. 5000.00 of which process fees and other charges will be deducted upfront. Also, that in case of default charges for overdue charges/ penalty charges, recovery, legal, etc. will be added to the outstanding dues and Borrower accepts to pay the same.</p>
<p>4.Disbursement of loan</p>
<p>ChiefLoan/Kailash Auto Finance Limited will ensure that the amount collected from the Kailash Auto Finance Limited in the name of the borrower is deposited into the borrower’s designated account within s-5 working days after execution of this document by both the Kailash Auto Finance Limited and borrowers. In case there is a delay in providing the money from Kailash Auto Finance Limited due to unforeseen circumstances, ChiefLoan will intimate the borrower immediately and 
Kailash Auto Finance Limited is provided additional 5 working days to deposit his loan amount with ChiefLoan. In the event repayment is not done within the due date after closure of bid as stated above, ChiefLoan will take necessary steps to reach to other Kailash Auto Finance Limited to offer the remaining amount to the borrower. The borrower can however choose to either take the offered amount or wait till the total loan amount is made available. This however, may take additional 5-10 working days to complete the loan transaction after execution of this agreement by both the Kailash Auto Finance Limited and borrower.</p>
<p>5.When the loan, interest, etc becomes due.</p>
<p>Interest will be calculated as per the details mentioned on the website/application (ChiefLoan) on the loan amount disbursed to the borrower (for more clarity, when the Kailash Auto Finance Limited is meeting the commitment of the borrower in partial, then interest would be calculated for his amount which is lent as mentioned on the website/application (ChiefLoan).</p>
<p>6.Mode of payment of Installment</p>
<p>6a. The Borrower shall make each payment under the Transaction Documents on or before the respective Due Date. No Due Date shall exceed the Tenure of the Facility.</p>
<p>6b. It is irrespective if the Due Date is Business Day or a holiday for any reason, then the Borrower agrees that the payment is made by way of internet banking and no facility of extension of time will be granted for any due date falling on working day/ weekend/holiday/any other day.</p>
<p>6c. All the payments made will be subject to taxation as provided by the government of India whether state or central government and both the parties shall abide by the laws pertaining to taxation as and when required as applicable in the transaction.</p>
<p>6d. Notwithstanding anything to the contrary, the Kailash Auto Finance Limited may, at any time, without assigning any reason, cancel the undisbursed portion of the Facility and can also recall any or all portion of the disbursed Loan on demand. Upon such recall, the Loan and other amounts stipulated by the Kailash Auto Finance Limited shall be payable forthwith with the new deadline or new due date provided by Kailash Auto Finance Limited.</p>
<p>6e. The Borrower will make repayment of the principal amount plus interest, penalty, delay interest, processing charges, taxation, any other charges (as applicable) under the Loan(s) in such proportion and periodicity as may be provided in the Transaction Documents or as communicated by the Kailash Auto Finance Limited from time to time.</p>
<p>7.Interest</p>
<p>The rate of interest applicable to the said loan as the same date upon which the disbursement amount is debited from the bank account of Kailash Auto Finance Limited and will be compounded with the monthly rests on the outstanding balance, namely the balance of loan and unpaid interest and costs, charges and expenses outstanding at the end of the month. Any dispute being raised about the amount due or interest computation will not enable the borrower to withhold payment of any installment. For purpose of computation of interest 30 days shall be considered per calendar month.</p>
<p>Therefor the Borrower shall be obliged to pay interest at the rate of 0.1 percent (%) per day, the "Interest", such interest to be paid together with the capital sum of the loan at the end of the loan period.</p>
<p>Or</p>
<p>The Borrower shall be obliged to pay the Interest Rate On the due date of the repayment, preclosure date (as applicable) in case the borrower exceeds the due date and defaults in repayment in such case delay interest will be charged and the rate of delay interest will differ on day to day basis. The delay interest will be charged s percent (%) per day on principal amount disbursed.</p>
<p>8.Period of disbursement of loan</p>
<p>The loan provided under this agreement will be for 6 days</p>
<p>Covenants / undertakings of</p>
<p>the parties By - Borrower</p>
<p>a)To utilize the entire loan for the required purpose.</p>
<p>b)To promptly notify any event or circumstances, which might operate as a cause of delay in the completion of this agreement.</p>
<p>c)To provide accurate and true information.</p>
<p>d)To repay the required funded amount without any failure.</p>
<p>e)To maintain sufficient balance in the account of the borrower’s bank</p>
<p>f)Due performance of all the terms and conditions provided under this loan agreement.</p>
<p>g)Borrower agree to indemnify and hold ChiefLoan harmless from and against any and all claims, action, liability, cost, loss, damage, endured by Kailash Auto Finance Limited by your access in violation to the listed terms of service and also to the applicable laws, rules and regulations or agreements prevailing from time to time.</p>
<p>h)The collection charges, calling, recovery, legal expenses if any, incurred by Kailash Auto Finance Limited, to be borne by the borrower</p>
<p>i)Cost of initiating legal proceedings.</p>
<p>By- Kailash Auto Finance Limited</p>
<p>a)To provide accurate and true information.</p>
<p>b)To fund the accepted amount to the borrower.</p>
<p>c)To maintain sufficient balance in the account of the drawee bank for payment of share of the borrower loan amount.</p>
<p>d)Due performance of all the terms and conditions provided under this loan agreement.</p>
<p>e)Borrower agree to indemnify and hold ChiefLoan harmless from and against any and all claims, action, liability, cost, loss, damage, endured by ChiefLoan by your access in violation to the listed terms of service and also to the applicable laws, rules and regulations or agreements prevailing from time to time.</p>
<p>10.Events of defaults</p>
<p>Notwithstanding anything to the contrary in this Agreement, if the Borrower defaults in the performance of any obligation under this Agreement, then the Kailash Auto Finance Limited may declare the principal amount, interest, delay interest, penalty, overdue charges, tele-calling charges, recovery charges, legal expenses, other charges owing under this Agreement at that time to be immediately due and payable.</p>
<p>11.Consequence of default</p>
<p>Kailash Auto Finance Limited will take such necessary steps as permitted by law against the borrower to realize the amounts due along with the principal amount, interest, delay interest, penalty , overdue charges, tele-calling charges, recovery charges, legal expenses, other charges at the decided rate and other fees / costs as agreed in this agreement including appointment of collection agents, appointment of attorneys/ consultants, as it thinks fit.</p>
<p>In case of default matter be referred under Arbitration and Conciliation act 2019 (as amended) and the matter be referred to the sole Arbitrator. The sole arbitrator will be appointed by Kailash Auto Finance Limited only the arbitrator be of the choice of Kailash Auto Finance Limited. The award passed by the arbitrator will be considered as a final award and the borrower will neither challenge the appointment of arbitrator nor will challenge the award of the arbitrator. All such legal expenses for recovering the money from the borrower will be borne by the borrower including, principal amount, interest, delay interest, penalty, overdue charges, tele-calling charges, recovery charges, legal expenses, other charges.</p>
<p>In case of any disputes, litigation, subject to Mumbai jurisdiction only.</p>
<p>12. Cancellation</p>
<p>The Kailash Auto Finance Limited reserves the unconditional right to cancel the limits sanctioned without giving any prior notice to the Borrower, on the occurrence of any one or more of the following-</p>
<p>1. in case the Facility (in full or in part) is not disbursed; or</p>
<p>2. in case of deterioration in the creditworthiness of the Borrower (as determined by the Kailash Auto Finance Limited) in any manner whatsoever; or</p>
<p>3. in case of non-compliance of the Transaction Documents.</p>
<p>13. Sever ability</p>
<p>If any provision of this agreement is found to be invalid or unenforceable, then the invalid or unenforceable provision will be deemed superseded by a valid enforceable provision that most closely matches the intent of the original provision and the remainder of the agreement shall continue in effect.</p>
<p>14.Governing laws & Jurisdiction</p>
<p>This agreement will be construed in accordance with and governed by laws of India. The parties have agreed to the exclusive jurisdiction of the courts at Mumbai.</p>
<p>15.Mandatory Arbitration</p>
<p>Any and all disputes or differences between the parties to the agreement, arising out of or in connection with this agreement or its performance shall, so far as it is possible, be settled by negotiations between the parties amicably through consultation.
Any dispute, which could not be settled by the parties through amicable settlement (as provided for under above clause) shall be initiated for settlement by way Arbitration which will be governed by The Arbitration and Conciliation Act, 2019 (as amended). In case of default matter be referred under Arbitration and Conciliation act 2019 (as amended) and the matter be referred to the sole Arbitrator. The sole arbitrator will be appointed by Kailash Auto Finance Limited only. the arbitrator be of the choice of Kailash Auto Finance Limited. The award passed by the arbitrator will be considered as a final award and the borrower will neither challenge the appointment of arbitrator nor will challenge the award of the arbitrator. All such legal expenses for recovering the money from the borrower will be borne by the borrower including, principal amount, interest, delay interest, penalty, overdue charges, tele-calling charges, recovery charges, legal expenses, other charges.</p>
<p>16.Force majeure</p>
<p>No party shall be liable to the other if, and to the extent, that the performance or delay in performance of any of their obligations under this agreement is prevented, restricted, delayed or interfered with, due to circumstances beyond the reasonable control of such party, including but not limited to, Government legislation's, fires, floods, explosions, epidemics, accidents, acts of God, wars, riots, strikes, lockouts, or other concerted acts of workmen, acts of Government and/or shortages of materials. The party claiming an event of force majeure shall promptly notify the other parties in writing, and provide full particulars of the cause or event and the date of first occurrence thereof, as soon as possible after the event and also keep the other parties informed of any further developments. The party so affected shall use its best efforts to remove the cause of non-performance, and the parties shall resume performance hereunder with the utmost dispatch when such cause is removed.</p>
<p>17.Binding effect</p>
<p>All warranties, undertakings and agreements given herein by the parties shall be binding upon the parties and upon its legal representatives and estates. This agreement (together with any amendments or modifications thereof) supersedes all prior discussions and agreements (whether oral or written) between the parties with respect to the transaction.
English shall be used in all correspondence and communications between the Parties.</p>
<p>18.Benefit of the Loan Agreement</p>
<p>The loan agreement shall be binding upon and to ensure to the benefit of each party thereto and its successors or heirs, administrators, as the case may be.</p>
<p>Any delay in exercising or omission to exercise any right, power or remedy accruing to the Kailash Auto Finance Limited under this agreement or any other agreement or document shall not impair any such right, power or remedy and shall not be construed to be a waiver thereof or any acquiescence in any default; nor shall the action or inaction of the Kailash Auto Finance Limited in respect of any default or any acquiescence in any default, affect or impair any right, power or remedy of Kailash Auto Finance Limited in respect of any other default.</p>
<p>19.Notices</p>
<p>Any notice or request to be given or made by a party to the other shall be in writing. All correspondence shall be addressed Kailash Auto Finance Limited will be on
().</p>
<p>20. Acceptance</p>
<p>The parties hereby declare as follows:</p>
<p>1. They have read the entire agreement and shall be bound by all the conditions.</p>
<p>2. This agreement and other documents have been read to by the borrower and has thoroughly understood the contents thereof.</p>
<p>3. They agree that this agreement shall be concluded and become legally binding on the date when it is signed by the parties.</p>
<p>21. Entire Agreement</p>
<p>The parties confirm that this contract contains the full terms of their agreement and that no addition to or variation of the contract shall be of any force and effect unless done in writing and signed by Kailash Auto Finance Limited.</p>
<p>IN WHEREOF the Parties have executed this Agreement as of the day and year first above written.</p>
<p>on behalf of Kailash Auto Finance</p>
<p>Limited on</p>
<p><center>ANNEXURE A</center></p>
<p>THE MOST IMPORTANT TERMS AND CONDITONS - MITC</p>
<p>1.We refer to the application form dated {$fileData['loanDate']} (“ Application Form ”) for grant of the Loan described below.</p>
<p>2.Capitalized terms used but not defined hereunder shall have the meaning ascribed to the term in other Transaction Documents.</p>
<p>3.The Borrower acknowledges and confirms that the below mentioned are the most important terms and conditions in the application for the Loan (and which would apply to the Borrower in respect of the Loan, if the request for the Loan is accepted by Kailash Auto Finance Limited) and they shall be read in conjunction with the Application Form(s), drawdown request(s) and the Standard Terms):</p>

<table border="1" cellspacing="0" width="100%">
  <tr>
    <td width="50%">Borrower</td>
    <td>{$fileData['realName']}</td>
  </tr>
  <tr>
    <td width="50%">Purpose</td>
    <td>{$fileData['loanPurpose']}</td>
  </tr>
  <tr>
    <td width="50%">Tenure</td>
    <td>{$fileData['cycle']} days</td>
  </tr>
  <tr>
    <td width="50%">Rate of Interest (p.m. %)</td>
    <td>{$fileData['rateOfInterest']} percent(%) per day</td>
  </tr>
  <tr>
    <td width="50%">Repayment</td>
    <td>One time repayment</td>
  </tr>
  <tr>
    <td width="50%">Risk Category</td>
    <td>Low Medium High</td>
  </tr>
  <tr>
    <td width="50%">Loan Amount</td>
    <td>{$fileData['loanAmount']}</td>
  </tr>
  
</table>
<p>4.The Borrower understands that the Kailash Auto Finance Limited has adopted risk-based pricing, which is arrived by taking into account, broad parameters like the customers financial and credit profile. Further, the Borrower acknowledges and confirms that the Kailash Auto Finance Limited shall have the discretion to change prospectively the rate of interest and other charges applicable to the Loan.</p>
<p>5.The Borrower acknowledges and confirms having received a copy of each Transaction Document and agrees that this letter is a Transaction Document.</p>
<p>ACHNOWELDEGEMENT: Kailash Auto Finance Limited acknowledges receipt of your Application Form together with the Standard Term We will revert within 5 working days subject to furnishing the necessary documents to Kailash Auto Finance Limited satisfaction.</p>
<p>Date: {$fileData['loanDate']} &nbsp;&nbsp;&nbsp;&nbsp; Sign: {$fileData['realName']}</p>
<p>Digitally Signed by: Kailash Auto Finance Limited</p>
<p>Name: {$fileData['realName']}</p>
<p>Location: {$fileData['address']}</p>
<p>Reason: None</p>
<p>Date: {$fileData['loanDate']} Time – {$fileData['loanTime']}.</p>
<p>Signed via Kailash Auto Finance Limited</p>
</body>
</html>
HTML;

        return $result;
    }
}



<?php
namespace common\services;

use backend\models\Merchant;
use Carbon\Carbon;
use common\helpers\CommonHelper;
use common\models\aglow\ApplyStatusUpdateForm;
use common\models\aglow\ConfirmLoanForm;
use common\models\aglow\CorrectOverdueForm;
use common\models\aglow\CustomerConfirmForm;
use common\models\aglow\FeesUpdateForm;
use common\models\aglow\LoanApplyForm;
use common\models\aglow\LoanDisbursedForm;
use common\models\aglow\LoanLicenceAglowOrder;
use common\models\aglow\LoanRepaymentForm;
use common\models\aglow\LoanStatusForm;
use common\models\agreement\SanctionLetterParams;
use common\models\enum\aglow\ApplyStatus;
use common\models\enum\aglow\ConfirmLoan;
use common\models\enum\aglow\CustomerConfirm;
use common\models\enum\aglow\LoanStatus;
use common\models\enum\Education;
use common\models\enum\Gender;
use common\models\enum\Industry;
use common\models\enum\Marital;
use common\models\enum\Relative;
use common\models\order\FinancialLoanRecord;
use common\models\order\UserLoanOrder;
use common\models\order\UserLoanOrderRepayment;
use common\models\pay\AglowAccountForm;
use common\models\pay\PayAccountSetting;
use common\models\pay\RazorpaySettlements;
use common\models\user\LoanPerson;
use common\services\agreement\AgreementService;
use common\services\order\OrderExtraService;
use common\services\order\OrderService;
use common\services\pay\BasePayService;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use kartik\mpdf\Pdf;
use light\hashids\Hashids;
use Yii;


/**
 * Class AglowService
 * @package common\services
 * @property string $partnerName
 * @property string $authKey
 * @property string $partnerId
 * @property string $kudosUrl
 * @property int $sourceId
 */
class AglowService  extends BasePayService
{

    const RETURN_CODE_SUCCESS = 200;
    const RETURN_CODE_FAIL = 400;

    private $url;
    private $token;
    private $payAccountSetting;
    private $merchantId;
    private $accountId;
    private $appName;
    private $orgName;
    private $companyName;
    private $companyAddr;
    private $GSTNumber;
    private $companyPhone;


    public function __construct(PayAccountSetting $payAccountSetting, $config = [])
    {
        $this->payAccountSetting = $payAccountSetting;
        /** @var AglowAccountForm $form */
        $form = self::formModel();
        $form->load($payAccountSetting->getAccountInfo(), '');
        $this->token = $form->token;
        $this->url = $form->url;
        $this->appName = $form->appName;
        $this->orgName = $form->orgName;
        $this->companyName = $form->companyName;
        $this->companyAddr = $form->companyAddr;
        $this->GSTNumber = $form->GSTNumber;
        $this->companyPhone = $form->companyPhone;
        $this->accountId = $payAccountSetting->id;
        $this->merchantId = $this->payAccountSetting->merchant_id;
        parent::__construct($config);
    }

    /**
     * @return AglowAccountForm|\yii\base\Model
     */
    public static function formModel()
    {
        return new AglowAccountForm();
    }


    /**
     * @param array $postData
     * @return array|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function doPost($uri, array $postData)
    {
        $client = new Client([
            'base_uri'              => $this->url,
            RequestOptions::TIMEOUT => 180,
        ]);

        $postData['auth_token'] = $this->token;
        $postData['app_name'] = $this->appName;
        $postData['org_name'] = $this->orgName;
        $responseRaw = $client->request('POST', $uri, [
            RequestOptions::FORM_PARAMS => $postData,
        ]);

        $r = $responseRaw->getBody()->getContents();
        $response = $responseRaw->getStatusCode() ? json_decode($r, true) : [];

        return $response;
    }


    /**
     * 创建aglow订单 放款成功或订单驳回后调用
     * @param UserLoanOrder $order
     * @return bool
     */
    public function createAglowOrder(UserLoanOrder $order)
    {
        $aglowOrder = LoanLicenceAglowOrder::find()->where(['order_id' => $order->id])->one();
        if(!is_null($aglowOrder))
        {
            return true;
        }
        $aglowOrder = new LoanLicenceAglowOrder();
        $aglowOrder->pay_account_id = $this->accountId;
        $aglowOrder->merchant_id = $this->merchantId;
        $aglowOrder->order_id = $order->id;
        $aglowOrder->user_id = $order->user_id;
        $aglowOrder->status = LoanLicenceAglowOrder::STATUS_DEFAULT;
        return $aglowOrder->save();

    }


    /**
     * 借款申请 放款成功或订单驳回后调用
     * @param $orderId
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function loanApply($orderId)
    {
        /** @var LoanLicenceAglowOrder $aglowOrder */
        $aglowOrder = LoanLicenceAglowOrder::find()->where([
            'order_id' => $orderId,
        ])->one();
        if(is_null($aglowOrder))
        {
            Yii::error("orderId:{$orderId},订单状态非默认状态" , 'aglow_loan_apply');
            return false;
        }
        if($aglowOrder->status >= LoanLicenceAglowOrder::STATUS_LOAN_APPLY)
        {
            return true;
        }
        if($aglowOrder->status != LoanLicenceAglowOrder::STATUS_DEFAULT)
        {
            return false;
        }

        $order = UserLoanOrder::findOne($orderId);
        $orderExtraService = new OrderExtraService($order);
        $loanPerson = $order->loanPerson;
        $workInfo = $orderExtraService->getUserWorkInfo();
        $baseInfo = $orderExtraService->getUserBasicInfo();

        $fileStorageService =  new FileStorageService();
        $panReport = $orderExtraService->getUserOcrPanReport();
        $panFilePath = $fileStorageService->downloadFile($panReport->img_front_path);
        $panPhoto = base64_encode(file_get_contents($panFilePath));
        @unlink($panFilePath);
        $selfReport = $orderExtraService->getUserFrReport();
        $selfFilePath = $fileStorageService->downloadFile($selfReport->img_fr_path);
        $selfPhoto = base64_encode(file_get_contents($selfFilePath));
        @unlink($selfFilePath);
//        $adhReport = $orderExtraService->getUserOcrAadhaarReport();
//        $adhFilePath = $fileStorageService->downloadFile($adhReport->img_front_mask_path);
//        $adhPhoto = base64_encode(file_get_contents($adhFilePath));
//        @unlink($adhFilePath);
        $userContact = $orderExtraService->getUserContact();


        $loanApplyForm = new LoanApplyForm();
        $loanApplyForm->full_name = !empty($loanPerson->name) ? $loanPerson->name : 'NA';
        $loanApplyForm->email = !empty($baseInfo->email_address) ? $baseInfo->email_address : 'NA';
        $loanApplyForm->phone = $loanPerson->phone;
        $loanApplyForm->dob = date('d-m-Y', strtotime($loanPerson->birthday));
        $loanApplyForm->gender = Gender::$mapForAglow[$loanPerson->gender] ?? 'NA';
        $loanApplyForm->pan_num = $loanPerson->pan_code;
        $loanApplyForm->adh_num_masked = !empty($loanPerson->aadhaar_mask) ? $loanPerson->aadhaar_mask : 'NA';
        $loanApplyForm->residential_address = !empty($workInfo->residential_address1) ? $workInfo->residential_address1 : 'NA';
        $loanApplyForm->detail_address = !empty($workInfo->residential_detail_address) ? $workInfo->residential_detail_address : 'NA';
        $loanApplyForm->zip_code = !empty($baseInfo->zip_code) ? $baseInfo->zip_code : 'NA';
        $loanApplyForm->adh_address = !empty($baseInfo->aadhaar_address1) ? $baseInfo->aadhaar_address1 : (!empty($workInfo->residential_address1) ? $workInfo->residential_address1 : 'NA');
        $loanApplyForm->adh_detail_address = !empty($baseInfo->aadhaar_detail_address) ? $baseInfo->aadhaar_detail_address : (!empty($workInfo->residential_detail_address) ? $workInfo->residential_detail_address : 'NA');
        $loanApplyForm->adh_zip_code = !empty($baseInfo->aadhaar_pin_code) ? $baseInfo->aadhaar_pin_code : 'NA';
        $loanApplyForm->education = Education::$map[$workInfo->educated] ?? 'NA';
        $loanApplyForm->is_student = 'NA';
        $loanApplyForm->marital_status = Marital::$map[$baseInfo->marital_status] ?? 'NA';
        $loanApplyForm->industry = Industry::$map[$workInfo->industry] ?? 'NA';
        $loanApplyForm->company_name = !empty($workInfo->company_name) ? $workInfo->company_name : 'NA';
        $loanApplyForm->monthly_salary_before_tax = !empty($workInfo->monthly_salary) ? CommonHelper::CentsToUnit($workInfo->monthly_salary) : '1000.00';
        $loanApplyForm->contact_list = !empty($userContact->phone) ? $userContact->phone : 'NA';
        $loanApplyForm->relative = Relative::$map[$userContact->relative_contact_person] ?? 'NA';
        $loanApplyForm->name = !empty($userContact->name) ? $userContact->name : 'NA';
        $loanApplyForm->mobile = !empty($userContact->phone) ? $userContact->phone : 'NA';
        $loanApplyForm->apply_date = date('d-m-Y', time());
        $loanApplyForm->imei = !empty($order->clientInfoLog->device_id) ? $order->clientInfoLog->device_id : 'NA';
        $loanApplyForm->adh_photo_masked = 'NA';
        $loanApplyForm->pan_photo = !empty($panPhoto) ? $panPhoto : 'NA';
        $loanApplyForm->self_photo = !empty($selfPhoto) ? $selfPhoto : 'NA';

        $result = $this->doPost('api/loan_apply', $loanApplyForm->toArray());
        $loanApplyForm->pan_photo = '';
        $loanApplyForm->self_photo = '';
        if(isset($result['result_code']) && self::RETURN_CODE_SUCCESS == $result['result_code'])
        {
            Yii::info(['orderId' => $orderId, 'request' => $loanApplyForm->toArray(), 'response' => $result], 'aglow_loan_apply');
            $aglowOrder->status = LoanLicenceAglowOrder::STATUS_LOAN_APPLY;
            $aglowOrder->application_no = $result['application_no'];
            $aglowOrder->customer_identification_no = $result['customer_identification_no'];
            return $aglowOrder->save();
        }else{
            Yii::error(['orderId' => $orderId,
                'key' => [
                    'auth_token' => $this->token,
                    'app_name' => $this->appName,
                 'org_name' => $this->orgName
                ],
                'request' => $loanApplyForm->toArray(),
                'response' => $result], "aglow_loan_apply");
            return false;
        }

    }


    /**
     * 借款申请状态更新接口 放款成功或订单驳回后调用
     * @param $orderId
     * @param $underwritingResult
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function applyStatusUpdate($orderId, $underwritingResult)
    {

        /** @var LoanLicenceAglowOrder $aglowOrder */
        $aglowOrder = LoanLicenceAglowOrder::find()->where([
            'order_id' => $orderId,
        ])->one();
        if(is_null($aglowOrder))
        {
            Yii::error("orderId:{$orderId},订单状态状态不匹配" , 'aglow_apply_status_update');
            return false;
        }
        if($aglowOrder->status >= LoanLicenceAglowOrder::STATUS_APPLY_STATUS_UPDATE_PASS)
        {
            return true;
        }
        if($aglowOrder->status != LoanLicenceAglowOrder::STATUS_LOAN_APPLY)
        {
            return false;
        }

        $form  = new ApplyStatusUpdateForm();
        $form->application_no = $aglowOrder->application_no;
        $form->underwriting_result = $underwritingResult;
        if($form->underwriting_result == 'pass')
        {
            $form->reject_reason = 'pass';
        }else{
            $form->reject_reason = 'Credit scores are too low';
        }

        $result = $this->doPost('api/apply_status_update', $form->toArray());
        if(isset($result['result_code']) && self::RETURN_CODE_SUCCESS == $result['result_code'])
        {
            Yii::info(['orderId' => $orderId, 'request' => $form->toArray(), 'response' => $result], 'aglow_apply_status_update');
            if($underwritingResult == ApplyStatus::PASS()->getValue())
            {
                $status = LoanLicenceAglowOrder::STATUS_APPLY_STATUS_UPDATE_PASS;
                $aglowOrder->loan_account_no = $result['loan_account_no'];
            }else{
                $status = LoanLicenceAglowOrder::STATUS_APPLY_STATUS_UPDATE_REJECT;
            }
            $aglowOrder->status = $status;
            return $aglowOrder->save();
        }else{
            if(isset($result['loan_account_no']))
            {
                if($underwritingResult == ApplyStatus::PASS()->getValue())
                {
                    $status = LoanLicenceAglowOrder::STATUS_APPLY_STATUS_UPDATE_PASS;
                    $aglowOrder->loan_account_no = $result['loan_account_no'];
                }else{
                    $status = LoanLicenceAglowOrder::STATUS_APPLY_STATUS_UPDATE_REJECT;
                }
                $aglowOrder->status = $status;
                return $aglowOrder->save();
            }
            Yii::error(['orderId' => $orderId, 'request' => $form->toArray(), 'response' => $result], "aglow_apply_status_update");
            return false;
        }
    }


    /**
     * 借款确认接口 用户提现超时和放款成功后调用
     * @param $orderId
     * @param $confirmResult
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function confirmLoan($orderId, $confirmResult)
    {
        /** @var LoanLicenceAglowOrder $aglowOrder */
        $aglowOrder = LoanLicenceAglowOrder::find()->where([
            'order_id' => $orderId,
        ])->one();
        if(is_null($aglowOrder))
        {
            Yii::error("orderId:{$orderId},订单状态状态不匹配" , 'aglow_confirm_loan');
            return false;
        }
        if($aglowOrder->status >= LoanLicenceAglowOrder::STATUS_CONFIRM_LOAN_YES)
        {
            return true;
        }
        if($aglowOrder->status != LoanLicenceAglowOrder::STATUS_APPLY_STATUS_UPDATE_PASS)
        {
            return false;
        }

        $form  = new ConfirmLoanForm();
        $form->loan_account_no = $aglowOrder->loan_account_no;
        $form->result = $confirmResult;

        $result = $this->doPost('api/confirm_loan', $form->toArray());
        if(isset($result['result_code']) && self::RETURN_CODE_SUCCESS == $result['result_code'])
        {
            Yii::info(['orderId' => $orderId, 'request' => $form->toArray(), 'response' => $result], 'aglow_confirm_loan');
            if($confirmResult == ConfirmLoan::YES()->getValue())
            {
                $status = LoanLicenceAglowOrder::STATUS_CONFIRM_LOAN_YES;
            }else{
                $status = LoanLicenceAglowOrder::STATUS_CONFIRM_LOAN_NO;
            }
            $aglowOrder->status = $status;
            return $aglowOrder->save();
        }else{
            Yii::error(['orderId' => $orderId, 'request' => $form->toArray(), 'response' => $result], "aglow_confirm_loan");
            return false;
        }
    }


    /**
     * 放款申请，放款后调用
     * @param $orderId
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function loanDisbursed($orderId)
    {
        /** @var LoanLicenceAglowOrder $aglowOrder */
        $aglowOrder = LoanLicenceAglowOrder::find()->where([
            'order_id' => $orderId,
        ])->one();
        if(is_null($aglowOrder))
        {
            Yii::error("orderId:{$orderId},订单状态状态不匹配" , 'aglow_loan_disbursed');
            return false;
        }
        if($aglowOrder->status >= LoanLicenceAglowOrder::STATUS_LOAN_DISBURSED)
        {
            return true;
        }
        if($aglowOrder->status != LoanLicenceAglowOrder::STATUS_CONFIRM_LOAN_YES)
        {
            Yii::error("orderId:{$orderId},订单状态状态不匹配2" , 'aglow_loan_disbursed');
            return false;
        }

        /** @var UserLoanOrder $order */
        $order = UserLoanOrder::findOne($orderId);
        if(is_null($order))
        {
            Yii::error("orderId:{$orderId},借款订单不存在" , 'aglow_loan_disbursed');
            return false;
        }

        $orderService = new OrderService($order);

        $form = new LoanDisbursedForm();
        $form->loan_account_no = $aglowOrder->loan_account_no;
        $form->principal = CommonHelper::CentsToUnit($orderService->loanAmount());
        $form->interest = CommonHelper::CentsToUnit($orderService->totalInterests());
        $form->total_processing_fees = CommonHelper::CentsToUnit($orderService->processFeeAndGst());
        $form->processing_fees = CommonHelper::CentsToUnit($orderService->processFee());
        $form->processing_fees_gst = CommonHelper::CentsToUnit($orderService->gst());
        $form->loan_term = $order->loan_term;
        $form->loan_installment_num = $order->periods;
        $form->interest_rate = $order->day_rate;
        $form->amt_disbursed = CommonHelper::CentsToUnit($orderService->disbursalAmount());
        $form->overdue_rate = $order->overdue_rate;
        $form->bank_account = $order->userBankAccount->account;
        $form->bank_ifsc = $order->userBankAccount->ifsc;
        $form->longitude = $order->clientInfoLog->longitude ? $order->clientInfoLog->longitude : 'NA';
        $form->latitude = $order->clientInfoLog->latitude ? $order->clientInfoLog->latitude : 'NA';

        $result = $this->doPost('api/loan_disbursed', $form->toArray());
        if(isset($result['result_code']) && self::RETURN_CODE_SUCCESS == $result['result_code'])
        {
            Yii::info(['orderId' => $orderId, 'request' => $form->toArray(), 'response' => $result], 'aglow_loan_disbursed');
            $aglowOrder->status = LoanLicenceAglowOrder::STATUS_LOAN_DISBURSED;
            if($aglowOrder->save()){
                return true;
            }else{
                Yii::error(['orderId' => $orderId, 'text' => $aglowOrder->getErrorSummary(true)], "aglow_loan_disbursed");
                return false;
            }
        }else{
            Yii::error(['orderId' => $orderId, 'request' => $form->toArray(), 'response' => $result], "aglow_loan_disbursed");
            return false;
        }


    }


    /**
     * 用户确认接口，放款后调用
     * @param $orderId
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function customerConfirm($orderId)
    {
        /** @var LoanLicenceAglowOrder $aglowOrder */
        $aglowOrder = LoanLicenceAglowOrder::find()->where([
            'order_id' => $orderId,
        ])->one();
        if(is_null($aglowOrder))
        {
            Yii::error("orderId:{$orderId},订单状态状态不匹配" , 'aglow_customer_confirm');
            return false;
        }
        if($aglowOrder->status >= LoanLicenceAglowOrder::STATUS_CUSTOMER_CONFIRM)
        {
            return true;
        }
        if($aglowOrder->status != LoanLicenceAglowOrder::STATUS_LOAN_DISBURSED)
        {
            return false;
        }

        $form = new CustomerConfirmForm();
        $form->loan_account_no = $aglowOrder->loan_account_no;
        $form->result = CustomerConfirm::ACCEPTED()->getValue();

        $result = $this->doPost('api/customer_confirm', $form->toArray());
        if(isset($result['result_code']) && self::RETURN_CODE_SUCCESS == $result['result_code'])
        {
            Yii::info(['orderId' => $orderId, 'request' => $form->toArray(), 'response' => $result], 'aglow_customer_confirm');
            $aglowOrder->status = LoanLicenceAglowOrder::STATUS_CUSTOMER_CONFIRM;
            return $aglowOrder->save();
        }else{
            Yii::error(['orderId' => $orderId, 'request' => $form->toArray(), 'response' => $result], "aglow_customer_confirm");
            return false;
        }

    }


    /**
     * 放款状态同步，放款后调用
     * @param $orderId
     * @param $loanStatus
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function loanStatus($orderId, $loanStatus)
    {
        /** @var LoanLicenceAglowOrder $aglowOrder */
        $aglowOrder = LoanLicenceAglowOrder::find()->where([
            'order_id' => $orderId,
        ])->one();
        if(is_null($aglowOrder))
        {
            Yii::error("orderId:{$orderId},订单状态状态不匹配" , 'aglow_loan_status');
            return false;
        }
        if($aglowOrder->status >= LoanLicenceAglowOrder::STATUS_LOAN_STATUS_SUCCESS)
        {
            return true;
        }
        if($aglowOrder->status != LoanLicenceAglowOrder::STATUS_CUSTOMER_CONFIRM)
        {
            return false;
        }

        $form = new LoanStatusForm();
        $form->loan_account_no = $aglowOrder->loan_account_no;
        $form->status = $loanStatus;
        if($loanStatus == LoanStatus::SUCCESS()->getValue())
        {
            /** @var FinancialLoanRecord $financialLoanRecord */
            $financialLoanRecord = FinancialLoanRecord::find()->where(['business_id' => $orderId])->one();
            $form->transaction_id = $financialLoanRecord->trade_no;
            $form->disbursement_date = date('d-m-Y', $financialLoanRecord->success_time);
        }else{
            $form->transaction_id = 'NA';
            $form->disbursement_date = 'NA';
        }

        $result = $this->doPost('api/loan_status', $form->toArray());
        if(isset($result['result_code']) && self::RETURN_CODE_SUCCESS == $result['result_code'])
        {
            Yii::info(['orderId' => $orderId, 'request' => $form->toArray(), 'response' => $result], 'aglow_loan_status');
            if($loanStatus == LoanStatus::SUCCESS()->getValue())
            {
                $status = LoanLicenceAglowOrder::STATUS_LOAN_STATUS_SUCCESS;
            }else{
                $status = LoanLicenceAglowOrder::STATUS_LOAN_STATUS_FAIL;
            }
            $aglowOrder->status = $status;
            return $aglowOrder->save();
        }else{
            Yii::error(['orderId' => $orderId, 'request' => $form->toArray(), 'response' => $result], "aglow_loan_status");
            return false;
        }
    }


    /**
     * 还款计划，放款成功后调用
     * @param $orderId
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function loanRepayment($orderId)
    {

        /** @var LoanLicenceAglowOrder $aglowOrder */
        $aglowOrder = LoanLicenceAglowOrder::find()->where([
            'order_id' => $orderId,
        ])->one();
        if(is_null($aglowOrder))
        {
            Yii::error("orderId:{$orderId},订单状态状态不匹配" , 'aglow_loan_repayment');
            return false;
        }
        if($aglowOrder->status >= LoanLicenceAglowOrder::STATUS_LOAN_REPAYMENT)
        {
            return true;
        }
        if($aglowOrder->status != LoanLicenceAglowOrder::STATUS_LOAN_STATUS_SUCCESS)
        {
            return false;
        }

        $order = UserLoanOrder::findOne($orderId);
        if(is_null($order))
        {
            return false;
        }
        $orderService = new OrderService($order);
        $endDate = date('d-m-Y', strtotime($orderService->repaymentTime()));
        $planRepaymentAmount = CommonHelper::CentsToUnit($orderService->totalRepaymentAmount());

        $form = new LoanRepaymentForm();
        $form->loan_account_no = $aglowOrder->loan_account_no;
        $form->repayment_plan = json_encode([
            'current_period_num'  => 1,
            'period_repayment_amount'  => $planRepaymentAmount,
            'period_end_date'  => $endDate,

        ]);
        $agreementDocReportPdfData = $this->getPdfStr('Agreement Doc', $this->getSanctionLetterContent($order, $aglowOrder->loan_account_no));
        $form->sanction_letter = base64_encode($agreementDocReportPdfData);

        $result = $this->doPost('api/loan_repayment', $form->toArray());
        $form->sanction_letter = '';
        if(isset($result['result_code']) && self::RETURN_CODE_SUCCESS == $result['result_code'])
        {
            Yii::info(['orderId' => $orderId, 'request' => $form->toArray(), 'response' => $result], 'aglow_loan_repayment');
            $aglowOrder->status = LoanLicenceAglowOrder::STATUS_LOAN_REPAYMENT;
            return $aglowOrder->save();
        }else{
            Yii::error(['orderId' => $orderId, 'request' => $form->toArray(), 'response' => $result], "aglow_loan_repayment");
            if(isset($result['info']) && 'Repayment plan already exist for this loan account' == $result['info'])
            {
                $aglowOrder->status = LoanLicenceAglowOrder::STATUS_LOAN_REPAYMENT;
                return $aglowOrder->save();
            }
            return false;
        }
    }


    /**
     * 费用更新 用户还款、逾期之后调用
     * @param int $orderId
     * @param bool $force
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function feesUpdate(int $orderId, bool $force = false)
    {
        /** @var LoanLicenceAglowOrder $aglowOrder */
        $aglowOrder = LoanLicenceAglowOrder::find()->where([
            'order_id' => $orderId,
        ])->one();
        if(is_null($aglowOrder))
        {
            Yii::error("orderId:{$orderId},订单状态状态不匹配" , 'aglow_fees_update');
            return false;
        }

        //如果强制更新，则不判断订单状态
        if(!$force)
        {
            if($aglowOrder->status >= LoanLicenceAglowOrder::STATUS_LOAN_CLOSE)
            {
                return true;
            }
            if($aglowOrder->status != LoanLicenceAglowOrder::STATUS_LOAN_REPAYMENT)
            {
                return false;
            }
        }


        $order = UserLoanOrder::findOne($orderId);
        if(is_null($order))
        {
            return false;
        }

        $repaymentOrder = $order->userLoanOrderRepayment;
        $couponAmount = $repaymentOrder->coupon_money;
        if($repaymentOrder->status == UserLoanOrderRepayment::STATUS_REPAY_COMPLETE)
        {
            $status = 'closed';
            $couponAmount = $repaymentOrder->total_money - $repaymentOrder->true_total_money;
        }elseif($repaymentOrder->is_overdue == UserLoanOrderRepayment::IS_OVERDUE_YES)
        {
            $status = 'overdued';
        }else{
            $status = 'pending';
        }


        $form = new FeesUpdateForm();
        $form->loan_account_no = $aglowOrder->loan_account_no;
        $form->fees_list = json_encode([
            'current_period_num'  => 1,
            'overdue_fee'  => CommonHelper::CentsToUnit($repaymentOrder->overdue_fee),
            'overdue_days'  => $repaymentOrder->overdue_day,
            'paid_amount'  => CommonHelper::CentsToUnit($repaymentOrder->true_total_money),
            'coupon_amount'  => CommonHelper::CentsToUnit($couponAmount),
            'status'  => $status,
            'closed_time' => !empty($repaymentOrder->closing_time) ? $repaymentOrder->closing_time : null
        ]);
        $form->closed_time = !empty($repaymentOrder->closing_time) ? $repaymentOrder->closing_time : null;
        $form->true_total_principal = CommonHelper::CentsToUnit($repaymentOrder->true_total_principal);
        $form->true_total_interests = CommonHelper::CentsToUnit($repaymentOrder->true_total_interests);
        $form->true_total_overdue_fee = CommonHelper::CentsToUnit($repaymentOrder->true_total_overdue_fee);


        $result = $this->doPost('api/fees_update', $form->toArray());
        if(isset($result['result_code']) && self::RETURN_CODE_SUCCESS == $result['result_code'])
        {
            Yii::info(['orderId' => $orderId, 'request' => $form->toArray(), 'response' => $result], 'aglow_fees_update');
            if('closed' == $status)
            {
                $aglowOrder->status = LoanLicenceAglowOrder::STATUS_LOAN_CLOSE;
            }else{
                $aglowOrder->updated_at = time();
            }
            return $aglowOrder->save();

        }else{
            if($force)
            {
                $this->setError($result);
            }
            Yii::error(['orderId' => $orderId, 'request' => $form->toArray(), 'response' => $result], "aglow_fees_update");
            return false;
        }
    }


    public function generateSanctionLetter(UserLoanOrder $order)
    {
        /** @var LoanLicenceAglowOrder $aglowOrder */
        $aglowOrder = LoanLicenceAglowOrder::find()->where([
            'order_id' => $order->id,
        ])->one();
        $agreementDocReportPdfData = $this->getPdfStr('Agreement Doc', $this->getSanctionLetterContent($order, $aglowOrder->loan_account_no));

        $fileName = '/tmp/' . $order->id . '.pdf';
        file_put_contents($fileName, $agreementDocReportPdfData);
    }


    private function getPdfStr($title, $content)
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


    /**
     * 获取sanction letter的参数
     * @param UserLoanOrder $order
     * @param string $DPNReferenceNO
     * @return SanctionLetterParams
     */
    public function getSanctionLetterParams(UserLoanOrder $order, string $DPNReferenceNO)
    {
        $loanPerson = LoanPerson::findOne($order->user_id);
        $personAge = Carbon::rawCreateFromFormat('Y-m-d', $loanPerson->birthday)->age;
        $workInfo = $loanPerson->userWorkInfo;
        $nbfc = Merchant::NBFC_AGLOW;
        $orderService = new OrderService($order);

        $sanctionLetterParams = new SanctionLetterParams();
        $sanctionLetterParams->date = date('M.d,Y', $order->loan_time);
        $sanctionLetterParams->customerId =  'customer_' . (new Hashids(['salt'=> 'ag', 'minHashLength'=> 16]))->encode($order->user_id);
        $sanctionLetterParams->loanApplicationDate = date('M.d,Y h:i a', $order->loan_time);
        $sanctionLetterParams->companyName = $this->companyName;
        $sanctionLetterParams->companyAddr = $this->companyAddr;
        $sanctionLetterParams->gstNumber = $this->GSTNumber;
        $sanctionLetterParams->companyPhone = $this->companyPhone;
        $sanctionLetterParams->customerName = $loanPerson->name;
        $sanctionLetterParams->customerAge = $personAge;
        $sanctionLetterParams->residentialDetailAddress = $workInfo->residential_detail_address;
        $sanctionLetterParams->residentialAddress2 = $workInfo->residential_address2;
        $sanctionLetterParams->residentialAddress1 = $workInfo->residential_address1;
        $sanctionLetterParams->residentialPincode = $workInfo->residential_pincode;
        $sanctionLetterParams->nbfcName = AgreementService::$nbfcMap[$nbfc]['nbfcName'];
        $sanctionLetterParams->nbfcAddr = AgreementService::$nbfcMap[$nbfc]['nbfcAddr'];
        $sanctionLetterParams->nbfcShortName = AgreementService::$nbfcMap[Merchant::NBFC_AGLOW]['shortName'];
        $sanctionLetterParams->offerValidityPeriod = date('M.d,Y', strtotime($orderService->repaymentTime()));
        $sanctionLetterParams->loanAmountSanctioned = CommonHelper::CentsToUnit($orderService->loanAmount());
        $sanctionLetterParams->availabilityPeriod = $orderService->totalLoanTerm();
        $sanctionLetterParams->interest = $orderService->totalRate();
        $sanctionLetterParams->totalInterestAmount = CommonHelper::CentsToUnit($orderService->totalInterests());
        $sanctionLetterParams->processingFees = CommonHelper::CentsToUnit($orderService->processFeeAndGst());
        $sanctionLetterParams->repayment = CommonHelper::CentsToUnit($orderService->loanAmount() + $orderService->totalInterests());
        $sanctionLetterParams->delayedPaymentCharges = $order->overdue_rate;
        $sanctionLetterParams->DPNReferenceNO = $DPNReferenceNO;
        $sanctionLetterParams->termsAcceptedAt = date('d-m-Y H:i:s', $order->loan_time);
        $sanctionLetterParams->deviceName = $order->clientInfoLog->device_name;
        $sanctionLetterParams->deviceId = $order->clientInfoLog->device_id;
        $sanctionLetterParams->headerImg = $nbfc;
        $sanctionLetterParams->productName = $order->productSetting->product_name;

        return $sanctionLetterParams;
    }


    private function getSanctionLetterContent(UserLoanOrder $order, string $DPNReferenceNO)
    {
        $sanctionLetterParams = $this->getSanctionLetterParams($order, $DPNReferenceNO);
        $service = new AgreementService();
        $result = $service->getSanctionLetterPdfContent($sanctionLetterParams);
        return $result;
    }

    public function pushSettlements($payAccountID)
    {
        $datas =  RazorpaySettlements::find()
            ->where(['status' => RazorpaySettlements::STATUS_DEFAULT])
            ->andWhere(['pay_account_id' => $payAccountID])
            ->orderBy(['id' => SORT_ASC])->all();
        /** @var RazorpaySettlements $settlement */
        foreach ($datas as $settlement)
        {
            $postData = [
                'amount' => CommonHelper::CentsToUnit($settlement->amount),
                'gst' => CommonHelper::CentsToUnit($settlement->tax),
                'fees' => CommonHelper::CentsToUnit($settlement->fees),
                'time' => date('Y-m-d', $settlement->settlements_time),
                'settlement_id' => $settlement->settlements_id,
            ];

            $result = $this->doPost('api/settlement',$postData);
            if(isset($result['result_code']) && self::RETURN_CODE_SUCCESS == $result['result_code'])
            {
                $settlement->status = RazorpaySettlements::STATUS_SUCCESS;
                $settlement->save(false);
            }else{
                var_dump($result);
            }
        }
    }


    public function correctOverdue(int $orderId, bool $force = false)
    {
        /** @var LoanLicenceAglowOrder $aglowOrder */
        $aglowOrder = LoanLicenceAglowOrder::find()->where([
            'order_id' => $orderId,
        ])->one();
        if(is_null($aglowOrder))
        {
            Yii::error("orderId:{$orderId},订单状态状态不匹配" , 'aglow_fees_update');
            return false;
        }

        //如果强制更新，则不判断订单状态
        if(!$force){
            if($aglowOrder->status >= LoanLicenceAglowOrder::STATUS_LOAN_CLOSE)
            {
                return true;
            }
            if($aglowOrder->status != LoanLicenceAglowOrder::STATUS_LOAN_REPAYMENT)
            {
                return false;
            }
        }


        $order = UserLoanOrder::findOne($orderId);
        if(is_null($order))
        {
            return false;
        }

        $repaymentOrder = $order->userLoanOrderRepayment;
        if(UserLoanOrderRepayment::STATUS_REPAY_COMPLETE != $repaymentOrder->status)
        {
            return true;
        }

        $form = new CorrectOverdueForm();
        $form->loan_account_no = $aglowOrder->loan_account_no;
        $form->closed_time = $repaymentOrder->closing_time;
        $form->true_total_principal = CommonHelper::CentsToUnit($repaymentOrder->true_total_principal);
        $form->true_total_interests = CommonHelper::CentsToUnit($repaymentOrder->true_total_interests);
        $form->true_total_overdue_fee = CommonHelper::CentsToUnit($repaymentOrder->true_total_overdue_fee);
        $form->true_disbursement_date = date('Y-m-d', $order->loan_time);


        $result = $this->doPost('api/correct_overdue', $form->toArray());
        if(isset($result['result_code']) && self::RETURN_CODE_SUCCESS == $result['result_code'])
        {
            $aglowOrder->status = LoanLicenceAglowOrder::STATUS_CORRECT_OVERDUE;
            $aglowOrder->save(false);
            Yii::info(['orderId' => $orderId, 'request' => $form->toArray(), 'response' => $result], 'aglow_correct_overdue');
            return true;
        }else{
            if($force)
            {
                $this->setError($result);
            }
            Yii::error(['orderId' => $orderId, 'request' => $form->toArray(), 'response' => $result], "aglow_correct_overdue");
            return false;
        }
    }

}
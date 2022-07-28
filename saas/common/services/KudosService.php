<?php


namespace common\services;


use backend\models\Merchant;
use Carbon\Carbon;
use common\helpers\CommonHelper;
use common\helpers\EncryptData;
use common\helpers\RedisQueue;
use common\models\agreement\SanctionLetterParams;
use common\models\enum\Education;
use common\models\enum\Gender;
use common\models\enum\kudos\LoanStatus;
use common\models\enum\kudos\NoteIssueType;
use common\models\enum\kudos\ReconciliationStatus;
use common\models\enum\kudos\ValidationStatus;
use common\models\enum\KudosQuery;
use common\models\enum\Marital;
use common\models\kudos\BorrowerInfoForm;
use common\models\kudos\LoanDemandForm;
use common\models\kudos\LoanKudosPerson;
use common\models\kudos\LoanKudosOrder;
use common\models\kudos\LoanKudosTranche;
use common\models\kudos\LoanRepayForm;
use common\models\kudos\LoanRequestForm;
use common\models\kudos\LoanSettlement;
use common\models\kudos\LoanStatementRequest;
use common\models\kudos\NcCheck;
use common\models\kudos\PGTransactionFrom;
use common\models\kudos\Reconciliation;
use common\models\kudos\StatusCheckForm;
use common\models\kudos\TrancheAppendForm;
use common\models\kudos\UploadDocumentForm;
use common\models\kudos\ValidationGetForm;
use common\models\kudos\ValidationPostForm;
use common\models\order\UserLoanOrder;
use common\models\pay\KudosAccountForm;
use common\models\pay\PayAccountSetting;
use common\models\user\LoanPerson;
use common\services\agreement\AgreementService;
use common\services\order\OrderExtraService;
use common\services\order\OrderService;
use frontend\models\agreement\LoanServiceForm;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use light\hashids\Hashids;
use Yii;
use Imagick;
use kartik\mpdf\Pdf;
use Exception;

class KudosService
{
    private $partnerName;
    private $authKey;
    private $partnerId;
    private $kudosUrl;
    private $companyName;
    private $companyAddr;
    private $GSTNumber;
    private $companyPhone;

    private $loanAccountSetting;
    private $merchantId;

    public function __construct(PayAccountSetting $payAccountSetting)
    {
        $this->loanAccountSetting = $payAccountSetting;
        $this->merchantId = $this->loanAccountSetting->merchant_id;
        $form = self::formModel();
        $form->load($payAccountSetting->getAccountInfo(), '');
        $this->partnerId = $form->apiV2PartnerId;
        $this->partnerName = $form->apiV2PartnerName;
        $this->authKey = $form->apiV2Key;
        $this->kudosUrl = $form->apiV2Url;
        $this->companyName = $form->companyName;
        $this->companyAddr = $form->companyAddr;
        $this->GSTNumber = $form->GSTNumber;
        $this->companyPhone = $form->companyPhone;
    }

    /**
     * @return KudosAccountForm|\yii\base\Model
     */
    public static function formModel()
    {
        return new KudosAccountForm();
    }

    /**
     * @param KudosQuery $query
     * @param array $postData
     * @return array
     * @throws
     */
    private function doPost(KudosQuery $query, array $postData)
    {
        $client = new Client([
            'base_uri'              => $this->kudosUrl,
            RequestOptions::TIMEOUT => 180,
            RequestOptions::HEADERS => [
                'Partnerid' => $this->partnerId,
                'Partner'   => $this->partnerName,
                'Authkey'   => $this->authKey,
                'Query'     => $query->getValue(),
            ],
        ]);

        $responseRaw = $client->request('POST', '', [
            RequestOptions::FORM_PARAMS => $postData,
        ]);

        $response = $responseRaw->getStatusCode() ? json_decode($responseRaw->getBody()->getContents(), true) : [];

        return $response;
    }

    /**
     * @param string $type
     * @return string
     */
    private function generateId(string $type = 'order'): string
    {
        $result = '';
        switch ($type) {
            case 'order':
                $result = uniqid('LOAN');
                break;
            case 'person':
                $result = uniqid('USER');
                break;
        }
        return $result;
    }

    private function deleteFile(array $paths)
    {
        foreach ($paths as $path) {
            @unlink($path);
        }
    }

    /**
     * 初始化kudos订单
     * @param UserLoanOrder $order
     * @return bool
     */
    public function createKudosOrder(UserLoanOrder $order): bool
    {
        /**
         * @var LoanKudosOrder $kudosOrder
         */
        $kudosOrder = LoanKudosOrder::find()
            ->where(['order_id' => $order->id])
            ->one();

        if ($kudosOrder) {
            return true;
        }

        $kudosOrder = new LoanKudosOrder();
        $kudosOrder->user_id = $order->user_id;
        $kudosOrder->order_id = $order->id;
        $kudosOrder->merchant_id = $this->merchantId;
        $kudosOrder->pay_account_id = $this->loanAccountSetting->id;
        $kudosOrder->partner_loan_id = $this->generateId('order');
        $kudosOrder->kudos_status = LoanStatus::INIT()->getValue();

        $kudosPerson = LoanKudosPerson::find()
            ->where([
                'user_id' => $order->user_id,
                'order_id' => $order->id
            ])
            ->one();
        if (!$kudosPerson) {
            $kudosPerson = new LoanKudosPerson();
            $kudosPerson->order_id = $order->id;
            $kudosPerson->merchant_id = $this->merchantId;
            $kudosPerson->pay_account_id = $this->loanAccountSetting->id;
            $kudosPerson->partner_borrower_id = $this->generateId('person');
            $kudosPerson->user_id = $order->user_id;
            $kudosPerson->save();

            $orderService = new OrderService($order);
            $orderService->changeOrderAllStatus(['after_loan_status' => UserLoanOrder::LOAN_STATUS_PUSH] , 'kudos订单已生成', 0);
        }

        return $kudosOrder->save();
    }

    /**
     * 第一步 申请贷款
     * @param UserLoanOrder $order
     * @return bool
     */
    public function loanRequest(UserLoanOrder $order): bool
    {
        $user = $order->loanPerson;

        /**
         * @var LoanKudosOrder $kudosOrder
         */
        $kudosOrder = LoanKudosOrder::find()
            ->where(['order_id' => $order->id])
            ->andWhere(['kudos_status' =>LoanStatus::INIT()->getValue()])
            ->one();

        if (!$kudosOrder) {
            return false;
        }

        $names = LoanPerson::getNameConversion($user->name);
        $form = new LoanRequestForm();
        $form->partner_borrower_id = $kudosOrder->kudosPerson->partner_borrower_id; //已经初始化
        $form->borrower_fName = $names['first_name'];
        $form->borrower_mName = $names['middle_name'];
        $form->borrower_lName = $names['last_name'];
        $form->borrower_employer_nme = $order->userWorkInfo->company_name;
        $form->borrower_email = $order->userBasicInfo->email_address;
        $form->borrower_mob = $user->phone;
        $form->borrower_dob = $user->birthday;
        $form->borrower_sex = Gender::$mapForKudos[$user->gender];
        $form->borrower_pan_num = $user->pan_code;
        $form->borrower_adhaar_num = !empty($user->check_code) ?
            EncryptData::decrypt($user->check_code) : $user->aadhaar_number;
        $form->partner_loan_id = $kudosOrder->partner_loan_id; //已经初始化
        $form->partner_loan_status = 'ACTIVE';
        $form->partner_loan_bucket = 'SASHAKT';
        $form->loan_purpose = 'shopping';
        $form->loan_amt = CommonHelper::CentsToUnit($order->amount);
        $form->loan_proc_fee = CommonHelper::CentsToUnit($order->cost_fee); //手续费=>砍头息
        $form->loan_conv_fee = '0.00';
        $form->loan_disbursement_amt = CommonHelper::CentsToUnit($order->disbursalAmount());
        $form->loan_typ = 'Bullet';
        $form->loan_installment_num = 1;
        $form->loan_tenure = $order->getTotalLoanTerm();

        $response = $this->doPost(KudosQuery::LOAN_QUEST(), $form->toArray());

        if (empty($response)) {
            return false;
        }

        if ($response['result_code'] != 200) {
            Yii::error([
                'function' => 'loanRequest',
                'params'   => $form->toArray(),
                'response' => $response,
            ], 'kudos');
            return false;
        } else {
            Yii::info([
                'function' => 'loanRequest',
                'params'   => $form->toArray(),
                'response' => $response,
            ], 'kudos');
            //记录
            $person = $kudosOrder->kudosPerson;
            $person->kudos_borrower_id = $response['kudosborrowerid'];
            $person->kudos_account_status = $response['account_status'];
            $person->kudos_va_acc = $response['va_acc'];
            $person->kudos_ifsc = $response['ifsc'];
            $person->kudos_bankname = $response['bankname'];
            $person->save();

            $kudosOrder->kudos_loan_id = $response['kudosloanid'];
            $kudosOrder->kudos_onboarded = $response['onboarded'];
            $kudosOrder->disbursement_amt = $order->disbursalAmount();
            $kudosOrder->kudos_status = LoanStatus::LOAN_REQUEST()->getValue();
            $kudosOrder->save();
        }

        return true;
    }



    /**
     * 第二步 同步借款人信息
     * @param UserLoanOrder $order
     * @param string $disbursement 支付订单号(不经过kudos放款流程)
     * @return bool
     */
    public function borrowerInfo(UserLoanOrder $order, string $disbursement = ''): bool
    {
        /**
         * @var LoanKudosOrder $kudosOrder
         */
        $kudosOrder = LoanKudosOrder::find()
            ->where(['order_id' => $order->id])
            ->andWhere(['kudos_status' =>LoanStatus::LOAN_REQUEST()->getValue()])
            ->one();

        if (!$kudosOrder) {
            return false;
        }

        $user = $order->loanPerson;
        $kudosOrder = $order->loanKudosOrder;
        $kudosPerson = $kudosOrder->kudosPerson;
        $workInfo = $order->userWorkInfo;
        $basicInfo = $order->userBasicInfo;

        $orderExtraService = new OrderExtraService($order);
        $aadhaarOcrModel = $orderExtraService->getUserOcrAadhaarReport();
        $borrowerPermPincode = $aadhaarOcrModel->pin ??
            (empty($basicInfo->aadhaar_pin_code) ? $basicInfo->zip_code : $basicInfo->aadhaar_pin_code);
        $borrowerPermState = $aadhaarOcrModel->state ??
            (empty($basicInfo->aadhaar_address1) ? $workInfo->residential_address1 : $basicInfo->aadhaar_address1);
        $borrowerPermCity = $aadhaarOcrModel->city ??
            (empty($basicInfo->aadhaar_address2) ? $workInfo->residential_address2 : $basicInfo->aadhaar_address2);
        $borrowerPermAddress = $aadhaarOcrModel->address ??
            (empty($basicInfo->aadhaar_detail_address) ? $workInfo->residential_detail_address : $basicInfo->aadhaar_detail_address);

        $names = LoanPerson::getNameConversion($user->name);
        $form = new BorrowerInfoForm();
        $form->partner_borrower_id = $kudosPerson->partner_borrower_id;
        $form->kudos_borrower_id = $kudosPerson->kudos_borrower_id;
        $form->partner_loan_id = $kudosOrder->partner_loan_id;
        $form->kudos_loan_id = $kudosOrder->kudos_loan_id;
        $form->borrower_fName = $names['first_name'];
        $form->borrower_mName = $names['middle_name'];
        $form->borrower_lName = $names['last_name'];
        $form->borrower_employer_id = ''; //kudos允许传递空值
        $form->borrower_employer_nme = $workInfo->company_name;
        $form->borrower_email = $basicInfo->email_address;
        $form->borrower_perm_pincode = $form->borrower_curr_pincode = $borrowerPermPincode;
        $form->borrower_perm_state = $form->borrower_curr_state = $borrowerPermState;
        $form->borrower_perm_city = $form->borrower_curr_city = $borrowerPermCity;
        $form->borrower_perm_address = $form->borrower_curr_addr = $borrowerPermAddress;
        $form->borrower_marital_status = Marital::$mapForKudos[$basicInfo->marital_status];
        $form->borrower_qualification = Education::$map[$workInfo->educated]; //kudos允许其他值
        $form->borrower_salary = CommonHelper::CentsToUnit($workInfo->monthly_salary);
        $form->borrower_credit_score = $order->userCreditReportCibil->score ?? '600'; //不存在抛异常，不作处理
        $form->borrower_foir = null; //kudos允许传递空值
        $form->borrower_ac_holder_nme = $user->name; //姓名
        $form->borrower_bnk_nme = $order->userBankAccount->bank_name; //银行名
        $form->borrower_ac_num = $order->userBankAccount->account; //卡号
        $form->borrower_bnk_ifsc = $order->userBankAccount->ifsc;
        $form->loan_typ = 'Bullet';
        $form->loan_tenure = $order->getTotalLoanTerm();
        $form->loan_emi_freq = 'Bullet'; //如果不对试一下 7
        $form->loan_installment_num = 1;
        $form->loan_prin_amt = CommonHelper::CentsToUnit($order->amount);
        $form->loan_proc_fee = CommonHelper::CentsToUnit($order->cost_fee);
        $form->loan_proc_fee_kudos = '0.00';
        $form->loan_proc_fee_partner = CommonHelper::CentsToUnit($order->cost_fee);
        $form->loan_conv_fee = '0.00';
        $form->loan_coupon_amt = '0.00'; //优惠,目前没有
        $form->loan_amt_disbursed = CommonHelper::CentsToUnit($order->disbursalAmount()); //打款金额
        $form->loan_int_rt = $order->day_rate * $order->getTotalLoanTerm(); //利率，如果不对试一下，30*日利率
        $form->loan_int_amt = CommonHelper::CentsToUnit($order->interests); //利息
        $form->loan_int_amt_kud = sprintf("%.2f", floatval($order->amount/100) * 0.015 * $order->getTotalLoanTerm() / 30); //kudos允许传递空值
        $form->loan_int_amt_par = $form->loan_int_amt - $form->loan_int_amt_kud;
        $form->loan_emi_dte_1 = $order->getRepayDate();
        $form->loan_emi_amt_1 = CommonHelper::CentsToUnit($order->calcTotalMoney()); //一期金额
        $form->loan_emi_dte_2 = '';
        $form->loan_emi_amt_2 = 0; //二期金额
        $form->loan_end_dte = $order->getRepayDate();
        $form->loan_disbursement_upd_status = 'Approved';
        $form->loan_disbursement_upd_dte = Carbon::now()->toDateString();
        $form->loan_disbursement_trans_dte = Carbon::now()->toDateString();
        $form->disbursement_trans_trac_num = $disbursement;
        $form->loan_emi_recd_num = 0;

        $response = $this->doPost(KudosQuery::BORROWER_INFO(), $form->toArray());

        if (empty($response)) {
            return false;
        }

        if ($response['result_code'] != 200) {
            Yii::error([
                'function' => 'borrowerInfo',
                'params'   => $form->toArray(),
                'response' => $response,
            ],'kudos');
            return false;
        } else {
            Yii::info([
                'function' => 'borrowerInfo',
                'params'   => $form->toArray(),
                'response' => $response,
            ],'kudos');
            /**
             * @var LoanKudosOrder $kudosOrder
             */
            $kudosOrder = LoanKudosOrder::find()
                ->where(['order_id' => $order->id])
                ->andWhere(['user_id' => $order->user_id])
                ->limit(1)
                ->one();
            if(isset($response['trancheid'])) {
                $kudosTranche = LoanKudosTranche::find()
                    ->where([
                        'kudos_tranche_id' => $response['trancheid'],
                        'merchant_id' => $this->merchantId,
                        'pay_account_id' => $this->loanAccountSetting->id
                    ])
                    ->limit(1)
                    ->one();
            }else{
                $kudosTranche = LoanKudosTranche::find()
                    ->where([
                        'date' => date('Y-m-d'),
                        'merchant_id' => $this->merchantId,
                        'pay_account_id' => $this->loanAccountSetting->id
                    ])
                    ->limit(1)
                    ->one();
            }
            if (!$kudosTranche) {
                $kudosTranche = new LoanKudosTranche();
                $kudosTranche->kudos_tranche_id = $response['trancheid'];
                $kudosTranche->date = date('Y-m-d');
                $kudosTranche->merchant_id = $this->merchantId;
                $kudosTranche->pay_account_id = $this->loanAccountSetting->id;
                $kudosTranche->save();
            }

            $kudosPerson = $kudosOrder->kudosPerson;
            $kudosPerson->kudos_account_status = $response['account_status'];
            $kudosPerson->request_data = json_encode($form->toArray(), JSON_UNESCAPED_UNICODE);
            $kudosOrder->kudos_tranche_id = $kudosTranche->id;
            $kudosOrder->kudos_status = LoanStatus::BORROWER_INFO()->getValue();
            $kudosPerson->save();
            $kudosOrder->save();
        }

        return true;
    }


    /**
     * 第三步 上传借款人材料
     * @param UserLoanOrder $order
     * @return bool
     * @throws
     */
    public function uploadDocument(UserLoanOrder $order): bool
    {
        /**
         * @var LoanKudosOrder $model
         */
        $model = LoanKudosOrder::find()
            ->where(['order_id' => $order->id])
            ->andWhere(['kudos_status' =>LoanStatus::BORROWER_INFO()->getValue()])
            ->one();

        if (!$model) {
            return false;
        }

        $fr = $order->userCreditechFr;
        $pan = $order->userCreditechOCRPan;
        $aadhaar = $order->userCreditechOCRAadhaar;

        try {
            //todo:图片需要有掩码
            $service = new FileStorageService(false);
            $facePath = $service->downloadFile($fr->img_fr_path);
            $panPath = $service->downloadFile($pan->img_front_path);

            if (!empty($aadhaar->check_data_z_path)) {
                $tmpFrontData = $service->downloadFile($aadhaar->check_data_z_path);
                $imgFrontPath = str_replace('.data', '.jpg', $tmpFrontData);
                EncryptData::decryptFile($tmpFrontData, $imgFrontPath, EncryptData::PUBLIC_KEY, true);
            } else {
                $imgFrontPath = $service->downloadFile($aadhaar->img_front_path);
            }
            if (!empty($aadhaar->check_data_f_path)) {
                $tmpBackData = $service->downloadFile($aadhaar->check_data_f_path);
                $imgBackData = str_replace('.data', '.jpg', $tmpBackData);
                EncryptData::decryptFile($tmpBackData, $imgBackData, EncryptData::PUBLIC_KEY, true);
            } else {
                $imgBackData = $service->downloadFile($aadhaar->img_back_path);
            }
            $addressProofPath = [
                $imgFrontPath,
                $imgBackData,
            ];

            $facePdfPath = '/tmp/face.pdf';
            $facePdf = new Imagick($facePath);
            $facePdf->setImageFormat('pdf');
            $facePdf->writeImages($facePdfPath, true);

            $panPdfPath = '/tmp/pan.pdf';
            $panPdf = new Imagick($panPath);
            $panPdf->setImageFormat('pdf');
            $panPdf->writeImages($panPdfPath, true);

            $aadhaarPdfPath = '/tmp/aadhaar.pdf';
            $aadhaarPdf = new Imagick($addressProofPath);
            $aadhaarPdf->setImageFormat('pdf');
            $aadhaarPdf->writeImages($aadhaarPdfPath, true);

            $creditReportPdfData = '!!!NODATA!!!';

            $sanctionLetterReportPdfData = $this->getPdfStr('Sanction Letter', $this->getDemandContent($order));
            $agreementDocReportPdfData = $this->getPdfStr('Agreement Doc', $this->getSanctionLetterContent($order, $model->partner_loan_id));

            $form = new UploadDocumentForm();
            $form->kudos_loan_id = $model->kudos_loan_id;
            $form->kudos_borrower_id = $model->kudosPerson->kudos_borrower_id;
            $form->partner_loan_id = $model->partner_loan_id;
            $form->partner_borrower_id = $model->kudosPerson->partner_borrower_id;
            //JPG => PDF => base64
            $form->borrower_adhaar_doc = base64_encode(file_get_contents($aadhaarPdfPath));
            $form->borrower_pan_doc = base64_encode(file_get_contents($panPdfPath));
            $form->borrower_photo_doc = base64_encode(file_get_contents($facePdfPath));
            //PDF => base64
            $form->borrower_cibil_doc = $creditReportPdfData;
            $form->borrower_bnk_stmt_doc = ''; //该文件不传
            $form->loan_sanction_letter = base64_encode($sanctionLetterReportPdfData);
            $form->loan_agreement_doc = base64_encode($agreementDocReportPdfData);

            $response = $this->doPost(KudosQuery::UPLOAD_DOCUMENT(), $form->toArray());
        } catch (Exception $exception) {
            throw $exception;
        } finally {
            $this->deleteFile(array_merge($addressProofPath ?? [], [
                $facePath ?? '',
                $facePdfPath ?? '',
                $panPath ?? '',
                $panPdfPath ?? '',
                $aadhaarPdfPath ?? '',
            ]));
        }


        if ($response['result_code'] != 200) {
            Yii::error([
                'function' => 'uploadDocument',
                'params'   => [
                    'order_id'            => $order->id,
                    'kudos_loan_id'       => $form->kudos_loan_id,
                    'kudos_borrower_id'   => $form->kudos_borrower_id,
                    'partner_loan_id'     => $form->partner_loan_id,
                    'partner_borrower_id' => $form->partner_borrower_id,
                ],
                'response' => $response,
            ],'kudos');
            return false;
        } else {
            Yii::info([
                'function' => 'uploadDocument',
                'params'   => [
                    'order_id'            => $order->id,
                    'kudos_loan_id'       => $form->kudos_loan_id,
                    'kudos_borrower_id'   => $form->kudos_borrower_id,
                    'partner_loan_id'     => $form->partner_loan_id,
                    'partner_borrower_id' => $form->partner_borrower_id,
                ],
                'response' => $response,
            ],'kudos');
            /**
             * @var LoanKudosOrder $kudosOrder
             */
            $kudosOrder = LoanKudosOrder::find()
                ->where(['order_id' => $order->id])
                ->andWhere(['user_id' => $order->user_id])
                ->limit(1)
                ->one();

            $kudosOrder->kudos_status = LoanStatus::UPLOAD_DOCUMENT()->getValue();
            $kudosOrder->save();
        }

        return true;
    }

    /**
     * 第四步 验证Kudos数据更新
     * @param UserLoanOrder $order
     * @return bool
     */
    public function validationGet(UserLoanOrder $order): bool
    {
        /**
         * @var LoanKudosOrder $model
         */
        $model = LoanKudosOrder::find()
            ->where(['order_id' => $order->id])
            ->andWhere(['kudos_status' =>LoanStatus::UPLOAD_DOCUMENT()->getValue()])
            ->one();

        if (!$model) {
            return false;
        }


        $form = new ValidationGetForm();
        $form->partner_borrower_id = $model->kudosPerson->partner_borrower_id;
        $form->kudos_borrower_id = $model->kudosPerson->kudos_borrower_id;
        $form->partner_loan_id = $model->partner_loan_id;
        $form->kudos_loan_id = $model->kudos_loan_id;

        $response = $this->doPost(KudosQuery::VALIDATION_GET(), $form->toArray());

        if ($response['result_code'] != 200) {
            Yii::error([
                'function' => 'validationGet',
                'params'   => $form->toArray(),
                'response' => $response,
            ],'kudos');
            return false;
        } else {
            Yii::info([
                'function' => 'validationGet',
                'params'   => $form->toArray(),
                'response' => $response,
            ],'kudos');
            $model->kudos_status = LoanStatus::VALIDATION_GET()->getValue();
            $model->kudosPerson->kudos_account_status = $response['account_status'];
            $model->kudosPerson->save();
            $model->save();
        }
        return true;
    }



    /**
     *  次日验证Kudos数据更新
     * @param LoanKudosOrder $model
     * @return bool
     */
    public function validationGetTwo(LoanKudosOrder $model): bool
    {

        $form = new ValidationGetForm();
        $form->partner_borrower_id = $model->kudosPerson->partner_borrower_id;
        $form->kudos_borrower_id = $model->kudosPerson->kudos_borrower_id;
        $form->partner_loan_id = $model->partner_loan_id;
        $form->kudos_loan_id = $model->kudos_loan_id;

        $response = $this->doPost(KudosQuery::VALIDATION_GET(), $form->toArray());

        if ($response['result_code'] != 200) {
            Yii::error([
                'function' => 'validationGetTwo',
                'params'   => $form->toArray(),
                'response' => $response,
            ], 'kudos');
            $model->validation_status = ValidationStatus::VALIDATION_FAILED()->getValue();
            $model->save();
            return false;
        }else{
            Yii::info([
                'function' => 'validationGetTwo',
                'params'   => $form->toArray(),
                'response' => $response,
            ], 'kudos');
            $model->need_check_status = 1;
            $model->validation_status = ValidationStatus::VALIDATION_SUCCESS()->getValue();
            $model->save();
        }
        return true;
    }


    /**
     * 第四步 验证Kudos数据更新(征信数据修改，暂时不用)
     * @param UserLoanOrder $order
     * @return bool
     */
    public function validationPost(UserLoanOrder $order): bool
    {
        /**
         * @var LoanKudosOrder $model
         */
        $model = LoanKudosOrder::find()
            ->where(['order_id' => $order->id])
            ->andWhere(['user_id' => $order->user_id])
            ->one();

        $form = new ValidationPostForm();
        $form->partner_borrower_id = $model->kudosPerson->partner_borrower_id;
        $form->kudos_borrower_id = $model->kudosPerson->kudos_borrower_id;
        $form->borrower_pan_num = $order->loanPerson->pan_code;
        $form->borrower_adhaar_num = !empty($order->loanPerson->check_code) ?
            EncryptData::decrypt($order->loanPerson->check_code) : $order->loanPerson->aadhaar_number;
        $form->borrower_credit_score = $order->userCreditReportCibil->score ? $order->userCreditReportCibil->score : 600;

        $response = $this->doPost(KudosQuery::VALIDATION_POST(), $form->toArray());

        if ($response['result_code'] != 200) {
            Yii::error([
                'function' => 'validationPost',
                'params'   => $form->toArray(),
                'response' => $response,
            ], 'kudos');
            return false;
        } else {
            Yii::info([
                'function' => 'validationPost',
                'params'   => $form->toArray(),
                'response' => $response,
            ],'kudos');
        }
        return true;
    }

    /**
     * 第五步 同步还款计划
     * @param UserLoanOrder $order
     * @return bool
     */
    public function loanRepaymentSchedule(UserLoanOrder $order): bool
    {
        /**
         * @var LoanKudosOrder $model
         */
        $model = LoanKudosOrder::find()
            ->where(['order_id' => $order->id])
            ->andWhere(['kudos_status' =>LoanStatus::VALIDATION_GET()->getValue()])
            ->one();

        if (!$model) {
            return false;
        }
        $form = new LoanRepayForm();
        $form->partner_loan_id = $model->partner_loan_id;
        $form->kudos_loan_id = $model->kudos_loan_id;
        $form->partner_borrower_id = $model->kudosPerson->partner_borrower_id;
        $form->kudos_borrower_id = $model->kudosPerson->kudos_borrower_id;
        $form->loan_emi_amt_1 = CommonHelper::CentsToUnit($order->calcTotalMoney());
        $form->loan_emi_dte_1 = $order->getRepayDate();
        $form->loan_post_disbursement_status = 'OPEN';
        $form->loan_emi_recd_num = 0;

        $response = $this->doPost(KudosQuery::LOAN_REPAYMENT_SCHEDULE(), $form->toArray());

        if ($response['result_code'] != 200) {
            Yii::error([
                'function' => 'loanRepaymentSchedule',
                'params'   => $form->toArray(),
                'response' => $response,
            ],'kudos');
            return false;
        } else {
            Yii::info([
                'function' => 'loanRepaymentSchedule',
                'params'   => $form->toArray(),
                'response' => $response,
            ],'kudos');
            $model->kudos_status = LoanStatus::LOAN_REPAYMENT_SCHEDULE()->getValue();
            $model->next_validation_time = time() + 86400;
            $model->validation_status = ValidationStatus::WAIT_VALIDATION()->getValue();
            $model->save();
        }
        return true;
    }

    /**
     * @param array $params
     * @return bool
     */
    public function loanTrancheAppend(LoanKudosTranche $tranche, array $params)
    {
        $form = new TrancheAppendForm();
        $form->loan_tranche_id = $params['tranche_id'];
        $form->loan_disbursement_dte = $params['disbursement_dte'];
        $form->loan_tranche_num = $params['tranche_num'];
        $form->loan_tranche_amt = $params['tranche_amt'];

        $response = $this->doPost(KudosQuery::LOAN_TRANCHE_APPEND(), $form->toArray());

        if ($response['result_code'] != 200) {
            Yii::error([
                'function' => 'loanTrancheAppend',
                'params'   => $form->toArray(),
                'response' => $response,
            ],'kudos');
            $tranche->kudos_status = -1;
            $tranche->save();
            return false;
        }else{
            Yii::info([
                'function' => 'loanTrancheAppend',
                'params'   => $form->toArray(),
                'response' => $response,
            ], 'kudos');
            $tranche->kudos_status = 1;
            $tranche->save();
        }

        return true;
    }

    /**
     * 第六步 状态检查和更新数据
     * @param UserLoanOrder $order
     * @param string $repaymentDate
     * @return bool
     */
    public function statusCheckUpdate(UserLoanOrder $order, string $repaymentDate): bool
    {
        /**
         * @var LoanKudosOrder $model
         */
        $model = LoanKudosOrder::find()
            ->where(['order_id' => $order->id])
            ->andWhere(['user_id' => $order->user_id])
            ->one();

        $form = new StatusCheckForm();
        $form->partner_loan_id = $model->partner_loan_id;
        $form->partner_borrower_id = $model->kudosPerson->partner_borrower_id;
        $form->kudos_borrower_id = $model->kudosPerson->kudos_borrower_id;
        $form->kudos_loan_id = $model->kudos_loan_id;
        $form->loan_disbursement_upd_status = 'DISBURSED-' . $repaymentDate;


        $response = $this->doPost(KudosQuery::STATUS_CHECK(), $form->toArray());

        if ($response['result_code'] != 200) {
            Yii::error([
                'function' => 'statusCheckUpdate',
                'params'   => $form->toArray(),
                'response' => $response,
            ]);
            return false;
        } else {
            $model->kudos_status = LoanStatus::STATUS_CHECK()->getKey();
            $model->save();
        }
        return true;
    }

    /**
     * 第六步 状态检查和更新数据
     * @param UserLoanOrder $order
     * @param string $repaymentDate
     * @return bool
     */
    public function statusCheck(LoanKudosOrder $model): bool
    {

        $form = new StatusCheckForm();
        $form->partner_loan_id = $model->partner_loan_id;
        $form->partner_borrower_id = $model->kudosPerson->partner_borrower_id;
        $form->kudos_borrower_id = $model->kudosPerson->kudos_borrower_id;
        $form->kudos_loan_id = $model->kudos_loan_id;


        $response = $this->doPost(KudosQuery::STATUS_CHECK(), $form->toArray());

        if ($response['result_code'] != 200) {
            Yii::error([
                'function' => 'statusCheck',
                'params'   => $form->toArray(),
                'response' => $response,
            ],'kudos');
            return false;
        } else {
            //订单关闭
            $kudosPerson = $model->kudosPerson;
            $beforeStatus = $kudosPerson->kudos_account_status;
            $afterStatus = $response['account_status'];
            if(strtoupper($response['account_status']) == 'CLOSED')
            {
                $model->need_check_status = 0;
            }elseif (strtoupper($response['account_status']) == 'ACTIVE')
            {
                if($beforeStatus == $afterStatus && 0 == $model->repayment_amt)
                {
                    $model->need_check_status = 1;
                    $model->next_check_status_time = time() + 3600 * 3;
                }else{
                    $model->need_check_status = 0;
                }
            }else{
                $model->need_check_status = 1;
                $model->next_check_status_time = time() + 3600 * 4;
            }
            $kudosPerson->kudos_account_status = $response['account_status'];
            $kudosPerson->save();
            $model->save();
            Yii::info([
                'function' => 'statusCheck',
                'params'   => $form->toArray(),
                'response' => $response,
            ],'kudos');
        }
        return true;
    }



    /**
     * 按需 用户通过优惠券减免后，需将优惠券金额同步给kudos
     * @param LoanKudosOrder $kudosOrder
     * @return bool
     */
    public function borrowerInfoCoupon(LoanKudosOrder $kudosOrder): bool
    {

        $couponRequestParams = json_decode($kudosOrder->kudosPerson->request_data, true);
        $form = new BorrowerInfoForm();
        foreach ($couponRequestParams as $k =>$v)
        {
            $form->$k = $v;
        }
        $form->loan_coupon_amt = CommonHelper::CentsToUnit($kudosOrder->coupon_amount);

        $response = $this->doPost(KudosQuery::BORROWER_INFO(), $form->toArray());

        if (empty($response)) {
            return false;
        }

        if ($response['result_code'] != 200) {
            Yii::error([
                'function' => 'borrowerInfoCoupon',
                'params'   => $form->toArray(),
                'response' => $response,
            ],'kudos');
            return false;
        } else {
            Yii::info([
                'function' => 'borrowerInfoCoupon',
                'params'   => $form->toArray(),
                'response' => $response,
            ],'kudos');
            $kudosOrder->need_check_status = 1;
            $kudosOrder->next_check_status_time = time();
            $kudosOrder->need_coupon_request = 0;
            $kudosOrder->save();
        }

        return true;
    }


    /**
     * 订单使用优惠券方法
     * @param int $orderId
     * @param int $couponAmount
     * @return bool
     */
    public static function  orderUseCoupon(int $orderId, int $couponAmount)
    {
        /** @var LoanKudosOrder $kudosOrder */
        $kudosOrder = LoanKudosOrder::find()->where(['order_id' => $orderId])->one();
        if(is_null($kudosOrder))
        {
            return false;
        }
        $kudosOrder->coupon_amount += $couponAmount;
        $kudosOrder->need_coupon_request = 1;
        $kudosOrder->save();

    }


    /**
     * 逾期的状态同步
     *
     * @param UserLoanOrder $order
     * @param NoteIssueType $issueType
     * @return bool
     */
    public function loanDemand(UserLoanOrder $order, NoteIssueType $issueType): bool
    {
        /**
         * @var LoanKudosOrder $model
         */
        $model = LoanKudosOrder::find()
            ->where(['order_id' => $order->id])
            ->andWhere(['user_id' => $order->user_id])
            ->one();

        $form = new LoanDemandForm();
        $form->partner_loan_id = $model->partner_loan_id;
        $form->partner_borrower_id = $model->kudosPerson->partner_borrower_id;
        $form->kudos_borrower_id = $model->kudosPerson->kudos_borrower_id;
        $form->kudos_loan_id = $model->kudos_loan_id;
        $form->loan_demand_note_issued = $issueType->getValue(); //ISSUED 逾期两天, RAISED 逾期七天

        $response = $this->doPost(KudosQuery::LOAN_DEMAND_NOTE(), $form->toArray());

        if ($response['result_code'] != 200) {
            Yii::error([
                'function' => 'loanDemand',
                'params'   => $form->toArray(),
                'response' => $response,
            ]);
            return false;
        } else {
            $model->kudos_status = $issueType->equals(NoteIssueType::ISSUED()) ?
                LoanStatus::LOAN_DEMAND_NOTE_ISSUED()->getValue() : LoanStatus::LOAN_DEMAND_NOTE_RAISED()->getValue();
            $model->save();
        }
        return true;
    }

    /**
     * 打款后的订单同步
     * 目前只使用，订单延期，订单关闭，提前还款
     *
     * @param UserLoanOrder $order
     * @param ReconciliationStatus $status
     * @return bool
     */
    public function reconciliation(UserLoanOrder $order,  $status): bool
    {
        /**
         * @var LoanKudosOrder $model
         */
        $model = LoanKudosOrder::find()
            ->where(['order_id' => $order->id])
            ->andWhere(['user_id' => $order->user_id])
            ->one();

        $form = new Reconciliation();
        $form->partner_loan_id = $model->partner_loan_id;
        $form->partner_borrower_id = $model->kudosPerson->partner_borrower_id;
        $form->kudos_borrower_id = $model->kudosPerson->kudos_borrower_id;
        $form->kudos_loan_id = $model->kudos_loan_id;
        $form->loan_recon_status = $status; //枚举值
        $form->loan_recon_dte = Carbon::now()->toDateString(); //日期

        $response = $this->doPost(KudosQuery::RECONCILIATION(), $form->toArray());

        if ($response['result_code'] != 200) {
            Yii::error([
                'function' => 'reconciliation',
                'params'   => $form->toArray(),
                'response' => $response,
            ],'kudos');
            return false;
        } else {
            Yii::info([
                'function' => 'reconciliation',
                'params'   => $form->toArray(),
                'response' => $response,
            ],'kudos');
            if(ReconciliationStatus::EXTENSION()->getValue()  == $status)
            {
                $kudosStatus = LoanStatus::EXTENSION()->getValue();
            }elseif (ReconciliationStatus::PRECLOSURE()->getValue() == $status)
            {
                $kudosStatus = LoanStatus::PRECLOSURE()->getValue();
            }else{
                $kudosStatus = LoanStatus::CLOSURE()->getValue();
            }
            $model->kudos_status = $kudosStatus;
            $model->save();
        }
        return true;
    }

    /**
     * 对账接口
     * @param UserLoanOrder $order
     * @return bool
     */
    public function loanStatementRequest(UserLoanOrder $order)
    {
        /**
         * @var LoanKudosOrder $model
         */
        $model = LoanKudosOrder::find()
            ->where(['order_id' => $order->id])
            ->andWhere(['user_id' => $order->user_id])
            ->one();

        $form = new LoanStatementRequest();
        $form->partner_loan_id = $model->partner_loan_id;
        $form->partner_borrower_id = $model->kudosPerson->partner_borrower_id;
        $form->kudos_borrower_id = $model->kudosPerson->kudos_borrower_id;
        $form->kudos_loan_id = $model->kudos_loan_id;
        $form->loan_stmt_req_flg = 1; //必须为1

        $response = $this->doPost(KudosQuery::Loan_StmtReq(), $form->toArray());

        if ($response['result_code'] != 200) {
            Yii::error([
                'function' => 'loanStatementRequest',
                'params'   => $form->toArray(),
                'response' => $response,
            ],'kudos');
            return false;
        } else {
            Yii::info([
                'function' => 'loanStatementRequest',
                'params'   => $form->toArray(),
                'response' => $response,
            ],'kudos');
        }
        return true;
    }

    public function ncCheck(UserLoanOrder $order): bool
    {
        /**
         * @var LoanKudosOrder $model
         */
        $model = LoanKudosOrder::find()
            ->where(['order_id' => $order->id])
            ->andWhere(['user_id' => $order->user_id])
            ->one();

        $form = new NcCheck();
        $form->partner_loan_id = $model->partner_loan_id;
        $form->partner_borrower_id = $model->kudosPerson->partner_borrower_id;
        $form->kudos_borrower_id = $model->kudosPerson->kudos_borrower_id;
        $form->kudos_loan_id = $model->kudos_loan_id;
        $form->loan_nc_status = 'CHECK';

        $response = $this->doPost(KudosQuery::NC_CHECK(), $form->toArray());

        if ($response['result_code'] != 200) {
            Yii::error($response);
            return false;
        } else {
            $model->kudos_status = LoanStatus::NC_CHECK()->getValue();
            $model->save();
        }

        return true;
    }

    public function loanSettlement(UserLoanOrder $order): bool
    {
        /**
         * @var LoanKudosOrder $model
         */
        $model = LoanKudosOrder::find()
            ->where(['order_id' => $order->id])
            ->andWhere(['user_id' => $order->user_id])
            ->one();

        $repayment = $order->userLoanOrderRepayment;

        $form = new LoanSettlement();
        $form->partner_loan_id = $model->partner_loan_id;
        $form->partner_borrower_id = $model->kudosPerson->partner_borrower_id;
        $form->kudos_borrower_id = $model->kudosPerson->kudos_borrower_id;
        $form->kudos_loan_id = $model->kudos_loan_id;
        $form->loan_tranche_id = $model->kudos_tranche_id;
        $form->loan_repay_dte = Carbon::createFromTimestamp($repayment->closing_time)->toDateString();
        $form->loan_repay_amt = CommonHelper::CentsToUnit($repayment->true_total_money);
        $form->loan_outst_amt = CommonHelper::CentsToUnit($repayment->total_money - $repayment->true_total_money);
        $form->loan_outst_days = $repayment->overdue_day;
        $form->loan_proc_fee = CommonHelper::CentsToUnit($order->cost_fee);
        $form->kudos_loan_proc_fee = 0;
        $form->partner_loan_proc_fee = CommonHelper::CentsToUnit($order->cost_fee);
        $form->loan_proc_fee_due_flg = 'Collected';
        $form->loan_proc_fee_due_dte = Carbon::createFromTimestamp($repayment->closing_time)->toDateString();
        $form->loan_proc_fee_due_amt = 0;
        $form->partner_loan_int_amt = CommonHelper::CentsToUnit($repayment->interests);

        $response = $this->doPost(KudosQuery::LOAN_SETTLEMENT(), $form->toArray());

        if ($response['result_code'] != 200) {
            Yii::error($response);
            return false;
        } else {
            $model->kudos_status = LoanStatus::LOAN_SETTLEMENT()->getValue();
            $model->save();
        }

        return true;
    }

    public function pgTransactionNotify(UserLoanOrder $order, int $paidAmount, int $timestamp)
    {
        /**
         * @var LoanKudosOrder $model
         */
        $model = LoanKudosOrder::find()
            ->where(['order_id' => $order->id])
            ->andWhere(['user_id' => $order->user_id])
            ->one();

        $form = new PGTransactionFrom();
        $form->orderid = $order->id;
        $form->partner_borrower_id = $model->kudosPerson->partner_borrower_id;
        $form->partner_loan_id = $model->partner_loan_id;
        $form->kudos_loan_id = $model->kudos_loan_id;
        $form->loan_tranche_id = $model->kudosTranche->kudos_tranche_id;
        $form->paid_amnt = CommonHelper::CentsToUnit($paidAmount);
        $form->pmnt_timestmp = Carbon::createFromTimestamp($timestamp)->isoFormat('DD-MM-YYYY HH:mm:ss');

        $response = $this->doPost(KudosQuery::PG_TRANSACTION(), $form->toArray());

        if ($response['result_code'] != 200) {
            Yii::error([
                'function' => 'pgTransactionNotify',
                'params'   => $form->toArray(),
                'response' => $response,
            ],'kudos');
            return false;
        } else {
            Yii::info([
                'function' => 'pgTransactionNotify',
                'params'   => $form->toArray(),
                'response' => $response,
            ],'kudos');
            $model->repayment_amt = $model->repayment_amt + $paidAmount;
            $model->save();
        }

        return true;
    }

    public function pgTransactionNotifyTest(UserLoanOrder $order, int $paidAmount, int $timestamp)
    {
        /**
         * @var LoanKudosOrder $model
         */
        $model = LoanKudosOrder::find()
            ->where(['order_id' => $order->id])
            ->andWhere(['user_id' => $order->user_id])
            ->one();

        $form = new PGTransactionFrom();
        $form->orderid = $order->id;
        $form->partner_borrower_id = $model->kudosPerson->partner_borrower_id;
        $form->partner_loan_id = $model->partner_loan_id;
        $form->kudos_loan_id = $model->kudos_loan_id;
        $form->loan_tranche_id = $model->kudosTranche->kudos_tranche_id;
        $form->paid_amnt = CommonHelper::CentsToUnit($paidAmount);
        $form->pmnt_timestmp = Carbon::createFromTimestamp($timestamp)->isoFormat('DD-MM-YYYY HH:mm:ss');

        $response = $this->doPost(KudosQuery::PG_TRANSACTION(), $form->toArray());

        if ($response['result_code'] != 200) {
            Yii::error([
                'function' => 'pgTransactionNotify',
                'params'   => $form->toArray(),
                'response' => $response,
            ],'kudos');
            return false;
        } else {
            Yii::info([
                'function' => 'pgTransactionNotify',
                'params'   => $form->toArray(),
                'response' => $response,
            ],'kudos');
        }

        return true;
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

    private function getDemandContent(UserLoanOrder $order)
    {
        $service = new AgreementService();
        $form = new LoanServiceForm();
        $form->amount = $order->amount;
        $form->productId = $order->product_id;
        $clientInfo = json_decode($order->client_info,true);
        $service->getDemandPromissoryNote(
            $order->user_id,
            $form,
            $clientInfo
        );
        $fileData = $service->getResult();

        $name = $fileData['name'];
        $company = $this->companyName;
        $money = $fileData['money'];
        $interest = $fileData['interest'];
        $date = $fileData['date'];
        $termsAcceptedAt = $fileData['termsAcceptedAt'];
        $device = $fileData['device'];
        $deviceId = $fileData['deviceId'];
        $ipAddress = $fileData['ipAddress'];

        $result = <<<HTML
<!Doctype html>
<html xmlns=http://www.w3.org/1999/xhtml> 
<head>
</head>
<body>
    <p align="center">DEMAND PROMISSORY NOTE</p>
    <p>On demand I {$name} residing at {$company} severally promise to pay Kudos Finance & Investments Pvt. Ltd., Pune a
        sum of Rs. {$money} /- together with interest thereon @ {$interest} % per annum or at such other rate
        as Kudos Finance & Investments Pvt Ltd may fix from time to time. Presentment for payment and protest of this
        Note are hereby unconditionally and irrevocably waived.</p>
    <p></p>
    Agreed and accepted by the Borrower:
    <table border="1" cellspacing="0" width="100%">
        <tr>
            <td width = 60%>
                Name:
            </td>
            <td>
                {$name}
            </td>
        </tr>
        <tr>
            <td>
                Date:
            </td>
            <td>
                {$date}
            </td>
        </tr>
        <tr>
            <td>
                Digitally signed by:
            </td>
            <td>
                {$name}
            </td>
        </tr>
        <tr>
            <td>
                Terms Accepted at:
            </td>
            <td>
                {$termsAcceptedAt}
            </td>
        </tr>
        <tr>
            <td>
                Device:
            </td>
            <td>
                {$device}
            </td>
        </tr>
        <tr>
            <td>
                Device ID:
            </td>
            <td>
                {$deviceId}
            </td>
        </tr>

        <tr>
            <td>
                IP Address:
            </td>
            <td>
                {$ipAddress}
            </td>
        </tr>
    </table>
</body>
HTML;

        return $result;
    }

    private function getSanctionLetterContent(UserLoanOrder $order, string $DPNReferenceNO)
    {
        $service = new AgreementService();
        $form = new LoanServiceForm();
        $orderService = new OrderService($order);
        $form->amount = intval(CommonHelper::CentsToUnit($orderService->disbursalAmount()));
        $form->productId = $order->product_id;
        $clientInfo = json_decode($order->client_info,true);
        $service->getSanctionLetter(
            $order->user_id,
            $form,
            $clientInfo,
            $DPNReferenceNO
        );
        $fileData = $service->getResult();

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
      <span>Customer ID: {$fileData['customerId']}</span>
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
            <span>Details of the Issuer of the Sanction Letter</span>
          </p>
        </td>
        <td>
          <p>
            <span><p>{$this->companyName}</p><p>{$this->companyAddr}</p></span>
          </p>
        </td>
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
            <span>Processing fees</span>
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
            <span>Delayed payment charges</span>
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
        also available on kudosfinance.in website, which you may kindly read before confirming your acceptance. The said
        documents are incorporated here. The Borrower's acceptance to the terms of this letter and the General Terms and
        Conditions should be informed to the Lender (Kudos Finance & Investment Pvt Ltd) by submission of a Most
        Important Documents (KYC) with the terms understood by the Borrower. Further, each of the Borrower shall be
        jointly and severally responsible for compliance to the terms of this loan sanction and for repayment of the
        loan amount disbursed.</span>
    </p>
    <p>
      <span>This sanction letter will only be a letter of offer and shall stand revoked and cancelled, if there are any
        material changes in the proposal for which the Loan is sanctioned or; If any event occurs which, in the kudos finance & investment Pvt Ltd
        sole opinion is prejudicial to the kudos finance & investment Pvt Ltd interest or is likely to affect the financial condition of the
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
  </div>
</body>
</html>
HTML;
        return $result;
    }



    /**
     * 订单完结后推送
     * @param $orderId int 订单号
     * @param $planRepaymentDate Y-m-d
     * @param $trueRepaymentDate Y-m-d
     */
    public static function pushOrderClose($orderId, $planRepaymentDate, $trueRepaymentDate)
    {
        $planRepaymentTime = strtotime($planRepaymentDate);
        $trueRepaymentTime = strtotime($trueRepaymentDate);

        $diff = $planRepaymentTime - $trueRepaymentTime;
        if($diff == 0)
        {
            //正常还款
            $kudosStatus = ReconciliationStatus::CLOSURE()->getValue();
        }elseif ($diff > 0)
        {
            //提前还款
            $kudosStatus = ReconciliationStatus::PRECLOSURE()->getValue();
        }else{
            //逾期还款
            $kudosStatus = ReconciliationStatus::EXTENSION()->getValue();
        }

        $data = [
            'order_id' => $orderId,
            'kudos_status' => $kudosStatus
        ];
        $r = RedisQueue::push([RedisQueue::LIST_KUDOS_USER_ORDER_CLOSURE, json_encode($data)]);
        if($r)
        {
            Yii::info([
                'func' => 'pushOrderClose',
                'params' => $data
            ], 'kudos');
        }else{
            Yii::error([
                'func' => 'pushOrderClose',
                'params' => $data
            ], 'kudos');
        }

    }


    /**
     * 获取用户虚拟账户
     * @param $userId
     * @return array
     */
    public function actionGetUserVa($userId){
        /** @var LoanKudosPerson $loanKudosPerson */
        $loanKudosPerson = LoanKudosPerson::find()->where(['user_id' => $userId])->orderBy(['id' => SORT_DESC])->one();
        return [
            'beneficiaryName' => 'kudos',
            'bankName' => $loanKudosPerson->kudos_bankname,
            'accountNumber' => $loanKudosPerson->kudos_va_acc,
            'ifsc' => $loanKudosPerson->kudos_ifsc
        ];

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
        $nbfc = Merchant::NBFC_KUDOS;
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
        $sanctionLetterParams->nbfcShortName = AgreementService::$nbfcMap[$nbfc]['shortName'];
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
        $sanctionLetterParams->headerImg = null;
        $sanctionLetterParams->productName = $order->productSetting->product_name;

        return $sanctionLetterParams;
    }
}
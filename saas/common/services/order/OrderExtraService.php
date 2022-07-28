<?php


namespace common\services\order;

use common\models\enum\AddressProofType;
use common\models\order\UserLoanOrder;
use common\models\order\UserLoanOrderExtraRelation;
use common\models\user\LoanPerson;
use common\models\user\UserCreditReportFrVerify;

class OrderExtraService
{
    /**
     * @var UserLoanOrder $order
     */
    private $order;

    public function __construct(UserLoanOrder $order)
    {
        $this->order = $order;
    }

    /**
     * 关联订单与用户信息
     *
     * @return bool
     */
    public function relateUserLoanOrder(): bool
    {
        $model = new UserLoanOrderExtraRelation();

        $user = $this->order->loanPerson;
        $model->order_id = $this->order->id;
        $model->user_work_info_id = $user->userWorkInfo->id ?? null;
        $model->user_basic_info_id = $user->userBasicInfo->id ?? null;
        $model->user_contact_id = $user->userContact->id ?? null;
        $model->user_fr_id = $user->userFrReport->id ?? null;
        $model->user_ocr_pan_id = $user->userPanReport->id ?? null;
        $model->user_ocr_aadhaar_id = $user->userAadhaarReport->id ?? null;
        $model->user_verify_pan_id = $user->userVerifyPanReport->id ?? null;
        $model->user_fr_pan_id = $user->userFrPanReport->id ?? null;
        $model->user_fr_fr_id = $user->userFrFrReport->id ?? null;
        $model->user_language_report_id = $user->userQuestionReport->id ?? null;


        return $model->save();
    }

    /**
     * 获取订单的用户信息
     * @return array
     */
    public function getUserLoanOrderExtraInfo(): array
    {
        return [
            'userWorkInfo'         => $this->getUserWorkInfo(),
            'userBasicInfo'        => $this->getUserBasicInfo(),
            'userContact'          => $this->getUserContact(),
            'userAadhaarReport'    => $this->getUserOcrAadhaarReport(),
            'userPanReport'        => $this->getUserOcrPanReport(),
            'userFrReport'         => $this->getUserFrReport(),
            'userAadhaarPanReport' => $this->getUserAadhaarPanReport(),
            'userVerifyPanReport'  => $this->getUserVerifyPanReport(),
            'userFrFrReport'       => $this->getUserFrFrReport(),
            'userFrPanReport'      => $this->getUserFrPanReport(),
            'userFrCompareReport'  => $this->getUserFrCompareReport(),
        ];
    }

    public function getUserVerifyPanReport()
    {
        return $this->order->userVerifyPan;
    }

    /**
     * @return \common\models\user\UserWorkInfo
     */
    public function getUserWorkInfo()
    {
        return $this->order->userWorkInfo;
    }

    public function getUserBasicInfo()
    {
        return $this->order->userBasicInfo;
    }

    public function getUserContact()
    {
        return $this->order->userContact;
    }

    public function getUserOcrPanReport()
    {
        return $this->order->userCreditechOCRPan;
    }

    public function getUserOcrAadhaarReport()
    {
        return $this->order->userCreditechOCRAadhaar;
    }

    public function getUserFrReport()
    {
        return $this->order->userCreditechFr;
    }

    public function getUserFrFrReport()
    {
        return $this->order->userFrFr;
    }

    public function getUserFrPanReport()
    {
        return $this->order->userFrPan;
    }

    /**
     * 人脸对比报告（report_type 0:fr_compare_pan 1:fr_compare_fr）
     * @return UserCreditReportFrVerify
     */
    public function getUserFrCompareReport()
    {
        return $this->order->userFrFr ?? $this->order->userFrPan;
    }

    public function getUserAadhaarPanReport()
    {
        return $this->order->userVerifyPan;
    }

    public function getUserCreditReportCibil()
    {
        return $this->order->userCreditReportCibil;
    }

    public function getUserQuestionReport()
    {
        return $this->order->userQuestionReport;
    }
}
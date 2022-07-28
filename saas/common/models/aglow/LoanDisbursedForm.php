<?php


namespace common\models\aglow;

use yii\base\Model;


class LoanDisbursedForm extends Model
{
    public $loan_account_no, $principal, $interest, $total_processing_fees,
    $processing_fees, $processing_fees_gst, $loan_term, $loan_installment_num,
    $interest_rate, $amt_disbursed, $overdue_rate, $bank_ifsc, $bank_account;

    public $latitude, $longitude;

}
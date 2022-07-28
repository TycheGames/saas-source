<?php

namespace common\models\kudos;

use yii\base\Model;

/**
 * Class LoanRequestForm
 * @package common\models\kudos
 *
 * @property string $partner_borrower_id
 * @property string $borrower_fName
 * @property string $borrower_mName
 * @property string $borrower_lName
 * @property string $borrower_employer_nme
 * @property string $borrower_email
 * @property string $borrower_mob
 * @property string $borrower_dob
 * @property string $borrower_sex
 * @property string $borrower_pan_num
 * @property string $borrower_adhaar_num
 * @property string $partner_loan_id
 * @property string $partner_loan_status
 * @property string $partner_loan_bucket
 * @property string $loan_purpose
 * @property float $loan_amt
 * @property float $loan_proc_fee
 * @property float $loan_conv_fee
 * @property int $loan_disbursement_amt
 * @property string $loan_typ
 * @property int $loan_installment_num
 * @property int $loan_tenure
 */
class LoanRequestForm extends Model
{
    public $partner_borrower_id;
    public $borrower_fName;
    public $borrower_mName;
    public $borrower_lName;
    public $borrower_employer_nme;
    public $borrower_email;
    public $borrower_mob;
    public $borrower_dob;
    public $borrower_sex;
    public $borrower_pan_num;
    public $borrower_adhaar_num;
    public $partner_loan_id;
    public $partner_loan_status;
    public $partner_loan_bucket;
    public $loan_purpose;
    public $loan_amt;
    public $loan_proc_fee;
    public $loan_conv_fee;
    public $loan_disbursement_amt;
    public $loan_typ;
    public $loan_installment_num;
    public $loan_tenure;
}
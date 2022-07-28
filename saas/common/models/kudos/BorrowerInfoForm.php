<?php


namespace common\models\kudos;


use yii\base\Model;

/**
 * Class BorrowerInfoForm
 * @package common\models\kudos
 *
 * @property string $partner_borrower_id
 * @property string $kudos_borrower_id
 * @property string $borrower_fName
 * @property string $borrower_mName
 * @property string $borrower_lName
 * @property string $borrower_employer_nme
 * @property string $borrower_email
 * @property string $borrower_curr_addr
 * @property string $borrower_curr_city
 * @property string $borrower_curr_state
 * @property int $borrower_curr_pincode
 * @property string $borrower_perm_address
 * @property string $borrower_perm_city
 * @property string $borrower_perm_state
 * @property int $borrower_perm_pincode
 * @property string $borrower_marital_status
 * @property string $borrower_qualification
 * @property string $borrower_employer_id
 * @property float $borrower_salary
 * @property int $borrower_credit_score
 * @property float $borrower_foir
 * @property string $borrower_ac_holder_nme
 * @property string $borrower_bnk_nme
 * @property string $borrower_ac_num
 * @property string $borrower_bnk_ifsc
 * @property string $partner_loan_id
 * @property string $kudos_loan_id
 * @property string $loan_typ
 * @property int $loan_tenure
 * @property int $loan_installment_num
 * @property string $loan_emi_freq
 * @property int $loan_prin_amt
 * @property int $loan_proc_fee
 * @property int $loan_proc_fee_partner
 * @property int $loan_proc_fee_kudos
 * @property int $loan_conv_fee
 * @property int $loan_coupon_amt
 * @property int $loan_amt_disbursed
 * @property int $loan_int_rt
 * @property int $loan_int_amt
 * @property int $loan_int_amt_kud
 * @property int $loan_int_amt_par
 * @property string $loan_emi_dte_1
 * @property int $loan_emi_amt_1
 * @property string $loan_emi_dte_2
 * @property int $loan_emi_amt_2
 * @property string $loan_end_dte
 * @property string $loan_disbursement_upd_status
 * @property string $loan_disbursement_upd_dte
 * @property string $loan_disbursement_trans_dte
 * @property string $disbursement_trans_trac_num
 * @property int $loan_emi_recd_num
 */
class BorrowerInfoForm extends Model
{
    public $partner_borrower_id;
    public $kudos_borrower_id;
    public $borrower_fName;
    public $borrower_mName;
    public $borrower_lName;
    public $borrower_employer_nme;
    public $borrower_email;
    public $borrower_curr_addr;
    public $borrower_curr_city;
    public $borrower_curr_state;
    public $borrower_curr_pincode;
    public $borrower_perm_address;
    public $borrower_perm_city;
    public $borrower_perm_state;
    public $borrower_perm_pincode;
    public $borrower_marital_status;
    public $borrower_qualification;
    public $borrower_employer_id;
    public $borrower_salary;
    public $borrower_credit_score;
    public $borrower_foir;
    public $borrower_ac_holder_nme;
    public $borrower_bnk_nme;
    public $borrower_ac_num;
    public $borrower_bnk_ifsc;
    public $partner_loan_id;
    public $kudos_loan_id;
    public $loan_typ;
    public $loan_tenure;
    public $loan_installment_num;
    public $loan_emi_freq;
    public $loan_prin_amt;
    public $loan_proc_fee;
    public $loan_proc_fee_partner;
    public $loan_proc_fee_kudos;
    public $loan_conv_fee;
    public $loan_coupon_amt;
    public $loan_amt_disbursed;
    public $loan_int_rt;
    public $loan_int_amt;
    public $loan_int_amt_kud;
    public $loan_int_amt_par;
    public $loan_emi_dte_1;
    public $loan_emi_amt_1;
    public $loan_emi_dte_2;
    public $loan_emi_amt_2;
    public $loan_end_dte;
    public $loan_disbursement_upd_status;
    public $loan_disbursement_upd_dte;
    public $loan_disbursement_trans_dte;
    public $disbursement_trans_trac_num;
    public $loan_emi_recd_num;
}
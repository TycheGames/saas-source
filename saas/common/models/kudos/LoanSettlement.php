<?php


namespace common\models\kudos;


use yii\base\Model;

/**
 * Class LoanSettlement
 * @package common\models\kudos
 *
 * @property string $partner_borrower_id
 * @property string $kudos_borrower_id
 * @property string $partner_loan_id
 * @property string $kudos_loan_id
 * @property string $loan_tranche_id
 * @property string $loan_repay_dte
 * @property int $loan_repay_amt
 * @property int $loan_outst_amt
 * @property int $loan_outst_days
 * @property int $loan_proc_fee
 * @property int $kudos_loan_proc_fee
 * @property int $partner_loan_proc_fee
 * @property string $loan_proc_fee_due_flg
 * @property string $loan_proc_fee_due_dte
 * @property string $loan_proc_fee_due_amt
 * @property int $partner_loan_int_amt
 */
class LoanSettlement extends Model
{
    public $partner_borrower_id;
    public $kudos_borrower_id;
    public $partner_loan_id;
    public $kudos_loan_id;
    public $loan_tranche_id;
    public $loan_repay_dte;
    public $loan_repay_amt;
    public $loan_outst_amt;
    public $loan_outst_days;
    public $loan_proc_fee;
    public $kudos_loan_proc_fee;
    public $partner_loan_proc_fee;
    public $loan_proc_fee_due_flg;
    public $loan_proc_fee_due_dte;
    public $loan_proc_fee_due_amt;
    public $partner_loan_int_amt;
}
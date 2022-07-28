<?php


namespace common\models\kudos;


use yii\base\Model;

/**
 * Class StatusCheckForm
 * @package common\models\kudos
 *
 * @property string $partner_borrower_id
 * @property string $kudos_borrower_id
 * @property string $partner_loan_id
 * @property string $kudos_loan_id
 * @property string $loan_disbursement_upd_status
 * @property string $loan_recon_status
 * @property string $partner_loan_status
 * @property string $partner_loan_comments
 */
class StatusCheckForm extends Model
{
    public $partner_borrower_id;
    public $kudos_borrower_id;
    public $partner_loan_id;
    public $kudos_loan_id;
    public $loan_disbursement_upd_status;
    public $loan_recon_status;
    public $partner_loan_status;
    public $partner_loan_comments;
}
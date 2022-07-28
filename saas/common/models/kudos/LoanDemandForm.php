<?php


namespace common\models\kudos;


use yii\base\Model;

/**
 * Class LoanDemandForm
 * @package common\models\kudos
 *
 * @property string $partner_borrower_id
 * @property string $kudos_borrower_id
 * @property string $partner_loan_id
 * @property string $kudos_loan_id
 * @property string $loan_demand_note_issued
 */
class LoanDemandForm extends Model
{
    public $partner_borrower_id;
    public $kudos_borrower_id;
    public $partner_loan_id;
    public $kudos_loan_id;
    public $loan_demand_note_issued;
}
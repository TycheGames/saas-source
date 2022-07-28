<?php


namespace frontend\models\notify;


use frontend\models\BaseForm;

/**
 * Class KudosOfflineTransactionForm
 * @package frontend\models\notify
 *
 * @property string $partner_borrower_id
 * @property string $partner_loan_id
 * @property string $kudos_loan_id
 * @property string $loan_tranche_id
 * @property string $paid_amnt
 * @property string $pmnt_timestmp
 */
class KudosOfflineTransactionForm extends BaseForm
{
    public $partner_borrower_id;
    public $partner_loan_id;
    public $kudos_loan_id;
    public $loan_tranche_id;
    public $paid_amnt;
    public $pmnt_timestmp;

    function maps(): array
    {
        return [];
    }

    public function rules()
    {
        return [
            [['partner_borrower_id', 'partner_loan_id', 'kudos_loan_id', 'loan_tranche_id', 'paid_amnt', 'pmnt_timestmp'], 'required'],
        ];
    }
}
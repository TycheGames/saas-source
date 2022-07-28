<?php


namespace common\models\kudos;


use yii\base\Model;

/**
 * Class NcCheck
 * @package common\models\kudos
 *
 * @property string $partner_borrower_id
 * @property string $kudos_borrower_id
 * @property string $partner_loan_id
 * @property string $kudos_loan_id
 * @property string $loan_nc_status
 */
class NcCheck extends Model
{
    public $partner_borrower_id;
    public $kudos_borrower_id;
    public $partner_loan_id;
    public $kudos_loan_id;
    public $loan_nc_status;
}
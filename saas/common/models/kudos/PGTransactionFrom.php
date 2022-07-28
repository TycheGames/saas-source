<?php


namespace common\models\kudos;


use yii\base\Model;

/**
 * Class PGTransactionFrom
 * @package common\models\kudos
 *
 * @property int $orderid
 * @property string $partner_borrower_id
 * @property string $partner_loan_id
 * @property string $kudos_loan_id
 * @property string $loan_tranche_id
 * @property int $paid_amnt
 * @property string $pmnt_timestmp
 */
class PGTransactionFrom extends Model
{
    public $orderid;
    public $partner_borrower_id;
    public $partner_loan_id;
    public $kudos_loan_id;
    public $loan_tranche_id;
    public $paid_amnt;
    public $pmnt_timestmp;
}
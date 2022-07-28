<?php


namespace common\models\kudos;


use yii\base\Model;

/**
 * Class TrancheAppendForm
 * @package common\models\kudos
 *
 * @property string $loan_tranche_id
 * @property string $loan_disbursement_dte
 * @property int $loan_tranche_num
 * @property int $loan_tranche_amt
 */
class TrancheAppendForm extends Model
{
    public $loan_tranche_id;
    public $loan_disbursement_dte;
    public $loan_tranche_num;
    public $loan_tranche_amt;
}
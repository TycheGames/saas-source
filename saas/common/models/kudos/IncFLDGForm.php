<?php


namespace common\models\kudos;

use yii\base\Model;

/**
 * Class IncFLDGForm
 * @package common\models\kudos
 *
 * @property int $partner_fldg_perc
 * @property int $partner_disbursement_tot
 * @property string $partner_limit_ext
 * @property string $partner_strt_dte
 * @property int $fldgcycleindays
 * @property int $amounttransffered
 */
class IncFLDGForm extends Model
{
    public $partner_fldg_perc;
    public $partner_disbursement_tot;
    public $partner_limit_ext;
    public $partner_strt_dte;
    public $fldgcycleindays;
    public $amounttransffered;
}
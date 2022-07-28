<?php


namespace common\models\kudos;


use yii\base\Model;

/**
 * Class ValidationPostForm
 * @package common\models\kudos
 *
 * @property string $partner_borrower_id
 * @property string $kudos_borrower_id
 * @property string $borrower_pan_num
 * @property string $borrower_adhaar_num
 * @property int $borrower_credit_score
 */
class ValidationPostForm extends Model
{
    public $partner_borrower_id;
    public $kudos_borrower_id;
    public $borrower_pan_num;
    public $borrower_adhaar_num;
    public $borrower_credit_score;
}
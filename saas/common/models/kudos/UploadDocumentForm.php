<?php


namespace common\models\kudos;


use yii\base\Model;

/**
 * Class UploadDocumentForm
 * @package common\models\kudos
 *
 * @property string $partner_borrower_id
 * @property string $partner_loan_id
 * @property string $kudos_loan_id
 * @property string $kudos_borrower_id
 * @property string $borrower_pan_doc
 * @property string $borrower_adhaar_doc
 * @property string $borrower_photo_doc
 * @property string $borrower_cibil_doc
 * @property string $borrower_bnk_stmt_doc
 * @property string $loan_sanction_letter
 * @property string $loan_agreement_doc
 */
class UploadDocumentForm extends Model
{
    public $partner_borrower_id;
    public $partner_loan_id;
    public $kudos_loan_id;
    public $kudos_borrower_id;
    public $borrower_pan_doc;
    public $borrower_adhaar_doc;
    public $borrower_photo_doc;
    public $borrower_cibil_doc;
    public $borrower_bnk_stmt_doc;
    public $loan_sanction_letter;
    public $loan_agreement_doc;
}
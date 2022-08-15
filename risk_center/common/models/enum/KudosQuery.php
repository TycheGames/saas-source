<?php


namespace common\models\enum;


use MyCLabs\Enum\Enum;

/**
 * Class KudosQuery
 * @package common\models\enum
 *
 * Loan
 * @method static KudosQuery LOAN_QUEST()
 * @method static KudosQuery BORROWER_INFO()
 * @method static KudosQuery UPLOAD_DOCUMENT()
 * @method static KudosQuery VALIDATION_GET()
 * @method static KudosQuery VALIDATION_POST()
 * @method static KudosQuery LOAN_REPAYMENT_SCHEDULE()
 * @method static KudosQuery LOAN_TRANCHE_APPEND()
 * @method static KudosQuery STATUS_CHECK()
 * @method static KudosQuery LOAN_DEMAND_NOTE()
 * @method static KudosQuery RECONCILIATION()
 * @method static KudosQuery INCREASE_FLDG()
 * @method static KudosQuery NC_CHECK()
 * @method static KudosQuery LOAN_SETTLEMENT()
 * @method static KudosQuery PG_TRANSACTION()
 * @method static KudosQuery Loan_StmtReq()
 *
 * Auth
 * @method static KudosQuery KYC_OCR()
 * @method static KudosQuery VOTER_ID_AUTH()
 */
class KudosQuery extends Enum
{
    //Loan
    private const LOAN_QUEST = 'Loan-Request';
    private const BORROWER_INFO = 'Borrower-Info';
    private const UPLOAD_DOCUMENT = 'Document-Upload';
    private const VALIDATION_GET = 'Validation-Get';
    private const VALIDATION_POST = 'Validation-Post';
    private const LOAN_REPAYMENT_SCHEDULE = 'Repayment-Schedule';
    private const LOAN_TRANCHE_APPEND = 'Tranche-Append';
    private const STATUS_CHECK = 'Status-Check';
    private const LOAN_DEMAND_NOTE = 'Loan-DemandNote';
    private const RECONCILIATION = 'Reconciliation';
    private const INCREASE_FLDG = 'Increase-FLDG';
    private const NC_CHECK = 'NC-Check';
    private const LOAN_SETTLEMENT = 'Loan-Settlement';
    private const PG_TRANSACTION = 'PG-Transaction';
    private const Loan_StmtReq = 'Loan-StmtReq';

    //Auth
    private const KYC_OCR = 'KYC-OCR';
    private const VOTER_ID_AUTH = 'Voter-ID-Verification';
}
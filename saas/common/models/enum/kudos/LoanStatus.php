<?php

namespace common\models\enum\kudos;

use MyCLabs\Enum\Enum;

/**
 * Class LoanStatus
 * @package common\models\enum\kudos
 *
 * @method static LoanStatus INIT()
 * @method static LoanStatus LOAN_REQUEST()
 * @method static LoanStatus BORROWER_INFO()
 * @method static LoanStatus UPLOAD_DOCUMENT()
 * @method static LoanStatus LOAN_REPAYMENT_SCHEDULE()
 * @method static LoanStatus VALIDATION_GET()
 * @method static LoanStatus TRANCHE_APPEND()
 * @method static LoanStatus VALIDATION_GET_2()
 * @method static LoanStatus STATUS_CHECK()
 * @method static LoanStatus VALIDATION_POST()
 * @method static LoanStatus LOAN_DEMAND_NOTE_ISSUED()
 * @method static LoanStatus LOAN_DEMAND_NOTE_RAISED()
 * @method static LoanStatus CLOSURE()
 * @method static LoanStatus EXTENSION()
 * @method static LoanStatus NC_CHECK()
 * @method static LoanStatus LOAN_SETTLEMENT()
 * @method static LoanStatus PRECLOSURE()
 */
class LoanStatus extends Enum
{
    private const INIT = 0;
    //数据推送
    private const LOAN_REQUEST = 11;
    private const BORROWER_INFO = 12;
    private const UPLOAD_DOCUMENT = 13;
    private const VALIDATION_GET = 14;
    private const LOAN_REPAYMENT_SCHEDULE = 15;
    private const TRANCHE_APPEND = 16;
    //数据检验
    private const VALIDATION_GET_2 = 21;
    private const STATUS_CHECK = 22;
    private const NC_CHECK = 23;
    private const VALIDATION_POST = 24;
    //贷后状态
    private const LOAN_DEMAND_NOTE_ISSUED = 31; //逾期第二天
    private const LOAN_DEMAND_NOTE_RAISED = 32; //逾期第七天
    private const CLOSURE = 33; //订单完成，包括提前还款
    private const EXTENSION = 34; //订单延期
    private const PRECLOSURE = 35; //提前还款
    //对账
    private const LOAN_SETTLEMENT = 41;
}
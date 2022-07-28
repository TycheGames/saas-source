<?php


namespace common\models\enum;


use MyCLabs\Enum\Enum;

/**
 * Class DataType
 * @package common\models\enum\creditech
 *
 * @method static CreditReportStatus NOT_RECEIVED()
 * @method static CreditReportStatus RECEIVED()
 * @method static CreditReportStatus REJECT_ERROR()
 * @method static CreditReportStatus REJECT_VALUE()
 * @method static CreditReportStatus PASS()
 */
class CreditReportStatus extends Enum
{
    private const NOT_RECEIVED= 0;
    private const RECEIVED = 1;
    private const REJECT_ERROR = 2;
    private const REJECT_VALUE = 3;
    private const PASS = 4;
}
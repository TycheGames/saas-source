<?php


namespace common\models\enum\aglow;


use MyCLabs\Enum\Enum;

/**
 * Class LoanType
 * @package common\models\enum\kudos
 *
 * @method static ApplyStatus PASS()
 * @method static ApplyStatus REJECT()
 */
class ApplyStatus extends Enum
{
    private const PASS = 'pass';
    private const REJECT = 'reject';
}
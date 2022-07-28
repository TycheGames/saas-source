<?php


namespace common\models\enum\aglow;


use MyCLabs\Enum\Enum;

/**
 * Class LoanStatus
 * @package common\models\enum\kudos
 *
 * @method static LoanStatus SUCCESS()
 * @method static LoanStatus FAIL()
 */
class LoanStatus extends Enum
{
    private const SUCCESS = 'success';
    private const FAIL = 'fail';
}
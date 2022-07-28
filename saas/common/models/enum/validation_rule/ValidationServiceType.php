<?php


namespace common\models\enum\validation_rule;


use MyCLabs\Enum\Enum;

/**
 * Class CreditechReportType
 * @package common\models\enum
 *
 * @method static ValidationServiceType VERIFY_PAN()
 * @method static ValidationServiceType VERIFY_BANK()
 */
class ValidationServiceType extends Enum
{
    private const VERIFY_PAN = 1;
    private const VERIFY_BANK = 2;

    public static $map = [
        self::VERIFY_PAN  => 'Pan verification service',
        self::VERIFY_BANK => 'Bank card authentication service',
    ];
}
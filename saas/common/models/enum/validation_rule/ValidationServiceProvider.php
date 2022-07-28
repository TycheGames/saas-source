<?php


namespace common\models\enum\validation_rule;


use MyCLabs\Enum\Enum;

/**
 * Class CreditechReportType
 * @package common\models\enum
 *
 * @method static ValidationServiceProvider VERIFY_PAN_ACCUAUTH()
 * @method static ValidationServiceProvider VERIFY_PAN_ACCUAUTH_LITE()
 * @method static ValidationServiceProvider VERIFY_BANK_YUAN_DING()
 * @method static ValidationServiceProvider VERIFY_BANK_AADHAAR_API()
 */
class ValidationServiceProvider extends Enum
{
    private const VERIFY_PAN_ACCUAUTH = 1;
    private const VERIFY_PAN_ACCUAUTH_LITE = 2;
    private const VERIFY_BANK_YUAN_DING = 3;
    private const VERIFY_BANK_AADHAAR_API = 4;

    public static $map = [
        self::VERIFY_PAN_ACCUAUTH      => 'AccuauthPan verification service',
        self::VERIFY_PAN_ACCUAUTH_LITE => 'AccuauthPanLite verification service',
        self::VERIFY_BANK_YUAN_DING    => 'Yuanding Bank Card Certification',
        self::VERIFY_BANK_AADHAAR_API  => 'AadhaarApi bank card certification',
    ];
}
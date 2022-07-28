<?php


namespace common\models\enum;


use MyCLabs\Enum\Enum;

/**
 * Class Seniority
 * @package common\models\enum
 *
 * @method static Seniority MONTH_0()
 * @method static Seniority MONTH_1()
 * @method static Seniority MONTH_3()
 * @method static Seniority MONTH_6()
 * @method static Seniority YEARS_1()
 * @method static Seniority YEARS_3()
 * @method static Seniority YEARS_5()
 */
class Seniority extends Enum
{
    use TCommon;

    private const MONTH_0 = 0;
    private const MONTH_1 = 1;
    private const MONTH_3 = 2;
    private const MONTH_6 = 3;
    private const YEARS_1 = 4;
    private const YEARS_3 = 5;
    private const YEARS_5 = 6;

    public static $map = [
        self::MONTH_0 => 'Less than 1 months',
        self::MONTH_1 => '1 months to 3 months',
        self::MONTH_3 => '3 months to 6 months',
        self::MONTH_6 => '6 months to 12 months',
        self::YEARS_1 => '1-2 Years',
        self::YEARS_3 => '3-5 Years',
        self::YEARS_5 => '5 Years+',
    ];
}
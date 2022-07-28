<?php

namespace common\models\enum;

use MyCLabs\Enum\Enum;

/**
 * 枚举值：婚姻
 *
 * @method static Marital UNMARRIED()
 * @method static Marital MARRIED_BUT_CHILDLESS()
 * @method static Marital MARRIED_WITH_CHILDREN()
 * @method static Marital DIVORCED()
 * @method static Marital WIDOWED()
 */
class Marital extends Enum
{
    use TCommon;

    private const UNMARRIED = 1;
    private const MARRIED_BUT_CHILDLESS = 2;
    private const MARRIED_WITH_CHILDREN = 3;
    private const DIVORCED_WIDOWED = 4;

    public static $map = [
        self::UNMARRIED             => 'Unmarried',
        self::MARRIED_BUT_CHILDLESS => 'Married but childless',
        self::MARRIED_WITH_CHILDREN => 'Married with children',
        self::DIVORCED_WIDOWED      => 'Divorced/Widowed',
    ];

    public static $mapForKudos = [
        self::UNMARRIED             => 'SINGLE',
        self::MARRIED_BUT_CHILDLESS => 'MARRIED',
        self::MARRIED_WITH_CHILDREN => 'MARRIED',
        self::DIVORCED_WIDOWED      => 'DIVORCED',
    ];
}
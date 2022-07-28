<?php


namespace common\models\enum;


use MyCLabs\Enum\Enum;

/**
 * Class Gender
 * @package common\models\enum
 *
 * @method static Gender FEMALE()
 * @method static Gender MALE()
 * @method static Gender TRANSGENDER()
 */
class Gender extends Enum
{
    use TCommon;

    private const FEMALE = 0;
    private const MALE = 1;
    private const TRANSGENDER = 2;

    public static $map = [
        self::FEMALE => 'female',
        self::MALE   => 'male',
        self::TRANSGENDER => 'transgender',
    ];

    public static $mapForKudos = [
        self::FEMALE => 'F',
        self::MALE   => 'M',
    ];

    public static $mapForAglow = [
        self::FEMALE => 'F',
        self::MALE   => 'M',
        self::TRANSGENDER => 'NA'
    ];
    public static $mapForKudosExperian = [
        self::FEMALE      => 2,
        self::MALE        => 1,
        self::TRANSGENDER => 3,
    ];
}
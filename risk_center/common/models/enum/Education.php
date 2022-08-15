<?php


namespace common\models\enum;


use MyCLabs\Enum\Enum;

/**
 * Class Education
 * @package common\models\enum
 *
 * @method static Education PRIMARY()
 * @method static Education MIDDLE_SCHOOL()
 * @method static Education HIGH_SCHOOL()
 * @method static Education SENIOR_SECONDARY()
 * @method static Education UNDER_GRADUATE()
 * @method static Education POST_GRADUATE()
 * @method static Education DOCTOR()
 * @method static Education OTHER()
 */
class Education extends Enum
{
    use TCommon;

    private const PRIMARY = 1;
    private const MIDDLE_SCHOOL = 2;
    private const HIGH_SCHOOL = 3;
    private const SENIOR_SECONDARY = 4;
    private const UNDER_GRADUATE = 5;
    private const POST_GRADUATE = 6;
    private const DOCTOR = 7;
    private const OTHER = 0;

    public static $map = [
        self::PRIMARY          => 'Primary',
        self::MIDDLE_SCHOOL    => 'Middle School',
        self::HIGH_SCHOOL      => 'High School',
        self::SENIOR_SECONDARY => 'Senior Secondary',
        self::UNDER_GRADUATE   => 'Under Graduate',
        self::POST_GRADUATE    => 'Post Graduate',
        self::DOCTOR           => 'Doctor',
        self::OTHER            => 'Below Primary School',
    ];
}
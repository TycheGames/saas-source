<?php


namespace common\models\enum;


use MyCLabs\Enum\Enum;

/**
 * Class Relative
 * @package common\models\enum
 *
 * @method static Relative PARENT()
 * @method static Relative BROTHER()
 * @method static Relative SISTER()
 * @method static Relative CHILD()
 */
class Relative extends Enum
{
    use TCommon;

    private const PARENT = 1;
    private const BROTHER = 2;
    private const SISTER = 3;
    private const CHILD = 4;

    public static $map = [
        self::PARENT  => 'Parent',
        self::BROTHER => 'Brother',
        self::SISTER  => 'Sister',
        self::CHILD   => 'Child',
    ];
}
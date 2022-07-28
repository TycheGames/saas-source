<?php

namespace common\models\enum;

use MyCLabs\Enum\Enum;

/**
 * 枚举值：学生
 *
 * @method static Student YES()
 * @method static Student NO()
 */
class Student extends Enum
{
    use TCommon;

    private const YES = 1;
    private const NO = 0;

    public static $map = [
        self::NO  => 'NO',
        self::YES => 'YES',
    ];
}
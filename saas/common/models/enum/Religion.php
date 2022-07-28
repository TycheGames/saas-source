<?php

namespace common\models\enum;

use MyCLabs\Enum\Enum;

/**
 * 枚举值：宗教信仰
 *
 * @method static Religion HINDUISM()
 * @method static Religion ISLAM()
 * @method static Religion CHRISTIANITY()
 * @method static Religion SIKHISM()
 * @method static Religion BUDDHISM()
 * @method static Religion JAINISM()
 * @method static Religion ZOROASTRIANISM()
 * @method static Religion JUDAISM()
 * @method static Religion OTHERS()
 * @method static Religion NO_RELIGION()
 */
Class Religion extends Enum
{
    use TCommon;

    private const HINDUISM = 1;
    private const ISLAM = 2;
    private const CHRISTIANITY = 3;
    private const SIKHISM = 4;
    private const BUDDHISM = 5;
    private const JAINISM = 6;
    private const ZOROASTRIANISM = 7;
    private const JUDAISM = 8;
    private const OTHERS = 9;
    private const NO_RELIGION = 10;

    public static $map = [
        self::HINDUISM       => 'HINDUISM',
        self::ISLAM          => 'ISLAM',
        self::CHRISTIANITY   => 'CHRISTIANITY',
        self::SIKHISM        => 'SIKHISM',
        self::BUDDHISM       => 'BUDDHISM',
        self::JAINISM        => 'JAINISM',
        self::ZOROASTRIANISM => 'ZOROASTRIANISM',
        self::JUDAISM        => 'JUDAISM',
        self::OTHERS         => 'OTHERS',
        self::NO_RELIGION    => 'NO RELIGION',
    ];
}
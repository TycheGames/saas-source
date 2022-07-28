<?php


namespace common\models\enum;


use MyCLabs\Enum\Enum;

/**
 * Class DrawStep
 * @package common\models\enum
 *
 * @method static DrawStep FUND_MATCH()
 * @method static DrawStep MANUAL_DRAW()
 * @method static DrawStep AUTO_DRAW()
 */
class DrawStep extends Enum
{
    use TCommon;

    private const FUND_MATCH = 1; //资方分配
    private const MANUAL_DRAW = 2; //手动提现
    private const AUTO_DRAW = 3; //自动提现


}
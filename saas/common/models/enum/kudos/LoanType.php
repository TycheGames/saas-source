<?php


namespace common\models\enum\kudos;


use MyCLabs\Enum\Enum;

/**
 * Class LoanType
 * @package common\models\enum\kudos
 *
 * @method static ApplyStatus BULLET()
 * @method static ApplyStatus EMI()
 */
class LoanType extends Enum
{
    private const BULLET = 'Bullet';
    private const EMI = 'EMI';
}
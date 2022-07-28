<?php


namespace common\models\enum\kudos;


use MyCLabs\Enum\Enum;

/**
 * Class ReconciliationStatus
 * @package common\models\enum\kudos
 *
 * @method static ReconciliationStatus CLOSURE()
 * @method static ReconciliationStatus PRECLOSURE()
 * @method static ReconciliationStatus EXTENSION()
 */
class ReconciliationStatus extends Enum
{
    private const CLOSURE = 'Loan Closure';
    private const PRECLOSURE = 'Loan Preclosure Request';
    private const EXTENSION = 'Loan Repayment Extension';
}
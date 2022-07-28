<?php


namespace common\models\enum\aglow;


use MyCLabs\Enum\Enum;

/**
 * Class CustomerConfirm
 * @package common\models\enum\kudos
 *
 * @method static CustomerConfirm ACCEPTED()
 * @method static CustomerConfirm DECLINED()
 */
class CustomerConfirm extends Enum
{
    private const ACCEPTED = 'accepted';
    private const DECLINED = 'declined';
}
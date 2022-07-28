<?php


namespace common\models\enum;


use MyCLabs\Enum\Enum;

/**
 * Class addressProofType
 * @package common\models\enum
 *
 * @method static AddressProofType VOTER_ID()
 * @method static AddressProofType PASSPORT()
 * @method static AddressProofType DRIVER()
 * @method static AddressProofType AADHAAR()
 */
class AddressProofType extends Enum
{
    private const VOTER_ID = 1;
    private const PASSPORT = 2;
    private const DRIVER = 3;
    private const AADHAAR = 4;
}
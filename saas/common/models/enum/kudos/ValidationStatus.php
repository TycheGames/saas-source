<?php

namespace common\models\enum\kudos;

use MyCLabs\Enum\Enum;

/**
 * Class LoanStatus
 * @package common\models\enum\kudos
 *
 * @method static LoanStatus INIT()
 * @method static LoanStatus WAIT_VALIDATION()
 * @method static LoanStatus VALIDATION_SUCCESS()
 * @method static LoanStatus VALIDATION_FAILED()

 */
class ValidationStatus extends Enum
{
    private const INIT = 0;
    //数据推送
    private const WAIT_VALIDATION = 1; //待验证
    private const VALIDATION_SUCCESS = 2; //验证通过
    private const VALIDATION_FAILED = -1; //验证失败

}
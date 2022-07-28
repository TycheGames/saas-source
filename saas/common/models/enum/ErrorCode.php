<?php

namespace common\models\enum;

use MyCLabs\Enum\Enum;

/**
 * 枚举值:错误码
 *
 * @method static ErrorCode SUCCESS() 成功
 * @method static ErrorCode ERROR_COMMON() 通用错误
 * @method static ErrorCode NOT_LOGGED() 未登录
 * @method static ErrorCode PARAMS_INVALID() 参数无效
 * @method static ErrorCode SYSTEM_EXCEPTION() 系统异常
 *
 */
class ErrorCode extends Enum
{
    private const SUCCESS = 0; //成功
    private const ERROR_COMMON = -1; //通用错误
    private const NOT_LOGGED = -2; //未登录
    private const PARAMS_INVALID = 2; //参数无效
    private const SYSTEM_EXCEPTION = 3; //系统异常
}

<?php

namespace common\helpers;

use yii\helpers\Json;

class ErrorCodeHelper {
    const CODE_FAILED = -1;
    const CODE_SUCCESS = 0;
    const CODE_DEBIT_ERROR = 4;
    const CODE_TIP_ALERT = 5;
    const CODE_TIP_CONFIRM = 6;
    const CODE_TIP_MESSAGE = 10;
    const CODE_SYS_ERROR = 1000;
    const CODE_INPUT_INVALID = 1001;
    const CODE_NOT_FOUND = 2001;
    const CODE_NO_LOGIN = 1002;
    const CODE_NO_PERM = 1003;
    const CDDE_UNFINISHED_VERIFY = 5001;

    static $err_map = [
        self::CODE_FAILED => '失败',
        self::CODE_SUCCESS => '成功',
        self::CODE_DEBIT_ERROR => '页面过期',
        self::CODE_TIP_ALERT => '弹出带确认按钮的警告框',
        self::CODE_TIP_CONFIRM => '弹出带取消按钮和跳转按钮的确认框',
        self::CODE_TIP_MESSAGE => '提示文案',
        self::CODE_SYS_ERROR => '系统错误',
        self::CODE_INPUT_INVALID => '输入错误',
        self::CODE_NOT_FOUND => '未找到',
        self::CODE_NO_LOGIN => '请先登录账户',
        self::CODE_NO_PERM => '没有相关权限',
    ];
}

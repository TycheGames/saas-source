<?php

namespace common\models\enum\mg_user_content;


use MyCLabs\Enum\Enum;

/**
 * Class UserContentType
 * @package common\models\enum\mg_user_content
 *
 * @method static UserContentType SMS()
 * @method static UserContentType APP_LIST()
 * @method static UserContentType CONTACT()
 * @method static UserContentType CALL_RECORDS()
 */
class UserContentType extends Enum
{
    private const SMS = 1; //短信
    private const APP_LIST = 4; //app列表
    private const CONTACT = 3; //通讯录
    private const CALL_RECORDS = 2; //通话记录
}
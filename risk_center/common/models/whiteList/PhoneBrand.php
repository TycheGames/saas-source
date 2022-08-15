<?php
namespace common\models\whiteList;
use yii\base\Model;

/**
 * 手机品牌
 * Class PhoneBrand
 * @package common\models\whiteList
 */
class PhoneBrand extends Model {

    //手机品牌白名单，必须是全大写
    public static $whiteList = [
        "ONEPLUS",
        "SAMSUNG",
        "HUAWEI",
        "HONOR",
        "XIAOMI",
        "REDMI",
        "REALME",
        "NOKIA",
        "LENOVO",
        "MOTOROLA",
        "LG",
        "VIVO",
        "OPPO",
        "APPLE",
        "HTC",
        "GOOGLE",
        "POCO",
        "ASUS",
        "INFINIX",
        "SONY",
        "10OR",
        "PANASONIC",
    ];
}
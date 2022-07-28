<?php
namespace common\helpers;



class Util {

    /**
     * 印度手机正则 规则：9开头 + 位数字
     * @return string
     */
    public static function getPhoneMatch(){
        return '/^(0091|91|0){0,1}([6-9]{1}[0-9]{9})$/';
    }

    /**
     * 印度手机正则 提供给注册使用
     * @return string
     */
    public static function getPhoneMatchForReg() {
        return '/^[6-9]{1}[0-9]{9}$/';
    }

    public static function getPanNumberMath() {
        return '/^[a-zA-Z]{5}[0-9]{4}[a-zA-Z]{1}$/';
    }

    public static function getAadNumberMath() {
        return '/^[0-9]{12}$/';
    }

    public static function getVoterIDMath() {
        return '/^(([a-z]{3}\/?\d{7})|([a-z]{2} ?\/ ?\d{1,2} ?\/ ?\d{3} ?\/ ?\d{6,7})|([a-z]{2}\d{12}))$/i';
    }

    /**
     * 校验印度手机号 规则：9开头 + 9位数字
     * @return boolean true|false
     */
    public static function verifyPhone($phone)
    {
        return boolval(preg_match(static::getPhoneMatch(), $phone));
    }


    /**
     * 判断是否是命令行环境下
     * @return boolean true|false
     */
    public static function isCli()
    {
        return preg_match("/cli/i", php_sapi_name());
    }

    public static function is_ip($gonten){
        $ip=explode(".",$gonten);
        for($i=0;$i<count($ip);$i++)
        {
            if($ip[$i]>255){
                return(0);
            }
        }
        return preg_match("/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/",$gonten);
    }

    /**
     * 获取用户ip地址
     */
    public static function getUserIP() {
        if (!\Yii::$app instanceof \yii\web\Application) {
            return "192.168.0.1";
        }

        $ip = \Yii::$app->request->getUserIP();
        if (\strstr($ip, ',')) {//含有逗号
            $a = \explode(',', $ip);
            return trim($a[1]);
        }
        else if (empty($ip) || strstr($ip, ":")) {
            return "192.168.0.1";
        }
        else {
            return $ip;
        }
    }

    /**
     * 简称
     * @param string $class
     * @param string $func
     * @return string
     */
    public static function short($class, $func) {
        if (\strpos($class, '\\') !== false) {
            $_tmp = \explode('\\', $class);
            $class = \array_pop($_tmp);
        }

        if (\strpos($func, 'action') !== false) {
            $func = \str_replace('action', '', $func);
        }

        return \sprintf('%s::%s', $class, $func);
    }


    /**
     * php-cli 公共设置修改
     * @param int $mem
     */
    public static function cliLimitChange($mem=512)
    {
        \set_time_limit(0);
        \ini_set('memory_limit', "{$mem}m");
    }

    private static $phone_area_code = [79,145,183,240,80,755,674,172,44,422,11,361,40,731,141,291,484,33,474,481,522,161,824,22,821,712,253,413,20,261,471,294,265,866,891];
    //判断手机号的有效性
    public static function isValidPhone($phone){
        $str_arr = ['0','1','2','3','4','5','6','7','8','9','+',')','('];
        $len = strlen($phone);
        for ($i = 0; $i < $len; $i++){
            if(!in_array($phone[$i],$str_arr)){
                return false;
            }
        }
        if($len < 10){
            return false;
        }else{
            $ten_phone = substr($phone,-10);
            for ($i = 0; $i < 10; $i++){
                if(!is_numeric($ten_phone[$i])){
                    return false;
                }
            }
            if(empty(array_intersect([substr($ten_phone,0,2),substr($ten_phone,0,3)], self::$phone_area_code))){
                if(substr($ten_phone,0,4) < 6000){
                    return false;
                }
            }
            if($len == 11){
                if($phone[0] != 0){
                    return false;
                }
            }
            if($len >= 12){
                if(!in_array(substr($phone,-12,2), ['91', '1)'])){
                    return false;
                }
            }
        }
        return true;
    }
}

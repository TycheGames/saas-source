<?php

namespace common\helpers;

use common\models\GlobalSetting;
use light\hashids\Hashids;
use yii\helpers\Console;
use yii\helpers\Json;
use Yii;

class CommonHelper
{

    /**
     * 生成订单号
     * @return string
     */
    public static function generateUuid()
    {
        return uniqid('order_') . sprintf('%02d', mt_rand(0,9999));
    }

    /**
     * 清空全部的 schema cache
     * @return bool
     */
    public static function clearSchemaCache() {
        $ret = false;
        try {
            $db_names = ['db','db_read_1'];
            foreach($db_names as $_db) {
                $db_ins = \yii::$app->get($_db);
                if ($db_ins) {
                    $db_ins->schema->refresh();
                }
            }

            $ret = true;
        }
        catch(\Exception $e) {
            \yii::warning( sprintf('clear_all_schema_cache_failed: %s', $e), 'system_genral' );
        }

        return $ret;
    }

    /**
     * 加锁 (用在console中)
     * @param string $lock_name
     */
    public static function lock($lock_name = NULL)
    {
        if (empty($lock_name)) {
            $backtrace = \debug_backtrace(null, 2);
            $class = $backtrace[1]['class']; # self::class
            $func = $backtrace[1]['function'];
            $args = \implode('_', $backtrace[1]['args']);
            $lock_name = \base64_encode($class . $func . $args);
        }
        $lock = \yii::$app->mutex->acquire($lock_name);
        if (!$lock) {
            $_err = "cannot get lock {$lock_name}.";
            if (self::inConsole()) {
                # CommonHelper::info( $_err );
                return FALSE;
            }

            throw new \Exception($_err);
        }

        \register_shutdown_function(function () use ($lock_name) {
            return \yii::$app->mutex->release($lock_name);
        });

        return TRUE;
    }


    /**
     * 是否在 console 上下文中
     * @return boolean
     */
    public static function inConsole()
    {
        return \yii::$app instanceof \yii\console\Application;
    }

    public static function stdout($string)
    {
        if (Console::streamSupportsAnsiColors(\STDOUT)) {
            $args = func_get_args();
            array_shift($args);
            $string = Console::ansiFormat($string, $args);
        }
        return Console::stdout($string);
    }



    public static function object_to_array($object)
    {
        if (is_null($object)) return null;
        $ret = array();
        foreach ($object as $key => $value) {
            $value_type = gettype($value);
            if ($value_type == "array" || $value_type == "object") {
                $ret[$key] = CommonHelper::object_to_array($value);
            } else {
                $ret[$key] = $value;
            }
        }
        return $ret;
    }


    /**
     * 获取menu中定义的配置
     * @param string $nav
     * @return array
     */
    public static function getNavConfig($nav)
    {
        $root = \yii::$app->basePath;
        include_once "{$root}/config/menu.php";
        $ret = [];
        foreach ($menu as $_l1_key => $_info) {
            foreach ($_info as $_l2_key => $_l2_info) {
                if ($_l2_key == $nav) {
                    $ret = $_l2_info;
                    break 2;
                }
            }
        }

        return $ret;
    }


    /***************公用************ end */


    public static function from10_to62($num)
    {
        $to = 62;
        $dict = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $ret = '';
        do {
            $ret = $dict[bcmod($num, $to)] . $ret;
            $num = bcdiv($num, $to);
        } while ($num > 0);
        return $ret;
    }

    public static function from62_to10($num)
    {
        $from = 62;
        $num = strval($num);
        $dict = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $len = strlen($num);
        $dec = 0;
        for ($i = 0; $i < $len; $i++) {
            $pos = strpos($dict, $num[$i]);
            $dec = bcadd(bcmul(bcpow($from, $len - $i - 1), $pos), $dec);
        }
        return $dec;

    }


    /**
     * 将“分”转化成“元”
     * @param $num
     * @return string
     */
    public static function CentsToUnit($num)
    {
        return sprintf("%.2f", $num / 100);
    }

    /**
     * 将“元”转化成“分”
     * 比如：10.01 变成 1001
     */
    public static function UnitToCents($num)
    {
        return intval(bcmul(floatval($num), 100));
    }

    /**
     * 计算2个经纬度之前的距离
     * @param $lng1
     * @param $lat1
     * @param $lng2
     * @param $lat2
     * @return float|int
     */
    public static function GetDistance($lng1, $lat1, $lng2, $lat2) {
        // 将角度转为狐度
        $radLat1 = deg2rad($lat1); //deg2rad()函数将角度转换为弧度
        $radLat2 = deg2rad($lat2);
        $radLng1 = deg2rad($lng1);
        $radLng2 = deg2rad($lng2);
        $a = $radLat1 - $radLat2;
        $b = $radLng1 - $radLng2;
        $s = 2 * asin(sqrt(pow(sin($a / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2))) * 6378.137 * 1000;
        return $s;
    }

    /**
     * 相册经纬度转换
     * @param $dms
     * @param $ref
     */
    public static function GetDecimalFromDms($dms, $ref){
        $data = explode(',', $dms);

        if(count($data) < 3){
            return false;
        }

        $degrees = explode('/', $data[0])[0] / explode('/', $data[0])[1];
        $minutes = explode('/', $data[1])[0] / explode('/', $data[1])[1] / 60;
        $seconds = explode('/', $data[2])[0] / explode('/', $data[2])[1] / 3600;

        if(in_array($ref, ['S', 'W'])){
            $degrees = -$degrees;
            $minutes = -$minutes;
            $seconds = -$seconds;
        }

        return round($degrees + $minutes + $seconds, 5);
    }


    /**
     * 字符串掩码
     * @param string $str 需要掩码的字符串
     * @param int $prefix 保留前几位
     * @param int $suffix 保留后几位
     * @return string
     */
    public static function strMask($str, $prefix, $suffix)
    {
        $count = mb_strlen($str);
        $prefix = substr($str,0,$prefix);
        $suffix = substr($str,0 - $suffix, $suffix);
        $mask_count = $count - $prefix - $suffix;
        if($mask_count <= 0)
        {
            $mask = '*****';
        }else{
            $mask = str_repeat('*', $mask_count);
        }
        return $prefix . $mask . $suffix;

    }

    /**
     * 姓名拆分匹配
     * @param $name1
     * @param $name2
     * @return int
     */
    public static function nameDiff($name1,$name2){
        preg_match_all('/\S+/', $name1, $name1_new);
        preg_match_all('/\S+/', $name2, $name2_new);

        $count = 0;
        foreach ($name1_new[0] as $v){
            foreach ($name2_new[0] as $val){
                if(strtoupper($v) == strtoupper($val)){
                    $count++;
                    break;
                }
            }
        }

        return $count;
    }

    /**
     * 拆分姓名
     * @param string $name
     * @return array
     */
    public static function getNameConversion(string $name): array
    {
        $arr = explode(' ', $name);
        if (count($arr) > 2) {
            $first_name = array_shift($arr);
            $middle_name = array_shift($arr);
            $last_name = implode(' ', $arr);
        } else {
            $first_name = array_shift($arr);
            $middle_name = '';
            $last_name = array_shift($arr) ?? '';
        }

        return [
            'first_name'  => $first_name,
            'middle_name' => $middle_name,
            'last_name'   => $last_name,
        ];
    }

    /**
     * 下拉列表双语
     * @param $array
     * @return array
     */
    public static function getListT($array){
        $list = array_map( function ($var){
            return Yii::t('common', $var);
        },$array);

        return $list;
    }


}

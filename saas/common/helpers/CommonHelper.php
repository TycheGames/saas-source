<?php

namespace common\helpers;

use common\models\GlobalSetting;
use light\hashids\Hashids;
use yii\helpers\Console;
use yii\helpers\Html;
use yii\helpers\Json;
use Yii;

class CommonHelper
{
    // Hashids配置
    public static $arrHashidsConfig  = ['salt'=>'jc', 'minHashLength'=>10];
    private static $sIdDisplayStatus = '';

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
            $db_names = ['db','db_read_1','db_stats', 'db_assist', 'db_assist_read'];
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
     * @param string $mask 掩码
     * @return string
     */
    public static function strMask($str, $prefix, $suffix, $mask = '*')
    {
        $count = mb_strlen($str);
        $prefix = substr($str,0,$prefix);
        $suffix = substr($str,0 - $suffix, $suffix);
        $mask_count = $count - mb_strlen($prefix) - mb_strlen($suffix);
        if($mask_count <= 0)
        {
            $mask = str_repeat($mask, 5);
        }else{
            $mask = str_repeat($mask, $mask_count);
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
     * 名字不区分大小写去除空格对比
     * @param $name1
     * @param $name2
     * @return bool
     */
    public static function nameCompare($name1, $name2)
    {
        $name1 = strtolower(preg_replace('/\s+/', '', $name1));
        $name2 = strtolower(preg_replace('/\s+/', '', $name2));
        return $name1 == $name2;
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


    /**
     * 将传入ID进行Hashids加密
     * @param int $nId ID
     * @param string $sPrefix 前缀
     * @return bool|string
     */
    public static function idEncryption( int $nId, string $sPrefix = 'default')
    {
        // 判断显示状态
        if (empty(self::$sIdDisplayStatus)) {
            $oSetting = GlobalSetting::find()->where(['key'=>'id_display_status'])->one();
            self::$sIdDisplayStatus = $oSetting->value;
        }

        if (self::$sIdDisplayStatus == 'clear') return $nId;

        if (empty($nId)) return false;

        $sId = $sPrefix . '_' . (new Hashids(self::$arrHashidsConfig))->encode($nId);

        return $sId;

    }// END idEncryption


    /**
     * 对Hashids加密的ID进行解密
     * @param $sEncryptedId
     * @return bool|mixed
     */
    public static function idDecryption( string $sEncryptedId )
    {
        // 判断显示状态
        if (empty(self::$sIdDisplayStatus)) {
            $oSetting = GlobalSetting::find()->where(['key'=>'id_display_status'])->one();
            self::$sIdDisplayStatus = $oSetting->value;
        }

        if (self::$sIdDisplayStatus == 'clear') return $sEncryptedId;

        if (empty($sEncryptedId)) return false;

        // 去掉前缀
        $sEncryptedId = trim(strrchr($sEncryptedId, '_'),'_');

        // 转换为真正的ID
        $result = (new Hashids(self::$arrHashidsConfig))->decode($sEncryptedId);

        if (!empty($result[0])) {
            return $result[0];
        } else {
            return 0;
        }

    }// END idDecryption

    /**
     * 生成随机密码
     * @param int $length 要生成的随机字符串长度
     * @return string
     */
    public static function make_password($length)
    {
        $arr1 = range('a','z');
        $arr2 = range('A','Z');
        $arr3 = range(0,9);

        //生成一个包含 大写英文字母, 小写英文字母, 数字的字符串
        $arr     = array_merge($arr1, $arr2, $arr3);
        $str     = $arr1[mt_rand(0,25)].$arr2[mt_rand(0,25)].$arr3[mt_rand(0,9)];
        for ($i = 3; $i < $length; $i++)
        {
            $rand = mt_rand(0, count($arr)-1);
            $str.=$arr[$rand];
        }
        $str = str_shuffle($str);
        return $str;
    }

    public static function getScheme()
    {
        $scheme = true;
        $secure = yii::$app->request->getIsSecureConnection();
        if($secure)
        {
            $scheme = 'https';
        }
        return $scheme;
    }

    /**
     * 处理数组的实体化
     * @param array $array
     * @return array
     */
    public static function HtmlEncodeToArray(array $array)
    {
        foreach ($array as &$item)
        {
            if(is_array($item))
            {
                $item = self::HtmlEncodeToArray($item);
            }else{
                $item = Html::encode($item);
            }
        }
        return $array;
    }

    /**
     * 取字符串中的数字
     * @param string $str
     * @return string
     */
    public static function findNum($str=''){
        $str = trim($str);
        if(empty($str)) {
            return '';
        }
        $result='';
        for($i=0;$i<strlen($str);$i++){
            if(is_numeric($str[$i])){
                $result.=$str[$i];
            }
        }
        return $result;
    }

    public static function _setcsvHeader($filename)
    {
        $now = gmdate("D, d M Y H:i:s");
        header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
        header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
        header("Last-Modified: {$now} GMT");
        // force download
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header("Content-type: application/vnd.ms-excel; charset=utf8");
        // disposition / encoding on response body
        header("Content-Disposition: attachment;filename={$filename}");
        header("Content-Transfer-Encoding: binary");
        //设置utf-8 + bom ，处理汉字显示的乱码
        print(chr(0xEF) . chr(0xBB) . chr(0xBF));
    }

    public static function _array2csv(&$array,$memory = '512M')
    {
        if (count($array) == 0 || !is_array($array)) {
            return null;
        }

        set_time_limit(0);//响应时间改为60秒
        ini_set('memory_limit', $memory);
        ob_start();
        $df = fopen("php://output", 'w');
        fputcsv($df, array_keys(reset($array)));
        foreach ($array as $row) {
            fputcsv($df, $row);
        }
        fclose($df);
        return ob_get_clean();
    }

}

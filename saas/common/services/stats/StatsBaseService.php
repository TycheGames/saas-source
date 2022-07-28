<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/10/15
 * Time: 15:43
 */

namespace common\services\stats;


class StatsBaseService
{
    public $db;

    public function __construct()
    {
        $this->db = \Yii::$app->db_read_1;
    }

    protected function _setcsvHeader($filename) {
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

    protected function _array2csv(&$array)
    {
        if (count($array) == 0 || !is_array($array)) {
            return null;
        }

        set_time_limit(0);//响应时间改为60秒
        ini_set('memory_limit', '512M');
        ob_start();
        $df = fopen("php://output", 'w');
        fputcsv($df, array_keys(reset($array)));
        foreach ($array as $row) {
            fputcsv($df, $row);
        }
        fclose($df);
        return ob_get_clean();
    }

    /**
     * @name 通用方法(插入并更新)批量
     * @use 用于生成一条插入sql语句
     * @param array $aData 数据
     * @param string $sTableName 表明
     * @param array $aUpdate 需要更新的字段
     */
    public function getSqlInsertOnDuplicateAll(array $aData, $sTableName ,array $fieldName, array $aUpdate = null) {
        $sSqlAction = '';
        $sSqlField  = '';
        $sSqlValueArr  = array();
        $sSqlValue  = '';
        $sSqlUpdate = '';

        $sSqlAction = "INSERT  INTO `" . $sTableName . "` ";
        foreach ($fieldName as $field) {
            $sSqlField .= "`" . addslashes($field) . "`, " ;
            if($aUpdate && in_array($field, $aUpdate)) {
                $sSqlUpdate .= "`" . addslashes($field) . "` = VALUES(`" . addslashes($field) . "`), ";
            }
        }
        foreach ($aData as $key => $value) {
            $sSqlValueItem = array();
            foreach ($fieldName as $field) {
                $sSqlValueItem[] = "'".addslashes($value[$field]) ."'";
            }
            $sSqlValueItem = preg_replace( "|, $|i", '' , $sSqlValueItem);
            $sSqlValueArr[] = '('.implode(',',$sSqlValueItem).')';
        }
        $sSqlValue = implode(',',$sSqlValueArr);

        $sSqlField = preg_replace( "|, $|i", '' , $sSqlField);
        if($sSqlUpdate) {
            $sSqlUpdate = preg_replace( "|, $|i", '' , $sSqlUpdate);
            $sSqlUpdate = " ON DUPLICATE KEY UPDATE " . $sSqlUpdate;
        }

        $sSqlQuery = $sSqlAction . '(' . $sSqlField . ') VALUES ' . $sSqlValue . ' '.$sSqlUpdate.';' ;

        return $sSqlQuery;
    }
}
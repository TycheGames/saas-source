<?php


namespace common\models\enum\verify;


class PassCode
{
    //通过码
    public static $passCode = [
        'PS'=>[
            'name'=>'normal pass',
            'child'=>[
                '001'=>['name' => 'pass the audit']
            ]
        ],
    ];

    //备注码
    public static function getRemarkCode()
    {
        $reject_list = self::$passCode;
        $reject_tmp = [];
        foreach($reject_list as $k=>$v){
            foreach($v['child'] as $key => $value){
                $reject_tmp[$k.'o'.$key] = $v['name']." / ".$value['name'];
            }
        }
        return $reject_tmp;
    }

    //check
    public static function checkCode($lineKey)
    {
        $passList = self::$passCode;
        if(!isset($lineKey[0]) || !isset($lineKey[1])){
            return false;
        }
        if(!isset($passList[$lineKey[0]]['child'][$lineKey[1]])){
            return false;
        }
        return true;
    }
}
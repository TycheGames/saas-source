<?php

namespace callcenter\service;


use callcenter\models\CollectorWorkApiOperate;

class StatisticsService
{

    public static function setWorkApiOperate($filedName,$userId){
        $date = date('Y-m-d');
        $collectorWorkApiOperate = CollectorWorkApiOperate::find()->where(['date' => $date,'user_id' => $userId])->one();
        if(!$collectorWorkApiOperate){
            $collectorWorkApiOperate = new CollectorWorkApiOperate();
            $collectorWorkApiOperate->date = $date;
            $collectorWorkApiOperate->user_id = $userId;
            $collectorWorkApiOperate->$filedName = 1;
            $collectorWorkApiOperate->save();
        }
    }
}
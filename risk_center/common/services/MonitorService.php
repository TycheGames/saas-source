<?php

namespace common\services;


use common\models\InfoDevice;
use common\models\InfoOrder;
use common\models\InfoUser;

class MonitorService
{
    /**
     * 获取按产品组合的全新单数
     * @param $startTime
     * @param $endTime
     * @param $field
     * @param $isLoan
     * @return array
     */
    public static function getAllNewCountByProductAnd($startTime,$endTime,$field,$isLoan = false){
        $result = [];
        $query = InfoOrder::find()
            ->select([
                'o.product_name',
                'u.'.$field,
                'orderCount' => 'COUNT(1)'
            ])
            ->alias('o')
            ->leftJoin(InfoUser::tableName().' u','o.order_id = u.order_id AND o.user_id = u.user_id AND o.app_name = u.app_name')
            ->where(['o.is_first' => InfoOrder::ENUM_IS_FIRST_Y,'o.is_all_first' => InfoOrder::ENUM_IS_ALL_FIRST_Y]);
        if($isLoan){
            $query->andWhere(['>=','o.loan_time',$startTime])->andWhere(['<','o.loan_time',$endTime]);
        }else{
            $query->andWhere(['>=','o.order_time',$startTime])->andWhere(['<','o.order_time',$endTime]);
        }
        $productAndCity = $query->groupBy(['o.product_name','u.'.$field])->asArray()->all();
        foreach ($productAndCity as $item){
            $result[$item['product_name']][$item[$field]] = $item['orderCount'];
        }
        return $result;
    }

    /**
     * 获取按$field的全新下单数大于等于十的
     * @param $startTime
     * @param $endTime
     * @param $field
     * @param $havingCount
     * @param $isLoan
     * @return array
     */
    public static function getAllNewCountGroupByFieldId($startTime,$endTime,$field,$havingCount,$isLoan = false){
        $arr = [
            'szlm_query_id' => 'd.szlm_query_id',
            'pan_code' => 'd.pan_code',
            'aadhaar_md5' => 'u.aadhaar_md5',
            'phone' => 'u.phone',
            'device_id' => 'd.device_id'
        ];
        $result = [];
        $query = InfoOrder::find()
            ->select([
                $arr[$field],
                'orderCount' => 'COUNT(1)'
            ])
            ->alias('o')
            ->leftJoin(InfoDevice::tableName().' d','o.order_id = d.order_id AND o.user_id = d.user_id AND o.app_name = d.app_name')
            ->leftJoin(InfoUser::tableName().' u','o.order_id = u.order_id AND o.user_id = u.user_id AND o.app_name = u.app_name')
            ->where(['o.is_first' => InfoOrder::ENUM_IS_FIRST_Y,'o.is_all_first' => InfoOrder::ENUM_IS_ALL_FIRST_Y]);
        if($isLoan){
            $query->andWhere(['>=','o.loan_time',$startTime])->andWhere(['<','o.loan_time',$endTime]);
        }else{
            $query->andWhere(['>=','o.order_time',$startTime])->andWhere(['<','o.order_time',$endTime]);
        }
        $res = $query->groupBy([$arr[$field]])->having(['>=','orderCount',$havingCount])->asArray()->all();
        foreach ($res as $item){
            $result[$item[$field]] = $item['orderCount'];
        }
        return $result;
    }


    /**
     * 获取订单按$field
     * @param $startTime
     * @param $endTime
     * @param $field
     * @param $fieldIds
     * @param $isLoan
     * @return array
     */
    public static function getAllNewOrderByFieldIds($startTime,$endTime,$field,$fieldIds,$isLoan = false){
        $arr = [
            'szlm_query_id' => 'd.szlm_query_id',
            'pan_code' => 'd.pan_code',
            'aadhaar_md5' => 'u.aadhaar_md5',
            'phone' => 'u.phone',
        ];
        if($field == 'szlm_query_id'){
            $arr['device_id'] = 'd.device_id';
        }
        $select = array_values($arr);
        $select['order_id_str'] = 'GROUP_CONCAT(o.order_id)';
        $query = InfoOrder::find()
            ->select($select)
            ->alias('o')
            ->leftJoin(InfoDevice::tableName().' d','o.order_id = d.order_id AND o.user_id = d.user_id AND o.app_name = d.app_name')
            ->leftJoin(InfoUser::tableName().' u','o.order_id = u.order_id AND o.user_id = u.user_id AND o.app_name = u.app_name')
            ->where(['o.is_first' => InfoOrder::ENUM_IS_FIRST_Y,'o.is_all_first' => InfoOrder::ENUM_IS_ALL_FIRST_Y]);
        if($isLoan){
            $query->andWhere(['>=','o.loan_time',$startTime])->andWhere(['<','o.loan_time',$endTime]);
        }else{
            $query->andWhere(['>=','o.order_time',$startTime])->andWhere(['<','o.order_time',$endTime]);
        }
        $query->andWhere([$arr[$field] => $fieldIds]);
        unset($arr[$field]);
        $group = array_values($arr);
        $result = $query->groupBy($group)->asArray()->all();
        return $result;
    }
}
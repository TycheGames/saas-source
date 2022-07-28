<?php

namespace common\services\loan_collection;

use callcenter\models\loan_collection\LoanCollectionOrder;
use callcenter\models\loan_collection\UserCompany;
use callcenter\models\loan_collection\UserSchedule;
use yii\base\Exception;
use callcenter\models\loan_collection\LoanCollection;

/**
*本功能类主要实现派发订单功能
*
*前提：每家公司设置的人员每天最大接单量不同。
*名词：最大接单量：其他团队可接收最大订单量（不包括自己团队）。
*原则：
*若待分配订单量未达到最大接单量——平均分配；
*若待分配订单量超过最大接单量——先人后己。（让其他公司每人都达到最大接单量，剩下订单平均分配给自己团队）
*
*/
class LoanCollectionDispatchService
{

    public static  function my_sort($arrays,$sort_key,$sort_order=SORT_ASC,$sort_type=SORT_NUMERIC ){
        if(is_array($arrays)){
            foreach ($arrays as $array){
                if(is_array($array)){
                    $key_arrays[] = $array[$sort_key];
                }else{
                    return false;
                }
            }
        }else{
            return false;
        }
        array_multisort($key_arrays,$sort_order,$sort_type,$arrays);
        return $arrays;
    }


    public  function rec_assoc_shuffle($array)
    {
          $ary_keys = array_keys($array);
          $ary_values = array_values($array);
          shuffle($ary_values);
          foreach($ary_keys as $key => $value) {
            if (is_array($ary_values[$key]) AND $ary_values[$key] != NULL) {
              $ary_values[$key] = $this->rec_assoc_shuffle($ary_values[$key]);
            }
            $new[$value] = $ary_values[$key];
          }
          return $new;
    }


}

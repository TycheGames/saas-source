<?php

namespace callcenter\models\order_statistics;

use Yii;
use yii\db\Exception;
use callcenter\models\loan_collection\LoanCollectionOrder;

/**
 * Class OrderStatisticsByGroup
 * @package callcenter\models\order_statistics
 * @property int $order_status
 * @property int $amount 单数
 * @property int $principal
 * @property int $group
 */
class OrderStatisticsByGroup extends \yii\db\ActiveRecord 
{
    static $connect_name = "";

    public function __construct($name = "")
    {
        static::$connect_name = $name;
    }
    public static function tableName()
    {
        return '{{%order_overview_statistics_bygroup}}';
    }
    public static function getDb()
    {
        return Yii::$app->get( !empty(static::$connect_name) ? static::$connect_name : 'db_assist');
    }


    /**
     * 统计更新
     * @param array $inputOrders 要处理的催收订单（来自催收订单表） - 删除本月并添加
     * @return bool
     */
   public static function collectionInputStatistics($inputOrders = array()){
       $transaction = self::getDb()->beginTransaction();
        try{
            if(empty($inputOrders)) return false;

            // foreach ($input_orders as $status => $each) {
                self::deleteAll("`order_status` = ".$inputOrders['status']." AND `create_at` >= ".strtotime(date('Y-m-d 0:0:0')) ." AND `create_at` <= ".strtotime(date('Y-m-d 23:59:59')));
            
                foreach ($inputOrders['groups'] as $p => $group) {
                    $item = new self();
                    $item->order_status = $inputOrders['status'];
                    $item->amount = $group['amount'];//订单数
                    $item->principal = $group['principal'];//本金
                    $item->group = $group['id'];
                    $item->merchant_id = $group['merchant_id'];
                    if(!$item->save())  throw new Exception("统计更新失败：录入分布数据表失败");
                }
            // }
            $transaction->commit();
            // return true;
        }catch(Exception $e){
            $transaction->rollBack();
            return false;
        
        }
        return true;

   }

   /**
    *昨日订单概览
    *【催收中】、【承诺还款】取当天更新的最新两条数据
    *【催收成功】则取所有数据的总和
    */
   public static function total($time =null, $sMerchantIds){
       $stautsList = [
           LoanCollectionOrder::STATUS_COLLECTION_PROGRESS,
           LoanCollectionOrder::STATUS_COLLECTION_PROMISE,
           LoanCollectionOrder::STATUS_COLLECTION_FINISH
       ];
        try{
            $db = Yii::$app->get('db_assist');
            $condition[] = 'and';
            if(!empty($time))
            {
                $y= substr($time,0,4);
                $m = substr($time,5,2);
                $d = substr($time,8,2);
                $startTime = mktime(0,0,0,$m,$d,$y);
                $endTime = mktime(23,59,59,$m,$d,$y);
                $condition[] = ['between', 'create_at', $startTime, $endTime];
                $condition[] = ['order_status' => $stautsList];
            }
            else
            {
                $condition[] = ['between', 'create_at', strtotime('today'), strtotime('tomorrow')];
                $condition[] = ['order_status' => $stautsList];
            }
            $condition[] = ['merchant_id' => $sMerchantIds];
            $arr1 = self::find()
                ->where($condition)
                ->asArray()
                ->orderBy(['create_at'=>SORT_DESC])
                ->all($db);
            $res1 = array();
            foreach ($arr1 as $key => $item) {
                $res1[$item['group']][$item['order_status']]['amount'] = $item['amount'];
                $res1[$item['group']][$item['order_status']]['principal'] = $item['principal'];
            }

            return $res1;
        }catch(Exception $e){
            throw new Exception($e->getMessage());
            
        }
   }

   public static function total_2($sMerchantIds){
       $stautsList = [
           LoanCollectionOrder::STATUS_COLLECTION_PROGRESS,
           LoanCollectionOrder::STATUS_COLLECTION_PROMISE,
           LoanCollectionOrder::STATUS_COLLECTION_FINISH
       ];
        try{
            $db = Yii::$app->get('db_assist');

            $arr2 = self::find()
                ->where([
                    'order_status' => $stautsList,
                    'merchant_id' => $sMerchantIds
                ])
                ->asArray()
                ->orderBy(['create_at'=>SORT_DESC])
                ->all($db);
//            $arr2 = self::find()->select("SUM(amount) AS amount, SUM(principal) AS principal, order_status, group")->where($condition)->groupBy('group')->asArray()->all($db);
            $res2 = array();
            foreach ($arr2 as $key => $item) {
                $res2[$item['group']][$item['order_status']]['amount'] = $item['amount'];
                $res2[$item['group']][$item['order_status']]['principal'] = $item['principal'];

            }
            return $res2;
        }catch(Exception $e){
            throw new Exception($e->getMessage());
            
        }
   }

    public function beforeSave($insert)  
    {  
        if(parent::beforeSave($insert)){  
            if($this->isNewRecord){  
                if(empty($this->create_at)) $this->create_at = time();

            }else{  
               $this->update_at = time();
            }
            return true;  
        }else{  
            return false;  
        }  
    }  



    
}

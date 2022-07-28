<?php

namespace callcenter\models\order_statistics;

use Yii;
use yii\db\Exception;
use callcenter\models\loan_collection\LoanCollectionOrder;

/**
 * Class OrderStatisticsByStatus 入催状态统计
 * @package callcenter\models\order_statistics
 * @property int $order_status
 * @property int $amount
 * @property int $principal
 * @property int $overdue_fee
 * @property int $true_overdue_fee
 *
 */
class OrderStatisticsByStatus extends \yii\db\ActiveRecord 
{
    static $connect_name = "";

    public function __construct($name = "")
    {
        static::$connect_name = $name;
    }

    public static function tableName()
    {
        return '{{%order_overview_statistics_bystatus}}';
    }
    public static function getDb()
    {
        return Yii::$app->get( !empty(static::$connect_name) ? static::$connect_name : 'db_assist');
    }
    public static function getDb_rd()
    {
        return Yii::$app->get('db_assist');
    }

    /**
     *返回最近一次更新时间
     */
    public static function last_update_time($status = ''){
        // return self::find()->select('create_at')->orderBy(['create_at'=>SORT_DESC])->limit(1)->createCommand()->getRawSql();
        $query = self::find()->select('create_at')->orderBy(['create_at'=>SORT_DESC])->limit(1);
        if(!empty($status) && array_key_exists($status, LoanCollectionOrder::$status_list))  $query->where(['order_status'=>$status]);
        return $query->scalar();
    }

    public static function last_update_time_rd($status = ''){
        // return self::find()->select('create_at')->orderBy(['create_at'=>SORT_DESC])->limit(1)->createCommand()->getRawSql();
        $query = self::find()->select('create_at')->orderBy(['create_at'=>SORT_DESC])->limit(1);
        if(!empty($status) && array_key_exists($status, LoanCollectionOrder::$status_list))  $query->where(['order_status'=>$status]);
        return $query->scalar(self::getDb_rd());
    }


    /**
     * 添加记录
     * @param array $inputOrders
     * @return bool
     */
   public static function collectionInputStatistics($inputOrders = array()){
       $transaction= self::getDb()->beginTransaction();//创建事务
        try{
            if(empty($inputOrders)) return false;
            foreach ($inputOrders as $key => $each) {
                $item = new self();
                $item->order_status = $each['status'];
                $item->amount = $each['amount'];//订单数
                $item->principal = $each['principal'];//本金
                $item->overdue_fee = $each['overdue_fee'];//
                $item->true_overdue_fee = $each['true_overdue_fee'];//
                $item->merchant_id = $each['merchant_id'];
                if(!$item->save())  throw new Exception("入催统计更新失败：录入数据表失败，录入内容：".json_encode($inputOrders));
            }
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
    *【催收中】、【承诺还款】、【催收成功】取更新的最新数据
    */
   public static function total($time = null, $sMerchantIds){
        try{
            $db = Yii::$app->get('db_assist');
            if(!empty($time))
            {
                $y= substr($time,0,4);
                $m = substr($time,5,2);
                $d = substr($time,8,2);
                $startTime = mktime(0,0,0,$m,$d,$y);
                $endTime = mktime(23,59,59,$m,$d,$y);
                if($endTime<time())
                {
                    $endTime += 3600;
                }
                $arr1 = self::find()
                    ->where([
                        'order_status' => LoanCollectionOrder::STATUS_COLLECTION_PROGRESS,
                        'merchant_id'  => $sMerchantIds,
                    ])
                    ->andFilterWhere(['between', 'create_at', $startTime, $endTime])
                    ->asArray()
                    ->indexBy('order_status')
                    ->orderBy(['create_at' => SORT_DESC])
                    ->limit(1)
                    ->all($db);
                $arr2 = self::find()
                    ->where([
                        'order_status' => LoanCollectionOrder::STATUS_COLLECTION_PROMISE,
                        'merchant_id'  => $sMerchantIds,
                    ])
                    ->andFilterWhere(['between', 'create_at', $startTime, $endTime])
                    ->asArray()
                    ->indexBy('order_status')
                    ->orderBy(['create_at' => SORT_DESC])->limit(1)->all($db);
                $arr3 = self::find()
                    ->where([
                        'order_status' => LoanCollectionOrder::STATUS_COLLECTION_FINISH,
                        'merchant_id'  => $sMerchantIds,
                    ])
                    ->andFilterWhere(['between', 'create_at', $startTime, $endTime])
                    ->asArray()
                    ->indexBy('order_status')
                    ->orderBy(['create_at' => SORT_DESC])
                    ->limit(1)
                    ->all($db);
            }
            else
            {
                $arr1 = self::find()
                    ->where([
                        'order_status' => LoanCollectionOrder::STATUS_COLLECTION_PROGRESS,
                        'merchant_id'  => $sMerchantIds,
                    ])
                    ->asArray()
                    ->indexBy('order_status')
                    ->orderBy(['create_at'=>SORT_DESC])
                    ->limit(1)
                    ->all($db);

                $arr2 = self::find()
                    ->where([
                        'order_status' => LoanCollectionOrder::STATUS_COLLECTION_PROMISE,
                        'merchant_id'  => $sMerchantIds,
                    ])
                    ->asArray()
                    ->indexBy('order_status')
                    ->orderBy(['create_at'=>SORT_DESC])
                    ->limit(1)
                    ->all($db);

                $arr3 = self::find()
                    ->where([
                        'order_status' => LoanCollectionOrder::STATUS_COLLECTION_FINISH,
                        'merchant_id'  => $sMerchantIds,
                    ])
                    ->asArray()
                    ->indexBy('order_status')
                    ->orderBy(['create_at'=>SORT_DESC])
                    ->limit(1)
                    ->all($db);
            }
            $arr = ($arr1 + $arr2 + $arr3);
            return $arr;
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

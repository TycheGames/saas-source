<?php

namespace callcenter\models\order_statistics;

use Yii;
use yii\db\Exception;


class OrderStatisticsByDay extends \yii\db\ActiveRecord 
{

    static $connect_name = "";

    public function __construct($name = "")
    {
        static::$connect_name = $name;
    }

    public static function tableName()
    {
        return '{{%order_overview_statistics_byday}}';
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
    *统计更新1(入催部分)
    */
   public static function collectionInputStatistics($input_orders = array()){
        try{
            if(empty($input_orders)) return false;
            if(!is_integer($input_orders['create_at'])){
              $input_orders['create_at'] = strtotime($input_orders['create_at']);
            }
            $start = strtotime(date('Y-m-d 0:0:0', $input_orders['create_at']));
            $end = strtotime(date('Y-m-d 23:59:59', $input_orders['create_at']));
           
            $record = self::find()
                ->where(['>=', 'create_at', $start])
                ->andWhere(['<', 'create_at', $end])
                ->orderBy(['create_at' => SORT_DESC])
                ->one();
             if(empty($record)){
                $record = new self();
            }else{
                unset($input_orders['create_at']);//防止覆盖最初信息
            }
            foreach ($input_orders as $key => $value) {
                $record->$key = $value;
            }
            if(!$record->save()){
                throw new Exception("更新入催统计记录失败, function:".__FUNCTION__);
            }
            
            
        }catch(Exception $e){
            Yii::error($e->getMessage(), 'collectionInputStatistics');
            throw new Exception('');
        }
        return true;

   }

   /**
    *统计更新2(还款部分)
    */
   public static function repay_records($input_orders = array()){
        try{
            if(empty($input_orders)) return false;
            $startTime = strtotime(date('Y-m-d 00:00:00', $input_orders['create_at']));
            $endTime = strtotime(date('Y-m-d 23:59:59', $input_orders['create_at']));
            $record = self::find()
                ->where(['between', 'create_at', $startTime, $endTime])
                ->one();
            if(empty($record))  $record = new self();
            $record->repay_amount = $input_orders['repay_amount'];
            $record->repay_principal = $input_orders['repay_principal'];
            $record->repay_late_fee = $input_orders['repay_late_fee'];
            if(!$record->save())    throw new Exception("更新还款统计记录失败, function:".__FUNCTION__);
            
            
        }catch(Exception $e){
            throw new Exception($e->getMessage());
            return false;
        
        }
        return true;
   }

   

   

   /**
    *返回催收统计信息
    * @param int $start
    * @param int $end
    * @param array $sMerchantIds
    * @return array
    */
    public static function lists($start = 0, $end = 0, $sMerchantIds = [])
    {
        try {
            $db = self::getDb();
            $start = empty($start) ? strtotime('today') : $start;
            $end = empty($end) ? strtotime('tomorrow') : $end;

            return self::find()
                ->where(['merchant_id' => $sMerchantIds])
                ->andWhere(['between', 'create_at', $start, $end])
                ->asArray()
                ->orderBy(['create_at' => SORT_DESC])
                ->all($db);

        } catch (Exception $e) {
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

    public static function GetCount($time,$sub_from=1){
        $info= self::find()->where(['stage_type'=>0,'create_at'=>$time,'sub_from'=>$sub_from])->one(Yii::$app->get('db_assist'));
        if($info){
            return $info->new_amount;
        }
        return '';
    }

    
}

<?php

namespace callcenter\models\order_statistics;

use callcenter\models\loan_collection\LoanCollectionOrder;
use Yii;
use yii\db\Exception;

class OrderStatisticsByRate extends \yii\db\ActiveRecord 
{
    const REPLACE = 0;//覆盖数据
    const APPEND = 1;//累加数据
    const IS_BY_STAGE = 1; //是分期
    const IS_NOT_STAGE = 0;  //  不是分期
    static $connect_name = "";

    public function __construct($name = "")
    {
        static::$connect_name = $name;
    }
    public static function tableName()
    {
        return '{{%order_overview_statistics_byrate}}';
    }
    public static function getDb()
    {
        return Yii::$app->get( !empty(static::$connect_name) ? static::$connect_name : 'db_assist');
    }

    //要统计催回率的天数：
    public static $rate_days = array(1,2,3,4,5,6,7,"8-10","11-30","16-20","20-21","31-60","61-90","91-999");


    /**
     * 统计更新3(催回率部分)
     * @param array $input_orders
     * @param int $type
     * @return bool
     */
   public static function rateAmount($input_orders = array(), $type = self::REPLACE){
        try{
            if(empty($input_orders)){
                return false;
            }
            $record = self::find()
                ->where(['>=','create_at',strtotime(date('Y-m-d 0:0:0', $input_orders['create_at']))])
                ->andWhere(['<','create_at',strtotime(date('Y-m-d 23:59:59', $input_orders['create_at']))])
                ->one();
            if(empty($record)){
                $record = new self();
            }else{
                unset($input_orders['create_at']);//防止覆盖最初信息
            }
            
            if($type == self::APPEND){
                foreach ($input_orders as $key => $value) {
                    $record->$key = ($value + $record->$key);
                }

            }else{
                foreach ($input_orders as $key => $value) {
                    $record->$key = $value;
                }
            }
            $record->save();
            
        }catch(\Exception $e){
            return false;
        
        }
        return true;
   }

   /**
    *返回指定逾期天数的指定列信息
    *@param int $day 逾期天数（相对于今天来说）
    *@return array time:记录时间， info:指定列信息
    */
   public static function overdueDay_collection($day = 0, $column, $offset ='' ,$sub_from=1){
       $db = Yii::$app->get('db_assist_read');
        if(empty($offset))  $offset = time();
        $collection_day =  $offset + $day * 86400;
        $collection_day_start = strtotime(date("Y-m-d", $collection_day));
        $collection_day_end = strtotime(date("Y-m-d 23:59:59", $collection_day));
        $principal = self::find()->select($column)
            ->where("`create_at` >= ".$collection_day_start." AND `create_at` <= ".$collection_day_end)
            ->andWhere(['stage_type'=>0,'sub_from'=>$sub_from])
            ->scalar($db);
        return array('time'=> $collection_day, 'info'=>$principal);
   }

   

   /**
    *返回催收统计信息
    *注意：其中，应还订单数为前一天数据
    *
    */
    public static function lists($start=0, $end=0,$sub_from=1){
        try{

            $db = Yii::$app->get('db_assist');
            $condition = 'stage_type=0 and sub_from='.$sub_from;
            if(empty($start)) $start=strtotime(date('Y-m-d 0:0:0'));
            if(empty($end)) $end=strtotime(date('Y-m-d 23:59:59'));
            $condition .= "  and `create_at` >= ".$start." AND `create_at` < ".$end;
            $lists = self::find()->select('*')->where($condition)->asArray()->orderBy(['create_at'=>SORT_DESC])->all($db);
            if(!empty($lists)){
                foreach ($lists as $key => $item) {
                   $condition2 = " stage_type=0 and `create_at` >= ".strtotime(date('Y-m-d 0:0:0', ($item['create_at'] - 24*3600)))." AND `create_at` < ".strtotime(date('Y-m-d 0:0:0', $item['create_at'])).' and sub_from='.$sub_from;
                   $lists[$key]['deadline_amount'] = self::find()->select('deadline_amount')->where($condition2)->orderBy(['create_at'=>SORT_DESC])->scalar($db);//使用前一天的应还总额，用于计算入催率
                }
            }
            return $lists;
            
        }catch(Exception $e){
            throw new Exception($e->getMessage());
            
        }
   }

    /**
     * 返回催收统计信息
     * 注意：其中，应还订单数为前一天数据
     * @param int $start
     * @param int $end
     * @param string $db_assist
     * @param int $sub_from
     * @param array $sMerchantIds
     * @return array
     */
    public static function lists_new($start = 0, $end = 0, $db_assist = 'db_assist', $sub_from = 1, $sMerchantIds = [])
    {
        $db = Yii::$app->get($db_assist);
        if(empty($start)) $start=strtotime(date('Y-m-d 0:0:0'));
        if(empty($end)) $end=strtotime(date('Y-m-d 23:59:59'));
        $lists = self::find()
            ->select('*')
            ->where([
                'sub_from' => $sub_from,
                'merchant_id' => $sMerchantIds
            ])
            ->andWhere(['between', 'create_at', $start, $end])
            ->asArray()
            ->orderBy(['create_at'=>SORT_DESC])
            ->all($db);
        if(!empty($lists)){
            foreach ($lists as $key => $item) {
                $start2 = strtotime(date('Y-m-d 0:0:0', ($item['create_at'] - 24*3600)));
                $end2 = strtotime(date('Y-m-d 0:0:0', $item['create_at']));
                $lists[$key]['deadline_amount'] = self::find()
                    ->select('deadline_amount')
                    ->where(['sub_from' => $sub_from])
                    ->andWhere(['between', 'create_at', $start2, $end2])
                    ->orderBy(['create_at'=>SORT_ASC])
                    ->scalar($db);//使用前一天的应还总额，用于计算入催率
            }
        }
        return $lists;
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

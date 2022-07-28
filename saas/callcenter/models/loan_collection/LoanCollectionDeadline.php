<?php
namespace callcenter\models\loan_collection;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use common\models\loan\UserSchedule;
use yii\base\Exception;


/**
 * LoanCollectionDeadline model
 *
 */
class LoanCollectionDeadline extends ActiveRecord 
{
   
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%loan_collection_deadline}}';
    }
    public static function getDb()
    {
        return Yii::$app->get('db_assist');
    }
    public static function getDb_rd()
    {
        return Yii::$app->get('db_assist');
    }
    
    public static function lists($start=0, $end=0,$stage_type =1,$sub_from=1){
        try{
            if(empty($start)) $start=strtotime(date('Y-m-d 0:0:0'));
            if(empty($end)) $end=strtotime(date('Y-m-d 23:59:59'));
            $condition = " stage_type=".$stage_type." and `created_at` >= ".$start." AND `created_at` < ".$end.' and sub_from='.$sub_from;
            return self::find()->where($condition)->asArray()->orderBy(['deadline_time'=>SORT_ASC])->all(Yii::$app->get('db_assist'));
            
        }catch(Exception $e){
            throw new Exception($e->getMessage());
            
        }
    }

    public static function new_record($record = array('deadline_time'=>0, 'created_at'=>0)){

        $item = self::find()
            ->where(['created_at'=>$record['created_at'], 'deadline_time'=>$record['deadline_time'],'stage_type'=>$record['stage_type'],'sub_from'=>$record['sub_from']])->one();
        if(empty($item))    $item = new self();
        foreach ($record as $key => $value) {
            $item->$key = $value;
        }
        return $item->save();
    }
    
    public function beforeSave($insert)  
    { 
        if(parent::beforeSave($insert)){  
            if($this->isNewRecord){  
                if(empty($this->created_at)) $this->created_at = time();
     
            }
            $this->updated_at = time();
            return true;  
        }else{  
            return false;  
        }  
    }

}

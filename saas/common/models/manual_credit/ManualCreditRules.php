<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/10/4
 * Time: 14:46
 */
namespace common\models\manual_credit;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * Class ManualCreditModule
 * @property $id
 * @property $back_code
 * @property $back_name
 * @property $module_id
 * @property $status
 * @property $questions
 * @property $created_at
 * @property $updated_at
 */

class ManualCreditRules extends ActiveRecord
{
    const STATUS_NO = 1;
    const STATUS_OFF = 0;

    const TYPE_SINGLE = 1;
    const TYPE_MULTI = 2;

    const QUESTION_PASS = 1;
    const QUESTION_NOT_PASS = 2;

    public static $status_list = [
        self::STATUS_OFF => 'close',
        self::STATUS_NO => 'open'
    ];

    public static $type_list = [
        self::TYPE_SINGLE => 'SINGLE',
        self::TYPE_MULTI => 'MULTI',
    ];

    public static $question_pass_list = [
        self::QUESTION_PASS => 'pass',
        self::QUESTION_NOT_PASS => 'not pass',
    ];


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%manual_credit_rules}}';
    }


    public static function getDb()
    {
        return Yii::$app->get('db');
    }

    public function behaviors() {
        return [
            TimestampBehavior::class
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['type','rule_name','back_code','type_id','status'], 'required'],
            [['questions','pass_que_count','reject_text'],'safe'],
            ['status', 'in', 'range'=> array_keys(self::$status_list),'message'=> 'status error']
        ];
    }

    public static function getAllManualRules($moduleNames){
        $list = ManualCreditRules::find()
            ->select('A.*,B.module_id,B.type_name,C.head_code,C.head_name')
            ->from(ManualCreditRules::tableName(). ' A')
            ->leftJoin(ManualCreditType::tableName() . ' B','B.id = A.type_id')
            ->leftJoin(ManualCreditModule::tableName() . ' C','C.id = B.module_id')
            ->where(['B.status' => ManualCreditType::STATUS_NO,'C.status' => ManualCreditModule::STATUS_NO])
            ->andWhere(['C.head_code' => $moduleNames])
            ->asArray()
            ->all();
        $data = [];
        foreach ($list as $item){
            $data[$item['id']] = $item;
        }
        return $data;
    }


    public static function conversionRules($list){
        $arr = [];
        $mouled_name = [];
        $type_name = [];
        $rule_names = [];
        $pass_question_count = [];
        foreach ($list as $item){
            if($item['type'] == ManualCreditRules::TYPE_MULTI){
                $pass_question_count[$item['id']] = $item['pass_que_count'];
            }
            $mouled_name[$item['module_id']] = $item['head_code'];
            $type_name[$item['type_id']] = $item['type_name'];
            $rule_names[$item['id']] = $item['rule_name'];
            $arr[$item['module_id']][$item['type_id']][$item['id']] = $item;
        }
        return ['data' => $arr,'rule_names' => $rule_names, 'module_name' => $mouled_name, 'type_name' => $type_name,'pass_question_count' => $pass_question_count];
    }

    public static function getRemarkCode($list){
        $res = [];
        foreach ($list as $item){
            $res[$item['id']] = $item['head_code'].'/'.$item['type_name'].'/'.$item['reject_text'];
        }
        return $res;
    }

    public static function passQuestionCheck($question,$list){
        //提交的电核问题是否缺漏
        ksort($question);
        ksort($list);
        if(array_keys($question) != array_keys($list)){
            return ['code' => 1, 'msg' => 'please select the situation of the problem'];
        }
        //提交的电核问题情况
        foreach ($question as $k => $que){
            if($list[$k]['type'] == ManualCreditRules::TYPE_MULTI){
                if(!is_array($que)){
                    return ['code' => 1, 'msg' => 'questions and type is error'];
                }
                $count = array_count_values($que);
                if($count[1] < $list[$k]['pass_que_count']){
                    return ['code' => 1, 'msg' => $list[$k]['type_name'].' success to pass mast greater than '.$list[$k]['pass_que_count']];
                }
            }elseif($list[$k]['type'] == ManualCreditRules::TYPE_SINGLE){
                if($que != 1){
                    return ['code' => 1, 'msg' => 'question:'.$list[$k]['rule_name'].' mast pass'];
                }
            }else{
                return ['code' => 1, 'msg' => 'type error'];
            }
        }
        return ['code' => 0];
    }

    public static function rejectQuestionCheck($code,$question,$list){
        if(!isset($list[$code])){
            return ['code' => 1, 'msg' => 'rule error'];
        }
        $rejectRule = $list[$code];
        if($rejectRule['type'] == ManualCreditRules::TYPE_SINGLE){
            if(!isset($question[$code]) || $question[$code] != 2){
                return ['code' => 1, 'msg' => $rejectRule['rule_name'] . ' need not pass'];
            }
        }elseif ($rejectRule['type'] == ManualCreditRules::TYPE_MULTI){
            if(!isset($question[$code])){
                return ['code' => 1, 'msg' => 'questions need checked in '.$rejectRule['type_name']];
            }
            $count = array_count_values($question[$code]);
            if(isset($count[1]) && $count[1] >= $rejectRule['pass_que_count']){
                return ['code' => 1, 'msg' => 'success to pass questions mast greater than '.$rejectRule['pass_que_count']];
            }
            if(!isset($count[2]) || $count[2] < count(json_decode($rejectRule['questions'],true)) - $rejectRule['pass_que_count']){
                return ['code' => 1, 'msg' => 'fail to no pass questions mast greater than '.$rejectRule['pass_que_count']];
            }
        }else{
            return ['code' => 1, 'msg' => 'type is error'];
        }
        return ['code' => 0, 'rule' => $rejectRule];
    }
}
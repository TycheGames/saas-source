<?php

namespace common\models\user;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * Class UserActiveTime
 * @package common\models\user
 *
 * @property int id
 * @property int user_id
 * @property int last_active_time 最后活跃时间
 * @property string last_pay_time 最后支付时间
 * @property int max_money 最大收款金额
 * @property string last_money_sms_time 最后接到收款短信时间
 * @property string level_change_call_success_time 催收账龄更变发送语音接通时间
 * @property string created_at 创建时间
 * @property string updated_at 更新时间

 */
class UserActiveTime extends ActiveRecord
{


    public static function tableName()
    {
        return '{{%user_active_time}}';
    }

    /**
     * @return object|\yii\db\Connection|null
     * @throws \yii\base\InvalidConfigException
     */
    public static function getDb()
    {
        return Yii::$app->get('db');
    }

    public static function getDbName(){
        if(preg_match('/dbname=(\w+)/', Yii::$app->db->dsn, $db) && !empty($db[1])){
            return $db[1];
        }
        return null;
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    const GREEN_COLOR = 5; //催收账龄更变发送语音接通时间
    const BLUE_COLOR = 1; //最后活跃时间
    const RED_COLOR = 2;  //最后支付时间
    const BLACK_COLOR = 3; //最后接到收款短信
    const DAZZLING_COLOR = 4;  //最后接到收款短信

    public static $colorMap = [
        self::GREEN_COLOR => ['green','phone normal'],
        self::BLUE_COLOR => ['blue','willing to repay'],
        self::RED_COLOR =>  ['red','strong willing to repay'],
        self::BLACK_COLOR => ['black','Enough Balance, Just Call'],
        self::DAZZLING_COLOR => ['dazzling','Enough Balance, Endless Call'],
    ];


    public static function colorBlinkerCondition($willingBlinker,$activeTableAlias = '',$paymentTableAlias = '',$attachAnd = true){
        $condition = '';
        $before3day = time() - 86400 * 3;
        $before5day = time() - 86400 * 5;
        $before30day = time() - 86400 * 30;
        $blinkerArr = [
            self::GREEN_COLOR => " ({$activeTableAlias}level_change_call_success_time > {$before30day}) ",
            self::BLUE_COLOR => " ({$activeTableAlias}last_active_time > {$before3day}) ",
            self::RED_COLOR =>  " ({$activeTableAlias}last_pay_time > {$before3day}) ",
            self::BLACK_COLOR => " ({$activeTableAlias}last_money_sms_time > {$before5day} and {$activeTableAlias}last_money_sms_time < {$before5day} and {$activeTableAlias}max_money + {$paymentTableAlias}true_total_money >= {$paymentTableAlias}principal + {$paymentTableAlias}interests) " ,
            self::DAZZLING_COLOR => " ({$activeTableAlias}last_money_sms_time > {$before3day} and {$activeTableAlias}max_money + {$paymentTableAlias}true_total_money >= {$paymentTableAlias}principal + {$paymentTableAlias}interests) " ,
        ];

        $willingBlinkerArr = [];
        foreach ($willingBlinker as $v) {
            if(isset($blinkerArr[$v])){
                $willingBlinkerArr[] = $blinkerArr[$v];
            }
        }
        $willingBlinkerStr = implode (" OR ",$willingBlinkerArr);
        if ($willingBlinkerStr){
            if($attachAnd){
                $condition = " and (".$willingBlinkerStr.")";
            }else{
                $condition = "(".$willingBlinkerStr.")";
            }
        }

        return $condition;
    }

    public static function colorBlinkerConditionNew($willingBlinker,$activeTableAlias = '',$paymentTableAlias = '',$attachAnd = true){
        $before3day = time() - 86400 * 3;
        $before5day = time() - 86400 * 5;
        $before30day = time() - 86400 * 30;
        $blinkerArr = [
            self::GREEN_COLOR => ['>', "{$activeTableAlias}level_change_call_success_time", $before30day],
            self::BLUE_COLOR => ['>', "{$activeTableAlias}last_active_time", $before3day],
            self::RED_COLOR =>  ['>', "{$activeTableAlias}last_pay_time", $before3day],
            self::BLACK_COLOR => [
                'AND',
                ['>', "{$activeTableAlias}last_money_sms_time", $before5day],
                ['<', "{$activeTableAlias}last_money_sms_time", $before5day],
                "{$activeTableAlias}`max_money` + {$paymentTableAlias}`true_total_money` >= {$paymentTableAlias}`principal` + {$paymentTableAlias}`interests`"
            ] ,
            self::DAZZLING_COLOR => [
                'AND',
                ['>', "{$activeTableAlias}last_money_sms_time", $before3day],
                "{$activeTableAlias}`max_money` + {$paymentTableAlias}`true_total_money`>= {$paymentTableAlias}`principal` + {$paymentTableAlias}`interests`",
            ] ,
        ];

        $willingBlinkerArr = ['or'];
        foreach ($willingBlinker as $v) {
            if(isset($blinkerArr[$v])){
                $willingBlinkerArr[] = $blinkerArr[$v];
            }
        }


        return $willingBlinkerArr;
    }

    public static function colorBlinkerShow($params){
        $colorArr = [];
        $before3day = time() - 86400 * 3;
        $before5day = time() - 86400 * 5;
        $before30day = time() - 86400 * 30;
        if(isset($params['principal']) && isset($params['interests']) && isset($params['true_total_money'])){
            $money = max($params['principal'] + $params['interests'] - $params['true_total_money'],0);
        }else{
            $money = 0;
        }
        if(isset($params['level_change_call_success_time']) && $params['level_change_call_success_time'] > $before30day){
            $colorArr[self::GREEN_COLOR] = self::$colorMap[self::GREEN_COLOR];
        }
        if(isset($params['last_active_time']) && $params['last_active_time'] > $before3day){
            $colorArr[self::BLUE_COLOR] = self::$colorMap[self::BLUE_COLOR];
        }
        if(isset($params['last_pay_time']) && $params['last_pay_time'] > $before3day){
            $colorArr[self::RED_COLOR] = self::$colorMap[self::RED_COLOR];
        }
        if(isset($params['last_money_sms_time']) && isset($params['max_money']) && $params['last_money_sms_time'] > $before5day && $params['last_money_sms_time'] < $before3day && $params['max_money'] >= $money){
            $colorArr[self::BLACK_COLOR] = self::$colorMap[self::BLACK_COLOR];
        }
        if(isset($params['last_money_sms_time']) && isset($params['max_money']) && $params['last_money_sms_time'] > $before3day && $params['max_money'] >= $money){
            $colorArr[self::DAZZLING_COLOR] = self::$colorMap[self::DAZZLING_COLOR];
        }
        return $colorArr;
    }

}
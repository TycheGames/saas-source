<?php

namespace common\models;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;


/**
 * Class BankCardAutoCheckLog
 *  @property int $id
 *  @property int $user_id
 *  @property int $order_id
 *  @property int $result 1-通过 2-跳过 3-人审
 *  @property int $created_at
 *  @property int $updated_at
 *
 */
class BankCardAutoCheckLog extends ActiveRecord
{

    const RESULT_PASS = 1;
    const RESULT_SKIP = 2;
    const RESULT_MANUAL = 3;


    public static function tableName()
    {
        return '{{%bank_card_auto_check_log}}';
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


    public static function checkCanSkip() : bool
    {
        $time1 = time() - 3600; //近一小时内的成功率
        $threshold1 = 0.7; //通过率阈值

        $time2 = time() - 600; //近10分钟内跳过单数
        $threshold2 = 10; //可跳过审核的订单数
        //一小时内银行卡机审的单数
        $total = self::find()->where(['>=', 'created_at', $time1])->count();

        if(0 == $total)
        {
            return false;
        }
        $passCount = self::find()->where(['>=', 'created_at', $time1])->andWhere(['result' => self::RESULT_PASS])->count();
        $passRate = $passCount / $total; //计算通过率

        if($passRate < $threshold1)
        {
            return false;
        }

        $skipCount = self::find()->where(['>=', 'created_at', $time2])->andWhere(['result' => self::RESULT_SKIP])->count();
        if($skipCount >= $threshold2)
        {
            return false;
        }

        return true;
    }
}

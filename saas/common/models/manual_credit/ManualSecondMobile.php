<?php

namespace common\models\manual_credit;
use common\models\order\UserLoanOrder;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * Class ManualSecondMobile
 * @property int $id
 * @property int $order_id
 * @property int $user_id
 * @property int $mobile
 * @property int $created_at
 * @property int $updated_at
 */

class ManualSecondMobile extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%manual_second_mobile}}';
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

    public static function updateSecondMobile(UserLoanOrder $order,$secondMobile){
        $manualSecondMobile = self::find()->where(['order_id' => $order->id])->one();
        if(is_null($manualSecondMobile)){
            $manualSecondMobile = new self();
            $manualSecondMobile->order_id = $order->id;
            $manualSecondMobile->user_id = $order->user_id;
        }
        $manualSecondMobile->mobile = $secondMobile;
        $manualSecondMobile->save();
    }
}
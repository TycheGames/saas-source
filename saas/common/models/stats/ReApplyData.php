<?php

namespace common\models\stats;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
/**
 * This is the model class for table "{{%statistics_loan_copy}}".
 */
class ReApplyData extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%re_apply_data}}';
    }

    public static function getDb()
    {
        return Yii::$app->get('db_stats');
    }

    /**
     * @inheritdoc
     */
    public function behaviors(){
        return [
            TimestampBehavior::class,
        ];
    }

}
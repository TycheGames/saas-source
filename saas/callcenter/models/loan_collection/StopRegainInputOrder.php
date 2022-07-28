<?php

namespace callcenter\models\loan_collection;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * CollectionCallRecords model
 *
 * @property int $id
 * @property int $collection_order_id
 * @property int $loan_order_id
 * @property int $loan_repayment_id
 * @property int $operator_id
 * @property int $status
 * @property int $next_input_time
 * @property int $created_at
 * @property int $updated_at
 */
class StopRegainInputOrder extends ActiveRecord
{

    const STATUS_UNAVAILABLE = 0;
    const STATUS_INVALID = 1;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%stop_regain_input_order}}';
    }

    public static function getDb()
    {
        return Yii::$app->get('db_assist');
    }

    public static function getDb_rd()
    {
        return Yii::$app->get('db_assist_read');
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }
}
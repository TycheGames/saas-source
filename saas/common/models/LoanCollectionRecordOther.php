<?php

namespace common\models;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * Class LoanCollectionRecordOther
 * @package common\models
 *
 * @property string $app_name
 * @property int $order_id
 * @property int $user_id
 * @property int $request_id
 * @property string $pan_code
 * @property int $contact_type
 * @property int $order_level
 * @property int $operate_type
 * @property int $operate_at
 * @property int $promise_repayment_time
 * @property int $risk_control
 * @property int $is_connect
 * @property int $created_at
 * @property int $updated_at
 */
class LoanCollectionRecordOther extends ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%loan_collection_record}}';
    }

    /**
     * @return object|\yii\db\Connection|null
     * @throws \yii\base\InvalidConfigException
     */
    public static function getDb()
    {
        return Yii::$app->get('db_risk');
    }

    public function behaviors() {
        return [
            TimestampBehavior::class
        ];
    }

    public function rules()
    {
        return [
            [['order_id', 'app_name', 'user_id', 'request_id', 'pan_code', 'contact_type',
              'order_level', 'operate_type', 'operate_at', 'promise_repayment_time',
              'risk_control', 'is_connect'], 'required'],
            [['app_name', 'request_id'], 'unique', 'targetAttribute' => ['app_name', 'request_id']]
        ];
    }

}

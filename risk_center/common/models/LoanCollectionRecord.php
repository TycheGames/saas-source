<?php

namespace common\models;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * Class LoanCollectionRecord
 * @package common\models
 *
 * @property string $app_name
 * @property int $order_id
 * @property int $user_id
 * @property int $request_id
 * @property string $pan_code
 * @property int $contact_type  联系人类型：0：自己，1：亲人，2：其他
 * @property int $order_level
 * @property int $operate_type  1：电话，2：短信
 * @property int $operate_at    催收时间
 * @property int $promise_repayment_time
 * @property int $risk_control
 * @property int $is_connect    是否接通：1：接通，2：未接通
 * @property int $created_at
 * @property int $updated_at
 */
class LoanCollectionRecord extends ActiveRecord
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
        return Yii::$app->get('db');
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

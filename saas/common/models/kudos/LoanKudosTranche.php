<?php

namespace common\models\kudos;

use common\models\pay\PayAccountSetting;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%loan_kudos_tranche}}".
 *
 * @property int $id
 * @property string $kudos_tranche_id
 * @property int $kudos_status 0 初始化 1 已推送
 * @property string $date 日期
 * @property int $merchant_id
 * @property int $pay_account_id
 * @property int $created_at
 * @property int $updated_at
 *
 * 关联表
 * @property array $kudosOrders
 * @property PayAccountSetting $payAccountSetting

 */
class LoanKudosTranche extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%loan_kudos_tranche}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['kudos_status', 'created_at', 'updated_at', 'merchant_id', 'pay_account_id'], 'integer'],
            [['kudos_tranche_id'], 'string', 'max' => 128],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'               => 'ID',
            'kudos_tranche_id' => 'Kudos Tranche ID',
            'kudos_status'     => 'Kudos Status',
            'merchant_id'     => 'merchant_id',
            'pay_account_id'     => 'pay_account_id',
            'created_at'       => 'Created At',
            'updated_at'       => 'Updated At',
        ];
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    public function getKudosOrders()
    {
        return $this->hasMany(LoanKudosOrder::class, ['kudos_tranche_id' => 'id', 'merchant_id' => 'merchant_id', 'pay_account_id' => 'pay_account_id']);
    }

    public function getPayAccountSetting()
    {
        return $this->hasOne(PayAccountSetting::class, ['id' => 'pay_account_id']);
    }
}

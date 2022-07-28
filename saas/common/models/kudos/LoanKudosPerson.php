<?php

namespace common\models\kudos;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%loan_kudos_person}}".
 *
 * @property int $id
 * @property int $user_id
 * @property int $order_id
 * @property string $kudos_va_acc
 * @property string $kudos_ifsc
 * @property string $kudos_bankname
 * @property string $kudos_account_status
 * @property string $kudos_borrower_id
 * @property string $partner_borrower_id
 * @property string $request_data
 * @property int $pay_account_id
 * @property int $merchant_id
 * @property int $created_at
 * @property int $updated_at
 */
class LoanKudosPerson extends ActiveRecord
{

    const ACCOUNT_STATUS_PENDING = 'PENDING';
    const ACCOUNT_STATUS_ACTIVE = 'ACTIVE';
    const ACCOUNT_STATUS_CLOSED = 'CLOSED';

    public static $account_status_map = [
        self::ACCOUNT_STATUS_PENDING => 'PENDING',
        self::ACCOUNT_STATUS_ACTIVE => 'ACTIVE',
        self::ACCOUNT_STATUS_CLOSED => 'CLOSED',
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%loan_kudos_person}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'created_at', 'updated_at', 'pay_account_id', 'merchant_id'], 'integer'],
            [['kudos_va_acc', 'kudos_borrower_id', 'partner_borrower_id'], 'string', 'max' => 128],
            [['kudos_ifsc', 'kudos_bankname', 'kudos_account_status'], 'string', 'max' => 64],
            [['request_data'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'                   => 'ID',
            'user_id'              => 'User ID',
            'kudos_va_acc'         => 'Kudos Va Acc',
            'pay_account_id'         => 'pay_account_id',
            'merchant_id'         => 'merchant_id',
            'kudos_ifsc'           => 'Kudos Ifsc',
            'kudos_bankname'       => 'Kudos Bankname',
            'kudos_account_status' => 'Kudos Account Status',
            'kudos_borrower_id'    => 'Kudos Borrower ID',
            'partner_borrower_id'  => 'Partner Borrower ID',
            'request_data'  => 'Request Data',
            'created_at'           => 'Created At',
            'updated_at'           => 'Updated At',
        ];
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }
}

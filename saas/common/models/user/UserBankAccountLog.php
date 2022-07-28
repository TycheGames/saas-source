<?php

namespace common\models\user;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%user_bank_account_log}}".
 *
 * @property int $id
 * @property int $account_id
 * @property int $source_type 1:元丁 2:AadhaarApi 3:历史 4:Accuauth
 * @property int $user_id
 * @property string $name 开户姓名
 * @property string $report_account_name 验证报告中的开户名
 * @property string $account 账户
 * @property string $ifsc ifsc code
 * @property string $bank_name 银行名
 * @property int $status 认证状态 0:待认证 1:认证成功 2:认证中 -1:认证失败
 * @property string $data
 * @property string $remark
 * @property int $created_at
 * @property int $updated_at
 */
class UserBankAccountLog extends ActiveRecord
{
    //UserBankAccount的source_type 保持一致
    const SOURCE_YUAN_DING = 1;
    const SOURCE_AADHAAR_API = 2;
    const SOURCE_DATABASE = 3;
    const SOURCE_ACCUAUTH = 4;

    const STATUS_UNVERIFIED = 0;
    const STATUS_SUCCESS = 1;
    const STATUS_FAILED = -1;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user_bank_account_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['account_id', 'source_type', 'user_id', 'status', 'created_at', 'updated_at'], 'integer'],
            [['data'], 'string'],
            [['name', 'report_account_name', 'account', 'ifsc', 'bank_name', 'remark'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'                  => 'ID',
            'source_type'         => 'Source Type',
            'user_id'             => 'User ID',
            'name'                => 'Name',
            'report_account_name' => 'Report Account Name',
            'account'             => 'Account',
            'ifsc'                => 'Ifsc',
            'bank_name'           => 'Bank Name',
            'status'              => 'Status',
            'data'                => 'Data',
            'created_at'          => 'Created At',
            'updated_at'          => 'Updated At',
        ];
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }
}

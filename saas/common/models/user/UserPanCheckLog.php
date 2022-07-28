<?php

namespace common\models\user;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%user_pan_check_log}}".
 *
 * 表属性
 * @property int $id
 * @property int $user_id
 * @property int $merchant_id
 * @property string $pan_input
 * @property string $pan_ocr
 * @property string $ocr_report_id
 * @property int $is_used
 * @property string $account_name
 * @property int $report_status 0:不通过 1:已通过
 * @property int $data_status 1:通过 2:不通过（pan卡号不符规则）3:不通过（pan卡号无效）4:不通过（两次卡号不一致）5:不通过(卡号被他人绑定）
 * @property int $check_third_source 0:历史记录 1:第三方-accuauth 2:第三方-aadhaar_api 9:api导流
 * @property string $full_name
 * @property string $first_name
 * @property string $middle_name
 * @property string $last_name
 * @property string $report_data
 * @property string $client_info
 * @property string $package_name
 * @property int $created_at
 * @property int $updated_at
 *
 * 关联表
 * @property LoanPerson loanPerson
 */
class UserPanCheckLog extends ActiveRecord
{
    const PASS = 1;
    const REJECT_PAN_RULE = 2;
    const REJECT_PAN_INVALID = 3;
    const REJECT_PAN_DIFFERENT = 4;
    const REJECT_PAN_USED = 5;

    const SOURCE_RECORD = 0;
    const SOURCE_ACCUAUTH = 1;
    const SOURCE_AADHAAR_API = 2;
    const SOURCE_EXPORT = 9;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user_pan_check_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'is_used', 'report_status', 'data_status', 'check_third_source', 'ocr_report_id', 'created_at', 'updated_at'], 'integer'],
            [['account_name', 'report_data', 'client_info', 'package_name'], 'string'],
            [['pan_input', 'pan_ocr', 'full_name', 'first_name', 'middle_name', 'last_name'], 'string', 'max' => 64],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'                 => 'ID',
            'user_id'            => 'User ID',
            'pan_input'          => 'Pan Input',
            'pan_ocr'            => 'Pan Ocr',
            'is_used'            => 'Is Used',
            'report_status'      => 'Report Status',
            'check_third_source' => 'Check Third Source',
            'full_name'          => 'Full Name',
            'first_name'         => 'First Name',
            'middle_name'        => 'Middle Name',
            'last_name'          => 'Last Name',
            'report_data'        => 'Report Data',
            'client_info'        => 'Client Info',
            'package_name'       => 'Package Name',
            'created_at'         => 'Created At',
            'updated_at'         => 'Updated At',
        ];
    }


    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    public function getLoanPerson()
    {
        return $this->hasOne(LoanPerson::tableName(), ['id' => 'user_id']);
    }

}

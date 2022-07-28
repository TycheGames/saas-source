<?php

namespace common\models\user;

use common\models\enum\CreditReportStatus;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%user_credit_report}}".
 *
 * @property int $id
 * @property int $user_id
 * @property int $merchant_id
 * @property string $account_name
 * @property int $source_type 1:accuauth
 * @property int $report_type 1. aad-ocr 2. pan-ocr 3. pan-verify
 * @property int $report_status 0:未收到 1:已收到
 * @property string $report_data
 * @property int $created_at
 * @property int $updated_at
 */
class UserCreditReport extends ActiveRecord
{
    //数据类型
    const TYPE_OCR_AAD = 1;
    const TYPE_OCR_PAN = 2;
    const TYPE_FR_LIVENESS = 3;
    const TYPE_FR_VERIFY = 4;
    //数据公司
    const SOURCE_ACCUAUTH = 1; //apk的accuauth
    const SOURCE_EXPORT = 2; //api导流的accuauth
    const SOURCE_ADVANCE  = 3; //apk的advance
    const SOURCE_EXPORT_ADVANCE = 4; //api导流的advance

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user_credit_report}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['source_type', 'report_type', 'report_status', 'created_at', 'updated_at'], 'integer'],
            [['account_name', 'report_data'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'            => 'ID',
            'user_id'       => 'User ID',
            'source_type'   => 'Source Type',
            'report_type'   => 'Report Type',
            'report_status' => 'Report Status',
            'report_data'   => 'Report Data',
            'created_at'    => 'Created At',
            'updated_at'    => 'Updated At',
        ];
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * @param int $userID
     * @param int $sourceType
     * @param int $reportType
     * @param int $merchantId
     * @return UserCreditReport
     */
    public function initRecord(int $userID,int $sourceType,int $reportType, int $merchantId): UserCreditReport
    {
        $model = new self();
        $model->user_id = $userID;
        $model->merchant_id = $merchantId;
        $model->source_type = $sourceType;
        $model->report_type = $reportType;
        $model->report_status = CreditReportStatus::NOT_RECEIVED()->getValue();
        $model->save();

        return $model;
    }
}

<?php

namespace common\models\user;

use common\models\ClientInfoLog;
use common\models\enum\CreditReportStatus;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%user_credit_report_ocr_pan}}".
 *
 * 表属性
 * @property int $id
 * @property int $user_id
 * @property int $merchant_id
 * @property int $report_id
 * @property int $report_status 0:未收到 1:已收到 2:报告错误 3:未达到阈值 4:通过
 * @property string $data_status
 * @property int $is_used 0:未使用 1:当前在使用
 * @property int $type 0:accu 1:advance
 * @property string $card_no
 * @property string $date_type
 * @property string $date_info
 * @property string $father_name
 * @property string $full_name
 * @property string $img_front_path
 * @property string $img_back_path
 * @property int $created_at
 * @property int $updated_at
 *
 * 关联表
 * @property ClientInfoLog clientInfo
 * @property UserCreditReport reportRecord
 */
class UserCreditReportOcrPan extends ActiveRecord
{
    const SOURCE_ACCUAUTH = 0;
    const SOURCE_ADVANCE  = 1;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user_credit_report_ocr_pan}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'report_id', 'report_status', 'is_used', 'type', 'created_at', 'updated_at'], 'integer'],
            [['card_no', 'date_type', 'date_info', 'father_name', 'full_name', 'data_status'], 'string', 'max' => 64],
            [['img_front_path', 'img_back_path'], 'string', 'max' => 256],
        ];
    }

    public function getReportRecord()
    {
        return $this->hasOne(UserCreditReport::class, ['id' => 'report_id']);
    }

    public function getClientInfo()
    {
        return $this->hasOne(ClientInfoLog::class,  ['event_id' => 'id'])
            ->where(['event' => ClientInfoLog::EVENT_PAN_OCR]);
    }

    public function setAllUnused($userID = 0)
    {
        $setUserID = $userID == 0 ? $this->user_id : $userID;
        $records = self::find()
            ->where(['user_id' => $setUserID])
            ->andWhere(['is_used' => 1])
            ->all();

        foreach ($records as $record) {
            /**
             * @var self $record
             */
            $record->is_used = 0;
            $record->save();
        }
    }

    public function setThisUsed()
    {
        $this->is_used = 1;
        $this->save();
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'             => 'ID',
            'user_id'        => 'User ID',
            'report_id'      => 'Report ID',
            'report_status'  => 'Report Status',
            'data_status'    => 'Data Status',
            'is_used'        => 'Is Used',
            'card_no'        => 'Card No',
            'date_type'      => 'Date Type',
            'data_info'      => 'Data Info',
            'father_name'    => 'Father Name',
            'full_name'      => 'Full Name',
            'img_front_path' => 'Img Front Path',
            'img_back_path'  => 'Img Back Path',
            'created_at'     => 'Created At',
            'updated_at'     => 'Updated At',
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
     * @param int $reportID
     * @param int $merchantId
     * @return UserCreditReportOcrPan
     */
    public function initRecord(int $userID,int $reportID, int $merchantId): UserCreditReportOcrPan
    {
        $model = new self();
        $model->user_id = $userID;
        $model->merchant_id = $merchantId;
        $model->report_id = $reportID;
        $model->report_status = CreditReportStatus::NOT_RECEIVED()->getValue();
        $model->save();

        return $model;
    }
}

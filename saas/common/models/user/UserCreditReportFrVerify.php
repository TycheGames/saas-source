<?php

namespace common\models\user;

use common\models\ClientInfoLog;
use common\models\enum\CreditReportStatus;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%user_credit_report_fr_verify}}".
 *
 * 表属性
 * @property int $id
 * @property int $user_id
 * @property int $merchant_id
 * @property int $report_id
 * @property int $report_status 0:未收到 1:已收到 2:报告错误 3:未达到阈值
 * @property string $data_status
 * @property int $is_used 0:未使用 1:当前在使用
 * @property int $type 0:accu 1:advance
 * @property int $report_type 0:fr_compare_pan 1:fr_compare_fr
 * @property int $identical 人脸匹配结果
 * @property string $score 人脸匹配分数
 * @property string $img1_path
 * @property string $img2_path
 * @property int $img1_report_id
 * @property int $img2_report_id
 * @property int $created_at
 * @property int $updated_at
 *
 * 关联表
 * @property ClientInfoLog clientInfo
 * @property UserCreditReport img1ReportRecord
 * @property UserCreditReport img2ReportRecord
 */
class UserCreditReportFrVerify extends ActiveRecord
{
    const TYPE_FR_COMPARE_PAN = 0;
    const TYPE_FR_COMPARE_FR = 1;

    const SOURCE_ACCUAUTH = 0;
    const SOURCE_ADVANCE  = 1;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user_credit_report_fr_verify}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'user_id', 'report_id', 'report_status', 'is_used', 'type', 'report_type', 'identical', 'img1_report_id', 'img2_report_id', 'created_at', 'updated_at'], 'integer'],
            [['score'], 'string', 'max' => 64],
            [['img1_path', 'img2_path'], 'string', 'max' => 256],
        ];
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
            'report_type'    => 'Report Type',
            'identical'      => 'Identical',
            'score'          => 'Score',
            'img1_path'      => 'Img1 Path',
            'img2_path'      => 'Img2 Path',
            'img1_report_id' => 'Img1 Report ID',
            'img2_report_id' => 'Img2 Report ID',
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

    public function getImg1ReportRecord()
    {
        return $this->hasOne(UserCreditReport::class, ['id' => 'img1_report_id']);
    }

    public function getImg2ReportRecord()
    {
        return $this->hasOne(UserCreditReport::class, ['id' => 'img2_report_id']);
    }

    public function getClientInfo()
    {
        if ($this->report_type == self::TYPE_FR_COMPARE_FR) {
            $event = ClientInfoLog::EVENT_FACE_TO_FACE_COMPARISON;
        } else {
            $event = ClientInfoLog::EVENT_PAN_TO_FACE_COMPARISON;
        }
        return $this->hasOne(ClientInfoLog::class, ['event_id' => 'id'])
            ->where(['event' => $event]);
    }

    public function setAllUnused(int $reportType, int $userID = 0)
    {
        $setUserID = $userID == 0 ? $this->user_id : $userID;
        $records = self::find()
            ->where(['user_id' => $setUserID])
            ->andWhere(['is_used' => 1])
            ->andWhere(['report_type' => $reportType])
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
     * @param int $userID
     * @param int $reportID
     * @param int $reportType
     * @param int $merchantId
     * @return UserCreditReportFrVerify
     */
    public function initRecord(int $userID, int $reportID, int $reportType, int $merchantId): UserCreditReportFrVerify
    {
        $model = new self();
        $model->user_id = $userID;
        $model->merchant_id = $merchantId;
        $model->report_id = $reportID;
        $model->report_type = $reportType;
        $model->report_status = CreditReportStatus::NOT_RECEIVED()->getValue();
        $model->save();

        return $model;
    }
}

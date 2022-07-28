<?php

namespace common\models\user;

use common\models\ClientInfoLog;
use common\models\enum\CreditReportStatus;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%user_credit_report_ocr_aad}}".
 *
 * 表属性
 * @property int $id
 * @property int $user_id
 * @property int $merchant_id
 * @property int $report_id
 * @property int $report_status 0:未收到 1:已收到 2:报告错误 3:未达到阈值 4:通过
 * @property string $data_front_status
 * @property string $data_back_status
 * @property int $is_used 0:未使用 1:当前在使用
 * @property int $type 0:accu 1:advance
 * @property string $card_no
 * @property string $card_no_mask
 * @property string $card_no_md5
 * @property string $card_no_encode
 * @property string $vid
 * @property string $date_type
 * @property string $date_info
 * @property int $gender
 * @property string $father_name
 * @property string $mother_name
 * @property string $full_name
 * @property string $phone_number
 * @property string $address
 * @property string $pin
 * @property string $state
 * @property string $city
 * @property string $img_front_path
 * @property string $img_back_path
 * @property string $img_front_mask_path
 * @property string $img_back_mask_path
 * @property string $check_data_z_path 加密后的正面
 * @property string $check_data_f_path 加密后的反面
 * @property string $is_mask
 * @property int $is_mask_back
 * @property string $is_encode
 * @property int $created_at
 * @property int $updated_at
 *
 * 关联表
 * @property ClientInfoLog clientInfo
 * @property UserCreditReport reportRecord
 */
class UserCreditReportOcrAad extends ActiveRecord
{
    const STATUS_ENCODE = 1;
    const STATUS_DELETE_FRONT = 2;
    const STATUS_DELETE_BACK = 3;

    const SOURCE_ACCUAUTH = 0;
    const SOURCE_ADVANCE  = 1;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user_credit_report_ocr_aad}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id'], 'required'],
            [['user_id', 'report_id', 'report_status', 'is_used', 'type', 'gender', 'is_mask', 'is_mask_back', 'is_encode', 'created_at', 'updated_at'], 'integer'],
            [['card_no', 'card_no_mask', 'card_no_md5', 'card_no_encode', 'vid', 'date_type', 'date_info', 'father_name', 'mother_name', 'full_name', 'phone_number', 'data_front_status', 'data_back_status', 'pin', 'state', 'city'], 'string', 'max' => 64],
            [['address', 'img_front_path', 'img_back_path', 'img_front_mask_path', 'img_back_mask_path', 'check_data_z_path', 'check_data_f_path'], 'string', 'max' => 256],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'                    => 'ID',
            'user_id'               => 'User ID',
            'report_id'             => 'Report ID',
            'report_status'         => 'Report Status',
            'ata_front_status'      => 'Data Front Status',
            'data_front_status'     => 'Data Front Status',
            'is_used'               => 'Is Used',
            'card_no'               => 'Card No',
            'vid'                   => 'Vid',
            'data_type'             => 'Data Type',
            'data_info'             => 'Data Info',
            'gender'                => 'Gender',
            'father_name'           => 'Father Name',
            'mother_name'           => 'Mother Name',
            'full_name'             => 'Full Name',
            'phone_number'          => 'Phone Number',
            'address'               => 'Address',
            'pin'                   => 'Pin',
            'state'                 => 'State',
            'city'                  => 'City',
            'img_front_path'        => 'Img Front Path',
            'img_back_path'         => 'Img Back Path',
            'img_front_mask_path'   => 'Img Front Mask Path',
            'img_back_mask_path'    => 'Img Back Mask Path',
            'check_data_z_path'     => 'Check Data Z Path',
            'check_data_f_path'     => 'Check Data F Path',
            'is_mask'               => 'Is Mask',
            'is_mask_back'          => 'Is Mask Back',
            'created_at'            => 'Created At',
            'updated_at'            => 'Updated At',
        ];
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    public function getReportRecord()
    {
        return $this->hasOne(UserCreditReport::class, ['id' => 'report_id']);
    }

    public function getClientInfo()
    {
        return $this->hasOne(ClientInfoLog::class, ['event_id' => 'id'])
            ->where(['event' => ClientInfoLog::EVENT_ADH_OCR_FRONT]);
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
     * @param int $userID
     * @param int $reportID
     * @param int $merchantId
     * @return UserCreditReportOcrAad
     */
    public function initRecord(int $userID, int $reportID, int $merchantId): UserCreditReportOcrAad
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

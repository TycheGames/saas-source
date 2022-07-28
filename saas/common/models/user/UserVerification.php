<?php

namespace common\models\user;


use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use Yii;

/**
 * Class UserVerification
 * @package common\models\user
 *
 * 表属性
 * @property int $id
 * @property int $user_id 用户ID
 * @property int $real_verify_status 是否进行了活体认证
 * @property int $real_fr_compare_pan_status 是否进行了人脸对比PAN认证
 * @property int $real_fr_compare_fr_status 是否进行了人脸对比人脸认证
 * @property int $real_basic_status 是否进行了基础信息填写
 * @property int $real_work_status 是否进行了工作信息认证
 * @property int $ocr_aadhaar_status 是否进行了OCR-AADHAAR
 * @property int $ocr_pan_status 是否进行了OCR-PAN
 * @property int $real_pan_status 是否进行了PAN验真
 * @property int $real_contact_status 是否进行了联系人认证
 * @property int $real_language_status 是否惊醒了语言认证
 * @property int $is_first_loan 是否是首次借款，1：是，0：否
 * @property int $status 状态，默认为0，备用
 * @property int $created_at 创建时间
 * @property int $updated_at 更新时间
 *
 * 关联表
 * @property LoanPerson loanPerson
 */
class UserVerification extends ActiveRecord
{

    const STATUS_NORMAL = 0;
    const VERIFICATION_NORMAL = 0;

    const FIRST_LOAN_YES = 1; //是首单
    const FIRST_LOAN_NO = 0; //不是首单

    public static function tableName()
    {
        return '{{%user_verification}}';
    }

    public static function getDb()
    {
        return Yii::$app->get('db');
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'                         => 'ID',
            'user_id'                    => 'User ID',
            'real_verify_status'         => 'Real Verify Status',
            'real_fr_compare_pan_status' => 'Real Fr Compare Pan Status',
            'real_fr_compare_fr_status'  => 'Real Fr Compare Fr Status',
            'real_basic_status'          => 'Real Basic Status',
            'real_work_status'           => 'Real Work Status',
            'ocr_aadhaar_status'         => 'Ocr Aadhaar Status',
            'ocr_pan_status'             => 'Ocr Pan Status',
            'real_pan_status'            => 'Real Pan Status',
            'real_contact_status'        => 'Real Contact Status',
            'real_language_status'       => 'Real Language Status',
            'is_first_loan'              => 'Is First Loan',
            'status'                     => 'Status',
            'updated_at'                 => 'Updated At',
            'created_at'                 => 'Created At',
        ];
    }

    const TYPE_VERIFY = 1;          // real_verify_status
    const TYPE_FR_COMPARE_PAN = 2;  // real_fr_compare_pan_status
    const TYPE_FR_COMPARE_FR = 3;   // real_fr_compare_fr_status
    const TYPE_BASIC = 4;           // real_basic_status
    const TYPE_WORK = 5;            // real_work_status
    const TYPE_OCR_AADHAAR = 6;     // ocr_aadhaar_status
    const TYPE_OCR_PAN = 7;         // ocr_pan_status
    const TYPE_PAN = 9;             // real_pan_status
    const TYPE_CONTACT = 11;        // real_contact_status
    const TYPE_LANGUAGE = 21;       // real_language_status

    public static $verification_filed_map = [
        self::TYPE_VERIFY         => 'real_verify_status',
        self::TYPE_FR_COMPARE_PAN => 'real_fr_compare_pan_status',
        self::TYPE_FR_COMPARE_FR  => 'real_fr_compare_fr_status',
        self::TYPE_BASIC          => 'real_basic_status',
        self::TYPE_WORK           => 'real_work_status',
        self::TYPE_OCR_AADHAAR    => 'ocr_aadhaar_status',
        self::TYPE_OCR_PAN        => 'ocr_pan_status',
        self::TYPE_PAN            => 'real_pan_status',
        self::TYPE_CONTACT        => 'real_contact_status',
        self::TYPE_LANGUAGE       => 'real_language_status',
    ];

    public function getLoanPerson()
    {
        return $this->hasOne(LoanPerson::class, ['id' => 'user_id']);
    }

    /**
     * 认证表更新，并添加认证记录
     * @param $type
     * @param $status
     * @param bool $isStatistics
     * @return bool
     */
    public function verificationUpdate($type, $status, bool $isStatistics = true)
    {
        $res = true;
        if (isset(self::$verification_filed_map[$type])) {
            $filed = self::$verification_filed_map[$type];
            $this->$filed = $status;
            $res = $this->save();
        }
        //添加认证记录
        if ($isStatistics) {
            $userVerificationLog = new UserVerificationLog();
            $userVerificationLog->user_id = $this->user_id;
            $userVerificationLog->type = $type;
            $userVerificationLog->status = $status;
            return $userVerificationLog->save() && $res;
        } else {
            return $res;
        }
    }
}
<?php

namespace common\models;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%info_user}}".
 *
 * @property int $id
 * @property string $app_name
 * @property int $order_id 业务系统的订单ID
 * @property int $user_id 业务系统的用户ID
 * @property string $phone
 * @property string $pan_code
 * @property string $pan_ocr_code
 * @property string $aadhaar_md5
 * @property string $gender 性别
 * @property string $email_address 邮箱地址
 * @property string $filled_name 申请人手填姓名
 * @property string $pan_ocr_name Pan OCR获取的姓名
 * @property string $aadhaar_ocr_name Aadhaar OCR获取的姓名
 * @property string $pan_verify_name Pan Verify的姓名
 * @property string $filled_birthday 申请人申请填写的生日
 * @property string $pan_birthday Pan生日
 * @property string $aadhaar_birthday Aadhaar生日
 * @property int $education_level 申请人教育水平
 * @property int $occupation 职业
 * @property string $residential_detail_address 申请人居住详细地址
 * @property string $residential_address 申请人居住联邦
 * @property string $residential_city 申请人居住地址城市
 * @property string $aadhaar_address 申请人OCR Aadhaar地址
 * @property string $aadhaar_ocr_pin_code 申请人OCR Aadhaar地址邮编
 * @property string $aadhaar_filled_city 申请人手填Aadhaar城市
 * @property string $aadhaar_pin_code 申请人手填Aadhaar地址邮编
 * @property int $monthly_salary 申请人月收入(单位分)
 * @property string $contact1_mobile_number 申请人第一紧急联系人手机号码
 * @property string $contact2_mobile_number 申请人第二紧急联系人手机号码
 * @property string $fr_liveness_source
 * @property string $fr_liveness_score
 * @property string $fr_verify_source
 * @property string $fr_verify_type
 * @property string $fr_verify_score
 * @property string $language_need_check
 * @property int $language_correct_number
 * @property int $language_time
 * @property int $register_time
 * @property int $app_market 注册时的appMarket
 * @property int $media_source 注册时的media_source
 * @property int $created_at
 * @property int $updated_at
 *
 * 关联表
 * @property InfoDevice $infoDevice
 */
class InfoUser extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%info_user}}';
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
            [[ 'phone', 'pan_code', 'aadhaar_md5', 'filled_name', 'gender', 'email_address', 'pan_ocr_code',
                'pan_ocr_name', 'aadhaar_ocr_name',  'pan_verify_name', 'filled_birthday', 'pan_birthday', 'aadhaar_birthday',
                'education_level', 'occupation', 'residential_detail_address', 'residential_address', 'residential_city', 'aadhaar_address',
               'aadhaar_pin_code', 'aadhaar_ocr_pin_code', 'aadhaar_filled_city',
                'monthly_salary', 'contact1_mobile_number', 'contact2_mobile_number', 'fr_liveness_source', 'fr_liveness_score', 'fr_verify_source',
                'fr_verify_type', 'fr_verify_score', 'language_need_check', 'language_correct_number', 'language_time', 'register_time',
               'app_market', 'media_source',
            ], 'safe'],
            [['created_at', 'updated_at'], 'safe']
        ];
    }

    public function getInfoDevice()
    {
        return $this->hasOne(InfoDevice::class, ['user_id' => 'user_id', 'order_id' => 'order_id', 'app_name' => 'app_name']);
    }

}

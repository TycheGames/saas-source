<?php

namespace frontend\models\risk;

use yii\base\Model;

class UserBasicForm extends Model
{
    public $package;
    public $phone;
    public $pan_code;
    public $aadhaar_md5;
    public $gender;
    public $email_address;
    public $filled_name;
    public $pan_ocr_name;
    public $aadhaar_ocr_name;
    public $pan_verify_name;
    public $filled_birthday;
    public $pan_birthday;
    public $aadhaar_birthday;
    public $education_level;
    public $occupation;
    public $residential_detail_address;
    public $residential_address;
    public $residential_city;
    public $aadhaar_address;
    public $aadhaar_pin_code;
    public $aadhaar_filled_city;
    public $aadhaar_ocr_pin_code;
    public $monthly_salary;
    public $contact1_mobile_number;
    public $contact2_mobile_number;
    public $fr_liveness_source;
    public $fr_liveness_score;
    public $fr_verify_source;
    public $fr_verify_type;
    public $fr_verify_score;
    public $language_need_check;
    public $language_correct_number;
    public $language_time;
    public $register_time;
    public $app_market;
    public $media_source;
    public $pan_ocr_code;


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['phone', 'pan_code', 'aadhaar_md5', 'filled_name', 'gender', 'email_address', 'pan_ocr_code',
                'pan_ocr_name', 'aadhaar_ocr_name',  'pan_verify_name', 'filled_birthday', 'pan_birthday', 'aadhaar_birthday',
                'education_level', 'occupation', 'residential_detail_address', 'residential_address', 'residential_city', 'aadhaar_address',
              'aadhaar_pin_code', 'aadhaar_ocr_pin_code', 'aadhaar_filled_city',
                'monthly_salary', 'contact1_mobile_number', 'contact2_mobile_number', 'fr_liveness_source', 'fr_liveness_score', 'fr_verify_source',
                'fr_verify_type', 'fr_verify_score', 'language_need_check', 'language_correct_number', 'language_time', 'register_time',
              'app_market', 'media_source',
            ], 'safe'],
        ];
    }

}

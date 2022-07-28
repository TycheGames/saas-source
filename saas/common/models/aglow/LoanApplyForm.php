<?php


namespace common\models\aglow;

use yii\base\Model;


class LoanApplyForm extends Model
{
    public
        $full_name, $email, $phone, $dob, $gender, $pan_num, $pan_photo, $self_photo,
        $adh_num_masked, $adh_photo_masked, $residential_address, $detail_address, $zip_code,
        $adh_address, $adh_detail_address, $adh_zip_code, $education, $is_student, $marital_status,
        $industry, $company_name, $monthly_salary_before_tax, $contact_list, $relative,
        $name, $mobile, $apply_date, $imei;

}
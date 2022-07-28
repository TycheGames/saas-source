<?php

namespace frontend\models\user;

use frontend\models\BaseForm;
use yii\base\Model;
use common\models\enum\Religion;
use common\models\enum\Student;
use common\models\enum\Marital;

class UserBasicInfoExternalForm extends UserBasicInfoForm
{
    /**
     * 兼容导流数据
     * @return array
     */
    public function rules()
    {
        return [
            //必填项
            [['maritalStatusId', 'emailVal', 'zipCodeVal', 'fullName', 'birthday', 'clientInfo', 'aadhaarPinCode'], 'required'],
            [['studentId', 'maritalStatusId', 'emailVal', 'zipCodeVal', 'fullName', 'aadhaarPinCode', 'aadhaarAddressId', 'aadhaarAddressVal', 'aadhaarDetailAddressVal'], 'trim'],
            ['maritalStatusId', 'in', 'range' => array_values(Marital::toArray()), 'message' => 'Error marital status'],
            ['emailVal', 'email'],
            [['zipCodeVal'], 'number', 'min' => 100000, 'max' => 999999],
        ];
    }
}
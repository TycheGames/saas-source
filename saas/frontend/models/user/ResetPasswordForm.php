<?php

namespace frontend\models\user;

use common\helpers\Util;
use frontend\models\BaseForm;

class ResetPasswordForm extends BaseForm
{
    public $phone, $code, $password;
    public $packageName;

    public function maps() : array
    {
        return [
            'phone' => 'phone',
            'password' => 'password',
            'code' => 'code',
            'packageName' => 'package_name'
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['phone','password','code'], 'required'],
            [['phone','password','code'], 'trim'],
            // ['phone', 'verifyPhone'],
            ['password', 'string', 'min' => 6, 'max' => 20],
            // ['code', 'string', 'length' => 6],
            [['packageName'], 'safe']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'phone' => 'phone number',
            'password' => 'password',
            'code' => 'OTP'
        ];
    }


    public function verifyPhone($attribute, $params)
    {
        if(!Util::verifyPhone($this->$attribute)){
            $this->addError($attribute, 'please enter a valid mobile phone number');
        }
        return;
    }


}

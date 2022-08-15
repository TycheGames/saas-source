<?php


namespace frontend\models\user;

use frontend\models\BaseForm;

class GetLoginCaptchaForm extends BaseForm
{
    public $phone;
    public $packageName;

    function maps(): array
    {
        return [
            'phone'                      => 'phone',
            'packageName'               => 'package_name'
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'phone'                      => 'Emergency contact phone',
            'packageName'               => 'package_name'
        ];
    }

    public function rules(): array
    {
        return [
            //必填项
            ['phone', 'required'],
            //对表单项进行去掉首尾空格的处理
            ['phone', 'trim'],
            // ['phone', 'validatePhone'],
            [['packageName'], 'safe']
        ];
    }


    public function validatePhone($attribute, $params)
    {
        if(strlen($this->$attribute) == 12 && substr($this->$attribute,0 , 2) == '91')
        {
            $this->$attribute = substr($this->$attribute, 2, 10);
        }

        if(strlen($this->$attribute) != 10)
        {
            $this->addError($attribute, 'please enter a valid mobile phone number');
        }
    }

}
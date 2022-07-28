<?php


namespace frontend\models\user;

use frontend\models\BaseForm;

class GetResetPassOtpForm extends BaseForm
{
    public $phone;
    public $packageName;

    function maps(): array
    {
        return [
            'phone'                      => 'phone',
            'packageName' => 'package_name'
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'phone'                      => 'Emergency contact phone',
            'packageName' => 'package name'
        ];
    }

    public function rules(): array
    {
        return [
            //必填项
            ['phone', 'required'],
            //对表单项进行去掉首尾空格的处理
            ['phone', 'trim'],
            [['packageName'], 'safe']
        ];
    }

}
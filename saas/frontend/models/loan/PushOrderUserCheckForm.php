<?php


namespace frontend\models\loan;


use Carbon\Carbon;
use frontend\models\BaseForm;

class PushOrderUserCheckForm extends BaseForm
{
    public $phone;
    public $aadhaar;
    public $pan;
    public $packageName;
    public $token;

    public function maps(): array
    {
        return [];
    }

    public function rules()
    {
        return [
            [['phone', 'aadhaar', 'pan', 'packageName', 'token'], 'required'],
            [['phone', 'aadhaar', 'pan', 'packageName', 'token'], 'trim'],
            ['token', 'required', 'requiredValue' => 'S9PCByTd7XNcljj1'],
        ];
    }
}
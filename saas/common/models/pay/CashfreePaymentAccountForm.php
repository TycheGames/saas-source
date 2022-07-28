<?php

namespace common\models\pay;


class CashfreePaymentAccountForm extends BaseAccountForm
{

    public $key;
    public $secret;
    public $notifyUrl;


    public function attributeLabels()
    {
        return [
            'key' => 'key',
            'secret' => 'secret',
            'notifyUrl' => 'notifyUrl',
        ];
    }

    public function rules()
    {
        return [
            [['key', 'secret', 'notifyUrl'], 'required']
        ];
    }

}

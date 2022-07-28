<?php

namespace common\models\pay;


class MojoPaymentForm extends BaseAccountForm
{
    public $amount;
    public $purpose;
    public $buyer_name;
    public $phone;
    public $email;
    public $webhook;
    public $allow_repeated_payments;

    public function attributeLabels()
    {
        return [
            'amount'                  => 'amount',
            'purpose'                 => 'purpose',
            'buyer_name'              => 'buyer_name',
            'phone'                   => 'phone',
            'email'                   => 'email',
            'webhook'                 => 'webhook',
            'allow_repeated_payments' => 'allow_repeated_payments',
        ];
    }

    public function rules()
    {
        return [
            [[
                'amount', 'purpose', 'buyer_name', 'phone', 'email', 'webhook',
                'allow_repeated_payments',
             ], 'required'],
        ];
    }

}

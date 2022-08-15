<?php

namespace common\models\pay;


class RazorpayAccountForm extends BaseAccountForm
{

    public $baseUri;
    public $payoutKeyId;
    public $payoutKeySecret;
    public $paymentKeyId;
    public $paymentSecret;
    public $webhooksSecret;
    public $accountNumber;


    public function attributeLabels()
    {
        return [
            'baseUri' => '请求地址',
            'payoutKeyId' => '放款Key',
            'payoutKeySecret' => '放款Secret',
            'accountNumber' => 'account number',
            'paymentKeyId' => '还款Key',
            'paymentSecret' => '还款Secret',
            'webhooksSecret' => '回调Secret',
        ];
    }

    public function rules()
    {
        return [
            [['baseUri', 'payoutKeyId', 'payoutKeySecret', 'paymentKeyId', 'paymentSecret', 'webhooksSecret', 'accountNumber'], 'required']
        ];
    }

}

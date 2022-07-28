<?php

namespace common\models\pay;


class SifangPaymentForm extends BaseAccountForm
{
    public $userId;
    public $orderNumber;
    public $paymentChannel;
    public $baseCurrency;
    public $quoteCurrency;
    public $amount;
    public $payerName;
    public $timestamp;
    public $returnUrl;
    public $informUrl;
    public $forTest;
    public $remark;

    public function attributeLabels()
    {
        return [
            'userId'         => 'userId',
            'orderNumber'    => 'orderNumber',
            'paymentChannel' => 'paymentChannel',
            'baseCurrency'   => 'baseCurrency',
            'quoteCurrency'  => 'quoteCurrency',
            'amount'         => 'amount',
            'payerName'      => 'payerName',
            'timestamp'      => 'timestamp',
            'returnUrl'      => 'returnUrl',
            'informUrl'      => 'informUrl',
            'forTest'        => 'forTest',
            'remark'         => 'remark',
        ];
    }

    public function rules()
    {
        return [
            [[
                'userId', 'orderNumber', 'paymentChannel', 'baseCurrency', 'quoteCurrency', 'amount',
                'payerName', 'timestamp', 'returnUrl', 'informUrl', 'forTest',
             ], 'required'],
            [['remark'], 'safe'],
        ];
    }

}

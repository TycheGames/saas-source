<?php

namespace common\models\pay;


class MpursePaymentForm extends BaseAccountForm
{
    public $payerVA;
    public $amount;
    public $txnId;
    public $product;
    public $cName;
    public $cMobile;


    public function attributeLabels()
    {
        return [
            'payerVA' => 'payerVA',
            'amount'  => 'amount',
            'txnId'   => 'txnId',
            'product' => 'product',
            'cName'   => 'cName',
            'cMobile' => 'cMobile',
        ];
    }

    public function rules()
    {
        return [
            [['amount', 'txnId', 'product', 'cName', 'cMobile'], 'required'],
            [['payerVA'], 'safe'],
        ];
    }

}

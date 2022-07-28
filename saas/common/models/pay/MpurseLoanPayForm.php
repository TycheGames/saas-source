<?php

namespace common\models\pay;


class MpurseLoanPayForm extends BaseAccountForm
{

    public $beneName;
    public $beneAccNo;
    public $bankName;
    public $beneIFSC;
    public $txnId;
    public $amount;
    public $remark;
    public $beneMobile;


    public function attributeLabels()
    {
        return [
            'beneName' => 'beneName',
            'beneAccNo' => 'beneAccNo',
            'bankName' => 'bankName',
            'beneIFSC' => 'beneIFSC',
            'txnId' => 'txnId',
            'amount' => 'amount',
            'remark' => 'remark',
            'beneMobile' => 'beneMobile',
        ];
    }

    public function rules()
    {
        return [
            [['beneName', 'beneAccNo', 'beneIFSC', 'amount', 'txnId', 'beneMobile'], 'required'],
            [['remark', 'bankName'], 'safe'],
        ];
    }

}

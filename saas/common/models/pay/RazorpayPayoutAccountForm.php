<?php

namespace common\models\pay;

use yii\base\Model;

class RazorpayPayoutAccountForm extends BasePayoutAccountForm
{

    public $payoutKeyId;
    public $payoutKeySecret;
    public $webhooksSecret;
    public $accountNumber;

    public function attributeLabels()
    {
        return [
            'payoutKeyId' => '打款Key',
            'payoutKeySecret' => '打款Secret',
            'accountNumber' => '打款账号',
            'webhooksSecret' => '回调Secret',
        ];
    }

    public function rules()
    {
        return [
            [['payoutKeyId', 'payoutKeySecret', 'webhooksSecret', 'accountNumber',], 'required'],

        ];
    }

}

<?php

namespace common\models\pay;

use yii\base\Model;

class PaytmPayoutAccountForm extends BaseAccountForm
{

    public $payTmMerchantID;
    public $payTmMerchantKey;
    public $payTmMerchantGuid;


    public function attributeLabels()
    {
        return [
            'payTmMerchantID' => 'paytm商户ID',
            'payTmMerchantKey' => 'paytm merchant_key',
            'payTmMerchantGuid' => 'paytm merchant_Guid',
        ];
    }

    public function rules()
    {
        return [
            [['payTmMerchantID', 'payTmMerchantKey', 'payTmMerchantGuid',], 'required']
        ];
    }

}

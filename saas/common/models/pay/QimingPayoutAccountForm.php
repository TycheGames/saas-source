<?php

namespace common\models\pay;

use yii\base\Model;

class QimingPayoutAccountForm extends BasePayoutAccountForm
{
    public $qimingKeyId;
    public $qimingApiKey;

    public function attributeLabels()
    {
        return [
            'qimingKeyId' => 'keyId',
            'qimingApiKey' => 'apiKey',
        ];
    }

    public function rules()
    {
        return [
            [['qimingApiKey', 'qimingKeyId'], 'required'],

        ];
    }

}

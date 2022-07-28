<?php

namespace common\models\pay;

use yii\base\Model;

class MpursePayoutAccountForm extends BasePayoutAccountForm
{

    public $mpursePartnerId;
    public $mpurseKey;


    public function attributeLabels()
    {
        return [
            'mpursePartnerId' => 'partnerID',
            'mpurseKey' => 'Key',
        ];
    }

    public function rules()
    {
        return [
            [['mpursePartnerId', 'mpurseKey',], 'required']
        ];
    }

}

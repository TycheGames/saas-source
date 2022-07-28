<?php

namespace common\models\pay;

use yii\base\Model;

class CashfreePayoutAccountForm extends BaseAccountForm
{

    public $cashFreeKey;
    public $cashFreeSecret;


    public function attributeLabels()
    {
        return [
            'cashFreeKey' => 'CashFree Client ID',
            'cashFreeSecret' => 'CashFree Client Secret',
        ];
    }

    public function rules()
    {
        return [
            [['cashFreeKey', 'cashFreeSecret', ], 'required']
        ];
    }

}

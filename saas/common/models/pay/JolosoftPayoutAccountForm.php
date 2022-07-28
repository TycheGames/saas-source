<?php

namespace common\models\pay;

use yii\base\Model;

class JolosoftPayoutAccountForm extends BasePayoutAccountForm
{
    public $jolosoftApikey;

    public function attributeLabels()
    {
        return [
            'jolosoftApikey' => 'apiKey',
        ];
    }

    public function rules()
    {
        return [
            [['jolosoftApikey'], 'required'],

        ];
    }

}

<?php

namespace frontend\models\user;

use frontend\models\BaseForm;

class OrderForm extends BaseForm
{
    public $orderId;

    function maps(): array
    {
        return [
            'orderId' => 'orderId',
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'orderId' => 'Order id',
        ];
    }

    public function rules(): array
    {
        return [
            ['orderId', 'safe']
        ];
    }

}// END CLASS
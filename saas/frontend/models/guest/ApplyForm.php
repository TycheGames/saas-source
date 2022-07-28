<?php

namespace frontend\models\guest;

use frontend\models\BaseForm;


class ApplyForm extends BaseForm
{
    public $amount;
    public $key;



    public function maps(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['amount', 'key'], 'required'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'amount'      => 'amount',
            'key'   => 'key'
        ];
    }
}

<?php

namespace frontend\models\guest;

use frontend\models\BaseForm;


class OrderDetailForm extends BaseForm
{
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
            [['key'], 'required'],
        ];
    }



    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'key'   => 'key'
        ];
    }
}

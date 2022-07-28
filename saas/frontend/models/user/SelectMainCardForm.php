<?php

namespace frontend\models\user;

use frontend\models\BaseForm;


class SelectMainCardForm extends BaseForm
{
    public $id;

    public function maps() : array
    {
        return [
            'id' => 'id',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['id', 'required'],
            ['id', 'trim']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'id',
        ];
    }



}

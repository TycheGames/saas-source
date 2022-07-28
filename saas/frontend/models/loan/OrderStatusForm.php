<?php

namespace frontend\models\loan;

use frontend\models\BaseForm;


class OrderStatusForm extends BaseForm
{
    public $id;
    public $userId;

    public function maps() : array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['id', 'required'],
            ['id', 'trim'],
            ['id', 'integer'],
            [['userId'], 'safe']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'order id',
            'userId' => 'user id'
        ];
    }



}

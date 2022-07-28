<?php

namespace frontend\models\loan;

use frontend\models\BaseForm;


class OrderBindCardForm extends BaseForm
{
    public $orderId;
    public $bankCardId;

    public function maps() : array
    {
        return [
            'orderId' => 'order_id',
            'bankCardId' => 'card_id',
        ];
        // TODO: Implement maps() method.
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['orderId', 'bankCardId'], 'required'],
            [['orderId', 'bankCardId'], 'trim'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'orderId' => 'order_id',
            'bankCardId' => 'card_id',
        ];
    }



}

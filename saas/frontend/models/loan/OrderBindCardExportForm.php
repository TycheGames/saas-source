<?php

namespace frontend\models\loan;

use frontend\models\BaseForm;


/**
 * ContactForm is the model behind the contact form.
 */
class OrderBindCardExportForm extends BaseForm
{
    public $bankCard;
    public $orderUuid;
    public $token;

    public function maps(): array
    {
        return [
            'orderUuid' => 'order uuid',
            'bankCard'  => 'bank card',
            'token'     => 'token',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['bankCard', 'orderUuid'], 'required'],
            ['token', 'required', 'requiredValue' => 'S9PCByTd7XNcljj1'],
            [['orderUuid'], 'trim'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'bankCard' => 'bank card',
            'orderId'  => 'order id',
            'token'    => 'token',
        ];
    }
}

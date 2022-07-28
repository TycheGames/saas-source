<?php

namespace frontend\models\loan;

use frontend\models\BaseForm;

/**
 * GetTransferDataForm is the model behind the contact form.
 */
class GetTransferDataForm extends BaseForm
{
    public $orderId;
    public $userID;

    public function maps(): array
    {
        return [
            'orderId' => 'order_id',
            'userID' => 'user_id',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['orderId', 'userID'], 'required'],
            [['orderId'], 'trim'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'orderId' => 'order id',
            'userID' => 'user id',
        ];
    }
}

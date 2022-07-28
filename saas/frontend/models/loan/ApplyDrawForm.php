<?php

namespace frontend\models\loan;

use frontend\models\BaseForm;


/**
 * @property int $amount
 * @property int $userId
 * @property int $orderId
 * @property array $clientInfo
 */
class ApplyDrawForm extends BaseForm
{
    public $amount;
    public $userId;
    public $orderId;
    public $clientInfo;

    public function maps(): array
    {
        return [
            'amount'     => 'amount',
            'userId'     => 'user_id',
            'orderId'    => 'order_id',
            'clientInfo' => 'client_info',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['amount',  'userId', 'orderId', 'clientInfo'], 'required'],
            [['amount',  'userId', 'orderId'], 'trim'],
            [['orderId'], 'string', 'length' => [1, 11]],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'amount'     => 'amount',
            'userId'     => 'user id',
            'orderId'    => 'order id',
            'clientInfo' => 'client info',
        ];
    }
}

<?php

namespace frontend\models\loan;

use frontend\models\BaseForm;


/**
 * @property int $amount
 * @property int $orderUuid
 */
class ApplyDrawExportForm extends BaseForm
{
    public $amount;
    public $orderUuid;

    public function maps(): array
    {
        return [
            'amount'     => 'amount',
            'orderUuid'  => 'order_uuid',
            'clientInfo' => 'client_info',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['amount', 'orderUuid'], 'required'],
            [['amount', 'orderUuid'], 'trim'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'amount'     => 'amount',
            'orderUuid'  => 'order_uuid',
            'clientInfo' => 'client_info',
        ];
    }
}

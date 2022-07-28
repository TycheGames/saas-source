<?php

namespace frontend\models\loan;

use frontend\models\BaseForm;


/**
 * RepaymentApplyExportForm is the model behind the contact form.
 */
class RepaymentApplyExportForm extends BaseForm
{
    public $amount;
    public $orderUuid;
    public $paymentType;
    public $token;
    public $host;
    public $serviceType;
    public $customerEmail;
    public $customerPhone;
    public $customerName;
    public $customerUpiAccount;
    public $paymentChannel;

    public function maps(): array
    {
        return [
            'amount'             => 'amount',
            'orderUuid'          => 'orderUuid',
            'token'              => 'token',
            'host'               => 'host',
            'paymentType'        => 'paymentType',
            'serviceType'        => 'serviceType',
            'customerEmail'      => 'customerEmail',
            'customerPhone'      => 'customerPhone',
            'customerName'       => 'customerName',
            'customerUpiAccount' => 'customerUpiAccount',
            'paymentChannel'     => 'paymentChannel',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['amount', 'orderUuid', 'paymentType'], 'required'],
            ['token', 'required', 'requiredValue' => 'S9PCByTd7XNcljj1'],
            [['amount', 'orderUuid'], 'trim'],
            ['serviceType', 'safe'],
            [['customerEmail', 'customerPhone', 'customerName', 'customerUpiAccount', 'paymentChannel', 'host'], 'safe'],

        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'amount'             => 'amount',
            'orderUuid'          => 'orderUuid',
            'token'              => 'token',
            'host'               => 'host',
            'paymentType'        => 'paymentType',
            'serviceType'        => 'serviceType',
            'customerEmail'      => 'customerEmail',
            'customerPhone'      => 'customerPhone',
            'customerName'       => 'customerName',
            'customerUpiAccount' => 'customerUpiAccount',
            'paymentChannel'     => 'paymentChannel',
        ];
    }


}

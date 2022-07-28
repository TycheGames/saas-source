<?php

namespace frontend\models\loan;

use frontend\models\BaseForm;

/**
 * ContactForm is the model behind the contact form.
 */
class ApplyLoanForm extends BaseForm
{
    public $amount;
    public $productId;
    public $bankCardId;
    public $userId;
    public $clientInfo;
    public $packageName;
    public $isExport = false;
    public $orderUUID = '';
    public $isAllFirst = null;

    public function maps(): array
    {
        return [
            'amount'      => 'amount',
            'productId'   => 'product_id',
            'userId'      => 'userId',
            'clientInfo'  => 'clientInfo',
            'packageName' => 'packageName',
            'isExport'    => 'isExport',
            'orderUUID'   => 'orderUUID',
            'isAllFirst'  => 'isAllFirst',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['amount',  'productId', 'userId', 'clientInfo', 'packageName'], 'required'],
            [['amount',  'productId', 'userId', 'packageName'], 'trim'],
            [['productId'], 'string', 'length' => [1, 11]],
            [['bankCardId', 'isExport', 'orderUUID', 'isAllFirst'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'amount'      => 'amount',
            'productId'   => 'product info',
            'userId'      => 'userId',
            'clientInfo'  => 'clientInfo',
            'packageName' => 'packageName',
            'isExport'    => 'isExport',
            'orderUUID'   => 'orderUUID',
            'isAllFirst'  => 'isAllFirst',
        ];
    }
}

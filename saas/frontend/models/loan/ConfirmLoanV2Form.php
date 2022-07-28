<?php

namespace frontend\models\loan;

use frontend\models\BaseForm;


/**
 * ContactForm is the model behind the contact form.
 */
class ConfirmLoanV2Form extends BaseForm
{
    public $disbursalAmount;
    public $hostInfo;
    public $userId;
    public $packageName;
    public $clientInfo;
    public $orderId;


    public function maps() : array
    {
        return [
            'amount' => 'amount',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['disbursalAmount', 'default', 'value' => 0],
            [['disbursalAmount',  'hostInfo', 'userId', 'packageName'], 'trim'],
            [['disbursalAmount', 'hostInfo', 'userId', 'packageName', 'clientInfo'], 'required'],
            ['orderId', 'safe']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'disbursalAmount' => 'disbursalAmount',
            'hostInfo' => 'hostInfo',
            'userId' => 'userId',
            'packageName' => 'packageName',
            'orderId' => 'orderId'
        ];
    }



}

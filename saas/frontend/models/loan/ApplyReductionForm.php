<?php

namespace frontend\models\loan;

use frontend\models\BaseForm;


/**
 * ContactForm is the model behind the contact form.
 */
class ApplyReductionForm extends BaseForm
{
    public $userId;
    public $orderId;
    public $reductionFee;
    public $repaymentDate;
    public $reasons;
    public $contact;


    public function maps(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['userId', 'orderId', 'reductionFee', 'repaymentDate', 'reasons'], 'required'],
            [['repaymentDate'], 'trim'],
            [['contact'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'userId'        => 'user id',
            'orderId'       => 'order id',
            'reductionFee'  => 'apply reduction fee',
            'repaymentDate' => 'assume repayment date',
            'reasons'       => 'reduction reasons',
            'contact'       => 'contact information',
        ];
    }
}

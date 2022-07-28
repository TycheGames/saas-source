<?php

namespace frontend\models\loan;

use frontend\models\BaseForm;


/**
 * ContactForm is the model behind the contact form.
 */
class RepaymentResultForm extends BaseForm
{
    public $razorpayPaymentId;
    public $razorpayOrderId;
    public $razorpaySignature;


    public function maps() : array
    {
        return [
            'razorpayPaymentId' => 'razorpayPaymentId',
            'razorpayOrderId' => 'razorpayOrderId',
            'razorpaySignature' => 'razorpaySignature',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['razorpayPaymentId', 'razorpayOrderId','razorpaySignature'], 'required'],
            [['razorpayPaymentId', 'razorpayOrderId','razorpaySignature'], 'trim'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'razorpayPaymentId' => 'razorpayPaymentId',
            'razorpayOrderId' => 'razorpayOrderId',
            'razorpaySignature' => 'razorpaySignature',
        ];
    }



}

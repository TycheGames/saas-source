<?php

namespace frontend\models\loan;

use frontend\models\BaseForm;

/**
 * ContactForm is the model behind the contact form.
 */
class PaymentOrderListForm extends BaseForm
{
    public $page;

    public function maps() : array
    {
       return [];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['page', 'required'],
            ['page', 'trim'],
            ['page', 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'page' => 'page',
        ];
    }



}

<?php

namespace frontend\models\agreement;

use yii\base\Model;

/**
 * ContactForm is the model behind the contact form.
 */
class LoanServiceForm extends Model
{
    public $amount;
    public $days;
    public $productId;



    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['amount', 'days', 'productId'], 'required'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'amount' => 'amount',
            'days' => 'days',
            'productId' => 'product info',
        ];
    }
}

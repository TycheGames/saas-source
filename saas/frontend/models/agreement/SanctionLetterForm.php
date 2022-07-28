<?php

namespace frontend\models\agreement;

use yii\base\Model;

/**
 * SanctionLetterForm is the model behind the contact form.
 */
class SanctionLetterForm extends Model
{
    public $amount;
    public $days;
    public $productId;
    public $orderID;
    public $userID;
    public $clientInfo;



    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['userID', 'clientInfo'], 'required'],
            [['productId', 'orderID', 'days', 'amount'], 'safe'],
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
            'orderID' => 'orderID',
            'userID' => 'userID',
            'clientInfo' => 'clientInfo',
        ];
    }
}

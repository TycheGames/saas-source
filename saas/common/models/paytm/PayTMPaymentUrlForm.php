<?php

namespace common\models\paytm;


use yii\base\Model;

class PayTMPaymentUrlForm extends Model
{

    public $mid;
    public $linkType;
    public $linkDescription;
    public $linkName;
    public $amount;
    public $statusCallbackUrl;


    public function attributeLabels()
    {
        return [
            'mid' => 'mid',
            'linkType' => 'linkType',
            'linkDescription' => 'linkDescription',
            'linkName' => 'linkName',
            'amount' => 'amount',
            'statusCallbackUrl' => 'statusCallbackUrl',
        ];
    }

    public function rules()
    {
        return [
            [['mid', 'linkType', 'linkDescription', 'linkName', 'amount'], 'required'],
            [['statusCallbackUrl'], 'safe'],
        ];
    }

}

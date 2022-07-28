<?php

namespace common\models\cashfree;


use yii\base\Model;

class CashFreeTransferForm extends Model
{

    public $beneId;
    public $amount; //单位元
    public $transferId;


    public function attributeLabels()
    {
        return [
            'beneId' => 'beneId',
            'amount' => 'amount',
            'transferId' => 'transferId',
        ];
    }

    public function rules()
    {
        return [
            [['name', 'amount', 'transferId'], 'required'],
        ];
    }

}

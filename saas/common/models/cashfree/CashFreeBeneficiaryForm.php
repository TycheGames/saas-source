<?php

namespace common\models\cashfree;


use yii\base\Model;

class CashFreeBeneficiaryForm extends Model
{

    public $beneId;
    public $name;
    public $email;
    public $phone;
    public $bankAccount;
    public $ifsc;
    public $address1;


    public function attributeLabels()
    {
        return [
            'beneId' => 'beneId',
            'name' => 'name',
            'email' => 'email',
            'phone' => 'phone',
            'bankAccount' => 'bankAccount',
            'ifsc' => 'ifsc',
            'address1' => 'address1',
        ];
    }

    public function rules()
    {
        return [
            [['name', 'email', 'phone', 'bankAccount', 'ifsc', 'address1', 'beneId'], 'required'],
        ];
    }

}

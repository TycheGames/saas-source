<?php

namespace frontend\models\loan;

use frontend\models\BaseForm;


class AuthLinkForm extends BaseForm
{
    public $name;
    public $email;
    public $phone;
    public $bankName;
    public $accountNumber;
    public $ifscCode;
    public $beneficiaryName;


    public function maps() : array
    {
        return [

        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'email', 'phone', 'bankName', 'accountNumber', 'ifscCode', 'beneficiaryName'], 'required'],
            [['name', 'email', 'phone', 'bankName', 'accountNumber', 'ifscCode', 'beneficiaryName'], 'trim'],
        ];
    }



}

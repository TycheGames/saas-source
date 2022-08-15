<?php

namespace common\models\pay;


class AglowAccountForm extends BaseAccountForm
{

    public $token;


    public function attributeLabels()
    {
        return [
            'token' => 'token',
        ];
    }

    public function rules()
    {
        return [
            [['token', ], 'required']
        ];
    }

}

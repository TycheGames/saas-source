<?php

namespace common\models\pay;


class AglowAccountForm extends BaseAccountForm
{

    public $token, $url, $appName, $orgName;
    public $companyName, $companyAddr, $GSTNumber;
    public $companyPhone;


    public function attributeLabels()
    {
        return [
            'token' => 'token',
            'url' => 'url',
            'appName' => 'app name',
            'orgName' => 'org name',
            'companyName' => 'companyName',
            'companyAddr' => 'companyAddr',
            'GSTNumber' => 'GSTNumber',
            'companyPhone' => 'companyPhone',
        ];
    }

    public function rules()
    {
        return [
            [['token', 'url', 'appName', 'orgName', 'companyName', 'companyAddr', 'GSTNumber', 'companyPhone'], 'required'],
            [['token', 'url', 'appName', 'orgName', 'companyName', 'companyAddr', 'GSTNumber', 'companyPhone'], 'trim']
        ];
    }

}

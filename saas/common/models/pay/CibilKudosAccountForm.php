<?php

namespace common\models\pay;


class CibilKudosAccountForm extends BaseAccountForm
{

    public $partnerId;
    public $authKey;
    public $url;


    public function attributeLabels()
    {
        return [
            'url' => '请求地址',
            'partnerId' => 'partner id',
            'authKey' => 'auth key',
        ];
    }

    public function rules()
    {
        return [
            [['url', 'partnerId', 'authKey'], 'required']];
    }

}

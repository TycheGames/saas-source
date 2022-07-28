<?php

namespace common\models\pay;


class KudosAccountForm extends BaseAccountForm
{

    public $apiV2Url;
    public $apiV2PartnerId;
    public $apiV2PartnerName;
    public $apiV2Key;
    public $companyName, $companyAddr;
    public $GSTNumber;
    public $companyPhone;

    public function attributeLabels()
    {
        return [
            'apiV2Url' => 'api V2 Url',
            'apiV2PartnerId' => 'api V2 Partner Id',
            'apiV2PartnerName' => 'api V2 Partner Name',
            'apiV2Key' => 'api V2 Key',
            'companyName' => '$companyName',
            'companyAddr' => '$companyAddr',
        ];
    }

    public function rules()
    {
        return [
            [['apiV2Url', 'apiV2PartnerId', 'apiV2PartnerName', 'apiV2Key', 'companyName', 'companyAddr', 'GSTNumber', 'companyPhone'], 'required'],
            [['apiV2Url', 'apiV2PartnerId', 'apiV2PartnerName', 'apiV2Key', 'companyName', 'companyAddr', 'GSTNumber', 'companyPhone'], 'trim'],
        ];
    }

}

<?php

namespace common\models\pay;


class KudosAccountForm extends BaseAccountForm
{

    public $apiV2Url;
    public $apiV2PartnerId;
    public $apiV2PartnerName;
    public $apiV2Key;


    public function attributeLabels()
    {
        return [
            'apiV2Url' => 'api V2 Url',
            'apiV2PartnerId' => 'api V2 Partner Id',
            'apiV2PartnerName' => 'api V2 Partner Name',
            'apiV2Key' => 'api V2 Key',
        ];
    }

    public function rules()
    {
        return [
            [['apiV2Url', 'apiV2PartnerId', 'apiV2PartnerName', 'apiV2Key'], 'required']];
    }

}

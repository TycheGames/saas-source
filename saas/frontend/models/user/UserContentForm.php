<?php


namespace frontend\models\user;


use common\models\enum\mg_user_content\UserContentType;
use frontend\models\BaseForm;

class UserContentForm extends BaseForm
{
    public $type, $data, $params, $user_id, $merchant_id;

    function maps(): array
    {
        return [
            'type'    => 'type',
            'data'    => 'data',
            'user_id' => 'user_id',
            'params'  => 'params',
        ];
    }

    public function rules()
    {
        return [
            [['type', 'data', 'params'], 'required'],
            ['type', 'in', 'range' => array_values(UserContentType::toArray()), 'message' => 'Error type status'],
        ];
    }
}
<?php


namespace frontend\models\risk;


use common\models\enum\mg_user_content\UserContentType;
use frontend\models\BaseForm;

class UserContentForm extends BaseForm
{
    public $type, $data, $app_name;

    function maps(): array
    {
        return [
            'type'     => 'type',
            'data'     => 'data',
            'app_name' => 'app_name',
        ];
    }

    public function rules()
    {
        return [
            [['type', 'data', 'app_name'], 'required'],
            ['type', 'in', 'range' => array_values(UserContentType::toArray()), 'message' => 'Error type status'],
        ];
    }
}
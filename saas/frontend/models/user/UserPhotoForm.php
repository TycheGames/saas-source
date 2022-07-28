<?php


namespace frontend\models\user;


use common\models\enum\mg_user_content\UserContentType;
use frontend\models\BaseForm;

class UserPhotoForm extends BaseForm
{
    public $content, $user_id, $date, $merchant_id;

    function maps(): array
    {
        return [
            'content' => 'content',
            'user_id' => 'user_id',
        ];
    }

    public function rules()
    {
        return [
            [['content', 'date'], 'required'],
        ];
    }
}
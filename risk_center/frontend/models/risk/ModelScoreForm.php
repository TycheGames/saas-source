<?php

namespace frontend\models\risk;

use yii\base\Model;

class ModelScoreForm extends Model
{

    public $pan_code;
    public $token;

    public function rules()
    {
        return [
            [['pan_code'], 'required'],
            ['token', 'required', 'requiredValue' => 'Fo17JCkz12yD6JrD'],
        ];
    }
}

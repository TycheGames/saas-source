<?php

namespace frontend\models\agreement;

use yii\base\Model;

/**
 * SanctionLetterApiForm is the model behind the contact form.
 */
class SanctionLetterApiForm extends Model
{
    public $orderID;
    public $token;


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['orderID', 'token'], 'required'],
            ['token', 'required', 'requiredValue' => 'S9PCByTd7XNcljj1'],
        ];
    }

}

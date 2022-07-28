<?php

namespace frontend\models\user;

use common\helpers\bank\IFSC;
use frontend\models\BaseForm;

/**
 * ContactForm is the model behind the contact form.
 */
class UserBankAccountStatusForm extends BaseForm
{
    public $id;
    public $userId;

    public function maps(): array
    {
        return [
            'id' => 'id',
            'userId'  => 'user_id',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'userId'], 'required'],
            [['id'], 'trim'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'id',
            'userId'  => 'user_id',
        ];
    }
}

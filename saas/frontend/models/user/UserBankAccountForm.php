<?php

namespace frontend\models\user;

use common\helpers\bank\IFSC;
use frontend\models\BaseForm;

/**
 * ContactForm is the model behind the contact form.
 */
class UserBankAccountForm extends BaseForm
{
    public $account;
    public $ifsc;
    public $clientInfo, $userId, $name;

    public function maps() : array
    {
        return [
            'account' => 'account',
            'ifsc' => 'ifsc',
            'clientInfo' => 'client_info',
            'userId' => 'user_id',
            'name' => 'name'
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['account', 'ifsc'], 'required'],
            [['account', 'ifsc'], 'trim'],
            [['clientInfo', 'userId', 'name'], 'safe'],
            // ['ifsc', 'validateIfsc', 'skipOnEmpty' => false, 'skipOnError' => true],
        ];
    }

    public function validateIfsc($attribute, $params)
    {
        if (!IFSC::simpleValidate($this->ifsc)) {
            $this->addError($attribute, 'IFSC code invalid');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'account' => 'account',
            'ifsc' => 'ifsc',
            'clientInfo' => 'client_info',
            'userId' => 'user_id',
            'name' => 'name'
        ];
    }



}

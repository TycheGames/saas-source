<?php

namespace frontend\models\user;

use common\helpers\Util;
use frontend\models\BaseForm;

class LoginForm extends BaseForm
{
    public $phone,  $password;
    public $clientInfo, $packageName;

    public function maps() : array
    {
        return [
            'phone' => 'phone',
            'password' => 'password',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['phone','password'], 'required'],
            [['phone','password'], 'trim'],
            ['phone', 'verifyPhone'],
            ['password', 'string', 'min' => 6, 'max' => 20],
            [['clientInfo', 'packageName'],  'safe']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'phone' => 'phone number',
            'password' => 'password',
            'sourceId' => 'source id',
            'clientInfo' => 'client info'
        ];
    }


    public function verifyPhone($attribute, $params)
    {
        if(!Util::verifyPhone($this->$attribute)){
            $this->addError($attribute, 'please enter a valid mobile phone number');
        }
        return;
    }


}

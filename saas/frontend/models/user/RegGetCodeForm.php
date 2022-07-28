<?php

namespace frontend\models\user;

use common\helpers\Util;
use frontend\models\BaseForm;


class RegGetCodeForm extends BaseForm
{
    public $phone;
    public $packageName;

    public function maps() : array
    {
        return [
            'phone' => 'phone',
            'sourceId' => 'source id'
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['phone', 'required'],
            ['phone', 'trim'],
            ['phone', 'validatePhone'],
            [['sourceId', 'packageName'], 'safe']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'phone' => 'phone number',
            'sourceId' => 'source id',
            'packageName' => 'package name'
        ];
    }


    public function validatePhone($attribute, $params)
    {
        if(strlen($this->$attribute) == 12 && substr($this->$attribute,0 , 2) == '91')
        {
            $this->$attribute = substr($this->$attribute, 2, 10);
        }

        if(strlen($this->$attribute) != 10)
        {
            $this->addError($attribute, 'please enter a valid mobile phone number');
        }
    }


}

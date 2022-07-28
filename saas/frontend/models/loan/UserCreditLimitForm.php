<?php

namespace frontend\models\loan;

use common\models\product\ProductSetting;
use frontend\models\BaseForm;


/**
 * ContactForm is the model behind the contact form.
 */
class UserCreditLimitForm extends BaseForm
{
    public $panNo;
    public $packageName;
    public $token;

    public function maps(): array
    {
        return [
            'panNo'       => 'panNo',
            'packageName' => 'packageName',
            'token'       => 'token',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['panNo', 'packageName'], 'required'],
            ['token', 'required', 'requiredValue' => 'S9PCByTd7XNcljj1'],
            [['panNo', 'packageName'], 'trim'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'panNo'       => 'panNo',
            'packageName' => 'packageName',
            'token'       => 'token',
        ];
    }
}

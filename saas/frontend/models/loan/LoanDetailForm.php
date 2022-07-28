<?php

namespace frontend\models\loan;

use frontend\models\BaseForm;

/**
 * LoanDetailForm is the model behind the contact form.
 */
class LoanDetailForm extends BaseForm
{
    public $id;
    public $userId;
    public $hostInfo;

    public function maps() : array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['id', 'required'],
            ['id', 'trim'],
            ['id', 'integer'],
            [['userId', 'hostInfo'], 'safe']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'order id',
            'userId' => 'user id',
            'hostInfo' => 'hostInfo',
        ];
    }



}

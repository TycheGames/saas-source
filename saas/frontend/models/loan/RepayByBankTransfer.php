<?php

namespace frontend\models\loan;

use frontend\models\BaseForm;


class RepayByBankTransfer extends BaseForm
{
    public $id;
    public $userId;


    public function maps() : array
    {
        return [
            'id' => 'id',
            'userId' => 'userId',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'userId'], 'required'],
            [['id', 'userId'], 'trim'],
            [['id', 'userId'], 'integer', 'min' => 1],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'id',
            'userId' => 'userId'
        ];
    }



}

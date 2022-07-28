<?php

namespace common\models\cashfree;


use yii\base\Model;

class CashFreePaymentUrlForm extends Model
{

    public $orderId;
    public $orderAmount; //单位元
    public $orderCurrency = 'INR';
    public $customerEmail;
    public $customerName;
    public $customerPhone;
    public $returnUrl;
    public $notifyUrl;


    public function attributeLabels()
    {
        return [
            'orderId' => 'orderId',
            'orderAmount' => '订单金额，单位元',
            'orderCurrency' => '货币单位，INR',
            'customerEmail' => '用户邮箱',
            'customerName' => '用户姓名',
            'customerPhone' => '用户手机号',
            'returnUrl' => 'h5回跳地址',
            'notifyUrl' => '服务器回调地址',
        ];
    }

    public function rules()
    {
        return [
            [['orderId', 'orderAmount', 'customerEmail', 'customerName',
                'customerPhone', 'returnUrl', 'notifyUrl'], 'required'],
            [['orderCurrency'], 'safe']
        ];
    }

}

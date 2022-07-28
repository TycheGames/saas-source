<?php

namespace frontend\models\loan;

use common\models\financial\FinancialPaymentOrder;
use frontend\models\BaseForm;

/**
 * ContactForm is the model behind the contact form.
 */
class RepaymentApplyForm extends BaseForm
{
    public $amount;
    public $orderId;
    public $serviceType; //新增服务类型 1 - razorpay  2 - cashfree 3-paytm
    public $host; //获取请求host
    public $userID;
    public $customerEmail;
    public $customerPhone;
    public $customerName;
    public $customerUpiAccount;
    public $paymentType; //支付类型 0正常 1延期部分还款 2延期部分还款并减免滞纳金 3展期
    public $paymentChannel; //支付通道serviceType为sifang时必填  印度市场的代收通道提供 5.Paytm UPI 7. Phonepe UPI 8. IndiaBank

    public function maps(): array
    {
        return [
            'amount'             => 'amount',
            'orderId'            => 'order_id',
            'serviceType'        => 'service type',
            'host'               => 'host',
            'userID'             => 'user id',
            'customerEmail'      => 'customerEmail',
            'customerPhone'      => 'customerPhone',
            'customerName'       => 'customerName',
            'customerUpiAccount' => 'customerUpiAccount',
            'paymentType'        => 'paymentType',
            'paymentChannel'     => 'paymentChannel',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['amount', 'orderId', 'serviceType', 'userID'], 'required'],
            [['amount', 'orderId'], 'trim'],
            [['serviceType'], 'integer'],
            ['paymentType', 'in', 'range' => array_keys(FinancialPaymentOrder::$payment_type_map)],
            ['serviceType', 'in', 'range' => array_keys(FinancialPaymentOrder::$service_type_map)],
            [['customerEmail', 'customerPhone', 'host', 'customerName', 'customerUpiAccount', 'paymentChannel'], 'safe'],
            ['serviceType', 'validateServiceType'],
        ];
    }

    public function validateServiceType() {
        if($this->serviceType == FinancialPaymentOrder::SERVICE_TYPE_SIFANG
            && !in_array($this->paymentChannel, [5,7,8])){
            $this->addError('paymentChannel', 'paymentChannel is error');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'amount'  => 'amount',
            'orderId' => 'order id',
            'serviceType' => 'service type',
            'host' => 'host',
            'userID' => 'user id',
            'customerEmail' => 'customerEmail',
            'customerPhone' => 'customerPhone',
            'paymentType' => 'paymentType',
        ];
    }
}

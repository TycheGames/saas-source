<?php

namespace common\models\pay;


class RazorpayAccountForm extends BaseAccountForm
{

    public $baseUri;
    public $payoutKeyId;
    public $payoutKeySecret;
    public $paymentKeyId;
    public $paymentSecret;
    public $paymentDomain;
    public $webhooksSecret;
    public $accountNumber;
    public $mpursePartnerId;
    public $mpurseKey;
    public $sifangUserId;
    public $sifangApiKey;
    public $mojoApiKey;
    public $mojoAuthToken;
    public $mojoSalt;

    public $jpayAppKey;
    public $jpayAppSecret;

    public $qimingKeyId;
    public $qimingKeySecret;
    public $qimingTenantId;

    public $rpayKeyId;
    public $rpayKeySecret;

    public $quanqiupayMchId;
    public $quanqiupayToken;

    public function attributeLabels()
    {
        return [
            'baseUri' => '请求地址',
            'payoutKeyId' => '放款Key',
            'payoutKeySecret' => '放款Secret',
            'accountNumber' => 'account number',
            'paymentKeyId' => '还款Key',
            'paymentSecret' => '还款Secret',
            'paymentDomain' => '支付网关域名',
            'webhooksSecret' => '回调Secret',
            'mpursePartnerId' => 'mpurse partnerID',
            'mpurseKey' => 'mpurse Key',
            'sifangUserId' => 'sifang UserId',
            'sifangApiKey' => 'sifang ApiKey',
            'mojoApiKey' => 'mojo Api Key',
            'mojoAuthToken' => 'mojo Auth Token',
            'mojoSalt' => 'mojo Salt',
            'qimingKeyId' => 'qiming Key Id',
            'qimingKeySecret' => 'qiming Key Secret',
            'qimingTenantId' => 'qiming TenantId',
            'rpayKeyId' => 'rpay Key Id',
            'rpayKeySecret' => 'rpay Key Secret',
            'quanqiupayMchId' => 'quanqiupay Mch Id',
            'quanqiupayToken' => 'quanqiupay Token',
        ];
    }

    public function rules()
    {
        return [
            [['baseUri', 'payoutKeyId', 'payoutKeySecret', 'paymentKeyId',
                'paymentSecret', 'webhooksSecret', 'accountNumber', 'paymentDomain',
                'mpursePartnerId', 'mpurseKey', 'sifangUserId', 'sifangApiKey',
              'jpayAppKey', 'jpayAppSecret',
              'mojoApiKey', 'mojoAuthToken', 'mojoSalt',
              'qimingKeyId', 'qimingKeySecret', 'qimingTenantId',
              'rpayKeyId', 'rpayKeySecret',
              'quanqiupayMchId', 'quanqiupayToken',
            ], 'required']
        ];
    }

}

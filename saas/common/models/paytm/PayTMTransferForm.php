<?php

namespace common\models\paytm;


use yii\base\Model;

class PayTMTransferForm extends Model
{

    public $orderId;
    public $subwalletGuid; //单位元
    public $amount;
    public $purpose;
    public $date;
//    public $transferMode;
    public $beneficiaryAccount;
    public $beneficiaryIFSC;
//    public $beneficiaryName;


    public function attributeLabels()
    {
        return [
            'orderId' => '支付订单号',
            'subwalletGuid' => 'subwalletGuid',
            'amount' => '放款金额 单位元',
            'purpose' => '用途 OTHERS',
            'date' => '放款日 Y-m-d',
//            'transferMode' => '支付方式:IMPS、NEFT、UPI',
            'beneficiaryAccount' => '收款人账号',
            'beneficiaryIFSC' => '收款人ifsc',
//            'beneficiaryName' => '收款人姓名',
        ];
    }

    public function rules()
    {
        return [
            [['orderId', 'subwalletGuid', 'amount', 'purpose', 'date',
//                'transferMode',
                'beneficiaryAccount', 'beneficiaryIFSC',
//                'beneficiaryName'
            ], 'required'],
        ];
    }

}

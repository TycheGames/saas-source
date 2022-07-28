<?php

namespace common\models\pay;

use yii\base\Model;

class LoanPayForm extends Model
{

    public $beneName;
    public $beneAccNo;
    public $bankName;
    public $beneIFSC;
    public $txnId;
    public $amount;
    public $remark;
    public $userID;
    public $beneMobile;


    public function attributeLabels()
    {
        return [
            'beneName' => '收款人',
            'beneAccNo' => '收款账号',
            'bankName' => '银行名',
            'beneIFSC' => '收款IFSC',
            'txnId' => '唯一订单号',
            'amount' => '放款金额(单位分)',
            'remark' => '备注',
            'userID' => '用户ID',
            'beneMobile' => '收款手机号',
        ];
    }

    public function rules()
    {
        return [
            [['beneName', 'beneAccNo', 'beneIFSC', 'amount', 'txnId', 'userID', 'beneMobile'], 'required'],
            [['remark', 'bankName'], 'safe'],
        ];
    }

}

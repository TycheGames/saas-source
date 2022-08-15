<?php

namespace frontend\models\risk;

use yii\base\Model;

class LoanCollectionRecordForm extends Model
{

    public $order_id;
    public $app_name;
    public $user_id;
    public $request_id;
    public $pan_code;
    public $contact_type;
    public $order_level;
    public $operate_type;
    public $operate_at;
    public $promise_repayment_time;
    public $risk_control;
    public $is_connect;


    public function rules()
    {
        return [
            [['order_id', 'app_name', 'user_id', 'request_id', 'pan_code', 'contact_type',
              'order_level', 'operate_type', 'operate_at', 'promise_repayment_time',
              'risk_control', 'is_connect'], 'required'],
        ];
    }
}

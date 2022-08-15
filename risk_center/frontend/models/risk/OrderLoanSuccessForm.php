<?php

namespace frontend\models\risk;

use yii\base\Model;

class OrderLoanSuccessForm extends Model
{

    public $order_id;
    public $user_id;
    public $app_name;
    public $loan_time;
    public $plan_repayment_time;
    public $principal;
    public $interests;
    public $cost_fee;
    public $total_money;
    public $data_version;


    public function rules()
    {
        return [
            [['order_id', 'user_id', 'app_name', 'loan_time', 'plan_repayment_time',
                'principal', 'interests', 'cost_fee', 'total_money', 'data_version'], 'required']
        ];
    }
}

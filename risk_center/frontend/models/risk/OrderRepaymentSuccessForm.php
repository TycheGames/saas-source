<?php

namespace frontend\models\risk;

use yii\base\Model;

class OrderRepaymentSuccessForm extends Model
{

    public $order_id;
    public $user_id;
    public $app_name;
    public $closing_time;
    public $true_total_money;
    public $overdue_fee;
    public $is_overdue;
    public $overdue_day;
    public $total_money;
    public $data_version;


    public function rules()
    {
        return [
            [['order_id', 'user_id', 'app_name', 'closing_time', 'true_total_money',
                'overdue_fee', 'is_overdue', 'overdue_day', 'total_money', 'data_version'], 'required']
        ];
    }
}

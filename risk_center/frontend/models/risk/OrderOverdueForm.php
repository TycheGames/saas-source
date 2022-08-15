<?php

namespace frontend\models\risk;

use yii\base\Model;

class OrderOverdueForm extends Model
{

    public $order_id;
    public $user_id;
    public $app_name;
    public $total_money;
    public $overdue_fee;
    public $overdue_day;
    public $data_version;


    public function rules()
    {
        return [
            [['order_id', 'user_id', 'app_name', 'total_money', 'overdue_fee', 'overdue_day', 'data_version'], 'required']
        ];
    }
}

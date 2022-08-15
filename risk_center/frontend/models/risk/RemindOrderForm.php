<?php

namespace frontend\models\risk;

use yii\base\Model;

class RemindOrderForm extends Model
{

    public $order_id;
    public $app_name;
    public $user_id;
    public $request_id;
    public $status;
    public $remind_return;
    public $payment_after_days;
    public $remind_count;
    public $created_at;
    public $updated_at;


    public function rules()
    {
        return [
            [['order_id', 'app_name', 'user_id', 'request_id',
              'created_at', 'updated_at'], 'required'],
            [['status', 'remind_return', 'payment_after_days', 'remind_count'], 'safe'],
        ];
    }
}

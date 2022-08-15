<?php

namespace frontend\models\risk;

use yii\base\Model;

class RemindLogForm extends Model
{

    public $order_id;
    public $app_name;
    public $user_id;
    public $request_id;
    public $source;
    public $remind_return;
    public $payment_after_days;
    public $created_at;
    public $updated_at;


    public function rules()
    {
        return [
            [['order_id', 'app_name', 'user_id', 'request_id', 'source',
              'created_at', 'updated_at'], 'required'],
            [['remind_return', 'payment_after_days'], 'safe'],
        ];
    }
}

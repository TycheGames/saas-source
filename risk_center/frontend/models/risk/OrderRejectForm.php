<?php

namespace frontend\models\risk;

use yii\base\Model;

class OrderRejectForm extends Model
{

    public $order_id;
    public $user_id;
    public $app_name;
    public $status;
    public $reject_reason;
    public $data_version;

    public function rules()
    {
        return [
            [['order_id', 'user_id', 'app_name', 'status', 'data_version'], 'required'],
            [['reject_reason'], 'safe']
        ];
    }
}

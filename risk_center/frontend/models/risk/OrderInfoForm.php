<?php

namespace frontend\models\risk;

use yii\base\Model;

class OrderInfoForm extends Model
{

    public $is_first;
    public $is_all_first;
    public $periods;
    public $is_external_first;
    public $is_external;
    public $external_app_name;
    public $day_rate;
    public $overdue_rate;
    public $cost_rate;
    public $order_time;
    public $principal;
    public $loan_amount;
    public $product_name;
    public $product_id;
    public $product_source;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['is_first', 'is_all_first', 'periods', 'is_external_first', 'is_external',
                'day_rate', 'overdue_rate',  'cost_rate', 'order_time', 'principal', 'loan_amount'], 'required'],
            [['external_app_name', 'product_name', 'product_id', 'product_source'], 'safe'],
        ];
    }

}

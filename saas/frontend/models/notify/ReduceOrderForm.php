<?php


namespace frontend\models\notify;


use frontend\models\BaseForm;

/**
 * Class PushOrderNotifyForm
 * @package frontend\models\notify
 *
 * @property string $order_id
 * @property string $user_id
 * @property string $app_name
 */
class ReduceOrderForm extends BaseForm
{
    public $app_name;
    public $user_id;
    public $order_id;

    function maps(): array
    {
        return [];
    }

    public function rules()
    {
        return [
            [['order_id', 'user_id', 'app_name'], 'required'],
        ];
    }
}
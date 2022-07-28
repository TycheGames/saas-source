<?php


namespace frontend\models\notify;


use frontend\models\BaseForm;

/**
 * Class PushOrderNotifyForm
 * @package frontend\models\notify
 *
 * @property int $order_id
 * @property string $data
 * @property string $token
 */
class PushOrderNotifyForm extends BaseForm
{
    public $token;
    public $data;
    public $order_id;

    function maps(): array
    {
        return [];
    }

    public function rules()
    {
        return [
            [['order_id', 'data'], 'required'],
            ['token', 'required', 'requiredValue' => 'QmFu6OZb35rfRaHl'],
        ];
    }
}
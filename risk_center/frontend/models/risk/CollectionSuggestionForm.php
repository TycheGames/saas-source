<?php

namespace frontend\models\risk;

use yii\base\Model;

class CollectionSuggestionForm extends Model
{

    public $app_name;
    public $user_id;
    public $order_id;
    public $pan_code;
    public $phone;
    public $szlm_query_id;


    public function rules()
    {
        return [
            [['szlm_query_id'], 'safe'],
            [['phone', 'pan_code', 'app_name', 'order_id', 'user_id'], 'required'],
        ];
    }
}

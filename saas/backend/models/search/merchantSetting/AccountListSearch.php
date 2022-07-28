<?php

namespace backend\models\search\merchantSetting;

use yii\base\Model;

class AccountListSearch extends Model
{
    public $service_type;
    public $merchant_id;

	public function rules(){
		return [
			[['service_type', 'merchant_id'], 'integer']
		];
	}


}
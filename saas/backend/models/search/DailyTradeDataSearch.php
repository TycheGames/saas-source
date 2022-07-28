<?php

namespace backend\models\search;

use yii\base\Model;


class DailyTradeDataSearch extends Model
{

    public $add_start;
    public $add_end;
    public $loan_type;
    public $repay_type;
    public $data_type;
    public $app_market;
    public $contrast_type;
    public $merchant_id;
    public $package_name;



	public function rules(){
		return [
            [['contrast_type'],'integer'],
            [['add_start','add_end', 'app_market', 'package_name', 'merchant_id'], 'string'],
            [['app_market'], 'trim'],
            ['add_start', 'default', 'value' => date('Y-m-d', time() - 7 * 86400)],
            ['add_end', 'default', 'value' => date('Y-m-d', time())]
		];
	}

    public function search($params){
        $condition = ['AND'];
        if (($this->load($params, '') && $this->validate())) {

            if(!empty($this->contrast_type) && $this->add_start != $this->add_end)
            {
                $this->add_start = date('Y-m-d');
                $this->add_end = date('Y-m-d');
            }

            if(!empty($this->add_start))
            {
                $condition[] = ['>=', 'date', $this->add_start];
            }
            if(!empty($this->add_end))
            {
                $condition[] = ['<=', 'date', $this->add_end];
            }

            if(!empty($this->app_market))
            {
                $condition[] = ['app_market' => $this->app_market];
            }

            if(!empty($this->package_name))
            {
                $condition[] = ['package_name' => $this->package_name];
            }

            if(!empty($this->merchant_id))
            {
                $condition[] = ['merchant_id' => $this->merchant_id];
            }
        }

        return $condition;
    }

}
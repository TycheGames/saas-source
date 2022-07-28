<?php

namespace backend\models\search;

use yii\base\Model;


class SlowListSearch extends Model
{

    public $id;
    public $title;
    public $use_case;
    public $status;



	public function rules(){
		return [
            [['id','status'],'integer'],
            [['title','use_case'], 'string'],
            [['title','use_case'], 'trim'],
		];
	}

    public function search($params){
        $condition = [];
        if (($this->load($params, '') && $this->validate())) {
            if(!empty($this->id))
            {
                $condition[] = ['id' => $this->id];
            }

            if(!empty($this->title))
            {
                $condition[] = ['like', 'title', $this->title];
            }

            if(!empty($this->use_case))
            {
                $condition[] = ['use_case' => $this->use_case];
            }

            if(!empty($this->status))
            {
                $condition[] = ['status' => $this->status];
            }
        }

        return $condition;
    }

}
<?php

namespace callcenter\models\search;


use yii\base\Model;

class CollectionOperateListSearch extends Model
{

    public $admin_user_name;
    public $admin_user_id;
    public $route;
    public $request_param;
    public $visit_start_time;
    public $visit_end_time;
    public $url;



	public function rules(){
		return [
            [
                ['admin_user_id'],
                'integer'
            ],
            [
                ['admin_user_name', 'route', 'request_param', 'visit_start_time', 'visit_end_time', 'url'],
                'string'
            ],
            [
                ['admin_user_name', 'route', 'request_param'],
                'trim'
            ],
		];
	}

    public function search($params){
	    $condition = [];

        if (($this->load($params, '') && $this->validate())) {
            if(!empty($this->admin_user_name))
            {
                $condition[] = ['like', 'admin_user_name', $this->admin_user_name];
            }
            if(!empty($this->admin_user_id))
            {
                $condition[] = ['admin_user_id' => $this->admin_user_id];
            }
            if(!empty($this->url))
            {
                $condition[] = ['like', 'route', $this->url];
            }
            if(!empty($this->request_param))
            {
                $condition[] = ['like', 'request_params', $this->request_param];
            }
            if(!empty($this->visit_start_time))
            {
                $condition[] = ['>=', 'created_at', strtotime($this->visit_start_time)];
            }
            if(!empty($this->visit_end_time))
            {
                $condition[] = ['<=', 'created_at', strtotime($this->visit_end_time)];
            }
        }


        return $condition;
    }

}
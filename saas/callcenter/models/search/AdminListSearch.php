<?php

namespace callcenter\models\search;


use yii\base\Model;

class AdminListSearch extends Model
{

    public $phone;
    public $username;
    public $role;
    public $merchant_id;
    public $created_user;
    public $open_status;



	public function rules(){
		return [
            [
                ['merchant_id','open_status'],
                'integer'
            ],
            [
                ['phone', 'username', 'role', 'created_user'],
                'string'
            ],
            [
                ['phone', 'username', 'role', 'created_user'],
                'trim'
            ],
		];
	}

    public function search($params){
	    $condition = [];
        if (($this->load($params, '') && $this->validate())) {
            if (!empty($this->phone)) {
                $condition[] = ['phone' => $this->phone];
            }

            if(!empty($this->username))
            {
                $condition[] = ['like', 'username',$this->username ];
            }

            if(!empty($this->role))
            {
                $condition[] = ['role' => $this->role];
            }

            if (isset($this->merchant_id) && $this->merchant_id != '') {
                $condition[] = ['merchant_id' => $this->merchant_id];
            }

            if (!empty($this->created_user)) {
                $condition[] = ['created_user' => $this->created_user];
            }

            if (isset($this->open_status) && $this->open_status != '') {
                $condition[] = ['open_status' => $this->open_status];
            }
        }


        return $condition;
    }

}
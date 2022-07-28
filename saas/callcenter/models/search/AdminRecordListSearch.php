<?php

namespace callcenter\models\search;


use yii\base\Model;

class AdminRecordListSearch extends Model
{

    public $id;
    public $collection_name;
    public $cuishou_real_name;
    public $order_level;
    public $outside;
    public $loan_group;
    public $group_game;
    public $status;
    public $operate_tp;
    public $risk_control;
    public $is_connect;
    public $order_id;
    public $start_time;
    public $end_time;


	public function rules(){
		return [
            [
                ['id', 'order_level', 'outside', 'loan_group', 'status', 'operate_tp', 'risk_control', 'is_connect', 'order_id'],
                'integer'
            ],
            [
                ['collection_name', 'cuishou_real_name', 'start_time', 'end_time'],
                'string'
            ],
            [
                ['id', 'collection_name', 'cuishou_real_name'],
                'trim'
            ],
            [['group_game'], 'safe']
		];
	}

    public function search($params){
	    $condition = [];
        if (($this->load($params, '') && $this->validate())) {
            if (!empty($this->id))
            {
                $condition[] = ['A.id' => $this->id];
            }

            if(!empty($this->collection_name))
            {
                $condition[] = ['B.username' => $this->collection_name];
            }

            if(!empty($this->cuishou_real_name))
            {
                $condition[] = ['like', 'B.real_name like', $this->cuishou_real_name];
            }


            if(!empty($this->order_level))
            {
                $condition[] = ['A.order_level' => $this->order_level];
            }


            if(!empty($this->outside))
            {
                $condition[] = ['B.outside' => $this->outside];
            }


            if(!empty($this->loan_group))
            {
                $condition[] = ['B.group' => $this->loan_group];
            }


            if(!empty($this->group_game))
            {
                if(is_array($this->group_game)){
                    $groupGameArr = ['or'];
                    foreach ($this->group_game as $k => $v) {
                        $groupGameArr[] = ['B.group_game' => $v];
                    }
                    $condition[] = $groupGameArr;
                }else{
                    $condition[] = ['B.group_game' => $this->group_game];
                }
            }

            if(!empty($this->status))
            {
                $condition[] = ['A.order_state' => $this->status];
            }

            if(!empty($this->operate_tp))
            {
                $condition[] = ['A.operate_type' => $this->operate_tp];
            }

            if(!empty($this->risk_control))
            {
                $condition[] = ['A.risk_control' => $this->risk_control];
            }

            if(!empty($this->is_connect))
            {
                $condition[] = ['A.is_connect' => $this->is_connect];
            }

            if(!empty($this->order_id))
            {
                $condition[] = ['A.order_id' => $this->order_id];
            }

            if(!empty($this->start_time))
            {
                $condition[] = ['>=', 'A.created_at', strtotime($this->start_time)];
            }

            if(!empty($this->end_time))
            {
                $condition[] = ['<=', 'A.created_at', strtotime($this->end_time) + 86400];
            }
        }


        return $condition;
    }

}
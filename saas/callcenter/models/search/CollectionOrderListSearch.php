<?php

namespace callcenter\models\search;

use callcenter\models\loan_collection\LoanCollectionOrder;
use common\models\user\UserActiveTime;
use yii\base\Model;

class CollectionOrderListSearch extends Model
{

    public $order_id;
    public $loan_collection_order_id;
    public $user_id;
    public $is_first;
    public $current_overdue_group;
    public $s_last_collection_time;
    public $e_last_collection_time;
    public $s_dispatch_time;
    public $e_dispatch_time;
    public $s_dispatch_outside_time;
    public $e_dispatch_outside_time;
    public $s_input_time;
    public $e_input_time;
    public $s_true_pay_time;
    public $e_true_pay_time;
    public $cuishou_name;
    public $cuishou_real_name;
    public $group_game;
    public $admin_user_id;
    public $loan_phone;
    public $name;
    public $current_overdue_level;
    public $status;
    public $outside;
    public $merchant_id;
    public $customer_type;
    public $overdue_day;
    public $is_finish;
    public $is_finish_principal_interest;
    public $willing_blinker;

	public function rules(){
		return [
            [
                ['order_id','loan_collection_order_id', 'user_id', 'is_first', 'current_overdue_group',
                'admin_user_id', 'current_overdue_level', 'status', 'outside', 'merchant_id', 'customer_type',
                'is_finish', 'is_finish_principal_interest'
                ],
                'integer'
            ],
            [
                ['s_last_collection_time','e_last_collection_time','s_dispatch_time','e_dispatch_time','s_dispatch_outside_time',
                'e_dispatch_outside_time','s_input_time','e_input_time', 's_true_pay_time', 'e_true_pay_time', 'cuishou_name',
                'cuishou_real_name', 'group_game', 'loan_phone', 'name', 'overdue_day'
                ],
                'string'
            ],
            [
                ['s_last_collection_time','e_last_collection_time','s_dispatch_time','e_dispatch_time','s_dispatch_outside_time',
                    'e_dispatch_outside_time','s_input_time','e_input_time', 's_true_pay_time', 'e_true_pay_time', 'cuishou_name',
                    'cuishou_real_name', 'group_game', 'loan_phone', 'name', 'overdue_day', 'order_id','loan_collection_order_id',
                    'user_id'
                ],
                'trim'
            ],
            [['willing_blinker'], 'safe']
		];
	}

    public function search($params){
	    $condition = [];
        if (($this->load($params, '') && $this->validate())) {
            if(!empty($this->order_id)) {
                $condition[] = ['A.user_loan_order_id' => $this->order_id];
            }
            if(!empty($this->loan_collection_order_id))
            {
                $condition[] = ['A.id' => $this->loan_collection_order_id];
            }
            if(!empty($this->user_id))
            {
                $condition[] = ['A.user_id' => $this->user_id];
            }
            if(!empty($this->is_first))
            {
                $condition[] = ['O.is_first' => $this->is_first];
            }
            //催收分组：
            if(!empty($this->current_overdue_group))
            {
                $condition[] = ['A.current_overdue_group' => $this->current_overdue_group];
            }
            //催收时间过滤
            if(!empty($this->s_last_collection_time))
            {
                $condition[] = ['>=', 'A.last_collection_time', strtotime($this->s_last_collection_time)];
            }
            if(!empty($this->e_last_collection_time))
            {
                $condition[] = ['<=', 'A.last_collection_time', strtotime($this->e_last_collection_time)];
            }
            //派单时间过滤
            if(!empty($this->s_dispatch_time))
            {
                $condition[] = ['>=', 'A.dispatch_time', strtotime($this->s_dispatch_time)];
            }
            if(!empty($this->e_dispatch_time))
            {
                $condition[] = ['<=', 'A.dispatch_time', strtotime($this->e_dispatch_time)];
            }

            //派单到公司时间过滤
            if(!empty($this->s_dispatch_outside_time))
            {
                $condition[] = ['>=', 'A.dispatch_outside_time', strtotime($this->s_dispatch_outside_time)];
            }
            if(!empty($this->e_dispatch_outside_time))
            {
                $condition[] = ['<=', 'A.dispatch_time', strtotime($this->e_dispatch_outside_time)];
            }

            //入催时间
            if(!empty($this->s_input_time))
            {
                $condition[] = ['>=', 'A.created_at', strtotime($this->s_input_time)];
            }
            if(!empty($this->e_input_time))
            {
                $condition[] = ['<=', 'A.created_at', strtotime($this->e_input_time)];
            }

            //订单还款关闭时间
            if(!empty($this->s_true_pay_time))
            {
                $condition[] = ['>=', 'D.closing_time', strtotime($this->s_true_pay_time)];
            }
            if(!empty($this->e_true_pay_time))
            {
                $condition[] = ['<=', 'D.closing_time', strtotime($this->e_true_pay_time)];
            }
            //催收人名字
            if(!empty($this->cuishou_name))
            {
                $condition[] = ['B.username' => $this->cuishou_name];
            }
            //催收人真实名字
            if(!empty($this->cuishou_real_name))
            {
                $condition[] = ['like', 'B.real_name', $this->cuishou_real_name];
            }
            if(!empty($this->group_game))
            {
                $condition[] = ['B.group_game' => $this->group_game];
            }
            if(!empty($this->admin_user_id))
            {
                $condition[] = ['A.current_collection_admin_user_id' => $this->admin_user_id];
            }
            if(!empty($this->loan_phone))
            {
                $condition[] = ['C.phone' => $this->loan_phone];
            }
            if(!empty($this->name))
            {
                $condition[] = ['C.name' => $this->name];
            }
            if(!empty($this->current_overdue_level))
            {
                $condition[] = ['A.current_overdue_level' => $this->current_overdue_level];
            }
            if(!empty($this->status))
            {
                $condition[] = ['A.status' => $this->status];
            }
            if(!empty($this->outside))
            {
                $condition[] = ['A.outside' => $this->outside];
            }
            if(!empty($this->merchant_id))
            {
                $condition[] = ['A.merchant_id' => $this->merchant_id];
            }
            if(is_numeric($this->customer_type))
            {
                $condition[] = ['C.customer_type' => $this->customer_type];
            }
            if(!empty($this->overdue_day) || 0 === $this->overdue_day)
            {
                $overdue_day = explode('-', $this->overdue_day);
                if(count($overdue_day) > 1){
                    $condition[] = ['>=', 'D.overdue_day', $overdue_day[0]];
                    $condition[] = ['<=', 'D.overdue_day', $overdue_day[1]];
                }else{
                    $condition[] = ['D.overdue_day' => $overdue_day[0]];
                }
            }

            if(is_numeric($this->is_finish) && in_array($this->is_finish, [0,1]))
            {
                if(1 == $this->is_finish){
                    $condition[] = ['A.status' => LoanCollectionOrder::STATUS_COLLECTION_FINISH];
                }else{
                    $condition[] = ['A.status' => LoanCollectionOrder::$not_end_status];
                }
            }

            if(is_numeric($this->is_finish_principal_interest) && in_array($this->is_finish_principal_interest, [0,1]))
            {
                if(1 == $this->is_finish_principal_interest){
                    $condition[] = 'D.true_total_money >= D.principal + D.interests';
                }else{
                    $condition[] = 'D.true_total_money < D.principal + D.interests';
                }
            }
            if(!empty($this->willing_blinker)){
                $condition[] = UserActiveTime::colorBlinkerConditionNew($this->willing_blinker,'G.','D.');
            }
        }


        return $condition;
    }

}
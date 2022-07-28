<?php

namespace backend\models\search;

use common\helpers\CommonHelper;
use yii\base\Model;


class FinancialLoanListSearch extends Model
{

    public $username;
    public $phone;
    public $user_id;
    public $rid;
    public $loan_amount_min;
    public $loan_amount_max;
    public $trade_no;
    public $order_id;
    public $status;
    public $begintime;
    public $endtime;
    public $updated_at_begin;
    public $updated_at_end;
    public $merchant_id;
    public $order_uuid;


	public function rules(){
		return [
            [['loan_amount_min','loan_amount_max', 'status', 'merchant_id'],'integer'],
            [['username','phone','user_id','rid','trade_no','order_id','begintime','endtime','updated_at_begin', 'updated_at_end', 'order_uuid'], 'string'],
            [['username','phone','user_id','rid','trade_no','order_id','loan_amount_min','loan_amount_max', 'order_uuid'], 'trim'],
		];
	}

    public function search($params){
        $condition = [];
        if (($this->load($params, '') && $this->validate())) {
            if(!empty($this->username))
            {
                $condition[] = ['like', 'p.name', $this->username];
            }

            if(!empty($this->phone))
            {
                $condition[] = ['p.phone' => $this->phone];
            }

            if(!empty($this->user_id))
            {
                $condition[] = ['l.user_id' => CommonHelper::idDecryption($this->user_id)];
            }

            if(!empty($this->rid))
            {
                $condition[] = ['l.id' => CommonHelper::idDecryption($this->rid)];
            }


            if(!empty($this->loan_amount_min))
            {
                $condition[] = ['>=', 'u.amount', $this->loan_amount_min * 100];
            }

            if(!empty($this->loan_amount_max))
            {
                $condition[] = ['<=', 'u.amount', $this->loan_amount_max * 100];
            }

            if(!empty($this->trade_no))
            {
                $condition[] = ['l.trade_no' => $this->trade_no];
            }

            if(!empty($this->order_id))
            {
                $condition[] = ['l.business_id' => CommonHelper::idDecryption($this->order_id)];
            }

            if(!empty($this->status))
            {
                $condition[] = ['l.status' => $this->status];
            }


            if(!empty($this->begintime))
            {
                $condition[] = ['>=', 'l.created_at', strtotime($this->begintime)];
            }

            if(!empty($this->endtime))
            {
                $condition[] = ['<', 'l.created_at', strtotime($this->endtime)];
            }


            if(!empty($this->updated_at_begin))
            {
                $condition[] = ['>=', 'l.success_time', strtotime($this->updated_at_begin)];
            }

            if(!empty($this->updated_at_end))
            {
                $condition[] = ['<', 'l.success_time', strtotime($this->updated_at_end)];
            }

            if (!empty($this->merchant_id)) {
                $condition[] = ['l.merchant_id' => $this->merchant_id];
            }

            if(!empty($this->order_uuid))
            {
                $condition[] = ['l.order_id' => $this->order_uuid];
            }
        }

        return $condition;
    }

}
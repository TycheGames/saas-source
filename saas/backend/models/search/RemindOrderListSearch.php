<?php

namespace backend\models\search;

use backend\models\AdminUser;
use backend\models\remind\RemindOrder;
use yii\base\Model;


class RemindOrderListSearch extends Model
{

    public $phone;
    public $remind_status;
    public $repayment_status;
    public $reminder_name;
    public $is_first;
    public $customer_type;
    public $dispatch_status;
    public $begintime_dispatch;
    public $endtime_dispatch;
    public $begintime_completed;
    public $endtime_completed;
    public $begintime_plan;
    public $endtime_plan;
    public $remind_reach;
    public $remind_return;

	public function rules(){
		return [
            [['remind_status', 'repayment_status', 'is_first', 'customer_type', 'dispatch_status', 'remind_reach', 'remind_return'],'integer'],
            [['phone', 'reminder_name', 'begintime_completed', 'endtime_completed', 'begintime_plan', 'endtime_plan','begintime_dispatch','endtime_dispatch'], 'string'],
		];
	}

    public function search($params){
	    $condition = [];
        if (($this->load($params, '') && $this->validate())) {
            if(!empty($this->phone))
            {
                $condition[] = ['C.phone' => $this->phone];
            }

            if(!empty($this->remind_status) && $this->remind_status != '')
            {
                $condition[] = ['A.status' => $this->remind_status];
            }

            if(isset($this->repayment_status) && $this->repayment_status != '')
            {
                $condition[] = ['B.status' => $this->repayment_status];
            }

            if(!empty($this->reminder_name))
            {
                $adminUserID = AdminUser::find()->select(['id'])->where(['username' => $this->reminder_name])->scalar();
                if($adminUserID){
                    $condition[] = ['A.customer_user_id' => $adminUserID];
                }
            }

            if(is_numeric($this->is_first))
            {
                $condition[] = ['F.is_first' => $this->is_first];
            }

            if(is_numeric($this->customer_type))
            {
                $condition[] = ['C.customer_type' => $this->customer_type];
            }

            if(is_numeric($this->dispatch_status))
            {
                $condition[] = ['A.dispatch_status' => $this->dispatch_status];
            }

            if(!empty($this->begintime_completed))
            {
                $condition[] = ['>=', 'B.closing_time', strtotime($this->begintime_completed)];
            }

            if(!empty($this->endtime_completed))
            {
                $condition[] = ['<', 'B.closing_time', strtotime($this->endtime_completed)];
            }

            if(!empty($this->begintime_plan))
            {
                $condition[] = ['>=', 'B.plan_repayment_time', strtotime($this->begintime_plan)];
            }

            if(!empty($this->endtime_plan))
            {
                $condition[] = ['<=', 'B.plan_repayment_time', strtotime($this->endtime_plan)];
            }

            if(!empty($this->begintime_dispatch))
            {
                $condition[] = ['>=', 'A.dispatch_time', strtotime($this->begintime_dispatch)];
            }

            if(!empty($this->endtime_dispatch))
            {
                $condition[] = ['<=', 'A.dispatch_time', strtotime($this->endtime_dispatch)];
            }

            if(is_numeric($this->remind_reach))
            {
                switch ($this->remind_reach)
                {
                    case RemindOrder::REMIND_REACH :
                        $condition[] = ['>', 'A.remind_return', 0];
                        break;
                    case RemindOrder::REMIND_NO_REACH:
                        $condition[] = ['<', 'A.remind_return', 0];
                        break;
                    default:
                        $condition[] = ['A.remind_return' => 0];

                }
            }

            if(is_numeric($this->remind_return))
            {
                $condition[] = ['A.remind_return' => $this->remind_return];
            }
        }



        return $condition;
    }

}
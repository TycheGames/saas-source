<?php

namespace backend\models\search;

use common\helpers\CommonHelper;
use common\models\financial\FinancialPaymentOrder;
use common\models\order\UserLoanOrder;
use common\models\user\LoanPerson;
use common\models\user\UserBasicInfo;
use yii\base\Model;
use yii\data\SqlDataProvider;
use yii\helpers\ArrayHelper;

class FinancialPaymentRecordSearch extends Model
{

    public $user_id, $id, $status, $merchant_id, $pay_order_id, $pay_payment_id;
    public $is_booked, $is_refund, $created_at, $success_time, $name, $phone, $email_address, $pay_account_id, $order_id;

	public function rules(){
		return [
            [['user_id','id', 'status', 'is_booked', 'is_refund'],'integer'],
            [['pay_order_id','pay_payment_id','created_at','success_time','email_address','phone','name','pay_account_id'], 'string'],
            [['merchant_id', 'order_id'], 'safe']
		];
	}

    public function search($merchantId, $params){
		$query = FinancialPaymentOrder::find()
            ->select(['f.*', 'p.name', 'p.phone'])
            ->from(FinancialPaymentOrder::tableName(). ' as f')
            ->leftJoin(UserLoanOrder::tableName() . ' as o', 'f.order_id = o.id')
            ->leftJoin(LoanPerson::tableName() . ' as p', 'f.user_id = p.id')
            ->orderBy(['f.id' => SORT_DESC]);

        if (($this->load($params) && $this->validate())) {

            if(!empty($this->created_at))
            {
                $begin_created_at = strtotime(explode(' - ', $this->created_at)[0]);
                $end_created_at = strtotime(explode(' - ', $this->created_at)[1]) + 86400;
                $query->andFilterWhere(['>=', 'f.created_at', $begin_created_at]);
                $query->andFilterWhere(['<', 'f.created_at', $end_created_at]);
            }
            if(!empty($this->success_time))
            {
                $begin_success_time = strtotime(explode(' - ', $this->success_time)[0]);
                $end_success_time = strtotime(explode(' - ', $this->success_time)[1]) + 86400;
                $query->andFilterWhere(['>=', 'f.success_time', $begin_success_time]);
                $query->andFilterWhere(['<', 'f.success_time', $end_success_time]);
            }
            if (!empty($this->id)) {
                $query->andFilterWhere(['f.id' => $this->id]);
            }

            if (!empty($this->order_id)) {
                $query->andFilterWhere(['f.order_id' => $this->order_id]);
            }

            if (!empty($this->user_id)) {
                $query->andFilterWhere(['f.user_id' => intval($this->user_id)]);
            }

            if (!empty($this->merchant_id)) {
                $query->andFilterWhere(['f.merchant_id' => intval($this->merchant_id)]);
            } else {
                $query->andFilterWhere(['f.merchant_id' => $merchantId]);
            }

            if (!empty(trim($this->name))) {
                $query->andFilterWhere(['name' => trim($this->name)]);
            }

            if (!empty(trim($this->email_address))) {
                $userIds = array_unique(ArrayHelper::getColumn(UserBasicInfo::find()->select(['user_id'])->where(['email_address' => trim($this->email_address)])->asArray()->all(), 'user_id'));
                if($userIds)
                {
                    $query->andFilterWhere(['f.user_id' => $userIds]);
                }
            }

            if (!empty(trim($this->phone))) {
                $query->andFilterWhere(['phone' => trim($this->phone)]);
            }

            if (!empty(trim($this->pay_order_id))) {
                $query->andFilterWhere(['pay_order_id' => trim($this->pay_order_id)]);
            }

            if (!empty(trim($this->pay_payment_id))) {
                $query->andFilterWhere(['pay_payment_id' => trim($this->pay_payment_id)]);
            }

            if (!empty(trim($this->pay_account_id))) {
                $query->andFilterWhere(['pay_account_id' => trim($this->pay_account_id)]);
            }

            if ($this->status !== '' && in_array($this->status, array_keys(FinancialPaymentOrder::$status_map))) {
                $query->andFilterWhere(['f.status' => intval($this->status)]);
            }

            if ($this->is_booked !== '' && in_array($this->is_booked, array_keys(FinancialPaymentOrder::$is_booked_map))) {
                $query->andFilterWhere(['is_booked' => intval($this->is_booked)]);
            }

            if ($this->is_refund !== '' && in_array($this->is_refund, array_keys(FinancialPaymentOrder::$is_refund_map))) {
                $query->andFilterWhere(['is_refund' => intval($this->is_refund)]);
            }
        } else {
            $query->andFilterWhere(['f.merchant_id' => $merchantId]);
        }

        if(!empty($params['is_summary']) && $params['is_summary'] == 1){
            $count = $query->cache(120)->count();
        }else{
            $count = 99999;
        }

        if(!empty($params['exportcsv']) && $params['exportcsv'] == 'exportcsv'){
            $data = $query->asArray()->all();
            return $this->_exportOrderData($data);
        }

        $dataProvider = new SqlDataProvider([
            'sql' => $query->createCommand()->rawSql,
            'totalCount' => $count,
        ]);

        return $dataProvider;
    }

    /**
     * 导出订单列表
     * @param $data
     */
    private function _exportOrderData($data){
        CommonHelper::_setcsvHeader('pay_order_'.time().'.csv');
        $items = [];
        $merchantList = \backend\models\Merchant::getMerchantId();
        foreach($data as $value){
            $items[] = [
                '订单号'  => $value['order_id'] ?? '-',
                'Name'   => $value['name'] ?? '-',
                '金额'   => $value['amount'] ?? '-',
                'pay_order_id' => $value['pay_order_id'] ?? '-',
                'pay_payment_id' => $value['pay_payment_id'] ?? '-',
                '状态' =>  FinancialPaymentOrder::$status_map[$value['status']] ?? '-',
                '所属商户' =>  $merchantList[$value['merchant_id']],
                '已入账' => FinancialPaymentOrder::$is_booked_map[$value['is_booked']] ?? '-',
                '已退款' => FinancialPaymentOrder::$is_refund_map[$value['is_refund']] ?? '-',
                '创建时间' => date('Y-m-d H:i:s',$value['created_at']) ?? '-',
                '成功时间' => date('Y-m-d H:i:s',$value['success_time']) ?? '-',
                '备注' => $value['remark']
            ];
        }
        echo CommonHelper::_array2csv($items);
        exit;
    }
}
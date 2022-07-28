<?php

namespace backend\models\search;

use common\helpers\CommonHelper;
use common\models\razorpay\RazorpayUPIAddress;
use common\models\risk\RiskRules;
use yii\data\ActiveDataProvider;

class RazorpayVirtualAccountSearch extends RiskRules
{

    public $user_id, $id, $status, $merchant_id, $vid, $account_number, $name, $ifsc, $created_at, $pay_account_id, $va_account, $address, $order_id;

	public function rules(){
		return [
            [['user_id','id', 'order_id'],'integer'],
            [['vid','name','ifsc','account_number','pay_account_id', 'va_account', 'address'], 'string'],
            [['vid','name','ifsc','account_number','pay_account_id', 'va_account', 'address'], 'trim'],
            ['merchant_id', 'safe']
		];
	}

    public function search($merchantId, $params) {
		$query = RazorpayUPIAddress::find()->orderBy(['id' => SORT_DESC]);

        if (($this->load($params) && $this->validate())) {
            if (!empty($this->id )) {
                $query->andFilterWhere(['id' => intval($this->id)]);
            }

            if (!empty($this->user_id )) {
                $query->andFilterWhere(['user_id' => CommonHelper::idDecryption($this->user_id)]);
            }

            if (!empty($this->order_id )) {
                $query->andFilterWhere(['order_id' => intval($this->order_id)]);
            }

            if (!empty($this->merchant_id)) {
                $query->andFilterWhere(['merchant_id' => intval($this->merchant_id)]);
            }

            if (!empty($this->vid)) {
                $query->andFilterWhere(['vid' => trim($this->vid)]);
            }

            if (!empty($this->account_number)) {
                $query->andFilterWhere(['va_account' => trim($this->account_number)]);
            }

            if (!empty($this->pay_account_id)) {
                $query->andFilterWhere(['pay_account_id' => $this->pay_account_id]);
            }

            if (!empty($this->va_account)) {
                $query->andFilterWhere(['va_account' => trim($this->va_account)]);
            }

            if (!empty($this->address)) {
                $query->andFilterWhere(['address' => $this->address]);
            }
        }

        $query->andFilterWhere(['merchant_id' => $merchantId]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        return $dataProvider;

    }

}
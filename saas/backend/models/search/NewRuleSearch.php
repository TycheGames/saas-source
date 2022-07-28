<?php

namespace backend\models\search;

use common\models\risk\RiskRules;
use yii\data\ActiveDataProvider;

class NewRuleSearch extends RiskRules
{

	public function rules(){
		return [
            [['type','status','order'],'integer'],
            [['version','code','alias','guard','result','params'], 'string'],
            [['description'],'safe']
		];
	}

    public function search($params){
		$query = RiskRules::find()->orderBy(['code' => SORT_ASC, 'order'=> SORT_ASC]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        if (!($this->load($params) && $this->validate())) {
            $query->groupBy(['code','version']);
            return $dataProvider;
        }


        if ($this->type !== "") {
            $query->andFilterWhere(['type' => intval($this->type)]);
        }


        if ($this->status !== "") {
            $query->andFilterWhere(['status' => intval($this->status)]);
        }

        if ($this->version !== "") {
            $query->andFilterWhere(['version' => $this->version]);
        }

        if ($this->code !== "") {
            $query->andFilterWhere(['code' => $this->code]);
        }else{
            $query->groupBy(['code','version']);
        }


        if ($this->alias !== "") {
            $query->andFilterWhere(['like', 'alias', $this->alias]);
        }



        return $dataProvider;
    }

}
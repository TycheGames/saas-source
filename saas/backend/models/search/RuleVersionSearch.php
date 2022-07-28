<?php

namespace backend\models\search;

use common\models\risk\RuleVersion;
use yii\data\ActiveDataProvider;

class RuleVersionSearch extends RuleVersion
{

	public function rules(){
		return [
			[['is_default', 'is_gray'], 'integer'],
            [['version', 'version_base_by', 'remark'], 'string'],
            [['remark','created_at', 'updated_at'], 'safe']
		];
	}

    public function search($params){
		$query = RuleVersion::find()->orderBy('id DESC');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        if ($this->version !== "") {
            $query->andFilterWhere(['version' => $this->version]);
        }

        if ($this->is_default !== "") {
            $query->andFilterWhere(['is_default' => intval($this->is_default)]);
        }

        if ($this->is_gray !== "") {
            $query->andFilterWhere(['is_gray' => intval($this->is_gray)]);
        }

        if ($this->version_base_by !== "") {
            $query->andFilterWhere(['version_base_by' => $this->version_base_by]);
        }

        if ($this->remark !== "") {
            $query->andFilterWhere(['like', 'remark', $this->remark]);
        }

        return $dataProvider;
    }

}
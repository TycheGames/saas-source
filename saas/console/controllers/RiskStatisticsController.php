<?php

namespace console\controllers;

use common\models\risk\RiskResultSnapshot;
use yii;

class RiskStatisticsController extends BaseController {


    public function actionManualOrderDaily($startDate = '', $endDate = '')
    {
        $list = [];
        $maxId = 0;
        $query = RiskResultSnapshot::find()
            ->select(['id', 'manual_node', 'created_at'])
            ->where(['result' => 'manual'])
            ->andWhere(['>=', 'created_at', strtotime($startDate)])
            ->andWhere(['<=', 'created_at', strtotime($endDate)]);

        $cloneQuery = clone $query;
        $datas = $cloneQuery->andWhere(['>', 'id', $maxId])->all();
        while ($datas)
        {
            /** @var RiskResultSnapshot $snapshot */
            foreach ($datas as $snapshot)
            {
                $date = strtotime('Y-m-d', $snapshot->created_at);
                if(!isset($list[$date])){
                    $list[$date] = [];
                }
                $modules = json_decode($snapshot->manual_node, true);
                if(!empty($modules))
                {
                    foreach ($modules as $module)
                    {
                        if(isset($list[$date][$module]))
                        {
                            $list[$date][$module] += 1;
                        }else{
                            $list[$date][$module] = 1;
                        }
                    }
                }
            }
        }
    }

}


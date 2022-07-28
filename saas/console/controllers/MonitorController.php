<?php

namespace console\controllers;

use common\models\order\UserLoanOrderRepayment;
use common\services\LogStashService;

class MonitorController extends BaseController {


    /**
     * 指定时间进入Loan Detail的人数
     * @param $beginTime '2019-12-06 13:00:00'
     * @param $endTime '2019-12-06 14:00:00'
     * @return array
     */
    public function actionEnterLoanDetailUserList($beginTime, $endTime )
    {
        $beginTime = strtotime($beginTime);
        $endTime = strtotime($endTime);
        $userList = [];
        $aliLogService = new LogStashService();
        $sql = "category:loan_detail_enter | select DISTINCT text limit 100000";
        $res = $aliLogService->queryYunTuLoanLog($sql, $beginTime, $endTime, 'yuntu_log_code');
        foreach ($res as $ret)
        {
            if(!empty(intval($ret->getContents()['text'])))
            {
                $userList[] = intval($ret->getContents()['text']);
            }
        }
        return $userList;
    }


    /**
     * 指定时间在当日到期的用户进入loan detail 的人数
     * @param $beginTime  '2019-12-06 13:00:00'
     * @param $endTime '2019-12-06 14:00:00'
     * @return int|string
     */
    public function actionDueUserEnterLoanDetailNum($beginTime, $endTime)
    {
        $userList = $this->actionEnterLoanDetailUserList($beginTime, $endTime);
        if(empty($userList))
        {
            return 0;
        }
        $startTime = date('Y-m-d', strtotime($beginTime));

        $count = UserLoanOrderRepayment::find()->where(['user_id' => $userList])
            ->andWhere(['>=', 'plan_repayment_time', $startTime])
            ->andWhere(['<', 'plan_repayment_time', $startTime + 86400])
            ->andWhere(['<', 'closing_time', strtotime($beginTime)])
            ->count();
        return $count;

    }

}


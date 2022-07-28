<?php

namespace common\services\stats;

use backend\models\AdminUser;
use common\models\manual_credit\ManualCreditLog;
use common\models\order\UserLoanOrder;
use common\models\order\UserOrderLoanCheckLog;

class DailyCreditAuditDataService
{


    static function getAuditCountData($startTime,$endTIme){
        $res = ManualCreditLog::find()
            ->select('log.operator_id,log.action,adminUser.merchant_id,count(log.id) as audit_count')
            ->from(ManualCreditLog::tableName() . '  log')
            ->leftJoin(AdminUser::tableName() . '  adminUser','log.operator_id = adminUser.id')
            ->where(['>=','log.created_at',$startTime])
            ->andWhere(['<','log.created_at',$endTIme])
            ->andWhere(['log.is_auto' => ManualCreditLog::NO_AUTO])
            ->groupBy(['log.operator_id','log.action'])
            ->asArray()
            ->all(\Yii::$app->get('db_read_1'));
        return $res;
    }

    static function getPassCountData($startTime,$endTIme){
        $res = ManualCreditLog::find()
            ->select('log.operator_id,log.action,count(log.id) as pass_count')
            ->from(ManualCreditLog::tableName() . '  log')
            ->leftJoin(AdminUser::tableName() . '  adminUser','log.operator_id = adminUser.id')
            ->where(['>=','log.created_at',$startTime])
            ->andWhere(['<','log.created_at',$endTIme])
            ->andWhere(['log.type' => ManualCreditLog::TYPE_PASS,'log.is_auto' => ManualCreditLog::NO_AUTO])
            ->groupBy(['log.operator_id','log.action'])
            ->asArray()
            ->all(\Yii::$app->get('db_read_1'));
        return $res;
    }
}
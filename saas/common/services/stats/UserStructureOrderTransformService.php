<?php

namespace common\services\stats;

use backend\models\AdminUser;
use common\models\ClientInfoLog;
use common\models\manual_credit\ManualCreditLog;
use common\models\order\UserLoanOrder;
use common\models\order\UserLoanOrderRepayment;
use common\models\order\UserOrderLoanCheckLog;
use common\models\user\LoanPerson;

class UserStructureOrderTransformService
{

    public static $userTypeWhereParams = [
        '0' => [],
        '1' => ['order.is_all_first' => UserLoanOrder::FIRST_LOAN_IS,'order.is_first' => UserLoanOrder::FIRST_LOAN_IS],   //全新本新
        '2' => ['order.is_all_first' => UserLoanOrder::FIRST_LOAN_NO,'order.is_first' => UserLoanOrder::FIRST_LOAN_IS],   //全老本新
        '3' => ['order.is_all_first' => UserLoanOrder::FIRST_LOAN_NO,'order.is_first' => UserLoanOrder::FIRST_LOAN_NO]    //全老本老
    ];

    //下单
    static function getApplyAmount($startTime, $endTIme, $whereParams){
        $res = UserLoanOrder::find()
            ->select([
                'order.merchant_id',
                'packageName' => "IF(order.is_export = 1,substring_index(substring_index(clientInfoLog.`app_market`,'_',2),'_',-1),clientInfoLog.package_name)",
                'apply_order_num' => 'count(order.id)',
                'apply_order_money' => 'sum(order.amount + order.interests)',
                'apply_person_num' => 'count(DISTINCT(order.user_id))'
            ])
            ->from(UserLoanOrder::tableName() . '  order')
            ->leftJoin(ClientInfoLog::tableName() . '  clientInfoLog','clientInfoLog.event_id = order.id AND clientInfoLog.event = '.ClientInfoLog::EVENT_APPLY_ORDER)
            ->where($whereParams)
            ->andWhere(['>=','order.created_at',$startTime])
            ->andWhere(['<','order.created_at',$endTIme])
            ->groupBy(['merchant_id','packageName'])
            ->asArray()
            ->all(\Yii::$app->get('db_read_1'));
        $result = [];
        foreach ($res as $val){
            $merchantId = $val['merchant_id'];
            $packageName = $val['packageName'];
            unset($val['packageName']);
            unset($val['merchant_id']);
            $result[$merchantId][$packageName] = $val;
        }
        return $result;
    }

    //通过风控
    static function getAuditAmount($startTime, $endTIme, $whereParams){
        $res = UserLoanOrder::find()
            ->select([
                'order.merchant_id',
                'packageName' => "IF(order.is_export = 1,substring_index(substring_index(clientInfoLog.`app_market`,'_',2),'_',-1),clientInfoLog.package_name)",
                'audit_pass_order_num' => 'count(order.id)',
                'audit_pass_order_money' => 'sum(order.amount + order.interests)',
                'audit_pass_person_num' => 'count(DISTINCT(order.user_id))'
            ])
            ->from(UserLoanOrder::tableName() . '  order')
            ->leftJoin(ClientInfoLog::tableName() . '  clientInfoLog','clientInfoLog.event_id = order.id AND clientInfoLog.event = '.ClientInfoLog::EVENT_APPLY_ORDER)
            ->leftJoin(UserOrderLoanCheckLog::tableName() . '  log','order.id = log.order_id')
            ->where($whereParams)
            ->andWhere(['log.before_status' => UserLoanOrder::STATUS_CHECK, 'log.after_status' => [UserLoanOrder::STATUS_LOANING,UserLoanOrder::STATUS_WAIT_DEPOSIT,UserLoanOrder::STATUS_WAIT_DRAW_MONEY]])
            ->andWhere(['>=','log.created_at',$startTime])
            ->andWhere(['<','log.created_at',$endTIme])
            ->groupBy(['merchant_id','packageName'])
            ->asArray()
            ->all(\Yii::$app->get('db_read_1'));
        $result = [];
        foreach ($res as $val){
            $merchantId = $val['merchant_id'];
            $packageName = $val['packageName'];
            unset($val['packageName']);
            unset($val['merchant_id']);
            $result[$merchantId][$packageName] = $val;
        }
        return $result;
    }

    //提现
    static function getWithdrawAmount($startTime, $endTIme, $whereParams){
        $res = UserLoanOrder::find()
            ->select([
                'order.merchant_id',
                'packageName' => "IF(order.is_export = 1,substring_index(substring_index(clientInfoLog.`app_market`,'_',2),'_',-1),clientInfoLog.package_name)",
                'withdraw_order_num' => 'count(order.id)',
                'withdraw_order_money' => 'sum(order.amount + order.interests)',
                'withdraw_person_num' => 'count(DISTINCT(order.user_id))'
            ])
            ->from(UserLoanOrder::tableName() . '  order')
            ->leftJoin(ClientInfoLog::tableName() . '  clientInfoLog','clientInfoLog.event_id = order.id AND clientInfoLog.event = '.ClientInfoLog::EVENT_APPLY_ORDER)
            ->leftJoin(UserOrderLoanCheckLog::tableName() . '  log','order.id = log.order_id')
            ->where($whereParams)
            ->andWhere(['log.before_status' => [UserLoanOrder::STATUS_CHECK,UserLoanOrder::STATUS_WAIT_DRAW_MONEY,UserLoanOrder::STATUS_WAIT_DEPOSIT], 'log.after_status' => UserLoanOrder::STATUS_LOANING])
            ->andWhere(['>=','log.created_at',$startTime])
            ->andWhere(['<','log.created_at',$endTIme])
            ->groupBy(['merchant_id','packageName'])
            ->asArray()
            ->all(\Yii::$app->get('db_read_1'));
        $result = [];
        foreach ($res as $val){
            $merchantId = $val['merchant_id'];
            $packageName = $val['packageName'];
            unset($val['packageName']);
            unset($val['merchant_id']);
            $result[$merchantId][$packageName] = $val;
        }
        return $result;
    }

    //放款成功
    static function getLoanSuccessAmount($startTime, $endTIme, $whereParams){
        $res = UserLoanOrderRepayment::find()
            ->select([
                'order.merchant_id',
                'packageName' => "IF(order.is_export = 1,substring_index(substring_index(clientInfoLog.`app_market`,'_',2),'_',-1),clientInfoLog.package_name)",
                'loan_success_order_num' => 'count(repayment.id)',
                'loan_success_order_money' => 'sum(repayment.principal + repayment.interests)',
                'loan_success_person_num' => 'count(DISTINCT(repayment.user_id))'
            ])
            ->from(UserLoanOrderRepayment::tableName() . '  repayment')
            ->leftJoin(UserLoanOrder::tableName() . '  order','order.id = repayment.order_id')
            ->leftJoin(ClientInfoLog::tableName() . '  clientInfoLog','clientInfoLog.event_id = order.id AND clientInfoLog.event = '.ClientInfoLog::EVENT_APPLY_ORDER)
            ->where($whereParams)
            ->andWhere(['>=','repayment.loan_time',$startTime])
            ->andWhere(['<','repayment.loan_time',$endTIme])
            ->groupBy(['merchant_id','packageName'])
            ->asArray()
            ->all(\Yii::$app->get('db_read_1'));
        $result = [];
        foreach ($res as $val){
            $merchantId = $val['merchant_id'];
            $packageName = $val['packageName'];
            unset($val['packageName']);
            unset($val['merchant_id']);
            $result[$merchantId][$packageName] = $val;
        }
        return $result;
    }

    static function getUserStructureOrderTransform($leftTime, $rightTime)
    {
        $data = [];
        foreach (static::$userTypeWhereParams as $userType => $whereParams){
            $res = static::getApplyAmount($leftTime,$rightTime,$whereParams);
            foreach ($res as $merchantId => $packageData){
                foreach ($packageData as $packageName => $value){
                    foreach ($value as $key => $val){
                        $data[$userType][$merchantId][$packageName][$key] = $val;
                    }
                }
            }
            $res = static::getAuditAmount($leftTime,$rightTime,$whereParams);
            foreach ($res as $merchantId => $packageData){
                foreach ($packageData as $packageName => $value){
                    foreach ($value as $key => $val){
                        $data[$userType][$merchantId][$packageName][$key] = $val;
                    }
                }
            }
            $res = static::getWithdrawAmount($leftTime,$rightTime,$whereParams);
            foreach ($res as $merchantId => $packageData){
                foreach ($packageData as $packageName => $value){
                    foreach ($value as $key => $val){
                        $data[$userType][$merchantId][$packageName][$key] = $val;
                    }
                }
            }
            $res = static::getLoanSuccessAmount($leftTime,$rightTime,$whereParams);
            foreach ($res as $merchantId => $packageData){
                foreach ($packageData as $packageName => $value){
                    foreach ($value as $key => $val){
                        $data[$userType][$merchantId][$packageName][$key] = $val;
                    }
                }
            }
        }
        return $data;
    }

    /**
     * 统一组装返回数据
     * @param $data
     * @param $total_data
     * @param $date_data
     * @param $date
     * @param $packageName
     * @param $type
     * @param $value
     */
    public function _getReturnData(&$data, &$total_data, &$date_data, $date, $packageName, $type, $value){

        //按天
        $apply_order_num = $value['apply_order_num'] ?? 0;
        $apply_order_money = $value['apply_order_money'] ?? 0;
        $apply_person_num = $value['apply_person_num'] ?? 0;
        $audit_pass_order_num = $value['audit_pass_order_num'] ?? 0;
        $audit_pass_order_money = $value['audit_pass_order_money'] ?? 0;
        $audit_pass_person_num = $value['audit_pass_person_num'] ?? 0;
        $withdraw_order_num = $value['withdraw_order_num'] ?? 0;
        $withdraw_order_money = $value['withdraw_order_money'] ?? 0;
        $withdraw_person_num = $value['withdraw_person_num'] ?? 0;
        $loan_success_order_num = $value['loan_success_order_num'] ?? 0;
        $loan_success_order_money = $value['loan_success_order_money'] ?? 0;
        $loan_success_person_num = $value['loan_success_person_num'] ?? 0;

        $data[$date][$packageName]['apply_order_num_'.$type] = $apply_order_num;
        $data[$date][$packageName]['apply_order_money_'.$type] = $apply_order_money;
        $data[$date][$packageName]['apply_person_num_'.$type] = $apply_person_num;
        $data[$date][$packageName]['audit_pass_order_num_'.$type] = $audit_pass_order_num;
        $data[$date][$packageName]['audit_pass_order_money_'.$type] = $audit_pass_order_money;
        $data[$date][$packageName]['audit_pass_person_num_'.$type] = $audit_pass_person_num;
        $data[$date][$packageName]['withdraw_order_num_'.$type] = $withdraw_order_num;
        $data[$date][$packageName]['withdraw_order_money_'.$type] = $withdraw_order_money;
        $data[$date][$packageName]['withdraw_person_num_'.$type] = $withdraw_person_num;
        $data[$date][$packageName]['loan_success_order_num_'.$type] = $loan_success_order_num;
        $data[$date][$packageName]['loan_success_order_money_'.$type] = $loan_success_order_money;
        $data[$date][$packageName]['loan_success_person_num_'.$type] = $loan_success_person_num;

        $date_data[$date]['apply_order_num_'.$type] = ($date_data[$date]['apply_order_num_'.$type] ?? 0) + $apply_order_num;
        $date_data[$date]['apply_order_money_'.$type] = ($date_data[$date]['apply_order_money_'.$type] ?? 0) + $apply_order_money;
        $date_data[$date]['apply_person_num_'.$type] = ($date_data[$date]['apply_person_num_'.$type] ?? 0) + $apply_person_num;
        $date_data[$date]['audit_pass_order_num_'.$type] = ($date_data[$date]['audit_pass_order_num_'.$type] ?? 0) + $audit_pass_order_num;
        $date_data[$date]['audit_pass_order_money_'.$type] = ($date_data[$date]['audit_pass_order_money_'.$type] ?? 0) + $audit_pass_order_money;
        $date_data[$date]['audit_pass_person_num_'.$type] = ($date_data[$date]['audit_pass_person_num_'.$type] ?? 0) + $audit_pass_person_num;
        $date_data[$date]['withdraw_order_num_'.$type] = ($date_data[$date]['withdraw_order_num_'.$type] ?? 0) + $withdraw_order_num;
        $date_data[$date]['withdraw_order_money_'.$type] = ($date_data[$date]['withdraw_order_money_'.$type] ?? 0) + $withdraw_order_money;
        $date_data[$date]['withdraw_person_num_'.$type] = ($date_data[$date]['withdraw_person_num_'.$type] ?? 0) + $withdraw_person_num;
        $date_data[$date]['loan_success_order_num_'.$type] = ($date_data[$date]['loan_success_order_num_'.$type] ?? 0) + $loan_success_order_num;
        $date_data[$date]['loan_success_order_money_'.$type] = ($date_data[$date]['loan_success_order_money_'.$type] ?? 0) + $loan_success_order_money;
        $date_data[$date]['loan_success_person_num_'.$type] = ($date_data[$date]['loan_success_person_num_'.$type] ?? 0) + $loan_success_person_num;


        //汇总
        $total_apply_order_num = $total_data['apply_order_num_'.$type] ?? 0;
        $total_apply_order_money = $total_data['apply_order_money_'.$type] ?? 0;
        $total_apply_person_num = $total_data['apply_person_num_'.$type] ?? 0;
        $total_audit_pass_order_num = $total_data['audit_pass_order_num_'.$type] ?? 0;
        $total_audit_pass_order_money = $total_data['audit_pass_order_money_'.$type] ?? 0;
        $total_audit_pass_person_num = $total_data['audit_pass_person_num_'.$type] ?? 0;
        $total_withdraw_order_num = $total_data['withdraw_order_num_'.$type] ?? 0;
        $total_withdraw_order_money = $total_data['withdraw_order_money_'.$type] ?? 0;
        $total_withdraw_person_num = $total_data['withdraw_person_num_'.$type] ?? 0;
        $total_loan_success_order_num = $total_data['loan_success_order_num_'.$type] ?? 0;
        $total_loan_success_order_money_ = $total_data['loan_success_order_money_'.$type] ?? 0;
        $total_loan_success_person_num = $total_data['loan_success_person_num_'.$type] ?? 0;

        $total_data['apply_order_num_'.$type] = $total_apply_order_num + $apply_order_num;
        $total_data['apply_order_money_'.$type] = $total_apply_order_money + $apply_order_money;
        $total_data['apply_person_num_'.$type] = $total_apply_person_num + $apply_person_num;
        $total_data['audit_pass_order_num_'.$type] = $total_audit_pass_order_num + $audit_pass_order_num;
        $total_data['audit_pass_order_money_'.$type] = $total_audit_pass_order_money + $audit_pass_order_money;
        $total_data['audit_pass_person_num_'.$type] = $total_audit_pass_person_num + $audit_pass_person_num;
        $total_data['withdraw_order_num_'.$type] = $total_withdraw_order_num + $withdraw_order_num;
        $total_data['withdraw_order_money_'.$type] = $total_withdraw_order_money + $withdraw_order_money;
        $total_data['withdraw_person_num_'.$type] = $total_withdraw_person_num + $withdraw_person_num;
        $total_data['loan_success_order_num_'.$type] = $total_loan_success_order_num + $loan_success_order_num;
        $total_data['loan_success_order_money_'.$type] = $total_loan_success_order_money_ + $loan_success_order_money;
        $total_data['loan_success_person_num_'.$type] = $total_loan_success_person_num + $loan_success_person_num;

        unset($data);
        unset($date_data);
        unset($total_data);
    }
}
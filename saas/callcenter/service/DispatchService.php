<?php
/**
 * 派单服务
 */
namespace callcenter\service;


use backend\models\Merchant;
use callcenter\models\AdminUser;
use callcenter\models\AdminUserRole;
use callcenter\models\CollectorClassSchedule;
use callcenter\models\loan_collection\LoanCollectionOrder;
use common\models\order\UserLoanOrder;
use common\models\order\UserLoanOrderRepayment;
use common\models\user\UserActiveTime;

class DispatchService  {

    const SUCCESS_CODE = 0;
    const ERROR_CODE = -1;

    const DISPATCH_RANDOM = 0;
    const DISPATCH_NEW = 1;
    const DISPATCH_OLD = 2;

    public static $dispatch_user_type = [
        self::DISPATCH_RANDOM => 'random',
        self::DISPATCH_NEW => 'new',
        self::DISPATCH_OLD => 'old',
    ];

    public static $dispatch_is_first_relation = [
        self::DISPATCH_NEW => UserLoanOrder::FIRST_LOAN_IS,
        self::DISPATCH_OLD => UserLoanOrder::FIRST_LOAN_NO,
    ];

    /**
     * 派单到机构绑定机构
     * @param array $waitingOrders
     * @param array $companyDispatchArr
     * @return bool
     */
    public function dispatchAllToCompany($waitingOrders = array(),$companyDispatchArr = array()){
        if(empty($waitingOrders)|| empty($companyDispatchArr))  {
            echo 'none dispatch or company'."\r\n";
            return false;
        }
        $totalDisNum =  array_sum($companyDispatchArr);
        if($totalDisNum < 1)  return false;
        $arr = [];
        $index = 1;
        $loanCollectionService = new LoanCollectionService();
        foreach ($companyDispatchArr as $k => $v){
            if($v >= 1){
                $arr[$k] = [$index,$index+$v-1];
                $index += $v;
            }
        }
        // 10 40 50
        foreach ($waitingOrders as $order){
            $mr = mt_rand(1,$totalDisNum);
            foreach ($arr as $outside => $item){
                echo $mr . '>=' . $item[0] . '&&' .$mr . '<=' .$item[1].PHP_EOL;
                if($mr >= $item[0] && $mr <= $item[1]){
                    //订单分配给公司
                    $loanCollectionService->dispatchToCompany($order['id'],$outside);
                }
            }
        }
    }


    /**
     * 分配到
     * @param array $overdue_orders
     * @param array $user_schedule
     * @return bool
     */
    public function dispatchCompanyToOperator($overdue_orders,$user_schedule){

        try{
            if(empty($overdue_orders)|| empty($user_schedule))  {
                echo 'none overdue_orders or user_schedule'."\r\n";
                return false;
            }

            $adminUsers = AdminUser::find()->where([
                    'group' => $user_schedule['group_id'],
                    'outside' => $user_schedule['company_id'],
                    'open_status' => AdminUser::$usable_status
                ]
            )->asArray()->all();
            $admin_ids = array_column($adminUsers, 'id');

            $loanCollectionService = new LoanCollectionService();
            // 10 40 50
            foreach ($overdue_orders as $order){
                //随机分配
                $r_key = array_rand($admin_ids);
                $operator_id = $admin_ids[$r_key];
                unset($admin_ids[$r_key]);
                $res = $loanCollectionService->dispatchToOperator($order['id'],$operator_id);
                if($res['code'] != LoanCollectionService::SUCCESS_CODE){
                    echo $res['message'].PHP_EOL;
                }
            }
        }catch(\Exception $e){
            echo $e->getFile().$e->getLine().$e->getMessage();
        }
        return true;
    }

    /**
     * 获取公司对应逾期等级待分派的订单id（对应新老）
     * @param int|array $outsides
     * @param $level
     * @param $overdueArr
     * @param array|int $merchantId 商户ID
     * @param array $activeArr
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getDispatchLevelOrderIds($outsides,$level,$overdueArr = [],$merchantId,$activeArr = []){
         $query =  LoanCollectionOrder::find()
            ->select(['A.id','B.is_first'])
            ->from(LoanCollectionOrder::tableName(). ' A')
            ->leftJoin(UserLoanOrder::getDbName().'.'.UserLoanOrder::tableName(). ' B','A.user_loan_order_id = B.id')
            ->leftJoin(UserLoanOrderRepayment::getDbName().'.'.UserLoanOrderRepayment::tableName(). ' C','A.user_loan_order_repayment_id = C.id')
            ->where([
                'A.merchant_id' => $merchantId,
                'A.outside' => $outsides,
                'A.current_overdue_level' => $level,
                'A.current_collection_admin_user_id'=> 0,
                'A.status' => LoanCollectionOrder::STATUS_WAIT_COLLECTION
            ]);
        if(isset($overdueArr[0])){
            $query->andWhere(['>=','C.overdue_day',intval($overdueArr[0])]);
            if(isset($overdueArr[1])){
                $query->andWhere(['<=','C.overdue_day',intval($overdueArr[1])]);
            }
        }
        if(!empty($activeArr)){
            $query->leftJoin(UserActiveTime::getDbName().'.'.UserActiveTime::tableName(). ' D','A.user_id = D.user_id');
            $condition = UserActiveTime::colorBlinkerConditionNew($activeArr,'D.','C.',false);
            $query->andWhere($condition);
        }
        $loanCollectionOrders = $query->asArray()->all();
        $res = [UserLoanOrder::FIRST_LOAN_NO => [],UserLoanOrder::FIRST_LOAN_IS => []];
        foreach ($loanCollectionOrders as $item){
            if(isset(UserLoanOrder::$first_loan_map[$item['is_first']])){
                $res[$item['is_first']][] = $item['id'];
            }
        }
        return $res;
    }

    /**
     * 获取公司对应逾期等级待分派的订单数（对应新老）
     * @param int|array $outsides
     * @param $level
     * @param $overdueArr
     * @param $merchantId
     * @param array $activeArr
     * @return array  [0 => 31,1 => 34]
     */
    public function getDispatchLevelOrdersCount($outsides,$level,$overdueArr = [], $merchantId = null,$activeArr = []){
        $query = LoanCollectionOrder::find()
            ->select(['B.is_first','count' => 'COUNT(A.id)'])
            ->from(LoanCollectionOrder::tableName(). ' A')
            ->leftJoin(UserLoanOrder::getDbName().'.'.UserLoanOrder::tableName(). ' B','A.user_loan_order_id = B.id')
            ->leftJoin(UserLoanOrderRepayment::getDbName().'.'.UserLoanOrderRepayment::tableName(). ' C','A.user_loan_order_repayment_id = C.id')
            ->where([
                'A.merchant_id' => $merchantId,
                'A.outside' => $outsides,
                'A.current_overdue_level' => $level,
                'A.current_collection_admin_user_id'=> 0,
                'A.status' => LoanCollectionOrder::STATUS_WAIT_COLLECTION
            ]);
        if(isset($overdueArr[0])){
            $query->andWhere(['>=','C.overdue_day',intval($overdueArr[0])]);
            if(isset($overdueArr[1])){
                $query->andWhere(['<=','C.overdue_day',intval($overdueArr[1])]);
            }
        }
        if(!empty($activeArr)){
            $query->leftJoin(UserActiveTime::getDbName().'.'.UserActiveTime::tableName(). ' D','A.user_id = D.user_id');
            $condition = UserActiveTime::colorBlinkerConditionNew($activeArr,'D.','C.',false);
            $query->andWhere($condition);
        }
        //echo $query->createCommand()->getRawSql();
        $countArr = $query->groupBy(['B.is_first'])
            ->asArray()
            ->all();
        $countArr = array_column($countArr,'count','is_first');
        $res = [];
        foreach (UserLoanOrder::$first_loan_map as $key => $item){
            $res[$key] = intval($countArr[$key] ?? 0);
        }
        return $res;
    }

    /**
     * 获取公司各个逾期等级可分派的新老单数
     * @param int|array $outsides
     * @param int|array$merchantId
     * @param array $activeArr
     * @return array
     */
    public function getDispatchCount($outsides, $merchantId, $activeArr = []){
        $totalCountArray = [];
        foreach (loanCollectionOrder::$current_level as $l => $name){
            //待分配机构的总数
            $toCompanyCount = $this->getDispatchLevelOrdersCount($outsides, $l, [], $merchantId, $activeArr);
            $newCount = $toCompanyCount[UserLoanOrder::FIRST_LOAN_IS] ?? 0;
            $oldCount = $toCompanyCount[UserLoanOrder::FIRST_LOAN_NO] ?? 0;
            $totalCountArray[$l] = [
                'totalCount' => $newCount + $oldCount,
                'newCount' => $newCount,
                'oldCount' => $oldCount,
            ];
        }
        return $totalCountArray;
    }


    /**
     * 分派检查可行性
     * @param int|array $outsides 分派前所属机构
     * @param int $level
     * @param array $dispatchCount 分派信息 [人或公司ID => 分派数]
     * @param array $isFirstArr 新老信息 [人或公司ID => 对应的新老TYPE]
     * @param array $overdueArr
     * @param array|int $merchantId 商户ID
     * @param array $activeArr
     * @return array
     */
    public function checkDispatch($outsides,$level,$dispatchCount,$isFirstArr,$overdueArr = [], $merchantId, $activeArr = []){
        $totalCount = 0;
        $newCount = 0;
        $oldCount = 0;
        $totalCountArray = $this->getDispatchLevelOrdersCount($outsides,$level,$overdueArr, $merchantId, $activeArr);
        foreach ($dispatchCount as $id => $dCount){
            if(isset($isFirstArr[$id]) && $dCount > 0){
                switch ($isFirstArr[$id]){
                    case self::DISPATCH_NEW:
                        $newCount += $dCount;
                        $totalCount += $dCount;
                        break;
                    case static::DISPATCH_OLD:
                        $oldCount += $dCount;
                        $totalCount += $dCount;
                        break;
                    default:
                        $totalCount += $dCount;
                }
            }
        }
        //提交的分派订单数为空
        if($totalCount == 0){
            return ['code' => -1, 'message' => 'The number of dispatch orders submitted is empty'];
        }
        //可分派订单数为空
        if(($totalCountArray[UserLoanOrder::FIRST_LOAN_IS] + $totalCountArray[UserLoanOrder::FIRST_LOAN_NO]) == 0){
            return ['code' => -1, 'message' => 'The number of dispatchable orders is empty'];
        }
        if($totalCount > $totalCountArray[UserLoanOrder::FIRST_LOAN_IS] + $totalCountArray[UserLoanOrder::FIRST_LOAN_NO]){
            return ['code' => -1, 'message' => 'dispatch total count greater than collection Order total count'];
        }
        if($oldCount > $totalCountArray[UserLoanOrder::FIRST_LOAN_NO]){
            return ['code' => -1, 'message' => 'dispatch old count greater than collection Order old count'];
        }
        if($newCount > $totalCountArray[UserLoanOrder::FIRST_LOAN_IS]){
            return ['code' => -1, 'message' => 'dispatch new count greater than collection Order new count'];
        }
        return ['code' => 0, 'message' => 'dispatch new count greater than collection Order new count'];
    }

    /**
     *
     * @param LoanCollectionService $loanCollectionService
     * @param int $level
     * @param $dispatchCount
     * @param $isFirstArr
     * @param array $overdueArr
     * @param array|int $merchantId 商户ID
     * @param array $activeArr
     * @return array
     */
    public function dispatchCompanyByRule(LoanCollectionService $loanCollectionService,$level,$dispatchCount,$isFirstArr,$overdueArr,$merchantId,$activeArr = []){
        $result = [];
        $collectionOrderIds = $this->getDispatchLevelOrderIds(0,$level,$overdueArr,$merchantId,$activeArr);
        $allCount = count($collectionOrderIds[UserLoanOrder::FIRST_LOAN_NO]) + count($collectionOrderIds[UserLoanOrder::FIRST_LOAN_IS]);
        if($allCount <= 0){
            return [ 'code' => -1, 'message' => 'There are no assigned orders'];
        }

        $dispatchData = [];
        $dispatchDataNew = [];
        $dispatchDataOld = [];
        //先新后老再不区分
        foreach ($dispatchCount as $outside => $dCount){
            if(isset($isFirstArr[$outside]) && $dCount > 0){
                switch ($isFirstArr[$outside]){
                    case self::DISPATCH_NEW:
                        $dispatchDataNew[] = ['id' => $outside, 'count' => $dCount, 'dis_type'=>self::DISPATCH_NEW ];
                        break;
                    case self::DISPATCH_OLD:
                        $dispatchDataOld[] = ['id' => $outside, 'count' => $dCount, 'dis_type'=>self::DISPATCH_OLD ];
                        break;
                    default:
                        $dispatchData[] = ['id' => $outside, 'count' => $dCount];
                        break;
                }

            }
        }

        //排序重新组合
        $dispatchDataNewAndOld = array_merge($dispatchDataNew,$dispatchDataOld);

        foreach ($dispatchDataNewAndOld as $datum){
            $outside = $datum['id'];
            $count = $datum['count'];
            $disType = $datum['dis_type'];
            $successCount = 0;
            if($count == 0){
                continue;
            }
            $isFirst = self::$dispatch_is_first_relation[$disType];
            $orderArr = array_rand($collectionOrderIds[$isFirst],$count);
            if(is_array($orderArr)){
                foreach ($orderArr as $orderIdKey){
                    //订单指派
                    $res = $loanCollectionService->dispatchToCompany($collectionOrderIds[$isFirst][$orderIdKey],$outside);
                    if($res['code'] == DispatchService::ERROR_CODE){
                        return [ 'code' => -1, 'message' => 'Assign failure！'.$res['message'], 'result'=>$result];
                    }
                    unset($collectionOrderIds[$isFirst][$orderIdKey]);
                    $successCount++;
                }

            }else{
                //订单指派
                $res = $loanCollectionService->dispatchToCompany($collectionOrderIds[$isFirst][$orderArr],$outside);
                if($res['code'] == DispatchService::ERROR_CODE){
                    return [ 'code' => -1, 'message' => 'Assign failure！'.$res['message'],'result'=> $result];
                }
                unset($collectionOrderIds[$isFirst][$orderArr]);
                $successCount++;
            }
            $result[$outside] = $successCount;


        }

        $collectionOrderRandomIds = array_merge($collectionOrderIds[UserLoanOrder::FIRST_LOAN_NO],$collectionOrderIds[UserLoanOrder::FIRST_LOAN_IS]);
        //不区分新老
        foreach ($dispatchData as $datum){
            $outside = $datum['id'];
            $count = $datum['count'];
            $successCount = 0;
            if($count == 0){
                continue;
            }

            $orderArr = array_rand($collectionOrderRandomIds,$count);
            if(is_array($orderArr)){
                foreach ($orderArr as $orderIdKey){
                    //订单指派
                    $res = $loanCollectionService->dispatchToCompany($collectionOrderRandomIds[$orderIdKey],$outside);
                    if($res['code'] == DispatchService::ERROR_CODE){
                        return [ 'code' => -1, 'message' => 'Assign failure！'.$res['message'], 'result'=>$result];
                    }
                    unset($collectionOrderRandomIds[$orderIdKey]);
                    $successCount++;
                }

            }else{
                //订单指派
                $res = $loanCollectionService->dispatchToCompany($collectionOrderRandomIds[$orderArr],$outside);
                if($res['code'] == DispatchService::ERROR_CODE){
                    return [ 'code' => -1, 'message' => 'Assign failure！'.$res['message'],'result'=> $result];
                }
                unset($collectionOrderRandomIds[$orderArr]);
                $successCount++;
            }
            $result[$outside] = $successCount;

        }
        return [ 'code' => 0, 'message' => '','result'=> $result];
    }

    /**
     *
     * @param LoanCollectionService $loanCollectionService
     * @param $outsideArr
     * @param int $level
     * @param $dispatchCount
     * @param $isFirstArr
     * @param array $overdueArr
     * @param array|int $merchantId 商户ID
     * @param array $activeArr
     * @return array
     */
    public function dispatchCompanyToOperatorByRule(LoanCollectionService $loanCollectionService,$outsideArr,$level,$dispatchCount,$isFirstArr,$overdueArr,$merchantId,$activeArr = []){
        $result = [];
        $collectionOrderIds = $this->getDispatchLevelOrderIds($outsideArr,$level,$overdueArr,$merchantId,$activeArr);
        $allCount = count($collectionOrderIds[UserLoanOrder::FIRST_LOAN_NO]) + count($collectionOrderIds[UserLoanOrder::FIRST_LOAN_IS]);
        if($allCount <= 0){
            return [ 'code' => -1, 'message' => 'There are no assigned orders'];
        }

        $dispatchData = [];
        $dispatchDataNew = [];
        $dispatchDataOld = [];
        //先新后老再不区分
        foreach ($dispatchCount as $adminId => $dCount){
            if(isset($isFirstArr[$adminId]) && $dCount > 0){
                switch ($isFirstArr[$adminId]){
                    case self::DISPATCH_NEW:
                        $dispatchDataNew[] = ['id' => $adminId, 'count' => $dCount, 'dis_type'=>self::DISPATCH_NEW ];
                        break;
                    case self::DISPATCH_OLD:
                        $dispatchDataOld[] = ['id' => $adminId, 'count' => $dCount, 'dis_type'=>self::DISPATCH_OLD ];
                        break;
                    default:
                        $dispatchData[] = ['id' => $adminId, 'count' => $dCount];
                        break;
                }

            }
        }

        //排序重新组合
        $dispatchDataNewAndOld = array_merge($dispatchDataNew,$dispatchDataOld);

        foreach ($dispatchDataNewAndOld as $datum){
            $collectionAdminId = $datum['id'];
            $count = $datum['count'];
            $disType = $datum['dis_type'];
            $successCount = 0;
            if($count == 0){
                continue;
            }
            $isFirst = self::$dispatch_is_first_relation[$disType];
            $orderArr = array_rand($collectionOrderIds[$isFirst],$count);
            if(is_array($orderArr)){
                foreach ($orderArr as $orderIdKey){
                    //订单指派
                    $res = $loanCollectionService->dispatchToOperator($collectionOrderIds[$isFirst][$orderIdKey],$collectionAdminId);
                    if($res['code'] == DispatchService::ERROR_CODE){
                        return [ 'code' => -1, 'message' => 'Assign failure！'.$res['message'], 'result'=>$result];
                    }
                    unset($collectionOrderIds[$isFirst][$orderIdKey]);
                    $successCount++;
                }

            }else{
                //订单指派
                $res = $loanCollectionService->dispatchToOperator($collectionOrderIds[$isFirst][$orderArr],$collectionAdminId);
                if($res['code'] == DispatchService::ERROR_CODE){
                    return [ 'code' => -1, 'message' => 'Assign failure！'.$res['message'],'result'=> $result];
                }
                unset($collectionOrderIds[$isFirst][$orderArr]);
                $successCount++;
            }
            $result[$collectionAdminId] = $successCount;


        }

        $collectionOrderRandomIds = array_merge($collectionOrderIds[UserLoanOrder::FIRST_LOAN_NO],$collectionOrderIds[UserLoanOrder::FIRST_LOAN_IS]);
        //不区分新老
        foreach ($dispatchData as $datum){
            $collectionAdminId = $datum['id'];
            $count = $datum['count'];
            $successCount = 0;
            if($count == 0){
                continue;
            }

            $orderArr = array_rand($collectionOrderRandomIds,$count);
            if(is_array($orderArr)){
                foreach ($orderArr as $orderIdKey){
                    //订单指派
                    $res = $loanCollectionService->dispatchToOperator($collectionOrderRandomIds[$orderIdKey],$collectionAdminId);
                    if($res['code'] == DispatchService::ERROR_CODE){
                        return [ 'code' => -1, 'message' => 'Assign failure！'.$res['message'], 'result'=>$result];
                    }
                    unset($collectionOrderRandomIds[$orderIdKey]);
                    $successCount++;
                }

            }else{
                //订单指派
                $res = $loanCollectionService->dispatchToOperator($collectionOrderRandomIds[$orderArr],$collectionAdminId);
                if($res['code'] == DispatchService::ERROR_CODE){
                    return [ 'code' => -1, 'message' => 'Assign failure！'.$res['message'],'result'=> $result];
                }
                unset($collectionOrderRandomIds[$orderArr]);
                $successCount++;
            }
            $result[$collectionAdminId] = $successCount;

        }
        return [ 'code' => 0, 'message' => '','result'=> $result];
    }


    public function getDispatchCollector($outside,$group, $groupGame = 0, $adminMerchantId){
        $allMerchantId = Merchant::getAllMerchantId();
        $roles = AdminUserRole::getRolesByGroup([AdminUserRole::TYPE_COLLECTION]);
        $where = ['A.role' => $roles, 'A.group' => $group, 'A.outside' => $outside, 'A.open_status' => AdminUser::$usable_status, 'A.can_dispatch' => AdminUser::CAN_DISPATCH];
        if($groupGame != 0){
            $where['A.group_game'] = $groupGame;
        }
        $loanCollection = AdminUser::find()
            ->select(['A.id','A.username','A.merchant_id','A.to_view_merchant_id'])
            ->alias('A')
            ->leftJoin(CollectorClassSchedule::tableName().' B','B.admin_id = A.id AND B.date = "'.date('Y-m-d').'"')
            ->where($where)
            ->andWhere(['OR',['B.status' => CollectorClassSchedule::STATUS_DEL],['IS','B.status',null]])
            ->asArray()->all();
        foreach ($loanCollection as $key => $value){
            if ($value['merchant_id'] > 0) {
                $merchantIds = [$value['merchant_id']];
            } else {
                if (!empty($value['to_view_merchant_id'])) {
                    $merchantIds = explode(',', $value['to_view_merchant_id']);
                } else {
                    $merchantIds = $allMerchantId;
                }
            }
            if(!in_array($adminMerchantId,$merchantIds)){
                  unset($loanCollection[$key]);
            }
        }
        $adminIds = array_column($loanCollection,'id');
        $adminOrder = loanCollectionOrder::find()
            ->select(['current_collection_admin_user_id','order_num' => 'COUNT(1)'])
            ->where(['current_collection_admin_user_id' => $adminIds,'status' => loanCollectionOrder::$not_end_status])
            ->groupBy('current_collection_admin_user_id')->asArray()->all();

        $adminOrderNumArr = array_column($adminOrder,'order_num','current_collection_admin_user_id');
        foreach ($loanCollection as &$adminValue){
            $adminValue['order_num'] = $adminOrderNumArr[$adminValue['id']] ?? 0;
        }
        return $loanCollection;
    }
}
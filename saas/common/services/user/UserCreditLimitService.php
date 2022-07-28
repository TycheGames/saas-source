<?php


namespace common\services\user;

use common\helpers\CommonHelper;
use common\models\order\UserLoanOrderRepayment;
use common\models\product\ProductSetting;
use common\models\user\LoanPerson;
use common\models\user\LoanPersonExternal;
use common\models\user\UserCreditLimit;
use common\models\user\UserCreditLimitChangeLog;
use common\services\BaseService;
use common\services\order\OrderService;
use common\services\package\PackageService;
use frontend\models\loan\UserCreditLimitForm;
use Yii;

class UserCreditLimitService extends BaseService
{

    //基础额度
    private $baseCredit = 170000;

    /**
     * 还款额度计算
     * @param UserLoanOrderRepayment $repaymentOrder
     */
    public function repaymentCreditLimitCalculation(UserLoanOrderRepayment $repaymentOrder)
    {
        $disbursalAmount = $repaymentOrder->principal - $repaymentOrder->cost_fee;
        $userCreditLimit = $this->getCreditByUserId($repaymentOrder->user_id, UserCreditLimit::TYPE_7_DAY);
        //更改前额度
        $beforeMaxLimit = $userCreditLimit->max_limit;

        $totalPrerepaymentCount = 0;
        $repaymentOrders = UserLoanOrderRepayment::find()->select(['plan_repayment_time', 'closing_time'])->where(['user_id' => $repaymentOrder->user_id])->all();
        /** @var UserLoanOrderRepayment $v */
        foreach ($repaymentOrders as $v){
            $vTime = strtotime(date('Y-m-d', $v->plan_repayment_time)) - strtotime(date('Y-m-d', $v->closing_time));
            if($vTime != 0)
            {
                $vDays = intval(ceil($vTime / 86400));
            }else{
                $vDays = 0;
            }
            if($vDays >= 4)
            {
                $totalPrerepaymentCount++;
            }
        }

        $diffTime = strtotime(date('Y-m-d', $repaymentOrder->plan_repayment_time)) - strtotime(date('Y-m-d', $repaymentOrder->closing_time));
        if($diffTime != 0)
        {
            $diffDays = intval(ceil($diffTime / 86400));
        }else{
            $diffDays = 0;
        }


        if($diffDays >= 4 && $diffDays <= 5)    //提前还款4-5天
        {
            //本次提前还款天数 [4, 5] 且 历史累计提前[4, 5]天的还款次数 >= 2
            if($totalPrerepaymentCount >= 2)
            {
                $afterMaxLimit = min($beforeMaxLimit + 10000, 800000);
            }else{
                /**
                 * 上一次额度 < 6000 min (上一次额度 * 2 , 8000)
                 * 上一次额度 >= 6000 min (上一次额度 + 1000 , 8000)
                 */
                if($beforeMaxLimit < 600000)
                {
                    if($disbursalAmount <= ($beforeMaxLimit * 0.8))
                    {
                        $afterMaxLimit = $beforeMaxLimit;
                    }else{
                        $afterMaxLimit = min($beforeMaxLimit * 2, 800000);
                    }

                }else{

                    if($disbursalAmount <= ($beforeMaxLimit * 0.8))
                    {
                        $afterMaxLimit = $beforeMaxLimit;
                    }else{
                        $afterMaxLimit = min($beforeMaxLimit + 100000, 800000);
                    }

                }
            }


        } elseif ($diffDays >= 6){
            //本次提前还款天数 >= 6
            if($disbursalAmount <= ($beforeMaxLimit * 0.8))
            {
                $afterMaxLimit = $beforeMaxLimit;
            }else{
                $afterMaxLimit = min($beforeMaxLimit + 10000, 800000);
            }

        } elseif($diffDays <= 3 && $repaymentOrder->overdue_day <= 3) //本次还款逾期天数 <= 3 且本次提前还款天数 <= 3
        {
            /**
             * 上一次额度 < 6000 min (上一次额度 * 2 , 8000)
             * 上一次额度 >= 6000 min (上一次额度 + 1000 , 8000)
             */
            if($beforeMaxLimit < 600000)
            {
                if($disbursalAmount <= ($beforeMaxLimit * 0.8))
                {
                    $afterMaxLimit = $beforeMaxLimit;
                }else{
                    $afterMaxLimit = min($beforeMaxLimit * 2, 800000);
                }

            }else{
                if($disbursalAmount <= ($beforeMaxLimit * 0.8))
                {
                    $afterMaxLimit = $beforeMaxLimit;
                }else{
                    $afterMaxLimit = min($beforeMaxLimit + 100000, 800000);
                }
            }
        }elseif($repaymentOrder->overdue_day >= 4 && $repaymentOrder->overdue_day < 7) // 本次还款逾期天数：[4, 7)
        {
            $afterMaxLimit = $beforeMaxLimit;
        }elseif ($repaymentOrder->overdue_day >= 7 && $repaymentOrder->overdue_day < 14) { //本次还款逾期天数：[7, 14)
            $afterMaxLimit = max($beforeMaxLimit/2, $this->baseCredit);
        }else { //本次还款逾期天数：[14, 30)
            $afterMaxLimit = $this->baseCredit;
        }

        //有额度更改才操作变更表
        if($afterMaxLimit != $beforeMaxLimit)
        {
            $userCreditLimit->max_limit = $afterMaxLimit;
            $userCreditLimit->save();
            $changeLog = new UserCreditLimitChangeLog();
            $changeLog->user_id = $repaymentOrder->user_id;
            $changeLog->before_max_limit = $beforeMaxLimit;
            $changeLog->after_max_limit = $afterMaxLimit;
            $changeLog->type = UserCreditLimit::TYPE_7_DAY;
            $changeLog->reason = "repaymentId:{$repaymentOrder->id}";
            $changeLog->save();
        }



    }


    /**
     * 获取用户最大额度
     * @param int $userId
     * @param int $productID
     * @return int 用户额度，单位元
     */
    public function getUserMaxLimit(int $userId, int $productID = 0) : int
    {

        $user = LoanPerson::findOne($userId);
        //本平台新客
        if(LoanPerson::CUSTOMER_TYPE_NEW == $user->customer_type)
        {
            $isAllNew = LoanPersonExternal::isAllPlatformNewCustomer($user->pan_code);
            $product = ProductSetting::findOne($productID);
            if($isAllNew)
            {
                $maxLimit = $product->default_credit_limit;
            }else{
                $maxLimit = $product->default_credit_limit_2;
            }

        }else{
            $userCreditLimit = $this->getCreditByUserId($userId, UserCreditLimit::TYPE_7_DAY);
            $maxLimit = intval(CommonHelper::CentsToUnit($userCreditLimit->max_limit));
        }
        return $maxLimit;
    }


    /**
     * 获取用户最小额度
     * @param $userId
     * @return int 用户额度，单位元
     */
    public function getUserMinLimit($userId) : int
    {
        $userCreditLimit = $this->getCreditByUserId($userId, UserCreditLimit::TYPE_7_DAY);
        $maxLimit = intval(CommonHelper::CentsToUnit($userCreditLimit->min_limit));
        return $maxLimit;
    }

    public function getUserLimitForExport(UserCreditLimitForm $form)
    {
        $packageService = new PackageService($form->packageName);
        Yii::error($form->toArray(), 'getUserLimitForExport');
        $sourceId = $packageService->getSourceId();
        /**
         * @var LoanPerson $user
         */
        $user = LoanPerson::find()
            ->where(['pan_code' => $form->panNo])
            ->andWhere(['source_id' => $sourceId])
            ->limit(1)
            ->one();


        //本平台新客
        if(is_null($user) || LoanPerson::CUSTOMER_TYPE_NEW == $user->customer_type)
        {
            $isAllNew = LoanPersonExternal::isAllPlatformNewCustomer($form->panNo);
            /**
             * @var ProductSetting $product
             */
            $product = ProductSetting::find()
                ->where(['package_name' => $form->packageName])
                ->andWhere(['is_internal' => ProductSetting::IS_EXTERNAL_YES])
                ->limit(1)
                ->one();

            if($isAllNew)
            {
                $disbursalAmount = $product->default_credit_limit;
            }else{
                //全老本新历史最大逾期天数>10则额度为2000
                if(OrderService::allPlatformOverdueMaxDayByPan($form->panNo) >= 10)
                {
                    $disbursalAmount = 2000;
                }else{
                    $disbursalAmount = $product->default_credit_limit_2;
                }
            }
            $minAmount = 1700;
        }else{
            $disbursalAmount = $this->getUserMaxLimit($user->id);
            $minAmount = $this->getUserMinLimit($user->id);

        }
        $this->setResult([
            'disbursalAmount' => $disbursalAmount,
            'minAmount'       => $minAmount,
        ]);
        return true;
    }

    /**
     * 获取用户额度列表 单位元
     * @param int $maxLimit  最大额度 单位元
     * @param int $minLimit 最小额度 单位元
     * @param int $step 步长 单位元
     * @return array
     */
    public function getLimitList(int $maxLimit,int $minLimit = 1700,int $step = 100) : array
    {
        if($maxLimit <= $minLimit)
        {
            return [
                $maxLimit
            ];
        }

        $list = [$maxLimit];

        while (($maxLimit - $step) >= $minLimit )
        {
            $maxLimit -= $step;
            $list[] = $maxLimit;
        }
        return $list;
    }

    public function getCreditByUserId($user_id, $type){
        $model = UserCreditLimit::findOne(['user_id' => $user_id, 'type' => $type]);
        if (!$model) {
            UserCreditLimit::initUserCredit($user_id, $type);
            $model = UserCreditLimit::findOne(['user_id' => $user_id, 'type' => $type]);
        }

        return $model;
    }

    /**
     * 修改用户额度
     * @param $userId
     * @param $limit
     */
    public function changeLimit($userId,$limit){
        $model = $this->getCreditByUserId($userId, UserCreditLimit::TYPE_7_DAY);
        $beforeMaxLimit = $model->max_limit;
        $afterMaxLimit = $limit;
        $beforeMinLimit = $model->min_limit;
        $afterMinLimit = min(120000, $limit);

        $model->max_limit = $afterMaxLimit;
        $model->min_limit = $afterMinLimit;
        $model->save();
        if($beforeMaxLimit != $afterMaxLimit || $beforeMinLimit != $afterMinLimit){
            $changeLog = new UserCreditLimitChangeLog();
            $changeLog->user_id = $userId;
            $changeLog->before_max_limit = $beforeMaxLimit;
            $changeLog->after_max_limit = $afterMaxLimit;
            $changeLog->before_min_limit = $beforeMinLimit;
            $changeLog->after_min_limit = $afterMinLimit;
            $changeLog->type = UserCreditLimit::TYPE_7_DAY;
            $changeLog->save();
        }
    }

}
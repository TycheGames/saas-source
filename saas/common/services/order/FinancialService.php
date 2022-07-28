<?php
namespace common\services\order;

use common\models\order\FinancialLoanRecord;
use common\models\order\UserLoanOrder;
use common\models\pay\LoanPayForm;
use common\services\BaseService;
use common\services\loan\LoanService;
use common\services\message\DingDingService;
use common\services\message\WeWorkService;
use yii;

class FinancialService extends BaseService {

    const LOAN_STATUS_SUCCESS = 'SUCCESS'; //打款成功
    const LOAN_STATUS_FAILURE = 'FAILURE'; //打款失败
    const LOAN_STATUS_PENDING = 'PENDING'; //打款处理中



    /**
     *  打款状态查询 2020-04-07
     * @param FinancialLoanRecord $financialLoanRecord
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function loanQuery(FinancialLoanRecord $financialLoanRecord)
    {
        $service = $financialLoanRecord->getPayoutService();
        if($service->loanQueryHandle($financialLoanRecord->order_id, $financialLoanRecord->trade_no)){
            switch ($service->loanPayStatus)
            {
                case self::LOAN_STATUS_SUCCESS:
                    $this->setResult("放款成功");
                    return $this->loanSuccessHandle($financialLoanRecord, $service->financialLoanCallback);
                    break;
                case self::LOAN_STATUS_FAILURE:
                    $this->setResult("放款失败");
                    return $this->loanFailureHandle($financialLoanRecord, $service->financialLoanCallback);
                    break;
                case self::LOAN_STATUS_PENDING:
                    $this->setResult("进行中");
                    return $this->loanPendingHandle($financialLoanRecord,  $service->financialLoanCallback);
                    break;
            }
        }



    }


    /**
     * 打款成功处理方法
     * @param FinancialLoanRecord $financialLoanRecord
     * @param array $financialLoanCallback
     * @return bool
     */
    public function loanSuccessHandle(FinancialLoanRecord $financialLoanRecord, $financialLoanCallback)
    {
        Yii::info("order_id:{$financialLoanRecord->business_id},service_type:{$financialLoanRecord->service_type},result:" . json_encode($financialLoanCallback, JSON_UNESCAPED_UNICODE), "financial_loan");
        $financialLoanRecord->status = FinancialLoanRecord::UMP_PAY_SUCCESS;

        foreach ($financialLoanCallback as $key => $value)
        {
            $financialLoanRecord->$key = $value;
        }
        $financialLoanRecord->save();
        $order = UserLoanOrder::findOne(['id' => $financialLoanRecord->business_id]);
        $loanService = new LoanService();
        return $loanService->loanSuccessCallback($order, $financialLoanRecord->success_time);
    }



    /**
     * 打款失败处理方法
     * @param FinancialLoanRecord $financialLoanRecord
     * @param array $financialLoanCallback
     * @return bool
     */
    public function loanFailureHandle(FinancialLoanRecord $financialLoanRecord, $financialLoanCallback)
    {
        Yii::error("order_id:{$financialLoanRecord->business_id},service_type:{$financialLoanRecord->service_type},result:" . json_encode($financialLoanCallback, JSON_UNESCAPED_UNICODE), "financial_loan");

        foreach ($financialLoanCallback as $key => $value)
        {
            $financialLoanRecord->$key = $value;
        }
        if(FinancialLoanRecord::SERVICE_TYPE_MPURSE != $financialLoanRecord->service_type
            && date('H') < '23' && $financialLoanRecord->retry_num < 5
            && date('Y-m-d') == date('Y-m-d', $financialLoanRecord->created_at)){
            $time = min(strtotime(date('Y-m-d 22:50:00')) - time(), 3600);
            if($time > 300){
                if($financialLoanRecord->service_type == FinancialLoanRecord::SERVICE_TYPE_JOLOSOFT){
                    $arr = explode('_', $financialLoanRecord->order_id);
                    $arr[2] = ($arr[2] ?? 0) + 1;
                    $financialLoanRecord->order_id = implode('_', $arr);
                }

                $financialLoanRecord->retry_num = $financialLoanRecord->retry_num + 1;
                $financialLoanRecord->retry_time = time() + mt_rand(300, $time);
                $financialLoanRecord->status = FinancialLoanRecord::UMP_PAYING;
                $financialLoanRecord->save();
                return true;
            }
        }

        $financialLoanRecord->status = FinancialLoanRecord::UMP_PAY_HANDLE_FAILED;
        $financialLoanRecord->save();
        $service = new WeWorkService();
        $message = sprintf('[order_id:%s] : 打款失败，需人工处理',
            $financialLoanRecord->business_id);
        $service->send($message);
        return true;
    }



    /**
     * 打款进行中处理方法
     * @param FinancialLoanRecord $financialLoanRecord
     * @param array $financialLoanCallback
     * @return bool
     */
    public function loanPendingHandle(FinancialLoanRecord $financialLoanRecord, $financialLoanCallback)
    {
        Yii::info("order_id:{$financialLoanRecord->business_id},service_type:{$financialLoanRecord->service_type},result:" . json_encode($financialLoanCallback, JSON_UNESCAPED_UNICODE), "financial_loan");
        $financialLoanRecord->updated_at = time();
        $financialLoanRecord->status = FinancialLoanRecord::UMP_CMB_PAYING;
        foreach ($financialLoanCallback as $key => $value)
        {
            $financialLoanRecord->$key = $value;
        }
        return $financialLoanRecord->save();
    }



    /**
     * 打款服务
     * @param FinancialLoanRecord $financialLoanRecord
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function doPayMoney(FinancialLoanRecord $financialLoanRecord){
        $financialLoanRecord->status = FinancialLoanRecord::UMP_PAY_WAITING;
        $financialLoanRecord->save();

        try{
            $form = new LoanPayForm();
            $form->beneName = $financialLoanRecord->loanPerson->name;
            $form->beneAccNo = $financialLoanRecord->account;
            $form->bankName = $financialLoanRecord->bank_name;
            $form->beneIFSC = $financialLoanRecord->ifsc;
            $form->txnId = $financialLoanRecord->order_id;
            $form->amount = $financialLoanRecord->money;
            $form->remark = 'payout';
            $form->userID = $financialLoanRecord->user_id;
            $form->beneMobile = $financialLoanRecord->loanPerson->phone;
            $service = $financialLoanRecord->getPayoutService();
            if($service->loanPayHandle($form))
            {
                switch ($service->loanPayStatus)
                {
                    case self::LOAN_STATUS_SUCCESS:
                        $this->setResult("放款成功");
                        return $this->loanSuccessHandle($financialLoanRecord, $service->financialLoanCallback);
                        break;
                    case self::LOAN_STATUS_FAILURE:
                        $this->setResult("放款失败");
                        return $this->loanFailureHandle($financialLoanRecord, $service->financialLoanCallback);
                        break;
                    case self::LOAN_STATUS_PENDING:
                        $this->setResult("进行中");
                        return $this->loanPendingHandle($financialLoanRecord, $service->financialLoanCallback);
                        break;
                }
            }

        }catch (\Exception $ex)
        {
            \Yii::error([
                'order_id' => $financialLoanRecord->business_id,
                'code'     => $ex->getCode(),
                'message'  => $ex->getMessage(),
                'line'     => $ex->getLine(),
                'file'     => $ex->getFile(),
                'trace'    => $ex->getTraceAsString(),
            ], 'financial');
            $service = new WeWorkService();
            $message = sprintf('[%s][%s][%s]异常[order_id:%s] : %s in %s:%s',
                YII_ENV, \yii::$app->id, Yii::$app->requestedRoute, $financialLoanRecord->business_id, $ex->getMessage(), $ex->getFile(), $ex->getLine());
            $service->send($message);
        }

    }


}

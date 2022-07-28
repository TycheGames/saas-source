<?php

namespace common\services\product;

use common\helpers\CommonHelper;
use common\models\order\UserLoanOrder;
use common\models\product\ProductPeriodSetting;
use common\models\product\ProductSetting;
use common\services\BaseService;
use common\services\user\UserCreditLimitService;
use frontend\models\loan\ConfirmLoanV2Form;


class ProductService extends BaseService
{

    /**
     * 获取确认借款信息
     * @param int $productId 借款产品id
     * @param int $days 借款天数
     * @param int $amount 借款金额 单位元
     * @param string $host
     * @return bool
     */
    public function getConfirmLoan(int $productId, int $days, int $amount, string $host)
    {
        $product = ProductSetting::findOne($productId);
        if (is_null($product) || $product->productPeriodSetting->loan_term != $days) {
            $this->setError('params err');
            return false;
        }

        $data = [
            'charge'        => $product->totalFeeCalc($amount),
            'dueDate'       => $product->getRepayDate(),
            'overdueCharge' => $product->overdue_rate,
            'contractUrl'   => [
                'loanService'       => $host . '/h5/#/LoanServiceContract?amount='.$amount.'&productId='.$productId.'&days='.$days,
                'userCommissioned'  => $host . '/h5/#/userCommissionedAgreement',
                'userAuthorization' => $host . '/h5/#/userAuthorizationAgreement',
            ],
        ];
        $this->setResult($data);
        return true;
    }


    /**
     * 新借款确认接口
     * @param ConfirmLoanV2Form $validateModel
     * @return bool
     */
    public function getConfirmLoanV2(ConfirmLoanV2Form $validateModel)
    {
        //合规配置，页面显示和下单两边都要修改
//        $appMarketFlag = $validateModel->clientInfo['appMarket'] == 'bigshark_google';
        if (in_array($validateModel->userId, [1187501, 1244268, 840593])) {
            //todo::写入指定的用户id
            $product = ProductSetting::findOne(['merchant_id' => -1, 'is_internal' => 1]);
        } else {
            $product = ProductSetting::findOne(['package_name' => $validateModel->packageName, 'is_internal' => 1]);
        }
        if (is_null($product)) {
            $this->setError('params err');
            return false;
        }

        $period = ProductPeriodSetting::findOne($product->period_id);
        if(is_null($period))
        {
            $this->setError('params err');
            return false;
        }

        //获取用户最大额度
        $userCreditLimitService = new UserCreditLimitService();
        $disbursalAmount = $userCreditLimitService->getUserMaxLimit($validateModel->userId, $product->id);
        $minAmount = $userCreditLimitService->getUserMinLimit($validateModel->userId);

        if(!empty($validateModel->orderId))
        {
            //用户提现流程
            /** @var  UserLoanOrder $order */
            $order = UserLoanOrder::find()->where(['user_id' => $validateModel->userId, 'id' => $validateModel->orderId])->one();
            $applyMoney = intval(CommonHelper::CentsToUnit($order->disbursalAmount()));
            if(UserLoanOrder::AUTO_DRAW_YES == $order->auto_draw)
            {
                $withdrawalsType = 'AMOUNT_RAISED';
                $countdown = max(0, $order->auto_draw_time - time());
                $minAmount = $applyMoney;
            }else{
                $withdrawalsType = 'DECREASE';
            }

            $disbursalAmount = intval(CommonHelper::CentsToUnit($order->credit_limit));
        }
        //判断用户传入的额度是否超过他的最大额度
        if($validateModel->disbursalAmount > $disbursalAmount)
        {
            $this->setError('Exceeding maximum limit');
            return false;
        }
        $disbursalAmountList = $userCreditLimitService->getLimitList($disbursalAmount, $minAmount);
        //如果表单的放款金额不为0，说明用户选择了金额
        if($validateModel->disbursalAmount > 0)
        {
            $disbursalAmount = $validateModel->disbursalAmount;
        }
        if(ProductSetting::SHOW_DAYS_YES == $product->show_days)
        {
            $days = $period->loan_term;
        }else{
            $days = null;
        }
        $data = [
            'disbursalAmount'     => $disbursalAmount,
            'duration'            => $days,
            'repaymentAmount'     => $product->totalRepaymentAmount($disbursalAmount),
            'repaymentDate'       => date('d/m/Y', strtotime($product->getRepayDate())),
            'productId'           => $product->id,
            'disbursalAmountList' => $disbursalAmountList,
            'dailyInterest'       => $product->day_rate,
            'agreementList'       => [
                [
                    'title' => 'Terms and Conditions',
                    'url'   => $validateModel->hostInfo . '/h5/#/summary',
                ],
//                [
//                    'title' => 'Sanction Letter',
//                    'url'   => $validateModel->hostInfo . "/h5/#/sanctionLetter?productId={$product->id}&amount={$disbursalAmount}&days={$days}",
//                ],
            ],
            'repaymentDetail'     => [
                'interest'        => $product->interestsCalc($disbursalAmount),
                'fee'             => $product->processingFees($disbursalAmount),
                'gst'             => $product->gst($disbursalAmount),
                'principalAmount' => $product->amount($disbursalAmount),
            ],
            'withdrawalsType' => $withdrawalsType ?? null,
            'applyMoney' => $applyMoney ?? null,
            'countdown' => $countdown ?? null
        ];
        $this->setResult($data);
        return true;
    }

    /**
     * 到期日计算
     * @param $loanMethod
     * @param $loanTerm
     * @param $periods
     * @param int $beginTime
     * @return false|string
     */
    public static function repayDateCalc($loanMethod, $loanTerm, $periods, $beginTime = 0)
    {
        if (0 == $beginTime) {
            $beginTime = time();
        }
        //todo 暂时只写短期一次性逻辑
        return date('Y-m-d', $beginTime + 86400 * ($loanTerm));
    }
}

<?php
namespace common\services\pay;

use common\models\order\FinancialLoanRecord;
use common\models\pay\PayoutAccountInfo;
use common\models\user\LoanPerson;
use common\models\user\UserBankAccount;
use common\services\BaseService;
use yii\db\Exception;


/**
 * Class BasePayService
 * @package common\services\pay
 *
 */
class BasePayoutService extends BaseService
{

    protected $accountId;
    protected $serviceType;
    protected $payAccountSetting;
    protected $merchantID;


    public function __construct(PayoutAccountInfo $payAccountSetting,$config = [])
    {
        $this->accountId = $payAccountSetting->id;
        $this->serviceType = $payAccountSetting->service_type;
        $this->payAccountSetting = $payAccountSetting;
        $this->merchantID = $payAccountSetting->merchant_id;
        parent::__construct($config);
    }

    /**
     * 生成打款订单
     * @param $data
     */
    public function createFinancialLoanRecord($data){
        try {
            $user_id      = $data['user_id'];//借款人ID
            $bind_card_id = intval($data['bind_card_id']);//绑卡自增表ID
            $business_id  = intval($data['business_id']);//业务订单主键ID
            $money        = $data['money'];//打款金额
            $ifsc         = $data['ifsc'];
            $bank_name    = $data['bank_name'];//银行名称
            $account      = $data['account'];//银行卡号
            if (empty($bind_card_id) || empty($business_id) || empty($money) || ($money <= 0 ) || empty($account) || empty($user_id)) {
                throw new Exception("抱歉，缺少必要的参数！");
            }

            $loan_data = FinancialLoanRecord::find()->where([
                'business_id' => $business_id])->one();
            if (!empty($loan_data)) {
                throw new Exception("抱歉，正在处理的打款订单号，不能重复添加！");
            }

            $loan_person = LoanPerson::findOne($user_id);
            if (empty($loan_person)) {
                throw new Exception("抱歉，非平台用户");
            }

            $card_info = UserBankAccount::findOne($bind_card_id);
            if (empty($card_info)) {
                throw new Exception("抱歉，银行卡不存在");
            }

            $query = new FinancialLoanRecord();
            $query->user_id      = $user_id;
            $query->order_id     = self::generateOrderId();
            $query->bind_card_id = $bind_card_id;
            $query->business_id  = $business_id;
            $query->status       = FinancialLoanRecord::UMP_PAYING;
            $query->money        = $money;
            $query->ifsc         = $ifsc;
            $query->bank_name    = $bank_name;
            $query->account      = $account;
            $query->payout_account_id = $this->accountId;
            $query->pay_account_id = 0;
            $query->service_type = $this->serviceType;
            $query->merchant_id = $this->merchantID;
            if ($query->save()) {
                return [
                    'code' => 0,
                    'message' => '插入成功',
                ];
            }
        }
        catch (Exception $e) {
            return [
                'code' => 1,
                'message' => $e->getMessage(),
            ];
        }

    }


    /**
     * 生成订单号
     */
    protected static function generateOrderId()
    {
        $uniqid = "_" . uniqid(rand(0, 9));

        $order_id = date('Ymd') . "{$uniqid}";
        return $order_id;
    }



}
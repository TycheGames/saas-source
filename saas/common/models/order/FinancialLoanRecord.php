<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2015/9/11
 * Time: 17:30
 */

namespace common\models\order;

use common\models\pay\PayAccountSetting;
use common\models\pay\PayoutAccountInfo;
use common\models\user\LoanPerson;
use common\services\pay\CashFreePayoutService;
use common\services\pay\CashFreePaymentService;
use common\services\pay\JolosoftPayoutService;
use common\services\pay\MpursePayoutService;
use common\services\pay\MpurseService;
use common\services\pay\PaytmPayoutService;
use common\services\pay\PaytmService;
use common\services\pay\QimingPayoutService;
use common\services\pay\RazorpayPayoutService;
use common\services\pay\RazorpayService;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * 财务打款记录表
 * @property int $id
 * @property string $trade_no
 * @property string $utr
 * @property string $order_id
 * @property int $user_id
 * @property int $pay_account_id
 * @property int $payout_account_id
 * @property  int $service_type
 * @property int $bind_card_id
 * @property int $business_id 业务id
 * @property int $merchant_id 商务id
 * @property int $money 打款金额, 单位分
 * @property int $status 打款状态
 * @property int $retry_num 重试次数
 * @property int $retry_time 重试时间
 * @property string $ifsc
 * @property string $bank_name
 * @property string $account
 * @property string $result 打款结果
 * @property string $notify_result
 * @property int $success_time 打款成功时间
 * @property int $created_at 创建时间
 * @property int $updated_at 更新时间
 *
 * 关联
 * @property LoanPerson $loanPerson
 * @property PayAccountSetting $payAccountSetting
 * @property PayoutAccountInfo $payoutAccountInfo
 */
class FinancialLoanRecord extends ActiveRecord
{

    const UMP_PAYING      = 1;
    const UMP_PAY_WAITING = 2;
    const UMP_PAY_FAILED  = 3;
    const UMP_PAY_SUCCESS = 4;
    const UMP_CMB_PAYING  = 5;
    const UMP_PAY_HANDLE_FAILED  = 6;
    const UMP_PAY_REQUEST_FAILED  = 7;
    const UMP_PAYOUT_REVERSED = 10;


    const SERVICE_TYPE_RAZORPAY = 1;
    const SERVICE_TYPE_MPURSE = 2;
    const SERVICE_TYPE_CASHFREE = 3;
    const SERVICE_TYPE_PAYTM = 4;
    const SERVICE_TYPE_JOLOSOFT = 5;
    const SERVICE_TYPE_QIMING = 6;

    public static $service_type_map = [
        self::SERVICE_TYPE_RAZORPAY => 'razorpay',
        self::SERVICE_TYPE_MPURSE => 'mpurse',
        self::SERVICE_TYPE_CASHFREE => 'cashfree',
        self::SERVICE_TYPE_PAYTM => 'paytm',
        self::SERVICE_TYPE_JOLOSOFT => 'jolosoft',
        self::SERVICE_TYPE_QIMING => 'qiming',
    ];

    public static $ump_pay_status = [
        self::UMP_PAYING             => 'Default',
        self::UMP_PAY_WAITING        => 'Pending payout',
        self::UMP_PAY_FAILED         => 'Failed',
        self::UMP_PAY_SUCCESS        => 'Success',
        self::UMP_CMB_PAYING         => 'Paying',
        self::UMP_PAY_HANDLE_FAILED  => 'Need to be handled manually',
        self::UMP_PAY_REQUEST_FAILED => 'Request failed to be handled manually',
        self::UMP_PAYOUT_REVERSED    => 'abnormal',

    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%financial_loan_record}}';
    }

    /**
     * @return object|\yii\db\Connection|null
     * @throws \yii\base\InvalidConfigException
     */
    public static function getDb()
    {
        return Yii::$app->get('db');
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    public static function addLock($id)
    {
        $key = "FinancialLoanRecord_lock_$id";
        if (1 == \Yii::$app->redis->executeCommand('INCRBY', [$key, 1])) {
            \Yii::$app->redis->executeCommand('EXPIRE', [$key, 2]);
            return true;
        } else {
            \Yii::$app->redis->executeCommand('EXPIRE', [$key, 2]);
        }
        return false;
    }

    public function getLoanPerson()
    {
        return $this->hasOne(LoanPerson::class, ['id' => 'user_id']);
    }

    public function getPayAccountSetting()
    {
        return $this->hasOne(PayAccountSetting::class, ['id' => 'pay_account_id']);
    }

    public function getPayoutAccountInfo()
    {
        return $this->hasOne(PayoutAccountInfo::class, ['id' => 'payout_account_id']);
    }



    /**
     * 获取支付类
     * @return CashFreePayoutService|MpursePayoutService|RazorpayPayoutService|PaytmPayoutService|JolosoftPayoutService|QimingPayoutService
     */
    public function getPayoutService()
    {
        switch ($this->service_type)
        {
            case self::SERVICE_TYPE_RAZORPAY:
                return new RazorpayPayoutService($this->payoutAccountInfo);
                break;
            case self::SERVICE_TYPE_MPURSE:
                return new MpursePayoutService($this->payoutAccountInfo);
                break;
            case self::SERVICE_TYPE_CASHFREE:
                return new CashFreePayoutService($this->payoutAccountInfo);
                break;
            case self::SERVICE_TYPE_PAYTM:
                return new PaytmPayoutService($this->payoutAccountInfo);
                break;
            case self::SERVICE_TYPE_JOLOSOFT:
                return new JolosoftPayoutService($this->payoutAccountInfo);
                break;
            case self::SERVICE_TYPE_QIMING:
                return new QimingPayoutService($this->payoutAccountInfo);
                break;
        }
    }
}
<?php

namespace common\models\order;
use backend\models\Merchant;
use common\helpers\CommonHelper;
use common\helpers\RedisQueue;
use common\models\ClientInfoLog;
use common\models\fund\LoanFund;
use common\models\kudos\LoanKudosOrder;
use common\models\product\ProductSetting;
use common\models\question\UserQuestionVerification;
use common\models\user\LoanPerson;
use common\models\user\LoanPersonExternal;
use common\models\user\UserBankAccount;
use common\models\user\UserBasicInfo;
use common\models\user\UserContact;
use common\models\user\UserCreditReportCibil;
use common\models\user\UserCreditReportExperian;
use common\models\user\UserCreditReportFrLiveness;
use common\models\user\UserCreditReportFrVerify;
use common\models\user\UserCreditReportOcrAad;
use common\models\user\UserCreditReportOcrPan;
use common\models\user\UserPanCheckLog;
use common\models\user\UserWorkInfo;
use common\services\product\ProductService;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Exception;

/**
 * This is the model class for table "{{%user_loan_order}}".
 *
 * 表属性
 * @property int $id
 * @property string $order_uuid 展示给用户看的订单号
 * @property int $user_id 用户id
 * @property int $product_id 产品ID
 * @property int $amount 金额，单位为分
 * @property string $day_rate 日利率
 * @property int $interests 总共利息，单位分
 * @property int $overdue_fee 单位：分，滞纳金，脚本跑出来，当还款的时候重新计算进行核对
 * @property string $overdue_rate 滞纳金日利率，单位为百分之几
 * @property int $cost_fee 手续费，单位为分
 * @property string $cost_rate 一整期手续费利率，单位为百分之几
 * @property int $loan_method 期数单位：0-按天 1-按月 2-按年
 * @property int $loan_term 每期的时间周期，根据loan_method确定单位
 * @property int $periods 多少期
 * @property int $card_id 银行卡ID
 * @property int $loan_status 支付状态
 * @property int $audit_operator
 * @property int $status 状态
 * @property int $audit_status 审核状态
 * @property int $audit_bank_status 审核绑卡状态
 * @property int $bank_num 绑卡次数
 * @property int audit_bank_operator 绑卡审核人ID
 * @property int audit_question 电核问题
 * @property int audit_remark  审核备注
 * @property int $audit_begin_time 领取审核订单时间
 * @property int $audit_bank_begin_time 领取绑卡审核订单时间
 * @property int $order_time 下单时间
 * @property int $loan_time 放款时间，用于计算利息的起止时间
 * @property int $audit_time 订单审核时间
 * @property int $is_first 是否是首单，0，不是；1，是
 * @property int $is_all_first 是否全局首单，0：不是；1：是
 * @property string $app_market 下单app名
 * @property string $client_info 客户端信息
 * @property string $device_id 设备号
 * @property string $ip ip地址
 * @property string $black_box 同盾用户标识
 * @property int $created_at 创建时间
 * @property int $updated_at 更新时间
 * @property int $fund_id 资方ID
 * @property int $gst_fee GST 增值税，是手续费的18%
 * @property string $did 数盟设备指纹
 * @property string $credit_limit 授信额度
 * @property int $merchant_id 商户id
 * @property int $is_export 是否外部订单 0-否 1-是
 * @property int $old_credit_limit 老授信额度
 * @property string $auto_draw 是否自动提现 y n
 * @property int $auto_draw_time 自动提现期限
 *
 * 关联表
 * @property LoanPerson $loanPerson
 * @property UserLoanOrderRepayment $userLoanOrderRepayment
 * @property UserLoanOrderExtraRelation $userLoanOrderExtraRelation
 * @property UserBankAccount $userBankAccount
 * @property UserWorkInfo $userWorkInfo
 * @property UserBasicInfo $userBasicInfo
 * @property UserContact $userContact
 * @property UserCreditReportFrLiveness $userCreditechFr
 * @property UserCreditReportFrVerify $userFrFr 人脸对比人脸报告
 * @property UserCreditReportFrVerify $userFrPan 人脸对比Pan报告
 * @property UserCreditReportOcrPan $userCreditechOCRPan Pan卡OCR报告
 * @property UserCreditReportOcrAad $userCreditechOCRAadhaar Aad卡OCR报告
 * @property UserPanCheckLog $userVerifyPan
 * @property UserCreditReportCibil $userCreditReportCibil
 * @property UserCreditReportExperian $userCreditReportExperian
 * @property LoanKudosOrder $loanKudosOrder
 * @property ProductSetting $productSetting
 * @property ClientInfoLog $clientInfoLog
 * @property UserQuestionVerification $userQuestionReport
 * @property LoanFund $loanFund;
 * @property Merchant $merchant;
 */
class UserLoanOrder extends ActiveRecord
{
    //自动提现
    const AUTO_DRAW_YES = 'y';
    const AUTO_DRAW_NO = 'n';

    const IS_EXPORT_YES = 1; //是外部订单
    const IS_EXPORT_NO = 0; //非外部订单

    const FIRST_LOAN_IS = 1; //是首单
    const FIRST_LOAN_NO = 0; //不是首单

    const REVIEW_PASS = 1;  //审核通过
    const REVIEW_REJECT = 2; //审核拒绝

    const EVENT_AFTER_CHANGE_STATUS = 'afterChangeStatus';//在更新状态后


    public static $first_loan_map = [
        self::FIRST_LOAN_NO => 'NO',
        self::FIRST_LOAN_IS => 'YES',
    ];

    const AUDIT_STATUS_GET_DATA = 1; //待获取数据
    const AUDIT_STATUS_AUTO_CHECK = 2; //待机审
    const AUDIT_STATUS_GET_ORDER = 3; //待领取订单
    const AUDIT_STATUS_MANUAL_CHECK = 4; //待人审
    const AUDIT_STATUS_MANUAL_CHECK_FINISH = 5; //人审已处理

    const AUDIT_BANK_STATUS_GET_ORDER = 1; //待领取绑卡订单
    const AUDIT_BANK_STATUS_MANUAL_CHECK = 2; //待人审绑卡

    //支付状态
    const LOAN_STATUS_ERROR = -30; //推送失败

    const LOAN_STATUS_WAIT_BIND_CARD = 0;//待绑卡
    const LOAN_STATUS_BIND_CARD_REJECT = -1; //绑卡驳回
    const LOAN_STATUS_BIND_CARD_CHECK = 1; //绑卡审核
    const LOAN_STATUS_DRAW_MONEY = 2; //待提现
    const LOAN_STATUS_FUND_MATCH = 10;  //资方匹配
    const LOAN_STATUS_FUND_MATCHED = 20; //已分配资方
    const LOAN_STATUS_PAY = 30; //放款中
    const LOAN_STATUS_LOAN_SUCCESS = 40; //放款成功 开始推送kudos订单
    const LOAN_STATUS_PUSH = 50; //已生成kudos订单



    //订单状态 展示给用户的状态，只有这8种，不允许新增或减少
    const STATUS_CHECK = 10; //审核中
    const STATUS_WAIT_DEPOSIT = 20; //待绑卡
    const STATUS_WAIT_DRAW_MONEY = 21; //待提现
    const STATUS_LOANING = 30; //放款中
    const STATUS_LOAN_COMPLETE = 40; //已放款
    const STATUS_PAYMENT_COMPLETE = 50; //已还款
    const STATUS_OVERDUE = 100; //逾期中
    const STATUS_CHECK_REJECT = -10; //审核驳回
    const STATUS_DEPOSIT_REJECT = -20; //绑卡驳回
    const STATUS_WAIT_DRAW_MONEY_TIMEOUT = -21; //提现超时
    const STATUS_LOAN_REJECT = -30; //放款驳回

    //进行中的订单状态
    public static $opening_order_status = [
        self::STATUS_CHECK,
        self::STATUS_WAIT_DEPOSIT,
        self::STATUS_WAIT_DRAW_MONEY,
        self::STATUS_LOAN_COMPLETE,
        self::STATUS_LOANING,
        self::STATUS_OVERDUE,
    ];

    //已完结的订单状态
    public static $end_order_status = [
        self::STATUS_PAYMENT_COMPLETE,
        self::STATUS_DEPOSIT_REJECT,
        self::STATUS_CHECK_REJECT,
        self::STATUS_LOAN_REJECT,
        self::STATUS_WAIT_DRAW_MONEY_TIMEOUT,
    ];

    public static $order_status_map = [
        self::STATUS_CHECK            => 'auditing',
        self::STATUS_WAIT_DEPOSIT     => 'bind card',
        self::STATUS_WAIT_DRAW_MONEY  => 'waiting withdraw',
        self::STATUS_LOANING          => 'loaning',
        self::STATUS_LOAN_COMPLETE    => 'loan complete',
        self::STATUS_PAYMENT_COMPLETE => 'repayment complete',
        self::STATUS_CHECK_REJECT     => 'check reject',
        self::STATUS_DEPOSIT_REJECT   => 'deposit reject',
        self::STATUS_LOAN_REJECT      => 'loan reject',
        self::STATUS_OVERDUE          => 'overdue',
        self::STATUS_WAIT_DRAW_MONEY_TIMEOUT          => 'withdraw timeout',
    ];


    public static $order_loan_status_map = [
        self::LOAN_STATUS_ERROR => 'loan error', //推送失败
        self::LOAN_STATUS_WAIT_BIND_CARD => 'wait bind card',//待绑卡
        self::LOAN_STATUS_DRAW_MONEY => 'waiting withdraw', //卢山要求调整文案，日期2019年12月09日20:37:54
        self::LOAN_STATUS_BIND_CARD_REJECT => 'bind card reject', //绑卡驳回
        self::LOAN_STATUS_BIND_CARD_CHECK => 'bind card check', //绑卡审核
        self::LOAN_STATUS_FUND_MATCH => 'fund match',  //资方匹配
        self::LOAN_STATUS_FUND_MATCHED => 'fund matched', //已分配资方
        self::LOAN_STATUS_PAY => 'loaning', //放款中
        self::LOAN_STATUS_LOAN_SUCCESS => 'loan success', //放款成功 开始推送kudos订单
        self::LOAN_STATUS_PUSH => 'push finish', //已生成kudos订单
    ];

    public function init()
    {
        parent::init();
        $this->on(self::EVENT_AFTER_CHANGE_STATUS, ['common\services\order\OrderEventHandler', 'remindByOderStatus']);
    }

    /**
     * 生成借款订单
     * @param $userId
     * @param $productId
     * @param $amount
     * @param $dayRate
     * @param $interests
     * @param $costRate
     * @param $costFee
     * @param $lateFeeRate
     * @param $cardId
     * @param $loanMethod
     * @param $loanTerm
     * @param $periods
     * @param $gstFee
     * @param $clientInfo
     * @param $deviceId
     * @param $ip
     * @param $blackbox
     * @param $did
     * @param $maxLimit
     * @param $merchant_id
     * @param $isExport
     * @param $orderUUID
     * @param $isAllFirst
     * @return UserLoanOrder|null
     * @throws Exception
     */
    public static function generateOrder(
        $userId, $productId, $amount, $dayRate,
        $interests, $costRate, $costFee, $lateFeeRate,
        $cardId, $loanMethod, $loanTerm, $periods,$gstFee,
        $did, $maxLimit,
        $clientInfo, $deviceId, $ip, $blackbox,$merchant_id, $isExport, $orderUUID = '', $isAllFirst = null
    )
    {

        $loanPerson = LoanPerson::findOne($userId);
        if($loanPerson->customer_type){
            $is_first = self::FIRST_LOAN_NO;
        }else{
            $is_first = self::FIRST_LOAN_IS;
        }
        if (is_null($isAllFirst)) {
            $isAllFirst = LoanPersonExternal::isAllPlatformNewCustomer($loanPerson->pan_code) ?
                self::FIRST_LOAN_IS : self::FIRST_LOAN_NO;
        }

        $order = new self();
        $order->user_id = $userId;
        $order->order_uuid = empty($orderUUID) ? CommonHelper::generateUuid() : $orderUUID;
        $order->product_id = $productId;
        $order->merchant_id = $merchant_id;
        $order->is_export = intval($isExport);
        $order->amount = $amount;
        $order->gst_fee = $gstFee;
        $order->day_rate = $dayRate;
        $order->interests = $interests;
        $order->cost_rate = $costRate;
        $order->cost_fee = $costFee;
        $order->overdue_rate = $lateFeeRate;
        $order->card_id = $cardId;
        $order->old_credit_limit = $maxLimit;
        $order->order_time = time();
        $order->status = self::STATUS_CHECK;
        $order->audit_status = self::AUDIT_STATUS_GET_DATA;
        $order->is_first = $is_first;
        $order->is_all_first = $isAllFirst;
        $order->loan_method = $loanMethod;
        $order->loan_term = $loanTerm;
        $order->periods = $periods;
        $order->client_info = $clientInfo;
        $order->device_id = $deviceId;
        $order->ip = $ip;
        $order->black_box = $blackbox;
        $order->did = $did;
        if($order->save()){
            return $order;
        }else{
            throw new Exception(implode(',',$order->getErrorSummary(true)));
        }

    }

    /**
     * 获取总借款天数
     * @return integer
     */
    public function getTotalLoanTerm()
    {
        return $this->loan_term;
    }

    /**
     * 获取到期日 生成还款计划表之后，需调用UserLoanOrderRepayment的getPlanRepaymentDate方法
     * @return string  Y-m-d
     */
    public function getRepayDate() : string
    {
        return ProductService::repayDateCalc(
            $this->loan_method,
            $this->loan_term,
            $this->periods,
            time()
        );
    }



    /**
     * 获取逾期计息时间
     * @return string
     */
    public function getFeeDate() : string
    {
        return date('Y-m-d',strtotime($this->getRepayDate()) + 86400);
    }

    /**
     * 计算总应还金额
     * @return mixed
     */
    public function calcTotalMoney()
    {
        return $this->amount //本金
            + $this->interests  //利息
            + $this->overdue_fee; //滞纳金
//            + $this->cost_fee; //手续费
    }

    /**
     * 借款金额 单位分  放款金额 + 手续费 + GST + 利息
     * @return int
     */
    public function loanAmount(){
        return $this->amount + $this->interests;
    }

    /**
     * 放款金额 单位分
     * @return int
     */
    public function disbursalAmount(){
        return $this->amount - $this->cost_fee;
    }


    /**
     * 年化率
     * @return float|int
     */
    public function yearRate()
    {
        return $this->day_rate * 365;
    }


    public static function tableName()
    {
        return '{{%user_loan_order}}';
    }

    /**
     * @return object|\yii\db\Connection|null
     * @throws \yii\base\InvalidConfigException
     */
    public static function getDb()
    {
        return Yii::$app->get('db');
    }

    public static function getDbName(){
        if(preg_match('/dbname=(\w+)/', Yii::$app->db->dsn, $db) && !empty($db[1])){
            return $db[1];
        }
        return null;
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    public function getLoanPerson()
    {
        return $this->hasOne(LoanPerson::class, ['id' => 'user_id']);
    }

    public function getUserLoanOrderRepayment()
    {
        return $this->hasOne(UserLoanOrderRepayment::class, ['order_id' => 'id']);
    }

    public function getUserLoanOrderExtraRelation()
    {
        return $this->hasOne(UserLoanOrderExtraRelation::class, ['order_id' => 'id']);
    }

    public function getLoanKudosOrder()
    {
        return $this->hasOne(LoanKudosOrder::class, ['order_id' => 'id']);
    }

    public function getUserWorkInfo()
    {
        return $this
            ->hasOne(UserWorkInfo::class, ['id' => 'user_work_info_id'])
            ->via('userLoanOrderExtraRelation');
    }

    public function getUserBasicInfo()
    {
        return $this
            ->hasOne(UserBasicInfo::class, ['id' => 'user_basic_info_id'])
            ->via('userLoanOrderExtraRelation');
    }

    public function getUserContact()
    {
        return $this
            ->hasOne(UserContact::class, ['id' => 'user_contact_id'])
            ->via('userLoanOrderExtraRelation');
    }

    public function getUserBankAccount()
    {
        return $this
            ->hasOne(UserBankAccount::class, ['id' => 'card_id']);
    }

    public function getUserCreditechFr()
    {
        return $this
            ->hasOne(UserCreditReportFrLiveness::class, ['id' => 'user_fr_id'])
            ->via('userLoanOrderExtraRelation');
    }

    public function getUserFrFr()
    {
        return $this
            ->hasOne(UserCreditReportFrVerify::class, ['id' => 'user_fr_fr_id'])
            ->via('userLoanOrderExtraRelation');
    }

    public function getUserFrPan()
    {
        return $this
            ->hasOne(UserCreditReportFrVerify::class, ['id' => 'user_fr_pan_id'])
            ->via('userLoanOrderExtraRelation');
    }

    public function getUserCreditechOCRPan()
    {
        return $this
            ->hasOne(UserCreditReportOcrPan::class, ['id' => 'user_ocr_pan_id'])
            ->via('userLoanOrderExtraRelation');
    }

    public function getUserCreditechOCRAadhaar()
    {
        return $this
            ->hasOne(UserCreditReportOcrAad::class, ['id' => 'user_ocr_aadhaar_id'])
            ->via('userLoanOrderExtraRelation');
    }

    public function getUserVerifyPan()
    {
        return $this
            ->hasOne(UserPanCheckLog::class, ['id' => 'user_verify_pan_id'])
            ->via('userLoanOrderExtraRelation');
    }

    public function getProductSetting()
    {
        return $this->hasOne(ProductSetting::class, ['id' => 'product_id']);
    }

    public function getClientInfoLog()
    {
        return $this->hasOne(ClientInfoLog::class, ['event_id' => 'id'])->where(['event' => ClientInfoLog::EVENT_APPLY_ORDER]);
    }

    public function getUserCreditReportCibil()
    {
        return $this
            ->hasOne(UserCreditReportCibil::class, ['id' => 'user_credit_report_cibil_id'])
            ->via('userLoanOrderExtraRelation');
    }

    public function getUserCreditReportExperian()
    {
        return $this
            ->hasOne(UserCreditReportExperian::class, ['id' => 'user_credit_report_experian_id'])
            ->via('userLoanOrderExtraRelation');

    }

    public function getUserQuestionReport()
    {
        return $this
            ->hasOne(UserQuestionVerification::class, ['id' => 'user_language_report_id'])
            ->via('userLoanOrderExtraRelation');
    }

    /**
     * 判断管理员是否可以领取审核订单
     * @param $operator
     * @return bool
     */
    public function canGetReviewOrder() : bool
    {
        if($this->status == self::STATUS_CHECK){
            if($this->audit_operator == 0 && $this->audit_status == self::AUDIT_STATUS_GET_ORDER){
                return true;
            }

            if($this->audit_operator > 0 && $this->audit_begin_time < time() - 3600 && $this->audit_status == self::AUDIT_STATUS_MANUAL_CHECK){   //订单领取审核超时超时
                return true;
            }
        }
        return false;
    }

    /**
     * 判断管理员是否可以领取审核订单
     * @param $operator
     * @return bool
     */
    public function canGetReviewBankOrder() : bool
    {
        if($this->status == self::STATUS_WAIT_DEPOSIT && $this->loan_status == self::LOAN_STATUS_BIND_CARD_CHECK){
            if($this->audit_bank_status == self::AUDIT_BANK_STATUS_GET_ORDER){
                return true;
            }

            if($this->audit_bank_operator > 0 && $this->audit_bank_begin_time < time() - 3600 && $this->audit_bank_status == self::AUDIT_BANK_STATUS_MANUAL_CHECK){   //订单领取审核超时超时
                return true;
            }
        }
        return false;
    }

    /**
     * 判断管理员是否可以审核订单
     * @param $operator
     * @param $isAuto
     * @return bool
     */
    public function canReviewOrder($operator,$isAuto = 0) : bool
    {
        if($isAuto){
            if($this->status == self::STATUS_CHECK
                && $this->audit_status == self::AUDIT_STATUS_GET_ORDER
            ){
                return true;
            }
        }else{
            if($this->status == self::STATUS_CHECK
                && $this->audit_status == self::AUDIT_STATUS_MANUAL_CHECK
                && $this->audit_operator == $operator
            ){
                return true;
            }
        }
        return false;
    }

    /**
     * 判断管理员是否可以审核绑卡订单
     * @param $operator
     * @param $isAuto
     * @return bool
     */
    public function canReviewBankOrder($operator,$isAuto = 0) : bool
    {
        if($isAuto){
            if($this->status == self::STATUS_WAIT_DEPOSIT
                && $this->loan_status == self::LOAN_STATUS_BIND_CARD_CHECK
                && $this->audit_bank_status == self::AUDIT_BANK_STATUS_GET_ORDER
            ){
                return true;
            }
        }else{
            if($this->status == self::STATUS_WAIT_DEPOSIT
                && $this->loan_status == self::LOAN_STATUS_BIND_CARD_CHECK
                && $this->audit_bank_status == self::AUDIT_BANK_STATUS_MANUAL_CHECK
                && $this->audit_bank_operator == $operator
            ){
                return true;
            }
        }
        return false;
    }


    /**
     * 添加订单审核锁
     */
    public static function lockReviewOrder($order_id)
    {
        $lock_key = sprintf("%s%s:%s", RedisQueue::ADMIN_OPERATE_LOCK, "review:order", $order_id);

        if (1 == RedisQueue::inc([$lock_key, 1])) {
            RedisQueue::expire([$lock_key, 10]);
            return true;
        } else {
            RedisQueue::expire([$lock_key, 10]);
        }
        return false;
    }

    /**
     * 释放订单审核锁
     */
    public static function releaseReviewOrderLock($order_id)
    {
        $lock_key = sprintf("%s%s:%s", RedisQueue::ADMIN_OPERATE_LOCK, "review:order", $order_id);
        RedisQueue::del(["key" => $lock_key]);
    }

    /**
     * 获取用户最后一笔订单
     * @param $userId
     * @return ActiveRecord|null
     */
    public static function userLastOrder($userId)
    {
        return self::find()->where(['user_id' => intval($userId), 'is_export' => self::IS_EXPORT_NO])
            ->orderBy(['id' => SORT_DESC])->one();
    }


    /**
     * 获取提现订单
     * @param $userId
     * @return array|yii\db\ActiveRecord|null
     */
    public static function getDepositOrder($userId)
    {
        $order = UserLoanOrder::find()
            ->where([
                'user_id' => $userId,
                'status' => UserLoanOrder::STATUS_WAIT_DEPOSIT])
            ->one();
        return $order;
    }

    /**
     * 判断用户是否有进行中的订单
     * @param $userId
     * @return bool
     */
    public static function haveLoaningOrder($userId)
    {
        $order = UserLoanOrder::find()->select(['id'])
            ->where([
                'user_id' => $userId,
                'status' => UserLoanOrder::$opening_order_status
            ])->one();
        return !is_null($order);
    }


    /**
     * 关联资方表
     * @return \yii\db\ActiveQuery
     */
    public function getLoanFund()
    {
        return $this->hasOne(LoanFund::class, ['id' => 'fund_id']);
    }

    public function getMerchant(){
        return $this->hasOne(Merchant::class, ['id' => 'merchant_id']);
    }
}
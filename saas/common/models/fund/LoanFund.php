<?php

namespace common\models\fund;
use common\exceptions\UserExceptionExt;
use common\models\pay\PayAccountSetting;
use common\services\fund\ApkNewCustomerService;
use common\services\fund\ApkOldCustomerService;
use common\services\fund\ExternalFundService;
use Yii;
use yii\base\Exception;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * 借款资金表
 * This is the model class for table "{{%loan_fund}}".
 *
 * @property string $id
 * @property string $name 资金方名称
 * @property string $merchant_id 商户id
 * @property int $day_quota_default 金额限制  单位为分
 * @property integer $score 优先分值 越高越大
 * @property integer $status 状态
 * @property integer $open_loan 状态
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 * @property integer $type 类型
 * @property integer $quota_type 配额类型
 * @property integer $alert_quota 报警配额
 * @property string $alert_phones 报警人
 * @property integer $can_use_quota 可用额度 用于循环额度
 * @property int $old_customer_proportion 新老客户百分比
 * @property int $all_old_customer_proportion 全老本新占比
 * @property int $is_old_customer 是否老客
 * @property int $loan_account_id 贷款牌照账号ID
 * @property int $pay_account_id 支付账号ID
 * @property string $payout_group 放款组
 * @property int $is_export 是否外部订单
 * @property string $app_markets 对哪些app_markets生效，多个appmareket用逗号分割，如xxx_vivo,xxx_xiaomi
 * @property int $show -1隐藏 1展示
 *
 * 关联
 * @property PayAccountSetting $payAccountSetting
 * @property PayAccountSetting $loanAccountSetting
 */
class LoanFund extends ActiveRecord
{
    const SHOW_YES = 1;
    const SHOW_NO = -1;

    public static $show_map = [
        self::SHOW_YES => '显示',
        self::SHOW_NO => '隐藏',
    ];

    //放款状态
    const OPEN_LOAN_ON = 1;
    const OPEN_LOAN_OFF = 0;

    public static $open_loan_map = [
        self::OPEN_LOAN_OFF => 'close',
        self::OPEN_LOAN_ON => 'open'
    ];


    const STATUS_ENABLE = 0;//启用
    const STATUS_DISABLE = -1;//禁用


    const STATUS_LIST = [
        self::STATUS_ENABLE => 'Enable',
        self::STATUS_DISABLE => 'Disable',
    ];


    //是否老客
    const IS_OLD_COSTOMER_YES = 2;
    const IS_OLD_COSTOMER_NO = 1;

    public static $is_old_costomer_map = [
        self::IS_OLD_COSTOMER_YES => 'yes',
        self::IS_OLD_COSTOMER_NO => 'no'
    ];


    const SERVICE_TYPE_EXPORT = 'export';
    const SERVICE_TYPE_SELF_OLD = 'self_old';
    const SERVICE_TYPE_SELF_NEW = 'self_new';

    public static $service_type_map = [
        self::SERVICE_TYPE_EXPORT => ExternalFundService::class,
        self::SERVICE_TYPE_SELF_OLD => ApkOldCustomerService::class,
        self::SERVICE_TYPE_SELF_NEW => ApkNewCustomerService::class,

    ];


    const IS_EXPORT_YES = 1; //是外部订单资方
    const IS_EXPORT_NO = 0; //不是外部订单资方

    public static $is_export_map = [
        self::IS_EXPORT_YES => 'yes',
        self::IS_EXPORT_NO => 'no',
    ];


    //资方类
    private $serviceList = [];


    private $repaymentService;



    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%loan_fund}}';
    }


    /**
     * @return object|\yii\db\Connection|null
     * @throws \yii\base\InvalidConfigException
     */
    public static function getDb()
    {
        return Yii::$app->get('db');
    }

    public function behaviors() {
        return [
            TimestampBehavior::class
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'day_quota_default', 'payout_group', 'is_export'], 'required'],
            [['app_markets', 'name'], 'trim'],
            [['day_quota_default'], 'integer', 'min'=>0],
            [['can_use_quota'], 'integer', 'min'=>0],
            ['is_export', 'in', 'range' =>array_keys(self::$is_export_map)],
            [['pay_account_id', 'loan_account_id'], 'integer', 'min'=> 0],
            ['pay_account_id', 'validatePayAccountId', 'skipOnEmpty' => false, 'skipOnError' => true],
            ['loan_account_id', 'validateLoanAccountId', 'skipOnEmpty' => false, 'skipOnError' => true],
            [['name'], 'string', 'max' => 32],
            [['name'], 'unique'],
            [['status','score', 'open_loan' ],'integer'],
            [['old_customer_proportion'], 'integer', 'min'=>0, 'max' => 100],
            [['alert_quota', 'alert_phones', 'merchant_id', 'is_old_customer', 'app_markets'], 'safe'],
            [['all_old_customer_proportion', 'old_customer_proportion'], 'integer' , 'min'=>0, 'max' => 100],
            [['all_old_customer_proportion', 'old_customer_proportion'], 'validateProportion']
        ];
    }

    public function validateProportion($attribute, $params)
    {
        if(self::IS_EXPORT_YES == $this->is_export)
        {
            if(($this->all_old_customer_proportion + $this->old_customer_proportion) > 100)
            {
                $this->addError($attribute, Yii::T('common', '总百分比不能超过100'));
            }
        }else{
            $this->all_old_customer_proportion = 0;
            $this->old_customer_proportion = 0;
        }
    }


    public function validatePayAccountId($attribute, $params)
    {
        if(!in_array(
            $this->pay_account_id,
            array_keys(PayAccountSetting::getAccountList($this->merchant_id, PayAccountSetting::SERVICE_TYPE_RAZORPAY)))
        )
        {
            $this->addError($attribute, Yii::T('common', 'Payment account and merchant number are inconsistent'));
        }
    }

    public function validateLoanAccountId($attribute, $params)
    {
        if($this->loan_account_id != 0 &&
            !in_array(
            $this->loan_account_id,
            array_keys(PayAccountSetting::getAccountList($this->merchant_id, [PayAccountSetting::SERVICE_TYPE_KUDOS, PayAccountSetting::SERVICE_TYPE_AGLOW])))
        )
        {
            $this->addError($attribute, Yii::T('common', 'The small loan account number is inconsistent with the merchant number'));
        }
    }

    public function attributeHints() {
        return [
            'score'=>Yii::T('common', 'The higher the priority, the higher the priority'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => Yii::T('common', 'name'),
            'day_quota_default' => Yii::T('common', 'Daily default quota (yuan)'),
            'can_use_quota'=>Yii::T('common', 'Available credit (yuan)'),
            'created_at' => Yii::T('common', 'Creation time'),
            'updated_at' => Yii::T('common', 'update time'),
            'status' => Yii::T('common', 'status'),
            'score'=>Yii::T('common', 'priority'),
            'type'=>Yii::T('common', 'type'),
            'quota_type'=>Yii::T('common', 'Quota type'),
            'open_loan'=>Yii::T('common', 'Whether to lend'),
            'alert_quota' => Yii::T('common', 'Alarm quota'),
            'alert_phones' => Yii::T('common', 'Corporate WeChat alarm person (comma separated)'),
            'is_old_customer' => Yii::T('common', 'is old customer'),
            'old_customer_proportion' => Yii::T('common', 'Old customer proportion'),
            'all_old_customer_proportion' => Yii::T('common', 'All Old Self New customer proportion'),
            'merchant_id' => Yii::T('common', 'merchant'),
            'pay_account_id' => Yii::T('common', 'Payment account'),
            'loan_account_id' => Yii::T('common', 'Small loan account'),
            'app_markets' => 'app_market',
            'is_export' => '是否导流订单',

        ];
    }


    /**
     * 获取全新本新占比
     * @return int
     */
    public function getAllNewSelfNewPr()
    {
        return 100 - $this->all_old_customer_proportion - $this->old_customer_proportion;
    }

    /**
     * 根据日期和类型获取余下配额
     * @param $date
     * @param int $nCustomerType 类型 [1=>'新客', 2=>'老客']
     * @return int
     */
    public function getDayRemainingQuota($date, $nCustomerType) {

        switch ($nCustomerType)
        {
            case LoanFundDayQuota::TYPE_APK_NEW:
                if(!(self::IS_EXPORT_NO == $this->is_export && self::IS_OLD_COSTOMER_NO == $this->is_old_customer))
                {
                    return 0;
                }
                break;
            case LoanFundDayQuota::TYPE_APK_OLD:
                if(!(self::IS_EXPORT_NO == $this->is_export && self::IS_OLD_COSTOMER_YES == $this->is_old_customer))
                {
                    return 0;
                }
                break;
            case LoanFundDayQuota::TYPE_NEW:
            case LoanFundDayQuota::TYPE_OLD:
            case LoanFundDayQuota::TYPE_REAL_OLD:
                if(self::IS_EXPORT_YES != $this->is_export)
                {
                    return 0;
                }
                break;
        }

        $quota_model = LoanFundDayQuota::findOne([
            'fund_id'=> $this->id,
            'date'=>$date,
            'merchant_id' => $this->merchant_id,
            'type' => $nCustomerType
        ]);
        /* @var $quota_model LoanFundDayQuota */
        if(!$quota_model) {
            // 添加配额记录
            $quota_model = LoanFundDayQuota::add($this->id, $date, $this->merchant_id, $nCustomerType);
            if(!$quota_model) {
                return 0;
            }
        }
        return $quota_model->remaining_quota;
    }


    /**
     * @return ExternalFundService|ApkOldCustomerService|ApkNewCustomerService
     * @throws \yii\base\InvalidConfigException
     */
    public function getService() {
        if(self::IS_EXPORT_YES == $this->is_export)
        {
            $serviceType = self::SERVICE_TYPE_EXPORT;
        }else{
            if(self::IS_OLD_COSTOMER_NO == $this->is_old_customer)
            {
                $serviceType = self::SERVICE_TYPE_SELF_NEW;
            }else{
                $serviceType = self::SERVICE_TYPE_SELF_OLD;
            }
        }

        if(isset($this->serviceList[$serviceType]))
        {
            return $this->serviceList[$serviceType];
        }

        $this->serviceList[$serviceType] = Yii::createObject(['class' => self::$service_type_map[$serviceType], 'loanFund' => $this]);
        return $this->serviceList[$serviceType];

    }



    /**
     * 获取所有资金方
     * @return array
     */
    public static function getAllFundArray($merchantId){
        $query =  self::find()->select(['id', 'name'])->where(['merchant_id' => $merchantId]);
        $res = $query->asArray()->all();
        $tmp = [];
        foreach ($res as $val){
            $tmp[$val['id']] = $val['name'];
        }

        return $tmp;

    }



    /**
     * 获取可用配额
     * @param int $merchantId
     * @param int $isExport
     * @return array|ActiveRecord[]
     */
    public static function canUserLoan($merchantId, $isExport) {
        return self::find()->where([
            'status' => LoanFund::STATUS_ENABLE,
            'merchant_id' => $merchantId,
            'is_export' => $isExport
        ])->orderBy(['score' => SORT_DESC])->all();
    }

    /**
     * 获取当天余下额度
     * @param int $nCustomerType 类型 [1=>'全新本新', 2=>'全老本新' 3=>'本老' 4 => '']
     * @return int
     */
    public function getTodayRemainingQuota($nCustomerType = null) {
        if(is_null($nCustomerType))
        {
            $quota = 0;
            foreach ($this->getCustomerType() as $item) {
                $quota += $this->getDayRemainingQuota(date('Y-m-d'), $item);
            }
        }else{
            $quota = $this->getDayRemainingQuota(date('Y-m-d'), $nCustomerType);
        }
        return $quota;
    }




    /**
     * 获取配额类型
     * @return array
     */
    public function getCustomerType()
    {
        //县判断是否是外部订单
        if(self::IS_EXPORT_NO == $this->is_export)
        {
            if(self::IS_OLD_COSTOMER_YES == $this->is_old_customer)
            {
                return [LoanFundDayQuota::TYPE_APK_OLD];
            }else{
                return [LoanFundDayQuota::TYPE_APK_NEW];
            }
        }else{
            return [LoanFundDayQuota::TYPE_NEW, LoanFundDayQuota::TYPE_OLD, LoanFundDayQuota::TYPE_REAL_OLD];
        }
    }


    /**
     * 减少资方额度
     * @param $amount
     * @param $day
     * @param int $nCustomerType 类型 [1=>'新客', 2=>'老客']
     * @throws \Exception
     */
    public function decreaseQuota($amount, $day, $nCustomerType) {
        //变更配额
        LoanFundDayQuota::decreaseDayQuota($this->id, $day, $amount, $nCustomerType);
    }


    /**
     * 增加资方额度
     * @param $amount
     * @param $day
     * @param $decr_loan_amount
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     */
    public function increaseQuota($amount, $day, $decr_loan_amount) {
        LoanFundDayQuota::increaseDayQuota($this->id, $day, $amount, $decr_loan_amount);
    }


    /**
     * 是否支持借款期限
     * @param $term
     * @return mixed
     * @throws \Exception
     */
    public function supportLoanTerm($term) {
        return true;
    }

    /**
     * 支付账号关联
     * @return \yii\db\ActiveQuery
     */
    public function getPayAccountSetting()
    {
        return $this->hasOne(PayAccountSetting::class, ['id' => 'pay_account_id']);
    }

    /**
     * 牌照账号关联
     * @return \yii\db\ActiveQuery
     */
    public function getLoanAccountSetting()
    {
        return $this->hasOne(PayAccountSetting::class, ['id' => 'loan_account_id']);

    }


    /**
     * 计算导流资方配额
     * @return bool
     * @throws UserExceptionExt
     */
    public  function calcExportFundQuota()
    {
        if(self::IS_EXPORT_NO == $this->is_export)
        {
            throw new UserExceptionExt('该资方非导流类型');
        }

        $map = [
            ['type' => LoanFundDayQuota::TYPE_REAL_OLD, 'pr' => $this->old_customer_proportion],
            ['type' => LoanFundDayQuota::TYPE_OLD, 'pr' => $this->all_old_customer_proportion],
            ['type' => LoanFundDayQuota::TYPE_NEW, 'pr' => $this->getAllNewSelfNewPr()],
        ];

        foreach ($map as $item)
        {
            $newQuota = intval(floor($this->day_quota_default * $item['pr'] / 100));
            /** @var LoanFundDayQuota $model */
            $model = LoanFundDayQuota::find()->where(['fund_id' => $this->id, 'date' => date('Y-m-d'), 'type' => $item['type']])->one();
            $model->pr = $item['pr'];
            $model->quota = max($newQuota, $model->loan_amount);
            $model->remaining_quota = max(0, $model->quota - $model->loan_amount);
            $model->save();

        }
        return true;

    }


    /**
     * 获取资方默认配额
     * @return array
     */
    public function getDailyDefaultQuota()
    {
        if(self::IS_EXPORT_NO == $this->is_export) {
            return [
                'quota' => $this->day_quota_default
            ];
        }else{
            $selfOld = intval(floor($this->day_quota_default * $this->old_customer_proportion / 100));
            $selfNewAllOld = intval(floor($this->day_quota_default * $this->all_old_customer_proportion / 100));
            $selfNewAllNew = intval(floor($this->day_quota_default * $this->getAllNewSelfNewPr() / 100));
            return [
                'selfOldAllOld' => $selfOld,
                'selfNewAllOld' => $selfNewAllOld,
                'selfNewAllNew' => $selfNewAllNew,
            ];
        }

    }


    /**
     * 调整每日配额
     * @param $arrParams
     * @return bool
     */
    public function adjustFundDayQuota()
    {
        $sDate = date('Y-m-d');

        $dailyQuota =  $this->day_quota_default;

        //非导流订单直接修改当日配额
        if(self::IS_EXPORT_NO == $this->is_export)
        {
            //当日配额
            $mDayQuota = LoanFundDayQuota::find()->where(['fund_id' => $this->id, 'date' => $sDate])->one();
            $mDayQuota->remaining_quota = max($mDayQuota->remaining_quota + $dailyQuota -  $mDayQuota->quota, 0);
            $mDayQuota->quota = $dailyQuota;
            if(!$mDayQuota->save(false))
            {
                throw new Exception('配额变更失败');
            }

        }else{
            // 更改新老客户分配额度
            $this->calcExportFundQuota();

        }

        $arrParams['date'] = $sDate;
        // 新增资方操作日志
        LoanFundOperateLog::addOperateLog($this->id, $arrParams, 2, LoanFundOperateLog::ACTION_UPDATE);

        return true;

    }


}

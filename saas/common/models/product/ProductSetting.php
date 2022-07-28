<?php

namespace common\models\product;
use backend\models\Merchant;
use common\helpers\CommonHelper;
use common\helpers\RedisQueue;
use common\services\package\PackageService;
use common\services\product\ProductService;
use Yii;
use yii\base\UserException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * Class ProductSetting
 * @package common\models\product
 *
 * 表属性
 * @property int $id
 * @property int $merchant_id
 * @property string $package_name
 * @property int $period_id 关联产品类型ID
 * @property int $location 首页位置
 * @property string $product_name 产品名称
 * @property float $day_rate 日利率
 * @property float $cost_rate 手续费率 整期费用
 * @property float $overdue_rate 逾期日费率
 * @property string $opreate_name 最后操作人
 * @property int $is_internal 是否内部订单 1内部  -1外部
 * @property int $delay_day 延期开启的位移天数
 * @property int $delay_status 延期 0:关闭 1:开启
 * @property int $delay_ratio 延期支付比例
 * @property int $delay_deduction_day 延期减免滞纳金偏移天数
 * @property int $delay_deduction_status 延期减免滞纳金 0:关闭 1:开启
 * @property int $delay_deduction_ratio 延期减免滞纳金支付比例
 * @property string $extend_day 展期开启天数范围
 * @property int $extend_status 展期 0:关闭 1:开启
 * @property int $extend_ratio 展期支付比例
 * @property int $created_at 创建时间
 * @property int $updated_at 更新时间
 * @property int $default_credit_limit 全新本新默认授信额度 单位元
 * @property int $default_credit_limit_2 全新本老默认授信额度 单位元
 * @property int $show_days
 *
 * 关联表
 * @property ProductPeriodSetting productPeriodSetting
 */
class ProductSetting extends ActiveRecord
{

    const SHOW_DAYS_YES = 1; //展示借款天数
    const SHOW_DAYS_NO = -1; //不展示借款天数

    public static $show_days_map = [
        self::SHOW_DAYS_YES => '显示',
        self::SHOW_DAYS_NO => '隐藏',

    ];


    const STATUS_DELAY_CLOSE = 0;
    const STATUS_DELAY_OPEN = 1;

    const IS_EXTERNAL_YES = -1;
    const IS_EXTERNAL_NO = 1;

    const STATUS_USABLE = 1;
    const STATUS_DISABLE = 0;

    public static $loan_amount_list = [5000, 10000, 1600];

    public static $status = array(
        self::STATUS_USABLE => 'usable',
        self::STATUS_DISABLE => 'disable',
    );

    public static $isInternal = array(
        self::IS_EXTERNAL_YES => '外部导流',
        self::IS_EXTERNAL_NO => '内部APK',
    );

    public static function tableName()
    {
        return '{{%product_setting}}';
    }

    public static function getDb()
    {
        return Yii::$app->get('db');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['period_id', 'product_name', 'day_rate', 'cost_rate', 'overdue_rate', 'package_name', 'merchant_id', 'is_internal', 'default_credit_limit'], 'required', 'message' => 'Can not be empty'],
            [['delay_day', 'delay_status', 'delay_ratio',
              'delay_deduction_day', 'delay_deduction_status', 'delay_deduction_ratio', 'default_credit_limit', 'default_credit_limit_2',
              'extend_status', 'extend_ratio',
                 ], 'integer'],
            [['merchant_id','is_internal', 'package_name'], 'unique', 'targetAttribute' => ['merchant_id', 'is_internal', 'package_name'], 'message' => Yii::T('common', 'Products have been there')],
            ['period_id', 'validatePeriodId', 'skipOnEmpty' => false, 'skipOnError' => true],
            ['package_name', 'validatePackageName', 'skipOnEmpty' => false, 'skipOnError' => true],
            ['extend_day', 'string', 'max' => 20],
            [['operator_name','show_days'], 'safe'],
        ];
    }

    public function validatePeriodId($attribute, $params)
    {
        $check = ProductPeriodSetting::find()->where(['id' => $this->period_id, 'merchant_id' => $this->merchant_id])->one();
        if(is_null($check))
        {
            $this->addError($attribute, '产品配置与商户号不匹配');
        }
    }

    public function validatePackageName($attribute, $params)
    {
        $service = new PackageService($this->package_name);
        if($service->getMerchantId() != $this->merchant_id)
        {
            $this->addError($attribute, '包名与商户号不匹配');

        }
    }


    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductPeriodSetting()
    {
        return $this->hasOne(ProductPeriodSetting::class, ['id' => 'period_id']);
    }

    //保存数据
    public static function saveRecord($params){
        $userMember = new static();
        foreach($params as $name => $val){
            $userMember -> $name = $val;
        }
        $res = $userMember->save();
        if($userMember->hasErrors()){
            throw new UserException(current($userMember->getErrors())[0]);
        }
        return $res ? $userMember : false;
    }

    //根据条件返回配置
    public static function WhereCondition($arr,$id=null){
        if(empty($arr)){
            return false;
        }
        if($id){
            return self::find()->where($arr)->andWhere('id !='.$id)->one();
        }
        return self::find()->where($arr)->one();
    }

    /**
     * 获取产品信息
     * @return array
     */
    public function getProductInfo()
    {
        $loan_term = $this->productPeriodSetting->loan_term;
        $day_rate = $this->day_rate;
        $cost_rate = $this->cost_rate;
        $product_info = [
            'product_id' => $this->id,
            'loan_term' => $loan_term,
            'day_rate' => $day_rate,
            'cost_rate' => $cost_rate,
            'cost_rate_detail' => self::getFeeDescribe($cost_rate),
        ];
        return $product_info;
    }


    /**
     * 借款金额
     * @param $disbursalAmount
     * @return float|int
     */
    public function loanAmount($disbursalAmount)
    {
        return intval(round($disbursalAmount * (1 + $this->cost_rate/100)));
    }

    /**
     * 手续费 + GST
     * @param int $amount  借款金额
     * @return float|int
     */
    public function processingFeesGst($disbursalAmount)
    {
        return $this->processingFees($disbursalAmount) + $this->gst($disbursalAmount);
    }


    /**
     * 手续费
     * @param $amount
     * @return float|int
     */
    public function processingFees($disbursalAmount)
    {
        return intval(round(
            ($this->loanAmount($disbursalAmount) - $disbursalAmount - $this->interestsCalc($disbursalAmount))
            /
            (1 + 0.18)
        ));
    }


    /**
     * GST
     * @param int $amount 借款金额
     * @return int
     */
    public function gst($disbursalAmount)
    {
        return $this->loanAmount($disbursalAmount) - $disbursalAmount - $this->processingFees($disbursalAmount) - $this->interestsCalc($disbursalAmount);
    }

    /**
     * 全部应该金额
     * @param $amount
     * @return float|int
     */
    public function totalRepaymentAmount($disbursalAmount)
    {
        return $this->loanAmount($disbursalAmount);
    }


    /**
     * 放款金额 单位 分
     * @param  int $amount 借款金额 单位元
     * @return float|int
     */
    public function disbursementAmount($disbursalAmount)
    {
        return $disbursalAmount;
    }

    /**
     * 件均 放款金额 + 砍头 + GST
     * @param $disbursalAmount
     * @return int
     */
    public function amount($disbursalAmount)
    {
        return $this->loanAmount($disbursalAmount) - $this->interestsCalc($disbursalAmount);
    }

    /**
     * 利息计算
     * @param  integer $amount
     * @return float|int
     */
    public function interestsCalc($disbursalAmount)
    {
        return intval(round($this->loanAmount($disbursalAmount) / (1 + $this->totalRate()) * $this->totalRate()));
    }


    /**
     * 总利率
     * @return float|int
     */
    public function totalRate()
    {
        return $this->day_rate / 100 * $this->productPeriodSetting->loan_term;
    }

    /**
     * 全部费用计算 手续费+利息
     * @param integer $amount
     * @return float|int
     */
    public function totalFeeCalc($amount)
    {
        return $this->interestsCalc($amount) + $this->processingFeesGst($amount);
    }

    /**
     * 获取到期日
     * @param integer $begin_time 开始时间-时间戳
     * @return string
     */
    public function getRepayDate($begin_time = 0)
    {
        return ProductService::repayDateCalc(
            $this->productPeriodSetting->loan_method,
            $this->productPeriodSetting->loan_term,
            $this->productPeriodSetting->periods,
            $begin_time
        );
    }

    /**
     * 根据总手续利率获取各项费率及描述
     * @param $rate
     * @return array
     */
    public static function getFeeDescribe($rate)
    {
        return [
            [
                'name' => 'Credit Assessment',
                'apr' => $rate * 10000 * 0.2 / 10000
            ],
            [
                'name' => 'LoanRecord Processing',
                'apr' => $rate * 10000 * 0.2 / 10000
            ],
            [
                'name' => 'Face Recognition',
                'apr' => $rate * 10000 * 0.2 / 10000
            ],
            [
                'name' => 'Statement of Account',
                'apr' => $rate * 10000 * 0.1 / 10000
            ],
            [
                'name' => 'Amortization Schedule',
                'apr' => $rate * 10000 * 0.1 / 10000
            ],
            [
                'name' => 'Cheque Swapping',
                'apr' => $rate * 10000 * 0.1 / 10000
            ],
            [
                'name' => 'Pre-payment',
                'apr' => $rate * 10000 * 0.1 / 10000
            ]
        ];
    }


    public static function getLoanExportProductName($packageName,$exportPackageName){
        $cacheKey = sprintf('%s:%s:%s', RedisQueue::MESSAGE_TIME_TASK_PRODUCT_NAME_CACHE, $packageName, $exportPackageName);
        $productNameCache = RedisQueue::get(['key' => $cacheKey]);
        if($productNameCache){
            $showProductName = $productNameCache;
        }else{
            $showProductName = 'none';
            /** @var ProductSetting $productSetting */
            $productSetting = self::find()
                ->select(['product_name'])
                ->where(['package_name' => $packageName, 'is_internal' => self::IS_EXTERNAL_YES])
                ->limit(1)
                ->one();
            if($productSetting){
                $loanProductSetting = ProductSetting::find()
                    ->alias('p')
                    ->select(['p.product_name'])
                    ->leftJoin(Merchant::tableName().' m','p.merchant_id = m.id')
                    ->where(['p.package_name' => $exportPackageName,'p.real_product_name' => $productSetting->product_name,'m.merchant_type' => 2])
                    ->limit(1)
                    ->asArray()
                    ->one(Yii::$app->get('db_loan'));
                if($loanProductSetting){
                    $showProductName = $loanProductSetting['product_name'];
                }
            }
            RedisQueue::set(['key' => $cacheKey,'value' => $showProductName, 'expire'=> 86400]);
        }
        return $showProductName;
    }
}
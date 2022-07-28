<?php

namespace common\models\fund;

use common\models\order\UserLoanOrder;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%loan_fund_day_quota}}".
 *
 * @property integer $id
 * @property integer $fund_id 资方ID
 * @property string $date 日期 格式 Y-m-d
 * @property integer $remaining_quota 余下配额
 * @property integer $quota 配额
 * @property integer $loan_amount 放款金额
 * @property int $merchant_id 商户id
 * @property int $type 类型
 * @property string $created_at
 * @property string $updated_at
 * @property int $pr 百分比
 */
class LoanFundDayQuota extends \yii\db\ActiveRecord
{

    const TYPE_NEW = 1; // 本新全新
    const TYPE_OLD = 2; // 本新全老
    const TYPE_REAL_OLD =3; //本包老客
    const TYPE_APK_NEW = 4; //apk新客
    const TYPE_APK_OLD = 5; //apk老客

    const TYPE_LIST = [
        self::TYPE_NEW => '本新全新',
        self::TYPE_OLD => '本新全老',
        self::TYPE_REAL_OLD => '本老全老',
        self::TYPE_APK_NEW => '新客',
        self::TYPE_APK_OLD => 'apk老客',
    ];


    public static $typeRedisKey = [
        self::TYPE_NEW => 'new',
        self::TYPE_OLD => 'self_new_all_old',
        self::TYPE_REAL_OLD => 'old',
        self::TYPE_APK_NEW => 'apk_new',
        self::TYPE_APK_OLD => 'apk_old',
    ];


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%loan_fund_day_quota}}';
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
            [['fund_id', 'date', 'remaining_quota', 'quota', 'merchant_id'], 'required'],
            [['fund_id', 'remaining_quota', 'quota', 'loan_amount', 'merchant_id'], 'integer'],
            ['fund_id', 'validateFundId', 'skipOnEmpty' => false, 'skipOnError' => true],
            [['date'], 'date', 'format'=>'php:Y-m-d'],
            [['pr'], 'integer', 'min'=>'0', 'max' => 100],
            [['fund_id', 'date', 'type'], 'unique', 'targetAttribute' => ['fund_id', 'date', 'type'], 'message' => Yii::T('common', 'Fund ID Date Duplicate'), 'when' => function ($model) {
                return  $model->isNewRecord || ($model->fund_id!=$model->getOldAttribute('fund_id')) || ($model->date!=$model->getOldAttribute('date'));
            }],
        ];
    }

    public function validateFundId($attribute, $params)
    {
        $loanFund = LoanFund::find()->where(['id' => $this->fund_id, 'merchant_id' => $this->merchant_id])->one();
        if(is_null($loanFund))
        {
            $this->addError($attribute, Yii::T('common', 'Management error'));
        }
    }


    public function attributeHints() {
        return [
            'date'=>Yii::T('common', 'Format is 2017-01-03'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'fund_id' => Yii::T('common', 'Fund ID'),
            'date' => Yii::T('common', 'date'),
            'remaining_quota' => Yii::T('common', 'Remaining quota'),
            'quota' => Yii::T('common', 'quota'),
            'type' => Yii::T('common', 'type'),
            'loan_amount' => Yii::T('common', 'Loan amount'),
            'created_at' => Yii::T('common', 'Creation time'),
            'updated_at' => Yii::T('common', 'update time'),
        ];
    }
    


    /**
     * 添加配额记录
     * @param int $fund_id
     * @param string $date 日期 格式为 YYYY-MM-DD
     * @param int $merchantId
     * @param int $nCustomerType 类型 [1=>'新客', 2=>'老客']
     * @return LoanFundDayQuota|null
     */
    public static function add(int $fund_id,string $date,int $merchantId, $nCustomerType)
    {
        // 获取资方实例
        /** @var LoanFund $oFund */
        $oFund = LoanFund::findOne($fund_id);

        // 获取当前类型的每日额度
        //如果是本平台老客，直接取默认配额
        if(in_array($nCustomerType, [self::TYPE_APK_NEW, self::TYPE_APK_OLD]))
        {
            $nRemainingQuota = $oFund->day_quota_default;
        }else{

            $arrCalcFundDefaultQuota = $oFund->getDailyDefaultQuota();
            if (self::TYPE_NEW == $nCustomerType) {
                $nRemainingQuota = $arrCalcFundDefaultQuota['selfNewAllNew'];
            } elseif(self::TYPE_OLD == $nCustomerType) { //本新全老
                $nRemainingQuota = $arrCalcFundDefaultQuota['selfNewAllOld'];
            }elseif (self::TYPE_REAL_OLD == $nCustomerType) { //本老全老
                $nRemainingQuota = $arrCalcFundDefaultQuota['selfOldAllOld'];
            }
        }

        $pr = 0;
        if(self::TYPE_NEW == $nCustomerType)
        {
            $pr = $oFund->getAllNewSelfNewPr();
        }
        if(self::TYPE_OLD == $nCustomerType)
        {
            $pr = $oFund->all_old_customer_proportion;
        }
        if(self::TYPE_REAL_OLD == $nCustomerType)
        {
            $pr = $oFund->old_customer_proportion;
        }
        $oLoanFundDayQuota = new self();

        $oLoanFundDayQuota->fund_id = $fund_id;
        $oLoanFundDayQuota->date = $date;
        $oLoanFundDayQuota->quota = $nRemainingQuota;
        $oLoanFundDayQuota->remaining_quota = $nRemainingQuota;
        $oLoanFundDayQuota->type = $nCustomerType;
        $oLoanFundDayQuota->merchant_id = $merchantId;
        $oLoanFundDayQuota->pr = $pr;

        // 获取当前占比的金额
        if($oLoanFundDayQuota->save())
        {
            return $oLoanFundDayQuota;
        } else {
            return null;
        }
    }
    
    /**
     * 添加配额
     * @param integer $fund_id 资方ID
     * @param string $date 日期 格式为 YYYY-MM-DD
     * @param integer $incr_quota 要增加的配额 单位为分 
     * @param integer $decr_loan_amount 减少放款的金额 默认为要增加的配额（一般放款失败或切换资方时，回增当天的额度，所以当天的借款金额减少等值）
     */
    public static function increaseDayQuota($fund_id, $date, $incr_quota, $decr_loan_amount=null) {
        $sql = 'UPDATE '.static::tableName().' SET `remaining_quota`=(`remaining_quota` + :incr_quota),`loan_amount`=(`loan_amount` - :decr_loan_amount) WHERE `fund_id`=:fund_id AND `date`=:date';
        static::getDb()->createCommand($sql,[
            ':fund_id'=>(int)$fund_id,
            ':date'=>trim($date),
            ':incr_quota'=>$incr_quota,
            ':decr_loan_amount'=>$decr_loan_amount===null?$incr_quota:$decr_loan_amount,
        ])->execute();
    }
    

    
    /**
     * 减少每日配额
     * @param integer $fund_id 基金ID
     * @param string $date 日期 格式为 YYYY-MM-DD
     * @param integer $decr_quota 要减少的配额 单位为分
     * @param int $nCustomerType 类型 [1=>'新客', 2=>'老客']
     * @param integer $incr_loan_amount 增加放款的金额 默认为增加减少的额度（一般放款时，减少当天的额度，所以当天的借款金额增加等值）
     * @throws \Exception
     */
    public static function decreaseDayQuota($fund_id, $date, $decr_quota, $nCustomerType, $incr_loan_amount=null) {
        $sql = 'UPDATE '.static::tableName().' SET `remaining_quota`=(`remaining_quota`- :decr_quota),`loan_amount`=(`loan_amount` + :incr_loan_amount) WHERE `fund_id`=:fund_id AND `date`=:date AND `type` = :type';
        static::getDb()->createCommand($sql,[
            ':fund_id'=>(int)$fund_id,
            ':date'=>trim($date),
            ':type'=>$nCustomerType,
            ':decr_quota'=>(int)$decr_quota,
            ':incr_loan_amount'=>$incr_loan_amount===null?$decr_quota:$incr_loan_amount,
        ])->execute();

    }
    


    /**
     * 获取指定日期剩余额度
     * @param int $nFundId
     * @param null $sDate
     * @return bool|mixed
     */
    public static function getRemainingQuota($nFundId, $sDate = null)
    {
        if ((int)$nFundId <= 0) return false;

        // 没有传日期默认当天
        if (empty($sDate)) {
            $sDate = date('Y-m-d');
        }

        $nSum = self::find()->where(['fund_id'=>$nFundId, 'date'=>$sDate])->sum('remaining_quota');

        return (int)$nSum;

    }// END getRemainingQuota


    /**
     * 获取指定日期总额度
     * @param int $nFundId
     * @param null $sDate
     * @return bool|int
     */
    public static function getTotalQuota($nFundId, $sDate = null)
    {
        if ((int)$nFundId <= 0) return false;

        // 没有传日期默认当天
        if (empty($sDate)) {
            $sDate = date('Y-m-d');
        }

        $nSum = self::find()->where(['fund_id'=>$nFundId, 'date'=>$sDate])->sum('quota');

        return (int)$nSum;

    }


    public static function getOrderCustomerType(UserLoanOrder $order)
    {
        if(UserLoanOrder::IS_EXPORT_YES == $order->is_export)
        {
            //全老本老
            if(UserLoanOrder::FIRST_LOAN_NO == $order->is_first)
            {
                return self::TYPE_REAL_OLD;
            }elseif (UserLoanOrder::FIRST_LOAN_NO == $order->is_all_first && UserLoanOrder::FIRST_LOAN_IS == $order->is_first)
            { //全老本新
                return self::TYPE_OLD;
            }else{
                return self::TYPE_NEW;
            }
        }else{
            if(UserLoanOrder::FIRST_LOAN_NO == $order->is_first)
            {
                return self::TYPE_APK_OLD;
            }else{
                return self::TYPE_APK_NEW;
            }
        }
    }
}

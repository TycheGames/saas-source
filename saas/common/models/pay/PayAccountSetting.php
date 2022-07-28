<?php

namespace common\models\pay;
use backend\models\Merchant;
use common\services\AglowService;
use common\services\KudosService;
use common\services\pay\BasePayService;
use common\services\pay\CashFreePaymentService;
use common\services\pay\RazorpayService;
use common\services\risk\CreditReportCibilService;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;


/**
 * 支付账号配置
 * Class PayAccountSetting
 * @package common\models\pay
 *
 * 表属性
 * @property int $id
 * @property int $name 名字
 * @property int $service_type 服务类型 1-razorpay
 * @property string $account_info 账户信息
 * @property int $merchant_id 商户号
 * @property string $remark 备注
 * @property int $created_at 创建时间
 * @property int $updated_at 更新时间
 *
 */
class PayAccountSetting extends ActiveRecord
{

    const SERVICE_TYPE_RAZORPAY = 1;
    const SERVICE_TYPE_KUDOS = 2;
    const SERVICE_TYPE_KUDOS_CREDIT = 3;//kodos 征信报告
    const SERVICE_TYPE_AGLOW = 4; //aglow小贷牌照
    const SERVICE_TYPE_CASHFREE = 5; //cashfree

    public static $service_type_map = [
        self::SERVICE_TYPE_RAZORPAY => 'razorpay',
        self::SERVICE_TYPE_KUDOS => 'kudos',
        self::SERVICE_TYPE_KUDOS_CREDIT => 'cibil_kudos',
        self::SERVICE_TYPE_AGLOW => 'aglow',
        self::SERVICE_TYPE_CASHFREE => 'cashfree',
    ];

    public static $service_map = [
        self::SERVICE_TYPE_RAZORPAY => RazorpayService::class,
        self::SERVICE_TYPE_KUDOS => KudosService::class,
        self::SERVICE_TYPE_KUDOS_CREDIT => CreditReportCibilService::class,
        self::SERVICE_TYPE_AGLOW => AglowService::class,
        self::SERVICE_TYPE_CASHFREE => CashFreePaymentService::class,
    ];


    public static function tableName()
    {
        return '{{%pay_account_setting}}';
    }

    public static function getDb()
    {
        return Yii::$app->get('db');
    }
    

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }


    /**
     * @param $name
     * @param $serviceType
     * @param RazorpayAccountForm $form
     * @param $remark
     * @return PayAccountSetting|null
     */
    public static function add($name, $serviceType,  $form, $remark)
    {
        $model = new self();
        $model->name = $name;
        $model->service_type = $serviceType;
        $model->account_info = json_encode($form->toArray(), JSON_UNESCAPED_UNICODE);
        $model->remark = $remark;
        if($model->save())
        {
            return $model;
        }else{
            return null;
        }
    }


    /**
     * @return array
     */
    public function getAccountInfo()
    {
        return json_decode($this->account_info, true);
    }


    /**
     * @return BasePayService
     */
    public function getPayServiceClass()
    {
        return self::$service_map[$this->service_type];
    }


    /**
     * @param $merchantId
     * @param $serviceType
     * @return array
     */
    public function getAccountList($merchantId, $serviceType)
    {
        $list = [];
        $query = self::find()->select(['id', 'name', 'merchant_id'])->where(['service_type' => $serviceType])->orderBy(['merchant_id' => SORT_DESC]);
        if($merchantId !== 0)
        {
            $query->andWhere(['merchant_id' => $merchantId]);
        }
        $accounts = $query->all();

        $merchanList = Merchant::getMerchantId(false);
        foreach ($accounts as $account) {
            $list[$account->id] = $merchanList[$account->merchant_id] . '-'. $account->name;
        }

        return $list;
    }


    public function attributeLabels()
    {
        return [
            'id' => 'id',
            'name' => '名字',
            'service_type' => '服务类型',
            'account_info' => '账户信息',
            'merchant_id' => '商户号',
            'remark' => '备注',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
        ];
    }

    public function rules()
    {
        return [
            [['name', 'service_type', 'account_info', 'merchant_id'], 'required'],
            [['id','created_at', 'updated_at', 'remark'], 'safe'],

        ];
    }

    /**
     * 获取还款方式是否可用
     * @param string $serviceType razorpay|cashfree|paytm|mpurse
     * @return bool
     */
    public function getPaymentServiceEnableStatus(string $serviceType) : bool
    {
        $list = [
            'razorpay' => false,
            'cashfree' => false,
            'paytm' => false,
            'mpurse' => false,
            'sifang' => false,
            'mojo' => false,
            'jpay' => false,
            'qiming' => false,
            'quanqiupay' => false,
            'rpay' => false,
        ];
        $form = new RazorpayAccountForm();
        $form->load(json_decode($this->account_info, true), '');
        if(!empty($form->paymentKeyId) && !empty($form->paymentSecret))
        {
            $list['razorpay'] = true;
        }
        if(!empty($form->cashFreePaymentKey) && !empty($form->cashFreePaymentSecret))
        {
            $list['cashfree'] = true;
        }
        if(!empty($form->mpursePartnerId) && !empty($form->mpurseKey))
        {
            $list['mpurse'] = true;
        }
        if(!empty($form->sifangUserId) && !empty($form->sifangApiKey))
        {
            $list['sifang'] = true;
        }
        if(!empty($form->jpayAppKey) && !empty($form->jpayAppSecret))
        {
            $list['jpay'] = true;
        }
        if(!empty($form->mojoApiKey) && !empty($form->mojoAuthToken) && !empty($form->mojoSalt))
        {
            $list['mojo'] = true;
        }
        if(!empty($form->qimingKeyId) && !empty($form->qimingKeySecret) && !empty($form->qimingTenantId))
        {
            $list['qiming'] = true;
        }
        if(!empty($form->rpayKeyId) && !empty($form->rpayKeySecret))
        {
            $list['rpay'] = true;
        }
        if(!empty($form->quanqiupayMchId) && !empty($form->quanqiupayToken))
        {
            $list['quanqiupay'] = true;
        }
        return $list[$serviceType];

    }
}
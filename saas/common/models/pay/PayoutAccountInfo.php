<?php

namespace common\models\pay;
use backend\models\Merchant;
use common\services\pay\CashFreePayoutService;
use common\services\pay\JolosoftPayoutService;
use common\services\pay\MpursePayoutService;
use common\services\pay\PaytmPayoutService;
use common\services\pay\QimingPayoutService;
use common\services\pay\RazorpayPayoutService;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;


/**
 * Class PayoutAccountInfo
 * @package common\model\pay
 * @property int $id
 * @property string $name
 * @property string $remark
 * @property int $service_type
 * @property string $account_info
 * @property int $merchant_id
 * @property int $created_at
 * @property int $updated_at
 *
 */
class PayoutAccountInfo extends ActiveRecord
{


    const SERVICE_TYPE_RAZORPAY = 1;
    const SERVICE_TYPE_MPURSE = 2;
    const SERVICE_TYPE_CASHFREE = 3;
    const SERVICE_TYPE_PAYTM = 4;
    const SERVICE_TYPE_JOLOSOFT = 5;
    const SERVICE_TYPE_QIMING = 6;

    public static $service_map = [
        self::SERVICE_TYPE_RAZORPAY => RazorpayPayoutService::class,
        self::SERVICE_TYPE_MPURSE => MpursePayoutService::class,
        self::SERVICE_TYPE_CASHFREE => CashFreePayoutService::class,
        self::SERVICE_TYPE_PAYTM => PaytmPayoutService::class,
        self::SERVICE_TYPE_JOLOSOFT => JolosoftPayoutService::class,
        self::SERVICE_TYPE_QIMING => QimingPayoutService::class,
    ];

    public static $service_type_map = [
        self::SERVICE_TYPE_RAZORPAY => 'razorpay',
        self::SERVICE_TYPE_MPURSE => 'mpurse',
        self::SERVICE_TYPE_CASHFREE => 'cashfree',
        self::SERVICE_TYPE_PAYTM => 'paytm',
        self::SERVICE_TYPE_JOLOSOFT => 'jolosoft',
        self::SERVICE_TYPE_QIMING => 'qiming',
    ];

    public static $listMap;


    public static function getListMap()
    {
        if(is_null(self::$listMap))
        {
            $data = [];
            $list = self::find()->select(['id', 'name', 'service_type', 'merchant_id'])->all();
            $merchantMap = Merchant::getMerchantId(false);
            /** @var PayoutAccountInfo $value */
            foreach ($list as $value)
            {
                $data[$value->id] = "[{$merchantMap[$value->merchant_id]}]".self::$service_type_map[$value->service_type]."-{$value->name}";
            }
            self::$listMap = $data;
        }

        return self::$listMap;

    }

    /**
     * @return array
     */
    public function getAccountInfo()
    {
        return json_decode($this->account_info, true);
    }


    /**
     * @return RazorpayPayoutService|MpursePayoutService|CashFreePayoutService|PaytmPayoutService|JolosoftPayoutService|QimingPayoutService
     */
    public function getService()
    {
        return self::$service_map[$this->service_type];
    }

    /**
     * @return RazorpayPayoutAccountForm|MpursePayoutAccountForm|CashfreePayoutAccountForm|PaytmPayoutAccountForm
     */
    public function getForm()
    {
        return $this->getService()::formPayoutModel();
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{pay_payout_account_info}}';
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


    public function rules()
    {
        return [
            [['name', 'service_type', 'account_info', 'merchant_id'], 'required'],
            [['id','created_at', 'updated_at', 'remark'], 'safe'],

        ];
    }

}

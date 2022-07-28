<?php

namespace common\models\product;
use backend\models\Merchant;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * Class ProductPeriodSetting
 * @package common\models\product
 *
 * 表属性
 * @property int $id
 * @property int $merchant_id
 * @property int $loan_method 期数单位：0-按天 1-按月 2-按年
 * @property int $loan_term 每期的时间周期，根据loan_method确定单位
 * @property int $periods 多少期
 * @property int $status 状态： 0-禁用 1-启用
 * @property int $is_internal 是否内部产品 1是 -1不是
 * @property string $operator_name 最后操作人
 * @property int $created_at 创建时间
 * @property int $updated_at 更新时间
 *
 */
class ProductPeriodSetting extends ActiveRecord
{
    const IS_EXTERNAL_YES = -1;
    const IS_EXTERNAL_NO = 1;

    const LOAN_METHOD_DAY = 0;  //按天
    const LOAN_METHOD_MONTH = 1;    //按月
    const LOAN_METHOD_YEAR = 2; //按年

    public static $loan_method_map = [
        self::LOAN_METHOD_DAY => 'day',
//        self::LOAN_METHOD_MONTH => 'month',
//        self::LOAN_METHOD_YEAR => 'year',
    ];

    const STATUS_DEFAULT = 0;
    const STATUS_ON = 1;


    public static $statusMap = [
        self::STATUS_DEFAULT => 'close',
        self::STATUS_ON => 'enable',
    ];

    public static $isInternal = array(
        self::IS_EXTERNAL_YES => '外部',
        self::IS_EXTERNAL_NO => '内部',
    );

    public static function tableName()
    {
        return '{{%product_period_setting}}';
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
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['loan_term', 'loan_method', 'merchant_id', 'is_internal', 'package_name'], 'required', 'message' => 'Can not be empty'],
//            [['merchant_id','is_internal', 'package_name'], 'unique', 'targetAttribute' => ['merchant_id', 'is_internal', 'package_name'], 'message' => Yii::T('common', 'Product type already exists')],
            [['status', 'operator_name', 'periods'], 'safe']
        ];
    }

    /**
     * 获取产品ID
     * @param int $merchantId
     * @param bool $isNotMerchantAdmin
     * @return array
     */
    public static function findPeriodSetting($merchantId, $isNotMerchantAdmin)
    {
        $data = [];
        $query = (new self())::find()->where(['merchant_id' => $merchantId]);
        if (!$isNotMerchantAdmin) {
            $query->andWhere(['is_internal' => self::IS_EXTERNAL_NO]);
        }
        $ret = $query->all();

        $merchantList = Merchant::getMerchantId(false);
        if ($ret) {
            foreach($ret as $v) {
                /**
                 * @var ProductPeriodSetting $v
                 */
                if ($isNotMerchantAdmin) {
                    $data[$v['id']] = sprintf('%s-%s-%speriods-%s%s',
                        static::$isInternal[$v->is_internal],
                        $merchantList[$v->merchant_id],
                        $v['periods'],
                        $v['loan_term'],
                        static::$loan_method_map[$v['loan_method']]);
                } else {
                    $data[$v['id']] = sprintf('%s-%speriods-%s%s',
                        $merchantList[$v->merchant_id],
                        $v['periods'],
                        $v['loan_term'],
                        static::$loan_method_map[$v['loan_method']]);
                }

            }
        }
        return $data;
    }

    /**
     *
     * 查询是否有重复设置
     *
     * @param $periods
     * @param $loanMethod
     * @param $loanTerm
     * @return boolean
     */
    public static function findSettingByPeriod($periods, $loanMethod, $loanTerm, $merchant_id, $id = null)
    {

        $query = (new self())::find()
            ->where([
                'loan_method' => $loanMethod,
                'loan_term' => $loanTerm,
                'periods' => $periods,
                'merchant_id' => $merchant_id
            ]);

        if ($id) {
            $query->andWhere('id !='.$id);
        }
        $row = $query->one();

        return empty($row) ? false : true;
    }
}
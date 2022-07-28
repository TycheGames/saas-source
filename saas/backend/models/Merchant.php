<?php
namespace backend\models;

use common\helpers\Util;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * Merchant model
 *
 * @property integer $id
 * @property string $name
 * @property int $status
 * @property int $is_hidden_address_book
 * @property int $is_hidden_contacts
 * @property string $operator
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $company_name
 * @property string $company_addr
 * @property string $gst_number
 * @property string $telephone 客服电话
 * @property int $nbfc

 */
class Merchant extends ActiveRecord {

    const STATUS_ON = 1;   //启用
    const STATUS_OFF = 0; //已删除

    const NBFC_AGLOW = 1;
    const NBFC_PAWAN = 2;
    const NBFC_KUDOS = 3;
    const NBFC_ACEMONEY = 4;
    const NBFC_CARE = 5; //lucky wallet用
    const NBFC_AGLOW_FAKE = 6; //cashalo伪造
    const NBFC_BCL = 7;
    const NBFC_ZAVRON = 9;


    public static $nbfc_map = [
        self::NBFC_AGLOW => 'aglow',
        self::NBFC_PAWAN => 'pawan',
        self::NBFC_KUDOS => 'kudos',
        self::NBFC_ACEMONEY => 'acemoney',
        self::NBFC_CARE => 'care',
        self::NBFC_AGLOW_FAKE => 'aglow fake',
        self::NBFC_BCL => 'bcl',
        self::NBFC_ZAVRON => 'zavron',
    ];


    public static $merchantList;

    public static $status_arr = [
        self::STATUS_OFF => 'Disable',
        self::STATUS_ON => 'Enable'
    ];

    const NOT_HIDDEN = 0; //不隐藏
    const IS_HIDDEN = 1;   //隐藏

    public static $is_hidden_arr = [
        self::NOT_HIDDEN => '不隐藏',
        self::IS_HIDDEN => '隐藏',
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%merchant}}';
    }

    /**
     * @return object|\yii\db\Connection|null
     * @throws \yii\base\InvalidConfigException
     */
    public static function getDb() {
        return Yii::$app->get('db');
    }

    /**
     * @inheritdoc
     */
    public function behaviors() {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['name', 'status', 'telephone', 'company_name', 'company_addr', 'gst_number', 'nbfc', 'is_hidden_address_book', 'is_hidden_contacts'], 'required'],
            [['name', 'telephone', 'company_name', 'company_addr', 'gst_number'], 'trim']
        ];
    }

    //返回商户对应键值
    public static function getMerchantId($type=true){
        if(is_null(self::$merchantList))
        {
            $merchant = self::find()->all();
            if($type){
                $ret = [0 => '无'];
            }
            foreach ($merchant as $v){
                $ret[$v['id']] = $v['name'];
            }
            self::$merchantList = $ret;
        }

        return self::$merchantList;
    }

    /**
     * 获取所有可用商户
     * @return array
     */
    public static function getAllMerchantId()
    {
        $models = self::find()->select(['id'])->where(['status' => self::STATUS_ON])->asArray()->all();
        if(empty($models))
        {
            return [];
        }
        return ArrayHelper::getColumn($models, 'id');
    }

    //返回商户对应键值
    public static function getMerchantByIds($merchantIds,$type=true, $message = '无'){
        $merchant = self::find()->where(['id' => $merchantIds])->all();
        if($type){
            $ret = [0 => $message];
        }
        foreach ($merchant as $v){
            $ret[$v['id']] = $v['name'];
        }
        return $ret;
    }
}

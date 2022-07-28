<?php

namespace common\models\package;
use common\models\pay\PayAccountSetting;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * Class PackageSetting
 * @package common\models\package
 *
 * 表属性
 * @property int $id
 * @property string $package_name 包名
 * @property int $source_id 用户来源
 * @property int $credit_account_id
 * @property string $name 名字
 * @property int $merchant_id 商户id
 * @property string $firebase_token 谷歌推送token
 * @property int $is_use_truecaller
 * @property string $truecaller_key
 * @property string $truecaller_fingerprint
 * @property int $is_google_review 谷歌商店审核 0:关闭 1:开启
 * @property int $created_at 创建时间
 * @property int $updated_at 更新时间
 *
 * 关联
 * @property PayAccountSetting $creditAccountSetting
 */
class PackageSetting extends ActiveRecord
{
    const GOOGLE_REVIEW_OPEN = 1;
    const GOOGLE_REVIEW_CLOSE = 0;

    /**
     * @return string
     */
    public static function tableName()
    {
        return '{{%package_setting}}';
    }


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['package_name', 'source_id', 'merchant_id'], 'required'],
            [['package_name', 'source_id'], 'unique'],
            [['is_google_review', 'created_at', 'updated_at'], 'integer'],
            [['credit_account_id', 'name', 'firebase_token', 'is_use_truecaller', 'truecaller_key', 'truecaller_fingerprint'], 'safe']
        ];

    }// END rules


    public function attributeLabels()
    {
        return [
            'id'                => 'ID',
            'package_name'      => 'Package name',
            'source_id'         => 'Source ID',
            'credit_account_id' => 'Credit account ID',
            'name'              => 'Name',
            'merchant_id'       => 'Merchant ID',
            'firebase_token'    => 'Firebase token',
            'is_use_truecaller' => 'Is Use Truecaller',
            'truecaller_key'    => 'Truecaller Key',
            'is_google_review'  => 'Is Google Review',
            'created_at'        => 'Created at',
            'updated_at'        => 'Updated at',
        ];

    }// END attributeLabels


    /**
     * @return object|\yii\db\Connection|null
     * @throws \yii\base\InvalidConfigException
     */
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

    public static function findPackageSetting($merchantId)
    {
        $data = [];
        $ret = (new self())::find()->where(['merchant_id' => $merchantId])->all();

        if ($ret) {
            foreach($ret as $v) {
                $data[$v['package_name']] = $v['name'];
            }
        }
        return $data;
    }

    /**
     * kudos征信报告账号关联
     * @return \yii\db\ActiveQuery
     */
    public function getCreditAccountSetting()
    {
        return $this->hasOne(PayAccountSetting::class, ['id' => 'credit_account_id']);

    }

    /**
     * 获取packagename数字字典
     * @param null $merchantId
     * @return array
     */
    public static function getPackageMap($merchantId = null)
    {
        $list = [];
        $query = self::find()->select(['id', 'package_name']);
        if(!is_null($merchantId))
        {
            $query->where(['merchant_id' => $merchantId]);
        }
        $models = $query->all();
        foreach ($models as $model) {
            $list[$model->id] = $model->package_name;
        }
        return $list;
    }

    /**
     * 获取source_id package_name数字字典
     * @param null $merchantId
     * @return array
     */
    public static function getSourceIdMap($merchantId = null)
    {
        $list = [];
        $query = self::find()->select(['package_name', 'source_id']);
        if(!is_null($merchantId))
        {
            $query->where(['merchant_id' => $merchantId]);
        }
        $models = $query->all();
        foreach ($models as $model) {
            $list[$model->package_name] = $model->source_id;
        }
        return $list;
    }

    /**
     * 获取All packagename数字字典
     * @param null $merchantId
     * @return array
     */
    public static function getAllLoanPackageNameMap($merchantId = null)
    {
        $list = [];
        $query = self::find()->select(['id', 'package_name']);
        if(!is_null($merchantId))
        {
            $query->where(['merchant_id' => $merchantId]);
        }
        $models = $query->all();
        foreach ($models as $model) {
            $list[$model->package_name] = $model->package_name;
        }
        $models = self::find()->select(['id', 'package_name'])->all(Yii::$app->get('db_loan'));
        foreach ($models as $model) {
            $list[$model->package_name] = $model->package_name;
        }
        return $list;
    }

    /**
     * 获取loan平台 packagename数字字典
     * @return array
     */
    public static function getLoanPackageNameMap()
    {
        $list = [];
        $models = self::find()->select(['id', 'package_name'])->all(Yii::$app->get('db_loan'));
        foreach ($models as $model) {
            $list[$model->package_name] = $model->package_name;
        }
        return $list;
    }

}
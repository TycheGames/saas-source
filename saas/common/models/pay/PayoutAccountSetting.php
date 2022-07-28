<?php

namespace common\models\pay;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;


/**
 * Class PayoutAccountSetting
 * @package common\model\pay
 * @property int $id
 * @property string $group
 * @property int $status
 * @property string $name
 * @property string $remark
 * @property int $weight
 * @property int $account_id
 * @property int $merchant_id
 * @property int $created_at
 * @property int $updated_at
 *
 * @property PayoutAccountInfo $payoutAccountInfo
 */
class PayoutAccountSetting extends ActiveRecord
{

    const STATUS_ENABLE = 1;
    const STATUS_DISABLE = -1;

    public static $status_map = [
        self::STATUS_ENABLE => '启用',
        self::STATUS_DISABLE => '禁用',
    ];


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{pay_payout_account_setting}}';
    }

    public function validateAccountId($attribute, $params)
    {
        $info = PayoutAccountInfo::findOne($this->account_id);
        if(is_null($info)){
            return $this->addError($attribute, '账户不存在');
        }
        if($this->merchant_id != $info->merchant_id)
        {
            return $this->addError($attribute, '账号信息商户号不一致');
        }
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
            [['name', 'account_id', 'group', 'status', 'weight', 'merchant_id'], 'required'],
            ['account_id', 'integer'],
            ['account_id', 'validateAccountId'],
            ['status', 'in', 'range' => array_keys(self::$status_map)],
            [['weight'], 'integer', 'min'=>0, 'max' => 100],
            [['id','created_at', 'updated_at', 'remark'], 'safe'],

        ];
    }


    public function getPayoutAccountInfo()
    {
        return $this->hasOne(PayoutAccountInfo::class, ['id' => 'account_id']);
    }

}

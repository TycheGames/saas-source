<?php

namespace common\models\user;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * Class UserBankAccount
 * @package common\models\user
 *
 * @property int id
 * @property int source_type 数据源 1:元丁 2:AadhaarApi
 * @property string report_account_name 验证报告开户姓名
 * @property string $service_account_name 服务商使用的账户
 * @property int user_id
 * @property int merchant_id
 * @property int main_card 是否主卡 0-非主卡 1-主卡
 * @property string bank_name 银行名
 * @property string client_info 客户端信息
 * @property string name 姓名
 * @property string account 账号
 * @property string ifsc ifsc code
 * @property string data 校验报告
 * @property int retry_limit 重试次数
 * @property int retry_time 重试时间戳
 * @property int status 状态 0-未认证 1-认证通过 -1-认证失败
 * @property int source_id 用户来源
 */
class UserBankAccount extends ActiveRecord
{

    const STATUS_UNVERIFIED = 0;
    const STATUS_SUCCESS = 1;
    const STATUS_FAILED = -1;

    const MAIN_IS = 1; //是主卡
    const MAIN_NO = 0; //非主卡

    const SOURCE_YUAN_DING = 1;
    const SOURCE_AADHAAR_API = 2;
    const SOURCE_EXPORT = 3; //api导流
    const SOURCE_DATABASE = 9;

    public static function tableName()
    {
        return '{{%user_bank_account}}';
    }

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


    /**
     * 查询用户是否有可使用的银行卡
     * @param $userId
     * @return bool
     */
    public static function haveCanUseCard($userId)
    {
        $check = self::find()->select(['id'])->where([
            'user_id' => $userId,
            'status' => self::STATUS_SUCCESS
        ])->one();
        return !is_null($check);
    }


}
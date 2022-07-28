<?php

namespace common\models\user;

use common\models\order\UserLoanOrder;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * Class UserBasicInfo
 * @package common\models\user
 *
 * 表属性
 * @property int $id
 * @property int $user_id
 * @property int $merchant_id
 * @property string $full_name
 * @property string $birthday
 * @property int $religion 宗教
 * @property int $student 是否学生
 * @property int $marital_status 婚姻状态
 * @property string $email_address 电子邮箱
 * @property string $zip_code 邮编
 * @property string $loan_purpose 借款用途
 * @property string $bank_statement 银行流水照片
 * @property string $aadhaar_pin_code aad卡上的邮编
 * @property string $aadhaar_address1 aad卡上的居住区域联邦
 * @property string $aadhaar_address2 aad卡上的居住区域城市
 * @property int $aadhaar_address_code1 aad卡上的居住地区编号联邦
 * @property int $aadhaar_address_code2 aad卡上的居住地区编号城市
 * @property string $aadhaar_detail_address aad卡上的居住详细地址
 * @property string $client_info
 * @property int $created_at
 * @property int $updated_at
 *
 * 关联表
 * @property LoanPerson loanPerson
 * @property UserLoanOrder userLoanOrder
 */
class UserBasicInfo extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%user_basic_info}}';
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

    public function getLoanPerson()
    {
        return $this->hasOne(LoanPerson::tableName(), ['id' => 'user_id']);
    }

    public function getUserLoanOrder()
    {
        return $this->hasOne(UserLoanOrder::tableName(), ['id' => 'order_id']);
    }

    public static function findByUserID($userID, $db = null)
    {
        $condition = [
            'user_id' => $userID,
        ];

        return self::find()->where($condition)->orderBy('id desc')->one($db);
    }

}
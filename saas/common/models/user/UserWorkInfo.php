<?php

namespace common\models\user;
use common\models\order\UserLoanOrder;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * Class UserWorkInfo
 * @package common\models\user
 *
 * @property int $id
 * @property int $user_id
 * @property int $merchant_id
 * @property string $educated_school 校名
 * @property int $educated 教育程度
 * @property string $residential_pincode 居住pincode
 * @property string $residential_address1 居住区域联邦
 * @property string $residential_address2 居住区域城市
 * @property int $residential_address_code1 居住区域编码联邦
 * @property int $residential_address_code2 居住区域编码城市
 * @property string $residential_detail_address 居住详细地址
 * @property int $industry 行业
 * @property string $company_name 公司名
 * @property string $company_phone 公司电话
 * @property string $company_address1 公司区域联邦
 * @property string $company_address2 公司区域城市
 * @property int $company_address_code1 公司区域编码联邦
 * @property int $company_address_code2 公司区域编码城市
 * @property string $company_detail_address 公司详细地址
 * @property string $work_position 职位
 * @property int $company_seniority 当前公司工龄
 * @property int $working_seniority 工龄
 * @property int $monthly_salary 月薪
 * @property string $certificate_of_company_docs 公司证明照片
 * @property string $client_info
 * @property int $created_at
 * @property int $updated_at
 *
 * 关联表
 * @property LoanPerson loanPerson
 * @property UserLoanOrder userLoanOrder
 */
class UserWorkInfo extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%user_work_info}}';
    }

    public static function getDb()
    {
        return Yii::$app->get('db');
    }

    public static function getDbName(){
        if(preg_match('/dbname=(\w+)/', Yii::$app->db->dsn, $db) && !empty($db[1])){
            return $db[1];
        }
        return null;
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    public static function findByUserID($userID, $db = null)
    {
        $condition = [
            'user_id' => $userID,
        ];

        return self::find()->where($condition)->orderBy('id desc')->one($db);
    }

    public function getLoanPerson()
    {
        return $this->hasOne(LoanPerson::tableName(), ['id' => 'user_id']);
    }

    public function getUserLoanOrder()
    {
        return $this->hasOne(UserLoanOrder::tableName(), ['id' => 'order_id']);
    }
}
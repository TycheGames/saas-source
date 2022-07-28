<?php

namespace common\models\order;

use common\models\user\UserBankAccount;
use common\models\user\UserBasicInfo;
use common\models\user\UserContact;
use common\models\user\UserWorkInfo;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%user_loan_order_extra_relation_new}}".
 *
 * @property int $id
 * @property int $order_id
 * @property int $user_ocr_pan_id
 * @property int $user_ocr_aadhaar_id
 * @property int $user_verify_pan_id
 * @property int $user_fr_id
 * @property int $user_fr_pan_id
 * @property int $user_fr_fr_id
 * @property int $user_basic_info_id
 * @property int $user_work_info_id
 * @property int $user_contact_id
 * @property int $user_credit_report_cibil_id cibil征信报告
 * @property int $user_credit_report_experian_id experian征信报告
 * @property int $user_language_report_id
 * @property int $created_at
 * @property int $updated_at
 *
 * 关联表
 * @property UserLoanOrder $userLoanOrder
 * @property UserWorkInfo $userWorkInfo
 * @property UserBasicInfo $userBasicInfo
 * @property UserContact $userContact
 */
class UserLoanOrderExtraRelation extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user_loan_order_extra_relation_new}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['order_id', 'user_ocr_pan_id', 'user_ocr_aadhaar_id', 'user_verify_pan_id', 'user_fr_id',
              'user_fr_pan_id', 'user_fr_fr_id', 'user_basic_info_id', 'user_work_info_id', 'user_contact_id',
              'user_credit_report_cibil_id', 'user_credit_report_experian_id', 'user_language_report_id',
              'created_at', 'updated_at'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'                             => 'ID',
            'order_id'                       => 'Order ID',
            'user_ocr_pan_id'                => 'User Ocr Pan ID',
            'user_ocr_aadhaar_id'            => 'User Ocr Aadhaar ID',
            'user_verify_pan_id'             => 'User Verify Pan ID',
            'user_fr_id'                     => 'User Fr ID',
            'user_fr_pan_id'                 => 'User Fr Pan ID',
            'user_fr_fr_id'                  => 'User Fr Fr ID',
            'user_basic_info_id'             => 'User Basic Info ID',
            'user_work_info_id'              => 'User Work Info ID',
            'user_contact_id'                => 'User Contact ID',
            'user_credit_report_cibil_id'    => 'User Credit Report Cibil ID',
            'user_credit_report_experian_id' => 'User Credit Report Experian ID',
            'user_language_report_id'        => 'User Language Report ID',
            'created_at'                     => 'Created At',
            'updated_at'                     => 'Updated At',
        ];
    }

    public function getUserLoanOrder()
    {
        return $this->hasOne(UserLoanOrder::class, ['id' => 'order_id']);
    }

    public function getUserWorkInfo()
    {
        return $this->hasOne(UserWorkInfo::class, ['id' => 'user_work_info_id']);
    }

    public function getUserBasicInfo()
    {
        return $this->hasOne(UserBasicInfo::class, ['id' => 'user_basic_info_id']);
    }

    public function getUserContact()
    {
        return $this->hasOne(UserContact::class, ['id' => 'user_contact_id']);
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    public static function getDbName(){
        if(preg_match('/dbname=(\w+)/', Yii::$app->db->dsn, $db) && !empty($db[1])){
            return $db[1];
        }
        return null;
    }
}

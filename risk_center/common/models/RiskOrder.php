<?php

namespace common\models;
use common\models\user\UserCreditReportBangaloreExperian;
use common\models\user\UserCreditReportCibil;
use common\models\user\UserCreditReportExperian;
use common\models\user\UserCreditReportMobiExperian;
use common\models\user\UserCreditReportShanyunExperian;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * Class RiskOrder
 * @package common\models
 *
 * @property int $id
 * @property string $app_name
 * @property int $order_id
 * @property int $user_id
 * @property int $status
 * @property int $type
 * @property int $is_push
 * @property int $user_cibil_id
 * @property int $user_experian_id
 * @property int $user_bangalore_experian_id
 * @property int $user_shanyun_experian_id
 * @property int $user_mobi_experian_id
 * @property int $created_at
 * @property int $updated_at
 *
 * 关联表
 * @property InfoOrder $infoOrder
 * @property InfoUser $infoUser
 * @property InfoDevice $infoDevice
 * @property InfoPictureMetadata $infoPictureMetadata
 * @property UserCreditReportCibil $userCreditReportCibil
 * @property UserCreditReportExperian $userCreditReportExperian
 * @property UserCreditReportBangaloreExperian $userCreditReportBangaloreExperian
 * @property UserCreditReportShanyunExperian $userCreditReportShanyunExperian
 * @property UserCreditReportMobiExperian $userCreditReportMobiExperian
 */
class RiskOrder extends ActiveRecord
{
    const STATUS_WAIT_CHECK    = 0; //待审核中
    const STATUS_CHECK_MANUAL  = 2; //转人工
    const STATUS_CHECK_SUCCESS = 1; //风控通过
    const STATUS_CHECK_REJECT  = -1; //风控驳回
    const STATUS_USER_CREDIT   = 3; //用户额计算


    const IS_PUSH_YES = 1; //已推送
    const IS_PUSH_NO = 0; //未推送

    const TYPE_AUTO_CHECK = 1; //风控类型
    const TYPE_USER_CREDIT = 2; //还款额度类型

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%risk_order}}';
    }

    public function changeStatus($status){
        $this->status = $status;
        if($this->save()){
            return true;
        }
        return false;
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

    public function getInfoOrder()
    {
        return $this->hasOne(InfoOrder::class, ['user_id' => 'user_id', 'order_id' => 'order_id', 'app_name' => 'app_name']);
    }

    public function getInfoUser()
    {
        return $this->hasOne(InfoUser::class, ['user_id' => 'user_id', 'order_id' => 'order_id', 'app_name' => 'app_name']);
    }

    public function getInfoDevice()
    {
        return $this->hasOne(InfoDevice::class, ['user_id' => 'user_id', 'order_id' => 'order_id', 'app_name' => 'app_name']);
    }

    public function getInfoPictureMetadata()
    {
        return $this->hasOne(InfoPictureMetadata::class, ['user_id' => 'user_id', 'order_id' => 'order_id', 'app_name' => 'app_name']);
    }

    public function getUserCreditReportCibil(){
        return $this->hasOne(UserCreditReportCibil::class, ['id' => 'user_cibil_id']);
    }

    public function getUserCreditReportExperian(){
        return $this->hasOne(UserCreditReportExperian::class, ['id' => 'user_experian_id']);
    }

    public function getUserCreditReportBangaloreExperian(){
        return $this->hasOne(UserCreditReportBangaloreExperian::class, ['id' => 'user_bangalore_experian_id']);
    }

    public function getUserCreditReportShanyunExperian(){
        return $this->hasOne(UserCreditReportShanyunExperian::class, ['id' => 'user_shanyun_experian_id']);
    }

    public function getUserCreditReportMobiExperian(){
        return $this->hasOne(UserCreditReportMobiExperian::class, ['id' => 'user_mobi_experian_id']);
    }
}

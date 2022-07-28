<?php

namespace common\models\workOrder;
use common\helpers\CommonHelper;
use common\helpers\RedisQueue;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * Class UserApplyReduction
 * @package common\models\workOrder
 *
 * 表属性
 * @property int $id
 * @property int $merchant_id
 * @property int $user_id
 * @property int $problem_id 投诉项目ID
 * @property string $description 投诉描述
 * @property string $image_list 图片信息
 * @property string $contact_information 联系方式信息
 * @property int $last_accept_user_id 最后受理人id
 * @property int $last_accept_time 最后受理时间
 * @property int $accept_status 受理状态
 * @property int $created_at 创建时间
 * @property int $updated_at 更新时间
 *
 */
class UserApplyComplaint extends ActiveRecord
{

    const ACCEPT_DEFAULT_STATUS = 0;  //提交后默认进行中
    const ACCEPT_FINISH_STATUS = 1;  //处理完成（关闭）

    public static $accept_status_map = [
        self::ACCEPT_DEFAULT_STATUS => 'wait accept',
        self::ACCEPT_FINISH_STATUS  => 'accept finish',
    ];

    /**
     * @return string
     */
    public static function tableName()
    {
        return '{{%user_apply_complaint}}';
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
     * 判断订单是否有提交的工单在进行中
     * @param $userId
     * @return bool
     */
    public static function isAcceptProgressByUserId($userId){
        return self::find()
            ->where(['user_id' => $userId, 'accept_status' => self::ACCEPT_DEFAULT_STATUS])
            ->exists();
    }

    /**
     * 添加
     * @param $merchantId
     * @param $userId
     * @param $problemId
     * @param $description
     * @param $contact
     * @param $imageList
     * @return bool
     */
    public static function createComplaintWorkOrder($merchantId, $userId, $problemId, $description, $contact, $imageList){
        $userApplyReduction = new self();
        $userApplyReduction->merchant_id = $merchantId;
        $userApplyReduction->user_id = $userId;
        $userApplyReduction->problem_id = $problemId;
        $userApplyReduction->description = $description;
        $userApplyReduction->contact_information = $contact;
        $userApplyReduction->image_list = $imageList;
        $userApplyReduction->accept_status = self::ACCEPT_DEFAULT_STATUS;
        return $userApplyReduction->save();
    }
}
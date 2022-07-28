<?php

namespace callcenter\models;

use callcenter\models\loan_collection\UserCompany;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%dispatch_overdue_days_finish}}".
 *
 * @property int $id
 * @property string $date 分派日期
 * @property int $overdue_day 分派时逾期天数
 * @property int $admin_user_id 分派给的催收员id
 * @property int $dispatch_count 分派单数(个人去重)
 * @property int $new_dispatch_count 分派单数-新客(个人去重)
 * @property int $old_dispatch_count 分派单数-老客(个人去重)
 * @property int $dispatch_amount 分派金额(个人去重)
 * @property int $new_dispatch_amount 分派金额-新客(个人去重)
 * @property int $old_dispatch_amount 分派金额-老客(个人去重)
 * @property int $today_repay_count 当日分派还款订单数
 * @property int $new_today_repay_count 当日分派还款订单数-新客
 * @property int $old_today_repay_count 当日分派还款订单数-老客
 * @property int $today_repay_amount 还款订单的金额
 * @property int $new_today_repay_amount 还款订单的金额-新客
 * @property int $old_today_repay_amount 还款订单的金额-老客
 * @property int $total_repay_count 分派后总的完成订单数
 * @property int $new_total_repay_count 分派后总的完成订单数-新客
 * @property int $old_total_repay_count 分派后总的完成订单数-老客
 * @property int $total_repay_amount 分派后总的完成订单金额
 * @property int $new_total_repay_amount 分派后总的完成订单金额-老客
 * @property int $old_total_repay_amount 分派后总的完成订单金额-新客
 * @property int $created_at 创建时间
 * @property int $updated_at 更新时间
 *
 * @property AdminUser $adminUser;
 * @property UserCompany $userCompany
 */
class DispatchOverdueDaysFinish extends ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%dispatch_overdue_days_finish}}';
    }

    /**
     * @return object|\yii\db\Connection|null
     * @throws \yii\base\InvalidConfigException
     */
    public static function getDb()
    {
        return Yii::$app->get('db_assist');
    }

    public static function getDb_rd()
    {
        return Yii::$app->get('db_assist_read');
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }
    /**
     * 关联资方表
     * @return \yii\db\ActiveQuery
     */
    public function getAdminUser()
    {
        return $this->hasOne(AdminUser::class, ['id' => 'admin_user_id']);
    }

    public function getUserCompany()
    {
        return $this
            ->hasOne(UserCompany::class, ['id' => 'outside'])
            ->via('adminUser');
    }
}

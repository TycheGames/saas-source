<?php

namespace callcenter\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%loan_collection_day_statistic}}".
 *
 */
class LoanCollectionDayStatistics extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%loan_collection_day_statistic}}';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db_assist');
    }
     public static function getDb_rd()
    {
        return Yii::$app->get('db_assist');
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }



    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'admin_user_id' => Yii::t('app', 'admin用户uid'),
            'username' => Yii::t('app', '姓名'),
            'outside' => Yii::t('app', '机构'),
            'total_money' => Yii::t('app', '总本金'),
            'loan_total' => Yii::t('app', '总单数'),
            'today_get_loan_total' => Yii::t('app', '今日入催单数 单位为分'),
            'today_get_total_money' => Yii::t('app', '今日入催本金总额 单位为分'),
            'today_finish_total_money' => Yii::t('app', '今日还款本金总额 单位为分'),
            'finish_total_money' => Yii::t('app', '还款本金总额 单位为分'),
            'no_finish_total_money' => Yii::t('app', '剩余本金总额 单位为分'),
            'operate_total' => Yii::t('app', '处理过的订单个数'),
            'today_finish_total' => Yii::t('app', '当日还款单数'),
            'finish_total' => Yii::t('app', '还款总数'),
            'finish_total_rate' => Yii::t('app', '还款率'),
            'no_finish_rate' => Yii::t('app', '迁徙率'),
            'finish_late_fee' => Yii::t('app', '滞纳金收取金额'),
            'late_fee_total' => Yii::t('app', '本应缴纳的滞纳金总额'),
            'finish_late_fee_rate' => Yii::t('app', '滞纳金回收率'),
            'leave_principal' => Yii::t('app', '转出本金'),
            'get_principal' => Yii::t('app', '转入本金'),
            'created_at' => Yii::t('app', '创建时间'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }
}

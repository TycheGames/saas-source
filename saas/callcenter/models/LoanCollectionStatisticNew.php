<?php

namespace callcenter\models;

use Yii;

/**
 * Class LoanCollectionStatisticNew
 * @package callcenter\models
 * @property int $id
 * @property int $admin_user_id
 * @property string $admin_user_name
 * @property string $real_name
 * @property int $loan_group
 * @property int $outside_id
 * @property int $order_level
 * @property int $today_all_money
 * @property int $loan_finish_total
 * @property int $loan_total
 * @property int $today_finish_money
 * @property int $today_no_finish_money
 * @property int $all_late_fee
 * @property int $finish_late_fee
 * @property int $dispatch_time
 * @property int $operate_total
 * @property int $true_total_money
 * @property int $oneday_money
 * @property int $oneday_total
 * @property float $finish_total_rate
 * @property int  $today_finish_late_fee
 * @property float $no_finish_rate
 * @property int $stage_type
 * @property int $created_at
 * @property int $updated_at
 * @property int $sub_from
 */
class LoanCollectionStatisticNew extends \yii\db\ActiveRecord
{

    static $connect_name = "";

    public function __construct($name = "")
    {
        static::$connect_name = $name;
    }
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%loan_collection_statistic_new}}';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get( !empty(static::$connect_name) ? static::$connect_name : 'db_assist');
    }
    public static function getDb_rd()
    {
        return Yii::$app->get('db_assist_read');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['admin_user_id', 'outside_id', 'today_all_money', 'loan_total', 'today_finish_money', 'loan_finish_total', 'today_no_finish_money', 'operate_total', 'all_late_fee','finish_late_fee'  ], 'integer'],
            ['admin_user_name', 'string', 'max' => 64],
            ['created_at','default', 'value'=>time()],
        ];
    }
    public static function queryCondition($condition,$order=true,$orderBy=['id'=>SORT_DESC]){
        if ($order) return self::find()->where($condition)->orderBy($orderBy);
        return self::find()->where($condition);
    }
}

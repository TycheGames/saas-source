<?php

namespace callcenter\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * Class LoanCollectionStatisticNew
 * @package callcenter\models
 * @property int $id
 * @property int $admin_user_id
 * @property string $admin_user_name
 * @property int $loan_group
 * @property int $outside_id
 * @property int $order_level
 * @property int $today_all_money
 * @property int $loan_finish_total
 * @property int $loan_total
 * @property int $today_finish_money
 * @property int $all_late_fee
 * @property int $finish_late_fee
 * @property int $dispatch_date
 * @property int $operate_total
 * @property int $true_total_money
 * @property int $oneday_money
 * @property int $oneday_total
 * @property float $finish_total_rate
 * @property int  $today_finish_late_fee
 * @property float $no_finish_rate
 * @property int $order_merchant_id
 * @property int $user_merchant_id
 * @property int $created_at
 * @property int $updated_at
 */
class LoanCollectionTrackStatistic extends ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%loan_collection_track_statistic}}';
    }


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

    const TRACK_TYPE_DAY = 1;
    const TRACK_TYPE_GRAND = 2;

    public static $track_type_map = [
        self::TRACK_TYPE_DAY => '日期',
        self::TRACK_TYPE_GRAND => '累计',
    ];

    const OBJ_TYPE_COLLECTOR = 1;
    const OBJ_TYPE_TEAM = 2;
    const OBJ_TYPE_COMPANY = 3;

    public static $obj_type_map = [
        self::OBJ_TYPE_COLLECTOR => '催收员',
        self::OBJ_TYPE_TEAM => '小组',
        self::OBJ_TYPE_COMPANY => '机构',
    ];

    public static $obj_group_map = [
        self::OBJ_TYPE_COLLECTOR => 'admin_user_id',
        self::OBJ_TYPE_TEAM => 'group_game',
        self::OBJ_TYPE_COMPANY => 'outside_id',
    ];

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['admin_user_id', 'outside_id', 'today_all_money', 'loan_total', 'today_finish_money', 'loan_finish_total', 'operate_total', 'all_late_fee','finish_late_fee'  ], 'integer'],
            ['admin_user_name', 'string', 'max' => 64],
        ];
    }
}

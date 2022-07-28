<?php

namespace callcenter\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%input_overday_out}}".
 *
 * @property int $id
 * @property string $date 日期
 * @property string $package_name 来源包
 * @property int $user_type 用户类型
 * @property int $input_count 入催单数
 * @property int $merchant_id 商户ID
 * @property int $overday_total_count 逾期出崔总单
 * @property int $overday1_count 逾期一天出崔单
 * @property int $overday2_count 逾期两天出崔单数
 * ....
 * @property int $overday30_count 逾期30天出崔单数
 * @property int $created_at 创建时间
 * @property int $updated_at 更新时间
 */
class InputOverdayOut extends \yii\db\ActiveRecord
{

    const USER_TYPE_ALL = 0;
    const USER_TYPE_NEW = 1;
    const USER_TYPE_OLD = 2;

    const USER_TYPE_ALL_NEW_SELF_NEW = 5;
    const USER_TYPE_ALL_OLD_SELF_NEW = 6;
    const USER_TYPE_ALL_OLD_SELF_OLD = 7;

    public static $user_type_map = [
        self::USER_TYPE_ALL => 'all',
        self::USER_TYPE_NEW => 'new',
        self::USER_TYPE_OLD => 'old'
    ];

    public static $all_user_type_map = [
        self::USER_TYPE_ALL => 'all',
        self::USER_TYPE_ALL_NEW_SELF_NEW => 'All new self new',
        self::USER_TYPE_ALL_OLD_SELF_NEW => 'All old self new',
        self::USER_TYPE_ALL_OLD_SELF_OLD => 'All old self old'
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%input_overday_out}}';
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
}

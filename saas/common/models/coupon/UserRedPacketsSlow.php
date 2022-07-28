<?php

/**
 * Created by PhpStorm.
 * User: user
 * Date: 2016/5/3
 * Time: 16:08
 */

namespace common\models\coupon;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * 表属性
 * @property int $id
 * @property int $merchant_id
 * @property string $code_pre
 * @property string $title
 * @property string $amount
 * @property int $use_case
 * @property int $use_type
 * @property int $status
 * @property int $user_use_days
 * @property string $user_admin
 * @property string $remark
 * @property int $use_start_time
 * @property int $use_end_time
 * @property int $created_at 创建时间
 * @property int $updated_at 更新时间
 *
 */
class UserRedPacketsSlow extends ActiveRecord {

    public $expire_str;
    const STATUS_DELETE = -1; //已删除
    const STATUS_FALSE = 0;  //失效
    const STATUS_SUCCESS = 1; //启用
    const STATUS_INVALID = 2; //作废

    public static $status_arr = [
        self::STATUS_SUCCESS => "Enable",
        self::STATUS_FALSE => "Not enabled",
        self::STATUS_DELETE => "Deleted",
        self::STATUS_INVALID => "Obsolete",
    ];

    const USE_CASE_FREE = 1;  //还款抵扣券

    public static $use_case_arr = [
        self::USE_CASE_FREE => "Repayment Voucher",
    ];

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return '{{%user_red_packets_slow}}';
    }

    /**
     * @return object|\yii\db\Connection|null
     * @throws \yii\base\InvalidConfigException
     */
    public static function getDb() {
        return Yii::$app->get('db');
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['title', 'use_case'], 'required', 'message' => '不能为空'],
            [['use_start_time', 'use_end_time', 'amount', 'code_pre', 'remark', 'status', 'merchant_id'], 'safe'],
            ['title', 'string', 'max' => 64, 'min' => 2, 'tooLong' => '标题请输入长度为2-15个字符', 'tooShort' => '用户名请输入长度为2-15个字'],
            ['user_use_days', 'integer', 'integerOnly' => true, 'message' => '时间必须是整数'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors() {
        return [
            TimestampBehavior::class,
        ];
    }
}

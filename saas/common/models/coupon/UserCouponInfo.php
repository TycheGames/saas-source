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
use yii\base\Exception;

/**
 * 表属性
 * @property int $id
 * @property int $user_id
 * @property int $merchant_id
 * @property int $phone
 * @property int $coupon_id
 * @property int $use_case
 * @property int $use_type
 * @property string $coupon_code
 * @property string $title
 * @property int $amount
 * @property int $is_use
 * @property string $user_admin
 * @property string $use_time
 * @property string $remark
 * @property int $start_time
 * @property int $end_time
 * @property int $created_at 创建时间
 * @property int $updated_at 更新时间
 *
 */
class UserCouponInfo extends ActiveRecord {

    public $expire_str;

    const COUPON_CASE_FREE = 1; // 抵扣券

    public static $use_case = [
        self::COUPON_CASE_FREE => 'Repayment Voucher',
    ];

    const STATUS_INVALID = -2;// "已作废",
    const STATUS_DELETE = -1; //已删除
    const STATUS_FALSE = 0;   //未使用
    const STATUS_SUCCESS = 1; //已使用
    const STATUS_ING = 2;

    const STATUS_SHOW_NO = 0;//未展示
    const STATUS_SHOW_YET = 1;//已展示

    public static $status_arr = [
        self::STATUS_SUCCESS => "Used",
        self::STATUS_ING => "Using",
        self::STATUS_FALSE => "Unused",
        self::STATUS_DELETE => "Deleted",
        self::STATUS_INVALID => "Obsolete",
    ];

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return '{{%user_coupon_info}}';
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
    public function behaviors() {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * 生成红包批次号
     */
    public static function makePacketCode($prex = '') {
        // 4
        $prex = !empty($prex) ? $prex : "mjp";
        if (strlen($prex) > 3) {
            $prex = substr($prex, 0, 3);
        } else {
            $prex = str_pad($prex, 3, "m", STR_PAD_LEFT);
        }
        // 10
        $now = time();
        // 4
        $rand = rand(1000, 9999);
        // 3
        $uqid = substr(uniqid(), -3);

        return sprintf("%s%s%s%s", $prex, $now, $rand, $uqid);
    }

    /**
     * 可用红包操作
     */
    public static function getUserCouponList($user_id) {
        $now = time();
        $coupon_list = static::find()->from(self::tableName() . ' as a')
            ->leftJoin(UserRedPacketsSlow::tableName() . ' as b', 'a.coupon_id = b.id')
            ->where(['a.user_id' => $user_id])
            ->andWhere(['<=', 'a.start_time', $now])
            ->andWhere(['>=', 'a.end_time', $now])
            ->andWhere(['a.is_use' => static::STATUS_FALSE])
            ->select(['a.is_use', 'a.title', 'a.amount', 'a.use_case', 'a.start_time', 'a.coupon_id', 'a.end_time', 'a.id'])
            ->orderBy('a.amount desc')->asArray()->all();

        return $coupon_list;
    }

    public function changeStatus($status,$time){
        $this->is_use = $status;
        $this->use_time = $time;
        if($this->save()){
            return true;
        }
        return false;
    }
}

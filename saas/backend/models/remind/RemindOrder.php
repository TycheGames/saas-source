<?php
namespace backend\models\remind;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * Class RemindOrder
 * @package backend\models\remind
 * @property int $id
 * @property int $repayment_id
 * @property int $customer_user_id
 * @property int $customer_group
 * @property int $plan_date_before_day
 * @property int $status
 * @property int $remind_return
 * @property int $payment_after_days
 * @property int $remind_count
 * @property int $dispatch_status
 * @property int $dispatch_time
 * @property string $remind_remark
 * @property int $merchant_id
 * @property int $is_test
 * @property int $created_at
 * @property int $updated_at
 */
class RemindOrder extends ActiveRecord
{


    const STATUS_WAIT_REMIND = 0;
    const STATUS_REMINDED = 1;

    public static $status_map = [
        self::STATUS_WAIT_REMIND => 'wait remind',
        self::STATUS_REMINDED => 'reminded',
    ];

    const STATUS_WAIT_DISPATCH = 0;
    const STATUS_FINISH_DISPATCH = 1;
    const STATUS_IS_OVERDUE = 2;
    const STATUS_REPAY_COMPLETE = 3;

    public static $dispatch_status_map = [
        self::STATUS_WAIT_DISPATCH => 'wait dispatch',
        self::STATUS_FINISH_DISPATCH => 'dispatch finish',
        self::STATUS_IS_OVERDUE => 'is overdue',
        self::STATUS_REPAY_COMPLETE => 'repay complete',
    ];

    const REMIND_RETURN_DEFAULT = 0;//初始
    //触达
    const REMIND_RETURN_REPAYMENT_DONE = 1;  //已还
    const REMIND_RETURN_REPAYING_TODAY = 2;  //今天还或后几天还
    const REMIND_RETURN_PAYMENT_AFTER_DAYS = 3;  //今天还或后几天还
    const REMIND_RETURN_REACH_OTHERS = 4;  //其他
    const REMIND_RETURN_CALL_BACK = 5;  //其他

    //未触达
    const REMIND_RETURN_BUSY = -1;  //繁忙
    const REMIND_RETURN_NOT_AVAILABLE = -2;
    const REMIND_RETURN_NOT_ANSWERING = -3;
    const REMIND_RETURN_SWITCHED_OFF = -4;
    const REMIND_RETURN_INVALID = -5;
    const REMIND_RETURN_ADRESS_INCOMPLETE = -6;
    const REMIND_RETURN_NOT_REACH_OTHER = -7;
    const REMIND_RETURN_RINGING = -8;
    const REMIND_RETURN_NETWORK_ISSUES= -9;


    public static $remind_return_map_all = [
        self::REMIND_RETURN_REPAYMENT_DONE => 'repayment done',
        self::REMIND_RETURN_REPAYING_TODAY => 'repaying today',
        self::REMIND_RETURN_PAYMENT_AFTER_DAYS => 'payment after days',
        self::REMIND_RETURN_REACH_OTHERS => 'other(reach)',
        self::REMIND_RETURN_CALL_BACK => 'call back',
//////
        self::REMIND_RETURN_BUSY => 'busy',
        self::REMIND_RETURN_NOT_AVAILABLE => 'not available',
        self::REMIND_RETURN_NOT_ANSWERING => 'not answering',
        self::REMIND_RETURN_SWITCHED_OFF => 'switched off',
        self::REMIND_RETURN_INVALID => 'invalid',
        self::REMIND_RETURN_ADRESS_INCOMPLETE => 'adress incomplete',
        self::REMIND_RETURN_NOT_REACH_OTHER => 'others(no reach)',
        self::REMIND_RETURN_RINGING => 'ringing',
        self::REMIND_RETURN_NETWORK_ISSUES => 'network issues',
    ];

    public static $remind_reach_return = [
        self::REMIND_RETURN_REPAYMENT_DONE => 'repayment done',
        self::REMIND_RETURN_REPAYING_TODAY => 'repaying today',
        self::REMIND_RETURN_PAYMENT_AFTER_DAYS => 'payment after days',
        self::REMIND_RETURN_REACH_OTHERS => 'other',
        self::REMIND_RETURN_CALL_BACK => 'call back',
    ];

    public static $remind_no_reach_return = [
        self::REMIND_RETURN_BUSY => 'busy',
        self::REMIND_RETURN_NOT_AVAILABLE => 'not available',
        self::REMIND_RETURN_NOT_ANSWERING => 'not answering',
        self::REMIND_RETURN_SWITCHED_OFF => 'switched off',
        self::REMIND_RETURN_INVALID => 'invalid',
        self::REMIND_RETURN_ADRESS_INCOMPLETE => 'adress incomplete',
        self::REMIND_RETURN_NOT_REACH_OTHER => 'others',
        self::REMIND_RETURN_RINGING => 'ringing',
        self::REMIND_RETURN_NETWORK_ISSUES => 'network issues',
    ];

    const REMIND_REACH_DEFAULT = 0;
    const REMIND_REACH = 1;
    const REMIND_NO_REACH = 2;

    public static $remind_reach_map = [
        self::REMIND_REACH_DEFAULT => 'default',
        self::REMIND_REACH => 'reach',
        self::REMIND_NO_REACH => 'no reach',
    ];

    public static $payment_after_days_map = [
        1 => '1 day',
        2 => '2 days',
        3 => '3 days',
        4 => '4 days',
        5 => '5 days',
        6 => '6 days',
        7 => '7 days',
        8 => '8 days',
        9 => '9 days',
        10 => '10 days'
    ];

//1. 触达，未触达
//2. 对于触达，包括以下子选项：repayment done、payment after XX days（可填写具体天数）、repaying today、others
//3. 对于未触达，包括：busy、not available、not answering、switched off、invalid、adress incomplete、others

    const NOT_TEST_CAN_DISPATCH = 0;
    const TEST_CAN_NOT_DISPATCH = 1;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%remind_order}}';
    }

    public static function getDb()
    {
        return Yii::$app->get('db');
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
}
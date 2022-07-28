<?php

namespace common\models\message;
use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\web\NotFoundHttpException;
use common\models\order\UserLoanOrder;



/**
 * Class MessageTimeTask
 * @package common\models\message
 *
 * @property int $id
 * @property int $tips_type
 * @property int $days_type
 * @property int $user_type
 * @property string $config
 * @property int $task_time
 * @property int $task_status
 * @property string $remark
 * @property string $handle_log
 * @property string $send_log
 * @property int $is_app_notice
 * @property int $created_at
 * @property int $updated_at
 * @property int $is_export
 * @property int $merchant_id
 */
class MessageTimeTask extends ActiveRecord
{
    // 提醒类型 - tips_type
    const TIPS_TODAY    = 1; // 当日
    const TIPS_FORWARD  = 2; // 提前
    const TIPS_OVERDUE  = 3; // 逾期
    const TIPS_DRAW_MONEY  = 4; // 待提现
    const TIPS_DRAW_MONEY_AUTO  = 5; // 待提现自动提现

    const Is_EXPORT_NO  = 0; // 内部
    const Is_EXPORT_YES  = 1; // 外部

    public static $tips_type_map = [
        self::TIPS_TODAY    => 'today',
        self::TIPS_FORWARD  => 'forward',
        self::TIPS_OVERDUE  => 'overdue',
        self::TIPS_DRAW_MONEY => 'withdrawal',
        self::TIPS_DRAW_MONEY_AUTO => 'withdrawal-auto',

    ];

    public static $is_export_map = [
        self::Is_EXPORT_NO => 'is_export_no',
        self::Is_EXPORT_YES => 'is_export_yes',
    ];

    const USER_TTPE_ALL = 0;
    const USER_TYPE_NEW = 1;
    const USER_TYPE_OLD = 2;

    public static $user_type_map = [
        self::USER_TTPE_ALL => 'all',
        self::USER_TYPE_NEW => 'new',
        self::USER_TYPE_OLD => 'old',
    ];

    // 任务定时 - task_time
    const TIME_Q = 17; // 00:00
    const TIME_QQ = 67; // 00:30
    const TIME_R = 18; // 01:00
    const TIME_RR = 68; // 01:30
    const TIME_S = 19; // 02:00
    const TIME_SS = 69; // 02:30
    const TIME_T = 20; // 03:00
    const TIME_TT = 70; // 03:30
    const TIME_U = 21; // 04:00
    const TIME_UU = 71; // 04:30
    const TIME_V = 22; // 05:00
    const TIME_VV = 72; // 05:30
    const TIME_W = 23; // 06:00
    const TIME_WW = 73; // 06:30
    const TIME_X = 24; // 07:00
    const TIME_XX = 74; // 07:30

    const TIME_A = 1; // 8:00
    const TIME_AA = 51; // 8:30
    const TIME_B = 2; // 9:00
    const TIME_BB = 52; // 9:30
    const TIME_C = 3; // 10:00
    const TIME_CC = 53; // 10:30
    const TIME_D = 4; // 11:00
    const TIME_DD = 54; // 11:30
    const TIME_E = 5; // 12:00
    const TIME_EE = 55; // 12:30
    const TIME_F = 6; // 13:00
    const TIME_FF = 56; // 13:30
    const TIME_G = 7; // 14:00
    const TIME_GG = 57; // 14:30
    const TIME_H = 8; // 15:00
    const TIME_HH = 58; // 15:30
    const TIME_I = 9; // 16:00
    const TIME_II = 59; // 16:30
    const TIME_J = 10; // 17:00
    const TIME_JJ = 60; // 17:30
    const TIME_K = 11; // 18:00
    const TIME_KK = 61; // 18:30
    const TIME_L = 12; // 19:00
    const TIME_LL = 62; // 19:30
    const TIME_M = 13; // 20:00
    const TIME_MM = 63; // 20:30
    const TIME_N = 14; // 21:00
    const TIME_NN = 64; // 21:30
    const TIME_O = 15; // 22:00
    const TIME_OO = 65; // 22:30
    const TIME_P = 16; // 23:00
    const TIME_PP = 66; // 23:30


    public static $task_time_map = [

        self::TIME_Q => '00:00',
        self::TIME_QQ => '00:30',
        self::TIME_R => '01:00',
        self::TIME_RR => '01:30',
        self::TIME_S => '02:00',
        self::TIME_SS => '02:30',
        self::TIME_T => '03:00',
        self::TIME_TT => '03:30',
        self::TIME_U => '04:00',
        self::TIME_UU => '04:30',
        self::TIME_V => '05:00',
        self::TIME_VV => '05:30',
        self::TIME_W => '06:00',
        self::TIME_WW => '06:30',
        self::TIME_X => '07:00',
        self::TIME_XX => '07:30',

        self::TIME_A => '08:00',
        self::TIME_AA => '08:30',
        self::TIME_B => '09:00',
        self::TIME_BB => '09:30',
        self::TIME_C => '10:00',
        self::TIME_CC => '10:30',
        self::TIME_D => '11:00',
        self::TIME_DD => '11:30',
        self::TIME_E => '12:00',
        self::TIME_EE => '12:30',
        self::TIME_F => '13:00',
        self::TIME_FF => '13:30',
        self::TIME_G => '14:00',
        self::TIME_GG => '14:30',
        self::TIME_H => '15:00',
        self::TIME_HH => '15:30',
        self::TIME_I => '16:00',
        self::TIME_II => '16:30',
        self::TIME_J => '17:00',
        self::TIME_JJ => '17:30',
        self::TIME_K => '18:00',
        self::TIME_KK => '18:30',
        self::TIME_L => '19:00',
        self::TIME_LL => '19:30',
        self::TIME_M => '20:00',
        self::TIME_MM => '20:30',
        self::TIME_N => '21:00',
        self::TIME_NN => '21:30',
        self::TIME_O => '22:00',
        self::TIME_OO => '22:30',
        self::TIME_P => '23:00',
        self::TIME_PP => '23:30',
    ];

    // 任务状态 - task_status
    const STATUS_INI  = 0; // 初始化
    const STATUS_ON   = 1; // 开启
    const STATUS_DOWN = 2; // 关闭
    public static $task_status_map = [
        self::STATUS_INI  => 'init',
        self::STATUS_ON    => 'open',
        self::STATUS_DOWN  => 'close',
    ];

    // 是否作为app消息 - is_app_notice
    const APP_NOTICE_NO  = 0; // 否
    const APP_NOTICE_YES = 1; // 是
    public static $is_app_notice_map = [
        self::APP_NOTICE_NO     => 'Not set as App message',
        self::APP_NOTICE_YES    => 'SET as App message',
    ];


    const SEND_SINGLE = 0;
    const SEND_BATCH = 1;

    public static $is_batch_send_map = [
        self::SEND_SINGLE => 'single',
        self::SEND_BATCH => 'batch'
    ];

    // 不配置通道key值默认
    const smsService_None = 'smsService_None';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%message_time_task}}';
    }


    /**
     * @return object|\yii\db\Connection|null
     * @throws \yii\base\InvalidConfigException
     */
    public static function getDb()
    {
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
     * @inheritdoc
     */
    public function rules() {
        return [
            [['tips_type', 'days_type', 'task_time', 'is_app_notice', 'user_type', 'is_export'], 'required'],
            [['tips_type',  'task_time', 'is_app_notice', 'user_type', 'is_export', 'merchant_id'], 'integer'],
            [['config', 'remark', 'handle_log', 'days_type'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            "id"            => "任务ID",
            "tips_type"     => YII::t('common','Reminder type'),
            "days_type"     => YII::t('common','days'),
            "config"        => "配置",
            "task_time"     => YII::t('common','Task timing'),
            "task_status"   => Yii::T('common', 'Task status'),
            'user_type'     => YII::t('common','user type'),
            "remark"        => "备注",
            "handle_log"    => "操作日志",
            "created_at"    => "创建时间",
            "updated_at"    => "更新时间",
            "send_log"      => "发送日志",
            "is_app_notice" => "App消息",
            "is_export"     => "内外部订单",
            "merchant_id"     => "商户ID",
        ];
    }


    // 获取配置文件中通道key数组
    public static function getAisleType($pack_name,$merchantIds)
    {
        $config_params = Yii::$app->params;
        $aisle_type = [
            self::smsService_None => 'Not set',
        ];
        foreach($config_params as $c_k => $c_v) {
            if(strpos($c_k,'smsService') === 0 && isset($c_v['aisle'])){
                if($merchantIds == 0)
                {
                    $aisle_type[$c_v['aisle']][$c_k] = $c_v['aisle_title'];
                }else
                {
                    if($c_v['aisle'] == $pack_name)
                    {
                        $aisle_type[$c_v['aisle']][$c_k] = $c_v['aisle_title'];
                    }
                }
            }
        }
        return $aisle_type;
    }

}

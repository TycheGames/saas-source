<?php
namespace common\models\message;

use Yii;
use yii\db\ActiveRecord;
use common\helpers\MessageHelper;

/*
 @up_time 2018-05-09 11:06
 @action 消息记录 - 消息中心列表&短信消息
 */
class NoticeSms extends ActiveRecord
{
    // 消息状态
    const SEND_WAIT    = 0;// 等待信息发送
    const SEND_SUCCESS = 1;// 信息发送成功
    const SEND_FAIL    = 2;// 信息发送失败
    const SEND_DOWN    = 3;// 消息关闭
    public static $send_status = [
        self::SEND_WAIT    => '默认',
        self::SEND_SUCCESS => '请求发送成功',
        self::SEND_FAIL    => '请求发送失败',
        self::SEND_DOWN    => '显示关闭 - 用户不可见',
    ];

    // 消息阅读状态
    const READ_NO  = 0; // 信息未读
    const READ_YES = 1; // 信息已读
    public static $read_status = [
        self::READ_NO    => '未读',
        self::READ_YES   => '已读',
    ];

    // 是否发送短信通知
    const SEND_SMS_Y = 1; // 默认发送短信
    const SEND_SMS_N = 0; // 不发送短信
    public static $send_sms = [
        self::SEND_SMS_Y => '发送',
        self::SEND_SMS_N => '不发送',
    ];

    // 消息类型 - 259版本前使用
    const NOTICE_REPAYMENT = 1;  // 通知消息
    public static $types = [
        self::NOTICE_REPAYMENT => '通知'
    ];

    // APP - 我的 - 消息中心Tab
    const CENTER_TAB_NOTICE     = 1; // 订单消息
    const CENTER_TAB_ACTIVITY   = 2; // 活动
    const CENTER_TAB_NEWPOK     = 3; // 新口子
    const CENTER_TAB_CONTENT    = 4; // 公告
    public static $center_tab = [
        self::CENTER_TAB_NOTICE => "订单",
        self::CENTER_TAB_ACTIVITY => "活动",
        self::CENTER_TAB_NEWPOK => "新口子",
        self::CENTER_TAB_CONTENT => "公告中心",
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%notice_sms}}';
    }

    /**
     * @inheritdoc
     */
    public function rules(){
        return [];
    }

    /**
     * 拼接消息信息字符串
     * @param $memo 参数数组
     * @return bool
     */
    const STARTSTR = '';
    public function init_sms_str($memo)
    {
        if(
            !is_array($memo) || 
            !isset($memo['user_id']) || empty($memo['user_id']) || 
            !isset($memo['phone']) || empty($memo['phone']) || 
            !isset($memo['title']) || empty($memo['title']) || 
            !isset($memo['content']) || empty($memo['content'])
        ){
            return false;
        }
        $is_send_sms = isset($memo['is_send_sms']) ? intval($memo['is_send_sms']) : self::SEND_SMS_Y; // 默认发送
        $aisle_key = isset($memo['aisle_key']) ? trim($memo['aisle_key']) : '';
        if($is_send_sms && empty($aisle_key)){
            return false;
        }
        $user_id = intval($memo['user_id']);
        $phone = trim($memo['phone']);
        $title = trim($memo['title']);
        $content = trim($memo['content']);
        $status = self::SEND_WAIT;
        $message_id = 0;
        if($is_send_sms){
            $ret = MessageHelper::sendAll($phone, $content, $aisle_key);
            $message_id = $ret;
            if(is_array($ret)){
                foreach($ret as $v){
                    if(isset($v['send_id'])){
                        $message_id = $v['send_id'];
                        break;
                    }
                }
            }
            $status = $message_id ? self::SEND_SUCCESS : self::SEND_FAIL;
        }
        $insert_ret = self::InsertDate($user_id, $phone, $title, $content, $aisle_key, $is_send_sms, $status, $message_id);
        return [
            'code' => $insert_ret ? 0 : -1,
        ];
    }

    // 插入消息表
    public function InsertDate($user_id, $phone, $title, $content, $aisle_key, $is_send_sms, $status, $message_id)
    {
        if(empty($user_id) || empty($phone) || empty($title) || empty($content)){
            return false;
        }

        $result = Yii::$app->db->createCommand()->insert(self::tableName(), [
            'user_id' => $user_id,
            'phone' => $phone,
            'title' => $title,
            'content' => $content,
            'aisle_key' => $aisle_key,
            'is_send_sms' => $is_send_sms,
            'message_id' => $message_id ? $message_id : 0,
            'status' => $status,
            'created_at' => time(),
            'updated_at' => time(),
        ])->execute();
        // 插入失败
        if(!$result){
            Yii::error("Notice_SMS insert failed, user_id:{$user_id}, phone:{$phone}, title:{$title}, content:{$content}, aisle_key:{$aisle_key}, is_send_sms:{$is_send_sms}, status:{$status}. ");
        }
        return $result;
    }

    // 查询消息数据
    public static function getNoticeData($user_id, $page, $page_size)
    {
        // 更新已读
        NoticeSms::updateAll(['is_read' => self::READ_YES],['user_id'=>$user_id,'is_read' => self::READ_NO]);

        // 分页拉取数据
        $notice_info = [];
        $notice_sms = NoticeSms::find()
            ->where(['user_id' => $user_id,'status' => [self::SEND_WAIT,self::SEND_SUCCESS,self::SEND_FAIL]])
            ->orderBy('id desc')
            ->offset($page_size * ($page - 1))->limit($page_size)
            ->asArray()->all();
        foreach($notice_sms as $notice){
            $notice_info[] = [
                'itemType' => self::CENTER_TAB_NOTICE,
                'title' => $notice['title'],
                'date' => date('Y-m-d H:i',$notice['created_at']),
                'content' => $notice['content'],
                'jump' => ''
            ];
        }

        return [
            'info' => $notice_info
        ];
    }

    // 判断是否有红点存在
    public static function getIsRed($user_id)
    {
        $no_read_info = NoticeSms::find()
            ->where(['user_id' => $user_id,'is_read' => self::READ_NO,'status' => [self::SEND_WAIT,self::SEND_SUCCESS,self::SEND_FAIL]])
            ->orderBy('id desc')->one();
        $is_red = $no_read_info ? true : false;
        $timestamp = $no_read_info ? $no_read_info->created_at : 0;
        return [
            'is_red' => $is_red,
            'timestamp' => $timestamp
        ];
    }
}
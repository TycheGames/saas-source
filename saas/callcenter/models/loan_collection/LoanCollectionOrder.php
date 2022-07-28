<?php

namespace callcenter\models\loan_collection;

use common\helpers\RedisQueue;
use yii\db\ActiveRecord;
use common\models\order\UserLoanOrder;
use common\models\order\UserLoanOrderRepayment;
use common\models\user\LoanPerson;
use Yii;
use yii\base\Event;
use yii\base\Exception;
use yii\behaviors\TimestampBehavior;

/**
 * Class LoanCollectionOrder
 * @package callcenter\models\loan_collection
 * @property int id
 * @property int outside
 * @property int current_overdue_level
 * @property int current_collection_admin_user_id
 * @property string $dispatch_name 派单人
 * @property int $dispatch_time 派单时间
 * @property int $last_dispatch_time 最后派单时间
 * @property int $dispatch_outside_time 派单给机构的时间
 * @property int $current_overdue_group
 * @property int $customer_type  入催时新老客：1老用户0新用户
 * @property int $status
 * @property int $before_status
 * @property int $open_app_apply_reduction 是否打开用户可提交减免信息
 * @property int $user_id
 * @property int $is_purpose 偿还意愿
 * @property int $promise_repayment_time 承诺还款时间
 * @property int $last_collection_time 最后一次操作时间
 * @property int $next_loan_advice
 * @property int $user_loan_order_id  借款订单id
 * @property int $user_loan_order_repayment_id 还款明细ID
 * @property int $merchant_id
 * @property int $updated_at 更新时间
 * @property string $operator_name 操作人
 * @property string $remark
 *
 * @property UserLoanOrderRepayment $repaymentOrder
 * @property UserLoanOrder $loanOrder
 */

class LoanCollectionOrder extends ActiveRecord
{

    const EVENT_DISPATCH_TO_COLLECTION = 'event_dispatch_to_collection';  //派单
    const EVENT_TO_WAIT_COLLECTION = 'event_to_wait_collection';  //回收
    const EVENT_LAST_COLLECTION_TIME_CHANGE = 'event_last_collection_time_change';  //添加催记更新last_collection_time

    /**
     * model初始化
     */
    public function init(){
        parent::init();
        $this->on(static::EVENT_DISPATCH_TO_COLLECTION, ['callcenter\service\LoanCollectionService', 'loanCollectionOrderEventHandler']);
        $this->on(static::EVENT_LAST_COLLECTION_TIME_CHANGE, ['callcenter\service\LoanCollectionService', 'loanCollectionOrderEventHandler']);
        $this->on(static::EVENT_TO_WAIT_COLLECTION, ['callcenter\service\LoanCollectionService', 'loanCollectionOrderEventHandler']);
    }


    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if (!$insert && isset($changedAttributes['status']) && $this->status == self::STATUS_COLLECTION_PROGRESS) {
            //分派
            $this->trigger(static::EVENT_DISPATCH_TO_COLLECTION, new Event());
        }
        if (!$insert && isset($changedAttributes['last_collection_time'])) {
            //写催记 last_collection_time 更新时
            $this->trigger(static::EVENT_LAST_COLLECTION_TIME_CHANGE, new Event());
        }
        if (!$insert && isset($changedAttributes['status']) && $this->status == self::STATUS_WAIT_COLLECTION) {
            //回收
            $this->trigger(static::EVENT_TO_WAIT_COLLECTION, new Event());
        }

    }

    const OPEN_APP_APPLY_REDUCTION = 1;
    const DEFAULT_CLOSE_APP_APPLY_REDUCTION = 0;

    public $amount = 0;

    const LEVEL_S1_1_3DAY = 2;
    const LEVEL_S1_4_7DAY = 3;
    const LEVEL_S1 = 4; //停用
    const LEVEL_S2 = 5;
    const LEVEL_M1 = 6;
    const LEVEL_M2 = 7;
    const LEVEL_M3 = 8;
    const LEVEL_M3_AFTER = 9;


    //催收订单的阶段：
    public static $level = [
        self::LEVEL_S1_1_3DAY =>'S1 D1-3',
        self::LEVEL_S1_4_7DAY =>'S1 D4-7',
        self::LEVEL_S1 =>'S1(Void)',  //停用
        self::LEVEL_S2 =>'S2',
        self::LEVEL_M1 =>'M1',
        self::LEVEL_M2 =>'M2',
        self::LEVEL_M3 =>'M3',
        self::LEVEL_M3_AFTER =>'M3+',

    ];

    //催收订单的阶段：
    public static $current_level = [
        self::LEVEL_S1_1_3DAY =>'S1 D1-3',
        self::LEVEL_S1_4_7DAY =>'S1 D4-7',
        self::LEVEL_S2 =>'S2',
        self::LEVEL_M1 =>'M1',
        self::LEVEL_M2 =>'M2',
        self::LEVEL_M3 =>'M3',
        self::LEVEL_M3_AFTER =>'M3+',

    ];


    const IS_NEW = 0;  // 新用户
    const IS_OLD = 1;  // 老用户

    //回收逾期天数
    public static $reset_overdue_days =[
        //        1 =>self::LEVEL_S1,
        1 => self::LEVEL_S1_1_3DAY,
        4 => self::LEVEL_S1_4_7DAY,
        8 =>self::LEVEL_S2,
        16=>self::LEVEL_M1,
        31=>self::LEVEL_M2,
        61=>self::LEVEL_M3,
        91=>self::LEVEL_M3_AFTER
    ] ;

    //后手
    public static $after_level = [
        self::LEVEL_M1,
        self::LEVEL_M2,
        self::LEVEL_M3,
        self::LEVEL_M3_AFTER,
    ];

    public static $customer_types = [
        self::IS_NEW =>"new",
        self::IS_OLD =>"old"
    ];

    //
    const STATUS_WAIT_COLLECTION = 11;  //待（分派到）催收
    const STATUS_COLLECTION_PROGRESS = 12;  //催收中
    const STATUS_COLLECTION_PROMISE = 13; //承诺还款
    const STATUS_COLLECTION_FINISH = 20;  //催收成功
    const STATUS_STOP_URGING = 30;     //停催
    const STATUS_DELAY_STOP_URGING = 40;   //延期

    //催收相关状态，
    public static $collection_status = [
        self::STATUS_COLLECTION_PROGRESS,
        self::STATUS_COLLECTION_PROMISE,
    ];

    public static $status_list = [
        self::STATUS_WAIT_COLLECTION =>'wait collection', //待（分派到）催收
        self::STATUS_COLLECTION_PROGRESS =>'in the collection', //催收中
        self::STATUS_COLLECTION_PROMISE =>'promise repayment', //承诺还款
        self::STATUS_COLLECTION_FINISH =>'collection finish', //催收成功
        self::STATUS_STOP_URGING=>'stop collection', //停催
        self::STATUS_DELAY_STOP_URGING => 'delay stop' //延期
    ];

    public static $not_end_status = [
        self::STATUS_WAIT_COLLECTION,
        self::STATUS_COLLECTION_PROGRESS,
        self::STATUS_COLLECTION_PROMISE,
    ];

    public static $end_status = [
        self::STATUS_COLLECTION_FINISH,
        self::STATUS_STOP_URGING,
        self::STATUS_DELAY_STOP_URGING
    ];

    //订单转换状态
    const TYPE_INPUT_COLLECTION = 5;
    const TYPE_DISPATCH_COLLECTION = 6;
    const TYPE_LEVEL_CHANGE = 7;
    const TYPE_USER_CHANGE = 8;
    const TYPE_AUTO_RECYCLE_BY_DAY = 9;
    const TYPE_LEVEL_FINISH = 100;
    const TYPE_STOP_URGING = 101;
    const TYPE_STOP_URGING_RECOVERY = 102;
    const TYPE_DELAY_STOP_URGING = 103;
    const TYPE_DELAY_STOP_URGING_RECOVERY = 104;
    const TYPE_RECYCLE = 99;
    const TYPE_MANUAL = 999;

    public static $type = [
        self::TYPE_INPUT_COLLECTION             =>'input collection',   //入催
        self::TYPE_DISPATCH_COLLECTION          =>'dispatch collection',    //催收派单
        self::TYPE_LEVEL_CHANGE                 =>'level change', //等级转换
        self::TYPE_USER_CHANGE                  =>'user change',  //转单
        self::TYPE_AUTO_RECYCLE_BY_DAY          =>'auto recycle by overdue day',  //同等级逾期数到短账期回收
        self::TYPE_LEVEL_FINISH                 =>'collection finish', //催收完成
        self::TYPE_STOP_URGING                  =>'collection stop', //催停催
        self::TYPE_STOP_URGING_RECOVERY         =>'collection recovery', //催停催恢复
        self::TYPE_DELAY_STOP_URGING            =>'delay stop', //延期停催
        self::TYPE_DELAY_STOP_URGING_RECOVERY   =>'delay recovery', //延期恢复
        self::TYPE_RECYCLE                      =>'recycle', //回收(重置状态)
        self::TYPE_MANUAL                       =>'manual',  //人工处理
    ];

    const RENEW_PASS = 1;
    const RENEW_DEFAULT = 0;
    const RENEW_REJECT = -1;
    const RENEW_CHECK = 2;

    public static $next_loan_advice = [
        self::RENEW_DEFAULT => 'default',
        self::RENEW_PASS => 'pass',
        self::RENEW_REJECT => 'reject',
        self::RENEW_CHECK => 'review',
    ];
    public static $before_next_loan_advice = [
        self::RENEW_DEFAULT => 'default',
        self::RENEW_REJECT => 'reject',
        self::RENEW_CHECK => 'review',
    ];

    public static function getResetLevelNameByOverdueDays($day){
        $list = self::getResetDispatchLevelList();
        if(isset($list[$day])){
            return $list[$day];
        }else{
            return false;
        }
    }

    public static function getResetLevelNameByOverdueDaysRange($day){
        $list = self::getResetDispatchLevelList();
        $res = false;
        foreach ($list as $d => $value){
            if($day >= $d){
                $res = $value;
            }else{
                break;
            }
        }
        return $res;
    }

    /**
     * @uses 获取逾期等级list
     * @return array
     */
    public static function getResetDispatchLevelList(){
        $dayLevelArr = [];
        foreach (self::$reset_overdue_days as $day => $level){
            $dayLevelArr[$day] = ['name' => LoanCollectionOrder::$level[$level], 'level' => $level];
        }
        return $dayLevelArr;
    }


    //返回催收成功，但无催收建议的记录
    public static function noSuggest(){
        return self::find()->select(['id', 'user_loan_order_id', 'user_loan_order_repayment_id', 'status'])
            ->where(['next_loan_advice'=>self::RENEW_DEFAULT, 'status'=>self::STATUS_COLLECTION_FINISH])
            ->asArray()->all(self::getDb_rd());
    }


    /**
     *根据用户ID，返回其当前任务
     * @param $userId
     * @return array
     */
    public static function missionUser($userId = ''){
        $uid = empty($userId) ? Yii::$app->user->id : $userId;
        $res = self::find()->select("`status`, count(id) AS `amount`")
            ->where([
                'current_collection_admin_user_id' => intval($uid),
                'status' => self::$not_end_status
            ])
            ->groupBy(['`status`'])->all();
        $result = array();
        if(!empty($res)){
            foreach ($res as $key => $item) {
                $result[$item['status']] = $item['amount'];
            }
        }
        return $result;
    }

    public static function ids($ids = array()){
        $result = array();
        $res = self::find()->select("*")->where(['id' => $ids])->all();
        if(!empty($res)){
            foreach ($res as $key => $item) {
                $result[$item['id']] = $item;
            }
        }
        return $result;
    }
    public static function arrayIds($ids = array()){
        if(empty($ids)) return array();
        $result = array();
        $res = self::find()->select("*")->asArray()->where(['id' => $ids])->all();
        if(!empty($res)){
            foreach ($res as $key => $item) {
                $result[$item['id']] = $item;
            }
        }
        return $result;
    }


    /**
     *更新催收建议
     *@param int $collectionId 催收记录ID
     *@param int $advice 催收建议
     *@param string $remark 备注
     *@return mixed true:成功, string:失败
     */
    public static function updateNextLoanAdvice($collectionId=0, $advice = 0, $remark = '手动'){
        if(!array_key_exists($advice, self::$next_loan_advice)) return false;
        try{
            if ((Yii::$app instanceof \yii\web\Application) && !empty(Yii::$app->user->identity)) {
                $username = Yii::$app->user->identity->username;
                $outside = Yii::$app->user->identity->outside;
            } elseif ((Yii::$app instanceof \yii\web\Application) && empty(Yii::$app->user->identity)) {
                throw new Exception("Sorry, please login");
            } else {
                $username = "system";
            }
            $item = self::find()->where(['id'=>$collectionId])->one();
            if(empty($item)){
                $cur_user = $username;
                throw new Exception("Failed to update collection suggestion——Collection records do not exist，ID：".$collectionId.", cur_user：".$cur_user);
            }
            $advice_before = $item->next_loan_advice;
            $item->next_loan_advice = $advice;
            if(!$item->save()){
                throw new Exception("Failed to update collection suggestion——Suggested update failed ID：".$collectionId.", suggest：".$advice);
            }
            $operator = $username;
            //建议转换
            $loan_collection_suggestion_change_log = new LoanCollectionSuggestionChangeLog();
            $loan_collection_suggestion_change_log->collection_id = $collectionId;
            $loan_collection_suggestion_change_log->order_id = $item->user_loan_order_id;
            $loan_collection_suggestion_change_log->suggestion_before = $advice_before;
            $loan_collection_suggestion_change_log->suggestion = $advice;
            $loan_collection_suggestion_change_log->created_at = time();
            $loan_collection_suggestion_change_log->operator_name = $operator;
            $loan_collection_suggestion_change_log->remark = $remark;
            if(isset($outside)){
                $loan_collection_suggestion_change_log->outside = $outside;
            }
            if(!$loan_collection_suggestion_change_log->save()){
                throw new Exception("Failed to create a collection suggestion record, ID：".$collectionId.", suggest：".$advice);
            }

            if($advice == self::RENEW_REJECT){
                RedisQueue::push([RedisQueue::PUSH_COLLECTION_SUGGESTION_DATA, $item->user_loan_order_id]);
            }

            return true;

        }catch(Exception $e){
            return $e->getMessage();
        }

    }
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%loan_collection_order}}';
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
        return Yii::$app->get('db_assist_read');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'user_loan_order_id', 'user_loan_order_repayment_id', 'dispatch_time', 'current_collection_admin_user_id', 'current_overdue_level', 'status', 'promise_repayment_time', 'last_collection_time', 'created_at', 'updated_at'], 'integer'],
            [['next_loan_advice'], 'integer'],[['remark'],'string'],
            [['dispatch_name', 'operator_name'], 'string', 'max' => 50]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'user_id' => Yii::t('app', '借款人ID'),
            'user_loan_order_id' => Yii::t('app', '借款记录ID'),
            'user_loan_order_repayment_id' => Yii::t('app', '还款明细ID'),
            'dispatch_name' => Yii::t('app', '派单人'),
            'dispatch_time' => Yii::t('app', '派单时间'),
            'current_collection_admin_user_id' => Yii::t('app', '当前催收员ID'),
            'current_overdue_level' => Yii::t('app', '当前逾期等级'),
            'status' => Yii::t('app', '催收状态'),
            'promise_repayment_time' => Yii::t('app', '承诺还款时间'),
            'last_collection_time' => Yii::t('app', '最后催收时间'),
            'next_loan_advice' => Yii::t('app', '下次贷款建议'),
            'created_at' => Yii::t('app', '创建时间'),
            'updated_at' => Yii::t('app', '更新时间'),
            'operator_name' => Yii::t('app', '操作人'),
            'remark' => Yii::t('app', '备注'),
        ];
    }

    public function getLoanPerson()
    {
        return $this->hasOne(LoanPerson::class, ['id' => 'user_id']);
    }

    public function getRepaymentOrder()
    {
        return $this->hasOne(UserLoanOrderRepayment::class, ['id' => 'user_loan_order_repayment_id']);
    }

    public function getLoanOrder()
    {
        return $this->hasOne(UserLoanOrder::class, ['id' => 'user_loan_order_id']);
    }



    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * 判断订单在app是否开启减免信息申请提交
     * @param $orderId
     * @return bool
     */
    public static function isOpenAppApplyReductionByOrderId($orderId){
        return self::find()
            ->where(['user_loan_order_id' => $orderId, 'open_app_apply_reduction'=> self::OPEN_APP_APPLY_REDUCTION])
            ->exists();
    }


    /**
     * 根据订单号获取在催催收员id
     * @param $orderId
     * @return int
     */
    public static function getCollectorIdByOrderId($orderId){
        /** @var self $collectionOrder */
        $collectionOrder =  self::find()->where(['user_loan_order_id' => $orderId])->one();
        if(is_null($collectionOrder)){
            return 0;
        }
        return $collectionOrder->current_collection_admin_user_id ?? 0;
    }

    /**
     *添加催收分派锁（按商户）
     * @param int $merchantId
     * @param int $expire
     * @return bool
     */
    public static function lockCollectionDispatchMerchant($merchantId,$expire = 60)
    {
        $lock_key = 'collection:dispatch:merchant:'.$merchantId;

        if (1 == RedisQueue::inc([$lock_key, 1])) {
            RedisQueue::expire([$lock_key, $expire]);
            return true;
        }
        return false;
    }

    /**
     * 释放催收分派锁（按商户）
     * @param int $merchantId
     */
    public static function releaseCollectionDispatchMerchantLock($merchantId)
    {
        $lock_key = 'collection:dispatch:merchant:'.$merchantId;
        RedisQueue::del(["key" => $lock_key]);
    }
}

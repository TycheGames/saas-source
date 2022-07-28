<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/10/4
 * Time: 14:46
 */
namespace common\models\manual_credit;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * Class ManualCreditModule
 * @property $order_id
 * @property $action
 * @property $type
 * @property $merchant_id 订单商户ID
 * @property $operator_id
 * @property $reject_rule_id
 * @property $que_info
 * @property $remark
 * @property $pan_code
 * @property $package_name 下单包
 * @property $bank_account 绑卡卡号
 * @property $is_auto
 * @property $created_at
 * @property $updated_at
 */

class ManualCreditLog extends ActiveRecord
{
    const TYPE_PASS = 1;
    const TYPE_REJECT = 2;

    public static $type_list = [
        self::TYPE_PASS => 'pass',
        self::TYPE_REJECT => 'reject'
    ];

    const ACTION_AUDIT_CREDIT = 1; //信审
    const ACTION_AUDIT_BANK = 2; //绑卡审核

    public static $action_list = [
        self::ACTION_AUDIT_CREDIT => '信审审核',
        self::ACTION_AUDIT_BANK => '绑卡审核',
    ];

    const NO_AUTO = 0;
    const IS_AUTO = 1;

    public static $is_auto_map = [
        self::NO_AUTO => '人工人审',
        self::IS_AUTO => '自动人审',
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%manual_credit_log}}';
    }


    public static function getDb()
    {
        return Yii::$app->get('db');
    }

    public function behaviors() {
        return [
            TimestampBehavior::class
        ];
    }

    public static function addLog($orderId,$merchant_id,$panCode,$packageName,$operatorId,$action,$type,$reject_rule_id,$queInfo,$remark = '',$isAuto = 0,$account = ''){
        $log = new static();
        $log->order_id = $orderId;
        $log->action = $action;
        $log->type = $type;
        $log->merchant_id = $merchant_id;
        $log->operator_id = $operatorId;
        $log->reject_rule_id = $reject_rule_id;
        $log->que_info = json_encode($queInfo);
        $log->pan_code = $panCode;
        $log->package_name = $packageName;
        $log->remark = $remark;
        $log->is_auto = $isAuto;
        $log->bank_account = $account;
        if(!$log->save()){
            return false;
        }
        return true;
    }
}
<?php
namespace callcenter\models;

use callcenter\models\loan_collection\LoanCollectionOrder;
use common\models\order\UserLoanOrder;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * Class SmsTemplate
 * @package callcenter\models
 * @property int $id
 * @property int $merchant_id
 * @property string $name
 * @property string $package_name
 * @property string $content
 * @property int $is_use
 * @property string $can_send_outside
 * @property string $can_send_group
 * @property int $created_at
 * @property int $updated_at
 */
class SmsTemplate extends ActiveRecord
{
    const SMS_NOT_USE = 0;
    const SMS_IS_USE  = 1;

    public static $is_use_map = [
        self::SMS_NOT_USE => '不启用',
        self::SMS_IS_USE => '启用',
    ];


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%sms_template}}';
    }

    public static function getDb()
    {
        return Yii::$app->get('db_assist');
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

    //拼接前缀
    const SHOW_START = 's_';

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'content', 'is_use','can_send_group','can_send_outside','package_name'], 'required'],
            ['can_send_group',  'filter', 'filter' => function($value) {
                if(is_array($value)) {
                    $value = implode(',', $value);
                }
                return $value;
            }],
            ['can_send_outside',  'filter', 'filter' => function($value) {
                if(is_array($value)) {
                    $value = implode(',', $value);
                }
                return $value;
            }],
            [['merchant_id'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'merchant_id' => 'merchant id',
            'can_send_group' => 'can send group',
            'can_send_outside' => 'can send outside',
            'name' => '模板名',
            'package_name' => 'package name',
            'content' => '模板内容',
            'is_use' => '是否启用',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
        ];
    }

    public static function getTemplateList(LoanCollectionOrder $order,$merchantIds,$tempId = 0){
        $res = ['name' => [],'content' => []];
        if($order->loanOrder->is_export == UserLoanOrder::IS_EXPORT_YES){
            $packageName = explode('_',$order->loanOrder->clientInfoLog->app_market)[1];
        }else{
            $packageName = $order->loanOrder->clientInfoLog->package_name;
        }
        $query = static::find()->where(['is_use' => self::SMS_IS_USE,'merchant_id' => $merchantIds, 'package_name' => $packageName])
            ->andWhere(['like','can_send_outside',self::SHOW_START.$order->outside])
            ->andWhere(['like','can_send_group',self::SHOW_START.$order->current_overdue_level]);
        if($tempId > 0){
            $query->andWhere(['id' => $tempId]);
        }

        /** @var SmsTemplate $list */
        $list = $query->all();
        foreach ($list as $val){
            $res['name'][$val->id] = $val->name;
            $sendMessage = str_replace(['#username#','#scheduled_payment_money#','#should_repay_date#','#overdue_day#'],
                [$order->repaymentOrder->loanPerson->name, $order->repaymentOrder->getScheduledPaymentAmount()/100, date('d/m/Y',$order->repaymentOrder->plan_repayment_time),$order->repaymentOrder->overdue_day],$val->content); // 处理文案内容信息替换
            $res['content'][$val->id] = $sendMessage;
        }
        return $res;
    }
}
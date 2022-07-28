<?php

namespace callcenter\models\loan_collection;


use yii\db\ActiveRecord;
use callcenter\models\AdminUser;
use Yii;
use yii\behaviors\TimestampBehavior;


/**
 * This is the model class for table "{{%loan_collection_record_new}}".
 *
 * @property integer $id
 * @property integer $order_id
 * @property integer $operator
 * @property integer $contact_id
 * @property integer $contact_type
 * @property integer $order_level
 * @property integer $order_state
 * @property integer $operate_type 催收类型
 * @property string $content 催收内容
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer promise_repayment_time
 * @property int $merchant_id
 * @property integer $user_amount
 * @property string $user_utr
 * @property string $user_pic
 */
class LoanCollectionRecord extends ActiveRecord
{
    const CONTACT_TYPE_SELF = 0;
    const CONTACT_TYPE_URGENT = 1;
    const CONTACT_TYPE_ADDRESS_BOOK = 2;

    public static $label_contact_type = [
        self::CONTACT_TYPE_SELF => 'oneself',
        self::CONTACT_TYPE_URGENT => 'urgent',
        self::CONTACT_TYPE_ADDRESS_BOOK => 'address book',

    ];

    const OPERATE_TYPE_CALL = 1;
    const OPERATE_TYPE_SMS = 2;

    public static $label_operate_type = [
        self::OPERATE_TYPE_CALL => 'Telephone',
        self::OPERATE_TYPE_SMS => 'SMS'
    ];

    const RISK_CONTROL_PROMISED_PAYMENT = 1;
    const RISK_CONTROL_USER_PAYMENT = 5; //用户反映已还款
    const RISK_CONTROL_WANT_REPAYMENT = 2;
    const RISK_CONTROL_INSOLVENCY = 3;
    const RISK_CONTROL_REJECT_REPAYMENT = 4;
    const RISK_CONTROL_NO_ANSWER = 11;
    const RISK_CONTROL_SHUTDOWN_OR_NULL = 12;

    //接通的情况
    public static $risk_connect_success_control = [
        self::RISK_CONTROL_PROMISED_PAYMENT => 'promised payment',
        self::RISK_CONTROL_USER_PAYMENT => 'user repayment completed',
        self::RISK_CONTROL_WANT_REPAYMENT => 'want to repay',
        self::RISK_CONTROL_INSOLVENCY => 'insolvency',
        self::RISK_CONTROL_REJECT_REPAYMENT => 'reject repayment',
    ];

    //未接通的情况
    public static $risk_connect_fail_control = [
        self::RISK_CONTROL_NO_ANSWER =>'no answer',
        self::RISK_CONTROL_SHUTDOWN_OR_NULL =>'shutdown or null',
    ];

    public static $risk_controls = [
        self::RISK_CONTROL_PROMISED_PAYMENT => 'promised payment',
        self::RISK_CONTROL_USER_PAYMENT => 'user repayment completed',
        self::RISK_CONTROL_WANT_REPAYMENT => 'want to repay',
        self::RISK_CONTROL_INSOLVENCY => 'insolvency',
        self::RISK_CONTROL_REJECT_REPAYMENT => 'reject repayment',
        self::RISK_CONTROL_NO_ANSWER =>'no answer',
        self::RISK_CONTROL_SHUTDOWN_OR_NULL =>'shutdown or null',
    ];

    const IS_CONNECT_SUCCESS = 1;
    const IS_CONNECT_FAIL = 2;

    public static $is_connect = [
        self::IS_CONNECT_SUCCESS => 'has connect',
        self::IS_CONNECT_FAIL =>'connect fail',
    ];

    const PAGE_SIZE_15 = 15;
    const PAGE_SIZE_30 = 30;
    const PAGE_SIZE_50 = 50;
    const PAGE_SIZE_100 = 100;
    //催收记录页面显示条数
    public static $page_size = [
        self::PAGE_SIZE_15 =>15,
        self::PAGE_SIZE_30 =>30,
        self::PAGE_SIZE_50 =>50,
        self::PAGE_SIZE_100 =>100,
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%loan_collection_record}}';
    }


    /**
     * 根据催收ID，返回承诺还款时间
     * @param $collectionId
     * @return array
     */
    public static function promiseTimeCollectionOrderId($collectionId){
        $res = self::find()->select('promise_repayment_time')->where(['order_id'=>$collectionId, 'order_state'=>LoanCollectionOrder::STATUS_COLLECTION_PROMISE])->all(self::getDb_rd());
       return empty($res) ? array() : array_unique(array_column($res, 'promise_repayment_time'));
    }

    /**
     *根据催收ID，返回记录次数
     * @param array|int $collectionIds
     * @return array
     */
    public static function getCollectionRecordCount($collectionIds){
        $res = self::find()->select(['order_id','count' => 'COUNT(1)'])
            ->where(['order_id'=> $collectionIds])
            ->groupBy(['order_id'])
            ->asArray()
            ->all(self::getDb_rd());
        return array_column($res,'count','order_id');
    }

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
            [['order_id', 'operator', 'order_level', 'order_state', 'operate_type', 'operate_at', 'created_at', 'updated_at'], 'required'],
            [['promise_repayment_time','order_id', 'operator', 'contact_id', 'contact_type', 'order_level', 'order_state', 'operate_type', 'operate_at', 'created_at', 'updated_at'], 'integer'],
            [['content'], 'string', 'max' => 512]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'id'),
            'order_id' => Yii::t('app', '催收订单ID'),
            'operator' => Yii::t('app', '催收操作人ID'),
            'contact_id' => Yii::t('app', '联系人ID'),
            'contact_type' => Yii::t('app', '联系人类型 1: 紧急联系人 2:通讯录联系人'),
            'contact_name' => Yii::t('app', '联系人姓名'),
            'relation' => Yii::t('app', '联系人关系'),
            'contact_phone' => Yii::t('app', '联系人电话'),
            'order_level' => Yii::t('app', '当前催收等级'),
            'order_state' => Yii::t('app', '当前催收状态'),
            'operate_type' => Yii::t('app', '催收类型'),
            'operate_at' => Yii::t('app', '催收时间'),
            'content' => Yii::t('app', '催收内容'),
            'created_at' => Yii::t('app', '创建时间'),
            'updated_at' => Yii::t('app', '更新时间'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::class,
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => 'updated_at'
                ],
            ],
        ];
    }

    public function getLoanCollectionOrder()
    {
        return $this->hasOne(LoanCollectionOrder::class, ['id' => 'order_id']);
    }

    public function getOrder()
    {
        return $this->hasOne(LoanCollectionOrder::class, ['id' => 'order_id']);
    }

    public function getOperator()
    {
        return $this->hasOne(AdminUser::class, ['id' => 'operator']);
    }
}

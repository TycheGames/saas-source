<?php

namespace callcenter\models\loan_collection;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%loan_collection_status_change_log}}".
 *
 * @property integer $id
 * @property integer $merchant_id
 * @property integer $loan_collection_order_id
 * @property integer $before_status
 * @property integer $after_status
 * @property integer $type
 * @property integer $created_at
 * @property string $operator_name
 * @property string $remark
 */
class LoanCollectionStatusChangeLog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%loan_collection_status_change_log}}';
    }
    static $connect_name = "";

    public function __construct($name = "")
    {
        static::$connect_name = $name;
    }
    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get( !empty(static::$connect_name) ? static::$connect_name : 'db_assist');
        //return Yii::$app->get('db_assist');
    }
     public static function getDb_rd()
    {
        return Yii::$app->get('db_assist');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['loan_collection_order_id', 'before_status', 'after_status', 'type','merchant_id','created_at'], 'integer'],
            [['remark'], 'string'],
            [['operator_name'], 'string', 'max' => 50]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'loan_collection_order_id' => Yii::t('app', '催收订单ID'),
            'before_status' => Yii::t('app', '操作前状态'),
            'after_status' => Yii::t('app', '操作后状态'),
            'type' => Yii::t('app', '操作类型'),
            'created_at' => Yii::t('app', '创建时间'),
            'operator_name' => Yii::t('app', '操作人'),
            'remark' => Yii::t('app', '操作备注'),
        ];
    }

    /**
     * 根据给定的订单ID与反馈发布时间筛选出在发布时间前最近派单的催收人
     * @param  array $collection_user_feedback_list 反馈列表信息
     * @return array                                反馈列表信息、订单ID、催收人ID
     */
    public static function collectionAdminInfoByOrderIdAndCreateAt($collection_user_feedback_order_list,$feedback_create_at){
        foreach ($collection_user_feedback_order_list as $key => $value) {
            $condition = "1=1 AND type = ". LoanCollectionOrder::TYPE_DISPATCH_COLLECTION ." AND " . self::tableName() . ".loan_collection_order_id = " . intval($value['user_loan_order_id']) ." AND " . self::tableName() . ".created_at < " . intval($feedback_create_at);
            $data = self::find()->select(['remark', 'created_at'])
                ->where($condition)
                ->orderBy('id desc')
                ->limit(3)
                ->asArray()
                ->one(self::getDb_rd());
            if (!empty($data)) {
                $str = $data['remark'];
                $collection_admin_id = mb_substr($str, mb_strpos($str, '催收人ID：') + mb_strlen('催收人ID：'));
                $collection_user_feedback_order_list[$key]['admin_user_id'] = $collection_admin_id;
                $collection_user_feedback_order_list[$key]['created_at'] = $data['created_at'];
            }else{
                $collection_user_feedback_order_list[$key]['admin_user_id'] = '';
                $collection_user_feedback_order_list[$key]['created_at'] = '';
            }
        }
        return $collection_user_feedback_order_list;
    }

    /**
     *根据给定催单ID返回指定类型的订单状态转换记录，默认类型为派单类型
     *@param int $collectionId 催单ID
     *@param int $type 类型，默认为派单类型
     *@param int $limit 记录条数限制，默认不限制
     *@return array
     */
    public static function collection_id($collectionId, $type = LoanCollectionOrder::TYPE_DISPATCH_COLLECTION, $limit = 0){
        $query = self::find()->where(['loan_collection_order_id'=>$collectionId, 'type'=>$type]);
        if(!empty($limit))  $query->limit($limit);
        return $query->orderBy(['created_at'=>SORT_DESC])->asArray()->all();
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }
}

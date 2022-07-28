<?php

namespace callcenter\models\loan_collection;

use Yii;
use yii\data\Pagination;

/**
 * This is the model class for table "{{%tb_loan_collection_suggestion_change_log}}".
 *
 */

/**
 * Class LoanCollectionSuggestionChangeLog
 * @package callcenter\models\loan_collection
 * @property int $merchant_id
 */
class LoanCollectionSuggestionChangeLog extends \yii\db\ActiveRecord
{
    public $loan_admin_info;
    /**
     * @inheritdoc
     */
    static $connect_name = "";

    public function __construct($name = "")
    {
        static::$connect_name = $name;
    }
    public static function tableName()
    {
        return '{{%loan_collection_suggestion_change_log}}';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get( !empty(static::$connect_name) ? static::$connect_name : 'db_assist');
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
            [['order_id', 'collection_id', 'suggestion_before', 'suggestion', 'created_at'], 'integer'],
            [['remark'], 'string'],
            [['operator_name'], 'string', 'max' => 20]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'order_id' => Yii::t('app', '订单ID'),
            'collection_id' => Yii::t('app', '催收单ID'),
            'suggestion_before' => Yii::t('app', '之前建议'),
            'suggestion' => Yii::t('app', '当前建议'),
            'created_at' => Yii::t('app', '创建时间'),
            'operator_name' => Yii::t('app', '操作人'),
            'remark' => Yii::t('app', '操作备注'),
        ];
    }

    /**
    * @ 判断过滤信息返回建议列表与分页功能
    */
    public static function getLoanSuggestionList()
    {
        // 接收过滤信息
        $condition = '1 = 1';
        if(Yii::$app->request->get('search_submit')) {
            $search = Yii::$app->request->get();
            if(!empty($search['collection_id'])) {
                $condition .= " AND ".self::tableName().".collection_id = ".intval($search['collection_id']);
            }
            if(!empty($search['order_id'])) {
                $condition .= " AND ".self::tableName().".order_id = ".intval($search['order_id']);
            }
            if(isset($search['stage_type'])) {
                $condition .= " AND ".self::tableName().".stage_type = ".intval($search['stage_type']);
            }
            if($search['suggestion']!=='') {
                $condition .= " AND ".self::tableName().".suggestion = ".intval($search['suggestion']);
            }
            if ($search['outside']  !=='') {
                $condition .= " AND ".self::tableName().".outside = ".intval($search['outside']);
            }

        }
        return $condition;
    }
}

<?php
namespace common\models\risk;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;


/**
 * Class RiskResultSnapshot
 * @package common\models\risk
 * @property int order_id
 * @property int user_id
 * @property string app_name
 * @property string tree_code
 * @property string tree_version
 * @property string result_data
 * @property string base_node
 * @property string guard_node
 * @property string manual_node
 * @property string result
 * @property string txt
 * @property int created_at
 * @property int updated_at
 *
 */
class RiskResultSnapshot extends ActiveRecord {


    /**
     * @inheritdoc
     * @return string
     */
    public static function tableName(){
        return '{{%risk_result_snapshot}}';
    }

    /**
     * @return null|object|\yii\db\Connection
     * @throws \yii\base\InvalidConfigException
     */
    public static function getDb(){
        return Yii::$app->get('db');
    }

    /**
     * @inheritdoc
     * @return array
     */
    public function behaviors(){
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * 获取节点列表
     * @return array
     */
    public static function getTreeCodeList()
    {
        $treeCodes = RiskResultSnapshot::find()->select(['tree_code'])
            ->distinct(['tree_code'])->asArray()->all();
        if(!empty($treeCodes))
        {
            $treeCodes = ArrayHelper::getColumn($treeCodes,'tree_code');
        }else{
            $treeCodes = [];
        }

        return $treeCodes;

    }


    /**
     * 节点筛选列表
     * @return array
     */
    public static function getTreeCodeSearchList()
    {
        $cacheKey = 'tree_code_search_list';
        if(Yii::$app->cache->get($cacheKey))
        {
            return json_decode(Yii::$app->cache->get($cacheKey), true);
        }else{
            $treeCodeList = [];
            $treeCodes = self::getTreeCodeList();
            foreach($treeCodes as $v)
            {
                $treeCodeList[$v] = $v;
            }
            Yii::$app->cache->set($cacheKey, json_encode($treeCodeList, JSON_UNESCAPED_UNICODE), 300);
            return $treeCodeList;
        }
    }
}
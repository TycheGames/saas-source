<?php
namespace common\models\risk;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * 风控规则数据表
 * This is the model class for table "{{%risk_rules}}".
 * Class RiskRules
 * @package common\models
 * @property integer    $id 自增ID
 * @property integer    $type 类型
 * @property integer    $status 状态
 * @property string     $code 节点代号
 * @property integer    $order 排序号
 * @property string     $version 版本
 * @property string     $alias 别名
 * @property string     $guard 条件表达式
 * @property string     $result 结果
 * @property string     $params 默认参数
 * @property string     $description 节点描述
 * @property string     $created_at 创建时间
 * @property string     $updated_at 修改时间
 */

class RiskRules extends ActiveRecord {

    # 基础规则、哨兵表达式
    const TYPE_BASE = 1;
    const TYPE_GUARD = 2;

    public static $type_map = [
        self::TYPE_BASE => 'basic',
        self::TYPE_GUARD => 'expression'
    ];
    # 停用、测试、启用
    const STATUS_STOP = 0;
    const STATUS_TEST = 1;
    const STATUS_OK = 2;

    public static $status_map = [
        self::STATUS_STOP => 'Disable',
        self::STATUS_TEST => 'Test',
        self::STATUS_OK => 'Enable',
    ];

    /**
     * 初始化
     */
    public function init(){
        parent::init();
    }

    /**
     * @inheritdoc
     * @return string
     */
    public static function tableName(){
        return '{{%risk_rules}}';
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
     * 字段验证规则
     * @return array
     */
    public function rules(){
        return [
            [['order','id'],'integer'],
            ['status','in','range' => array_keys(self::$status_map)],
            ['type','in','range' => array_keys(self::$type_map)],
            [['code','alias','guard','result'],'required'],
            [['version','code','alias','guard','result'],'string'],
            [['params','description'],'safe'],
        ];
    }


    /**
     * 规则缓存
     */
    static private $rulesCache = null;

    /**
     * 规则较少时将数据缓存到内存[规则多时优化]
     */
    static protected function initCache($version){
        if( !empty(self::$rulesCache[$version]) ){
            return;
        }

        $caches = [];
        $rows = self::find()
            ->select(["id","type", "status", "code", "order", "guard", "result", "params", "alias"])
            ->where(["status" => [self::STATUS_OK, self::STATUS_TEST], 'version' => $version])
            ->orderBy(['order' => SORT_ASC])
            ->all();

        if( empty($rows) ){
            return;
        }

        /** @var self $rule */
        foreach ($rows as $rule){
            !isset($caches[$rule->code]) && ($caches[$rule->code] = []);
            $caches[$rule->code][] = $rule;
        }

        self::$rulesCache[$version] = $caches;
        return;
    }

    /**
     * 通过code获得节点
     * @param string $code  规则代码
     * @return null | array
     */
    static public function getNodesByCode(string $code, string $version){
        self::initCache($version);
        return !isset(self::$rulesCache[$version][$code]) ? null : self::$rulesCache[$version][$code];
    }

    public function getMappingNodes($rule)
    {
        $match = [];
        $match_2 = [];
        preg_match_all("/@([0-9a-zA-Z_]+)/",$rule->guard, $match);
        preg_match_all("/@([0-9a-zA-Z_]+)/",$rule->result, $match_2);
        $codes = array_unique(array_merge($match[1], $match_2[1]));
        return $codes;
    }

    public function generateTree( &$nodeDataArray, $parentId){
        if(self::TYPE_GUARD == $this->type)
        {
            $codes = self::getMappingNodes($this);
            foreach ($codes as $code) {
                $nodes = self::find()->where(['code' => $code, 'version' => $this->version])->orderBy(['order' => SORT_ASC])->all();
                $tmp = [
                    'id' => $code,
                    'parentid' => $parentId,
                    'expanded' => true,
                ];
                $topic = [];
                /**
                 * @var RiskRules $node
                 */
                foreach($nodes as $node)
                {
                    if(self::TYPE_BASE == $node->type)
                    {
                        $tmpTopic = "方法:{$node->code}-{$node->alias}<br/>";
                        $tmpTopic .= "&nbsp&nbsp&nbsp&nbsp方法名:check{$node->result}";
                    }else{
                        $tmpTopic = "特征:{$node->code}-{$node->alias}<br/>";
                        $tmpTopic .= "&nbsp&nbsp&nbsp&nbsp表达式:{$node->guard}";
                        $tmpTopic .= "&nbsp结果:{$node->result}";
                    }

                    $topic[] = $tmpTopic;

                    if($node->type == self::TYPE_GUARD){
                        $node->generateTree($nodeDataArray, $node->code);
                    }
                }
                $tmp['topic'] = implode('<br/>', $topic);

                $nodeDataArray[] = $tmp;


            }
        }
        if($this->type == self::TYPE_BASE && $this->code != $parentId){
            $nodeDataArray[] = [
                'id' => $this->code,
                'code' => $this->code,
                'parentid' => $parentId,
                'expanded' => false,
                'topic' => "基础方法:{$this->code}-{$this->alias}"
            ];
        }

    }

//    public function generateTree( &$nodeDataArray, $parentId){
//        if($this->type == self::TYPE_BASE){
//            if( $this->code != $parentId){
//                $nodeDataArray[] = [
//                    'id' => $this->code,
//                    'title' => $this->alias,
//                    'code' => $this->code,
//                    'parentid' => $parentId,
//                    'expanded' => false,
//                    'topic' => "基础方法:{$this->code}-{$this->alias}"
//                ];
//            }
//        }else{
//            $codes = self::getMappingNodes($this);
//            foreach ($codes as $code) {
//                $nodes = self::find()->where(['code' => $code])->all();
//                 $tmp = [
//                    'id' => $code,
//                    'parentid' => $parentId,
//                    'expanded' => false,
//                ];
//                $topic = [];
//                foreach($nodes as $node)
//                {
//                    $topic[] = "特征:{$node->code}-{$node->alias}({$node->guard})";
//
//                    if($node->type == self::TYPE_GUARD){
//                        $node->generateTree($nodeDataArray, $node->code);
//                    }
//                }
//                $tmp['topic'] = implode('<br/>', $topic);
//
//                $nodeDataArray[] = $tmp;
//
//
//            }
//        }
//
//    }

    public function expressionTransform($expression){

        $expression = str_replace(' ', '', $expression);

        $expression = preg_replace_callback(
            "/@[0-9]+/",
            function ($matches) {
                $matche = str_replace('@', '', $matches[0]);
                return '特征'.$matche;
            },
            $expression
        );

        return $expression;
    }

    public function addNodeByMapping($rule){

        $description = [];

        $child_ids = [];

        $rule_mapping = NewRuleExtendMap::getExtendRuleMapping($rule->id, $this->version ?? RuleVersion::getDefaultVersion());

        foreach ($rule_mapping as $key => $mapping) {
            $expression = self::expressionTransform($mapping->expression);
            $result = $mapping->result;
            $description[] = [
                'left'      => $expression,
                'middle'    => ' : ',
                'right'     => $result
            ];
            $child_ids = array_unique(array_merge($child_ids, self::getIdsFromExpression($mapping->expression)));
            $child_ids = array_unique(array_merge($child_ids, self::getIdsFromExpression($mapping->result)));
            $child_ids = array_unique(array_merge($child_ids, self::getIdsFromExpression($rule->result)));
        }

        return [
            'description' => $description,
            'child_ids' => $child_ids
        ];

    }

    public function getIdsFromExpression($expression){

        $matches = [];
        $expression = str_replace(' ', '', $expression);
        $expression = preg_match_all("/@[0-9]+/", $expression, $matches);

        $child_ids = [];

        foreach ($matches[0] as $key => $value) {
            $child_id = str_replace('@', '', $value);
            $child_ids[] = $child_id;
        }

        return $child_ids;
    }

    public static function copyVersionRules($version, $version_by) {
        $rules = self::find()->where(['version' => $version_by])->all();
        if (!$rules) {
            return false;
        }

        foreach ($rules as $rule) {
            $newRule = new RiskRules($rule);
            unset($newRule->id);
            $newRule->version = $version;
            if (!$newRule->save()) {
                return false;
            }
        }

        return true;
    }
}
<?php
namespace common\services\risk;

use common\exceptions\RiskException;
use common\models\risk\MgRiskTreeResult;
use common\models\risk\RiskResultSnapshot;
use common\models\risk\RiskResultSnapshotGray;
use common\models\risk\RiskRules;
use common\models\risk\RuleVersion;

/**
 * 风控决策树-Service
 * Class RiskTreeService
 * @package common\services
 */
class RiskTreeService {

    /**
     * 风控数据
     * @var RiskDataInterface|null
     */
    public $riskData = null;

    /**
     * 风控节点结果记录日志
     * @var array
     */
    public $nodeValueLog = [];

    /**
     * 风控manual节点结果记录日志
     * @var array
     */
    public $manualValueLog = [];

    /**
     * 风控节点结果缓存
     * @var array
     */
    public $nodeValueCaches = [];

    /**
     * 基础节点结果缓存
     * @var array
     */
    public $basicNodeValues = [];

    /**
     * 是否全完执行
     * @var bool
     */
    private $fullExecute = true;
    /**
     * 运算节点循环嵌套检测
     * @var bool
     */
    private $checkLoop = null;

    /**
     * 树节点循环嵌套检测时，用来存放计算中的节点
     * @var array|null
     */
    private $runningNodeCode = null;

    public $lastNode = [];

    private $rule_version;


    /**
     * RiskTreeService constructor.
     * @param RiskDataInterface|null $riskData
     * @param bool $checkLoop
     * @param bool $preCalcBasicNode
     * @param bool $fullExecute
     */
    public function __construct(RiskDataInterface $riskData = null, $checkLoop = false, $preCalcBasicNode = true){
        # init计算数据源
        !empty($riskData) && ($this->riskData = $riskData);
        # 检测运行中的节点，避免循环递归
        $this->checkLoop = !!$checkLoop;
        $this->checkLoop && ($this->runningNodeCode = []);
//        if($preCalcBasicNode){
//            $this->preCalcBasicNode();
//        }
    }

    public function setRuleVersion($rule_version) {
        $this->rule_version = $rule_version;
    }

    /**
     * 计算节点结果
     * @param string $nodeCode
     * @param bool $isTest  是否运行测试节点
     * @return mixed|null
     */
    public function exploreNodeValue(string $nodeCode, bool $isTest = false){

        # 检测树节点循环
        if( $this->checkLoop ){
            if( isset($this->runningNodeCode[$nodeCode]) ){
                RiskException::throwError(RiskException::CODE_ERR_RULE_LOOP, "决策树<{$nodeCode}>存在循环");
            }
            # 记录节点计算中状态 - 以便于检测
            $this->runningNodeCode[$nodeCode] = true;
        }

        if (!$this->rule_version) {
            $this->setRuleVersion(RuleVersion::getDefaultVersion());
        }

        $this->lastNode[] = $nodeCode;
        # 计算结果
        $result = null;
        $isFirstResult = true;
        $firstResult = null;
        if( isset($this->nodeValueCaches[$nodeCode]) ){
            # 使用缓存值
            $firstResult = $this->nodeValueCaches[$nodeCode];
        } else {
            # 首次计算
            $rules = RiskRules::getNodesByCode($nodeCode, $this->rule_version);
            if( empty($rules) ){
                RiskException::throwError(RiskException::CODE_ERR_RULE_NIL, "规则<{$nodeCode}>不存在");
            }

            /** @var RiskRules $rule */
            foreach ( $rules as $rule ){
                if( $rule->status === RiskRules::STATUS_OK || ($isTest && $rule->status === RiskRules::STATUS_TEST) ){
                    if( $this->calcExpValue($rule->guard) ){
                        if(RiskRules::TYPE_BASE === $rule->type)
                        {
                            $result = $this->riskData->calcBaseRuleValue($rule);
                            $this->basicNodeValues[$rule->result] = $result;
                            $this->nodeValueLog['base_node'][$rule->code] = $result;
                        }else{
                            $result = $this->calcExpValue($rule->result);
                            if(isset($result['result']) && $result['result'] === 'manual' && !in_array($result, $this->manualValueLog[$result['ManualModule']] ?? [])){
                                $this->manualValueLog[$result['ManualModule']][] = $result;
                            }
                        }
                        if($isFirstResult){
                            if($rule->type === RiskRules::TYPE_GUARD){
                                $this->nodeValueLog['guard_node'][$rule->code] = $result;
                                if(isset($result['result']) && ($result['result'] === 'manual' || $result['result'] === 'reject')){
                                    $this->riskData->isGetData = false;
                                }
                            }
                            $firstResult = $result;
                            $isFirstResult = false;
                        }
                        if(!$this->fullExecute){
                            break;
                        }
                    }
                }
            }

            if( null === $firstResult ){
                # 风控节点不提供默认值，故要求哨兵表达式必须完全匹配
                # 避免意外的节点结果作为决策条件，出现意外的结果
                RiskException::throwError(RiskException::CODE_ERR_RULE_UNMATCH, "规则<{$nodeCode}>未匹配到条件");
            }

            $this->nodeValueCaches[$nodeCode] = $firstResult;
        }


        if( $this->checkLoop ){
            # 释放节点计算中状态
            unset($this->runningNodeCode[$nodeCode]);
        }

        return $firstResult;
    }

    /**
     * 计算表达式的值
     * 和 exploreNodeValue 存在相互调用[小心递归带来的循环]
     * @param string $expression
     * @return mixed
     */
    public function calcExpValue(string $expression){
        # 过滤表达式
        $expression = preg_replace("/\s+/", "", $expression);
        # 解析表达式
        $transExpression = preg_replace_callback("/@[0-9a-zA-Z_]+/", function ($matches){
                $match = str_replace("@", "", $matches[0]);
                return sprintf("\$this->exploreNodeValue(\"%s\")", $match);
        }, $expression);
        # 计算表达式
        $result = eval("return {$transExpression};");
        return $result;
    }


    /**
     * 预计算基础节点
     */
    private function preCalcBasicNode()
    {
        $rules = RiskRules::find()
            ->select(["code",  "result", "params"])
            ->where([
                "status" => [
                    RiskRules::STATUS_OK,
//                    RiskRules::STATUS_TEST
                ],
                "type" => RiskRules::TYPE_BASE
            ])
            ->orderBy(['order' => SORT_ASC])
            ->all();

        if( empty($rules) ){
            return;
        }
        /**
         * @var RiskRules $rule
         */
        foreach ($rules as $rule)
        {
            $this->nodeValueCaches[$rule->code] = $this->riskData->calcBaseRuleValue($rule);
            $this->basicNodeValues[] = [
                'code' => $rule->code,
                'name' => $rule->result,
                'result' => $this->nodeValueCaches[$rule->code]
            ];
        }
        return;
    }


    /**
     * 插入风控数据快照
     * @param int $orderId
     * @param array $resultData
     */
    public function insertRiskResultSnapshot(int $orderId, int $user_id, array $resultData)
    {
        $riskResult = new MgRiskTreeResult();
        $riskResult->order_id = $orderId;
        $riskResult->user_id = $user_id;
        $riskResult->base_node = $this->nodeValueLog['base_node'];
        $riskResult->guard_node = $this->nodeValueLog['guard_node'];
        $riskResult->manual_node = $this->manualValueLog;
        $riskResult->result = $resultData;

        if (!$riskResult->save()) {
            RiskException::throwError(RiskException::CODE_ERR_SAVE, "RiskResultSnapshot save fail");
        }
    }

    public function insertRiskResultSnapshotToDb(int $orderId, int $user_id, $treeCode,  $resultData, $version = '')
    {
        $riskResult = new RiskResultSnapshot();
        $riskResult->order_id = $orderId;
        $riskResult->user_id = $user_id;
        $riskResult->tree_code = $treeCode;
        $riskResult->tree_version = $this->rule_version;
        $riskResult->result_data = json_encode($resultData);
        $riskResult->txt = $resultData['txt'] ?? '';;
        $riskResult->base_node = json_encode($this->nodeValueLog['base_node']);
        $riskResult->guard_node = json_encode($this->nodeValueLog['guard_node']);
        $riskResult->manual_node = json_encode($this->manualValueLog);
        $riskResult->result = $resultData['result'] ?? '';


        if (!$riskResult->save()) {
            RiskException::throwError(RiskException::CODE_ERR_SAVE, "RiskResultSnapshotToDb save fail");
        }
    }

    public function insertRiskResultSnapshotGrayToDb(int $orderId, int $user_id, $treeCode,  $resultData)
    {
        $riskResult = new RiskResultSnapshotGray();
        $riskResult->order_id = $orderId;
        $riskResult->user_id = $user_id;
        $riskResult->tree_code = $treeCode;
        $riskResult->tree_version = $this->rule_version;
        $riskResult->result_data = json_encode($resultData);
        $riskResult->txt = $resultData['txt'] ?? '';;
        $riskResult->base_node = json_encode($this->nodeValueLog['base_node']);
        $riskResult->guard_node = json_encode($this->nodeValueLog['guard_node']);
        $riskResult->manual_node = json_encode($this->manualValueLog);
        $riskResult->result = $resultData['result'] ?? '';


        if (!$riskResult->save()) {
            RiskException::throwError(RiskException::CODE_ERR_SAVE, "RiskResultSnapshotGrayToDb save fail");
        }
    }

    public static function validateExpression()
    {

    }

}
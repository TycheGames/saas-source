<?php
namespace common\services\risk;

use common\exceptions\RiskException;
use common\models\risk\RiskRules;

class RiskDataService implements RiskDataInterface {

    /**
     * 计算风控基础规则值
     * @param RiskRules $rule
     * @return mixed
     */
    public function calcBaseRuleValue(RiskRules $rule){
        $baseFnName = "check" . $rule->result;
        return $this->{$baseFnName}($rule->params);
    }


    /**
     * 风控函数默认
     * @param string $name
     * @param array $arguments
     */
    public function __call(string $name, array $arguments){
        RiskException::throwError(RiskException::CODE_ERR_RULE_FN_NIL, "基础函数<{$name}>不存在");
    }

    /**
     * 当前日期
     * @param string $params
     * @return false|string
     */
    public function checkDateToday(string $params = ''){
        return date("Y-m-d", time());
    }

}
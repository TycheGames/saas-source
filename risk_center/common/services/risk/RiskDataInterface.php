<?php
namespace common\services\risk;

use common\models\risk\RiskRules;

/**
 * 风控数据接口规范
 * Interface RiskDataInterface
 * @package common\interfaces
 */
interface RiskDataInterface {

    /**
     * 计算基础规则结果
     * @param RiskRules $rule
     * @return mixed
     */
    public function calcBaseRuleValue(RiskRules $rule);


}
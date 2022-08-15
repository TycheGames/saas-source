<?php
namespace common\services\risk;


use common\models\risk\RiskRules;

/**
 * RiskData 验证类 只是用来验证管理后台配置的规则是否有效
 * Class RiskDataVerificationService
 * @package common\services
 */
class RiskDataVerificationService extends RiskDataService
{
    private $targetClass;

    public function __construct()
    {
        $this->targetClass = new \ReflectionClass(RiskDataDemoService::class);
    }

    /**
     * 计算风控基础规则值
     * @param RiskRules $rule
     * @return mixed
     */
    public function calcBaseRuleValue(RiskRules $rule){
        $baseFnName = "check" . $rule->result;
        $this->targetClass->getMethod($baseFnName);
        return 1;
    }

}
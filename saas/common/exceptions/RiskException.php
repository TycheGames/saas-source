<?php
namespace common\exceptions;

use yii\base\Exception;

/**
 * 风控模块异常
 * Class RiskException
 * @package common\exception
 */
class RiskException extends Exception {

    const CODE_ERR_RULE_NIL = 8101;
    const CODE_ERR_RULE_FN_NIL = 8102;
    const CODE_ERR_RULE_LOOP = 8103;
    const CODE_ERR_RULE_UNMATCH = 8104;

    const CODE_OK = 0;
    const CODE_ERR_SYS = 1001;
    const CODE_ERR_UNCODE = 1002;
    const CODE_ERR_SAVE = 1003;
    const CODE_ERR_ORDER_NIL = 1004;
    const CODE_ERR_ORDER_LOCKED = 1005;
    const CODE_ERR_PARAM_FMT = 1006;

    /**
     * code映射
     * @return array
     */
    static public function getMaps(){
        return [
            self::CODE_OK           => "OK",
            self::CODE_ERR_SYS      => "系统错误",
            self::CODE_ERR_UNCODE   => "错误码不存在",
            self::CODE_ERR_SAVE     => "数据保存失败",
            self::CODE_ERR_ORDER_NIL    => "订单不存在",
            self::CODE_ERR_ORDER_LOCKED => "订单锁定中",
            self::CODE_ERR_PARAM_FMT    => "参数格式错误",
            self::CODE_ERR_RULE_NIL         => "风控规则为空",
            self::CODE_ERR_RULE_FN_NIL      => "风控函数不存在",
            self::CODE_ERR_RULE_LOOP        => "决策树循环",
            self::CODE_ERR_RULE_UNMATCH     => "风控节点未匹配到条件",
        ];
    }


    /**
     * 获取错误信息
     * @param $code
     * @return mixed|null
     */
    static public function getCodeMessage($code){
        $maps = static::getMaps();
        return isset($maps[$code]) ? $maps[$code] : null;
    }

    /**
     * 抛出异常
     * @param int $code
     * @param string $message
     * @throws BaseException
     */
    static public function throwError(int $code, string $message = ""){
        throw new static(empty($message) ? static::getCodeMessage($code) : $message, $code);
    }

    /**
     * 获取类名
     * @return string
     */
    public function getName() {
        return __CLASS__;
    }

}
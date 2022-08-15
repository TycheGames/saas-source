<?php

namespace common\services\risk;


use common\models\InfoUser;
use common\models\RiskOrder;
use common\services\BaseService;

/**
 * Class BaseRiskDataService
 * @package common\services\risk
 * @property InfoUser $infoUser
 * @property RiskOrder $order
 * @property bool $canSkip 未获取到数据时，是否可以跳过，继续获取后面的数据
 * @property bool $isRequire 数据是否必须，必须时超过重试次数就会驳回订单
 * @property bool $needValidate 获取数据后是否需要立即验证，如验证失败则驳回订单
 * @property int $retryLimit 最大重试次数
 */
abstract class BaseDataService extends BaseService
{

    public $infoUser, $order;
    public $canSkip = false;
    public $isRequire = true;
    public $needValidate = false;

    public $retryLimit = 10;

    public function __construct($config = [])
    {
        parent::__construct($config);
    }

    /**
     * 数据获取
     * @return bool
     */
    abstract public function getData(): bool;

    /**
     * 是否可以重试
     * @return bool
     */
    abstract public function canRetry(): bool;

    /**
     * 验证数据是否有效
     * @return bool
     */
    abstract public function validateData(): bool;

}

<?php
namespace common\services\pay;

use common\services\BaseService;
use yii\base\Model;


/**
 * Class BasePayService
 * @package common\services\pay
 *
 */
class BasePayService extends BaseService
{

    private $accountId;


    /**
     * @return Model
     */
    public static function formModel()
    {
        return new Model();
    }

}
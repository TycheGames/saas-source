<?php
namespace common\services\pay;

use common\models\pay\PayoutAccountSetting;
use common\services\BaseService;


class PayoutService extends BaseService
{

    private $modelList; //可用配置列表
    private static $instanceList = []; //已初始化的实例列表

    private $group;
    private $weightSum; //总权重值，初始化实例后赋值
    private $merchantID;



    public function __construct($group, $merchantID, $config = [])
    {
        parent::__construct($config);
        $this->group = $group;
        $this->merchantID = $merchantID;
        $this->getServiceList();
        $this->getWeightSum();
    }


    /**
     * 通过组获取实例
     * @param string|int $group
     * @param int $merchantID
     * @return PayoutService
     */
    public static function getInstanceByGroup($group, $merchantID)
    {
        if(!isset(self::$instanceList[$group]))
        {
            $instance = new self($group, $merchantID);
            self::$instanceList["{$group}_{$merchantID}"] = $instance;
        }

        return  self::$instanceList["{$group}_{$merchantID}"];
    }


    /**
     * 获取模型
     * @return PayoutAccountSetting
     */
    public function getModel()
    {
        //随机权重
        $count = 0;
        $weightRandomNum = mt_rand(1, $this->weightSum);
        /** @var PayoutAccountSetting $model */
        foreach ($this->modelList as $model)
        {
            $oldCount = $count;
            $count += $model->weight;
            //如果随机数在区间内，则返回记录
            if($weightRandomNum > $oldCount && $weightRandomNum <= $count)
            {
                return $model;
            }
        }

        return null;
    }


    /**
     * @return RazorpayPayoutService|MpursePayoutService|CashFreePayoutService|PaytmPayoutService
     */
    public function getService()
    {
        $model = $this->getModel();
        $class = $model->payoutAccountInfo->getService();
        return new $class($model->payoutAccountInfo);
    }

    /**
     * 获取总权重
     * @return int
     */
    public function getWeightSum()
    {
        if(is_null($this->weightSum))
        {
            $this->weightSum = 0;
            /** @var PayoutAccountSetting $setting */
            foreach ($this->modelList as $setting)
            {
                $this->weightSum += $setting->weight;
            }
        }
        return $this->weightSum;
    }


    /**
     * 获取可用配置列表列表
     * @param $group
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getServiceList()
    {
        //查找状态为启用且权重大于0的数据
        if(is_null($this->modelList))
        {
            $this->modelList = PayoutAccountSetting::find()
                ->where(['group' => $this->group, 'status' => PayoutAccountSetting::STATUS_ENABLE])
                ->andWhere(['>', 'weight', 0])
                ->all();
        }
        return $this->modelList;
    }




}
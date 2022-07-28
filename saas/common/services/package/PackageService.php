<?php

namespace common\services\package;

use common\models\package\PackageSetting;
use common\services\BaseService;

/**
 * Class PackageService
 * @package common\services\package
 *
 * @property PackageSetting $packageSetting
 * @property string $packageName
 */
class PackageService extends BaseService
{
    private $packageSetting;
    private $packageName;

    public function __construct($packageName, $config = [])
    {
        $this->packageName = $packageName;
        parent::__construct($config);
    }


    /**
     * 获取package_setting实例
     * @return PackageSetting|null
     */
    public function getPackageSetting()
    {
        if(is_null($this->packageSetting))
        {
            $this->packageSetting = PackageSetting::findOne(['package_name' => $this->packageName]);
        }
        return $this->packageSetting;
    }

    /**
     * 获取source id
     * @return int
     */
    public function getSourceId()
    {
        return $this->getPackageSetting()->source_id;
    }

    /**
     * 获取merchant id
     * @return int
     */
    public function getMerchantId()
    {
        return $this->getPackageSetting()->merchant_id;
    }

    /**
     * 获取名字
     * @return string
     */
    public function getName()
    {
        return $this->getPackageSetting()->name;
    }

    /**
     * 获取谷歌推送token
     * @return float
     */
    public function getFirebaseToken()
    {
        return $this->getPackageSetting()->firebase_token;
    }
}

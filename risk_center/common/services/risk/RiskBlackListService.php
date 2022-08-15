<?php
namespace common\services\risk;

use common\models\risk\RiskBlackListAadhaar;
use common\models\risk\RiskBlackListDeviceid;
use common\models\risk\RiskBlackListPan;
use common\models\risk\RiskBlackListPhone;
use common\models\risk\RiskBlackListSzlm;
use common\services\BaseService;
use yii\helpers\ArrayHelper;
use Yii;

/**
 * 风控黑名单服务
 * Class RiskBlackListService
 * @package common\services
 */
class RiskBlackListService extends BaseService
{

    /**
     * 检查pan卡是否命中黑名单
     * @param array $pan
     * @return bool  true 命中  false 未命中
     */
    public function checkHitByPan(array $pan)
    {
        $check = RiskBlackListPan::find()->select(['id'])
            ->where(['value' => $pan])->one();
        if($check){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 检查身份证是否命中黑名单
     * @param array $aadhaars
     * @return bool  true 命中  false 未命中
     */
    public function checkHitByAadhaar(array $aadhaars)
    {
        $check = RiskBlackListAadhaar::find()->select(['id'])
            ->where(['value' => $aadhaars])->one();
        if($check){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 检查手机号码是否命中黑名单
     * @param array $phones
     * @return bool  true 命中  false 未命中
     */
    public function checkHitByPhones(array $phones)
    {
        $check = RiskBlackListPhone::find()->select(['id'])
            ->where(['value' => $phones])->one();
        if($check){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 检查设备id是否命中黑名单
     * @param array $deviceIds
     * @return bool  true 命中  false 未命中
     */
    public function checkHitByDeviceIds(array $deviceIds)
    {
        $check = RiskBlackListDeviceid::find()->select(['id'])
            ->where(['value' => $deviceIds])->one();
        if($check){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 检查数盟id是否命中黑名单
     * @param array $deviceIds
     * @return bool  true 命中  false 未命中
     */
    public function checkHitBySMDeviceIds(array $deviceIds){
        $check = RiskBlackListSzlm::find()->select(['id'])
            ->where(['value' => $deviceIds])->one();
        if($check){
            return true;
        }else{
            return false;
        }
    }
}

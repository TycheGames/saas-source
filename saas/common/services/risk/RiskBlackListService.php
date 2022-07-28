<?php
namespace common\services\risk;

use common\models\ClientInfoLog;
use common\models\risk\RiskBlackListAadhaar;
use common\models\risk\RiskBlackListContact;
use common\models\risk\RiskBlackListDeviceid;
use common\models\risk\RiskBlackListPan;
use common\models\risk\RiskBlackListPhone;
use common\models\risk\RiskBlackListSzlm;
use common\models\user\LoanPerson;
use common\models\user\UserContact;
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
     * 将用户加入黑名单
     * @param LoanPerson $loanPerson
     * @param int $source
     * @param int $operatorId
     * @throws \Exception
     */
    public function addListByLoanPerson(LoanPerson $loanPerson, int $source, int $operatorId)
    {
        $deviceIds = $this->getDeviceIdsByUserId($loanPerson->id);
        $this->addListByDeviceIds($deviceIds, $loanPerson->id, $source, $operatorId, $loanPerson->merchant_id);
        $szlmIds = $this->getSzlmIdsByUserId($loanPerson->id);
        $this->addListBySzlmIds($szlmIds, $loanPerson->id, $source, $operatorId, $loanPerson->merchant_id);
        if(!$this->addListByPhone($loanPerson->phone, $loanPerson->id, $source, $operatorId, $loanPerson->merchant_id)){
            throw new \Exception('保存黑名单失败');
        }
        if(!empty($loanPerson->aadhaar_md5) && !$this->addListByAadhaar($loanPerson->aadhaar_md5, $loanPerson->id, $source, $operatorId, $loanPerson->merchant_id)){
            throw new \Exception('保存黑名单失败');
        }
        if(!empty($loanPerson->pan_code) && !$this->addListByPan($loanPerson->pan_code, $loanPerson->id, $source, $operatorId, $loanPerson->merchant_id)){
            throw new \Exception('保存黑名单失败');
        }

        $data = [
            'device_ids'  => $deviceIds,
            'szlm_ids'    => $szlmIds,
            'phone'       => $loanPerson->phone,
            'aadhaar_md5' => $loanPerson->aadhaar_md5,
            'pan_code'    => $loanPerson->pan_code,
        ];

        return $data;
//        $contactPhones = $this->getContactPhonesByUserId($loanPerson->id);
//        $this->addListByContactPhones($contactPhones, $loanPerson->id, $source, $operatorId, $loanPerson->merchant_id);
    }

    /**
     * 取消黑名单
     * @param $user_id
     * @throws \Exception
     */
    public function delListByLoanPerson($user_id, $merchantId)
    {
        RiskBlackListDeviceid::deleteAll(['user_id' => $user_id, 'merchant_id' => $merchantId]);
        RiskBlackListSzlm::deleteAll(['user_id' => $user_id, 'merchant_id' => $merchantId]);
        RiskBlackListPhone::deleteAll(['user_id' => $user_id, 'merchant_id' => $merchantId]);
        RiskBlackListAadhaar::deleteAll(['user_id' => $user_id, 'merchant_id' => $merchantId]);
        RiskBlackListPan::deleteAll(['user_id' => $user_id, 'merchant_id' => $merchantId]);
    }

    /**
     * 多条设备号加入黑名单
     * @param array $deviceIds
     * @param int $userId
     * @param int $source
     * @param int $operatorId
     * @throws \Exception
     */
    public function addListByDeviceIds(array $deviceIds, int $userId, int $source, int $operatorId, int $merchantId)
    {
        foreach($deviceIds as $deviceId)
        {
            if(empty($deviceId)){
                continue;
            }
            if(!$this->addListByDeviceId($deviceId, $userId, $source, $operatorId, $merchantId)){
                throw new \Exception('保存黑名单失败');
            }
        }
    }

    /**
     * 多条数盟id加入黑名单
     * @param array $szlmIds
     * @param int $userId
     * @param int $source
     * @param int $operatorId
     * @throws \Exception
     */
    public function addListBySzlmIds(array $szlmIds, int $userId, int $source, int $operatorId, int $merchantId)
    {
        foreach($szlmIds as $szlmId)
        {
            if(empty($szlmId)){
                continue;
            }
            if(!$this->addListBySzlmId($szlmId, $userId, $source, $operatorId, $merchantId)){
                throw new \Exception('保存黑名单失败');
            }
        }
    }

    /**
     * 设备号加入黑名单
     * @param string $value
     * @param int $userId
     * @param int $source
     * @param int $operatorId
     * @return bool
     */
    public function addListByDeviceId(string $value, int $userId, int $source, int  $operatorId, int $merchantId)
    {
        $check = RiskBlackListDeviceid::find()->select(['id'])
            ->where(['value' => $value, 'merchant_id' => $merchantId])->one();
        if(is_null($check)){
            $list = new RiskBlackListDeviceid();
            $list->user_id = $userId;
            $list->merchant_id = $merchantId;
            $list->value = $value;
            $list->source = $source;
            $list->operator_id = $operatorId;
            return $list->save();
        }else{
            return true;
        }
    }

    /**
     * 数盟id加入黑名单
     * @param string $value
     * @param int $userId
     * @param int $source
     * @param int $operatorId
     * @return bool
     */
    public function addListBySzlmId(string $value, int $userId, int $source, int  $operatorId, int $merchantId)
    {
        $check = RiskBlackListSzlm::find()->select(['id'])
            ->where(['value' => $value, 'merchant_id' => $merchantId])->one();
        if(is_null($check)){
            $list = new RiskBlackListSzlm();
            $list->user_id = $userId;
            $list->merchant_id = $merchantId;
            $list->value = $value;
            $list->source = $source;
            $list->operator_id = $operatorId;
            return $list->save();
        }else{
            return true;
        }
    }

    /**
     * 将手机号加入黑名单
     * @param string $value
     * @param int $userId
     * @param int $source
     * @param int $operatorId
     * @return bool
     */
    public function addListByPhone(string $value, int $userId, int $source, int  $operatorId, int $merchantId)
    {
        $check = RiskBlackListPhone::find()->select(['id'])
            ->where(['value' => $value, 'merchant_id' => $merchantId])->one();
        if(is_null($check)){
            $list = new RiskBlackListPhone();
            $list->user_id = $userId;
            $list->merchant_id = $merchantId;
            $list->value = $value;
            $list->source = $source;
            $list->operator_id = $operatorId;
            return $list->save();
        }else{
            return true;
        }
    }


    /**
     * 将身份证加入黑名单
     * @param string $value
     * @param int $userId
     * @param int $source
     * @param int $operatorId
     * @return bool
     */
    public function addListByAadhaar(string $value, int $userId, int $source, int  $operatorId, int $merchantId)
    {
        $check = RiskBlackListAadhaar::find()->select(['id'])
            ->where(['value' => $value, 'merchant_id' => $merchantId])->one();
        if(is_null($check)){
            $list = new RiskBlackListAadhaar();
            $list->user_id = $userId;
            $list->merchant_id = $merchantId;
            $list->value = $value;
            $list->source = $source;
            $list->operator_id = $operatorId;
            return $list->save();
        }else{
            return true;
        }
    }

    /**
     * 将pan卡加入黑名单
     * @param string $value
     * @param int $userId
     * @param int $source
     * @param int $operatorId
     * @return bool
     */
    public function addListByPan(string $value, int $userId, int $source, int  $operatorId, int $merchantId)
    {
        $check = RiskBlackListPan::find()->select(['id'])
            ->where(['value' => $value, 'merchant_id' => $merchantId])->one();
        if(is_null($check)){
            $list = new RiskBlackListPan();
            $list->user_id = $userId;
            $list->merchant_id = $merchantId;
            $list->value = $value;
            $list->source = $source;
            $list->operator_id = $operatorId;
            return $list->save();
        }else{
            return true;
        }
    }

    /**
     * 添加第一二联系人手机号进入黑名单
     * @param $phones
     * @param int $userId
     * @param int $source
     * @param int $operatorId
     */
    public function addListByContactPhones(array $phones, int $userId, int $source, int  $operatorId, int $merchantId)
    {
        foreach($phones as $phone)
        {
            $this->addListByContactPhone($phone, $userId, $source, $operatorId, $merchantId);
        }
    }

    /**
     * 添加紧急联系人进入黑名单
     * @param string $value
     * @param int $userId
     * @param int $source
     * @param int $operatorId
     * @return bool
     */
    public function addListByContactPhone(string $value, int $userId, int $source, int  $operatorId, int $merchantId)
    {
        $check = RiskBlackListContact::find()->select(['id'])
            ->where(['value' => $value, 'merchant_id' => $merchantId])->one();
        if(is_null($check)){
            $list = new RiskBlackListContact();
            $list->user_id = $userId;
            $list->merchant_id = $merchantId;
            $list->value = $value;
            $list->source = $source;
            $list->operator_id = $operatorId;
            return $list->save();
        }else{
            return true;
        }
    }


    /**
     * 检查紧急联系人是否命中黑名单
     * @param array $phones
     * @return bool  true 命中  false 未命中
     */
    public function checkHitByContactPhones(array $phones, int $merchantId)
    {
        $check = RiskBlackListContact::find()->select(['id'])
            ->where(['value' => $phones])->one();
        if($check){
            return true;
        }else{
            $check = RiskBlackListContact::find()->select(['id'])
                ->where(['value' => $phones])->one(Yii::$app->db_loan);
            if($check){
                return true;
            }
            return false;
        }
    }

    /**
     * 检查pan卡是否命中黑名单
     * @param array $pan
     * @return bool  true 命中  false 未命中
     */
    public function checkHitByPan(array $pan, int $merchantId)
    {
        $check = RiskBlackListPan::find()->select(['id'])
            ->where(['value' => $pan])->one();
        if($check){
            return true;
        }else{
            $check = RiskBlackListPan::find()->select(['id'])
                ->where(['value' => $pan])->one(Yii::$app->db_loan);
            if($check){
                return true;
            }
            return false;
        }
    }

    /**
     * 检查身份证是否命中黑名单
     * @param array $aadhaars
     * @return bool  true 命中  false 未命中
     */
    public function checkHitByAadhaar(array $aadhaars, int $merchantId)
    {
        $check = RiskBlackListAadhaar::find()->select(['id'])
            ->where(['value' => $aadhaars])->one();
        if($check){
            return true;
        }else{
            $check = RiskBlackListAadhaar::find()->select(['id'])
                ->where(['value' => $aadhaars])->one(Yii::$app->db_loan);
            if($check){
                return true;
            }
            return false;
        }
    }

    /**
     * 检查手机号码是否命中黑名单
     * @param array $phones
     * @return bool  true 命中  false 未命中
     */
    public function checkHitByPhones(array $phones, int $merchantId)
    {
        $check = RiskBlackListPhone::find()->select(['id'])
            ->where(['value' => $phones])->one();
        if($check){
            return true;
        }else{
            $check = RiskBlackListPhone::find()->select(['id'])
                ->where(['value' => $phones])->one(Yii::$app->db_loan);
            if($check){
                return true;
            }
            return false;
        }
    }

    /**
     * 检查设备id是否命中黑名单
     * @param array $deviceIds
     * @return bool  true 命中  false 未命中
     */
    public function checkHitByDeviceIds(array $deviceIds, int $merchantId)
    {
        $check = RiskBlackListDeviceid::find()->select(['id'])
            ->where(['value' => $deviceIds])->one();
        if($check){
            return true;
        }else{
            $check = RiskBlackListDeviceid::find()->select(['id'])
                ->where(['value' => $deviceIds])->one(Yii::$app->db_loan);
            if($check){
                return true;
            }
            return false;
        }
    }

    /**
     * 检查数盟id是否命中黑名单
     * @param array $deviceIds
     * @return bool  true 命中  false 未命中
     */
    public function checkHitBySMDeviceIds(array $deviceIds, int $merchantId){
        $check = RiskBlackListSzlm::find()->select(['id'])
            ->where(['value' => $deviceIds])->one();
        if($check){
            return true;
        }else{
            $check = RiskBlackListSzlm::find()->select(['id'])
                ->where(['value' => $deviceIds])->one(Yii::$app->db_loan);
            if($check){
                return true;
            }
            return false;
        }
    }

    /**
     * 通过用户ID获取设备号
     * @param $userId
     * @return array
     */
    public function getDeviceIdsByUserId($userId) : array
    {
        $devices = array_unique(
            ArrayHelper::getColumn(
                ClientInfoLog::find()
                    ->select(['device_id'])
                    ->where(['user_id' => $userId, 'event' => ClientInfoLog::EVENT_LOGIN])
                    ->asArray()->all(),
            'device_id'
        ));
        return $devices;
    }

    /**
     * 通过用户ID获取数盟id
     * @param $userId
     * @return array
     */
    public function getSzlmIdsByUserId($userId) : array
    {
        $devices = array_unique(
            ArrayHelper::getColumn(
                ClientInfoLog::find()
                    ->select(['szlm_query_id'])
                    ->where(['user_id' => $userId, 'event' => ClientInfoLog::EVENT_LOGIN])
                    ->asArray()->all(),
                'szlm_query_id'
            ));
        return $devices;
    }

    /**
     * 通过用户ID获取通讯录手机号
     * @param $userId
     * @return array
     */
    public function getContactPhonesByUserId($userId) : array
    {
        /**
         * @var UserContact $contact
         */
        $contact = UserContact::find()
            ->select(['phone', 'other_phone'])
            ->where(['user_id' => $userId])
            ->orderBy(['id' => SORT_DESC])->all();
        return [$contact->phone, $contact->other_phone];
    }
}

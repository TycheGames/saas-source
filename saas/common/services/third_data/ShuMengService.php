<?php

namespace common\services\third_data;

use common\models\package\PackageSetting;
use common\models\third_data\ThirdDataShumeng;
use common\services\risk\BaseDataService;
use GuzzleHttp\Client;
use function GuzzleHttp\Psr7\build_query;
use yii\db\Exception;

/**
 * 数盟服务
 * Class ShuMengService
 * @package common\services\risk
 * @property ThirdDataShumeng $thirdDataShumeng
 * @property  string $url
 */
class ShuMengService extends BaseDataService
{
    private $url = 'https://ddi.shuzilm.cn/q';

    private $thirdDataShumeng;

    public function query($did, $pkg, $ver = null)
    {
        $client = new Client();
        $params = [
            'protocol' => 2,
            'did' => $did,
            'pkg' => $pkg,
        ];
        if(!is_null($ver))
        {
            $params['ver'] = $ver;
        }
        $params = build_query($params);
        $query = $this->url . '?' . $params;
        $response = $client->get($query);
        $result = 200 == $response->getStatusCode() ? $response->getBody()->getContents() : new \stdClass();
        return json_decode($result , true);
    }


    /**
     * @return bool
     * @throws Exception
     */
    public function getData(): bool
    {
        $this->initData();
        if(!is_null($this->thirdDataShumeng) && ThirdDataShumeng::STATUS_SUCCESS == $this->thirdDataShumeng->status)
        {
            return true;
        }
        $clientInfo = $this->order->clientInfoLog;
        if(empty($clientInfo))
        {
            return true;
        }
        $did = $clientInfo->szlm_query_id;
        if(empty($did)){
            return true;
        }
        if(!$this->canRetry()){
            return true;
        }
        $pkg = $clientInfo->bundle_id;

        $this->thirdDataShumeng->user_id = $this->order->user_id;
        $this->thirdDataShumeng->merchant_id = $this->loanPerson->merchant_id;
        $this->thirdDataShumeng->order_id = $this->order->id;
        $this->thirdDataShumeng->device_id = $did;
        $this->thirdDataShumeng->retry_limit = $this->thirdDataShumeng->retry_limit + 1;
        try {
            $result = $this->query($did, $pkg);
        } catch (\Exception $e) {
            $this->thirdDataShumeng->save();
            return false;
        }
        if(isset($result['err']) && in_array($result['err'],[0,-3,-5] ))
        {
            $status = ThirdDataShumeng::STATUS_SUCCESS;
            $return = true;
        }else{
            $status= ThirdDataShumeng::STATUS_FAILED;
            $return = false;
        }
        $this->thirdDataShumeng->status = $status;
        $this->thirdDataShumeng->err = $result['err'] ?? 0;
        $this->thirdDataShumeng->device_type = $result['device_type'] ?? 0;
        $this->thirdDataShumeng->report = json_encode($result, JSON_UNESCAPED_UNICODE);
        if(!$this->thirdDataShumeng->save())
        {
            throw new Exception('thirdDataShumeng保存失败');
        }
        return $return;
    }

    private function initData()
    {
        if(is_null($this->thirdDataShumeng))
        {
            $this->thirdDataShumeng = ThirdDataShumeng::find()
                ->where([
                    'user_id' => $this->order->user_id,
                    'order_id' => $this->order->id
                ])->one();
            if(is_null($this->thirdDataShumeng)){
                $this->thirdDataShumeng = new ThirdDataShumeng();
            }
        }
    }

    public function canRetry() : bool
    {
        $this->initData();
        if(!isset($this->thirdDataShumeng->retry_limit)){
            return true;
        }
        return $this->thirdDataShumeng->retry_limit < $this->retryLimit;
    }


    public function validateData() : bool
    {
        return true;
    }
}

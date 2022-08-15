<?php

namespace common\services\third_data;

use common\models\third_data\ThirdDataGoogleMaps;
use common\services\risk\BaseDataService;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use yii\db\Exception;
use Yii;

class GoogleMapsService extends BaseDataService
{
    private $baseUrl;
    private $geoUrl;
    private $apiKey;

    private $thirdDataGoogleMaps;

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->baseUrl = Yii::$app->params['googleMap']['base_url'];
        $this->geoUrl = Yii::$app->params['googleMap']['geo_url'];
        $this->apiKey = Yii::$app->params['googleMap']['api_key'];
    }

    public function query(string $uri, string $latlng)
    {
        $client = new Client([
            'base_uri'              => $this->baseUrl,
            RequestOptions::TIMEOUT => 60,
        ]);

        $body[RequestOptions::HTTP_ERRORS] = false; //禁止http_errors 4xx 和 5xx
        $body[RequestOptions::QUERY] = [
            'latlng'      => $latlng,
            'language'    => 'en',
            'result_type' => 'administrative_area_level_2',
            'key'         => $this->apiKey,
        ];

        return $client->request('GET', $uri, $body);
    }


    /**
     * @return bool
     * @throws Exception
     */
    public function getData(): bool
    {
        $this->initData();
        if(!is_null($this->thirdDataGoogleMaps) && ThirdDataGoogleMaps::STATUS_SUCCESS == $this->thirdDataGoogleMaps->status)
        {
            return true;
        }
        $clientInfo = $this->order->infoDevice;
        if(empty($clientInfo))
        {
            return true;
        }
        $lat = $clientInfo->latitude;
        $lng = $clientInfo->longitude;
        if(empty($lat) || empty($lng)){
            return true;
        }
        if(!$this->canRetry()){
            return true;
        }

        $this->thirdDataGoogleMaps->app_name = $this->order->app_name;
        $this->thirdDataGoogleMaps->user_id = $this->order->user_id;
        $this->thirdDataGoogleMaps->order_id = $this->order->order_id;
        $this->thirdDataGoogleMaps->lat = $lat;
        $this->thirdDataGoogleMaps->lng = $lng;
        $this->thirdDataGoogleMaps->retry_limit = $this->thirdDataGoogleMaps->retry_limit + 1;
        try {
            $latlng = sprintf('%s,%s', $lat, $lng);
            $geoData = json_decode($this->query($this->geoUrl, $latlng)->getBody()->getContents(), true);
        } catch (\Exception $e) {
            $this->thirdDataGoogleMaps->save();
            return false;
        }

        $result = [];
        $addressComponents = $geoData['results'][0]['address_components'] ?? [];
        foreach ($addressComponents as $addressComponent) {
            if (in_array('administrative_area_level_2', $addressComponent['types'])) {
                $result['district'] = $addressComponent['long_name'];
            } elseif (in_array('administrative_area_level_1', $addressComponent['types'])) {
                $result['state'] = $addressComponent['long_name'];
            } elseif (in_array('country', $addressComponent['types'])) {
                $result['country'] = $addressComponent['long_name'];
            }
        }

        $this->thirdDataGoogleMaps->status = thirdDataGoogleMaps::STATUS_SUCCESS;
        $this->thirdDataGoogleMaps->district = $result['district'] ?? '';
        $this->thirdDataGoogleMaps->state = $result['state'] ?? '';
        $this->thirdDataGoogleMaps->country = $result['country'] ?? '';
        $this->thirdDataGoogleMaps->data = json_encode($geoData, JSON_UNESCAPED_UNICODE);
        if(!$this->thirdDataGoogleMaps->save())
        {
            throw new Exception('thirdDataGoogleMaps保存失败');
        }
        return true;
    }

    private function initData()
    {
        if(is_null($this->thirdDataGoogleMaps))
        {
            $this->thirdDataGoogleMaps = ThirdDataGoogleMaps::find()
                ->where([
                    'app_name' => $this->order->app_name,
                    'user_id' => $this->order->user_id,
                    'order_id' => $this->order->order_id
                ])->one();
            if(is_null($this->thirdDataGoogleMaps)){
                $this->thirdDataGoogleMaps = new ThirdDataGoogleMaps();
            }
        }
    }

    public function canRetry() : bool
    {
        $this->initData();
        if(!isset($this->thirdDataGoogleMaps->retry_limit)){
            return true;
        }
        return $this->thirdDataGoogleMaps->retry_limit < $this->retryLimit;
    }


    public function validateData() : bool
    {
        return true;
    }
}

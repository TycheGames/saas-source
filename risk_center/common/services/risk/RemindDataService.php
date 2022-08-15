<?php

namespace common\services\risk;

use common\services\BaseService;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Yii;
use yii\db\Exception;


class RemindDataService extends BaseService
{

    private $base_uri       = 'https://api-remind.smallflyelephantsaas.com/';
    private $getRiskDataUri = 'risk/get-risk-data';


    public function __construct(array $config = [])
    {
        parent::__construct($config);
    }

    /**
     * 查询数据
     * @param $params
     * @return array|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getRiskData($params)
    {
        $result = $this->postData($this->getRiskDataUri, $params);
        return $result;
    }

    /**
     * @param string $uri
     * @param array $params
     * @return array|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function postData(string $uri, array $params)
    {
        $client = new Client([
            'base_uri'              => $this->base_uri,
            RequestOptions::TIMEOUT => 60,
        ]);

        $response = $client->request('POST', $uri, [
            RequestOptions::FORM_PARAMS => $params
        ]);

        $result = 200 == $response->getStatusCode() ? json_decode($response->getBody()->getContents(), true) : [];
        return $result;
    }


}

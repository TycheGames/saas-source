<?php


namespace common\services\order;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use yii\base\BaseObject;
use Yii;

class PushOrderRiskService extends BaseObject
{
    private $baseUrl = '';
    private $token = '';
    private $riskUri = 'notify/order-risk-notify';

    public function __construct($source, $config = [])
    {
        parent::__construct($config);

        $this->baseUrl = Yii::$app->params['ExportRisk'][$source]['base_url'];
        $this->token = Yii::$app->params['ExportRisk'][$source]['token'];
    }

    /**
     * @param int $orderId
     * @param array $data
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function pushOrderRisk(int $orderId, array $data)
    {
        $params = [
            'order_id' => $orderId,
            'token'    => $this->token,
            'data'     => json_encode($data)
        ];

        return $this->postData($this->riskUri, $params);
    }

    /**
     * @param string $uri
     * @param array $params
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function postData(string $uri, array $params)
    {
        $client = new Client([
                'base_uri'              => $this->baseUrl,
                RequestOptions::TIMEOUT => 60,
            ]
        );
        $body[RequestOptions::HTTP_ERRORS] = false; //禁止http_errors 4xx 和 5xx
        $body[RequestOptions::FORM_PARAMS] = $params;
        $response = $client->request('POST', $uri, $body);

        return json_decode($response->getBody()->getContents(), true);
    }
}
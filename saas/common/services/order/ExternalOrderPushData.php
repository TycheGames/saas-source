<?php


namespace common\services\order;


use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Yii;
use yii\base\BaseObject;

class ExternalOrderPushData extends BaseObject
{
    private $baseUrl = '';
    private $token = '';
    private $canLoanTimeUri = 'notify/saas-can-loan-time';

    public function __construct($config = [])
    {
        parent::__construct($config);

        $this->baseUrl = Yii::$app->params['loan']['base_url'];
        $this->token = Yii::$app->params['loan']['token'];
    }

    public function pushCanLoanTime(array $params)
    {
        return $this->postData($this->canLoanTimeUri, $params);
    }

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
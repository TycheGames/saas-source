<?php


namespace common\services\user;

use common\services\BaseService;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Yii;

class AadhaarApiService  extends BaseService
{
    private $baseUrl = '';
    private $token = '';
    private $aadhaarVerification = 'api/v1/aadhaar-validation/aadhaar-validation';
    private $bankVerification = 'api/v1/bank-verification/';

    public $userSourceId;
    public $apiId;

    public function __construct($config = [])
    {
        parent::__construct($config);

        $this->baseUrl = Yii::$app->params['AadhaarApi'][$this->userSourceId]['base_url'] ?? Yii::$app->params['AadhaarApi']['common']['base_url'];
        $this->token = Yii::$app->params['AadhaarApi'][$this->userSourceId]['token'] ?? Yii::$app->params['AadhaarApi']['common']['token'];
        $this->apiId = Yii::$app->params['AadhaarApi'][$this->userSourceId]['account'] ?? Yii::$app->params['AadhaarApi']['common']['account'];
    }

    public function checkAadhaar($aadhaar)
    {
        return $this->postData($this->aadhaarVerification, [
            'id_number' => $aadhaar,
        ]);
    }

    public function getBankInfo($account, $ifsc)
    {
        $requestTime = time();
        $res = $this->postData($this->bankVerification, [
            'id_number' => $account,
            'ifsc'      => $ifsc,
        ]);
        $responseTime = time();
        $response = $res['response'];
        $responseBody = $response->getBody()->getContents();
        $responseHttpCode = $response->getStatusCode();
        $requestBody = $res['request']['body'];

        $log = [
            'uri'    => $this->bankVerification,
            'params' => $requestBody,
            'result' => $responseBody,
            'time'   => $responseTime - $requestTime,
        ];
        Yii::info($log, 'AadhaarApiReport');

        $reportData = json_decode($responseBody, true);
        $accountExists = $reportData['data']['account_exists'] ?? false;
        $isSuccess = $reportData['success'] ?? false;
        if (200 == $responseHttpCode && $isSuccess && $accountExists) {
            return $reportData;
        } elseif(422 == $responseHttpCode) {
            $this->setError('Please enter the correct bank account and IFSC code.');
            return ['success' =>false];
        } else {
            Yii::error($log, 'AadhaarApiReport');
            throw new \Exception('Oops failed! Please try again later.');
        }
    }

    private function postData(string $uri, array $params)
    {
        $client = new Client([
                'base_uri'              => $this->baseUrl,
                RequestOptions::TIMEOUT => 60,
            ]
        );
        $body[RequestOptions::HTTP_ERRORS] = false; //禁止http_errors 4xx 和 5xx
        $body[RequestOptions::JSON] = $params;
        $body[RequestOptions::HEADERS] = [
            'Authorization' => 'Bearer ' . $this->token,
            'Accept'        => 'application/json',
        ];
        $response = $client->request('POST', $uri, $body);

        $result = [
            'request' => [
                'body' => $params,
                'head' => $body[RequestOptions::HEADERS],
            ],
            'response' => $response,
        ];

        return $result;
    }
}
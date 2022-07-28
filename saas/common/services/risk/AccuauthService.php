<?php


namespace common\services\risk;

use common\helpers\RedisQueue;
use common\models\enum\validation_rule\ValidationServiceProvider;
use common\models\enum\validation_rule\ValidationServiceType;
use common\models\third_data\ValidationRule;
use common\models\user\LoanPerson;
use common\services\BaseService;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\RequestOptions;
use Yii;

/**
 * 接口地址：http://devcenter.cloud.accuauth.com/ocr/ocr_indian_card.html
 * Class AccuauthOcrService
 * @package common\services\risk
 */
class AccuauthService extends BaseService
{
    private $baseUrl;
    private $ocrUri = 'ocr/indian_card';
    private $faceUri = 'face/v2/verify';
    private $panUri = 'verify/indian_pan';
    private $panLiteUri = 'verify/indian_pan_lite';
    private $hackUri = 'face/liveness_anti_hack';
    private $aadhaarMaskUri = 'ocr/indian_card_with_mask';
    public $apiId;
    private $apiSecret;

    public $userSourceId;

    public function __construct($config = [])
    {
        parent::__construct($config);

        $this->baseUrl = Yii::$app->params['Accuauth'][$this->userSourceId]['base_url'] ?? Yii::$app->params['Accuauth']['common']['base_url'];
        $this->apiId = Yii::$app->params['Accuauth'][$this->userSourceId]['api_id'] ?? Yii::$app->params['Accuauth']['common']['api_id'];
        $this->apiSecret = Yii::$app->params['Accuauth'][$this->userSourceId]['api_secret'] ?? Yii::$app->params['Accuauth']['common']['api_secret'];
    }


    /**
     * @param string $base64Image1
     * @param string $base64Image2
     * @return array|mixed
     * @throws ClientException
     */
    public function faceVerify(string $base64Image1,string $base64Image2)
    {
        try {
            $result = $this->postData($this->faceUri, [
                'image_base64_1' => $base64Image1,
                'image_base64_2' => $base64Image2,
            ]);
        } catch (ClientException $clientException) {
            if($clientException->getResponse()->getStatusCode() == 400) {
                $result = json_decode($clientException->getResponse()->getBody()->getContents(), true);
            } else {
                throw $clientException;
            }
        }
        return $result;
    }

    /**
     * @param string $livenessDataFilePath
     * @return array|mixed
     * @throws ClientException
     */
    public function faceHack(string $livenessDataFilePath)
    {
        try {
            $result = $this->postData($this->hackUri, [
                [
                    'name'     => 'liveness_data_file',
                    'contents' => fopen($livenessDataFilePath, 'r'),
                ],
            ], true);
        } catch (ClientException $clientException) {
            if($clientException->getResponse()->getStatusCode() == 400) {
                $result = json_decode($clientException->getResponse()->getBody()->getContents(), true);
            } else {
                throw $clientException;
            }
        }
        return $result;
    }

    public function panCardCheckAuto(string $panCode)
    {
        $currentService = RedisQueue::get(['key' => RedisQueue::LIST_VALIDATION_PAN_SERVICE]);
        switch ($currentService) {
            case ValidationServiceProvider::VERIFY_PAN_ACCUAUTH()->getKey():
                $result = $this->panCardCheck($panCode);
                break;
            case ValidationServiceProvider::VERIFY_PAN_ACCUAUTH_LITE()->getKey():
                //数据更新有延迟
                $result = $this->panCardCheckLite($panCode);
                break;
            default:
                //默认使用数据源
                RedisQueue::set([
                    'expire' => 86400,
                    'key'    => RedisQueue::LIST_VALIDATION_PAN_SERVICE,
                    'value'  => ValidationServiceProvider::VERIFY_PAN_ACCUAUTH()->getKey(),
                ]);
                $result = $this->panCardCheck($panCode);
                break;
        }

        return $result;
    }

    public function recordPanError(ValidationServiceProvider $provider)
    {
        $keySuffix = date('Hi');
        switch ($provider->getKey()) {
            case ValidationServiceProvider::VERIFY_PAN_ACCUAUTH()->getKey():
                $keyPrefix = RedisQueue::LIST_VALIDATION_PAN_ACCUAUTH_NORMAL;
                break;
            case ValidationServiceProvider::VERIFY_PAN_ACCUAUTH_LITE()->getKey():
                $keyPrefix = RedisQueue::LIST_VALIDATION_PAN_ACCUAUTH_LITE;
                break;
            default:
                $keyPrefix = RedisQueue::LIST_VALIDATION_PAN_ACCUAUTH_NORMAL;
        }
        $keyName = $keyPrefix . '_' .  $keySuffix;
        RedisQueue::inc([$keyName, 1]);
        RedisQueue::expire([$keyName, 86400]);

        /**
         * @var ValidationRule $rule
         */
        $rule = ValidationRule::find()
            ->where(['service_current' => $provider->getValue()])
            ->andWhere(['validation_type' => ValidationServiceType::VERIFY_PAN()->getValue()])
            ->andWhere(['is_used' => ValidationRule::IS_USED])
            ->orderBy(['id' =>SORT_DESC])
            ->limit(1)
            ->one();
        $errorCount = 0;
        for($i = 1; $i <= $rule->service_time; $i++) {
            $strTime = "-{$i} minute";
            $checkKeySuffix = date('Hi',strtotime($strTime));
            $checkKeyName = $keyPrefix . '_' .  $checkKeySuffix;
            $errorCount += RedisQueue::get(['key' => $checkKeyName]);
        }


        if ($errorCount >= $rule->service_error) {
            //清空近期的数据
            for ($i = 1; $i <= $rule->service_time; $i++) {
                $strTime = "-{$i} minute";
                $checkKeySuffix = date('Hi', strtotime($strTime));
                $checkKeyName = $keyPrefix . '_' . $checkKeySuffix;
                $errorCount += RedisQueue::set([
                    'expire' => 86400,
                    'key'    => $checkKeyName,
                    'value'  => 0,
                ]);
            }
            RedisQueue::set([
                'expire' => 86400,
                'key'    => RedisQueue::LIST_VALIDATION_PAN_SERVICE,
                'value'  => ValidationServiceProvider::search($rule->service_switch),
            ]);
        }
    }

    /**
     * @param string $panCode
     * @return array|mixed
     * @throws ClientException
     */
    public function panCardCheck(string $panCode)
    {
        try {
            $result = $this->postData($this->panUri, [
                'pan' => strtoupper($panCode)
            ]);
        } catch (ClientException $clientException) {
            if($clientException->getResponse()->getStatusCode() == 400) {
                $result = json_decode($clientException->getResponse()->getBody()->getContents(), true);
            } else {
                $this->recordPanError(ValidationServiceProvider::VERIFY_PAN_ACCUAUTH());
                throw $clientException;
            }
        } catch (RequestException $exception) {
            $this->recordPanError(ValidationServiceProvider::VERIFY_PAN_ACCUAUTH());
            throw $exception;
        }
        return $result;
    }

    public function panCardCheckLite(string $panCode)
    {
        try {
            $result = $this->postData($this->panLiteUri, [
                'pan' => strtoupper($panCode)
            ]);
        } catch (ClientException $clientException) {
            if($clientException->getResponse()->getStatusCode() == 400) {
                $result = json_decode($clientException->getResponse()->getBody()->getContents(), true);
            } else {
                $this->recordPanError(ValidationServiceProvider::VERIFY_PAN_ACCUAUTH_LITE());
                throw $clientException;
            }
        } catch (RequestException $exception) {
            $this->recordPanError(ValidationServiceProvider::VERIFY_PAN_ACCUAUTH_LITE());
            throw $exception;
        }

        //重组数据，兼容报告
        $newResult = $result;
        $reportUserName = $newResult['result']['name'] ?? '';
        $splitName = LoanPerson::getNameConversion($reportUserName);
        $newResult['result']['last_name'] = $splitName['last_name'];
        $newResult['result']['first_name'] = $splitName['first_name'];
        $newResult['result']['middle_name'] = $splitName['middle_name'];

        return $newResult;
    }

    /**
     * @param string $url
     * @return array|mixed
     * @throws ClientException
     */
    public function aadhaarMask(string $url)
    {
        try {
            $result = $this->postData($this->aadhaarMaskUri, [
                'url' => $url
            ]);
        } catch (ClientException $clientException) {
            if($clientException->getResponse()->getStatusCode() == 400) {
                $result = json_decode($clientException->getResponse()->getBody()->getContents(), true);
            } else {
                throw $clientException;
            }
        }
        return $result;
    }

    /**
     * @param string $base64Image
     * @return array|mixed
     * @throws ClientException
     */
    public function panCardOcr(string $base64Image)
    {
        try {
            $result = $this->ocrQuery('pan_card', $base64Image);
        } catch (ClientException $clientException) {
            if($clientException->getResponse()->getStatusCode() == 400) {
                $result = json_decode($clientException->getResponse()->getBody()->getContents(), true);
            } else {
                throw $clientException;
            }
        }
        return $result;
    }

    /**
     * @param string $base64Image
     * @return array|mixed
     * @throws ClientException
     */
    public function aadhaarCardOcr(string $base64Image)
    {
        try {
            $result = $this->ocrQuery('aadhaar_card', $base64Image);
        } catch (ClientException $clientException) {
            if($clientException->getResponse()->getStatusCode() == 400) {
                $result = json_decode($clientException->getResponse()->getBody()->getContents(), true);
            } else {
                throw $clientException;
            }
        }
        return $result;
    }

    private function ocrQuery(string $type, string $base64Image)
    {
        return $this->postData($this->ocrUri, [
            'card_type'    => $type,
            'image_base64' => $base64Image,
        ]);
    }

    private function postData(string $uri, array $params, bool $isMultiPart = false)
    {
        $client = new Client([
                'base_uri'              => $this->baseUrl,
                RequestOptions::TIMEOUT => 30,
                RequestOptions::HEADERS => [
                    'X-DF-API-ID'     => $this->apiId,
                    'X-DF-API-SECRET' => $this->apiSecret,
                ],
            ]
        );
        $body = $isMultiPart ? [
            RequestOptions::MULTIPART => $params,
        ] : [
            RequestOptions::FORM_PARAMS => $params,
        ];
        $response = $client->request('POST', $uri, $body);

        $result = 200 == $response->getStatusCode() ? json_decode($response->getBody()->getContents(), true) : [];
        Yii::info(['uri' => $uri, 'params' => [], 'result' => $result], 'AccuauthReport');

        return $result;
    }
}
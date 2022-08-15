<?php


namespace common\services\message;


use common\helpers\RedisQueue;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Yii;

class DingDingService
{
    private $dingKey = 'SEC967a9cc721d29862bc5c9e30c2a03e12ef736187b46d8c422427956b6e8c1fab';
    private $dingToken = '0f6afd267a340b71750e3718cb19b3791762b65366ab97cb11f03b310137bb0f';
    private $baseUrl = 'https://oapi.dingtalk.com/';

    private $userIDMap = [
        'songhuan'  => '2339202714755434',
        'zhengyuqing' => '242840286636852910',
        'yanzhenlin'  => '01005942525738605620',
        'xionghuakun' => '061751604428631910',
        'zhufangqi'   => '01091215143126223746',
        'zhouchunlu'  => '276449282721605747',
        'lushan'      => '1200270261685391',
        'wangpeng'    => '0108132620943791',
        'wangcheng'   => '0140212536942053',
        'zhongyue'    => '1736541664650241',
        'meiyunfei'   => '022718385426376114',
        'tanghaojie'  => '196469664927402487',
        'xuyingtao'   => '103353273124749198',
        'LiGuanHui'   => '221154334226178239',
    ];

    private $alarmGroup = [
        'wangpeng',
        'wangcheng',
        'zhongyue',
        'meiyunfei',
        'tanghaojie',
        'xuyingtao',
        'LiGuanHui',
    ];

    public function getToken($currentTime)
    {
        $signData = $currentTime . "\n" . $this->dingKey;
        return urlencode(base64_encode(hash_hmac('sha256', $signData, $this->dingKey, true)));
    }

    public function sendText(array $to, string $text)
    {
        if (YII_ENV_PROD) {
            RedisQueue::push([
                RedisQueue::DING_DING_ALERT_LIST_RISK_CENTER, json_encode([
                    'to'   => $to,
                    'text' => $text,
                ])
            ], 'redis_alert');
        } else {
            $workMsgUserID = [];
            foreach ($to as $user) {
                if (!in_array($user, $this->alarmGroup) && array_key_exists($user, $this->userIDMap)) {
                    array_push($workMsgUserID, $this->userIDMap[$user]);
                }
            }
            if (!empty($workMsgUserID)) {
                Yii::$app->dingtalk->sendTextMsg($workMsgUserID, '', $text);
            }

            $currentTime = time() * 1000;
            $sign = $this->getToken($currentTime);
            $client = new Client([
                    'base_uri'              => $this->baseUrl,
                    RequestOptions::TIMEOUT => 30,
                ]
            );
            $body[RequestOptions::HTTP_ERRORS] = false; //禁止http_errors 4xx 和 5xx
            $body[RequestOptions::JSON] = [
                'msgtype' => 'text',
                'text'    => [
                    'content' => $text,
                ],
            ];
            $body[RequestOptions::QUERY] = [
                'access_token' => $this->dingToken,
                'timestamp'    => $currentTime,
                'sign'         => $sign,
            ];

            return $client->request('POST', '/robot/send', $body);
        }
    }
}
<?php


namespace common\services\message;


use common\helpers\RedisQueue;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Yii;

class DingDingService
{

    private $baseUrl = 'https://oapi.dingtalk.com/';

    private $userIDMap = [
        'a'  => '0213213213213',
        'b' => '03213213123213',
        'c'   => '023123213213',

    ];
    private $alarmGroup = [
        'a',
        'b',
        'c',
    ];


    private $alterGroupMap = [
        'default' => [
            'key' => 'xxx',
            'token' => 'xxx'
        ],

        'business' => [
            'key' => 'xxx',
            'token' => 'xxx'
        ]
    ];


    public function getToken($currentTime, $key)
    {
        $signData = $currentTime . "\n" . $key;
        return urlencode(base64_encode(hash_hmac('sha256', $signData, $key, true)));
    }

    public function sendText(array $to, string $text)
    {
//        if (YII_ENV_PROD) {
//            RedisQueue::push([
//                RedisQueue::DING_DING_ALERT_LIST_SAAS, json_encode([
//                    'to'   => $to,
//                    'text' => $text,
//                ])
//            ], 'redis_alert');
//        } else {
//            $workMsgUserID = [];
//            foreach ($to as $user) {
//                if (!in_array($user, $this->alarmGroup) && array_key_exists($user, $this->userIDMap)) {
//                    array_push($workMsgUserID, $this->userIDMap[$user]);
//                }
//            }
//            if (!empty($workMsgUserID)) {
//                Yii::$app->dingtalk->sendTextMsg($workMsgUserID, '', $text);
//            }
//            $currentTime = time() * 1000;
//            $sign = $this->getToken($currentTime, $this->alterGroupMap['default']['key']);
//            $client = new Client([
//                    'base_uri'              => $this->baseUrl,
//                    RequestOptions::TIMEOUT => 30,
//                ]
//            );
//            $body[RequestOptions::HTTP_ERRORS] = false; //禁止http_errors 4xx 和 5xx
//            $body[RequestOptions::JSON] = [
//                'msgtype' => 'text',
//                'text'    => [
//                    'content' => $text,
//                ],
//            ];
//            $body[RequestOptions::QUERY] = [
//                'access_token' => $this->alterGroupMap['default']['token'],
//                'timestamp'    => $currentTime,
//                'sign'         => $sign,
//            ];
//
//            return $client->request('POST', '/robot/send', $body);
//        }
    }


    public function sendToGroup(string $text, $group = 'default')
    {
        if (YII_ENV_PROD) {
            RedisQueue::push([
                RedisQueue::DING_DING_ALERT_LIST_BUSINESS, json_encode([
                    'to'   => ['test'],
                    'text' => $text,
                ])
            ], 'redis_alert');
        } else {
            if (!isset($this->alterGroupMap[$group])) {
                $group = 'default';
            }
            $config = $this->alterGroupMap[$group];
            $key = $config['key'];
            $token = $config['token'];

            $currentTime = time() * 1000;
            $sign = $this->getToken($currentTime, $key);
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
                'access_token' => $token,
                'timestamp'    => $currentTime,
                'sign'         => $sign,
            ];

            return $client->request('POST', '/robot/send', $body);
        }
    }
}
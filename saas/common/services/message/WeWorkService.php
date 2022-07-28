<?php

namespace common\services\message;

use common\helpers\RedisQueue;
use common\services\BaseService;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use yii;

class WeWorkService extends BaseService
{
    private $agentId,$corpId,$secret,$token;
    private $expire = 6000;

    private $alarmGroup = [
        'wangpeng',
        'wangcheng',
        'zhongyue',
        'meiyunfei',
        'tanghaojie',
        'xuyingtao',
        'LiGuanHui'
    ];

    public function __construct($config = [])
    {
        parent::__construct($config);

//        $this->agentId = Yii::$app->params['WeWork']['agent_id'];
//        $this->corpId = Yii::$app->params['WeWork']['corp_id'];
//        $this->secret = Yii::$app->params['WeWork']['secret'];
//
//        $this->token = $this->getToken();

    }


    public function getToken()
    {
        if(! $token = $this->getTokenCache())
        {
            $url = "https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid={$this->corpId}&corpsecret={$this->secret}";
            $client = new Client([
                RequestOptions::TIMEOUT => 5
            ]);

            $response = $client->get($url);
            $result = 200 == $response->getStatusCode() ? $response->getBody()->getContents() : new \stdClass();
            if($result)
            {
                $result = json_decode($result, true);
                if(isset($result['errcode']) && 0 == $result['errcode'])
                {
                    $token = $result['access_token'];
                    $this->setTokenCache($token);
                }
            }


        }
        return $token;

    }

    private function getTokenCache()
    {
        $key = RedisQueue::MESSAGE_WEWORK_TOKEN_CACHE;
        return RedisQueue::get(['key' => $key]);
    }

    private function setTokenCache($token)
    {
        $key = RedisQueue::MESSAGE_WEWORK_TOKEN_CACHE;
        RedisQueue::set(['key' => $key, 'value' => $token, 'expire' => $this->expire]);

    }


    /**
     * @param array $to
     * @param $text
     * @return bool|\stdClass|string
     */
    public function sendText(array $to, $text)
    {
        $dingdingService = new DingDingService();
        $dingdingService->sendText($to, '['.YII_ENV.']'.$text);

//        if(empty($this->token)){
//            return false;
//        }
//        $url = "https://qyapi.weixin.qq.com/cgi-bin/message/send?access_token={$this->token}";
//        $client = new Client([
//            RequestOptions::TIMEOUT => 5
//        ]);
//
//        try{
//            $response = $client->request('POST',$url,
//                [
//                    RequestOptions::JSON => [
//                        'touser' => implode('|', $to),
//                        'msgtype' => 'text',
//                        'agentid' => $this->agentId,
//                        'text' => [
//                            'content' => $text
//                        ]
//                    ]
//                ]
//            );
//            $result = 200 == $response->getStatusCode() ? $response->getBody()->getContents() : new \stdClass();
//            return $result;
//        } catch (\GuzzleHttp\Exception\GuzzleException $exception)
//        {
//            Yii::error($exception->getTraceAsString(), 'WeWork');
//        }

    }


    /**
     * @param $text
     * @return bool|\stdClass|string
     */
    public function send($text)
    {
        return $this->sendText($this->alarmGroup, $text);
    }
}

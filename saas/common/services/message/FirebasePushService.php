<?php

namespace common\services\message;

use common\models\user\UserLoginLog;
use common\services\BaseService;
use common\services\package\PackageService;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\Exception\GuzzleException;
use yii;

class FirebasePushService extends BaseService
{
    private $url = 'https://fcm.googleapis.com/fcm/send';
//    private $key = 'AIzaSyBHqoiz-g3k8ybyQgy8D6Bp_rQsS3EfG44';  #老包的
    private $key = 'AAAAgclZSaU:APA91bHY-kRCBeoeqIGslqcNx6JK5oSgQ-YG1eAVPgiCALjjyATZnf3GvHrsotKn-Gm6GfCtd5LXRcG7rUDh_2KTSuDfqaTMrxyTxCvzCBEJzFCBtX6spsl7qb1BaZb6kHRyITinQ_92';

    private $packageName;
    private $packageService;


    public function __construct($packageName = 'bigshark', $config = [])
    {
        $this->packageName = $packageName;
        $this->packageService = new PackageService($this->packageName);
        $this->key = $this->packageService->getFirebaseToken();
        parent::__construct($config);
    }

    /**
     * 通过客户端上报的push_token发送推送
     * @param $pushToken
     * @param $title
     * @param $message
     * @return bool
     */
    public function push($pushToken, $title, $message)
    {
        $client = new Client();
        try{
            $response = $client->request('POST', $this->url, [
                RequestOptions::HEADERS => [
                    'Authorization' => "key={$this->key}"
                ],
                RequestOptions::JSON => [
                    'to' => $pushToken,
                    'notification' => [
                        'title' => $title,
                        'body' => $message,
                    ]
                ],
            ]);
            $result = 200 == $response->getStatusCode() ? $response->getBody()->getContents() : new \stdClass();
            yii::info([
                'message' => $message,
                'title' => $title,
                'response' => $result
            ],'push');
            $result = json_decode($result, true);
            if(isset($result['success']) && $result['success'] > 0)
            {
                return true;
            }else{
                return false;
            }
        }catch (GuzzleException $e)
        {
            yii::error($e->getTraceAsString());
            return false;
        }

    }


    /**
     * 推送消息给用户
     * @param $userId
     * @param $title
     * @param $message
     * @return bool
     */
    public function pushToUser($userId, $title, $message)
    {
        $pushToken = UserLoginLog::getUserLastPushLog($userId);
        if(empty($pushToken))
        {
            return false;
        }
        return $this->push($pushToken, $title, $message);

    }



    public function setPushKey($key){
        $this->key = $key;
    }

    public function getPackageService(){
        return $this->packageService;
    }
}

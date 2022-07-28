<?php

namespace common\services;

use afs\Request\V20180112\AuthenticateSigRequest;
use Yii;
use yii\base\BaseObject;

/**
 * 日志查询
 */
class SlideService extends BaseObject
{
    public $access_key;
    public $secret_key;

    public function init()
    {
        $config = Yii::$app->params['ali_slide'];
        $this->access_key = $config['access_key'];
        $this->secret_key = $config['secret_key'];
        require_once Yii::getAlias('@common/api/aliyun-openapi/aliyun-php-sdk-afs/aliyun-php-sdk-core') . '/Config.php';
    }

    /**
     * 阿里云滑动验证码签名验证
     * @param $data 前端传的的参数
     * @return \SimpleXMLElement
     */
    public function checkSignAli($data)
    {
        $csessionid = $data['csessionid'];
        $sig        = $data['sig'];
        $scene      = $data['scene'];
        $token      = $data['nc_token'];

        $iClientProfile = \DefaultProfile::getProfile("cn-hangzhou", $this->access_key, $this->secret_key);
        $client = new \DefaultAcsClient($iClientProfile);
        \DefaultProfile::addEndpoint("cn-hangzhou", "cn-hangzhou", "afs", "afs.aliyuncs.com");

        $request = new AuthenticateSigRequest();
        $request->setSessionId($csessionid);
        $request->setToken($token);
        $request->setSig($sig);
        $request->setScene($scene);
        $request->setAppKey('FFFF0N00000000008D88');
        $request->setRemoteIp(\Yii::$app->request->getUserIP());

        $result = $client->getAcsResponse($request);

        //result->Code为返回的验签结果(100成功,900失败)
        return $result->Code === 100 ? true : false;
    }


}

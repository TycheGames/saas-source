<?php

namespace common\services;

use Yii;
use yii\base\BaseObject;


/**
 * 日志查询
 */
class LogStashService extends BaseObject
{
    /** @var \Aliyun_Log_Client $client */
    public $client;
    public $req1;
    public $endpoint;
    public $accessKeyId;
    public $accessKey;
    public $project;
    public $logstore;

    public function init()
    {
        $config = Yii::$app->params['ali_log'];

        $this->endpoint = YII_ENV_PROD ? $config['endpoint_lan'] : $config['endpoint_wan'];  # 选择与上面步骤创建 project 所属区域匹配的 Endpoint
        $this->accessKeyId = $config['accessKeyId'];    # 使用你的阿里云访问秘钥 AccessKeyId
        $this->accessKey = $config['accessKey'];        # 使用你的阿里云访问秘钥 AccessKeySecret
        $this->project = $config['project'];            # 上面步骤创建的项目名称
        $this->logstore = $config['logstore'];          # 上面步骤创建的日志库名称

        require_once Yii::getAlias('@common/api/aliyun-openapi/aliyun-log-php-sdk') . '/Log_Autoload.php';
        $this->client = new \Aliyun_Log_Client($this->endpoint, $this->accessKeyId, $this->accessKey);
    }

    /**
     * @param $sql
     * @param string $from
     * @param string $to
     * @return mixed
     */
    public function getYunTuLoanLog($sql,$from='',$to='', $logstore = null)
    {
        if(!is_null($logstore))
        {
            $this->logstore = $logstore;
        }
        $topic = "";
        $query = "* | " . $sql;
        $from = $from ? $from : strtotime("today");//当日0点
        $to = $to ? $to : time();
        $res3 = NULL;
        /** @var \Aliyun_Log_Models_GetLogsResponse $res3 */
        while (is_null($res3) || (! $res3->isCompleted())) {
            $req3 = new \Aliyun_Log_Models_GetLogsRequest($this->project, $this->logstore, $from, $to, $topic, $query);
            $res3 = $this->client->getLogs($req3);
            $result = $res3->getLogs();
            return $result;
        }
    }

    public function queryYunTuLoanLog($query,$from='',$to='', $logstore = null)
    {
        if(!is_null($logstore))
        {
            $this->logstore = $logstore;
        }
        $topic = "";
        $from = $from ? $from : strtotime("today");//当日0点
        $to = $to ? $to : time();
        $res3 = NULL;
        /** @var \Aliyun_Log_Models_GetLogsResponse $res3 */
        while (is_null($res3) || (! $res3->isCompleted())) {
            $req3 = new \Aliyun_Log_Models_GetLogsRequest($this->project, $this->logstore, $from, $to, $topic, $query);
            $res3 = $this->client->getLogs($req3);
            $result = $res3->getLogs();
            return $result;
        }
    }
}

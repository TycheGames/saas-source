<?php

namespace common\helpers;

use yii;

/**
 * 延迟队列
 * Class RedisDelayQueue
 * @package common\helpers
 */
class RedisDelayQueue {

    //延迟队列桶的数量
    const BUCKET_NUM = YII_ENV_PROD ? 12 : 1;

    //延迟队列桶的名称
    const BUCKET_NAME = 'delay_queue:bucket_';
    const JOB_KEY_NAME = 'delay_queue:job';


    /**
     * @return object|null
     */
    private static function getDb()
    {
        return  yii::$app->redis;
    }

    private static function setBucket()
    {
        $key = mt_rand(0, self::BUCKET_NUM - 1);
        return self::getBucketList()[$key];

    }

    /**
     * 获取延迟桶列表
     * @return array
     */
    public static function getBucketList()
    {
        $list = [];
        for ($i = 1; $i <= self::BUCKET_NUM; $i++ )
        {
            $list[] = self::BUCKET_NAME . $i;
        }
        return $list;
    }


    /**
     * 延迟队列 - 入列
     * @param string $queueName 真实的队列名
     * @param string|int $body 队列
     * @param int $delayTime 延迟时间 单位秒
     * @return bool
     */
    public static function pushDelayQueue($queueName, $body, $delayTime)
    {
        $time = time() + $delayTime;
        $bucketName = self::setBucket();
        $redis = self::getDb();
        $jobId = self::generateJobId($queueName . md5($body));
        $content = json_encode([
            'body' => $body,
            'queue_name' => $queueName,
            'delay_time' => $time,
            'job_id' => $jobId
        ], JSON_UNESCAPED_UNICODE);
        if(!self::setJob($jobId, $content))
        {
            return false;
        }
        if(!$redis->zadd($bucketName, $time, $jobId))
        {
            return false;
        }

        return true;
    }


    /**
     * 延迟队列 - 出列
     * @param string $bucketName 延迟队列桶的名字
     * @return bool
     */
    public static function popDelayQueue($bucketName)
    {
        $redis = self::getDb();
        $bucket = $redis->zrange($bucketName, 0, 0, 'WITHSCORES');
        if(empty($bucket))
        {
            return false;
        }
        //判断当前时间是否大于延迟时间
        if(time() < $bucket[1])
        {
            return false;
        }
        $jobId = $bucket[0];
        $job = self::getJob($jobId);

        self::delBucket($bucketName, $jobId);
        self::delJob($jobId);

        return $job;

    }

    private static function setJob($jobId, $body)
    {
        $redis = self::getDb();
        return boolval($redis->hset(self::JOB_KEY_NAME, $jobId, $body));
    }

    private static function getJob($jobId)
    {
        $redis = self::getDb();
        return $redis->hget(self::JOB_KEY_NAME, $jobId);
    }

    private static function delJob($jobId)
    {
        $redis = self::getDb();
        return boolval($redis->hdel(self::JOB_KEY_NAME, $jobId));
    }


    private static function delBucket($bucketName, $jobId)
    {
        $redis = self::getDb();
        return boolval($redis->zrem($bucketName, $jobId));
    }

    private static function generateJobId($preName)
    {
        return uniqid($preName);
    }
}


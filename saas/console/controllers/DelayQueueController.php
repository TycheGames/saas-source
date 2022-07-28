<?php
namespace console\controllers;

use Yii;
use common\helpers\RedisDelayQueue;
use common\helpers\RedisQueue;
use common\services\message\WeWorkService;
use yii\base\Exception;

class DelayQueueController extends BaseController {

    private $maxExecuteTime = 300;

    public function actionTimer($bucketName)
    {
        if(!$this->lock())
        {
            return;
        }

        $now = time();

        while (true)
        {
            if (time() - $now > $this->maxExecuteTime) {
                $this->printMessage('运行满5分钟，关闭当前脚本');
                return;
            }

            $queue = RedisDelayQueue::popDelayQueue($bucketName);
            if (empty($queue)) {
                $this->printMessage('无延迟队列,休眠1秒');
                sleep(1);
                continue;
            }

            $queue = json_decode($queue, true);
            $queueName = $queue['queue_name'];
            $delayTime = $queue['delay_time'];
            $body = $queue['body'];
            $jobId = $queue['job_id'];
            $this->printMessage("队列名:{$queueName},jobId:{$jobId},延迟时间:". date('Y-m-d H:i:s', $delayTime) . "开始入列");

            try{
                if(RedisQueue::push([$queueName, $body]))
                {
                    $this->printMessage("队列名:{$queueName},jobId:{$jobId},延迟时间:". date('Y-m-d H:i:s', $delayTime) . "开始成功");
                }else{
                    throw new Exception("队列名:{$queueName},jobId:{$jobId},延迟时间:". date('Y-m-d H:i:s', $delayTime) . "开始失败");
                }
            }catch (Exception $exception)
            {
                RedisDelayQueue::pushDelayQueue($queue, $body, 0);

                $service = new WeWorkService();
                $message = sprintf('[%s][delay-queue/timer] %s in %s:%s',
                    Yii::$app->id, $exception->getMessage(), $exception->getFile(), $exception->getLine());
                $message .= $exception->getTraceAsString();
                $service->send($message);
            }



        }


    }

}

<?php
namespace console\components;


use common\helpers\Util;
use common\services\message\WeWorkService;
use Yii;
use yii\web\HttpException;


class ErrorHandler extends \yii\console\ErrorHandler {

    /**
     * Logs the given exception
     * @param \Exception $exception the exception to be logged
     */
    public function logException($exception)
    {
        if(YII_ENV_PROD
            && !($exception instanceof HttpException && 403 == $exception->statusCode)
        )
        {
            $server_ip = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '';
            $client_ip = Util::getUserIP();
            $requested_route = \yii::$app->requestedRoute;
            $category = get_class($exception);
            if ($exception instanceof HttpException) {
                $category = 'yii\\web\\HttpException:' . $exception->statusCode;
            } elseif ($exception instanceof \ErrorException) {
                $category .= ':' . $exception->getSeverity();
            }

            $message = sprintf('[%s][%s][%s][%s][%s]异常: %s in %s:%s',
                \yii::$app->id, $server_ip, $client_ip, $requested_route,$category,
                $exception->getMessage(), $exception->getFile(), $exception->getLine());
            $cache_key = md5($message);
            if(!Yii::$app->cache->get($cache_key))
            {
                $service = new WeWorkService();
                $service->send($message);
                Yii::$app->cache->set($cache_key,1, 600);
            }
        }

        parent::logException($exception);
    }
}

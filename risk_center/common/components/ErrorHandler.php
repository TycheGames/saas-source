<?php
namespace common\components;

use common\helpers\CommonHelper;
use common\helpers\Util;
use common\services\message\WeWorkService;
use Yii;
use yii\base\UserException;
use yii\web\Response;
use yii\web\HttpException;
use yii\web\ForbiddenHttpException;
use yii\helpers\Html;


class ErrorHandler extends \yii\web\ErrorHandler {
    /**
     * @see \yii\web\ErrorHandler::renderException()
     */
    public function renderException($exception) {
        if (Yii::$app->has('response')) {
            $response = Yii::$app->getResponse();
        } else {
            $response = new Response();
        }

        $useErrorView = $response->format === Response::FORMAT_HTML && (!YII_DEBUG || $exception instanceof UserException);

        if ($useErrorView && $this->errorAction !== null) {
            $result = Yii::$app->runAction($this->errorAction);
            if ($result instanceof Response) {
                $response = $result;
            } else {
                $response->data = $result;
            }
        }
        elseif ($response->format === Response::FORMAT_HTML) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest' || YII_ENV_TEST) {
                // AJAX request
                $response->data = '<pre>' . $this->htmlEncode($this->convertExceptionToString($exception)) . '</pre>';
            } else {
                // if there is an error during error rendering it's useful to
                // display PHP error in debug mode instead of a blank screen
                if (YII_DEBUG) {
                    \ini_set('display_errors', true);
                }
                $file = $useErrorView ? $this->errorView : $this->exceptionView;
                $response->data = $this->renderFile($file, [
                    'exception' => $exception,
                ]);
            }
        } else {
            $response->data = $this->convertExceptionToArray($exception);
        }

        // http状态码统一用200，避免客户端处理麻烦，错误内容在返回内容的code中体现
        // 后面看是否需要对网站做处理
        $response->setStatusCode(200);

        $response->send();
    }

    /**
     * 重写抛出的异常数据的数据结构
     * @see \yii\web\ErrorHandler::convertExceptionToArray()
     */
    public function convertExceptionToArray($exception)
    {
        // 非debug模式下的非用户级的异常将模糊提示，避免暴露服务端信息
        if (!YII_DEBUG && !$exception instanceof UserException && !$exception instanceof HttpException) {
            $exception = new HttpException(500, 'Server busy');
        }

        if ($exception instanceof ForbiddenHttpException) { // 未登录code为-2
            $code = -2;
            $message = "Please login again";
        }
        else if ($exception->getCode() == 0) { // 特殊处理，所有默认exception为0时，返回给客户端都置为-1
            $code = -1;
            $message = $exception->getMessage() ?: 'Server busy';
        }
        else {
            $code = $exception->getCode();
            $message = $exception->getMessage();
        }

        $array = [
            'message' => $message,
            'data' => [],
            'code' => $code,
        ];

        if (YII_DEBUG) { // debug模式下，多一些错误信息
            $array['type'] = get_class($exception);
            if (!$exception instanceof UserException) {
                $array['file'] = $exception->getFile();
                $array['line'] = $exception->getLine();
                $array['stack-trace'] = explode("\n", $exception->getTraceAsString());
                if ($exception instanceof \yii\db\Exception) {
                    $array['error-info'] = $exception->errorInfo;
                }
            }
            else
            {
                $array['file'] = $exception->getFile();
                $array['line'] = $exception->getLine();
            }
        }
        if (($prev = $exception->getPrevious()) !== null) {
            $array['previous'] = $this->convertExceptionToArray($prev);
        }


        // 如果是jsonp
        if (Yii::$app->getResponse()->format === Response::FORMAT_JSONP) {
            $array = [
                'data' => $array,
                'callback' => Html::encode(\yii::$app->request->get('callback')),
            ];
        }
        if (-2 == $code) {
            $array = [
                'code'=>-2,
                'message'=>'Please login again',
                'data'=>[ 'item'=>[] ],
            ];
        }

        return $array;
    }

    /**
     * Logs the given exception
     * @param \Exception $exception the exception to be logged
     */
    public function logException($exception) {

        $sendAlert = true;
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
                if(404 == $exception->statusCode)
                {
                    $sendAlert = false;
                }
            } elseif ($exception instanceof \ErrorException) {
                $category .= ':' . $exception->getSeverity();
            }

            $userId = 0;
            if(!Util::isCli() && !Yii::$app->user->isGuest)
            {
                $userId = Yii::$app->user->id;
            }

            $message = sprintf('[%s][%s][%s][%s][%s][%s]异常: %s in %s:%s',
                \yii::$app->id, $server_ip, $client_ip, $userId, $requested_route,$category,
                $exception->getMessage(), $exception->getFile(), $exception->getLine());
            $cache_key = md5($message);
            if($sendAlert && !Yii::$app->cache->get($cache_key))
            {
                $service = new WeWorkService();
                $service->send($message);
                Yii::$app->cache->set($cache_key,1, 600);
            }
        }

        parent::logException($exception);


    }
}

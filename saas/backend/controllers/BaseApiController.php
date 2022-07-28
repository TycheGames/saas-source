<?php
namespace backend\controllers;

use backend\models\Merchant;
use Yii;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\helpers\Url;
use backend\models\AdminOperateLog;
use yii\web\Response;

/**
 * Base controller
 *
 * @property \yii\web\Request $request The request component.
 * @property \yii\web\Response $response The response component.
 */

abstract class BaseApiController extends Controller
{
    const MSG_NORMAL = 0;
    const MSG_SUCCESS = 1;
    const MSG_ERROR = 2;

    protected $merchantIds; //商户ID
    protected $isNotMerchantAdmin = false; //是否非商户管理员

    public $showPhoneAdminIds = [1,29,30,80];//29lushan 30ruchongzhi 80yanzhenlin
    public $isHiddenPhone = true;
    public function init()
    {
        parent::init();
        $this->response->format = Response::FORMAT_JSON;
        if (Yii::$app->user->isGuest) {
            $this->merchantIds = 0;
        } else {
            if (Yii::$app->user->identity->merchant_id) {
                $this->merchantIds = Yii::$app->user->identity->merchant_id;
            } else {
                $this->isNotMerchantAdmin = true;

                if (!empty(Yii::$app->user->identity->to_view_merchant_id)) {
                    $arrMerchantIds = explode(',', Yii::$app->user->identity->to_view_merchant_id);
                    $this->merchantIds = $arrMerchantIds;
                } else {
                    $this->merchantIds = Merchant::getAllMerchantId();
                }

                $this->merchantIds[] = 0;

            }
            if(in_array(Yii::$app->user->identity->getId(),$this->showPhoneAdminIds)){
                $this->isHiddenPhone = false;
            }
        }
    }

    // 是否验证本系统的权限逻辑
    public $verifyPermission = true;

    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {

            if(yii::$app->user->isGuest)
            {
                throw new ForbiddenHttpException();
            }
            if ($this->verifyPermission) {
                if (YII_ENV_PROD ) {
                    $this->saveRequestLog();
                }
            }

            return true;
        } else {
            return false;
        }
    }

    /**
     * 改变redirect的默认行为
     * 调用 yii\web\Response::send() 方法来确保没有其他内容追加到响应中
     *
     * @see yii\web\Controller::redirect()
     */
    public function redirect($url, $statusCode = 302, $isSend = true)
    {
        if (is_array($url)) {
            $url[0] = \yii\helpers\Inflector::camel2id($url[0]);
        } else {
            $url = \yii\helpers\Inflector::camel2id($url);
        }

        $response = parent::redirect($url, $statusCode);

        if ($isSend === true) {
            $response->send();
            exit;
        } else {
            return $response;
        }
    }

    /**
     * 获得请求对象
     */
    public function getRequest()
    {
        return \Yii::$app->getRequest();
    }

    /**
     * 获得返回对象
     */
    public function getResponse()
    {
        return \Yii::$app->getResponse();
    }

    /**
     * 跳转到提示页面
     * @param string $message 提示语
     * @param int $msgType 提示类型，不同提示类型提示语样式不一样
     * @param string $url 自动跳转url地址，不设置则默认显示返回上一页连接
     * @return string
     */
    public function redirectMessage($message, $msgType = self::MSG_NORMAL, $url = '')
    {
        switch ($msgType) {
            case self::MSG_SUCCESS:
                $messageClassName = 'infotitle2';
                break;
            case self::MSG_ERROR:
                $messageClassName = 'infotitle3';
                break;
            default:
                $messageClassName = 'marginbot normal';
                break;
        }
        return $this->render('/message', array(
            'message' => $message,
            'messageClassName' => $messageClassName,
            'url' => $url,
        ));
    }

    /*
      * 保存请求日志
      */
    private function saveRequestLog() {
        $route = Url::to();
        if (\yii::$app->request->method=='POST') {
            $params = array_merge($_GET, $_POST);
            if (isset($params['r'])) {
                unset($params['r']);
            }
            if (isset($params['_csrf'])) {
                unset($params['_csrf']);
            }
            if (isset($params['ADMIN_SID'])) {
                unset($params['ADMIN_SID']);
            }
            if(strpos($route,'&')!==false)
            {
                $route = strstr($route,'&',true);
            }
            $model = new AdminOperateLog();
            $model->merchant_id = Yii::$app->user->identity->merchant_id;
            $model->admin_user_id = Yii::$app->user->identity->id;
            $model->admin_user_name = Yii::$app->user->identity->username;
            $model->request = \yii::$app->request->method;
            $model->request_params = json_encode($params, JSON_UNESCAPED_UNICODE);
            $model->ip = Yii::$app->request->userIP;
            $model->route = $route;
            return $model->save();
        }
    }
}

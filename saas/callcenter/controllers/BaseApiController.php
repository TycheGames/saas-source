<?php
namespace callcenter\controllers;

use backend\models\Merchant;
use callcenter\models\CollectionAdminOperateLog;
use Yii;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\helpers\Url;
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

    // 是否验证本系统的权限逻辑
    public $verifyPermission = true;
    protected $client_secret;
    protected $org_name;
    protected $token_hours = 24;
    protected $price = 0;

    public $is_voip;   //是否支持网络电话
    public $work_name; //工号
    public $sub_detail ; //子账号信息
    protected $merchantIds; //商户ID
    protected $isNotMerchantAdmin = false; //是否非商户管理员

    public $showPhoneAdminIds = [1,2,6];//2lushan 6wangpeng
    public $isHiddenPhone = true;
    public function init()
    {
        parent::init();
        $this->response->format = Response::FORMAT_JSON;

        if(Yii::$app->user->isGuest)
        {
            $this->merchantIds = 0;
        }else{
            if(Yii::$app->user->identity->merchant_id){
                $this->merchantIds = Yii::$app->user->identity->merchant_id;
            }else{
                $this->isNotMerchantAdmin = true;
                $this->merchantIds = Merchant::getAllMerchantId();
                $this->merchantIds[] = 0;
            }
            if(in_array(Yii::$app->user->identity->getId(),$this->showPhoneAdminIds)){
                $this->isHiddenPhone = false;
            }
            if(empty(yii::$app->user->identity->login_app)){
                Yii::$app->user->logout();
            }
        }
    }

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



 /*
 * 保存请求日志
 */
    private function saveRequestLog() {
        $route = Url::to();
        $flag1 = strpos($route,'user-collection');
        $flag2 = strpos($route,'admin-user');
        if(\yii::$app->request->method=='POST' || $flag1 !==false || $flag2 !== false )
        {
            $flag3 = strpos($route,'user-collection%2Fuser-list');
            $flag4 = strpos($route,'admin-user%2Flist');
            $flag5 = strpos($route,'admin-user%2Fcollection-operate-list');
            $flag6 = strpos($route,'admin-user%2Frole-list');
            if( $flag3===false && $flag4===false && $flag5===false && $flag6===false){
                $params = array_merge($_GET, $_POST);
                if ($params) {
                    if (isset($params['r'])) {
                        unset($params['r']);
                    }
                    if (isset($params['_csrf'])) {
                        unset($params['_csrf']);
                    }
                    if (isset($params['ADMIN_SID'])) {
                        unset($params['ADMIN_SID']);
                    }
                }
                //$route = Url::to();
                if(strpos($route,'&')!==false)
                {
                    $route = strstr($route,'&',true);
                }
                $model = new CollectionAdminOperateLog();
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

    protected function _setcsvHeader($filename){
        $now = gmdate("D, d M Y H:i:s");
        header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
        header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
        header("Last-Modified: {$now} GMT");
        // force download
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header("Content-type: application/vnd.ms-excel; charset=utf8");
        // disposition / encoding on response body
        header("Content-Disposition: attachment;filename={$filename}");
        header("Content-Transfer-Encoding: binary");
        //设置utf-8 + bom ，处理汉字显示的乱码
        print(chr(0xEF).chr(0xBB).chr(0xBF));
    }
    protected function _array2csv(array &$array){
        if (count($array) == 0) {
            return  null;
        }
        ob_start();
        $df = fopen("php://output", 'w');
        fputcsv($df, array_keys(reset($array)));
        foreach ($array as $row) {
            fputcsv($df, $row);
        }
        fclose($df);
        return ob_get_clean();
    }
}
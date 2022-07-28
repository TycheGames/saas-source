<?php
namespace callcenter\controllers;

use backend\models\Merchant;
use callcenter\models\AdminUserRole;
use callcenter\models\CollectionAdminOperateLog;
use Yii;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use callcenter\models\ActionModel;
use yii\helpers\Url;


/**
 * Base controller
 *
 * @property \yii\web\Request $request The request component.
 * @property \yii\web\Response $response The response component.
 */
abstract class BaseController extends Controller
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

    public $showPhoneAdminIds = [
        1,
        2, //lushan
        6, //wangpeng
        16, //ruchongzhi
        58,120,1683,2122,2550,2560,2938, //yanzhenlin yanzhenlin_moneyclick yanzhenlin_Rf
        261,  //xionghuakun
        264,  //zhanghaiyun
        1098,
        1376,1378,1684,2434,2551,2561,2940,  //zhufangqi_all zhufangqi_moneyclick zhufangqi_RF
        2067,2068,2069,2433,2552,2562,2939, //zhouchunlu_mk zhouchunlu_RF zhouchunlu
        2753, //sumitbhardhwaj20125_BTL
        3407, //changdaoyin
        5030, //sunjiahao
        5033, //zhangyan
        4668
    ];
    public $isHiddenPhone = true;
    public $strategyOperating = false;

    public function init()
    {
        parent::init();
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
            if(in_array(Yii::$app->user->identity->getId(),[
                1,
                2,79,370, //lushan
                6, //wangpeng
                16, //ruchongzhi
                261, //xionghuakun
                1376,1377,1378,1684,2434,2551,2561,2940, //zhufangqi
                58,120,779,1304,1683,2122,2550,2560,2938, //yanzhenlin
                2067,2068,2069,2433,2552,2562,2939, //zhouchunlu
                3407, //changdaoyin
                5030, //sunjiahao
                5033, //zhangyan
                4668
            ])){
                $this->strategyOperating = true;
            }
        }
    }

    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {

            //非线上环境强制 填写方法名称
            if (YII_ENV_DEV) {
                $permissionArr = Yii::$app->params['permissionControllers'];
                list($controllerId, $actionId) = explode('/', $this->getRoute());
                $className = str_replace(' ', '', ucwords(str_replace('-', ' ', $controllerId)));
                $controName = $className . "Controller";
                if (array_key_exists($controName, $permissionArr)) {
                    $actionName = str_replace(' ', '', ucwords(str_replace('-', ' ', $actionId)));
                    $class = "callcenter\\controllers\\" . $controName;
                    $action = "action" . $actionName;
                    $rf = new \ReflectionClass($class);
                    $method = $rf->getMethod($action);
                    $actionModel = new ActionModel($method);
                    $title = $actionModel->getTitle();
                    $name = $actionModel->getName();
                    if (empty($title) || ($title == $name)) {
                        throw new ForbiddenHttpException('抱歉，此控制器：'.$controName.'，此方法：'.$method->name.', 没有添加注释请添加！如： @name 测试。');
                    }
                }
            }


            if ($this->verifyPermission) {

                // 验证登录
                if (Yii::$app->user->getIsGuest()) {
                    return $this->redirect(['main/login']);
                }
                //验证外包催收机构IP
//                if(Yii::$app->user->id && LoanCollection::is_outside(Yii::$app->user->id)){
//                    $userIps = Util::getUserIP();
//                    $userIp = explode(',', $userIps);
//                    $valid_ip = LoanCollectionIp::is_valid_ip(trim($userIp[0]));
//                    if(!$valid_ip){
//                        throw new ForbiddenHttpException('IP未备案，禁止访问，您的IP：'.$userIp[0]);
//                    }
//                }
                if (YII_ENV_PROD ) {
                    $this->saveRequestLog();
                }
                // 验证权限
                if (!Yii::$app->user->identity->getIsSuperAdmin()) {
                    $permissionsKey = 'permissions';
                    if(Yii::$app->user->identity->master_user_id){
                        //副手权限
                        $permissionsKey = 'permissions_'.Yii::$app->user->identity->master_user_id;
                    }
                    $permissions = Yii::$app->getSession()->get($permissionsKey);
                    if ($permissions) {
                        $permissions = json_decode($permissions, true);
                        if (!in_array($this->getRoute(), $permissions)) {
                            throw new ForbiddenHttpException('The role does not have this permission 1');
                        }
                    } else {
                        $role = Yii::$app->user->identity->role;
                        if($role){
                            $roleModel = AdminUserRole::find()
                                ->andWhere(['name' => explode(',', $role)])
                                ->all();
                            if($roleModel){
                                $arr = array();
                                foreach ($roleModel as $val) {
                                    if($val->permissions)
                                        $arr = array_unique(array_merge($arr,json_decode($val->permissions)));
                                }
                                Yii::$app->getSession()->set($permissionsKey, json_encode($arr));
                                if (!in_array($this->getRoute(), $arr)) {
                                    throw new ForbiddenHttpException('The role does not have this permission 2');
                                }
                            }else{
                                throw new ForbiddenHttpException('The role does not have this permission 3');
                            }


                        }else{
                            throw new ForbiddenHttpException('The role does not have this permission');
                        }
                    }
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

    /**
     * 跳转到提示页面
     * @param string $message	提示语
     * @param int $msgType		提示类型，不同提示类型提示语样式不一样
     * @param string $url		自动跳转url地址，不设置则默认显示返回上一页连接
     * @param int $goHistory
     * @return string
     */
    public function redirectMessage($message, $msgType = self::MSG_NORMAL, $url = '', $goHistory = -1)
    {
        switch ($msgType) {
            case self::MSG_SUCCESS: $messageClassName = 'infotitle2';break;
            case self::MSG_ERROR: $messageClassName = 'infotitle3';break;
            default: $messageClassName = 'marginbot normal';break;
        }
        return $this->render('/message', array(
            'message' => $message,
            'messageClassName' => $messageClassName,
            'goHistory' => $goHistory,
            'url' => $url,
        ));
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
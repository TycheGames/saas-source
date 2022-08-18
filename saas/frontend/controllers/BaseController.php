<?php
namespace frontend\controllers;

use Carbon\Carbon;
use common\helpers\RedisQueue;
use common\helpers\Util;
use common\helpers\ReturnCode;
use common\models\enum\ErrorCode;
use common\services\client\ClientService;
use common\services\loan\LoanService;
use Yii;
use yii\helpers\Html;
use yii\log\Target;
use yii\web\Controller;
use yii\web\Response;


abstract class BaseController extends Controller
{

    /**
     * @var $return ReturnCode
     */
    protected $return;

	// 由于都是api接口方式，所以不启用csrf验证
	public $enableCsrfValidation = false;
	protected $request;
	protected $isApiDocumentRequest = false;

    private $_appInfo = null;

	public function init()
	{
		parent::init();
		Yii::$app->getResponse()->format = Response::FORMAT_JSON;
		$this->request = Yii::$app->request;
        $this->return = new ReturnCode();
        if (YII_DEBUG && $this->request->get('callback')) { // 参数有callback的话则是jsonp
            Yii::$app->getResponse()->format = Response::FORMAT_JSONP;
            $this->isApiDocumentRequest = true;
        }
        // if (!Yii::$app->user->isGuest) {
        //     $check = LoanService::haveOpeningOrderNoExport(Yii::$app->user->id);
        //     if (!$check) {
        //         Yii::$app->user->logout();
        //         return $this->return->returnFailed(ErrorCode::NOT_LOGGED());
        //     }
        // }
	}
	

	
	public function afterAction($action, $result)
	{
		$result = parent::afterAction($action, $result);


		// 调试情况下返回性能数据
		if (YII_DEBUG && YII_ENV_DEV && Yii::$app->getResponse()->format == Response::FORMAT_JSON) {
		    $messages = Target::filterMessages(Yii::getLogger()->messages, yii\log\Logger::LEVEL_PROFILE, ['yii\db\Command::query', 'yii\db\Command::execute']);
		    $timings = Yii::getLogger()->calculateTimings($messages);
		    $sql_count = count($timings);
            $sql_time_temp = 0;
            foreach ($timings as $timing) {
                $sql_time_temp += $timing['duration'];
            }
            $sql_time = number_format($sql_time_temp * 1000) . 'ms';
            $result['debug_info'] = [
                'sql_count' => strval($sql_count),
                'sql_time' => $sql_time
            ];

        }

        if (YII_DEBUG && Yii::$app->getResponse()->format == Response::FORMAT_JSONP) {
            // 特殊处理：如果是验证码，由于已经encode过了，所以需要先decode成原始数据

            // jsonp返回数据特殊处理
            $callback = Html::encode(Yii::$app->request->get('callback'));
            $result = [
                'data' => $result,
                'callback' => $callback,
            ];
        }
		return $result;
	}

    public function getError($error)
    {
        return $error[0] ?? '';
    }

    /**
     * 客户端上报的所有信息  解密也在这边操作
     * @param null $key
     * @return array|mixed|string
     */
    public function getClientInfo($key = null)
    {
        if (empty($this->_appInfo)) {
            //如果是API Document发起的请求，则自己模拟客户端传参
            if ($this->isApiDocumentRequest) {
                $clientInfo = [
                    'clientType'      => 'android',
                    'osVersion'       => '9.9.9',
                    'appVersion'      => '1.3.6',
                    'deviceName'      => 'DEBUG 999',
                    'appMarket'       => 'dhancash_debug',
                    'deviceId'        => '',
                    'brandName'       => 'DEBUG',
                    'bundleId'        => 'com.jc.dhancash',
                    'longitude'       => '',
                    'latitude'        => '',
                    'configVersion'   => '1.3.6',
                    'szlmQueryId'     => '', //数盟ID
                    'screenWidth'     => 720,
                    'screenHeight'    => 1344,
                    'packageName'     => 'dhancash',
                    'googlePushToken' => '',
                    'tdBlackbox'      => '',
                    'ip'              => '127.0.0.1',
                    'clientTime'      => time() * 100
                ];
                if (is_null($key)) {
                    return $clientInfo;
                } else {
                    return $clientInfo[$key];
                }
            }
            $appInfo = Yii::$app->request->headers->get('appInfo');
            $appIv = Yii::$app->request->headers->get('appIv');
            $appKey = Yii::$app->request->headers->get('appKey');
            $service = new ClientService();
            $appInfo = $service->getClientInfo($appInfo, $appKey, $appIv);
            $appInfo = json_decode($appInfo, true);
            $appInfo['ip'] = Util::getUserIP();
            $this->_appInfo = $appInfo;
        } else {
            $appInfo = $this->_appInfo;
        }

        if(is_null($key))
        {
            return $appInfo;
        }else{
            return $appInfo[$key] ?? '';
        }

    }

    public function isTrueUser()
    {
        $clientInfo = $this->getClientInfo();
        $bundleIdFlag = strpos($clientInfo['bundleId'] ?? '', 'com') !== false;
        $appVersionFlag = trim($clientInfo['appVersion'] ?? '0') != trim($clientInfo['appVersionShow'] ?? '1');
        if (empty($clientInfo['deviceId'])) {
            $deviceIdFlag = true;
        } else {
            $strDate = date('Ymd');
            $deviceIdKey = sprintf('user_device_id:%s:%s:%s', $strDate, $clientInfo['packageName'], $clientInfo['deviceId']);
            $deviceIdRes = RedisQueue::inc([$deviceIdKey, 1]);
            $deviceIdFlag = $deviceIdRes <= 100;
            if ($deviceIdRes < 2) {
                RedisQueue::expire([$deviceIdKey, strtotime('tomorrow') - time()]);
            }
        }
        $clientTime = intval($clientInfo['timestamp'] ?? 0);
        $clientTimeFlag = Carbon::createFromTimestampMs($clientTime)->diffInRealHours(Carbon::now()) <= 2;

        if ($bundleIdFlag && $appVersionFlag && $deviceIdFlag && $clientTimeFlag) {
            return true;
        }

        return false;
    }

    public function googlePushToken()
    {
        return $this->getClientInfo('googlePushToken');
    }

    public function tdBlackbox()
    {
        return $this->getClientInfo('tdBlackbox');
    }

    public function ip()
    {
        return $this->getClientInfo('ip');
    }

    public function packageName()
    {
        return $this->getClientInfo('packageName');
    }

    public function screenHeight()
    {
        return $this->getClientInfo('screenHeight');
    }

    public function screenWidth()
    {
        return $this->getClientInfo('screenWidth');
    }

    public function szlmQueryId()
    {
        return $this->getClientInfo('szlmQueryId');
    }

    public function latitude()
    {
        return $this->getClientInfo('latitude');
    }

    public function longitude()
    {
        return $this->getClientInfo('longitude');
    }

    public function bundleId()
    {
        return $this->getClientInfo('bundleId');
    }

    public function brandName()
    {
        return $this->getClientInfo('brandName');
    }

    public function deviceId()
    {
        return $this->getClientInfo('deviceId');
    }

    public function appMarket()
    {
        return $this->getClientInfo('appMarket');
    }

    public function deviceName()
    {
        return $this->getClientInfo('deviceName');
    }

    public function osVersion()
    {
        return $this->getClientInfo('osVersion');
    }

    /**
     * 客户端类型
     * @return array|mixed|string
     */
    public function clientType()
    {
        return $this->getClientInfo('clientType');
    }

    /**
     * 版本号
     * @return array|mixed
     */
    public function appVersion()
    {
        $ver = Yii::$app->request->headers->get('appVersion');
        if(empty($ver))
        {
            $ver = Yii::$app->request->get('appVersion','');
        }
        return $ver;
    }
}
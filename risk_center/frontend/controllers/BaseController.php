<?php
namespace frontend\controllers;

use common\helpers\ReturnCode;
use Yii;
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

	public function init()
	{
		parent::init();
		Yii::$app->getResponse()->format = Response::FORMAT_JSON;
		$this->request = Yii::$app->request;
        $this->return = new ReturnCode();
	}
	



    public function getError($error)
    {
        return $error[0] ?? '';
    }


}
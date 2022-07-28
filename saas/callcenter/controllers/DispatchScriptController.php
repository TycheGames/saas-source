<?php


namespace callcenter\controllers;


use callcenter\models\ScriptTaskLog;
use common\helpers\RedisQueue;
use Yii;
use yii\helpers\Url;

class DispatchScriptController extends BaseController
{
    /**
     * @name 工作台-分派任务列表
     * @return string
     */
    public function actionList()
    {
        $searchModel = new ScriptTaskLog();
        $dataProvider = $searchModel->search(Yii::$app->request->post());

        return $this->render('list', [
                'dataProvider' => $dataProvider,
                'searchModel'  => $searchModel,
            ]
        );
    }

    /**
     * @name 工作台-分派任务执行
     * @return string
     */
    public function actionAdd()
    {
        $lockRes = RedisQueue::lock('dispatch_script_lock', 600);
        if (!$lockRes) {
            return $this->redirectMessage('添加分配脚本失败，请10分钟后重试', self::MSG_ERROR);
        }

        $model = new ScriptTaskLog();
        $model->script_type = ScriptTaskLog::SCRIPT_TYPE_DISPATCH;
        $model->exec_status = ScriptTaskLog::STATUS_INIT;
        $model->operator_id = Yii::$app->user->identity->getId();
        $model->save();

        return $this->redirectMessage('添加分配脚本成功',self::MSG_SUCCESS, Url::toRoute('dispatch-script/list'));
    }
}
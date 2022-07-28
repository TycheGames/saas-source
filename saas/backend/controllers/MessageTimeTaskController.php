<?php
namespace backend\controllers;

use common\models\package\PackageSetting;
use Yii;
use yii\helpers\Url;
use yii\data\Pagination;
use common\models\message\MessageTimeTask;

/**
 * 短信&语音定时任务 - 脚本处理console - MessageNoticeController
 * 2018-04-18 lijia
 */
class MessageTimeTaskController extends BaseController {


    /**
     * @name 内容管理-通知管理-短信&语音定时任务
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function actionList()
    {
        // $config = MessageTimeTask::getConfigKeyArr();
        // echo '<pre/>';
        // print_r($config);
        // exit;
        $query = MessageTimeTask::find();
        $get = $this->request->get();
        $merchantId = is_array($this->merchantIds) ? 0 : $this->merchantIds;

        if(isset($get['task_type']) && $get['task_type'] != null){
            $query = $query->andWhere(['task_type' => \intval($get['task_type'])]);
        }
        if(isset($get['loan_type']) && $get['loan_type'] != null){
            $query = $query->andWhere(['loan_type' => \intval($get['loan_type'])]);
        }
        if(isset($get['tips_type']) && $get['tips_type'] != null){
            $query = $query->andWhere(['tips_type' => \intval($get['tips_type'])]);
        }
        if(isset($get['task_time']) && $get['task_time'] != null){
            $query = $query->andWhere(['task_time' => \intval($get['task_time'])]);
        }
        if(isset($get['task_status']) && $get['task_status'] != null){
            $query = $query->andWhere(['task_status' => \intval($get['task_status'])]);
        }
        if($merchantId != 0){
            $query = $query->andWhere(['merchant_id' => $merchantId]);
        }
        if(isset($get['provider']) && $get['provider'] != null && $get['provider'] != 'smsService_None'){
            $hasSelectProvider = true;
        } else {
            $hasSelectProvider = false;
        }
        $get['is_export'] = isset($get['is_export']) ?  $get['is_export'] : 0;
        $query = $query->andWhere(['is_export' => \intval($get['is_export'])]);

        $countQuery = clone $query;
        $pages = new Pagination(['totalCount' => $countQuery->count('id',Yii::$app->get('db'))]);
        $pages->pageSize = 100;
        $query = $query->orderBy(["id" => SORT_DESC]);
        $list = $query->offset($pages->offset)->limit($pages->limit)->asArray()->all(Yii::$app->get('db'));

        $newList = [];
        $config_params = Yii::$app->params;
        foreach ($list as $item) {
            /**
             * @var MessageTimeTask $item
             */
            $newItem = $item;
            $newItem['task_time'] = MessageTimeTask::$task_time_map[$item['task_time']];
            $configs = json_decode($newItem['config'], true);
            $newItem['provider'] = '';
            $providerKeys = '';
            foreach ($configs as $config) {
                $batchSend = $config['batch_send'] ?? 0;
                if(!empty($config['content'])) {
                    if (isset($config_params[$config['aisle_type']]['aisle_title'])) {
                        $tmpProvider = $config_params[$config['aisle_type']]['aisle_title'] . '</br>';
                    } else {
                        $tmpProvider = '';
                    }
                    $newItem['provider'] .= '(' . MessageTimeTask::$is_batch_send_map[$batchSend] . ') ' . $tmpProvider;
                    $providerKeys .= $config['aisle_type'] . ',';
                }
            }
            if($hasSelectProvider && strpos($providerKeys, $get['provider']) === false) {
                continue;
            }

            array_push($newList, $newItem);
        }
        $taskTime = array_column($newList, 'task_time');
        array_multisort($taskTime, SORT_ASC, $newList);
        $merchantId = is_array($this->merchantIds) ? array_pop($this->merchantIds) : $this->merchantIds;

        return $this->render('list', [
            'list'  => $newList,
            'is_export' => $get['is_export'],
            'merchantId' => $merchantId,
            'pages' => $pages,
            'package_setting' => array_keys(PackageSetting::getSourceIdMap($this->merchantIds))
        ]);
    }

    /**
     * @name [内容管理]新增短信&语音定时任务
     */
    public function actionAdd()
    {
        $model = new MessageTimeTask();
        $get = $this->request->get();
        $merchantId = is_array($this->merchantIds) ? 0 : $this->merchantIds;
        if($model->load($this->request->post()) && $model->validate()){
            $post = $this->request->post();
            foreach($post['MessageTimeTask']['config'] as $apps => &$config){
                $config['aisle_type'] = trim($config['aisle_type']);
//                $config['text_type'] = intval($config['text_type']);
                $config['content'] = trim($config['content']);
            }
            $model->merchant_id = $merchantId;
            $model->config = json_encode($post['MessageTimeTask']['config']);
            $handle_log = [
                ['handler' => Yii::$app->user->identity->username, 'action' => '新增任务', 'time' => date('Y-m-d H:i:s', time()), 'record' => '']
            ];
            $model->handle_log = json_encode($handle_log);
            if($model->save(false)){
                return $this->redirectMessage(Yii::T('common', 'Add success'), self::MSG_SUCCESS, Url::toRoute(['message-time-task/list', 'is_export' => $get['is_export']]));
            }else{
                return $this->redirectMessage(Yii::T('common', 'Add fail'), self::MSG_ERROR);
            }
        }

        //判断$this->merchantIds

        return $this->render('add', [
            'model' => $model,
            'is_export' => $get['is_export'],
            'merchantId' => $merchantId,
            'package_setting' => array_keys(PackageSetting::getSourceIdMap($this->merchantIds))
        ]);
    }

    /**
     * @name [内容管理]编辑短信&语音定时任务
     */
    public function actionEdit($id)
    {
        $model = MessageTimeTask::findOne($id);
        $get = $this->request->get();
        if($model->load($this->request->post()) && $model->validate()){
            $post = $this->request->post();
            foreach($post['MessageTimeTask']['config'] as $apps => &$config){
                $config['aisle_type'] = trim($config['aisle_type']);
                $config['batch_send'] = intval($config['batch_send']);
//                $config['text_type'] = intval($config['text_type']);
                $config['content'] = trim($config['content']);
            }
            $model->config = json_encode($post['MessageTimeTask']['config']);
            $handle_log = json_decode($model->handle_log,true);
            $handle_log[] = ['handler' => Yii::$app->user->identity->username, 'action' => '更新任务', 'time' => date('Y-m-d H:i:s', time())];
            $model->handle_log = json_encode($handle_log);
            if($model->save(false)){
                return $this->redirectMessage(Yii::T('common', 'Update success'), self::MSG_SUCCESS, Url::toRoute(['message-time-task/list', 'is_export' => $get['is_export']]));
            }else{
                return $this->redirectMessage(Yii::T('common', 'Update fail'), self::MSG_ERROR);
            }
        }
        $merchantId = is_array($this->merchantIds) ? 0 : $this->merchantIds;

        return $this->render('edit', [
            'model' => $model,
            'is_export' => $get['is_export'],
            'merchantId' => $merchantId,
            'package_setting' => array_keys(PackageSetting::getSourceIdMap($this->merchantIds))
        ]);
    }

    /**
     * @name [内容管理]预览短信&语音定时任务
     */
    public function actionPreView($id)
    {
        $model = MessageTimeTask::find()->where(['id' => intval($id)])->limit(1)->asArray()->one();
        $model['tips_type'] = $model['tips_type'] == MessageTimeTask::TIPS_TODAY ? MessageTimeTask::$tips_type_map[$model['tips_type']] : MessageTimeTask::$tips_type_map[$model['tips_type']].$model['days_type'].'天';
        $model['task_time'] = MessageTimeTask::$task_time_map[$model['task_time']];
        $model['task_status'] = MessageTimeTask::$task_status_map[$model['task_status']];
        $model['is_app_notice'] = MessageTimeTask::$is_app_notice_map[$model['is_app_notice']];
        $model['created_at'] = date('Y-m-d H:i:s',$model['created_at']);
        $model['updated_at'] = date('Y-m-d H:i:s',$model['updated_at']);
        $model['config'] = json_decode($model['config'],true);
        $model['handle_log'] = json_decode($model['handle_log'],true);
        $model['send_log'] = json_decode($model['send_log'],true);

        $package_setting = array_keys(PackageSetting::getSourceIdMap($this->merchantIds));
        $merchantId = is_array($this->merchantIds) ? 0 : $this->merchantIds;
        foreach ($package_setting as $pack_name)
        {
            if($merchantId == 0)
            {
                $aisle_type = MessageTimeTask::getAisleType($pack_name,$merchantId);
            }
            else
            {
                $aisle_type = array();
                $aisle_type1[] = MessageTimeTask::getAisleType($pack_name,$merchantId);
                foreach($aisle_type1 as $k => $v)
                {
                    foreach($v as $kk=>$vv)
                    {
                        $aisle_type[$kk] = $vv;
                    }
                }
            }
        }

        foreach ($model['config'] as $key => $value) {
            $aisle = trim($value['aisle_type']);
            if($aisle == MessageTimeTask::smsService_None){
                $model['config'][$key]['aisle_name'] = $aisle_type[$aisle];
            }else{
                $aisle_type_tab = array_column($aisle_type, $aisle);
                $model['config'][$key]['aisle_name'] = $aisle_type_tab[0];
            }
            $batch = $value['batch_send'] ?? 0;
            $model['config'][$key]['batch_send_name'] = MessageTimeTask::$is_batch_send_map[$batch];
        }
        echo json_encode($model);exit;
    }

    /**
     * @name [内容管理]预览短信&语音定时任务
     */
    public function actionChangeStatus($id,$status)
    {
        $status = intval($status);
        $id = intval($id);
        if(isset(MessageTimeTask::$task_status_map[$status])){
            $model = MessageTimeTask::find()->where(['id' => intval($id)])->limit(1)->asArray()->one();
            if($model['task_status'] != $status){
                $action = MessageTimeTask::$task_status_map[$status].'任务';
                $handle_log = json_decode($model['handle_log'],true);
                $handle_log[] = ['handler' => Yii::$app->user->identity->username, 'action' => $action, 'time' => date('Y-m-d H:i:s', time())];
                $ret = Yii::$app->db->createCommand()->update(MessageTimeTask::tableName(),[
                    "task_status" => $status,
                    "handle_log" => json_encode($handle_log)
                ],[
                    "id" => $id
                ])->execute();

                if($ret){
                    $model['task_status'] = MessageTimeTask::$task_status_map[$status];
                    $model['handle_log'] = $handle_log;
                }
            }
            echo json_encode($model);
        }
        exit;
    }
}


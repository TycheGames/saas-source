<?php


namespace callcenter\controllers;

use callcenter\models\AdminMessageTask;
use callcenter\models\loan_collection\LoanCollectionOrder;
use callcenter\models\loan_collection\UserCompany;
use callcenter\models\SmsTemplate;
use common\models\package\PackageSetting;
use yii\data\Pagination;
use yii\helpers\Url;
use Yii;

class ContentSettingController extends  BaseController
{
    public $companyList;

    public function init()
    {
        parent::init();
        $this->companyList = UserCompany::outsideRealName($this->merchantIds);
    }

    /**
     * @name ContentSettingController 催收短信模板列表
     * @return string
     */
    public function actionSmsTemplateList()
    {
        $condition[] = 'and';
        if ($this->getRequest()->getIsGet()) {
            $search = $this->request->get();
            if (isset($search['is_use']) && $search['is_use'] != '') {
                $condition[] = ['is_use' => intval($search['is_use'])];
            }
        }
        $query = SmsTemplate::find()
            ->where($condition)
            ->andWhere(['merchant_id' => $this->merchantIds]);
        if(!empty($request['is_summary']) && $request['is_summary'] == 1){
            $pages = new Pagination(['totalCount' => $query->count('id')]);
        }else{
            $pages = new Pagination(['totalCount' => 999999]);
        }
        $page_size = \Yii::$app->request->get('page_size',15);
        $pages->pageSize = $page_size;
        $list = $query->orderBy(['id' => SORT_DESC])
            ->offset($pages->offset)
            ->limit($pages->limit)
            ->all();
        return $this->render('sms-template-list',[
            'pages'=> $pages,
            'list' => $list,
            'companys' => $this->companyList
        ]);
    }

    /**
     * @name ContentSettingController 短信模板添加
     * @return string
     */
    public function actionSmsTemplateAdd()
    {
        $model = new SmsTemplate();
        $arrPackage = PackageSetting::getAllLoanPackageNameMap($this->merchantIds);
        $data = $this->request->post();
        if($model->load($data) && $model->validate()){
            $model->merchant_id = Yii::$app->user->identity->merchant_id;
            if($model->save()){
                return $this->redirectMessage('Add Sms Template success',self::MSG_SUCCESS, Url::toRoute('sms-template-list'));
            }else{
                return $this->redirectMessage('Add Sms Template fail',self::MSG_ERROR);
            }
        }
        return $this->render('sms-template-add',[
            'model'=>$model,
            'arrPackage' => $arrPackage,
            'companys' => $this->companyList
        ]);
    }

    /**
     * @name ContentSettingController 短信模板编辑
     * @param int id
     * @return string
     */
    public function actionSmsTemplateEdit($id)
    {
        $model = SmsTemplate::findOne($id);
        $arrPackage = PackageSetting::getAllLoanPackageNameMap($this->merchantIds);
        $data = $this->request->post();
        if($data && $model->load($data) && $model->validate()){
            if($model->save()) {
                return $this->redirectMessage('Update Sms Template success',self::MSG_SUCCESS, Url::toRoute('sms-template-list'));
            } else {
                return $this->redirectMessage('Update Sms Template fail',self::MSG_ERROR);
            }
        }
        $model->can_send_group = $model->can_send_group ? explode(',', $model->can_send_group) : '';
        $model->can_send_outside = $model->can_send_outside ? explode(',', $model->can_send_outside) : '';
        return $this->render('sms-template-edit',[
            'model'=>$model,
            'arrPackage' => $arrPackage,
            'companys' => $this->companyList
        ]);
    }

    /**
     * @name ContentSettingController 组长任务消息配置
     * @return string
     */
    public function actionTeamMessageTask(){
        $condition[] = 'and';
        if ($this->getRequest()->getIsGet()) {
            $search = $this->request->get();
            if (isset($search['status']) && $search['status'] != '') {
                $condition[] = ['task.status' => intval($search['status'])];
            }
            if (isset($search['outside']) && $search['outside'] != '') {
                $condition[] = ['task.outside' => intval($search['outside'])];
            }
            if (isset($search['group']) && $search['group'] != '') {
                $condition[] = ['task.group' => intval($search['group'])];
            }
        }
        $query = AdminMessageTask::find()
            ->alias('task')
            ->select(['task.id','company.title','task.group','task.task_type','task.task_value','task.status','task.updated_at'])
            ->leftJoin(UserCompany::tableName().' company','task.outside = company.id')
            ->where($condition);

        $pages = new Pagination(['totalCount' => $query->count('task.id')]);
        $page_size = \Yii::$app->request->get('page_size',15);
        $pages->pageSize = $page_size;
        $list = $query->orderBy(['task.id' => SORT_DESC])
            ->offset($pages->offset)
            ->limit($pages->limit)
            ->asArray()
            ->all();
        foreach ($list as &$item){
            if(isset(AdminMessageTask::$task_type_overdue_day_map[$item['task_type']])){
                $item['task_type'] = AdminMessageTask::$task_type_overdue_day_map[$item['task_type']];
            }elseif (isset(AdminMessageTask::$task_type_new_map[$item['task_type']])){
                $item['task_type'] = AdminMessageTask::$task_type_new_map[$item['task_type']];
            }else{
                $item['task_type'] = 'money per person';
            }
        }
        return $this->render('team-message-task',[
            'pages'=> $pages,
            'list' => $list,
            'companyList' => $this->companyList
        ]);
    }

    /**
     * @name ContentSettingController 组长任务消息配置添加
     * @return string
     */
    public function actionTeamMessageTaskAdd(){
        /** @var AdminMessageTask $model */
        $model = new AdminMessageTask;
        if($this->request->isPost){
            if($model->load($this->request->post())){
                switch ($model->group){
                    case LoanCollectionOrder::LEVEL_S1_4_7DAY:
                        $model->task_type = AdminMessageTask::TASK_TYPE_S_D4;
                        break;
                    case LoanCollectionOrder::LEVEL_S2:
                        $model->task_type = AdminMessageTask::TASK_TYPE_S_D8;
                        break;
                    case LoanCollectionOrder::LEVEL_M1:
                        $model->task_type = AdminMessageTask::TASK_TYPE_M1;
                        break;
                    case LoanCollectionOrder::LEVEL_M2:
                        $model->task_type = AdminMessageTask::TASK_TYPE_M2;
                        break;
                    case LoanCollectionOrder::LEVEL_M3:
                        $model->task_type = AdminMessageTask::TASK_TYPE_M3;
                        break;
                    case LoanCollectionOrder::LEVEL_M3_AFTER:
                        $model->task_type = AdminMessageTask::TASK_TYPE_M3P;
                        break;
                }
                if($model->validate()){
                    if($model->save()){
                        return $this->redirectMessage('Add success',self::MSG_SUCCESS, Url::toRoute('team-message-task'));
                    } else {
                        return $this->redirectMessage('Add fail',self::MSG_ERROR);
                    }
                }
            }
        }
        $taskTypes = AdminMessageTask::$task_type_overdue_day_map;
        return $this->render('team-message-task-add',[
            'model'=> $model,
            'taskTypes' => $taskTypes,
            'companys' => $this->companyList
        ]);
    }

    /**
     * @name ContentSettingController 组长任务消息配置编辑
     * @return string
     */
    public function actionTeamMessageTaskEdit($id){

        /** @var AdminMessageTask $model */
        $model = AdminMessageTask::findOne($id);
        if($this->request->isPost){
            if($model->load($this->request->post()) && $model->validate(['task_value','status'])){
                if($model->save()){
                    return $this->redirectMessage('Update success',self::MSG_SUCCESS, Url::toRoute('team-message-task'));
                } else {
                    return $this->redirectMessage('Update fail',self::MSG_ERROR);
                }
            }
        }

        if(isset(AdminMessageTask::$task_type_overdue_day_map[$model->task_type])){
            $taskTypes = AdminMessageTask::$task_type_overdue_day_map;
        }elseif (isset(AdminMessageTask::$task_type_new_map[$model->task_type])){
            $taskTypes = [$model->task_type => AdminMessageTask::$task_type_new_map[$model->task_type]];
        }else{
            $taskTypes = [$model->task_type => 'money per person'];
        }

        return $this->render('team-message-task-edit',[
            'model'=> $model,
            'taskTypes' => $taskTypes,
            'companys' => $this->companyList
        ]);
    }
}
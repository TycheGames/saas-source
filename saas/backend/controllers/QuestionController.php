<?php


namespace backend\controllers;


use common\models\question\QuestionList;
use common\services\FileStorageService;
use yii\data\Pagination;
use yii\helpers\Url;
use yii\web\UploadedFile;

class QuestionController extends BaseController
{
    /**
     * @name QuestionController 认证问题-列表
     * @return string
     */
    public function actionQuestionList()
    {
        $query = QuestionList::find();

        $search = $this->request->get();
        if (!empty($search['title'])) {
            $query->where(['like', 'question_title', $search['title']]);
        }
        if (isset($search['is_used']) && $search['is_used'] != '') {
            $query->where(['is_used' => intval($search['is_used'])]);
        }

        $countQuery = clone $query;
        $pages = new Pagination(['totalCount' => $countQuery->count('id')]);
        $pages->pageSize = 15;

        $models = $query
            ->offset($pages->offset)
            ->limit($pages->limit)
            ->all();

        return $this->render('question-list', [
            'models' => $models,
            'pages'  => $pages,
        ]);
    }

    /**
     * @name QuestionController 认证问题-添加
     * @return string
     */
    public function actionQuestionAdd()
    {
        $model = new QuestionList();

        $postData = $this->request->post();
        $questionImg = UploadedFile::getInstance($model,'question_img');
        if ($questionImg) {
            $service = new FileStorageService();
            $postData['QuestionList']['question_img'] = $service->uploadFile(
                'india/backend',
                $questionImg->tempName,
                $questionImg->getExtension()
            );
        }
        if ($postData && $model->load($postData) && $model->validate()) {
            if($model->save()) {
                return $this->redirectMessage('Add question success',self::MSG_SUCCESS, Url::toRoute('question-list'));
            } else {
                return $this->redirectMessage('Add question fail',self::MSG_ERROR);
            }
        }

        return $this->render('question-add', [
            'model' => $model
        ]);
    }

    /**
     * @name QuestionController 认证问题-编辑
     * @return string
     */
    public function actionQuestionEdit()
    {
        $id = $this->request->get('id', 0);

        $model = QuestionList::findOne($id);
        if (empty($model)) {
            return $this->redirectMessage('Question does not exist',self::MSG_ERROR);
        }
        $postData = $this->request->post();

        if ($postData && $model->load($postData) && $model->validate()) {
            $questionImg = UploadedFile::getInstance($model,'question_img');
            if ($questionImg) {
                $service = new FileStorageService();
                $model->question_img = $service->uploadFile(
                    'india/backend',
                    $questionImg->tempName,
                    $questionImg->getExtension()
                );
            } else {
                $model->question_img = $model->oldAttributes['question_img'] ?? null;
            }
            if($model->save()) {
                return $this->redirectMessage('Update question success',self::MSG_SUCCESS, Url::toRoute('question-list'));
            } else {
                return $this->redirectMessage('Update question fail',self::MSG_ERROR);
            }
        }

        return $this->render('question-edit', [
            'model' => $model,
        ]);
    }

    /**
     * @name QuestionController 认证问题-启用
     * @return string
     */
    public function actionQuestionEnable()
    {
        $id = $this->request->get('id', 0);
        $isUsed =  $this->request->get('is_used', 0);
        $model = QuestionList::findOne($id);
        if (empty($model)) {
            return $this->redirectMessage('Question does not exist',self::MSG_ERROR);
        }
        $model->is_used = intval($isUsed);
        if ($model->save()) {
            return $this->redirectMessage('Update question successful', self::MSG_SUCCESS, Url::toRoute('question-list'));
        } else {
            return $this->redirectMessage('Update question failed', self::MSG_ERROR);
        }
    }
}
<?php

use yii\helpers\Url;

$this->shownav('system', 'menu_language_question_list');

$this->showsubmenu(Yii::T('common', 'Language certification'), [
    [Yii::T('common', 'Questions list'), Url::toRoute('question/question-list'), 1],
]);

echo $this->render('question-form', [
    'model' => $model,
]);
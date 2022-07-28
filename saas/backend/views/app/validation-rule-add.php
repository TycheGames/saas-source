<?php

use yii\helpers\Url;

$this->shownav('system', 'menu_validation_switch_rule');
$this->showsubmenu(Yii::T('common', 'Authentication service routing rules'), [
    [Yii::T('common', 'Rules List'), Url::toRoute('app/validation-switch-rule'), 0],
    [Yii::T('common', 'Add rules'), Url::toRoute('app/validation-switch-rule-add'), 1],
]);

echo $this->render('validation-rule-form', [
    'model' => $model,
]);
<?php

use yii\helpers\Url;

$this->shownav('system', 'menu_validation_switch_rule');
$this->showsubmenu(Yii::T('common', 'Rules List'), [
    ['List', Url::toRoute('app/validation-switch-rule'), 1],
]);

echo $this->render('validation-rule-form', [
    'model' => $model,
]);
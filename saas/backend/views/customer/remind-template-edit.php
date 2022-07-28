<?php

use yii\helpers\Url;

$this->shownav('customer', 'menu_remind_sms_template');
$this->showsubmenu(Yii::T('common', 'Remind Sms Template Edit'), [
    ['List', Url::toRoute('customer/remind-sms-template'), 1],
]);

echo $this->render('remind-template-form', [
    'model' => $model,
    'arrPackage' => $arrPackage
]);
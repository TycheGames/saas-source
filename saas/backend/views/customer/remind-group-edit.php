<?php

use yii\helpers\Url;

$this->shownav('customer', 'menu_remind_group');
$this->showsubmenu('Remind group', [
    ['List', Url::toRoute('customer/remind-group'), 1],
]);

echo $this->render('remind-group-form', [
    'model' => $model,
    'merchants' => $merchants,
    'isNotMerchantAdmin' => $isNotMerchantAdmin
]);
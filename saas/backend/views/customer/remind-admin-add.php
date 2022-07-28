<?php

use yii\helpers\Url;

$this->shownav('customer', 'menu_remind_admin_list');
$this->showsubmenu(Yii::T('common', 'Remind Admin Add'), [
    ['List', Url::toRoute('customer/remind-admin-list'), 1],
]);

echo $this->render('remind-admin-form', [
    'model' => $model,
    'remindGroups' => $remindGroups,
    'groups' => $groups,
    'merchants' => $merchants,
    'isNotMerchantAdmin' => $isNotMerchantAdmin
]);
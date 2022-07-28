<?php

use yii\helpers\Url;
$this->shownav('system', 'menu_product_begin');

$this->showsubmenu(Yii::T('common', 'Type list'), array(
    array('List', Url::toRoute('product-setting/period-setting-list'), 1),
));

echo $this->render('period-setting-form', [
    'model' => $model,
    'data' => $data,
    'packageSetting' => $packageSetting,
    'isNotMerchantAdmin' => $isNotMerchantAdmin,
]);
?>


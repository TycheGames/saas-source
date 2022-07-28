<?php

echo $this->render('/product-setting/submenus',['route'=>Yii::$app->controller->route]);
?>

<div class="loan-fund-create">

    <?= $this->render('setting-form', [
        'model'         => $model,
        'packageSetting' => $packageSetting,
        'periodList' => $periodList,
        'isNotMerchantAdmin' => $isNotMerchantAdmin
    ]) ?>

</div>
